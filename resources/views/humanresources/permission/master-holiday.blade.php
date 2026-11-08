@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Holiday</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row no-gutters">
		 <div id="Holiday" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Holiday.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="Settings" NAME="Settings" value="Settings" onclick="settinglibur()" class="btn btn-info mt-2 mr-2"/>
		</div>
	</div>
	<div id="HolidayDiv"></div>
<script>





Holiday = new dhtmlXGridObject('Holiday');
Holiday.setImagePath("./dhtmlx/imgs/");
Holiday.setH@extends('layout.mainlayout-hrd')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-calendar me-2"></i>Master Hari Libur Nasional & Perusahaan (Holiday Master)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Master Data</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">Holiday Settings</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-arrow-left me-1"></i> Ke Portal Absensi
            </a>
            <a href="{{ url('/permission-all') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-list me-1"></i> Data Izin & Cuti
            </a>
            <a href="{{ url('/permission-collective-leave') }}" class="btn btn-info btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-users me-1"></i> Setup Cuti Bersama
            </a>
            <button type="button" id="AddBM" class="btn btn-primary btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-plus-circle me-1"></i> Tambah Hari Libur
            </button>
            <button type="button" id="SaveBM" class="btn btn-success btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-save me-1"></i> Simpan Perubahan
            </button>
            <button type="button" id="DeleteBM" class="btn btn-danger btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-trash-2 me-1"></i> Hapus Baris
            </button>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Card Grid Tabel Libur -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-calendar text-primary me-2"></i>Daftar Hari Libur Resmi & Kalender Perusahaan</h6>
                <small class="text-muted">Data ini menjadi acuan kalkulasi durasi cuti, lembur, dan filter hari kerja pada portal absensi</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="RefreshBM" class="btn btn-light btn-sm rounded-pill border px-3" title="Refresh">
                    <i class="fe fe-refresh-cw me-1"></i> Refresh
                </button>
                <button type="button" id="Settings" onclick="settinglibur()" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fe fe-settings me-1"></i> Pengaturan Khusus
                </button>
                <button type="button" id="ekselPODListDet" class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="Holiday.toExcel('./export/generate.php');" title="Export Excel">
                    <i class="fe fe-download me-1"></i> Excel
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="Holiday" style="width:100%;height:540px; background-color:white;overflow:hidden" class="rounded-3 border"></div>
        </div>
    </div>

    <div id="HolidayDiv" class="mt-3"></div>

<script>
    var Holiday = new dhtmlXGridObject('Holiday');
    Holiday.setImagePath("./dhtmlx/imgs/");
    Holiday.setHeader("No,Tahun,Hari,Tanggal Libur,Nama Hari Libur,Deskripsi / Keterangan");
    Holiday.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
    Holiday.setInitWidths("70,100,120,130,220,350");
    Holiday.setColAlign("center,center,center,center,left,left");
    Holiday.setColTypes("ro,ed,ro,ed,ed,ed");
    Holiday.setColSorting("int,str,str,date,str,str");
    Holiday.setEditable(true);
    Holiday.enableMultiline(true);
    Holiday.enableSmartRendering(true, 100);
    Holiday.setSkin("dhx_skyblue");

    var PHoliday = new dataProcessor("hrd-master-holiday-submit");
    PHoliday.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    PHoliday.setUpdateMode("Off");
    PHoliday.init(Holiday);
    Holiday.init();

    function settinglibur() {
        LoadM('HolidayDiv', 'master-holiday-detail');
        $('html, body').animate({
            scrollTop: $("#HolidayDiv").offset().top - 80
        }, 500);
    }
            
    setTimeout(function(){
        reloadmasterHoliday();
    }, 500);

    function reloadmasterHoliday(){
        Holiday.clearAndLoad("xml-hrd-master-holiday");	
    }

    $("#SaveBM").click(function(){
        PHoliday.sendData();
        alert("Perubahan Hari Libur berhasil disimpan!");
        setTimeout(function(){
            reloadmasterHoliday();
        }, 1000);
    });

    $("#DeleteBM").click(function(){
        dhtmlx.confirm('Apakah Anda yakin ingin menghapus data libur terpilih?', 'Konfirmasi Hapus', function(r) {
            if (r == true) {
                Holiday.deleteSelectedRows();
                PHoliday.sendData();
                alert("Data berhasil dihapus!");
                reloadmasterHoliday();
            }
        });
    });

    $("#AddBM").click(function(){
        var idbi = Holiday.uid();
        var today = new Date().toISOString().slice(0, 10);
        var year = new Date().getFullYear();
        Holiday.addRow(idbi, [Holiday.getRowsNum() + 1, year, '-', today, 'Libur Baru', '-'], 0);
        Holiday.selectRow(0);
    });

    $("#RefreshBM").click(function(){
        reloadmasterHoliday();
    });
</script>
@endsection
eader("No,Year,Day,Date,Holiday,Description");
Holiday.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
Holiday.setInitWidths("80,100,100,100,100,300");
Holiday.setColAlign("center,right,center,center,left");
Holiday.setColTypes("ro,ro,ro,ro,ro,ro");
Holiday.setColSorting("int,str,str,str,str,str");
Holiday.setEditable(true);
Holiday.enableMultiline(true);
Holiday.enableSmartRendering(true, 100);
Holiday.setSkin("dhx_skyblue");
PHoliday = new dataProcessor("hrd-master-holiday-submit");
	PHoliday.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PHoliday.setUpdateMode("Off");
	
PHoliday.init(Holiday);
Holiday.init();

function settinglibur() {
	LoadM('HolidayDiv','master-holiday-detail');
}
		
setTimeout(function(){
	reloadmasterHoliday();
},1000);

function reloadmasterHoliday(){
	Holiday.clearAndLoad("xml-hrd-master-holiday");	

}
$("#SaveBM").click(function(){
		PHoliday.sendData();
		alert("Holiday was Saved!");
		setTimeout(function(){
			reloadmasterHoliday();
		},1000);
		
});
$("#DeleteBM").click(function(){
	dhtmlx.confirm('Delete Data?', 'Holiday', function(r) {
			
		if (r==true){
							
				Holiday.deleteSelectedRows();
				PHoliday.sendData();
				alert("Data were deleted!");
				reloadmasterHoliday();
		}
	});
		
});
$("#AddBM").click(function(){
	var idbi =Holiday.uid();
		Holiday.addRow(idbi,[Holiday.getRowsNum()+1,''],0);
		Holiday.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadmasterHoliday();		
});



</script>

@endsection
