@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <!-- Breadcrumb & Header Modern -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-file-text me-2"></i>Master Template Rekrutmen
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Recruitment</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Template Notifikasi</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light rounded-pill px-3 shadow-sm" onclick="refreshdata()">
                <i class="fe fe-refresh-cw me-1"></i> Refresh Data
            </button>
            <button type="button" id="NewReq" onclick="newreq()" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fe fe-plus-circle me-1"></i> Tambah Template Baru
            </button>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fe fe-list me-1 text-primary"></i> Daftar Master Template Pesan
                        </h6>
                        <small class="text-muted">Klik ganda (double-click) pada baris tabel untuk melihat atau mengedit template.</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" id="ekselMasterTemplateDet" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="MasterTemplate.toExcel('./export/generate.php');">
                            <i class="fe fe-download me-1"></i> Export Excel
                        </button>
                        <button type="button" id="delets" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="delsRequisition()">
                            <i class="fe fe-trash-2 me-1"></i> Nonaktifkan Template
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive rounded-3 border">
                        <div id="MasterTemplate" style="width:100%; height:320px; background-color:white; overflow:hidden"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Form Dynamic Details / New Template -->
    <div class="row">
        <div class="col-12">
            <div id="divlistRecruitment"></div>
        </div>
    </div>

<script>
function delsRequisition(){
    var selectedId = MasterTemplate.getSelectedId();
    if (!selectedId) {
        alert('Silakan pilih template terlebih dahulu dari tabel!');
        return;
    }

    dhtmlx.confirm({
        title: "Konfirmasi",
        text: "Apakah Anda yakin ingin menonaktifkan template ini?",
        callback: function(r) {
            if (r == true) {
                MasterTemplate.deleteSelectedRows();
                PMasterTemplate.sendData();
                alert('Template berhasil dinonaktifkan!');
                setTimeout(function(){
                    refreshdata();
                }, 1000);
            }
        }
    });
}

function newreq() {
    LoadM('divlistRecruitment', 'hrd-master-template-new');
    setTimeout(function() {
        var el = document.getElementById('divlistRecruitment');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }, 250);
}

function MasterTemplateKlik() {
    var MasterTemplateID = MasterTemplate.getSelectedId();
    if (MasterTemplateID != '') {
        LoadM('divlistRecruitment', 'hrd-master-template-detail?ID=' + MasterTemplateID);
        setTimeout(function() {
            var el = document.getElementById('divlistRecruitment');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        }, 250);
    }
    return true;
}

MasterTemplate = new dhtmlXGridObject('MasterTemplate');
MasterTemplate.setImagePath("./dhtmlx/imgs/");
MasterTemplate.setHeader("No.,Template ID,Message Type,Status,Created By,Created Date");
MasterTemplate.setInitWidths("60,220,150,120,180,*");
MasterTemplate.setColAlign("center,left,center,center,left,center");
MasterTemplate.setColTypes("ro,ro,ro,ro,ro,ro");
MasterTemplate.setColSorting("int,str,str,str,str,str");
MasterTemplate.enableKeyboardSupport(true);
MasterTemplate.enableAutoWidth(true);
MasterTemplate.attachEvent("onRowDblClicked", MasterTemplateKlik);

PMasterTemplate = new dataProcessor("hrd-master-template-submit");
PMasterTemplate.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
PMasterTemplate.init(MasterTemplate);

MasterTemplate.setSkin("dhx_skyblue");
MasterTemplate.init();
refreshdata();

function refreshdata(){
    MasterTemplate.clearAndLoad('xml-hrd-master-template');
}
</script>
@endsection