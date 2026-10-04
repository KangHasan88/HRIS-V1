#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/people_analytics_login.html /tmp/people_analytics_after_login.html /tmp/people_analytics_page.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8091/login -o /tmp/people_analytics_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/people_analytics_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/people_analytics_after_login.html \
    -w "login:%{http_code}\n"

curl -k -s -L -b "$cookie" "https://31.97.106.123:8091/analytics/people-dashboard?start_date=2026-01-01&end_date=2026-07-20" \
    -o /tmp/people_analytics_page.html \
    -w "status:%{http_code}\n"

grep -q 'People Analytics' /tmp/people_analytics_page.html
grep -q 'Headcount Aktif' /tmp/people_analytics_page.html
grep -q 'Insight HR' /tmp/people_analytics_page.html

echo "page:ok"
