<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->Emp_ID;
	$data = DB::table("stock_request")->where("SR_No",$_GET['ID'])->first();
	
	?>

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">APPROVAL STOCK REQUEST</div>
		<div id="ContentPagex">
			<div class="FormInput_Form_Content">
				<div class="FormInput_left">
						
					   TASK No
				
				</div>
				<div class="FormInput_right">
						<?php echo $data->SR_No; ?>
				 
				 </div>
				 <div class="FormInput_left">
						
					   Date Request
				
				</div>
				<div class="FormInput_right">
						<?php echo $data->SR_Date; ?>
				 
				 </div>
				 
				
				<div class="FormInput_left">
						
						User Request
				
				</div>
				<div class="FormInput_right">
					<?php echo $data->Request_By; ?>
						
				 </div>

				<div class="FormInput_left">
						
						Department
				
				</div>
				<div class="FormInput_right">
				
						<?php echo $data->Department; ?>

						<input type="hidden" value="<?php echo $data->SR_No; ?>" id="SR_No">
				 
				 </div>
					
				<div class="FormInput_left">
						
					  Request To
				
				</div>
				<div class="FormInput_right">
									<?php echo $data->WH_Dest; ?>

				

				 
				 </div>
				<div class="FormInput_left">
						
						FR No
				
				</div>
				<div class="FormInput_right">
				
					<?php echo $data->Reference; ?>

				 
				 </div>

				<div class="FormInput_left">
						
						Category
				
				</div>
				<div class="FormInput_right">
			
					<?php echo $data->Stock_Category; ?>
				 </div>
				 
				<div class="FormInput_left">
						
					   Date Pick Up
				
				</div>
				<div class="FormInput_right">
				
						<?php echo $data->Date_Pickup; ?>      	
				 
				 </div>
				<div class="FormInput_left">
						
					   Note Request
				
				</div>
				<div class="FormInput_right">
				
						<?php echo $data->Remark; ?>      	
				 
				 </div>
				<div class="FormInput_left">
						
						Note Approval
				
				</div>
				<div class="FormInput_right" style="height:80px;">
						<textarea id="Note" cols="100" rows="2"></textarea>

				 </div>
			<div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<div id="divtom">
			<input type="button" id="DelPRNewDetail" NAME="DelPRNewDetail" value="Reject" />
			<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Approve" />                
			</div>
		
<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Date_Pickup"]);
myCalendar.setDateFormat("%Y-%m-%d");
	

PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
PRNewDetail.setImagePath("./dhtmlx/imgs/");
PRNewDetail.setHeader("No,Category,Product Code,Product Desc,Request Qty,Approved Qty,Unit,Current Stock,Keterangan,Prices");
PRNewDetail.setInitWidths("50,100,100,auto,200,200,100,0,200,0");
PRNewDetail.setColTypes("ro,ro,ro,ro,ron,edn,ro,ro,txt,ro");
PRNewDetail.setColAlign("center,left,center,left,right,left,center,center,right");
PRNewDetail.setColSorting("int,str,str,str,str,str,str,str");
PRNewDetail.setEditable(true);
PRNewDetail.setNumberFormat("0,000.00",5,".",",");
PRNewDetail.enableKeyboardSupport(true);
PRNewDetail.setSkin("dhx_skyblue");
PPRNewDetail = new dataProcessor("inventory-request-management-detail-update");
PPRNewDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
		  payload: {"SR_No":"<?php echo $_GET['ID'] ?>"}

    })
	
PPRNewDetail.setTransactionMode("POST", true);
PPRNewDetail.setUpdateMode("Off");
PPRNewDetail.init(PRNewDetail);
PRNewDetail.init();
refreshdata();

function refreshdata(){
	PRNewDetail.clearAndLoad('xml-inventory-request-management-detail?SR_No=<?php echo $_GET['ID']?>',updateGrid);

}
function updateGrid() {
	var out = 0;
	for (var i = 0; i < PRNewDetail.getRowsNum(); i++) {
		rowIdMov=PRNewDetail.getRowId(i);
		PPRNewDetail.setUpdated(rowIdMov,true);
	}

	return out;
}

$("#DelPRNewDetail").click(function(){
		
	PRNewDetail.deleteSelectedRows();
	
})




function getAllValues() {
		var inputValues = [];
		$('#ContentPagex input,select,textarea').each(function() {
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
$('#SubmitCustDetail').click(function(){
	PPRNewDetail.sendData();
	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "inventory-request-management-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
				PPRNewDetail.sendData();
					alert('Data Inventory Request Approved!');
					setTimeout(function(){
						location.href="approval-stock";
					},2000);
			}else{
				alert('Inventory Request Failed to Submit!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})



</script>
