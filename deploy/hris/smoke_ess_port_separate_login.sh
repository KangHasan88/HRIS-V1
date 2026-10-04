#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/hris_8091_login.html /tmp/hris_8092_body.html' EXIT

login_html="$(curl -k -s -c "$cookie" https://31.97.106.123:8091/login)"
token="$(printf '%s' "$login_html" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/hris_8091_login.html \
    -w "admin_8091_login %{http_code} %{redirect_url}\n"

curl -k -s -b "$cookie" -I https://31.97.106.123:8092/ess/request-center --max-time 10 | head -10
curl -k -s -b "$cookie" https://31.97.106.123:8092/login --max-time 10 -o /tmp/hris_8092_body.html -w "ess_8092_login_page %{http_code}\n"
grep -o 'Kurmigo HRIS\|Masuk\|Login\|email' /tmp/hris_8092_body.html | head -10
