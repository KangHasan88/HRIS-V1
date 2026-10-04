#!/usr/bin/env bash
set -euo pipefail

base="https://31.97.106.123:8091"
cookie="/tmp/hris_identity_provider_cookie.txt"
login="/tmp/hris_identity_provider_login.html"
page="/tmp/hris_identity_provider_page.html"

curl -ksS -c "$cookie" "$base/login" > "$login"
token=$(grep -oP 'name="_token" value="\K[^"]+' "$login" | head -1)

curl -ksS -b "$cookie" -c "$cookie" -o /tmp/hris_identity_provider_post.html -w "login:%{http_code}\n" \
  -X POST "$base/login" \
  -d "_token=$token" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  -d "remember=1"

curl -ksS -L -b "$cookie" -c "$cookie" -o "$page" -w "status:%{http_code}\n" "$base/integrations/identity-providers"
grep -q "SSO & Identity Provider" "$page"
grep -q "Tambah Identity Provider" "$page"
grep -q "Claim Mapping" "$page"
grep -q "Auto Provision" "$page"
echo "page:ok"
