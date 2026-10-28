<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

use Hubleto\Framework\Db\Column\Date;

/**
 * Hubleto stores an empty date string as today's date (Date::normalize() calls new DateTime('')).
 * Models with optional dates call this before saving, so an empty date stays empty.
 */
trait EmptyDatesAsNull
{
  protected function emptyDatesAsNull(array $record): array
  {
    foreach ($this->getColumns() as $columnName => $column) {
      $isEmptyDate = $column instanceof Date && array_key_exists($columnName, $record) && $record[$columnName] === '';
      if ($isEmptyDate) {
        $record[$columnName] = null;
      }
    }
    return $record;
  }
}
