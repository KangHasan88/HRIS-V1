# HRIS V1 Local Snapshot

Snapshot ini berisi source lokal yang tersedia di komputer kerja saat repo dibuat.

Catatan penting:

- Folder ini belum berupa full Laravel application export dari VPS.
- Source final yang siap jalan terakhir berada di VPS pada `/var/www/kurmigo-hris`.
- Folder `deploy/hris` berisi migration, controller, model, blade, route, smoke script, mobile shell, attendance agent, dan file sync yang pernah dipakai untuk membangun HRIS.
- File cookie/session dan helper yang berisi credential operasional tidak disertakan.

Jika akses VPS sudah normal, langkah berikutnya adalah mengambil full Laravel project dari `/var/www/kurmigo-hris` lalu mengganti snapshot ini dengan source lengkap.
