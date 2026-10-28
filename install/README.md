# Installing the Hlthcr CRM (Hubleto 2)

Needs PHP 8.2 or newer, composer, node and npm, a MySQL or MariaDB database, and read access to the Hlthcr
repositories on GitHub (an SSH key in your GitHub account, or a git login over HTTPS).

Everything Hlthcr needs is in one file, `hlthcr.example.yaml` in this folder: the Hlthcr packages for composer,
and the apps and settings for `php hubleto init`.

1. Create a Hubleto project:

   ```bash
   composer create-project hubleto/install my-project --stability=dev
   cd my-project
   ```

2. Put `hlthcr.example.yaml` (from this folder on GitHub, or from a clone of this repository) into the project root
   as `hlthcr.yaml`, and fill in the URL, database and admin values.

3. Add the Hlthcr packages and install:

   ```bash
   php hubleto prepare-composer hlthcr.yaml   # adds the 8 Hlthcr packages to composer.json
   composer update
   php hubleto init hlthcr.yaml               # drops and recreates the database named in the yaml
   ```

   Run `prepare-composer` only once per project: a second run doubles the entries and breaks `composer.json`.

4. Fix the Hubleto 2 project template, then build the frontend:

   ```bash
   bash vendor/wai-blue/hubleto-hlthcr-core/install/fix-hubleto2-template.sh
   npm install && npm run build
   ```

   Without the fix the build fails (wrong path to `Main.tsx`), no app page works (`react-ui.js` not built) and fonts are missing.
   The script skips what is already fixed, so it is safe to run again.

5. Point the web server to the project folder (the template's `.htaccess` sends every route to `index.php`).
   Under Apache, the web server user needs write access to `log`, `upload` and `secure`.

6. Optional: hide the Hubleto apps you do not need (for example Tasks, Worksheets) in Settings > Sidebar (order 0).

7. Optional, FAKE demo data only: `php vendor/wai-blue/hubleto-hlthcr-core/src/Cli/generate-demo-data.php <demo-user-password>`.

Updates later: `composer update "wai-blue/*"`, then `npm run build`.

## Developing the apps

As in the developer guide ("External apps"): clone the apps into any folder and let composer link them into `vendor/`,
so changes show at once. Before step 3, change each repository in the yaml's `composer:` part from `vcs` to `path`:

```bash
mkdir -p /var/www/html/hlthcr-apps && cd /var/www/html/hlthcr-apps
for app in core clients branches employees medical-exams audits documents events; do
  git clone git@github.com:JakubKrc/hubleto-hlthcr-$app.git
done
```

```yaml
composer:
  repositories:
    wai-blue/hubleto-hlthcr-core: { type: path, url: /var/www/html/hlthcr-apps/hubleto-hlthcr-core }
    # ... the same for the other 7
```

Commit and push in the cloned folders. Files copied into `src/apps` do not work: that folder is for custom apps,
and Hubleto finds external apps only as composer packages.

## Without prepare-composer

The same as step 3, by hand:

```bash
for app in core clients branches employees medical-exams audits documents events; do
  composer config repositories.wai-blue/hubleto-hlthcr-$app \
    "{\"type\": \"vcs\", \"url\": \"https://github.com/JakubKrc/hubleto-hlthcr-$app\", \"no-api\": true}"
done
composer require wai-blue/hubleto-hlthcr-core:dev-main wai-blue/hubleto-hlthcr-clients:dev-main \
  wai-blue/hubleto-hlthcr-branches:dev-main wai-blue/hubleto-hlthcr-employees:dev-main \
  wai-blue/hubleto-hlthcr-medical-exams:dev-main wai-blue/hubleto-hlthcr-audits:dev-main \
  wai-blue/hubleto-hlthcr-documents:dev-main wai-blue/hubleto-hlthcr-events:dev-main
php hubleto init hlthcr.yaml
```

`no-api` makes composer clone with plain git, so it uses your git login and needs no GitHub token of its own.
