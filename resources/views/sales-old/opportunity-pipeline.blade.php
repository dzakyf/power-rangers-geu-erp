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
        <h1>Sales</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Opportunity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Pipeline Summaries</span>
            </li>
        </ol>
    </div> 
   
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	<div class="form-group">
		Year <input type="text" id="Tgl1"  size="5" value="<?php echo date('Y') ?>" maxlength="4" class="form-control-sm" />
		<input type="button" id="SubmitCheck" value="Process" class="btn btn-success" style="vertical-align:top" />
	</div>

	
		<div id="ReportPipeline" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
	<div>
	 <input type="button" id="ExportExl" name="ExportExl" value="Export Table" onclick="ReportPipeline.toExcel('./export/generate.php?filename=Trial_Balance&title=Trial_Balance');" class="btn btn-success mt-2 mr-2"  />
	
	</div>
</div>
		
	
<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y");


	$("#SubmitCheck").click(function(){
		//$.blockUI(); 
				ReportPipeline.clearAndLoad('xml-sales-opportunity-pipeline?Tgl1=' + $("#Tgl1").val(),itungx);
	});

	function itungx() {
		$.unblockUI(); 
		return true;
	}
			
			ReportPipeline = new dhtmlXGridObject('ReportPipeline');
			ReportPipeline.setImagePath("dhtmlx/imgs/");
			ReportPipeline.setHeader("No,Status,January,February,March,April,May,June,July,August,September,October,November,December,Grand Total");
			ReportPipeline.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter");
			ReportPipeline.attachFooter("Total,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
			ReportPipeline.setInitWidths("60,200,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
			ReportPipeline.setColAlign("center,center,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right");
			ReportPipeline.setColTypes("ro,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron");
			ReportPipeline.setNumberFormat("0,000",2,".",",");
			ReportPipeline.setNumberFormat("0,000",3,".",",");
			ReportPipeline.setNumberFormat("0,000",4,".",",");
			ReportPipeline.setNumberFormat("0,000",5,".",",");
			ReportPipeline.setNumberFormat("0,000",6,".",",");
			ReportPipeline.setNumberFormat("0,000",7,".",",");
			ReportPipeline.setNumberFormat("0,000",8,".",",");
			ReportPipeline.setNumberFormat("0,000",9,".",",");
			ReportPipeline.setNumberFormat("0,000",10,".",",");
			ReportPipeline.setNumberFormat("0,000",11,".",",");
			ReportPipeline.setNumberFormat("0,000",12,".",",");
			ReportPipeline.setNumberFormat("0,000",14,".",",");
			ReportPipeline.setNumberFormat("0,000",15,".",",");
			ReportPipeline.setMathRound(2);
			ReportPipeline.setEditable(false);
			ReportPipeline.enableMultiline(true);
			ReportPipeline.enableSmartRendering(true);
			ReportPipeline.setSkin("dhx_skyblue");
			ReportPipeline.init();




	</script>
	
@endsection
