@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@include('humanresources.performance.partials.page-header', [
    'title'       => '2. Master Soft Skill & Kompetensi',
    'parentLabel' => 'Performance Management',
    'parentUrl'   => url('hrd-performance-competency-index'),
    'current'     => 'Soft Skill & Skala Nilai',
    'icon'        => 'fa-sliders',
    'badgeText'   => 'Step 2: Versioning & Skala',
])

@include('humanresources.performance.partials.workflow-guide', ['activeStep' => 2])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@php
    $isDraft = $activeVersion && $activeVersion->status === 'DRAFT';
@endphp

<!-- Panel Versioning & Skala Penilaian -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <label class="form-label fw-bold mb-1">Pilih Versi Master Soft Skill / Kompetensi</label>
                <form method="GET" action="{{ url('hrd-performance-competency-index') }}" class="d-flex gap-2">
                    <select name="version_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($versions as $ver)
                            <option value="{{ $ver->id }}" {{ $activeVersion && $activeVersion->id === $ver->id ? 'selected' : '' }}>
                                v{{ $ver->version_no }} — {{ $ver->version_label }} [{{ $ver->status }} | Skala {{ $ver->scale_min }}-{{ $ver->scale_max }}]
                            </option>
                        @endforeach
                    </select>
                </form>
                @if($activeVersion)
                    <div class="mt-1 small text-muted">
                        Status:
                        <span class="badge bg-{{ $activeVersion->status === 'PUBLISHED' ? 'success' : ($activeVersion->status === 'DRAFT' ? 'warning text-dark' : 'secondary') }}">
                            {{ $activeVersion->status }}
                        </span>
                        @if($activeVersion->published_at)
                            | Dipublish: {{ $activeVersion->published_at->format('d M Y H:i') }}
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                @if($activeVersion)
                    <form action="{{ url('hrd-performance-competency-version-scale', $activeVersion->id) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-4">
                            <label class="form-label small fw-bold mb-0">Skala Min</label>
                            <input type="number" name="scale_min" class="form-control form-control-sm" value="{{ $activeVersion->scale_min }}" {{ $isDraft ? '' : 'disabled' }}>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold mb-0">Skala Maks</label>
                            <input type="number" name="scale_max" class="form-control form-control-sm" value="{{ $activeVersion->scale_max }}" {{ $isDraft ? '' : 'disabled' }}>
                        </div>
                        <div class="col-4">
                            @if($isDraft)
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">Simpan Skala</button>
                            @endif
                        </div>
                    </form>
                @endif
            </div>

            <div class="col-md-3 text-md-end">
                @if($isDraft)
                    <form action="{{ url('hrd-performance-competency-version-publish', $activeVersion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Publish versi ini sebagai standar aktif? Versi Published sebelumnya akan diarsipkan.');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="feather-check-circle me-1"></i> Publish Versi Ini
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateDraftVersion">
                        <i class="feather-copy me-1"></i> Buat Draft Versi Baru
                    </button>
                @endif
                <a href="{{ url('hrd-performance-review-index') }}" class="btn btn-sm btn-secondary ms-1">Kembali</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Form Tambah Poin (Hanya aktif di versi DRAFT) -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Poin Soft Skill / Kompetensi</h5>
            </div>
            <div class="card-body">
                @if(!$isDraft)
                    <div class="alert alert-info small">
                        Versi <strong>{{ $activeVersion->version_label ?? '-' }}</strong> berstatus <strong>{{ $activeVersion->status ?? '-' }}</strong> (terkunci untuk menjaga histori penilaian). Klik <strong>Buat Draft Versi Baru</strong> di atas untuk menambah atau mengubah poin.
                    </div>
                @else
                    <form action="{{ url('hrd-performance-competency-store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="version_id" value="{{ $activeVersion->id }}">
                        <div class="mb-2">
                            <label class="form-label font-weight-bold">Grup Kategori (Mis. PDF FR/HCGA/14-00)</label>
                            <input type="text" name="category_group" class="form-control form-control-sm" placeholder="Contoh: 1. Kedisiplinan dan Loyalitas Kerja">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-8">
                                <label class="form-label font-weight-bold">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control form-control-sm text-uppercase" placeholder="SS-DISIPLIN-1" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label font-weight-bold">Urutan</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $competencies->count() + 1 }}" min="0">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label font-weight-bold">Pernyataan / Nama Poin <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Contoh: Memiliki kedisiplinan pada jam datang dan pulang" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-7">
                                <label class="form-label font-weight-bold">Tipe</label>
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="CORE">Core Values</option>
                                    <option value="FUNCTIONAL">Functional / Teknis</option>
                                    <option value="LEADERSHIP">Leadership</option>
                                </select>
                            </div>
                            <div class="col-5">
                                <label class="form-label font-weight-bold">Target Skor</label>
                                <input type="number" name="target_level" class="form-control form-control-sm" min="{{ $activeVersion->scale_min }}" max="{{ $activeVersion->scale_max }}" value="{{ $activeVersion->scale_max }}" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Deskripsi / Keterangan Tambahan</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Indikator Perilaku</label>
                            <textarea name="behavioral_indicators" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="feather-plus me-1"></i> Tambah ke Draft
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Poin -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Poin Penilaian ({{ $competencies->count() }} Poin | Skala {{ $activeVersion ? $activeVersion->scale_min . '-' . $activeVersion->scale_max : '1-5' }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45px;">#</th>
                                <th>Grup / Poin Soft Skill</th>
                                <th style="width: 90px;">Tipe</th>
                                <th class="text-center" style="width: 70px;">Status</th>
                                @if($isDraft)
                                    <th class="text-center" style="width: 140px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($competencies as $idx => $c)
                                <tr class="{{ $c->is_active ? '' : 'table-secondary text-muted' }}">
                                    <td class="text-center">{{ $c->sort_order ?: ($idx + 1) }}</td>
                                    <td>
                                        @if($c->category_group)
                                            <div class="badge bg-light text-dark border mb-1">{{ $c->category_group }}</div>
                                        @endif
                                        <div class="fw-bold">{{ $c->name }} <small class="text-muted">({{ $c->code }})</small></div>
                                        @if($c->description || $c->behavioral_indicators)
                                            <div class="text-muted small">{{ $c->behavioral_indicators ?: $c->description }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $c->type === 'CORE' ? 'primary' : ($c->type === 'LEADERSHIP' ? 'warning text-dark' : 'info text-white') }}">
                                            {{ $c->type }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $c->is_active ? 'success' : 'secondary' }}">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    @if($isDraft)
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick='openEditCompModal(@json($c))'>Edit</button>
                                            <form action="{{ url('hrd-performance-competency-toggle', $c->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-warning">{{ $c->is_active ? 'Off' : 'On' }}</button>
                                            </form>
                                            <form action="{{ url('hrd-performance-competency-delete', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus poin ini dari Draft?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger">Hapus</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isDraft ? 5 : 4 }}" class="text-center text-muted py-4">Belum ada poin Soft Skill pada versi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Draft Versi Baru -->
<div class="modal fade" id="modalCreateDraftVersion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('hrd-performance-competency-version-draft') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Buat Draft Versi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Salin Poin Dari Versi</label>
                        <select name="source_version_id" class="form-select">
                            @foreach($versions as $ver)
                                <option value="{{ $ver->id }}" {{ $publishedVersion && $publishedVersion->id === $ver->id ? 'selected' : '' }}>
                                    v{{ $ver->version_no }} — {{ $ver->version_label }} ({{ $ver->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Label Versi Baru <span class="text-danger">*</span></label>
                        <input type="text" name="version_label" class="form-control" placeholder="Contoh: Versi 2 (Skala 1-10 FR/HCGA/14-00)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Perubahan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buat Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Poin Draft -->
<div class="modal fade" id="modalEditCompetency" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditCompetency" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Poin Soft Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label fw-bold">Grup Kategori</label>
                        <input type="text" name="category_group" id="editCompGroup" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Pernyataan / Nama Poin</label>
                        <input type="text" name="name" id="editCompName" class="form-control form-control-sm" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="form-label fw-bold">Tipe</label>
                            <select name="type" id="editCompType" class="form-select form-select-sm">
                                <option value="CORE">CORE</option>
                                <option value="FUNCTIONAL">FUNCTIONAL</option>
                                <option value="LEADERSHIP">LEADERSHIP</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold">Target Skor</label>
                            <input type="number" name="target_level" id="editCompTarget" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold">Urutan</label>
                            <input type="number" name="sort_order" id="editCompSort" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="editCompDesc" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Indikator Perilaku</label>
                        <textarea name="behavioral_indicators" id="editCompInd" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditCompModal(c) {
        document.getElementById('formEditCompetency').action = "{{ url('hrd-performance-competency-update') }}/" + c.id;
        document.getElementById('editCompGroup').value = c.category_group || '';
        document.getElementById('editCompName').value = c.name || '';
        document.getElementById('editCompType').value = c.type || 'CORE';
        document.getElementById('editCompTarget').value = c.target_level || 5;
        document.getElementById('editCompSort').value = c.sort_order || 0;
        document.getElementById('editCompDesc').value = c.description || '';
        document.getElementById('editCompInd').value = c.behavioral_indicators || '';
        new bootstrap.Modal(document.getElementById('modalEditCompetency')).show();
    }
</script>
@endsection
