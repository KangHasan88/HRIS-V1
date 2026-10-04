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

page=$(curl -ksS -b "$cookie" "$base/external-workforce/compliance")
status=$(curl -ksS -o /dev/null -w '%{http_code}' -b "$cookie" "$base/external-workforce/compliance")

printf 'status:%s\n' "$status"
printf 'page:%s\n' "$(printf '%s' "$page" | grep -q 'Compliance Outsourcing' && echo ok)"
printf 'form:%s\n' "$(printf '%s' "$page" | grep -q 'Tambah Item Compliance' && echo ok)"
printf 'register:%s\n' "$(printf '%s' "$page" | grep -q 'Register Compliance Vendor' && echo ok)"

rm -f "$cookie"
