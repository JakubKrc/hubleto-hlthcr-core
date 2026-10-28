<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore;

class Loader extends HlthcrApp
{
  // Apps are constructed before any app is initialised, so overrides registered here apply to all apps.
  public function __construct()
  {
    parent::__construct();
    HubletoOverrides\NoRecordUpdateNotifications::register();
    HubletoOverrides\GuardedRenderer::register();
    HubletoOverrides\GuardedRenderer::addRouteGuard(Services\RecordWriteGuard::class);
    HubletoOverrides\HomePage::register();
    HubletoOverrides\Search::register();
    // Before any app starts, so Hubleto's apps translate their names with the Hlthcr overrides too.
    Services\Translator::register();
  }

  public function init(): void
  {
    Services\Translator::register();
    parent::init();

    header_register_callback([Services\ResponseHeaders::class, 'keepEachCookieOnce']);
    HubletoOverrides\Dashboard::register();

    $this->router()->get([
      '/^hlthcr\/?$/' => Controllers\Home::class,
      '/^hlthcr\/settings\/?$/' => Controllers\Settings::class,
      '/^hlthcr\/api\/customer-lookup\/?$/' => Controllers\Api\CustomerLookup::class,
      '/^hlthcr\/api\/refused\/?$/' => Controllers\Api\Refused::class,
    ]);
    // Replaces Hubleto's lookup endpoint (same route), see HubletoOverrides/RecordLookup.
    $this->router()->get(['/^api\/record\/lookup\/?$/' => HubletoOverrides\RecordLookup::class]);
    $this->router()->crud('hlthcr/hazards', Controllers\Hazards::class);
    $this->router()->crud('hlthcr/clinics', Controllers\Clinics::class);
    $this->router()->crud('hlthcr/clinic-has-medical-staff', Controllers\ClinicHasMedicalStaff::class);
    $this->router()->crud('hlthcr/cars', Controllers\Cars::class);

    /** @var \Hubleto\App\Community\Settings\Loader $settingsApp */
    $settingsApp = $this->appManager()->getApp(\Hubleto\App\Community\Settings\Loader::class);
    $settingsApp->addSetting($this, [
      'title' => $this->translate('Hazards (categorisation items)'),
      'icon' => 'fas fa-triangle-exclamation',
      'url' => 'hlthcr/hazards',
    ]);
    $settingsApp->addSetting($this, [
      'title' => $this->translate('Clinics'),
      'icon' => 'fas fa-house-medical',
      'url' => 'hlthcr/clinics',
    ]);

    // Users who get a role also get the apps and the home dashboard panels of that role.
    $this->eventManager()->addEventListener('onModelAfterCreate', $this->getService(EventListeners\UserRoleChanges::class));
  }

  public function installApp(int $round): void
  {
    // During "php hubleto init" the app is installed before its init() runs.
    Services\Translator::register();

    if ($round == 1) {
      $this->getModel(Models\Hazard::class)->upgradeSchema();
      $this->getModel(Models\Clinic::class)->upgradeSchema();
      $this->getModel(Models\ClinicHasMedicalStaff::class)->upgradeSchema();
      $this->getModel(Models\Car::class)->upgradeSchema();
      // "php hubleto app install" runs only round 1, so foreign keys are created here too (Hubleto skips them later).
      $this->installForeignKeys();

      $this->installHazardCatalogue();

      // Round 1, because "php hubleto app install" runs only round 1. Auth is always installed before custom apps.
      $this->getService(Services\Roles::class)->installRoles();
    }
  }

  private function installHazardCatalogue(): void
  {
    /** @var Models\Hazard */
    $mHazard = $this->getModel(Models\Hazard::class);

    $catalogueAlreadyInstalled = $mHazard->record->count() > 0;
    if ($catalogueAlreadyInstalled) return;

    foreach (Seeds\HazardCatalogue::getHazardNamesByType() as $type => $hazardNames) {
      foreach ($hazardNames as $hazardName) {
        $mHazard->record->recordCreate(['name' => $hazardName, 'type' => $type]);
      }
    }
  }

  public function search(array $expressions): array
  {
    $clinicsQuery = $this->getModel(Models\Clinic::class)->record->prepareReadQuery();

    foreach ($expressions as $expression) {
      $clinicsQuery = $clinicsQuery->where(function ($matchingClinic) use ($expression) {
        $matchingClinic->orWhere('hlthcr_clinics.name', 'like', '%' . $expression . '%');
        $matchingClinic->orWhere('hlthcr_clinics.city', 'like', '%' . $expression . '%');
      });
    }

    $results = [];
    foreach ($clinicsQuery->limit(20)->get()->toArray() as $clinic) {
      $results[] = [
        'id' => $clinic['id'],
        'label' => $clinic['name'],
        'url' => 'hlthcr/clinics/' . $clinic['id'],
        'description' => trim(($clinic['street'] ?? '') . ' ' . ($clinic['city'] ?? '')),
      ];
    }
    return $results;
  }

  public function renderSecondSidebar(): string
  {
    return '
      ' . $this->secondSidebarTitle() . '
      <div class="app-sidebar-buttons">
        ' . $this->secondSidebarButton('hlthcr/hazards', 'fas fa-triangle-exclamation', $this->translate('Hazards')) . '
        ' . $this->secondSidebarButton('hlthcr/clinics', 'fas fa-house-medical', $this->translate('Clinics')) . '
        ' . $this->secondSidebarButton('hlthcr/cars', 'fas fa-car', $this->translate('Cars')) . '
        ' . $this->secondSidebarButton('hlthcr/settings', 'fas fa-cog', $this->translate('Settings')) . '
      </div>
    ';
  }

  public function renderAlerts(): string
  {
    return '';
  }

  public function getSidebarBadgeNumber(): int
  {
    return 0;
  }

  public function generateHlthcrDemoData(): void
  {
    $this->setDemoHazardIntervals();
    $demoUserIds = $this->getService(Services\DemoData::class)->ensureDemoUsers();
    $this->getService(Services\HomeDashboard::class)->ensureForAllUsers();

    /** @var Models\Clinic */
    $mClinic = $this->getModel(Models\Clinic::class);
    $demoClinics = [
      ['name' => 'Ambulancia pracovného lekárstva', 'city' => 'Bratislava', 'street' => 'Nemocničná 1'],
      ['name' => 'Ambulancia všeobecného lekára', 'city' => 'Trnava', 'street' => 'Zdravotnícka 5'],
      ['name' => 'Audiometrické pracovisko', 'city' => 'Nitra', 'street' => 'Lekárska 12'],
    ];
    foreach ($demoClinics as $demoClinic) {
      $clinicExists = $mClinic->record->where('name', $demoClinic['name'])->exists();
      if ($clinicExists) continue;
      $mClinic->record->recordCreate(array_merge($demoClinic, ['note' => $this->translate('Demo clinic')]));
    }

    // The demo medical staff user works in the first demo clinic.
    $firstDemoClinic = $mClinic->record->where('name', $demoClinics[0]['name'])->first();
    $this->getModel(Models\ClinicHasMedicalStaff::class)->record->firstOrCreate([
      'id_clinic' => $firstDemoClinic->id,
      'id_user' => $demoUserIds['medical-staff'],
    ]);

    /** @var Models\Car */
    $mCar = $this->getModel(Models\Car::class);
    $demoCars = [
      ['name' => 'Škoda Octavia', 'license_plate' => 'BA000AA'],
      ['name' => 'Hyundai i20', 'license_plate' => 'BA000BB'],
    ];
    foreach ($demoCars as $demoCar) {
      $carExists = $mCar->record->where('license_plate', $demoCar['license_plate'])->exists();
      if ($carExists) continue;
      $mCar->record->recordCreate($demoCar);
    }
  }

  /**
   * Illustrative exam intervals (months) of a few hazards, only for demo data; real intervals are set by the administrator.
   * Values by category: [without category, 1, 2, 3, 4].
   */
  private function setDemoHazardIntervals(): void
  {
    $demoIntervals = [
      'Hluk' => [24, 48, 36, 24, 12],
      'Fyzická záťaž' => [24, 48, 36, 24, 12],
      'Chemické faktory' => [24, 48, 36, 24, 12],
      'Audiometria' => [24, null, null, null, null],
      'EKG' => [24, null, null, null, null],
    ];

    /** @var Models\Hazard */
    $mHazard = $this->getModel(Models\Hazard::class);
    foreach ($demoIntervals as $hazardName => $intervals) {
      $hazard = $mHazard->record->where('name', $hazardName)->first();
      $intervalsAlreadySet = $hazard && !empty($hazard->interval_months);
      if (!$hazard || $intervalsAlreadySet) continue;

      $hazard->update([
        'interval_months' => $intervals[0],
        'interval_months_category_1' => $intervals[1],
        'interval_months_category_2' => $intervals[2],
        'interval_months_category_3' => $intervals[3],
        'interval_months_category_4' => $intervals[4],
      ]);
    }
  }
}
