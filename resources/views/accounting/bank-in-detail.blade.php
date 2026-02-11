<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('cash_handler')->where('NIK_handler',$Saya)->first();
	if($cekakun->Account_No == ''){
		
		echo "You Don't have access to this menu!";
		exit;
	}
	
	$priv =  Auth::user()->Priv_ID;
	
	
$getcur = DB::table("currency")->where("Currency_Symbol",$_GET['Currency'])->first();
$kursval = $getcur->Currency_Value;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">BANK IN DETAIL</div>

	<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="SubmitGridBankIN" NAME="SubmitGridBankIN" value="POSTING!" class="btn btn-info mt-2 mr-2" />                

   
	<div id="WinCOA">
	</div>
		


<script>
var winCOA;
var WCOA;
function WindoWCOA(text,url,width,height) {
    winCOA = new dhtmlXWindows();

	winCOA.attachViewportTo('PagesContent');
    WCOA = winCOA.createWindow("WCOA", 0, 0, width, height);
    WCOA.keepInViewport(false);
	WCOA.setText(text);
    WCOA.attachURL(url, true);
	WCOA.button("minmax1").hide();
    WCOA.button("park").hide();
	WCOA.centerOnScreen();
	WCOA.allowMove();	
}
	function editcekBankIN(rId,cInd,stage,nValue,oValue) {
				
			if (cInd == 1){
				WindoWCOA("Chart of Accounts","finance-bank-in-detail-coa",650,350);
			}
			return true;
		}
		GridBankIN = new dhtmlXGridObject('grid_kbterima');
		GridBankIN.setImagePath("./dhtmlx/imgs/");
		GridBankIN.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val,Department");
		GridBankIN.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","",""]);
		GridBankIN.setInitWidths("70,350,100,150,200,100,100,100");
		GridBankIN.setColAlign("center,left,center,left,left,right,right,center");
		GridBankIN.setColTypes("ro,ro,ro,ed,ed,edn,ron,ro");
		GridBankIN.setColSorting("int,str,str,str,str,str,str,str");
		GridBankIN.setDateFormat("%Y-%m-%d"); 
		GridBankIN.setEditable(true);
		GridBankIN.setNumberFormat("0,000",5,".",",");
		GridBankIN.setNumberFormat("0,000",6,".",",");
		GridBankIN.attachEvent("onRowDblClicked", editcekBankIN);
		GridBankIN.enableEditEvents(true,false,true);
		GridBankIN.enableKeyboardSupport(true);
		GridBankIN.setSkin("dhx_skyblue");
		PGridBankIN = new dataProcessor("accounting-bank-in-detail-update");
		PGridBankIN.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"ID":"<?php echo $_GET['ID'] ?>"}

		})
		
		PGridBankIN.setTransactionMode("POST", true);
		PGridBankIN.setUpdateMode("Off");
		PGridBankIN.init(GridBankIN);
		GridBankIN.init();
		combocurr = GridBankIN.getColumnCombo(7);

		combocurr.enableFilteringMode(true);
		combocurr.load("xml-finance-department");
		
		combocurrx = GridBankIN.getColumnCombo(1);
		combocurrx.enableFilteringMode(true);
		combocurrx.load("xml-finance-coa");
		
		
		refreshdatadetail();
				
		function refreshdatadetail(){
					GridBankIN.clearAndLoad("xml-accounting-bank-in-detail?ID=<?php echo $_GET['ID'];?>",UpdateGrid);

		}
		$('#SubmitGridBankIN').click(function(){
			
			PGridBankIN.sendData();
			
			alert("Data Successfully Posted!");
			setTimeout(function(){
				$("#DivBankDetail").html('');
				refreshdata();
			},1500);
									   
		});

		function UpdateGrid() {
			var out = 0;
			for (var i = 0; i < GridBankIN.getRowsNum(); i++) {
				rowIdMov=GridBankIN.getRowId(i);
				PGridBankIN.setUpdated(rowIdMov,true);
			}
			
			return out;
		}


		</script>
