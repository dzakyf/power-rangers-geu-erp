<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("recruitment")->where("ID",$_GET['ID'])->first();
	$datahasil2 = DB::table("recruitment_detail")->where("ID",$_GET['ID'])->first();
	$result = DB::select("SELECT COLUMN_NAME FROM information_schema.columns where TABLE_NAME = 'recruitment' AND TABLE_SCHEMA = 'erp_ksps'");
	$jumlah = count($result);
	if ($jumlah > 0) {
		echo "<script>";
		
		foreach ($result as $row) {
			
			$kolom = $row->COLUMN_NAME;
			
				
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
	$tipe = $datahasil->Recruitment_Type;
	if ($tipe == 'PRODUCTION') $pesan = "Next we will waiting from Business to Review"; else $pesan = "Next Waiting Management Approval";
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CANDIDATE PROFILE</div>

<div id="BankTransferDiv">
    <div class="FormInput_Form_Content">

    		
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr><td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
    <tr height="30">
        <td class="badan" width="150">Task ID</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
            <?php echo $datahasil->Task_ID;?>
        </td>
        <td class="badan" width="100">Name</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
            <?php echo $datahasil->Name;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Gender</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Gender;?>
        </td>
        <td class="badan">Birth Date &amp; Place </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Birth_Date." / ".$datahasil->Birth_Place;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Citizen ID Card </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->KTP;?>
        </td>
        <td class="badan">Citizenship</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->kewarganegaraan;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Marital Status </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Marital_Status;?>
        </td>
        <td class="badan">Last Education </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Last_Education;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Religion </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Religion;?>
        </td>
        <td class="badan">Date Created </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Created_Date;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Address</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Address.", ".$datahasil->City;?>
        </td>
         <td class="badan">Phone Contact</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Phone ." / ".$datahasil->Handphone;?>
        </td>
	</tr>
    <tr height="30">
        <td class="badan">Education From</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Last_Education_From;?>
        </td>
         <td class="badan">Position Fulfillment</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Position;?>
        </td>
	</tr>
</table>
</td>
</tr>
</table>


<div class="JudulContent"  style="color:#699;background-image:url(./_CSS_/hdr_back.gif);">ADVANCED INFORMATION</div>
    <div class="FormInput_Form_Content" id="ContentPages" >
   		<div class="FormInput_left" style="height:80px;">
        		
                Describe Yourself *
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Self"> <?php echo $datahasil2->Describe_Self;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Why you left last job? *
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Left_Job"><?php echo $datahasil2->Left_Job;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What do you truly think about your previous boss?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Previous_Boss"><?php echo $datahasil2->Previous_Boss;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What do you know about our company?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Know"><?php echo $datahasil2->Describe_Know;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What did you do to improve your knowledge lately?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Improve"><?php echo $datahasil2->Describe_Improve;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
               What are you looking for in this position of work?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Looking"><?php echo $datahasil2->Describe_Looking;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
			Why are you interested to work in this company?        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Interest"><?php echo $datahasil2->Describe_Interest;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                How long would you expect to work for us if hired?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Long"><?php echo $datahasil2->Describe_Long;?></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What is your career goals in 5 years?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Target"><?php echo $datahasil2->Describe_Target;?></textarea>        	

         
         </div>
   		<div class="FormInput_left">
        		
                What is your salary expectations
        
        </div>
		<div class="FormInput_right">
        
				Rp. <input type="text" size="30" id="Salary_Expectation" value="<?php echo $datahasil2->Salary_Expectation;?>"/>        	

         
         </div>
   		<div class="FormInput_left">
        		
               Any other facilities you expect from us?
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" size="50" id="Facility_Expectation" value="<?php echo $datahasil2->Describe_Self;?>"/>        	

         
         </div>
    		<div class="FormInput_left">
        		
                What type of work environment do you prefer?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Environment"><option>OFFICE</option><option>FIELD</option><option>FACTORY</option><option>WORKSHOP</option></select>
                       	

         
         </div>
  		<div class="FormInput_left" style="height:80px;">
        		
                How are you spending your leisure time?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Free_Time"></textarea>        	

         
         </div>
  		<div class="FormInput_left" style="height:80px;">
        		
                What is your strengths?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Strong"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Can you describe your greatest weakness?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Weak"></textarea>        	

         
         </div>
   		<div class="FormInput_left">
        		
                Do you agree to work overtime when needed?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Overtime"><option>YES</option><option>NO</option> </select>     	

         
         </div>
   		<div class="FormInput_left">
        		
                Would you work on holiday if needed?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Holiday"><option>YES</option><option>NO</option>  </select>    	

         
         </div>
   		<div class="FormInput_left">
        		
                Are you comfortable with shift work and a changing schedule?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Shift_Work"><option>YES</option><option>NO</option> </select>     	

         
         </div>
   		<div class="FormInput_left">
        		
               Are you willing to travel for work purposes?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Out_Town"><option>YES</option><option>NO</option> </select>     	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What computer skills do you have?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Computer"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
               Do you have any questions?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Questions_Us"></textarea>        	

         
         </div>

		
	<div class="JudulContent" style="color:#699;background-image:url(./_CSS_/hdr_back.gif);">INTERVIEW DECISION</div>
   		<div class="FormInput_left" style="height:80px;" id="DECISIONDiv">
        		
                Note Interview
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Interview_Note"></textarea>        	

         
         </div>
   		<div class="FormInput_left">
        		
               Interview Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Interview_Date" size="15" value="<?php echo date('Y-m-d');?>"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
               Interview Status
        
        </div>
		<div class="FormInput_right">
        
				<select id="Interview_Status">
						<option value="">Select</option>
						<option>ACCEPT</option>
						<option>REJECT</option>
						<option>OFFER DECLINED</option>
						<option>INVITATION DECLINED</option>
						<option>PENDING</option>
				</select>		
         
         </div>
		  
   		<div class="FormInput_left" style="height:100px;">
        		
               Interview Result
        
        </div>
		<div class="FormInput_right" style="height:100px;">
        
				<textarea id="Interview_Result" cols="80" rows="5"></textarea>        	
         
         </div>
		 <div class="FormInput_left" style="height:150px;">
        		
               Interview Recap Documents Upload
        
        </div>
		<div class="FormInput_right" style="height:150px;">
        
				<div id="vault" class="with-header" style='height:150px;margin:0px;width:auto;'></div>
					<input type="hidden" id="ID"/>
					<input type="hidden" id="Interview_File"/>
					<input type="hidden" id="Recruitment_Type"/>
         
         </div>

</div>

    <div id="divtom" align="center" style="height:100px">
        <input type="button" id="SubmitInterview" NAME="SubmitInterview"   class="btn btn-info mt-2 mr-2"  value="Finish!" />                
    </div>
	
<script>
	var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Interview_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");


	var vault = new dhx.Vault("vault", {
					uploader: {
						target: "upload-rekrut-interview.php"
					},
						filesLimit: 1

				});
				
	vault.events.on("uploadComplete", function(obj) {
		$("#Interview_File").val(obj[0].name);	
	});
	vault.events.on("uploadBegin", function(obj) {
		$("#Interview_File").val("");
		
	});	
	vault.events.on("beforeAdd", function(fileItem) {
		if (vault.data.getLength() >= 3) {
			alert('Only can upload maximum 3 fileS per checkpoint. create in a pdf file if you want upload multiple!');
			return false;
		}
	});
	
	
$('#SubmitInterview').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "recruitment-interview-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
				if ($("#Status").val() == 'PENDING'){
					alert('Candidate Pending and moved to Pending Lists');
				}else{
					alert('Data Interview Was Submitted! <?php echo $pesan ?>');
				}

					location.href="recruitment-interview";
			}else{
				alert('Interview Failed to Submit!');
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