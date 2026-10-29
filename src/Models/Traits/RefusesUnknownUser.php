<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

use Hubleto\App\Community\Auth\Models\User;
use Hubleto\Framework\Exceptions\RecordSaveException;

/**
 * For models with a user column: an id of a user that does not exist gets a readable message
 * instead of a database error.
 */
trait RefusesUnknownUser
{
  protected function refuseUnknownUser(array $record, string $userColumn): void
  {
    $idUser = (int) ($record[$userColumn] ?? 0);
    if ($idUser <= 0) return;

    $userExists = $this->getModel(User::class)->record->where('id', $idUser)->exists();
    if (!$userExists) {
      throw new RecordSaveException($this->translate('The chosen user does not exist.'));
    }
  }
}
