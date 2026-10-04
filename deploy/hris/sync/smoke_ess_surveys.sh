#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/ess_survey_login.html /tmp/ess_survey_after_login.html /tmp/ess_survey_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8092/login -o /tmp/ess_survey_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/ess_survey_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8092/login \
    -d "_token=$token" \
    -d "email=dimas@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/ess_survey_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" https://31.97.106.123:8092/ess/surveys \
    -o /tmp/ess_survey_page.html \
    -w "status:%{http_code}\n"

grep -q 'Survey Saya' /tmp/ess_survey_page.html
grep -q 'Layanan Karyawan' /tmp/ess_survey_page.html
grep -q 'Survey Aktif' /tmp/ess_survey_page.html

echo "page:ok"
