# Hlthcr Core

Hubleto app with the shared core of the Hlthcr apps (occupational health service, Slovakia):
- code lists: hazard catalogue with exam intervals per category, clinics with their medical staff, cars;
- the five roles (administrator, manager, hygienist, nurse, medical staff) and what each may see and change;
- shared services of the other Hlthcr apps (access scope, record write guard, translations, protected files, demo data).

Namespace: `Hubleto\App\External\WaiBlue\HlthcrCore`. The other Hlthcr apps need this one.

## Install into a Hubleto project

```bash
composer config repositories.wai-blue/hubleto-hlthcr-core vcs <repository-url>
composer require wai-blue/hubleto-hlthcr-core:dev-main
php hubleto app install "Hubleto\App\External\WaiBlue\HlthcrCore"
npm run build
```

Fake demo data of all installed Hlthcr apps (from the project folder):

```bash
php vendor/wai-blue/hubleto-hlthcr-core/src/Cli/generate-demo-data.php <demo-user-password>
```
