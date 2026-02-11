<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("recruitment")->where("ID",$_GET['ID'])->first();
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
	
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DOCUMENTS ARCHIVING</div>

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
<div class="JudulContent" style="color:#699;background-image:url(./_CSS_/hdr_back.gif);">RECRUITMENT DOCUMENT</div>
<div  id='DocID' style="width:auto;background-color:#cecece;font-family:'Trebuchet MS', Arial, Helvetica, sans-serif">
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 1. Foto</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
		<div id="vault" class="with-header" style='height:150px;margin:0px;width:auto;'></div>
			    	
		<input type="hidden" id="File_Foto" size="60" style="background-color:#fff" /> 
    </div>
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 2. CV</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
    	<div id="vaultcv" class="with-header" style='height:150px;margin:0px;width:auto;'></div>
			    	
		<input type="hidden" id="File_CV" size="60" style="background-color:#fff" /> 
    </div>
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 3. Ijazah</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
    	 <div id="vaultijazah" class="with-header" style='height:150px;margin:0px;width:auto;'></div>    	
		<input type="hidden" id="File_Ijazah" size="60" style="background-color:#fff" /> 
    </div>
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 4. Sertifikat</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
    	<div id="vaultsertifikat" class="with-header" style='height:150px;margin:0px;width:auto;'></div>    	
		<input type="hidden" id="File_Sertifikat" size="60" style="background-color:#fff" /> 
    </div>
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 5. KTP</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
    	<div id="vaultktp" class="with-header" style='height:150px;margin:0px;width:auto;'></div>    	
		<input type="hidden" id="File_KTP" size="60" style="background-color:#fff" /> 
    </div>
    <div style="font-weight:bold;width:100%;padding-left:40px;height:30px;vertical-align:middle" align="left">.:. 6. NPWP</div>
    <div style="padding-top:20px;padding-bottom:20px;padding-left:80px;font-weight:none;background-color:#efefef">
    	 <div id="vaultnpwp" class="with-header" style='height:150px;margin:0px;width:auto;'></div>    	
		<input type="hidden" id="File_NPWP" size="60" style="background-color:#fff" /> 
    </div>
</div>
    
    <div id="divtom" align="center">
        <input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Save" />                
    </div>
	
	
<script>

var vaultsertifikat = new dhx.Vault("vaultsertifikat", {
                uploader: {
                    target: "upload-rekrut-sertifikat.php"
                },
				    filesLimit: 1

            });
			
vaultsertifikat.events.on("uploadComplete", function(obj) {
	$("#File_Sertifikat").val(obj[0].name);	
});
vaultsertifikat.events.on("uploadBegin", function(obj) {
	$("#File_Sertifikat").val("");
	
});	
vaultsertifikat.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});

var vaultnpwp = new dhx.Vault("vaultnpwp", {
                uploader: {
                    target: "upload-rekrut-npwp.php"
                },
				    filesLimit: 1

            });
			
vaultnpwp.events.on("uploadComplete", function(obj) {
	$("#File_NPWP").val(obj[0].name);	
});
vaultnpwp.events.on("uploadBegin", function(obj) {
	$("#File_NPWP").val("");
	
});	
vaultnpwp.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});

var vaultktp = new dhx.Vault("vaultktp", {
                uploader: {
                    target: "upload-rekrut-ktp.php"
                },
				    filesLimit: 1

            });
			
vaultktp.events.on("uploadComplete", function(obj) {
	$("#File_KTP").val(obj[0].name);	
});
vaultktp.events.on("uploadBegin", function(obj) {
	$("#File_KTP").val("");
	
});	
vaultktp.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});

var vaultijazah = new dhx.Vault("vaultijazah", {
                uploader: {
                    target: "upload-rekrut-ijazah.php"
                },
				    filesLimit: 1

            });
			
vaultijazah.events.on("uploadComplete", function(obj) {
	$("#File_Ijazah").val(obj[0].name);	
});
vaultijazah.events.on("uploadBegin", function(obj) {
	$("#File_Ijazah").val("");
	
});	
vaultijazah.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});


var vaultcv = new dhx.Vault("vaultcv", {
                uploader: {
                    target: "upload-rekrut-cv.php"
                },
				    filesLimit: 1

            });
			
vaultcv.events.on("uploadComplete", function(obj) {
	$("#File_CV").val(obj[0].name);	
});
vaultcv.events.on("uploadBegin", function(obj) {
	$("#File_CV").val("");
	
});	
vaultcv.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});




var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-rekrut-foto.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#File_Foto").val(obj[0].name);	
});
vault.events.on("uploadBegin", function(obj) {
	$("#File_Foto").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});
            
</script>
