<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('bank_handler')->where('NIK_handler',$Saya)->first();
	if($cekakun->Account_No == ''){
		
		echo "You Don't have access to this menu!";
		exit;
	}
	
	$priv =  Auth::user()->Priv_ID;
	
	
$getcur = DB::table("currency")->where("Currency_Symbol",$_GET['Currency'])->first();
$kursval = $getcur->Currency_Value;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">BANK OUT DETAIL</div>

	<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
	<input type="button" id="AddGridBankIN" NAME="AddGridBankIN" value="Add Detail" />
	<input type="button" id="DelGridBankIN" NAME="DelGridBankIN" value="Delete" />
	<input type="button" id="SaveGridBankIN" NAME="SaveGridBankIN" value="S A V E" />                
	<input type="button" id="SubmitGridBankIN" NAME="SubmitGridBankIN" value="SAVE AND SUBMIT" />                

   
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


		function sumColumn(ind) {
			var out = 0;
			for (var i = 0; i < GridBankIN.getRowsNum(); i++) {
				out += parseFloat(GridBankIN.cells2(i, ind).getValue());
			}
			return out;
		}
		function sumColumn2(ind) {
			var out = 0;
			for (var i = 0; i < GridBankIN.getRowsNum(); i++) {
				out += parseFloat(GridBankIN.cells2(i, ind).getValue()) * parseFloat(GridBankIN.cells2(i, 6).getValue());
			}
			return out;
		}
		function editcekBankIN(rId,cInd,stage,nValue,oValue) {
				
				if (cInd==1){
					WindoWCOA("Chart of Accounts","finance-bank-in-detail-coa",650,550);
				}
			
			
			return true;
		}

		 
		GridBankIN = new dhtmlXGridObject('grid_kbterima');
		GridBankIN.setImagePath("./dhtmlx/imgs/");
		GridBankIN.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val");
		GridBankIN.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total",""]);
		GridBankIN.setInitWidths("70,350,100,150,200,100,100");
		GridBankIN.setColAlign("center,left,center,left,left,right,right");
		GridBankIN.setColTypes("ro,ro,ro,ed,ed,edn,ron");
		GridBankIN.setColSorting("int,str,str,str,str,str,str");
		GridBankIN.setDateFormat("%Y-%m-%d"); 
		GridBankIN.setEditable(true);
		GridBankIN.setNumberFormat("0,000",5,".",",");
		GridBankIN.setNumberFormat("0,000",6,".",",");
		GridBankIN.attachEvent("onRowDblClicked", editcekBankIN);
		GridBankIN.enableKeyboardSupport(true);
		GridBankIN.setSkin("dhx_skyblue");
		PGridBankIN = new dataProcessor("finance-bank-in-detail-update");
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
		refreshdatadetail();
		
function refreshdatadetail(){
			GridBankIN.clearAndLoad("xml-finance-bank-in-detail?ID=<?php echo $_GET['ID'];?>");

}
		$('#SubmitGridBankIN').click(function(){
			

			
			PGridBankIN.sendData();
			BankIN.cells("<?php echo $_GET['ID']?>",6).setValue(sumColumn(5));
			BankIN.cells("<?php echo $_GET['ID']?>",7).setValue(sumColumn2(5));
			BankIN.cells("<?php echo $_GET['ID']?>",10).setValue('CLOSED');
			PBankIN.setUpdated("<?php echo $_GET['ID']?>",true);
			PBankIN.sendData();

			alert("Data Successfully Submitted!");
			setTimeout(function(){
				$("#DivBankDetail").html('');
				refreshdata();
			},1500);
									   
		});

		$('#SaveGridBankIN').click(function(){
									   
			PGridBankIN.sendData();
			BankIN.cells("<?php echo $_GET['ID']?>",6).setValue(sumColumn(5));
			BankIN.cells("<?php echo $_GET['ID']?>",7).setValue(sumColumn2(5));
			PBankIN.setUpdated("<?php echo $_GET['ID']?>",true);
			PBankIN.sendData();

			alert("Data Successfully Saved!");
			setTimeout(function(){
				GridBankIN.clearAll();
				refreshdata();
			},1500);
									   
									   });

		

		$('#AddGridBankIN').click(function(){
			if(GridBankIN.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=GridBankIN.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
					var today = new Date();
					var dd = today.getDate();
					var mm = today.getMonth()+1; //January is 0!
				
					var yyyy = today.getFullYear();		
					var tanggals = "<?php echo $_GET['Tgl']?>";
					if(dd < 10){dd='0'+dd} if(mm<10){mm='0'+mm} var today = yyyy + '-' + mm + '-' + dd;
					if (tanggals == '') skrg = today ; else skrg = tanggals;
					GridBankIN.addRow(GridBankIN.uid(), [nums_reqrow,'',skrg,'','',0,'<?php echo $kursval;?>'],GridBankIN.getRowsNum());
					GridBankIN.selectRow(nomorrowsreq);
									});

		$('#DelGridBankIN').click(function(){

			dhxml.confirm('Are you sure want to delete this data?', 'Bank Management', function(r) {
					
				if (r==true){
					GridBankIN.deleteSelectedRows();
					
					
					
				}
			})

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
