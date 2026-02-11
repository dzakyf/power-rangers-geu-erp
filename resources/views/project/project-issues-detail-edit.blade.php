<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	$datahasil = DB::table("project_issues")->where("ID",$_GET['ID'])->first();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'project_issues'");
	$jumlah = count($result);
	if ($jumlah > 0) {
		echo "<script>";

		foreach ($result as $row) {

			$kolom = $row->column_name;
			
				
				if ($datahasil->$kolom == '0000-00-00')
				{
					echo "$('#$kolom').val('');\n";
				
				}else{
					if($datahasil->$kolom != 'ID'){
					$dataForm = $datahasil->$kolom;
					
					echo "$('#$kolom').val('$dataForm');\n";
					}
				}
			

		}
	   echo "</script>";

	}
	
	
	
	?>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT ISSUE</div>

		<div id="ContentPages">
			 <div class="FormInput_Form_Content" >
				<div class="FormInput_left">
						
						Start Date Time    
				</div>
				<div class="FormInput_right">
				<input type="hidden" id="ID" size="20"/>
				<input type="hidden" id="Task_ID" size="20"/>
						<input type="text" id="Start_Date" size="20" disabled="disabled" readonly="readonly" />        	

				 
				 </div>
				<div class="FormInput_left">
						
						Resolving Date Time       
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="End_Date" size="20" readonly="readonly"/>        	

				 
				 </div>
				 <div class="FormInput_left">
						
						Issue Subject
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Issue_Name" disabled="disabled" size="50"/>        	
				</div>
				
				<div class="FormInput_left">
						
					   Priority
				
				</div>
				<div class="FormInput_right">
				
						<select id='Priority'>
							<option>HIGH</option>
							<option>MEDIUM</option>
							<option>LOW</option>
						</select>
				 
				 </div>
				 <div class="FormInput_left">
						
					   Category
				
				</div>
				<div class="FormInput_right">
				
						<select id='Category'>
							<option>TECHNICAL</option>
							<option>BUSINESS PROCESS</option>
							<option>CHANGE MANAGEMENT</option>
							<option>RESOURCE</option>
							<option>THIRD PARTY</option>
						</select>
				 
				 </div>
				<div class="FormInput_left" style="height:80px;">
						
					   Description
				
				</div>
				<div class="FormInput_right" style="height:80px;">
				
						<textarea cols="100" rows="4" id="Description"></textarea>        	
				 
				 </div>
				<div class="FormInput_left">
						
						Status
				
				</div>
				<div class="FormInput_right">
						<select id='Status'>
							<option>OPEN</option>
							<option>INVESTIGATING</option>
							<option>IMPLEMENTING</option>
							<option>ESCALATED</option>
							<option>RESOLVED</option>
						</select>
				 
				 </div>

				<div class="FormInput_left">
						
						Risk Information
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Risk_Information" size="30"/>        	
				</div>
				<div class="FormInput_left">
						
						Assignment/Responsibility PIC
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Assignment" size="20"/>        	
				 </div>
				 
				 <div class="FormInput_left">
						
						Estimated Loss Revenue
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Loss_Revenue" size="20" value="0"/>        	
				 </div>
				 
				<div class="FormInput_left">
						
						Solving Method Description
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Solving_Method" size="80"/>        	

				 
				 </div>
				
		</div>
	</div>	
		<div id="divtom">
			<input type="button" id="DelCustDetail" NAME="DelCustDetail" value="Cancel"  class="btn btn-danger mt-2 mr-2"/>
			<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />                
		</div>
<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");

$('#DelCustDetail').click(function(){
	refreshdataissuedetail();
	$("#DivIssueListsDetail").html('');

});

$('#SubmitCustDetail').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "project-issues-detail-edit-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
					alert('Issue Edited!');
					refreshdataissuedetail();
					$("#DivIssueListsDetail").html('');
					
			}else{
				alert('Issue Data Failed to Submit!');
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
