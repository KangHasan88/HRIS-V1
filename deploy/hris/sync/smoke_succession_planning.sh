#!/usr/bin/env bash
set -euo pipefail

cookie=$(mktemp)
base="https://31.97.106.123:8091"

login=$(curl -ksS -c "$cookie" "$base/login")
token=$(printf '%s' "$login" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p')

curl -ksS -b "$cookie" -c "$cookie" \
  -d "_token=$token" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  "$base/login" >/dev/null

page=$(curl -ksS -b "$cookie" "$base/performance/succession")
status=$(curl -ksS -o /dev/null -w '%{http_code}' -b "$cookie" "$base/performance/succession")

printf 'status:%s\n' "$status"
printf 'page:%s\n' "$(printf '%s' "$page" | grep -q 'Succession Planning' && echo ok)"
printf 'plan:%s\n' "$(printf '%s' "$page" | grep -q 'Buat Succession Plan' && echo ok)"
printf 'matrix:%s\n' "$(printf '%s' "$page" | grep -q 'Readiness Matrix' && echo ok)"

rm -f "$cookie"
