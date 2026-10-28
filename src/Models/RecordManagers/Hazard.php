<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers;

class Hazard extends \Hubleto\Erp\RecordManager
{
  public $table = 'hlthcr_hazards';

  public function prepareReadQuery(mixed $query = null, int $level = 0, array|null $includeRelations = null): mixed
  {
    return parent::prepareReadQuery($query, $level, $includeRelations);
  }

  public function addUrlFiltersToQuery(mixed $query): mixed
  {
    $query = parent::addUrlFiltersToQuery($query);

    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    $filters = $hubleto->router()->urlParamAsArray('filters');

    $selectedTypes = $filters['fType'] ?? [];
    if (is_array($selectedTypes) && count($selectedTypes) > 0) {
      $query = $query->whereIn($this->table . '.type', $selectedTypes);
    }

    return $query;
  }

  public function prepareLookupData(array $dataRaw): array
  {
    return parent::prepareLookupData($dataRaw);
  }
}
