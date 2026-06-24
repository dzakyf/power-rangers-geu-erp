<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("employment")->where("NIK",$_GET['ID'])->first();
	$jx = DB::table("contract_lists")->where("NIK",$_GET['ID'])->first();
	if($jx){
		$catno = $jx->Category_No + 1;
		$jums= $jx->Urutan + 1;
	}else{
		$catno = 0;
		$jums = 1;
	}
	if ($datahasil->NIK_KSPS == '0'){
		if($datahasil->Category == 'INTERNAL'){
			$datahasil->NIK_KSPS = "3".date("y").date("y",strtotime($datahasil->Birth_Date)).rand(10000,99999);
		}else{
			$datahasil->NIK_KSPS = "2".date("y").date("y",strtotime($datahasil->Birth_Date)).rand(10000,99999);
		}
	}
?>
			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>

		<div id="ContentPages">
			 <div class="FormInput_Form_Content" >
				<div class="FormInput_left">
						
						Contract No
				</div>
				<div class="FormInput_right">
						<input type="hidden" id="Urutan" size="20" value="<?php echo $jums?>" disabled="disabled"/> 
						<input type="hidden" id="NIK" size="20" value="<?php echo $datahasil->NIK?>" disabled="disabled"/> 
						<input type="text" id="Contract_No" size="40"/>
				 </div>
				 <div class="FormInput_left">
						
						Contract Date    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Contract_Date" size="20" readonly="readonly"/>        	

				 
				 </div>
				  <div class="FormInput_left">
						
						NIK GEU    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="NIK_KSPS" size="20"value="<?php echo $datahasil->NIK_KSPS?>" readonly="readonly"/>        	

				 
				 </div>
				   <div class="FormInput_left">
						
						NIK User/Client    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="NIK_Client"value="<?php echo $datahasil->NIK_Client?>" size="20"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						Start Date    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Start_Date" size="20" readonly="readonly"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						End Date       
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="End_Date" size="20" readonly="readonly"/>        	

				 
				 </div>
				 <div class="FormInput_left" style="height:100px;">
						
					   Category
				
				</div>
				<div class="FormInput_right" style="height:100px;">
				
						<select id='Category' multiple class="control-form">
							<option>KONTRAK AWAL</option>
							<option>ADDENDUM -  MUTASI</option>
							<option>ADDENDUM -  PERPANJANGAN</option>
							<option>ADDENDUM -  PERUBAHAN JABATAN DAN GAJI</option>
							<option>ADDENDUM -  PERUBAHAN JABATAN</option>
							<option>ADDENDUM -  PERUBAHAN GAJI</option>
						</select>
				 
				 </div>
				  <div class="FormInput_left">
						
					   Category No
				
				</div>
				<div class="FormInput_right">
						
						<input type="text" id="Category_No" size="5" value="<?php echo $catno?>"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
					   Status
				
				</div>
				<div class="FormInput_right">
				
						<select id='Status'>
							<option value='CREATED'>ACTIVE</option>
							<option>CLOSED</option>
							<option>CANCEL</option>
						</select>
				 
				 </div>
				<div class="FormInput_left" style="height:100px;">
						
					   Note
				
				</div>
				<div class="FormInput_right" style="height:100px;">
				
						<textarea cols="100" rows="3" id="Note"></textarea>        	
				 
				 </div>
				 
			</div>
			
		</div>
		<div id="divtom">
			<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail"  class="btn btn-success mt-2 mr-2" value="Submit" />                
		</div>
<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date","Contract_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");


$('#SubmitCustDetail').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "os-contract-renew-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Contract Submitted!');
					refreshdata();
					$("#detailcontractdiv").html('');
					
			}else{
				alert('Contract Failed to Submit!');
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
