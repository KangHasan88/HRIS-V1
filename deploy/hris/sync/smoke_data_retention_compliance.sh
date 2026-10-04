#!/usr/bin/env bash
set -euo pipefail

base="https://31.97.106.123:8091"
cookie="$(mktemp)"
trap 'rm -f "$cookie"' EXIT

login=$(curl -ksS -c "$cookie" "$base/login")
token=$(printf '%s' "$login" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)

curl -ksS -b "$cookie" -c "$cookie" -o /dev/null -w 'login:%{http_code}\n' \
  -d "_token=$token&email=admin@kurmigo.id&password=password123" \
  "$base/login"

page=$(curl -ksS -b "$cookie" "$base/settings/data-retention-compliance")
status=$(curl -ksS -o /dev/null -w '%{http_code}' -b "$cookie" "$base/settings/data-retention-compliance")

echo "status:$status"
printf '%s' "$page" | grep -q 'Retensi & Legal Hold'
printf '%s' "$page" | grep -q 'Buat Policy Retensi'
printf '%s' "$page" | grep -q 'Buat Legal Hold'
printf '%s' "$page" | grep -q 'Catat Execution'
echo "page:ok"
