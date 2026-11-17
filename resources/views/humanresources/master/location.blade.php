@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4">
        <div>
            <h4 class="content-title text-white fw-bold mb-1">Master Location / Office</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('home-hrd') }}" style="color: rgba(255,255,255,0.85) !important;">Master Data</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Master Location</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if (can_menu('SI01005', 'view') || Auth::user()->hasRole('ADM'))
                <a href="{{ route('attendance.locations.index') }}" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm">
                    <i class="fe fe-map-pin me-1"></i> Lokasi &amp; Radius Absensi
                </a>
            @endif
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
        <!-- Form Tambah Master Location -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fe fe-plus-circle text-primary me-1"></i> Tambah Master Location</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('hrd.master-location.store') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Location Name <span class="text-danger">*</span></label>
                            <input type="text" name="Name" maxlength="30" class="form-control form-control-sm" value="{{ old('Name') }}" required placeholder="Contoh: Head Office Malang">
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Location Type <span class="text-danger">*</span></label>
                                <select name="Location_Type" class="form-select form-select-sm" required>
                                    <option value="Branch Office" {{ old('Location_Type') === 'Branch Office' ? 'selected' : '' }}>Branch Office</option>
                                    <option value="Head Office" {{ old('Location_Type') === 'Head Office' ? 'selected' : '' }}>Head Office</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Company ID</label>
                                <input type="text" name="Company_ID" maxlength="11" class="form-control form-control-sm" value="{{ old('Company_ID', Auth::user()->Company_ID ?? 'GEU') }}" placeholder="GEU">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Location ID</label>
                                <input type="text" name="Location_ID" maxlength="11" class="form-control form-control-sm" value="{{ old('Location_ID') }}" placeholder="Auto (HO-/BR-)">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Location Code</label>
                                <input type="text" name="Location_Code" maxlength="15" class="form-control form-control-sm" value="{{ old('Location_Code') }}" placeholder="Kode internal">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Address</label>
                            <textarea name="Address" rows="2" class="form-control form-control-sm" placeholder="Alamat lengkap">{{ old('Address') }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Street</label>
                            <input type="text" name="Street" class="form-control form-control-sm" value="{{ old('Street') }}" placeholder="Nama jalan / gedung / blok">
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">City</label>
                                <input type="text" name="City" maxlength="20" class="form-control form-control-sm" value="{{ old('City') }}" placeholder="Contoh: Malang">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">ID City</label>
                                <input type="text" name="IDCity" maxlength="11" class="form-control form-control-sm" value="{{ old('IDCity') }}" placeholder="Contoh: MLG / JKT">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <label class="form-label small fw-semibold mb-1">Main City</label>
                                <input type="text" name="MainCity" maxlength="11" class="form-control form-control-sm" value="{{ old('MainCity') }}" placeholder="Surabaya">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold mb-1">Zip Code</label>
                                <input type="text" name="ZipCode" maxlength="15" class="form-control form-control-sm" value="{{ old('ZipCode') }}" placeholder="65111">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold mb-1">Country ID</label>
                                <input type="text" name="IDCountry" maxlength="11" class="form-control form-control-sm" value="{{ old('IDCountry', 'IDN') }}" placeholder="IDN">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">PIC</label>
                                <input type="text" name="PIC" maxlength="30" class="form-control form-control-sm" value="{{ old('PIC') }}" placeholder="Penanggung jawab">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Phone</label>
                                <input type="text" name="Phone" maxlength="40" class="form-control form-control-sm" value="{{ old('Phone') }}" placeholder="0341-xxxxxx">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Phone Ext</label>
                                <input type="text" name="Phone_Ext" maxlength="40" class="form-control form-control-sm" value="{{ old('Phone_Ext') }}" placeholder="Ext. 101">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Fax</label>
                                <input type="text" name="Fax" maxlength="40" class="form-control form-control-sm" value="{{ old('Fax') }}" placeholder="0341-xxxxxx">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Latitude</label>
                                <input type="text" name="Latitude" maxlength="20" class="form-control form-control-sm" value="{{ old('Latitude') }}" placeholder="-7.983908">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Longitude</label>
                                <input type="text" name="Longitude" maxlength="20" class="form-control form-control-sm" value="{{ old('Longitude') }}" placeholder="112.621391">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
                            <i class="fe fe-save me-1"></i> Simpan Master Location
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Master Location -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="fe fe-briefcase text-success me-1"></i> Daftar Master Location / Office</h6>
                        <span class="badge bg-primary text-white rounded-pill">{{ count($locations) }} Lokasi</span>
                    </div>
                    <form action="{{ route('hrd.master-location.index') }}" method="GET" class="d-flex gap-1">
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control form-control-sm" placeholder="Cari nama / ID / kode / kota..." style="width: 220px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fe fe-search"></i>
                        </button>
                        @if (!empty($search))
                            <a href="{{ route('hrd.master-location.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filter">
                                <i class="fe fe-x"></i>
                            </a>
                        @endif
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Lokasi &amp; Kode</th>
                                    <th>Alamat &amp; Wilayah</th>
                                    <th>Kontak &amp; PIC</th>
                                    <th>Koordinat</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($locations as $idx => $loc)
                                    @php
                                        $isHead = stripos((string) $loc->Location_Type, 'head') !== false;
                                        $fullAddress = trim(implode(', ', array_filter([$loc->Address ?? '', $loc->Street ?? ''])));
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $loc->Name }}</div>
                                            <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                                <span class="badge {{ $isHead ? 'bg-primary text-white' : 'bg-info text-white' }} rounded-pill">{{ $loc->Location_Type ?: '-' }}</span>
                                                <small class="text-muted fw-semibold">{{ $loc->Location_ID }}</small>
                                                @if (!empty($loc->Location_Code))
                                                    <small class="text-muted">({{ $loc->Location_Code }})</small>
                                                @endif
                                                @if (!empty($loc->Company_ID))
                                                    <span class="badge bg-light text-dark border">{{ $loc->Company_ID }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $fullAddress !== '' ? $fullAddress : '-' }}</div>
                                            <small class="text-muted">
                                                {{ $loc->City ?: '-' }}
                                                @if (!empty($loc->IDCity)) ({{ $loc->IDCity }}) @endif
                                                @if (!empty($loc->ZipCode)) &bull; {{ $loc->ZipCode }} @endif
                                                @if (!empty($loc->IDCountry)) &bull; {{ $loc->IDCountry }} @endif
                                                @if (!empty($loc->MainCity)) &bull; Main: {{ $loc->MainCity }} @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $loc->PIC ?: '-' }}</div>
                                            @if (!empty($loc->Phone) || !empty($loc->Phone_Ext))
                                                <small class="text-muted d-block">
                                                    <i class="fe fe-phone me-1"></i>{{ $loc->Phone ?: '-' }}@if (!empty(trim((string) $loc->Phone_Ext))) (Ext. {{ trim((string) $loc->Phone_Ext) }})@endif
                                                </small>
                                            @endif
                                            @if (!empty(trim((string) $loc->Fax)))
                                                <small class="text-muted d-block">Fax: {{ trim((string) $loc->Fax) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($loc->Latitude) && !empty($loc->Longitude))
                                                <a href="https://maps.google.com/?q={{ $loc->Latitude }},{{ $loc->Longitude }}" target="_blank" class="text-decoration-none">
                                                    <i class="fe fe-external-link me-1"></i>{{ $loc->Latitude }}, {{ $loc->Longitude }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 me-1"
                                                onclick="openEditMasterLocationModal({{ json_encode($loc) }})">
                                                <i class="fe fe-edit-2"></i> Edit
                                            </button>
                                            <form action="{{ route('hrd.master-location.destroy', $loc->ID) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Hapus master lokasi {{ addslashes($loc->Name) }} ({{ addslashes($loc->Location_ID) }})?');">
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
                                            Belum ada data master lokasi{{ !empty($search) ? ' yang sesuai pencarian "' . e($search) . '"' : '' }}.
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

    <!-- Modal Edit Master Location -->
    <div class="modal fade" id="editMasterLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="editMasterLocationForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Edit Master Location <span id="edit_loc_id_badge" class="badge bg-secondary text-white ms-1"></span></h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Location Name <span class="text-danger">*</span></label>
                                <input type="text" name="Name" id="edit_ml_name" maxlength="30" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Location Type <span class="text-danger">*</span></label>
                                <select name="Location_Type" id="edit_ml_type" class="form-select form-select-sm" required>
                                    <option value="Branch Office">Branch Office</option>
                                    <option value="Head Office">Head Office</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Company ID</label>
                                <input type="text" name="Company_ID" id="edit_ml_company" maxlength="11" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Location ID</label>
                                <input type="text" name="Location_ID" id="edit_ml_loc_id" maxlength="11" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Location Code</label>
                                <input type="text" name="Location_Code" id="edit_ml_loc_code" maxlength="15" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Address</label>
                                <textarea name="Address" id="edit_ml_address" rows="2" class="form-control form-control-sm"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Street</label>
                                <textarea name="Street" id="edit_ml_street" rows="2" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">City</label>
                                <input type="text" name="City" id="edit_ml_city" maxlength="20" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">ID City</label>
                                <input type="text" name="IDCity" id="edit_ml_id_city" maxlength="11" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Main City</label>
                                <input type="text" name="MainCity" id="edit_ml_main_city" maxlength="11" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Zip Code</label>
                                <input type="text" name="ZipCode" id="edit_ml_zip" maxlength="15" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Country ID</label>
                                <input type="text" name="IDCountry" id="edit_ml_country" maxlength="11" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">PIC</label>
                                <input type="text" name="PIC" id="edit_ml_pic" maxlength="30" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Phone</label>
                                <input type="text" name="Phone" id="edit_ml_phone" maxlength="40" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Phone Ext</label>
                                <input type="text" name="Phone_Ext" id="edit_ml_phone_ext" maxlength="40" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Fax</label>
                                <input type="text" name="Fax" id="edit_ml_fax" maxlength="40" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Latitude</label>
                                <input type="text" name="Latitude" id="edit_ml_lat" maxlength="20" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Longitude</label>
                                <input type="text" name="Longitude" id="edit_ml_lng" maxlength="20" class="form-control form-control-sm">
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
        function openEditMasterLocationModal(loc) {
            const form = document.getElementById('editMasterLocationForm');
            form.action = "{{ url('hrd-master-location') }}/" + loc.ID + "/update";
            document.getElementById('edit_loc_id_badge').textContent = loc.Location_ID || '';
            document.getElementById('edit_ml_name').value = loc.Name || '';
            document.getElementById('edit_ml_type').value = loc.Location_Type || 'Branch Office';
            document.getElementById('edit_ml_company').value = loc.Company_ID || '';
            document.getElementById('edit_ml_loc_id').value = loc.Location_ID || '';
            document.getElementById('edit_ml_loc_code').value = loc.Location_Code || '';
            document.getElementById('edit_ml_address').value = loc.Address || '';
            document.getElementById('edit_ml_street').value = loc.Street || '';
            document.getElementById('edit_ml_city').value = loc.City || '';
            document.getElementById('edit_ml_id_city').value = loc.IDCity || '';
            document.getElementById('edit_ml_main_city').value = loc.MainCity || '';
            document.getElementById('edit_ml_zip').value = loc.ZipCode || '';
            document.getElementById('edit_ml_country').value = loc.IDCountry || 'IDN';
            document.getElementById('edit_ml_pic').value = loc.PIC || '';
            document.getElementById('edit_ml_phone').value = loc.Phone || '';
            document.getElementById('edit_ml_phone_ext').value = loc.Phone_Ext || '';
            document.getElementById('edit_ml_fax').value = loc.Fax || '';
            document.getElementById('edit_ml_lat').value = loc.Latitude || '';
            document.getElementById('edit_ml_lng').value = loc.Longitude || '';

            const modal = new bootstrap.Modal(document.getElementById('editMasterLocationModal'));
            modal.show();
        }
    </script>
@endsection
