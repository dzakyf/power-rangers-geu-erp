<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	function loadept(){
		$optx = "";
		$query = DB::table("master_department")->get();
			foreach ($query as $fa){
					$optx .="<option value='$fa->Initial'>$fa->Department - $fa->Initial</option>";
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
                <span class="active">Cash Flow</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Year <input type="text" id="Tgl1"  size="10" value="<?php echo date('Y') ?>"/>
		Type <select id="tipe">
				<option>Transaction</option>
				<option>Budget</option>
			</select>
				
		Department <select id="dept">
				<?php echo loadept() ?>
			</select>		 
				 
		</select>
		<input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>

	
		<div id="CashFlow" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="CashFlow.toExcel('./export/generate.php');"  />
	</div>
</div>
<div>
		
	
<script>
	/*var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");
*/
function CashFlowCounting() {
	$.unblockUI();
	return true;
}


	$("#SubmitCheck").click(function(){
		$.blockUI(); 
				CashFlow.clearAndLoad('xml-accounting-report-cash-flow?tipe=' + $("#tipe").val() + '&Tgl1=' + $("#Tgl1").val()+ '&dept=' + $("#dept").val(),itungx);
	});


	function itungx() {
		$.unblockUI(); 
		return true;
	}
			
			CashFlow = new dhtmlXGridObject('CashFlow');
			CashFlow.setImagePath("dhtmlx/imgs/");
			CashFlow.setHeader("No,Code ,Description,January,February,March,April,May,June,July,August,September,October,November,December");
			CashFlow.attachHeader("#rspan,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter");
			CashFlow.attachFooter("Total,#cspan,#cspan,<div id='div_jan'>{#stat_total}</div>,<div id='div_feb'>{#stat_total}</div>,<div id='div_mar'>{#stat_total}</div>,<div id='div_apr'>{#stat_total}</div>,<div id='div_may'>{#stat_total}</div>,<div id='div_jun'>{#stat_total}</div>,<div id='div_jul'>{#stat_total}</div>,<div id='div_aug'>{#stat_total}</div>,<div id='div_sep'>{#stat_total}</div>,<div id='div_oct'>{#stat_total}</div>,<div id='div_nov'>{#stat_total}</div>,<div id='div_dec'>{#stat_total}</div>");

			CashFlow.setInitWidths("80,100,180,130,130,130,130,130,130,130,130,130,130,130,130");
			CashFlow.setColAlign("center,left,left,right,right,right,right,right,right,right,right,right,right,right,right");
			CashFlow.setColTypes("ro,ro,ro,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn");
			CashFlow.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int");

			CashFlow.setNumberFormat("0,000.00",3,".",",");
			CashFlow.setNumberFormat("0,000.00",4,".",",");
			CashFlow.setNumberFormat("0,000.00",5,".",",");
			CashFlow.setNumberFormat("0,000.00",6,".",",");
			CashFlow.setNumberFormat("0,000.00",7,".",",");
			CashFlow.setNumberFormat("0,000.00",8,".",",");
			CashFlow.setNumberFormat("0,000.00",9,".",",");
			CashFlow.setNumberFormat("0,000.00",10,".",",");
			CashFlow.setNumberFormat("0,000.00",11,".",",");
			CashFlow.setNumberFormat("0,000.00",12,".",",");
			CashFlow.setNumberFormat("0,000.00",13,".",",");
			CashFlow.setNumberFormat("0,000.00",14,".",",");
			CashFlow.setEditable(false);
			CashFlow.setSkin("dhx_skyblue");
			CashFlow.init();

			
	</script>
	
@endsection
