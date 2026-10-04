#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/health_login.html /tmp/health_after_login.html /tmp/health_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/health_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/health_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/health_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" \
    "https://31.97.106.123:8091/settings/integration-health" \
    -o /tmp/health_page.html \
    -w "status:%{http_code}\n"

grep -q 'Health Integrasi' /tmp/health_page.html
grep -q 'Queue' /tmp/health_page.html
grep -q 'Health Connector' /tmp/health_page.html

echo "page:ok"
