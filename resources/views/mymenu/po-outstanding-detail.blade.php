<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$NIK =  Auth::user()->Emp_ID;

$dpt =  Auth::user()->Department;
$date = date('Y-m');
$fetch = DB::table("purchase_request")->where("PR_No", $_GET['ID'])->first();
$sobjf = DB::select(DB::raw("SHOW FIELDS FROM purchase_request"));

echo "<script>";

foreach ($sobjf as $objf) {
	$idobj = $objf->Field;
	$isinya = $fetch->$idobj;
	if ($idobj != 'Note') {
		echo "$('#$idobj').val('$isinya').attr('disabled','disabled'); \n";
	} else {
		echo "$('#$idobj').val('$isinya'); \n";
	}
}

echo "</script>";

?>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">DETAIL PURCHASE REQUEST</div>

<div id="ContentPages">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			PR No

		</div>
		<div class="FormInput_right">

			<input type="text" id="PR_No" size="50" readonly="readonly" disabled="disabled" />

		</div>
		<div class="FormInput_left">

			Date Request

		</div>
		<div class="FormInput_right">

			<input type="text" id="PR_Date" readonly="readonly" size="15" value="" />

		</div>
		<div class="FormInput_left">

			User Request

		</div>
		<div class="FormInput_right">

			<input type="text" id="User_Request" size="15" value="<?php echo $NIK ?>" readonly="readonly" /> &nbsp;&nbsp;
			<input type="text" id="User_Request_Name" size="40" value="<?php echo $Saya ?>" readonly="readonly" />

		</div>

		<!---->

		<div class="FormInput_left">

			PIC to Receive

		</div>
		<div class="FormInput_right">

			<input type="text" id="PIC" size="40" value="" /> <i>(Penerima Barang)</i>

		</div>


		<div class="FormInput_left" style="height:60px;">

			PIC Address

		</div>
		<div class="FormInput_right" style="height:60px;">

			<textarea id="PIC_Address" rows="2" cols="55"></textarea>


		</div>
		<div class="FormInput_left">

			PIC Phone

		</div>
		<div class="FormInput_right">

			<input type="text" id="PIC_Phone" size="30" />


		</div>


		<div class="FormInput_left">

			Category

		</div>
		<div class="FormInput_right">

			<select id="Category" onchange="loadSubCat(this.value);">
				<option>CONSUMABLE</option>
				<option>ASSET</option>
				<option>SERVICES</option>
			</select>

		</div>
		<div class="FormInput_left">

			Group Category

		</div>
		<div class="FormInput_right">
			<div id="SubCat">
				<input type="text" readonly="readonly" id="Sub_Category" size="30" />

			</div>

		</div>
		<div class="FormInput_left">

			Sub Category

		</div>
		<div class="FormInput_right">
			<div id="SubCat2">
				<input type="text" readonly="readonly" id="Supplies_Type" size="30" />

			</div>

		</div>


		<div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>

		<div class="FormInput_left">

			References/FPB (Manual)

		</div>
		<div class="FormInput_right">

			<input type="text" id="Reference" size="40" />

		</div>


	</div>
</div>


<script>
	PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
	PRNewDetail.setImagePath("dhtmlx/imgs/");
	PRNewDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Prices");
	PRNewDetail.setInitWidths("50,100,100,250,50,200,100,0,0,0,0");
	PRNewDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	PRNewDetail.setColAlign("center,left,center,left,right,left,center,center,center,center");
	PRNewDetail.setColSorting("int,str,str,str,str,str,str,str");
	PRNewDetail.setEditable(true);
	PRNewDetail.setNumberFormat("0,000.00", 4, ".", ",");
	PRNewDetail.enableKeyboardSupport(true);
	PRNewDetail.setSkin("dhx_skyblue");
	PRNewDetail.enableMultiline(true);
	PRNewDetail.init();
	PRNewDetail.loadXML("xml-purchasing-request-approval-detail?ID=<?php echo $fetch->PR_No;  ?>");
</script>