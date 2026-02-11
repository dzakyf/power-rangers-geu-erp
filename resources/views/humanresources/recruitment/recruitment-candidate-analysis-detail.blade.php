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
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'recruitment'");
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
<div id="ContentPages">
	<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">USER INTERVIEW DECISION</div>
   		<div class="FormInput_left" style="height:80px;" id="DECISIONDiv">
        		
                Note Interview
        
        </div>
		<div class="FormInput_right" style="height:80px;">

				<textarea cols="100" rows="4" id="User_Interview_Note"></textarea>        	

         
         </div>
   		<div class="FormInput_left">
        		
               Interview Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="User_Interview_Date" size="15" value="<?php echo date('Y-m-d');?>"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
               Interview Status
        
        </div>
		<div class="FormInput_right">
        
				<select id="User_Interview_Status">
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
        
				<textarea id="User_Interview_Result" cols="80" rows="5"></textarea>        	
         
         </div>
		 <div class="FormInput_left" style="height:150px;">
        		
               Interview Recap Documents Upload
        
        </div>
		<div class="FormInput_right" style="height:150px;">
        
				<div id="vault" class="with-header" style='height:150px;margin:0px;width:auto;'></div>
					<input type="hidden" id="ID"/>
					<input type="hidden" id="User_Interview_File"/>
					<input type="hidden" id="Recruitment_Type"/>
         
         </div>

</div>

    <div id="divtom" align="center" style="height:100px">
        <input type="button" id="SubmitInterview" NAME="SubmitInterview"   class="btn btn-info mt-2 mr-2"  value="Finish!" />                
    </div>
</div>	
<script>
	var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["User_Interview_Date"]);
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
		url : "recruitment-candidate-analysis-detail-submit?_token={{ csrf_token() }}",
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

					location.href="recruitment-candidate-analisys";
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