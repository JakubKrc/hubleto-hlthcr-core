<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

use Hubleto\App\Community\Auth\Models\User;

/**
 * Record visibility rules shared by all Hlthcr apps.
 * Hubleto's generic CRUD API accepts any model, so every Hlthcr record manager must apply these rules.
 */
class AccessScope extends \Hubleto\Erp\Core
{
  const SEES_ALL = 'all';
  const SEES_OWNED = 'owned';
  const SEES_OWNED_AND_MANAGED = 'owned-and-managed';
  const SEES_NOTHING = 'nothing';

  private function roles(): Roles
  {
    return $this->getService(Roles::class);
  }

  /**
   * The Hlthcr role decides, whatever the user type (both legacy apps): hygienists see own clients, nurses and medical
   * staff all. External users see nothing; users with no Hlthcr role follow Hubleto's rule for Customers.
   */
  public function getVisibilityOfActiveUser(): string
  {
    $roles = $this->roles();
    if ($roles->isActiveUserAdministratorOrManager()) return self::SEES_ALL;
    if ($roles->isActiveUserExternal()) return self::SEES_NOTHING;
    if ($roles->activeUserHasRole(Roles::NURSE) || $roles->activeUserHasRole(Roles::MEDICAL_STAFF)) return self::SEES_ALL;
    if ($roles->activeUserHasRole(Roles::HYGIENIST)) return self::SEES_OWNED;

    $userType = $this->authProvider()->getUserType();

    if ($userType == User::TYPE_EMPLOYEE || $userType == User::TYPE_ASSISTANT) {
      return self::SEES_OWNED;
    }

    if ($userType == User::TYPE_MANAGER || $userType == User::TYPE_CHIEF_OFFICER) {
      return self::SEES_OWNED_AND_MANAGED;
    }

    return self::SEES_ALL;
  }

  public function activeUserHasHlthcrRole(): bool
  {
    $roles = $this->roles();
    foreach (array_keys($roles->getRoleDefinitions()) as $roleKey) {
      if ($roles->activeUserHasRole($roleKey)) return true;
    }
    return false;
  }

  public function restrictToVisibleCustomers(mixed $query, string $customerIdColumn): mixed
  {
    $visibility = $this->getVisibilityOfActiveUser();
    if ($visibility == self::SEES_ALL) return $query;
    if ($visibility == self::SEES_NOTHING) return $query->whereRaw('1 = 0');

    $idUser = $this->authProvider()->getUserId();

    return $query->whereIn($customerIdColumn, function ($visibleCustomers) use ($idUser, $visibility) {
      $visibleCustomers->select('id')->from('customers')->where('id_owner', $idUser);
      if ($visibility == self::SEES_OWNED_AND_MANAGED) {
        $visibleCustomers->orWhere('id_manager', $idUser);
      }
    });
  }

  // Medical staff have only clients, branches, employees and exams, so no audits, documents or deficiencies.
  public function activeUserSeesWorkRecords(): bool
  {
    $roles = $this->roles();
    if ($roles->isActiveUserAdministratorOrManager()) return true;
    if ($roles->activeUserHasRole(Roles::NURSE) || $roles->activeUserHasRole(Roles::HYGIENIST)) return true;
    if ($roles->isActiveUserExternal()) return false;
    return !$roles->activeUserHasRole(Roles::MEDICAL_STAFF);
  }

  public function restrictToWorkRecordReaders(mixed $query): mixed
  {
    if ($this->activeUserSeesWorkRecords()) return $query;
    return $query->whereRaw('1 = 0');
  }
}
