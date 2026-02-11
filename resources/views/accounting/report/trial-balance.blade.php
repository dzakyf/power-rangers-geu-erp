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
                <span class="active">Trial Balance</span>
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
				 Show <select id="Tampilan">
				 <option value="">ALL</option>
				 <option value="WITHOUT">WITHOUT HEADER/GROUP</option>
				 <option value="ONLY">ONLY HEADER/GROUP</option>
				 
		</select>
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
	function sumColumn(ind) {
    var out = 0;
    for (var i = 0; i < BRTB.getRowsNum(); i++) {
 		itungenggak = BRTB.cells2(i,7).getValue();
		if (itungenggak != 'yes'){
        out += parseFloat(BRTB.cells2(i, ind).getValue());
		}
    }
    return Math.round(out,2);
}
	function itungx() {
	$.unblockUI(); 
		var srQ = document.getElementById("JumCreditJournal1");
		srQ.innerHTML = number_format(sumColumn(5));
		var srQ = document.getElementById("JumDebtJournal1");
		srQ.innerHTML = number_format(sumColumn(4));
		var srQ = document.getElementById("jumtotal");
		srQ.innerHTML = number_format(sumColumn(4)-sumColumn(5));
	return true;
}
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");



	$("#SubmitCheck").click(function(){
		$.blockUI(); 
				BRTB.clearAndLoad('xml-accounting-report-trial-balance?City=' + $("#Origin").val() + '&Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val()+ "&Tampilan=" + $("#Tampilan").val(),itungx);
	});


	
			
				BRTB = new dhtmlXGridObject('BRTBDIV');
				BRTB.setImagePath("./dhtmlx/imgs/");
				BRTB.setHeader("&nbsp;,Acc No,Description,Beginning Balance,Debet,Credit,Ending Balance,Ending Balance Last Year,Net Mutation");
				BRTB.attachFooter(["Total",'#cspan','#cspan','{#stat_total}',"<div id='JumDebtJournal1'>{#stat_total}</div>","<div id='JumCreditJournal1'>{#stat_total}</div>","{#stat_total}","{#stat_total}",,"#cspan"]);
				BRTB.attachFooter(["Balance",'#cspan','#cspan','#cspan',"#cspan","<div class='merah' id='jumtotal'>0</div>",,,"#cspan"]);
				BRTB.attachHeader("&nbsp;,#combo_filter,#combo_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#rspan");
				BRTB.setInitWidths("50,80,340,100,100,100,100,100,100");
				BRTB.setColAlign("center,left,left,right,right,right,right,right,right");
				BRTB.setColSorting("int,str,str,str,str,int,int,int,int,int");
				BRTB.setColTypes("ro,ro,ro,ron,ron,ron,ron,ron,ron,ron");
				BRTB.setNumberFormat("0,000",3,".",",");
				BRTB.setNumberFormat("0,000",4,".",",");
				BRTB.setNumberFormat("0,000",5,".",",");
				BRTB.setNumberFormat("0,000",6,".",",");
				BRTB.setNumberFormat("0,000",7,".",",");
				BRTB.setNumberFormat("0,000",8,".",",");
				BRTB.setNumberFormat("0,000",9,".",",");
				BRTB.setEditable(false);
				BRTB.enableSmartRendering(true);
				BRTB.setSkin("dhx_skyblue");
				BRTB.init();



	</script>
	
@endsection
