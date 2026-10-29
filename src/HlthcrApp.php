<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore;

/**
 * Base of the Hlthcr app loaders.
 */
abstract class HlthcrApp extends \Hubleto\Erp\App
{
  /**
   * Each Hlthcr app registers the Hlthcr translator as soon as it is created: apps start in alphabetical order,
   * and "php hubleto init" installs an app before its init() runs.
   */
  public function __construct()
  {
    parent::__construct();
    Services\Translator::register();
  }

  // Whether the signed-in user may open the app, for buttons and links to it.
  public function isAppPermittedForActiveUser(string $appNamespace): bool
  {
    $app = $this->appManager()->getApp($appNamespace);
    if ($app === null) return false;
    return $this->permissionsManager()->isAppPermittedForActiveUser($app);
  }

  /**
   * Hubleto builds external apps into js/external/<Vendor>/<App>.js but does not load them yet.
   */
  public function getJavascriptAssetsUrls(): array
  {
    if (!file_exists($this->srcFolder . '/Loader.tsx')) return [];
    return [$this->env()->assetsCompiledUrl . '/js/external/WaiBlue/' . $this->shortName . '.js'];
  }

  /**
   * Hubleto calls generateDemoData() of the apps in alphabetical order, but the Hlthcr demo data needs
   * the order of the app dependencies, so the first call generates the demo data of all Hlthcr apps.
   */
  public function generateDemoData(): void
  {
    $this->getService(Services\DemoData::class)->generateForAllHlthcrApps();
  }

  // FAKE demo data of this app only; DemoData::generateForAllHlthcrApps() calls it in the right order.
  public function generateHlthcrDemoData(): void
  {
  }
}
