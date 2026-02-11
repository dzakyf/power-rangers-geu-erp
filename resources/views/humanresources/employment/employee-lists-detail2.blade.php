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
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT PEGAWAI <?php echo $_GET['ID']?></div>
<div  id="DataEmpDetail" >
	 <input type="hidden" id = 'ID'  value="<?php echo $datahasil->ID?>"/>

<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr><td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
		<tr height="30">
        <td class="badan" width="150">NIK</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
            <input type="text" id = 'NIK' size="30" value="<?php echo $datahasil->NIK?>" />
        </td>
        <td class="badan" width="100">Nama</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">

			 <input type="text" id = 'Name' size="40" value="<?php echo $datahasil->Name?>" />
        </td>
    </tr>
	 <tr height="30">
        <td class="badan">PIN ABSEN</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
			<input type="text" id = 'Attendant_No' size="10"  value="<?php echo $datahasil->Attendant_No?>"/>
		    
        </td>
        <td class="badan">LAST EDUCATION </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		 <input type="text" id = 'Last_Education' size="10" value="<?php echo $datahasil->Last_Education?>"/>
            
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Jenis Kelamin</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <select id="Gender">
			<option value="L">LAKI LAKI</option>
			<option value="P">PEREMPUAN</option>
			
		</select>
        </td>
        <td class="badan">Tgl  / Tmpt Lahir </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		 <input type="text" id = 'Birth_Date' readonly="readonly" size="15" /> / <input type="text" id = 'Birth_Place' size="20" value="<?php echo $datahasil->Birth_Place?>"/>
            
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Telepon </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Phone' size="20" value="<?php echo $datahasil->Phone?>"/>
          
        </td>
        <td class="badan">Jabatan</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Position' size="20" value="<?php echo $datahasil->Position?>"/>
        
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Directorat </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Directorat' size="20" value="<?php echo $datahasil->Directorat?>"/>
          
        </td>
        <td class="badan">Department</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
		 	<input type="text" id = 'Department' size="20" value="<?php echo $datahasil->Department?>"/>
          
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Sub Seksi</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Subsection' size="20" value="<?php echo $datahasil->Priority?>"/>
          
        </td>
        <td class="badan">Location</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
		 	<input type="text" id = 'Location' size="20" value="<?php echo $datahasil->Location?>"/>
          
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Work Area </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Work_Area' size="20" value="<?php echo $datahasil->Work_Area?>"/>
          
        </td>
        <td class="badan">Alokasi Untuk di</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
		 	<input type="text" id = 'Alocation' size="20" value="<?php echo $datahasil->Alocation?>"/>
          
        </td>
    </tr>
	
    <tr height="30">
        <td class="badan">Status Pernikahan </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<select id="Marital_Status">
			<option value="TK/0">BELUM Menikah</option>
			<option value="K/0">Sudah Menikah Belum Memiliki Anak</option>
			<option value="K/1">Sudah Menikah Anak 1</option>
			<option value="K/2">Sudah Menikah Anak 2</option>
			<option value="K/3">Sudah Menikah Anak 3</option>
			<option value="K/4">Sudah Menikah Anak 4</option>
			<option value="K/5">Sudah Menikah Anak 5</option>
			<option value="K/6">Sudah Menikah Anak 6</option>
			<option value="K/7">Sudah Menikah Anak 7</option>
			<option value="K/8">Sudah Menikah Anak 8</option>
			
			<option value="J">JANDA</option>
			<option value="D">DUDA</option>
			<option value="TK/0">Tidak Menikah Belum Memiliki Anak</option>
			<option value="TK/1">Tidak Menikah Anak 1</option>
			<option value="TK/2">Tidak Menikah Anak 2</option>
			<option value="TK/3">Tidak Menikah Anak 3</option>
			<option value="TK/4">Tidak Menikah Anak 4</option>
			<option value="TK/5">Tidak Menikah Anak 5</option>
			<option value="TK/6">Tidak Menikah Anak 6</option>
			<option value="TK/7">Tidak Menikah Anak 7</option>
			<option value="TK/8">Tidak Menikah Anak 8</option>
			
		
		</select>
            
        </td>
        <td class="badan">Tanggal Masuk</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		  			<input type="text" id = 'Date_in' size="10"value="<?php echo $datahasil->Date_in?>"/>

        </td>
    </tr>
    <tr height="30">
        <td class="badan">Agama </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <select id="Religion">
			<option>ISLAM</option>
			<option>KATHOLIK</option>
			<option>PROTESTAN</option>
			<option>HINDU</option>
			<option>BUDHA</option>
			<option>Lainnya</option>
		
		</select>
        </td>
        <td class="badan">Tanggal Mulai Kerja </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id = 'Start_Date' size="15"/>
       
           
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Alamat</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'Address' size="45"/>
       
        </td>
         <td class="badan">HP</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'Handphone' size="20"/>
       
        </td>
	</tr>
    <tr height="30">
        <td class="badan">KTP</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'KTP' size="20"/>
       
        </td>
         <td class="badan">NPWP</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'NPWP' size="20"/>
       
        </td>
	</tr>	
	<tr height="30">
		<td class="badan">Email</td>
		<td class="badan">:</td>
		<td class="badan_kiri">
			<input type="text" id = 'Email' size="25"/>
   
		</td>
		<td class="badan">Payment Type</td>
		<td class="badan">:</td>
		<td class="badan_kiri">
			<select id="Payment_Method">
			<option>TRANSFER</option>
			<option>CASH</option>
		
		</select>
		</td>
		
	</tr>
    <tr height="30">
        <td class="badan">Sisa Cuti</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'Sisa_Cuti' size="5"/>
       
        </td>
         <td class="badan">Status Karyawan</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			   <select id="Employment_Status">
			<option>ACTIVE</option>
			<option>RESIGNED</option>
		
		</select>
       
        </td>
	</tr>	
	<tr height="30">
        <td class="badan">Salary Group</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'Salary_Group' size="20"/>
       
        </td>
         <td class="badan">Jenis Karyawan</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			   <select id="Employment_Class">
			<option>PERMANEN</option>
			<option>KONTRAK</option>
			<option>DAILY WORKER</option>
			<option>BOD</option>
			<option>OUTSOURCING</option>
		
		</select>
       
        </td>
	</tr>
	
	<tr height="30">
		<td class="badan">Nama Bank</td>
		<td class="badan">:</td>
		<td class="badan_kiri">
			<input type="text" id = 'Payment_Reference' size="25"/>
   
		</td>
		<td class="badan">Bank</td>
		<td class="badan">:</td>
		<td class="badan_kiri">
			<input type="text" id = 'Payment_Reference2' size="20"/>
		</td>
		
	</tr>	
	<tr>
	
	<td class="badan">FOTO</td><td colspan="3" align="center";>
	
		<img height="113" width='100' src="./doc_recruitment/photo<?php echo $datahasil->Foto?>" id="gambar" onclick="cekFoto('<?php echo $datahasil->Foto?>')"/>
		<input type="button" id="gantifoto" value="Ganti Foto" onclick="uploadGambar('Foto');"/>  <input type="hidden" value="<?php echo $datahasil->Foto?>" id="Foto" readonly="readonly" size="6"/>


	</td>
	</tr>
 <tr height="30">
        <td class="badan">Resigned Note</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'Resigned_Note' size="30"/>
       
        </td>
         <td class="badan">Resigned Date</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id = 'Resigned_Date' size="10"/>
       
        </td>
	</tr>  
</table>
</td>
</tr>
</table>

 </div>
 </div>
    <div id="divtom" align="center">
        <input type="button" id="SimpanNewPEg" NAME="SimpanNewPEg" value="Simpan" />                
    </div>		
<script>
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
		url : "employee-lists-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Employee Updated!');

					location.href="employee-lists";
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
