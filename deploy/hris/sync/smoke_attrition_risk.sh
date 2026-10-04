#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/attrition_login.html /tmp/attrition_after_login.html /tmp/attrition_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/attrition_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/attrition_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/attrition_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" \
    "https://31.97.106.123:8091/analytics/attrition-risk" \
    -o /tmp/attrition_page.html \
    -w "status:%{http_code}\n"

grep -q 'Risiko Attrition' /tmp/attrition_page.html
grep -q 'Faktor Dominan' /tmp/attrition_page.html
grep -q 'Daftar Risiko Karyawan' /tmp/attrition_page.html

echo "page:ok"
