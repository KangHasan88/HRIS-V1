echo "===== asiatri_report Dashboard refs ====="
find /var/www/html/dev/asiatri/asiatri_report/application/controllers -maxdepth 2 -type f | grep -i "Dashboard\\|Home\\|Main" | while read file; do
  echo "--- $file"
  sed -n "1,220p" "$file"
done
echo "===== report controllers is_logged_in ====="
grep -RIn "is_logged_in\\|redirect('Auth'\\|redirect(\"Auth\"\\|base_url('Auth" /var/www/html/dev/asiatri/asiatri_report/application/controllers 2>/dev/null | sed -n "1,180p"
