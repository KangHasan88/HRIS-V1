#!/usr/bin/env bash
set -euo pipefail

cd /var/www/kurmigo-hris

sudo cp /tmp/AppServiceProvider.php app/Providers/AppServiceProvider.php
sudo cp /tmp/sidebar.blade.php resources/views/layouts/sidebar.blade.php
sudo cp /tmp/apache-hris-8091-ssl.conf /etc/apache2/sites-available/hris-8091-ssl.conf
sudo cp /tmp/apache-hris-8092-ess-ssl.conf /etc/apache2/sites-available/hris-8092-ess-ssl.conf
sudo a2ensite hris-8092-ess-ssl.conf >/dev/null

php8.3 artisan optimize:clear
php8.3 artisan view:cache

if ! ss -ltn | grep -q '127.0.0.1:8093'; then
    sudo bash -lc 'cd /var/www/kurmigo-hris && nohup php8.3 artisan serve --host=127.0.0.1 --port=8093 > storage/logs/artisan-serve-8093.log 2>&1 < /dev/null &'
    sleep 2
fi

if sudo ss -ltnp | grep -q '127.0.0.1:8092'; then
    sudo fuser -k 8092/tcp >/dev/null 2>&1 || true
    sleep 1
fi

sudo apache2ctl configtest
sudo systemctl reload apache2

sudo ss -ltnp | grep -E ':8091|:8092|:8093' || true
