#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/hris_login_after.html' EXIT

login_html="$(curl -k -s -c "$cookie" https://31.97.106.123:8091/login)"
token="$(printf '%s' "$login_html" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"

echo "token_len ${#token}"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8091/login \
    -d "_token=$token" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/hris_login_after.html \
    -w "login %{http_code} %{redirect_url}\n"

curl -k -s -b "$cookie" -I https://31.97.106.123:8091/ess/request-center --max-time 10 | head -14

curl -k -s -b "$cookie" https://31.97.106.123:8091/ess/request-center --max-time 10 \
    | grep -o 'User login belum terhubung\|Request Center ESS\|Ajukan Lembur' \
    | head -10
