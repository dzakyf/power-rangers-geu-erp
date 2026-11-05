@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@php
    $allowMap = [];
    foreach ($allowances as $a) {
        $allowMap[$a->trip_type_key][$a->job_level_key] = $a;
    }
    $rp = function ($v) { return ($v !== null && $v !== '' && (float) $v > 0) ? 'Rp ' . number_format($v, 0, ',', '.') : '-'; };
@endphp

<style>
/* Modern styling for Master SPPD Regulation */
.sppd-page-wrapper {
    margin-bottom: 2rem;
}
.sppd-header-card {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    border-radius: 14px;
    padding: 1.5rem 1.75rem;
    box-shadow: 0 4px 14px rgba(30, 58, 138, 0.15);
    color: #fff;
    margin-top: 1rem;
    margin-bottom: 1.5rem;
}
.sppd-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    background: #ffffff;
    overflow: hidden;
}
.sppd-tabs {
    border-bottom: 1px solid #eef2f6;
    padding: 0.75rem 1.25rem 0;
    gap: 0.5rem;
    background: #ffffff;
    flex-wrap: nowrap;
    overflow-x: auto;
}
.sppd-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.875rem;
    white-space: nowrap;
    padding: 0.75rem 1.25rem;
    border-radius: 8px 8px 0 0;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.sppd-tabs .nav-link:hover {
    color: #2563eb;
    background: #f8fafc;
}
.sppd-tabs .nav-link.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
    background: #eff6ff;
}
.sppd-btn {
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.85rem;
    padding: 0.45rem 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
}
.sppd-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.08);
}
.sppd-btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.15s ease;
}
.sppd-btn-icon:hover {
    transform: scale(1.06);
}
.sppd-pill {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.65rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.sppd-pill.blue { background: #e0f2fe; color: #0284c7; }
.sppd-pill.green { background: #dcfce7; color: #16a34a; }
.sppd-pill.amber { background: #fef3c7; color: #d97706; }
.sppd-pill.purple { background: #f3e8ff; color: #9333ea; }
.sppd-pill.gray { background: #f1f5f9; color: #475569; }

/* Table styling */
.sppd-table {
    margin: 0;
    font-size: 0.86rem;
    border-collapse: separate;
    border-spacing: 0;
}
.sppd-table thead th {
    background: #f8fafc;
    color: #334155;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    vertical-align: middle;
}
.sppd-table tbody td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.sppd-table tbody tr:hover td {
    background-color: #f8fafc;
}

/* Matrix Table Specifics */
.sppd-matrix-box {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    margin: 0 1.25rem 1.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.sppd-matrix-header {
    background: #f8fafc;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.sppd-matrix thead th.lvl-head {
    text-align: center;
    min-width: 140px;
    border-left: 1px solid #f1f5f9;
}
.sppd-matrix tbody td.cell-data {
    text-align: center;
    border-left: 1px solid #f1f5f9;
}
.sppd-matrix tbody td.cell-label {
    font-weight: 600;
    color: #1e293b;
    background: #ffffff;
    min-width: 220px;
}
.sppd-matrix tbody tr.action-row td {
    background: #fafcff;
    border-top: 1px dashed #cbd5e1;
    padding: 0.85rem 1rem;
}

/* Custom Modal UI */
.modal-content.sppd-modal {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}
.sppd-modal .modal-header {
    background: linear-gradient(135deg, #179412 0%, #3d62c7 100%);
    color: #ffffff;
    padding: 1.15rem 1.5rem;
    border-bottom: none;
}
.sppd-modal .modal-header .modal-title {
    font-size: 1.05rem;
    font-weight: 600;
    letter-spacing: 0.2px;
    color: #ffffff;
}
.sppd-modal .modal-body {
    padding: 1.5rem;
    background: #f8fafc;
}
.sppd-modal .form-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.sppd-modal .form-section-title {
    font-size: 0.78rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
    margin-bottom: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.sppd-modal .form-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.35rem;
}
.sppd-modal .form-control, .sppd-modal .form-select {
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}
.sppd-modal .form-control:focus, .sppd-modal .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.sppd-modal .modal-footer {
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    padding: 0.95rem 1.5rem;
}
</style>

<div class="sppd-page-wrapper">
    {{-- Modern Breadcrumb Header --}}
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Master Regulasi & Plafon SPPD
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Perjalanan Dinas</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Konfigurasi Matriks Plafon</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <div class="d-flex gap-2 align-items-center">
                <a href="hrd-sppd-index" class="btn btn-outline-light sppd-btn shadow-sm">
                    <i class="fa fa-arrow-left text-muted"></i> Kembali
                </a>
                <button type="button" class="btn btn-outline-warning sppd-btn text-dark shadow-sm fw-bold" onclick="syncFromJson()" title="Reset data awal dari matric.json">
                    <i class="fa fa-rotate me-1"></i> Reset/Sync dari JSON
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card sppd-card">
        <!-- Tabs Header -->
        <ul class="nav sppd-tabs" id="masterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="matrix-tab" data-bs-toggle="tab" data-toggle="tab" href="#matrix-content" role="tab" aria-controls="matrix-content" aria-selected="true">
                    <i class="fa fa-table-cells text-primary"></i> Matriks Plafon
                    <span class="badge bg-primary text-white rounded-pill ms-1">{{ count($tripTypes) }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="component-tab" data-bs-toggle="tab" data-toggle="tab" href="#component-content" role="tab" aria-controls="component-content" aria-selected="false">
                    <i class="fa fa-list-check text-info"></i> Komponen Tunjangan
                    <span class="badge bg-secondary text-white rounded-pill ms-1">{{ count($components) }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="joblevel-tab" data-bs-toggle="tab" data-toggle="tab" href="#joblevel-content" role="tab" aria-controls="joblevel-content" aria-selected="false">
                    <i class="fa fa-user-tie text-success"></i> Level Pekerjaan
                    <span class="badge bg-secondary text-white rounded-pill ms-1">{{ count($jobLevels) }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="triptype-tab" data-bs-toggle="tab" data-toggle="tab" href="#triptype-content" role="tab" aria-controls="triptype-content" aria-selected="false">
                    <i class="fa fa-plane-departure text-warning"></i> Jenis Perjalanan
                    <span class="badge bg-secondary text-white rounded-pill ms-1">{{ count($tripTypes) }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content" id="masterTabsContent">

            <!-- TAB 1: MATRIKS PLAFON -->
            <div class="tab-pane fade show active" id="matrix-content" role="tabpanel" aria-labelledby="matrix-tab">
                @php
                    $linkedComponents = $components->filter(function ($c) use ($componentMap) { return isset($componentMap[$c->code]); })->values();
                    $unlinkedComponents = $components->filter(function ($c) use ($componentMap) { return !isset($componentMap[$c->code]); });
                    $fmt = function ($allw, $def) use ($rp) {
                        if (!$allw) return '<span class="text-muted opacity-50">-</span>';
                        $v = $allw->{$def['column']};
                        if ($def['format'] === 'list') {
                            $arr = is_string($v) ? json_decode($v, true) : $v;
                            return (is_array($arr) && count($arr)) ? '<span class="fw-semibold text-dark">' . e(implode(', ', $arr)) . '</span>' : '<span class="text-muted opacity-50">-</span>';
                        }
                        if ($v === null || $v === '' || ($def['format'] !== 'text' && (float) $v <= 0)) {
                            return $def['column'] === 'ticket_max' ? '<span class="sppd-pill gray">Tak terbatas</span>' : '<span class="text-muted opacity-50">-</span>';
                        }
                        if ($def['format'] === 'money') return '<span class="fw-semibold text-dark">' . e($rp($v)) . '</span>';
                        if ($def['format'] === 'star') return '<span class="text-warning"><i class="fa fa-star me-1"></i>' . e($v) . '</span>';
                        return '<span class="fw-semibold text-dark">' . e($v) . '</span>';
                    };
                @endphp

                <div class="p-3 pb-2">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="fa fa-layer-group text-primary"></i> Plafon &amp; Tunjangan per Skenario Perjalanan
                            </h6>
                            <p class="text-muted small mb-0">
                                Baris matriks terhubung langsung dengan master <b>Komponen Tunjangan</b>. Klik tombol <b>Edit Plafon</b> di baris terbawah pada masing-masing level untuk mengatur nilai.
                            </p>
                        </div>
                    </div>

                    @if($unlinkedComponents->count())
                    <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small d-flex align-items-center gap-2 border-0 shadow-sm rounded-3">
                        <i class="fa fa-triangle-exclamation text-warning fs-14"></i>
                        <div>
                            <strong>{{ $unlinkedComponents->count() }} komponen belum terhubung ke matriks:</strong>
                            <span class="text-muted">{{ $unlinkedComponents->pluck('name')->implode(', ') }}</span>.
                            <small class="d-block text-secondary">Pastikan memilih kode yang sesuai di tab Komponen Tunjangan agar otomatis masuk ke perhitungan plafon.</small>
                        </div>
                    </div>
                    @endif
                </div>

                @foreach($tripTypes as $tt)
                <div class="sppd-matrix-box">
                    <div class="sppd-matrix-header">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="avatar avatar-xs bg-primary-transparent text-primary rounded-2">
                                <i class="fa fa-tag"></i>
                            </span>
                            <span class="fw-bold text-dark fs-14">{{ $tt->name }}</span>
                            @if($tt->duration)
                                <span class="sppd-pill blue"><i class="fa fa-clock me-1"></i>{{ $tt->duration }}</span>
                            @endif
                        </div>
                        @if($tt->note)
                            <div class="small text-muted fst-italic"><i class="fa fa-circle-info me-1"></i>{{ $tt->note }}</div>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table sppd-table sppd-matrix align-middle">
                            <thead>
                                <tr>
                                    <th style="min-width: 220px;" class="ps-3">Komponen Tunjangan</th>
                                    @foreach($jobLevels as $jl)
                                    <th class="lvl-head">
                                        <div class="fw-bold text-dark">{{ $jl->label }}</div>
                                        <div class="small text-muted fw-normal">Grade {{ $jl->grade }}</div>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($linkedComponents as $comp)
                                @php $def = $componentMap[$comp->code]; @endphp
                                <tr>
                                    <td class="cell-label ps-3">
                                        <div class="fw-semibold text-dark">{{ $comp->name }}</div>
                                        <div class="small text-muted">{{ $comp->category }}</div>
                                    </td>
                                    @foreach($jobLevels as $jl)
                                    <td class="cell-data">{!! $fmt($allowMap[$tt->type_key][$jl->level] ?? null, $def) !!}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                                <tr class="action-row">
                                    <td class="cell-label ps-3">
                                        <span class="fw-bold text-primary"><i class="fa fa-pen-to-square me-1"></i> Aksi Konfigurasi</span>
                                    </td>
                                    @foreach($jobLevels as $jl)
                                    <td class="cell-data">
                                        <button type="button" class="btn btn-sm btn-outline-primary sppd-btn w-100 justify-content-center"
                                            onclick="editAllowance('{{ $tt->type_key }}', '{{ $jl->level }}', '{{ addslashes($tt->name) }}', '{{ addslashes($jl->label) }}')">
                                            <i class="fa fa-pen-to-square"></i> Edit Plafon
                                        </button>
                                    </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- TAB 2: KOMPONEN TUNJANGAN -->
            <div class="tab-pane fade" id="component-content" role="tabpanel" aria-labelledby="component-tab">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 pb-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-1"><i class="fa fa-list-check text-info me-1"></i> Komponen Tunjangan SPPD</h6>
                        <p class="text-muted small mb-0">Daftar item biaya dan plafon standar perjalanan dinas.</p>
                    </div>
                    <button class="btn btn-success sppd-btn text-white shadow-sm" onclick="addComponent()">
                        <i class="fa fa-plus"></i> Tambah Komponen
                    </button>
                </div>
                <div class="table-responsive p-3 pt-0">
                    <table class="table sppd-table align-middle border rounded-3 overflow-hidden">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">ID</th>
                                <th style="width: 170px;">Kode Key</th>
                                <th>Nama Komponen</th>
                                <th style="width: 140px;">Kategori</th>
                                <th style="width: 140px;">Tipe Input</th>
                                <th style="width: 130px;" class="text-center">Status Matriks</th>
                                <th>Deskripsi Regulasi</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($components as $comp)
                            <tr>
                                <td class="text-center text-muted small">{{ $comp->id }}</td>
                                <td><code class="text-primary bg-light px-2 py-1 rounded">{{ $comp->code }}</code></td>
                                <td class="fw-bold text-dark">{{ $comp->name }}</td>
                                <td><span class="sppd-pill blue">{{ $comp->category }}</span></td>
                                <td><span class="sppd-pill gray">{{ $comp->type }}</span></td>
                                <td class="text-center">
                                    @if(isset($componentMap[$comp->code]))
                                        <span class="sppd-pill green"><i class="fa fa-check-circle"></i> Terhubung</span>
                                    @else
                                        <span class="sppd-pill amber" title="Kode tidak terdaftar di skema matriks"><i class="fa fa-triangle-exclamation"></i> Belum link</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $comp->description ?: '-' }}</td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-outline-primary sppd-btn-icon me-1" title="Edit Komponen" onclick="editComponent({{ $comp->id }}, '{{ $comp->code }}', '{{ addslashes($comp->name) }}', '{{ addslashes($comp->category) }}', '{{ addslashes($comp->type) }}', '{{ addslashes($comp->description) }}')">
                                        <i class="fa fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-danger sppd-btn-icon" title="Hapus Komponen" onclick="deleteComponent({{ $comp->id }}, '{{ addslashes($comp->name) }}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open text-muted fs-3 mb-2 d-block"></i> Belum ada komponen tunjangan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: JOB LEVEL -->
            <div class="tab-pane fade" id="joblevel-content" role="tabpanel" aria-labelledby="joblevel-tab">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 pb-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-1"><i class="fa fa-user-tie text-success me-1"></i> Master Level Pekerjaan</h6>
                        <p class="text-muted small mb-0">Tingkatan jabatan dan grade yang menjadi kolom penentu plafon.</p>
                    </div>
                    <button class="btn btn-success sppd-btn text-white shadow-sm" onclick="addJobLevel()">
                        <i class="fa fa-plus"></i> Tambah Job Level
                    </button>
                </div>
                <div class="table-responsive p-3 pt-0">
                    <table class="table sppd-table align-middle border rounded-3 overflow-hidden">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">ID</th>
                                <th style="width: 180px;">Key Level</th>
                                <th style="width: 150px;">Grade Range</th>
                                <th>Label Display</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobLevels as $jl)
                            <tr>
                                <td class="text-center text-muted small">{{ $jl->id }}</td>
                                <td><code class="text-success bg-light px-2 py-1 rounded">{{ $jl->level }}</code></td>
                                <td><span class="sppd-pill gray font-monospace">{{ $jl->grade }}</span></td>
                                <td class="fw-bold text-dark">{{ $jl->label }}</td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-outline-primary sppd-btn-icon me-1" title="Edit Level" onclick="editJobLevel({{ $jl->id }}, '{{ $jl->level }}', '{{ $jl->grade }}', '{{ addslashes($jl->label) }}')">
                                        <i class="fa fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-danger sppd-btn-icon" title="Hapus Level" onclick="deleteJobLevel({{ $jl->id }}, '{{ addslashes($jl->label) }}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open text-muted fs-3 mb-2 d-block"></i> Belum ada level pekerjaan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: TRIP TYPE -->
            <div class="tab-pane fade" id="triptype-content" role="tabpanel" aria-labelledby="triptype-tab">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 pb-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-1"><i class="fa fa-plane-departure text-warning me-1"></i> Master Jenis Perjalanan Dinas</h6>
                        <p class="text-muted small mb-0">Skenario mobilitas dinas karyawan (misal: tugas reguler, pelatihan, relokasi).</p>
                    </div>
                    <button class="btn btn-success sppd-btn text-white shadow-sm" onclick="addTripType()">
                        <i class="fa fa-plus"></i> Tambah Jenis Perjalanan
                    </button>
                </div>
                <div class="table-responsive p-3 pt-0">
                    <table class="table sppd-table align-middle border rounded-3 overflow-hidden">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">ID</th>
                                <th style="width: 180px;">Key Type</th>
                                <th>Nama Jenis Perjalanan</th>
                                <th style="width: 160px;">Durasi Standar</th>
                                <th>Catatan Regulasi</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tripTypes as $tt)
                            <tr>
                                <td class="text-center text-muted small">{{ $tt->id }}</td>
                                <td><code class="text-warning bg-light px-2 py-1 rounded">{{ $tt->type_key }}</code></td>
                                <td class="fw-bold text-dark">{{ $tt->name }}</td>
                                <td><span class="sppd-pill blue">{{ $tt->duration ?: '-' }}</span></td>
                                <td class="text-muted small">{{ $tt->note ?: '-' }}</td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-outline-primary sppd-btn-icon me-1" title="Edit Jenis" onclick="editTripType({{ $tt->id }}, '{{ $tt->type_key }}', '{{ addslashes($tt->name) }}', '{{ addslashes($tt->duration) }}', '{{ addslashes($tt->note) }}')">
                                        <i class="fa fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-danger sppd-btn-icon" title="Hapus Jenis" onclick="deleteTripType({{ $tt->id }}, '{{ addslashes($tt->name) }}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open text-muted fs-3 mb-2 d-block"></i> Belum ada jenis perjalanan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODALS INTERAKTIF (WARNA & UI MODERN)                    -->
<!-- ========================================================= -->

<!-- 1. MODAL ALLOWANCE EDIT -->
<div class="modal fade" id="modalAllowance" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content sppd-modal">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalAllowanceTitle">
                    <i class="fa fa-sliders"></i> Edit Plafon Tunjangan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAllowance" onsubmit="submitAllowance(event)">
                <div class="modal-body">
                    <input type="hidden" id="allowance_trip_type_key" name="trip_type_key">
                    <input type="hidden" id="allowance_job_level_key" name="job_level_key">

                    <!-- Transportasi Section -->
                    <div class="form-card">
                        <div class="form-section-title"><i class="fa fa-plane text-primary"></i> Transportasi &amp; Tiket</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Transportasi Darat</label>
                                <input type="text" class="form-control" id="allowance_transport_land" name="transport_land" placeholder="Contoh: kereta ekonomi, bus ekonomi">
                                <div class="form-text fs-11 text-muted">Pisahkan dengan koma jika lebih dari satu</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Transportasi Udara</label>
                                <input type="text" class="form-control" id="allowance_transport_air" name="transport_air" placeholder="Contoh: pesawat ekonomi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maksimal Harga Tiket (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fs-12">Rp</span>
                                    <input type="number" class="form-control" id="allowance_ticket_max" name="ticket_max" placeholder="Kosongkan jika tak terbatas">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Akomodasi & Harian Section -->
                    <div class="form-card">
                        <div class="form-section-title"><i class="fa fa-hotel text-info"></i> Akomodasi &amp; Uang Saku Harian</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Max Reimburse Hotel / Malam</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fs-12">Rp</span>
                                    <input type="number" class="form-control" id="allowance_reimburse_max_per_night" name="reimburse_max_per_night" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bintang Hotel</label>
                                <select class="form-select" id="allowance_hotel_star" name="hotel_star">
                                    <option value="">- Tidak Ditentukan -</option>
                                    <option value="1">★ 1 Bintang</option>
                                    <option value="2">★★ 2 Bintang</option>
                                    <option value="3">★★★ 3 Bintang</option>
                                    <option value="4">★★★★ 4 Bintang</option>
                                    <option value="5">★★★★★ 5 Bintang</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Uang Makan / Hari</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fs-12">Rp</span>
                                    <input type="number" class="form-control" id="allowance_daily_meal" name="daily_meal" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Uang Saku / Hari</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fs-12">Rp</span>
                                    <input type="number" class="form-control" id="allowance_daily_pocket" name="daily_pocket" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bantuan Khusus Section -->
                    <div class="form-card mb-0">
                        <div class="form-section-title"><i class="fa fa-hand-holding-dollar text-warning"></i> Bantuan Khusus &amp; Mobilisasi</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Bantuan Kos / Bulan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fs-12">Rp</span>
                                    <input type="number" class="form-control" id="allowance_kos_per_month" name="kos_per_month" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan Bantuan Kos</label>
                                <input type="text" class="form-control" id="allowance_assistance_note" name="assistance_note" placeholder="Ketentuan kos...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bantuan Relokasi (One-time)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fs-12">Rp</span>
                                    <input type="number" class="form-control" id="allowance_relocation_one_time_payment" name="relocation_one_time_payment" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan Relokasi</label>
                                <input type="text" class="form-control" id="allowance_relocation_note" name="relocation_note" placeholder="Ketentuan relokasi...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Uang Perbantuan / Minggu</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fs-12">Rp</span>
                                    <input type="number" class="form-control" id="allowance_mobilization_cost_per_week" name="mobilization_cost_per_week" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan Mobilisasi</label>
                                <input type="text" class="form-control" id="allowance_mobilization_note" name="mobilization_note" placeholder="Ketentuan mobilisasi...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light sppd-btn text-dark border" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary sppd-btn"><i class="fa fa-save"></i> Simpan Plafon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. MODAL JOB LEVEL -->
<div class="modal fade" id="modalJobLevel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content sppd-modal">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalJobLevelTitle">
                    <i class="fa fa-user-tie"></i> Level Pekerjaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formJobLevel" onsubmit="submitJobLevel(event)">
                <div class="modal-body">
                    <input type="hidden" id="joblevel_id">
                    <div class="form-card mb-0">
                        <div class="mb-3">
                            <label class="form-label">Key Level <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="joblevel_level" required placeholder="Contoh: pelaksana, senior_middle">
                            <div class="form-text fs-11 text-muted">Gunakan huruf kecil tanpa spasi (snake_case)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grade Range <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="joblevel_grade" required placeholder="Contoh: 11-9, 8-7, dsb">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Label Tampilan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="joblevel_label" required placeholder="Contoh: Pelaksana, Senior Middle">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light sppd-btn text-dark border" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary sppd-btn"><i class="fa fa-save"></i> Simpan Level</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. MODAL TRIP TYPE -->
<div class="modal fade" id="modalTripType" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content sppd-modal">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalTripTypeTitle">
                    <i class="fa fa-plane-departure"></i> Jenis Perjalanan Dinas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTripType" onsubmit="submitTripType(event)">
                <div class="modal-body">
                    <input type="hidden" id="triptype_id">
                    <div class="form-card mb-0">
                        <div class="mb-3">
                            <label class="form-label">Key Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="triptype_key" required placeholder="Contoh: tugas_kerja, relokasi">
                            <div class="form-text fs-11 text-muted">Gunakan huruf kecil tanpa spasi (snake_case)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Jenis Perjalanan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="triptype_name" required placeholder="Contoh: Tugas Kerja">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Durasi Standard</label>
                            <input type="text" class="form-control" id="triptype_duration" placeholder="Contoh: < 2 minggu">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Catatan Regulasi</label>
                            <textarea class="form-control" id="triptype_note" rows="2" placeholder="Catatan mengenai jenis perjalanan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light sppd-btn text-dark border" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary sppd-btn"><i class="fa fa-save"></i> Simpan Jenis</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. MODAL COMPONENT TUNJANGAN -->
<div class="modal fade" id="modalComponent" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content sppd-modal">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalComponentTitle">
                    <i class="fa fa-list-check"></i> Komponen Tunjangan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formComponent" onsubmit="submitComponent(event)">
                <div class="modal-body">
                    <input type="hidden" id="component_id">
                    <div class="form-card mb-0">
                        <div class="mb-3">
                            <label class="form-label">Kode Komponen Matriks <span class="text-danger">*</span></label>
                            <select class="form-select" id="component_code" required onchange="onComponentCodeChange()">
                                @foreach($componentMap as $code => $def)
                                <option value="{{ $code }}">{{ $def['name'] }} ({{ $code }})</option>
                                @endforeach
                            </select>
                            <div class="form-text fs-11 text-muted">Menentukan kolom tujuan pada tabel matriks plafon</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="component_name" required placeholder="Contoh: Uang Makan / Hari">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" id="component_category" required>
                                    <option value="Transportasi">Transportasi</option>
                                    <option value="Akomodasi">Akomodasi</option>
                                    <option value="Uang Saku">Uang Saku</option>
                                    <option value="Bantuan Khusus">Bantuan Khusus</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipe Input</label>
                                <select class="form-select" id="component_type" required>
                                    <option value="Nominal (Rp)">Nominal (Rp)</option>
                                    <option value="Teks">Teks</option>
                                    <option value="Teks / List Pilihan">Teks / List Pilihan</option>
                                    <option value="Angka (Star)">Angka (Star)</option>
                                    <option value="Angka">Angka</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Keterangan / Deskripsi Regulasi</label>
                            <textarea class="form-control" id="component_description" rows="2" placeholder="Penjelasan mengenai komponen regulasi ini..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light sppd-btn text-dark border" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary sppd-btn"><i class="fa fa-save"></i> Simpan Komponen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Kompatibel Bootstrap 5 (data-bs-*) dan jQuery plugin
(function () {
    if (window.bootstrap && bootstrap.Modal) {
        $.fn.modal = function (action) {
            return this.each(function () {
                var inst = bootstrap.Modal.getOrCreateInstance(this);
                if (action === 'show') inst.show(); else if (action === 'hide') inst.hide();
            });
        };
    }
    var h = location.hash;
    if (h && $('a[href="' + h + '"][role="tab"]').length) {
        var el = document.querySelector('a[href="' + h + '"][role="tab"]');
        if (window.bootstrap && bootstrap.Tab) bootstrap.Tab.getOrCreateInstance(el).show(); else $(el).tab('show');
    }
    $(document).on('shown.bs.tab click', '#masterTabs a[role="tab"]', function () {
        history.replaceState(null, '', this.getAttribute('href'));
    });
})();

var rawAllowances = @json($allowances);
var componentDefaults = @json($componentMap);
var componentCodesUsed = @json($components->pluck('code')->values());
var linkedComponentCodes = @json($components->pluck('code')->filter(function ($c) use ($componentMap) { return isset($componentMap[$c]); })->values());

// Field plafon hanya tampil bila komponennya ada di master Komponen Tunjangan
function syncAllowanceFields() {
    Object.keys(componentDefaults).forEach(function (code) {
        var wrap = $('#allowance_' + code).closest('[class*="col-md-"]');
        wrap.toggle(linkedComponentCodes.indexOf(code) !== -1);
    });
}

function editAllowance(tripTypeKey, jobLevelKey, tripTypeName, jobLevelLabel) {
    syncAllowanceFields();
    $('#modalAllowanceTitle').html('<i class="fa fa-sliders me-2"></i>Edit Plafon: ' + $('<div>').text(tripTypeName + ' - ' + jobLevelLabel).html());
    $('#allowance_trip_type_key').val(tripTypeKey);
    $('#allowance_job_level_key').val(jobLevelKey);

    var allw = rawAllowances.find(function(item) {
        return item.trip_type_key === tripTypeKey && item.job_level_key === jobLevelKey;
    });

    if (allw) {
        var land = allw.transport_land;
        if (typeof land === 'string') {
            try { land = JSON.parse(land); } catch(e){}
        }
        $('#allowance_transport_land').val(Array.isArray(land) ? land.join(', ') : (land || ''));
        $('#allowance_transport_air').val(allw.transport_air || '');
        $('#allowance_ticket_max').val(allw.ticket_max !== null ? allw.ticket_max : '');
        $('#allowance_reimburse_max_per_night').val(allw.reimburse_max_per_night !== null ? allw.reimburse_max_per_night : '');
        $('#allowance_hotel_star').val(allw.hotel_star !== null ? allw.hotel_star : '');
        $('#allowance_daily_meal').val(allw.daily_meal !== null ? allw.daily_meal : '');
        $('#allowance_daily_pocket').val(allw.daily_pocket !== null ? allw.daily_pocket : '');
        $('#allowance_kos_per_month').val(allw.kos_per_month !== null ? allw.kos_per_month : '');
        $('#allowance_relocation_one_time_payment').val(allw.relocation_one_time_payment !== null ? allw.relocation_one_time_payment : '');
        $('#allowance_mobilization_cost_per_week').val(allw.mobilization_cost_per_week !== null ? allw.mobilization_cost_per_week : '');
        $('#allowance_mobilization_note').val(allw.mobilization_note || '');
        $('#allowance_assistance_note').val(allw.assistance_note || '');
        $('#allowance_relocation_note').val(allw.relocation_note || '');
    } else {
        $('#formAllowance')[0].reset();
        $('#allowance_trip_type_key').val(tripTypeKey);
        $('#allowance_job_level_key').val(jobLevelKey);
    }

    $('#modalAllowance').modal('show');
}

function submitAllowance(e) {
    e.preventDefault();
    $.ajax({
        url: 'hrd-sppd-master-allowance-save',
        type: 'POST',
        data: $('#formAllowance').serialize(),
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Terjadi kesalahan saat menyimpan plafon');
        }
    });
}

// Job Level Functions
function addJobLevel() {
    $('#joblevel_id').val('');
    $('#joblevel_level').val('').prop('readonly', false);
    $('#joblevel_grade').val('');
    $('#joblevel_label').val('');
    $('#modalJobLevelTitle').html('<i class="fa fa-user-tie me-2"></i>Tambah Level Pekerjaan Baru');
    $('#modalJobLevel').modal('show');
}

function editJobLevel(id, level, grade, label) {
    $('#joblevel_id').val(id);
    $('#joblevel_level').val(level).prop('readonly', true);
    $('#joblevel_grade').val(grade);
    $('#joblevel_label').val(label);
    $('#modalJobLevelTitle').html('<i class="fa fa-user-tie me-2"></i>Edit Level Pekerjaan');
    $('#modalJobLevel').modal('show');
}

function submitJobLevel(e) {
    e.preventDefault();
    var id = $('#joblevel_id').val();
    var url = id ? ('hrd-sppd-master-job-level-update/' + id) : 'hrd-sppd-master-job-level-store';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            level: $('#joblevel_level').val(),
            grade: $('#joblevel_grade').val(),
            label: $('#joblevel_label').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Gagal menyimpan Job Level');
        }
    });
}

function deleteJobLevel(id, label) {
    if (!confirm('Apakah Anda yakin ingin menghapus Job Level "' + label + '"? Data matriks terkait juga akan terhapus.')) return;
    $.ajax({
        url: 'hrd-sppd-master-job-level-delete/' + id,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        }
    });
}

// Trip Type Functions
function addTripType() {
    $('#triptype_id').val('');
    $('#triptype_key').val('').prop('readonly', false);
    $('#triptype_name').val('');
    $('#triptype_duration').val('');
    $('#triptype_note').val('');
    $('#modalTripTypeTitle').html('<i class="fa fa-plane-departure me-2"></i>Tambah Jenis Perjalanan Dinas');
    $('#modalTripType').modal('show');
}

function editTripType(id, key, name, duration, note) {
    $('#triptype_id').val(id);
    $('#triptype_key').val(key).prop('readonly', true);
    $('#triptype_name').val(name);
    $('#triptype_duration').val(duration);
    $('#triptype_note').val(note);
    $('#modalTripTypeTitle').html('<i class="fa fa-plane-departure me-2"></i>Edit Jenis Perjalanan Dinas');
    $('#modalTripType').modal('show');
}

function submitTripType(e) {
    e.preventDefault();
    var id = $('#triptype_id').val();
    var url = id ? ('hrd-sppd-master-trip-type-update/' + id) : 'hrd-sppd-master-trip-type-store';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            type_key: $('#triptype_key').val(),
            name: $('#triptype_name').val(),
            duration: $('#triptype_duration').val(),
            note: $('#triptype_note').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Gagal menyimpan Jenis Perjalanan');
        }
    });
}

function deleteTripType(id, name) {
    if (!confirm('Apakah Anda yakin ingin menghapus Jenis Perjalanan "' + name + '"? Data matriks terkait juga akan terhapus.')) return;
    $.ajax({
        url: 'hrd-sppd-master-trip-type-delete/' + id,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        }
    });
}

// Component Functions
function addComponent() {
    $('#component_id').val('');
    $('#component_code option[data-extra]').remove();
    $('#component_code').prop('disabled', false);
    var used = componentCodesUsed;
    $('#component_code option').each(function () { $(this).prop('disabled', used.indexOf(this.value) !== -1); });
    var firstFree = $('#component_code option:not(:disabled)').first().val();
    $('#component_code').val(firstFree || '');
    onComponentCodeChange();
    $('#component_description').val('');
    $('#modalComponentTitle').html('<i class="fa fa-list-check me-2"></i>Tambah Komponen Tunjangan Baru');
    $('#modalComponent').modal('show');
}

function onComponentCodeChange() {
    var d = componentDefaults[$('#component_code').val()];
    if (!d || $('#component_id').val()) return;
    $('#component_name').val(d.name);
    $('#component_category').val(d.category);
    $('#component_type').val(d.type);
}

function editComponent(id, code, name, category, type, description) {
    $('#component_id').val(id);
    $('#component_code option[data-extra]').remove();
    $('#component_code option').prop('disabled', false);
    if ($('#component_code option[value="' + code + '"]').length === 0) {
        $('#component_code').append($('<option data-extra="1"></option>').val(code).text(code + ' (tidak terhubung)'));
    }
    $('#component_code').val(code).prop('disabled', true);
    $('#component_name').val(name);
    $('#component_category').val(category);
    $('#component_type').val(type);
    $('#component_description').val(description);
    $('#modalComponentTitle').html('<i class="fa fa-list-check me-2"></i>Edit Komponen Tunjangan');
    $('#modalComponent').modal('show');
}

function submitComponent(e) {
    e.preventDefault();
    var id = $('#component_id').val();
    var url = id ? ('hrd-sppd-master-component-update/' + id) : 'hrd-sppd-master-component-store';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            code: $('#component_code').val(),
            name: $('#component_name').val(),
            category: $('#component_category').val(),
            type: $('#component_type').val(),
            description: $('#component_description').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Gagal menyimpan Komponen Tunjangan');
        }
    });
}

function deleteComponent(id, name) {
    if (!confirm('Apakah Anda yakin ingin menghapus Komponen Tunjangan "' + name + '"?')) return;
    $.ajax({
        url: 'hrd-sppd-master-component-delete/' + id,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        }
    });
}

function syncFromJson() {
    if (!confirm('Peringatan: Reset/Sync akan memperbarui skema regulasi awal dari matric.json. Lanjutkan?')) return;
    $.ajax({
        url: 'hrd-sppd-master-sync-json',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        },
        error: function() {
            alert('Terjadi kesalahan saat memproses sync');
        }
    });
}
</script>
@endsection
