#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/payroll_variance_login.html /tmp/payroll_variance_after_login.html /tmp/payroll_variance_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/payroll_variance_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/payroll_variance_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/payroll_variance_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" "https://31.97.106.123:8091/analytics/payroll-variance-assistant" \
    -o /tmp/payroll_variance_page.html \
    -w "status:%{http_code}\n"

grep -q 'Asisten Varians Payroll' /tmp/payroll_variance_page.html
grep -q 'Penjelasan Varians' /tmp/payroll_variance_page.html
grep -q 'Daftar Anomali Payroll' /tmp/payroll_variance_page.html

echo "page:ok"
