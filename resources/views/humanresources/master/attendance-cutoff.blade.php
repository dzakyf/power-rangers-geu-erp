@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-calendar me-2"></i>Master Cutoff &amp; Siklus Periode Absensi
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1" style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Master Data</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Attendance Cutoff</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.data') }}" class="btn btn-sm rounded-pill shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-file-text me-1"></i> Laporan Rekap Presensi
            </a>
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-monitor me-1"></i> Portal Absensi
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Info Banner Penjelasan Logika Cutoff -->
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-start gap-3">
        <div class="rounded-circle bg-white text-info p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; min-width: 38px;">
            <i class="fe fe-info fs-5"></i>
        </div>
        <div class="small">
            <strong class="d-block mb-1 text-dark">Bagaimana Siklus Cutoff Bekerja?</strong>
            Contoh: Jika <strong>Tanggal Mulai = 21</strong> dan <strong>Tanggal Cutoff = 20</strong>, maka laporan presensi untuk <strong>Periode November 2026</strong> akan dihitung dari <strong>21 Oktober 2026 s/d 20 November 2026</strong>. Sedangkan absensi mulai <strong>21 November 2026</strong> otomatis masuk ke <strong>Periode Desember 2026</strong>. Begitu pula jika cutoff diatur <strong>26 s/d 25</strong>.
        </div>
    </div>

    <!-- Tabel Daftar Master Cutoff -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-sliders text-primary me-2"></i>Daftar Konfigurasi Cutoff Aktif</h6>
                <span class="badge bg-light text-primary border">{{ count($cutoffs) }} Konfigurasi</span>
            </div>
            <button type="button" class="btn btn-warning btn-sm rounded-pill shadow-sm px-3 fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#modalAddCutoff">
                <i class="fe fe-plus-circle me-1"></i> Tambah Aturan Cutoff
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 text-center" style="width: 50px;">No</th>
                            <th style="min-width: 140px;">Kode Cutoff</th>
                            <th style="min-width: 220px;">Nama Konfigurasi</th>
                            <th class="text-center" style="min-width: 130px;">Tgl Mulai (Start)</th>
                            <th class="text-center" style="min-width: 130px;">Tgl Akhir (Cutoff)</th>
                            <th class="text-center" style="min-width: 160px;">Target Penerapan</th>
                            <th class="text-center" style="min-width: 120px;">Default</th>
                            <th class="text-center" style="min-width: 90px;">Status</th>
                            <th class="text-center pe-3" style="min-width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cutoffs as $idx => $c)
                            <tr>
                                <td class="ps-3 text-center text-muted small">{{ $idx + 1 }}</td>
                                <td>
                                    <code class="fw-semibold">{{ $c->cutoff_code }}</code>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $c->name }}</div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;" title="{{ $c->notes }}">{{ $c->notes ?: '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        Tanggal {{ $c->start_day }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Tanggal {{ $c->end_day }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($c->target_type === 'GLOBAL')
                                        <span class="badge bg-primary">Semua Karyawan</span>
                                    @elseif($c->target_type === 'DEPARTMENT')
                                        <span class="badge bg-info text-dark">Dept: {{ $c->target_value }}</span>
                                    @elseif($c->target_type === 'EMPLOYEE_TYPE')
                                        <span class="badge bg-secondary">Tipe: {{ $c->target_value }}</span>
                                    @else
                                        <span class="badge bg-dark">NIK: {{ $c->target_value }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($c->is_default)
                                        <span class="badge bg-success"><i class="fe fe-check me-1"></i>Ya</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $c->status === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="text-center pe-3 text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2" onclick='editCutoff(@json($c))' title="Edit Konfigurasi">
                                            <i class="fe fe-edit me-1"></i>Edit
                                        </button>
                                        @if(!$c->is_default)
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill py-1 px-2" onclick="deleteCutoff({{ $c->id }})" title="Hapus Konfigurasi">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Belum ada data konfigurasi cutoff absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Simulator & Live Tester Cutoff -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-activity text-primary me-2"></i>Simulator &amp; Live Tester Rentang Periode</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Pilih Periode Bulan</label>
                    <input type="month" id="sim_periode" class="form-control rounded-3" value="{{ date('Y-m') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Pilih Aturan Cutoff</label>
                    <select id="sim_cutoff_id" class="form-select rounded-3">
                        @foreach($cutoffs as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Tgl {{ $c->start_day }} s/d {{ $c->end_day }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary rounded-pill w-100 fw-semibold" onclick="testSimulasi()">
                        <i class="fe fe-play me-1"></i> Hitung Rentang Tanggal
                    </button>
                </div>
                <div class="col-12 mt-3" id="sim_result_wrapper" style="display: none;">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small d-block">Hasil Perhitungan Rentang Periode Laporan:</span>
                        <h5 class="fw-bold text-primary mt-1 mb-0" id="sim_result_text">-</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Konfigurasi Cutoff -->
    <div class="modal fade" id="modalAddCutoff" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <form id="formAddCutoff">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Tambah Aturan Cutoff Absensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kode Cutoff <span class="text-danger">*</span></label>
                                <input type="text" name="cutoff_code" class="form-control rounded-3" placeholder="CUTOFF_CUSTOM_26_25" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Konfigurasi <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="Cutoff Site 26-25" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tanggal Mulai (Start) <span class="text-danger">*</span></label>
                                <input type="number" name="start_day" class="form-control rounded-3" min="1" max="31" value="21" required>
                                <small class="text-muted" style="font-size: 11px;">Contoh: 21 (bulan sebelumnya)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tanggal Akhir (Cutoff) <span class="text-danger">*</span></label>
                                <input type="number" name="end_day" class="form-control rounded-3" min="1" max="31" value="20" required>
                                <small class="text-muted" style="font-size: 11px;">Contoh: 20 (bulan berjalan)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Siklus Cutoff</label>
                                <select name="cutoff_cycle" class="form-select rounded-3">
                                    <option value="PREV_TO_CURRENT">21 Bln Lalu s/d 20 Bln Ini (Standar)</option>
                                    <option value="SAME_MONTH">Dalam 1 Bulan Kalender yang Sama</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Target Penerapan</label>
                                <select name="target_type" id="add_target_type" class="form-select rounded-3" onchange="toggleTargetValue('add')">
                                    <option value="GLOBAL">Semua Karyawan (Global)</option>
                                    <option value="DEPARTMENT">Spesifik Departemen</option>
                                    <option value="EMPLOYEE_TYPE">Tipe Karyawan (Office/Site)</option>
                                    <option value="EMPLOYEE">Spesifik NIK Karyawan</option>
                                </select>
                            </div>
                            <div class="col-12" id="add_target_value_wrapper" style="display: none;">
                                <label class="form-label small fw-semibold" id="add_target_value_label">Nilai Target</label>
                                <div id="add_target_value_container">
                                    <input type="text" name="target_value" id="add_target_value_input" class="form-control rounded-3" placeholder="Masukkan nilai...">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="add_is_default">
                                    <label class="form-check-label small fw-semibold" for="add_is_default">Jadikan Default Utama Sistem</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Catatan / Keterangan</label>
                                <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Catatan operasional..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnSaveCutoff">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Konfigurasi Cutoff -->
    <div class="modal fade" id="modalEditCutoff" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <form id="formEditCutoff">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Edit Aturan Cutoff Absensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kode Cutoff</label>
                                <input type="text" id="edit_cutoff_code" class="form-control rounded-3 bg-light" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Konfigurasi <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tanggal Mulai (Start) <span class="text-danger">*</span></label>
                                <input type="number" name="start_day" id="edit_start_day" class="form-control rounded-3" min="1" max="31" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tanggal Akhir (Cutoff) <span class="text-danger">*</span></label>
                                <input type="number" name="end_day" id="edit_end_day" class="form-control rounded-3" min="1" max="31" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Siklus Cutoff</label>
                                <select name="cutoff_cycle" id="edit_cutoff_cycle" class="form-select rounded-3">
                                    <option value="PREV_TO_CURRENT">21 Bln Lalu s/d 20 Bln Ini (Standar)</option>
                                    <option value="SAME_MONTH">Dalam 1 Bulan Kalender yang Sama</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Target Penerapan</label>
                                <select name="target_type" id="edit_target_type" class="form-select rounded-3" onchange="toggleTargetValue('edit')">
                                    <option value="GLOBAL">Semua Karyawan (Global)</option>
                                    <option value="DEPARTMENT">Spesifik Departemen</option>
                                    <option value="EMPLOYEE_TYPE">Tipe Karyawan (Office/Site)</option>
                                    <option value="EMPLOYEE">Spesifik NIK Karyawan</option>
                                </select>
                            </div>
                            <div class="col-12" id="edit_target_value_wrapper" style="display: none;">
                                <label class="form-label small fw-semibold">Nilai Target</label>
                                <input type="text" name="target_value" id="edit_target_value_input" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" id="edit_status" class="form-select rounded-3">
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-center mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="edit_is_default">
                                    <label class="form-check-label small fw-semibold" for="edit_is_default">Jadikan Default Utama</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Catatan / Keterangan</label>
                                <textarea name="notes" id="edit_notes" class="form-control rounded-3" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnUpdateCutoff">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function toggleTargetValue(mode) {
    const type = document.getElementById(mode + '_target_type').value;
    const wrapper = document.getElementById(mode + '_target_value_wrapper');
    if (type === 'GLOBAL') {
        wrapper.style.display = 'none';
    } else {
        wrapper.style.display = 'block';
    }
}

// Submit Form Tambah
document.getElementById('formAddCutoff').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveCutoff');
    btn.disabled = true;

    const formData = new FormData(this);

    fetch("{{ route('attendance.cutoff.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            alert(res.message);
            window.location.reload();
        } else {
            alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
    });
});

// Edit Cutoff Handler
function editCutoff(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_cutoff_code').value = item.cutoff_code;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_start_day').value = item.start_day;
    document.getElementById('edit_end_day').value = item.end_day;
    document.getElementById('edit_cutoff_cycle').value = item.cutoff_cycle || 'PREV_TO_CURRENT';
    document.getElementById('edit_target_type').value = item.target_type || 'GLOBAL';
    document.getElementById('edit_target_value_input').value = item.target_value || '';
    document.getElementById('edit_status').value = item.status;
    document.getElementById('edit_is_default').checked = item.is_default == 1;
    document.getElementById('edit_notes').value = item.notes || '';

    toggleTargetValue('edit');

    const modal = new bootstrap.Modal(document.getElementById('modalEditCutoff'));
    modal.show();
}

// Submit Form Edit
document.getElementById('formEditCutoff').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('edit_id').value;
    const btn = document.getElementById('btnUpdateCutoff');
    btn.disabled = true;

    const formData = new FormData(this);

    fetch(`/hrd-master-attendance-cutoff/${id}/update`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            alert(res.message);
            window.location.reload();
        } else {
            alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
    });
});

// Hapus Cutoff
function deleteCutoff(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus konfigurasi cutoff ini?')) return;

    fetch(`/hrd-master-attendance-cutoff/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            alert(res.message);
            window.location.reload();
        } else {
            alert('Gagal menghapus: ' + res.message);
        }
    })
    .catch(err => alert('Terjadi kesalahan koneksi.'));
}

// Simulasi Rentang Tanggal
function testSimulasi() {
    const periode = document.getElementById('sim_periode').value;
    const cutoffId = document.getElementById('sim_cutoff_id').value;

    fetch(`{{ route('attendance.cutoff.preview') }}?periode=${periode}&cutoff_id=${cutoffId}`)
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            const range = res.range;
            document.getElementById('sim_result_wrapper').style.display = 'block';
            document.getElementById('sim_result_text').innerHTML = `
                ${range.formatted_label} <br>
                <small class="text-muted fw-normal" style="font-size: 12px;">
                    Mulai Tanggal: <strong>${range.start_date}</strong> s/d <strong>${range.end_date}</strong> (Siklus: ${range.cycle_type})
                </small>
            `;
        }
    });
}
</script>
@endsection

