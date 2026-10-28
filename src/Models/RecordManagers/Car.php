<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers;

class Car extends \Hubleto\Erp\RecordManager
{
  public $table = 'hlthcr_cars';

  public function prepareReadQuery(mixed $query = null, int $level = 0, array|null $includeRelations = null): mixed
  {
    return parent::prepareReadQuery($query, $level, $includeRelations);
  }

  public function addUrlFiltersToQuery(mixed $query): mixed
  {
    return parent::addUrlFiltersToQuery($query);
  }

  public function prepareLookupData(array $dataRaw): array
  {
    return parent::prepareLookupData($dataRaw);
  }
}
