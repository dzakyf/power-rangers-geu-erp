# Panduan Deployment

Dokumen ini menjelaskan cara membangun artifact dan melakukan deployment ke
server klien. Untuk strategi branching dan versioning, lihat
`readme/branching-strategy.md`.

## 1. Model Deployment

Deployment menggunakan **artifact**, bukan `git pull` pada source repository.

- Source repository berisi monolit penuh (seluruh modul) dan tidak diberikan
  kepada klien.
- Artifact dibangun menggunakan build profile sehingga hanya memuat modul yang
  diaktifkan.
- Setiap artifact memiliki manifest dan checksum SHA-256.
- Deployment dilakukan secara in-place ke direktori rilis baru, lalu symlink
  `current` dipindahkan secara atomik.

## 2. Prasyarat Server

- PHP **7.4** dengan Composer 2.
- MySQL 8.
- Web server Apache atau Nginx dengan document root ke `public/`.
- Ekstensi PHP: `mbstring`, `pdo_mysql`, `gd`, `zip`, `bcmath`, `intl`, `dom`,
  `fileinfo`, dan `openssl`.
- Direktori yang dapat ditulis oleh user web server:

  ```bash
  storage/
  bootstrap/cache/
  public/filescontract/
  ```

- Akses SSH atau mekanisme deploy lain yang disetujui.

## 3. Build Profile

Komposisi modul ditentukan pada `deploy/profiles.php`.

| Profil | Modul |
|---|---|
| `bdp` | `core`, `credential`, `bp`, `finance` |
| `cns` | `core`, `credential`, `cns` |
| `hcga` | `core`, `credential`, `cns`, `hcga` |
| `full` | seluruh modul |

Untuk instance yang bertumbuh secara bertahap, gunakan profil kumulatif,
misalnya `core,credential,bp,finance,cns`.

## 4. Instalasi Pertama

1. Buat database baru (jangan menghapus database yang sudah ada).
2. Ekstrak artifact ke direktori rilis, misalnya `releases/1.0.0/`.
3. Salin `.env.example` menjadi `.env` dan sesuaikan:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=
   APP_URL=https://domain-klien
   DB_CONNECTION=mysql
   DB_HOST=...
   DB_PORT=3306
   DB_DATABASE=...
   DB_USERNAME=...
   DB_PASSWORD=...
   ```

4. Jalankan:

   ```bash
   php artisan key:generate
   composer install --no-dev -o
   php artisan migrate --force
   ```

5. Import data referensi atau jalankan seeder yang sesuai. Untuk data yang
   menyerupai live, gunakan `deploy/import-bdp-live.php` dengan akses read-only
   ke database live. Skrip tersebut tidak menulis ke database live.
6. Buat direktori upload runtime dan atur permission. Direktori ini tidak
   disertakan dalam artifact maupun Git:

   ```bash
   mkdir -p \
     public/filevendor \
     public/tmpdata \
     public/project_file \
     public/accounting_file \
     public/doc_recruitment \
     public/filescontract/contracts \
     public/filescontract/bookings

   chown -R www-data:www-data \
     storage \
     bootstrap/cache \
     public/filevendor \
     public/tmpdata \
     public/project_file \
     public/accounting_file \
     public/doc_recruitment \
     public/filescontract

   find public/filevendor public/tmpdata public/project_file public/accounting_file public/doc_recruitment public/filescontract -type d -exec chmod 775 {} \;
   find public/filevendor public/tmpdata public/project_file public/accounting_file public/doc_recruitment public/filescontract -type f -exec chmod 664 {} \;
   ```

7. Jalankan:

   ```bash
   php artisan config:cache
   php artisan view:cache
   ```

   Jangan menjalankan `route:cache` karena terdapat route berbasis closure.

8. Lakukan smoke test sebelum serah terima.

## 5. Deploy Rilis dari Tag

1. Pada mesin build, checkout tag rilis dan bangun artifact:

   ```bash
   git switch --detach release/1.0.0
   php deploy/build-profile.php bdp
   php deploy/check-boundaries.php bdp
   ```

2. Buat arsip dan checksum:

   ```bash
   tar -czf bdp-1.0.0-<commit>.tar.gz -C build bdp
   shasum -a 256 bdp-1.0.0-<commit>.tar.gz > bdp-1.0.0-<commit>.tar.gz.sha256
   ```

3. Transfer artifact dan checksum ke server klien.
4. Verifikasi checksum sebelum ekstrak.
5. Backup database, artifact lama, `.env`, `storage/`,
   `public/filescontract/`, dan direktori upload runtime (`public/filevendor`,
   `public/tmpdata`, `public/project_file`, `public/accounting_file`,
   `public/doc_recruitment`).
6. Ekstrak ke direktori rilis baru, misalnya `releases/1.0.0/`.
7. Pertahankan `.env`, `storage/`, `public/filescontract/`, dan direktori upload
   runtime.
8. Ganti symlink `current` ke direktori rilis baru.
9. Jalankan `composer install --no-dev -o` apabila `vendor/` tidak disertakan.
10. Jalankan `php artisan migrate --force`.
11. Jalankan `php artisan config:cache` dan `php artisan view:cache`.
12. Lakukan smoke test. Artifact lama tetap disimpan untuk rollback.

## 6. Pembaruan Patch untuk Klien

Pembaruan patch (misalnya `release/1.0.0` menjadi `release/1.0.1`) dilakukan
secara in-place menggunakan artifact baru. Klien tidak melakukan reinstalasi dan
tidak melakukan `git pull` pada source repository.

1. Unduh artifact `bdp-1.0.1-<commit>.tar.gz` beserta checksum.
2. Verifikasi SHA-256 artifact.
3. Backup database, artifact lama, `.env`, `storage/`,
   `public/filescontract/`, dan direktori upload runtime.
4. Ekstrak artifact ke direktori rilis baru, misalnya `releases/1.0.1/`.
5. Pertahankan `.env`, `storage/`, `public/filescontract/`, dan direktori upload
   runtime.
6. Ganti symlink `current` ke direktori rilis baru secara atomik.
7. Jalankan `composer install --no-dev -o` apabila `vendor/` tidak disertakan.
8. Jalankan `php artisan migrate --force`.
9. Jalankan `php artisan config:cache` dan `php artisan view:cache`.
10. Lakukan smoke test. Artifact lama tetap disimpan untuk rollback.

Ketentuan penting:

- Klien hanya menerima artifact sesuai build profile. Untuk rilis pertama,
  artifact `bdp` hanya memuat `core`, `credential`, `bp`, dan `finance`.
- Source repository (seluruh modul) tidak diberikan kepada klien.
- Reinstalasi dan pembuatan ulang database tidak diperlukan untuk patch.
- Import ulang master data tidak diperlukan untuk patch.

## 7. Konfigurasi dan Cache

- Gunakan `config:cache` dan `view:cache` pada production.
- Jangan gunakan `route:cache` karena terdapat route berbasis closure.
- Setelah mengubah `.env` atau konfigurasi, jalankan:

  ```bash
  php artisan config:clear
  php artisan config:cache
  php artisan view:clear
  php artisan view:cache
  ```

- Cron scheduler:

  ```bash
  * * * * * cd /path/current && php artisan schedule:run >> /dev/null 2>&1
  ```

## 8. Migrasi dan Keamanan Database

- Jalankan migrasi secara forward-only:

  ```bash
  php artisan migrate --force
  ```

- Dilarang menjalankan `migrate:fresh`, `migrate:refresh`, `migrate:reset`,
  `db:wipe`, `TRUNCATE`, atau drop tabel pada database yang sudah berisi data.
- Migrasi harus bersifat aditif dan kompatibel dengan database yang sudah ada.
- Akses ke database live bersifat read-only untuk pengambilan data referensi.
- Backup database wajib dilakukan sebelum migrasi atau deployment.

## 9. Rollback

1. Aktifkan maintenance mode.
2. Pulihkan snapshot database.
3. Pindahkan symlink `current` kembali ke artifact sebelumnya.
4. Jalankan `config:clear` dan `view:clear`.
5. Lakukan smoke test sebelum keluar dari maintenance mode.

Rollback tidak menggunakan down-migration. Pemulihan database mengandalkan
snapshot.

## 10. Checklist Smoke Test

- `/` mengarah ke login atau dashboard.
- `/login` merespons 200.
- Login menggunakan user yang valid.
- Halaman Credential/RBAC memuat modul yang sesuai profil.
- Halaman BDP (partner, warehouse/UCO, projection, survey, performance,
  renewal) dapat diakses.
- Halaman Finance (cost, expenditure, approval) dapat diakses.
- Route modul yang tidak diaktifkan mengembalikan 404.
- Tidak ada error pada `storage/logs/laravel.log` dan log web server.
- `php artisan migrate:status` tidak memiliki migrasi tertunda.

## 11. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Artifact gagal boot | Modul yang tidak ada masih direferensikan | Jalankan `php deploy/check-boundaries.php <profil>` |
| Tabel tidak ditemukan | Migrasi modul tidak dijalankan | Periksa profil dan `config/modules.php` |
| Error permission | Ownership `storage`/`bootstrap/cache` | Perbaiki ownership dan permission |
| Perubahan `.env` tidak terbaca | Config cache | `config:clear` lalu `config:cache` |
| Route error setelah deploy | Cache route usang | Jangan `route:cache`; jalankan `route:clear` |

## 12. Referensi

- `readme/branching-strategy.md`
- `readme/how-to-start-development.md`
- `deploy/profiles.php`
- `deploy/module-paths.php`
- `deploy/build-profile.php`
- `deploy/check-boundaries.php`
- `deploy/import-bdp-live.php`
- `config/modules.php`
