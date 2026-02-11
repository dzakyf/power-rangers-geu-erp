<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	function loadept(){
		$optx = "<option value=''>ALL</option>";
		$query = DB::table("master_department")->get();
			foreach ($query as $fa){
					$optx .="<option value='$fa->Initial'>$fa->Department - $fa->Initial</option>";
			}
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
                <span class="active">Balance Sheet</span>
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

	
		<div id="MgtBalanceSheet" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MgtBalanceSheet.toExcel('./export/generate.php');"  />
	</div>
</div>
<div>
		
	
<script>
	/*var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");
*/
function MgtBalanceSheetCounting() {
	$.unblockUI();
	return true;
}


	$("#SubmitCheck").click(function(){
		$.blockUI(); 
				MgtBalanceSheet.clearAndLoad('xml-accounting-report-balance?tipe=' + $("#tipe").val() + '&Tgl1=' + $("#Tgl1").val()+ '&dept=' + $("#dept").val(),itungx);
	});


	function itungx() {
	//	grupkan();
		$.unblockUI(); 
		return true;
	}
			
			MgtBalanceSheet = new dhtmlXGridObject('MgtBalanceSheet');
			MgtBalanceSheet.setImagePath("dhtmlx/imgs/");
			MgtBalanceSheet.setHeader("Grouping,Code,Description,Beg Balance,January,February,March,April,May,June,July,August,September,October,November,December,Average,GroupBy");
			MgtBalanceSheet.attachHeader("#rspan,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter");
			MgtBalanceSheet.attachFooter("Total,#cspan,#cspan,<div id='div_beg'>{#stat_total}</div>,<div id='div_jan'>{#stat_total}</div>,<div id='div_feb'>{#stat_total}</div>,<div id='div_mar'>{#stat_total}</div>,<div id='div_apr'>{#stat_total}</div>,<div id='div_may'>{#stat_total}</div>,<div id='div_jun'>{#stat_total}</div>,<div id='div_jul'>{#stat_total}</div>,<div id='div_aug'>{#stat_total}</div>,<div id='div_sep'>{#stat_total}</div>,<div id='div_oct'>{#stat_total}</div>,<div id='div_nov'>{#stat_total}</div>,<div id='div_dec'>{#stat_total}</div>,<div id='div_total'>{#stat_total}</div>,");

			MgtBalanceSheet.setInitWidths("80,100,280,130,130,130,130,130,130,130,130,130,130,130,130,130,150,100");
			MgtBalanceSheet.setColAlign("center,left,left,right,right,right,right,right,right,right,right,right,right,right,right,right,right,center");
			MgtBalanceSheet.setColTypes("ro,ro,ro,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,ron[=c3+c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14],ro");
			MgtBalanceSheet.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,int,str");

			MgtBalanceSheet.setNumberFormat("0,000.00",3,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",4,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",5,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",6,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",7,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",8,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",9,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",10,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",11,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",12,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",13,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",14,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",15,".",",");
			MgtBalanceSheet.setNumberFormat("0,000.00",16,".",",");
			MgtBalanceSheet.setEditable(false);
			MgtBalanceSheet.setSkin("dhx_skyblue");
			MgtBalanceSheet.init();

			function grupkan(){
				var out = 0;
				for (var i = 0; i < MgtBalanceSheet.getRowsNum(); i++) {
					var cek = MgtBalanceSheet.cells2(i, 17).getValue();
					if (cek != ''){
						MgtBalanceSheet.groupBy(0,["","#title","#cspan","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total"]);

					}
				}
				for (var i = 0; i < MgtBalanceSheet.getRowsNum(); i++) {
					var cek = MgtBalanceSheet.cells2(i, 17).getValue();
					if (cek == ''){
						MgtBalanceSheet.groupBy(0,["","#title","#cspan","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total","#stat_total"]);

					}
				}
			}
	</script>
	
@endsection
