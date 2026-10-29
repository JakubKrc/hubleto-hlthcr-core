<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

/**
 * For Hlthcr overrides of community models: outside the overridden methods they behave as the community model
 * (same migrations, config keys and translations). Call it in the constructor, after parent::__construct().
 */
trait BehavesAsCommunityModel
{
  private function behaveAsCommunityModel(string $communityModelClass, string $translationContext): void
  {
    $communityModel = new \ReflectionClass($communityModelClass);
    $this->fullName = $communityModel->getName();
    $this->shortName = $communityModel->getShortName();
    $this->namespace = $communityModel->getNamespaceName();
    $this->srcFolder = pathinfo((string) $communityModel->getFileName(), PATHINFO_DIRNAME);
    $this->translationContext = $translationContext;
    $this->translationContextInner = 'Models\\' . $communityModel->getShortName();
  }
}
