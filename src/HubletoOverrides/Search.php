<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\HubletoOverrides;

use Hubleto\Erp\Api\Search as HubletoSearch;

/**
 * Hubleto's global search (Ctrl+K) with the translated app names instead of the technical ones ("HlthcrAudits"),
 * and only results of apps the user has (registered in the HlthcrCore loader).
 */
class Search extends HubletoSearch
{
  public static function register(): void
  {
    \Hubleto\Framework\DependencyInjection::setServiceProvider(HubletoSearch::class, self::class);
  }

  public function __construct()
  {
    parent::__construct();
    // The class is not in a Controllers namespace, so Hubleto does not set the context itself.
    $this->translationContext = 'hubleto-app-external-waiblue-hlthcrcore-loader';
    $this->translationContextInner = 'HubletoOverrides\\Search';
  }

  public function renderJson(): array
  {
    $results = [];
    foreach (parent::renderJson() as $result) {
      $appNamespace = (string) ($result['APP_NAMESPACE'] ?? '');
      if ($appNamespace == '') {
        $results[] = $this->translateHelpLink($result);
        continue;
      }

      $app = $this->appManager()->getApp($appNamespace);
      if ($app === null || !$this->permissionsManager()->isAppPermittedForActiveUser($app)) continue;

      $result['APP_SHORT_NAME'] = $app->manifest['nameTranslated'] ?? $app->shortName;
      $results[] = $result;
    }
    return $results;
  }

  private function translateHelpLink(array $result): array
  {
    if (($result['url'] ?? '') == 'help/search') {
      $result['label'] = $this->translate('Help: how to search');
    }
    return $result;
  }
}
