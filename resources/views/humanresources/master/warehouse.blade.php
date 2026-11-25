@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 flex-wrap gap-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1">Master Warehouse / Site</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('home-hrd') }}" style="color: rgba(255,255,255,0.85) !important;">Master Data</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Master Warehouse</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ url('hrd-asset-lists') }}" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm">
                <i class="fe fe-box me-1"></i> Asset Lists
            </a>
            <a href="{{ url('hrd-asset-lists-qr-print?mode=location') }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                <i class="fa fa-qrcode me-1"></i> Cetak Semua QR Lokasi
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

    <datalist id="warehouseLocationList">
        @foreach ($refOptions['locations'] ?? [] as $locOpt)
            <option value="{{ $locOpt['value'] }}">{{ $locOpt['label'] }}</option>
        @endforeach
    </datalist>

    <datalist id="warehouseDeptList">
        @foreach ($refOptions['departments'] ?? [] as $deptOpt)
            <option value="{{ $deptOpt }}"></option>
        @endforeach
    </datalist>

    <div class="row g-3">
        <!-- Form Tambah Master Warehouse -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fe fe-plus-circle text-primary me-1"></i> Tambah Warehouse / Site</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('hrd.master-warehouse.store') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Kode Warehouse (Gudang_ID)</label>
                            <input type="text" name="Gudang_ID" maxlength="50" class="form-control form-control-sm text-uppercase"
                                   value="{{ old('Gudang_ID') }}" placeholder="Kosongkan untuk auto: WH-XXX0001">
                            <small class="text-muted">Otomatis dibuat jika dikosongkan, atau isi manual (unik).</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Nama / Deskripsi Warehouse <span class="text-danger">*</span></label>
                            <input type="text" name="Description" maxlength="500" class="form-control form-control-sm"
                                   value="{{ old('Description') }}" required placeholder="Contoh: Gudang / Site Malang">
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Area / Location</label>
                                <input type="text" name="Location" maxlength="30" list="warehouseLocationList"
                                       class="form-control form-control-sm" value="{{ old('Location', Auth::user()->Location ?? 'GEU-HO') }}"
                                       placeholder="GEU-HO / KSPS-HO">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Department</label>
                                <input type="text" name="Department" maxlength="150" list="warehouseDeptList"
                                       class="form-control form-control-sm" value="{{ old('Department') }}"
                                       placeholder="Operational / HCGA">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">PIC</label>
                                <input type="text" name="PIC" maxlength="40" class="form-control form-control-sm"
                                       value="{{ old('PIC') }}" placeholder="Penanggung jawab">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">City / Kota</label>
                                <input type="text" name="City" maxlength="255" class="form-control form-control-sm"
                                       value="{{ old('City') }}" placeholder="Malang / Surabaya">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Street</label>
                            <input type="text" name="Street" maxlength="255" class="form-control form-control-sm"
                                   value="{{ old('Street') }}" placeholder="Nama jalan / kawasan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">Address</label>
                            <textarea name="Address" rows="2" maxlength="255" class="form-control form-control-sm"
                                      placeholder="Alamat lengkap lokasi gudang / site">{{ old('Address') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
                            <i class="fe fe-save me-1"></i> Simpan Master Warehouse
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Master Warehouse -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="fe fe-layers text-success me-1"></i> Daftar Warehouse / Site</h6>
                        <span class="badge bg-primary text-white rounded-pill">{{ $summary['total'] ?? count($warehouses) }} Total</span>
                        <span class="badge bg-success text-white rounded-pill">{{ $summary['active'] ?? 0 }} Aktif</span>
                        @if (($summary['inactive'] ?? 0) > 0)
                            <span class="badge bg-secondary text-white rounded-pill">{{ $summary['inactive'] }} Nonaktif</span>
                        @endif
                    </div>
                    <form action="{{ route('hrd.master-warehouse.index') }}" method="GET" class="d-flex flex-wrap gap-1 align-items-center">
                        <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="ALL" {{ ($statusFilter ?? 'ALL') === 'ALL' ? 'selected' : '' }}>Semua Status</option>
                            <option value="ACTIVE" {{ ($statusFilter ?? 'ALL') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                            <option value="INACTIVE" {{ ($statusFilter ?? 'ALL') === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                        </select>
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control form-control-sm"
                               placeholder="Cari kode / deskripsi / kota / PIC..." style="width: 210px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fe fe-search"></i>
                        </button>
                        @if (!empty($search) || ($statusFilter ?? 'ALL') !== 'ALL')
                            <a href="{{ route('hrd.master-warehouse.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filter">
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
                                    <th>Kode &amp; Deskripsi</th>
                                    <th>Area / Dept &amp; Alamat</th>
                                    <th>PIC</th>
                                    <th class="text-center">Aset</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($warehouses as $idx => $wh)
                                    @php
                                        $isActive = strtoupper((string) ($wh->Status ?? 'ACTIVE')) !== 'INACTIVE';
                                        $fullAddress = trim(implode(', ', array_filter([$wh->Address ?? '', $wh->Street ?? '', $wh->City ?? ''], function ($v) {
                                            $t = trim((string) $v);
                                            return $t !== '' && $t !== '-';
                                        })));
                                    @endphp
                                    <tr class="{{ $isActive ? '' : 'table-light text-muted' }}">
                                        <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap align-items-center gap-1">
                                                <span class="fw-bold {{ $isActive ? 'text-dark' : 'text-muted' }}">{{ $wh->Gudang_ID }}</span>
                                                <span class="badge {{ $isActive ? 'bg-success text-white' : 'bg-secondary text-white' }} rounded-pill">
                                                    {{ $isActive ? 'ACTIVE' : 'INACTIVE' }}
                                                </span>
                                            </div>
                                            <div class="small {{ $isActive ? 'text-dark' : 'text-muted' }} mt-1">{{ $wh->Description ?: '-' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1 mb-1">
                                                @if (!empty($wh->Location))
                                                    <span class="badge bg-info text-white">{{ $wh->Location }}</span>
                                                @endif
                                                @if (!empty($wh->Department))
                                                    <span class="badge bg-light text-dark border">{{ $wh->Department }}</span>
                                                @endif
                                            </div>
                                            <small class="text-muted d-block">{{ $fullAddress !== '' ? $fullAddress : '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="{{ $isActive ? 'text-dark' : 'text-muted' }}">{{ !empty(trim((string) $wh->PIC)) ? $wh->PIC : '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ ($wh->asset_count ?? 0) > 0 ? 'bg-primary text-white' : 'bg-light text-muted border' }} rounded-pill">
                                                {{ (int) ($wh->asset_count ?? 0) }} unit
                                            </span>
                                        </td>
                                        <td class="text-end pe-3 text-nowrap">
                                            <a href="{{ url('hrd-asset-lists-qr-print?mode=location&filter=1&codes[]=' . urlencode($wh->Gudang_ID)) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-success rounded-pill px-2 me-1"
                                               title="Cetak QR Lokasi {{ $wh->Gudang_ID }}">
                                                <i class="fa fa-qrcode"></i> QR
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 me-1"
                                                    onclick="openEditMasterWarehouseModal({{ json_encode($wh) }})">
                                                <i class="fe fe-edit-2"></i> Edit
                                            </button>
                                            <form action="{{ route('hrd.master-warehouse.toggle-status', $wh->ID) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ $isActive ? 'Nonaktifkan' : 'Aktifkan kembali' }} warehouse {{ addslashes($wh->Gudang_ID) }}?');">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $isActive ? 'INACTIVE' : 'ACTIVE' }}">
                                                <button type="submit"
                                                        class="btn btn-sm {{ $isActive ? 'btn-outline-warning text-dark' : 'btn-outline-info' }} rounded-pill px-2"
                                                        title="{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="fe {{ $isActive ? 'fe-slash' : 'fe-check-circle' }}"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Belum ada data master warehouse{{ !empty($search) ? ' yang sesuai pencarian "' . e($search) . '"' : '' }}.
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

    <!-- Modal Edit Master Warehouse -->
    <div class="modal fade" id="editMasterWarehouseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="editMasterWarehouseForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            Edit Master Warehouse
                            <span id="edit_wh_id_badge" class="badge bg-secondary text-white ms-1"></span>
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">Kode Warehouse <span class="text-danger">*</span></label>
                                <input type="text" name="Gudang_ID" id="edit_wh_code" maxlength="50" class="form-control form-control-sm text-uppercase" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold mb-1">Nama / Deskripsi <span class="text-danger">*</span></label>
                                <input type="text" name="Description" id="edit_wh_desc" maxlength="500" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Status</label>
                                <select name="Status" id="edit_wh_status" class="form-select form-select-sm">
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">Area / Location</label>
                                <input type="text" name="Location" id="edit_wh_location" maxlength="30" list="warehouseLocationList" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">Department</label>
                                <input type="text" name="Department" id="edit_wh_dept" maxlength="150" list="warehouseDeptList" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">PIC</label>
                                <input type="text" name="PIC" id="edit_wh_pic" maxlength="40" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">City / Kota</label>
                                <input type="text" name="City" id="edit_wh_city" maxlength="255" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold mb-1">Street</label>
                                <input type="text" name="Street" id="edit_wh_street" maxlength="255" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Address</label>
                            <textarea name="Address" id="edit_wh_address" rows="2" maxlength="255" class="form-control form-control-sm"></textarea>
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
        function openEditMasterWarehouseModal(wh) {
            const form = document.getElementById('editMasterWarehouseForm');
            form.action = "{{ url('hrd-master-warehouse') }}/" + wh.ID + "/update";
            document.getElementById('edit_wh_id_badge').textContent = wh.Gudang_ID || '';
            document.getElementById('edit_wh_code').value = wh.Gudang_ID || '';
            document.getElementById('edit_wh_desc').value = wh.Description || '';
            document.getElementById('edit_wh_status').value = (wh.Status === 'INACTIVE') ? 'INACTIVE' : 'ACTIVE';
            document.getElementById('edit_wh_location').value = wh.Location || '';
            document.getElementById('edit_wh_dept').value = wh.Department || '';
            document.getElementById('edit_wh_pic').value = wh.PIC || '';
            document.getElementById('edit_wh_city').value = wh.City || '';
            document.getElementById('edit_wh_street').value = wh.Street || '';
            document.getElementById('edit_wh_address').value = wh.Address || '';

            const modal = new bootstrap.Modal(document.getElementById('editMasterWarehouseModal'));
            modal.show();
        }
    </script>
@endsection
