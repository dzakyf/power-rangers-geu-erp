@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4">
        <div>
            <h4 class="content-title text-white fw-bold mb-1">Setting Lokasi &amp; Batas Maksimal Jarak Absensi</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('attendance.data') }}" style="color: rgba(255,255,255,0.85) !important;">Monitoring Presensi</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Lokasi &amp; Radius Geofence</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('attendance.data') }}" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm">
                <i class="fe fe-arrow-left me-1"></i> Kembali ke Rekap Presensi
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fe fe-alert-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Form Tambah Lokasi -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fe fe-plus-circle text-primary me-1"></i> Tambah Lokasi Kantor / Site</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.locations.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Isi Cepat dari Master Location (Opsional)</label>
                            <select class="form-select form-select-sm" id="quickFillMasterLocation">
                                <option value="">-- Pilih dari Master Location --</option>
                                @foreach ($masterLocations as $ml)
                                    <option value="{{ $ml->Location_ID }}"
                                        data-name="{{ $ml->Name }}"
                                        data-lat="{{ $ml->Latitude ?? '' }}"
                                        data-lng="{{ $ml->Longitude ?? '' }}"
                                        data-city="{{ $ml->City ?? '' }}">
                                        {{ $ml->Name }} ({{ $ml->Location_ID }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lokasi / Kantor <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="add_loc_name" class="form-control form-control-sm" required placeholder="Contoh: Head Office Malang">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.00000001" name="latitude" id="add_loc_lat" class="form-control form-control-sm" required placeholder="-7.983908">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.00000001" name="longitude" id="add_loc_lng" class="form-control form-control-sm" required placeholder="112.621391">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Batas Jarak Maks. (Meter) <span class="text-danger">*</span></label>
                                <input type="number" min="10" max="50000" name="radius_meters" class="form-control form-control-sm" value="100" required>
                                <div class="form-text fs-11">Min 10m. Default 100m.</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="ACTIVE" selected>ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
                            <i class="fe fe-save me-1"></i> Simpan Lokasi Absensi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Lokasi -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="fe fe-map-pin text-success me-1"></i> Daftar Titik Koordinat &amp; Radius Absensi</h6>
                    <span class="badge bg-primary text-white rounded-pill">{{ count($locations) }} Lokasi</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Nama Lokasi</th>
                                    <th>Koordinat (Lat, Lng)</th>
                                    <th class="text-center">Batas Jarak</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($locations as $idx => $loc)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $loc->name }}</div>
                                            @if (!empty($loc->region_group) || !empty($loc->zipcode))
                                                <small class="text-muted">{{ $loc->region_group ?? '' }} {{ $loc->zipcode ?? '' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="https://maps.google.com/?q={{ $loc->latitude }},{{ $loc->longitude }}" target="_blank" class="text-decoration-none">
                                                <i class="fe fe-external-link me-1"></i>{{ $loc->latitude }}, {{ $loc->longitude }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-white rounded-pill px-3">{{ (int) $loc->radius_meters }} meter</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $loc->status === 'ACTIVE' ? 'bg-success text-white' : 'bg-secondary text-white' }} rounded-pill">
                                                {{ $loc->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 me-1"
                                                onclick="openEditLocationModal({{ json_encode($loc) }})">
                                                <i class="fe fe-edit-2"></i> Edit
                                            </button>
                                            <form action="{{ route('attendance.locations.destroy', $loc->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Hapus lokasi absensi {{ addslashes($loc->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Belum ada titik lokasi absensi. Tambahkan lokasi untuk mengaktifkan validasi jarak geofence.
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

    <!-- Modal Edit Lokasi -->
    <div class="modal fade" id="editLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="editLocationForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Edit Lokasi &amp; Batas Maksimal Jarak</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lokasi <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_loc_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.00000001" name="latitude" id="edit_loc_lat" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.00000001" name="longitude" id="edit_loc_lng" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Batas Jarak Maks. (Meter) <span class="text-danger">*</span></label>
                                <input type="number" min="10" max="50000" name="radius_meters" id="edit_loc_radius" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_loc_status" class="form-select form-select-sm" required>
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('quickFillMasterLocation')?.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) return;
            document.getElementById('add_loc_name').value = opt.dataset.name || '';
            if (opt.dataset.lat) document.getElementById('add_loc_lat').value = opt.dataset.lat;
            if (opt.dataset.lng) document.getElementById('add_loc_lng').value = opt.dataset.lng;
        });

        function openEditLocationModal(loc) {
            const form = document.getElementById('editLocationForm');
            form.action = "{{ url('hrd-attendance-locations') }}/" + loc.id + "/update";
            document.getElementById('edit_loc_name').value = loc.name || '';
            document.getElementById('edit_loc_lat').value = loc.latitude || '';
            document.getElementById('edit_loc_lng').value = loc.longitude || '';
            document.getElementById('edit_loc_radius').value = loc.radius_meters || 100;
            document.getElementById('edit_loc_status').value = loc.status || 'ACTIVE';
            const modal = new bootstrap.Modal(document.getElementById('editLocationModal'));
            modal.show();
        }
    </script>
@endsection
