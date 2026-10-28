<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers\Traits;

/**
 * For record managers whose model describes a default order of its list (describeTable()->ui['orderBy']).
 */
trait DefaultOrder
{
  // Hubleto's table first asks for "id desc" (before it knows the description); the id is not a list column,
  // so then the list uses the order its model describes.
  public function addOrderByToQuery(mixed $query, array $orderBy): mixed
  {
    $field = $orderBy['field'] ?? '';
    $hasRequestedOrder = $field != '' && $field != 'id' && !empty($orderBy['direction']);
    if (!$hasRequestedOrder) {
      $orderBy = $this->model->describeTable()->ui['orderBy'] ?? [];
    }
    return parent::addOrderByToQuery($query, $orderBy);
  }
}
