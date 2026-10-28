<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models;

use Hubleto\App\Community\Auth\Models\User;
use Hubleto\Framework\Db\Column\Lookup;

/**
 * A medical staff user working in a clinic: they see and plan only this clinic's exams.
 * The clinic itself has no user field (legacy: the clinic's `id_user`).
 */
class ClinicHasMedicalStaff extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  public string $table = 'hlthcr_clinic_has_medical_staff';
  public string $recordManagerClass = RecordManagers\ClinicHasMedicalStaff::class;
  public ?string $lookupSqlValue = '(select concat(first_name, " ", last_name) from users where users.id = {%TABLE%}.id_user)';
  public ?string $lookupUrlDetail = 'hlthcr/clinic-has-medical-staff/{%ID%}';

  public array $relations = [
    'CLINIC' => [ self::BELONGS_TO, Clinic::class, 'id_clinic', 'id' ],
    'USER' => [ self::BELONGS_TO, User::class, 'id_user', 'id' ],
  ];

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'id_clinic' => (new Lookup($this, $this->translate('Clinic'), Clinic::class, 'CASCADE'))->setRequired()->setDefaultVisible(),
      'id_user' => (new Lookup($this, $this->translate('Medical staff user'), User::class, 'CASCADE'))
        ->setReactComponent('InputUserSelect')
        ->setRequired()
        ->setDefaultVisible()
      ,
    ]);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add medical staff user');
    $description->show(['header', 'fulltextSearch', 'columnSearch', 'moreActionsButton']);
    $description->hide(['footer']);
    return $description;
  }

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    return parent::describeForm();
  }

  public function getRelationsIncludedInLoadTableData(): array|null
  {
    return ['CLINIC', 'USER'];
  }

  public function getMaxReadLevelForLoadTableData(): int
  {
    return 1;
  }
}
