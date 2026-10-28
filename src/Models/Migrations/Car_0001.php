<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Migrations;

use Hubleto\Framework\Migration;

class Car_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_cars` (
      `id` int(8) primary key auto_increment,
      `name` varchar(255) NOT NULL,
      `license_plate` varchar(255) NOT NULL,
      UNIQUE INDEX `license_plate` (`license_plate`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_cars`");
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
