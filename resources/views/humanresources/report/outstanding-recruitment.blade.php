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

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Report</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">OUTSTANDING RECRUITMENT</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	
	
		<div id="ReportOustandingRecruitment" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
	 <input type="button" id="ExportExl" name="ExportExl" value="Export Table" onclick="ReportOustandingRecruitment.toExcel('./export/generate.php');"  />
	
	</div>
</div>
<div>
		
	
<script>
	
	 
			ReportOustandingRecruitment = new dhtmlXGridObject('ReportOustandingRecruitment');
			ReportOustandingRecruitment.setImagePath("dhtmlx/imgs/");
			ReportOustandingRecruitment.setHeader("No,Reference No,Category,Requisition ID,Requisition Name,Qty,Registered,Fullfiled");
			ReportOustandingRecruitment.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan");
			ReportOustandingRecruitment.setInitWidths("60,250,250,150,300,100,100,100");
			ReportOustandingRecruitment.setColAlign("center,center,left,center,center,right,right,right");
			ReportOustandingRecruitment.setColTypes("ro,ro,ro,ro,ro,ron,ron,ron");
			ReportOustandingRecruitment.setNumberFormat("0,000",5,".",",");
			ReportOustandingRecruitment.setNumberFormat("0,000",6,".",",");
			ReportOustandingRecruitment.setNumberFormat("0,000",7,".",",");
			ReportOustandingRecruitment.setMathRound(2);
			ReportOustandingRecruitment.setEditable(false);
			ReportOustandingRecruitment.enableMultiline(true);
			ReportOustandingRecruitment.enableSmartRendering(true);
			ReportOustandingRecruitment.setSkin("dhx_skyblue");
			ReportOustandingRecruitment.init();
			ReportOustandingRecruitment.clearAndLoad('xml-hr-report-outstanding-recruitment');
	



	</script>
	
@endsection
