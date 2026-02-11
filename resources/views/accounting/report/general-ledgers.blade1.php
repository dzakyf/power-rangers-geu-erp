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
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">REPORT</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">General Ledgers</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <input type="text" id="Tgl1"  size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" /> to 
		<input type="text" id="Tgl2" size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" /> 
				 Branch <select id="Origin">
				 <?php if ($LSP == 'PSS-HO'){ ?>
				 
				 <option value="<?php echo $LSP ?>"><?php echo $LSP?></option><?php echo load_bac_kota();?>
				 
				 <?php
				 
				 }else{ 
					echo "<option>$LSP</option>";
				 }
				 ?>
				 </select>
				 
				 
		</select>
		<input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>

	
		<div id="CTRX" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="CTRX.toExcel('./export/generate.php');"  />
	</div>
</div>
<div>
		
	
<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

function CTRXCounting() {
	$.unblockUI();
	return true;
}


	$("#SubmitCheck").click(function(){
		$.blockUI(); 
				CTRX.clearAndLoad('xml-accounting-report-general-ledgers?City=' + $("#Origin").val() + '&Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val()+ "&Tampilan=" + $("#Tampilan").val(),itungx);
	});


	function itungx() {
		$.unblockUI(); 
		return true;
	}
			
				
CTRX = new dhtmlXGridObject('CTRX');
CTRX.setImagePath("./dhtmlx/imgs/");
CTRX.setHeader("No,Posting Date,Document No,External Doc,Journal No,GL/Account,GL Name/Description,Remark,Debet,Kredit,Reference No,Reference 2,User,Department,Counterpart Name,Counterpart Code,Acc Check");
CTRX.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#text_filter,#rspan,#combo_filter,#combo_filter,#text_filter,#combo_filter,#combo_filter");
CTRX.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,{#stat_total},{#stat_total},,,,,,");
CTRX.setInitWidths("80,100,100,150,150,100,200,150,100,100,140,100,100,100,150,100,100");
CTRX.setColAlign("center,center,left,left,center,center,left,left,right,right,left,center,center,center,left,center,center");
CTRX.setColTypes("ro,ro,ro,ro,ed,ro,ron,ron,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro");
CTRX.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
CTRX.enableSmartRendering(true, 1000);
CTRX.setNumberFormat("0,000",8,".",",");
CTRX.setNumberFormat("0,000",9,".",",");
CTRX.setSkin("dhx_skyblue");
CTRX.init();



	</script>
	
@endsection
