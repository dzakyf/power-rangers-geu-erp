<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	
	
$kursval = 1;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT REQUEST</div>

	<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
	<input type="button" id="AddGridBankOUT" NAME="AddGridBankOUT" value="Add Detail" class="btn btn-success mt-2 mr-2"/>
	<input type="button" id="DelGridBankOUT" NAME="DelGridBankOUT" value="Delete" class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="SaveGridBankOUT" NAME="SaveGridBankOUT" value="S A V E"  class="btn btn-info mt-2 mr-2"/>                
	<input type="button" id="SubmitGridBankOUT" NAME="SubmitGridBankOUT" value="SAVE AND SUBMIT" class="btn btn-info mt-2 mr-2" />                

   
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
			for (var i = 0; i < GridBankOUT.getRowsNum(); i++) {
				out += parseFloat(GridBankOUT.cells2(i, ind).getValue());
			}
			return out;
		}
		function sumColumn2(ind) {
			var out = 0;
			for (var i = 0; i < GridBankOUT.getRowsNum(); i++) {
				out += parseFloat(GridBankOUT.cells2(i, ind).getValue()) * parseFloat(GridBankOUT.cells2(i, 6).getValue());
			}
			return out;
		}
		function editcekBankOUT(rId,cInd,stage,nValue,oValue) {
				
			if (cInd == 1){
				WindoWCOA("Chart of Accounts","finance-bank-out-detail-coa",650,350);
			}
			return true;
		}

		 
		GridBankOUT = new dhtmlXGridObject('grid_kbterima');
		GridBankOUT.setImagePath("./dhtmlx/imgs/");
		GridBankOUT.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val,Department");
		GridBankOUT.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","",""]);
		GridBankOUT.setInitWidths("70,350,100,150,200,100,100,100");
		GridBankOUT.setColAlign("center,left,center,left,left,right,right,center");
		GridBankOUT.setColTypes("ro,ro,ro,ed,ed,edn,ron,combo");
		GridBankOUT.setColSorting("int,str,str,str,str,str,str,str");
		GridBankOUT.setDateFormat("%Y-%m-%d"); 
		GridBankOUT.setEditable(true);
		GridBankOUT.setNumberFormat("0,000",5,".",",");
		GridBankOUT.setNumberFormat("0,000",6,".",",");
		GridBankOUT.attachEvent("onRowDblClicked", editcekBankOUT);
		GridBankOUT.enableEditEvents(true,false,true);
		GridBankOUT.enableKeyboardSupport(true);
		GridBankOUT.setSkin("dhx_skyblue");
		PGridBankOUT = new dataProcessor("finance-bank-out-detail-update");
		PGridBankOUT.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"ID":"<?php echo $_GET['ID'] ?>"}

		})
		
		PGridBankOUT.setTransactionMode("POST", true);
		PGridBankOUT.setUpdateMode("Off");
		PGridBankOUT.init(GridBankOUT);
		GridBankOUT.init();
		dept = "BUSINESS";
		combocurr = GridBankOUT.getColumnCombo(7);
		if (dept == 'OVERHEAD'){
			combocurr.enableFilteringMode(true);
			combocurr.load("xml-finance-department");
		}else{
			
			combocurr.addOption(dept,dept);


		}
		refreshdatadetail();
				
		function refreshdatadetail(){
					GridBankOUT.clearAndLoad("xml-finance-bank-out-detail?ID=<?php echo $_GET['ID'];?>");

		}
		$('#SubmitGridBankOUT').click(function(){
			
			PGridBankOUT.sendData();
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

		$('#SaveGridBankOUT').click(function(){
									   
			PGridBankOUT.sendData();
			BankOUT.cells("<?php echo $_GET['ID']?>",6).setValue(sumColumn(5));
			BankOUT.cells("<?php echo $_GET['ID']?>",7).setValue(sumColumn2(5));
			PBankOUT.setUpdated("<?php echo $_GET['ID']?>",true);
			PBankOUT.sendData();

			alert("Data Successfully Saved!");
			setTimeout(function(){
				$("#DivBankDetail").html('');
				refreshdata();
			},1500);
									   
									   });

		

		$('#AddGridBankOUT').click(function(){
			if(GridBankOUT.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=GridBankOUT.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
					var today = new Date();
					var dd = today.getDate();
					var mm = today.getMonth()+1; //January is 0!
				
					var yyyy = today.getFullYear();		
					var tanggals = "<?php echo date("Y-m-d")?>";
					if(dd < 10){dd='0'+dd} if(mm<10){mm='0'+mm} var today = yyyy + '-' + mm + '-' + dd;
					if (tanggals == '') skrg = today ; else skrg = tanggals;
					
					dept = "<?php echo $_GET['Department']?>";
					if (dept == 'OVERHEAD') $dept = '';
					GridBankOUT.addRow(GridBankOUT.uid(), [nums_reqrow,'',skrg,'','',0,'<?php echo $kursval;?>',dept],GridBankOUT.getRowsNum());
					GridBankOUT.selectRow(nomorrowsreq);
									});

		$('#DelGridBankOUT').click(function(){

			dhxml.confirm('Batalkan Tasklog?', 'Bank Management', function(r) {
					
				if (r==true){
					GridBankOUT.deleteSelectedRows();
					
					
					
				}
			})

		});
		function UpdateGrid() {
			var out = 0;
			for (var i = 0; i < GridBankOUT.getRowsNum(); i++) {
				rowIdMov=GridBankOUT.getRowId(i);
				PGridBankOUT.setUpdated(rowIdMov,true);
			}
			
			return out;
		}


		</script>
