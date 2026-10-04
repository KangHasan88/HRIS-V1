#!/usr/bin/env bash
set -euo pipefail

tmpcookie=/tmp/hris_ess_flow_cookie.txt
login=/tmp/hris_ess_flow_login.html
page=/tmp/hris_ess_flow_page.html

curl -k -s -c "$tmpcookie" https://31.97.106.123:8092/login -o "$login"
token=$(grep -o 'name="_token" value="[^"]*' "$login" | sed 's/.*value="//')

curl -k -s -b "$tmpcookie" -c "$tmpcookie" \
    -X POST https://31.97.106.123:8092/login \
    --data-urlencode "_token=$token" \
    --data-urlencode "email=dimas@kurmigo.id" \
    --data-urlencode "password=password123" \
    -o /tmp/hris_ess_flow_post.html

code=$(curl -k -s -b "$tmpcookie" -o "$page" -w '%{http_code}' https://31.97.106.123:8092/ess/flow-center)
echo "status=$code"
grep -q 'Alur ESS Saya' "$page"
grep -q 'Flow Karyawan' "$page"
grep -q 'Pusat Pengajuan' "$page"
if grep -q 'Administration Panel' "$page"; then
    echo 'unexpected Administration Panel text'
    exit 1
fi
if grep -q 'Data Karyawan' "$page"; then
    echo 'unexpected admin Data Karyawan menu'
    exit 1
fi
echo 'smoke=ok'
