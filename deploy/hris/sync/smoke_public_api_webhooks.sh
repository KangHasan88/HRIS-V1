#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/api_login.html /tmp/api_after_login.html /tmp/api_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/api_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/api_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/api_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" \
    "https://31.97.106.123:8091/integrations/public-api-webhooks" \
    -o /tmp/api_page.html \
    -w "status:%{http_code}\n"

grep -q 'Public API' /tmp/api_page.html
grep -q 'Endpoint Catalog' /tmp/api_page.html
grep -q 'Webhook Event' /tmp/api_page.html

echo "page:ok"
