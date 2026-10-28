<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Migrations;

use Hubleto\Framework\Migration;

class ClinicHasMedicalStaff_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_clinic_has_medical_staff` (
      `id` int(8) primary key auto_increment,
      `id_clinic` int(8) NULL DEFAULT NULL,
      `id_user` int(8) NULL DEFAULT NULL,
      UNIQUE INDEX `clinic_user` (`id_clinic`, `id_user`),
      INDEX `id_user` (`id_user`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_clinic_has_medical_staff`");
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_clinic_has_medical_staff` ADD CONSTRAINT `fk__hlthcr_clinic_has_medical_staff__id_clinic` FOREIGN KEY (`id_clinic`) REFERENCES `hlthcr_clinics` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
    $this->db->execute("ALTER TABLE `hlthcr_clinic_has_medical_staff` ADD CONSTRAINT `fk__hlthcr_clinic_has_medical_staff__id_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_clinic_has_medical_staff` DROP FOREIGN KEY `fk__hlthcr_clinic_has_medical_staff__id_clinic`");
    $this->db->execute("ALTER TABLE `hlthcr_clinic_has_medical_staff` DROP FOREIGN KEY `fk__hlthcr_clinic_has_medical_staff__id_user`");
  }
}
