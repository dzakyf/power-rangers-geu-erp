@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Jadwal Perawatan Aset (Maintenance Schedule)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="hrd-asset-fixing" style="color: rgba(255, 255, 255, 0.8) !important;">Asset Fixing</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Maintenance Schedule</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-calendar-check me-1"></i> Preventive Schedule
        </span>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3 mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white"><i class="fa fa-calendar-check me-2"></i>Create Maintenance Schedule</h6>
        <a href="hrd-asset-fixing" class="btn btn-light btn-sm"><i class="fa fa-arrow-left me-1"></i> Kembali ke Asset Fixing</a>
    </div>
    <div class="card-body">
        <form id="FormMaintenanceSchedule" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Pilih Aset <span class="text-danger">*</span></label>
                <select name="Asset_No" class="form-select form-select-sm" required>
                    <option value="">-- Pilih Aset --</option>
                    @foreach($assets as $a)
                        <option value="{{ $a->Asset_No }}">{{ $a->Asset_No }} - {{ $a->Product_Description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jenis Perawatan <span class="text-danger">*</span></label>
                <select name="Jenis_Perawatan" class="form-select form-select-sm" required>
                    <option value="PREVENTIVE_MAINTENANCE">1. Preventive Maintenance</option>
                    <option value="INSPECTION">2. Inspection</option>
                    <option value="CLEANING">3. Cleaning</option>
                    <option value="SPARE_PART_REPLACEMENT">4. Spare Part Replacement</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Frekuensi <span class="text-danger">*</span></label>
                <select name="Frekuensi" class="form-select form-select-sm" required>
                    <option value="BULANAN">1. Bulanan</option>
                    <option value="3_BULANAN">2. 3 Bulanan</option>
                    <option value="6_BULANAN">3. 6 Bulanan</option>
                    <option value="TAHUNAN">4. Tahunan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jadwal Berikutnya <span class="text-danger">*</span></label>
                <input type="date" name="Next_Schedule_Date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required />
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">PIC Internal (GA / Teknisi)</label>
                <input type="text" name="PIC_Internal" class="form-control form-control-sm" value="{{ auth()->user()->username ?? '' }}" />
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Vendor External?</label>
                <select id="Is_External_Vendor" name="Is_External_Vendor" class="form-select form-select-sm" onchange="$('#SupplierWrap').toggle(this.value === '1');">
                    <option value="0" selected>Tidak (Internal)</option>
                    <option value="1">Ya (External Vendor)</option>
                </select>
            </div>
            <div class="col-md-3" id="SupplierWrap" style="display:none;">
                <label class="form-label small fw-semibold">Pilih Vendor</label>
                <select name="Supplier_ID" class="form-select form-select-sm">
                    <option value="">-- Pilih Vendor --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->Supplier_ID }}">{{ $s->Company_Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Estimasi Biaya (Rp)</label>
                <input type="number" name="Estimasi_Biaya" class="form-control form-control-sm" value="0" min="0" />
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="button" class="btn btn-primary btn-sm" onclick="submitSchedule();">
                    <i class="fa fa-save me-1"></i> Simpan Schedule (Ajukan ke HCGA Mgr)
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light fw-bold">Daftar Jadwal Perawatan Aset</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No Schedule</th>
                        <th>Aset</th>
                        <th>Jenis</th>
                        <th>Frekuensi</th>
                        <th>Jadwal</th>
                        <th>PIC / Vendor</th>
                        <th>Voucher Finance</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $sch)
                    <tr>
                        <td class="fw-bold">{{ $sch->Schedule_No }}</td>
                        <td>{{ $sch->Asset_No }}<br><small class="text-muted">{{ $sch->Asset_Name }}</small></td>
                        <td>{{ $sch->Jenis_Perawatan }}</td>
                        <td>{{ $sch->Frekuensi }}</td>
                        <td class="text-nowrap">{{ $sch->Next_Schedule_Date }}</td>
                        <td>{{ $sch->PIC_Internal }} @if($sch->Is_External_Vendor) <br><small class="text-info">{{ $sch->Vendor_Name }}</small> @endif</td>
                        <td>{{ $sch->Voucher_No ?: '-' }}</td>
                        <td><span class="badge bg-info text-dark">{{ $sch->Status }}</span></td>
                        <td class="text-nowrap">
                            @if($sch->Status === 'APPROVAL_HCGA')
                                <button class="btn btn-sm btn-success" onclick="actSchedule('hrd-asset-fixing-schedule-approval', {{ $sch->ID }}, 'APPROVE')">Approve</button>
                                <button class="btn btn-sm btn-danger" onclick="actSchedule('hrd-asset-fixing-schedule-approval', {{ $sch->ID }}, 'REJECT')">Reject</button>
                            @elseif(in_array($sch->Status, ['RETURNED', 'REJECTED'], true))
                                <button class="btn btn-sm btn-outline-primary" onclick="actSchedule('hrd-asset-fixing-schedule-resubmit', {{ $sch->ID }})">Ajukan Ulang</button>
                            @elseif($sch->Status === 'FINANCE_SUBMITTED')
                                <button class="btn btn-sm btn-primary" onclick="actSchedule('hrd-asset-fixing-schedule-confirm', {{ $sch->ID }})">Confirm Schedule</button>
                            @elseif($sch->Status === 'SCHEDULED')
                                <button class="btn btn-sm btn-warning text-dark" onclick="actSchedule('hrd-asset-fixing-schedule-start', {{ $sch->ID }})">Mulai Eksekusi</button>
                            @elseif($sch->Status === 'IN_EXECUTION')
                                <button class="btn btn-sm btn-success" onclick="actSchedule('hrd-asset-fixing-schedule-complete', {{ $sch->ID }})">Selesai &amp; Aktifkan Aset</button>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-3">Belum ada jadwal perawatan aset.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function submitSchedule() {
    $.ajax({
        url: 'hrd-asset-fixing-schedule-store',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: $('#FormMaintenanceSchedule').serialize(),
        success: function(res) { alert(res.message); location.reload(); },
        error: function(xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan.'); }
    });
}
function actSchedule(url, id, action) {
    $.ajax({
        url: url,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: { id: id, action: action || '' },
        success: function(res) { alert(res.message); location.reload(); },
        error: function(xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memproses.'); }
    });
}
</script>
@endsection
