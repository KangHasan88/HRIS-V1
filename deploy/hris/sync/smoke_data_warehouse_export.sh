#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/dw_login.html /tmp/dw_after_login.html /tmp/dw_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/dw_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/dw_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/dw_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" \
    "https://31.97.106.123:8091/integrations/data-warehouse-export?period=2026-07&format=csv" \
    -o /tmp/dw_page.html \
    -w "status:%{http_code}\n"

grep -q 'Data Warehouse Export' /tmp/dw_page.html
grep -q 'Dataset Catalog' /tmp/dw_page.html
grep -q 'Snapshot Plan' /tmp/dw_page.html

echo "page:ok"
