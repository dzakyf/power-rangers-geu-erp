<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	$Fetch = DB::table("cash")->where("ID",$_GET['ID'])->first();
	$Recurring_ID = "RID-KSPS-". date("ymdhis");

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">RECURRING DETAIL</div>

<div id="CashTransferDiv">
    <div class="FormInput_Form_Content">
    		<div class="FormInput_left">
        		
                From COA (Debet)
        
        	</div>
			<div class="FormInput_right">
        	        	<input type="hidden" id="Recurring_ID" size="25" value="<?php echo $Recurring_ID?>" readonly="readonly"/>   
        	        	<input type="hidden" id="Total" size="25" value="<?php echo $Fetch->Total?>" readonly="readonly"/>   
        	        	<input type="hidden" id="Task_ID" size="25" value="<?php echo $Fetch->Cash_No?>" readonly="readonly"/>   
        	        	<input type="hidden" id="From_Acc" size="25" value="<?php echo $Fetch->Description?>" readonly="readonly"/>   

                <?php
					echo $Fetch->Description;
				?>
                
            </select>
         
            </div>

         <div class="FormInput_left">
        		
                Start Exec Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Exec_Date" size="25" readonly="readonly"/>        	
         
         </div>

   		<div class="FormInput_left">
        		
                Period
        
        </div>
		<div class="FormInput_right">
        
				<select id="periode">
				<option>MONTHLY</option>
				<option>YEARLY</option>
				<option>QUARTERLY</option>
				<option>SEMESTER</option>
				<option>TRIMESTER</option>
				</select>
				
         
         </div>
		  <div class="FormInput_left">
        		
                Recurring Times
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Recurring_Times" size="5" value="1"/>        	
         
         </div>
   		<div class="FormInput_left" style="height:75px;">
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:75px;">
        		<textarea id="Note" cols="80" rows="3"></textarea>

         </div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CREDIT DETAIL</div>
		 
		<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<div id="divtom">
			<input type="button" id="AddGridCash" NAME="AddGridCash" value="Add Detail" class="btn btn-success mt-2 mr-2"/>
			<input type="button" id="DelGridCash" NAME="DelGridCash" value="Delete" class="btn btn-danger mt-2 mr-2"  />
			                  <input type="button" value="CREATE" id="SubmitTransfer" onclick="SubmitTerima();"  class="btn btn-success mt-2 mr-2"/>        	
               

		   
			<div id="WinCOA">
		</div>
		

	</div>
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
				var sellx = GridCash.cells(GridCash.getSelectedId(),0).getValue();
				
				if (sellx != '0'){
					
					if (cInd==1){
						WindoWCOA("Chart of Accounts","recurring-cash-out-detail-coa",650,350);
					
					}
				
				}
			
			return true;
		}

		 
		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Account No,Date,Reference,Remark,Debet,Credit Val,Department");
		GridCash.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","#stat_total",""]);
		GridCash.setInitWidths("70,350,100,150,200,100,100,100");
		GridCash.setColAlign("center,left,center,left,left,right,right,center");
		GridCash.setColTypes("ro,ro,dhxCalendar,ed,ed,edn,edn,combo");
		GridCash.setColSorting("int,str,str,str,str,str,str");
		GridCash.setDateFormat("%Y-%m-%d"); 
		GridCash.setEditable(true);
		GridCash.setNumberFormat("0,000",5,".",",");
		GridCash.setNumberFormat("0,000",6,".",",");
		GridCash.enableEditEvents(true,false,true);

		GridCash.attachEvent("onRowDblClicked", editcekcashout);
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		PGridCash = new dataProcessor("accounting-recurring-cash-out-detail-update");
		PGridCash.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"ID":"<?php echo $Recurring_ID ?>","Cash_No":"<?php echo $_GET['ID'] ?>"}

		})
		
		PGridCash.setUpdateMode("Off");
		PGridCash.init(GridCash);
		GridCash.init();
		combocurr = GridCash.getColumnCombo(7);
			combocurr.enableFilteringMode(true);
			combocurr.load("xml-finance-department");
		refreshdatadetail();

function refreshdatadetail(){
					
			GridCash.clearAndLoad("xml-accounting-recurring-cash-out-detail?ID=<?php echo $_GET['ID'];?>",UpdateGrid);

}

		
		$('#AddGridCash').click(function(){
			if(GridCash.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=GridCash.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
					
					var tanggals = $("#Exec_Date").val();
					GridCash.addRow(nomorrowsreq, [nums_reqrow,'',tanggals,'','',0,0]);
					GridCash.selectRow(nomorrowsreq);
									});

		$('#DelGridCash').click(function(){
			
			var getid = GridCash.getSelectedId();
			var sellx = GridCash.cells(GridCash.getSelectedId(),0).getValue();

			if (sellx == 0 || sellx == '0'){
				alert("You cannot delete this data!");
			}else{
				
				dhtmlx.confirm('Are you sure want to delete this data?', 'Recurring Management', function(r) {
						
					if (r==true){
						GridCash.deleteSelectedRows();
						
						
						
					}
				})
			}

		});
		function UpdateGrid() {
			var out = 0;
			for (var i = 0; i < GridCash.getRowsNum(); i++) {
				rowIdMov=GridCash.getRowId(i);
				PGridCash.setUpdated(rowIdMov,true);
			}
			/*var tanggals = $("#Exec_Date").val();
				GridCash.addRow(GridCash.uid(),[0,'<?php echo $Fetch->Description?>','<?php echo $Fetch->Cash_Date?>','<?php echo $Fetch->Voucher_No ?>','',0,'<?php echo $Fetch->Total?>'],0);
			*/
			return out;
		}
	
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Exec_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

 				
function getAllValues() {
    var inputValues = [];
    $('#CashTransferDiv input,select,textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }
        else if (type != "button" || type != "submit") {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
      	 } else if ($this.is("textarea")) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }
    })
	
    return inputValues.join('&');
}

function SubmitTerima(){
	dapat = getAllValues();
	PGridCash.sendData();

	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "accounting-recurring-cash-out-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Recurring Cash Out was Created!');

				setTimeout(function(){
						
					$("#DivCashDetail").html('');
					
					location.href='accounting-recurring-cash-out';
				},1000);				

			}else{
				
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "error" );
	
		}
	});
}

</script>
