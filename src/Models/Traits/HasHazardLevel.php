<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;
use Hubleto\Framework\Exceptions\RecordSaveException;

/**
 * For models that store the level of one catalogue hazard (columns id_hazard and level).
 */
trait HasHazardLevel
{
  // Texts of the stored level 1-4; for the yes/no hazard types level 1 means "yes".
  protected function getLevelTexts(): array
  {
    return [
      1 => $this->translate('Category 1 / yes'),
      2 => $this->translate('Category 2'),
      3 => $this->translate('Category 3'),
      4 => $this->translate('Category 4'),
    ];
  }

  // SQL of the list column: "Category n" for work factors, "Yes" for the other hazard types.
  protected function getLevelTextSql(): string
  {
    $categoryCases = '';
    $categoryTexts = [
      1 => $this->translate('Category 1'),
      2 => $this->translate('Category 2'),
      3 => $this->translate('Category 3'),
      4 => $this->translate('Category 4'),
    ];
    foreach ($categoryTexts as $category => $categoryText) {
      $categoryCases .= ' when ' . $category . ' then ' . $this->quoteSqlText($categoryText);
    }
    $yesText = $this->quoteSqlText($this->translate('Yes'));

    return 'select case when hlthcr_hazards.type = ' . Hazard::TYPE_FACTOR
      . ' then (case ' . $this->table . '.level' . $categoryCases . ' end)'
      . ' else ' . $yesText . ' end'
      . ' from hlthcr_hazards where hlthcr_hazards.id = ' . $this->table . '.id_hazard'
    ;
  }

  private function quoteSqlText(string $text): string
  {
    return "'" . str_replace(["\\", "'"], ["\\\\", "''"], $text) . "'";
  }

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
