#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://31.97.106.123:8091}"
COOKIE="/tmp/hris_integr094_cookie.txt"
LOGIN_HTML="/tmp/hris_integr094_login.html"
PAGE_HTML="/tmp/hris_integr094_bio.html"

curl -k -s -c "$COOKIE" "$BASE_URL/login" > "$LOGIN_HTML"
TOKEN="$(grep -o 'name="_token" value="[^"]*' "$LOGIN_HTML" | sed 's/name="_token" value="//')"

curl -k -s -b "$COOKIE" -c "$COOKIE" \
  -X POST "$BASE_URL/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" > /tmp/hris_integr094_post.html

CODE="$(curl -k -s -b "$COOKIE" -o "$PAGE_HTML" -w '%{http_code}' "$BASE_URL/integrations/biometric-connectors")"

test "$CODE" = "200"
grep -q "Biometric Connector" "$PAGE_HTML"
grep -q "Endpoint Agent" "$PAGE_HTML"
grep -q "Perlu Cek" "$PAGE_HTML"
grep -q "Heartbeat" "$PAGE_HTML"

echo "integr094 biometric connector smoke ok ($CODE)"
