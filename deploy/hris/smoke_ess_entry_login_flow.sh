#!/usr/bin/env bash
set -euo pipefail

admin_cookie="$(mktemp)"
ess_cookie="$(mktemp)"
trap 'rm -f "$admin_cookie" "$ess_cookie" /tmp/hris_admin_dashboard.html /tmp/hris_ess_login.html /tmp/hris_ess_after_login.html' EXIT

admin_login="$(curl -k -s -c "$admin_cookie" https://31.97.106.123:8091/login)"
admin_token="$(printf '%s' "$admin_login" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"

curl -k -s -b "$admin_cookie" -c "$admin_cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$admin_token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/hris_admin_dashboard.html \
    -w "admin_login %{http_code} %{redirect_url}\n"

curl -k -s -b "$admin_cookie" https://31.97.106.123:8091/ -o /tmp/hris_admin_dashboard.html
grep -n '31.97.106.123:8092/ess/request-center-entry\|target="_blank"\|Request Center' /tmp/hris_admin_dashboard.html | head -10

curl -k -s -b "$admin_cookie" -c "$ess_cookie" -L https://31.97.106.123:8092/ess/request-center-entry -o /tmp/hris_ess_login.html -w "entry_follow %{http_code} %{url_effective}\n"
grep -o 'Login\|Masuk\|Kurmigo HRIS' /tmp/hris_ess_login.html | head -10

ess_token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/hris_ess_login.html | head -1)"
curl -k -s -b "$ess_cookie" -c "$ess_cookie" \
    -X POST https://31.97.106.123:8092/login \
    -d "_token=$ess_token" \
    -d "email=dimas@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/hris_ess_after_login.html \
    -w "ess_login_post %{http_code} %{redirect_url}\n"

curl -k -s -b "$ess_cookie" https://31.97.106.123:8092/ess/request-center -o /tmp/hris_ess_after_login.html -w "ess_request_get %{http_code}\n"

grep -o 'Request Center ESS\|Ajukan Lembur\|Dimas' /tmp/hris_ess_after_login.html | head -10
