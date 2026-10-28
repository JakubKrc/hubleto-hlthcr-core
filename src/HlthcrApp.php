<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore;

/**
 * Base of the Hlthcr app loaders.
 */
abstract class HlthcrApp extends \Hubleto\Erp\App
{
  /**
   * Hubleto builds external apps into js/external/<Vendor>/<App>.js but does not load them yet.
   */
  public function getJavascriptAssetsUrls(): array
  {
    if (!file_exists($this->srcFolder . '/Loader.tsx')) return [];
    return [$this->env()->assetsCompiledUrl . '/js/external/WaiBlue/' . $this->shortName . '.js'];
  }
}
