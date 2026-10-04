for app in hris-asiatri ess-asiatri payroll-asiatri; do
  echo "===== $app ====="
  echo "-- routes --"
  sed -n "1,160p" /var/www/html/dev/asiatri/$app/application/config/routes.php 2>/dev/null || true
  echo "-- controllers --"
  find /var/www/html/dev/asiatri/$app/application/controllers -maxdepth 2 -type f 2>/dev/null | sed -n "1,120p"
done
