@extends('layout.mainlayout-hrd')
@section('content-wrapper')
@csrf

<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Human Resources</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Recruitment</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Dashboard Progress</span>
        </li>
    </ol>
</div>

<!-- Info Box: Alur Recruitment -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center">
                            <div class="ml-4">
                                <h5 class="font-weight-bold text-primary">📋 Alur Recruitment HCGA</h5>
                                <small class="text-muted mb-0">
                                    <span class="badge badge-info">1. Input Candidate</span> → 
                                    <span class="badge badge-warning">2. Verifikasi Dokumen</span> → 
                                    <span class="badge badge-info">3. Psikotest</span> → 
                                    <span class="badge badge-warning">4. Interview HR</span> → 
                                    <span class="badge badge-info">5. Interview User</span> → 
                                    <span class="badge badge-warning">6. Tes Teknik</span> → 
                                    <span class="badge badge-info">7. MCU</span> → 
                                    <span class="badge badge-success">8. Offering Letter</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pivot Dashboard Card -->
    <div class="col-12 mb-3">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white m-2">
                <h5 class="card-title mb-0">📊 Dashboard Progress (Pivot)</h5>
            </div>
            <div class="card-body">
                <div class="w-100" id="pivottrial"></div>
                <div id="RecruitmentPivotGrid" style="width:auto;height:300px; background-color:white;overflow:hidden;border:1px solid #e2e3e5;border-radius:5px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Detail PTK Card (Grid Utama) -->
    <div class="col-12 mb-3">
        <div class="card shadow border-0" id="detailPTKCard" style="display: none;">
            <div class="card-header bg-primary text-white m-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">📋 Daftar Candidate (Recruitment Lists)</h5>
                <div class="badge badge-light">
                    <i class="fa fa-info-circle"></i> Klik 2x pada baris untuk melihat detail
                </div>
            </div>
            <div class="card-body p-0">
                <div id="RecruitmentLists" style="width:auto;height:550px; background-color:white;overflow:hidden;border:1px solid #e2e3e5;border-radius:5px 5px 0 0;"></div>
                <div class="p-2 border-top" style="background-color:#f8f9fa;">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-warning btn-sm" onclick="RecruitmentLists.toExcel('./export/generate.php');">
                            <i class="fa fa-file-export"></i> Export Excel
                        </button>
                    </div>
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshdata();">
                            <i class="fa fa-sync"></i> Refresh
                        </button>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger btn-sm" onclick="delsRecruitment();">
                            <i class="fa fa-times-circle"></i> Cancel Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="divlistRecruitment"></div>

<!-- Custom CSS - Modern Grid Styling -->
<style>
    /* Status indicator colors */
    .grid-status-pending {
        background-color: #e2e3e5 !important;
        color: #383d41 !important;
        font-weight: normal;
    }
    .grid-status-scheduled {
        background-color: #fff3cd !important;
        color: #856404 !important;
        font-weight: normal;
    }
    .grid-status-process {
        background-color: #cce5ff !important;
        color: #004085 !important;
        font-weight: normal;
    }
    .grid-status-done {
        background-color: #d4edda !important;
        color: #155724 !important;
        font-weight: bold;
    }
    .grid-status-reject {
        background-color: #f8d7da !important;
        color: #721c24 !important;
        font-weight: bold;
    }
    .grid-status-hired {
        background-color: #28a745 !important;
        color: #ffffff !important;
        font-weight: bold;
    }
    .verify-link {
        color: #699;
        text-decoration: none;
        font-weight: bold;
    }
    .verify-link:hover {
        text-decoration: underline;
    }

    /* Modern card styling */
    .recruitment-card {
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: box-shadow 0.2s ease;
    }
    .recruitment-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }

    /* Progress bar inside cells */
    .progress-bar-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 55px;
    }
    .progress-bar {
        width: 100%;
        height: 6px;
        border-radius: 3px;
        margin-top: 2px;
    }
    .progress-bar-full  { background-color: #28a745; }
    .progress-bar-half  { background-color: #ffc107; }
    .progress-bar-empty { background-color: #adb5bd; }

    /* Grid cell improvements */
    .dhtmlx_grid_cell {
        padding: 4px 8px !important;
        font-size: 12px;
    }

    /* Active/hover row */
    .dhtmlx_grid_cell.hover {
        background-color: #e8f4fd !important;
    }

    /* Header style */
    .dhtmlx_grid_head {
        background-color: #3b72a0 !important;
        color: white !important;
        font-weight: bold !important;
    }

    /* Row number styling */
    .dhtmlx_grid_row:nth-child(even) td {
        background-color: #f8f9fa !important;
    }
</style>

<script>
/**
 * Recruitment Management Grid - Enhanced Version
 * 
 * Alur Recruitment yang benar:
 * 1. Input Candidate → 2. Verifikasi Dokumen → 3. Psikotest → 
 * 4. Interview HR → 5. Interview User → 6. Tes Teknik → 
 * 7. MCU → 8. Offering Letter
 * 
 * Visual Indicator:
 * - Pending/Abai   : Abu-abu (#e2e3e5)
 * - Scheduled/Proses: Kuning (#fff3cd)
 * - Process/Baru   : Biru (#cce5ff)
 * - Selesai/Pass   : Hijau (#d4edda)
 * - Diterima/Hired : Hijau Gelap (#28a745)
 * - Ditolak/Failed : Merah (#f8d7da)
 */

// Status colors configuration
var STATUS_COLORS = {
    'ACCEPT':  { bg: '#d4edda', text: '#155724', label: 'Selesai' },
    'PENDING': { bg: '#e2e3e5', text: '#383d41', label: 'Pending' },
    'REJECT':  { bg: '#f8d7da', text: '#721c24', label: 'Ditolak' },
    'SEEK':    { bg: '#fff3cd', text: '#856404', label: 'Process' },
    'CREATED': { bg: '#e2e3e5', text: '#383d41', label: 'Baru' },
    'UPLOADED': { bg: '#fff3cd', text: '#856404', label: 'Uploaded' },
    'PSIKOTEST': { bg: '#cce5ff', text: '#004085', label: 'Psikotest' },
    'INTERVIEW_HR': { bg: '#cce5ff', text: '#004085', label: 'Interview HR' },
    'INTERVIEW_USER': { bg: '#cce5ff', text: '#004085', label: 'Interview User' },
    'TECHNICAL_TEST': { bg: '#cce5ff', text: '#004085', label: 'Tes Teknik' },
    'MCU': { bg: '#cce5ff', text: '#004085', label: 'MCU' },
    'OFFERING': { bg: '#d4edda', text: '#155724', label: 'Offering' },
    'HIRED': { bg: '#28a745', text: '#ffffff', label: 'Hired' },
    'BUSINESS_REVIEW': { bg: '#fff3cd', text: '#856404', label: 'Review' },
    'CLOSED': { bg: '#d4edda', text: '#155724', label: 'Closed' },
    'OFFER DECLINED': { bg: '#f8d7da', text: '#721c24', label: 'Declined' },
    'INVITATION DECLINED': { bg: '#f8d7da', text: '#721c24', label: 'Inv. Declined' },
    'CANCELED': { bg: '#e2e3e5', text: '#383d41', label: 'Canceled' }
};

/**
 * Dapatkan warna untuk cell status berdasarkan value
 */
function getStatusCellStyle(statusValue) {
    var color = STATUS_COLORS[statusValue];
    if (!color) {
        color = STATUS_COLORS['PENDING'];
    }
    return 'background-color: ' + color.bg + '; color: ' + color.text + ';';
}

/**
 * Click handler untuk klik ganda pada baris
 */
function RecruitmentListsKlik() {
    var RecruitmentListsID = RecruitmentLists.getSelectedId();
    
    if (RecruitmentListsID !== '' && RecruitmentListsID !== null) {
        var splitID = RecruitmentListsID.split('|');
        var recruitmentId = splitID[0];
        
        // Kolom 10: Seleksi Administrasi -> link ke verifikasi dokumen jika PENDING
        var docStatus = RecruitmentLists.cells(RecruitmentListsID, 10).getValue();
        if (docStatus === '1') {
            LoadM('divlistRecruitment', 'recruitment-document-approval-detail?ID=' + recruitmentId);
            return;
        }
        
        // Kolom 10b: Jika Document_Status PENDING -> link ke halaman verifikasi dokumen
        var docPending = RecruitmentLists.cells(RecruitmentListsID, 10).getAttribute('data-status');
        if (docPending === 'PENDING') {
            LoadM('divlistRecruitment', 'hrd-recruitment-verify-document/' + recruitmentId);
            return;
        }
        
        // Kolom 11: Psikotest -> link ke test result
        var psyStatus = RecruitmentLists.cells(RecruitmentListsID, 11).getValue();
        if (psyStatus === '1') {
            LoadM('divlistRecruitment', 'recruitment-test-result-detail?ID=' + recruitmentId);
            return;
        }
        
        // Kolom 13: Interview HR -> link ke interview detail
        var hrStatus = RecruitmentLists.cells(RecruitmentListsID, 13).getValue();
        if (hrStatus === '1') {
            LoadM('divlistRecruitment', 'recruitment-interview-detail?ID=' + recruitmentId);
            return;
        }
        
        // Kolom 16: Interview User -> link ke candidate analysis
        var userStatus = RecruitmentLists.cells(RecruitmentListsID, 16).getValue();
        if (userStatus === '1') {
            LoadM('divlistRecruitment', 'recruitment-candidate-analysis-detail?ID=' + recruitmentId);
            return;
        }
        
        // Default: buka halaman detail umum
        LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + recruitmentId);
    }
    return true;
}

/**
 * Hapus candidate yang dipilih (decline invitation)
 */
function delsRecruitment() {
    dhtmlx.confirm({
        title: 'Cancel Recruitment?',
        text: 'Apakah Anda yakin ingin membatalkan recruitment candidate yang dipilih?',
        ok: 'Yes, Cancel',
        cancel: 'Batal',
        callback: function(result) {
            if (result) {
                RecruitmentLists.deleteSelectedRows();
                PRecruitmentLists.sendData();
                alert('Data Successfully Deleted!');
                setTimeout(function() {
                    refreshdata();
                }, 1000);
            }
        }
    });
}

// ==========================================
// Pivot Grid (Dashboard Summary)
// ==========================================

RecruitmentPivot = new dhtmlXGridObject('RecruitmentPivotGrid');
RecruitmentPivot.setImagePath("./dhtmlx/imgs/");

// Header pivot sesuai urutan tahapan yang benar
RecruitmentPivot.setHeader(
    "PTK Number,Current Approver By,Seleksi<br>Administrasi,Psikotes,Interview<br>HR,Interview<br>User,Tes<br>Teknik,MCU,Offering<br>Letter,Keterangan"
);

// Header attachment
RecruitmentPivot.attachHeader(
    "#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
);

RecruitmentPivot.setInitWidths(
    "150,150,90,80,90,90,80,70,90,250"
);

RecruitmentPivot.setColAlign(
    "left,left,center,center,center,center,center,center,center,left"
);

RecruitmentPivot.setColTypes(
    "ro,ro,ro,ro,ro,ro,ro,ro,ro,ro"
);

RecruitmentPivot.setColSorting(
    "str,str,int,int,int,int,int,int,int,str"
);

RecruitmentPivot.enableKeyboardSupport(true);
RecruitmentPivot.setEditable(false);
RecruitmentPivot.setSkin("dhx_skyblue");

// Double click pada pivot row
RecruitmentPivot.attachEvent("onRowDblClicked", function(id, ind) {
    document.getElementById('detailPTKCard').style.display = '';
    filterByRequisition(id);
});

RecruitmentPivot.init();
RecruitmentPivot.load('xml-hrd-recruitment-pivot');

/**
 * Inisialisasi dhtmlxGrid utama (RecruitmentLists)
 */
RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
RecruitmentLists.setImagePath("./dhtmlx/imgs/");

// Header urutan yang benar sesuai alur recruitment
RecruitmentLists.setHeader(
    "No.,Recruitment Type,Name,Gender,PO No,Requisition,Last Education,Education Major,Recruiter," +
    "Seleksi<br>Administrasi,Psikotest,Decline<br>Invitation,Interview<br>HR,Pending,Rejected,Interview<br>User,Candidate<br>Analysis,Decline<br>Offered,Hired"
);

RecruitmentLists.setInitWidths(
    "50,90,120,70,80,150,90,90,90," +
    "80,60,60,60,50,50,60,60,50,50"
);

RecruitmentLists.setColAlign(
    "center,left,left,center,center,center,center,center,center," +
    "center,center,center,center,center,center,center,center,center,center"
);

RecruitmentLists.setColTypes(
    "ro,ro,ro,ro,ro,ro,ro,ro,ro," +
    "ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str"
);

RecruitmentLists.setColSorting(
    "int,str,str,str,str,str,str,str,str," +
    "str,str,str,str,str,str,str,str,str,str"
);

RecruitmentLists.enableKeyboardSupport(true);
RecruitmentLists.setEditable(false);
RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
RecruitmentLists.setSkin("dhx_skyblue");

// Data processor untuk CRUD operations
PRecruitmentLists = new dataProcessor("recruitment-data-submit");
PRecruitmentLists.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
});
PRecruitmentLists.setTransactionMode("POST", true);
PRecruitmentLists.setUpdateMode("Off");
PRecruitmentLists.init(RecruitmentLists);

// Inisialisasi grid
RecruitmentLists.init();
refreshdata();

/**
 * Refresh data dari server
 */
function refreshdata() {
	let id = RecruitmentPivot.getSelectedId();
	if (id == null) {
		RecruitmentLists.clearAndLoad('xml-hrd-recruitment-management');
	}else {
		RecruitmentLists.clearAndLoad('xml-hrd-recruitment-management?Requisition_ID=' + id);
	}
    RecruitmentLists.sortRows(0, "str", "des");
    
    // Tampilkan loading indicator
    document.querySelector('#RecruitmentLists').style.opacity = '0.5';
    setTimeout(function() {
        document.querySelector('#RecruitmentLists').style.opacity = '1';
    }, 500);
}

/**
 * Filter data berdasarkan Requisition ID
 */
function filterByRequisition(id) {
    setTimeout(function() {
        RecruitmentLists.setSizes();
        RecruitmentLists.clearAndLoad('xml-hrd-recruitment-management?Requisition_ID=' + encodeURIComponent(id));
        RecruitmentLists.sortRows(0, "str", "des");
    }, 50);
}

// Auto-refresh setiap 60 detik
setInterval(function() {
    refreshdata();
    RecruitmentPivot.load('xml-hrd-recruitment-pivot');
}, 60000);

</script>

<!-- Required Libraries -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/tips_data.min.js"></script>
<script>
    var rawJson = `{!! json_encode($rows ? $rows->values()->toArray() : []) !!}`;
    var parsedJson = JSON.parse(rawJson);
    
    if (parsedJson.length > 0) {
        var derivers = $.pivotUtilities ? $.pivotUtilities.derivers : null;
        $('#pivottrial').pivotUI(parsedJson, {
            rows: ["no_ptk", "Date"],
            cols: ["Stage"],
            aggregatorName: "Sum",
            vals: ["Count"],
            rendererName: "Table"
        });
    } else {
        $('#pivottrial').html('<div class="text-center text-muted p-4"><i class="fa fa-info-circle fa-2x mb-2"></i><br>Tidak ada data untuk ditampilkan di pivot table</div>');
    }
</script>
@endsection
