<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Controllers;

class ClinicHasMedicalStaff extends \Hubleto\Erp\Controller
{
  public function prepareView(): void
  {
    parent::prepareView();
    $this->setView('@Hubleto:App:External:WaiBlue:HlthcrCore/ClinicHasMedicalStaff.twig');
  }
}
