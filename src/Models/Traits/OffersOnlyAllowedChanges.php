<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

use Hubleto\App\External\WaiBlue\HlthcrCore\Services\RecordWriteGuard;

/**
 * For models whose forms and lists should offer adding, editing and deleting only when the user may change
 * the model (the rules of Services/RecordWriteGuard).
 */
trait OffersOnlyAllowedChanges
{
  protected function offerOnlyAllowedChanges(mixed $description): mixed
  {
    /** @var RecordWriteGuard */
    $recordWriteGuard = $this->getService(RecordWriteGuard::class);
    if ($recordWriteGuard->mayActiveUserChangeModel($this->fullName)) return $description;

    $description->permissions['canCreate'] = false;
    $description->permissions['canUpdate'] = false;
    $description->permissions['canDelete'] = false;
    return $description;
  }
}
