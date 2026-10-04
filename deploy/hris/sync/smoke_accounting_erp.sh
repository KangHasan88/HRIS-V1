#!/usr/bin/env bash
set -euo pipefail

base="https://31.97.106.123:8091"
cookie="/tmp/hris_accounting_erp_cookie.txt"
login="/tmp/hris_accounting_erp_login.html"
page="/tmp/hris_accounting_erp_page.html"

curl -ksS -c "$cookie" "$base/login" > "$login"
token=$(grep -oP 'name="_token" value="\K[^"]+' "$login" | head -1)

curl -ksS -b "$cookie" -c "$cookie" -o /tmp/hris_accounting_erp_post.html -w "login:%{http_code}\n" \
  -X POST "$base/login" \
  -d "_token=$token" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  -d "remember=1"

curl -ksS -L -b "$cookie" -c "$cookie" -o "$page" -w "status:%{http_code}\n" "$base/integrations/accounting-erp"
grep -q "Accounting & ERP" "$page"
grep -q "Setup Connector ERP" "$page"
grep -q "Generate Journal Payroll" "$page"
grep -q "Journal Batch" "$page"
echo "page:ok"
