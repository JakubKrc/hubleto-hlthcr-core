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
  const SEES_NOTHING = 'nothing';

  const BRANCHES_APP = 'Hubleto\\App\\External\\WaiBlue\\HlthcrBranches';

  private function roles(): Roles
  {
    return $this->getService(Roles::class);
  }

  /**
   * The Hlthcr role decides, whatever the user type (both legacy apps): hygienists see own clients, nurses and medical
   * staff all. External users see nothing; users with no Hlthcr role see by their user type, like in Hubleto.
   */
  public function getVisibilityOfActiveUser(): string
  {
    $roles = $this->roles();
    if ($roles->isActiveUserAdministratorOrManager()) return self::SEES_ALL;
    if ($roles->isActiveUserExternal()) return self::SEES_NOTHING;
    if ($roles->activeUserHasRole(Roles::NURSE) || $roles->activeUserHasRole(Roles::MEDICAL_STAFF)) return self::SEES_ALL;
    if ($roles->activeUserHasRole(Roles::HYGIENIST)) return self::SEES_OWNED;

    $userType = $this->authProvider()->getUserType();

    // Hubleto also shows managers the clients they manage; the client's manager is not used here (hidden, no legacy use).
    $seesOwnedOnly = $userType == User::TYPE_EMPLOYEE || $userType == User::TYPE_ASSISTANT
      || $userType == User::TYPE_MANAGER || $userType == User::TYPE_CHIEF_OFFICER;
    if ($seesOwnedOnly) return self::SEES_OWNED;

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

    return $query->whereIn($customerIdColumn, function ($ownedCustomers) use ($idUser) {
      $ownedCustomers->select('id')->from('customers')->where('id_owner', $idUser);
    });
  }

  /**
   * Clients a user may choose in dropdowns and sees as the text of linked records: the visible ones, plus the client
   * of a branch the user owns (a branch can have another hygienist than its client, as in one legacy app).
   */
  public function restrictToLinkableCustomers(mixed $query, string $customerIdColumn): mixed
  {
    $visibility = $this->getVisibilityOfActiveUser();
    if ($visibility == self::SEES_ALL) return $query;
    if ($visibility == self::SEES_NOTHING) return $query->whereRaw('1 = 0');

    $idUser = $this->authProvider()->getUserId();
    $hasBranches = $this->appManager()->isAppInstalled(self::BRANCHES_APP);

    return $query->where(function ($linkableCustomers) use ($customerIdColumn, $idUser, $hasBranches) {
      $linkableCustomers->whereIn($customerIdColumn, function ($ownedCustomers) use ($idUser) {
        $ownedCustomers->select('id')->from('customers')->where('id_owner', $idUser);
      });
      if ($hasBranches) {
        $linkableCustomers->orWhereIn($customerIdColumn, function ($customersOfOwnedBranches) use ($idUser) {
          $customersOfOwnedBranches->select('id_customer')->from('hlthcr_branches')->where('id_owner', $idUser);
        });
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
