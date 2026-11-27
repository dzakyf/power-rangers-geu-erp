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
        <button type="button" class="btn btn-outline-dark btn-sm" onclick="openCoaMappingModal();">
            <i class="fa fa-sitemap me-1"></i> Mapping COA
        </button>
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
                <div class="small text-muted">Payroll / THR / SPPD / Reimburse</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Voucher GA, Perawatan, Pajak &amp; Manual</div>
                <div class="fs-3 fw-bold text-warning" id="kpiVouchers">0</div>
                <div class="small text-muted"><span id="kpiGaVoucherCnt">0</span> GA &bull; <span id="kpiMaintCnt">0</span> Maint &bull; <span id="kpiTaxCnt">0</span> STNK &bull; <span id="kpiManualCnt">0</span> Manual &bull; <span id="kpiApCnt">0</span> AP Aktif</div>
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
                <button class="nav-link fw-semibold" id="tab-ga-voucher-btn" data-bs-toggle="tab" data-bs-target="#tab-ga-voucher" type="button" role="tab">
                    <i class="fa fa-file-invoice-dollar me-1"></i> Voucher GA Order Pekerjaan
                    <span class="badge bg-success text-white ms-1" id="cntGaVoucher">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="tab-tax-btn" data-bs-toggle="tab" data-bs-target="#tab-tax" type="button" role="tab">
                    <i class="fa fa-car me-1"></i> Voucher Pajak STNK / KEUR
                    <span class="badge bg-info text-white ms-1" id="cntTax">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="tab-manual-voucher-btn" data-bs-toggle="tab" data-bs-target="#tab-manual-voucher" type="button" role="tab">
                    <i class="fa fa-file-alt me-1"></i> Voucher / AP Manual
                    <span class="badge bg-primary text-white ms-1" id="cntManualVoucher">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="tab-ap-monitoring-btn" data-bs-toggle="tab" data-bs-target="#tab-ap-monitoring" type="button" role="tab">
                    <i class="fa fa-credit-card me-1"></i> Hutang Dagang (AP) &amp; Tenor
                    <span class="badge bg-danger text-white ms-1" id="cntApVoucher">0</span>
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

            <!-- Tab GA Order Pekerjaan Vouchers -->
            <div class="tab-pane fade" id="tab-ga-voucher" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Voucher</th>
                                <th>No. Tiket</th>
                                <th>Subjek &amp; Dept</th>
                                <th>Penerima / Bank</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="FinanceGaVoucherBody">
                            <tr><td colspan="7" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="PagerGaVoucher" class="d-flex flex-wrap justify-content-between align-items-center mt-2"></div>
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

            <!-- Tab: Voucher / AP Manual HRGA -->
            <div class="tab-pane fade" id="tab-manual-voucher" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Voucher</th>
                                <th>Usulan Jalur</th>
                                <th>Ref PO / Faktur</th>
                                <th>Vendor &amp; Perihal</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="FinanceManualVoucherBody">
                            <tr><td colspan="7" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="PagerManualVoucher" class="d-flex flex-wrap justify-content-between align-items-center mt-2"></div>
            </div>

            <!-- Tab: Hutang Dagang (AP) & Tenor Treasury -->
            <div class="tab-pane fade" id="tab-ap-monitoring" role="tabpanel">
                <div class="alert alert-info py-2 small d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <span><i class="fa fa-info-circle me-1"></i> Voucher berjalur AP tersinkron otomatis ke tabel <code>account_payable</code>. Proses lanjut (Receive, Request Payment, &amp; Pelunasan Partial/Full) menggunakan menu legacy <strong>Account Payable</strong>.</span>
                    <div class="d-flex flex-wrap gap-1">
                        <a href="{{ url('finance-ap-receive') }}" class="btn btn-xs btn-outline-primary">Receive From Other Dept</a>
                        <a href="{{ url('finance-ap-request') }}" class="btn btn-xs btn-outline-info">Request Payment</a>
                        <a href="{{ url('finance-ap-management') }}" class="btn btn-xs btn-success">AP Management (Bayar)</a>
                        <a href="{{ url('finance-ap-history') }}" class="btn btn-xs btn-outline-secondary">AP History</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Voucher &amp; Task AP</th>
                                <th>Sumber &amp; Ref PO</th>
                                <th>Vendor &amp; Perihal</th>
                                <th>Jatuh Tempo / Tenor</th>
                                <th class="text-end">Total AP</th>
                                <th class="text-end">Sisa Hutang</th>
                                <th>Status (AP Legacy)</th>
                                <th class="text-end">Menu Legacy AP</th>
                            </tr>
                        </thead>
                        <tbody id="FinanceApVoucherBody">
                            <tr><td colspan="8" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="PagerApVoucher" class="d-flex flex-wrap justify-content-between align-items-center mt-2"></div>
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

<!-- Modal Pembayaran Voucher GA Order Pekerjaan -->
<div class="modal fade" id="FinanceGaPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title text-white">Pembayaran Voucher GA Order Pekerjaan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="FormFinanceGaPay" class="row g-3" enctype="multipart/form-data">
                    <input type="hidden" id="Fin_Pay_Ga_ID" name="id" />
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input type="date" name="paid_date" class="form-control" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Referensi Transfer</label>
                        <input type="text" name="payment_ref" id="Fin_Pay_Ga_Ref" class="form-control" placeholder="ex: TRF/GA/2026/001" />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Upload Bukti Bayar (Gambar / PDF)</label>
                        <input type="file" id="Fin_Pay_Ga_File" name="proof_file" class="form-control" accept="image/*,.pdf" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" onclick="submitFinanceGaPay();">Simpan Pembayaran</button>
            </div>
        </div>
    </div>
</div>

@include('finance.humanresources.partials.voucher-process-modal')
@include('finance.humanresources.partials.coa-mapping-modal')
@include('finance.humanresources.partials.ap-pay-modal')

<script>
var finCache = { tasks: [], maintenance: [], vehicle_tax: [], ga_vouchers: [], manual_vouchers: [], ap_vouchers: [], history_maintenance: [], history_tax: [], history_ga_vouchers: [], history_manual_vouchers: [], history_workflows: [] };
var finPages = { inbox: 1, maint: 1, tax: 1, ga_voucher: 1, manual_voucher: 1, ap_voucher: 1, history: 1 };

function resetPagesAndRender() {
    finPages = { inbox: 1, maint: 1, tax: 1, ga_voucher: 1, manual_voucher: 1, ap_voucher: 1, history: 1 };
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
        finCache.ga_vouchers = res.ga_vouchers || [];
        finCache.manual_vouchers = res.manual_vouchers || [];
        finCache.ap_vouchers = res.ap_vouchers || [];
        finCache.history_maintenance = res.history_maintenance || [];
        finCache.history_tax = res.history_tax || [];
        finCache.history_ga_vouchers = res.history_ga_vouchers || [];
        finCache.history_manual_vouchers = res.history_manual_vouchers || [];
        finCache.history_workflows = res.history_workflows || [];

        var s = res.summary || {};
        $('#kpiTotalPending').text(s.total_pending || 0);
        $('#kpiInbox').text(s.inbox_count || 0);
        $('#kpiVouchers').text((s.maintenance_count || 0) + (s.vehicle_tax_count || 0) + (s.ga_voucher_count || 0) + (s.manual_voucher_count || 0));
        $('#kpiGaVoucherCnt').text(s.ga_voucher_count || 0);
        $('#kpiMaintCnt').text(s.maintenance_count || 0);
        $('#kpiTaxCnt').text(s.vehicle_tax_count || 0);
        $('#kpiManualCnt').text(s.manual_voucher_count || 0);
        $('#kpiApCnt').text(s.ap_voucher_count || 0);
        $('#kpiTotalAmount').text(fmtRp(s.total_voucher_amount || 0));

        $('#cntInbox').text(finCache.tasks.length);
        $('#cntMaint').text(finCache.maintenance.length);
        $('#cntTax').text(finCache.vehicle_tax.length);
        $('#cntGaVoucher').text(finCache.ga_vouchers.length);
        $('#cntManualVoucher').text(finCache.manual_vouchers.length);
        $('#cntApVoucher').text(s.ap_voucher_count || 0);
        $('#cntHistory').text(finCache.history_maintenance.length + finCache.history_tax.length + finCache.history_ga_vouchers.length + finCache.history_manual_vouchers.length + finCache.history_workflows.length);

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
    renderGaVouchers(finCache.ga_vouchers.filter(function (r) { return match(r, ['Voucher_No', 'Ticket_No', 'Ticket_Subject', 'Payee_Name', 'Department', 'Reporter_Name']); }));
    renderManualVouchers(finCache.manual_vouchers.filter(function (r) { return match(r, ['Voucher_No', 'PO_References', 'Reference', 'Vendor_Name', 'Subject_Payment', 'Department']); }));
    renderApVouchers(finCache.ap_vouchers.filter(function (r) { return match(r, ['Voucher_No', 'Legacy_AP_Task_ID', 'PO_References', 'Reference', 'Vendor_Name', 'Subject_Payment']); }));
    renderHistory(
        finCache.history_maintenance.filter(function (r) { return match(r, ['Voucher_No', 'Schedule_No', 'Asset_No', 'Asset_Name']); }),
        finCache.history_tax.filter(function (r) { return match(r, ['Voucher_No', 'Tax_Request_No', 'Asset_No', 'Registration_No', 'Product_Description']); }),
        finCache.history_ga_vouchers.filter(function (r) { return match(r, ['Voucher_No', 'Ticket_No', 'Ticket_Subject', 'Payee_Name']); }),
        finCache.history_workflows.filter(function (r) { return match(r, ['Request_No', 'Request_Type', 'Description', 'Department', 'Requester_Name', 'Requester_Username']); }),
        finCache.history_manual_vouchers.filter(function (r) { return match(r, ['Voucher_No', 'PO_References', 'Reference', 'Vendor_Name', 'Subject_Payment']); })
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
        var chBadge = r.Requested_Channel === 'AP' ? '<span class="badge bg-info text-white ms-1">Usulan AP</span>' : '<span class="badge bg-light text-dark border ms-1">Direct</span>';
        html += '<tr ondblclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')" style="cursor:pointer;">'
            + '<td class="fw-bold text-primary">' + escHtml(r.Voucher_No || '-') + chBadge + '</td>'
            + '<td>' + escHtml(r.Schedule_No) + '</td>'
            + '<td>' + escHtml(r.Asset_No) + '<br><small class="text-muted">' + escHtml(r.Asset_Name || '-') + ' &bull; ' + escHtml(r.Asset_Location || '-') + '</small></td>'
            + '<td>' + escHtml(r.Jenis_Perawatan) + '<br><small class="text-muted">' + escHtml(r.Frekuensi) + ' &bull; Jadwal: ' + escHtml(r.Next_Schedule_Date) + '</small></td>'
            + '<td>' + escHtml(r.PIC_Internal || '-') + (r.Vendor_Name ? '<br><small class="text-info">' + escHtml(r.Vendor_Name) + '</small>' : '') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRp(r.Estimasi_Biaya) + '</td>'
            + '<td class="text-end text-nowrap">'
            +   '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')">Detail</button>'
            +   '<a href="finance-hr-voucher-print?source_module=ASSET_MAINTENANCE&source_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1"><i class="fa fa-print"></i> Print</a>'
            +   '<button class="btn btn-xs btn-primary me-1" onclick="openFinVoucherReview({ source_module: \'ASSET_MAINTENANCE\', source_id: ' + r.ID + ', voucher_id: ' + (r.Central_Voucher_ID || 0) + ' })">Proses COA / AP</button>'
            +   '<button class="btn btn-xs btn-outline-danger" onclick="quickRejectSourceVoucher(\'ASSET_MAINTENANCE\', ' + r.ID + ')">Reject</button>'
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
        var chBadge = r.Requested_Channel === 'AP' ? '<span class="badge bg-info text-white ms-1">Usulan AP</span>' : '<span class="badge bg-light text-dark border ms-1">Direct</span>';
        html += '<tr ondblclick="openFinanceDetail(\'tax\', ' + r.ID + ')" style="cursor:pointer;">'
            + '<td class="fw-bold text-primary">' + escHtml(r.Voucher_No || '-') + chBadge + '</td>'
            + '<td>' + escHtml(r.Tax_Request_No) + '</td>'
            + '<td><strong>' + escHtml(r.Registration_No || r.Asset_No) + '</strong><br><small class="text-muted">' + escHtml(r.Product_Description || '-') + ' &bull; ' + escHtml(r.Asset_Location || '-') + '</small></td>'
            + '<td><span class="badge bg-dark text-white">' + escHtml(r.Jenis_Pajak) + '</span></td>'
            + '<td>' + escHtml(r.Tanggal_Jatuh_Tempo || '-') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRp(r.Estimasi_Biaya) + '</td>'
            + '<td class="text-end text-nowrap">'
            +   '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'tax\', ' + r.ID + ')">Detail</button>'
            +   '<a href="finance-hr-voucher-print?source_module=VEHICLE_TAX&source_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1"><i class="fa fa-print"></i> Print</a>'
            +   '<button class="btn btn-xs btn-primary me-1" onclick="openFinVoucherReview({ source_module: \'VEHICLE_TAX\', source_id: ' + r.ID + ', voucher_id: ' + (r.Central_Voucher_ID || 0) + ' })">Proses COA / AP</button>'
            +   '<button class="btn btn-xs btn-outline-danger" onclick="quickRejectSourceVoucher(\'VEHICLE_TAX\', ' + r.ID + ')">Reject</button>'
            + '</td></tr>';
    });
    tbody.html(html);
}

function renderGaVouchers(rows) {
    var tbody = $('#FinanceGaVoucherBody');
    var meta = paginateRows(rows, 'ga_voucher');
    renderPager('PagerGaVoucher', 'ga_voucher', meta);
    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada voucher GA Order Pekerjaan yang menunggu pembayaran Finance.</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (r) {
        var chBadge = r.Requested_Channel === 'AP' ? '<span class="badge bg-info text-white ms-1">Usulan AP</span>' : '<span class="badge bg-light text-dark border ms-1">Direct</span>';
        html += '<tr ondblclick="openFinanceDetail(\'ga_voucher\', ' + r.ID + ')" style="cursor:pointer;">'
            + '<td class="fw-bold text-primary">' + escHtml(r.Voucher_No) + chBadge + '</td>'
            + '<td>' + escHtml(r.Ticket_No) + '</td>'
            + '<td><strong>' + escHtml(r.Ticket_Subject || '-') + '</strong><br><small class="text-muted">' + escHtml(r.Department || '-') + ' &bull; ' + escHtml(r.Reporter_Name || r.Reporter || '-') + '</small></td>'
            + '<td>' + escHtml(r.Payee_Name) + '<br><small class="text-muted">' + escHtml(r.Payment_Method || 'TRANSFER') + ' ' + escHtml(r.Bank_Name || '') + ' ' + escHtml(r.Bank_Account_No || '') + '</small></td>'
            + '<td>' + escHtml(r.Due_Date || '-') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRp(r.Amount) + '</td>'
            + '<td class="text-end text-nowrap">'
            +   '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'ga_voucher\', ' + r.ID + ')">Detail</button>'
            +   '<a href="finance-hr-voucher-print?source_module=GA_WORK_ORDER&source_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1"><i class="fa fa-print"></i> Print</a>'
            +   '<button class="btn btn-xs btn-primary me-1" onclick="openFinVoucherReview({ source_module: \'GA_WORK_ORDER\', source_id: ' + r.ID + ', voucher_id: ' + (r.Central_Voucher_ID || 0) + ' })">Proses COA / AP</button>'
            +   '<button class="btn btn-xs btn-outline-danger" onclick="quickRejectSourceVoucher(\'GA_WORK_ORDER\', ' + r.ID + ')">Reject</button>'
            + '</td></tr>';
    });
    tbody.html(html);
}

function renderManualVouchers(rows) {
    var tbody = $('#FinanceManualVoucherBody');
    var meta = paginateRows(rows, 'manual_voucher');
    renderPager('PagerManualVoucher', 'manual_voucher', meta);
    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pengajuan Voucher / AP Manual yang menunggu Finance.</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (r) {
        var reqBadge = r.Requested_Channel === 'AP'
            ? '<span class="badge bg-primary text-white">Usulan AP (Hutang)</span>'
            : '<span class="badge bg-warning text-dark">Usulan Direct (' + escHtml(r.Payment_Method || 'BANK') + ')</span>';
        html += '<tr>'
            + '<td class="fw-bold text-primary">' + escHtml(r.Voucher_No) + '<br><small class="text-muted fw-normal">' + escHtml(r.Expense_Category || '') + '</small>' + (r.Legacy_AP_Task_ID ? '<br><small class="text-info">AP: ' + escHtml(r.Legacy_AP_Task_ID) + '</small>' : '') + '</td>'
            + '<td>' + reqBadge + '</td>'
            + '<td>' + (r.PO_References ? '<span class="badge bg-light text-dark border">PO: ' + escHtml(r.PO_References) + '</span><br>' : '') + '<small>Faktur: ' + escHtml(r.Reference || '-') + '</small></td>'
            + '<td><strong>' + escHtml(r.Vendor_Name || '-') + '</strong><br><small class="text-muted">' + escHtml(r.Department || '-') + ' &bull; ' + escHtml(r.Subject_Payment || '-') + '</small></td>'
            + '<td>' + escHtml(r.Due_Date || '-') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRp(r.Total) + '</td>'
            + '<td class="text-end text-nowrap">'
            +   '<a href="finance-hr-voucher-print?voucher_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1"><i class="fa fa-print"></i> Print</a>'
            +   '<button class="btn btn-xs btn-primary me-1" onclick="openFinVoucherReview({ voucher_id: ' + r.ID + ' })">Verifikasi &amp; Proses COA/AP</button>'
            +   '<button class="btn btn-xs btn-outline-danger" onclick="quickRejectCentralVoucher(' + r.ID + ')">Reject</button>'
            + '</td></tr>';
    });
    tbody.html(html);
}

function renderApVouchers(rows) {
    var tbody = $('#FinanceApVoucherBody');
    var meta = paginateRows(rows, 'ap_voucher');
    renderPager('PagerApVoucher', 'ap_voucher', meta);
    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted py-3">Belum ada Voucher Hutang Dagang (AP).</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (r) {
        var effStatus = r.AP_Status || r.Status;
        var stBadge = (effStatus === 'CLOSED' || effStatus === 'PAID')
            ? '<span class="badge bg-success text-white">' + escHtml(effStatus) + ' (Lunas)</span>'
            : (effStatus === 'PARTIAL'
                ? '<span class="badge bg-warning text-dark">PARTIAL</span>'
                : '<span class="badge bg-info text-white">' + escHtml(effStatus) + '</span>');

        var tenorSummary = '';
        if ((r.tenors || []).length > 1) {
            tenorSummary = '<br><small class="text-muted">' + r.tenors.length + ' termin manual</small>';
        }

        var actions = '<a href="finance-hr-voucher-print?voucher_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1"><i class="fa fa-print"></i> Print</a>';
        if (effStatus === 'PROPOSED' || effStatus === 'NEED_APPROVE') {
            actions += '<a href="{{ url("finance-ap-receive") }}" class="btn btn-xs btn-outline-primary me-1">Buka Receive From Other Dept</a>';
        } else if (effStatus === 'RECEIVED_FINANCE') {
            actions += '<a href="{{ url("finance-ap-request") }}" class="btn btn-xs btn-outline-info me-1">Request Payment</a>';
            actions += '<a href="{{ url("finance-ap-management") }}" class="btn btn-xs btn-success">AP Management</a>';
        } else if (effStatus !== 'CLOSED' && effStatus !== 'PAID' && Number(r.Remain_Val || 0) > 0) {
            actions += '<a href="{{ url("finance-ap-management") }}" class="btn btn-xs btn-success">AP Management (Bayar)</a>';
        } else {
            actions += '<a href="{{ url("finance-ap-history") }}" class="btn btn-xs btn-outline-secondary">AP History</a>';
        }

        html += '<tr>'
            + '<td class="fw-bold text-primary">' + escHtml(r.Voucher_No) + '<br><small class="text-muted fw-normal">' + escHtml(r.Legacy_AP_Task_ID || '-') + '</small></td>'
            + '<td><span class="badge bg-light text-dark border">' + escHtml(r.Source_Module) + '</span>' + (r.PO_References ? '<br><small>PO: ' + escHtml(r.PO_References) + '</small>' : '') + (r.Is_Converted_To_AP == 1 ? '<br><small class="text-info">Konversi Direct &rarr; AP</small>' : '') + '</td>'
            + '<td><strong>' + escHtml(r.Vendor_Name || '-') + '</strong><br><small class="text-muted">' + escHtml(r.Reference || '-') + ' &bull; ' + escHtml(r.Subject_Payment || '-') + '</small></td>'
            + '<td>' + escHtml(r.Due_Date || '-') + tenorSummary + '</td>'
            + '<td class="text-end">' + fmtRp(r.Total) + '</td>'
            + '<td class="text-end fw-bold text-danger">' + fmtRp(r.Remain_Val) + '</td>'
            + '<td>' + stBadge + '</td>'
            + '<td class="text-end text-nowrap">' + actions + '</td>'
            + '</tr>';
    });
    tbody.html(html);
}

function renderHistory(maintRows, taxRows, gaVoucherRows, workflowRows, manualRows) {
    var tbody = $('#FinanceHistoryBody');
    var combined = [];
    (workflowRows || []).forEach(function (r) { combined.push({ kind: 'workflow', row: r }); });
    maintRows.forEach(function (r) { combined.push({ kind: 'maintenance', row: r }); });
    taxRows.forEach(function (r) { combined.push({ kind: 'tax', row: r }); });
    (gaVoucherRows || []).forEach(function (r) { combined.push({ kind: 'ga_voucher', row: r }); });
    (manualRows || []).forEach(function (r) { combined.push({ kind: 'manual_voucher', row: r }); });
    var meta = paginateRows(combined, 'history');
    renderPager('PagerHistory', 'history', meta);

    if (!meta.slice.length) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Belum ada riwayat approval atau voucher yang selesai diproses.</td></tr>');
        return;
    }
    var html = '';
    meta.slice.forEach(function (item) {
        var r = item.row;
        if (item.kind === 'workflow') {
            var stBadge = (String(r.Task_Status || '').toUpperCase() === 'REJECTED')
                ? '<span class="badge bg-danger text-white">REJECTED</span>'
                : '<span class="badge bg-success text-white">' + escHtml(r.Task_Status || 'APPROVED') + '</span>';
            html += '<tr ondblclick="openFinanceDetail(\'workflow\', ' + r.Task_ID + ')" style="cursor:pointer;">'
                + '<td><span class="badge bg-primary text-white">' + escHtml(r.Request_Type || 'WORKFLOW') + '</span></td>'
                + '<td class="fw-bold">Task #' + escHtml(r.Task_ID) + '</td>'
                + '<td class="fw-semibold">' + escHtml(r.Request_No) + '</td>'
                + '<td>' + escHtml(r.Description || '-') + '<br><small class="text-muted">Pemohon: ' + escHtml(r.Requester_Name || r.Requester_Username || '-') + ' (' + escHtml(r.Department || '-') + ')</small></td>'
                + '<td class="text-end fw-semibold">' + fmtRp(r.Amount) + '</td>'
                + '<td>' + stBadge + '</td>'
                + '<td class="text-end text-nowrap"><button class="btn btn-xs btn-outline-primary" onclick="openFinanceDetail(\'workflow\', ' + r.Task_ID + ')">Detail</button></td>'
                + '</tr>';
        } else if (item.kind === 'maintenance') {
            html += '<tr ondblclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')" style="cursor:pointer;">'
                + '<td><span class="badge bg-warning text-dark">Perawatan Aset</span></td>'
                + '<td class="fw-bold">' + escHtml(r.Voucher_No || '-') + '</td>'
                + '<td>' + escHtml(r.Schedule_No) + '</td>'
                + '<td>' + escHtml(r.Asset_No) + ' - ' + escHtml(r.Asset_Name || '-') + '</td>'
                + '<td class="text-end">' + fmtRp(r.Estimasi_Biaya) + '</td>'
                + '<td><span class="badge bg-success text-white">' + escHtml(r.Status) + '</span></td>'
                + '<td class="text-end text-nowrap"><button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'maintenance\', ' + r.ID + ')">Detail</button><a href="finance-hr-voucher-print?source_module=ASSET_MAINTENANCE&source_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary">Voucher</a></td>'
                + '</tr>';
        } else if (item.kind === 'ga_voucher') {
            var gaDocs = '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'ga_voucher\', ' + r.ID + ')">Detail</button>'
                + '<a href="finance-hr-voucher-print?source_module=GA_WORK_ORDER&source_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1">Voucher</a>';
            if (r.Payment_Proof_Path) {
                gaDocs += '<a href="{{ asset("filevendor") }}/' + escHtml(r.Payment_Proof_Path) + '" target="_blank" class="btn btn-xs btn-outline-success">Bukti</a>';
            }
            html += '<tr ondblclick="openFinanceDetail(\'ga_voucher\', ' + r.ID + ')" style="cursor:pointer;">'
                + '<td><span class="badge bg-success text-white">GA Order Voucher</span></td>'
                + '<td class="fw-bold">' + escHtml(r.Voucher_No) + '</td>'
                + '<td>' + escHtml(r.Ticket_No) + '</td>'
                + '<td>' + escHtml(r.Payee_Name) + ' - ' + escHtml(r.Ticket_Subject || '-') + '</td>'
                + '<td class="text-end">' + fmtRp(r.Amount) + '</td>'
                + '<td><span class="badge bg-success text-white">' + escHtml(r.Status) + ' (' + escHtml(r.Paid_Date || '-') + ')</span></td>'
                + '<td class="text-end text-nowrap">' + gaDocs + '</td>'
                + '</tr>';
        } else if (item.kind === 'manual_voucher') {
            var mBadge = (r.Status === 'RETURNED' || r.Status === 'CANCELLED')
                ? '<span class="badge bg-danger text-white">' + escHtml(r.Status) + '</span>'
                : '<span class="badge bg-success text-white">' + escHtml(r.Status) + ' (' + escHtml(r.Final_Channel || '-') + ')</span>';
            var legacyLink = '<a href="finance-hr-voucher-print?voucher_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1">Voucher</a>';
            if (r.Legacy_Cash_No) {
                legacyLink += '<a href="{{ url("finance-cash-out") }}" class="btn btn-xs btn-outline-success me-1">Cash Out</a>';
            } else if (r.Legacy_Bank_No) {
                legacyLink += '<a href="{{ url("finance-bank-out") }}" class="btn btn-xs btn-outline-primary me-1">Bank Out</a>';
            } else if (r.Legacy_AP_ID) {
                legacyLink += '<a href="{{ url("finance-ap-history") }}" class="btn btn-xs btn-outline-info me-1">AP History</a>';
            }
            html += '<tr>'
                + '<td><span class="badge bg-primary text-white">Manual Voucher</span></td>'
                + '<td class="fw-bold">' + escHtml(r.Voucher_No) + '</td>'
                + '<td>' + escHtml(r.PO_References || r.Reference || '-') + '</td>'
                + '<td>' + escHtml(r.Vendor_Name || '-') + ' - ' + escHtml(r.Subject_Payment || '-') + '</td>'
                + '<td class="text-end">' + fmtRp(r.Total) + '</td>'
                + '<td>' + mBadge + '</td>'
                + '<td class="text-end text-nowrap">' + legacyLink + '<small class="text-muted">' + escHtml(r.Journal_No || '-') + '</small></td>'
                + '</tr>';
        } else {
            var docs = '<button class="btn btn-xs btn-outline-primary me-1" onclick="openFinanceDetail(\'tax\', ' + r.ID + ')">Detail</button>'
                + '<a href="finance-hr-voucher-print?source_module=VEHICLE_TAX&source_id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1">Voucher</a>';
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

function openFinanceGaPayModal(id) {
    $('#Fin_Pay_Ga_ID').val(id);
    $('#Fin_Pay_Ga_Ref').val('');
    $('#Fin_Pay_Ga_File').val('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('FinanceGaPayModal')).show();
}

function submitFinanceGaPay() {
    var fd = new FormData(document.getElementById('FormFinanceGaPay'));
    $.ajax({
        url: 'finance-hr-approval-ga-pay',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            alert(res.message);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('FinanceGaPayModal')).hide();
            $('#FinanceApprovalDetail').empty();
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mencatat pembayaran voucher GA.'); }
    });
}

// ============================================================================
// Finance Review, COA Multi-Debet/Kredit (branch_trial_balance -> branch_coa),
// Konversi Direct <-> AP, Tenor Manual, & Pelunasan Fleksibel Treasury
// ============================================================================
var finTbGroups = [];
var finCoaCacheByHeader = {};
var finBankAccounts = [];
var finCurrentVoucherDept = '';
var finCoaMappingsCache = null;
var finCoaGuideState = { cat: 'ALL' };

function openFinVoucherReview(params) {
    $.get('finance-hr-voucher-review', params, function (res) {
        if (res.status !== 'success') return;
        var d = res.data;
        var v = d.voucher;
        finTbGroups = d.tb_groups || [];
        finBankAccounts = d.bank_accounts || [];
        finCurrentVoucherDept = v.Department || '';
        $('#fvCoaGuidePanel').hide();
        $('#fvCoaGuideTbFilter').data('populated', false).html('<option value="">-- Semua Kelompok TB --</option>');

        $('#fv_id').val(v.ID);
        $('#fv_source_module').val(v.Source_Module);
        $('#fv_requested_channel').val(v.Requested_Channel || 'DIRECT');
        $('#FinVpModalTitle').text('Verifikasi & Penerbitan Voucher — ' + v.Voucher_No);
        $('#FinVpModalSub').text('Sumber: ' + v.Source_Module + (v.Source_No ? ' (' + v.Source_No + ')' : '') + ' • Dibuat oleh: ' + (v.Created_By || '-'));

        $('#fv_final_channel').val(v.Final_Channel || v.Requested_Channel || 'DIRECT');
        $('#fv_payment_method').val(v.Payment_Method || 'BANK');
        $('#fv_po_ref').val(v.PO_References || '');
        $('#fv_reference').val(v.Reference || '');
        $('#fv_inv_type').val(v.Inv_Type || 'SINGLE');
        $('#fv_inv_date').val(v.Inv_Vendor_Date || '{{ date("Y-m-d") }}');
        $('#fv_due_date').val(v.Due_Date || '{{ date("Y-m-d") }}');
        $('#fv_vendor_id').val(v.Vendor_ID || '');
        $('#fv_vendor_name').val(v.Vendor_Name || '');
        $('#fv_bank_name').val(v.Bank_Name || '');
        $('#fv_bank_acc').val(v.Bank_Acc || '');
        $('#fv_bank_acc_name').val(v.Bank_Acc_Name || '');
        $('#fv_subtotal').val(v.Subtotal || 0);
        $('#fv_dp').val(v.DP || 0);
        $('#fv_delivery').val(v.Delivery_Costs || 0);
        $('#fv_mgmt').val(v.Management_Fee || 0);
        $('#fv_materai').val(v.Materai || 0);
        $('#fv_ppn').val(v.PPN || 0);
        $('#fv_pph').val(v.PPH || 0);
        $('#fv_total').val(v.Total || 0);
        $('#fv_subject').val(v.Subject_Payment || '');
        $('#fv_note').val(v.Finance_Note || '');
        $('#fv_payment_ref').val('');
        $('#fv_proof_file').val('');

        var bankSel = $('#fv_bank_acc_ref').empty().append('<option value="">-- Sesuai COA Kredit --</option>');
        finBankAccounts.forEach(function (b) {
            bankSel.append('<option value="' + escHtml(b.Acc_No) + '">' + escHtml(b.Bank_Name) + ' - ' + escHtml(b.Acc_No) + ' (' + escHtml(b.Currency) + ')</option>');
        });
        initFinSelect2(bankSel, $('#FinVoucherProcessModal'), '-- Sesuai COA Kredit --');

        $('#fvCoaLinesBody').empty();
        (d.lines || []).forEach(function (ln) {
            addFinCoaLine(ln.Line_Side, ln);
        });

        $('#fvTenorBody').empty();
        (d.tenors || []).forEach(function (t) {
            addFinTenorRow(t.Tenor_Label, t.Due_Date, t.Amount, t.Note);
        });

        onFinChannelChange(true);
        recalcFinBalance();
        recalcFinTenors();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('FinVoucherProcessModal')).show();
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat data voucher.');
    });
}

function onFinChannelChange(skipSuggest) {
    var reqCh = $('#fv_requested_channel').val();
    var finCh = $('#fv_final_channel').val();
    $('#fv_req_badge').html('Usulan Awal: <strong>' + escHtml(reqCh) + '</strong>');
    $('#fv_convert_badge').toggleClass('d-none', !(reqCh === 'DIRECT' && finCh === 'AP'));
    $('#fv_pay_method_wrap').toggle(finCh === 'DIRECT');
    $('#fv_direct_extra').toggle(finCh === 'DIRECT');
    $('#fv_tenor_section').toggleClass('d-none', finCh !== 'AP');
    if (!skipSuggest) {
        recalcFinTenors();
    }
}

function recalcFinVoucherTotal() {
    var sub = parseFloat($('#fv_subtotal').val() || '0');
    var dp = parseFloat($('#fv_dp').val() || '0');
    var deliv = parseFloat($('#fv_delivery').val() || '0');
    var mgmt = parseFloat($('#fv_mgmt').val() || '0');
    var mat = parseFloat($('#fv_materai').val() || '0');
    var ppn = parseFloat($('#fv_ppn').val() || '0');
    var pph = parseFloat($('#fv_pph').val() || '0');
    $('#fv_total').val(Math.max(0, sub + dp + deliv + mgmt + mat + ppn - pph).toFixed(2));
    recalcFinTenors();
}

function suggestFinCoaLines() {
    $.ajax({
        url: 'finance-hr-voucher-suggest-lines',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: {
            source_module: $('#fv_source_module').val(),
            channel: $('#fv_final_channel').val(),
            payment_method: $('#fv_payment_method').val(),
            amounts: {
                Subtotal: $('#fv_subtotal').val(),
                DP: $('#fv_dp').val(),
                Delivery_Costs: $('#fv_delivery').val(),
                Management_Fee: $('#fv_mgmt').val(),
                Materai: $('#fv_materai').val(),
                PPN: $('#fv_ppn').val(),
                PPH: $('#fv_pph').val(),
                Total: $('#fv_total').val()
            }
        },
        success: function (res) {
            $('#fvCoaLinesBody').empty();
            (res.data || []).forEach(function (ln) {
                addFinCoaLine(ln.Line_Side, ln);
            });
            recalcFinBalance();
        }
    });
}

function finCoaSelect2Matcher(params, data) {
    if ($.trim(params.term) === '') return data;
    if (!data.text) return null;
    var terms = params.term.toLowerCase().split(/\s+/);
    var haystack = data.text.toLowerCase();
    for (var i = 0; i < terms.length; i++) {
        if (terms[i] && haystack.indexOf(terms[i]) === -1) return null;
    }
    return data;
}

function initFinSelect2($el, $modalParent, placeholderText) {
    if (!$.fn.select2) return;
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }
    $el.select2({
        width: '100%',
        allowClear: true,
        placeholder: placeholderText || '-- Cari / Pilih --',
        dropdownParent: $modalParent && $modalParent.length ? $modalParent : $(document.body),
        matcher: finCoaSelect2Matcher
    });
}

function loadCoaDropdownForSelect($coaSelect, headerNo, selectedAccNo, $modalParent) {
    var cacheKey = headerNo || 'ALL';
    var renderOpts = function (list) {
        $coaSelect.empty().append('<option value="">-- Ketik Nama / Kode COA --</option>');
        var foundSelected = false;
        list.forEach(function (c) {
            var sel = (selectedAccNo && String(c.Acc_No) === String(selectedAccNo)) ? 'selected' : '';
            if (sel) foundSelected = true;
            var tbLabel = c.Trial_Balance_Header_Name ? (' (' + c.Trial_Balance_Header_Name + ')') : '';
            $coaSelect.append('<option value="' + escHtml(c.Acc_No) + '" data-tb="' + escHtml(c.Trial_Balance_Header_No || '') + '" ' + sel + '>' + escHtml(c.Acc_Name) + ' [' + escHtml(c.Acc_No) + ']' + escHtml(tbLabel) + '</option>');
        });
        if (selectedAccNo && !foundSelected) {
            $coaSelect.append('<option value="' + escHtml(selectedAccNo) + '" selected>' + escHtml(selectedAccNo) + '</option>');
        }
        initFinSelect2($coaSelect, $modalParent, 'Ketik nama / kode COA...');
    };
    if (finCoaCacheByHeader[cacheKey]) {
        renderOpts(finCoaCacheByHeader[cacheKey]);
        return;
    }
    $.get('finance-hr-voucher-coa-options', { header_no: headerNo || '', limit: 2000 }, function (res) {
        finCoaCacheByHeader[cacheKey] = res.data || [];
        renderOpts(finCoaCacheByHeader[cacheKey]);
    });
}

function applyFinCoaRowSideStyle($tr, side) {
    var isDebet = (side === 'DEBET');
    $tr.removeClass('fv-row-debet fv-row-credit').addClass(isDebet ? 'fv-row-debet' : 'fv-row-credit');
    $tr.find('.fv-ln-side').val(side);

    $tr.find('.fv-side-btn-debet').toggleClass('active-debet', isDebet);
    $tr.find('.fv-side-btn-credit').toggleClass('active-credit', !isDebet);

    $tr.find('.fv-amt-box-debet')
        .removeClass('is-active-debet is-inactive')
        .addClass(isDebet ? 'is-active-debet' : 'is-inactive');
    $tr.find('.fv-amt-box-credit')
        .removeClass('is-active-credit is-inactive')
        .addClass(!isDebet ? 'is-active-credit' : 'is-inactive');
}

function setFinCoaRowAmount($tr, amount) {
    var side = $tr.find('.fv-ln-side').val() || 'DEBET';
    var num = parseFloat(amount || '0');
    var formatted = (isNaN(num) || num <= 0) ? '0' : Number(num.toFixed(2)).toString();
    $tr.find('.fv-ln-amt').val(isNaN(num) ? 0 : num);
    if (side === 'DEBET') {
        $tr.find('.fv-ln-amt-db').val(formatted);
        $tr.find('.fv-ln-amt-cr').val('');
    } else {
        $tr.find('.fv-ln-amt-cr').val(formatted);
        $tr.find('.fv-ln-amt-db').val('');
    }
}

function switchFinCoaRowSide($tr, targetSide, preserveAmount) {
    var currentSide = $tr.find('.fv-ln-side').val() || 'DEBET';
    if (currentSide === targetSide) return;

    var curVal = parseFloat($tr.find('.fv-ln-amt').val() || '0');
    applyFinCoaRowSideStyle($tr, targetSide);

    if (preserveAmount) {
        setFinCoaRowAmount($tr, curVal);
    } else {
        var typedRaw = targetSide === 'DEBET'
            ? $tr.find('.fv-ln-amt-db').val()
            : $tr.find('.fv-ln-amt-cr').val();
        var typedNum = parseFloat(typedRaw || '0');
        $tr.find('.fv-ln-amt').val(isNaN(typedNum) ? 0 : typedNum);
        if (targetSide === 'DEBET') {
            $tr.find('.fv-ln-amt-cr').val('');
        } else {
            $tr.find('.fv-ln-amt-db').val('');
        }
    }
    recalcFinBalance();
}

function addFinCoaLine(side, data) {
    data = data || {};
    var normalizedSide = (String(side || 'DEBET').toUpperCase() === 'CREDIT') ? 'CREDIT' : 'DEBET';
    var tbOpts = '<option value="">-- Semua Kelompok TB --</option>';
    finTbGroups.forEach(function (g) {
        var sel = (data.Trial_Balance_Header_No && String(g.Header_No) === String(data.Trial_Balance_Header_No)) ? 'selected' : '';
        tbOpts += '<option value="' + escHtml(g.Header_No) + '" ' + sel + '>[' + escHtml(g.Header_No) + '] ' + escHtml(g.Header_Name) + '</option>';
    });

    var initAmt = Number(data.Amount || 0);
    var dbInitVal = normalizedSide === 'DEBET' ? initAmt : '';
    var crInitVal = normalizedSide === 'CREDIT' ? initAmt : '';

    var $tr = $('<tr>'
        + '<td>'
            + '<input type="hidden" class="fv-ln-side" value="' + normalizedSide + '" />'
            + '<input type="hidden" class="fv-ln-amt" value="' + initAmt + '" />'
            + '<div class="fv-side-switch" role="group" aria-label="Posisi Akun">'
                + '<button type="button" class="fv-side-btn fv-side-btn-debet" title="Posisi DEBET">DEBET</button>'
                + '<button type="button" class="fv-side-btn fv-side-btn-credit" title="Posisi KREDIT">KREDIT</button>'
            + '</div>'
        + '</td>'
        + '<td><select class="form-select form-select-sm fv-ln-tb">' + tbOpts + '</select></td>'
        + '<td><select class="form-select form-select-sm fv-ln-acc"></select></td>'
        + '<td>'
            + '<div class="input-group input-group-sm fv-amt-box fv-amt-box-debet">'
                + '<span class="input-group-text">DR</span>'
                + '<input type="number" step="0.01" min="0" class="form-control text-end fv-ln-amt-db" value="' + dbInitVal + '" placeholder="— Debet" />'
            + '</div>'
        + '</td>'
        + '<td>'
            + '<div class="input-group input-group-sm fv-amt-box fv-amt-box-credit">'
                + '<span class="input-group-text">CR</span>'
                + '<input type="number" step="0.01" min="0" class="form-control text-end fv-ln-amt-cr" value="' + crInitVal + '" placeholder="— Kredit" />'
            + '</div>'
        + '</td>'
        + '<td><input type="text" class="form-control form-control-sm fv-ln-dept" value="' + escHtml(data.Department || '') + '" /></td>'
        + '<td><input type="text" class="form-control form-control-sm fv-ln-rem" value="' + escHtml(data.Remark || '') + '" /><input type="hidden" class="fv-ln-tag" value="' + escHtml(data.Component_Tag || 'OTHER') + '" /></td>'
        + '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger" title="Hapus baris" onclick="$(this).closest(\'tr\').remove(); recalcFinBalance();">&times;</button></td>'
        + '</tr>');

    $tr.find('.fv-side-btn-debet').on('click', function () {
        switchFinCoaRowSide($tr, 'DEBET', true);
    });
    $tr.find('.fv-side-btn-credit').on('click', function () {
        switchFinCoaRowSide($tr, 'CREDIT', true);
    });
    $tr.find('.fv-ln-amt-db').on('input', function () {
        var v = $(this).val();
        if ($tr.find('.fv-ln-side').val() !== 'DEBET') {
            if (v === '') return;
            switchFinCoaRowSide($tr, 'DEBET', false);
        } else {
            $tr.find('.fv-ln-amt').val(parseFloat(v || '0') || 0);
            recalcFinBalance();
        }
    });
    $tr.find('.fv-ln-amt-cr').on('input', function () {
        var v = $(this).val();
        if ($tr.find('.fv-ln-side').val() !== 'CREDIT') {
            if (v === '') return;
            switchFinCoaRowSide($tr, 'CREDIT', false);
        } else {
            $tr.find('.fv-ln-amt').val(parseFloat(v || '0') || 0);
            recalcFinBalance();
        }
    });

    var $modalParent = $('#FinVoucherProcessModal');
    var $tbSel = $tr.find('.fv-ln-tb');
    var $coaSel = $tr.find('.fv-ln-acc');
    var syncingTb = false;

    $tbSel.on('change', function () {
        if (syncingTb) return;
        loadCoaDropdownForSelect($coaSel, $(this).val(), '', $modalParent);
    });

    $coaSel.on('change', function () {
        var tbNo = $(this).find('option:selected').attr('data-tb') || '';
        if (tbNo && String($tbSel.val() || '') !== String(tbNo)) {
            syncingTb = true;
            $tbSel.val(tbNo).trigger('change.select2');
            syncingTb = false;
        }
    });

    $tr.data('setAccount', function (tbNo, accNo) {
        syncingTb = true;
        $tbSel.val(tbNo || '').trigger('change.select2');
        syncingTb = false;
        loadCoaDropdownForSelect($coaSel, tbNo || '', accNo || '', $modalParent);
    });

    applyFinCoaRowSideStyle($tr, normalizedSide);
    $('#fvCoaLinesBody').append($tr);
    initFinSelect2($tbSel, $modalParent, 'Semua Kelompok TB...');
    loadCoaDropdownForSelect($coaSel, data.Trial_Balance_Header_No || '', data.Acc_No || '', $modalParent);
    recalcFinBalance();
}

function recalcFinBalance() {
    var deb = 0, cr = 0;
    $('#fvCoaLinesBody tr').each(function () {
        var s = $(this).find('.fv-ln-side').val();
        var a = parseFloat($(this).find('.fv-ln-amt').val() || '0');
        if (s === 'DEBET') deb += a;
        else cr += a;
    });
    $('#fvSumDebet').text(fmtRp(deb));
    $('#fvSumCredit').text(fmtRp(cr));
    var diff = Math.abs(deb - cr);
    var $b = $('#fvBalanceBadge');
    var $diffTxt = $('#fvBalanceDiffText');
    if (deb === 0 && cr === 0) {
        $b.removeClass('bg-success bg-danger').addClass('bg-secondary').text('Belum Diisi');
        $diffTxt.removeClass('text-success text-danger fw-semibold').addClass('text-muted').text('Debet harus = Kredit');
    } else if (deb > 0 && diff < 0.01) {
        $b.removeClass('bg-secondary bg-danger').addClass('bg-success').text('SEIMBANG');
        $diffTxt.removeClass('text-muted text-danger').addClass('text-success fw-semibold').html('<i class="fa fa-check-circle me-1"></i> Jurnal Seimbang');
    } else {
        $b.removeClass('bg-secondary bg-success').addClass('bg-danger').text('Selisih: ' + fmtRp(diff));
        var hintSide = deb > cr ? 'Kurang Kredit: ' : 'Kurang Debet: ';
        $diffTxt.removeClass('text-muted text-success').addClass('text-danger fw-semibold').text(hintSide + fmtRp(diff));
    }
}

function autoBalanceFinCredit() {
    var deb = 0, otherCr = 0;
    var $mainCreditRow = null;
    $('#fvCoaLinesBody tr').each(function () {
        var s = $(this).find('.fv-ln-side').val();
        var a = parseFloat($(this).find('.fv-ln-amt').val() || '0');
        if (s === 'DEBET') {
            deb += a;
        } else {
            if (!$mainCreditRow) {
                $mainCreditRow = $(this);
            } else {
                otherCr += a;
            }
        }
    });
    if (deb <= 0) {
        var tot = parseFloat($('#fv_total').val() || '0');
        if (tot > 0) {
            var $firstDeb = $('#fvCoaLinesBody tr').filter(function () { return $(this).find('.fv-ln-side').val() === 'DEBET'; }).first();
            if ($firstDeb.length) {
                setFinCoaRowAmount($firstDeb, tot);
                deb = tot;
            }
        }
    }
    if (!$mainCreditRow) {
        addFinCoaLine('CREDIT');
        $mainCreditRow = $('#fvCoaLinesBody tr').last();
    }
    setFinCoaRowAmount($mainCreditRow, Math.max(0, deb - otherCr));
    recalcFinBalance();
}

function sortFinCoaLines() {
    var $tbody = $('#fvCoaLinesBody');
    var debRows = [];
    var crRows = [];
    $tbody.children('tr').each(function () {
        if ($(this).find('.fv-ln-side').val() === 'DEBET') {
            debRows.push(this);
        } else {
            crRows.push(this);
        }
    });
    debRows.concat(crRows).forEach(function (tr) {
        $tbody.append(tr);
    });
    recalcFinBalance();
}

function toggleFinCoaGuide(forceState) {
    var $panel = $('#fvCoaGuidePanel');
    var willOpen = (typeof forceState === 'boolean') ? forceState : !$panel.is(':visible');
    if (willOpen) {
        populateFinCoaGuideTbFilter();
        renderFinCoaGuideBankList();
        $panel.stop(true, true).slideDown(150);
    } else {
        $panel.stop(true, true).slideUp(130);
    }
}

function switchFinCoaGuideTab(tab) {
    var isMap = (tab === 'tbmap');
    var isDef = (tab === 'defaults');
    var isCat = (tab === 'catalog');
    $('#fvGuideTabTbMap').toggle(isMap);
    $('#fvGuideTabDefaults').toggle(isDef);
    $('#fvGuideTabCatalog').toggle(isCat);

    $('#fvGuideBtnMap').toggleClass('btn-primary', isMap).toggleClass('btn-outline-primary', !isMap);
    $('#fvGuideBtnDefaults').toggleClass('btn-primary', isDef).toggleClass('btn-outline-primary', !isDef);
    $('#fvGuideBtnCatalog').toggleClass('btn-primary', isCat).toggleClass('btn-outline-primary', !isCat);

    if (isDef) {
        loadFinCoaGuideDefaults();
    } else if (isCat) {
        ensureFinCoaCatalogAll(function () {
            populateFinCoaGuideTbFilter();
            renderFinCoaGuideList();
            setTimeout(function () { $('#fvCoaGuideSearch').focus(); }, 60);
        });
    }
}

function renderFinCoaGuideBankList() {
    var $box = $('#fvGuideBankQuickList');
    if (!$box.length) return;
    if (!finBankAccounts.length) {
        $box.html('<span class="text-muted">Tidak ada rekening bank internal aktif.</span>');
        return;
    }
    var html = '';
    finBankAccounts.forEach(function (b) {
        var accNo = escHtml(b.Acc_No || '');
        html += '<div class="d-flex justify-content-between align-items-center border-bottom pb-1">'
            + '<div class="text-truncate me-2"><strong>' + escHtml(b.Bank_Name) + '</strong> <small class="text-muted">(' + escHtml(b.Currency || 'IDR') + ' &bull; <code>' + accNo + '</code>)</small></div>'
            + (accNo ? '<button type="button" class="btn btn-xs fv-btn-add-credit fw-semibold flex-shrink-0" onclick="quickPickFinCoaAccount(\'CREDIT\', \'' + accNo + '\', \'\');">+ Kredit</button>' : '')
            + '</div>';
    });
    $box.html(html);
}

function loadFinCoaGuideDefaults() {
    var $tbody = $('#fvGuideDefaultsBody');
    var renderRows = function (res) {
        var rows = (res && res.mappings) ? res.mappings : [];
        var srcMod = ($('#fv_source_module').val() || '').toUpperCase();
        var compMap = (res && res.component_keys) ? res.component_keys : {};
        var modMap = (res && res.source_modules) ? res.source_modules : {};

        if (!rows.length) {
            $tbody.html('<tr><td colspan="6" class="text-center text-muted py-3">Belum ada mapping default di <code>finance_coa_mapping</code>. Gunakan tombol <em>Master Default Mapping COA</em> untuk menyimpannya.</td></tr>');
            return;
        }
        var sorted = rows.slice().sort(function (a, b) {
            var aScore = (a.Source_Module === srcMod ? 0 : (a.Source_Module === 'GLOBAL' ? 1 : 2));
            var bScore = (b.Source_Module === srcMod ? 0 : (b.Source_Module === 'GLOBAL' ? 1 : 2));
            return aScore - bScore;
        });

        var html = '';
        sorted.forEach(function (m) {
            var isCurrent = (m.Source_Module === srcMod);
            var isDeb = (String(m.Default_Side || '').toUpperCase() === 'DEBET');
            var badgeStyle = isDeb ? 'background-color:#0284c7; color:#fff;' : 'background-color:#d97706; color:#fff;';
            var btnClass = isDeb ? 'fv-btn-add-debet' : 'fv-btn-add-credit';
            html += '<tr class="' + (isCurrent ? 'table-info' : '') + '">'
                + '<td><strong>' + escHtml(modMap[m.Source_Module] || m.Source_Module) + '</strong>' + (isCurrent ? ' <span class="badge bg-primary text-white">Modul Ini</span>' : '') + '</td>'
                + '<td>' + escHtml((compMap[m.Component_Key] && compMap[m.Component_Key].label) || m.Component_Key) + '</td>'
                + '<td><span class="badge" style="' + badgeStyle + '">' + escHtml(m.Default_Side) + '</span></td>'
                + '<td><code>' + escHtml(m.Acc_No) + '</code> &mdash; ' + escHtml(m.Acc_Name) + '</td>'
                + '<td><span class="text-muted">[' + escHtml(m.Trial_Balance_Header_No || '-') + ']</span> ' + escHtml(m.Trial_Balance_Header_Name || '-') + '</td>'
                + '<td class="text-end"><button type="button" class="btn btn-xs ' + btnClass + ' fw-semibold" onclick="quickPickFinCoaAccount(\'' + escHtml(m.Default_Side) + '\', \'' + escHtml(m.Acc_No) + '\', \'' + escHtml(m.Trial_Balance_Header_No || '') + '\');">+ Pakai</button></td>'
                + '</tr>';
        });
        $tbody.html(html);
    };

    if (finCoaMappingsCache) {
        renderRows(finCoaMappingsCache);
        return;
    }
    $.get('finance-hr-voucher-coa-mapping', function (res) {
        if (res && res.status === 'success') {
            finCoaMappingsCache = res;
            renderRows(res);
        }
    });
}

function ensureFinCoaCatalogAll(callback) {
    if (finCoaCacheByHeader['ALL']) {
        if (callback) callback();
        return;
    }
    $.get('finance-hr-voucher-coa-options', { header_no: '', limit: 2000 }, function (res) {
        finCoaCacheByHeader['ALL'] = (res && res.data) ? res.data : [];
        if (callback) callback();
    });
}

function populateFinCoaGuideTbFilter() {
    var $sel = $('#fvCoaGuideTbFilter');
    if (!$sel.length || $sel.data('populated')) return;
    finTbGroups.forEach(function (g) {
        $sel.append('<option value="' + escHtml(g.Header_No) + '">[' + escHtml(g.Header_No) + '] ' + escHtml(g.Header_Name) + ' (' + escHtml(g.Account_Count || 0) + ')</option>');
    });
    $sel.data('populated', true);
}

function setFinCoaGuideCat(cat) {
    finCoaGuideState.cat = cat || 'ALL';
    $('#fvCoaGuideCatPills .fv-guide-cat-btn').each(function () {
        var c = $(this).data('cat');
        var active = (c === finCoaGuideState.cat);
        $(this).toggleClass('btn-dark text-white', active && c === 'ALL')
            .toggleClass('btn-primary text-white', active && (c === 'EXPENSE' || c === 'DP_PPN'))
            .toggleClass('btn-warning text-dark fw-bold', active && (c === 'CASH_BANK' || c === 'LIABILITY'));
        if (!active) {
            $(this).removeClass('btn-dark btn-primary btn-warning text-white fw-bold');
            if (c === 'ALL') $(this).addClass('btn-outline-dark');
            else if (c === 'EXPENSE' || c === 'DP_PPN') $(this).addClass('btn-outline-primary');
            else $(this).addClass('btn-outline-warning text-dark');
        } else {
            $(this).removeClass('btn-outline-dark btn-outline-primary btn-outline-warning');
        }
    });
    renderFinCoaGuideList();
}

function openFinCoaGuideSearch(cat, keyword) {
    ensureFinCoaCatalogAll(function () {
        populateFinCoaGuideTbFilter();
        $('#fvCoaGuideTbFilter').val('');
        $('#fvCoaGuideSearch').val(keyword || '');
        switchFinCoaGuideTab('catalog');
        setFinCoaGuideCat(cat || 'ALL');
    });
}

function matchesFinCoaGuideCat(c, cat) {
    if (!cat || cat === 'ALL') return true;
    var h = String(c.Trial_Balance_Header_No || '').toUpperCase();
    var no = String(c.Acc_No || '');
    if (cat === 'EXPENSE') {
        return h.indexOf('5') === 0 || h.indexOf('6') === 0 || no.indexOf('5-') === 0 || no.indexOf('6-') === 0;
    }
    if (cat === 'DP_PPN') {
        return h === 'A11' || h === 'A12' || h === 'A19';
    }
    if (cat === 'CASH_BANK') {
        return ['A1', 'A2', 'A3', 'A4'].indexOf(h) !== -1 || no.indexOf('1-11') === 0 || no.indexOf('1-12') === 0;
    }
    if (cat === 'LIABILITY') {
        return ['L1', 'L4', 'L6', 'L7'].indexOf(h) !== -1 || no.indexOf('2-') === 0;
    }
    return true;
}

function renderFinCoaGuideList() {
    var $tbody = $('#fvCoaGuideBody');
    if (!$tbody.length) return;
    var list = finCoaCacheByHeader['ALL'] || [];
    var q = ($('#fvCoaGuideSearch').val() || '').toLowerCase().trim();
    var tbFilter = $('#fvCoaGuideTbFilter').val() || '';
    var cat = finCoaGuideState.cat || 'ALL';

    var matches = list.filter(function (c) {
        if (!matchesFinCoaGuideCat(c, cat)) return false;
        if (tbFilter && String(c.Trial_Balance_Header_No || '') !== String(tbFilter)) return false;
        if (q) {
            var hay = (String(c.Acc_No || '') + ' ' + String(c.Acc_Name || '') + ' ' + String(c.Trial_Balance_Header_Name || '') + ' ' + String(c.Trial_Balance_Header_No || '')).toLowerCase();
            var terms = q.split(/\s+/);
            for (var i = 0; i < terms.length; i++) {
                if (terms[i] && hay.indexOf(terms[i]) === -1) return false;
            }
        }
        return true;
    });

    var shown = matches.slice(0, 60);
    $('#fvCoaGuideCount').text('Menampilkan ' + shown.length + ' dari ' + matches.length + ' akun');

    if (!shown.length) {
        $tbody.html('<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada akun COA yang cocok. Coba ubah kata kunci atau pilih kategori "Semua".</td></tr>');
        return;
    }

    var html = '';
    shown.forEach(function (c) {
        var encAcc = escHtml(c.Acc_No);
        var encTb = escHtml(c.Trial_Balance_Header_No || '');
        html += '<tr>'
            + '<td class="fw-semibold text-nowrap"><code>' + encAcc + '</code></td>'
            + '<td class="fw-semibold">' + escHtml(c.Acc_Name) + '</td>'
            + '<td><span class="text-muted">[' + (encTb || '-') + ']</span> ' + escHtml(c.Trial_Balance_Header_Name || '-') + '</td>'
            + '<td class="text-end text-nowrap">'
                + '<button type="button" class="btn btn-xs fv-btn-add-debet fw-semibold me-1" onclick="quickPickFinCoaAccount(\'DEBET\', \'' + encAcc + '\', \'' + encTb + '\');">+ Debet</button>'
                + '<button type="button" class="btn btn-xs fv-btn-add-credit fw-semibold" onclick="quickPickFinCoaAccount(\'CREDIT\', \'' + encAcc + '\', \'' + encTb + '\');">+ Kredit</button>'
            + '</td>'
            + '</tr>';
    });
    $tbody.html(html);
}

function quickPickFinCoaAccount(side, accNo, tbNo) {
    if (!accNo) return;
    var resolvedTb = tbNo || '';
    if (!resolvedTb && finCoaCacheByHeader['ALL']) {
        var found = finCoaCacheByHeader['ALL'].find(function (c) { return String(c.Acc_No) === String(accNo); });
        if (found) resolvedTb = found.Trial_Balance_Header_No || '';
    }

    var $targetRow = null;
    $('#fvCoaLinesBody tr').each(function () {
        var rowSide = $(this).find('.fv-ln-side').val();
        var rowAcc = $(this).find('.fv-ln-acc').val();
        if (rowSide === side && !rowAcc && !$targetRow) {
            $targetRow = $(this);
        }
    });

    if ($targetRow && typeof $targetRow.data('setAccount') === 'function') {
        $targetRow.data('setAccount')(resolvedTb, accNo);
    } else {
        addFinCoaLine(side, {
            Trial_Balance_Header_No: resolvedTb,
            Acc_No: accNo,
            Department: finCurrentVoucherDept || ''
        });
        $targetRow = $('#fvCoaLinesBody tr').last();
    }

    recalcFinBalance();
    if ($targetRow && $targetRow.length) {
        var $amtInput = side === 'DEBET' ? $targetRow.find('.fv-ln-amt-db') : $targetRow.find('.fv-ln-amt-cr');
        $targetRow.addClass('table-active');
        setTimeout(function () { $targetRow.removeClass('table-active'); }, 900);
        $amtInput.focus();
    }
}

function addFinTenorRow(label, due, amt, note) {
    var idx = $('#fvTenorBody tr').length + 1;
    var $tr = $('<tr>'
        + '<td><input type="text" class="form-control form-control-sm fv-t-label" value="' + escHtml(label || ('Termin ' + idx)) + '" /></td>'
        + '<td><input type="date" class="form-control form-control-sm fv-t-due" value="' + escHtml(due || $('#fv_due_date').val() || '') + '" /></td>'
        + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm text-end fv-t-amt" value="' + Number(amt || 0) + '" oninput="recalcFinTenors();" /></td>'
        + '<td><input type="text" class="form-control form-control-sm fv-t-note" value="' + escHtml(note || '') + '" /></td>'
        + '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger" onclick="$(this).closest(\'tr\').remove(); recalcFinTenors();">&times;</button></td>'
        + '</tr>');
    $('#fvTenorBody').append($tr);
    recalcFinTenors();
}

function recalcFinTenors() {
    var sum = 0;
    $('#fvTenorBody tr').each(function () {
        sum += parseFloat($(this).find('.fv-t-amt').val() || '0');
    });
    var tot = parseFloat($('#fv_total').val() || '0');
    $('#fvTenorSum').text(fmtRp(sum));
    if (Math.abs(sum - tot) < 0.01) {
        $('#fvTenorMatch').html('<span class="text-success fw-semibold">Sesuai Total AP</span>');
    } else {
        $('#fvTenorMatch').html('<span class="text-danger fw-semibold">Harus sama dengan ' + fmtRp(tot) + '</span>');
    }
}

function submitFinVoucherProcess() {
    var lines = [];
    $('#fvCoaLinesBody tr').each(function () {
        var acc = $(this).find('.fv-ln-acc').val() || '';
        var amt = parseFloat($(this).find('.fv-ln-amt').val() || '0');
        if (acc || amt > 0) {
            lines.push({
                Line_Side: $(this).find('.fv-ln-side').val(),
                Trial_Balance_Header_No: $(this).find('.fv-ln-tb').val(),
                Acc_No: acc,
                Amount: amt,
                Department: $(this).find('.fv-ln-dept').val(),
                Remark: $(this).find('.fv-ln-rem').val(),
                Component_Tag: $(this).find('.fv-ln-tag').val() || 'OTHER'
            });
        }
    });

    var tenors = [];
    $('#fvTenorBody tr').each(function () {
        var amt = parseFloat($(this).find('.fv-t-amt').val() || '0');
        var due = $(this).find('.fv-t-due').val() || '';
        if (amt > 0 || due !== '') {
            tenors.push({
                Tenor_Label: $(this).find('.fv-t-label').val(),
                Due_Date: due,
                Amount: amt,
                Note: $(this).find('.fv-t-note').val()
            });
        }
    });

    var fd = new FormData(document.getElementById('FormFinVoucherProcess'));
    fd.append('Lines', JSON.stringify(lines));
    fd.append('Tenors', JSON.stringify(tenors));

    $('#btnSubmitFinVoucher').prop('disabled', true);
    $.ajax({
        url: 'finance-hr-voucher-process',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            alert(res.message);
            bootstrap.Modal.getInstance(document.getElementById('FinVoucherProcessModal')).hide();
            $('#FinanceApprovalDetail').empty();
            loadFinanceApprovalData();
        },
        error: function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memproses voucher.');
        },
        complete: function () {
            $('#btnSubmitFinVoucher').prop('disabled', false);
        }
    });
}

function rejectFromModal() {
    quickRejectCentralVoucher($('#fv_id').val(), true);
}

function quickRejectCentralVoucher(voucherId, closeModal) {
    var reason = prompt('Masukkan alasan penolakan / catatan revisi:');
    if (!reason) return;
    $.ajax({
        url: 'finance-hr-voucher-reject',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { voucher_id: voucherId, reason: reason },
        success: function (res) {
            alert(res.message);
            if (closeModal) {
                bootstrap.Modal.getInstance(document.getElementById('FinVoucherProcessModal')).hide();
            }
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menolak voucher.'); }
    });
}

function quickRejectSourceVoucher(sourceModule, sourceId) {
    var reason = prompt('Masukkan alasan penolakan / catatan revisi:');
    if (!reason) return;
    $.ajax({
        url: 'finance-hr-voucher-reject',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { source_module: sourceModule, source_id: sourceId, reason: reason },
        success: function (res) {
            alert(res.message);
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menolak voucher.'); }
    });
}

function openCoaMappingModal() {
    $.get('finance-hr-voucher-coa-mapping', function (res) {
        if (res.status !== 'success') return;
        var srcSel = $('#cm_source_module').empty();
        Object.keys(res.source_modules || {}).forEach(function (k) {
            srcSel.append('<option value="' + escHtml(k) + '">' + escHtml(res.source_modules[k]) + '</option>');
        });
        var cmpSel = $('#cm_component_key').empty();
        Object.keys(res.component_keys || {}).forEach(function (k) {
            cmpSel.append('<option value="' + escHtml(k) + '">' + escHtml(res.component_keys[k].label) + ' (' + escHtml(res.component_keys[k].side) + ')</option>');
        });
        var tbSel = $('#cm_tb_group').empty().append('<option value="">-- Semua Kelompok TB --</option>');
        (res.tb_groups || []).forEach(function (g) {
            tbSel.append('<option value="' + escHtml(g.Header_No) + '">[' + escHtml(g.Header_No) + '] ' + escHtml(g.Header_Name) + '</option>');
        });

        var tbody = $('#cmTableBody').empty();
        (res.mappings || []).forEach(function (m) {
            tbody.append('<tr>'
                + '<td>' + escHtml(m.Source_Module) + '</td>'
                + '<td>' + escHtml(m.Component_Key) + '</td>'
                + '<td>' + escHtml(m.Trial_Balance_Header_No || '') + ' - ' + escHtml(m.Trial_Balance_Header_Name || '') + '</td>'
                + '<td><strong>' + escHtml(m.Acc_No) + '</strong> ' + escHtml(m.Acc_Name || '') + '</td>'
                + '<td>' + escHtml(m.Default_Side) + '</td>'
                + '<td class="text-center"><button class="btn btn-xs btn-outline-danger" onclick="deleteCoaMapping(' + m.ID + ')">&times;</button></td>'
                + '</tr>');
        });

        initFinSelect2(tbSel, $('#FinCoaMappingModal'), 'Semua Kelompok TB...');
        loadMappingCoaOptions();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('FinCoaMappingModal')).show();
    });
}

function loadMappingCoaOptions() {
    loadCoaDropdownForSelect($('#cm_acc_no'), $('#cm_tb_group').val(), '', $('#FinCoaMappingModal'));
}

function saveCoaMapping() {
    $.ajax({
        url: 'finance-hr-voucher-coa-mapping-save',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: {
            Source_Module: $('#cm_source_module').val(),
            Component_Key: $('#cm_component_key').val(),
            Acc_No: $('#cm_acc_no').val()
        },
        success: function (res) {
            alert(res.message);
            openCoaMappingModal();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan mapping.'); }
    });
}

function deleteCoaMapping(id) {
    $.ajax({
        url: 'finance-hr-voucher-coa-mapping-delete',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { id: id },
        success: function () { openCoaMappingModal(); }
    });
}

function advanceFinApRequest(voucherId) {
    if (!confirm('Teruskan AP ini ke tahap Payment Request (RECEIVED_APPROVAL1)?')) return;
    $.ajax({
        url: 'finance-hr-voucher-ap-request',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { voucher_id: voucherId },
        success: function (res) {
            alert(res.message);
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memproses.'); }
    });
}

function openFinApPayModal(voucherId) {
    var r = (finCache.ap_vouchers || []).find(function (x) { return Number(x.ID) === Number(voucherId); });
    if (!r) return;
    $('#FormFinApPay')[0].reset();
    $('#ap_pay_voucher_id').val(r.ID);
    $('#FinApPayTitle').text('Pembayaran AP Treasury — ' + r.Voucher_No + ' (' + (r.Vendor_Name || '-') + ')');
    $('#ap_pay_total_txt').text(fmtRp(r.Total));
    $('#ap_pay_paid_txt').text(fmtRp(r.Paid));
    $('#ap_pay_remain_txt').text(fmtRp(r.Remain_Val));
    $('#ap_pay_amount').val(Number(r.Remain_Val || 0).toFixed(2));

    var tSel = $('#ap_pay_tenor_id').empty().append('<option value="">-- Bebas / FIFO (Sisa: ' + fmtRp(r.Remain_Val) + ') --</option>');
    (r.tenors || []).forEach(function (t) {
        var rem = Math.max(0, Number(t.Amount) - Number(t.Paid_Amount));
        if (rem > 0) {
            tSel.append('<option value="' + t.ID + '" data-remain="' + rem + '">' + escHtml(t.Tenor_Label) + ' (JT: ' + escHtml(t.Due_Date) + ' • Sisa: ' + fmtRp(rem) + ')</option>');
        }
    });

    $.get('finance-hr-voucher-bank-accounts', function (res) {
        var bSel = $('#ap_pay_bank_ref').empty().append('<option value="">-- Otomatis dari COA --</option>');
        (res.data || []).forEach(function (b) {
            bSel.append('<option value="' + escHtml(b.Acc_No) + '">' + escHtml(b.Bank_Name) + ' - ' + escHtml(b.Acc_No) + '</option>');
        });
        initFinSelect2(bSel, $('#FinApPayModal'), '-- Otomatis dari COA --');
    });

    reloadApPayCoaOptions();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('FinApPayModal')).show();
}

function onSelectPayTenor() {
    var rem = $('#ap_pay_tenor_id option:selected').data('remain');
    if (rem) {
        $('#ap_pay_amount').val(Number(rem).toFixed(2));
    }
}

function reloadApPayCoaOptions() {
    var ch = $('#ap_pay_channel').val();
    var header = ch === 'CASH' ? 'A1' : 'A3';
    loadCoaDropdownForSelect($('#ap_pay_acc_no'), header, '', $('#FinApPayModal'));
}

function submitFinApPay() {
    var fd = new FormData(document.getElementById('FormFinApPay'));
    $.ajax({
        url: 'finance-hr-voucher-ap-pay',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            alert(res.message);
            bootstrap.Modal.getInstance(document.getElementById('FinApPayModal')).hide();
            loadFinanceApprovalData();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mencatat pembayaran AP.'); }
    });
}

loadFinanceApprovalData();
</script>
@endsection
