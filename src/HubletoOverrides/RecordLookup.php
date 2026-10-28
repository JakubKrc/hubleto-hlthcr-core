<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\HubletoOverrides;

/**
 * Hubleto's record lookup without its 5-minute cache. That cache is shared by all users,
 * but Hlthcr lookups offer each user only the records the user may see.
 */
class RecordLookup extends \Hubleto\Framework\Controllers\Api\Record\Lookup
{
  public function response(): array
  {
    $search = $this->router()->urlParamAsString('search');
    $query = $this->model->record->prepareLookupQuery($search);

    // Some dropdowns offer fewer records than they can show (for example only active ones).
    if (method_exists($this->model->record, 'restrictToOfferedRecords')) {
      $query = $this->model->record->restrictToOfferedRecords($query);
    }

    $records = $query->get()->toArray();
    return $this->model->record->prepareLookupData($records);
  }
}
