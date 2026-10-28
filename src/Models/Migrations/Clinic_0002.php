<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Migrations;

use Hubleto\Framework\Migration;

// Clinic type or specialty, which both legacy apps have (models and lookups review B7).
class Clinic_0002 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_clinics` ADD COLUMN `type` varchar(255) NULL DEFAULT NULL AFTER `name`");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_clinics` DROP COLUMN `type`");
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
