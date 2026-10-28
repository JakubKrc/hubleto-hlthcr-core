<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

use Hubleto\Framework\Interfaces\CoreInterface;

/**
 * Reads <app>/Lang/<lang>.json of the Hlthcr apps (Hubleto core reads only the project's lang folder) and a few
 * label overrides <app>/Lang/overrides/<lang>/<context>.json for community apps (only where the meaning is unclear).
 */
class Translator extends \Hubleto\Framework\Services\Translator
{
  const HLTHCR_APP_CONTEXT_PREFIX = 'hubleto-app-external-waiblue-';
  const HLTHCR_APP_CONTEXT_SUFFIX = '-loader';
  // Composer packages of the Hlthcr apps start with this name.
  const HLTHCR_PACKAGE_PREFIX = 'wai-blue/hubleto-hlthcr-';

  /** @var array<string, string>|null [app short name => app folder], read once per request */
  private ?array $hlthcrAppFolders = null;

  /** @var array<string, bool> "language/context" pairs whose overrides are already merged */
  private array $overridesApplied = [];

  /**
   * After this call the DI container returns this translator instead of the core one.
   * Apps are initialized alphabetically, so each Hlthcr app calls it, not only HlthcrCore.
   */
  public static function register(): void
  {
    \Hubleto\Framework\DependencyInjection::setServiceProvider(
      \Hubleto\Framework\Services\Translator::class,
      self::class
    );
  }

  public function getDictionaryFilename(CoreInterface $core, string $language, string $context): string
  {
    $normalizedContext = strtolower(strtr($context, '\\/', '--'));
    $appFolder = $this->findHlthcrAppFolderForContext($core, $normalizedContext);

    if ($appFolder === '') {
      return parent::getDictionaryFilename($core, $language, $context);
    }

    return $appFolder . '/Lang/' . $language . '.json';
  }

  // Used by PHP translations (one context at a time).
  public function loadDictionary(CoreInterface $core, string $language, string $context): void
  {
    parent::loadDictionary($core, $language, $context);

    $overrideKey = $language . '/' . $context;
    if (isset($this->overridesApplied[$overrideKey])) return;
    $this->overridesApplied[$overrideKey] = true;

    $overrides = $this->loadOverrides($core, $language);
    if (!isset($overrides[$context])) return;

    $contextDictionary = $this->dictionary[$language][$context] ?? [];
    $this->dictionary[$language][$context] = $this->mergeOverride($contextDictionary, $overrides[$context]);
  }

  // Used by the React UI (whole dictionary at once).
  public function loadFullDictionary(CoreInterface $core, string $language): array
  {
    $dictionary = parent::loadFullDictionary($core, $language);

    if (strlen($language) != 2) return $dictionary;

    foreach ($this->getHlthcrAppFolders() as $appFolderName => $appFolder) {
      $dictionaryFile = $appFolder . '/Lang/' . $language . '.json';
      if (!is_file($dictionaryFile)) continue;

      $appDictionary = json_decode((string) file_get_contents($dictionaryFile), true);
      if (!is_array($appDictionary)) continue;

      $context = self::HLTHCR_APP_CONTEXT_PREFIX . strtolower($appFolderName) . self::HLTHCR_APP_CONTEXT_SUFFIX;
      $dictionary[$context] = $appDictionary;
    }

    foreach ($this->loadOverrides($core, $language) as $context => $contextOverride) {
      $dictionary[$context] = $this->mergeOverride($dictionary[$context] ?? [], $contextOverride);
    }

    return $dictionary;
  }

  /**
   * Returns [context => [contextInner => [english => translated]]] from all apps' Lang/overrides/<language>/ folders.
   */
  private function loadOverrides(CoreInterface $core, string $language): array
  {
    $overrides = [];
    foreach ($this->getHlthcrAppFolders() as $appFolder) {
      $overridesFolder = $appFolder . '/Lang/overrides/' . $language;
      if (!is_dir($overridesFolder)) continue;

      foreach (scandir($overridesFolder) as $fileName) {
        if (!str_ends_with($fileName, '.json')) continue;

        $contextOverride = json_decode((string) file_get_contents($overridesFolder . '/' . $fileName), true);
        if (!is_array($contextOverride)) continue;

        $context = substr($fileName, 0, -strlen('.json'));
        $overrides[$context] = $this->mergeOverride($overrides[$context] ?? [], $contextOverride);
      }
    }
    return $overrides;
  }

  private function mergeOverride(array $contextDictionary, array $contextOverride): array
  {
    foreach ($contextOverride as $contextInner => $translations) {
      if (!is_array($translations)) continue;
      foreach ($translations as $original => $translated) {
        $contextDictionary[$contextInner][$original] = $translated;
      }
    }
    return $contextDictionary;
  }

  /**
   * Returns [app short name => app folder] of the installed Hlthcr apps (composer packages wai-blue/hubleto-hlthcr-*).
   */
  private function getHlthcrAppFolders(): array
  {
    if ($this->hlthcrAppFolders !== null) return $this->hlthcrAppFolders;

    $this->hlthcrAppFolders = [];
    foreach (\Composer\InstalledVersions::getInstalledPackages() as $packageName) {
      if (!str_starts_with($packageName, self::HLTHCR_PACKAGE_PREFIX)) continue;

      $packageFolder = \Composer\InstalledVersions::getInstallPath($packageName);
      $manifestFile = $packageFolder . '/src/manifest.yaml';
      if ($packageFolder === null || !is_file($manifestFile)) continue;

      $manifest = (array) \Symfony\Component\Yaml\Yaml::parse((string) file_get_contents($manifestFile));
      $appNamespace = (string) ($manifest['namespace'] ?? '');
      $appShortName = substr($appNamespace, strrpos($appNamespace, '\\') + 1);
      $this->hlthcrAppFolders[$appShortName] = realpath($packageFolder . '/src');
    }
    return $this->hlthcrAppFolders;
  }

  private function findHlthcrAppFolderForContext(CoreInterface $core, string $normalizedContext): string
  {
    $isHlthcrAppContext = str_starts_with($normalizedContext, self::HLTHCR_APP_CONTEXT_PREFIX)
      && str_ends_with($normalizedContext, self::HLTHCR_APP_CONTEXT_SUFFIX);
    if (!$isHlthcrAppContext) return '';

    $appNameLowercase = substr(
      $normalizedContext,
      strlen(self::HLTHCR_APP_CONTEXT_PREFIX),
      -strlen(self::HLTHCR_APP_CONTEXT_SUFFIX)
    );

    foreach ($this->getHlthcrAppFolders() as $appFolderName => $appFolder) {
      if (strtolower($appFolderName) == $appNameLowercase) return $appFolder;
    }
    return '';
  }
}
