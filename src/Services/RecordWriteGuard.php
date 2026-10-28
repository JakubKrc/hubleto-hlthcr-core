<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

use Hubleto\App\External\WaiBlue\HlthcrCore\Interfaces\RouteGuard;
use Hubleto\Framework\Controllers\Api\Record\Delete;
use Hubleto\Framework\Controllers\Api\Record\Save;
use Hubleto\Framework\Controllers\Api\Record\SaveJunction;

/**
 * Rules for changes through Hubleto's generic record API (save, delete, save-junction).
 * Hubleto checks only the owner column there, so without this any user could change any record by its id.
 */
class RecordWriteGuard extends \Hubleto\Erp\Core implements RouteGuard
{
  const REFUSED_ROUTE = 'hlthcr/api/refused';

  // Code lists, the clinic staff and workflows: only administrators and managers change them.
  const MODELS_FOR_ADMINISTRATORS_AND_MANAGERS = [
    'Hubleto\\App\\External\\WaiBlue\\HlthcrCore\\Models\\Hazard',
    'Hubleto\\App\\External\\WaiBlue\\HlthcrCore\\Models\\Clinic',
    'Hubleto\\App\\External\\WaiBlue\\HlthcrCore\\Models\\ClinicHasMedicalStaff',
    'Hubleto\\App\\External\\WaiBlue\\HlthcrCore\\Models\\Car',
    'Hubleto\\App\\External\\WaiBlue\\HlthcrBranches\\Models\\BranchType',
    'Hubleto\\App\\External\\WaiBlue\\HlthcrClients\\Models\\ClientType',
    'Hubleto\\App\\Community\\Workflow\\Models\\Workflow',
    'Hubleto\\App\\Community\\Workflow\\Models\\WorkflowStep',
  ];

  // Medical staff change only exams (both legacy apps show them clients and branches read-only).
  const APPS_MEDICAL_STAFF_MAY_CHANGE = [
    'Hubleto\\App\\External\\WaiBlue\\HlthcrMedicalExams',
    'Hubleto\\App\\Community\\Notifications',
    'Hubleto\\App\\Community\\Dashboards',
  ];

  // Columns that link a record to its parent: the user must see the parent.
  const PARENT_MODELS_BY_COLUMN = [
    'id_customer' => 'Hubleto\\App\\Community\\Customers\\Models\\Customer',
    'id_branch' => 'Hubleto\\App\\External\\WaiBlue\\HlthcrBranches\\Models\\Branch',
    'id_job' => 'Hubleto\\App\\External\\WaiBlue\\HlthcrBranches\\Models\\Job',
    'id_employee' => 'Hubleto\\App\\External\\WaiBlue\\HlthcrEmployees\\Models\\Employee',
    'id_exam' => 'Hubleto\\App\\External\\WaiBlue\\HlthcrMedicalExams\\Models\\Exam',
    'id_audit' => 'Hubleto\\App\\External\\WaiBlue\\HlthcrAudits\\Models\\Audit',
  ];

  // Related records that Hubleto's own forms save together with the record.
  const ALLOWED_SAVE_RELATIONS = [
    'Hubleto\\App\\Community\\Customers\\Models\\Customer' => ['TAGS'],
    'Hubleto\\App\\Community\\Contacts\\Models\\Contact' => ['VALUES', 'TAGS'],
  ];

  public function getRouteToRender(string $route, string $controllerClass): string
  {
    $isRecordChange = in_array($controllerClass, [Save::class, Delete::class, SaveJunction::class]);
    if (!$isRecordChange) return $route;

    // Not signed in: Hubleto itself answers with the sign-in page.
    if (!$this->authProvider()->isUserInSession()) return $route;

    /** @var Roles */
    $roles = $this->getService(Roles::class);
    if ($roles->isActiveUserAdministrator()) return $route;

    if ($this->isChangeAllowed($controllerClass)) return $route;
    return self::REFUSED_ROUTE;
  }

  // Used also by the forms and lists (HlthcrModel), so they offer only the changes the guard allows.
  public function mayActiveUserChangeModel(string $modelClass): bool
  {
    /** @var Roles */
    $roles = $this->getService(Roles::class);
    if ($roles->isActiveUserAdministrator()) return true;
    return $this->isModelChangeAllowedForRoles($modelClass);
  }

  private function isChangeAllowed(string $controllerClass): bool
  {
    // Only Hubleto's leads, deals and projects use it; it can also grant roles.
    if ($controllerClass == SaveJunction::class) return false;

    $modelClass = str_replace('/', '\\', $this->router()->urlParamAsString('model'));
    if (!$this->isModelChangeAllowedForRoles($modelClass)) return false;

    if ($controllerClass == Delete::class) {
      return $this->isRecordVisible($modelClass, $this->router()->urlParamAsInteger('id'));
    }

    $record = $this->router()->urlParamAsArray('record');
    $idRecord = (int) ($record['id'] ?? 0);
    if ($idRecord > 0 && !$this->isRecordVisible($modelClass, $idRecord)) return false;
    if (!$this->areParentsVisible($record)) return false;
    return $this->areSavedRelationsAllowed($modelClass, $idRecord, $record);
  }

  private function isModelChangeAllowedForRoles(string $modelClass): bool
  {
    $appNamespace = $this->getAppNamespaceOfModel($modelClass);
    $app = $this->appManager()->getApp($appNamespace);
    if ($app === null) return false;

    if (!$this->permissionsManager()->isAppPermittedForActiveUser($app)) return false;

    /** @var Roles */
    $roles = $this->getService(Roles::class);
    $isManager = $roles->activeUserHasRole(Roles::MANAGER);

    $isAdministrationModel = in_array($modelClass, self::MODELS_FOR_ADMINISTRATORS_AND_MANAGERS);
    if ($isAdministrationModel && !$isManager) return false;

    $isOnlyMedicalStaff = $roles->activeUserHasRole(Roles::MEDICAL_STAFF) && !$isManager
      && !$roles->activeUserHasRole(Roles::NURSE) && !$roles->activeUserHasRole(Roles::HYGIENIST);
    if ($isOnlyMedicalStaff && !in_array($appNamespace, self::APPS_MEDICAL_STAFF_MAY_CHANGE)) return false;

    return true;
  }

  // "Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Branch" belongs to "Hubleto\App\External\WaiBlue\HlthcrBranches".
  private function getAppNamespaceOfModel(string $modelClass): string
  {
    $modelsPosition = strpos($modelClass, '\\Models\\');
    if ($modelsPosition === false) return '';
    return substr($modelClass, 0, $modelsPosition);
  }

  // The same visibility rules as for reading (the record managers' prepareReadQuery).
  private function isRecordVisible(string $modelClass, int $idRecord): bool
  {
    if ($idRecord <= 0) return false;
    if (!class_exists($modelClass)) return false;

    $model = $this->getModel($modelClass);
    return $model->record->prepareReadQuery()->where($model->table . '.id', $idRecord)->exists();
  }

  private function areParentsVisible(array $record): bool
  {
    foreach (self::PARENT_MODELS_BY_COLUMN as $parentColumn => $parentModelClass) {
      $idParent = (int) ($record[$parentColumn] ?? 0);
      if ($idParent <= 0) continue;
      if (!class_exists($parentModelClass)) continue;
      if (!$this->isRecordVisible($parentModelClass, $idParent)) return false;
    }
    return true;
  }

  // Related rows that already exist must belong to the saved record (no taking over another record's rows).
  private function areSavedRelationsAllowed(string $modelClass, int $idRecord, array $record): bool
  {
    $relationNames = $this->router()->urlParamAsArray('saveRelations');
    if (count($relationNames) == 0) return true;

    $allowedRelationNames = self::ALLOWED_SAVE_RELATIONS[$modelClass] ?? [];
    $model = $this->getModel($modelClass);

    foreach ($relationNames as $relationName) {
      if (!in_array($relationName, $allowedRelationNames)) return false;

      $relationModelClass = $model->relations[$relationName][1];
      $foreignKeyColumn = $model->relations[$relationName][2];
      $relationModel = $this->getModel($relationModelClass);

      $relatedRows = $record[$relationName] ?? [];
      if (!is_array($relatedRows)) continue;

      foreach ($relatedRows as $relatedRow) {
        $idRelatedRow = (int) ($relatedRow['id'] ?? 0);
        if ($idRelatedRow <= 0) continue;

        $idOwnerRecord = (int) $relationModel->record->where('id', $idRelatedRow)->value($foreignKeyColumn);
        if ($idRecord <= 0 || $idOwnerRecord != $idRecord) return false;
      }
    }
    return true;
  }
}
