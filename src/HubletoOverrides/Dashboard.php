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
  public static function register(): void
  {
    \Hubleto\Framework\DependencyInjection::setServiceProvider(CommunityDashboard::class, self::class);
  }

  public function __construct()
  {
    parent::__construct();

    // Behave as the community model everywhere else: same migrations, config keys and translations.
    $communityModel = new \ReflectionClass(CommunityDashboard::class);
    $this->fullName = $communityModel->getName();
    $this->shortName = $communityModel->getShortName();
    $this->namespace = $communityModel->getNamespaceName();
    $this->srcFolder = pathinfo((string) $communityModel->getFileName(), PATHINFO_DIRNAME);
    $this->translationContext = 'hubleto-app-community-dashboards-loader';
    $this->translationContextInner = 'Models\\Dashboard';
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
