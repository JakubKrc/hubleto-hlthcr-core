<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

use Hubleto\App\External\WaiBlue\HlthcrCore\Services\RecordWriteGuard;
use Hubleto\App\External\WaiBlue\HlthcrCore\Services\Roles;

/**
 * For models whose forms and lists should offer adding, editing and deleting only when the user may change
 * the model (the rules of Services/RecordWriteGuard).
 */
trait OffersOnlyAllowedChanges
{
  protected function offerOnlyAllowedChanges(mixed $description): mixed
  {
    /** @var Roles */
    $roles = $this->getService(Roles::class);
    // Hubleto's "Import from CSV" skips the write rules (patch notes), so only administrators are offered it.
    // An entry without a type replaces it in the table's "more actions" menu and shows nothing.
    $isTable = $description instanceof \Hubleto\Framework\Description\Table;
    if ($isTable && !$roles->isActiveUserAdministrator()) {
      $description->ui['moreActions']['showImportCsvScreen'] = ['type' => 'hidden'];
    }

    /** @var RecordWriteGuard */
    $recordWriteGuard = $this->getService(RecordWriteGuard::class);
    if ($recordWriteGuard->mayActiveUserChangeModel($this->fullName)) return $description;

    $description->permissions['canCreate'] = false;
    $description->permissions['canUpdate'] = false;
    $description->permissions['canDelete'] = false;
    return $description;
  }
}
