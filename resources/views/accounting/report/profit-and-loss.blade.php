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
                <span class="active">Profit and Loss</span>
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

	
		<div id="ProfitLoss" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="ProfitLoss.toExcel('./export/generate.php');"  />
	</div>
</div>
<div>
		
	
<script>
	/*var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");
*/
function ProfitLossCounting() {
	$.unblockUI();
	return true;
}


	$("#SubmitCheck").click(function(){
	//	$.blockUI(); 
				ProfitLoss.clearAndLoad('xml-accounting-report-profit-and-loss?tipe=' + $("#tipe").val() + '&Tgl1=' + $("#Tgl1").val()+ '&dept=' + $("#dept").val(),itungx);
	});


	function itungx() {
		//$.unblockUI(); 
		return true;
	}
			
			ProfitLoss = new dhtmlXGridObject('ProfitLoss');
			ProfitLoss.setImagePath("dhtmlx/imgs/");
			ProfitLoss.setHeader("No,Code ,Description,January,February,March,April,May,June,July,August,September,October,November,December,TOTAL");
			ProfitLoss.attachHeader("#rspan,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");

			ProfitLoss.attachFooter(["LABA KOTOR",'#cspan','#cspan',"<div id='gross_jan'>0</div>","<div id='gross_feb'>0</div>","<div id='gross_mar'>0</div>","<div id='gross_apr'>0</div>","<div id='gross_mei'>0</div>","<div id='gross_jun'>0</div>","<div id='gross_jul'>0</div>","<div id='gross_agt'>0</div>","<div id='gross_sep'>0</div>","<div id='gross_oct'>0</div>","<div id='gross_nov'>0</div>","<div id='gross_dec'>0</div>","<div id='gross_total'>0</div>"]);
			ProfitLoss.attachFooter(["PENDAPATAN OPERASIONAL",'#cspan','#cspan',"<div id='operational_jan'>0</div>","<div id='operational_feb'>0</div>","<div id='operational_mar'>0</div>","<div id='operational_apr'>0</div>","<div id='operational_mei'>0</div>","<div id='operational_jun'>0</div>","<div id='operational_jul'>0</div>","<div id='operational_agt'>0</div>","<div id='operational_sep'>0</div>","<div id='operational_oct'>0</div>","<div id='operational_nov'>0</div>","<div id='operational_dec'>0</div>","<div id='operational_total'>0</div>"]);
			ProfitLoss.attachFooter(["LABA RUGI SEBELUM PAJAK",'#cspan','#cspan',"<div id='beforeTax_jan'>0</div>","<div id='beforeTax_feb'>0</div>","<div id='beforeTax_mar'>0</div>","<div id='beforeTax_apr'>0</div>","<div id='beforeTax_mei'>0</div>","<div id='beforeTax_jun'>0</div>","<div id='beforeTax_jul'>0</div>","<div id='beforeTax_agt'>0</div>","<div id='beforeTax_sep'>0</div>","<div id='beforeTax_oct'>0</div>","<div id='beforeTax_nov'>0</div>","<div id='beforeTax_dec'>0</div>","<div id='beforeTax_total'>0</div>"]);
			ProfitLoss.attachFooter(["LABA RUGI SETELAH PAJAK",'#cspan','#cspan',"<div id='afterTax_jan'>0</div>","<div id='afterTax_feb'>0</div>","<div id='afterTax_mar'>0</div>","<div id='afterTax_apr'>0</div>","<div id='afterTax_mei'>0</div>","<div id='afterTax_jun'>0</div>","<div id='afterTax_jul'>0</div>","<div id='afterTax_agt'>0</div>","<div id='afterTax_sep'>0</div>","<div id='afterTax_oct'>0</div>","<div id='afterTax_nov'>0</div>","<div id='afterTax_dec'>0</div>","<div id='afterTax_total'>0</div>"]);
			ProfitLoss.setInitWidths("80,100,180,130,130,130,130,130,130,130,130,130,130,130,130,130");
			ProfitLoss.setColAlign("center,left,left,right,right,right,right,right,right,right,right,right,right,right,right,right");
			ProfitLoss.setColTypes("ro,ro,ro,ron[0],ron[0],ron[0],ron[0],ron[0],ron[0],ron[0],ron[0],ron[0],ron[0],ron[0],ron[0],ron[0]");
			ProfitLoss.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int");
			ProfitLoss.attachEvent("onDataReady",calculatedFooter);
			ProfitLoss.setNumberFormat("0,000.00",3,".",",");
			ProfitLoss.setNumberFormat("0,000.00",4,".",",");
			ProfitLoss.setNumberFormat("0,000.00",5,".",",");
			ProfitLoss.setNumberFormat("0,000.00",6,".",",");
			ProfitLoss.setNumberFormat("0,000.00",7,".",",");
			ProfitLoss.setNumberFormat("0,000.00",8,".",",");
			ProfitLoss.setNumberFormat("0,000.00",9,".",",");
			ProfitLoss.setNumberFormat("0,000.00",10,".",",");
			ProfitLoss.setNumberFormat("0,000.00",11,".",",");
			ProfitLoss.setNumberFormat("0,000.00",12,".",",");
			ProfitLoss.setNumberFormat("0,000.00",13,".",",");
			ProfitLoss.setNumberFormat("0,000.00",14,".",",");
			ProfitLoss.setNumberFormat("0,000.00",15,".",",");
			ProfitLoss.setEditable(false);
			ProfitLoss.setSkin("dhx_skyblue");
			ProfitLoss.init();
			ProfitLoss.splitAt(3);
			function calculatedFooter () {
				var jan = parseFloat(ProfitLoss.cells(4,3).getValue()) + parseFloat(ProfitLoss.cells(51,3).getValue());
				var feb = parseFloat(ProfitLoss.cells(4,4).getValue()) + parseFloat(ProfitLoss.cells(51,4).getValue());
				var mar = parseFloat(ProfitLoss.cells(4,5).getValue()) + parseFloat(ProfitLoss.cells(51,5).getValue());
				var apr = parseFloat(ProfitLoss.cells(4,6).getValue()) + parseFloat(ProfitLoss.cells(51,6).getValue());
				var mei = parseFloat(ProfitLoss.cells(4,7).getValue()) + parseFloat(ProfitLoss.cells(51,7).getValue());
				var jun = parseFloat(ProfitLoss.cells(4,8).getValue()) + parseFloat(ProfitLoss.cells(51,8).getValue());
				var jul = parseFloat(ProfitLoss.cells(4,9).getValue()) + parseFloat(ProfitLoss.cells(51,9).getValue());
				var agt = parseFloat(ProfitLoss.cells(4,10).getValue()) + parseFloat(ProfitLoss.cells(51,10).getValue());
				var sep = parseFloat(ProfitLoss.cells(4,11).getValue()) + parseFloat(ProfitLoss.cells(51,11).getValue());
				var oct = parseFloat(ProfitLoss.cells(4,12).getValue()) + parseFloat(ProfitLoss.cells(51,12).getValue());
				var nov = parseFloat(ProfitLoss.cells(4,13).getValue()) + parseFloat(ProfitLoss.cells(51,13).getValue());
				var dec = parseFloat(ProfitLoss.cells(4,14).getValue()) + parseFloat(ProfitLoss.cells(51,14).getValue());
				var total = jan + feb + mar + apr + mei + jun + jul + agt + sep + oct + nov + dec;
				//send to grid
				document.getElementById("gross_jan").innerHTML = formatNumber(jan);
				document.getElementById("gross_feb").innerHTML = formatNumber(feb);
				document.getElementById("gross_mar").innerHTML = formatNumber(mar);
				document.getElementById("gross_apr").innerHTML = formatNumber(apr);
				document.getElementById("gross_mei").innerHTML = formatNumber(mei);
				document.getElementById("gross_jun").innerHTML = formatNumber(jun);
				document.getElementById("gross_jul").innerHTML = formatNumber(jul);
				document.getElementById("gross_agt").innerHTML = formatNumber(agt);
				document.getElementById("gross_sep").innerHTML = formatNumber(sep);
				document.getElementById("gross_oct").innerHTML = formatNumber(oct);
				document.getElementById("gross_nov").innerHTML = formatNumber(nov);
				document.getElementById("gross_dec").innerHTML = formatNumber(dec);
				document.getElementById("gross_total").innerHTML = formatNumber(total);

				var jan_opr =  jan + parseFloat(ProfitLoss.cells(52,3).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),3).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),3).getValue());
				var feb_opr =  feb + parseFloat(ProfitLoss.cells(52,4).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),4).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),4).getValue());
				var mar_opr =  mar + parseFloat(ProfitLoss.cells(52,5).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),5).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),5).getValue());
				var apr_opr =  apr + parseFloat(ProfitLoss.cells(52,6).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),6).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),6).getValue());
				var mei_opr =  mei + parseFloat(ProfitLoss.cells(52,7).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),7).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),7).getValue());
				var jun_opr =  jun + parseFloat(ProfitLoss.cells(52,8).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),8).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),8).getValue());
				var jul_opr =  jul + parseFloat(ProfitLoss.cells(52,9).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),9).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),9).getValue());
				var agt_opr =  agt + parseFloat(ProfitLoss.cells(52,10).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),10).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),10).getValue());
				var sep_opr =  sep + parseFloat(ProfitLoss.cells(52,11).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),11).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),11).getValue());
				var oct_opr =  oct + parseFloat(ProfitLoss.cells(52,12).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),12).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),12).getValue());
				var nov_opr =  nov + parseFloat(ProfitLoss.cells(52,13).getValue())- parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),13).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),13).getValue());
				var dec_opr =  dec + parseFloat(ProfitLoss.cells(52,14).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),14).getValue()) - parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),14).getValue());
				
				var total_opr = jan_opr + feb_opr + mar_opr + apr_opr + mei_opr + jun_opr + jul_opr + agt_opr + sep_opr + oct_opr + nov_opr + dec_opr;
				document.getElementById("operational_jan").innerHTML = formatNumber(jan_opr);
				document.getElementById("operational_feb").innerHTML = formatNumber(feb_opr);
				document.getElementById("operational_mar").innerHTML = formatNumber(mar_opr);
				document.getElementById("operational_apr").innerHTML = formatNumber(apr_opr);
				document.getElementById("operational_mei").innerHTML = formatNumber(mei_opr);
				document.getElementById("operational_jun").innerHTML = formatNumber(jun_opr);
				document.getElementById("operational_jul").innerHTML = formatNumber(jul_opr);
				document.getElementById("operational_agt").innerHTML = formatNumber(agt_opr);
				document.getElementById("operational_sep").innerHTML = formatNumber(sep_opr);
				document.getElementById("operational_oct").innerHTML = formatNumber(oct_opr);
				document.getElementById("operational_nov").innerHTML = formatNumber(nov_opr);
				document.getElementById("operational_dec").innerHTML = formatNumber(dec_opr);
				document.getElementById("operational_total").innerHTML = formatNumber(total_opr);

				var jan_before_tax =  jan_opr + parseFloat(ProfitLoss.cells(6,3).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),3).getValue());
				var feb_before_tax =  feb_opr + parseFloat(ProfitLoss.cells(6,4).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),4).getValue());
				var mar_before_tax =  mar_opr + parseFloat(ProfitLoss.cells(6,5).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),5).getValue());
				var apr_before_tax =  apr_opr + parseFloat(ProfitLoss.cells(6,6).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),6).getValue());
				var mei_before_tax =  mei_opr + parseFloat(ProfitLoss.cells(6,7).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),7).getValue());
				var jun_before_tax =  jun_opr + parseFloat(ProfitLoss.cells(6,8).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),8).getValue());
				var jul_before_tax =  jul_opr + parseFloat(ProfitLoss.cells(6,9).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),9).getValue());
				var agt_before_tax =  agt_opr + parseFloat(ProfitLoss.cells(6,10).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),10).getValue());
				var sep_before_tax =  sep_opr + parseFloat(ProfitLoss.cells(6,11).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),11).getValue());
				var oct_before_tax =  oct_opr + parseFloat(ProfitLoss.cells(6,12).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),12).getValue());
				var nov_before_tax =  nov_opr + parseFloat(ProfitLoss.cells(6,13).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),13).getValue());
				var dec_before_tax =  dec_opr + parseFloat(ProfitLoss.cells(6,14).getValue()) + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.5'),14).getValue());

				var total_before_tax = jan_before_tax + feb_before_tax + mar_before_tax + apr_before_tax + mei_before_tax + jun_before_tax + jul_before_tax + agt_before_tax + sep_before_tax + oct_before_tax + nov_before_tax + dec_before_tax;
				document.getElementById("beforeTax_jan").innerHTML = formatNumber(jan_before_tax);
				document.getElementById("beforeTax_feb").innerHTML = formatNumber(feb_before_tax);
				document.getElementById("beforeTax_mar").innerHTML = formatNumber(mar_before_tax);
				document.getElementById("beforeTax_apr").innerHTML = formatNumber(apr_before_tax);
				document.getElementById("beforeTax_mei").innerHTML = formatNumber(mei_before_tax);
				document.getElementById("beforeTax_jun").innerHTML = formatNumber(jun_before_tax);
				document.getElementById("beforeTax_jul").innerHTML = formatNumber(jul_before_tax);
				document.getElementById("beforeTax_agt").innerHTML = formatNumber(agt_before_tax);
				document.getElementById("beforeTax_sep").innerHTML = formatNumber(sep_before_tax);
				document.getElementById("beforeTax_oct").innerHTML = formatNumber(oct_before_tax);
				document.getElementById("beforeTax_nov").innerHTML = formatNumber(nov_before_tax);
				document.getElementById("beforeTax_dec").innerHTML = formatNumber(dec_before_tax);
				document.getElementById("beforeTax_total").innerHTML = formatNumber(total_before_tax);
				var jan_afterTax =  jan_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),3).getValue());
				var feb_afterTax =  feb_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),4).getValue());
				var mar_afterTax =  mar_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),5).getValue());
				var apr_afterTax =  apr_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),6).getValue());
				var mei_afterTax =  mei_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),7).getValue());
				var jun_afterTax =  jun_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),8).getValue());
				var jul_afterTax =  jul_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),9).getValue());
				var agt_afterTax =  agt_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),10).getValue());
				var sep_afterTax =  sep_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),11).getValue());
				var oct_afterTax =  oct_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),12).getValue());
				var nov_afterTax =  nov_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),13).getValue());
				var dec_afterTax =  dec_before_tax + parseFloat(ProfitLoss.cells2(ProfitLoss.getRowIndex('522.8.6'),14).getValue());
				var total_afterTax = jan_afterTax + feb_afterTax + mar_afterTax + apr_afterTax + mei_afterTax + jun_afterTax + jul_afterTax + agt_afterTax + sep_afterTax + oct_afterTax + nov_afterTax + dec_afterTax;
				document.getElementById("afterTax_jan").innerHTML = formatNumber(jan_afterTax);
				document.getElementById("afterTax_feb").innerHTML = formatNumber(feb_afterTax);
				document.getElementById("afterTax_mar").innerHTML = formatNumber(mar_afterTax);
				document.getElementById("afterTax_apr").innerHTML = formatNumber(apr_afterTax);
				document.getElementById("afterTax_mei").innerHTML = formatNumber(mei_afterTax);
				document.getElementById("afterTax_jun").innerHTML = formatNumber(jun_afterTax);
				document.getElementById("afterTax_jul").innerHTML = formatNumber(jul_afterTax);
				document.getElementById("afterTax_agt").innerHTML = formatNumber(agt_afterTax);
				document.getElementById("afterTax_sep").innerHTML = formatNumber(sep_afterTax);
				document.getElementById("afterTax_oct").innerHTML = formatNumber(oct_afterTax);
				document.getElementById("afterTax_nov").innerHTML = formatNumber(nov_afterTax);
				document.getElementById("afterTax_dec").innerHTML = formatNumber(dec_afterTax);
				document.getElementById("afterTax_total").innerHTML = formatNumber(total_afterTax);
				
			}

			function formatNumber (amount) {
				if(Number.isNaN(amount)) {
					amount = 0;
				}
				return new Intl.NumberFormat().format(amount);
			}
		
			
	</script>
	
@endsection
