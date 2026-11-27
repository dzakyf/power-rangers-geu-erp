@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="breadcrumb-header justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="content-title mb-1">Pengajuan Voucher &amp; Hutang Dagang (AP)</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Operasional</a></li>
                <li class="breadcrumb-item active" aria-current="page">Voucher &amp; AP Manual</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadHrVouchers();">
            <i class="fa fa-sync me-1"></i> Refresh
        </button>
        @if(!empty($canCreate))
            <button type="button" class="btn btn-warning btn-sm text-dark fw-semibold" onclick="openHrVoucherForm('DIRECT');">
                <i class="fa fa-plus me-1"></i> Ajukan Voucher Langsung
            </button>
            <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="openHrVoucherForm('AP');">
                <i class="fa fa-plus me-1"></i> Ajukan AP (Hutang Dagang)
            </button>
        @endif
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Total Pengajuan</div>
                <div class="fs-3 fw-bold text-primary" id="hrKpiTotal">0</div>
                <div class="small text-muted">Approval Atasan: <strong id="hrKpiWaiting">0</strong> &bull; Finance: <strong id="hrKpiSubmitted">0</strong></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Dikembalikan (Perlu Revisi)</div>
                <div class="fs-3 fw-bold text-danger" id="hrKpiReturned">0</div>
                <div class="small text-muted">Ditolak Finance / Revisi</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">AP Aktif / Berjalan</div>
                <div class="fs-3 fw-bold text-warning" id="hrKpiApActive">0</div>
                <div class="small text-muted">Sisa Hutang: <strong id="hrKpiRemain">Rp 0</strong></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Selesai / Lunas</div>
                <div class="fs-3 fw-bold text-success" id="hrKpiCompleted">0</div>
                <div class="small text-muted">Total Nilai: <strong id="hrKpiAmount">Rp 0</strong></div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-2 mb-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" id="HrVoucherSearch" class="form-control" placeholder="Cari No Voucher, PO, Vendor, No Faktur, atau Perihal..." oninput="renderHrVoucherTable();" />
                </div>
            </div>
            <div class="col-md-3">
                <select id="HrVoucherStatusFilter" class="form-select form-select-sm" onchange="loadHrVouchers();">
                    <option value="ALL">Semua Status</option>
                    <option value="WAITING_APPROVAL">Menunggu Approval Atasan (WAITING_APPROVAL)</option>
                    <option value="SUBMITTED">Menunggu Verifikasi Finance (SUBMITTED)</option>
                    <option value="PROPOSED">AP Masuk Receive (PROPOSED)</option>
                    <option value="RETURNED">Perlu Revisi (RETURNED)</option>
                    <option value="RECEIVED_FINANCE">AP Diterima Finance</option>
                    <option value="RECEIVED_APPROVAL1">AP Payment Request</option>
                    <option value="PARTIAL">Dibayar Sebagian (PARTIAL)</option>
                    <option value="PAID">Lunas Direct (PAID)</option>
                    <option value="CLOSED">Lunas AP (CLOSED)</option>
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">Keputusan akhir AP vs Cash/Bank &amp; verifikasi COA diproses oleh Finance.</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Voucher</th>
                        <th>Usulan &rarr; Final</th>
                        <th>Ref. PO / Faktur</th>
                        <th>Vendor &amp; Dept</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Sisa</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="HrVoucherBody">
                    <tr><td colspan="9" class="text-center text-muted py-3">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Workspace Bawah Tabel (Mutually Exclusive: Tampil Form Pengajuan ATAU Detail Voucher) -->
<div id="HrVoucherWorkspace" class="mt-3">
    @include('humanresources.voucher.partials.create-form')
    <div id="HrVoucherDetailBox"></div>
</div>

<script>
var hrVoucherRows = [];
var hrCoaCatalog = { groups: [], accounts: [], loaded: false };
var hrFormDirty = false;

function escHr(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function fmtRpHr(val) {
    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
}

function statusBadgeHr(st) {
    var s = String(st || '').toUpperCase();
    if (s === 'PAID' || s === 'CLOSED' || s === 'APPROVED') return '<span class="badge bg-success text-white">' + escHr(s) + '</span>';
    if (s === 'FORCE_APPROVED') return '<span class="badge bg-dark text-white">FORCE APPROVED</span>';
    if (s === 'SKIPPED_FORCE') return '<span class="badge bg-warning text-dark">Dilewati (Force Approve)</span>';
    if (s === 'SKIPPED' || s === 'AUTO_APPROVED') return '<span class="badge bg-info text-white">' + (s === 'SKIPPED' ? 'Dilewati Sistem' : 'AUTO APPROVED') + '</span>';
    if (s === 'UPCOMING') return '<span class="badge bg-light text-dark border">Menunggu Giliran</span>';
    if (s === 'NOT_REACHED') return '<span class="badge bg-secondary text-white">Tidak Tercapai</span>';
    if (s === 'WAITING_APPROVAL' || s === 'PENDING' || s === 'CLAIMED') return '<span class="badge bg-warning text-dark">' + (s === 'CLAIMED' ? 'Sedang Diproses' : (s === 'PENDING' ? 'Menunggu Approval' : 'Menunggu Approval Atasan')) + '</span>';
    if (s === 'HELD') return '<span class="badge bg-danger text-white">Ditahan (Vacant)</span>';
    if (s === 'RETURNED' || s === 'REJECTED') return '<span class="badge bg-danger text-white">' + escHr(s) + '</span>';
    if (s === 'PARTIAL') return '<span class="badge bg-warning text-dark">PARTIAL</span>';
    if (s === 'PROPOSED' || s === 'RECEIVED_FINANCE' || s === 'RECEIVED_APPROVAL1') return '<span class="badge bg-info text-white">' + escHr(s) + '</span>';
    if (s === 'CANCELLED' || s === 'CANCELED') return '<span class="badge bg-secondary text-white">' + escHr(s) + '</span>';
    return '<span class="badge bg-primary text-white">' + escHr(s) + '</span>';
}

function hrCoaSelect2Matcher(params, data) {
    if ($.trim(params.term) === '') return data;
    if (!data.text) return null;
    var terms = params.term.toLowerCase().split(/\s+/);
    var haystack = data.text.toLowerCase();
    for (var i = 0; i < terms.length; i++) {
        if (terms[i] && haystack.indexOf(terms[i]) === -1) return null;
    }
    return data;
}

function initHrSelect2($el, placeholderText) {
    if (!$.fn.select2) return;
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }
    $el.select2({
        width: '100%',
        allowClear: true,
        placeholder: placeholderText || '-- Cari / Pilih --',
        matcher: hrCoaSelect2Matcher
    });
}

function ensureHrCoaCatalog(callback) {
    if (hrCoaCatalog.loaded) {
        if (callback) callback();
        return;
    }
    $.get('hrd-voucher-coa-catalog', function (res) {
        if (res.status === 'success' && res.data) {
            hrCoaCatalog.groups = res.data.groups || [];
            hrCoaCatalog.accounts = res.data.accounts || [];
            hrCoaCatalog.loaded = true;
        }
        if (callback) callback();
    });
}

function openHrVoucherForm(defaultChannel) {
    ensureHrCoaCatalog(function () {
        $('#HrVoucherDetailBox').empty().hide();
        $('#FormHrVoucherCreate')[0].reset();
        $('#hr_v_id').val('');
        $('#hr_v_po_ref').val('');
        $('#hr_v_pr_no').val('');
        $('#hr_po_selected_badge').text('Belum memilih PO (Pengajuan Manual Non-PO).');
        $('#hr_po_results, #hr_sup_results').hide().empty();
        $('#hrTenorBody').empty();
        $('#hrCoaLinesBody').empty();
        $('#hrCoaGuidePanel').hide();
        $('#hr_v_channel').val(defaultChannel || 'AP');
        $('#HrVoucherFormTitle').text(defaultChannel === 'DIRECT' ? 'Form Pengajuan Voucher Pembayaran Langsung' : 'Form Pengajuan AP (Hutang Dagang) Manual');
        onHrChannelChange();
        updateHrCoaGuideContext();
        addHrCoaRow('DEBET');
        addHrCoaRow('CREDIT');
        hrFormDirty = false;
        $('#HrVoucherFormCard').stop(true, true).slideDown(180);
        document.getElementById('HrVoucherWorkspace').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

function closeHrVoucherForm() {
    if (hrFormDirty && !confirm('Tutup form pengajuan? Data yang belum dikirim akan hilang.')) {
        return;
    }
    hrFormDirty = false;
    $('#HrVoucherFormCard').slideUp(150);
}

function openHrVoucherEditForm(id) {
    ensureHrCoaCatalog(function () {
        $.get('hrd-voucher-detail', { id: id }, function (res) {
            if (res.status !== 'success') return;
            var v = res.data.voucher;
            $('#HrVoucherDetailBox').empty().hide();
            $('#FormHrVoucherCreate')[0].reset();
            $('#hr_v_id').val(v.ID);
            $('#HrVoucherFormTitle').text('Revisi & Kirim Ulang: ' + v.Voucher_No);
            $('#hr_v_channel').val(v.Requested_Channel || 'AP');
            $('#hr_v_category').val(v.Expense_Category || 'OPERASIONAL');
            $('#hr_v_pay_method').val(v.Payment_Method || 'BANK');
            $('#hr_v_po_ref').val(v.PO_References || '');
            $('#hr_v_pr_no').val(v.PR_No || '');
            $('#hr_po_selected_badge').text(v.PO_References ? ('PO Terpilih: ' + v.PO_References) : 'Belum memilih PO (Pengajuan Manual Non-PO).');
            $('#hr_v_vendor_id').val(v.Vendor_ID || '');
            $('#hr_v_vendor_name').val(v.Vendor_Name || '');
            $('#hr_v_inv_type').val(v.Inv_Type || 'SINGLE');
            $('#hr_v_reference').val(v.Reference || '');
            $('#hr_v_inv_date').val(v.Inv_Vendor_Date || '');
            $('#hr_v_recv_date').val(v.Date_Received || '');
            $('#hr_v_due_date').val(v.Due_Date || '');
            $('#hr_v_bank_type').val(v.Bank_Type || 'DIRECT TRANSFER');
            $('#hr_v_bank_name').val(v.Bank_Name || '');
            $('#hr_v_bank_acc').val(v.Bank_Acc || '');
            $('#hr_v_bank_acc_name').val(v.Bank_Acc_Name || '');
            $('#hr_v_subtotal').val(v.Subtotal || 0);
            $('#hr_v_dp').val(v.DP || 0);
            $('#hr_v_delivery').val(v.Delivery_Costs || 0);
            $('#hr_v_mgmt').val(v.Management_Fee || 0);
            $('#hr_v_materai').val(v.Materai || 0);
            $('#hr_v_ppn_type').val(v.PPN_Type || '0');
            $('#hr_v_ppn').val(v.PPN || 0);
            $('#hr_v_pph_type').val(v.PPH_Type || '0');
            $('#hr_v_pph').val(v.PPH || 0);
            $('#hr_v_total').val(v.Total || 0);
            $('#hr_v_dept').val(v.Department || '');
            $('#hr_v_subject').val(v.Subject_Payment || '');
            $('#hr_v_desc').val(v.Description || '');

            $('#hrCoaLinesBody').empty();
            (res.data.lines || []).forEach(function (ln) {
                addHrCoaRow(ln.Line_Side, ln);
            });
            if (!$('#hrCoaLinesBody tr').length) {
                addHrCoaRow('DEBET');
                addHrCoaRow('CREDIT');
            }

            $('#hrTenorBody').empty();
            (res.data.tenors || []).forEach(function (t) {
                addHrTenorRow(t.Tenor_Label, t.Due_Date, t.Amount, t.Note);
            });

            onHrChannelChange();
            $('#hrCoaGuidePanel').hide();
            updateHrCoaGuideContext();
            recalcHrCoaBalance();
            hrFormDirty = false;
            $('#HrVoucherFormCard').stop(true, true).slideDown(180);
            document.getElementById('HrVoucherWorkspace').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
}

function onHrChannelChange() {
    var ch = $('#hr_v_channel').val();
    $('#hrTenorWrapper').toggle(ch === 'AP');
    $('#hr_pay_method_wrap').toggle(ch === 'DIRECT');
    updateHrCoaGuideContext();
}

var hrCoaGuideState = { cat: 'ALL' };

function toggleHrCoaGuide(forceState) {
    var $panel = $('#hrCoaGuidePanel');
    var willOpen = (typeof forceState === 'boolean') ? forceState : !$panel.is(':visible');
    if (willOpen) {
        ensureHrCoaCatalog(function () {
            populateHrCoaGuideTbFilter();
            updateHrCoaGuideContext();
            renderHrCoaGuideList();
            $panel.stop(true, true).slideDown(150);
        });
    } else {
        $panel.stop(true, true).slideUp(130);
    }
}

function switchHrCoaGuideTab(tab) {
    var isScen = (tab === 'scenarios');
    $('#hrGuideTabScenarios').toggle(isScen);
    $('#hrGuideTabCatalog').toggle(!isScen);
    $('#hrGuideBtnScenarios').toggleClass('btn-primary', isScen).toggleClass('btn-outline-primary', !isScen);
    $('#hrGuideBtnCatalog').toggleClass('btn-primary', !isScen).toggleClass('btn-outline-primary', isScen);
    if (!isScen) {
        ensureHrCoaCatalog(function () {
            populateHrCoaGuideTbFilter();
            renderHrCoaGuideList();
            setTimeout(function () { $('#hrCoaGuideSearch').focus(); }, 60);
        });
    }
}

function updateHrCoaGuideContext() {
    var $hint = $('#hrGuideContextHint');
    if (!$hint.length) return;
    var catText = $('#hr_v_category option:selected').text() || 'Operasional Umum';
    var ch = $('#hr_v_channel').val() || 'AP';
    var pm = $('#hr_v_pay_method').val() || 'BANK';
    var creditHint = ch === 'AP'
        ? '<strong>Hutang Dagang / Usaha (Kelompok L1)</strong>'
        : (pm === 'CASH' ? '<strong>Kas / Petty Cash (Kelompok A1–A2)</strong>' : '<strong>Bank Keluar (Kelompok A3–A4)</strong>');
    $hint.html('<i class="fa fa-compass me-1"></i> <strong>Rekomendasi Pengajuan Saat Ini</strong> &mdash; Kategori: <strong>' + escHr(catText) + '</strong> &bull; Jalur: <strong>' + escHr(ch) + '</strong><br>'
        + '<span class="ms-3">Sisi <span class="badge hr-badge-debet">DEBET</span>: Pilih akun biaya sesuai kategori <em>' + escHr(catText) + '</em> (+ PPN Masukan / DP jika ada). &nbsp;|&nbsp; '
        + 'Sisi <span class="badge hr-badge-credit">KREDIT</span>: ' + creditHint + ' (+ Hutang PPh <code>L7</code> jika ada potongan PPh).</span>');
}

function populateHrCoaGuideTbFilter() {
    var $sel = $('#hrCoaGuideTbFilter');
    if (!$sel.length || $sel.data('populated')) return;
    hrCoaCatalog.groups.forEach(function (g) {
        $sel.append('<option value="' + escHr(g.Header_No) + '">[' + escHr(g.Header_No) + '] ' + escHr(g.Header_Name) + ' (' + escHr(g.Account_Count || 0) + ')</option>');
    });
    $sel.data('populated', true);
}

function setHrCoaGuideCat(cat) {
    hrCoaGuideState.cat = cat || 'ALL';
    $('#hrCoaGuideCatPills .hr-guide-cat-btn').each(function () {
        var c = $(this).data('cat');
        var active = (c === hrCoaGuideState.cat);
        $(this).toggleClass('btn-dark text-white', active && c === 'ALL')
            .toggleClass('btn-primary text-white', active && c === 'EXPENSE')
            .toggleClass('btn-warning text-dark fw-bold', active && (c === 'CASH_BANK' || c === 'LIABILITY_AP'))
            .toggleClass('btn-secondary text-white', active && c === 'OTHER');
        if (!active) {
            $(this).removeClass('btn-dark btn-primary btn-warning btn-secondary text-white fw-bold');
            if (c === 'ALL') $(this).addClass('btn-outline-dark');
            else if (c === 'EXPENSE') $(this).addClass('btn-outline-primary');
            else if (c === 'CASH_BANK' || c === 'LIABILITY_AP') $(this).addClass('btn-outline-warning text-dark');
            else $(this).addClass('btn-outline-secondary');
        } else {
            $(this).removeClass('btn-outline-dark btn-outline-primary btn-outline-warning btn-outline-secondary');
        }
    });
    renderHrCoaGuideList();
}

function openHrCoaGuideSearch(cat, keyword) {
    ensureHrCoaCatalog(function () {
        populateHrCoaGuideTbFilter();
        $('#hrCoaGuideTbFilter').val('');
        $('#hrCoaGuideSearch').val(keyword || '');
        switchHrCoaGuideTab('catalog');
        setHrCoaGuideCat(cat || 'ALL');
    });
}

function hrCoaCatBadgeHtml(tag) {
    if (tag === 'EXPENSE') return '<span class="badge hr-badge-debet">Biaya (Debet)</span>';
    if (tag === 'CASH_BANK') return '<span class="badge hr-badge-credit">Kas / Bank</span>';
    if (tag === 'LIABILITY_AP') return '<span class="badge hr-badge-credit">Hutang / Pajak</span>';
    return '<span class="badge bg-secondary text-white">DP / PPN / Lain</span>';
}

function renderHrCoaGuideList() {
    var $tbody = $('#hrCoaGuideBody');
    if (!$tbody.length) return;
    var q = ($('#hrCoaGuideSearch').val() || '').toLowerCase().trim();
    var tbFilter = $('#hrCoaGuideTbFilter').val() || '';
    var cat = hrCoaGuideState.cat || 'ALL';

    var matches = (hrCoaCatalog.accounts || []).filter(function (a) {
        if (cat !== 'ALL' && a.Category_Tag !== cat) return false;
        if (tbFilter && String(a.Header_No || '') !== String(tbFilter)) return false;
        if (q) {
            var hay = (String(a.Acc_No || '') + ' ' + String(a.Acc_Name || '') + ' ' + String(a.Header_Name || '') + ' ' + String(a.Header_No || '')).toLowerCase();
            var terms = q.split(/\s+/);
            for (var i = 0; i < terms.length; i++) {
                if (terms[i] && hay.indexOf(terms[i]) === -1) return false;
            }
        }
        return true;
    });

    var limit = 60;
    var shown = matches.slice(0, limit);
    $('#hrCoaGuideCount').text('Menampilkan ' + shown.length + ' dari ' + matches.length + ' akun');

    if (!shown.length) {
        $tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada akun COA yang cocok dengan filter. Coba kata kunci lain atau pilih kategori "Semua".</td></tr>');
        return;
    }

    var html = '';
    shown.forEach(function (a) {
        var encAcc = escHr(a.Acc_No);
        html += '<tr>'
            + '<td class="fw-semibold text-nowrap"><code>' + encAcc + '</code></td>'
            + '<td class="fw-semibold">' + escHr(a.Acc_Name) + '</td>'
            + '<td><span class="text-muted">[' + escHr(a.Header_No || '-') + ']</span> ' + escHr(a.Header_Name || '-') + '</td>'
            + '<td>' + hrCoaCatBadgeHtml(a.Category_Tag) + '</td>'
            + '<td class="text-end text-nowrap">'
                + '<button type="button" class="btn btn-xs hr-btn-add-debet fw-semibold me-1" onclick="quickPickHrCoaAccount(\'DEBET\', \'' + encAcc + '\');">+ Debet</button>'
                + '<button type="button" class="btn btn-xs hr-btn-add-credit fw-semibold" onclick="quickPickHrCoaAccount(\'CREDIT\', \'' + encAcc + '\');">+ Kredit</button>'
            + '</td>'
            + '</tr>';
    });
    $tbody.html(html);
}

function quickPickHrCoaAccount(side, accNo) {
    var acc = (hrCoaCatalog.accounts || []).find(function (a) { return String(a.Acc_No) === String(accNo); });
    if (!acc) return;

    var $targetRow = null;
    $('#hrCoaLinesBody tr').each(function () {
        var rowSide = $(this).find('.hr-ln-side').val();
        var rowAcc = $(this).find('.hr-ln-acc').val();
        if (rowSide === side && !rowAcc && !$targetRow) {
            $targetRow = $(this);
        }
    });

    if ($targetRow && typeof $targetRow.data('setAccount') === 'function') {
        $targetRow.data('setAccount')(acc.Header_No || '', acc.Acc_No);
    } else {
        addHrCoaRow(side, { Trial_Balance_Header_No: acc.Header_No || '', Acc_No: acc.Acc_No });
        $targetRow = $('#hrCoaLinesBody tr').last();
    }

    hrFormDirty = true;
    recalcHrCoaBalance();
    if ($targetRow && $targetRow.length) {
        var $amtInput = side === 'DEBET' ? $targetRow.find('.hr-ln-amt-db') : $targetRow.find('.hr-ln-amt-cr');
        $targetRow.addClass('table-active');
        setTimeout(function () { $targetRow.removeClass('table-active'); }, 900);
        $amtInput.focus();
    }
}

function applyHrCoaRowSideStyle($tr, side) {
    var isDebet = (side === 'DEBET');
    $tr.removeClass('hr-row-debet hr-row-credit').addClass(isDebet ? 'hr-row-debet' : 'hr-row-credit');
    $tr.find('.hr-ln-side').val(side);

    $tr.find('.hr-side-btn-debet').toggleClass('active-debet', isDebet);
    $tr.find('.hr-side-btn-credit').toggleClass('active-credit', !isDebet);

    var $hint = $tr.find('.hr-side-hint');
    $hint.removeClass('is-debet is-credit').addClass(isDebet ? 'is-debet' : 'is-credit');
    $hint.html(isDebet
        ? '<i class="fa fa-arrow-up"></i> Keperluan / Biaya'
        : '<i class="fa fa-arrow-down"></i> Sumber / Hutang');

    $tr.find('.hr-amt-box-debet')
        .removeClass('is-active-debet is-inactive')
        .addClass(isDebet ? 'is-active-debet' : 'is-inactive');
    $tr.find('.hr-amt-box-credit')
        .removeClass('is-active-credit is-inactive')
        .addClass(!isDebet ? 'is-active-credit' : 'is-inactive');
}

function setHrCoaRowAmount($tr, amount) {
    var side = $tr.find('.hr-ln-side').val() || 'DEBET';
    var num = parseFloat(amount || '0');
    var formatted = (isNaN(num) || num <= 0) ? '0' : Number(num.toFixed(2)).toString();
    $tr.find('.hr-ln-amt').val(isNaN(num) ? 0 : num);
    if (side === 'DEBET') {
        $tr.find('.hr-ln-amt-db').val(formatted);
        $tr.find('.hr-ln-amt-cr').val('');
    } else {
        $tr.find('.hr-ln-amt-cr').val(formatted);
        $tr.find('.hr-ln-amt-db').val('');
    }
}

function switchHrCoaRowSide($tr, targetSide, preserveAmount) {
    var currentSide = $tr.find('.hr-ln-side').val() || 'DEBET';
    if (currentSide === targetSide) return;

    var curVal = parseFloat($tr.find('.hr-ln-amt').val() || '0');
    applyHrCoaRowSideStyle($tr, targetSide);

    if (preserveAmount) {
        setHrCoaRowAmount($tr, curVal);
    } else {
        var typedRaw = targetSide === 'DEBET'
            ? $tr.find('.hr-ln-amt-db').val()
            : $tr.find('.hr-ln-amt-cr').val();
        var typedNum = parseFloat(typedRaw || '0');
        $tr.find('.hr-ln-amt').val(isNaN(typedNum) ? 0 : typedNum);
        if (targetSide === 'DEBET') {
            $tr.find('.hr-ln-amt-cr').val('');
        } else {
            $tr.find('.hr-ln-amt-db').val('');
        }
    }
    hrFormDirty = true;
    recalcHrCoaBalance();
}

function addHrCoaRow(side, data) {
    data = data || {};
    var normalizedSide = (String(side || 'DEBET').toUpperCase() === 'CREDIT') ? 'CREDIT' : 'DEBET';
    var tbOpts = '<option value="">-- Semua Kelompok --</option>';
    hrCoaCatalog.groups.forEach(function (g) {
        var sel = (data.Trial_Balance_Header_No && String(g.Header_No) === String(data.Trial_Balance_Header_No)) ? 'selected' : '';
        tbOpts += '<option value="' + escHr(g.Header_No) + '" ' + sel + '>' + escHr(g.Header_Name) + ' (' + escHr(g.Header_No) + ')</option>';
    });

    var initAmt = Number(data.Amount || 0);
    var dbInitVal = normalizedSide === 'DEBET' ? initAmt : '';
    var crInitVal = normalizedSide === 'CREDIT' ? initAmt : '';

    var $tr = $('<tr>'
        + '<td>'
            + '<input type="hidden" class="hr-ln-side" value="' + normalizedSide + '" />'
            + '<input type="hidden" class="hr-ln-amt" value="' + initAmt + '" />'
            + '<div class="hr-side-switch" role="group" aria-label="Posisi Akun">'
                + '<button type="button" class="hr-side-btn hr-side-btn-debet" title="Masuk Debet (Keperluan / Biaya)">DEBET</button>'
                + '<button type="button" class="hr-side-btn hr-side-btn-credit" title="Masuk Kredit (Sumber Dana / Hutang)">KREDIT</button>'
            + '</div>'
            + '<div class="hr-side-hint"></div>'
        + '</td>'
        + '<td><select class="form-select form-select-sm hr-ln-tb">' + tbOpts + '</select></td>'
        + '<td><select class="form-select form-select-sm hr-ln-acc"></select></td>'
        + '<td>'
            + '<div class="input-group input-group-sm hr-amt-box hr-amt-box-debet">'
                + '<span class="input-group-text">DR</span>'
                + '<input type="number" step="0.01" min="0" class="form-control text-end hr-ln-amt-db" value="' + dbInitVal + '" placeholder="— Debet" />'
            + '</div>'
        + '</td>'
        + '<td>'
            + '<div class="input-group input-group-sm hr-amt-box hr-amt-box-credit">'
                + '<span class="input-group-text">CR</span>'
                + '<input type="number" step="0.01" min="0" class="form-control text-end hr-ln-amt-cr" value="' + crInitVal + '" placeholder="— Kredit" />'
            + '</div>'
        + '</td>'
        + '<td><input type="text" class="form-control form-control-sm hr-ln-rem" value="' + escHr(data.Remark || '') + '" placeholder="Catatan singkat" /><input type="hidden" class="hr-ln-tag" value="' + escHr(data.Component_Tag || 'OTHER') + '" /></td>'
        + '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger" title="Hapus baris" onclick="$(this).closest(\'tr\').remove(); recalcHrCoaBalance();">&times;</button></td>'
        + '</tr>');

    $tr.find('.hr-side-btn-debet').on('click', function () {
        switchHrCoaRowSide($tr, 'DEBET', true);
    });
    $tr.find('.hr-side-btn-credit').on('click', function () {
        switchHrCoaRowSide($tr, 'CREDIT', true);
    });
    $tr.find('.hr-ln-amt-db').on('input', function () {
        var v = $(this).val();
        if ($tr.find('.hr-ln-side').val() !== 'DEBET') {
            if (v === '') return;
            switchHrCoaRowSide($tr, 'DEBET', false);
        } else {
            $tr.find('.hr-ln-amt').val(parseFloat(v || '0') || 0);
            recalcHrCoaBalance();
        }
    });
    $tr.find('.hr-ln-amt-cr').on('input', function () {
        var v = $(this).val();
        if ($tr.find('.hr-ln-side').val() !== 'CREDIT') {
            if (v === '') return;
            switchHrCoaRowSide($tr, 'CREDIT', false);
        } else {
            $tr.find('.hr-ln-amt').val(parseFloat(v || '0') || 0);
            recalcHrCoaBalance();
        }
    });

    var $tbSel = $tr.find('.hr-ln-tb');
    var $accSel = $tr.find('.hr-ln-acc');
    var syncingTb = false;

    var populateAcc = function (headerFilter, selectedAcc) {
        $accSel.empty().append('<option value="">-- Ketik Nama / Kode Akun COA --</option>');
        var list = hrCoaCatalog.accounts.filter(function (a) {
            if (headerFilter && String(a.Header_No) !== String(headerFilter)) return false;
            return true;
        });
        list.forEach(function (a) {
            var sel = (selectedAcc && String(a.Acc_No) === String(selectedAcc)) ? 'selected' : '';
            $accSel.append('<option value="' + escHr(a.Acc_No) + '" data-tb="' + escHr(a.Header_No || '') + '" ' + sel + '>' + escHr(a.Acc_Name) + ' &mdash; [' + escHr(a.Header_Name) + ' | ' + escHr(a.Acc_No) + ']</option>');
        });
        initHrSelect2($accSel, 'Ketik nama/deskripsi/kode COA...');
    };

    $tbSel.on('change', function () {
        if (syncingTb) return;
        populateAcc($(this).val(), '');
    });

    $accSel.on('change', function () {
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
        populateAcc(tbNo || '', accNo || '');
        if (accNo && String($accSel.val() || '') !== String(accNo)) {
            populateAcc('', accNo);
        }
    });

    applyHrCoaRowSideStyle($tr, normalizedSide);
    $('#hrCoaLinesBody').append($tr);
    initHrSelect2($tbSel, 'Semua Kelompok TB...');
    populateAcc(data.Trial_Balance_Header_No || '', data.Acc_No || '');
    recalcHrCoaBalance();
}

function recalcHrCoaBalance() {
    var deb = 0, cr = 0;
    $('#hrCoaLinesBody tr').each(function () {
        var s = $(this).find('.hr-ln-side').val();
        var a = parseFloat($(this).find('.hr-ln-amt').val() || '0');
        if (s === 'DEBET') deb += a;
        else cr += a;
    });
    $('#hrSumDebet').text(fmtRpHr(deb));
    $('#hrSumCredit').text(fmtRpHr(cr));
    var diff = Math.abs(deb - cr);
    var $b = $('#hrBalanceBadge');
    var $diffTxt = $('#hrBalanceDiffText');
    if (deb === 0 && cr === 0) {
        $b.removeClass('bg-success bg-danger').addClass('bg-secondary').text('Opsional / Belum Diisi');
        $diffTxt.removeClass('text-success text-danger fw-semibold').addClass('text-muted').text('Debet harus = Kredit');
    } else if (diff < 0.01) {
        $b.removeClass('bg-secondary bg-danger').addClass('bg-success').text('SEIMBANG (Debet = Kredit)');
        $diffTxt.removeClass('text-muted text-danger').addClass('text-success fw-semibold').html('<i class="fa fa-check-circle me-1"></i> Jurnal Seimbang');
    } else {
        $b.removeClass('bg-secondary bg-success').addClass('bg-danger').text('Selisih: ' + fmtRpHr(diff));
        var hintSide = deb > cr ? 'Kurang Kredit: ' : 'Kurang Debet: ';
        $diffTxt.removeClass('text-muted text-success').addClass('text-danger fw-semibold').text(hintSide + fmtRpHr(diff));
    }
}

function autoBalanceHrCredit() {
    var deb = 0, otherCr = 0;
    var $mainCreditRow = null;
    $('#hrCoaLinesBody tr').each(function () {
        var s = $(this).find('.hr-ln-side').val();
        var a = parseFloat($(this).find('.hr-ln-amt').val() || '0');
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
        var tot = parseFloat($('#hr_v_total').val() || '0');
        if (tot > 0) {
            var $firstDeb = $('#hrCoaLinesBody tr').filter(function () { return $(this).find('.hr-ln-side').val() === 'DEBET'; }).first();
            if ($firstDeb.length) {
                setHrCoaRowAmount($firstDeb, tot);
                deb = tot;
            }
        }
    }
    if (!$mainCreditRow) {
        addHrCoaRow('CREDIT');
        $mainCreditRow = $('#hrCoaLinesBody tr').last();
    }
    setHrCoaRowAmount($mainCreditRow, Math.max(0, deb - otherCr));
    hrFormDirty = true;
    recalcHrCoaBalance();
}

function suggestHrCoaLines() {
    ensureHrCoaCatalog(function () {
        $.ajax({
            url: 'hrd-voucher-suggest-lines',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            data: {
                channel: $('#hr_v_channel').val(),
                payment_method: $('#hr_v_pay_method').val(),
                department: $('#hr_v_dept').val(),
                amounts: {
                    Subtotal: $('#hr_v_subtotal').val(),
                    DP: $('#hr_v_dp').val(),
                    Delivery_Costs: $('#hr_v_delivery').val(),
                    Management_Fee: $('#hr_v_mgmt').val(),
                    Materai: $('#hr_v_materai').val(),
                    PPN: $('#hr_v_ppn').val(),
                    PPH: $('#hr_v_pph').val(),
                    Total: $('#hr_v_total').val()
                }
            },
            success: function (res) {
                $('#hrCoaLinesBody').empty();
                (res.data || []).forEach(function (ln) {
                    addHrCoaRow(ln.Line_Side, ln);
                });
                recalcHrCoaBalance();
            }
        });
    });
}

function addHrTenorRow(label, due, amt, note) {
    var idx = $('#hrTenorBody tr').length + 1;
    var row = '<tr>'
        + '<td><input type="text" class="form-control form-control-sm hr-t-label" value="' + escHr(label || ('Termin ' + idx)) + '" /></td>'
        + '<td><input type="date" class="form-control form-control-sm hr-t-due" value="' + escHr(due || $('#hr_v_due_date').val() || '') + '" /></td>'
        + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm text-end hr-t-amt" value="' + Number(amt || 0) + '" /></td>'
        + '<td><input type="text" class="form-control form-control-sm hr-t-note" value="' + escHr(note || '') + '" /></td>'
        + '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger" onclick="$(this).closest(\'tr\').remove();">&times;</button></td>'
        + '</tr>';
    $('#hrTenorBody').append(row);
}

function autoCalcHrPpn() {
    var pct = parseFloat($('#hr_v_ppn_type').val() || '0');
    var sub = parseFloat($('#hr_v_subtotal').val() || '0');
    $('#hr_v_ppn').val((sub * pct / 100).toFixed(2));
    recalcHrVoucherTotal();
}

function autoCalcHrPph() {
    var type = $('#hr_v_pph_type').val();
    var pct = 0;
    if (type === 'PPH23_2' || type === 'PPH42') pct = 2;
    else if (type === 'PPH23_4') pct = 4;
    else if (type === 'PPH23_15') pct = 15;
    else if (type === 'PPH_21_5') pct = 5;
    var sub = parseFloat($('#hr_v_subtotal').val() || '0');
    $('#hr_v_pph').val((sub * pct / 100).toFixed(2));
    recalcHrVoucherTotal();
}

function recalcHrVoucherTotal() {
    var sub = parseFloat($('#hr_v_subtotal').val() || '0');
    var dp = parseFloat($('#hr_v_dp').val() || '0');
    var deliv = parseFloat($('#hr_v_delivery').val() || '0');
    var mgmt = parseFloat($('#hr_v_mgmt').val() || '0');
    var mat = parseFloat($('#hr_v_materai').val() || '0');
    var ppn = parseFloat($('#hr_v_ppn').val() || '0');
    var pph = parseFloat($('#hr_v_pph').val() || '0');
    var tot = Math.max(0, sub + dp + deliv + mgmt + mat + ppn - pph);
    $('#hr_v_total').val(tot.toFixed(2));
}

function searchHrPo() {
    var q = $('#hr_po_search_input').val() || '';
    $.get('hrd-voucher-po-search', { q: q }, function (res) {
        var box = $('#hr_po_results').empty();
        var rows = res.data || [];
        if (!rows.length) {
            box.html('<div class="list-group-item small text-muted">PO tidak ditemukan.</div>').show();
            return;
        }
        rows.forEach(function (po) {
            var item = $('<button type="button" class="list-group-item list-group-item-action py-2 px-3 small"></button>');
            item.html('<strong>' + escHr(po.PO_No) + '</strong> &bull; ' + escHr(po.Vendor_Name || po.Vendor_ID) + ' &bull; <span class="text-success fw-bold">' + fmtRpHr(po.Total) + '</span><br><span class="text-muted">' + escHr(po.Subject_Payment || po.Remark || '') + '</span>');
            item.on('click', function () {
                $('#hr_v_po_ref').val(po.PO_No || '');
                $('#hr_v_pr_no').val(po.PR_No || '');
                $('#hr_po_selected_badge').html('PO Terpilih: <strong>' + escHr(po.PO_No) + '</strong> (PR: ' + escHr(po.PR_No || '-') + ')');
                $('#hr_v_vendor_id').val(po.Vendor_ID || '');
                $('#hr_v_vendor_name').val(po.Vendor_Name || '');
                $('#hr_v_bank_name').val(po.Bank_Name || '');
                $('#hr_v_bank_acc').val(po.Bank_Acc || '');
                $('#hr_v_bank_acc_name').val(po.Bank_Acc_Name || po.Vendor_Name || '');
                if (po.Department) $('#hr_v_dept').val(po.Department);
                $('#hr_v_subtotal').val(po.Subtotal || 0);
                $('#hr_v_dp').val(po.DP || 0);
                $('#hr_v_delivery').val(po.Delivery_Costs || 0);
                $('#hr_v_mgmt').val(po.Management_Fee || 0);
                $('#hr_v_ppn').val(po.PPN || 0);
                $('#hr_v_pph').val(po.PPH || 0);
                $('#hr_v_total').val(po.Total || 0);
                if (po.Subject_Payment || po.Remark) $('#hr_v_subject').val(po.Subject_Payment || po.Remark);
                box.hide();
                hrFormDirty = true;
            });
            box.append(item);
        });
        box.show();
    });
}

function clearHrPoRef() {
    $('#hr_v_po_ref').val('');
    $('#hr_v_pr_no').val('');
    $('#hr_po_selected_badge').text('Belum memilih PO (Pengajuan Manual Non-PO).');
    $('#hr_po_results').hide().empty();
}

function searchHrSuppliers() {
    var q = $('#hr_sup_search_input').val() || '';
    $.get('hrd-voucher-supplier-search', { q: q }, function (res) {
        var box = $('#hr_sup_results').empty();
        var rows = res.data || [];
        if (!rows.length) {
            box.html('<div class="list-group-item small text-muted">Vendor tidak ditemukan.</div>').show();
            return;
        }
        rows.forEach(function (sup) {
            var item = $('<button type="button" class="list-group-item list-group-item-action py-2 px-3 small"></button>');
            item.html('<strong>' + escHr(sup.Company_Name) + '</strong> (' + escHr(sup.Supplier_ID) + ')');
            item.on('click', function () {
                $('#hr_v_vendor_id').val(sup.Supplier_ID || '');
                $('#hr_v_vendor_name').val(sup.Company_Name || '');
                $('#hr_v_bank_name').val(sup.Bank_Name || '');
                $('#hr_v_bank_acc').val(sup.Bank_Acc || '');
                $('#hr_v_bank_acc_name').val(sup.Bank_Acc_Name || sup.Company_Name || '');
                box.hide();
                hrFormDirty = true;
            });
            box.append(item);
        });
        box.show();
    });
}

function submitHrVoucher() {
    var form = document.getElementById('FormHrVoucherCreate');
    var fd = new FormData(form);

    var lines = [];
    $('#hrCoaLinesBody tr').each(function () {
        var acc = $(this).find('.hr-ln-acc').val() || '';
        var amt = parseFloat($(this).find('.hr-ln-amt').val() || '0');
        if (acc && amt > 0) {
            lines.push({
                Line_Side: $(this).find('.hr-ln-side').val(),
                Trial_Balance_Header_No: $(this).find('.hr-ln-tb').val() || ($(this).find('.hr-ln-acc option:selected').data('tb') || ''),
                Acc_No: acc,
                Amount: amt,
                Department: $('#hr_v_dept').val() || '',
                Remark: $(this).find('.hr-ln-rem').val() || $('#hr_v_subject').val() || '',
                Component_Tag: $(this).find('.hr-ln-tag').val() || 'OTHER'
            });
        }
    });
    if (lines.length) {
        fd.append('Lines', JSON.stringify(lines));
    }

    var tenors = [];
    $('#hrTenorBody tr').each(function () {
        var amt = parseFloat($(this).find('.hr-t-amt').val() || '0');
        var due = $(this).find('.hr-t-due').val() || '';
        if (amt > 0 || due !== '') {
            tenors.push({
                Tenor_Label: $(this).find('.hr-t-label').val() || '',
                Due_Date: due,
                Amount: amt,
                Note: $(this).find('.hr-t-note').val() || ''
            });
        }
    });
    if (tenors.length) {
        fd.append('Tenors', JSON.stringify(tenors));
    }

    var id = $('#hr_v_id').val();
    var url = id ? 'hrd-voucher-resubmit' : 'hrd-voucher-store';
    $('#btnSubmitHrVoucher').prop('disabled', true);

    $.ajax({
        url: url,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            alert(res.message || 'Berhasil dikirim.');
            hrFormDirty = false;
            $('#HrVoucherFormCard').slideUp(150);
            loadHrVouchers();
        },
        error: function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengirim pengajuan.');
        },
        complete: function () {
            $('#btnSubmitHrVoucher').prop('disabled', false);
        }
    });
}

function loadHrVouchers() {
    var st = $('#HrVoucherStatusFilter').val() || 'ALL';
    $.get('hrd-voucher-data', { status: st }, function (res) {
        hrVoucherRows = res.data || [];
        var s = res.summary || {};
        $('#hrKpiTotal').text(s.total || 0);
        $('#hrKpiWaiting').text(s.waiting_approval || 0);
        $('#hrKpiSubmitted').text(s.submitted || 0);
        $('#hrKpiReturned').text(s.returned || 0);
        $('#hrKpiApActive').text(s.ap_active || 0);
        $('#hrKpiCompleted').text(s.completed || 0);
        $('#hrKpiRemain').text(fmtRpHr(s.remain_amount || 0));
        $('#hrKpiAmount').text(fmtRpHr(s.total_amount || 0));
        renderHrVoucherTable();
    });
}

function renderHrVoucherTable() {
    var q = ($('#HrVoucherSearch').val() || '').toLowerCase().trim();
    var filtered = hrVoucherRows.filter(function (r) {
        if (!q) return true;
        return ['Voucher_No', 'PO_References', 'Reference', 'Vendor_Name', 'Subject_Payment', 'Department'].some(function (k) {
            return String(r[k] || '').toLowerCase().indexOf(q) !== -1;
        });
    });

    var tbody = $('#HrVoucherBody');
    if (!filtered.length) {
        tbody.html('<tr><td colspan="9" class="text-center text-muted py-3">Belum ada data pengajuan voucher / AP manual.</td></tr>');
        return;
    }

    var html = '';
    filtered.forEach(function (r) {
        var reqBadge = r.Requested_Channel === 'AP'
            ? '<span class="badge bg-primary text-white">AP</span>'
            : '<span class="badge bg-warning text-dark">DIRECT</span>';
        var finalBadge = r.Final_Channel
            ? ' &rarr; ' + (r.Final_Channel === 'AP' ? '<span class="badge bg-info text-white">AP</span>' : '<span class="badge bg-success text-white">' + escHr(r.Payment_Method || 'DIRECT') + '</span>')
            : ' <span class="text-muted small">(Pending)</span>';
        var effStatus = r.Effective_Status || r.Status;
        var apRefInfo = r.Legacy_AP_Task_ID ? '<br><small class="text-info">AP: ' + escHr(r.Legacy_AP_Task_ID) + '</small>' : '';

        var actions = '<button type="button" class="btn btn-xs btn-outline-primary me-1" onclick="viewHrVoucherDetail(' + r.ID + ')">Detail</button>'
            + '<a href="hrd-voucher-print?id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1"><i class="fa fa-print"></i> Print</a>';
        if (r.Status === 'RETURNED') {
            actions += '<button type="button" class="btn btn-xs btn-warning text-dark me-1" onclick="openHrVoucherEditForm(' + r.ID + ')">Revisi &amp; Kirim Ulang</button>';
        }
        if (r.Status === 'WAITING_APPROVAL' || r.Status === 'SUBMITTED' || r.Status === 'RETURNED') {
            actions += '<button type="button" class="btn btn-xs btn-outline-danger" onclick="cancelHrVoucher(' + r.ID + ')">Batal</button>';
        }

        html += '<tr>'
            + '<td class="fw-bold text-primary">' + escHr(r.Voucher_No) + '<br><small class="text-muted fw-normal">' + escHr(r.Expense_Category || '') + '</small>' + apRefInfo + '</td>'
            + '<td>' + reqBadge + finalBadge + (r.Is_Converted_To_AP == 1 ? '<br><small class="text-info">Dikonversi Finance ke AP</small>' : '') + '</td>'
            + '<td>' + (r.PO_References ? '<span class="badge bg-light text-dark border">PO: ' + escHr(r.PO_References) + '</span><br>' : '') + '<small>Faktur: ' + escHr(r.Reference || '-') + '</small></td>'
            + '<td><strong>' + escHr(r.Vendor_Name || '-') + '</strong><br><small class="text-muted">' + escHr(r.Department || '-') + ' &bull; ' + escHr(r.Subject_Payment || '') + '</small></td>'
            + '<td>' + escHr(r.Due_Date || '-') + '</td>'
            + '<td class="text-end fw-semibold">' + fmtRpHr(r.Total) + '</td>'
            + '<td class="text-end fw-semibold text-danger">' + fmtRpHr(r.Remain_Val) + '</td>'
            + '<td>' + statusBadgeHr(effStatus) + (r.Reject_Reason ? '<br><small class="text-danger">' + escHr(r.Reject_Reason) + '</small>' : '') + '</td>'
            + '<td class="text-end text-nowrap">' + actions + '</td>'
            + '</tr>';
    });
    tbody.html(html);
}

function viewHrVoucherDetail(id) {
    if ($('#HrVoucherFormCard').is(':visible')) {
        if (hrFormDirty && !confirm('Tutup form pengajuan dan buka detail voucher? Data form yang belum dikirim akan hilang.')) {
            return;
        }
        hrFormDirty = false;
        $('#HrVoucherFormCard').stop(true, true).hide();
    }
    $.get('hrd-voucher-detail', { id: id }, function (res) {
        if (res.status !== 'success') return;
        var d = res.data;
        var v = d.voucher;
        var html = '<div class="card border-info shadow-sm">'
            + '<div class="card-header bg-light d-flex justify-content-between align-items-center">'
            + '<h6 class="mb-0 fw-bold">Detail Pengajuan: ' + escHr(v.Voucher_No) + ' (' + escHr(v.Status) + ')</h6>'
            + '<div class="d-flex gap-2">'
            + '<a href="hrd-voucher-print?id=' + v.ID + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa fa-print me-1"></i> Print Voucher</a>'
            + '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="$(\'#HrVoucherDetailBox\').empty().hide();">Tutup</button>'
            + '</div></div><div class="card-body">';

        if (v.Reject_Reason) {
            html += '<div class="alert alert-danger py-2"><strong>Catatan Penolakan / Revisi:</strong> ' + escHr(v.Reject_Reason) + '</div>';
        }

        html += '<div class="row g-3 mb-3">'
            + '<div class="col-md-6"><table class="table table-sm table-borderless mb-0">'
            + '<tr><th style="width:38%">Vendor</th><td>' + escHr(v.Vendor_Name || '-') + ' (' + escHr(v.Vendor_ID || '-') + ')</td></tr>'
            + '<tr><th>Ref PO / Faktur</th><td>' + escHr(v.PO_References || '-') + ' / ' + escHr(v.Reference || '-') + '</td></tr>'
            + '<tr><th>Rekening</th><td>' + escHr(v.Bank_Name || '-') + ' &bull; ' + escHr(v.Bank_Acc || '-') + ' (' + escHr(v.Bank_Acc_Name || '-') + ')</td></tr>'
            + '<tr><th>Perihal</th><td>' + escHr(v.Subject_Payment || '-') + '</td></tr>'
            + (v.Attachment_Path ? '<tr><th>Lampiran</th><td><a href="{{ asset('filevendor') }}/' + escHr(v.Attachment_Path) + '" target="_blank" class="btn btn-xs btn-outline-primary">Buka Lampiran</a></td></tr>' : '')
            + '</table></div>'
            + '<div class="col-md-6"><table class="table table-sm table-borderless mb-0">'
            + '<tr><th style="width:42%">Subtotal / DP</th><td>' + fmtRpHr(v.Subtotal) + ' / ' + fmtRpHr(v.DP) + '</td></tr>'
            + '<tr><th>Delivery / Mgmt Fee</th><td>' + fmtRpHr(v.Delivery_Costs) + ' / ' + fmtRpHr(v.Management_Fee) + '</td></tr>'
            + '<tr><th>PPN / PPh / Materai</th><td>' + fmtRpHr(v.PPN) + ' / -' + fmtRpHr(v.PPH) + ' / ' + fmtRpHr(v.Materai) + '</td></tr>'
            + '<tr><th>Total / Terbayar / Sisa</th><td class="fw-bold">' + fmtRpHr(v.Total) + ' / ' + fmtRpHr(v.Paid) + ' / <span class="text-danger">' + fmtRpHr(v.Remain_Val) + '</span></td></tr>'
            + '</table></div></div>';

        if ((d.lines || []).length) {
            var detDeb = 0, detCre = 0;
            html += '<h6 class="fw-bold mt-2">Usulan / Rincian COA (Debet &amp; Kredit)</h6>'
                + '<div class="table-responsive"><table class="table table-sm table-bordered align-middle">'
                + '<thead><tr>'
                + '<th style="width:110px;">Posisi</th>'
                + '<th>Deskripsi Akun</th>'
                + '<th>Kelompok</th>'
                + '<th class="text-end" style="width:150px; background-color:#e0f2fe; color:#0369a1;">Debet (Rp)</th>'
                + '<th class="text-end" style="width:150px; background-color:#fef3c7; color:#b45309;">Kredit (Rp)</th>'
                + '<th>Keterangan</th>'
                + '</tr></thead><tbody>';
            d.lines.forEach(function (ln) {
                var isDeb = String(ln.Line_Side || '').toUpperCase() === 'DEBET';
                var amtVal = parseFloat(ln.Amount || '0') || 0;
                if (isDeb) detDeb += amtVal; else detCre += amtVal;
                var rowBg = isDeb ? '#f4faff' : '#fffbeb';
                var borderLeft = isDeb ? '4px solid #0284c7' : '4px solid #d97706';
                var badgeStyle = isDeb ? 'background-color:#0284c7; color:#fff;' : 'background-color:#d97706; color:#fff;';
                var sideLabel = isDeb ? '<i class="fa fa-arrow-up me-1"></i>DEBET' : '<i class="fa fa-arrow-down me-1"></i>KREDIT';
                html += '<tr style="background-color:' + rowBg + ';">'
                    + '<td style="border-left:' + borderLeft + ';"><span class="badge" style="' + badgeStyle + '">' + sideLabel + '</span></td>'
                    + '<td class="fw-semibold">' + escHr(ln.Acc_Name || ln.Acc_No) + ' <small class="text-muted">(' + escHr(ln.Acc_No) + ')</small></td>'
                    + '<td>' + escHr(ln.Trial_Balance_Header_Name || '-') + '</td>'
                    + '<td class="text-end fw-bold" style="color:#0369a1;">' + (isDeb ? fmtRpHr(amtVal) : '<span class="text-muted fw-normal">—</span>') + '</td>'
                    + '<td class="text-end fw-bold" style="color:#b45309;">' + (!isDeb ? fmtRpHr(amtVal) : '<span class="text-muted fw-normal">—</span>') + '</td>'
                    + '<td>' + escHr(ln.Remark || '-') + '</td>'
                    + '</tr>';
            });
            var detDiff = Math.abs(detDeb - detCre);
            var detBalBadge = detDiff < 0.01
                ? '<span class="badge bg-success text-white">SEIMBANG</span>'
                : '<span class="badge bg-danger text-white">Selisih: ' + fmtRpHr(detDiff) + '</span>';
            html += '</tbody><tfoot class="table-light">'
                + '<tr>'
                + '<td colspan="3" class="text-end fw-bold">Total &amp; Status Keseimbangan ' + detBalBadge + '</td>'
                + '<td class="text-end fw-bold" style="background-color:#e0f2fe; color:#0369a1;">' + fmtRpHr(detDeb) + '</td>'
                + '<td class="text-end fw-bold" style="background-color:#fef3c7; color:#b45309;">' + fmtRpHr(detCre) + '</td>'
                + '<td></td>'
                + '</tr></tfoot></table></div>';
        }

        if ((d.tenors || []).length) {
            html += '<h6 class="fw-bold mt-2">Jadwal Tenor / Jatuh Tempo</h6><table class="table table-sm table-bordered"><thead><tr><th>#</th><th>Label</th><th>Jatuh Tempo</th><th class="text-end">Nominal</th><th class="text-end">Terbayar</th><th>Status</th></tr></thead><tbody>';
            d.tenors.forEach(function (t) {
                html += '<tr><td>' + escHr(t.Tenor_Seq) + '</td><td>' + escHr(t.Tenor_Label) + '</td><td>' + escHr(t.Due_Date) + '</td><td class="text-end">' + fmtRpHr(t.Amount) + '</td><td class="text-end">' + fmtRpHr(t.Paid_Amount) + '</td><td>' + escHr(t.Status) + '</td></tr>';
            });
            html += '</tbody></table>';
        }

        if ((d.payments || []).length) {
            html += '<h6 class="fw-bold mt-2">Riwayat Pembayaran Finance</h6><table class="table table-sm table-bordered"><thead><tr><th>Tanggal</th><th>Metode</th><th>Akun COA</th><th class="text-end">Nominal</th><th>No Jurnal HO</th><th>Ref</th></tr></thead><tbody>';
            d.payments.forEach(function (p) {
                html += '<tr><td>' + escHr(p.Payment_Date) + '</td><td>' + escHr(p.Payment_Channel) + '</td><td>' + escHr(p.Payment_Acc_No) + ' - ' + escHr(p.Payment_Acc_Name || '') + '</td><td class="text-end">' + fmtRpHr(p.Amount) + '</td><td>' + escHr(p.Journal_No || '-') + '</td><td>' + escHr(p.Payment_Ref || '-') + '</td></tr>';
            });
            html += '</tbody></table>';
        }

        if ((d.approval_trail || []).length) {
            html += '<h6 class="fw-bold mt-2">Alur Approval Berjenjang</h6><div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead class="table-light"><tr><th style="width:90px;">Stage</th><th>Jabatan / Posisi</th><th style="width:170px;">Status</th><th>Eksekutor / Kandidat</th><th style="width:155px;">Waktu</th><th>Keterangan</th></tr></thead><tbody>';
            d.approval_trail.forEach(function (ap) {
                var posCode = ap.position_code ? ' <code class="ms-1">' + escHr(ap.position_code) + '</code>' : '';
                html += '<tr>'
                    + '<td class="fw-semibold">Stage ' + escHr(ap.stage_order) + '</td>'
                    + '<td>' + escHr(ap.label) + posCode + '</td>'
                    + '<td>' + statusBadgeHr(ap.status) + '</td>'
                    + '<td>' + escHr(ap.actor_name || '-') + '</td>'
                    + '<td class="small text-muted">' + escHr(ap.acted_at || '-') + '</td>'
                    + '<td class="small">' + escHr(ap.notes || '-') + '</td>'
                    + '</tr>';
            });
            html += '</tbody></table></div>';
        }

        html += '</div></div>';
        $('#HrVoucherDetailBox').html(html).show();
        document.getElementById('HrVoucherWorkspace').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

function cancelHrVoucher(id) {
    if (!confirm('Batalkan pengajuan voucher ini?')) return;
    $.post('hrd-voucher-cancel', {
        _token: $('input[name="_token"]').val(),
        id: id
    }, function (res) {
        alert(res.message || 'Dibatalkan.');
        loadHrVouchers();
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal membatalkan.');
    });
}

$(function () {
    $('#FormHrVoucherCreate').on('input change', 'input, select, textarea', function () {
        hrFormDirty = true;
    });
    $('#hr_v_category, #hr_v_pay_method').on('change', updateHrCoaGuideContext);
    loadHrVouchers();
    var params = new URLSearchParams(window.location.search);
    var openId = parseInt(params.get('open_id') || '0', 10);
    if (openId > 0) {
        viewHrVoucherDetail(openId);
    }
});
</script>
@endsection
