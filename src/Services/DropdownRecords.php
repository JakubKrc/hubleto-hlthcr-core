<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

/**
 * Loads the records of a dropdown for the Hlthcr lookup endpoints (models and lookups review C3).
 */
class DropdownRecords extends \Hubleto\Erp\Core
{
  /**
   * Hubleto's lookup query eager-loads every relation of the model, but Hubleto 2's dropdown shows only the lookup
   * text, so the relations are not loaded. The records are sorted by their text, or by the model's $lookupOrderBy.
   */
  public function load(\Hubleto\Framework\Model $model, mixed $query): array
  {
    $query = $query->setEagerLoads([]);

    $orderBy = '_LOOKUP';
    if (property_exists($model, 'lookupOrderBy')) {
      $orderBy = str_replace('{%TABLE%}', $model->table, $model->lookupOrderBy);
    }
    $query = $query->orderByRaw($orderBy);

    $records = $query->get()->toArray();
    return $model->record->prepareLookupData($records);
  }
}
