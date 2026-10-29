<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\HubletoOverrides;

use Hubleto\App\Community\Dashboards\Models\Dashboard as CommunityDashboard;

/**
 * TEMPORARY replacement of Hubleto's Dashboard model (registered in HlthcrCore init). Hubleto filters
 * dashboards by owner only for some user types, so users without a type (nurse, medical staff, manager)
 * got the first default dashboard of anybody on their home page. This model prefers the user's own one.
 */
class Dashboard extends CommunityDashboard
{
  use \Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\BehavesAsCommunityModel;
  public static function register(): void
  {
    \Hubleto\Framework\DependencyInjection::setServiceProvider(CommunityDashboard::class, self::class);
  }

  public function __construct()
  {
    parent::__construct();

    $this->behaveAsCommunityModel(CommunityDashboard::class, 'hubleto-app-community-dashboards-loader');
  }

  public function getDefaultDashboard(): mixed
  {
    $ownDefaultDashboard = $this->record->prepareReadQuery()
      ->where('is_default', true)
      ->where('id_owner', $this->authProvider()->getUserId())
      ->with('PANELS')
      ->first()
    ;
    if ($ownDefaultDashboard) return $ownDefaultDashboard;

    return parent::getDefaultDashboard();
  }
}
