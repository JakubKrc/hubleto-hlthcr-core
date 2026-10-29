<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Controllers\Api;

use Hubleto\App\Community\Auth\Models\User;
use Hubleto\App\External\WaiBlue\HlthcrCore\Services\DropdownRecords;
use Hubleto\App\External\WaiBlue\HlthcrCore\Services\Roles;

/**
 * User dropdown limited to one Hlthcr role, as in both legacy apps (hygienists for clients, branches and audits,
 * medical staff for clinics): active internal users with the role, plus the user the form already has.
 * Same output format as api/record/lookup.
 */
class UserLookup extends \Hubleto\Erp\Controllers\ApiController
{
  const ENDPOINT = 'hlthcr/api/user-lookup';

  // Endpoint of one user column, e.g. UserLookup::endpointFor(Roles::HYGIENIST, 'id_owner').
  public static function endpointFor(string $roleKey, string $formColumn): string
  {
    return self::ENDPOINT . '?role=' . $roleKey . '&column=' . $formColumn;
  }

  public function renderJson(): array
  {
    $search = $this->router()->urlParamAsString('search');
    $roleKey = $this->router()->urlParamAsString('role');
    $formColumn = $this->router()->urlParamAsString('column');
    $formRecord = $this->router()->urlParamAsArray('formRecord');
    $idChosenUser = (int) ($formRecord[$formColumn] ?? 0);

    /** @var Roles */
    $roles = $this->getService(Roles::class);
    $userIdsWithRole = $roles->userIdsWithRole($roleKey);

    /** @var User */
    $mUser = $this->getModel(User::class);
    $query = $mUser->record->prepareLookupQuery($search);
    $query = $query->where(function ($offeredUsers) use ($userIdsWithRole, $idChosenUser) {
      $offeredUsers->where(function ($usersWithRole) use ($userIdsWithRole) {
        $usersWithRole->where('users.is_active', 1);
        $usersWithRole->where('users.type', '<>', User::TYPE_EXTERNAL);
        $usersWithRole->whereIn('users.id', $userIdsWithRole);
      });
      if ($idChosenUser > 0) {
        $offeredUsers->orWhere('users.id', $idChosenUser);
      }
    });

    // A list, as Hubleto's own lookup returns since Hubleto 2.
    return $this->getService(DropdownRecords::class)->load($mUser, $query);
  }
}
