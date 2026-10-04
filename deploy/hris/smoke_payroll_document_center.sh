#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://31.97.106.123:8091}"
COOKIE_JAR="$(mktemp)"
LOGIN_PAGE="$(mktemp)"
DOC_PAGE="$(mktemp)"

cleanup() {
    rm -f "$COOKIE_JAR" "$LOGIN_PAGE" "$DOC_PAGE"
}
trap cleanup EXIT

curl -k -sS -c "$COOKIE_JAR" "$BASE_URL/login" -o "$LOGIN_PAGE"
TOKEN="$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_PAGE" | head -1)"

curl -k -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -X POST "$BASE_URL/login" \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    --data-urlencode "_token=$TOKEN" \
    --data-urlencode "email=admin@kurmigo.id" \
    --data-urlencode "password=password123" \
    -o /dev/null

STATUS="$(curl -k -sS -b "$COOKIE_JAR" -o "$DOC_PAGE" -w '%{http_code}' "$BASE_URL/payroll/document-center")"
echo "status=$STATUS"

grep -q 'Pusat Dokumen Payroll' "$DOC_PAGE"
grep -q 'Checklist Publish' "$DOC_PAGE"
grep -q 'Workflow Dokumen' "$DOC_PAGE"

echo 'smoke=ok'
