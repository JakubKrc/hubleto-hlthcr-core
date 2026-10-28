<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models;

use Hubleto\Framework\Db\Column\Email;
use Hubleto\Framework\Db\Column\Text;
use Hubleto\Framework\Db\Column\Varchar;

class Clinic extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  public string $table = 'hlthcr_clinics';
  public string $recordManagerClass = RecordManagers\Clinic::class;
  public ?string $lookupSqlValue = 'concat({%TABLE%}.name, ifnull(concat(", ", {%TABLE%}.city), ""))';
  public ?string $lookupUrlDetail = 'hlthcr/clinics/{%ID%}';

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'name' => (new Varchar($this, $this->translate('Name')))->setRequired()->setDefaultVisible(),
      // Free text in one legacy app; the other has a list of specialties plus its own text.
      'type' => (new Varchar($this, $this->translate('Type / specialty')))->setDefaultVisible(),
      'street' => (new Varchar($this, $this->translate('Street')))->setDefaultVisible(),
      'city' => (new Varchar($this, $this->translate('City')))->setDefaultVisible(),
      'postal_code' => (new Varchar($this, $this->translate('Postal code')))->setDefaultVisible(),
      'contact_person' => (new Varchar($this, $this->translate('Contact person')))->setDefaultVisible(),
      'phone' => (new Varchar($this, $this->translate('Phone')))->setDefaultVisible(),
      'email' => (new Email($this, $this->translate('E-mail')))->setDefaultVisible(),
      'note' => (new Text($this, $this->translate('Note'))),
    ]);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add clinic');
    $description->show(['header', 'fulltextSearch', 'columnSearch', 'moreActionsButton']);
    $description->hide(['footer']);
    return $description;
  }

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    return parent::describeForm();
  }
}
