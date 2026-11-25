# GEU ERP

Modular Laravel 6 monorepo untuk deployment BDP, CNS, dan HCGA.

## Dokumentasi

- [Panduan Memulai Pengembangan](readme/how-to-start-development.md)
  - Setup lingkungan lokal
  - PHP 7.4 dan Docker
  - `MODULES`
  - Migrasi dan seeder

- [Strategi Branching dan Rilis](readme/branching-strategy.md)
  - `main`, `feature/*`, dan `hotfix/*`
  - Phase branch `phase/X.Y.Z`
  - Release tag `release/X.Y.Z`
  - Phase 1 aktif dan Phase 2 ditunda

- [Panduan Deployment](readme/deploy.md)
  - Deployment phase branch/tag
  - Instalasi pertama
  - Update rilis
  - Migrasi, cache, rollback, dan smoke test

## Quick Start

Prasyarat: PHP 7.4, Composer 2, MySQL 8, dan Node.js.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Atur database dan `APP_URL` di `.env` sebelum menjalankan migrasi.

## Status Rilis

- Phase 1: `phase/1.0.0` → `release/1.0.0`
  - Modul: `core`, `credential`, `bp`, `finance`
- Phase 2: `phase/1.1.0` → `release/1.1.0` (ditunda)

## Aturan Penting

- `main` adalah monolit penuh dan menjadi sumber seluruh pengembangan.
- `phase/X.Y.Z` adalah hasil build untuk deployment, bukan branch pengembangan.
- Jangan melakukan merge `main` ke phase branch secara manual.
- Jangan menjalankan `route:cache` karena proyek memiliki route berbasis closure.
- Migrasi bersifat forward-only; jangan menjalankan `migrate:fresh`,
  `migrate:refresh`, `migrate:reset`, `db:wipe`, atau drop tabel pada database
  yang sudah berisi data.
- Aturan operasional agent tersedia di [AGENTS.md](AGENTS.md).
