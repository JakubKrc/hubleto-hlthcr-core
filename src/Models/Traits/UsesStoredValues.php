<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

/**
 * For models whose save rules read columns that a save may not send.
 */
trait UsesStoredValues
{
  // Hubleto passes only the submitted columns to onBeforeUpdate; the listed columns that are missing get the stored values.
  protected function addStoredValuesOfMissingColumns(array $record, array $columns): array
  {
    $storedRecord = $this->record->where('id', (int) ($record['id'] ?? 0))->first();
    if (!$storedRecord) return $record;

    foreach ($columns as $column) {
      if (!array_key_exists($column, $record)) $record[$column] = $storedRecord->$column;
    }
    return $record;
  }
}
