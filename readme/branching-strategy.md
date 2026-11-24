# Strategi Branching dan Rilis

Dokumen ini menjelaskan strategi branching dan versioning yang berlaku pada
repositori ini. Untuk prosedur deployment ke klien, lihat
`readme/deploy.md`.

## 1. Prinsip Utama

1. Repositori menggunakan satu trunk utama, yaitu `main`.
2. `main` berisi monolit penuh (seluruh modul: `core`, `credential`, `bp`,
   `finance`, `cns`, `hcga`, dan `legacy`).
3. Deployment menggunakan **phase branch** yang merupakan tampilan source yang
   sudah dipangkas untuk modul tertentu.
4. Setiap phase diberi satu tag rilis: `release/X.Y.Z`.
5. Tag bersifat immutable (tidak boleh dipindahkan). Perbaikan selalu dibuat
   sebagai tag rilis baru.
6. Branch pengembangan bersifat sementara (short-lived) dan sebaiknya dihapus
   setelah di-merge.

## 2. Struktur Branch

| Branch | Fungsi | Umur |
|---|---|---|
| `main` | Trunk/integration, monolit penuh, basis seluruh pengembangan | Permanen |
| `feature/<modul>/<slug>` | Pengembangan fitur untuk modul tertentu | Sementara |
| `hotfix/<modul>/<versi>` | Perbaikan darurat dari tag rilis | Sementara |
| `phase/X.Y.Z` | Source tree yang sudah dipangkas untuk phase tertentu | Release view |

`phase/X.Y.Z` bukan branch pengembangan. Branch tersebut dihasilkan dari
`main` menggunakan build profile dan hanya berisi modul, tests, dan docs yang
relevan untuk phase tersebut.

Tidak ada branch permanen `dev`, `release/<modul>`, atau branch per modul.
Tidak ada tag modul seperti `bdp/vX.Y.Z` atau `cns/vX.Y.Z` untuk rilis baru.

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

## 4. Alur Rilis

Rilis dibuat dari `main` setelah QA selesai.

1. Merge perubahan ke `main`.
2. Buat phase branch dari `main` menggunakan build profile yang sesuai.
3. Buat tag rilis pada commit phase branch.
4. Push phase branch dan tag.

Contoh Phase 1:

```bash
git worktree add --detach /tmp/erp-phase-1.0.0 main
cd /tmp/erp-phase-1.0.0

git switch -c phase/1.0.0
php deploy/build-profile.php bdp --with-tests
```

Ganti isi worktree dengan hasil build, lalu commit:

```bash
git rm -r .
rsync -a build/bdp/ ./
git add .
git commit -m "phase 1.0.0: BDP-only source tree"
```

Buat tag:

```bash
git tag -a release/1.0.0 phase/1.0.0 \
  -m "Phase 1.0.0 BDP release"
```

Push:

```bash
git push origin phase/1.0.0
git push origin release/1.0.0
```

Aturan:

- Satu tag rilis mewakili satu phase branch commit.
- Jangan memindahkan atau membuat ulang tag yang sudah dipublikasikan.
- Tag rilis tidak menentukan isi modul; isi modul ditentukan oleh phase branch
  yang dihasilkan dari build profile.

## 5. Alur Hotfix

Hotfix dikerjakan dari `main` atau dari branch hotfix sementara, lalu phase
branch baru dibuat dari commit yang sudah diperbaiki.

```bash
git switch main
git switch -c hotfix/bdp/1.0.1
# lakukan perbaikan dan verifikasi
git switch main
git merge --no-ff hotfix/bdp/1.0.1
git push origin main
```

Setelah perbaikan masuk ke `main`, buat phase branch baru `phase/1.0.1` dengan
profile `bdp`, lalu tag `release/1.0.1`.

## 6. Tag dan Versioning

Hanya gunakan tag rilis dengan format:

```text
release/X.Y.Z
```

Aturan versioning:

- **Patch** (`1.0.1`) untuk perbaikan bug.
- **Minor** (`1.1.0`) untuk penambahan modul atau fitur baru yang kompatibel.
- **Major** (`2.0.0`) untuk perubahan yang tidak kompatibel.
- Tag rilis tidak boleh dipindahkan; buat tag baru untuk setiap perubahan.
- `release-manifest.json` pada phase branch mencatat profil, versi, modul, dan
  commit sumber.

Tag `bdp/v1.0.0` sudah dihapus dan tidak digunakan lagi. Tag `archive/*` bersifat
lokal dan tidak dipublikasikan ke remote.

## 7. Isolasi Tim dan Modul

- `main` tetap berupa monolit penuh untuk kebutuhan pengembangan bersama.
- Isolasi tim dilakukan secara lunak melalui `sparse-checkout` dan `CODEOWNERS`.
- Isolasi deployment dilakukan melalui phase branch yang dihasilkan dari build
  profile, sehingga working tree client hanya berisi modul yang aktif.
- Modul tidak boleh saling mengimpor secara langsung; kontrak bersama ditempatkan
  pada `core`.

## 8. Kondisi Repositori Saat Ini

- Branch utama: `main`.
- Phase branch aktif: `phase/1.0.0`.
- Tag rilis aktif: `release/1.0.0`.
- Tag lama `bdp/v1.0.0` sudah dihapus.
- Tag `archive/*` hanya tersedia secara lokal.
- Remote aktif: `origin`.

## 9. Referensi

- `readme/deploy.md`
- `readme/how-to-start-development.md`
- `config/modules.php`
- `deploy/profiles.php`
- `deploy/module-paths.php`
- `deploy/build-profile.php`
