<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models;

use Hubleto\Framework\Db\Column\Integer;
use Hubleto\Framework\Db\Column\Varchar;

/**
 * An item of the hazard catalogue (both legacy apps call it "položka kategorizácie").
 * The four types are the same in both legacy apps, so they are a fixed list, not a table.
 */
class Hazard extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  const TYPE_FACTOR = 1;
  const TYPE_SPECIAL_REGULATION = 2;
  const TYPE_PROFESSIONAL_ACTIVITY = 3;
  const TYPE_EXAMINATION = 4;

  // Work categories of a factor on a job position; 0 means "not in this category".
  const CATEGORIES = [1, 2, 3, 4];

  public string $table = 'hlthcr_hazards';
  public string $recordManagerClass = RecordManagers\Hazard::class;
  public ?string $lookupSqlValue = '{%TABLE%}.name';
  public ?string $lookupUrlDetail = 'hlthcr/hazards/{%ID%}';

  public function getTypeNames(): array
  {
    return [
      self::TYPE_FACTOR => $this->translate('Work factor'),
      self::TYPE_SPECIAL_REGULATION => $this->translate('Special regulation'),
      self::TYPE_PROFESSIONAL_ACTIVITY => $this->translate('Professional activity'),
      self::TYPE_EXAMINATION => $this->translate('Examination'),
    ];
  }

  // Only work factors are marked with a category 1-4; the other types are yes/no.
  public static function typeUsesCategories(int $type): bool
  {
    return $type == self::TYPE_FACTOR;
  }

  public function describeColumns(): array
  {
    $monthsUnit = $this->translate('months');

    return array_merge(parent::describeColumns(), [
      'name' => (new Varchar($this, $this->translate('Name')))->setRequired()->setDefaultVisible()->setByteSize(512),
      'type' => (new Integer($this, $this->translate('Type')))
        ->setRequired()
        ->setDefaultVisible()
        ->setDefaultValue(self::TYPE_FACTOR)
        ->setEnumValues($this->getTypeNames())
      ,
      // Intervals of the periodic medical exam, used for the next exam date.
      'interval_months' => (new Integer($this, $this->translate('Exam interval without category')))->setDefaultVisible()->setUnit($monthsUnit),
      'interval_months_category_1' => (new Integer($this, $this->translate('Exam interval, category 1')))->setUnit($monthsUnit),
      'interval_months_category_2' => (new Integer($this, $this->translate('Exam interval, category 2')))->setUnit($monthsUnit),
      'interval_months_category_3' => (new Integer($this, $this->translate('Exam interval, category 3')))->setUnit($monthsUnit),
      'interval_months_category_4' => (new Integer($this, $this->translate('Exam interval, category 4')))->setUnit($monthsUnit),
    ]);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add hazard');
    $description->show(['header', 'fulltextSearch', 'columnSearch', 'moreActionsButton']);
    $description->hide(['footer']);
    $description->addFilter('fType', [
      'title' => $this->translate('Type'),
      'type' => 'multipleSelectButtons',
      'options' => $this->getTypeNames(),
    ]);
    return $description;
  }

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    return parent::describeForm();
  }

  /**
   * The exam interval in months of a hazard at a category (0 = without category).
   * Returns 0 when no interval is set.
   */
  public static function intervalMonthsForCategory(array $hazard, int $category): int
  {
    $columnName = 'interval_months';
    if ($category > 0) {
      $columnName = 'interval_months_category_' . $category;
    }
    return (int) ($hazard[$columnName] ?? 0);
  }
}
