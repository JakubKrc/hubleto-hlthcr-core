<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;
use Hubleto\Framework\Exceptions\RecordSaveException;

/**
 * For models that store the level of one catalogue hazard (columns id_hazard and level).
 */
trait HasHazardLevel
{
  // A row means the hazard is present: a work factor with category 1-4, the other types with "yes" (1).
  protected function refuseInvalidHazardLevel(array $record): void
  {
    $idHazard = (int) ($record['id_hazard'] ?? 0);
    $level = (int) ($record['level'] ?? 0);

    // A save may send only some columns; the others keep their stored values.
    $storedRecord = $this->record->where('id', (int) ($record['id'] ?? 0))->first();
    if ($storedRecord && !array_key_exists('id_hazard', $record)) $idHazard = (int) $storedRecord->id_hazard;
    if ($storedRecord && !array_key_exists('level', $record)) $level = (int) $storedRecord->level;

    $hazard = $this->getModel(Hazard::class)->record->where('id', $idHazard)->first();
    if (!$hazard) return;

    if (!Hazard::isValidLevel((int) $hazard->type, $level)) {
      throw new RecordSaveException($this->translate('A work factor needs a category 1 to 4, other hazards only "yes" (1).'));
    }
  }
}
