<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Migrations;

use Hubleto\Framework\Migration;

class Hazard_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_hazards` (
      `id` int(8) primary key auto_increment,
      `name` varchar(512) NOT NULL,
      `type` int(8) NOT NULL DEFAULT 1,
      `interval_months` int(8) NULL DEFAULT NULL,
      `interval_months_category_1` int(8) NULL DEFAULT NULL,
      `interval_months_category_2` int(8) NULL DEFAULT NULL,
      `interval_months_category_3` int(8) NULL DEFAULT NULL,
      `interval_months_category_4` int(8) NULL DEFAULT NULL,
      INDEX `type` (`type`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_hazards`");
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
