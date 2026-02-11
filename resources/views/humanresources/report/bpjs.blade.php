<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	function load_bac_kota(){
		$query = DB::table("branch_lists")->get();
			foreach ($query as $fa){
					$optx .="<option value='$fa->IATA_Code'>$fa->IATA_Code - $fa->City_Name</option>";
			}
		$optx .= "<option value=''>ALL</option>";
		return $optx;
	}
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Report</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">BPJS</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <input type="text" id="Tgl1"  size="10" value="<?php echo date('Y-m') ?>" readonly="readonly" />
		<input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>

	
		<div id="ReportBPJS" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
	 <input type="button" id="ExportExl" name="ExportExl" value="Export Table" onclick="ReportBPJS.toExcel('./export/generate.php?filename=Trial_Balance&title=Trial_Balance');"  />
	  <input type="button" id="ExportExl" name="ExportExl" value="Export BPJS Format" onclick="exportbpjs();"  />
	
	</div>
</div>
		
	
<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m");


	$("#SubmitCheck").click(function(){
		$.blockUI(); 
				ReportBPJS.clearAndLoad('xml-hr-report-bpjs?Tgl1=' + $("#Tgl1").val(),itungx);
	});

function exportbpjs(){
	
	window.open("print-bpjs?Tgl1=" + $("#Tgl1").val());
}

	function itungx() {
		$.unblockUI(); 
		return true;
	}
			
			ReportBPJS = new dhtmlXGridObject('ReportBPJS');
			ReportBPJS.setImagePath("dhtmlx/imgs/");
			ReportBPJS.setHeader("No,Nomor KPJ,NIK,KTP,Lokasi,Kelompok Kerja,Nama Lengkap,Tgl Lahir,Upah,Rincian Iuran,#cspan,#cspan,#cspan,#cspan,#cspan,Total Iuran");
			ReportBPJS.attachHeader("#rspan,#rspan,#rspan,#rspan,#rspan,#rspan,#rspan,#rspan,Bulan Ini,JKK,JKM,JHT PRSHN,JHT TK,JPN PRSHN,JPN TK,#rspan");
			ReportBPJS.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,0.24 %,0.30 %,3.70 %,2.00 %,2.00 %,1.00 %,#rspan");
			ReportBPJS.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
			ReportBPJS.setInitWidths("60,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
			ReportBPJS.setColAlign("center,center,center,center,center,center,center,center,right,right,right,right,right,right,right,right");
			ReportBPJS.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron");
			ReportBPJS.setNumberFormat("0,000",8,".",",");
			ReportBPJS.setNumberFormat("0,000",9,".",",");
			ReportBPJS.setNumberFormat("0,000",10,".",",");
			ReportBPJS.setNumberFormat("0,000",11,".",",");
			ReportBPJS.setNumberFormat("0,000",12,".",",");
			ReportBPJS.setNumberFormat("0,000",14,".",",");
			ReportBPJS.setNumberFormat("0,000",13,".",",");
			ReportBPJS.setNumberFormat("0,000",15,".",",");
			ReportBPJS.setMathRound(2);
			ReportBPJS.setEditable(false);
			ReportBPJS.enableMultiline(true);
			ReportBPJS.enableSmartRendering(true);
			ReportBPJS.setSkin("dhx_skyblue");
			ReportBPJS.init();




	</script>
	
@endsection
