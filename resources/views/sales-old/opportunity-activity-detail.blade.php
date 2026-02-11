<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("activity")->where("ID",$_GET['ID'])->first();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'activity'");
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
			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL ACTIVITY</div>

		<div id="ContentPages">
			 <div class="FormInput_Form_Content" >
				<div class="FormInput_left">
						
					  Task ID/Account No
				
				</div>
				<div class="FormInput_right">
						<input type="hidden" id="ID" size="20" disabled="disabled"/> 
						<input type="text" id="Task_ID" size="20" disabled="disabled"/> <input type="text" id="Company_Name" readonly="readonly" size="40" disabled="disabled"/>
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
							<option>NEGOTIATE</option>
							<option>AGREED SHIPPING</option>
							<option>TRIAL SHIPPING</option>
							<option>NEW CONTRACT</option>
							<option>ADDENDUM</option>
							<option>CLOSE CONTRACT</option>
							<option>FAILED</option>
							<option value='FAILED DELIVERY'>COMPLAINT: FAILED DELIVERY</option>
							<option value='MISS ROUTE'>COMPLAINT: MISS ROUTE</option>
							<option value='DELAY'>COMPLAINT: DELAY</option>
							<option value='MISS PICK UP'>COMPLAINT: MISS PICK UP</option>
							<option value='LOSE'>COMPLAINT: LOSE</option>
							<option value='DAMAGE'>COMPLAINT: DAMAGE</option>
							<option value='OTHERS'>COMPLAINT: OTHERS</option>
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
						
						PIC
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="PIC" size="30"/>        	
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

				 
				 </div>

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
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Activity_Date","End_Date"]);
myCalendar.setDateFormat("%Y-%m-%d %H:%m");


$('#SubmitCustDetail').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "sales-opportunity-activity-detail-submit?_token={{ csrf_token() }}",
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
