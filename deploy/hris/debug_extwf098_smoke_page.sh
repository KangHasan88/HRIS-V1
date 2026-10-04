#!/usr/bin/env bash
set -euo pipefail

COOKIE="/tmp/hris_extwf098_cookie.txt"
LOGIN_PAGE="/tmp/hris_extwf098_login.html"
PAGE="/tmp/hris_extwf098_attendance.html"

curl -k -sS -c "$COOKIE" https://31.97.106.123:8091/login -o "$LOGIN_PAGE"
TOKEN="$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_PAGE" | head -1)"
curl -k -sS -b "$COOKIE" -c "$COOKIE" \
    -d "_token=$TOKEN" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    https://31.97.106.123:8091/login -o /tmp/hris_extwf098_login_out.html
curl -k -sS -b "$COOKIE" https://31.97.106.123:8091/external-workforce/attendance -o "$PAGE"
grep -n "Approval\|Handoff\|Daftar Attendance\|Attendance" "$PAGE" | head -40
