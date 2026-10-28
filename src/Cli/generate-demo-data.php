<?php

// Usage (from the project folder): php vendor/wai-blue/hubleto-hlthcr-core/src/Cli/generate-demo-data.php <demo-user-password>
// Adds FAKE demo data of all installed Hlthcr apps. Safe to run repeatedly (existing demo users keep their password).

$demoUserPassword = $argv[1] ?? '';
if ($demoUserPassword == '') {
  fwrite(STDERR, "Usage: php vendor/wai-blue/hubleto-hlthcr-core/src/Cli/generate-demo-data.php <demo-user-password>\n");
  exit(1);
}

// The app is a composer package, so the project is the folder the script is started from.
$projectFolder = getcwd();
if (!is_file($projectFolder . '/boot.php')) {
  fwrite(STDERR, "Run this from the Hubleto project folder.\n");
  exit(1);
}
require_once($projectFolder . '/boot.php');

$hubleto->config()->set(\Hubleto\App\External\WaiBlue\HlthcrCore\Services\DemoData::DEMO_PASSWORD_CONFIG_PATH, $demoUserPassword);

// Order matters: later apps use records created by earlier ones.
$hlthcrApps = [
  'Hubleto\App\External\WaiBlue\HlthcrCore',
  'Hubleto\App\External\WaiBlue\HlthcrClients',
  'Hubleto\App\External\WaiBlue\HlthcrBranches',
  'Hubleto\App\External\WaiBlue\HlthcrEmployees',
  'Hubleto\App\External\WaiBlue\HlthcrAudits',
  'Hubleto\App\External\WaiBlue\HlthcrDocuments',
  'Hubleto\App\External\WaiBlue\HlthcrEvents',
  'Hubleto\App\External\WaiBlue\HlthcrMedicalExams',
];

foreach ($hlthcrApps as $appNamespace) {
  $app = $hubleto->appManager()->getApp($appNamespace);
  if ($app === null) {
    echo "skipped (not installed): {$appNamespace}\n";
    continue;
  }
  $app->generateDemoData();
  echo "demo data generated: {$appNamespace}\n";
}
