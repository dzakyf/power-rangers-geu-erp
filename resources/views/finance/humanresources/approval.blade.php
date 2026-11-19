@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<div class="breadcrumb-header justify-content-between">
    <div>
        <h4 class="content-title mb-1">Finance Approval &amp; Voucher (HR &amp; GA)</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Finance</a></li>
                <li class="breadcrumb-item"><a href="#">Human Resources &amp; GA</a></li>
                <li class="breadcrumb-item active" aria-current="page">Approval &amp; Voucher</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary btn-sm" onclick="loadFinanceApprovalData();">
            <i class="fa fa-sync me-1"></i> Muat Ulang Data
        </button>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Total Menunggu Proses</div>
                <div class="fs-3 fw-bold text-primary" id="kpiTotalPending">0</div>
                <div class="small text-muted">Seluruh antrean aktif</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Approval Inbox Workflow</div>
                <div class="fs-3 fw-bold text-info" id="kpiInbox">0</div>
                <div class="small text-muted">SPPD / Settlement / Reimburse</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Voucher Perawatan &amp; Pajak</div>
                <div class="fs-3 fw-bold text-warning" id="kpiVouchers">0</div>
                <div class="small text-muted"><span id="kpiMaintCnt">0</span> Maintenance &bull; <span id="kpiTaxCnt">0</span> STNK/KEUR</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Total Nilai Estimasi Voucher</div>
                <div class="fs-4 fw-bold text-success" id="kpiTotalAmount">Rp 0</div>
                <div class="small text-muted">Menunggu verifikasi/bayar</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Card: Clean Tabs without flex conflict -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light pt-3 pb-0 border-bottom">
        <ul class="nav nav-tabs card-header-tabs" id="FinanceApprovalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="tab-inbox-btn" data-bs-toggle="tab" data-bs-target="#tab-inbox" type="button" role="tab">
                    <i class="fa fa-inbox me-1"></i> Approval Inbox
                    <span class="badge bg-primary text-white ms-1" id="cntInbox">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="tab-maint-btn" data-bs-toggle="tab" data-bs-target="#tab-maint" type="button" role="tab">
                    <i class="fa fa-tools me-1"></i> Voucher Perawatan Aset
                    <span class="badge bg-warning text-dark ms-1" id="cntMaint">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="tab-tax-btn" data-bs-toggle="tab" data-bs-target="#tab-tax" type="button" role="tab">
                    <i class="fa fa-car me-1"></i> Voucher Pajak STNK / KEUR
                    <span class="badge bg-info text-white ms-1" id="cntTax">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="tab-history-btn" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab">
                    <i class="fa fa-history me-1"></i> Riwayat Selesai
                    <span class="badge bg-secondary text-white ms-1" id="cntHistory">0</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <!-- Toolbar Pencarian & Jumlah Baris -->
        <div class="row g-2 mb-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" id="FinSearchInput" class="form-control" placeholder="Cari No Request, Voucher, No Polisi, Aset, atau Pemohon..." oninput="resetPagesAndRender();" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm" style="max-width:190px;">
                    <span class="input-group-text bg-white">Tampilkan</span>
                    <select id="FinPerPage" class="form-select form-select-sm" onchange="resetPagesAndRender();">
                        <option value="10" selected>10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">Klik <strong>Detail</strong> atau klik dua kali baris untuk melihat rincian.</small>
            </div>
        </div>

        <div class="tab-content">
            <!-- Tab 1: Approval Inbox Finance -->
            <div class="tab-pane fade show active" id="tab-inbox" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No Request</th>
                                <th>Tipe</th>
                                <th>Pemohon</th>
                                <th>Dept</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Nominal</th>
                                <th>Tanggal Submit</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="FinanceInboxBody">
                            <tr><td colspan="8" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="PagerInbox" class="d-flex flex-wrap justify-content-between align-items-center mt-2"></div>
            </div>

            <!-- Tab 2: Voucher Perawatan Aset -->
            <div class="tab-pane fade" id="tab-maint" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Voucher Finance</th>
                                <th>No Schedule</th>
                                <th>Aset &amp; Lokasi</th>
                                <th>Jenis &amp; Frekuensi</th>
                                <th>PIC / Vendor</th>
                                <th class="text-end">Estimasi Biaya</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="FinanceMaintBody">
                            <tr><td colspan="7" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="PagerMaint" class="d-flex flex-wrap justify-content-between align-items-center mt-2"></div>
            </div>

            <!-- Tab 3: Voucher Pajak STNK / KEUR -->
            <div class="tab-pane fade" id="tab-tax" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Voucher Finance</th>
                                <th>No Pengajuan</th>
                                <th>Kendaraan &amp; Lokasi</th>
                                <th>Jenis</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Estimasi Biaya</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="FinanceTaxBody">
                            <tr><td colspan="7" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="PagerTax" class="d-flex flex-wrap justify-content-between align-items-center mt-2"></div>
            </div>

            <!-- Tab 4: Riwayat Selesai -->
            <div class="tab-pane fade" id="tab-history" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kategori</th>
                                <th>Voucher Finance</th>
                                <th>No Dokumen</th>
                                <th>Aset / Kendaraan</th>
                                <th class="text-end">Nominal</th>
                                <th>Status</th>
                                <th class="text-end">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody id="FinanceHistoryBody">
                            <tr><td colspan="7" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="PagerHistory" class="d-flex flex-wrap justify-content-between align-items-center mt-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- Inline Detail Container (seperti resources/views/humanresources/approval/index.blade.php) -->
<div id="FinanceApprovalDetail"></div>

<!-- Modal Pembayaran Voucher Pajak STNK / KEUR -->
<div class="modal fade" id="FinanceTaxPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title text-white">Pembayaran Voucher Pajak STNK / KEUR</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="FormFinanceTaxPay" class="row g-3" enctype="multipart/form-data">
                    <input type="hidden" id="Fin_Pay_Tax_ID" name="id" />
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input type="date" name="paid_date" class="form-control" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Masa Berlaku Baru <span class="text-danger">*</span></label>
                        <input type="date" name="new_due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">No. Referensi Transfer / Bukti</label>
                        <input type="text" name="bukti_bayar" class="form-control" placeholder="ex: TRF/FIN/2026/001" />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Upload Bukti Bayar (Gambar / PDF)</label>
                        <input type="file" id="Fin_Pay_Bukti_File" name="bukti_file" class="form-control" accept="image/*,.pdf" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" onclick="submitFinanceTaxPay();">Simpan Pembayaran</button>
            </div>
        </div>
    </div>
</div>

<script>
var finCache = { tasks: [], maintenance: [], vehicle_tax: [], history_maintenance: [], history_tax: [] };
var finPages = { inbox: 1, maint: 1, tax: 1, history: 1 };

function resetPagesAndRender() {
    finPages = { inbox: 1, maint: 1, tax: 1, history: 1 };
    applyFinanceSearch();
}

function paginateRows(rows, tabKey) {
    var perPage = parseInt($('#FinPerPage').val() || '10', 10);
    var total = rows.length;
    var totalPages = Math.max(1, Math.ceil(total / perPage));
    if (finPages[tabKey] > totalPages) finPages[tabKey] = totalPages;
    var page = finPages[tabKey];
    var start = (page - 1) * perPage;
    return {
        slice: rows.slice(start, start + perPage),
        page: page,
        totalPages: totalPages,
        total: total,
        start: total ? start + 1 : 0,
        end: Math.min(start + perPage, total)
    };
}

function renderPager(pagerId, tabKey, meta) {
    var el = $('#' + pagerId);
    if (meta.total === 0) {
        el.html('');
        return;
    }
    var info = '<small class="text-muted">Menampilkan ' + meta.start + '&ndash;' + meta.end + ' dari ' + meta.total + ' data</small>';
    var btns = '<nav><ul class="pagination pagination-sm mb-0">';
    btns += '<li class="page-item ' + (meta.page <= 1 ? 'disabled' : '') + '"><button class="page-link" type="button" onclick="gotoFinPage(\'' + tabKey + '\',' + (meta.page - 1) + ')">Prev</button></li>';
    var startP = Math.max(1, meta.page - 2);
    var endP = Math.min(meta.totalPages, meta.page + 2);
    for (var p = startP; p <= endP; p++) {
        btns += '<li class="page-item ' + (p === meta.page ? 'active' : '') + '"><button class="page-link" type="button" onclick="gotoFinPage(\'' + tabKey + '\',' + p + ')">' + p + '</button></li>';
    }
    btns += '<li class="page-item ' + (meta.page >= meta.totalPages ? 'disabled' : '') + '"><button class="page-link" type="button" onclick="gotoFinPage(\'' + tabKey + '\',' + (meta.page + 1) + ')">Next</button></li>';
    btns += '</ul></nav>';
    el.html(info + btns);
}

function gotoFinPage(tabKey, page) {
    finPages[tabKey] = Math.max(1, page);
    applyFinanceSearch();
}

function openFinanceDetail(type, id) {
    LoadM('FinanceApprovalDetail', 'finance-hr-approval-detail?type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id));
    var el = document.getElementById('FinanceApprovalDetail');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function escHtml(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function fmtRp(val) {
    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
}

function loadFinanceApprovalData() {
    $.get('finance-hr-approval-data', function (res) {
        finCache.tasks = res.tasks || [];
        finCache.maintenance = res.maintenance || [];
        finCache.vehicle_tax = res.vehicle_tax || [];
        finCache.history_maintenance = res.history_maintenance || [];
        finCache.history_tax = res.history_tax || [];

        var s = res.summary || {};
        $('#kpiTotalPending').text(s.total_pending || 0);
        $('#kpiInbox').text(s.inbox_count || 0);
        $('#kpiVouchers').text((s.maintenance_count || 0) + (s.vehicle_tax_count || 0));
        $('#kpiMaintCnt').text(s.maintenance_count || 0);
        $('#kpiTaxCnt').text(s.vehicle_tax_count || 0);
        $('#kpiTotalAmount').text(fmtRp(s.total_voucher_amount || 0));

        $('#cntInbox').text(finCache.tasks.length);
        $('#cntMaint').text(finCache.maintenance.length);
        $('#cntTax').text(finCache.vehicle_tax.length);
        $('#cntHistory').text(finCache.history_maintenance.length + finCache.history_tax.length);

        applyFinanceSearch();
    });
}

function applyFinanceSearch() {
    var q = ($('#FinSearchInput').val() || '').toLowerCase().trim();
    var match = function (row, fields) {
        if (!q) return true;
        return fields.some(function (f) { return String(row[f] || '').toLowerCase().indexOf(q) !== -1; });
    };

    renderInbox(finCache.tasks.filter(function (r) { return match(r, ['Request_No', 'Request_Type', 'Description', 'Department', 'Requester_Name', 'Requester_Username']); }));
    renderMaint(finCache.maintenance.filter(function (r) { return match(r, ['Voucher_No', 'Schedule_No', 'Asset_No', 'Asset_Name', 'PIC_Internal', 'Vendor_Name']); }));
    renderTax(finCache.vehicle_tax.filter(function (r) { return match(r, ['Voucher_No', 'Tax_Request_No', 'Asset_No', 'Registration_No', 'Product_Description']); }));
    renderHistory(
        finCache.history_maintenance.filter(function (r) { return match(r, ['Voucher_No', 'Schedule_No', 'Asset_No', 'Asset_Name']); }),
        finCache.history_tax.filter(function (r) { return match(r, ['Voucher_No', 'Tax_Request_No', 'Asset_No', 'Registration_No', 'Product_Description']); })
    );
}

function renderInbox(rows) {
    var tbody = $('#FinanceInboxBody');
    var meta = paginateRows(rows, 'inbox');
    renderPager('PagerInbox', 'inbox', meta);
    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada tugas approval aktif untuk Anda.</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (r) {
        html += '<tr ondblclick="openFinanceDetail(\'workflow\', ' + r.Task_ID + ')" style="cursor:pointer;">'
            + '<td class="fw-bold">' + escHtml(r.Request_No) + '</td>'
            + '<td><span class="badge bg-secondary text-white">' + escHtml(r.Request_Type) + '</span></td>'
            + '<td>' + escHtml(r.Requester_Name || r.Requester_Username || '-') + '</td>'
            + '<td>' + escHtml(r.Department || '-') + '</td>'
            + '<td>' + escHtml(r.Description || '-') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRp(r.Amount) + '</td>'
            + '<td>' + escHtml(r.Submitted_At || '-') + '</td>'
            + '<td class="text-end text-nowrap">'
            +   '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'workflow\', ' + r.Task_ID + ')">Detail</button>'
            +   '<button class="btn btn-xs btn-success me-1" onclick="actFinanceWorkflow(' + r.Task_ID + ', \'APPROVE\')">Approve</button>'
            +   '<button class="btn btn-xs btn-danger" onclick="actFinanceWorkflow(' + r.Task_ID + ', \'REJECT\')">Reject</button>'
            + '</td></tr>';
    });
    tbody.html(html);
}

function renderMaint(rows) {
    var tbody = $('#FinanceMaintBody');
    var meta = paginateRows(rows, 'maint');
    renderPager('PagerMaint', 'maint', meta);
    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada voucher perawatan aset yang menunggu verifikasi Finance.</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (r) {
        html += '<tr ondblclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')" style="cursor:pointer;">'
            + '<td class="fw-bold text-primary">' + escHtml(r.Voucher_No || '-') + '</td>'
            + '<td>' + escHtml(r.Schedule_No) + '</td>'
            + '<td>' + escHtml(r.Asset_No) + '<br><small class="text-muted">' + escHtml(r.Asset_Name || '-') + ' &bull; ' + escHtml(r.Asset_Location || '-') + '</small></td>'
            + '<td>' + escHtml(r.Jenis_Perawatan) + '<br><small class="text-muted">' + escHtml(r.Frekuensi) + ' &bull; Jadwal: ' + escHtml(r.Next_Schedule_Date) + '</small></td>'
            + '<td>' + escHtml(r.PIC_Internal || '-') + (r.Vendor_Name ? '<br><small class="text-info">' + escHtml(r.Vendor_Name) + '</small>' : '') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRp(r.Estimasi_Biaya) + '</td>'
            + '<td class="text-end text-nowrap">'
            +   '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')">Detail</button>'
            +   '<button class="btn btn-xs btn-success" onclick="confirmMaintVoucher(' + r.ID + ')">Verifikasi &amp; Setujui</button>'
            + '</td></tr>';
    });
    tbody.html(html);
}

function renderTax(rows) {
    var tbody = $('#FinanceTaxBody');
    var meta = paginateRows(rows, 'tax');
    renderPager('PagerTax', 'tax', meta);
    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada voucher pajak STNK/KEUR yang menunggu pembayaran Finance.</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (r) {
        html += '<tr ondblclick="openFinanceDetail(\'tax\', ' + r.ID + ')" style="cursor:pointer;">'
            + '<td class="fw-bold text-primary">' + escHtml(r.Voucher_No || '-') + '</td>'
            + '<td>' + escHtml(r.Tax_Request_No) + '</td>'
            + '<td><strong>' + escHtml(r.Registration_No || r.Asset_No) + '</strong><br><small class="text-muted">' + escHtml(r.Product_Description || '-') + ' &bull; ' + escHtml(r.Asset_Location || '-') + '</small></td>'
            + '<td><span class="badge bg-dark text-white">' + escHtml(r.Jenis_Pajak) + '</span></td>'
            + '<td>' + escHtml(r.Tanggal_Jatuh_Tempo || '-') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRp(r.Estimasi_Biaya) + '</td>'
            + '<td class="text-end text-nowrap">'
            +   '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'tax\', ' + r.ID + ')">Detail</button>'
            +   '<a href="hrd-vehicle-management-tax-voucher?id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1">Cetak Voucher</a>'
            +   '<button class="btn btn-xs btn-success" onclick="openFinanceTaxPayModal(' + r.ID + ')">Bayar &amp; Selesai</button>'
            + '</td></tr>';
    });
    tbody.html(html);
}

function renderHistory(maintRows, taxRows) {
    var tbody = $('#FinanceHistoryBody');
    var combined = [];
    maintRows.forEach(function (r) { combined.push({ kind: 'maintenance', row: r }); });
    taxRows.forEach(function (r) { combined.push({ kind: 'tax', row: r }); });
    var meta = paginateRows(combined, 'history');
    renderPager('PagerHistory', 'history', meta);

    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Belum ada riwayat voucher yang selesai diproses.</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (item) {
        var r = item.row;
        if (item.kind === 'maintenance') {
            html += '<tr ondblclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')" style="cursor:pointer;">'
                + '<td><span class="badge bg-warning text-dark">Perawatan Aset</span></td>'
                + '<td class="fw-bold">' + escHtml(r.Voucher_No || '-') + '</td>'
                + '<td>' + escHtml(r.Schedule_No) + '</td>'
                + '<td>' + escHtml(r.Asset_No) + ' - ' + escHtml(r.Asset_Name || '-') + '</td>'
                + '<td class="text-end">' + fmtRp(r.Estimasi_Biaya) + '</td>'
                + '<td><span class="badge bg-success text-white">' + escHtml(r.Status) + '</span></td>'
                + '<td class="text-end text-nowrap"><button class="btn btn-xs btn-outline-primary" onclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')">Detail</button></td>'
                + '</tr>';
        } else {
            var docs = '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'tax\', ' + r.ID + ')">Detail</button>'
                + '<a href="hrd-vehicle-management-tax-voucher?id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1">Voucher</a>';
            if (r.Bukti_Bayar && r.Bukti_Bayar.indexOf('/') !== -1) {
                docs += '<a href="{{ asset("filevendor") }}/' + escHtml(r.Bukti_Bayar) + '" target="_blank" class="btn btn-xs btn-outline-success">Bukti</a>';
            }
            html += '<tr ondblclick="openFinanceDetail(\'tax\', ' + r.ID + ')" style="cursor:pointer;">'
                + '<td><span class="badge bg-info text-white">Pajak STNK/KEUR</span></td>'
                + '<td class="fw-bold">' + escHtml(r.Voucher_No || '-') + '</td>'
                + '<td>' + escHtml(r.Tax_Request_No) + '</td>'
                + '<td>' + escHtml(r.Registration_No || r.Asset_No) + ' - ' + escHtml(r.Product_Description || '-') + '</td>'
                + '<td class="text-end">' + fmtRp(r.Estimasi_Biaya) + '</td>'
                + '<td><span class="badge bg-success text-white">PAID (' + escHtml(r.Tanggal_Bayar || '-') + ')</span></td>'
                + '<td class="text-end text-nowrap">' + docs + '</td>'
                + '</tr>';
        }
    });
    tbody.html(html);
}

function actFinanceWorkflow(taskId, action, closeDetail) {
    var notes = action === 'REJECT' ? (prompt('Alasan penolakan:') || '') : '';
    $.ajax({
        url: 'finance-hr-approval-workflow-act',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { task_id: taskId, action: action, notes: notes },
        success: function (res) {
            alert(res.message);
            if (closeDetail) $('#FinanceApprovalDetail').empty();
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memproses.'); }
    });
}

function confirmMaintVoucher(id, closeDetail) {
    if (!confirm('Verifikasi voucher perawatan aset ini?')) return;
    $.ajax({
        url: 'finance-hr-approval-maintenance-confirm',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { id: id },
        success: function (res) {
            alert(res.message);
            if (closeDetail) $('#FinanceApprovalDetail').empty();
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memproses voucher.'); }
    });
}

function openFinanceTaxPayModal(id) {
    $('#Fin_Pay_Tax_ID').val(id);
    $('#Fin_Pay_Bukti_File').val('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('FinanceTaxPayModal')).show();
}

function submitFinanceTaxPay() {
    var fd = new FormData(document.getElementById('FormFinanceTaxPay'));
    $.ajax({
        url: 'finance-hr-approval-tax-pay',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            alert(res.message);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('FinanceTaxPayModal')).hide();
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mencatat pembayaran.'); }
    });
}

loadFinanceApprovalData();
</script>
@endsection
