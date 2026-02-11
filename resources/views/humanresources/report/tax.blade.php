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
                <span class="active">Tax WHT/PPH</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <input type="text" id="Tgl1"  size="10" value="<?php echo date('Y-m') ?>" readonly="readonly" />
		Category <select id="tipepajak">
					<option>ALL</option>
					<option>OS/OT</option>
					<option>OHD</option>
				</select>
		<input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>

	
		<div id="ReportTax" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
	 <input type="button" id="ExportExl" name="ExportExl" value="Export Table" onclick="ReportTax.toExcel('./export/generate.php?filename=TaxReport&title=Trial_Balance');"  />
	  <input type="button" id="ExportExl" name="ExportExl" value="Export PPH Format" onclick="exportPPH();"  />
	
	</div>
</div>
<div>
		
	
<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m");


	$("#SubmitCheck").click(function(){
		$.blockUI(); 
				ReportTax.clearAndLoad('xml-hr-report-tax?Tgl1=' + $("#Tgl1").val(),itungx);
	});

function exportPPH(){
	window.open("print-pph?Tgl1=" + $("#Tgl1").val());
}
	function itungx() {
		$.unblockUI(); 
		return true;
	}
			
			ReportTax = new dhtmlXGridObject('ReportTax');
			ReportTax.setImagePath("dhtmlx/imgs/");
			ReportTax.setHeader("No,NIK,Nama,NPWP,Kelompok,Penghasilan Bruto(Rp),PPh 21(Rp)");
			ReportTax.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan");
			ReportTax.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total");
			ReportTax.setInitWidths("60,100,250,250,100,100,100");
			ReportTax.setColAlign("center,center,left,center,center,right,right");
			ReportTax.setColTypes("ro,ro,ro,ro,ro,ron,ron");
			ReportTax.setNumberFormat("0,000",5,".",",");
			ReportTax.setNumberFormat("0,000",6,".",",");
			ReportTax.setMathRound(2);
			ReportTax.setEditable(false);
			ReportTax.enableMultiline(true);
			ReportTax.enableSmartRendering(true);
			ReportTax.setSkin("dhx_skyblue");
			ReportTax.init();




	</script>
	
@endsection
