<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Migrations;

use Hubleto\Framework\Migration;

class Clinic_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_clinics` (
      `id` int(8) primary key auto_increment,
      `name` varchar(255) NOT NULL,
      `street` varchar(255) NULL DEFAULT NULL,
      `city` varchar(255) NULL DEFAULT NULL,
      `postal_code` varchar(255) NULL DEFAULT NULL,
      `contact_person` varchar(255) NULL DEFAULT NULL,
      `phone` varchar(255) NULL DEFAULT NULL,
      `email` varchar(255) NULL DEFAULT NULL,
      `note` text NULL DEFAULT NULL
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_clinics`");
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
