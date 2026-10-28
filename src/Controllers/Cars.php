<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Controllers;

class Cars extends \Hubleto\Erp\Controller
{
  public function prepareView(): void
  {
    parent::prepareView();
    $this->setView('@Hubleto:App:External:WaiBlue:HlthcrCore/Cars.twig');
  }
}
