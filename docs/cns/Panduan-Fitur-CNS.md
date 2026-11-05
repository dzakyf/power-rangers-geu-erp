# Panduan Fitur Modul CNS

**Versi Dokumen:** 1.0  
**Tanggal Penyusunan:** 27 Agustus 2026  
**Penyusun:** Tim Sistem Informasi GEU  
**Status:** Draf untuk reviu

---

## Tentang Panduan Ini

Panduan ini disusun sebagai rujukan penggunaan seluruh fitur yang tersedia pada modul **CNS** dalam sistem aplikasi GEU. Modul CNS merupakan pusat pengelolaan dokumen ekspor minyak jelantah (UCO), mulai dari perencanaan kapal, pengisian kontainer, penerbitan dokumen ekspor, hingga pengelolaan sertifikasi keberlanjutan.

Seluruh penjelasan ditulis dengan bahasa formal yang mudah dipahami, termasuk penjelasan logika perhitungan pada bagian yang memerlukannya. Setiap pembahasan fitur disertai gambaran lokasi menu, fungsi utama, serta hubungan antar dokumen. Tanda **[Sisipkan tangkapan layar]** menunjukkan posisi yang direkomendasikan untuk dilengkapi gambar tampilan aplikasi.

---

## Daftar Isi

1. Pendahuluan
2. Gambaran Umum Modul CNS
3. Menu Vessel Schedule
4. Menu Stuffing
5. Menu Export Management
6. Menu Quality Control
7. Menu Sales & Commercial
8. Menu ISCC Sustainability
9. Menu Procurement
10. Menu Inventory
11. Menu Master Data
12. Glosarium

> Catatan: Setelah dokumen dibuka pada Microsoft Word, silakan klik kanan pada area daftar isi lalu pilih _Update Field_ agar nomor halaman tampil dengan benar.

---

## 1. Pendahuluan

### 1.1 Tentang Modul CNS

Modul CNS adalah bagian dari sistem aplikasi GEU yang menangani proses ekspor minyak jelantah (UCO). Modul ini menghubungkan seluruh tahapan kegiatan ekspor, yaitu:

- Perencanaan dan pemantauan jadwal kapal beserta boking pengiriman.
- Pencatatan pengisian kontainer (stuffing) beserta data truk, kontainer, dan segel.
- Penerbitan dokumen ekspor secara berjenjang, mulai dari perintah pengiriman hingga sertifikat asal.
- Pengelolaan data kualitas produk dan pengendalian mutu.
- Pengelolaan penjualan dan pengadaan pendukung.
- Pemenuhan keterlacakan dan sertifikasi keberlanjutan ISCC.
- Pengelolaan stok di pelabuhan dan data induk perusahaan.

Dokumen-dokumen pada modul ini saling berkaitan dan umumnya dibuat secara berjenjang; satu dokumen menjadi dasar pembuatan dokumen berikutnya. Rincian hubungan tersebut dijelaskan pada Bab 2.

### 1.2 Siapa yang Menggunakan Modul Ini

Modul ini digunakan oleh beberapa kelompok pengguna dengan hak akses yang berbeda, antara lain:

- **Administrator**, mengelola data induk perusahaan, persetujuan pengadaan, dan pemantauan menyeluruh.
- **Tim Operasional Ekspor**, mengelola jadwal kapal, stuffing, dan dokumen ekspor.
- **Tim Penjualan & Komersial**, membuat pesanan penjualan (Sales Order).
- **Tim Keberlanjutan (Sustainability)**, mengelola kualitas produk, keterlacakan, dan deklarasi keberlanjutan.
- **Tim Pengadaan**, mengelola permintaan dan pesanan pembelian.

### 1.3 Cara Membaca Panduan

- Urutan bab mengikuti urutan menu pada bilah navigasi atas aplikasi modul CNS.
- Istilah teknis diminimalkan; istilah khusus yang tidak dapat dihindari dijelaskan pada bagian **Glosarium** di akhir panduan.
- Bagian yang menjelaskan perhitungan (terutama pada Bab 8) ditujukan bagi pengguna yang ingin memahami bagaimana data tersusun.

---

## 2. Gambaran Umum Modul CNS

### 2.1 Alur Dokumen Ekspor

Sebagian besar dokumen pada modul CNS dibuat secara berjenjang. Alur pembuatan dokumen ekspor secara ringkas adalah sebagai berikut:

1. **Jadwal Kapal / Boking** menjadi sumber awal; dari sini data kontainer (nomor kontainer, segel, dan berat) dicatat pada menu **Stuffing**.
2. **Shipment Instruction (SI)** dibuat dari data boking setelah seluruh kontainer tercatat. Dokumen SI kemudian difinalisasi menjadi **Final Shipment Instruction (FSI)**.
3. Dari **FSI** dihasilkan dokumen **Packing List** dan **Commercial Invoice**.
4. **Weight Bridge Ticket** dibuat berdasarkan **Packing List**, memuat bukti tiket timbangan setiap kontainer.
5. **Certificate of Origin** dibuat berdasarkan **Weight Bridge Ticket**, memuat rincian kontainer dan segel.
6. **Annex VII** dibuat berdasarkan **FSI** sebagai dokumen pergerakan lintas batas.
7. **Sustainability Declaration** dan **Delivery Note** merupakan dokumen mandiri yang tidak dihasilkan dari dokumen lain.

| Dokumen                          | Dihasilkan dari       | Keterangan                          |
| -------------------------------- | --------------------- | ----------------------------------- |
| Shipment Instruction (SI)        | Boking / Jadwal Kapal | Nomor SI dibuat otomatis            |
| Final Shipment Instruction (FSI) | SI                    | Menjadi kunci dokumen hilir         |
| Packing List (PL)                | FSI                   | Terdapat varian cetak untuk pembeli |
| Commercial Invoice (CI)          | FSI                   | Berstatus draf hingga difinalisasi  |
| Weight Bridge Ticket (WBT)       | Packing List          | Memuat unggahan tiket timbangan     |
| Certificate of Origin (COO)      | Weight Bridge Ticket  | Rincian kontainer dan segel         |
| Annex VII                        | FSI                   | Dokumen pergerakan lintas batas     |
| Sustainability Declaration       | Mandiri               | Tidak terhubung dokumen lain        |
| Delivery Note                    | Mandiri               | Tidak terhubung dokumen lain        |

[Sisipkan tangkapan layar: Bagan alur pembuatan dokumen ekspor]

### 2.2 Peta Menu Modul CNS

Menu utama modul CNS dan sub-menunya adalah sebagai berikut:

1. **Vessel Schedule** — jadwal dan boking kapal.
2. **Stuffing** — pencatatan pengisian kontainer, dengan sub-menu **Stuffing Monitoring**.
3. **Export Management** — kumpulan dokumen ekspor: Shipment Instruction, Final Shipment Instruction, Packing List, Commercial Invoice, Weight Bridge Ticket, Certificate of Origin, Sustainability Declaration, Delivery Note, dan Annex VII.
4. **Quality Control** — pengendalian mutu produk UCO, terdiri dari sub-menu **Quality Control External** dan **Quality Control Internal** yang masing-masing melayani pengujian **Inbound** dan **Outbound**.
5. **Sales & Commercial** — pesanan penjualan (Sales Order).
6. **ISCC Sustainability** — keterlacakan dan sertifikasi keberlanjutan (Traceability).
7. **Procurement** — Purchase Request, Outstanding Purchase Request, dan Purchase Order.
8. **Inventory** — pemantauan stok di Pelindo.
9. **Master Data** — Master Company, Master Supplier, dan Master Consignee.

---

## 3. Menu Vessel Schedule

Menu ini mengelola jadwal dan boking kapal ekspor. Setiap baris data merupakan boking pengiriman yang terikat pada **nomor Order (DO) / Booking Confirmation (BC)**, memuat rencana jumlah kontainer, nama kapal, pelabuhan asal (POL), pelabuhan tujuan (POD), serta tanggal-tanggal penting pengiriman.

### 3.1 Daftar Jadwal Kapal

Kolom yang ditampilkan pada daftar antara lain:

| Nama Kolom                           | Keterangan                                                    |
| ------------------------------------ | ------------------------------------------------------------- |
| Order (DO)/Booking Confirmation (BC) | Nomor boking pengiriman                                       |
| Qty                                  | Jumlah kontainer serta tipe kontainer (20 FT atau 20 ISOTANK) |
| Kapal                                | Nama kapal                                                    |
| POL / POD                            | Pelabuhan muat / pelabuhan bongkar                            |
| Stuffing                             | Tanggal rencana pengisian kontainer                           |
| Checking                             | Tanggal pemeriksaan bea cukai (Checking BC)                   |
| Closing CY                           | Tanggal penutupan container yard                              |
| ETD / ATD                            | Estimasi / aktual keberangkatan kapal                         |
| ETA / ATA                            | Estimasi / aktual kedatangan kapal                            |
| Estimated Day(s) / Actual Day(s)     | Lama pelayaran estimasi / aktual dalam hari                   |
| Note                                 | Catatan                                                       |

Daftar dapat difilter berdasarkan periode tanggal stuffing dan diekspor ke Excel.

### 3.2 Pembuatan Jadwal Kapal Baru

Pengguna dapat membuat jadwal baru melalui tombol **New Schedule** dengan mengisi nomor order, jumlah dan tipe kontainer, nama kapal, POL, POD, serta tanggal Stuffing, Checking BC, Closing CY, ETD, dan ETA. Sistem menolak nomor order yang sudah pernah digunakan (duplikat).

[Sisipkan tangkapan layar: Form pembuatan jadwal kapal baru]

### 3.3 Detail dan Riwayat Perubahan

Dari halaman detail, pengguna dapat memperbarui seluruh data jadwal, termasuk tanggal aktual ATD dan ATA. Apabila nomor order diubah, sistem turut memperbarui dokumen **Shipment Instruction** dan **Packing List** yang masih memakai nomor order yang sama.

Seluruh perubahan tercatat pada bagian **Vessel Schedule Log**, menampilkan pengguna, tanggal, dan jenis tindakan (pembuatan, perubahan data, atau pembaruan stuffing kontainer). Sistem juga menyediakan tautan pelacakan pengiriman kapal (ShipmentLink) untuk memantau posisi kapal secara eksternal.

---

## 4. Menu Stuffing

Menu Stuffing memantau dan mencatat progres pengisian kontainer sesuai jadwal kapal. Satu pengiriman (order) memiliki rencana jumlah kontainer; setiap kontainer dicatat lengkap dengan data truk, kontainer, segel, dan berat.

### 4.1 Daftar Stuffing

Daftar menampilkan seluruh jadwal kapal beserta kolom **Container (Seal)** yang menggabungkan data seluruh kontainer yang telah dicatat. Status progres ditandai dengan warna pada baris:

- **Merah** — pengisian kontainer belum dimulai (belum ada catatan kontainer).
- **Kuning** — pengisian belum lengkap (jumlah kontainer belum sesuai rencana).
- **Hijau** — pengisian lengkap (jumlah kontainer sudah sesuai rencana).

Daftar dapat difilter berdasarkan periode tanggal stuffing dan diekspor ke Excel.

### 4.2 Detail Stuffing

Pada halaman detail, pengguna dapat menambah, mengubah, dan menghapus catatan kontainer. Kolom yang tersedia antara lain:

| Nama Kolom           | Keterangan                                    |
| -------------------- | --------------------------------------------- |
| Jam Awal / Jam Akhir | Waktu pengisian (format HH:MM)                |
| Tanggal              | Tanggal pengisian                             |
| Nopol                | Nomor polisi truk pengangkut                  |
| Kontainer            | Nomor kontainer                               |
| Seal                 | Nomor segel kontainer                         |
| Tare / Netto / Gross | Berat dalam kilogram (Kg) dan metrik ton (MT) |
| Note                 | Catatan                                       |

Sistem menghitung beberapa nilai secara otomatis:

> Netto (MT) = Netto (Kg) / 1.000  
> Gross (Kg) = Tare (Kg) + Netto (Kg)  
> Gross (MT) = Gross (Kg) / 1.000

Jumlah baris kontainer dibatasi sesuai rencana jumlah kontainer pada jadwal kapal. Nomor polisi dapat dipilih dari daftar yang sudah tersedia. Penghapusan baris kontainer dicatat sebagai dibatalkan dan tetap tersimpan untuk keperluan audit.

[Sisipkan tangkapan layar: Detail stuffing kontainer]

### 4.3 Stuffing Monitoring

Menu ini menyajikan seluruh catatan pengisian kontainer dari berbagai order dalam satu daftar yang dapat diubah langsung (inline). Pengguna dapat menambah, mengubah, dan menghapus data kontainer tanpa harus membuka halaman detail per order. Daftar difilter berdasarkan periode tanggal pengisian dan dapat diekspor ke Excel.

---

## 5. Menu Export Management

Menu Export Management menampung seluruh dokumen ekspor. Setiap sub-bab berikut menjelaskan fungsi, kolom utama, dan hubungan antardokumen.

### 5.1 Shipment Instruction (SI)

Dokumen perintah pengiriman ekspor yang dibuat berdasarkan boking/jadwal kapal yang seluruh kontainernya telah tercatat. Saat memilih nomor order, sistem otomatis mengisi nama kapal, tanggal stuffing, ETD/ETA, POL/POD, serta daftar kontainer. Nomor SI dibuat otomatis dengan format **SI/EXP/…/BULAN/TAHUN**.

Aksi yang tersedia antara lain:

- **New SI** — membuat dokumen SI baru.
- **Detail SI** — melihat dan memperbarui data SI.
- **Print SI** — mencetak dokumen berformat PDF dengan kop dan kaki perusahaan.
- **Create Final SI** — memfinalisasi SI menjadi **Final Shipment Instruction**.

Status SI: **CREATED** (draf, masih tampil pada daftar SI) hingga **FINAL** (telah difinalisasi dan berpindah ke daftar FSI).

[Sisipkan tangkapan layar: Daftar Shipment Instruction]

### 5.2 Final Shipment Instruction (FSI)

Dokumen finalisasi SI yang menjadi kunci referensi bagi dokumen ekspor lainnya. Nomor Final SI dibuat otomatis dengan format **FINALSI/EXP/…**.

Aksi yang tersedia antara lain:

- **New Final SI** — memilih SI berstatus CREATED untuk difinalisasi.
- **Print Final SI** — mencetak dokumen berformat PDF.
- **Create Packing List** — membuat Packing List dari FSI.
- **Create Commercial Invoice** — membuat Commercial Invoice dari FSI.

### 5.3 Packing List

Dokumen yang merinci kontainer, segel, serta berat (tare, netto, dan gross) per pengiriman. Packing List dibuat dari FSI dengan memilih nomor Final SI; nomor Packing List dibuat otomatis.

Aksi yang tersedia antara lain:

- **New Packing List** — membuat dari FSI terpilih.
- **Detail Packing List** — melihat dan memperbarui data.
- **Print Packing List** — mencetak dokumen; tersedia varian untuk pembeli.

### 5.4 Commercial Invoice

Faktur komersial ekspor yang memuat kuantitas (MT), harga satuan, jumlah, uang muka, biaya lain, dan total per pengiriman. Dibuat dari FSI; kuantitas otomatis diambil dari total berat bersih kontainer.

Status Commercial Invoice: **CREATED** (draf, masih dapat diubah) hingga **FINAL** (terkunci setelah difinalisasi). Dokumen dapat dicetak untuk keperluan bea cukai maupun untuk pembeli.

### 5.5 Weight Bridge Ticket

Dokumen tiket timbangan pengiriman yang dibuat berdasarkan Packing List. Setiap kontainer dapat dilengkapi dengan **unggahan berkas tiket timbangan** sebagai bukti berat. Nomor Weight Bridge Ticket dibuat otomatis dengan format **WBT-GEU/…**.

[Sisipkan tangkapan layar: Detail Weight Bridge Ticket dengan unggahan tiket]

### 5.6 Certificate of Origin

Sertifikat asal barang ekspor yang dibuat berdasarkan Weight Bridge Ticket. Dokumen memuat asal barang, penerima (consignee), cara pengiriman, jenis pengiriman, kapal dan pelabuhan, kode HS, deskripsi barang, berat bersih, serta rincian nomor kontainer dan segel.

### 5.7 Sustainability Declaration

Deklarasi keberlanjutan yang menyatakan kesesuaian pengiriman dengan skema keberlanjutan ISCC/EU RED, memuat tanggal pengiriman, kuantitas (MT), negara asal, penerima, serta pernyataan kepatuhan terhadap berbagai kriteria keberlanjutan dan emisi gas rumah kaca. Dokumen ini bersifat mandiri dan tidak dihasilkan dari dokumen ekspor lainnya.

### 5.8 Delivery Note

Surat pengantaran yang memuat tanggal pengiriman, penerima, kontrak, produk, total tonase (MT), dan negara asal. Dokumen ini bersifat mandiri.

### 5.9 Annex VII

Dokumen pergerakan lintas batas (Annex VII) sesuai ketentuan peraturan pengangkutan limbah, memuat rantai pengelolaan secara lengkap: produsen, penyelenggara, penerima, hingga tiga pengangkut, fasilitas pemulihan, kode limbah, negara asal/transit/tujuan, dan nomor kontainer. Dokumen dibuat berdasarkan **FSI** dan dapat dicetak beserta contoh format kosongnya.

[Sisipkan tangkapan layar: Halaman Annex VII]

---

## 6. Menu Quality Control

Menu ini mengelola hasil analisis laboratorium kualitas produk UCO. Modul Quality Control terbagi menjadi dua sub-menu utama:
1. **Quality Control External** — mencatat pengujian mutu pada transaksi/sampel pihak eksternal.
2. **Quality Control Internal** — mencatat pengujian mutu pada fasilitas/tangki internal.

Pada masing-masing sub-menu di atas, pengguna dapat membuat dua jenis pemeriksaan mutu (**Jenis QC**):
- **Inbound (Pemeriksaan Barang Masuk)**: menguji 4 parameter standar:
  - **FFA As Palmitic (%)** — kandungan asam lemak bebas sebagai asam palmitat.
  - **FFA As Oleic (%)** — kandungan asam lemak bebas sebagai asam oleat.
  - **MC (%)** — Moisture Content / kadar air.
  - **IMP (%)** — Impurities / kadar kotoran.
- **Outbound (Pemeriksaan Barang Keluar / Ekspor)**: menguji 9 parameter lengkap (4 parameter standar ditambah 5 parameter ekspor):
  - **IV (g I2/100g)** — Iodine Value / bilangan iodium.
  - **Sulphur (ppm)** — kandungan belerang.
  - **Phosphorus (ppm)** — kandungan fosfor.
  - **Total Chlorides (ppm)** — kadar klorida total.
  - **Total Metals (ppm)** — kadar logam total.

Pengambilan sampel dilakukan pada tiga level tangki, yaitu **Top**, **Middle**, dan **Bottom**, di mana sistem secara otomatis menghitung nilai rata-rata (**Average**) untuk seluruh parameter.

Daftar disusun per tanggal dan jenis QC dengan filter aktivitas (**Semua / Inbound / Outbound**). Aksi yang tersedia antara lain:
- **Create** — membuat catatan kualitas baru (Inbound atau Outbound) beserta lampiran CoA/lab.
- **Detail** — melihat rincian dan memperbarui hasil pengujian.
- **Delete** — menghapus catatan kualitas terpilih.
- **Print** — mencetak formulir laporan pengujian berformat Word (`template-form-qc.docx` untuk Inbound dan `template-form-qc-outbound.docx` untuk Outbound).

> Catatan: Catatan kualitas yang dibuat melalui menu **Inventory Pelindo** tidak ditampilkan pada daftar Quality Control, karena keduanya menggunakan satu tabel penyimpanan yang sama namun dipisahkan berdasarkan sumber pembuatannya.

[Sisipkan tangkapan layar: Daftar Quality Control External & Internal]

---

## 7. Menu Sales & Commercial

### 7.1 Sales Order

Menu ini digunakan untuk membuat dan mengelola pesanan penjualan produk UCO. Setiap Sales Order memuat data pembeli, lokasi penjualan, metode pembayaran, rincian barang (deskripsi, kuantitas, harga satuan, dan total), serta ringkasan subtotal, pajak, biaya pengiriman, biaya lain, dan total keseluruhan. Nomor Sales Order dibuat otomatis dengan format **001/SO/BULAN/TAHUN**.

Aksi yang tersedia antara lain:

- **New Sales Order** — membuat pesanan penjualan baru.
- **Detail Sales Order** — melihat rincian pesanan.
- **Print Sales Order** — mencetak dokumen pesanan berformat A4.
- **Export Excel** — mengunduh daftar dalam format Excel.

Sales Order merupakan dokumen penjualan yang berdiri sendiri dan tidak terkait dengan rangkaian dokumen ekspor pada menu Export Management.

[Sisipkan tangkapan layar: Form pembuatan Sales Order]

---

## 8. Menu ISCC Sustainability

Menu ISCC Sustainability pada saat ini memiliki satu fitur utama, yaitu **Traceability**, yang mengelola keterlacakan (traceability) minyak jelantah (UCO) dari titik pengumpulan hingga pengiriman untuk mendukung pemenuhan sertifikasi keberlanjutan ISCC.

Data pada fitur ini berasal dari dua sumber:

- **NATURAL** — data riil titik pengumpulan yang tercatat dari proses operasional (master POO), dirangkai dengan Work Order, Pickup Order, dan Delivery Order.
- **EXTERNAL** — data pengiriman pemasok eksternal yang dihasilkan melalui proses impor dan simulasi, dipecah menjadi fragment-fragment yang mewakili titik pengumpulan.

### 8.1 Halaman Indeks Traceability

Halaman ini menampilkan seluruh data keterlacakan dari kedua sumber dalam satu daftar. Filter yang tersedia:

- **Sumber (Source)** — seluruh sumber, NATURAL, atau EXTERNAL.
- **Kota / Kabupaten** — membatasi data pada kota atau kabupaten tertentu.
- **Rentang Tanggal** — membatasi data berdasarkan tanggal.

Kolom yang ditampilkan antara lain: No, Source, POO Name, Type, City, Province, Qty (Kg), PO Qty, WO Code, WO Date, WO Status, PO Code, PO Date, PO Status, Surat Jalan Code, Surat Jalan Date, Surat Jalan Status, dan **Split** (penanda bahwa satu pengiriman dipecah menjadi beberapa fragment).

Tombol aksi: **Edit Data** (untuk baris EXTERNAL) dan **Print Doc**.

[Sisipkan tangkapan layar: Halaman Indeks Traceability]

### 8.2 Halaman Utama (Rekap) dan Detail Pengiriman

Halaman rekap menampilkan seluruh pengiriman eksternal berstatus **LOCKED** atau **AUDITED**, dengan informasi nama pemasok, lokasi, provinsi, tanggal surat jalan, total kuantitas, status, dan nomor referensi. Dengan klik ganda, sistem menampilkan rincian fragment hasil pecahan pengiriman beserta baris total kuantitas.

### 8.3 Pengeditan Data Fragment

Pengguna dapat melengkapi data fragment, antara lain kuantitas, alamat lengkap, koordinat, catatan dokumentasi, serta **foto dokumentasi** (maksimal tiga foto). Foto dapat diunggah dan dihapus sebagai bukti pendukung sertifikasi.

### 8.4 Alur Status Pengiriman

Status pengiriman eksternal terdiri dari:

1. **PENDING** — pengiriman baru diterima dan belum dipecah.
2. **LOCKED** — pengiriman telah dipecah menjadi fragment dan terkunci.
3. **AUDITED** — pengiriman telah ditandai sebagai diaudit.

Grid hanya menampilkan pengiriman berstatus **LOCKED** atau **AUDITED**.

### 8.5 Mekanisme Pembuatan Data Eksternal

Data eksternal dihasilkan dari proses impor berkas CSV dari aplikasi penjemputan maupun simulasi alur API. Setiap baris rincian surat jalan berstatus **done** dikelompokkan berdasarkan surat jalan dan pemasok menjadi satu pengiriman. Setiap pengiriman diberi nomor referensi unik agar proses impor ulang tidak menghasilkan data ganda.

Pemasok berjenis **SUPPLIER** dipecah menjadi beberapa fragment, sedangkan pemasok berjenis lain (misalnya POO RETAIL, POO COMDEV, dan POO B2B CONTRACT) ditampilkan apa adanya sebagai satu fragment dengan nama dan tanggal asli.

Nama POO pada fragment hasil pecahan diambil secara acak dari pemasok berjenis **selain SUPPLIER** (POO RETAIL, POO COMDEV, POO B2B CONTRACT, Prospect, dan sejenisnya), dengan tetap mengutamakan kesamaan kota atau kabupaten.

### 8.6 Perhitungan dan Logika Kalkulasi

Bagian ini menjelaskan tiga logika utama pembentukan data eksternal.

**a. Fragmentasi Jumlah (Split Quantity)**

- Apabila total kuantitas **kurang dari 225 kg**, pengiriman menjadi satu fragment utuh.
- Apabila total kuantitas kurang dari dua kali batas bawah rentang jenis, pengiriman tetap menjadi satu fragment.
- Apabila tidak, jumlah fragment (n) dihitung dengan rumus:

> n = min( maks(2, ⌈total / mid⌉), 20, ⌊total / min⌋ )

dengan **mid** adalah titik tengah rentang (min + maks) / 2, **min** batas bawah, dan **maks** batas atas rentang.

Jumlah seluruh fragment **selalu sama dengan total kuantitas** pengiriman (keseimbangan massa). Rentang kuantitas per jenis:

| Jenis            | Rentang (kg) |
| ---------------- | ------------ |
| Restoran         | 150 – 750    |
| Hotel            | 300 – 750    |
| IRT              | 5 – 200      |
| Kafe             | 15 – 100     |
| Warung           | 50 – 500     |
| Lembaga          | 25 – 300     |
| SPPG             | 150 – 1.000  |
| Pabrik / Lainnya | 300 – 1.000  |

**b. Penyebaran Tanggal Surat Jalan**

Penyebaran tanggal hanya diterapkan pada pengiriman berjenis **SUPPLIER**, dalam dua tahap:

1. Total kuantitas dipecah menjadi kelompok harian dengan batas **1.200 kg per hari**; jumlah kelompok (n) = ⌈total / 1.200⌉.
2. Tanggal kelompok ke-i (i = 0, 1, …, n−1) dihitung relatif terhadap tanggal surat jalan asli:

> tanggal fragment = tanggal surat jalan + (i − ⌊n/2⌋) hari

Sebaran bersifat simetris di sekitar tanggal surat jalan asli, contoh:

| Jumlah Kelompok (n) | Selisih Hari terhadap Tanggal Surat Jalan |
| ------------------- | ----------------------------------------- |
| 1                   | 0                                         |
| 2                   | −1, 0                                     |
| 3                   | −1, 0, +1                                 |
| 4                   | −2, −1, 0, +1                             |
| 5                   | −2, −1, 0, +1, +2                         |

**c. Penentuan Nama POO (Randomisasi)**

Penamaan POO secara acak hanya diterapkan pada fragment hasil pecahan pengiriman berjenis **SUPPLIER**. Nama diambil dari kumpulan (pool) pemasok aktif berjenis **selain SUPPLIER** dengan urutan prioritas:

1. Pemasok berjenis **selain SUPPLIER** (POO RETAIL, POO COMDEV, POO B2B CONTRACT, Prospect, dan sejenisnya) pada **kota atau kabupaten yang sama**.
2. Pemasok berjenis **selain SUPPLIER** pada **provinsi yang sama**.

Pemilihan bersifat deterministik (hasilnya sama untuk data yang sama). Nama yang terpilih berulang diberi akhiran nomor urut. Fragment yang tidak dipecah memakai nama pemasok asli. Apabila tidak tersedia pemasok berjenis lain pada kota/provinsi yang sama, sistem memakai nama pemasok pengiriman itu sendiri.

### 8.7 Hasil Verifikasi Data

Hasil pemeriksaan data aktual pada modul ISCC Traceability adalah sebagai berikut:

| Uraian                                                                              | Jumlah |
| ----------------------------------------------------------------------------------- | ------ |
| Total pengiriman eksternal                                                          | 10.090 |
| Pengiriman berjenis SUPPLIER                                                        | 593    |
| Pengiriman berjenis lain (POO RETAIL 7.842, POO B2B CONTRACT 1.068, POO COMDEV 587) | 9.497  |
| Total fragment                                                                      | 16.195 |
| Pengiriman SUPPLIER yang dipecah (lebih dari satu fragment)                         | 519    |
| Fragment hasil pecahan dengan nama acak                                             | 6.624  |

Asal nama POO acak (6.278 dari 6.624 nama berhasil dicocokkan ke data induk pemasok; seluruhnya berjenis selain SUPPLIER):

| Asal Nama                             | Jumlah | Persentase |
| ------------------------------------- | ------ | ---------- |
| Kota/kabupaten sama                   | 4.904  | 78,1%      |
| Provinsi sama, kota/kabupaten berbeda | 1.374  | 21,9%      |
| Provinsi berbeda                      | 0      | 0,0%       |

Seluruh nama acak berasal dari pemasok berjenis **selain SUPPLIER**, dengan rincian: POO RETAIL 5.130, POO B2B CONTRACT 1.056, POO COMDEV 82, dan Prospect 10. Tidak ada nama yang diambil dari pemasok berjenis SUPPLIER.

Sebaran selisih hari tanggal surat jalan fragment terhadap tanggal surat jalan asli:

| Selisih Hari                              | Jumlah Fragment | Persentase |
| ----------------------------------------- | --------------- | ---------- |
| Hari yang sama (0)                        | 10.593          | 65,4%      |
| Sebelum tanggal surat jalan (−1 s.d. −63) | 3.097           | 19,1%      |
| Setelah tanggal surat jalan (+1 s.d. +63) | 2.505           | 15,5%      |

Kesimpulan: sebaran tanggal tidak terbatas pada kisaran −1 hingga +1 hari, melainkan menyesuaikan ukuran pengiriman. Dengan batas kelompok harian 1.200 kg, rentang selisih hari menjadi lebih lebar dibandingkan batas sebelumnya (5.000 kg).

---

## 9. Menu Procurement

Menu Procurement mengelola proses pengadaan pendukung kegiatan ekspor.

### 9.1 Purchase Request (PR)

Permintaan pembelian yang diajukan oleh pengguna untuk kebutuhan operasional. Setiap PR memuat nomor dan tanggal PR, pemasok, syarat pembayaran, spesifikasi produk, departemen anggaran, rincian barang, serta ringkasan biaya. Nomor PR dibuat otomatis dengan format **PR-EXP/…/TAHUN**.

Pengajuan PR melalui **alur persetujuan berjenjang** sesuai struktur organisasi:

1. Pengajuan awal berstatus **APPROVAL_UPPER**.
2. Diteruskan sesuai peran penyetuju (misalnya tingkat pimpinan, keuangan, atau umum).
3. Disetujui menjadi **APPROVED** atau ditolak menjadi **REJECTED**.

Aksi yang tersedia antara lain: membuat PR baru, membuka detail (dengan tombol **Approve** dan **Reject**), mencetak PR, dan mengekspor daftar ke Excel. Seluruh keputusan persetujuan tercatat pada riwayat (log).

[Sisipkan tangkapan layar: Daftar Purchase Request]

### 9.2 Outstanding Purchase Request

Menu ini menampilkan PR yang telah **disetujui** namun belum sepenuhnya dikonversi menjadi Purchase Order. Pada halaman detail, pengguna menentukan kuantitas yang akan dipesan per barang beserta harga yang diusulkan, kemudian mengirimkan ke tahap pembuatan **Purchase Order**. Setelah seluruh kuantitas barang tercakup dalam PO, status PR menjadi **CLOSED** dan tidak lagi tampil pada daftar ini.

### 9.3 Purchase Order (PO)

Menu ini menampilkan seluruh Purchase Order yang dibuat dari PR. Halaman detail PO memuat informasi pemasok, ringkasan biaya, riwayat persetujuan, rincian barang, serta **perbandingan penawaran pemasok** (penawaran ke-1 hingga ke-5) sebagai dasar keputusan pemilihan. Dokumen PO dapat dicetak.

---

## 10. Menu Inventory

### 10.1 Inventory Pelindo

Menu ini mencatat pergerakan stok UCO di tangki fasilitas Pelindo sebagai jurnal bulanan. Setiap transaksi bersifat **masuk (inbound)** atau **keluar (outbound)** dengan informasi kuantitas, kuantitas hasil pengukuran (sounding), nomor referensi, lampiran nota timbang, dan catatan. Daftar menampilkan saldo berjalan stok Pelindo per tanggal, dengan baris **Opening Stock** sebagai saldo awal bulan dan jumlah total pada kaki tabel.

Selain mencatat pergerakan stok, pengguna juga dapat melengkapi **hasil pengujian kualitas** (Top, Middle, Bottom) pada transaksi, yang tersimpan terhubung dengan data stok tersebut.

[Sisipkan tangkapan layar: Daftar Inventory Pelindo]

---

## 11. Menu Master Data

Menu Master Data mengelola data induk perusahaan yang digunakan di seluruh dokumen ekspor. Ketiga menu pada kelompok ini menggunakan satu kumpulan data perusahaan yang sama; perbedaan hanya pada tampilan dan peran yang disaring.

### 11.1 Master Company

Daftar lengkap perusahaan mitra, baik yang berperan sebagai penerima barang (consignee/buyer) maupun pemasok (vendor/supplier). Kolom yang ditampilkan antara lain: Company ID, Company Name, Contact Person, City, Phone, Fax, Email, Sales, Payment, Address, PIC, serta kelengkapan berkas (Letterhead, Footer, Logo, Logo Inverse, dan Stamps).

Daftar dapat disaring berdasarkan jenis perusahaan: **Semua**, **Consignee/Buyer**, atau **Vendor/Supplier**. Pengguna dapat membuat perusahaan baru dan mengubah datanya melalui halaman detail, termasuk **mengunggah berkas kop surat (letterhead), kaki surat (footer), logo, dan cap** yang dipakai pada dokumen cetak. ID perusahaan dibuat otomatis dengan format **GEU-COMP-…**.

[Sisipkan tangkapan layar: Daftar Master Company]

### 11.2 Master Supplier

Menu ini menampilkan perusahaan yang berperan sebagai **pemasok (vendor/supplier)**. Data yang dikelola sama dengan Master Company, dengan tampilan yang telah disaring hanya untuk pemasok.

### 11.3 Master Consignee

Menu ini menampilkan perusahaan yang berperan sebagai **penerima barang (consignee/buyer)**. Sama seperti Master Supplier, tampilan telah disaring hanya untuk penerima barang, dan digunakan untuk mengidentifikasi pihak penerima pada dokumen ekspor.

---

## 12. Glosarium

| Istilah            | Arti                                                                                   |
| ------------------ | -------------------------------------------------------------------------------------- |
| UCO                | Used Cooking Oil atau minyak jelantah, komoditas utama rantai pasok                    |
| ISCC               | International Sustainability and Carbon Certification, skema sertifikasi keberlanjutan |
| SI                 | Shipment Instruction, perintah pengiriman ekspor                                       |
| FSI                | Final Shipment Instruction, finalisasi SI dan kunci dokumen hilir                      |
| PL                 | Packing List, daftar kemasan/kontainer pengiriman                                      |
| CI                 | Commercial Invoice, faktur komersial ekspor                                            |
| WBT                | Weight Bridge Ticket, tiket timbangan pengiriman                                       |
| COO                | Certificate of Origin, sertifikat asal barang                                          |
| SD                 | Sustainability Declaration, deklarasi keberlanjutan                                    |
| DN                 | Delivery Note, surat pengantaran                                                       |
| Annex VII          | Dokumen pergerakan lintas batas sesuai ketentuan pengangkutan limbah                   |
| DO / BC            | Delivery Order / Booking Confirmation, nomor boking pengiriman                         |
| POL / POD          | Port of Loading / Port of Discharge, pelabuhan muat / bongkar                          |
| ETD / ATD          | Estimated / Actual Time of Departure, estimasi / aktual keberangkatan                  |
| ETA / ATA          | Estimated / Actual Time of Arrival, estimasi / aktual kedatangan                       |
| Stuffing           | Kegiatan pengisian kontainer                                                           |
| Nopol              | Nomor polisi kendaraan/truk pengangkut                                                 |
| Tare               | Berat kosong kontainer/kendaraan                                                       |
| Netto              | Berat bersih muatan                                                                    |
| Gross              | Berat kotor (tare + netto)                                                             |
| MT                 | Metrik ton (1.000 kg)                                                                  |
| BC                 | Bea Cukai                                                                              |
| CY                 | Container Yard, area penumpukan kontainer                                              |
| FFA                | Free Fatty Acid, kadar asam lemak bebas                                                |
| MC                 | Moisture Content, kadar air                                                            |
| IMP                | Impurity, kadar kotoran                                                                |
| Pelindo            | PT Pelabuhan Indonesia, operator fasilitas pelabuhan                                   |
| POO                | Point of Origin, titik pengumpulan UCO                                                 |
| WO / PO / DO       | Work Order / Pickup Order / Delivery Order (surat jalan)                               |
| Fragment           | Bagian hasil pemecahan satu pengiriman menjadi titik pengumpulan                       |
| NATURAL            | Sumber data riil dari proses operasional (master POO)                                  |
| EXTERNAL           | Sumber data pengiriman pemasok eksternal hasil impor/simulasi                          |
| PR                 | Purchase Request, permintaan pembelian                                                 |
| PO                 | Purchase Order, pesanan pembelian                                                      |
| PS                 | Purchase Summary, ringkasan pembelian/penawaran                                        |
| V1 – V5            | Perbandingan penawaran pemasok pertama hingga kelima                                   |
| Keseimbangan Massa | Prinsip jumlah seluruh fragment sama dengan total kuantitas pengiriman                 |
| Traceability       | Keterlacakan, kemampuan menelusuri asal-usul dan pergerakan komoditas                  |

---

_Akhir dokumen. Panduan ini akan diperbarui secara berkala mengikuti perkembangan fitur aplikasi._
