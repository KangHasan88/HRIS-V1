#!/usr/bin/env bash
set -euo pipefail
cd /tmp
rm -f hris_mgr_cookie.txt login.html approval.html
curl -sk -c hris_mgr_cookie.txt https://31.97.106.123:8091/login -o login.html
token=$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' login.html | head -1)
if [ -z "$token" ]; then
  echo "token missing"
  exit 1
fi
curl -sk -b hris_mgr_cookie.txt -c hris_mgr_cookie.txt -X POST https://31.97.106.123:8091/login \
  --data-urlencode "_token=$token" \
  --data-urlencode "email=bima@kurmigo.id" \
  --data-urlencode "password=password123" \
  --data-urlencode "remember=1" \
  -o /dev/null -w "login %{http_code}\n"
curl -sk -b hris_mgr_cookie.txt -o approval.html -w "approval %{http_code}\n" https://31.97.106.123:8091/manager/approval-inbox
grep -E "Inbox Approval|LV-SMOKE-052|OT-SMOKE-052|High" approval.html | head -10
