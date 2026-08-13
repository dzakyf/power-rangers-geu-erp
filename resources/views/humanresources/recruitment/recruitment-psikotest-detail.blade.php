<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("recruitment")->where("ID",$_GET['ID'])->first();
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
	
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TEST RESULTS ARCHIVING</div>

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
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TEST RESULT DOCUMENTS UPLOAD</div>
<div  id='DocID' style="width:auto;background-color:#cecece;font-family:'Trebuchet MS', Arial, Helvetica, sans-serif">
				<input type="text" size="10" ID="ID" value="<?php echo $datahasil->ID;?>" class="form-control">

    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 1. PSIKOTEST</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
		<div id="vault" class="with-header" style='height:150px;margin:0px;width:auto;'></div>
				<input type="text" disabled="disabled" id="Psikotest_File" size="60" style="background-color:#fff" /> 
        <table cellpadding="10" cellspacing="10" width="100%" align="center" style="padding-top:10;" border="0">
		<tr>
		<td align="center">
			<table cellpadding="0" cellspacing="0" width="100%" border="0">
			<tr height="30">
				<td class="badan" width="150">Score</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
				<input type="text" size="10" value="<?php echo $datahasil->Psikotest_Score;?>"  id="Psikotest_Score" class="form-control">

				</td>
				<td class="badan" width="100">Note</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
				<input type="text" size="50" id="Psikotest_Note"  value="<?php echo $datahasil->Psikotest_Note;?>"  class="form-control">
				</td>
			</tr>
			</table>
		</td>
		</tr>
		</table>
    </div>
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 2. TECHNICAL TEST</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
    	<div id="vaultcv" class="with-header" style='height:150px;margin:0px;width:auto;'></div>

		<input type="text" id="Technical_File" value="<?php echo $datahasil->Technical_File;?>"  size="60" style="background-color:#fff" /> 
		<table cellpadding="10" cellspacing="10" width="100%" align="center" style="padding-top:10;" border="0">
		<tr>
		<td align="center">
			<table cellpadding="0" cellspacing="0" width="100%" border="0">
			<tr height="30">
				<td class="badan" width="150">Score</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
				<input type="text" size="10" id="Technical_Score" value="<?php echo $datahasil->Technical_Score;?>"   class="form-control">

				</td>
				<td class="badan" width="100">Note</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
				<input type="text" size="50" id="Technical_Note" value="<?php echo $datahasil->Technical_Note;?>"   class="form-control">
				</td>
			</tr>
			</table>
		</td>
		</tr>
		</table>
		
    </div>
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 3. TEST FROM USERS</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
    	 <div id="vaultijazah" class="with-header" style='height:150px;margin:0px;width:auto;'></div>    	
		<input type="text" id="Test_File" disabled="disabled"  value="<?php echo $datahasil->Test_File;?>"  size="60" style="background-color:#fff" /> 
		<table cellpadding="10" cellspacing="10" width="100%" align="center" style="padding-top:10;" border="0">
		<tr>
		<td align="center">
			<table cellpadding="0" cellspacing="0" width="100%" border="0">
			<tr height="30">
				<td class="badan" width="150">Score</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
				<input type="text" size="10" id="Test_Score"  value="<?php echo $datahasil->Test_Score?>"  class="form-control">

				</td>
				<td class="badan" width="100">Note</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
				<input type="text" size="50" id="Test_Note" value="<?php echo $datahasil->Test_Note;?>"   class="form-control">
				</td>
			</tr>
			</table>
		</td>
		</tr>
		</table>
    </div>
	
</div>
    
    <div id="divtom" align="center">
        <input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" onclick="UpdateReq()" value="Save" />                
    </div>
	
	
<script>

 
var vaultijazah = new dhx.Vault("vaultijazah", {
                uploader: {
                    target: "upload-rekrut-ijazah.php"
                },
				    filesLimit: 1

            });
			
vaultijazah.events.on("uploadComplete", function(obj) {
	$("#Test_File").val(obj[0].name);	
});
vaultijazah.events.on("uploadBegin", function(obj) {
	$("#Test_File").val("");
	
});	


var vaultcv = new dhx.Vault("vaultcv", {
                uploader: {
                    target: "upload-rekrut-cv.php"
                },
				    filesLimit: 1

            });
			
vaultcv.events.on("uploadComplete", function(obj) {
	$("#Technical_File").val(obj[0].name);	
});
vaultcv.events.on("uploadBegin", function(obj) {
	$("#Technical_File").val("");
	
});	


var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-rekrut-foto.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#Psikotest_File").val(obj[0].name);	
});
vault.events.on("uploadBegin", function(obj) {
	$("#Psikotest_File").val("");
	
});	




function getAllValues() {
		var inputValues = [];
		$('#BankTransferDiv input,select,textarea').each(function() {
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


function UpdateReq(){
	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "recruitment-test-result-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Updated!');
					refreshdata();
					$("#IncomingPartsDiv").html('');
					
			}else{
				alert('Update Incoming Failed to Submit!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
alert("Test Result Uploaded!");
	setTimeout(function(){
		location.href='recruitment-test-result';
	},1000);
}	
	        
</script>
