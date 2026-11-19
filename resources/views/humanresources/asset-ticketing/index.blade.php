@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2 flex-wrap gap-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Asset Ticketing &amp; Lifecycle Hub
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; General Affairs</a></li>
                <li class="breadcrumb-item"><a href="hrd-asset-lists" style="color: rgba(255, 255, 255, 0.8) !important;">Asset Lists</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Asset Ticketing Hub</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="hrd-asset-lists" class="btn btn-light btn-sm">
            <i class="fa fa-boxes me-1"></i> Master Asset Lists
        </a>
        <button type="button" class="btn btn-danger btn-sm" onclick="openAssetTicketModal('DAMAGE')">
            <i class="fa fa-exclamation-triangle me-1"></i> Lapor Kerusakan
        </button>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAssetTicketModal('PROCUREMENT')">
            <i class="fa fa-cart-plus me-1"></i> Pengadaan Aset
        </button>
        <button type="button" class="btn btn-info btn-sm text-white" onclick="openAssetTicketModal('MOVING')">
            <i class="fa fa-exchange-alt me-1"></i> Pemindahan
        </button>
        <button type="button" class="btn btn-dark btn-sm" onclick="openAssetTicketModal('DISPOSAL')">
            <i class="fa fa-trash-alt me-1"></i> Pemusnahan
        </button>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mt-1 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Tiket &amp; Perbaikan Kerusakan</div>
                <div class="fs-3 fw-bold text-danger" id="kpiDamage">0</div>
                <div class="small text-muted">Menunggu / dalam penanganan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Pengadaan Aset (PR)</div>
                <div class="fs-3 fw-bold text-primary" id="kpiProcurement">0</div>
                <div class="small text-muted">Baru &amp; penggantian aset</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Pemindahan Aset</div>
                <div class="fs-3 fw-bold text-info" id="kpiMoving">0</div>
                <div class="small text-muted">Menunggu persetujuan mutasi</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-dark border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Pemusnahan (Disposal)</div>
                <div class="fs-3 fw-bold text-dark" id="kpiDisposal">0</div>
                <div class="small text-muted">Menunggu HCGA / BOD / Finance</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light pt-3 pb-0 border-bottom">
        <ul class="nav nav-tabs card-header-tabs" id="AssetTicketTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" data-type="ALL" onclick="switchAssetTab('ALL', this)">Semua Aktivitas</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-type="DAMAGE" onclick="switchAssetTab('DAMAGE', this)">
                    <i class="fa fa-tools text-danger me-1"></i> Kerusakan &amp; Perbaikan
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-type="PROCUREMENT" onclick="switchAssetTab('PROCUREMENT', this)">
                    <i class="fa fa-shopping-cart text-primary me-1"></i> Pengadaan Aset
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-type="MOVING" onclick="switchAssetTab('MOVING', this)">
                    <i class="fa fa-truck-loading text-info me-1"></i> Pemindahan
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-type="DISPOSAL" onclick="switchAssetTab('DISPOSAL', this)">
                    <i class="fa fa-ban text-dark me-1"></i> Pemusnahan
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" id="AssetTicketSearch" class="form-control" placeholder="Cari No Dokumen, Asset No, Nama Aset, Pemohon, Status..." oninput="loadAssetTicketingData();" />
                </div>
            </div>
            <div class="col-md-4">
                <input type="text" id="AssetTicketFilterAssetNo" class="form-control form-control-sm" value="{{ $prefillAssetNo }}" placeholder="Filter Asset No tertentu (opsional)" oninput="loadAssetTicketingData();" />
            </div>
            <div class="col-md-3 text-md-end">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadAssetTicketingData();">
                    <i class="fa fa-sync me-1"></i> Muat Ulang
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kategori</th>
                        <th>No Dokumen</th>
                        <th>Asset No</th>
                        <th>Nama Aset</th>
                        <th>Keterangan</th>
                        <th>Pemohon</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="AssetTicketBody">
                    <tr><td colspan="9" class="text-center text-muted py-3">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="AssetTicketingDetail" class="mt-3"></div>

<!-- Modal Pengajuan Terpadu 4-in-1 -->
<div class="modal fade" id="AssetTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="AssetTicketModalTitle">Pengajuan Aset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="AssetTicketForm" onsubmit="submitAssetTicketForm(event);">
                <div class="modal-body">
                    <input type="hidden" id="FormActionType" name="action_type" value="DAMAGE" />
                    <div class="row g-3">
                        <div class="col-md-12" id="WrapAssetSelect">
                            <label class="form-label fw-semibold">Pilih Barang Aset (<span class="text-danger">*</span>)</label>
                            <select id="FormAssetNo" name="Asset_No" class="form-select" onchange="onModalAssetChange();">
                                <option value="">-- Pilih Asset No --</option>
                                @foreach($assets as $a)
                                    <option value="{{ $a->Asset_No }}"
                                        data-name="{{ $a->Product_Description }}"
                                        data-loc="{{ $a->Asset_Location }}"
                                        data-cond="{{ $a->Kondisi_Aset ?? 'BAIK' }}"
                                        data-val="{{ $a->Buy_Val ?? 0 }}"
                                        data-user="{{ $a->User_Responsible ?? '-' }}">
                                        {{ $a->Asset_No }} — {{ $a->Product_Description }} ({{ $a->Asset_Location ?: '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1" id="ModalAssetMeta"></div>
                        </div>

                        <!-- Fields DAMAGE -->
                        <div class="col-md-6 field-group field-DAMAGE">
                            <label class="form-label fw-semibold">Tingkat Kerusakan</label>
                            <select name="Damage_Severity" class="form-select">
                                <option value="RUSAK RINGAN">RUSAK RINGAN (Masih bisa dipakai / perbaikan minor)</option>
                                <option value="RUSAK BERAT">RUSAK BERAT (Tidak bisa dipakai / butuh perbaikan besar)</option>
                            </select>
                        </div>
                        <div class="col-md-6 field-group field-DAMAGE">
                            <label class="form-label fw-semibold">Prioritas</label>
                            <select name="Priority" class="form-select">
                                <option value="NORMAL">NORMAL</option>
                                <option value="HIGH">HIGH / URGENT</option>
                            </select>
                        </div>
                        <div class="col-md-12 field-group field-DAMAGE">
                            <label class="form-label fw-semibold">Judul Keluhan / Kerusakan</label>
                            <input type="text" name="Ticket_Subject" class="form-control" placeholder="Contoh: Layar laptop berkedip / AC tidak dingin" />
                        </div>
                        <div class="col-md-12 field-group field-DAMAGE">
                            <label class="form-label fw-semibold">Deskripsi Kerusakan</label>
                            <textarea name="Description" class="form-control" rows="3" placeholder="Jelaskan kronologi & kondisi kerusakan aset..."></textarea>
                        </div>

                        <!-- Fields PROCUREMENT -->
                        <div class="col-md-6 field-group field-PROCUREMENT d-none">
                            <label class="form-label fw-semibold">Jenis Pengadaan</label>
                            <select id="FormProcReqType" name="Asset_Request_Type" class="form-select">
                                <option value="NEW">Pengadaan Aset Baru (NEW)</option>
                                <option value="REPLACEMENT">Penggantian Aset Rusak/Disposal (REPLACEMENT)</option>
                            </select>
                        </div>
                        <div class="col-md-6 field-group field-PROCUREMENT d-none">
                            <label class="form-label fw-semibold">Nama Barang / Spesifikasi Singkat</label>
                            <input type="text" name="Item_Name" class="form-control" placeholder="Contoh: Laptop Bisnis i5 16GB RAM" />
                        </div>
                        <div class="col-md-4 field-group field-PROCUREMENT d-none">
                            <label class="form-label fw-semibold">Qty</label>
                            <input type="number" name="Qty" class="form-control" value="1" min="1" />
                        </div>
                        <div class="col-md-4 field-group field-PROCUREMENT d-none">
                            <label class="form-label fw-semibold">Estimasi Harga Satuan (Rp)</label>
                            <input type="number" name="Est_Price" class="form-control" value="0" min="0" />
                        </div>
                        <div class="col-md-4 field-group field-PROCUREMENT d-none">
                            <label class="form-label fw-semibold">Satuan</label>
                            <input type="text" name="Unit" class="form-control" value="UNIT" />
                        </div>
                        <div class="col-md-12 field-group field-PROCUREMENT d-none">
                            <label class="form-label fw-semibold">Catatan / Alasan Pengadaan</label>
                            <textarea name="Remark" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Fields MOVING -->
                        <div class="col-md-6 field-group field-MOVING d-none">
                            <label class="form-label fw-semibold">Lokasi / Cabang Tujuan</label>
                            <input type="text" name="Moving_Destination" class="form-control" placeholder="Contoh: GEU-HO / Gudang Surabaya" />
                        </div>
                        <div class="col-md-6 field-group field-MOVING d-none">
                            <label class="form-label fw-semibold">Tanggal Pemindahan</label>
                            <input type="date" name="Moving_Date" class="form-control" value="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-md-6 field-group field-MOVING d-none">
                            <label class="form-label fw-semibold">Nama Penerima (PIC Baru)</label>
                            <input type="text" name="Receipent_Name" class="form-control" />
                        </div>
                        <div class="col-md-6 field-group field-MOVING d-none">
                            <label class="form-label fw-semibold">Departemen Penerima</label>
                            <input type="text" name="Receipent_Department" class="form-control" />
                        </div>
                        <div class="col-md-12 field-group field-MOVING d-none">
                            <label class="form-label fw-semibold">Alasan Pemindahan</label>
                            <textarea name="Moving_Reason" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Fields DISPOSAL -->
                        <div class="col-md-6 field-group field-DISPOSAL d-none">
                            <label class="form-label fw-semibold">Nilai Buku / Estimasi Sisa (Rp)</label>
                            <input type="number" id="FormBookValue" name="Book_Value" class="form-control" value="0" min="0" />
                        </div>
                        <div class="col-md-6 field-group field-DISPOSAL d-none">
                            <label class="form-label fw-semibold">Rencana Tanggal Pemusnahan</label>
                            <input type="date" name="Disposal_Actual_Date" class="form-control" value="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-md-12 field-group field-DISPOSAL d-none">
                            <label class="form-label fw-semibold">Alasan Pemusnahan</label>
                            <textarea name="Disposal_Reason" class="form-control" rows="3" placeholder="Contoh: Rusak total tidak ekonomis untuk diperbaiki"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="BtnSubmitAssetTicket">
                        <i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var currentAssetTab = 'ALL';
var assetTicketModalInstance = null;

function switchAssetTab(type, btn) {
    currentAssetTab = type;
    $('#AssetTicketTabs .nav-link').removeClass('active');
    $(btn).addClass('active');
    loadAssetTicketingData();
}

function loadAssetTicketingData() {
    $.get('hrd-asset-ticketing-data', {
        type: currentAssetTab,
        search: $('#AssetTicketSearch').val(),
        asset_no: $('#AssetTicketFilterAssetNo').val()
    }, function(res) {
        if (!res || res.status !== 'success') return;
        var s = res.summary || {};
        $('#kpiDamage').text(s.damage_open || 0);
        $('#kpiProcurement').text(s.procurement_open || 0);
        $('#kpiMoving').text(s.moving_open || 0);
        $('#kpiDisposal').text(s.disposal_open || 0);

        var rows = res.items || [];
        if (!rows.length) {
            $('#AssetTicketBody').html('<tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data pengajuan aset yang cocok.</td></tr>');
            return;
        }

        var catBadge = {
            'DAMAGE_TICKET': '<span class="badge bg-danger text-white">Tiket Kerusakan</span>',
            'ASSET_FIXING': '<span class="badge bg-warning text-dark">Perbaikan Aset</span>',
            'PROCUREMENT': '<span class="badge bg-primary text-white">Pengadaan PR</span>',
            'ASSET_MOVING': '<span class="badge bg-info text-white">Pemindahan</span>',
            'ASSET_DISPOSAL': '<span class="badge bg-dark text-white">Pemusnahan</span>'
        };

        var html = '';
        rows.forEach(function(r) {
            var stClass = 'bg-secondary text-white';
            if (['APPROVED', 'RESOLVED', 'CLOSED', 'COMPLETED'].indexOf(r.status) >= 0) stClass = 'bg-success text-white';
            else if (['REJECTED', 'CANCELLED'].indexOf(r.status) >= 0) stClass = 'bg-danger text-white';
            else if (['OPEN', 'WAITING_APPROVAL', 'APPROVAL_UPPER', 'APPROVAL_GA', 'APPROVAL_BOD', 'IN_PROGRESS'].indexOf(r.status) >= 0) stClass = 'bg-warning text-dark';

            html += '<tr ondblclick="viewAssetTicketDetail(\'' + r.category + '\',' + r.ref_id + ')" style="cursor:pointer;">' +
                '<td>' + (catBadge[r.category] || r.category) + '</td>' +
                '<td class="fw-bold text-primary">' + escapeHtml(r.doc_no) + '</td>' +
                '<td>' + escapeHtml(r.asset_no) + '</td>' +
                '<td>' + escapeHtml(r.asset_name) + '</td>' +
                '<td><div>' + escapeHtml(r.title) + '</div><small class="text-muted">' + escapeHtml(r.sub_info) + '</small></td>' +
                '<td>' + escapeHtml(r.requester) + '</td>' +
                '<td><span class="badge ' + stClass + '">' + escapeHtml(r.status) + '</span></td>' +
                '<td>' + escapeHtml(r.doc_date || '-') + '</td>' +
                '<td class="text-end"><button type="button" class="btn btn-xs btn-outline-primary" onclick="viewAssetTicketDetail(\'' + r.category + '\',' + r.ref_id + ')"><i class="fa fa-eye"></i> Detail</button></td>' +
                '</tr>';
        });
        $('#AssetTicketBody').html(html);
    });
}

function viewAssetTicketDetail(category, id) {
    LoadM('AssetTicketingDetail', 'hrd-asset-ticketing-detail?category=' + encodeURIComponent(category) + '&id=' + encodeURIComponent(id));
    var el = document.getElementById('AssetTicketingDetail');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function openAssetTicketModal(type, prefillAssetNo) {
    $('#FormActionType').val(type);
    $('.field-group').addClass('d-none');
    $('.field-' + type).removeClass('d-none');

    var titles = {
        'DAMAGE': 'Lapor Kerusakan Barang Aset',
        'PROCUREMENT': 'Ajukan Pengadaan Aset (Baru / Pengganti)',
        'MOVING': 'Ajukan Pemindahan Aset',
        'DISPOSAL': 'Ajukan Pemusnahan Aset (Disposal)'
    };
    $('#AssetTicketModalTitle').text(titles[type] || 'Pengajuan Aset');

    if (prefillAssetNo) {
        $('#FormAssetNo').val(prefillAssetNo);
        if (type === 'PROCUREMENT') {
            $('#FormProcReqType').val('REPLACEMENT');
        }
    }
    onModalAssetChange();

    var modalEl = document.getElementById('AssetTicketModal');
    if (!assetTicketModalInstance && window.bootstrap) {
        assetTicketModalInstance = new bootstrap.Modal(modalEl);
    }
    if (assetTicketModalInstance) {
        assetTicketModalInstance.show();
    } else {
        $('#AssetTicketModal').modal('show');
    }
}

function onModalAssetChange() {
    var opt = $('#FormAssetNo option:selected');
    var val = opt.val();
    if (!val) {
        $('#ModalAssetMeta').text('');
        return;
    }
    var cond = opt.data('cond') || 'BAIK';
    var loc = opt.data('loc') || '-';
    var user = opt.data('user') || '-';
    var buyVal = opt.data('val') || 0;
    $('#ModalAssetMeta').html('Lokasi: <strong>' + escapeHtml(loc) + '</strong> &bull; Kondisi: <strong>' + escapeHtml(cond) + '</strong> &bull; Pemegang: <strong>' + escapeHtml(user) + '</strong>');
    if ($('#FormActionType').val() === 'DISPOSAL') {
        $('#FormBookValue').val(buyVal);
    }
}

function submitAssetTicketForm(e) {
    e.preventDefault();
    var btn = $('#BtnSubmitAssetTicket');
    btn.prop('disabled', true);
    $.ajax({
        url: 'hrd-asset-ticketing-store',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: $('#AssetTicketForm').serialize(),
        success: function(res) {
            alert(res.message || 'Pengajuan berhasil disimpan.');
            if (assetTicketModalInstance) assetTicketModalInstance.hide();
            else $('#AssetTicketModal').modal('hide');
            $('#AssetTicketForm')[0].reset();
            loadAssetTicketingData();
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memproses pengajuan.');
        },
        complete: function() {
            btn.prop('disabled', false);
        }
    });
}

function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

$(document).ready(function() {
    loadAssetTicketingData();
    var prefillAction = @json($prefillAction);
    var prefillAssetNo = @json($prefillAssetNo);
    if (prefillAction && ['DAMAGE', 'PROCUREMENT', 'MOVING', 'DISPOSAL'].indexOf(prefillAction) >= 0) {
        openAssetTicketModal(prefillAction, prefillAssetNo);
    }
});
</script>
@endsection
