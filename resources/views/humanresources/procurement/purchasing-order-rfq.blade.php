@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .rfq-hero-banner {
        background: linear-gradient(135deg, #064E3B 0%, #047857 100%);
        border-radius: 12px;
        padding: 18px 22px;
        color: #FFFFFF;
        margin-bottom: 18px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 4px 14px rgba(6, 78, 59, 0.14);
    }
    .rfq-hero-title {
        font-size: 19px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .rfq-hero-breadcrumb {
        font-size: 12px;
        color: #D1FAE5;
        margin-top: 4px;
    }
    .rfq-hero-breadcrumb a {
        color: #A7F3D0;
        text-decoration: none;
    }
    .rfq-card-panel {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }
    .rfq-card-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }
    .rfq-card-title {
        font-size: 15.5px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    .rfq-card-subtitle {
        font-size: 12px;
        color: #6B7280;
        margin-top: 2px;
    }
    .rfq-pill-badge {
        background: #0284C7;
        color: #FFFFFF;
        font-size: 11.5px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .rfq-grid-wrap {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        overflow: hidden;
    }
    .rfq-bottom-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #F1F5F9;
    }
    .rfq-action-btn {
        border-radius: 999px !important;
        padding: 7px 14px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

<!-- Header Banner Hijau -->
<div class="rfq-hero-banner">
    <div>
        <h1 class="rfq-hero-title">
            <i class="fas fa-file-alt"></i>
            <span>Request For Quotation (RFQ)</span>
        </h1>
        <div class="rfq-hero-breadcrumb">
            <a href="{{ url('procurement-dashboard') }}">Procurement</a> / <span>Request For Quotation (RFQ)</span>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
        <button type="button" class="btn btn-light text-success font-weight-bold shadow-sm"
                style="border-radius: 8px; font-size: 12.5px; padding: 7px 14px;"
                onclick="openCreateRFQFromApprovedPR()">
            <i class="fas fa-plus-circle mr-1"></i> Create RFQ (PR Approved)
        </button>
    </div>
</div>

<!-- Panel Card Putih -->
<div class="rfq-card-panel">
    <div class="rfq-card-header">
        <div>
            <h2 class="rfq-card-title">Daftar Request For Quotation (RFQ)</h2>
            <div class="rfq-card-subtitle">
                Klik <strong>Create RFQ</strong> untuk membuat RFQ baru dari PR yang sudah <strong>APPROVED</strong>, atau pilih satu baris RFQ di bawah untuk Edit/Hapus, Preview, Download, &amp; Kirim ke Vendor.
            </div>
        </div>
        <span class="rfq-pill-badge">
            <i class="fas fa-layer-group"></i> Request Type: RFQ
        </span>
    </div>

    <!-- DHTMLX Grid Container -->
    <div class="rfq-grid-wrap">
        <div id="RFQ" style="width:100%; height:420px; background-color:white; overflow:hidden;"></div>
    </div>

    <!-- Bottom Action Bar (Tanpa Double Click) -->
    <div class="rfq-bottom-toolbar">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button" id="BtnCreateRFQBottom" class="btn btn-success rfq-action-btn shadow-sm" onclick="openCreateRFQFromApprovedPR()">
                <i class="fas fa-plus-circle"></i> Create RFQ
            </button>

            <button type="button" id="BtnEditRFQ" class="btn btn-warning text-dark rfq-action-btn shadow-sm" onclick="openEditRFQPanel()">
                <i class="fas fa-edit"></i> Edit RFQ / Item &amp; Vendor
            </button>

            <button type="button" id="BtnDeleteRFQ" class="btn btn-danger rfq-action-btn shadow-sm" onclick="deleteSelectedRFQ()">
                <i class="fas fa-trash-alt"></i> Hapus RFQ
            </button>

            <button type="button" id="BtnPreviewRFQ" class="btn btn-primary rfq-action-btn shadow-sm" onclick="previewSelectedRFQ(false)">
                <i class="fas fa-eye"></i> Preview RFQ
            </button>

            <button type="button" id="BtnDownloadRFQ" class="btn btn-dark rfq-action-btn shadow-sm" onclick="previewSelectedRFQ(true)">
                <i class="fas fa-file-pdf"></i> Download PDF
            </button>

            <button type="button" id="BtnSendWA" class="btn btn-outline-success rfq-action-btn" onclick="sendRFQChannel('WA')">
                <i class="fab fa-whatsapp"></i> Kirim WA
            </button>

            <button type="button" id="BtnSendEmail" class="btn btn-info rfq-action-btn shadow-sm" onclick="sendRFQChannel('EMAIL')">
                <i class="fas fa-envelope"></i> Kirim Email
            </button>
        </div>

        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button" id="BtnRefreshRFQGrid" class="btn btn-outline-secondary rfq-action-btn" onclick="refreshRFQGrid()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Panel Create RFQ Langsung dari PR APPROVED -->
<div id="RFQCreateWrapper" class="mt-3" style="display:none;">
    <div class="rfq-card-panel border-success">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <h5 class="mb-0 font-weight-bold text-success" style="font-size: 15px;">
                    <i class="fas fa-plus-circle mr-1"></i> Buat RFQ Baru dari Purchase Request (Status: APPROVED)
                </h5>
                <small class="text-muted">Pilih Nomor PR yang sudah disetujui untuk menampilkan rincian item &amp; menambahkan vendor penerima RFQ.</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeCreateRFQPanel()">
                <i class="fas fa-times mr-1"></i> Tutup
            </button>
        </div>

        <div class="row align-items-end mb-3">
            <div class="col-md-6">
                <label class="small font-weight-bold text-dark mb-1">Pilih Nomor PR (Status APPROVED) *</label>
                <select id="selectApprovedPRForRFQ" class="form-control form-control-sm" onchange="loadApprovedPRToRFQForm(this.value)">
                    <option value="">-- Memuat daftar PR APPROVED... --</option>
                </select>
            </div>
            <div class="col-md-3 mt-2 mt-md-0">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="fetchApprovedPRDropdown()">
                    <i class="fas fa-sync-alt mr-1"></i> Muat Ulang Daftar PR
                </button>
            </div>
        </div>

        <!-- Kontainer Form Detail PR Approved & Pembuatan Multi-Vendor RFQ -->
        <div id="RFQCreateDetailContainer"></div>
    </div>
</div>

<!-- Area Panel Edit RFQ / Item / Vendor -->
<div id="RFQEditPanel" class="mt-3"></div>

<!-- Area Preview Dokumen RFQ -->
<div id="RFQPreviewPanel" class="mt-3"></div>

<script>
function getSelectedRFQId() {
    var selectedRFQ = RFQ.getSelectedId();
    if (!selectedRFQ) {
        alert("Silakan klik / pilih salah satu baris RFQ pada tabel terlebih dahulu!");
        return null;
    }
    return selectedRFQ;
}

function openCreateRFQFromApprovedPR() {
    $('#RFQEditPanel').empty();
    $('#RFQPreviewPanel').empty();
    $('#RFQCreateWrapper').slideDown(200);
    fetchApprovedPRDropdown();

    setTimeout(function() {
        var el = document.getElementById('RFQCreateWrapper');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 220);
}

// function closeCreateRFQPanel() {
//     $('#RFQCreateWrapper').slideUp(180);
//     $('#RFQCreateDetailContainer').empty();
// }

function closeCreateRFQPanel() {
    var $wrapper = $('#RFQCreateWrapper');
    $wrapper.stop(true, true).hide();
    $('#RFQCreateDetailContainer').empty();

    var $prSel = $('#selectApprovedPRForRFQ');
    if ($prSel.length) {
        $prSel.off('change.rfqLoad');
        if ($prSel.hasClass('select2-hidden-accessible')) {
            $prSel.val('').trigger('change.select2');
        } else {
            $prSel.val('');
        }
        // Pasang kembali listener load setelah nilai dikosongkan
        setTimeout(function() {
            $prSel.off('change.rfqLoad').on('change.rfqLoad', function() {
                loadApprovedPRToRFQForm(this.value);
            });
        }, 50);
    }
}

function initSelect2ApprovedPR() {
    var $sel = $('#selectApprovedPRForRFQ');
    if (!$sel.length || typeof $.fn.select2 !== 'function') return;

    if ($sel.hasClass('select2-hidden-accessible')) {
        $sel.select2('destroy');
    }

    $sel.select2({
        placeholder: '-- Ketik untuk mencari Nomor PR, Dept, atau PIC --',
        allowClear: true,
        width: '100%'
    });
}

function fetchApprovedPRDropdown() {
    var selectEl = document.getElementById('selectApprovedPRForRFQ');
    if (!selectEl) return;

    if ($(selectEl).hasClass('select2-hidden-accessible')) {
        $(selectEl).select2('destroy');
    }

    selectEl.innerHTML = '<option value="">-- Memuat daftar PR APPROVED... --</option>';

    fetch("{{ url('rfq-approved-pr-list') }}", {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        var rows = res.data || [];
        if (rows.length === 0) {
            selectEl.innerHTML = '<option value="">-- Belum ada PR dengan status APPROVED --</option>';
            initSelect2ApprovedPR();
            return;
        }

        var html = '<option value=""></option>';
        rows.forEach(function(item) {
            var budget = Number(item.Estimated_Budget || 0).toLocaleString('id-ID');
            html += '<option value="' + item.PR_No + '">' +
                item.PR_No + ' | Dept: ' + (item.Department || '-') +
                ' | PIC: ' + (item.PIC || '-') +
                ' | Est: Rp ' + budget +
            '</option>';
        });
        selectEl.innerHTML = html;

        // Aktifkan Select2 dengan kotak pencarian ketik
        initSelect2ApprovedPR();
    })
    .catch(function() {
        selectEl.innerHTML = '<option value="">Gagal memuat daftar PR APPROVED</option>';
        initSelect2ApprovedPR();
    });
}

function loadApprovedPRToRFQForm(prNo) {
    if (!prNo) {
        $('#RFQCreateDetailContainer').empty();
        return;
    }

    $('#RFQCreateDetailContainer').html(
        '<div class="text-center p-4 bg-light rounded border">' +
            '<i class="fas fa-spinner fa-spin fa-2x text-success"></i>' +
            '<p class="mt-2 mb-0 small text-muted">Memuat detail PR ' + prNo + ' &amp; form pembuatan RFQ...</p>' +
        '</div>'
    );

    $('#RFQCreateDetailContainer').load("{{ url('purchasing-order-rfq-created') }}?PR_No=" + encodeURIComponent(prNo), function(response, status, xhr) {
        if (status === "error") {
            $('#RFQCreateDetailContainer').html(
                '<div class="alert alert-danger mb-0">' +
                    'Gagal memuat detail PR ' + prNo + ' (HTTP ' + xhr.status + ').' +
                '</div>'
            );
        }
    });
}

function openEditRFQPanel() {
    var rfqNo = getSelectedRFQId();
    if (!rfqNo) return;

    closeCreateRFQPanel();
    $('#RFQPreviewPanel').empty();
    $('#RFQEditPanel').html('<div class="text-center p-4 bg-white rounded border"><i class="fas fa-spinner fa-spin fa-2x text-warning"></i><p class="mt-2 mb-0 small text-muted">Memuat panel edit RFQ, item &amp; vendor...</p></div>');
    $('#RFQEditPanel').load("{{ url('rfq-edit-panel') }}?rfq_no=" + encodeURIComponent(rfqNo), function() {
        $('html, body').animate({ scrollTop: $('#RFQEditPanel').offset().top - 70 }, 400);
    });
}

function deleteSelectedRFQ() {
    var rfqNo = getSelectedRFQId();
    if (!rfqNo) return;

    if (!confirm("Hapus seluruh RFQ dengan nomor " + rfqNo + " beserta detail item-nya?")) {
        return;
    }

    fetch("{{ url('rfq-delete') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ rfq_no: rfqNo })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            alert(data.message || 'RFQ berhasil dihapus.');
            refreshRFQGrid();
        } else {
            alert(data.message || 'Gagal menghapus RFQ.');
        }
    })
    .catch(function() {
        alert('Terjadi kesalahan saat menghapus RFQ.');
    });
}

function previewSelectedRFQ(isDownload) {
    var rfqNo = getSelectedRFQId();
    if (!rfqNo) return;

    var url = "{{ url('rfq-print') }}?rfq=" + encodeURIComponent(rfqNo);
    if (isDownload) {
        url += "&download=1";
        window.open(url, '_blank');
        return;
    }

    closeCreateRFQPanel();
    $('#RFQEditPanel').empty();
    $('#RFQPreviewPanel').html(
        '<div class="card shadow-sm border-0">' +
            '<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">' +
                '<span class="small font-weight-bold"><i class="fas fa-file-pdf mr-1"></i> Preview Dokumen RFQ: ' + rfqNo + '</span>' +
                '<div>' +
                    '<a href="' + url + '&download=1" target="_blank" class="btn btn-sm btn-warning text-dark py-0 mr-1"><i class="fas fa-file-pdf mr-1"></i> Download PDF</a>' +
                    '<button type="button" class="btn btn-sm btn-outline-light py-0" onclick="$(\'#RFQPreviewPanel\').empty()">Tutup</button>' +
                '</div>' +
            '</div>' +
            '<div class="card-body p-0">' +
                '<iframe src="' + url + '" style="width:100%; height:620px; border:0;"></iframe>' +
            '</div>' +
        '</div>'
    );
    $('html, body').animate({ scrollTop: $('#RFQPreviewPanel').offset().top - 70 }, 400);
}



function sendRFQChannel(channel) {
    var rfqNo = getSelectedRFQId();
    if (!rfqNo) return;

    var isWA = (channel === 'WA');
    var endpoint = isWA ? "{{ url('rfq-send-wa') }}" : "{{ url('rfq-send-email') }}";

    if (!isWA && !confirm('Kirim dokumen PDF ' + rfqNo + ' ke email vendor yang terdaftar?')) {
        return;
    }

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ rfq_no: rfqNo, channel: channel })
    })
    .then(async function(res) {
        var data = {};
        try { data = await res.json(); } catch (e) {}
        if (!res.ok) {
            throw new Error(data.message || ('Gagal memproses pengiriman ' + channel + ' (HTTP ' + res.status + ').'));
        }
        return data;
    })
    .then(function(data) {
        if (data.status === 'success') {
            if (data.wa_url) {
                window.open(data.wa_url, '_blank');
            }
            alert(data.message || ('RFQ ' + rfqNo + ' berhasil diproses untuk dikirim via ' + channel + '.'));
            refreshRFQGrid();
        } else {
            alert(data.message || 'Gagal mengirim RFQ.');
        }
    })
    .catch(function(err) {
        alert(err.message || 'Terjadi kesalahan saat memproses pengiriman RFQ.');
    });
}

// function refreshRFQGrid() {
//     RFQ.clearAll();
//     RFQ.load("{{ url('xml-purchasing-order-rfq') }}");
//     $('#RFQEditPanel').empty();
//     $('#RFQPreviewPanel').empty();
// }



// Saat klik tombol "Download PDF"
function downloadSelectedRFQPDF() {
    var rfqNo = getSelectedRFQNo();
    if (!rfqNo) { alert('Pilih baris RFQ terlebih dahulu!'); return; }
    window.open('rfq-print?rfq=' + encodeURIComponent(rfqNo) + '&download=1', '_blank');
}

// Saat klik tombol "Kirim WA"
function sendSelectedRFQWA() {
    var rfqNo = getSelectedRFQNo();
    if (!rfqNo) { alert('Pilih baris RFQ terlebih dahulu!'); return; }

    $.post('rfq-send-wa', { _token: '{{ csrf_token() }}', rfq_no: rfqNo }, function(res) {
        if (res.status === 'success' && res.wa_url) {
            window.open(res.wa_url, '_blank');
            if (typeof refreshParentRFQGrid === 'function') refreshParentRFQGrid();
        } else {
            alert(res.message);
        }
    }).fail(function(xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyiapkan pengiriman WA.');
    });
}

// Saat klik tombol "Kirim Email"
function sendSelectedRFQEmail() {
    var rfqNo = getSelectedRFQNo();
    if (!rfqNo) { alert('Pilih baris RFQ terlebih dahulu!'); return; }
    if (!confirm('Kirim dokumen PDF ' + rfqNo + ' ke email vendor yang terdaftar?')) return;

    $.post('rfq-send-email', { _token: '{{ csrf_token() }}', rfq_no: rfqNo }, function(res) {
        alert(res.message);
        if (res.status === 'success' && typeof refreshParentRFQGrid === 'function') {
            refreshParentRFQGrid();
        }
    }).fail(function(xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengirim email RFQ.');
    });
}




function refreshRFQGrid() {
    if (typeof RFQ !== 'undefined' && RFQ) {
        RFQ.clearAll();
        var sep = "{{ url('xml-purchasing-order-rfq') }}".indexOf('?') > -1 ? '&' : '?';
        RFQ.load("{{ url('xml-purchasing-order-rfq') }}" + sep + "_t=" + new Date().getTime());
    }
    $('#RFQEditPanel').empty();
    $('#RFQPreviewPanel').empty();
}

var RFQ = new dhtmlXGridObject('RFQ');
RFQ.setImagePath("./dhtmlx/imgs/");
RFQ.setHeader("No.,No. RFQ,No. PR,Vendor / Supplier,Tanggal RFQ,No. Quotation,Grand Total (Rp),Status,Dibuat Oleh");
RFQ.setInitWidths("55,165,165,220,105,140,140,105,105");
RFQ.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
RFQ.setColAlign("center,center,center,left,center,center,right,center,center");
RFQ.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
RFQ.setColSorting("int,str,str,str,str,str,str,str,str");
RFQ.setEditable(false);
RFQ.enableMultiline(true);
RFQ.setSkin("dhx_skyblue");
RFQ.init();

RFQ.load("{{ url('xml-purchasing-order-rfq') }}");
</script>
@endsection