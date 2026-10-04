#!/usr/bin/env bash
set -euo pipefail

rm -f /tmp/hris-ess-reimb-cookie /tmp/hris-mgr-budget-cookie

ess_token="$(curl -ks -c /tmp/hris-ess-reimb-cookie https://31.97.106.123:8092/login | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"
curl -ks -b /tmp/hris-ess-reimb-cookie -c /tmp/hris-ess-reimb-cookie -o /dev/null -w "ess_login %{http_code}\n" \
  --data-urlencode "_token=${ess_token}" \
  --data-urlencode "email=dimas@kurmigo.id" \
  --data-urlencode "password=password123" \
  https://31.97.106.123:8092/login
curl -ks -b /tmp/hris-ess-reimb-cookie -o /dev/null -w "ess_reimbursements %{http_code}\n" \
  https://31.97.106.123:8092/ess/reimbursements

mgr_token="$(curl -ks -c /tmp/hris-mgr-budget-cookie https://31.97.106.123:8091/login | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"
curl -ks -b /tmp/hris-mgr-budget-cookie -c /tmp/hris-mgr-budget-cookie -o /dev/null -w "manager_login %{http_code}\n" \
  --data-urlencode "_token=${mgr_token}" \
  --data-urlencode "email=bima@kurmigo.id" \
  --data-urlencode "password=password123" \
  https://31.97.106.123:8091/login
curl -ks -b /tmp/hris-mgr-budget-cookie -o /dev/null -w "manager_budget %{http_code}\n" \
  "https://31.97.106.123:8091/manager/budget?start_date=2026-07-01&end_date=2026-07-31"
