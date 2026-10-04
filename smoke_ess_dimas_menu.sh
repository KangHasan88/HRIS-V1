#!/usr/bin/env bash
set -euo pipefail

cookie="$(mktemp)"
trap 'rm -f "$cookie" /tmp/ess_dimas_after_login.html /tmp/ess_dimas_root.html /tmp/ess_dimas_login.html' EXIT

curl -k -s -c "$cookie" https://31.97.106.123:8092/login -o /tmp/ess_dimas_login.html
token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/ess_dimas_login.html | head -1)"

curl -k -s -b "$cookie" -c "$cookie" \
    -X POST https://31.97.106.123:8092/login \
    -d "_token=$token" \
    -d "email=dimas@kurmigo.id" \
    -d "password=password123" \
    -o /tmp/ess_dimas_after_login.html \
    -w "login %{http_code} %{redirect_url}\n"

curl -k -s -L -b "$cookie" https://31.97.106.123:8092/ \
    -o /tmp/ess_dimas_root.html \
    -w "root %{http_code} %{redirect_url}\n"

grep -o 'Employee Self Service\|Beranda ESS\|Request Center\|Cuti Saya\|Slip Gaji Saya\|Dokumen ESS\|Data Karyawan\|HRIS Dashboard\|Payroll & Tax\|System Administration\|Administration Panel' /tmp/ess_dimas_root.html \
    | sort \
    | uniq
