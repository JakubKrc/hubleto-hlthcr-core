<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\OffersOnlyAllowedChanges;

/**
 * Base of the Hlthcr models. Forms and lists offer adding, editing and deleting only when the user may change
 * the model, e.g. medical staff see clients and branches read-only.
 */
abstract class HlthcrModel extends \Hubleto\Erp\Model
{
  use OffersOnlyAllowedChanges;

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    $description = parent::describeForm();
    return $this->offerOnlyAllowedChanges($description);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    return $this->offerOnlyAllowedChanges($description);
  }
}
