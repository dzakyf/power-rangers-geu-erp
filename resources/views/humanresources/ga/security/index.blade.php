@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .ga-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .ga-filter-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .ga-filter-group .form-control,
    .ga-filter-group .form-select {
        width: auto;
        min-width: 150px;
    }
    .ga-btn-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
</style>

<div class="main-content-header">
    <div>
        <h3 class="main-content-title tx-24 mg-b-5">GA Security & Visitor Management</h3>
        <ol class="breadcrumb mg-b-0">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-ticket-index') }}">GA Operation</a></li>
            <li class="breadcrumb-item active" aria-current="page">Security & Visitor</li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="row mb-3">
            <div class="col-12">
                <ul class="nav nav-pills" id="securityTab" role="tablist" style="gap: 0.5rem;">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="visitor-tab" data-bs-toggle="pill" data-toggle="pill" href="#tabVisitor" role="tab">Buku Tamu (Visitor Log)</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="incident-tab" data-bs-toggle="pill" data-toggle="pill" href="#tabIncident" role="tab">Laporan Insiden Keamanan</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="securityTabContent">
            <!-- TAB 1: VISITOR LOG -->
            <div class="tab-pane fade show active" id="tabVisitor" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="ga-toolbar">
                            <div class="ga-filter-group">
                                <label class="mb-0 fw-bold">Tanggal:</label>
                                <input type="date" id="filterVisitorDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                <label class="mb-0 fw-bold ms-2">Status:</label>
                                <select id="filterVisitorStatus" class="form-control form-control-sm">
                                    <option value="">Semua Status</option>
                                    <option value="IN" selected>IN (Masih di Lokasi)</option>
                                    <option value="OUT">OUT (Sudah Keluar)</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-info text-white" onclick="reloadVisitorGrid()">
                                    <i class="mdi mdi-filter"></i> Filter
                                </button>
                            </div>
                            <div class="ga-btn-group">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalVisitorEntry" onclick="showSecurityModal('#modalVisitorEntry')">
                                    <i class="mdi mdi-account-plus"></i> Catat Tamu Masuk
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="checkOutVisitor()">
                                    <i class="mdi mdi-account-arrow-right"></i> Check-Out Tamu
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="reloadVisitorGrid()">
                                    <i class="mdi mdi-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="gridVisitors" style="width:100%;height:380px;background-color:white;"></div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: INCIDENT REPORT -->
            <div class="tab-pane fade" id="tabIncident" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="ga-toolbar">
                            <h5 class="card-title mb-0">Insiden Keamanan & Investigasi</h5>
                            <div class="ga-btn-group">
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalRecordIncident" onclick="showSecurityModal('#modalRecordIncident')">
                                    <i class="mdi mdi-alert-circle"></i> Laporkan Insiden Baru
                                </button>
                                <button type="button" class="btn btn-sm btn-warning text-dark" onclick="followUpIncident()">
                                    <i class="mdi mdi-hammer"></i> Buat Tiket Perbaikan
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="reloadIncidentGrid()">
                                    <i class="mdi mdi-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="gridIncidents" style="width:100%;height:380px;background-color:white;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Tamu Masuk -->
<div class="modal fade" id="modalVisitorEntry" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formVisitorEntry" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Form Tamu Masuk (Buku Tamu)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Nama Pengunjung <span class="text-danger">*</span></label>
                        <input type="text" name="Visitor_Name" class="form-control" placeholder="Nama lengkap tamu" required>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>No. Identitas (KTP / SIM / Passport)</label>
                        <input type="text" name="Identity_Number" class="form-control" placeholder="Nomor identitas fisik">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Institusi / Perusahaan Asal</label>
                        <input type="text" name="Institution" class="form-control" placeholder="Contoh: PT Mitra Sejahtera / Tamu Pribadi">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Nomor Telepon / WhatsApp</label>
                        <input type="text" name="Phone_Number" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Nama Karyawan yang Dituju (Host) <span class="text-danger">*</span></label>
                        <input type="text" name="Host_Name" class="form-control" placeholder="Nama host yang ditemui" required>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Departemen Host</label>
                        <input type="text" name="Host_Department" class="form-control" placeholder="Contoh: Finance / HCGA / IT">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label>No Kartu Tamu / Badge Pass</label>
                        <input type="text" name="Badge_Number" class="form-control" placeholder="Contoh: PASS-05">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label>No Polisi Kendaraan</label>
                        <input type="text" name="Vehicle_Number" class="form-control" placeholder="Contoh: B 1234 ABC">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label>Pos Penjagaan</label>
                        <input type="text" name="Post_Name" class="form-control" value="Pos Gerbang Utama">
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label>Keperluan Kunjungan <span class="text-danger">*</span></label>
                    <textarea name="Purpose" class="form-control" rows="2" placeholder="Contoh: Rapat koordinasi proyek dan pengiriman dokumen penawaran" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Catat Masuk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Laporkan Insiden -->
<div class="modal fade" id="modalRecordIncident" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formRecordIncident" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Form Laporan Insiden Pengamanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Judul Insiden / Ringkasan Singkat <span class="text-danger">*</span></label>
                    <input type="text" name="Title" class="form-control" placeholder="Contoh: Kerusakan Pagar Sisi Timur / Kehilangan Helm Karyawan" required>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label>Jenis Insiden <span class="text-danger">*</span></label>
                        <select name="Incident_Type" class="form-control" required>
                            <option value="THEFT">Pencurian / Kehilangan</option>
                            <option value="PROPERTY_DAMAGE">Kerusakan Properti / Fasilitas</option>
                            <option value="INTRUSION">Penyusupan / Akses Tak Dikenal</option>
                            <option value="FIGHT">Perselisihan / Kegaduhan</option>
                            <option value="SAFETY_HAZARD">Potensi Bahaya K3 / Keselamatan</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label>Tingkat Keparahan (Severity)</label>
                        <select name="Severity" class="form-control">
                            <option value="LOW">LOW (Rendah)</option>
                            <option value="MEDIUM" selected>MEDIUM (Sedang)</option>
                            <option value="HIGH">HIGH (Tinggi)</option>
                            <option value="CRITICAL">CRITICAL (Kritis / Darurat)</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label>Tingkat Kerahasiaan</label>
                        <select name="Confidentiality" class="form-control">
                            <option value="NORMAL" selected>NORMAL (Operasional GA)</option>
                            <option value="CONFIDENTIAL">CONFIDENTIAL (Terbatas)</option>
                            <option value="STRICTLY_CONFIDENTIAL">STRICTLY_CONFIDENTIAL (Hanya Head)</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label>Tanggal Kejadian</label>
                        <input type="date" name="Incident_Date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label>Waktu / Jam Kejadian</label>
                        <input type="time" name="Incident_Time" class="form-control" value="{{ date('H:i') }}">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label>Lokasi Spesifik</label>
                        <input type="text" name="Specific_Location" class="form-control" placeholder="Contoh: Area Parkir Motor Barat">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Deskripsi / Kronologi Kejadian <span class="text-danger">*</span></label>
                    <textarea name="Description" class="form-control" rows="3" placeholder="Rincikan fakta kejadian, urutan waktu, dan temuan fisik" required></textarea>
                </div>
                <div class="form-group mb-3">
                    <label>Pihak / Saksi yang Terlibat</label>
                    <input type="text" name="Involved_Parties" class="form-control" placeholder="Nama saksi, korban, atau pihak terkait">
                </div>
                <div class="form-group mb-2">
                    <label>Tindakan Awal yang Diambil Sekuriti</label>
                    <textarea name="Initial_Action" class="form-control" rows="2" placeholder="Contoh: Pengamanan barang bukti, pemasangan garis pembatas, pengecekan CCTV"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Laporkan Insiden</button>
            </div>
        </form>
    </div>
</div>

<script>
var gridVisitors, gridIncidents;

function showSecurityModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (typeof $ !== 'undefined' && $.fn.modal) {
        $(el).modal('show');
    }
}

function hideSecurityModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        var instance = window.bootstrap.Modal.getInstance(el);
        if (instance) instance.hide();
    }
    if (typeof $ !== 'undefined' && $.fn.modal) {
        $(el).modal('hide');
    }
}

function initSecurityGrids() {
    gridVisitors = new dhtmlXGridObject('gridVisitors');
    gridVisitors.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridVisitors.setHeader("No,No Kunjungan,Nama Tamu,Instansi,Host / Tujuan,Keperluan,No Kartu,Jam Masuk,Jam Keluar,Status");
    gridVisitors.setInitWidths("40,140,160,150,140,200,90,120,120,80");
    gridVisitors.setColAlign("center,left,left,left,left,left,center,center,center,center");
    gridVisitors.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    gridVisitors.setColSorting("int,str,str,str,str,str,str,str,str,str");
    gridVisitors.setSkin("dhx_skyblue");
    gridVisitors.init();
    reloadVisitorGrid();

    gridIncidents = new dhtmlXGridObject('gridIncidents');
    gridIncidents.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridIncidents.setHeader("No,No Insiden,Judul,Jenis,Keparahan,Kerahasiaan,Waktu Kejadian,Lokasi,Status,Deskripsi");
    gridIncidents.setInitWidths("40,140,180,120,90,110,130,100,100,250");
    gridIncidents.setColAlign("center,left,left,center,center,center,center,left,center,left");
    gridIncidents.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    gridIncidents.setColSorting("int,str,str,str,str,str,str,str,str,str");
    gridIncidents.setSkin("dhx_skyblue");
    gridIncidents.init();
    reloadIncidentGrid();
}

function reloadVisitorGrid() {
    var date = $('#filterVisitorDate').val();
    var status = $('#filterVisitorStatus').val();
    gridVisitors.clearAndLoad("{{ url('hrd-ga-security-visitor-data') }}?date=" + encodeURIComponent(date) + "&status=" + encodeURIComponent(status));
}

function reloadIncidentGrid() {
    gridIncidents.clearAndLoad("{{ url('hrd-ga-security-incident-data') }}");
}

function checkOutVisitor() {
    var id = gridVisitors.getSelectedRowId();
    if (!id) {
        alert('Pilih baris pengunjung yang akan keluar terlebih dahulu.');
        return;
    }
    if (confirm('Catat pengunjung ini keluar sekarang?')) {
        $.post("{{ url('hrd-ga-security-visitor-exit') }}", {
            _token: "{{ csrf_token() }}",
            id: id
        }, function(resp) {
            alert(resp.message);
            reloadVisitorGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal check-out.');
        });
    }
}

function followUpIncident() {
    var id = gridIncidents.getSelectedRowId();
    if (!id) {
        alert('Pilih baris insiden yang memerlukan tindak lanjut teknis terlebih dahulu.');
        return;
    }
    var action = prompt('Masukkan instruksi perbaikan fisik / teknis yang dibutuhkan:');
    if (action !== null && action.trim() !== '') {
        $.post("{{ url('hrd-ga-security-incident-ticket') }}", {
            _token: "{{ csrf_token() }}",
            incident_id: id,
            Action_Needed: action
        }, function(resp) {
            alert(resp.message);
            reloadIncidentGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal membuat tiket.');
        });
    }
}

$(document).ready(function() {
    initSecurityGrids();

    $('#securityTab a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('#securityTab .nav-link').removeClass('active');
        $(this).addClass('active');
        $('#securityTabContent .tab-pane').removeClass('show active');
        $(target).addClass('show active');

        setTimeout(function() {
            if (target === '#tabVisitor' && gridVisitors) gridVisitors.setSizes();
            if (target === '#tabIncident' && gridIncidents) gridIncidents.setSizes();
        }, 50);
    });

    $('#formVisitorEntry').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-security-visitor-entry') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideSecurityModal('#modalVisitorEntry');
            $('#formVisitorEntry')[0].reset();
            reloadVisitorGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal mencatat tamu.');
        });
    });

    $('#formRecordIncident').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-security-incident-save') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideSecurityModal('#modalRecordIncident');
            $('#formRecordIncident')[0].reset();
            reloadIncidentGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal melaporkan insiden.');
        });
    });
});
</script>
@endsection

