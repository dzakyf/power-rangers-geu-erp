@include('layout.plugins')
<?php
error_reporting(0);
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
	
		$datapo = DB::table("opportunity_detail")->where("Task_ID",$datahasil->Registration_Status)->first();
	$datahasil2 = DB::table("recruitment_detail")->where("Task_ID",$datahasil->Task_ID)->first();
	$Query1 = DB::table("recruitment_akademic")->get();

	foreach($Query1 as $Fetch1){
		$akademis[$Fetch1->Task_ID] = $Fetch1->Education_Major;
	}
	$rekusisi = "";
		$pecahin = explode(",",$datahasil->Requisition_ID);
		
		$Requisition = DB::table("requisition")->where("Status","CREATED")->get();
		foreach($Requisition as $Reks){
		$rekusisi .= "<option value='$Reks->Task_ID'>$Reks->PO_No - $Reks->Job_Position</option>";
		}
?>
<div id="fotonya" style="position:absolute;height:113px;width:100px;border:10px solid gray;top:50px;left:86%;"><img height="113" width='100' src="./doc_recruitment/photo/<?php echo $datahasil->Foto?>" id="gambar" onclick="cekFoto('<?php echo $datahasil->Foto?>')"/><br><Br>
<input type="button" id="gantifoto" value="Change" onclick="uploadGambar('Foto');"/>  </div>

<div id="ContentPages">    		
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0"  class="table table-hover text-vertical-middle mb-0">
    <tr><td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" border="0" style="padding:10px">
    <tr height="30">
        <td class="badan" width="150">Task ID</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
		<input type="hidden" value="<?php echo $datahasil->ID?>" id="ID" readonly="readonly" size="6"/>
		<input type="hidden" value="<?php echo $datahasil->Foto?>" id="Foto" readonly="readonly" size="6"/>
            <?php echo $datahasil->Task_ID;?>
        </td>
        <td class="badan" width="100">Name</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
            <?php echo $datahasil->Name;?>
        </td>
    </tr>
	 <tr height="30">
       <td class="badan" width="150">Initial</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
		<?php echo $datahasil->Initial;?>
        </td>
        <td class="badan">Religion</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Religion;?>
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
		<?php echo $datahasil->Birth_Date;?> / <?php echo $datahasil->Birth_Place;?>
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
        <td class="badan">Priority Recruitment </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Priority;?>
            
        </td>
        <td class="badan">Date Created </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Created_Date;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Address (ID Card)</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Address;?>,<?php echo $datahasil->City;?>
		
           
        </td>
         <td class="badan">Phone Contact</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Phone;?>
		Whatsapp No  : <?php echo $datahasil->Handphone;?>
        </td>
	</tr>
	 <tr height="30">
        <td class="badan">Address (Live)</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Address_Living;?>,
		<?php echo $datahasil->Address_Living_City;?>,
		
        </td>
         <td class="badan" width="100">Blood Type</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
		<?php echo $datahasil->Blood;?>
		Rhesus : <?php echo $datahasil->Blood_Rhesus;?>
        </td>
	</tr>
    <tr height="30">
        <td class="badan">Education From</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Last_Education_From;?>
        
        </td>
         <td class="badan">Interested Requisition</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			<select id="Position">
			<option value=''>Change Requisition</option>
				<?php echo $rekusisi ?>
			</option>

        </td>
	</tr>
	<tr height="30">
        <td class="badan">Education Major</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
           <?php echo $datahasil->Education_Major;?>
        
        </td>
        <td class="badan">Email Individual </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
					<?php echo $datahasil->Email_Individual;?>

        </td>
    </tr>
	<tr height="30">
        <td class="badan">Date Join </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
				<?php echo $datahasil->Date_in;?>

        </td>
        <td class="badan">PTKP</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->PTKP_Status;?>
	  </td>
    </tr>
	
	<tr height="30">
        <td class="badan">NPWP</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		
            <?php echo $datahasil->NPWP;?>
        </td>
        <td class="badan">Payment Method</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Payment_Method;?>
        </td>
    </tr>
	
	<tr height="30">
        <td class="badan">Bank Account</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
				<?php echo $datahasil->Payment_Reference;?>

        </td>
        <td class="badan">Bank Account No</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
				<?php echo $datahasil->Payment_Reference2;?>

        </td>
    </tr>
	<tr height="30">
        <td class="badan">Bank Branch</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Payment_Reference_Branch;?>
        </td>
        <td class="badan">Bank Account Name</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<?php echo $datahasil->Payment_Reference3;?>
      
        </td>
    </tr>
	<tr height="30">
        <td colspan="6" style="color:#699;background-image:url(hdr_back.gif);">EMPLOYEE JOB DETAIL</td>
       
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Division</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Directorat;?>
        </td>
        <td class="badan">Alocation</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Alocation;?>
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Department</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Department;?>
        </td>
        <td class="badan">Location</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Location;?>
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Subsection</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Subsection;?>
        </td>
        <td class="badan">Work Area</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Work_Area;?>
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Employment Type</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Employment_Class;?>
        </td>
        <td class="badan">Employee Grade</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Employment_Grade;?>
			<input type="hidden" value="<?php echo $datahasil->ID?>" id="ID" readonly="readonly" size="6"/>
		
        </td>
    </tr>
	<tr height="30">
        <td colspan="6" style="color:#699;background-image:url(hdr_back.gif);">EXTERNAL DATA DETAIL</td>
       
        </td>
    </tr>
	
	<tr height="30">
			<td class="badan">PO No</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo $datapo->PO_No;?>
			</td>
			<td class="badan">PO Date</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo date("d M Y",strtotime($datapo->PO_Date));?>
			</td>
		</tr>
		<tr height="30">
			<td class="badan">PO Start Date</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo date("d M Y",strtotime($datapo->Start_Date));?>
			</td>
			<td class="badan">PO End Date</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo date("d M Y",strtotime($datapo->End_Date));?>
			</td>
		</tr>
		<tr height="30">
			<td class="badan">PO Subject</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo $datapo->Subject;?>
			</td>
			<td class="badan">PO User</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo $datapo->Department;?>
			</td>
		</tr>
		<tr height="30">
			<td class="badan">PO Initial</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo $datapo->PO_Initial;?>
			</td>
			<td class="badan">PO Alocation</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo $datahasil->Alocation;?>
			</td>
		</tr>
		<tr height="30">
			<td class="badan">Reference No</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo $datapo->Reference_No;?>
			</td>
			<td class="badan">PO Contract No</td>
			<td class="badan">:</td>
			<td class="badan_kiri">
				<?php echo $datahasil->Contract_No;?>
			</td>
		</tr>				
</table>
</td>
</tr>
</table>
<input type="button" id="buttonSimpan" value="Move to Interview"  class="btn btn-info mt-2 mr-2"/>
</div>
<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date","Date_in","Birth_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");


$('#buttonSimpan').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "recruitment-pending-detail2-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Pending Moved to Interview Process!');

			}else{
				alert('Data Pending Failed to Submit!');
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

