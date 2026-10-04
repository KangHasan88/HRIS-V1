#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/hris_admin_dashboard.html' EXIT

login_html="$(curl -k -s -c "$cookie" https://31.97.106.123:8091/login)"
token="$(printf '%s' "$login_html" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/hris_login_after.html \
    -w "login_post %{http_code} %{redirect_url}\n"

curl -k -s -b "$cookie" https://31.97.106.123:8091/ -o /tmp/hris_admin_dashboard.html -w "dashboard_get %{http_code}\n"

wc -c /tmp/hris_admin_dashboard.html
grep -n '<title>\|HRIS Dashboard\|Request Center\|8092' /tmp/hris_admin_dashboard.html | head -20 || true
grep -n '31.97.106.123:8092/ess/request-center\|Request Center\|target="_blank"' /tmp/hris_admin_dashboard.html | head -10
