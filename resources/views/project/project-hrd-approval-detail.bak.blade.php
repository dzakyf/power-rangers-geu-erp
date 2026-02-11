<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("recruitment")->where("ID",$_GET['ID'])->first();
		$ops = DB::table("opportunity")->where("ID",$_GET['ID'])->first();
		$ops = DB::table("opportunity_detail")->where("ID",$_GET['ID'])->first();

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

	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CANDIDATE SETUP</div>
   		
	<div>
		 <table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
			
			<tr height="30">
				<td class="badan" width="150">Divisi</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Division" size="25" /> 
				</td>
				<td class="badan" width="100">Department</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Department" size="25" /> 
				</td>
			</tr>	
			<tr height="30">
				<td class="badan" width="150">Sub Department</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Department" size="25" /> 
				</td>
				<td class="badan" width="100">Section</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Division" size="25" /> 
				</td>
			</tr>	
			<tr height="30">
				<td class="badan" width="150">Sub Section</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Subsection" size="25" /> 
				</td>
				<td class="badan" width="150">Work Location</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Work_Area" size="25" /> 
				</td>
			</tr>	
			<tr height="30">
				<td class="badan" width="150">Work Area Province</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Work_Area_Province" size="25" /> 
				</td>
				<td class="badan" width="100">Work Area City</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<input type="text" id="Work_Area_City" size="20" /> 
				</td>
			</tr>
			<tr height="30">
				<td class="badan" width="150">Work Time</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<select id="Work_Time"><option>OFFICE HOUR</option><option>SHIFT</option></select> 
				</td>
				<td class="badan" width="100">Work at WeekEnd</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width="250">
					<select id="Work_Weekend"><option>YES</option><option>NO</option></select> 
				</td>
			</tr>				
         </table>
         </div>	

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">COST AND ALLOWANCE DETAIL</div>
		<div class="FormInput_left">
        		
              Basic Salary
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="Basic_Salary" size="15" />        	
         
         </div>
    	<div class="FormInput_left">
        		
              Meal (Allowance)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="A_Meal" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Position (Allowance)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="A_Position" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Telecommunication (Allowance)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="A_Tel" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
             Transport (Allowance)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="A_Transport" size="15"/>        	
         
         </div>

    	<div class="FormInput_left">
        		
              Other (Allowance)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="A_Other" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
             In Period (Allowance)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="In_Period" size="15"/>        	
         
         </div>

    	<div class="FormInput_left">
        		
              School (Allowance)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="A_School" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
             School Rank (Allowance)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="A_Rank" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Overtime
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="Overtime" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
             Bea Bank
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="Bea_Bank" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Cooperative (Costs)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="C_Cooperative" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
             Social Fund (Costs)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="C_Social_Fund" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Insurances (Costs)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="C_Insurance" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
             Fines (Costs)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="C_Fines" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Zakat (Costs)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="C_Zakat" size="15"/>        	
         
         </div>


    	<div class="FormInput_left">
        		
             Loan Credit (Costs)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="C_Loan_Credit" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Loan Credit - Other (Costs)
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="C_Credit_Other" size="15"/>        	
         
         </div>

    	<div class="FormInput_left">
        		
            Correction
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="Correction" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Correction Note
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" min="0" id="Correction_Note" size="15"/>        	
         
         </div>

    	<div class="FormInput_left">
        		
              Loan Credit Cooperative (Costs)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="C_Loan_Credit_Cooperative" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Loan Credit Cooperative Note
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="C_Loan_Credit_Cooperative_Note" size="40"/>        	
         
         </div>

    	<div class="FormInput_left">
        		
              Loan Credit Cooperative Goods (Costs)
        
        </div>
		<div class="FormInput_left">
        
				<input type="text" min="0" id="C_Loan_Credit_Cooperative_Goods" size="15"/>        	
         
         </div>
    	<div class="FormInput_left">
        		
              Loan Credit Cooperative Goods Note
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="C_Loan_Credit_Cooperative_Goods_Note" size="40"/>        	
         
         </div>

    	<div class="FormInput_left">
        		
              Total
        
        </div>
		<div class="FormInput_right">
        
			Allowances	<input type="text" min="0" id="Total_Allowances" readonly="readonly" size="15"/>        	
			Costs	<input type="text" min="0" id="Total_Costs" size="15" readonly="readonly"/>        	
         
         </div>






    	<div class="FormInput_left">
        		
              Gross
        
        </div>
		<div class="FormInput_right">
        
			<input type="text" min="0" id="THP" readonly="readonly" size="30"  value="0"/>        	
		:: THP => 	<input type="text" min="0" readonly="readonly" id="NETT" size="30"  value="0"/>        	
         
         </div>



</div>

    <div id="divtom" align="center" style="height:100px">
        <input type="button" id="SubmitInterview" NAME="SubmitInterview"   class="btn btn-info mt-2 mr-2"  value="Finish!" />                
    </div>
	
<script>
$("input").change(function(){
	
	if(this.id!='C_Loan_Credit_Cooperative_Note' && this.id!='C_Loan_Credit_Cooperative_Goods_Note' && this.id!='Correction_Note'){
		
	Allowances = parseInt($("#A_Meal").val()) + parseInt($("#A_Other").val()) + parseInt($("#A_Rank").val()) +  parseInt($("#A_Transport").val()) +  parseInt($("#Bea_Bank").val())  +  parseInt($("#A_Tel").val()) +  parseInt($("#A_Position").val()) +  parseInt($("#Overtime").val()) +  parseInt($("#In_Period").val());	                        
	Costs = parseInt($("#C_Cooperative").val()) + parseInt($("#C_Credit_Other").val()) + parseInt($("#C_Fines").val()) +  parseInt($("#C_Insurance").val()) +  parseInt($("#C_Loan_Credit").val()) +  parseInt($("#C_Loan_Credit_Cooperative").val()) +  parseInt($("#C_Loan_Credit_Cooperative_Goods").val()) +  parseInt($("#C_Social_Fund").val()) +  parseInt($("#C_Zakat").val()) +  parseInt($("#Correction").val()) ;	                        
		$("#Total_Costs").val(Costs);
		$("#Total_Allowances").val(Allowances);
   	BasicSal = parseInt($("#Basic_Salary").val());
	THP = (BasicSal + Allowances);
	$("#THP").val(THP);
	NETT = (BasicSal + Allowances)-Costs;
	$("#NETT").val(NETT);
			
	}
});

	var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Interview_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	
$('#SubmitInterview').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "project-hrd-approval-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Candidate Approved to Contract Process!');

					location.href="project-hrd-approval";
			}else{
				alert('Data Candidate Failed to Submit!');
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