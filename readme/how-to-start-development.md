# Panduan Memulai Pengembangan

Dokumen ini menjelaskan langkah-langkah untuk memulai pengembangan proyek pada
lingkungan lokal, branch yang digunakan, modul yang diaktifkan, migrasi yang
dijalankan, serta beberapa alat bantu agar proses pengembangan lebih mudah.

## 1. Prasyarat

Pastikan lingkungan lokal memenuhi kebutuhan berikut:

- PHP **7.4** (proyek mensyaratkan `php: ^7.4.3` pada `composer.json`). PHP 8.4
  tidak kompatibel dengan dependensi proyek.
- Composer 2.
- MySQL 8.
- Node.js dan npm (untuk kompilasi aset Laravel Mix).
- Ekstensi PHP: `mbstring`, `pdo_mysql`, `gd`, `zip`, `bcmath`, `intl`, `dom`,
  `fileinfo`, dan `openssl`.

Jika PHP lokal bukan versi 7.4, gunakan container PHP 7.4 pada bagian Docker di
bawah.

## 2. Setup Awal

1. Clone repositori dan masuk ke direktori proyek:

   ```bash
   git clone <url-repositori>
   cd power-rangers-geu-erp
   ```

2. Pastikan berada pada branch `main` dan tarik perubahan terbaru:

   ```bash
   git switch main
   git pull --ff-only
   ```

3. Salin konfigurasi lingkungan:

   ```bash
   cp .env.example .env
   ```

4. Sesuaikan `.env`:

   ```dotenv
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=erp_local
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Pasang dependensi dan buat application key:

   ```bash
   composer install
   php artisan key:generate
   ```

6. Buat database baru (jangan menghapus database yang sudah ada), lalu jalankan
   migrasi:

   ```bash
   php artisan migrate
   ```

## 3. Branch yang Digunakan

`main` adalah branch trunk yang berisi monolit penuh (seluruh modul). Untuk
pengembangan lokal, modul aktif diatur melalui `MODULES`. Untuk deployment,
maintainer membuat phase branch yang sudah dipangkas dari `main` dan menandainya
dengan tag `release/<versi>`.

Jangan melakukan pengembangan pada branch `phase/X.Y.Z`. Branch tersebut adalah
hasil build yang hanya dipakai untuk deployment, bukan branch kerja. Gunakan
`main` atau feature branch untuk pengembangan. Komposisi modul pada phase branch
sudah dipin, sehingga `MODULES` hanya relevan untuk pengembangan lokal di
`main`.

- Push langsung ke `main` secara teknis diperbolehkan.
- Branch fitur bersifat **opsional**, dan direkomendasikan untuk perubahan
  non-trivial, pekerjaan kolaboratif, atau perubahan lintas modul:

  ```bash
  git switch -c feature/bp/nama-fitur
  ```

- Penamaan branch fitur:

  | Modul | Contoh branch |
  |---|---|
  | Business Partner | `feature/bp/nama-fitur` |
  | CNS | `feature/cns/nama-fitur` |
  | HCGA | `feature/hcga/nama-fitur` |
  | Core / Credential | `feature/core/nama-fitur` |

- Jika menggunakan feature branch: ajukan Pull Request, lakukan review, lalu
  merge ke `main` dan hapus branch.
- Untuk rilis, buat phase branch `phase/<versi>` dari `main` menggunakan build
  profile, lalu tag branch tersebut dengan `release/<versi>`. Untuk perbaikan
  darurat, gunakan branch `hotfix/<modul>/<versi>`. Panduan lengkap tersedia
  pada `readme/branching-strategy.md`.

## 4. Modul yang Diaktifkan

Modul yang dimuat ditentukan oleh variabel `MODULES`. Nilai default pada
`config/modules.php` mencakup seluruh modul, tetapi untuk pengembangan harian
sebaiknya sesuaikan dengan modul yang sedang dikerjakan.

| Kebutuhan | Nilai `MODULES` |
|---|---|
| BDP | `core,credential,bp,finance` |
| CNS | `core,credential,cns` |
| HCGA | `core,credential,cns,hcga` |
| Full monolith | `core,credential,bp,finance,cns,hcga,legacy` |

Catatan:

- `credential` selalu perlu diaktifkan karena digunakan untuk RBAC.
- Modul yang tidak diaktifkan tidak memuat route, provider, maupun migrasinya.
- Untuk pengembangan modul secara terisolasi, gunakan database baru dan
  `MODULES` yang sesuai agar tabel modul lain tidak ikut dibuat.

Contoh menjalankan aplikasi untuk BDP:

```bash
export MODULES=core,credential,bp,finance
php artisan serve
```

Contoh untuk CNS:

```bash
export MODULES=core,credential,cns
php artisan serve
```

## 5. Migrasi

### 5.1 Pilih Modul Aktif Sebelum Migrasi

Migrasi mengikuti modul yang aktif pada `MODULES`. Pilih modul yang sesuai
kebutuhan pengembangan sebelum menjalankan `php artisan migrate`.

Contoh BDP:

```bash
export MODULES=core,credential,bp,finance
php artisan migrate
```

Contoh CNS:

```bash
export MODULES=core,credential,cns
php artisan migrate
```

Contoh HCGA:

```bash
export MODULES=core,credential,cns,hcga
php artisan migrate
```

Contoh full monolith:

```bash
export MODULES=core,credential,bp,finance,cns,hcga,legacy
php artisan migrate
```

Jika ingin membuat nilai `MODULES` permanen untuk lokal, simpan di `.env`:

```dotenv
MODULES=core,credential,bp,finance
```

### 5.2 Jalankan Migrasi

- Jalankan migrasi secara forward-only:

  ```bash
  php artisan migrate
  ```

- Periksa status migrasi:

  ```bash
  php artisan migrate:status
  ```

- Jangan menjalankan `migrate:fresh`, `migrate:refresh`, `migrate:reset`,
  `db:wipe`, `TRUNCATE`, atau drop tabel pada database yang sudah berisi data.
- Untuk mengulang pengujian dari awal, buat database baru (misalnya
  `erp_local_bp_2`) dan jalankan `php artisan migrate` pada database tersebut.
- Migrasi yang dijalankan mengikuti `migration_dirs` pada `config/modules.php`.
  Modul `core` memuat `core` dan `approval-core`; modul `bp` memuat `bdp`.

## 6. Seeder Lokal

`DatabaseSeeder` bersifat module-aware: seeder hanya dijalankan untuk modul yang
aktif.

```bash
php artisan db:seed
```

Flag yang tersedia:

| Variabel | Fungsi |
|---|---|
| `SEED_MENUS` | Menjalankan `MenuListsSeeder` (default `true`) |
| `SEED_REFERENCE_DATA` | Menjalankan seeder data referensi (`master_bank`, `config_system`, `approval_level`, dll.) |
| `SEED_RBAC` | Menjalankan `RbacSeeder` (default `true`) |

Contoh pengembangan BDP tanpa data referensi dari live:

```bash
export MODULES=core,credential,bp,finance
php artisan db:seed
```

Untuk data yang menyerupai live, gunakan `deploy/import-bdp-live.php` (opsional,
memerlukan akses read-only ke database live):

```bash
php deploy/import-bdp-live.php
```

Sesuaikan variabel `LIVE_*` dan `LOCAL_*` pada environment sebelum menjalankan
skrip tersebut. Skrip ini hanya membaca data live dan tidak menulis ke database
live.

## 7. Menjalankan Aplikasi

- Jalankan server pengembangan:

  ```bash
  php artisan serve
  ```

- Periksa daftar route:

  ```bash
  php artisan route:list
  ```

- Jika mengubah aset frontend:

  ```bash
  npm install
  npm run watch
  ```

- Pastikan direktori berikut dapat ditulis oleh pengguna yang menjalankan
  aplikasi. Direktori upload bersifat runtime dan tidak dilacak Git, sehingga
  perlu dibuat pada checkout baru:

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

  chmod -R ug+rwX \
    storage \
    bootstrap/cache \
    public/filevendor \
    public/tmpdata \
    public/project_file \
    public/accounting_file \
    public/doc_recruitment \
    public/filescontract
  ```

- Jangan menjalankan `php artisan route:cache` karena proyek memiliki route
  berbasis closure. Gunakan `config:cache` dan `view:cache` bila diperlukan.

## 8. Docker PHP 7.4

Gunakan bagian ini jika PHP lokal bukan versi 7.4 atau ingin lingkungan yang
konsisten.

Buat direktori sementara, misalnya `/tmp/php74`, lalu simpan `Dockerfile`
berikut:

```dockerfile
FROM php:7.4-cli

RUN sed -i \
        -e 's/deb.debian.org/archive.debian.org/g' \
        -e 's|security.debian.org|archive.debian.org|g' \
        -e '/bullseye-updates/d' \
        /etc/apt/sources.list \
    && rm -f /etc/apt/sources.list.d/*.sources \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        unzip \
        git \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd zip pdo_mysql mysqli bcmath intl mbstring exif \
    && apt-get clean

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
```

Build image dan jalankan container:

```bash
docker build -t erp-php74 /tmp/php74

docker run --rm -it \
  -u $(id -u):$(id -g) \
  -e HOME=/tmp \
  -e COMPOSER_HOME=/tmp/composer \
  -v "$PWD":/app \
  -w /app \
  erp-php74 bash
```

Di dalam container:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

Jika ingin mengakses server dari host, tambahkan pemetaan port:

```bash
docker run --rm -it -p 8000:8000 \
  -u $(id -u):$(id -g) \
  -e HOME=/tmp \
  -e COMPOSER_HOME=/tmp/composer \
  -v "$PWD":/app \
  -w /app \
  erp-php74 bash
```

Saat menggunakan MySQL yang berjalan di host, atur `DB_HOST=host.docker.internal`.
Jika MySQL dijalankan sebagai container, gunakan network Docker yang sama dan
atur `DB_HOST` sesuai nama service/container MySQL.

## 9. Tips Pengembangan Lokal

- Simpan preset modul pada shell agar tidak perlu mengetik ulang:

  ```bash
  export MODULES=core,credential,bp,finance
  ```

- Bersihkan cache setelah mengubah konfigurasi:

  ```bash
  php artisan config:clear
  php artisan view:clear
  php artisan cache:clear
  ```

- Sebelum mengajukan Pull Request, jalankan pemeriksaan batas modul:

  ```bash
  php deploy/check-boundaries.php bdp
  ```

- Gunakan `php artisan route:list` untuk memastikan hanya route modul yang aktif
  yang termuat.

- Untuk pengujian dengan modul tertentu, gunakan `MODULES` yang sesuai dan
  database baru agar data antar modul tidak tercampur.

## 10. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| `composer install` gagal | PHP 8.x | Gunakan PHP 7.4 atau container Docker |
| Tabel modul tidak ditemukan | `MODULES` tidak sesuai | Sesuaikan `MODULES` dan jalankan `php artisan migrate` |
| Route tidak ditemukan | Modul tidak aktif | Periksa `MODULES` dan `php artisan route:list` |
| Permission denied pada storage | Kepemilikan direktori | Perbaiki permission `storage` dan `bootstrap/cache` |
| Perubahan konfigurasi tidak terbaca | Cache konfigurasi | Jalankan `php artisan config:clear` |
| Error pada route | Cache route usang | Jangan gunakan `route:cache`; jalankan `php artisan route:clear` |

## 11. Referensi

- `readme/branching-strategy.md`
- `readme/deploy.md`
- `deploy/profiles.php`
- `deploy/module-paths.php`
- `deploy/build-profile.php`
- `deploy/check-boundaries.php`
- `deploy/import-bdp-live.php`
- `config/modules.php`
- `AGENTS.md`
