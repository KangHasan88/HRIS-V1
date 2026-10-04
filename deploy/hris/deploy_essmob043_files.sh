#!/usr/bin/env bash
set -euo pipefail

mkdir -p /var/www/kurmigo-hris/resources/views/ess
ts="$(date +%Y%m%d-%H%M%S)"

for f in \
  /var/www/kurmigo-hris/routes/web.php \
  /var/www/kurmigo-hris/resources/views/layouts/sidebar.blade.php \
  /var/www/kurmigo-hris/resources/views/hris/ess-home.blade.php \
  /var/www/kurmigo-hris/tests/Feature/ExampleTest.php
do
  if [ -f "$f" ]; then
    cp "$f" "$f.bak-essmob043-$ts"
  fi
done

cp /tmp/ess-profile-updates.blade.php /var/www/kurmigo-hris/resources/views/ess/profile-updates.blade.php
cp /tmp/hris-web.php /var/www/kurmigo-hris/routes/web.php
cp /tmp/hris-sidebar.blade.php /var/www/kurmigo-hris/resources/views/layouts/sidebar.blade.php
cp /tmp/hris-ess-home.blade.php /var/www/kurmigo-hris/resources/views/hris/ess-home.blade.php
cp /tmp/hris-ExampleTest.php /var/www/kurmigo-hris/tests/Feature/ExampleTest.php

chown -R codex:www-data \
  /var/www/kurmigo-hris/resources/views/ess \
  /var/www/kurmigo-hris/routes/web.php \
  /var/www/kurmigo-hris/resources/views/layouts/sidebar.blade.php \
  /var/www/kurmigo-hris/resources/views/hris/ess-home.blade.php \
  /var/www/kurmigo-hris/tests/Feature/ExampleTest.php
