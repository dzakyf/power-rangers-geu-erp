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


<div class="JudulContent" style="color:#699;background-image:url(./_CSS_/hdr_back.gif);">NEW REQUISITION BASED ON PO CUSTOMER </div>
<div id="ContentPages">
    <div class="FormInput_Form_Content" >
		<input type="hidden" id="Task_ID" size="15" value="<?php echo $Req_ID;?>"/>        	
		<div class="FormInput_left">
        		
               PO No
        
        </div>
		<div class="FormInput_right">
        
				<select id='PO_No' onchange="loadpolists()">
					<option value=""></option>
					<?php
					$q = DB::select(DB::raw("SELECT * FROM opportunity WHERE Status!='CANCELED' Order By ID DESC"));
					foreach ($q as $fa){
						$qx = DB::table("opportunity_detail")->where("Task_ID",$fa->Task_ID)->first();
						if($qx){
							echo "<option value='$qx->Task_ID'>$qx->PO_No - $fa->Oppor_Subject</option>";	
						}
					}
					?>
					</select>
         
         </div>
   		<div class="FormInput_left">
        		
               Priority
        
        </div>
		<div class="FormInput_right">
        
        
				<select id="Priority" onchange="gantitab(this.value,7)">
                    <option value="">Select</option>
                    <option>NORMAL</option>
                    <option>URGENT</option>
                </select>
         
         </div>
   		
   		<div class="FormInput_left">
        		
               Open Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Start_Date" onblur="gantitab(this.value,5)" size="15"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Close Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="End_Date" onblur="gantitab(this.value,6)" size="15"/>        	
         
         </div>
		 <div class="FormInput_left" style="height:100px">
        		
               Detail Requirement
        
        </div>
		<div class="FormInput_right" style="height:100px">
        
				<textarea cols="40" rows="3" id="Description" onblur="gantitab(this.value,8)"></textarea>        	
         
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
		SelectPO.setHeader("Select,PO No.,Department,Position,Qty Person,Start Date,End Date,Priority,Description");
		SelectPO.setInitWidths("70,170,250,200,100,120,100,100,100,1000");
		SelectPO.setColAlign("center,center,center,center,center,center,center,center,center,left");
		SelectPO.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,rotxt");
		SelectPO.setColSorting("int,str,str,str,int,str,str,str,str");
		SelectPO.setDateFormat("%Y-%m-%d"); 
		SelectPO.setEditable(true);
		PSelectPO = new dataProcessor("sales-po-lists-update");
		PSelectPO.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"Task_ID":"<?php echo $Req_ID ?>"}

		})
		SelectPO.enableEditEvents(true,false,true);
		SelectPO.enableKeyboardSupport(true);
		SelectPO.setSkin("dhx_skyblue");
		PSelectPO.setUpdateMode("Off");
		PSelectPO.init(SelectPO);
		SelectPO.init();
		
function loadpolists(){
		SelectPO.clearAndLoad("xml-sales-po-lists-detail?Task_ID=" + $("#PO_No").val());
	
}

 
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

 	
</script>
