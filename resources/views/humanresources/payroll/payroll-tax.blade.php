@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header mb-3">
        <h1 class="h3 fw-bold text-dark">Master Pajak & Pengurang Gaji (Tax & Deduction Setup)</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1" class="text-decoration-none">Payroll</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Master Tax & Deduction Rules</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

    <!-- Nav Tabs Konfigurasi Terpadu -->
    <ul class="nav nav-pills mb-3 gap-2" id="taxDeductionTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-semibold px-4" id="ptkp-tab" data-bs-toggle="pill" data-bs-target="#ptkp-pane" type="button" role="tab">
                <i class="fa fa-users me-1"></i> PTKP & Kategori TER
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold px-4" id="ter-tab" data-bs-toggle="pill" data-bs-target="#ter-pane" type="button" role="tab" onclick="loadTerRates()">
                <i class="fa fa-percent me-1"></i> Tarif TER Bulanan (PP 58/2023)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold px-4" id="pasal17-tab" data-bs-toggle="pill" data-bs-target="#pasal17-pane" type="button" role="tab" onclick="loadTaxBrackets()">
                <i class="fa fa-balance-scale me-1"></i> Tarif Progresif (UU HPP Ps. 17)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold px-4" id="deduction-tab" data-bs-toggle="pill" data-bs-target="#deduction-pane" type="button" role="tab" onclick="loadDeductionConfigs()">
                <i class="fa fa-cogs me-1"></i> Setup Deduction (Unpaid, Late, BPJS)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="taxDeductionTabContent">
        <!-- 1. TAB PTKP & TER MAPPING -->
        <div class="tab-pane fade show active" id="ptkp-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-primary">Master PTKP (Penghasilan Tidak Kena Pajak) & Pemetaan TER</h5>
                        <small class="text-muted">Kelola status perkawinan, tanggungan anak, nilai PTKP, dan kategori TER A/B/C.</small>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="TaxList" style="width:100%; height:450px; background-color:white; overflow:hidden;"></div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" id="AddBM" class="btn btn-primary rounded-pill px-3">
                            <i class="fa fa-plus me-1"></i> Tambah Data
                        </button>
                        <button type="button" id="SaveBM" class="btn btn-success rounded-pill px-4 text-white">
                            <i class="fa fa-save me-1"></i> Simpan Perubahan
                        </button>
                        <button type="button" id="DeleteBM" class="btn btn-danger rounded-pill px-3 text-white">
                            <i class="fa fa-trash me-1"></i> Hapus Baris
                        </button>
                        <button type="button" id="RefreshBM" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="fa fa-sync me-1"></i> Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TAB TARIF TER BULANAN PP 58/2023 -->
        <div class="tab-pane fade" id="ter-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-success">Tabel Tarif Efektif Rata-Rata (TER) Bulanan</h5>
                        <small class="text-muted">Sesuai PP 58/2023 & PMK 168/2023. Dapat disesuaikan sewaktu-waktu jika ada revisi aturan pemerintah.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-semibold text-muted mb-0">Kategori TER:</label>
                        <select id="terCategoryFilter" class="form-select form-select-sm rounded-pill" style="width: 140px;" onchange="loadTerRates()">
                            <option value="A">Kategori A</option>
                            <option value="B">Kategori B</option>
                            <option value="C">Kategori C</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-3 small">
                        <strong>Keterangan Kategori:</strong>
                        <ul class="mb-0 ps-3">
                            <li><strong>Kategori A:</strong> TK/0 (54 jt), TK/1 (58.5 jt), K/0 (58.5 jt)</li>
                            <li><strong>Kategori B:</strong> TK/2 (63 jt), TK/3 (67.5 jt), K/1 (63 jt), K/2 (67.5 jt)</li>
                            <li><strong>Kategori C:</strong> K/3 (72 jt)</li>
                        </ul>
                    </div>
                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover align-middle table-sm border" id="terRatesTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Kategori</th>
                                    <th>Batas Bawah Bruto (Rp)</th>
                                    <th>Batas Atas Bruto (Rp)</th>
                                    <th class="text-center" style="width: 130px;">Tarif TER (%)</th>
                                    <th>Dasar Hukum</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="terRatesBody">
                                <tr><td colspan="7" class="text-center py-4 text-muted">Memuat data tarif TER...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TAB TARIF PROGRESIF PASAL 17 UU HPP -->
        <div class="tab-pane fade" id="pasal17-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-dark">Lapisan Tarif Progresif PPh 21 Tahunan (Pasal 17 UU HPP)</h5>
                    <small class="text-muted">Digunakan untuk rekonsiliasi masa pajak terakhir (Desember) atau karyawan berhenti bekerja / resign.</small>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border" id="bracketsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 80px;">No</th>
                                    <th>Lapisan PKP Tahunan</th>
                                    <th>Batas Bawah PKP (Rp)</th>
                                    <th>Batas Atas PKP (Rp)</th>
                                    <th class="text-center" style="width: 150px;">Tarif Pajak (%)</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="bracketsBody">
                                <tr><td colspan="6" class="text-center py-4 text-muted">Memuat lapisan tarif progresif...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. TAB SETUP DEDUCTION DINAMIS -->
        <div class="tab-pane fade" id="deduction-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-danger">Parameter Pemotongan Gaji (Deduction Setup)</h5>
                    <small class="text-muted">Konfigurasi formula potongan ketidakhadiran (Unpaid Leave / Alpha), Keterlambatan, BPJS TK, dan BPJS Kesehatan.</small>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3" id="deductionConfigContainer">
                        <div class="col-12 text-center py-4 text-muted">Memuat parameter potongan...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Tarif TER -->
    <div class="modal fade" id="modalEditTer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Tarif TER</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_ter_id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori</label>
                        <input type="text" id="edit_ter_cat" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Batas Bawah Bruto (Rp)</label>
                        <input type="number" id="edit_ter_min" class="form-control" step="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Batas Atas Bruto (Rp - Kosongkan jika tanpa batas)</label>
                        <input type="number" id="edit_ter_max" class="form-control" step="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tarif TER (%)</label>
                        <input type="number" id="edit_ter_rate" class="form-control" step="0.01">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveTerRate()">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

<script>
var TaxList = new dhtmlXGridObject('TaxList');
TaxList.setImagePath("./dhtmlx/imgs/");
TaxList.setHeader("No,Status PTKP,Keterangan,Nilai PTKP (Rp),Kategori TER,Status Nikah,Jml Anak,Created By,Created Date,Modified By,Modified Date");
TaxList.setInitWidths("60,110,220,130,110,110,90,110,120,110,120");
TaxList.setColAlign("center,center,left,right,center,center,center,left,center,left,center");
TaxList.setColTypes("ro,ed,ed,edn,coro,coro,edn,ro,ro,ro,ro");
TaxList.setColSorting("int,str,str,int,str,str,int,str,str,str,str");
TaxList.setEditable(true);
TaxList.setNumberFormat("0,000", 3, ".", ",");
TaxList.setSkin("dhx_skyblue");

var PTaxList = new dataProcessor("payroll-tax-update");
PTaxList.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
PTaxList.setUpdateMode("Off");
PTaxList.init(TaxList);
TaxList.init();

TaxList.getCombo(4).put("A", "Kategori A");
TaxList.getCombo(4).put("B", "Kategori B");
TaxList.getCombo(4).put("C", "Kategori C");
TaxList.getCombo(5).put("1", "MARRIED");
TaxList.getCombo(5).put("0", "NOT MARRIED");

Reload_TaxList();

function Reload_TaxList() {
    TaxList.clearAndLoad("xml-payroll-tax");
}

$("#SaveBM").click(function(){
    PTaxList.sendData();
    alert("Perubahan Master PTKP berhasil disimpan!");
    setTimeout(function(){
        Reload_TaxList();
    }, 1500);
});

$("#DeleteBM").click(function(){
    if (confirm("Yakin ingin menghapus baris yang dipilih?")) {
        TaxList.deleteSelectedRows();
        PTaxList.sendData();
        Reload_TaxList();
    }
});

$("#AddBM").click(function(){
    var idbi = TaxList.uid();
    TaxList.addRow(idbi, [TaxList.getRowsNum() + 1, 'TK/0', 'Penghasilan Tidak Kena Pajak', 54000000, 'A', '0', 0, '{{ Auth::user() ? Auth::user()->username : "SYSTEM" }}', ''], 0);
    TaxList.selectRow(0);
});

$("#RefreshBM").click(function(){
    Reload_TaxList();
});

// =========================================================================
// AJAX HANDLERS UNTUK TER, PASAL 17 & DEDUCTION SETUP
// =========================================================================

function formatRupiah(num) {
    if (num === null || num === undefined || num === '') return '-';
    return Number(num).toLocaleString('id-ID');
}

function loadTerRates() {
    var cat = $("#terCategoryFilter").val() || 'A';
    $("#terRatesBody").html('<tr><td colspan="7" class="text-center py-4 text-muted">Memuat data tarif TER Kategori ' + cat + '...</td></tr>');

    $.get('/api/payroll/tax-ter-rates?category=' + cat, function(res) {
        if (res.success && res.data.length > 0) {
            var html = '';
            $.each(res.data, function(idx, item) {
                var maxLabel = item.max_gross ? formatRupiah(item.max_gross) : '<span class="badge bg-secondary text-white">Tak Terbatas</span>';
                html += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td><span class="badge bg-primary text-white">Kategori ' + item.category + '</span></td>' +
                    '<td>Rp ' + formatRupiah(item.min_gross) + '</td>' +
                    '<td>' + (item.max_gross ? 'Rp ' + maxLabel : maxLabel) + '</td>' +
                    '<td class="text-center fw-bold text-success">' + Number(item.ter_rate_percent).toFixed(2) + ' %</td>' +
                    '<td class="small text-muted">' + (item.legal_basis || 'PP 58/2023') + '</td>' +
                    '<td class="text-center">' +
                        '<button class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" onclick=\'openEditTer(' + JSON.stringify(item) + ')\'>' +
                            '<i class="fa fa-edit"></i> Edit' +
                        '</button>' +
                    '</td>' +
                '</tr>';
            });
            $("#terRatesBody").html(html);
        } else {
            $("#terRatesBody").html('<tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data tarif TER. Silakan jalankan seeder.</td></tr>');
        }
    });
}

function openEditTer(item) {
    $("#edit_ter_id").val(item.id);
    $("#edit_ter_cat").val('Kategori ' + item.category);
    $("#edit_ter_min").val(item.min_gross);
    $("#edit_ter_max").val(item.max_gross !== null ? item.max_gross : '');
    $("#edit_ter_rate").val(item.ter_rate_percent);
    var modal = new bootstrap.Modal(document.getElementById('modalEditTer'));
    modal.show();
}

function saveTerRate() {
    var id = $("#edit_ter_id").val();
    var minVal = $("#edit_ter_min").val();
    var maxVal = $("#edit_ter_max").val();
    var rateVal = $("#edit_ter_rate").val();

    $.ajax({
        url: '/api/payroll/tax-ter-rates/update',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            id: id,
            min_gross: minVal,
            max_gross: maxVal !== '' ? maxVal : null,
            ter_rate_percent: rateVal
        },
        success: function(res) {
            alert(res.message);
            bootstrap.Modal.getInstance(document.getElementById('modalEditTer')).hide();
            loadTerRates();
        },
        error: function(err) {
            alert('Gagal memperbarui tarif TER.');
        }
    });
}

function loadTaxBrackets() {
    $("#bracketsBody").html('<tr><td colspan="6" class="text-center py-4 text-muted">Memuat data lapisan tarif Pasal 17...</td></tr>');
    $.get('/api/payroll/tax-brackets', function(res) {
        if (res.success && res.data.length > 0) {
            var html = '';
            $.each(res.data, function(idx, item) {
                var maxLabel = item.max_amount ? 'Rp ' + formatRupiah(item.max_amount) : '<span class="badge bg-secondary text-white">Diatas Batas Lapisan IV</span>';
                html += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td class="fw-bold text-dark">' + item.bracket_name + '</td>' +
                    '<td>Rp ' + formatRupiah(item.min_amount) + '</td>' +
                    '<td>' + maxLabel + '</td>' +
                    '<td class="text-center">' +
                        '<div class="input-group input-group-sm justify-content-center" style="max-width: 120px; margin: 0 auto;">' +
                            '<input type="number" id="bracket_rate_' + item.id + '" class="form-control text-center fw-bold" step="0.5" value="' + item.rate_percent + '">' +
                            '<span class="input-group-text">%</span>' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-center">' +
                        '<button class="btn btn-sm btn-success rounded-pill px-3 py-1 text-white" onclick="saveBracket(' + item.id + ')">' +
                            '<i class="fa fa-save"></i> Simpan' +
                        '</button>' +
                    '</td>' +
                '</tr>';
            });
            $("#bracketsBody").html(html);
        }
    });
}

function saveBracket(id) {
    var rate = $("#bracket_rate_" + id).val();
    $.ajax({
        url: '/api/payroll/tax-brackets/update',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            id: id,
            rate_percent: rate
        },
        success: function(res) {
            alert(res.message);
            loadTaxBrackets();
        },
        error: function() {
            alert('Gagal menyimpan tarif lapisan.');
        }
    });
}

function loadDeductionConfigs() {
    $("#deductionConfigContainer").html('<div class="col-12 text-center py-4 text-muted">Memuat parameter potongan...</div>');
    $.get('/api/payroll/deduction-configs', function(res) {
        if (res.success && res.data.length > 0) {
            var html = '';
            $.each(res.data, function(idx, item) {
                html += '<div class="col-md-6">' +
                    '<div class="card border rounded-3 p-3 bg-light h-100">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<h6 class="fw-bold mb-0 text-dark">' + item.config_name + '</h6>' +
                            '<span class="badge bg-primary text-white">' + item.category + '</span>' +
                        '</div>' +
                        '<p class="small text-muted mb-3">' + (item.description || '-') + '</p>' +
                        '<div class="input-group">' +
                            '<input type="text" id="cfg_' + item.config_key + '" class="form-control" value="' + item.value_data + '">' +
                            '<span class="input-group-text">' + (item.unit || '') + '</span>' +
                            '<button class="btn btn-primary" onclick="saveDeductionConfig(\'' + item.config_key + '\')">' +
                                '<i class="fa fa-check"></i> Simpan' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });
            $("#deductionConfigContainer").html(html);
        }
    });
}

function saveDeductionConfig(key) {
    var val = $("#cfg_" + key).val();
    $.ajax({
        url: '/api/payroll/deduction-configs/update',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            config_key: key,
            value_data: val
        },
        success: function(res) {
            alert(res.message);
            loadDeductionConfigs();
        },
        error: function() {
            alert('Gagal menyimpan konfigurasi.');
        }
    });
}

$(document).ready(function() {
    loadTerRates();
    loadTaxBrackets();
    loadDeductionConfigs();
});
</script>
@endsection
