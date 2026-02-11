<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$project = DB::table("project_mgt")->where("ID",$_GET['Task_ID'])->first();

	
	
	?>
			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD NEW ACTIVITY</div>

		<div id="ContentPages">
			 <div class="FormInput_Form_Content" >
				<div class="FormInput_left">
						
					  Task ID/Account No
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Task_ID" size="20" readonly="readonly" value="<?php echo $project->Task_ID?>"/> <input type="text" id="Company_Name" readonly="readonly" disabled="disabled" size="40" value="<?php echo $project->Customer_Name?>"/>
				 </div>
				<div class="FormInput_left">
						
						Start Date Time    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Activity_Date" size="20" readonly="readonly" value="<?php echo date('Y-m-d H:i');?>"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						Estimated End Date Time       
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="End_Date" size="20" readonly="readonly" value="<?php echo date('Y-m-d H:i'); ?>"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
					   Activity Type
				
				</div>
				<div class="FormInput_right">
				
						<select id='Activity_Type'>
							<option>EMAIL</option>
							<option>PHONE CALL</option>
							<option>MEETING</option>
							<option>SUBMIT PROPOSAL</option>
							<option>SUBMIT QUOTATION</option>
							<option>NEGOTIATE</option>
							<option>CONTRACT</option>
							<option>ADDENDUM</option>
							<option>RENEWAL</option>
							<option>CLOSE CONTRACT</option>
							<option>FAILED</option>
						</select>
				 
				 </div>
				 
				<div class="FormInput_left" style="height:80px;">
						
					   Activity Description
				
				</div>
				<div class="FormInput_right" style="height:80px;">
				
						<textarea cols="100" rows="4" id="Activity_Description"></textarea>        	
				 
				 </div>
				<div class="FormInput_left">
						
						Priority
				
				</div>
				<div class="FormInput_right">
						<select id='Priority'>
							<option>NORMAL</option>
							<option>HIGH</option>
							<option>HIGHEST</option>
							<option>LOW</option>
							<option>LOWEST</option>
						</select>
				 
				 </div>

				
				 <div class="FormInput_left">
						
						Persentase
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="persentase" size="30"/>        	
				</div>

				<div class="FormInput_left">
						
						PIC
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="PIC" size="30"/>        	
				</div>
				<div class="FormInput_left">
						
						Reference
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Reference" size="40"/>        	
				 </div>
				 
				 <div class="FormInput_left">
						
						Amount
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Estimated_Rev" size="20" value="0"/>        	
				 </div>
				 
				<div class="FormInput_left">
						
						PIC Phone
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PIC_Phone" size="30"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						PIC Function
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PIC_Function" size="40"/>        	
						<input type="hidden" id="Path_File">

				 
				 </div>
			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD DOCUMENTATION</div>

			<div id="vault" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
			
			
				<div class="FormInput_left" style="height:80px;">

						Note

				</div>
				<div class="FormInput_right" style="height:80px;">
						<textarea id="Note" cols="100" rows="2"></textarea>
				 </div>
				 
			</div>
			
		</div>
		<div id="divtom">
			<input type="reset" id="DelCustDetail" NAME="DelCustDetail" value="Reset" />
			<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" />                
		</div>
<script>

var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-activity.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#Path_File").val(obj[0].name);	
});
vault.events.on("uploadBegin", function(obj) {
	$("#Path_File").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Activity_Date","End_Date"]);
myCalendar.setDateFormat("%Y-%m-%d %H:%m");
myCalendar.showTime();

setTimeout(function(){
		
	$("#Task_ID").coolautosuggest({
		url:"project-search?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#Task_ID"),
		width:500,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#Task_ID").val(result.id); 
			$("#Company_Name").val(result.data); 
				pecah = result.thumbnail.split("|");
			$("#PIC").val(pecah[3]);
			$("#PIC_Function").val(pecah[5]);
			$("#PIC_Phone").val(pecah[4]);
			

		  }
		}
	});
},13000);
$('#SubmitCustDetail').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "project-activity-add-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Activity Submitted!');
					ActivityListsRefresh();
					$("#divlistActivity").html('');
					
			}else{
				alert('Activity Data Failed to Submit!');
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
