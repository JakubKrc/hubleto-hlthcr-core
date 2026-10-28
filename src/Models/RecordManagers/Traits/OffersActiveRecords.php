<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers\Traits;

/**
 * For record managers whose dropdowns offer only active records (table column is_active).
 * The record manager implements restrictToOfferedRecords(), which the Hlthcr lookup endpoints call.
 */
trait OffersActiveRecords
{
  // Active records, plus the one the form already has (both legacy apps). Not in prepareLookupQuery(),
  // because Hubleto builds the text of every linked record with it, and an inactive one must still show.
  protected function restrictToActiveOrChosen(mixed $query, string $formColumn): mixed
  {
    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    $formRecord = $hubleto->router()->urlParamAsArray('formRecord');
    $idChosenRecord = (int) ($formRecord[$formColumn] ?? 0);
    $table = $this->table;

    return $query->where(function ($offeredRecords) use ($table, $idChosenRecord) {
      $offeredRecords->where($table . '.is_active', 1);
      if ($idChosenRecord > 0) {
        $offeredRecords->orWhere($table . '.id', $idChosenRecord);
      }
    });
  }
}
