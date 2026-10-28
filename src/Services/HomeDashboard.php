<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

use Hubleto\App\Community\Auth\Models\User;
use Hubleto\App\Community\Dashboards\Models\Dashboard;
use Hubleto\App\Community\Dashboards\Models\Panel;

/**
 * The home screen of Hlthcr users: a default Hubleto dashboard with the panels both legacy apps show.
 * Both show the hygienist the audits of the next month; other panels differ, so they are left out.
 */
class HomeDashboard extends \Hubleto\Erp\Core
{
  // [board url slug => panel title]
  const BOARDS_FOR_INTERNAL_ROLES = [
    'hlthcr-audits/boards/upcoming-audits' => 'Najbližšie audity (max 1 mesiac)',
  ];

  // For installations with existing users (Cli/setup-home-dashboards.php) and for the demo data.
  public function ensureForAllUsers(): void
  {
    $userIds = $this->getModel(User::class)->record->where('is_active', 1)->pluck('id');
    foreach ($userIds as $idUser) {
      $this->ensureForUser((int) $idUser);
    }
  }

  /**
   * Creates the user's default dashboard (once) and adds missing panels for the user's roles.
   */
  public function ensureForUser(int $idUser): void
  {
    $isDashboardsAppInstalled = $this->appManager()->getApp(\Hubleto\App\Community\Dashboards\Loader::class) !== null;
    if (!$isDashboardsAppInstalled || $idUser <= 0) return;

    // Users with an Hlthcr role get an own dashboard even without panels,
    // otherwise Hubleto would show them somebody else's default dashboard.
    $boards = $this->getBoardsForUser($idUser);
    $hasHlthcrRole = count($this->getService(RoleApps::class)->getAppsForUser($idUser)) > 0;
    if (count($boards) == 0 && !$hasHlthcrRole) return;

    $idDashboard = $this->getOrCreateDefaultDashboard($idUser);
    /** @var Panel */
    $mPanel = $this->getModel(Panel::class);
    $registeredBoards = $this->getService(\Hubleto\App\Community\Dashboards\Manager::class)->getBoards();

    $position = $mPanel->record->where('id_dashboard', $idDashboard)->count();
    foreach ($boards as $boardUrlSlug => $panelTitle) {
      if (!isset($registeredBoards[$boardUrlSlug])) continue;

      $panelExists = $mPanel->record
        ->where('id_dashboard', $idDashboard)
        ->where('board_url_slug', $boardUrlSlug)
        ->exists()
      ;
      if ($panelExists) continue;

      $position++;
      $mPanel->record->create([
        'id_dashboard' => $idDashboard,
        'board_url_slug' => $boardUrlSlug,
        'title' => $panelTitle,
        'width' => 1,
        'position' => $position,
      ]);
    }
  }

  // Panels for the roles of the given user (not the logged-in one: this also runs when an admin assigns roles).
  private function getBoardsForUser(int $idUser): array
  {
    $user = $this->getModel(User::class)->record->where('id', $idUser)->first();
    if (!$user) return [];

    $isMedicalStaff = $this->userHasRole($idUser, Roles::MEDICAL_STAFF);
    if ($isMedicalStaff) return [];

    $boards = self::BOARDS_FOR_INTERNAL_ROLES;
    return $boards;
  }

  private function userHasRole(int $idUser, string $roleKey): bool
  {
    $idRole = $this->getService(Roles::class)->getRoleId($roleKey);
    return $this->getModel(\Hubleto\App\Community\Auth\Models\UserHasRole::class)->record
      ->where('id_user', $idUser)
      ->where('id_role', $idRole)
      ->exists()
    ;
  }

  private function getOrCreateDefaultDashboard(int $idUser): int
  {
    /** @var Dashboard */
    $mDashboard = $this->getModel(Dashboard::class);
    $defaultDashboard = $mDashboard->record
      ->where('id_owner', $idUser)
      ->where('is_default', 1)
      ->first()
    ;
    if ($defaultDashboard) return (int) $defaultDashboard->id;

    $createdDashboard = $mDashboard->record->create([
      'id_owner' => $idUser,
      'title' => 'Prehľad',
      'slug' => 'hlthcr',
      'color' => '#0062b1',
      'is_default' => 1,
    ]);
    return (int) $createdDashboard->id;
  }
}
