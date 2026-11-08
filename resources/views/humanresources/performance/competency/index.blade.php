@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-review-index') }}">Performance</a></li>
        <li class="breadcrumb-item active"><span class="active">Competency Matrix</span></li>
    </ol>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- Form Tambah Kompetensi -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Kompetensi Baku</h5>
            </div>
            <div class="card-body">
                <form action="{{ url('hrd-performance-competency-store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Kode Kompetensi <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="Contoh: COMP-COMMUNICATION" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Nama Kompetensi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Effective Communication" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Kategori / Tipe</label>
                        <select name="type" class="form-select form-control" required>
                            <option value="CORE">Core Values (Nilai Inti Budaya)</option>
                            <option value="FUNCTIONAL">Functional / Teknis</option>
                            <option value="LEADERSHIP">Leadership & Manajerial</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Target Level Standar (1 - 5)</label>
                        <input type="number" name="target_level" class="form-control" min="1" max="5" value="3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Definisi kompetensi..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Indikator Perilaku Utama (Key Behaviors)</label>
                        <textarea name="behavioral_indicators" class="form-control" rows="3" placeholder="Contoh perilaku nyata yang dinilai..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="feather-plus me-1"></i> Simpan Kompetensi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Kamus Kompetensi -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Kamus & Matriks Kompetensi Baku Perusahaan</h5>
                <a href="{{ url('hrd-performance-review-index') }}" class="btn btn-sm btn-secondary">
                    <i class="feather-arrow-left me-1"></i> Ke Performance Review
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Kompetensi</th>
                                <th>Kategori</th>
                                <th class="text-center" style="width: 80px;">Target Level</th>
                                <th>Indikator Perilaku</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($competencies as $c)
                                <tr>
                                    <td><strong>{{ $c->code }}</strong></td>
                                    <td>
                                        <div class="fw-bold">{{ $c->name }}</div>
                                        <div class="text-muted small">{{ $c->description }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $c->type === 'CORE' ? 'primary' : ($c->type === 'LEADERSHIP' ? 'warning text-dark' : 'info text-white') }}">
                                            {{ $c->type }}
                                        </span>
                                    </td>
                                    <td class="text-center font-weight-bold fs-6">
                                        <span class="badge bg-secondary">Level {{ $c->target_level }}/5</span>
                                    </td>
                                    <td class="small">{{ $c->behavioral_indicators ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada kompetensi terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

