<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

/**
 * For Hlthcr overrides of community models: Hubleto reads custom columns by the class in use,
 * but Hlthcr apps store them for the community model (see the patches documents).
 */
trait UsesCommunityCustomColumns
{
  private function addCommunityCustomColumns(array $columns, string $communityModelClass): array
  {
    $customColumns = $this->config()->forModel($communityModelClass)->getAsJson('customColumns');

    foreach ($customColumns as $columnName => $columnDefinition) {
      if (!is_array($columnDefinition)) continue;
      $columnClass = 'Hubleto\\Framework\\Db\\Column\\' . ($columnDefinition['class'] ?? '');
      if (!class_exists($columnClass)) continue;

      $columns[$columnName] = (new $columnClass($this, $columnDefinition['title'] ?? ''))
        ->fromArray($columnDefinition)
        ->setProperty('isCustom', true)
        ->setDefaultVisible()
      ;
    }

    return $columns;
  }
}
