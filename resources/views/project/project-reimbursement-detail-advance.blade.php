<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
$kursval =1;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT ADVANCE/PUM DETAIL</div>

	<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	


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
			for (var i = 0; i < GridCash.getRowsNum(); i++) {
				out += parseFloat(GridCash.cells2(i, ind).getValue());
			}
			return out;
		}
		function sumColumn2(ind) {
			var out = 0;
			for (var i = 0; i < GridCash.getRowsNum(); i++) {
				out += parseFloat(GridCash.cells2(i, ind).getValue()) * parseFloat(GridCash.cells2(i, 6).getValue());
			}
			return out;
		}
		function editcekcashout(rId,cInd,stage,nValue,oValue) {
				
				if (cInd==1){
					WindoWCOA("Chart of Accounts","finance-cash-out-detail-coa",650,350);
				}
			
			
			return true;
		}

		 
		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val");
		GridCash.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","",""]);
		GridCash.setInitWidths("70,0,100,150,300,100,100,0");
		GridCash.setColAlign("center,left,center,left,left,right,right,center");
		GridCash.setColTypes("ro,ro,ro,ed,ed,edn,ron,combo");
		GridCash.setColSorting("int,str,str,str,str,str,str");
		GridCash.setDateFormat("%Y-%m-%d"); 
		GridCash.setEditable(false);
		GridCash.setNumberFormat("0,000",5,".",",");
		GridCash.setNumberFormat("0,000",6,".",",");
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		PGridCash = new dataProcessor("project-reimbursement-detail-advance-update");
		PGridCash.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"ID":"<?php echo $_GET['ID'] ?>"}

		})
		
		PGridCash.setUpdateMode("Off");
		PGridCash.init(GridCash);
		GridCash.init();
		refreshdatadetail();

function refreshdatadetail(){
			GridCash.clearAndLoad("xml-project-reimbursement-detail-advance?ID=<?php echo $_GET['ID'];?>",UpdateGrid);

}
		$('#SubmitGridCash').click(function(){
			

			
			PGridCash.sendData();
			ProjectReimbursementDetail.cells("<?php echo $_GET['ID'];?>",12).setValue("SUBMIT");
			PProjectReimbursementDetail.setUpdated("<?php echo $_GET['ID'];?>",true);
				
			alert("Data Successfully Submitted!");
			setTimeout(function(){
				PProjectReimbursementDetail.sendData();
				$("#ProjectReimbursementDetailDiv2").html('');
				refreshdetailreimbursement();
				refreshdatadetail();
			},1500);
									   
		});

		$('#SaveGridCash').click(function(){
									   
			PGridCash.sendData();
			
			alert("Data Successfully Saved!");
			setTimeout(function(){
				GridCash.clearAll();
				refreshdatadetail();
				refreshdetailreimbursement();
			},1500);
									   
									   });

		

		$('#AddGridCash').click(function(){
			if(GridCash.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=GridCash.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
					var today = new Date();
					var dd = today.getDate();
					var mm = today.getMonth()+1; //January is 0!
				
					var yyyy = today.getFullYear();		
					var tanggals = "<?php echo $_GET['Tgl']?>";
					if(dd < 10){dd='0'+dd} if(mm<10){mm='0'+mm} var today = yyyy + '-' + mm + '-' + dd;
					if (tanggals == '') skrg = today ; else skrg = tanggals;
					GridCash.addRow(GridCash.uid(), [nums_reqrow,'',skrg,'','',0,'<?php echo $kursval;?>'],GridCash.getRowsNum());
					GridCash.selectRow(nomorrowsreq);
									});

		$('#DelGridCash').click(function(){

			dhxml.confirm('Are you sure want to delete this data?', 'Reimbursement Management', function(r) {
					
				if (r==true){
					GridCash.deleteSelectedRows();
					
					
					
				}
			})

		});
		function UpdateGrid() {
			var out = 0;
			for (var i = 0; i < GridCash.getRowsNum(); i++) {
				rowIdMov=GridCash.getRowId(i);
				PGridCash.setUpdated(rowIdMov,true);
			}
			
			return out;
		}


		</script>
