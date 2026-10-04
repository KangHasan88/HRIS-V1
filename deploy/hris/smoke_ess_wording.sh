#!/usr/bin/env bash
set -eu

cookie="/tmp/hris-ess-wording-cookie"
rm -f "$cookie" /tmp/hris-wording_ess*.html

token="$(curl -ks -c "$cookie" https://31.97.106.123:8092/login \
    | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' \
    | head -1)"

curl -ks -b "$cookie" -c "$cookie" -o /dev/null -w "login %{http_code}\n" \
    --data-urlencode "_token=${token}" \
    --data-urlencode "email=dimas@kurmigo.id" \
    --data-urlencode "password=password123" \
    https://31.97.106.123:8092/login

for path in \
    /ess \
    /ess/request-center \
    /ess/profile-updates \
    /ess/reimbursements \
    /ess/leave-requests \
    /ess/payslips \
    /ess/documents \
    /ess/tax-documents
do
    file="/tmp/hris-wording${path//\//_}.html"
    status="$(curl -ks -b "$cookie" -o "$file" -w "%{http_code}" "https://31.97.106.123:8092${path}")"
    echo "$path $status"
    if [ "$status" = "404" ] || [ "$status" = "500" ]; then
        exit 8
    fi
done

grep -R "Layanan Karyawan\|Pusat Pengajuan\|Klaim Reimbursement\|Cuti & Izin\|Slip Gaji\|Bukti Potong PPh 21\|Update Profil" /tmp/hris-wording_ess*.html | head -30 || true

if grep -R "Employee Profile\|Reimbursement Saya\|Update Profil Saya\|Request Center ESS\|Slip Gaji Saya\|Dokumen ESS\|Bukti Potong ESS" /tmp/hris-wording_ess*.html; then
    exit 9
fi
