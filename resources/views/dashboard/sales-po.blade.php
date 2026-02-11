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
        <h1>Dashboard</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">PROFORMA & PO</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">SALES PO</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Year <input type="text" id="Tgl1"  size="10" value="<?php echo date('Y') ?>" maxlength="4" /> 
		<input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>

	
		<div id="BRTBDIV" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
		<div id="coaWin"></div>
	<div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BRTB.toExcel('./export/generate.php');"  />
	</div>
</div>
<div>
		
	
<script>
	
	function itungx() {
		$.unblockUI(); 
		
		return true;
	}
	


	$("#SubmitCheck").click(function(){
		$.blockUI(); 
				BRTB.clearAndLoad('xml-dashboard-sales-po?Tgl1=' + $("#Tgl1").val(),itungx);
	});


	
			
				BRTB = new dhtmlXGridObject('BRTBDIV');
				BRTB.setImagePath("./dhtmlx/imgs/");
				BRTB.setHeader("No,PO No,Customer,Subject,Periode,Amount,Belum Tertagih,Ditagih (Proforma Subtotal) Tahun Berjalan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan");
				BRTB.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec");
				BRTB.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
				BRTB.setInitWidths("50,80,120,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
				BRTB.setColAlign("center,left,left,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right");
				BRTB.setColSorting("int,str,str,str,str,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int");
				BRTB.setColTypes("ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron");
				BRTB.setNumberFormat("0,000",5,".",",");
				BRTB.setNumberFormat("0,000",6,".",",");
				BRTB.setNumberFormat("0,000",7,".",",");
				BRTB.setNumberFormat("0,000",8,".",",");
				BRTB.setNumberFormat("0,000",9,".",",");
				BRTB.setNumberFormat("0,000",10,".",",");
				BRTB.setNumberFormat("0,000",11,".",",");
				BRTB.setNumberFormat("0,000",12,".",",");
				BRTB.setNumberFormat("0,000",13,".",",");
				BRTB.setNumberFormat("0,000",14,".",",");
				BRTB.setNumberFormat("0,000",15,".",",");
				BRTB.setNumberFormat("0,000",16,".",",");
				BRTB.setNumberFormat("0,000",17,".",",");
				BRTB.setNumberFormat("0,000",18,".",",");
				BRTB.setEditable(false);
				BRTB.enableSmartRendering(true,200);
				BRTB.setSkin("dhx_skyblue");
				BRTB.init();



	</script>
	
@endsection
