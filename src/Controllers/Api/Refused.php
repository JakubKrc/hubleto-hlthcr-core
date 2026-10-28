<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Controllers\Api;

/**
 * Answer to a record change the user may not make (see Services/RecordWriteGuard).
 */
class Refused extends \Hubleto\Erp\Controllers\ApiController
{
  public bool $permittedForAllUsers = true;

  public function renderJson(): array
  {
    http_response_code(403);
    return ['status' => 'error', 'message' => $this->translate('You do not have permission to make this change.')];
  }
}
