<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

use Hubleto\App\Community\Auth\Models\User;

/**
 * Who receives a notification, chosen by role.
 * Both legacy apps used fixed user ids from the config, which reached nobody when the user did not exist.
 */
class Recipients extends \Hubleto\Erp\Core
{
  // Active users with the given Hlthcr role.
  public function getUserIdsWithRole(string $roleKey): array
  {
    /** @var Roles */
    $roles = $this->getService(Roles::class);
    if ($roles->getRoleId($roleKey) <= 0) return [];

    /** @var User */
    $mUser = $this->getModel(User::class);
    $userIds = $mUser->record
      ->where('is_active', 1)
      ->whereIn('id', $roles->userIdsWithRole($roleKey))
      ->pluck('id')
    ;

    $ids = [];
    foreach ($userIds as $idUser) {
      $ids[] = (int) $idUser;
    }
    return $ids;
  }

  // Active Hubleto administrators (user type).
  public function getAdministratorIds(): array
  {
    /** @var User */
    $mUser = $this->getModel(User::class);
    $ids = [];
    foreach ($mUser->record->where('is_active', 1)->where('type', User::TYPE_ADMINISTRATOR)->pluck('id') as $idUser) {
      $ids[] = (int) $idUser;
    }
    return $ids;
  }

  // Nurses and administrators, without duplicates (they plan the medical exams in both legacy apps).
  public function getExamPlanners(): array
  {
    return array_values(array_unique(array_merge($this->getUserIdsWithRole(Roles::NURSE), $this->getAdministratorIds())));
  }
}
