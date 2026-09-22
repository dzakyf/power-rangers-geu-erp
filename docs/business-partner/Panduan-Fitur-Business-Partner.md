# Panduan Fitur Modul Business Partner

**Versi Dokumen:** 1.3  
**Tanggal Penyusunan:** 31 Agustus 2026  
**Tanggal Pembaruan:** 22 September 2026  
**Penyusun:** Tim Sistem Informasi GEU  
**Status:** Draf untuk reviu

---

## Tentang Panduan Ini

Panduan ini disusun sebagai rujukan penggunaan seluruh fitur yang tersedia pada modul **Business Partner** dalam sistem aplikasi GEU. Seluruh penjelasan ditulis dengan bahasa sehari-hari yang formal agar dapat dipahami oleh pengguna dari berbagai latar belakang, termasuk pengguna yang tidak memiliki kemampuan teknis.

Setiap pembahasan fitur disertai gambaran lokasi menu, fungsi utama, serta langkah penggunaan apabila diperlukan. Tanda **[Sisipkan tangkapan layar]** menunjukkan posisi yang direkomendasikan untuk dilengkapi gambar tampilan aplikasi.

---

## Daftar Isi

1. Pendahuluan
2. Gambaran Umum Alur Kemitraan
3. Menu Opportunity: Pengelolaan Calon Mitra
4. Menu Onboarding: Persetujuan dan Aktivasi Mitra
5. Menu Keuangan
6. Menu Compliance: Kepatuhan
7. Menu Inventory: Persediaan dan Logistik UCO
8. Menu Performance: Kinerja
9. Menu Support: Dukungan Pengguna
10. Menu Branding: Pemasaran
11. Menu Dashboard: Pemantauan
12. Menu Mitra Management: Siklus Keanggotaan
13. Menu Training: Pelatihan
14. Menu Asset dan Fasilitas
15. Profil Saya
16. Glosarium

> Catatan: Setelah dokumen dibuka pada Microsoft Word, silakan klik kanan pada area daftar isi lalu pilih *Update Field* agar nomor halaman tampil dengan benar.

---

## 1. Pendahuluan

### 1.1 Tentang Modul Business Partner

Modul Business Partner adalah bagian dari sistem aplikasi GEU yang mengelola keseluruhan proses kemitraan, mulai dari pencatatan calon mitra, proses persetujuan, hingga pengelolaan mitra yang telah aktif beroperasi. Melalui modul ini, tim internal maupun mitra dapat melakukan aktivitas kerja secara terpusat dan terekam dengan rapi.

### 1.2 Siapa yang Menggunakan Modul Ini

Modul ini digunakan oleh beberapa kelompok pengguna dengan hak akses yang berbeda, antara lain:

- **Administrator**, mengelola data induk, proses persetujuan, dan pemantauan menyeluruh.
- **Tim Sales**, mengelola calon mitra (prospek), penugasan, dan pencapaian kinerja.
- **Mitra (Business Partner)**, memantau usaha, fasilitas, pelatihan, laporan keuangan, dan layanan dukungan.

Hak akses setiap pengguna dikelola melalui halaman **Credential → Role**. Role dan permission diselaraskan dengan daftar menu yang terdaftar pada sistem; menu yang sebelumnya belum memiliki izin akses kini otomatis terisi. Halaman ini dapat diubah oleh Administrator, dan terdapat pengamanan agar tindakan **Revoke All** pada role **ADM** tidak mencabut akses pengelolaan role itu sendiri (mencegah admin terkunci keluar).

Penambahan menu baru pada halaman yang sama dilakukan secara terpandu dalam **4 level**: Modul (kode MS), Submodul (kode SM), Subheader (kode SS, dapat dilewati bila tidak diperlukan), dan Menu Item (kode SI). Pada setiap level tersedia pilihan memilih menu yang sudah ada atau membuat baru; bila menu menengah dibuat baru, sistem otomatis membuat elemen menu beserta izin aksesnya dalam satu proses.

### 1.3 Cara Membaca Panduan

- Urutan bab mengikuti urutan menu pada bilah navigasi atas aplikasi.
- Istilah teknis diminimalkan; istilah khusus yang tidak dapat dihindari dijelaskan pada bagian **Glosarium** di akhir panduan.
- Langkah-langkah penggunaan hanya dicantumkan pada fitur yang alurnya berjenjang, agar panduan tetap ringkas.

[Sisipkan tangkapan layar: Tampilan awal halaman utama modul Business Partner]

---

## 2. Gambaran Umum Alur Kemitraan

Sebelum membahas tiap menu, berikut alur besar hubungan kerja sama antara calon mitra dan GEU:

1. **Perkenalan dan Pendaftaran** — Calon mitra mendaftar melalui tautan pendaftaran daring atau didata oleh tim sales.
2. **Pengelolaan Prospek** — Data calon mitra dikelompokkan dan ditugaskan kepada sales pendamping.
3. **Proses Persetujuan (Onboarding)** — Berlangsung tujuh tahap: pemeriksaan dokumen, penandatanganan MOU, penerbitan tagihan uang muka, penandatanganan Perjanjian Kerja Sama (PKS), survei lokasi, penyiapan fasilitas, hingga mitra dinyatakan **Aktif**.
4. **Operasional** — Mitra menjalankan usaha dengan dukungan inventori, logistik UCO (minyak jelantah), pelatihan, dan layanan dukungan.
5. **Pemantauan dan Pelaporan** — Kinerja, laporan keuangan, dan bagi hasil dipantau melalui menu Dashboard dan Keuangan.
6. **Akhir Masa Kerja Sama** — Kontrak dapat diperpanjang (renewal), diakhiri (pemutusan), atau dialihkan kepemilikannya sesuai kesepakatan.

> Prinsip penting: hampir semua keputusan penting (persetujuan mitra baru, perpanjangan, pengakhiran, pengalihan) melewati proses **persetujuan bertingkat** agar tertib administrasi tetap terjaga.

---

## 3. Menu Opportunity: Pengelolaan Calon Mitra

Menu Opportunity adalah tempat kerja utama tim sales untuk mengelola calon mitra sebelum resmi menjadi mitra. Istilah **lead** atau prospek dipakai untuk menyebut data calon mitra yang belum atau sedang dalam proses pendekatan.

[Sisipkan tangkapan layar: Bilah menu Opportunity beserta sub-menunya]

### 3.1 Fresh Leads

Berisi data calon mitra baru yang belum ditindaklanjuti. Sales dapat membuka detail setiap prospek, melengkapi informasi, serta menetapkan status, kualifikasi, dan klasifikasi prospek agar mudah diprioritaskan.

### 3.2 Outstanding Leads

Berisi prospek yang masih menunggu tindak lanjut. Setiap prospek dapat dibuka detailnya dan diberi catatan tindak lanjut agar tidak ada calon mitra yang terlewat.

### 3.3 Canceled Leads

Berisi prospek yang dibatalkan beserta alasannya. Alasan pembatalan berguna sebagai bahan evaluasi tim pemasaran.

### 3.4 History Leads

Berisi riwayat seluruh perubahan data prospek sehingga jejak komunikasi dan perubahan status dapat ditelusuri kembali.

### 3.5 Assignment Sales

Fitur untuk menugaskan seorang sales sebagai penanggung jawab (PIC) suatu prospek.

Langkah singkat:

1. Buka menu **Assignment Sales** dan pilih prospek yang akan ditugaskan.
2. Tetapkan sales penanggung jawab, lalu simpan.
3. Gunakan tombol **WhatsApp** pada baris data untuk langsung menghubungi calon mitra.

[Sisipkan tangkapan layar: Halaman Assignment Sales]

### 3.6 Reassign Sales

Digunakan ketika penanggung jawab sales perlu diganti, misalnya karena mutasi atau wilayah penugasan. Riwayat pergantian tetap tersimpan.

### 3.7 Registrasi Partner Baru

Calon mitra dapat mendaftar melalui dua cara:

- **Tautan pendaftaran daring** — Calon mitra mengisi formulir mandiri melalui halaman pendaftaran publik, baik untuk kategori perseorangan maupun perusahaan.
- **Impor berkas Excel** — Tim internal mengunduh templat Excel, mengisi data calon mitra, lalu mengunggahnya agar masuk otomatis sebagai data prospek.

Formulir pendaftaran **internal (sales/admin)** dan **publik** kini memakai komponen formulir yang sama dengan satu proses pengiriman terpadu. Dropdown **Provinsi, Kota/Kabupaten, Kecamatan, Kelurahan, dan Kode Pos** terisi otomatis dari data master secara berantai. Sistem menampilkan peringatan agar pengguna **mengizinkan akses lokasi (GPS)** pada peramban; koordinat lokasi tetap dapat diisi secara opsional.

Formulir pendaftaran yang dibuka melalui tautan yang dikirim oleh tim sales (misalnya melalui WhatsApp) kini **terisi otomatis** dari data prospek yang telah diinput sebelumnya, sehingga calon mitra tidak perlu mengisi ulang data yang sama. Berkas KTP dan NPWP yang diunggah melalui formulir tersebut kini ikut tersimpan dengan benar pada saat pengajuan dikirim.

> **Cara perhitungan estimasi modal (Capital Estimation):** sistem mengelompokkan estimasi modal calon mitra ke dalam empat tingkat: **1** = kurang dari Rp50 juta; **2** = Rp100–200 juta; **3** = Rp200–300 juta; **4** = lebih dari Rp300 juta.

> **Cara perhitungan rekomendasi kelayakan otomatis:** berdasarkan sumber dana yang diisi pada formulir, sistem memberi penanda awal kelayakan. Individu dengan sumber dana pinjaman atau pensiun otomatis ditandai **Tidak Direkomendasikan**; sumber dana tabungan atau usaha ditandai **Direkomendasikan**; selain itu **Dipertimbangkan**. Untuk perusahaan, sumber dana pinjaman menandai **Tidak Direkomendasikan**, sedangkan sumber dana usaha atau investor menandai **Direkomendasikan**. Penanda ini hanya bantuan awal — keputusan akhir tetap melalui proses persetujuan.

[Sisipkan tangkapan layar: Halaman pendaftaran publik calon mitra]

### 3.8 Assignment History

Mencatat seluruh sejarah penugasan sales, termasuk tanggal penugasan dan pergantian penanggung jawab, sebagai bahan audit dan evaluasi.

> Fitur pendukung: hampir semua daftar pada modul ini memiliki tombol **Export Excel** sehingga data dapat diolah lebih lanjut di luar aplikasi.

---

## 4. Menu Onboarding: Persetujuan dan Aktivasi Mitra

Menu Onboarding mengelola proses hingga seorang calon mitra resmi menjadi mitra aktif.

### 4.1 Business Lists

Daftar seluruh mitra terdaftar beserta ringkasan datanya, baik kategori perseorangan maupun perusahaan. Dari halaman ini pengguna dapat:

- Membuka **detail registrasi** setiap mitra untuk verifikasi kelengkapan data.
- Mengirim pesan **WhatsApp** kepada mitra berstatus Aktif.
- Mengekspor daftar ke Excel.

[Sisipkan tangkapan layar: Halaman Business Lists]

### 4.2 Tujuh Tahap Persetujuan Mitra

Setiap calon mitra melewati tahapan berikut secara berurutan. Kemajuan tahapan dapat dipantau melalui tampilan langkah (stepper) pada halaman persetujuan.

1. **Cek Dokumen** — Pemeriksaan kelengkapan dokumen identitas dan legalitas.
2. **Penandatanganan MOU** — Naskah Memorandum of Understanding dibuat otomatis oleh sistem, dapat dipratinjau, dicetak, lalu diunggah kembali setelah ditandatangani.
3. **Penerbitan Tagihan Uang Muka (DP)** — Sistem menerbitkan tagihan; bukti pembayaran dipantau pada tahap ini.
4. **Perjanjian Kerja Sama (PKS)** — Naskah PKS dibuat otomatis, ditandatangani, kemudian diunggah kembali.
5. **Survei Lokasi** — Tim melakukan survei kelayakan lokasi usaha; hasilnya melalui proses persetujuan survei.
6. **Instalasi/Penyiapan** — Pencatatan penyerapan fasilitas dan pemesanan peralatan (lihat Bab 14).
7. **Aktif** — Setelah seluruh syarat terpenuhi, sistem otomatis membuat **akun login mitra** dan mengubah status menjadi Aktif.

> **Cara perhitungan masa kerja sama:** kontrak kemitraan berlaku selama **4 tahun** sejak mitra dinyatakan aktif.

> **Cara perhitungan tagihan kemitraan:** nilai tagihan dihitung dari estimasi pendapatan mitra dan dibagi menjadi tiga tahap pembayaran: **uang muka (DP)** sebesar **12,13%**, **termin 1 (pengadaan fasilitas)** sebesar **66,66%**, dan **termin 2 (modal kerja)** sebesar **21,22%** dari nilai estimasi.

> Catatan: Terdapat fitur **Force Approve** untuk mempercepat persetujuan dalam kondisi khusus. Fitur ini hanya untuk pengguna berwenang karena melewati sebagian tahapan normal.

> Pembaruan: setiap tahap yang sudah selesai dapat dibuka kembali tanpa menurunkan kembali kemajuan mitra. Mitra yang telah berstatus **Aktif** tetap Aktif, dan data tahap yang sudah selesai tidak berubah.

[Sisipkan tangkapan layar: Halaman progres onboarding dengan tampilan tujuh tahap]

### 4.3 Halaman Persetujuan (Approval)

Menampilkan daftar pengajuan yang menunggu persetujuan beserta posisi pejabat penyetuju saat ini. Penyetuju dapat menyetujui (approve) atau menolak (reject) disertai catatan. Seluruh keputusan tercatat dalam log persetujuan yang dapat ditinjau ulang.

Daftar ini juga menampilkan rekomendasi kelayakan setiap pengajuan (**Direkomendasikan**, **Tidak Direkomendasikan**, atau **Dalam Pertimbangan**) sesuai sumber dana yang diisi pada saat pendaftaran. Proses persetujuan untuk perpanjangan (renewal), pengakhiran (termination), dan pengalihan kepemilikan (transfer) kini berjalan tuntas: keputusan akhir otomatis memperbarui data pengajuan terkait.

### 4.4 Survei dan Approval Survei

Menu survei digunakan untuk mencatat jadwal dan hasil kunjungan survei lokasi calon mitra. Hasil survei kemudian diproses melalui menu Approval Survei sebelum tahapan onboarding dapat berlanjut.

Setelah survei dikirim, bagian **Analisis Surveyor → Penilaian** menampilkan skor tiap aspek serta hasil analisisnya secara otomatis. Survei yang telah disetujui pada seluruh tingkat persetujuan kini tercatat dengan status **selesai (DONE_SURVEY)**, dan tahapan onboarding dapat dilanjutkan.

> **Cara perhitungan nilai survei:** survei dinilai dalam empat aspek dengan skor total maksimal **100**.
> Aspek **C (Foto)** — setiap item bernilai maksimal 2,5; total maksimal 10.
> Aspek **D (Checklist)** — setiap item terpenuhi bernilai 6; satu item khusus bernilai 7; total maksimal 55.
> Aspek **E (Lingkungan)** — tiap item bernilai 1, 3, atau 5; total maksimal 20.
> Aspek **F (Estimasi UCO)** — berdasarkan perkiraan tonase UCO: lebih dari 15 ton = 15; 10 ton = 12; 6 ton = 8; 4 ton = 5; di bawah itu = 2; total maksimal 15.
>
> **Skor total = C + D + E + F.** Hasil **≥ 80** berarti **Layak**; **70–79** berarti **Layak Bersyarat**; di bawah 70 berarti **Tidak Layak**. Setiap calon mitra dibatasi maksimal tiga kali survei.

---

## 5. Menu Keuangan

Menu Keuangan memuat seluruh laporan dan proses terkait aspek finansial kemitraan.

### 5.1 Proyeksi

Pengelolaan proyeksi pendapatan tiap mitra/gudang. Proyeksi diinput melalui templat Excel yang diunduh, diisi, lalu diunggah kembali ke sistem. Daftar pengeluaran juga dikelola pada bagian ini.

> **Cara perhitungan awal periode proyeksi:** proyeksi dihitung mulai dari **bulan setelah bulan pengunggahan berkas**. Bulan pertama setelah unggah dipetakan sebagai **Bulan 1**; bulan-bulan sebelum itu bernilai nol. Acuan ini **tidak** bergantung pada tahap onboarding mitra, dan apabila berkas diunggah ulang, awal periode mengikuti bulan unggah yang terbaru.

### 5.2 Laporan Keuangan Bulanan dan Tahunan (LK)

Laporan laba rugi periodik per gudang/mitra yang dapat dilihat langsung di layar maupun diekspor. Laporan menjadi dasar perhitungan bagi hasil.

> **Cara perhitungan laporan (ringkas):**
> **Harga Transfer dan Pendapatan** diambil dari **harga transfer per liter yang diisi pada setiap dokumen outstock yang telah selesai (COMPLETED)**: Pendapatan = total (jumlah UCO keluar kontainer × harga transfer dokumen); Harga Transfer = Pendapatan ÷ total jumlah keluar. Bulan tanpa dokumen outstock berharga transfer menampilkan nilai **nol** (konsep harga transfer baru; data lama dibiarkan nol).
> **Harga pokok (COGS)** = biaya langsung (jurnal golongan 502) + nilai bahan baku (harga beli rata-rata × jumlah UCO keluar).
> **Laba bersih** = pendapatan − harga pokok − biaya operasional − alokasi kapital. Alokasi kapital = **2,5%** dari pendapatan.
> **Bagi hasil mitra = 60% dari laba bersih**; bagian perusahaan = 40%.
> **EBITDA** = (laba bersih × 60%) − biaya gaji − biaya sewa; **ROI** = EBITDA ÷ total investasi × 100.
> **Periode laporan** mengikuti tanggal akhir bulan yang sebenarnya, sehingga seluruh bulan (termasuk Februari, April, Juni, September, dan November) tampil dengan benar.
> **Baris biaya langsung** pada laporan mengikuti kode akun COA: **52001 Bensin, 52002 Charging, dan 52003 Parkir**.

[Sisipkan tangkapan layar: Laporan Keuangan Bulanan]

### 5.3 Management Fee

Membandingkan nilai biaya manajemen dengan bagi hasil mitra dan perusahaan per gudang sebagai bahan evaluasi profitabilitas.

> **Cara perhitungan:** total keseluruhan per gudang = biaya manajemen + bagi hasil mitra (60% laba bersih) + bagi hasil perusahaan (40% laba bersih).

### 5.4 Valuasi dan Summary Valuasi

Menghitung nilai usaha mitra berdasarkan daftar aset beserta penyusutannya. Bagian Summary menyajikan total investasi, total nilai aset, akumulasi penyusutan, dan sisa nilai aset.

> **Cara perhitungan penyusutan:** tingkat penyusutan = 1 ÷ masa manfaat aset (tahun). Penyusutan per bulan = total nilai aset × tingkat penyusutan ÷ 12. Sisa nilai aset = penyusutan per bulan × sisa bulan masa manfaat.

> **Cara perhitungan waktu pakai (bulan):** waktu pakai dihitung sejak **tanggal order** aset, bukan dari konfigurasi masa manfaat. Nilai waktu pakai dibatasi maksimal sepanjang masa penyusutan, sehingga sisa waktu penyusutan tidak pernah bernilai negatif.

> **Catatan daftar aset:** daftar aset pada halaman valuasi (termasuk pada formulir pengajuan perpanjangan, pemutusan, dan pengalihan kepemilikan) hanya menampilkan item dengan status **ASSET**.

### 5.5 Dashboard Profit Sharing dan Overview Mitra

Menampilkan bagi hasil per gudang selama dua belas bulan serta rekap bagi hasil per mitra. Berguna untuk memantau tren bagi hasil dari waktu ke waktu.

Proyeksi pada dashboard mengikuti **bulan unggah berkas proyeksi**: bulan **setelah** bulan unggah dipetakan sebagai **Bulan 1**, sehingga seluruh **empat tahun** proyeksi tampil berurutan dan tidak lagi dimulai dari Januari. Acuan ini tidak bergantung pada tahap onboarding mitra.

### 5.6 Pengajuan Bagi Hasil dan Approval

Mitra mengajukan pencairan bagi hasil dengan cara:

1. Mengunduh formulir pengajuan (berkas Word) yang sudah terisi otomatis.
2. Mencetak, menandatangani, lalu mengunggah kembali berkas tersebut.
3. Pengajuan diproses melalui persetujuan bertingkat hingga disetujui.

> **Cara perhitungan total pembayaran:** total yang dibayarkan = royalti + PPN; **PPN dihitung 11%** dari nilai royalti.

### 5.7 History Royalti

Riwayat pembayaran royalti beserta statusnya. Administrator dapat memperbarui catatan pembayaran pada bagian ini.

### 5.8 CAPEX

Catatan pengajuan belanja modal (Capital Expenditure) berupa deskripsi dan nilai belanja. Data dapat ditambah, diubah, dan dihapus sesuai kewenangan.

---

## 6. Menu Compliance: Kepatuhan

Menu Compliance memastikan operasional mitra memenuhi standar dan ketentuan yang berlaku.

### 6.1 Master Audit dan Master Audit Parameter

Data induk untuk kegiatan audit: kategori audit beserta parameter penilaiannya. Keduanya wajib disiapkan sebelum kegiatan audit gudang dilakukan.

### 6.2 SOP

Gudang penyimpanan dokumen Prosedur Operasional Standar yang dapat diunduh oleh pengguna sebagai pedoman kerja.

### 6.3 Warehouse Audit

Pencatatan jadwal audit gudang beserta nomor audit dan temuan yang ditemukan. Temuan audit menjadi dasar tindak lanjut perbaikan. Warna baris pada daftar audit mengikuti rentang skor (Sangat Baik, Baik, Perlu Perbaikan, Tidak Memenuhi Standar), sedangkan temuan berkategori **kritis** ditandai dengan warna merah pada kolom kategori temuan.

### 6.4 Update Pajak Kemitraan

Rekapitulasi jenis pajak, objek pajak, persentase, dasar perhitungan, dan penanggung jawab pajak untuk setiap gudang mitra.

### 6.5 Legal

Tempat mengunggah dan mengelola dokumen-dokumen legal kemitraan. Daftar dokumen legal dapat diunduh sebagai arsip.

---

## 7. Menu Inventory: Persediaan dan Logistik UCO

Menu Inventory terbagi dua bagian besar: inventori gudang mitra dan logistik UCO (Used Cooking Oil / minyak jelantah) yang merupakan komoditas utama rantai pasok.

### 7.1 Inventori Gudang Mitra

- **Gudang** — Pendaftaran gudang mitra dengan kode otomatis, lokasi, kapasitas, tipe HUB, dan status aktif/nonaktif. Halaman detail gudang menampilkan **Total System Quantity** (jumlah stok menurut sistem pada seluruh kontainer gudang) beserta rincian per kontainer, serta riwayat operasional UCO dalam satu log yang dapat ditelusuri.
- **Open Warehouse** — Pencatatan penerimaan barang masuk gudang, lengkap dengan volume diterima, harga per liter, volume tolakan, dan unggahan bukti timbangan.
- **Stock Request (Warehouse Out)** — Permintaan dan pencatatan barang keluar dari gudang, dinilai berdasarkan harga master stok.
- **Monitoring Stock** — Pantauan sisa stok terkini per gudang, dapat ditelusuri sampai rincian persediaan dan riwayat permintaan.
- **Jurnal** — Catatan keuangan harian gudang berdasarkan kode akun, dilengkapi rekapitulasi bulanan dan tahunan.
- **Stock Opname** — Penghitungan fisik stok berkala per kontainer. Kolom **System Quantity** (stok menurut sistem) terisi otomatis dan tidak dapat diubah manual; nilainya **diperbarui dari catatan terkini pada saat opname diselesaikan**. Daftar **Container (IBC)** hanya menampilkan kontainer milik gudang yang dipilih, dan tanggal opname tidak dapat dimundurkan (minimal hari ini). Selisih antara stok fisik dan catatan sistem tercatat sebagai **surplus** (lebih) atau **shortage** (kurang); selisih dalam batas toleransi 3% disetujui otomatis, di luar itu menunggu persetujuan. Apabila pengajuan **ditolak**, opname hitung biasa dikembalikan ke status **COUNTED** untuk dihitung ulang, sedangkan koreksi atas opname yang telah selesai dikembalikan ke status **CLOSED** tanpa mengubah saldo.

> **Cara perhitungan System Quantity:** stok menurut sistem dihitung sebagai rangkaian kejadian (event): **ditambah** setiap stok masuk (received_qty) dan **dikurangi** setiap pengeluaran (aktual loading outstock) sesuai urutan waktu pencatatan; pada setiap **opname berstatus CLOSED**, saldo **diatur ulang (rebaseline)** menjadi **Physical_Qty** hasil hitung fisik. Opname berstatus DRAFT, COUNTED, atau CLOSED_PENDING **tidak** mengubah saldo. Nilai dapat bernilai negatif sebelum dilakukan opname, dan hitung fisik berikutnya akan menyelaraskannya.

> **Cara pembacaan log operasional UCO (detail gudang):** setiap baris menampilkan **waktu pencatatan lengkap (tanggal dan jam:menit:detik)**, jenis operasi (Instock, Outstock, Opname, Ampas/Dregs), kolom **Keterangan** (nomor dokumen/kontainer/POO), serta kolom Qty Masuk, Qty Keluar, Ampas, dan Saldo. Urutan tampilan dari yang **terbaru ke terlama** (menurun); baris dengan operasi **Opname hanya ditampilkan bila opname telah selesai (CLOSED)**.

> **Cara perhitungan selisih opname:** selisih = stok fisik − stok sistem. Selisih positif menjadi **Surplus**; selisih negatif menjadi **Shortage**. Selisih di bawah **3%** dari stok sistem disetujui otomatis; di atas itu menunggu persetujuan. Waktu acuan penyelesaian opname menggunakan **waktu persetujuan (Closed_At)**.

[Sisipkan tangkapan layar: Monitoring Stock per gudang]

### 7.2 Logistik UCO

- **Master Container** — Data induk tangki/kontainer penyimpanan di tiap gudang.
- **Master POO (Point of Origin)** — Data titik pengumpulan UCO: penanggung jawab, alamat, koordinat, jenis dan harga UCO, serta rekening. Kolom **Nearest Warehouse** (gudang terdekat) terisi otomatis dari data gudang operasional; **Type** (SUPPLIER / MITRA) dan **Price Unit** (L / KG / TON / KEMASAN / RP) dipilih dari menu pilihan.
- **Master Kemasan dan Master Status** — Data induk kemasan dan daftar status pekerjaan yang dipakai pada Work Order.
- **Work Order (WO)** — Pembuatan perintah kerja penjemputan/pengumpulan per POO lengkap dengan status pengerjaannya. Tanggal WO dikunci ke tanggal hari ini (tidak dapat memilih tanggal lain).
- **Pickup Order (PO)** — Permintaan penjemputan UCO: jenis, cakupan wilayah, paket, jumlah, harga, dan penugasan armada. Nomor PO kini tampil pada daftar; kolom **Route/Coverage** dan **Site** terisi otomatis dari data POO namun tetap dapat disunting.
- **Route Mapping** — Perhitungan, penyimpanan, dan tampilan peta rute penjemputan optimal dengan beberapa titik persinggahan. Jumlah pickup dan total berat terisi otomatis dari PO yang dipilih.
- **Surat Jalan (Delivery Order)** — Dokumen pengiriman. Setelah PO dipilih, status otomatis **PROGRESS** dan dapat diubah pada halaman pembaruan. Pengemudi menutup DO dengan mengisi jumlah aktual, koordinat lokasi, tanda tangan penerima, foto bukti serah terima, dan foto timbangan. Apabila status Surat Jalan telah **DONE**, dokumen menjadi **terkunci** dan tidak dapat diubah lagi (halaman pembaruan tampil hanya-baca); pilihan pembatalan menggunakan label **CANCELLED**.
- **Inbound (Barang Masuk)** — Pencatatan penerimaan stok UCO per gudang/kontainer: sumber, volume diterima, harga, total, dan tolakan; termasuk form penimbangan masuk. Alokasi ke **kontainer wajib diisi** dan total alokasi harus sama dengan volume yang diterima; sistem menolak pengisian yang melebihi kapasitas kontainer. Volume **ampas (dregs)** dicatat terpisah pada tab Ampas dan tidak mengurangi volume UCO yang diterima.
- **Outbound (Barang Keluar)** — Pengelolaan pengiriman keluar. Menu Outstock menampilkan daftar lengkap dengan tombol **Buat Draft**. Prosesnya meliputi muat barang (loading), serah terima dengan bukti foto, penetapan **Harga Transfer per liter beserta dokumen pendukung**, dan cetak surat jalan. Pada saat loading, sistem **menolak** pengeluaran apabila stok menurut sistem kontainer **0 atau kurang**, atau apabila jumlah yang dimuat **melebihi** stok sistem kontainer tersebut (tanpa menyimpan data). Pada tahap serah terima, satu total aktual dibagikan ke baris-baris kontainer secara proporsional, dan sistem memeriksa ulang agar jumlah yang dibagikan tidak melebihi stok sistem tiap kontainer.
- **Settlement dan Acquisition Cost** — Penyelesaian biaya per rute (jarak kilometer dan persetujuannya) serta perbandingan biaya rencana vs aktual per pengiriman dengan unggahan nota.

> **Alur Settlement:** rute yang baru dibuat langsung masuk halaman **Settlement** dengan status **PENDING_SETTLEMENT**. Pengguna mitra/operasional mengisi rincian biaya rute (BBM, tol, parkir, biaya driver, biaya lapangan, dan biaya tanpa nota) beserta bukti berkas, lalu status menjadi **SETTLEMENT_PENDING**. Pada menu **Settlement Cost** (Finance), petugas menyetujui (status **SETTLED**) atau menolak (kembali ke **PENDING_SETTLEMENT**). **Pickup Cost** mencatat pencairan uang muka kas (rekening tujuan, nominal, dan bukti transfer); **Acquisition Cost** mencatat pembayaran ke POO atas UCO yang dibeli (nominal dan qty aktual, bukti transfer kini tampil pada halaman detail).

> **Cara perhitungan biaya rute (mode Manual):** BBM = (jarak ÷ konsumsi kendaraan) × harga bahan bakar; biaya operasional = parkir + tol + biaya driver + biaya lapangan; total biaya = BBM + tol + biaya operasional; **total keseluruhan = total biaya + APC**.

> **Cara perhitungan biaya rute (mode Lumpsum):** total = biaya lumpsum + APC.

> **Cara perhitungan kapasitas kontainer:** stok terisi mengikuti **System Quantity** (perhitungan kejadian seperti pada bagian 7.1); **sisa kapasitas = volume kontainer − stok terisi**; sistem menolak pengisian yang melebihi sisa kapasitas.

> **Cara perhitungan Harga Transfer pada laporan:** nilai Harga Transfer dan Pendapatan pada Laporan Keuangan diambil dari **Harga Transfer per liter** yang diisi pada dokumen outstock yang selesai, bukan dari harga pokok pembelian. Dokumen outstock lama yang belum memiliki harga transfer bernilai **nol**.

> **Pembukuan jurnal biaya langsung:** saat settlement disetujui (SETTLED), rincian biaya yang telah dipetakan dibukukan ke jurnal keuangan mitra sebagai **Biaya Langsung (Direct Costs)** — BBM ke akun **52001** dan Parkir ke akun **52003**; rincian biaya yang belum dipetakan ke akun tidak dibukukan.

[Sisipkan tangkapan layar: Route Mapping dengan peta rute]

---

## 8. Menu Performance: Kinerja

Menu Performance menyajikan ukuran capaian kinerja tim sales dan layanan purna jual.

- **KPI** — Indikator kinerja utama per periode: bobot, target, realisasi, dan departemen penanggung jawab.
- **Master Target** — Matriks target bulanan untuk setiap sales.
- **Tiering** — Tingkatan (tier) anggota/sales sebagai dasar benefit dan evaluasi.
- **Team Performance** — Kartu skor bulanan per PIC: capaian deal, capaian purna jual, performa terhadap standar, status (Above/Below Target), dan peringkat.
- **Pipeline Leads** — Corong konversi prospek per sales: dari total leads, respons, kualifikasi, pertemuan pertama/kedua, hingga deal atau batal.
- **Ratio Pipeline Leads / Ratio Pipeline Deal** — Rasio konversi antartahap sebagai ukuran efektivitas sales.
- **Aftersales Profit Mitra** — Perbandingan proyeksi dan realisasi bagi hasil per gudang, termasuk indikator surplus/minus dan persentase performa.
- **Aftersales Handling Complain** — Pemantauan penanganan keluhan mitra: jumlah tiket keluhan, terselesaikan, tertunda, dan persentase keberhasilan penanganan.

> **Cara perhitungan Team Performance:** tiga komponen — capaian deal (jumlah deal ÷ target × 100), capaian purna jual (bagi hasil ÷ proyeksi × 100), dan capaian penanganan keluhan (keluhan selesai ÷ total keluhan × 100) — dirata-ratakan menjadi satu nilai performa. Nilai **≥ 70** berstatus **Above Target**; di bawah 70 berstatus **Below Target**.

> **Cara perhitungan jenjang (Tiering):** pencapaian berturut-turut (streak) menentukan jenjang: lebih dari 3 bulan beruntun di atas target = **GOLD**; tepat 3 bulan = **BRONZE**; selain itu = **SILVER**. Streak dihitung ulang saat performa berada di bawah target.

> **Cara perhitungan rasio pipeline:** setiap rasio = jumlah prospek pada tahap tersebut ÷ total prospek × 100.

> **Cara perhitungan penanganan keluhan:** persentase keberhasilan = keluhan selesai ÷ total keluhan × 100; keluhan belum selesai = keluhan berjalan + keluhan terbuka.

> Catatan: menu **KPI** mencatat bobot, target, dan realisasi sebagai data; tidak ada skor akhir yang dihitung otomatis oleh sistem.

[Sisipkan tangkapan layar: Team Performance bulanan]

---

## 9. Menu Support: Dukungan Pengguna

Menu Support adalah kanal bantuan dan informasi bagi pengguna aplikasi.

### 9.1 Ticketing

Alur pelaporan kendala yang tertib dan terekam:

1. **New Ticket** — Buat tiket baru: jenis, kategori, subjek, deskripsi, tanggal yang diusulkan, dan lampiran bila ada.
2. **Ticket Open** — Tiket aktif ditanggapi oleh petugas; percakapan dan status progres tercatat. Tiket ditutup disertai catatan penyelesaian.
3. **Ticket History** — Arsip tiket selesai beserta seluruh riwayat responsnya.

[Sisipkan tangkapan layar: Form New Ticket]

### 9.2 Chat

Percakapan dua arah antara administrator dan mitra, ditampilkan seperti aplikasi perpesanan dengan penanda pesan belum dibaca dan tanda pesan telah dibaca.

### 9.3 Announcement

Pembuatan pengumuman dengan status draf atau final. Saat pengumuman difinalkan, isinya otomatis dikirim ke seluruh ruang chat admin–mitra dan memunculkan notifikasi.

### 9.4 Broadcast

Publikasi siaran informasi umum berjudul, jenis, deskripsi ringkas/panjang, tanggal berlaku, dan dokumen terlampir.

### 9.5 Panduan

Pusat unduhan dokumen panduan operasional. Dokumen panduan (termasuk dokumen ini) diterbitkan dengan nomor versi, tanggal berlaku, dan catatan perubahan (changelog), serta disertai notifikasi rilis kepada pengguna.

---

## 10. Menu Branding: Pemasaran

- **Marketing** — Pencatatan kegiatan pemasaran per mitra beserta rekapan (rekap marketing).
- **Promosi** — Pengajuan usulan promosi, termasuk pembuatan RAB (rencana anggaran biaya) voucher promosi.
- **Monitoring Promosi** — Pemantauan jalannya promosi dan tindak lanjutnya.
- **Approval Promosi** — Persetujuan usulan promosi dan evaluasi hasilnya melalui proses persetujuan bertingkat.

> **Cara perhitungan evaluasi pemasaran:** biaya per prospek (CPL) = anggaran ÷ jumlah prospek; tingkat konversi = jumlah deal ÷ jumlah prospek × 100; biaya per akuisisi (CPA) = anggaran ÷ jumlah deal; estimasi pendapatan = nilai investasi HUB × jumlah deal; **ROI = (pendapatan − anggaran) ÷ anggaran × 100**.

---

## 11. Menu Dashboard: Pemantauan

Kumpulan halaman ringkasan visual untuk memantau kondisi bisnis tanpa membuka data satu per satu.

### 11.1 Dashboard After Sales

Ringkasan layanan purna jual, meliputi:

- Jumlah gudang mitra menurut status: beroperasi, persiapan, dan tutup.
- **Pemantauan kontrak**: aktif, segera berakhir (kurang dari tiga bulan), dan sudah berakhir.
- Jumlah tiket terbuka dan selesai, kinerja respon (rata-rata waktu penyelesaian), permintaan dukungan menurut kategori, widget kinerja, serta daftar keluhan.

> Pembaruan: ketika filter gudang **tidak dipilih**, data dihitung untuk **seluruh gudang terdaftar**; bagi pengguna mitra, cakupannya hanya gudang milik mitra tersebut. Tabel **Net Profit per Gudang** menampilkan **satu baris per gudang terdaftar** (termasuk gudang tanpa data dengan nilai 0) dan disajikan dengan komponen **DataTables** berjendela tetap yang dapat **digulir dan berpaginasi**. Filter **Pilih Gudang** kini dapat **dicari (searchable)**, dan filter **Region** dihapus. Widget **Penilaian Kinerja** dan **Daftar Keluhan** masih menunggu kelengkapan data.

> **Cara perhitungan MTTR / MTTF:** MTTR (rata-rata waktu penyelesaian gangguan) = rata-rata jam dari munculnya gangguan hingga tindakan penyelesaiannya; MTTF (rata-rata jeda antargangguan) = rata-rata jam antara dua gangguan pada tiket yang sama. **Pemantauan kontrak** menggolongkan kontrak **segera berakhir** bila sisa masa berlaku kurang dari **3 bulan**.

[Sisipkan tangkapan layar: Dashboard After Sales]

### 11.2 Dashboard Kemitraan (Membership)

Ringkasan populasi mitra: jumlah mitra menurut status dan perkembangannya dari waktu ke waktu. Filter **Pilih Gudang** dapat dicari (searchable); filter Region tidak ditampilkan.

> **Cara perhitungan ringkas:** estimasi uang muka = jumlah mitra (MOU) × nilai DP kemitraan; rasio corong = jumlah mitra tahap saat ini ÷ jumlah mitra tahap sebelumnya × 100; estimasi pendapatan = total proyeksi pendapatan (skenario rendah) untuk 12 bulan ke depan.

### 11.3 Dashboard Financial Insight

Wawasan finansial ringkas: pendapatan, volume UCO, dan tren keuangan utama dalam satu halaman. Ketika filter gudang tidak dipilih, seluruh angka dihitung dari **seluruh gudang terdaftar** (bagi pengguna mitra, hanya gudang miliknya). Filter **Pilih Gudang** dapat dicari (searchable).

> **Cara perhitungan:** ROI = EBITDA ÷ total investasi × 100; margin = laba bersih ÷ pendapatan × 100.

### 11.4 Dashboard Compliance

Ringkasan tindak lanjut perbaikan (corrective action) atas temuan audit gudang. Filter **Pilih Gudang** dapat dicari (searchable); filter Region tidak ditampilkan.

### 11.5 Dashboard Survey

Statistik survei lokasi: jumlah lokasi tersurvei, lokasi menunggu lebih dari sembilan puluh hari, sebaran per kota, dan daftar lokasi alternatif terbaru.

### 11.6 Dashboard Profit Sharing

Visualisasi bagi hasil lintas gudang dan periode sebagai pelengkap laporan pada menu Keuangan.

---

## 12. Menu Mitra Management: Siklus Keanggotaan

Menu ini mengatur kejadian-kejadian penting menjelang atau setelah masa kerja sama berakhir.

### 12.1 Renewal (Perpanjangan)

Kontrak yang mendekati masa berakhir ditampilkan pada daftar renewal. Alur perpanjangan:

1. Pilih mitra yang akan diperpanjang. Sistem menampilkan **Nomor Kontrak Kemitraan** sesuai data mitra, pilihan **Jangka Waktu Perpanjangan yang Diajukan (1 Tahun, 3 Tahun, atau 5 Tahun)**, serta daftar aset/barang gudang mitra sebagai bahan peninjauan.
2. Klik **Preview** untuk melihat pratinjau surat permohonan perpanjangan, lalu **Submit** — sistem membuat surat permohonan perpanjangan secara otomatis: **nomor kontrak kemitraan tercantum, jangka waktu yang diajukan tercantum sebagai teks (1/3/5 Tahun), dan surat ditandatangani dengan tanda tangan dari halaman profil**.
3. Pengajuan diproses pada menu **Approval Renewal**. Setelah disetujui pada seluruh tingkat, status perpanjangan menjadi **Disetujui** dan masa kontrak diperpanjang sesuai lama perpanjangan yang dipilih (tersimpan sebagai lama perpanjangan).

### 12.2 Pemutusan Mitra (Termination)

Alur pengakhiran kerja sama:

1. Pilih mitra; sistem menampilkan **Nomor Kontrak Kemitraan** sesuai data mitra dan daftar aset/barang gudang mitra. Pilih **Jenis Terminasi** (Berakhir Masa Kontrak, Pengakhiran oleh Mitra, Pengakhiran oleh Perusahaan, atau Kesepakatan Bersama), isi **Alasan Terminasi**, dan isi **Tanggal Efektif Terminasi**.
2. Klik **Preview** untuk melihat pratinjau surat, lalu **Submit** — sistem membuat surat pemberitahuan exit/terminasi secara otomatis: jenis terminasi yang dipilih ditandai pada surat, alasan tercantum, **tanggal efektif terminasi tercantum pada surat**, dan surat ditandatangani dengan tanda tangan dari halaman profil.
3. Pengajuan diproses pada menu **Approval Pemutusan Mitra**. Setelah disetujui, status mitra menjadi **Terminated**, **tanggal efektif terminasi tersimpan**, dan gudang terkait dinonaktifkan.

### 12.3 Transfer Kepemilikan

Pengalihan keanggotaan kepada pihak baru.

1. Pilih mitra saat ini dan mitra baru; sistem menampilkan **Nomor Kontrak Kemitraan** sesuai data mitra beserta daftar aset/barang gudang mitra.
2. Isi **Tanggal Efektif Transfer**, **Status Kontrak Setelah Transfer (ACTIVE atau INACTIVE)**, dan **Biaya Administrasi Transfer**.
3. Periksa pratinjau dokumen transfer (memuat nomor kontrak, tanggal efektif, status kontrak, biaya administrasi, dan **tanda tangan pemohon dari halaman profil**), lalu finalisasi pengajuan.
4. Pengajuan diproses pada **Approval Transfer Kepemilikan**. Setelah disetujui, hasil transfer, status kontrak, biaya, dan tanggal efektifnya tersimpan pada data pengalihan.

[Sisipkan tangkapan layar: Halaman Transfer Kepemilikan]

---

## 13. Menu Training: Pelatihan

Menu Training mengelola kegiatan peningkatan kapasitas mitra secara menyeluruh.

- **Training Application** — Mitra mengajukan pelatihan: nama pelatihan, daftar peserta (nama, jabatan, unit, surel), dan estimasi biaya.
- **Approval Training** — Pengajuan ditelaah, disetujui, dan ditetapkan pembicara serta materinya.
- **Training List** — Daftar pelatihan berjalan sesuai target: nama, tanggal, biaya, metode, lokasi, dan status.
- **Attendance** — Presensi peserta per sesi; kehadiran menjadi dasar kelulusan peserta. Halaman presensi hanya menampilkan jadwal **hari ini** milik peserta yang sedang login; pilih baris jadwal lalu klik **Lakukan Presensi**. Baris berwarna hijau apabila peserta sudah tercatat hadir.
- **Certificate** — Penerbitan dan pencetakan sertifikat peserta maupun mentor, termasuk langkah unggah tanda tangan.
- **Materi** — Perpustakaan materi pelatihan dengan hitungan jumlah dilihat dan diunduh. Kolom **View** dan **Download** berupa tautan langsung yang dapat diklik menuju berkas materi.
- **Penilaian Kepuasan (Satisfaction)** — Kuesioner kepuasan peserta atas pelatihan, dilengkapi bank soal pada **Master Pertanyaan**.
- **Master Materi, Master Training, Master Pertanyaan** — Data induk yang mendukung seluruh proses pelatihan. Pada **Master Training**, kolom Peruntukan, Frekuensi, dan Jadwal dipilih dari menu dropdown; baris baru tidak lagi terisi tanggal atau angka secara otomatis. Pada **Master Pertanyaan**, kolom Peruntukan dipilih dari menu (participant/manager) dan tombol **Save Training** menyimpan perubahan.
- **Report Training** — Rekapitulasi pelatihan per mitra sebagai bahan evaluasi.

[Sisipkan tangkapan layar: Training List]

---

## 14. Menu Asset dan Fasilitas

Menu ini mendampingi proses penyiapan sarana usaha mitra, baik untuk tipe HUB **Booth** maupun **Mobil**.

- **Order Equipment** — Pencatatan pemesanan peralatan per gudang: tanggal order, tanggal penerimaan, dan ceklist penerimaan barang. Nilai **Harga Satuan** terisi awal dari data master fasilitas namun dapat diubah; nilai yang diubah tersimpan khusus untuk mitra tersebut.
- **Asset Management** — Daftar aset yang terpasang di lokasi mitra: jenis aset, jumlah, harga satuan, dan total. Nilai **Qty, Harga Satuan, dan Total** bersifat baca-saja dan mengikuti nilai yang telah dikirim melalui Order Equipment. Kolom ceklist ketersediaan tidak lagi ditampilkan karena pengelolaannya berada pada modul Fasilitas.
- **Fasilitas Booth / Fasilitas Mobil** — Status pengiriman barang benefit kemitraan: tanggal terkirim, status kiriman, tanggal diterima, serta jumlah dan harga barang. Mitra melakukan **konfirmasi penerimaan** pada daftar ceklist ini; konfirmasi tersebut menjadi salah satu syarat tahapan instalasi pada proses onboarding.

> Hubungan proses: Order Equipment dan konfirmasi Fasilitas akan otomatis memajukan tahap Instalasi pada proses persetujuan mitra apabila seluruh item telah terpenuhi.

[Sisipkan tangkapan layar: Checklist Fasilitas Booth]

---

## 15. Profil Saya

Halaman profil menampilkan dan mengelola data akun pengguna yang sedang login:

- Data diri: nama, kontak, surel, alamat, kota, jabatan, dan area kerja.
- Data rekening (nama bank, nomor, dan nama pemilik) yang digunakan untuk pembayaran, misalnya bagi hasil.
- Nama pengguna (username) bersifat tetap dan tidak dapat diubah dari halaman ini.
- **Tanda Tangan** — pengguna dapat menggambar tanda tangan pada halaman ini; tanda tangan tersimpan dan otomatis digunakan pada dokumen yang dihasilkan sistem (misalnya surat perpanjangan dan surat pengakhiran).

Disarankan meninjau kebenaran data rekening secara berkala agar pembayaran tidak terkendala.

---

## 16. Glosarium

| Istilah | Arti |
| --- | --- |
| Lead / Prospek | Data calon mitra yang belum atau sedang dalam proses pendekatan penjualan |
| MOU | Memorandum of Understanding, naskah kesepahaman awal sebelum PKS |
| PKS | Perjanjian Kerja Sama antara GEU dan mitra |
| DP | Uang muka (Down Payment) dalam proses penerbitan tagihan kemitraan |
| Onboarding | Rangkaian proses dari pendaftaran hingga mitra resmi aktif |
| Renewal | Perpanjangan masa kerja sama |
| Termination | Pengakhiran kerja sama |
| Transfer Membership | Pengalihan keanggotaan kepada pihak baru |
| UCO | Used Cooking Oil atau minyak jelantah, komoditas utama rantai pasok |
| POO | Point of Origin, titik pengumpulan UCO |
| WO | Work Order, perintah kerja penjemputan/pengumpulan |
| PO | Pickup Order, permintaan penjemputan UCO |
| DO / Surat Jalan | Delivery Order, dokumen pengiriman barang |
| Instock / Outstock | Pencatatan barang masuk / barang keluar gudang |
| Stock Opname | Penghitungan fisik persediaan gudang secara berkala |
| Surplus / Shortage | Selisih stok lebih / kurang hasil opname |
| LK | Laporan Keuangan (bulanan/tahunan) |
| CAPEX | Capital Expenditure, belanja modal |
| RAB | Rencana Anggaran Biaya |
| Royalti | Pembayaran balik atas hak kerja sama sesuai perjanjian |
| MTTR / MTTF | Rata-rata waktu penyelesaian gangguan / rata-rata jeda antargangguan pada layanan purna jual |
| PIC | Person In Charge, penanggung jawab |
| SOP | Standard Operating Procedure, prosedur operasional standar |
| COGS | Harga pokok penjualan (Cost of Goods Sold) |
| EBITDA | Laba sebelum bunga, pajak, penyusutan, dan amortisasi |
| ROI | Return on Investment, tingkat pengembalian investasi |
| CPL / CPA | Biaya per prospek / biaya per akuisisi pada evaluasi pemasaran |
| BEP | Break Even Point, titik impas usaha |
| APC | Biaya tambahan pada perhitungan biaya rute penjemputan |

---

*Akhir dokumen. Panduan ini akan diperbarui secara berkala mengikuti perkembangan fitur aplikasi.*
