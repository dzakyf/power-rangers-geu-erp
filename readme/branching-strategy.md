# Strategi Branching dan Rilis

Dokumen ini menjelaskan strategi branching dan versioning yang berlaku pada
repositori ini. Untuk prosedur deployment ke klien, lihat
`readme/deploy.md`.

## 1. Prinsip Utama

1. Repositori menggunakan satu trunk utama, yaitu `main`.
2. `main` berisi monolit penuh (seluruh modul: `core`, `credential`, `bp`,
   `finance`, `cns`, `hcga`, dan `legacy`).
3. Pemisahan modul untuk kebutuhan rilis tidak dilakukan melalui branch,
   melainkan melalui build profile dan build-strip artifact.
4. Tag bersifat immutable (tidak boleh dipindahkan). Perbaikan selalu dibuat
   sebagai tag patch baru.
5. Branch bersifat sementara (short-lived) dan sebaiknya dihapus setelah
   di-merge.

## 2. Struktur Branch

| Branch | Fungsi | Umur |
|---|---|---|
| `main` | Trunk/integration, monolit penuh, basis seluruh pengembangan | Permanen |
| `feature/<modul>/<slug>` | Pengembangan fitur untuk modul tertentu | Sementara |
| `release/<versi>` | Pembekuan (freeze) dan QA rilis | Sementara |
| `hotfix/<modul>/<versi>` | Perbaikan darurat dari tag rilis | Sementara |

Tidak ada branch permanen `dev`, `release/<modul>` jangka panjang, atau branch
per modul.

## 3. Alur Pengembangan

1. Pastikan berada pada branch `main` dan tarik perubahan terbaru:

   ```bash
   git switch main
   git pull --ff-only
   ```

2. Pengembangan dapat dilakukan dengan dua cara:

   - **Push langsung ke `main`** — diperbolehkan secara teknis, terutama untuk
     perubahan kecil atau perbaikan cepat.
   - **Feature branch (opsional, direkomendasikan)** — untuk perubahan
     non-trivial, pekerjaan kolaboratif, atau perubahan lintas modul:

     ```bash
     git switch -c feature/cns/nama-fitur
     ```

3. Jika menggunakan feature branch, ajukan Pull Request ke `main`, lakukan
   review, lalu merge dan hapus branch.
4. Kode setiap modul berada pada direktori yang telah ditentukan, antara lain:

   - `app/Modules/<NamaModul>`
   - `routes/modules/<modul>.php`
   - `database/migrations/<modul>`
   - `resources/views/modules/<modul>`

## 4. Alur Rilis dan Hotfix

Rilis dilakukan dari `main` melalui branch `release/<versi>` untuk QA, lalu
diberi tag bundle dan tag modul pada commit yang sama.

```bash
git switch main
git switch -c release/1.0
# QA
git tag -a release/1.0.0 -m "Rilis BDP 1.0.0"
git tag -a bdp/v1.0.0 -m "Modul BDP 1.0.0"
```

Hotfix dibuat dari tag rilis yang sedang berjalan, bukan dari `main`, agar
perubahan yang belum dirilis tidak ikut terkirim.

```bash
git switch -c hotfix/bdp/1.0.1 bdp/v1.0.0
# perbaikan
git tag -a release/1.0.1 -m "Hotfix BDP 1.0.1"
git tag -a bdp/v1.0.1 -m "Hotfix modul BDP 1.0.1"
```

Setelah diverifikasi, perbaikan di-merge atau di-cherry-pick kembali ke `main`.

## 5. Tag dan Versioning

Terdapat dua jenis tag:

| Jenis Tag | Contoh | Fungsi |
|---|---|---|
| Tag modul | `bdp/v1.0.0`, `cns/v1.0.0` | Menandai versi kode suatu modul |
| Tag bundle/rilis | `release/1.0.0`, `release/1.1.0` | Menandai rilis produk yang siap dideploy |

Aturan versioning:

- Penambahan modul baru bersifat aditif sehingga menggunakan kenaikan minor,
  misalnya `1.0.0` (BDP) menjadi `1.1.0` (BDP + CNS).
- Perbaikan bug menggunakan tag patch, misalnya `1.1.1`.
- Tag rilis tidak boleh dipindahkan; buat tag baru untuk setiap perubahan.
- Setiap tag bundle memiliki manifest rilis yang mencatat versi setiap modul
  dan commit sumber.

Tag hanya menandai commit sumber. Komposisi modul yang benar-benar dikirim ke
klien ditentukan oleh build profile; lihat `readme/deploy.md`.

## 6. Isolasi Tim dan Modul

- `main` tetap berupa monolit penuh untuk kebutuhan pengembangan bersama.
- Isolasi tim dilakukan secara lunak melalui `sparse-checkout` dan `CODEOWNERS`.
- Isolasi artifact dilakukan melalui build-strip, sehingga artifact hanya memuat
  modul yang diaktifkan pada profil.
- Modul tidak boleh saling mengimpor secara langsung; kontrak bersama ditempatkan
  pada `core`.

## 7. Kondisi Repositori Saat Ini

- Branch utama: `main`.
- Tag rilis yang tersedia: `bdp/v1.0.0` dan `release/1.0.0`.
- Tag arsip (`archive/*`) disimpan secara lokal dan tidak dipublikasikan.
- Remote aktif: `origin`.

## 8. Referensi

- `readme/deploy.md`
- `readme/how-to-start-development.md`
- `deploy/profiles.php`
- `deploy/module-paths.php`
- `config/modules.php`
