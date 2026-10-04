#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/hrreport_login.html /tmp/hrreport_after_login.html /tmp/hrreport_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/hrreport_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/hrreport_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/hrreport_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" \
    "https://31.97.106.123:8091/analytics/hr-report-builder?prompt=Buat%20ringkasan%20payroll%20dan%20absensi" \
    -o /tmp/hrreport_page.html \
    -w "status:%{http_code}\n"

grep -q 'Builder Laporan HR' /tmp/hrreport_page.html
grep -q 'Prompt Laporan' /tmp/hrreport_page.html
grep -q 'Metrik Pendukung' /tmp/hrreport_page.html

echo "page:ok"
