<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

use Hubleto\App\Community\Auth\Models\User;
use Hubleto\App\Community\Auth\Models\UserHasRole;

/**
 * Shared FAKE demo data (users per role, client companies) for all Hlthcr apps.
 * Never put real client data here; the repo and test servers use only this.
 */
class DemoData extends \Hubleto\Erp\Core
{
  // Set by Cli/generate-demo-data.php. There is no default, so a public server never gets a known password.
  const DEMO_PASSWORD_CONFIG_PATH = 'hlthcrDemoPassword';

  public function getDemoUserDefinitions(): array
  {
    return [
      'hygienist-1' => [
        'first_name' => 'Hana',
        'last_name' => 'Hygienička',
        'email' => 'hygienik1@example.com',
        'type' => User::TYPE_EMPLOYEE,
        'role' => Roles::HYGIENIST,
      ],
      'hygienist-2' => [
        'first_name' => 'Hugo',
        'last_name' => 'Hygienik',
        'email' => 'hygienik2@example.com',
        'type' => User::TYPE_EMPLOYEE,
        'role' => Roles::HYGIENIST,
      ],
      'nurse' => [
        'first_name' => 'Sára',
        'last_name' => 'Sestrička',
        'email' => 'sestra@example.com',
        'type' => User::TYPE_NOT_SPECIFIED,
        'role' => Roles::NURSE,
      ],
      'medical-staff' => [
        'first_name' => 'Lukáš',
        'last_name' => 'Lekár',
        'email' => 'lekar@example.com',
        'type' => User::TYPE_NOT_SPECIFIED,
        'role' => Roles::MEDICAL_STAFF,
      ],
      'manager' => [
        'first_name' => 'Marta',
        'last_name' => 'Manažérka',
        'email' => 'manazer@example.com',
        'type' => User::TYPE_NOT_SPECIFIED,
        'role' => Roles::MANAGER,
      ],
    ];
  }

  public function getDemoPassword(): string
  {
    $demoPassword = $this->config()->getAsString(self::DEMO_PASSWORD_CONFIG_PATH);
    if ($demoPassword == '') {
      throw new \Exception('Password for demo users is not set. Run Cli/generate-demo-data.php <password>.');
    }
    return $demoPassword;
  }

  /**
   * Creates the demo users (if missing) and returns [demo user key => user id].
   */
  public function ensureDemoUsers(): array
  {
    /** @var User */
    $mUser = $this->getModel(User::class);
    /** @var UserHasRole */
    $mUserHasRole = $this->getModel(UserHasRole::class);
    /** @var Roles */
    $roles = $this->getService(Roles::class);

    $userIds = [];
    foreach ($this->getDemoUserDefinitions() as $userKey => $userDefinition) {
      $existingUser = $mUser->record->where('email', $userDefinition['email'])->first();
      if ($existingUser) {
        // Keeps the password; only updates the apps the role may open (e.g. apps added since then).
        $this->getService(RoleApps::class)->grantAppsOfRoles((int) $existingUser->id, true);
        $userIds[$userKey] = (int) $existingUser->id;
        continue;
      }

      $createdUser = $mUser->record->recordCreate([
        'type' => $userDefinition['type'],
        'first_name' => $userDefinition['first_name'],
        'last_name' => $userDefinition['last_name'],
        'nick' => $userDefinition['first_name'],
        'email' => $userDefinition['email'],
        'login' => $userDefinition['email'],
        'password' => password_hash($this->getDemoPassword(), PASSWORD_DEFAULT),
        'is_active' => true,
        'id_default_company' => 1,
        'language' => 'sk',
      ]);
      $userIds[$userKey] = (int) $createdUser['id'];

      $mUserHasRole->record->recordCreate([
        'id_user' => $createdUser['id'],
        'id_role' => $roles->getRoleId($userDefinition['role']),
      ]);
      $this->getService(RoleApps::class)->grantAppsOfRoles((int) $createdUser['id'], true);
    }

    return $userIds;
  }

  /**
   * Fictional client companies. Company ids (IČO) 99000001+ are not real.
   * client_type is the name of a seeded client type (HlthcrClients).
   */
  public function getDemoClients(array $demoUserIds): array
  {
    return [
      [
        'name' => 'Demo Stavby s.r.o.',
        'company_id' => '99000001',
        'tax_id' => '2099000001',
        'vat_id' => 'SK2099000001',
        'street' => 'Priemyselná 10',
        'city' => 'Trnava',
        'postal_code' => '917 01',
        'id_hygienist' => $demoUserIds['hygienist-1'],
        'client_type' => 'Paušálny dohľad',
        'employee_count' => 120,
      ],
      [
        'name' => 'Demo Stavby Servis s.r.o.',
        'company_id' => '99000002',
        'tax_id' => '2099000002',
        'vat_id' => 'SK2099000002',
        'street' => 'Priemyselná 12',
        'city' => 'Trnava',
        'postal_code' => '917 01',
        'id_hygienist' => $demoUserIds['hygienist-1'],
        'client_type' => 'Paušálny dohľad',
        'employee_count' => 30,
      ],
      [
        'name' => 'Demo Logistika a.s.',
        'company_id' => '99000003',
        'tax_id' => '2099000003',
        'vat_id' => 'SK2099000003',
        'street' => 'Skladová 3',
        'city' => 'Nitra',
        'postal_code' => '949 01',
        'id_hygienist' => $demoUserIds['hygienist-2'],
        'client_type' => 'Audit/reaudit',
        'employee_count' => 80,
      ],
      [
        'name' => 'Demo Kancelárie s.r.o.',
        'company_id' => '99000004',
        'tax_id' => '2099000004',
        'vat_id' => 'SK2099000004',
        'street' => 'Hlavná 1',
        'city' => 'Bratislava',
        'postal_code' => '811 01',
        'id_hygienist' => $demoUserIds['hygienist-2'],
        'client_type' => 'Paušálny dohľad',
        'employee_count' => 15,
      ],
    ];
  }

  /**
   * Company ids (IČO) of the demo clients; other apps use them to find demo customers.
   */
  public function getDemoCompanyIds(): array
  {
    $demoCompanyIds = [];
    foreach ($this->getDemoClients($this->ensureDemoUsers()) as $demoClient) {
      $demoCompanyIds[] = $demoClient['company_id'];
    }
    return $demoCompanyIds;
  }
}
