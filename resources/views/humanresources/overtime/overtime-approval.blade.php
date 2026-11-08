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
                <i class="fe fe-check-circle me-2"></i>Approval Lembur Karyawan (Overtime Approval)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Employee Portal</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">Overtime Approval</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-arrow-left me-1"></i> Kembali ke Portal
            </a>
            <a href="{{ url('/overtime-all') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-list me-1"></i> Rekap Semua Data
            </a>
            <button type="button" id="SubmitApproval" class="btn btn-success btn-sm rounded-pill shadow-sm px-4 fw-semibold">
                <i class="fe fe-check-circle me-1"></i> Approve Terpilih
            </button>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Card Filter Periode -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-calendar me-1"></i>Periode Mulai</label>
                    <input type="date" id="Tgl1" class="form-control rounded-3" value="{{ date('Y-m-01') }}" />
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-calendar me-1"></i>Periode Akhir</label>
                    <input type="date" id="Tgl2" class="form-control rounded-3" value="{{ date('Y-m-d') }}" />
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="button" id="SubmitCheck" onclick="Reload_Overtime()" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="fe fe-search me-1"></i> Muat Data
                    </button>
                    <button type="button" id="refreshdata" class="btn btn-light rounded-pill px-3 border shadow-sm" title="Refresh">
                        <i class="fe fe-refresh-cw me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Grid Tabel Approval -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-inbox text-primary me-2"></i>Daftar Pengajuan Lembur Menunggu Persetujuan</h6>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill"><i class="fe fe-clock me-1"></i>Status: Menunggu Approval</span>
        </div>
        <div class="card-body p-3">
            <div id="Overtime" style="width:100%;height:540px; background-color:white;overflow:hidden" class="rounded-3 border"></div>
        </div>
    </div>

    <div id="OvertimeDiv"></div>

<script>
    var Overtime = new dhtmlXGridObject('Overtime');
    Overtime.setImagePath("./dhtmlx/imgs/");
    Overtime.setHeader("Approve,Task ID,NIK,Nama Karyawan,Tgl Mulai,Tgl Selesai,Pra Masuk,Pra Keluar,Pasca Masuk,Pasca Keluar,Libur?,Total Jam,Jam Hitung,Tarif/Rate,Periode Gaji,Nominal Lembur,Referensi / No OT,File Lampiran/Hardcopy");
    Overtime.attachHeader("#master_checkbox,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan");
    Overtime.setInitWidths("70,140,110,230,100,100,90,90,90,90,70,90,90,110,110,130,160,200");
    Overtime.setColAlign("center,left,center,left,center,center,center,center,center,center,center,center,center,right,center,right,left,left");
    Overtime.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,ro,ch,ro,ro,ron,ro,ron,ro,ro");
    Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,int,str,int,str,str");
    Overtime.setEditable(true);
    Overtime.enableEditEvents(true, false, true);
    Overtime.setNumberFormat("0,000", 13, ".", ",");
    Overtime.setNumberFormat("0,000", 15, ".", ",");
    Overtime.setSkin("dhx_skyblue");

    var POvertime = new dataProcessor("overtime-approval-update");
    POvertime.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    POvertime.setUpdateMode("Off");
    POvertime.init(Overtime);
    Overtime.init();
    Reload_Overtime();

    function Reload_Overtime(){
        var tgl1 = $("#Tgl1").val() || '';
        var tgl2 = $("#Tgl2").val() || '';
        Overtime.clearAndLoad("xml-overtime-approval?Tgl1=" + encodeURIComponent(tgl1) + "&Tgl2=" + encodeURIComponent(tgl2));
    }

    $("#refreshdata").click(function(){
        Reload_Overtime();
    });

    $("#SubmitApproval").click(function(){
        dhtmlx.confirm('Apakah Anda yakin ingin menyetujui pengajuan lembur yang dipilih?', 'Konfirmasi Persetujuan Lembur', function(r) {
            if (r == true){
                POvertime.sendData();
                setTimeout(function(){
                    alert("Data pengajuan lembur berhasil disetujui (Approved)!");
                    Reload_Overtime();
                }, 1500);
            }
        });
    });
</script>
@endsection
