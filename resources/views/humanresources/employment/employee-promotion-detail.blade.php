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
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'employment'");
	$jumlah = count($result);
	if ($jumlah > 0) {
		echo "<script>";
		
		foreach ($result as $row) {
			
			$kolom = $row->column_name;
			
				
				if ($datahasil->$kolom == '0000-00-00')
				{
					echo "$('#$kolom').val('');\n";
				
				}else{
					
					$dataForm = $datahasil->$kolom;
					
					echo "$('#$kolom').val('$dataForm');\n";
					
				}
			

		}
	   echo "</script>";

	}
	
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EMPLOYEE PROMOTION</div>
<div  id="DataEmpDetail" >
	 <input type="hidden" id = 'ID'  value="<?php echo $datahasil->ID?>"/>

<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr><td align="center">
		<table cellpadding="10" cellspacing="0" width="100%" border="0" >
		<tr height="30">
        <td class="badan" width="150">NIK</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
            <input type="text" id = 'NIK' size="30" value="<?php echo $datahasil->NIK?>" />
        </td>
        <td class="badan" width="100">Name</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">

			 <input type="text" id = 'Name' size="40" value="<?php echo $datahasil->Name?>" />
        </td>
    </tr>
	 <tr height="30">
        <td class="badan">KTP</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'KTP' size="30"/>
       
        </td>
         <td class="badan">NPWP</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'NPWP' size="30"/>
       
        </td>
	</tr>	
	
	
	 <tr height="30">
        <td class="badan">Division</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
			<input type="text" id = 'Attendant_No' size="10"  value="<?php echo $datahasil->Directorat?>"/>
		    
        </td>
        <td class="badan">LAST EDUCATION </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		 <input type="text" id = 'Last_Education' size="5" value="<?php echo $datahasil->Last_Education?>"/>
            
        </td>
    </tr>
    
	<tr height="30">
        <td class="badan">Department </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Directorat' size="30" value="<?php echo $datahasil->Department?>"/>
          
        </td>
        <td class="badan">Location</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
		 	<input type="text" id = 'Department' size="15" value="<?php echo $datahasil->Department?>"/>
          
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Sub Section</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Subsection' size="30" value="<?php echo $datahasil->Subsection?>"/>
          
        </td>
        <td class="badan">Alocation</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
		 	<input type="text" id = 'Location' size="15" value="<?php echo $datahasil->Alocation?>"/>
          
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Work Area </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Work_Area' size="30" value="<?php echo $datahasil->Work_Area?>"/>
          
        </td>
        <td class="badan">Position</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
		 	<input type="text" id = 'Alocation' size="30" value="<?php echo $datahasil->Position?>"/>
          
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Employee Grade</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Work_Area' size="30" value="<?php echo $datahasil->Employment_Grade?>"/>
          
        </td>
        <td class="badan">Employment Class</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
		 	<input type="text" id = 'Alocation' size="30" value="<?php echo $datahasil->Employment_Class?>"/>
          		<input type="hidden" id = 'Mutation_File' size="30"/>

        </td>
    </tr>
   
   
</table>
</td>
</tr>
</table>
<div id="vault" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
			
 </div>
 </div>
    <div id="divtom" align="center"><br><br>
        <input type="button" id="SimpanNewPEg" NAME="SimpanNewPEg" value="Process" />                
    </div>		
<script>
var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-mutation.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#Mutation_File").val(obj[0].name);	
});
vault.events.on("uploadBegin", function(obj) {
	$("#Mutation_File").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});
   
var PODMGT_Cal;
PODMGT_Cal = new dhtmlXCalendarObject(["Start_Date","Birth_Date","Date_in","Resigned_Date"]);
PODMGT_Cal.setDateFormat("%Y-%m-%d");

			
var winUpload;
var WUpload;
function uploadGambar(idfoto) {
   winUpload = new dhtmlXWindows();
    winUpload.attachViewportTo("divtom");
    winUpload.enableAutoViewport(true);
    winUpload.setImagePath(PathDHTMLX + "imgs/");
    WUpload = winUpload.createWindow("WUpload", 0, 0, 450, 360);
    WUpload.setText("Upload Gambar");
    WUpload.attachURL('upload-foto-pegawai?idfoto='+idfoto, true);
	WUpload.button("minmax1").hide();
    WUpload.button("park").hide();
	WUpload.centerOnScreen();
	WUpload.allowMove();	
	WUpload.progressOn();

}
	
function cekFoto(pathf){
	if (pathf!='') {
		window.open ("./doc_recruitment/photo/"+pathf,"mywindow");
	}
}

function getAllValues() {
	 
		
    	var inputValues = [];
		$('#DataEmpDetail input,select,textarea').each(function() {
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



$('#SimpanNewPEg').click(function(){

var	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "employee-promotion-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Employee Updated!');

					location.href="employee-promotion";
			}else{
				alert('Employee Failed to Update!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})

</script>
