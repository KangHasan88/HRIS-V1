#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/hrdoc_login.html /tmp/hrdoc_after_login.html /tmp/hrdoc_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/hrdoc_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/hrdoc_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/hrdoc_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" \
    "https://31.97.106.123:8091/analytics/hr-document-assistant?question=cuti%20tahunan" \
    -o /tmp/hrdoc_page.html \
    -w "status:%{http_code}\n"

grep -q 'Asisten Dokumen HR' /tmp/hrdoc_page.html
grep -q 'Jawaban' /tmp/hrdoc_page.html
grep -q 'Draft Surat' /tmp/hrdoc_page.html

echo "page:ok"
