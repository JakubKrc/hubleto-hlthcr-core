#!/bin/bash
# Fixes the Hubleto 2 project template (hubleto/install) so that "npm run build" works.
# Run it in the project root, after "composer require" of the Hlthcr apps and before "npm run build":
#   bash vendor/wai-blue/hubleto-hlthcr-core/install/fix-hubleto2-template.sh
# Each fix is skipped when it is not needed (already applied, or fixed upstream), so it can run again.
set -e

installFolder="$(cd "$(dirname "$0")" && pwd)"

if [ ! -f assets.config.js ] || [ ! -f package.json ]; then
  echo "Run this in the root of a Hubleto project." >&2
  exit 1
fi

# 1. The main entry points to a file that does not exist in the template.
if grep -q "'../hubleto/src/assets/src/Main.tsx'" assets.config.js; then
  sed -i "s|'../hubleto/src/assets/src/Main.tsx'|'./vendor/hubleto/assets/src/Main.tsx'|" assets.config.js
  echo "fixed: main entry in assets.config.js"
fi

# 2. The app name regex returns the whole path, so community apps are built into a wrong folder.
appNameRegexLine='const appName = /\\/(.*?)\\/Loader/.exec(app)\[1\];'
if grep -q "$appNameRegexLine" assets.config.js; then
  sed -i "s|$appNameRegexLine|const appName = path.basename(path.dirname(app));|" assets.config.js
  echo "fixed: community app names in assets.config.js"
fi

# 3. react-ui.js is not built (see webpack.config.js next to this script).
if [ ! -f webpack.config.js ]; then
  cp "$installFolder/webpack.config.js" webpack.config.js
  echo "added: webpack.config.js"
fi
if grep -q -- "--config vendor/hubleto/assets/webpack.config.js" package.json; then
  sed -i "s|--config vendor/hubleto/assets/webpack.config.js|--config webpack.config.js|g" package.json
  echo "fixed: build scripts in package.json use webpack.config.js"
fi

# 4. The compiled CSS loads its fonts from assets/fonts, which the template does not create.
if [ ! -d assets/fonts ] && [ -d vendor/hubleto/assets/fonts ]; then
  mkdir -p assets
  cp -r vendor/hubleto/assets/fonts assets/fonts
  echo "added: assets/fonts"
fi

echo "Done. Now: npm install && npm run build"
