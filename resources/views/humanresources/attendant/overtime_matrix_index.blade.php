@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2"">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-layers me-2"></i>Kelola Matriks Tarif Lembur (Dynamic Overtime Matrix)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}">Portal Absensi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Master Matriks Lembur</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fe fe-arrow-left me-1"></i> Kembali ke Portal
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm"
                onclick="openAddMatrixModal()">
                <i class="fe fe-plus-circle me-1"></i> Tambah Matriks Baru
            </button>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-body pb-0">
            <!-- NAV TABS ATAS: MATRIKS vs MASTER WILAYAH -->
            <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-semibold" id="tab-matrix" data-bs-toggle="pill"
                        data-bs-target="#pane-matrix" type="button" role="tab">
                        <i class="fe fe-grid me-1"></i> Matriks Tarif Lembur ({{ count($matrices) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold ms-2" id="tab-regions" data-bs-toggle="pill"
                        data-bs-target="#pane-regions" type="button" role="tab">
                        <i class="fe fe-map me-1"></i> Master Wilayah & Kodepos ({{ count($regions) }})
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="pills-tabContent">
        <!-- PANE 1: KATALOG MATRIKS TARIF -->
        <div class="tab-pane fade show active" id="pane-matrix" role="tabpanel">
            <div class="row g-4">
                @forelse($matrices as $m)
                    <div class="col-lg-6" id="matrix-card-{{ $m->id }}">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div
                                class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary text-white me-2">{{ $m->matrix_code }}</span>
                                    <h6 class="fw-bold d-inline mb-0">{{ $m->matrix_name }}</h6>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span
                                        class="badge {{ $m->status === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }} text-white me-2">
                                        {{ $m->status }}
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-circle"
                                        style="width: 32px; height: 32px; padding: 0;" title="Edit Matriks"
                                        onclick="openEditMatrixModal({{ $m->id }})">
                                        <i class="fe fe-edit-2"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle"
                                        style="width: 32px; height: 32px; padding: 0;" title="Hapus Matriks"
                                        onclick="deleteMatrix({{ $m->id }}, '{{ $m->matrix_name }}')">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block" style="font-size: 11px;">Kelompok Grading /
                                            Sasaran:</small>
                                        <span class="fw-semibold text-dark">{{ $m->grade_group ?: '-' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block" style="font-size: 11px;">Target Wilayah
                                            Penempatan:</small>
                                        @php
                                            $regObj = $regions->firstWhere('region_code', $m->region_type);
                                        @endphp
                                        @if ($m->region_type === 'ALL')
                                            <span class="badge bg-secondary text-white">Semua Wilayah (Nasional)</span>
                                        @elseif($regObj)
                                            <span class="badge bg-info text-dark border">{{ $regObj->region_name }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border">{{ $m->region_type }}</span>
                                        @endif
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-muted d-block" style="font-size: 11px;">Kata Kunci Jabatan yang
                                            Dicocokkan:</small>
                                        @php
                                            $decodedTitles = json_decode($m->job_titles, true);
                                            $titlesList = is_array($decodedTitles)
                                                ? $decodedTitles
                                                : array_filter(array_map('trim', explode(',', $m->job_titles)));
                                        @endphp
                                        @if (!empty($titlesList))
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach ($titlesList as $t)
                                                    <span
                                                        class="badge bg-light text-dark border">{{ $t }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">Semua jabatan dalam kelompok
                                                grading/wilayah</span>
                                        @endif
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-muted d-block" style="font-size: 11px;">Tipe Kalkulasi
                                            Tarif:</small>
                                        <span class="badge bg-light text-dark border">{{ $m->pricing_type }} (Batas
                                            Maksimal: {{ $m->max_hours }} Jam)</span>
                                    </div>
                                </div>

                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">Tier</th>
                                                <th>Keterangan Durasi / Range</th>
                                                <th class="text-end">Tarif Dasar / Flat</th>
                                                <th class="text-end">Per Jam Lanjutan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $mtiers = $tiers[$m->id] ?? collect();
                                            @endphp
                                            @forelse($mtiers as $t)
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $t->tier_order }}</td>
                                                    <td>{{ $t->tier_label }}</td>
                                                    <td class="text-end fw-semibold text-primary">Rp
                                                        {{ number_format($t->base_rate, 0, ',', '.') }}</td>
                                                    <td class="text-end fw-semibold text-danger">
                                                        {{ $t->next_per_hour_rate > 0 ? '+ Rp ' . number_format($t->next_per_hour_rate, 0, ',', '.') . ' / jam' : '-' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-2">Belum ada tier
                                                        konfigurasi.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-light py-2 text-muted small d-flex justify-content-between">
                                <span><i class="fe fe-calendar me-1"></i> Update:
                                    {{ date('d M Y H:i', strtotime($m->updated_at)) }}</span>
                                <span>Oleh: {{ $m->updated_by ?: ($m->created_by ?: 'System') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Belum ada data matriks tarif lembur. Silakan tambahkan matriks baru.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PANE 2: MASTER WILAYAH LEMBUR (FULL CRUD) -->
        <div class="tab-pane fade" id="pane-regions" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-map-pin text-primary me-2"></i>Daftar Master
                            Wilayah Lembur</h6>
                        <small class="text-muted">Wilayah digunakan pada matriks tarif dan dicocokkan otomatis melalui kode
                            pos lokasi kerja karyawan</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3"
                        onclick="openAddRegionModal()">
                        <i class="fe fe-plus me-1"></i> Tambah Wilayah Baru
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 150px;">Kode Wilayah</th>
                                    <th style="width: 250px;">Nama Wilayah</th>
                                    <th>Kata Kunci Kota / Provinsi Dicocokkan</th>
                                    <th>Keterangan</th>
                                    <th style="width: 100px;">Status</th>
                                    <th class="text-end pe-3" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="regions-table-body">
                                @forelse($regions as $r)
                                    <tr id="region-row-{{ $r->id }}">
                                        <td class="ps-3 fw-bold text-primary">{{ $r->region_code }}</td>
                                        <td class="fw-semibold text-dark">{{ $r->region_name }}</td>
                                        <td>
                                            <div class="small text-muted" style="max-width: 400px;">
                                                {{ $r->coverage_keywords ?: '(Semua / Default)' }}
                                            </div>
                                        </td>
                                        <td><small class="text-muted">{{ $r->description ?: '-' }}</small></td>
                                        <td>
                                            <span
                                                class="badge {{ $r->status === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $r->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-circle"
                                                style="width: 28px; height: 28px; padding: 0;" title="Edit Wilayah"
                                                onclick="openEditRegionModal({{ $r->id }})">
                                                <i class="fe fe-edit-2"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle"
                                                style="width: 28px; height: 28px; padding: 0;" title="Hapus Wilayah"
                                                onclick="deleteRegion({{ $r->id }}, '{{ $r->region_name }}')">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada wilayah yang
                                            didaftarkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-muted small">
                    <i class="fe fe-info me-1"></i> Saat karyawan mengajukan lembur, kode pos kantor absensinya dicocokkan
                    ke Master Kodepos lalu dipetakan ke wilayah di atas.
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FORM DYNAMIC ADD / EDIT MATRIKS & TIERS -->
    <div class="modal fade" id="matrixModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="matrixModalTitle"><i
                            class="fe fe-layers text-primary me-2"></i>Form Matriks Tarif Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="matrixForm">
                    @csrf
                    <input type="hidden" id="matrix_id" name="matrix_id">
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kode Unik Matriks <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="matrix_code" id="modal_matrix_code"
                                    class="form-control rounded-3" placeholder="Contoh: OT_STAFF_SPV_W1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Matriks Lembur <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="matrix_name" id="modal_matrix_name"
                                    class="form-control rounded-3" placeholder="Contoh: Staff - SPV Wilayah 1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kelompok Grading</label>
                                <input type="text" name="grade_group" id="modal_grade_group"
                                    class="form-control rounded-3"
                                    placeholder="Contoh: Grading 8-5, Grading 11, Non Grading">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Target Wilayah Penempatan <span
                                        class="text-danger">*</span></label>
                                <select name="region_type" id="modal_region_type" class="form-select rounded-3" required>
                                    <option value="ALL">Semua Wilayah (Nasional / Umum)</option>
                                    @foreach ($regions as $r)
                                        <option value="{{ $r->region_code }}">{{ $r->region_name }}
                                            ({{ $r->region_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Kata Kunci Jabatan (Pisahkan dengan
                                    koma)</label>
                                <input type="text" name="job_titles" id="modal_job_titles"
                                    class="form-control rounded-3"
                                    placeholder="Contoh: Kru Gudang, Driver, Sopir, OB, QC Officer">
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Jika diisi, kalkulasi
                                    lembur otomatis mencocokkan jabatan pemohon dengan kata kunci ini.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tipe Perhitungan Tarif <span
                                        class="text-danger">*</span></label>
                                <select name="pricing_type" id="modal_pricing_type" class="form-select rounded-3"
                                    required onchange="handlePricingTypeChange()">
                                    <option value="TIERED_HOURLY">TIERED_HOURLY (4 Jam Pertama + Per Jam Selanjutnya)
                                    </option>
                                    <option value="FLAT_TASK">FLAT_TASK (Tarif Tetap Flat Per Penugasan)</option>
                                    <option value="MINUTE_RANGE">MINUTE_RANGE (Range Menit: 0-90 mnt, 91-150 mnt, dst)
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Maks Jam Lembur <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.5" name="max_hours" id="modal_max_hours"
                                    class="form-control rounded-3" value="8" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Status Matriks <span
                                        class="text-danger">*</span></label>
                                <select name="status" id="modal_status" class="form-select rounded-3" required>
                                    <option value="ACTIVE" selected>ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </div>

                        <!-- PENGATURAN TIER DINAMIS -->
                        <div class="border-top pt-3 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-list text-primary me-1"></i>Detail
                                    Tingkatan / Tiers Tarif Matriks</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                    onclick="addTierRow()">
                                    <i class="fe fe-plus me-1"></i> Tambah Baris Tier
                                </button>
                            </div>
                            <small class="text-muted d-block mb-3" style="font-size: 11px;">Atur jenjang menit/jam serta
                                tarif dasar & tarif per jam berikutnya.</small>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle" id="table-tiers">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 180px;">Label Tingkat / Tier</th>
                                            <th style="width: 100px;">Min Menit</th>
                                            <th style="width: 100px;">Maks Menit</th>
                                            <th style="width: 130px;">Tarif Dasar (Rp)</th>
                                            <th style="width: 130px;">+ / Jam Lanjutan (Rp)</th>
                                            <th style="width: 45px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tiers-container">
                                        <!-- Dynamic Tier Rows injected via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm"
                            id="btn-save-matrix">
                            <i class="fe fe-save me-1"></i> Simpan Matriks
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL FORM DYNAMIC ADD / EDIT MASTER WILAYAH -->
    <div class="modal fade" id="regionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="regionModalTitle"><i
                            class="fe fe-map-pin text-primary me-2"></i>Form Master Wilayah Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="regionForm">
                    @csrf
                    <input type="hidden" id="region_id" name="region_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Kode Wilayah <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="region_code" id="modal_reg_code"
                                    class="form-control rounded-3" placeholder="Contoh: WILAYAH_1, WILAYAH_2, WILAYAH_3"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nama Wilayah <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="region_name" id="modal_reg_name"
                                    class="form-control rounded-3" placeholder="Contoh: Wilayah 1 (Jabodetabek)" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Kata Kunci Provinsi / Kota Dicocokkan</label>
                                <textarea name="coverage_keywords" id="modal_reg_keywords" rows="3" class="form-control rounded-3"
                                    placeholder="Contoh: DKI Jakarta, Jakarta, Bogor, Depok, Tangerang, Bekasi (pisahkan dengan koma)"></textarea>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Jika kode pos lokasi
                                    karyawan menghasilkan kota/provinsi ini, wilayah ini akan terpilih otomatis.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Keterangan / Deskripsi</label>
                                <input type="text" name="description" id="modal_reg_desc"
                                    class="form-control rounded-3" placeholder="Keterangan tambahan cakupan wilayah">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Status Wilayah <span
                                        class="text-danger">*</span></label>
                                <select name="status" id="modal_reg_status" class="form-select rounded-3" required>
                                    <option value="ACTIVE" selected>ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm"
                            id="btn-save-region">
                            <i class="fe fe-save me-1"></i> Simpan Wilayah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let modalInstance = null;
        let regionModalInstance = null;
        let currentTierIndex = 0;

        document.addEventListener('DOMContentLoaded', function() {
            modalInstance = new bootstrap.Modal(document.getElementById('matrixModal'));
            regionModalInstance = new bootstrap.Modal(document.getElementById('regionModal'));

            document.getElementById('matrixForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveMatrix();
            });

            document.getElementById('regionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveRegion();
            });
        });

        function openRegionManagementModal() {
            const triggerEl = document.querySelector('#tab-regions');
            if (triggerEl) {
                bootstrap.Tab.getOrCreateInstance(triggerEl).show();
            }
        }

        function openAddRegionModal() {
            document.getElementById('regionForm').reset();
            document.getElementById('region_id').value = '';
            document.getElementById('regionModalTitle').innerHTML =
                '<i class="fe fe-plus-circle text-primary me-2"></i>Tambah Master Wilayah Baru';
            regionModalInstance.show();
        }

        function openEditRegionModal(id) {
            document.getElementById('regionForm').reset();
            document.getElementById('region_id').value = id;
            document.getElementById('regionModalTitle').innerHTML =
                '<i class="fe fe-edit-2 text-warning me-2"></i>Ubah Master Wilayah';
            regionModalInstance.show();

            fetch(`/hrd-overtime-regions/${id}/detail`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        const r = res.data;
                        document.getElementById('modal_reg_code').value = r.region_code;
                        document.getElementById('modal_reg_name').value = r.region_name;
                        document.getElementById('modal_reg_keywords').value = r.coverage_keywords || '';
                        document.getElementById('modal_reg_desc').value = r.description || '';
                        document.getElementById('modal_reg_status').value = r.status;
                    } else {
                        alert(res.message || 'Gagal memuat detail wilayah.');
                        regionModalInstance.hide();
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                    regionModalInstance.hide();
                });
        }

        function saveRegion() {
            const btn = document.getElementById('btn-save-region');
            btn.disabled = true;

            const id = document.getElementById('region_id').value;
            const url = id ? `/hrd-overtime-regions/${id}/update` : `/hrd-overtime-regions/store`;

            const payload = {
                region_code: document.getElementById('modal_reg_code').value,
                region_name: document.getElementById('modal_reg_name').value,
                coverage_keywords: document.getElementById('modal_reg_keywords').value,
                description: document.getElementById('modal_reg_desc').value,
                status: document.getElementById('modal_reg_status').value
            };

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    ok: res.ok,
                    body: data
                })))
                .then(({
                    status,
                    ok,
                    body
                }) => {
                    if (ok && body.status === 'success') {
                        alert(body.message || 'Wilayah berhasil disimpan!');
                        window.location.reload();
                    } else {
                        let errorMsg = body.message || 'Terjadi kendala penyimpanan.';
                        if (body.errors) {
                            const errList = Object.values(body.errors).flat().join('\n');
                            errorMsg += '\n' + errList;
                        }
                        alert('Gagal: ' + errorMsg);
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                    btn.disabled = false;
                });
        }

        function deleteRegion(id, name) {
            if (!confirm(`Hapus wilayah "${name}"? Pastikan tidak ada matriks yang menggunakan wilayah ini.`)) {
                return;
            }

            fetch(`/hrd-overtime-regions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    ok: res.ok,
                    body: data
                })))
                .then(({
                    status,
                    ok,
                    body
                }) => {
                    if (ok && body.status === 'success') {
                        alert(body.message);
                        const row = document.getElementById(`region-row-${id}`);
                        if (row) row.remove();
                    } else {
                        alert(body.message || 'Gagal menghapus wilayah.');
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                });
        }

        function openAddMatrixModal() {
            document.getElementById('matrixForm').reset();
            document.getElementById('matrix_id').value = '';
            document.getElementById('matrixModalTitle').innerHTML =
                '<i class="fe fe-plus-circle text-primary me-2"></i>Tambah Matriks Tarif Lembur Baru';
            document.getElementById('tiers-container').innerHTML = '';
            currentTierIndex = 0;

            addTierRow('4 Jam Pertama', 1, 240, 100000, 0);
            addTierRow('Per Jam Selanjutnya', 241, 480, 100000, 15000);

            modalInstance.show();
        }

        function openEditMatrixModal(id) {
            document.getElementById('matrixForm').reset();
            document.getElementById('matrix_id').value = id;
            document.getElementById('matrixModalTitle').innerHTML =
                '<i class="fe fe-edit-2 text-warning me-2"></i>Ubah Matriks Tarif Lembur';
            document.getElementById('tiers-container').innerHTML =
                '<tr><td colspan="6" class="text-center py-3 text-muted">Memuat data...</td></tr>';
            currentTierIndex = 0;

            modalInstance.show();

            fetch(`/hrd-overtime-matrix/${id}/detail`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        const m = res.matrix;
                        document.getElementById('modal_matrix_code').value = m.matrix_code;
                        document.getElementById('modal_matrix_name').value = m.matrix_name;
                        document.getElementById('modal_grade_group').value = m.grade_group || '';
                        document.getElementById('modal_region_type').value = m.region_type;
                        document.getElementById('modal_job_titles').value = res.job_titles_str || '';
                        document.getElementById('modal_pricing_type').value = m.pricing_type;
                        document.getElementById('modal_max_hours').value = m.max_hours;
                        document.getElementById('modal_status').value = m.status;

                        document.getElementById('tiers-container').innerHTML = '';
                        const tiers = res.tiers || [];
                        if (tiers.length > 0) {
                            tiers.forEach(t => {
                                addTierRow(t.tier_label, t.min_minutes, t.max_minutes, t.base_rate, t
                                    .next_per_hour_rate);
                            });
                        } else {
                            addTierRow('Default Tier', 1, null, 50000, 0);
                        }
                    } else {
                        alert(res.message || 'Gagal memuat detail matriks');
                        modalInstance.hide();
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                    modalInstance.hide();
                });
        }

        function addTierRow(label = '', min = 0, max = '', base = 0, nextRate = 0) {
            const idx = currentTierIndex++;
            const maxVal = (max !== null && max !== undefined) ? max : '';
            const row = document.createElement('tr');
            row.id = `tier-row-${idx}`;
            row.innerHTML = `
        <td>
            <input type="text" name="tiers[${idx}][tier_label]" class="form-control form-control-sm rounded-2" placeholder="e.g. 0 - 90 menit" value="${label}" required>
        </td>
        <td>
            <input type="number" name="tiers[${idx}][min_minutes]" class="form-control form-control-sm rounded-2" value="${min}" required>
        </td>
        <td>
            <input type="number" name="tiers[${idx}][max_minutes]" class="form-control form-control-sm rounded-2" placeholder="Unset" value="${maxVal}">
        </td>
        <td>
            <input type="number" step="1000" name="tiers[${idx}][base_rate]" class="form-control form-control-sm rounded-2 text-end fw-semibold text-primary" value="${base}" required>
        </td>
        <td>
            <input type="number" step="1000" name="tiers[${idx}][next_per_hour_rate]" class="form-control form-control-sm rounded-2 text-end fw-semibold text-danger" value="${nextRate}">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger p-1 rounded-circle" onclick="removeTierRow(${idx})">
                <i class="fe fe-x"></i>
            </button>
        </td>
    `;
            document.getElementById('tiers-container').appendChild(row);
        }

        function removeTierRow(idx) {
            const rows = document.querySelectorAll('#tiers-container tr');
            if (rows.length <= 1) {
                alert('Setidaknya harus memiliki minimal 1 tingkatan (tier) konfigurasi.');
                return;
            }
            const target = document.getElementById(`tier-row-${idx}`);
            if (target) {
                target.remove();
            }
        }

        function handlePricingTypeChange() {
            const type = document.getElementById('modal_pricing_type').value;
            if (type === 'FLAT_TASK') {
                const rows = document.querySelectorAll('#tiers-container tr');
                if (rows.length === 0) {
                    addTierRow('Per Penugasan (Flat)', 1, '', 50000, 0);
                }
            }
        }

        function saveMatrix() {
            const btn = document.getElementById('btn-save-matrix');
            btn.disabled = true;

            const id = document.getElementById('matrix_id').value;
            const url = id ? `/hrd-overtime-matrix/${id}/update` : `/hrd-overtime-matrix/store`;

            const payload = {
                matrix_code: document.getElementById('modal_matrix_code').value,
                matrix_name: document.getElementById('modal_matrix_name').value,
                grade_group: document.getElementById('modal_grade_group').value,
                region_type: document.getElementById('modal_region_type').value,
                job_titles: document.getElementById('modal_job_titles').value,
                pricing_type: document.getElementById('modal_pricing_type').value,
                max_hours: document.getElementById('modal_max_hours').value,
                status: document.getElementById('modal_status').value,
                tiers: []
            };

            const tierRows = document.querySelectorAll('#tiers-container tr');
            tierRows.forEach(tr => {
                const label = tr.querySelector('input[name*="[tier_label]"]').value;
                const min = tr.querySelector('input[name*="[min_minutes]"]').value;
                const max = tr.querySelector('input[name*="[max_minutes]"]').value;
                const base = tr.querySelector('input[name*="[base_rate]"]').value;
                const next = tr.querySelector('input[name*="[next_per_hour_rate]"]').value;

                payload.tiers.push({
                    tier_label: label,
                    min_minutes: min,
                    max_minutes: max,
                    base_rate: base,
                    next_per_hour_rate: next
                });
            });

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    ok: res.ok,
                    body: data
                })))
                .then(({
                    status,
                    ok,
                    body
                }) => {
                    if (ok && body.status === 'success') {
                        alert(body.message || 'Matriks berhasil disimpan!');
                        window.location.reload();
                    } else {
                        let errorMsg = body.message || 'Terjadi kendala penyimpanan.';
                        if (body.errors) {
                            const errList = Object.values(body.errors).flat().join('\n');
                            errorMsg += '\n' + errList;
                        }
                        alert('Gagal: ' + errorMsg);
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                    btn.disabled = false;
                });
        }

        function deleteMatrix(id, name) {
            if (!confirm(
                    `Apakah Anda yakin ingin menghapus matriks tarif "${name}"? Perhitungan lembur yang cocok akan terpengaruh.`
                )) {
                return;
            }

            fetch(`/hrd-overtime-matrix/${id}`, {
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
                        const card = document.getElementById(`matrix-card-${id}`);
                        if (card) card.remove();
                    } else {
                        alert(res.message || 'Gagal menghapus matriks.');
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                });
        }
    </script>
@endsection
