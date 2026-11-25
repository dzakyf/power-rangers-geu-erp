@if (!isset($GLOBALS['__ga_ticketing_guide_modal_rendered']))
@php $GLOBALS['__ga_ticketing_guide_modal_rendered'] = true; @endphp
<style>
    #gaTicketingGuideModal .ga-guide-nav .nav-link {
        color: #475569;
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 0.375rem 0.375rem 0 0;
        padding: 0.6rem 0.9rem;
    }
    #gaTicketingGuideModal .ga-guide-nav .nav-link.active {
        color: #0d6efd;
        background-color: #ffffff;
        border-color: #dee2e6 #dee2e6 #ffffff;
    }
    #gaTicketingGuideModal .ga-guide-flow {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }
    #gaTicketingGuideModal .ga-guide-step-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 0.375rem;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #1e293b;
    }
    #gaTicketingGuideModal .ga-guide-arrow {
        color: #94a3b8;
        font-size: 0.8rem;
    }
    #gaTicketingGuideModal .ga-guide-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.9rem 1rem;
        background: #ffffff;
        height: 100%;
    }
</style>

<div class="modal fade" id="gaTicketingGuideModal" tabindex="-1" aria-labelledby="gaTicketingGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="gaTicketingGuideModalLabel">
                        <i class="fa fa-book-open text-primary me-2"></i>Panduan Penggunaan GA Ticketing &amp; Work Order
                    </h5>
                    <small class="text-muted">Referensi alur kerja Tiket Operasional GA, Order Pekerjaan &amp; Voucher Pembayaran, serta Work Order.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light bg-opacity-50 p-3 p-md-4">
                <ul class="nav nav-tabs ga-guide-nav mb-3" id="gaGuideTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="ga-guide-tab-overview" data-bs-toggle="tab" data-bs-target="#gaGuideTabOverview" href="#gaGuideTabOverview" role="tab" aria-controls="gaGuideTabOverview" aria-selected="true">
                            <i class="fa fa-compass me-1"></i> Ringkasan &amp; Akses Cepat
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="ga-guide-tab-operational" data-bs-toggle="tab" data-bs-target="#gaGuideTabOperational" href="#gaGuideTabOperational" role="tab" aria-controls="gaGuideTabOperational" aria-selected="false">
                            <i class="fa fa-headset me-1"></i> Tiket Operasional GA
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="ga-guide-tab-voucher" data-bs-toggle="tab" data-bs-target="#gaGuideTabVoucher" href="#gaGuideTabVoucher" role="tab" aria-controls="gaGuideTabVoucher" aria-selected="false">
                            <i class="fa fa-file-invoice-dollar me-1"></i> Order Pekerjaan &amp; Voucher
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="ga-guide-tab-workorder" data-bs-toggle="tab" data-bs-target="#gaGuideTabWorkOrder" href="#gaGuideTabWorkOrder" role="tab" aria-controls="gaGuideTabWorkOrder" aria-selected="false">
                            <i class="fa fa-wrench me-1"></i> Work Order &amp; Sinkronisasi
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="ga-guide-tab-faq" data-bs-toggle="tab" data-bs-target="#gaGuideTabRolesFaq" href="#gaGuideTabRolesFaq" role="tab" aria-controls="gaGuideTabRolesFaq" aria-selected="false">
                            <i class="fa fa-circle-question me-1"></i> Peran, Status &amp; FAQ
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="gaGuideTabContent">
                    {{-- TAB 1: RINGKASAN & AKSES CEPAT --}}
                    <div class="tab-pane fade show active" id="gaGuideTabOverview" role="tabpanel" aria-labelledby="ga-guide-tab-overview">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="ga-guide-card border-primary border-opacity-25">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-primary mb-0"><i class="fa fa-headset me-1"></i> Mode 1: Tiket Operasional GA</h6>
                                        <span class="badge bg-info text-white">OPERATIONAL</span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Digunakan untuk permintaan layanan, keluhan fasilitas, atau perbaikan harian yang tidak membutuhkan alur pencairan voucher ke Finance.
                                    </p>
                                    <ul class="small mb-0 ps-3">
                                        <li>Berbasis <strong>SLA</strong> (<em>Response Due</em> &amp; <em>Resolve Due</em>).</li>
                                        <li>Melewati proses <strong>Triage</strong> &amp; penugasan ke PIC / Tim / Vendor.</li>
                                        <li>Cocok untuk pengajuan cepat, termasuk via scan <strong>QR Point</strong>.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ga-guide-card border-success border-opacity-25">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-success mb-0"><i class="fa fa-file-invoice-dollar me-1"></i> Mode 2: Order Pekerjaan &amp; Voucher</h6>
                                        <span class="badge bg-success text-white">WORK_ORDER_VOUCHER</span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Digunakan untuk pengajuan pekerjaan/perbaikan berbiaya yang memerlukan verifikasi GA, persetujuan berjenjang, hingga pembayaran Finance.
                                    </p>
                                    <ul class="small mb-0 ps-3">
                                        <li>Melewati <strong>Approval Dept Head</strong> (Fase 1) dan <strong>GA Verification</strong>.</li>
                                        <li><strong>Approval Biaya Berjenjang</strong> (Fase 2) sesuai nominal verifikasi.</li>
                                        <li>Menghasilkan <strong>Voucher Pembayaran</strong> yang dapat dicetak &amp; dilunasi Finance.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="ga-guide-card mb-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa fa-wrench text-warning me-1"></i> Peran Work Order (WO)</h6>
                            <p class="small text-muted mb-2">
                                <strong>Work Order</strong> dipakai untuk mengeksekusi pekerjaan teknis atau pekerjaan vendor secara terstruktur, baik yang dibuat dari sebuah Tiket maupun berdiri sendiri:
                            </p>
                            <div class="row g-2 small">
                                <div class="col-md-4"><i class="fa fa-check text-success me-1"></i> Menetapkan Assignee, Tim, atau Vendor serta jadwal <em>Planned</em> vs <em>Actual</em>.</div>
                                <div class="col-md-4"><i class="fa fa-check text-success me-1"></i> Mencatat pemakaian <strong>Material</strong> dan rincian <strong>Biaya</strong> (<code>MATERIAL</code>, <code>LABOR</code>, <code>VENDOR</code>, <code>OTHER</code>).</div>
                                <div class="col-md-4"><i class="fa fa-check text-success me-1"></i> Menyinkronkan status selesai (<code>COMPLETED</code>) ke Tiket terkait (otomatis menjadi <code>RESOLVED</code>).</div>
                            </div>
                        </div>

                        <div class="ga-guide-card">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa fa-sitemap text-primary me-1"></i> Peta Menu Terkait</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 24%;">Menu</th>
                                            <th>Fungsi Utama</th>
                                            <th style="width: 18%;">Akses Cepat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">Ticketing</td>
                                            <td>Daftar seluruh tiket GA (mode Operasional &amp; Order Voucher), filter status/mode, buat tiket baru, detail/tindak lanjut, dan Scan SLA.</td>
                                            <td><a href="{{ url('hrd-ga-ticket-index') }}" class="btn btn-xs btn-outline-primary">Buka</a></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Work Order</td>
                                            <td>Mengelola daftar Work Order, perubahan status eksekusi, input material &amp; biaya, serta pengajuan approval WO.</td>
                                            <td><a href="{{ url('hrd-ga-work-order-index') }}" class="btn btn-xs btn-outline-primary">Buka</a></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">My Assignments</td>
                                            <td>Antrean khusus tiket yang ditugaskan kepada akun Anda (PIC / Assignee) untuk ditindaklanjuti lebih cepat.</td>
                                            <td><a href="{{ url('hrd-ga-my-assignment-index') }}" class="btn btn-xs btn-outline-primary">Buka</a></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Master Tiket &amp; QR Point</td>
                                            <td>Pengaturan kategori/subkategori, SLA, tim GA, dan titik QR lokasi/aset untuk pelaporan cepat oleh karyawan.</td>
                                            <td>
                                                <a href="{{ url('hrd-ga-ticket-master-index') }}" class="btn btn-xs btn-outline-secondary me-1">Master</a>
                                                <a href="{{ url('hrd-ga-qr-point-index') }}" class="btn btn-xs btn-outline-secondary">QR</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">IT Support &amp; Helpdesk</td>
                                            <td>Portal ticketing terpisah khusus untuk kendala IT/perangkat teknologi informasi.</td>
                                            <td><a href="{{ url('hrd-it-ticket-index') }}" class="btn btn-xs btn-outline-secondary">Buka</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: TIKET OPERASIONAL GA --}}
                    <div class="tab-pane fade" id="gaGuideTabOperational" role="tabpanel" aria-labelledby="ga-guide-tab-operational">
                        <div class="ga-guide-card mb-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa fa-diagram-project text-primary me-1"></i> Alur Status Tiket Operasional GA</h6>
                            <div class="ga-guide-flow mb-2">
                                <span class="ga-guide-step-pill"><span class="badge bg-secondary text-white">1</span> OPEN</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-info text-white">2</span> TRIAGED</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-primary text-white">3</span> ASSIGNED</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-warning text-dark">4</span> IN_PROGRESS / ON_HOLD</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-success text-white">5</span> RESOLVED</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-secondary text-white">6</span> CLOSED</span>
                            </div>
                            <div class="small text-muted">
                                Status tambahan: <span class="badge bg-warning text-dark">REOPENED</span> jika tiket yang sudah selesai perlu dibuka kembali, atau <span class="badge bg-danger text-white">REJECTED</span> jika pengajuan ditolak.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="ga-guide-card">
                                    <h6 class="fw-bold text-primary mb-2">1. Pembuatan Tiket (Pemohon / Admin)</h6>
                                    <ol class="small mb-0 ps-3">
                                        <li class="mb-1">Klik tombol <strong>Tiket Baru</strong> di halaman Ticketing (atau pindai QR Code pada titik <strong>QR Point</strong> agar lokasi &amp; perusahaan terisi otomatis).</li>
                                        <li class="mb-1">Pastikan <strong>Tipe Alur Tiket</strong> diset ke <code>Tiket Operasional GA</code>.</li>
                                        <li class="mb-1">Isi <strong>Subjek</strong>, pilih <strong>Prioritas</strong> (<code>LOW</code>, <code>NORMAL</code>, <code>HIGH</code>, <code>URGENT</code>), <strong>Kategori / Subkategori</strong>, dan <strong>Deskripsi</strong> masalah.</li>
                                        <li class="mb-1">Jika melaporkan mewakili rekan lain, pilih karyawan pada kolom <strong>Atas Nama (on behalf)</strong> dan isi <strong>Telepon Reporter</strong> bila perlu.</li>
                                        <li>Sertakan foto/dokumen lampiran lalu simpan. Tiket terbuat dengan status <code>OPEN</code>.</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ga-guide-card">
                                    <h6 class="fw-bold text-success mb-2">2. Triage, Penugasan &amp; Tindak Lanjut (Tim GA)</h6>
                                    <ol class="small mb-0 ps-3">
                                        <li class="mb-1">Pilih baris tiket lalu klik <strong>Detail / Tindak Lanjut</strong> (atau klik ganda baris pada tabel).</li>
                                        <li class="mb-1">Klik tombol <strong>Triage</strong> untuk menentukan kategori/prioritas GA, target SLA, serta menugaskan ke <strong>Assignee</strong>, <strong>Tim GA</strong>, atau <strong>Vendor</strong>.</li>
                                        <li class="mb-1">PIC yang ditunjuk memantau tugas di menu <strong>My Assignments</strong>, memperbarui progres (<code>IN_PROGRESS</code> / <code>ON_HOLD</code>), menambahkan komentar atau lampiran bukti kerja.</li>
                                        <li>Setelah selesai dikerjakan (atau setelah Work Order terkait selesai), ubah status menjadi <code>RESOLVED</code> lalu lakukan penutupan akhir ke <code>CLOSED</code>.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0 small">
                            <div class="fw-bold mb-1"><i class="fa fa-stopwatch me-1"></i> Pemantauan SLA &amp; Tombol Scan SLA</div>
                            Setiap tiket operasional memiliki batas waktu <strong>Response Due</strong> dan <strong>Resolve Due</strong>. Jika melewati tenggat waktu, badge merah <span class="badge bg-danger text-white">OVERDUE</span> akan tampil di Detail Tiket. Klik tombol <strong>Scan SLA</strong> di halaman daftar Ticketing untuk memperbarui status pemindaian SLA seluruh tiket aktif secara berkala.
                        </div>
                    </div>

                    {{-- TAB 3: ORDER PEKERJAAN & VOUCHER --}}
                    <div class="tab-pane fade" id="gaGuideTabVoucher" role="tabpanel" aria-labelledby="ga-guide-tab-voucher">
                        <div class="ga-guide-card mb-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa fa-route text-success me-1"></i> Alur 2 Fase + Pembayaran Finance</h6>
                            <div class="ga-guide-flow mb-2">
                                <span class="ga-guide-step-pill"><span class="badge bg-warning text-dark">1</span> WAITING_DEPT_HEAD</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-info text-white">2</span> GA_VERIFICATION</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-warning text-dark">3</span> WAITING_BUDGET_APPROVAL</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-primary text-white">4</span> READY_FOR_VOUCHER</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-info text-white">5</span> VOUCHER_CREATED</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-success text-white">6</span> PAID</span>
                            </div>
                            <div class="small text-muted">
                                Jika ditolak oleh Dept Head (<code>REJECTED</code>), pemohon dapat langsung merevisi dan mengirim ulang melalui form <strong>Ajukan Ulang ke Dept Head</strong> pada detail tiket yang sama.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="ga-guide-card">
                                    <h6 class="fw-bold text-dark mb-2">Langkah 1 &ndash; 3: Pengajuan hingga Verifikasi GA</h6>
                                    <ul class="small mb-0 ps-3">
                                        <li class="mb-1"><strong>Pengajuan Pemohon:</strong> Pada form Tiket Baru, pilih mode <code>Order Pekerjaan &amp; Voucher Pembayaran</code>. Isi <em>Estimasi Biaya Awal</em> dan <em>Rekomendasi Vendor</em> (opsional). Tiket masuk ke status <code>WAITING_DEPT_HEAD</code>.</li>
                                        <li class="mb-1"><strong>Approval Dept Head (Fase 1):</strong> Atasan departemen pemohon menyetujui pengajuan di Approval Inbox. Status berpindah ke <code>GA_VERIFICATION</code>.</li>
                                        <li><strong>GA Verification (Fase 2):</strong> Tim GA melakukan survei/pengecekan harga, lalu mengisi <strong>Nominal Verifikasi GA (wajib)</strong>, <strong>Vendor Final</strong>, dan <strong>Catatan Verifikasi / RAB</strong>. Sistem otomatis menampilkan pratinjau jalur approval biaya sebelum diajukan.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ga-guide-card">
                                    <h6 class="fw-bold text-dark mb-2">Langkah 4 &ndash; 6: Approval Biaya, Voucher &amp; Pelunasan</h6>
                                    <ul class="small mb-0 ps-3">
                                        <li class="mb-1"><strong>Approval Biaya Berjenjang (<code>WAITING_BUDGET_APPROVAL</code>):</strong>
                                            <ul class="ps-3 mt-1 mb-1">
                                                <li><code>&lt; Rp 3.000.000</code> : HCGA Manager</li>
                                                <li><code>Rp 3.000.000 &ndash; Rp 5.000.000</code> : + FAT Manager &amp; VP FAT</li>
                                                <li><code>&gt; Rp 5.000.000</code> : + BOD</li>
                                            </ul>
                                            Jika diminta revisi, tiket kembali ke <code>GA_VERIFICATION</code>; pengajuan ulang langsung menuju Approval Biaya tanpa mengulang approval Dept Head.
                                        </li>
                                        <li class="mb-1"><strong>Terbitkan Voucher (<code>READY_FOR_VOUCHER</code>):</strong> Tim GA memilih Vendor Master (bank &amp; rekening terisi otomatis), metode bayar (<code>TRANSFER</code> / <code>CASH</code> / <code>GIRO</code>), nominal (maksimal sebesar Nominal Verifikasi), jatuh tempo, opsional tautan <strong>Work Order</strong>, serta lampiran invoice/penawaran.</li>
                                        <li><strong>Cetak &amp; Pelunasan (<code>VOUCHER_CREATED</code> &rarr; <code>PAID</code>):</strong> Gunakan tombol <strong>Cetak Voucher</strong> untuk arsip/penyerahan ke Finance hingga status berubah menjadi <code>PAID</code>.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mb-0 small">
                            <i class="fa fa-rotate me-1"></i> <strong>Tips Sinkronisasi Approval:</strong> Apabila approver sudah menyetujui atau menolak melalui menu <em>Approval Inbox</em> namun status pada Detail Tiket masih tertahan di <code>WAITING_DEPT_HEAD</code> atau <code>WAITING_BUDGET_APPROVAL</code>, klik tombol <strong>Sync Status Approval</strong> di halaman Detail Tiket.
                        </div>
                    </div>

                    {{-- TAB 4: WORK ORDER & SINKRONISASI --}}
                    <div class="tab-pane fade" id="gaGuideTabWorkOrder" role="tabpanel" aria-labelledby="ga-guide-tab-workorder">
                        <div class="ga-guide-card mb-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa fa-gears text-primary me-1"></i> Siklus Status Work Order</h6>
                            <div class="ga-guide-flow mb-2">
                                <span class="ga-guide-step-pill"><span class="badge bg-secondary text-white">1</span> DRAFT / OPEN</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-warning text-dark">2</span> WAITING_APPROVAL</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-success text-white">3</span> APPROVED</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-primary text-white">4</span> ASSIGNED</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-warning text-dark">5</span> IN_PROGRESS</span>
                                <i class="fa fa-arrow-right ga-guide-arrow"></i>
                                <span class="ga-guide-step-pill"><span class="badge bg-success text-white">6</span> COMPLETED</span>
                            </div>
                            <div class="small text-muted">
                                Status akhir alternatif: <span class="badge bg-danger text-white">REJECTED</span> jika approval ditolak, atau <span class="badge bg-dark text-white">CANCELLED</span> jika pekerjaan dibatalkan.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="ga-guide-card">
                                    <h6 class="fw-bold text-primary mb-2">Cara Membuat &amp; Menjalankan Work Order</h6>
                                    <ol class="small mb-0 ps-3">
                                        <li class="mb-1"><strong>Dari Detail Tiket:</strong> Klik <strong>Buat Work Order</strong> agar WO otomatis terhubung ke nomor tiket tersebut (tersedia selama tiket belum <code>CLOSED</code>, <code>REJECTED</code>, atau <code>PAID</code>).</li>
                                        <li class="mb-1"><strong>Dari Menu Work Order:</strong> Klik <strong>Work Order Baru</strong> untuk membuat WO mandiri atau menautkannya ke tiket tertentu.</li>
                                        <li class="mb-1"><strong>Approval WO:</strong> Jika diperlukan persetujuan biaya/pekerjaan WO, klik <strong>Ajukan Approval</strong> pada Detail WO dan gunakan <strong>Sync Status Approval</strong> bila perlu.</li>
                                        <li class="mb-1"><strong>Pengajuan Voucher ke Finance:</strong> Setelah WO berstatus <code>APPROVED</code> (atau <code>ASSIGNED</code> / <code>IN_PROGRESS</code> / <code>COMPLETED</code>) dan total biaya &gt; 0, klik <strong>Ajukan Voucher Finance</strong> di Detail WO untuk menerbitkan Voucher Pembayaran (<code>FINANCE_SUBMITTED</code>) ke antrean Finance dan mencetaknya lewat <strong>Cetak Voucher</strong>.</li>
                                        <li><strong>Eksekusi:</strong> Gunakan form <strong>Ubah Status Work Order</strong> untuk memperbarui progres secara bertahap hingga <code>COMPLETED</code>.</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ga-guide-card">
                                    <h6 class="fw-bold text-success mb-2">Pencatatan Material, Biaya &amp; History</h6>
                                    <ul class="small mb-0 ps-3">
                                        <li class="mb-1"><strong>Tab Material:</strong> Menampilkan daftar item material yang digunakan beserta kuantitas, harga satuan, total, dan nomor <em>Stock Request</em>.</li>
                                        <li class="mb-1"><strong>Form &amp; Tab Biaya:</strong> Selama WO belum <code>COMPLETED</code> atau <code>CANCELLED</code>, gunakan form <strong>Tambah Biaya</strong> dengan tipe <code>MATERIAL</code>, <code>LABOR</code>, <code>VENDOR</code>, atau <code>OTHER</code>. Total biaya otomatis terakumulasi di ringkasan WO.</li>
                                        <li><strong>Tab History:</strong> Mencatat seluruh riwayat perpindahan status, waktu perubahan, pelaku, dan catatan pekerjaan.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0 small">
                            <div class="fw-bold mb-1"><i class="fa fa-link me-1"></i> Aturan Sinkronisasi Otomatis Work Order ke Tiket</div>
                            <ul class="mb-0 ps-3">
                                <li>Saat mengubah status Work Order menjadi <code>COMPLETED</code>, kolom <strong>Catatan</strong> (hasil pekerjaan) <strong>wajib diisi</strong>.</li>
                                <li>Begitu Work Order berstatus <code>COMPLETED</code>, status Tiket terkait otomatis berubah menjadi <span class="badge bg-success text-white">RESOLVED</span>.</li>
                                <li>Tiket belum otomatis tertutup penuh: Tim GA tetap perlu membuka <strong>Detail Tiket</strong> untuk verifikasi akhir dan mengubah status dari <code>RESOLVED</code> menjadi <code>CLOSED</code>.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- TAB 5: PERAN, STATUS & FAQ --}}
                    <div class="tab-pane fade" id="gaGuideTabRolesFaq" role="tabpanel" aria-labelledby="ga-guide-tab-faq">
                        <div class="ga-guide-card mb-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa fa-users-gear text-primary me-1"></i> Matriks Peran &amp; Hak Akses</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 24%;">Peran</th>
                                            <th style="width: 24%;">Menu / Permission</th>
                                            <th>Wewenang Utama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">Pemohon / Karyawan</td>
                                            <td><code>SI01013</code> (view, create)</td>
                                            <td>Membuat tiket baru (Operasional / Order &amp; Voucher), memantau progress stepper, dan mengajukan ulang jika ditolak Dept Head.</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Admin / PIC GA</td>
                                            <td><code>SI01013</code> (edit, approve)<br/><code>SI01014</code> (create, edit)</td>
                                            <td>Melakukan Triage, verifikasi biaya &amp; vendor, menerbitkan Voucher, membuat &amp; mengelola Work Order, serta menutup tiket (<code>CLOSED</code>).</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Teknisi / Assignee</td>
                                            <td>My Assignments &amp; Work Order</td>
                                            <td>Mengerjakan tiket/WO yang ditugaskan, mencatat material/biaya, mengisi catatan hasil, dan menandai pekerjaan selesai.</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Approver (Dept Head / HCGA Mgr / FAT / BOD)</td>
                                            <td>Approval Inbox</td>
                                            <td>Menyetujui, meminta revisi, atau menolak pengajuan Order Pekerjaan &amp; Work Order sesuai tier nominal.</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Finance</td>
                                            <td>Payment Voucher</td>
                                            <td>Memproses pembayaran berdasarkan Voucher yang diterbitkan GA hingga status berubah menjadi <code>PAID</code>.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="ga-guide-card">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa fa-circle-question text-danger me-1"></i> Kendala Umum &amp; Solusi (FAQ)</h6>
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light h-100">
                                        <div class="fw-bold text-dark mb-1">1. Tombol Triage, Verifikasi GA, Buat Voucher, atau Buat WO tidak muncul?</div>
                                        <div class="text-muted">Periksa dua hal: pastikan akun Anda memiliki hak akses menu terkait (<code>SI01013</code> untuk Tiket, <code>SI01014</code> untuk Work Order) dan pastikan status tiket saat ini memang berada pada fase tombol tersebut.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light h-100">
                                        <div class="fw-bold text-dark mb-1">2. Approval sudah diproses di Inbox, tetapi status Tiket/WO belum berubah?</div>
                                        <div class="text-muted">Buka halaman Detail Tiket atau Detail Work Order terkait lalu klik tombol <strong>Sync Status Approval</strong> untuk menarik pembaruan status terbaru dari engine approval.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light h-100">
                                        <div class="fw-bold text-dark mb-1">3. Work Order sudah COMPLETED, kenapa tiket belum CLOSED?</div>
                                        <div class="text-muted">Secara desain sistem, WO yang <code>COMPLETED</code> otomatis mengubah tiket menjadi <code>RESOLVED</code>. Tim GA perlu klik <strong>Buka Detail Tiket</strong> untuk memverifikasi hasil dan menutup tiket ke <code>CLOSED</code>.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light h-100">
                                        <div class="fw-bold text-dark mb-1">4. Gagal menyimpan perubahan status Work Order ke COMPLETED?</div>
                                        <div class="text-muted">Pastikan kolom <strong>Catatan</strong> pada form Ubah Status Work Order sudah diisi ringkasan hasil pekerjaan, karena field tersebut wajib untuk status <code>COMPLETED</code>.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top">
                <span class="small text-muted me-auto"><i class="fa fa-circle-info me-1"></i> Klik tab di atas untuk berpindah topik panduan.</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup Panduan</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function activateGuideTab(tabId) {
        if (!tabId) return;
        var selector = '#gaGuideTabs a[href="#' + tabId + '"]';
        var tabTrigger = document.querySelector(selector);
        if (!tabTrigger) return;

        if (window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
            return;
        }

        var links = document.querySelectorAll('#gaGuideTabs .nav-link');
        var panes = document.querySelectorAll('#gaGuideTabContent > .tab-pane');
        Array.prototype.forEach.call(links, function (el) {
            el.classList.remove('active');
            el.setAttribute('aria-selected', 'false');
        });
        Array.prototype.forEach.call(panes, function (pane) {
            pane.classList.remove('show', 'active');
        });
        tabTrigger.classList.add('active');
        tabTrigger.setAttribute('aria-selected', 'true');
        var targetPane = document.getElementById(tabId);
        if (targetPane) {
            targetPane.classList.add('show', 'active');
        }
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('#gaGuideTabs .nav-link') : null;
        if (!link) return;
        var href = link.getAttribute('href') || '';
        if (href.indexOf('#') === 0 && href.length > 1) {
            e.preventDefault();
            activateGuideTab(href.substring(1));
        }
    });

    window.openGaTicketingGuide = function (tabId) {
        var modalEl = document.getElementById('gaTicketingGuideModal');
        if (!modalEl) return false;
        if (tabId) {
            activateGuideTab(tabId);
        }
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
            window.jQuery(modalEl).modal('show');
        }
        return false;
    };
})();
</script>
@endif
