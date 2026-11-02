@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .ga-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .ga-filter-group,
    .ga-btn-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .ga-filter-group .form-control,
    .ga-filter-group .form-select {
        width: auto;
        min-width: 150px;
    }
    #cleaningTab .nav-link {
        cursor: pointer;
        font-weight: 600;
        padding: 0.55rem 1.1rem;
        border-radius: 0.375rem;
        margin-right: 0.5rem;
        margin-bottom: 0.25rem;
    }
</style>

<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-ga-ticket-index') }}">General Affairs</a></li>
        <li class="breadcrumb-item active"><span class="active">Cleaning Management</span></li>
    </ol>
</div>

<div class="card mt-3">
    <div class="card-header bg-white border-bottom pb-2">
        <ul class="nav nav-pills" id="cleaningTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="task-tab" data-bs-toggle="pill" data-bs-target="#tab-task" data-toggle="pill" href="#tab-task" role="tab" aria-controls="tab-task" aria-selected="true">
                    <i class="fa fa-tasks me-1 me-1"></i> Tugas Harian
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="inspection-tab" data-bs-toggle="pill" data-bs-target="#tab-inspection" data-toggle="pill" href="#tab-inspection" role="tab" aria-controls="tab-inspection" aria-selected="false">
                    <i class="fa fa-clipboard-check me-1 me-1"></i> Inspeksi Supervisor
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="schedule-tab" data-bs-toggle="pill" data-bs-target="#tab-schedule" data-toggle="pill" href="#tab-schedule" role="tab" aria-controls="tab-schedule" aria-selected="false">
                    <i class="fa fa-calendar-alt me-1 me-1"></i> Jadwal Pembersihan
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="area-tab" data-bs-toggle="pill" data-bs-target="#tab-area" data-toggle="pill" href="#tab-area" role="tab" aria-controls="tab-area" aria-selected="false">
                    <i class="fa fa-qrcode me-1 me-1"></i> Master Area &amp; QR
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content" id="cleaningTabContent">
            <!-- TAB 1: TUGAS HARIAN -->
            <div class="tab-pane fade show active" id="tab-task" role="tabpanel" aria-labelledby="task-tab">
                <div class="ga-toolbar">
                    <div class="ga-filter-group">
                        <label class="fw-bold font-weight-bold mb-0">Tanggal:</label>
                        <input type="date" id="filter_task_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        <label class="fw-bold font-weight-bold mb-0 ms-2 ml-2">Status:</label>
                        <select id="filter_task_status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="SCHEDULED">SCHEDULED</option>
                            <option value="IN_PROGRESS">IN_PROGRESS</option>
                            <option value="COMPLETED">COMPLETED</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-info text-white" id="btn-refresh-task">
                            <i class="fa fa-filter me-1 me-1"></i> Filter
                        </button>
                    </div>
                    <div class="ga-btn-group">
                        <button type="button" class="btn btn-sm btn-warning fw-bold font-weight-bold text-dark" id="btn-start-task">
                            <i class="fa fa-qrcode me-1 me-1"></i> Scan QR Mulai Kerja
                        </button>
                        <button type="button" class="btn btn-sm btn-success fw-bold font-weight-bold" id="btn-complete-task">
                            <i class="fa fa-check-circle me-1 me-1"></i> Selesaikan Tugas
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-bold font-weight-bold" id="btn-generate-task">
                            <i class="fa fa-calendar-plus me-1 me-1"></i> Generate Tugas Hari Ini
                        </button>
                    </div>
                </div>
                <div id="grid_task" style="width: 100%; height: 430px; background-color: white; overflow: hidden;"></div>
            </div>

            <!-- TAB 2: INSPEKSI SUPERVISOR -->
            <div class="tab-pane fade" id="tab-inspection" role="tabpanel" aria-labelledby="inspection-tab">
                <div class="ga-toolbar">
                    <div>
                        <h5 class="card-title mb-1 fw-bold font-weight-bold">Riwayat Audit / Inspeksi Kebersihan</h5>
                        <small class="text-muted">Inspeksi berkala atau audit uji petik area. Jika temuan FAILED / NEEDS_IMPROVEMENT, lakukan <strong>Inspeksi Ulang</strong> pada nomor yang sama hingga PASSED.</small>
                    </div>
                    <div class="ga-btn-group">
                        <button type="button" class="btn btn-sm btn-info text-white" id="btn-refresh-inspection">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-warning fw-bold font-weight-bold text-dark" id="btn-reinspect">
                            <i class="fa fa-redo me-1 me-1"></i> Inspeksi Ulang (Re-Audit)
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-bold font-weight-bold" id="btn-open-add-inspection" data-bs-toggle="modal" data-bs-target="#modal-add-inspection" data-toggle="modal" data-target="#modal-add-inspection">
                            <i class="fa fa-plus-circle me-1 me-1"></i> Catat Hasil Inspeksi
                        </button>
                    </div>
                </div>
                <div id="grid_inspection" style="width: 100%; height: 430px; background-color: white; overflow: hidden;"></div>
            </div>

            <!-- TAB 3: JADWAL -->
            <div class="tab-pane fade" id="tab-schedule" role="tabpanel" aria-labelledby="schedule-tab">
                <div class="ga-toolbar">
                    <h5 class="card-title mb-0 fw-bold font-weight-bold">Master Jadwal Rutin Kebersihan</h5>
                    <div class="ga-btn-group">
                        <button type="button" class="btn btn-sm btn-info text-white" id="btn-refresh-schedule">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-bold font-weight-bold" id="btn-open-add-schedule" data-bs-toggle="modal" data-bs-target="#modal-add-schedule" data-toggle="modal" data-target="#modal-add-schedule">
                            <i class="fa fa-plus-circle me-1 me-1"></i> Tambah Jadwal Rutin
                        </button>
                    </div>
                </div>
                <div id="grid_schedule" style="width: 100%; height: 430px; background-color: white; overflow: hidden;"></div>
            </div>

            <!-- TAB 4: MASTER AREA -->
            <div class="tab-pane fade" id="tab-area" role="tabpanel" aria-labelledby="area-tab">
                <div class="ga-toolbar">
                    <h5 class="card-title mb-0 fw-bold font-weight-bold">Daftar Area, Ruangan &amp; QR Checkpoint</h5>
                    <div class="ga-btn-group">
                        <button type="button" class="btn btn-sm btn-info text-white" id="btn-refresh-area">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <a href="{{ url('hrd-ga-cleaning-area-print-qr') }}" target="_blank" class="btn btn-sm btn-dark fw-bold font-weight-bold">
                            <i class="fa fa-print me-1 me-1"></i> Cetak Label QR Area
                        </a>
                        <button type="button" class="btn btn-sm btn-primary fw-bold font-weight-bold" id="btn-open-add-area" data-bs-toggle="modal" data-bs-target="#modal-add-area" data-toggle="modal" data-target="#modal-add-area">
                            <i class="fa fa-plus-circle me-1 me-1"></i> Daftarkan Area Baru
                        </button>
                    </div>
                </div>
                <div id="grid_area" style="width: 100%; height: 430px; background-color: white; overflow: hidden;"></div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SCAN QR CHECK-IN (DENGAN FOTO SEBELUM) -->
<div class="modal fade" id="modal-start-task" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-start-task" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="Task_ID" id="start_task_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold font-weight-bold">Check-In Mulai Kerja &amp; Scan QR Checkpoint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <small>Petugas wajib berada di lokasi dan memindai / menginput kode QR Checkpoint area serta melampirkan foto kondisi awal.</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Task Pembersihan:</label>
                        <input type="text" id="start_task_title" class="form-control" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Kode QR Checkpoint Area <span class="text-danger">*</span></label>
                        <input type="text" name="Qr_Code" id="start_qr_code" class="form-control" required placeholder="Scan / masukkan kode QR point area">
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Foto Kondisi Awal (Photo Before / Sebelum Dikerjakan) <span class="text-danger">*</span></label>
                        <input type="file" name="Photo_Before" class="form-control" accept="image/*" capture="camera" required>
                        <small class="text-muted">Bisa langsung gunakan kamera HP atau upload file gambar (maks 5MB).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold font-weight-bold text-dark">Konfirmasi Check-In</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SELESAIKAN TUGAS (DENGAN FOTO SESUDAH & CHECKLIST) -->
<div class="modal fade" id="modal-complete-task" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-complete-task" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="Task_ID" id="complete_task_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold font-weight-bold">Penyelesaian Tugas &amp; Foto Kondisi Akhir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Task Pembersihan:</label>
                        <input type="text" id="complete_task_title" class="form-control" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="fw-bold font-weight-bold mb-0">Checklist Standar Kebersihan:</label>
                            <span id="complete_area_category_badge" class="badge bg-info text-white badge-info">-</span>
                        </div>
                        <div id="complete_task_checklist_container" class="p-2 border rounded bg-light" style="max-height: 220px; overflow-y: auto;">
                            <div class="text-muted text-center py-2"><i class="fa fa-spinner fa-spin me-1 me-1"></i> Memuat checklist template...</div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Foto Kondisi Akhir (Photo After / Sesudah Pembersihan) <span class="text-danger">*</span></label>
                        <input type="file" name="Photo_After" class="form-control" accept="image/*" capture="camera" required>
                        <small class="text-muted">Foto bukti kebersihan area setelah pengerjaan (maks 5MB).</small>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Catatan Pengerjaan (Opsional)</label>
                        <textarea name="Notes" class="form-control" rows="2" placeholder="Catatan pekerjaan atau kendala di lokasi"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold font-weight-bold">Simpan &amp; Selesaikan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH AREA -->
<div class="modal fade" id="modal-add-area" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-add-area">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold font-weight-bold">Daftarkan Area Pembersihan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Area / Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="Area_Name" class="form-control" required placeholder="misal: Toilet Pria Lt. 1">
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Gedung <span class="text-danger">*</span></label>
                            <input type="text" name="Building" class="form-control" required placeholder="misal: Gedung Utama">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Lantai <span class="text-danger">*</span></label>
                            <input type="text" name="Floor" class="form-control" required placeholder="misal: Lantai 1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Kategori Area</label>
                            <select name="Category" class="form-control">
                                <option value="OFFICE">Office / Ruang Kerja</option>
                                <option value="TOILET">Toilet / Restroom</option>
                                <option value="PANTRY">Pantry / Kantin</option>
                                <option value="CORRIDOR">Koridor / Selasar</option>
                                <option value="LOBBY">Lobby</option>
                                <option value="OUTDOOR">Outdoor / Halaman</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Lokasi / Site</label>
                            <input type="text" name="Location" class="form-control" placeholder="misal: GEU-HO">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">QR Code / Checkpoint String</label>
                        <input type="text" name="Qr_Code" class="form-control" placeholder="Biarkan kosong untuk otomatis disamakan dengan Kode Area">
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="Remarks" class="form-control" rows="2" placeholder="Catatan fasilitas pembersihan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Area</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH JADWAL -->
<div class="modal fade" id="modal-add-schedule" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-add-schedule">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold font-weight-bold">Buat Jadwal Pembersihan Rutin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Area Pembersihan <span class="text-danger">*</span></label>
                        <select name="Area_ID" id="schedule_area_id" class="form-control" required>
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->id }}">{{ $a->Area_Name }} ({{ $a->Building }} Lt. {{ $a->Floor }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Frekuensi <span class="text-danger">*</span></label>
                            <select name="Frequency" class="form-control" required>
                                <option value="DAILY">Harian (Daily)</option>
                                <option value="WEEKLY">Mingguan (Weekly)</option>
                                <option value="MONTHLY">Bulanan (Monthly)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Shift <span class="text-danger">*</span></label>
                            <select name="Shift" class="form-control" required>
                                <option value="MORNING">Pagi (Morning)</option>
                                <option value="AFTERNOON">Siang (Afternoon)</option>
                                <option value="NIGHT">Malam (Night)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Pelaksana <span class="text-danger">*</span></label>
                            <select name="Assignee_Type" class="form-control" required>
                                <option value="INTERNAL">Internal Staff</option>
                                <option value="VENDOR">Vendor Cleaning</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Nama Petugas / Vendor</label>
                            <input type="text" name="Assignee_Name" class="form-control" placeholder="Nama Petugas">
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Instruksi / Catatan Jadwal</label>
                        <textarea name="Notes" class="form-control" rows="2" placeholder="Standar kebersihan shift"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH INSPEKSI -->
<div class="modal fade" id="modal-add-inspection" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-add-inspection">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold font-weight-bold">Form Audit &amp; Nilai Inspeksi Supervisor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Area yang Diinspeksi <span class="text-danger">*</span></label>
                        <select name="Area_ID" id="inspection_area_id" class="form-control" required>
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->id }}">{{ $a->Area_Name }} ({{ $a->Building }} Lt. {{ $a->Floor }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Skor Nilai Kebersihan (0 - 100) <span class="text-danger">*</span></label>
                        <input type="number" name="Score" class="form-control" min="0" max="100" step="1" required value="85">
                        <small class="text-muted">&ge;80: Lulus Baik (PASSED), 60-79: Perbaikan (IMPROVEMENT), &lt;60: Tidak Lulus (FAILED)</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Catatan Temuan / Evaluasi</label>
                        <textarea name="Finding_Notes" class="form-control" rows="3" placeholder="Rincian kondisi fisik area atau kekurangan yang ditemukan"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="Spawn_Follow_Up_Ticket" value="1" class="form-check-input" id="chk_spawn_ticket" checked>
                        <label class="form-check-label" for="chk_spawn_ticket">
                            Terbitkan Tiket Perbaikan Otomatis jika Nilai &lt; 60 (FAILED)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold font-weight-bold">
                        <i class="fa fa-save me-1 me-1"></i> Catat Hasil Inspeksi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL RE-INSPEKSI (AUDIT ULANG PADA NOMOR SAMA) -->
<div class="modal fade" id="modal-reinspect" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="form-reinspect">
            @csrf
            <input type="hidden" name="Inspection_ID" id="reinspect_inspection_id">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold font-weight-bold">
                        <i class="fa fa-redo me-1 me-1"></i> Inspeksi Ulang (Re-Audit) Area
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fa fa-info-circle me-1 me-1"></i> Melakukan audit ulang pada No. Inspeksi yang sama untuk mencatat perkembangan dari status FAILED / NEEDS_IMPROVEMENT menjadi PASSED.
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label fw-bold font-weight-bold">No. Inspeksi:</label>
                            <input type="text" id="reinspect_no" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label fw-bold font-weight-bold">Area Pembersihan:</label>
                            <input type="text" id="reinspect_area" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="card mb-3 border">
                        <div class="card-header bg-light py-2 fw-bold font-weight-bold">
                            <i class="fa fa-history me-1 me-1"></i> Riwayat Audit Sebelumnya (Round Log)
                        </div>
                        <div class="card-body p-2" id="reinspect_history_container" style="max-height: 180px; overflow-y: auto;">
                            <div class="text-muted text-center py-2"><i class="fa fa-spinner fa-spin me-1 me-1"></i> Memuat riwayat...</div>
                        </div>
                    </div>

                    <div class="card p-3 border-warning bg-light">
                        <h6 class="fw-bold font-weight-bold text-dark mb-2">Form Penilaian Audit Ulang (Round Selanjutnya):</h6>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold font-weight-bold">Skor Nilai Baru (0 - 100) <span class="text-danger">*</span></label>
                            <input type="number" name="Score" id="reinspect_score" class="form-control" min="0" max="100" step="1" required value="85">
                            <small class="text-muted">&ge;80: Lulus (PASSED). Jika sebelumnya ada tiket perbaikan terbuka, tiket otomatis diselesaikan (Resolved).</small>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label fw-bold font-weight-bold">Catatan Hasil Verifikasi &amp; Perbaikan <span class="text-danger">*</span></label>
                            <textarea name="Notes" id="reinspect_notes" class="form-control" rows="3" required placeholder="Jelaskan kondisi setelah perbaikan dilakukan (misal: Lantai telah dipel ulang dan bebas noda, kloset lancar)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold font-weight-bold text-dark">
                        <i class="fa fa-check-circle me-1 me-1"></i> Simpan Hasil Re-Inspeksi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var gridTask, gridInspection, gridSchedule, gridArea;

function showCleaningModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (window.jQuery && $.fn.modal) {
        $(el).modal('show');
    }
}

function hideCleaningModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && bootstrap.Modal) {
        var inst = bootstrap.Modal.getInstance(el);
        if (inst) {
            inst.hide();
            return;
        }
    }
    if (window.jQuery && $.fn.modal) {
        $(el).modal('hide');
    }
}

function initCleaningGrids() {
    // 1. Grid Task
    gridTask = new dhtmlXGridObject('grid_task');
    gridTask.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridTask.setHeader("No,No Task,Area Pembersihan,Tanggal,Shift,Petugas,Waktu Scan In,Foto Before,Waktu Selesai,Foto After,Status");
    gridTask.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#select_filter,#text_filter,#text_filter,&nbsp;,#text_filter,&nbsp;,#select_filter");
    gridTask.setInitWidths("45,140,170,95,80,130,120,90,120,90,100");
    gridTask.setColAlign("center,left,left,center,center,left,center,center,center,center,center");
    gridTask.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    gridTask.setColSorting("int,str,str,date,str,str,str,na,str,na,str");
    gridTask.setSkin("dhx_skyblue");
    gridTask.init();
    loadTaskData();

    // 2. Grid Inspection
    gridInspection = new dhtmlXGridObject('grid_inspection');
    gridInspection.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridInspection.setHeader("No,No Inspeksi,Area Pembersihan,Tanggal,Inspektor,Skor,Hasil,Follow Up Tiket");
    gridInspection.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#numeric_filter,#select_filter,#text_filter");
    gridInspection.setInitWidths("45,140,210,100,140,80,150,160");
    gridInspection.setColAlign("center,left,left,center,left,right,center,center");
    gridInspection.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
    gridInspection.setColSorting("int,str,str,date,str,int,str,str");
    gridInspection.setSkin("dhx_skyblue");
    gridInspection.init();
    reloadInspectionGrid();

    // 3. Grid Schedule
    gridSchedule = new dhtmlXGridObject('grid_schedule');
    gridSchedule.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridSchedule.setHeader("No,No Jadwal,Area Pembersihan,Frekuensi,Shift,Petugas / Vendor,Status");
    gridSchedule.attachHeader("&nbsp;,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#select_filter");
    gridSchedule.setInitWidths("45,140,250,110,100,170,100");
    gridSchedule.setColAlign("center,left,left,center,center,left,center");
    gridSchedule.setColTypes("ro,ro,ro,ro,ro,ro,ro");
    gridSchedule.setColSorting("int,str,str,str,str,str,str");
    gridSchedule.setSkin("dhx_skyblue");
    gridSchedule.init();
    reloadScheduleGrid();

    // 4. Grid Area
    gridArea = new dhtmlXGridObject('grid_area');
    gridArea.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridArea.setHeader("No,Kode Area,Nama Area,Lokasi / Gedung / Lt,Kategori,QR Checkpoint,Status");
    gridArea.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#select_filter");
    gridArea.setInitWidths("45,130,220,200,110,150,90");
    gridArea.setColAlign("center,left,left,left,center,center,center");
    gridArea.setColTypes("ro,ro,ro,ro,ro,ro,ro");
    gridArea.setColSorting("int,str,str,str,str,str,str");
    gridArea.setSkin("dhx_skyblue");
    gridArea.init();
    reloadAreaGrid();
}

function loadTaskData() {
    var date = $('#filter_task_date').val();
    var status = $('#filter_task_status').val();
    gridTask.clearAndLoad("{{ url('hrd-ga-cleaning-task-data') }}?date=" + encodeURIComponent(date) + "&status=" + encodeURIComponent(status));
}

function reloadInspectionGrid() {
    gridInspection.clearAndLoad("{{ url('hrd-ga-cleaning-inspection-data') }}");
}

function reloadScheduleGrid() {
    gridSchedule.clearAndLoad("{{ url('hrd-ga-cleaning-schedule-data') }}");
}

function reloadAreaGrid() {
    gridArea.clearAndLoad("{{ url('hrd-ga-cleaning-area-data') }}");
}

function handleCleaningTabActive(target) {
    setTimeout(function() {
        if (target === '#tab-inspection') {
            if (gridInspection) gridInspection.setSizes();
            reloadInspectionGrid();
        } else if (target === '#tab-schedule') {
            if (gridSchedule) gridSchedule.setSizes();
            reloadScheduleGrid();
        } else if (target === '#tab-area') {
            if (gridArea) gridArea.setSizes();
            reloadAreaGrid();
        } else if (target === '#tab-task') {
            if (gridTask) gridTask.setSizes();
            loadTaskData();
        }
    }, 50);
}

$(document).ready(function() {
    initCleaningGrids();

    // Tab switching handler (supports Bootstrap 4 & Bootstrap 5)
    $('#cleaningTab a.nav-link').on('click', function(e) {
        e.preventDefault();
        var $link = $(this);
        var target = $link.attr('data-bs-target') || $link.attr('href');
        if (!target) return;

        $('#cleaningTab a.nav-link').removeClass('active').attr('aria-selected', 'false');
        $link.addClass('active').attr('aria-selected', 'true');

        $('#cleaningTabContent > .tab-pane').removeClass('show active');
        $(target).addClass('show active');

        handleCleaningTabActive(target);
    });

    $('#btn-refresh-task').click(loadTaskData);
    $('#filter_task_date, #filter_task_status').change(loadTaskData);

    $('#btn-refresh-inspection').click(reloadInspectionGrid);
    $('#btn-refresh-schedule').click(reloadScheduleGrid);
    $('#btn-refresh-area').click(reloadAreaGrid);

    // 1. Submit Tambah Area
    $('#form-add-area').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-cleaning-area-save') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideCleaningModal('#modal-add-area');
            $('#form-add-area')[0].reset();
            reloadAreaGrid();
            if (resp.data && resp.data.id) {
                var opt1 = new Option(resp.data.Area_Name + ' (' + resp.data.Building + ' Lt. ' + resp.data.Floor + ')', resp.data.id);
                var opt2 = new Option(resp.data.Area_Name + ' (' + resp.data.Building + ' Lt. ' + resp.data.Floor + ')', resp.data.id);
                $('#schedule_area_id').append(opt1);
                $('#inspection_area_id').append(opt2);
            }
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan area.');
        });
    });

    // 2. Submit Tambah Jadwal
    $('#form-add-schedule').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-cleaning-schedule-save') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideCleaningModal('#modal-add-schedule');
            $('#form-add-schedule')[0].reset();
            reloadScheduleGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan jadwal.');
        });
    });

    // 3. Submit Inspeksi Supervisor
    $('#form-add-inspection').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-cleaning-inspection-save') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideCleaningModal('#modal-add-inspection');
            $('#form-add-inspection')[0].reset();
            reloadInspectionGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan inspeksi.');
        });
    });

    // 3b. Tombol Buka Modal Re-Inspeksi
    $('#btn-reinspect').click(function() {
        var inspectionId = gridInspection.getSelectedRowId();
        if (!inspectionId) {
            alert('Pilih baris inspeksi yang akan di-inspeksi ulang terlebih dahulu.');
            return;
        }

        var inspNo = gridInspection.cells(inspectionId, 1).getValue();
        var areaName = gridInspection.cells(inspectionId, 2).getValue();

        $('#reinspect_inspection_id').val(inspectionId);
        $('#reinspect_no').val(inspNo);
        $('#reinspect_area').val(areaName);
        $('#reinspect_score').val(85);
        $('#reinspect_notes').val('');
        $('#reinspect_history_container').html('<div class="text-muted text-center py-2"><i class="fa fa-spinner fa-spin me-1 me-1"></i> Memuat riwayat...</div>');
        showCleaningModal('#modal-reinspect');

        $.get("{{ url('hrd-ga-cleaning-inspection-detail') }}", { id: inspectionId }, function(resp) {
            if (resp.success && resp.data) {
                var container = $('#reinspect_history_container');
                container.empty();
                var logs = resp.data.logs || [];
                if (logs.length === 0) {
                    container.html('<div class="text-muted text-center py-2">Belum ada riwayat audit round tercatat.</div>');
                    return;
                }

                var listHtml = '<ul class="list-group list-group-flush">';
                $.each(logs, function(idx, l) {
                    var badgeClass = l.Result_Status === 'PASSED'
                        ? 'bg-success text-white badge-success'
                        : (l.Result_Status === 'NEEDS_IMPROVEMENT' ? 'bg-warning text-dark badge-warning' : 'bg-danger text-white badge-danger');
                    listHtml += '<li class="list-group-item p-2">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<strong>Round ' + l.Audit_Round + ' - Skor: ' + l.Score + '</strong>' +
                            '<span class="badge ' + badgeClass + '">' + l.Result_Status + '</span>' +
                        '</div>' +
                        '<div class="small text-muted mt-1">' + (l.Notes ? l.Notes : 'Tanpa catatan') + '</div>' +
                        '<div class="small text-secondary fst-italic font-italic mt-1">Oleh: ' + l.Inspector_Name + ' (' + l.Created_At + ')</div>' +
                    '</li>';
                });
                listHtml += '</ul>';
                container.html(listHtml);
            }
        }).fail(function() {
            $('#reinspect_history_container').html('<div class="text-danger text-center py-2">Gagal memuat riwayat audit.</div>');
        });
    });

    // 3c. Submit Re-Inspeksi
    $('#form-reinspect').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-cleaning-reinspection-save') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideCleaningModal('#modal-reinspect');
            $('#form-reinspect')[0].reset();
            reloadInspectionGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan re-inspeksi.');
        });
    });

    // 4. Tombol Generate Tugas Hari Ini
    $('#btn-generate-task').click(function() {
        var date = $('#filter_task_date').val();
        if (confirm('Generate tugas harian kebersihan untuk tanggal ' + date + '?')) {
            $.post("{{ url('hrd-ga-cleaning-generate-tasks') }}", {
                _token: "{{ csrf_token() }}",
                date: date
            }, function(resp) {
                alert(resp.message);
                loadTaskData();
            }).fail(function(err) {
                alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal generate tugas.');
            });
        }
    });

    // 5. Tombol Scan QR Mulai Kerja (Check-in & Photo Before)
    $('#btn-start-task').click(function() {
        var taskId = gridTask.getSelectedRowId();
        if (!taskId) {
            alert('Pilih baris tugas yang akan dimulai terlebih dahulu.');
            return;
        }
        var taskNo = gridTask.cells(taskId, 1).getValue();
        var areaName = gridTask.cells(taskId, 2).getValue();
        $('#start_task_id').val(taskId);
        $('#start_task_title').val(taskNo + ' - ' + areaName);
        $('#start_qr_code').val('');
        showCleaningModal('#modal-start-task');
    });

    $('#form-start-task').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "{{ url('hrd-ga-cleaning-task-start') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(resp) {
                alert(resp.message);
                hideCleaningModal('#modal-start-task');
                $('#form-start-task')[0].reset();
                loadTaskData();
            },
            error: function(err) {
                alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal check-in.');
            }
        });
    });

    // 6. Tombol Selesaikan Tugas (Checklist & Photo After)
    $('#btn-complete-task').click(function() {
        var taskId = gridTask.getSelectedRowId();
        if (!taskId) {
            alert('Pilih baris tugas yang akan diselesaikan terlebih dahulu.');
            return;
        }
        var taskNo = gridTask.cells(taskId, 1).getValue();
        var areaName = gridTask.cells(taskId, 2).getValue();
        $('#complete_task_id').val(taskId);
        $('#complete_task_title').val(taskNo + ' - ' + areaName);
        $('#complete_area_category_badge').text('Memuat...');
        $('#complete_task_checklist_container').html('<div class="text-muted text-center py-2"><i class="fa fa-spinner fa-spin me-1 me-1"></i> Memuat checklist template...</div>');
        showCleaningModal('#modal-complete-task');

        $.get("{{ url('hrd-ga-cleaning-task-checklist-template') }}", { task_id: taskId }, function(resp) {
            if (resp.success && resp.data) {
                var category = (resp.data.area && resp.data.area.Category) ? resp.data.area.Category : 'OFFICE';
                $('#complete_area_category_badge').text('Kategori: ' + category);
                var templates = resp.data.templates || [];
                var container = $('#complete_task_checklist_container');
                container.empty();

                if (templates.length === 0) {
                    container.html('<div class="text-muted text-center py-2">Tidak ada template checklist khusus.</div>');
                    return;
                }

                $.each(templates, function(idx, item) {
                    var desc = item.Item_Description ? '<br><small class="text-muted">' + item.Item_Description + '</small>' : '';
                    var html = '<div class="form-check border-bottom pb-2 mb-2">' +
                        '<label class="form-check-label fw-normal font-weight-normal">' +
                            '<input type="checkbox" name="Checklist[' + idx + '][Result]" value="PASS" class="form-check-input" checked> ' +
                            '<strong>' + item.Item_Name + '</strong>' + desc +
                            '<input type="hidden" name="Checklist[' + idx + '][Item_Name]" value="' + item.Item_Name.replace(/"/g, '&quot;') + '">' +
                        '</label>' +
                    '</div>';
                    container.append(html);
                });
            }
        }).fail(function() {
            $('#complete_area_category_badge').text('Error');
            $('#complete_task_checklist_container').html('<div class="text-danger text-center py-2">Gagal memuat template checklist.</div>');
        });
    });

    $('#form-complete-task').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "{{ url('hrd-ga-cleaning-task-complete') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(resp) {
                alert(resp.message);
                hideCleaningModal('#modal-complete-task');
                $('#form-complete-task')[0].reset();
                loadTaskData();
            },
            error: function(err) {
                alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyelesaikan tugas.');
            }
        });
    });
});
</script>
@endsection
