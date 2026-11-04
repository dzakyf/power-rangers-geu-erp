@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fa fa-fingerprint me-2"></i>Attendance &amp; Timesheet Entry
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Attendance Log</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <a href="{{ route('attendance.mobile.index') }}" target="_blank" class="btn btn-sm rounded-pill shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-mobile-screen-button me-1"></i> Preview Mobile Webview
            </a>
        </div>
    </div> 
    <!-- End Main Content Header -->

    <!-- Info Stats & Overview -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-primary border-4">
                <small class="text-muted d-block">Total Periode Aktif</small>
                <h5 class="fw-bold mb-0 text-dark" id="stat-total-period">Memuat...</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-success border-4">
                <small class="text-muted d-block">Status Kehadiran Hari Ini</small>
                <h5 class="fw-bold mb-0 text-success"><i class="fa fa-circle-check me-1"></i>Aktif</h5>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block">Panduan Cepat</small>
                        <span class="small text-secondary">Pilih baris pada tabel periode, lalu klik <strong>Buka Rincian Harian</strong> untuk mengisi jam masuk/pulang.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Grid Periode Absensi -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white p-2 rounded-3 me-2">
                    <i class="fa fa-calendar-alt"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Daftar Periode Absensi Karyawan</h6>
                    <small class="text-muted">Kelola rekap log absensi per bulan</small>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" id="AddBM" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa fa-plus-circle me-1"></i> Buat Periode Baru
                </button>
                <button type="button" id="SETLIST" class="btn btn-info text-white btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa fa-folder-open me-1"></i> Buka Rincian Harian
                </button>
                <button type="button" id="RefreshBM" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa fa-rotate me-1"></i> Refresh
                </button>
                <button type="button" id="ekselPODListDet" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" onclick="AttendantEntry.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="DeleteBM" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa fa-trash-can me-1"></i> Batalkan Periode
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="AttendantEntry" style="width:100%;height:320px;background-color:white;border-radius:8px;overflow:hidden;border:1px solid #e9ecef;"></div>
        </div>
    </div>

    <!-- Container Rincian Harian (Loaded via Ajax LoadM) -->
    <div id="AttendantEntryDiv"></div>

<script>
AttendantEntry = new dhtmlXGridObject('AttendantEntry');
AttendantEntry.setImagePath("./dhtmlx/imgs/");
AttendantEntry.setHeader("No,Task No,NIK,Nama Karyawan,Periode,Status,Dibuat Oleh,Tanggal Dibuat");
AttendantEntry.setInitWidths("60,160,140,220,110,120,130,*");
AttendantEntry.setColAlign("center,left,left,left,center,center,left,center");
AttendantEntry.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
AttendantEntry.setColSorting("int,str,str,str,str,str,str,str");
AttendantEntry.setEditable(false);
AttendantEntry.setSkin("dhx_skyblue");

PAttendantEntry = new dataProcessor("attendant-entry-update");
PAttendantEntry.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
PAttendantEntry.setUpdateMode("Off");
PAttendantEntry.init(AttendantEntry);
AttendantEntry.init();

Reload_AttendantEntry();

function Reload_AttendantEntry(){
    AttendantEntry.clearAndLoad("xml-attendant-entry", function() {
        var count = AttendantEntry.getRowsNum();
        document.getElementById('stat-total-period').innerText = count + ' Periode';
    });
}

// Double click langsung buka rincian
AttendantEntry.attachEvent("onRowDblClicked", function(rId, cInd){
    var AttendantID = AttendantEntry.cells(rId, 1).getValue();
    if(AttendantID) {
        LoadM('AttendantEntryDiv', 'attendant-entry-lists?ID=' + AttendantID);
        $('html, body').animate({
            scrollTop: $("#AttendantEntryDiv").offset().top - 80
        }, 400);
    }
});

$("#DeleteBM").click(function(){
    var selectedId = AttendantEntry.getSelectedId();
    if(!selectedId) {
        alert("Pilih baris periode yang ingin dibatalkan terlebih dahulu.");
        return;
    }
    if(confirm("Apakah Anda yakin ingin membatalkan periode absensi terpilih?")) {
        AttendantEntry.deleteSelectedRows();
        PAttendantEntry.sendData();
        Reload_AttendantEntry();
    }
});

$("#AddBM").click(function(){
    $.blockUI({ message: '<h6><i class="fa fa-spinner fa-spin me-2"></i>Membuat Periode Absensi...</h6>' });
    var idbi = AttendantEntry.uid();
    AttendantEntry.addRow(idbi, [AttendantEntry.getRowsNum()+1, ''], 0);
    AttendantEntry.selectRow(0);
    PAttendantEntry.sendData();
    setTimeout(function(){
        $.unblockUI();
        Reload_AttendantEntry();
    }, 1500);
});

$("#RefreshBM").click(function(){
    Reload_AttendantEntry();        
});

$("#SETLIST").click(function(){
    var selectedId = AttendantEntry.getSelectedId();
    if(!selectedId) {
        alert("Silakan pilih baris periode pada tabel terlebih dahulu.");
        return;
    }
    var AttendantID = AttendantEntry.cells(selectedId, 1).getValue();
    LoadM('AttendantEntryDiv', 'attendant-entry-lists?ID=' + AttendantID);
    $('html, body').animate({
        scrollTop: $("#AttendantEntryDiv").offset().top - 80
    }, 400);
});
</script>

@endsection
