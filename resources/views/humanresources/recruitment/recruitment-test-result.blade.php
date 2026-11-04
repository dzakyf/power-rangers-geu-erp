@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Breadcrumb Header -->
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Test Result Archiving
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Test Result Archiving</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-file-lines me-1"></i> Archiving &amp; Test Monitoring
        </span>
    </div>
</div>

<!-- Card Container for Grid -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fa fa-table-list text-primary me-2"></i>Daftar Hasil Tes &amp; Kandidat
            </h6>
            <small class="text-muted">Klik ganda pada baris untuk membuka rincian arsip hasil tes kandidat di bawah tabel.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata()" title="Muat ulang data tabel">
                <i class="fa fa-rotate me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="bukaDetailTerpilih()" title="Buka detail baris yang dipilih">
                <i class="fa fa-folder-open me-1"></i> Buka Detail
            </button>
            <button type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" class="btn btn-sm btn-success text-white" onclick="RecruitmentLists.toExcel('./export/generate.php');" title="Export grid ke file Excel">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="RecruitmentLists" style="width:100%;height:360px;background-color:white;overflow:hidden"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between text-muted fs-12">
        <div>
            <i class="fa fa-circle-info text-info me-1"></i> Gunakan kolom filter di bawah judul kolom untuk menyaring data dengan cepat.
        </div>
        <div id="grid-status-info" class="fw-semibold">
            Status: Siap
        </div>
    </div>
</div>

<!-- Container Detail Arsip -->
<div id="divlistRecruitment"></div>

<script>
var RecruitmentLists;

function RecruitmentListsKlik() {
    var RecruitmentListsID = RecruitmentLists.getSelectedId();

    if (RecruitmentListsID && RecruitmentListsID !== '') {
        // Tampilkan loading placeholder sebelum konten dimuat
        $('#divlistRecruitment').html(
            '<div class="card shadow-sm border-0 mb-4">' +
                '<div class="card-body text-center py-5">' +
                    '<div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">' +
                        '<span class="visually-hidden">Loading...</span>' +
                    '</div>' +
                    '<h6 class="text-secondary fw-semibold">Memuat Detail Hasil Tes (ID: ' + RecruitmentListsID + ')...</h6>' +
                    '<p class="text-muted fs-13 mb-0">Mohon tunggu sebentar selagi sistem mengambil data kandidat.</p>' +
                '</div>' +
            '</div>'
        );

        // Scroll halus ke container detail
        $('html, body').animate({
            scrollTop: $("#divlistRecruitment").offset().top - 80
        }, 300);

        LoadM('divlistRecruitment', 'recruitment-test-result-detail?ID=' + RecruitmentListsID);
    }
    return true;
}

function bukaDetailTerpilih() {
    var id = RecruitmentLists.getSelectedId();
    if (!id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Baris',
                text: 'Silakan pilih atau klik salah satu baris kandidat terlebih dahulu.',
                confirmButtonColor: '#0d6efd'
            });
        } else {
            alert('Silakan pilih salah satu baris kandidat terlebih dahulu.');
        }
        return;
    }
    RecruitmentListsKlik();
}

RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
RecruitmentLists.setImagePath("./dhtmlx/imgs/");
RecruitmentLists.setHeader("No.,Task ID,Name,Gender,Birth Date,Position,Last Education,Reference,Reference Desc,Status,Aging Remain(days)");
RecruitmentLists.attachHeader("#rspan,#text_filter,#text_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter");
RecruitmentLists.setInitWidths("60,140,180,90,110,180,120,130,150,130,120");
RecruitmentLists.setColAlign("center,left,left,center,center,left,center,left,left,center,center");
RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
RecruitmentLists.setColSorting("int,str,str,str,date,str,str,str,str,str,int");
RecruitmentLists.enableKeyboardSupport(true);
RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
RecruitmentLists.setSkin("dhx_skyblue");
RecruitmentLists.init();

function refreshdata(){
    $('#grid-status-info').html('<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data...</span>');
    RecruitmentLists.clearAndLoad('xml-recruitment-test-result', function() {
        var count = RecruitmentLists.getRowsNum();
        $('#grid-status-info').html('<span class="text-success"><i class="fa fa-check me-1"></i> Total ' + count + ' data termuat</span>');
    });
}

// Initial load
refreshdata();
</script>
@endsection
