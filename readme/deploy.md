# Panduan Deployment

Dokumen ini menjelaskan deployment production ke server klien menggunakan
**phase branch** dan **release tag** di dalam monorepo yang sama.

Untuk strategi branching dan versioning, lihat
`readme/branching-strategy.md`.

## 1. Model Deployment

- `main` berisi monolit penuh (seluruh modul).
- Setiap phase memiliki branch deployment yang sudah dipangkas:

  ```text
  phase/1.0.0
  ```

- Setiap phase diberi satu tag rilis:

  ```text
  release/1.0.0
  ```

- Tag rilis menunjuk ke commit pada phase branch, bukan ke `main`.
- Working tree pada phase branch hanya berisi modul, tests, dan docs yang
  relevan untuk phase tersebut.
- Client melakukan checkout tag rilis. Client tidak perlu menjalankan build
  profile dan tidak perlu menyalin folder `build/`.
- Client tetap dapat melihat branch lain di repository, tetapi checkout phase
  branch/tag hanya menampilkan source phase tersebut.
- Build profile digunakan oleh maintainer sebagai alat internal untuk membuat
  phase branch, bukan sebagai langkah deployment client.

## 2. Prasyarat Server

- Akses Git ke repositori.
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
  public/filevendor/
  public/tmpdata/
  public/project_file/
  public/accounting_file/
  public/doc_recruitment/
  ```

## 3. Komposisi Phase

| Phase | Status | Branch | Tag | Modul |
|---|---|---|---|---|
| Phase 1 | Aktif | `phase/1.0.0` | `release/1.0.0` | `core`, `credential`, `bp`, `finance` |
| Phase 2 | Ditunda | `phase/1.1.0` | `release/1.1.0` | `core`, `credential`, `bp`, `finance`, `cns` |

Phase branch juga memuat tests dan docs yang relevan dengan modul aktif.

Untuk Phase 1:

- BDP tests/docs disertakan.
- CNS, HCGA, dan legacy tidak disertakan.
- Tests/docs core yang shared tetap disertakan.

Phase 2 belum tersedia. Profil `bdp-cns` harus ditambahkan terlebih dahulu
sebelum `phase/1.1.0` dan `release/1.1.0` dapat dibuat.

## 4. Instalasi Pertama

Contoh untuk Phase 1.

### 4.1 Clone dan checkout tag

```bash
git clone <url-repositori> /var/www/erp
cd /var/www/erp
git fetch origin --tags
git checkout release/1.0.0
```

Pastikan berada pada commit phase:

```bash
git log -1 --oneline
cat release-manifest.json
```

### 4.2 Konfigurasi environment

```bash
cp .env.example .env
```

Sesuaikan `.env`:

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

`MODULES` tidak perlu diubah pada phase branch karena `config/modules.php`
sudah dipin ke komposisi phase. Variabel `MODULES` tetap digunakan untuk
pengembangan lokal di `main`.

### 4.3 Pasang dependensi dan migrasi

```bash
composer install --no-dev -o
php artisan key:generate
php artisan migrate --force
```

### 4.4 Buat direktori upload dan permission

```bash
mkdir -p \
  storage/framework/{cache,sessions,views} \
  bootstrap/cache \
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

### 4.5 Cache dan web server

```bash
php artisan config:cache
php artisan view:cache
```

Document root web server:

```text
/var/www/erp/public
```

Jangan jalankan `route:cache` karena proyek memiliki route berbasis closure.

### 4.6 Smoke test

Lakukan smoke test sebelum serah terima.

## 5. Deploy Rilis Baru

### 5.1 Sisi maintainer

1. Merge perubahan ke `main`.
2. Lakukan QA.
3. Buat phase branch baru dari `main` menggunakan build profile dan sertakan
   tests.
4. Buat tag rilis pada commit phase branch.
5. Push phase branch dan tag.

Detail pembuatan phase branch ada pada bagian 8.

### 5.2 Sisi client

```bash
cd /var/www/erp
git fetch origin --tags
git checkout release/1.0.1
```

Lalu:

```bash
composer install --no-dev -o
php artisan config:clear
php artisan migrate --force
php artisan config:cache
php artisan view:cache
```

`.env`, `storage/`, dan direktori upload tidak berubah karena tidak dilacak Git.
Tidak perlu menyalin folder build atau artifact.

## 6. Rencana Penambahan Modul (Phase 2)

Penambahan modul dilakukan dengan membuat phase branch baru dari `main`,
bukan dengan menyalin modul ke branch lama.

Phase 2 masih ditunda. Langkah berikut baru dijalankan setelah Phase 1 stabil
dan profil `bdp-cns` ditambahkan ke `deploy/profiles.php`.

Contoh Phase 2: BDP + CNS.

1. Merge pekerjaan CNS ke `main`.
2. Buat phase branch baru:

   ```text
   phase/1.1.0
   ```

   dengan profile `bdp-cns`.

3. Buat tag:

   ```text
   release/1.1.0
   ```

4. Client menjalankan:

   ```bash
   git fetch origin --tags
   git checkout release/1.1.0

   composer install --no-dev -o
   php artisan config:clear
   php artisan migrate --force
   php artisan config:cache
   php artisan view:cache
   ```

Data lama, `.env`, `storage/`, dan file upload tetap dipertahankan. Migrasi CNS
dijalankan secara aditif.

Gunakan kenaikan **minor** untuk penambahan modul, misalnya `1.0.0` menjadi
`1.1.0`.

## 7. Konfigurasi dan Cache

- Gunakan `config:cache` dan `view:cache` pada production.
- Jangan gunakan `route:cache`.
- Setelah mengubah `.env`:

  ```bash
  php artisan config:clear
  php artisan config:cache
  php artisan view:clear
  php artisan view:cache
  ```

- Cron scheduler:

  ```bash
  * * * * * cd /var/www/erp && php artisan schedule:run >> /dev/null 2>&1
  ```

## 8. Pembuatan Phase Branch

Langkah ini hanya dilakukan oleh maintainer, bukan oleh client.

Gunakan clean worktree agar `.env`, upload lokal, dan file untracked tidak ikut
masuk ke phase branch.

```bash
git worktree add --detach /tmp/erp-phase-1.0.0 main
cd /tmp/erp-phase-1.0.0

git switch -c phase/1.0.0
php deploy/build-profile.php bdp --with-tests
```

Ganti isi worktree dengan hasil build:

```bash
git rm -r .
rsync -a build/bdp/ ./
git add .
git commit -m "phase 1.0.0: BDP-only source tree"
```

Commit tersebut memiliki parent `main`, sehingga history `main` tetap
dipertahankan, tetapi tree phase hanya berisi modul yang aktif.

Buat tag rilis:

```bash
git tag -a release/1.0.0 phase/1.0.0 \
  -m "Phase 1.0.0 BDP release"
```

Push:

```bash
git push origin phase/1.0.0
git push origin release/1.0.0
```

Untuk Phase 2, gunakan profile `bdp-cns`, branch `phase/1.1.0`, dan tag
`release/1.1.0`.

## 9. Migrasi dan Keamanan Database

- Jalankan migrasi secara forward-only:

  ```bash
  php artisan migrate --force
  ```

- Dilarang menjalankan `migrate:fresh`, `migrate:refresh`, `migrate:reset`,
  `db:wipe`, `TRUNCATE`, atau drop tabel pada database yang sudah berisi data.
- Migrasi harus aditif dan kompatibel dengan database yang sudah ada.
- Akses ke database live bersifat read-only untuk pengambilan data referensi.
- Backup database wajib dilakukan sebelum migrasi atau deployment.

## 10. Rollback

1. Aktifkan maintenance mode.
2. Pulihkan snapshot database.
3. Checkout tag rilis sebelumnya:

   ```bash
   git fetch origin --tags
   git checkout release/1.0.0
   ```

4. Jalankan `config:clear` dan `view:clear`.
5. Lakukan smoke test sebelum keluar dari maintenance mode.

Rollback tidak menggunakan down-migration. Pemulihan database mengandalkan
snapshot.

## 11. Checklist Smoke Test

- `/` mengarah ke login atau dashboard.
- `/login` merespons 200.
- Login menggunakan user yang valid.
- Halaman Credential/RBAC memuat modul yang sesuai.
- Halaman BDP (partner, warehouse/UCO, projection, survey, performance,
  renewal) dapat diakses.
- Halaman Finance (cost, expenditure, approval) dapat diakses.
- Route modul yang tidak diaktifkan mengembalikan 404.
- Tidak ada error pada `storage/logs/laravel.log` dan log web server.
- `php artisan migrate:status` tidak memiliki migrasi tertunda.

## 12. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Tag tidak ditemukan | Tag belum di-fetch | Jalankan `git fetch origin --tags` |
| File modul lain terlihat | Checkout bukan phase branch/tag | Periksa `git branch --show-current` atau tag |
| Tabel tidak ditemukan | Migrasi modul tidak dijalankan | Jalankan `php artisan migrate --force` |
| Error permission | Ownership `storage`/`bootstrap/cache` | Perbaiki ownership dan permission |
| Perubahan `.env` tidak terbaca | Config cache | `config:clear` lalu `config:cache` |
| Route error setelah deploy | Cache route usang | Jangan `route:cache`; jalankan `route:clear` |

## 13. Referensi

- `readme/branching-strategy.md`
- `readme/how-to-start-development.md`
- `config/modules.php`
- `deploy/profiles.php`
- `deploy/module-paths.php`
- `deploy/build-profile.php`
- `deploy/check-boundaries.php`
- `deploy/import-bdp-live.php`
