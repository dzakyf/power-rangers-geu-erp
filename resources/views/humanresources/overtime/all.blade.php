@extends('layout.mainlayout-hrd')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-list me-2"></i>Rekap Data Lembur Karyawan (All Overtime)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Employee Portal</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">All Overtime Data</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-arrow-left me-1"></i> Kembali ke Portal
            </a>
            <a href="{{ url('/overtime-new') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-plus-circle me-1"></i> Input / Ajukan Lembur
            </a>
            <a href="{{ url('/overtime-approval') }}" class="btn btn-warning btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-check-square me-1"></i> Approval Lembur
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Card Filter & Aksi -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-user me-1"></i>Karyawan (NIK / Nama)</label>
                    <div class="input-group">
                        <input type="text" id="Name" name="Name" class="form-control rounded-3" placeholder="Ketik nama karyawan untuk mencari..."/>
                        <input type="hidden" id="NIK" name="NIK"/>
                        <button class="btn btn-outline-secondary rounded-3 ms-1" type="button" title="Reset Karyawan" onclick="$('#Name').val(''); $('#NIK').val(''); Reload_Overtime();">
                            <i class="fe fe-x"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-calendar me-1"></i>Dari Tanggal</label>
                    <input type="date" id="Tgl1" name="Tgl1" class="form-control rounded-3" value="{{ date('Y-m-01') }}"/>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-calendar me-1"></i>Sampai Tanggal</label>
                    <input type="date" id="Tgl2" name="Tgl2" class="form-control rounded-3" value="{{ date('Y-m-d') }}"/>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="button" id="Process" class="btn btn-primary rounded-pill px-3 fw-semibold shadow-sm w-100" onclick="Reload_Overtime();">
                        <i class="fe fe-filter me-1"></i> Filter Data
                    </button>
                    <button type="button" id="refreshdata" class="btn btn-light rounded-pill px-3 border shadow-sm" title="Refresh">
                        <i class="fe fe-refresh-cw"></i>
                    </button>
                    <button type="button" id="ekselRecruitmentListsDet" class="btn btn-success rounded-pill px-3 fw-semibold shadow-sm" onclick="Overtime.toExcel('./export/generate.php');" title="Export Excel">
                        <i class="fe fe-download me-1"></i> Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Tabel / Grid -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-layers text-primary me-2"></i>Daftar Lembur Karyawan Terdata</h6>
            <span class="badge bg-light text-primary border" id="info-count"><i class="fe fe-database me-1"></i>Data Terkini</span>
        </div>
        <div class="card-body p-3">
            <div id="Overtime" style="width:100%;height:540px; background-color:white;overflow:hidden" class="rounded-3 border"></div>
        </div>
    </div>

    <div id="OvertimeDiv"></div>

<script>
    $("#Name").coolautosuggest({
        url: "search-employee?chars=",
        showThumbnail: false,
        showDescription: true,
        idField: $("#NIK"),
        width: 400,
        minChars: 1,
        onSelected: function(result){
            if (result != null){
                $("#NIK").val(result.id); 
                $("#Name").val(result.data); 
            }
        }
    });

    var Overtime = new dhtmlXGridObject('Overtime');
    Overtime.setImagePath("./dhtmlx/imgs/");
    Overtime.setHeader("No,Task ID,NIK,Nama Karyawan,Tgl Mulai,Tgl Selesai,Pra Masuk,Pra Keluar,Pasca Masuk,Pasca Keluar,Libur?,Total Jam,Jam Hitung,Tarif/Rate,Periode Gaji,Nominal Lembur,Referensi / No OT,Uraian / Note,Grup Gaji");
    Overtime.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#text_filter");
    Overtime.setInitWidths("60,140,110,230,100,100,90,90,90,90,70,90,90,110,110,130,160,250,150");
    Overtime.setColAlign("center,left,center,left,center,center,center,center,center,center,center,center,center,right,center,right,left,left,left");
    Overtime.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ch,ro,ro,ron,ro,ron,ro,ro,ro");
    Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,int,str,int,str,str,str");
    Overtime.setEditable(false);
    Overtime.setNumberFormat("0,000", 13, ".", ",");
    Overtime.setNumberFormat("0,000", 15, ".", ",");
    Overtime.setSkin("dhx_skyblue");
    Overtime.init();
    Reload_Overtime();

    function Reload_Overtime(){
        var nik = $("#NIK").val() || '';
        var tgl1 = $("#Tgl1").val() || '';
        var tgl2 = $("#Tgl2").val() || '';
        Overtime.clearAndLoad("xml-overtime-all?NIK=" + encodeURIComponent(nik) + "&Tgl1=" + encodeURIComponent(tgl1) + "&Tgl2=" + encodeURIComponent(tgl2));
    }

    $("#refreshdata").click(function(){
        Reload_Overtime();
    });
</script>
@endsection
