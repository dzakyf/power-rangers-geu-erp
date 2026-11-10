# Setup Modul CNS

Panduan khusus modul **CNS** untuk `power-rangers-geu-erp`. Repo ini memakai
routing monolitik (`routes/web.php`) dan tidak memakai `config/modules.php`,
jadi modul CNS tidak perlu diaktifkan lewat env `MODULES`.

## 1. Aktivasi Modul
Tidak ada langkah aktivasi terpisah. Route CNS sudah terdaftar di
`routes/web.php`, dan migration CNS berada di `database/migrations/cns`.

## 2. Migrasi
Jalankan migration core baru (`user_logs`, `notification`, `chat`,
`signature`, permission approval, dan kolom UCO) terlebih dahulu, lalu
migration CNS:

```bash
php artisan migrate --path=database/migrations/core
php artisan migrate --path=database/migrations/cns
```

Cek status:
```bash
php artisan migrate:status --path=database/migrations/cns
```

> Jangan menjalankan `migrate:fresh` pada database produksi/live. Migration CNS
> bersifat guarded/additive, tetapi validasi dulu di staging.

## 3. Seeder CNS
Jalankan setelah migrasi:
```bash
php artisan db:seed --class=CnsMenuListsSeeder
# opsional, jika belum ada user admin:
php artisan db:seed --class=UserSeeder
# opsional, data contoh alur CNS (hanya untuk development):
php artisan db:seed --class=CnsFlowSeeder
# opsional: role & permission dari menu_lists
php artisan db:seed --class=RbacSeeder
```
- `CnsMenuListsSeeder` mengisi/memperbarui baris `menu_lists` dengan
  `Modules='CNS'` dari `database/seeds/data/menu_lists_cns.php`. Seeder ini
  idempotent berdasarkan `Menu_ID`, menghapus baris CNS yang tidak ada di
  snapshot lokal, dan menyinkronkan `Sub_Menu` user admin/CNS.
- `UserSeeder` membuat user `admin`/`geu009` dengan `Main_Menu=CNS` dan
  `Sub_Menu` berisi menu CNS. Jangan jalankan bila user sudah ada.
- `CnsFlowSeeder` membuat satu alur CNS lengkap: kontrak pembelian
  (`SEED/CTR/001`), booking (`SEED-BK-001`), vessel schedule (`VS/SEED/001`),
  DRAFT SI (`SI/SEED/001`), dan 2 container stuffing.
- Jika `Class ... not found`:
  ```bash
  composer dump-autoload --no-scripts
  ```
- **Jangan** gunakan `php artisan db:seed` tanpa `--class`: `DatabaseSeeder`
  memanggil `MenuListsSeeder` yang melakukan truncate seluruh `menu_lists`.

## 4. Verifikasi Data
```sql
SELECT COUNT(*) FROM menu_lists WHERE Modules='CNS';           -- 50
SELECT Position, COUNT(*) FROM menu_lists WHERE Modules='CNS' GROUP BY Position; -- 1/10/11/27
SELECT username, Priv_ID, Main_Menu FROM users WHERE username='admin'; -- admin / ADM / CNS
```

## 5. Menjalankan & Mengakses
```bash
php artisan serve --host=127.0.0.1 --port=8000
```
- Login: `admin` / `geu009`.
- Halaman awal CNS: `http://127.0.0.1:8000/home-cns`
- Contoh menu: `http://127.0.0.1:8000/cns-vessel-schedule`
- Cek route CNS:
  ```bash
  php artisan route:list | grep cns
  ```

## 6. Troubleshooting
- Sidebar CNS kosong: pastikan `menu_lists` berisi `Modules='CNS'` dan user
  `admin` memiliki `Sub_Menu` CNS (jalankan `CnsMenuListsSeeder`).
- `Nothing to migrate`: migrasi sudah tercatat di tabel `migrations`; cek
  `migrate:status`.
- Error 500 setelah login: cek `storage/logs/laravel-*.log`; pastikan tabel
  core (`user_logs`, `notification`, `chat`) sudah dimigrasi.
- Bersihkan cache:
  ```bash
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```
