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
	<input type="button" id="AddGridBank" NAME="AddGridBank" value="Add Detail" />
	<input type="button" id="DelGridBank" NAME="DelGridBank" value="Delete" />
	<input type="button" id="SaveGridBank" NAME="SaveGridBank" value="S A V E" />                
	<input type="button" id="SubmitGridBank" NAME="SubmitGridBank" value="SAVE AND SUBMIT" />                

   
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
			for (var i = 0; i < GridBank.getRowsNum(); i++) {
				out += parseFloat(GridBank.cells2(i, ind).getValue());
			}
			return out;
		}
		function sumColumn2(ind) {
			var out = 0;
			for (var i = 0; i < GridBank.getRowsNum(); i++) {
				out += parseFloat(GridBank.cells2(i, ind).getValue()) * parseFloat(GridBank.cells2(i, 6).getValue());
			}
			return out;
		}
		function editcekbankout(rId,cInd,stage,nValue,oValue) {
				
				if (cInd==1){
					WindoWCOA("Chart of Accounts","finance-bank-out-detail-coa",650,550);
				}
			
			
			return true;
		}

		 
		GridBank = new dhtmlXGridObject('grid_kbterima');
		GridBank.setImagePath("./dhtmlx/imgs/");
		GridBank.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val");
		GridBank.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total",""]);
		GridBank.setInitWidths("70,350,100,150,200,100,100");
		GridBank.setColAlign("center,left,center,left,left,right,right");
		GridBank.setColTypes("ro,ro,ro,ed,ed,edn,ron");
		GridBank.setColSorting("int,str,str,str,str,str,str");
		GridBank.setDateFormat("%Y-%m-%d"); 
		GridBank.setEditable(true);
		GridBank.setNumberFormat("0,000",5,".",",");
		GridBank.setNumberFormat("0,000",6,".",",");
		GridBank.attachEvent("onRowDblClicked", editcekbankout);
		GridBank.enableKeyboardSupport(true);
		GridBank.setSkin("dhx_skyblue");
		PGridBank = new dataProcessor("finance-bank-out-detail-update");
		PGridBank.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"ID":"<?php echo $_GET['ID'] ?>"}

		})
		
		PGridBank.setUpdateMode("Off");
		PGridBank.init(GridBank);
		GridBank.init();
		refreshdatadetail();
		
function refreshdatadetail(){
			GridBank.clearAndLoad("xml-finance-bank-out-detail?ID=<?php echo $_GET['ID'];?>");

}
		$('#SubmitGridBank').click(function(){
			

			
			PGridBank.sendData();
			BankOUT.cells("<?php echo $_GET['ID']?>",6).setValue(sumColumn(5));
			BankOUT.cells("<?php echo $_GET['ID']?>",7).setValue(sumColumn2(5));
			BankOUT.cells("<?php echo $_GET['ID']?>",10).setValue('CLOSED');
			PBankOUT.setUpdated("<?php echo $_GET['ID']?>",true);
			PBankOUT.sendData();

			alert("Data Successfully Submitted!");
			setTimeout(function(){
				$("#DivBankDetail").html('');
				refreshdata();
			},1500);
									   
		});

		$('#SaveGridBank').click(function(){
									   
			PGridBank.sendData();
			BankOUT.cells("<?php echo $_GET['ID']?>",6).setValue(sumColumn(5));
			BankOUT.cells("<?php echo $_GET['ID']?>",7).setValue(sumColumn2(5));
			PBankOUT.setUpdated("<?php echo $_GET['ID']?>",true);
			PBankOUT.sendData();

			alert("Data Successfully Saved!");
			setTimeout(function(){
				GridBank.clearAll();
				refreshdata();
			},1500);
									   
									   });

		

		$('#AddGridBank').click(function(){
			if(GridBank.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=GridBank.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
					var today = new Date();
					var dd = today.getDate();
					var mm = today.getMonth()+1; //January is 0!
				
					var yyyy = today.getFullYear();		
					var tanggals = "<?php echo $_GET['Tgl']?>";
					if(dd < 10){dd='0'+dd} if(mm<10){mm='0'+mm} var today = yyyy + '-' + mm + '-' + dd;
					if (tanggals == '') skrg = today ; else skrg = tanggals;
					GridBank.addRow(GridBank.uid(), [nums_reqrow,'',skrg,'','',0,'<?php echo $kursval;?>'],GridBank.getRowsNum());
					GridBank.selectRow(nomorrowsreq);
									});

		$('#DelGridBank').click(function(){

			dhxml.confirm('Are you sure want to delete this data?', 'Bank Management', function(r) {
					
				if (r==true){
					GridBank.deleteSelectedRows();
					
					
					
				}
			})

		});
		function UpdateGrid() {
			var out = 0;
			for (var i = 0; i < GridBank.getRowsNum(); i++) {
				rowIdMov=GridBank.getRowId(i);
				PGridBank.setUpdated(rowIdMov,true);
			}
			
			return out;
		}


		</script>
