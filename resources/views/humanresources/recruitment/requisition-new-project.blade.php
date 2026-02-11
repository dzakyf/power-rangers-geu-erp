<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;

$Req_ID = "R-".date("131-y-md-his").rand();	


?>


<div class="JudulContent" style="color:#699;background-image:url(./_CSS_/hdr_back.gif);">NEW REQUISITION BASED ON PROJECT </div>
<div id="ContentPages">
    <div class="FormInput_Form_Content" >
		<input type="hidden" id="Task_ID" size="15" value="<?php echo $Req_ID;?>"/>        	
		<div class="FormInput_left">
        		
               PO No
        
        </div>
		<div class="FormInput_right">
        
				<select id='PO_No' onchange="refreshgrid()">
					<option value=""></option>
					<?php
					$q = DB::select(DB::raw("SELECT * FROM project_mgt WHERE Status!='CANCELED' Order By ID DESC"));
					foreach ($q as $fa){
						$qx = DB::table("project_detail_requisition")->where("Task_ID",$fa->Task_ID)->first();
						if($qx){
							echo "<option value='$fa->Task_ID'>$fa->Task_ID :: $fa->Description</option>";	
						}
					}
					?>
					</select>
         
         </div>
   		   		
   		<div class="FormInput_left">
        		
               Open Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Start_Date" onblur="gantitab(this.value,10)" size="15"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Close Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="End_Date" onblur="gantitab(this.value,11)" size="15"/>        	
         
         </div>
		 <div id="SelectPO" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>

		
				<div class="FormInput_right" style="height:175px;vertical-align:bottom;">

					<input type="button" value="Submit" id="UpdateReq" onclick="UpdateReq();" class="btn btn-info mt-2 mr-2"  />        	

				</div>		

		
  </div>
</div>

<script>
function gantitab(isinya,nomor){
	if(SelectPO.getRowsNum() > 0){
		
		var out = 0;
		for (var i = 0; i < SelectPO.getRowsNum(); i++) {
			rowIdMov=SelectPO.getRowId(i);
			SelectPO.cells(rowIdMov,nomor).setValue(isinya);
		}
	}
	return true;
}


function UpdateReq(){
	PSelectPO.sendData();
	alert('Data Requisition Submitted!');

	setTimeout(function(){
		refreshdata();
	
		$("#divlistRequisition").html('');
		
	},1000);				

	
}
		SelectPO = new dhtmlXGridObject('SelectPO');
		SelectPO.setImagePath("./dhtmlx/imgs/");
		SelectPO.setHeader("No.,Department,Position,Qty Person,Detail Requirement,Detail Position,Total Amount(IDR),Site,Salary Offering,Benefit Information,Open Date,Close Date");
		SelectPO.attachFooter(["Total",'#cspan','#cspan','#cspan',"#cspan","#cspan","#stat_total",,,,,]);
		SelectPO.setInitWidths("70,120,150,100,250,250,200,100,120,250,100,100");
		SelectPO.setColAlign("center,left,center,left,left,left,left,center,right,right,center,left,center,center");
		SelectPO.setColTypes("ro,ed,ed,ed,txt,txt,edn,ed,edn,txt,ro,ro");
		SelectPO.setColSorting("int,str,str,str,str,str,int,str,int,str,str,str");
		PSelectPO = new dataProcessor("requisition-new-project-update");
		PSelectPO.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  
			})
			
		PSelectPO.setTransactionMode("POST", true);
		PSelectPO.setUpdateMode("Off");
		PSelectPO.init(SelectPO);
		SelectPO.setDateFormat("%Y-%m-%d"); 
		SelectPO.enableMultiline(true);
		SelectPO.setEditable(false);
		SelectPO.setNumberFormat("0,000",6,".",",");
		SelectPO.setNumberFormat("0,000",8,".",",");
		SelectPO.enableEditEvents(true,false,true);
		SelectPO.enableKeyboardSupport(true);
		SelectPO.setSkin("dhx_skyblue");
		SelectPO.init();
		

	function refreshgrid(){
			SelectPO.clearAndLoad("xml-requisition-new-detail?Task_ID=" + $("#PO_No").val());
		
	}
	

 
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

 	
</script>
