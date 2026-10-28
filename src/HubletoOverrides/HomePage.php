<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\HubletoOverrides;

use Hubleto\App\Community\Desktop\Controllers\Home as DesktopHome;

/**
 * Hubleto's home page with a Slovak greeting, the user's dashboard and the user card, without Hubleto's
 * English tips, its onboarding box and its logo (registered in the HlthcrCore loader).
 */
class HomePage extends DesktopHome
{
  public static function register(): void
  {
    \Hubleto\Framework\DependencyInjection::setServiceProvider(DesktopHome::class, self::class);
  }

  public function __construct()
  {
    parent::__construct();
    // The class is not in a Controllers namespace, so Hubleto does not set the context itself.
    $this->translationContext = 'hubleto-app-external-waiblue-hlthcrcore-loader';
    $this->translationContextInner = 'Controllers\\HomePage';
  }

  public function prepareView(): void
  {
    parent::prepareView();
    $this->viewParams['greeting'] = $this->getGreeting();
    $this->setView('@Hubleto:App:External:WaiBlue:HlthcrCore/HomePage.twig');
  }

  private function getGreeting(): string
  {
    $hourNow = (int) date('H');
    if ($hourNow < 10) return $this->translate('Good morning');
    if ($hourNow < 18) return $this->translate('Good day');
    return $this->translate('Good evening');
  }
}
