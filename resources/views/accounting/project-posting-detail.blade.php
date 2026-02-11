<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("project_mgt")->where("Source_Reference",$_GET['ID'])->first();
	
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'project_mgt'");
	$jumlah = count($result);
	if ($jumlah > 0) {
		echo "<script>";

		foreach ($result as $row) {

			$kolom = $row->column_name;
			
				if($kolom != 'Budget_Available'){
						
					if ($datahasil->$kolom == '0000-00-00')
					{
						echo "$('#$kolom').val('');\n";
					
					}else{
						
							$dataForm = $datahasil->$kolom;
							
							echo "$('#$kolom').val('$dataForm');\n";
						
					}
				}
			

		}
	   echo "</script>";

	}
		$podata = DB::table("opportunity_detail")->where("Task_ID",$_GET['ID'])->first();



?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL PROJECT</div>
<div id="ContentPages">
			<input type="hidden" disabled="disabled" id="ID" readonly="readonly"/>
			<div class="FormInput_Form_Content" >
				
				<div class="FormInput_left">
						
					  Related Customer
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Customer_No" size="20" disabled="disabled"/> <input type="text" disabled="disabled" id="Customer_Name" readonly="readonly" size="40"/>
				 </div>
				 
				<div class="FormInput_left">
						
					  Related Vendor/Supplier
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Vendor_ID" size="20" disabled="disabled"/> <input type="text" disabled="disabled" id="Vendor_Name" readonly="readonly" size="40"/>
				 </div>
				 <div class="FormInput_left">
						
						Opportunity Task
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Source_Reference" disabled="disabled" size="40"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						PM Task
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Task_ID" disabled="disabled" size="40"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
					  Reference
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Reference" size="40" disabled="disabled"/>        	
				 
				 </div>
				  	<div class="FormInput_left">
						
					  PO No
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PO_No" size="40" disabled="disabled" value="<?php echo $podata->PO_No?>"/>        	
				 
				 </div>
				 	<div class="FormInput_left">
						
					  PO Type
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PO_Type" size="40" disabled="disabled" value="<?php echo $podata->Project_Type?>"/>        	
				 
				 </div>
				 	<div class="FormInput_left">
						
					  PO Date
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PO_Date" size="40" disabled="disabled" value="<?php echo $podata->PO_Date?>"/>        	
				 
				 </div>
					<div class="FormInput_left">
						
					  PO Value
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PO_Value" size="20" disabled="disabled" value="<?php echo $podata->Total?>"/>        	
				 
				 </div>
				
				<div class="FormInput_left">
						
					  Project Name
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Project_Name" size="50" onchange="this.value.toUpper()" disabled="disabled"/>        	
				 
				 </div>
				 <div class="FormInput_left">
						
						Project Date
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Project_Date" size="10" readonly="readonly"  disabled="disabled"/>        	

				 
				 </div>
				
				<div class="FormInput_left">
						
					   Description
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Description" size="70" disabled="disabled"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Budget Available
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Budget_Available"  disabled="disabled" size="20"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Estimated Revenue
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Estimated_Revenue"  disabled="disabled" onkeypress="return NumOnly(event)"/>        	
				 
				 </div>
				 	<div class="FormInput_left">
						
					   Estimated Costs
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Estimated_Cost"  disabled="disabled" value="0" onkeypress="return NumOnly(event)"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Department
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Department" size="35"  disabled="disabled"/>        	
				 
				 </div>
				 <div class="FormInput_left">
						
						Target Finish Date
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Target_Finish" size="10" readonly="readonly"  disabled="disabled"/>        	

				 
				 </div>
				 <div class="FormInput_left">
						
						Term of Payment (Days)
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="TOP" size="10" value="30" disabled="disabled"/>        	

				 
				 </div>				 
			</div>
			<div id="divtom">
				<input type="button" id="updatefinalclose" NAME="updatefinalclose" value="Ignore to Post" class="btn  btn-danger mt-2 mr-2" />

				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Posting Project" class="btn  btn-primary mt-2 mr-2" />
			</div>

</div>

<script>
 $(function() {
	  $('#Estimated_Revenue,#Estimated_Cost,#Budget_Available').mask('#.##0', {reverse: true});
  });
function isValidKey(e)
{
    var charCode = e.keyCode || e.which;
    if (charCode == 46)
        return false;

    return true;
}

var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Project_Date","Target_Finish"]);
myCalendar.setDateFormat("%Y-%m-%d");


$('#SubmitCustDetail').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "accounting-project-posting-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Project Success to Post!');

					location.href="accounting-project-posting";
			}else{
				alert('Project Failed to Post!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})

$('#updatefinalclose').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "accounting-project-posting-detail-ignore?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Project Ignored!');

					location.href="accounting-project-posting";
			}else{
				alert('Project Failed to Ignore!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})

$('#finishproject').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "project-detail-finish?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Project Finished!');

					location.href="project-lists";
			}else{
				alert('Project Failed to Finish!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})
function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,select,textarea').each(function() {
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



</script>
