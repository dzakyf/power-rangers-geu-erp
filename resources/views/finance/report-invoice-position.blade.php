<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Finance</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Fast Reports</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Invoice Position</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <input type="text" id="Tgl1"  size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" /> to <input type="text" id="Tgl2"  size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" /> 
		<input type="button" id="SubmitCheck" value="Submit" onclick="refreshdata()" class="btn btn-success mt-2 mr-2" />
	</div>
		<div id="ARAging" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselARAgingDet" name="ekselARAgingDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ARAging.toExcel('./export/generate.php');"  />
		</div>
	</div>	
	<div id="ARCareDetail"></div>

<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

ARAging = new dhtmlXGridObject('ARAging');
ARAging.setImagePath("./dhtmlx/imgs/");
ARAging.setHeader("No.,Account No,Account Name,Invoice No,Invoice Date,Total,Position Information,Due Days,Biller");
ARAging.attachHeader("#rspan,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#text_filter,#text_filter,#combo_filter");
ARAging.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
ARAging.setInitWidths("60,100,300,100,120,120,120,120,120,100");
ARAging.setColAlign("center,center,left,right,right,right,right,center,center,center");
ARAging.setColSorting("int,str,str,str,int,int,int,str,str,str");
ARAging.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro,ro");
ARAging.setNumberFormat("0,000.00",4,".",",");
ARAging.init();
refreshdata();

function refreshdata(){
	ARAging.clearAndLoad('xml-finance-invoice-position?tgl1=' + $("#Tgl1").val() + "&tgl2=" + $("#Tgl2").val());

}
</script>
@endsection