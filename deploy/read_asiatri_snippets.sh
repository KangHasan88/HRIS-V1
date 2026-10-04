for app in asiatri_base asiatri_report hris-asiatri ess-asiatri payroll-asiatri; do
  file="/var/www/html/dev/asiatri/$app/application/config/config.php"
  echo "===== $app config top/session ====="
  if [ -f "$file" ]; then
    sed -n "1,140p" "$file"
    sed -n "340,420p" "$file"
  fi
done
echo "===== asiatri_report Auth.php ====="
sed -n "1,180p" /var/www/html/dev/asiatri/asiatri_report/application/controllers/Auth.php
