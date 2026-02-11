<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$nik =   Auth::user()->Emp_ID;	

$result = DB::table('reimbursement')->where("Reimbursement_No",$_GET['ID'])->first();



?>


		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT DETAIL</div>
	<div id="AdvanceForm">
		<div class="FormInput_Form_Content">
			
			<div class="FormInput_left">

				Tanggal

			</div>

			<div class="FormInput_right">

				<input type="hidden" id="Reimbursement_No" size="15" disabled="disabled" value="<?php echo $result->Reimbursement_No ?>" />
				<input type="text" id="Reimbursement_Date"  disabled="disabled" size="15" value="<?php echo $result->Reimbursement_Date ?>" />

			</div>

			<div class="FormInput_left">

				Responsibility

			</div>
			<div class="FormInput_right">
				<input type="text" size="10" id="User_Responsibility_ID" disabled="disabled" value="<?php echo $result->User_Responsibility_ID ?>" disabled="disabled" /> :: 
				<input type="text" readonly="readonly" size="30" disabled="disabled" id="User_Responsibility_Name"   value="<?php echo $result->User_Responsibility_Name ?>" disabled="disabled" />:: 
				<input type="text" readonly="readonly" size="30" disabled="disabled" id="User_Reference" disabled="disabled"  value="<?php echo $result->User_Reference ?>" /> <input type="hidden" disabled="disabled" readonly="readonly" size="20" id="User_Responsibility_ID"  value="<?php echo $result->User_Responsibility_ID ?>" />

			</div>
			<div class="FormInput_left">

				Department

			</div>
			<div class="FormInput_right">

				<input type="text" id="Department" size="20" readonly="readonly"  disabled="disabled" value="<?php echo $result->Department?>" />

			</div>
			<div class="FormInput_left">

				Amount Estimated

			</div>
			<div class="FormInput_right">

				<input type="text" id="Total" size="20" value="<?php echo number_format($result->Total,0,",",".") ?>"  disabled="disabled" />

			</div>


			<div class="FormInput_left">

				Reference

			</div>
			<div class="FormInput_right">

				<input type="text" id="Reference_No" size="40"   disabled="disabled"  value="<?php echo $result->Reference_No ?>" />

			</div>
			<div class="FormInput_left" style="height:80px;" disabled="disabled">

				Note

			</div>
			<div class="FormInput_right" style="height:80px;">
				<textarea id="Description" cols="40" rows="3" disabled="disabled"><?php echo $result->Description ?></textarea>

			</div>
			

		</div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT DETAIL</div>

		<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			
<div id="WinCOA"></div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
							
			
<script>
		
	


		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Date,Reference,External Ref No,Note,Amount,Currency Val,Department");
		GridCash.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","",""]);
		GridCash.setInitWidths("70,100,100,150,200,100,100,100");
		GridCash.setColAlign("center,left,center,left,left,right,right,center");
		GridCash.setColTypes("ro,ro,ed,ed,ed,edn,ro,ro");
		GridCash.setColSorting("int,str,str,str,str,str,str");
		GridCash.setDateFormat("%Y-%m-%d"); 
		GridCash.setEditable(false);
		GridCash.setNumberFormat("0,000",5,".",",");
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		GridCash.init();
		refreshdatadetail();	
		
function refreshdatadetail(){
			GridCash.clearAndLoad("xml-my-reimbursement-detail?ID=<?php echo $_GET['ID'];?>");

}		
</script>
</div>
			