<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

/**
 * For models with StoredFile columns: a saved path can only stay as it is, a new file must be uploaded.
 * Otherwise a crafted save could point a record to another record's file and download it.
 */
trait KeepsStoredFiles
{
  // Call from onBeforeCreate() and onBeforeUpdate(); $originalRecord is null for a new record.
  protected function keepStoredFile(array $record, string $column, mixed $originalRecord): array
  {
    if (!array_key_exists($column, $record)) return $record;

    $value = $record[$column];
    $isNewUpload = is_array($value);
    $isRemoved = $value === null || $value === '';
    if ($isNewUpload || $isRemoved) return $record;

    $originalPath = $originalRecord ? (string) $originalRecord->{$column} : '';
    $record[$column] = $originalPath == '' ? null : $originalPath;
    return $record;
  }
}
