<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers\Traits;

use Hubleto\App\External\WaiBlue\HlthcrCore\Services\AccessScope;

/**
 * For record managers of the service's work records (audits, deficiencies, working documents).
 */
trait WorkRecord
{
  // Medical staff do not see work records, also not through Hubleto's generic API.
  protected function restrictToWorkRecordReaders(mixed $query): mixed
  {
    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    /** @var AccessScope */
    $accessScope = $hubleto->getService(AccessScope::class);
    return $accessScope->restrictToWorkRecordReaders($query);
  }
}
