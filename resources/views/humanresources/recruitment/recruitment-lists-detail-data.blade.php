@include('layout.plugins')
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />
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
	$result = DB::select("SELECT COLUMN_NAME FROM information_schema.columns where TABLE_NAME = 'recruitment' AND TABLE_SCHEMA = 'erp_ksps'");
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
		
		foreach($pecahin as $pecahreq){
		$Requisition = DB::table("requisition")->where("Task_ID",$pecahreq)->first();
		$rekusisi .= $Requisition->Job_Position .",";
		}
$rekusisi = substr($rekusisi,0,-1);
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
		<input type="text" id="Initial" size="20" class="form-control-lg" value="<?php echo $datahasil->Initial;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
        </td>
        <td class="badan">Religion</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<select id="Religion">
			<option>ISLAM</option>
			<option>KRISTEN PROTESTAN</option>
			<option>KRISTEN KATHOLIK</option>
			<option>HINDU</option>
			<option>BUDHA</option>
			<option>KONGHUCU</option>
		</select>
        </td>
    </tr>

    <tr height="30">
        <td class="badan">Gender</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<select id="Gender">
			<option value="L">MALE</option>
			<option value="P">FEMALE</option>
		</select>
        </td>
        
        <td class="badan">Birth Date &amp; Place </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="Birth_Date" size="10" readonly="readonly" class="form-control-lg" value="<?php echo $datahasil->Birth_Date;?>" style="border:none;outline:none;border-bottom:1px solid gray"/> / <input type="text" id="Birth_Place" size="10" class="form-control-lg" value="<?php echo $datahasil->Birth_Place;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
        </td>
    </tr>
	
    <tr height="30">
        <td class="badan">Citizen ID Card </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
				<input type="text" id="KTP" size="20" class="form-control-lg" value="<?php echo $datahasil->KTP;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
		</td>
        <td class="badan">Citizenship</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="kewarganegaraan" size="20" class="form-control-lg" value="<?php echo $datahasil->kewarganegaraan;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
		</td>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Marital Status </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<select id="Marital_Status">
			<option>SINGLE</option>
			<option>MARRIED</option>
			<option>WIDOW</option>
			<option>WIDOWER</option>
		</select>
        </td>
        <td class="badan">Last Education </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<select id="Last_Education">
			<option>SMA</option>
			<option>SMK</option>
			<option>D2</option>
			<option>D3</option>
			<option>S1</option>
			<option>S2</option>
		</select>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Priority Recruitment </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<select id="Priority">
			<option>URGENT</option>
			<option>NORMAL</option>
		</select>
            
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
		<input type="text" id="Address" size="40" class="form-control-lg" value="<?php echo $datahasil->Address;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>,
		City : <input type="text" id="City" size="10" class="form-control-lg" value="<?php echo $datahasil->City;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>,
		
           
        </td>
         <td class="badan">Phone Contact</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="Phone" size="20" class="form-control-lg" value="<?php echo $datahasil->Phone;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
		Whatsapp No  <input type="text" id="Handphone" size="20" class="form-control-lg" value="<?php echo $datahasil->Handphone;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
        </td>
	</tr>
	 <tr height="30">
        <td class="badan">Address (Live)</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="Address_Living" size="40" class="form-control-lg" value="<?php echo $datahasil->Address;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>,
		City : <input type="text" id="Address_Living_City" size="10" class="form-control-lg" value="<?php echo $datahasil->City;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>,
		
        </td>
         <td class="badan" width="100">Blood Type</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
		<select id="Blood">
			<option>A</option>
			<option>AB</option>
			<option>B</option>
			<option>O</option>
		</select>
		Rhesus : <select id="Blood_Rhesus">
			<option>+</option>
			<option>-</option>
		</select>
        </td>
	</tr>
    <tr height="30">
        <td class="badan">Education From</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="Last_Education_From" size="30" class="form-control-lg" value="<?php echo $datahasil->Last_Education_From;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
        
        </td>
         <td class="badan">Interested Requisition</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
			<input type="text" id="Position" size="30" class="form-control-lg" value="<?php echo $rekusisi;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>

        </td>
	</tr>
	<tr height="30">
        <td class="badan">Education Major</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <input type="text" id="Education_Major" size="20" class="form-control-lg" value="<?php echo $datahasil->Education_Major;?>"style="border:none;outline:none;border-bottom:1px solid gray"/>
        
        </td>
        <td class="badan">Email Individual </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
					<input type="text" id="Email_Individual" size="20" class="form-control-lg" value="<?php echo $datahasil->Email_Individual;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>

        </td>
    </tr>
	<tr height="30">
        <td class="badan">Date Join </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
				<input type="text" id="Date_in" size="10" readonly="readonly" class="form-control-lg" value="<?php echo  date("d M Y",strtotime($datahasil->Date_in));?>" style="border:none;outline:none;border-bottom:1px solid gray"/>

        </td>
        <td class="badan">PTKP</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<select id="PTKP_Status">
			<option>TK/0</option>
			<option>K/0</option>
			<option>K/1</option>
			<option>K/2</option>
			<option>K/3</option>
			<option>K/4</option>
			<option>K/5</option>
			<option>K/6</option>
			<option>K/7</option>
			<option>K/8</option>
			<option>TK/0</option>
			<option>TK/1</option>
			<option>TK/2</option>
			<option>TK/3</option>
			<option>TK/4</option>
			<option>TK/5</option>
			<option>TK/6</option>
			<option>TK/7</option>
			<option>TK/8</option>
		</select>
	  </td>
    </tr>
	
	<tr height="30">
        <td class="badan">NPWP</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="NPWP" size="20" class="form-control-lg" value="<?php echo $datahasil->NPWP;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
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
				<input type="text" id="Payment_Reference" size="20" class="form-control-lg" value="<?php echo $datahasil->Payment_Reference;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>

        </td>
        <td class="badan">Bank Account No</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
				<input type="text" id="Payment_Reference2" size="20" class="form-control-lg" value="<?php echo $datahasil->Payment_Reference2;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>

        </td>
    </tr>
	<tr height="30">
        <td class="badan">Bank Branch</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="Payment_Reference_Branch" size="20" class="form-control-lg" value="<?php echo $datahasil->Payment_Reference_Branch;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
        </td>
        <td class="badan">Bank Account Name</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
		<input type="text" id="Payment_Reference3" size="20" class="form-control-lg" value="<?php echo $datahasil->Payment_Reference3;?>" style="border:none;outline:none;border-bottom:1px solid gray"/>
      
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
        </td>
    </tr>	
	<tr height="30">
        <td colspan="6" style="color:#699;background-image:url(hdr_back.gif);">Interview Result</td>
       
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Interview Note</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Interview_Note;?>
        </td>
        <td class="badan">Interview Date</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Interview_Date;?>
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Interview Result</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Interview_Result;?>
        </td>
        <td class="badan">Interview By</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Interview_By;?>
        </td>
    </tr>
	<tr height="30">
        <td colspan="6" style="color:#699;background-image:url(hdr_back.gif);">User Interview Result</td>
       
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Interview Note</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->User_Interview_Note;?>
        </td>
        <td class="badan">Interview Date</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->User_Interview_Date;?>
        </td>
    </tr>
	<tr height="30">
        <td class="badan">Interview Result</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->User_Interview_Result;?>
        </td>
        <td class="badan">Interview By</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->User_Interview_By;?>
        </td>
    </tr>
</table>
</td>
</tr>
</table>
</div>
<div style="padding:10px">
    <input type="button" id="buttonSimpan" class="btn btn-primary" value="Save">
</div>
<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date","Date_in","Birth_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");


$('#buttonSimpan').click(function(){
    $.blockUI();
    gettingVars = getAllValues();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url : "recruitment-lists-detail-data-submit?_token={{ csrf_token() }}",
        type: "POST",
        data : gettingVars,
        complete: function() { $.unblockUI(); },
        success: function(data) {
            if (data && data.status === 'success') {
                alert('Data updated successfully.');
                if (window.parent.refreshdata) window.parent.refreshdata();
                if (window.parent.$('#divlistRecruitment')) window.parent.$('#divlistRecruitment').empty();
            } else {
                alert('Failed to submit!');
            }
        },
        error: function () {
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

