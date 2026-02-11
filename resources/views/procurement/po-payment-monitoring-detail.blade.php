<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$yourdate = date("Y-m-d H:i:s");
$TimeOut_Progress = strtotime('+ 5 minute', $yourdate);
$ket_remark = "";

function getSupplier($VID)
{
	$fetchx = DB::table("supplier")->where("Supplier_ID", $VID)->first();
	return $fetchx;
}

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$nik =  Auth::user()->Emp_ID;

$dpt =  Auth::user()->Department;
$date = date('Y-m');

$fetch = DB::table("purchase_summary")->where("PR_No", $_GET['ID'])->first();
$v1 = getSupplier($fetch->V1_ID);
$v2 = getSupplier($fetch->V2_ID);
$v3 = getSupplier($fetch->V3_ID);
$v4 = getSupplier($fetch->V4_ID);
$v5 = getSupplier($fetch->V5_ID);


if (strlen($fetch->Active_Progress) >= '0') {
	$newdate = date("Y-m-d H:i:s");

	if ($fetch->TimeOut_Progress >= $newdate) {
		if ($_SESSION['userid'] <> $fetch->Active_Progress) {
			$act_color = "color:red;font-style:italic;";
			$ket_remark = " ==>> SEDANG DIPROSES OLEH: " . $fetch->Active_Progress;
			$disable = "DISABLED";
		}
	} else {
		$newdate = date("Y-m-d H:i:s");
		$TimeOut_Progress = strtotime('+ 5 minute', $newdate);
		$act_color = "";
		$disable = "";
		$ket_remark = "";
	}
} else {
	$newdate = date("Y-m-d H:i:s");
	$TimeOut_Progress = strtotime('+ 5 minute', $newdate);



	$act_color = "";
	$disable = "";
	$ket_remark = "";
}

$nomorx = 1;
if ($fetch->V2_ID == NULL) {
	$display2 = "display:none;";
	$hide2 = "true";
	$nomorx = 2;
} else {
	$hide2 = "false";
};
if ($fetch->V3_ID == NULL) {
	$display3 = "display:none;";
	$hide3 = "true";
	$nomorx = 3;
} else {
	$hide3 = "false";
};
if ($fetch->V4_ID == NULL) {
	$display4 = "display:none;";
	$hide4 = "true";
	$nomorx = 4;
} else {
	$hide4 = "false";
};
if ($fetch->V5_ID == NULL) {
	$display5 = "display:none;";
	$hide5 = "true";
	$nomorx = 5;
} else {
	$hide5 = "false";
};

///	echo json_encode($fetch);exit;

?>
<script>
	function UbahKurs(vul, val) {
		if (vul == '1') {
			document.getElementById("kursnya1").innerHTML = val + " ";
			document.getElementById("kursnya2").innerHTML = val + " ";
			document.getElementById("kursnya3").innerHTML = val + " ";

		} else if (vul == '2') {
			document.getElementById("kursnya4").innerHTML = val + " ";
			document.getElementById("kursnya5").innerHTML = val + " ";
			document.getElementById("kursnya6").innerHTML = val + " ";
		} else if (vul == '3') {
			document.getElementById("kursnya7").innerHTML = val + " ";
			document.getElementById("kursnya8").innerHTML = val + " ";
			document.getElementById("kursnya9").innerHTML = val + " ";
		} else if (vul == '4') {
			document.getElementById("kursnya10").innerHTML = val + " ";
			document.getElementById("kursnya11").innerHTML = val + " ";
			document.getElementById("kursnya12").innerHTML = val + " ";
		} else if (vul == '5') {
			document.getElementById("kursnya13").innerHTML = val + " ";
			document.getElementById("kursnya14").innerHTML = val + " ";
			document.getElementById("kursnya15").innerHTML = val + " ";
		}
	}
</script>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">PRICE INPUT</div>

<div id="ContentPages">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left" style="height:20px;<?php echo $act_color; ?>">

			PR No

		</div>
		<div class="FormInput_right" style="height:20px;<?php echo $act_color; ?>">

			<?php echo $_GET['ID'] . " " . $ket_remark; ?>
			<input type="hidden" value="<?php echo $_GET['ID'] ?>">
		</div>
		<div class="FormInput_left" style="height:20px;<?php echo $act_color; ?>">

			PO Category

		</div>
		<div class="FormInput_right" style="height:20px;<?php echo $act_color; ?>">

			<?php echo $fetch->Purchase_Category; ?>

		</div>
		<div class="FormInput_left" style="height:20px;<?php echo $act_color; ?>">

			PO Sub Category

		</div>
		<div class="FormInput_right" style="height:20px;<?php echo $act_color; ?>">

			<?php echo $fetch->Purchase_Sub_Category; ?>

		</div>

		<div class="FormInput_left" style="height:20px;<?php echo $act_color; ?>">

			PO Type

		</div>
		<div class="FormInput_right" style="height:20px;<?php echo $act_color; ?>">

			<?php echo $fetch->Purchase_Type; ?>

		</div>
		<div class="FormInput_left" style="height:20px;<?php echo $act_color; ?>">

			PO Date

		</div>
		<div class="FormInput_right" style="height:20px;<?php echo $act_color; ?>">

			<?php echo date("d/m/Y", strtotime($fetch->Purchase_Date)); ?>

		</div>
		<div class="FormInput_left" style="height:20px;">

			User Request

		</div>
		<div class="FormInput_right" style="height:20px;">

			<?php
			$row_n = DB::table("purchase_request")->where("PO_Task_No", $fetch->Task_ID)->first();
			$row_u = DB::table("users")->where("username", $row_n->Created_By)->first();
			print("<b>" . $row_u->Name . "</b> | " . $row_u->Department . "<br/>");
			?>

		</div>

		<div class="FormInput_left" style="height:20px;<?php echo $act_color; ?>">

			Peruntukkan

		</div>
		<div class="FormInput_right" style="height:20px;<?php echo $act_color; ?>">

			<?php echo "<b>" . $row_n->PIC . "</b> | Address: " . $row_n->PIC_Address . " | Telp: " . $row_n->PIC_Phone; ?>
			<input type="hidden" id="nomorx" value="<?php echo $nomorx ?>" />
		</div>

		<div class="FormInput_left" style="height:150px;">

			Notes

		</div>
		<div class="FormInput_right" style="height:150px;overflow:scroll;text-align:left;">
			<textarea style="height:98%;overflow:scroll;width:100%;" READONLY><?php print(str_replace("<br>", "&#13;", $row_n->Note)); ?></textarea>

		</div>

		<div class="FormInput_left" style="height:150px;">

			Status Approval

		</div>
		<div class="FormInput_right" style="height:150px;overflow:scroll;">
			<textarea style="height:98%;overflow:scroll;width:100%;" READONLY><?php print(str_replace("<br>", "&#13;", $fetch->Note)); ?></textarea>

		</div>

		<div id="POMGTNEW" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
		<input type="button" id="PreVendor" NAME="PreVendor" value="Export Excel" onclick="PRDetailnya.toExcel('./export/generate.php');" class="cs-gradient-blue cs-corner-all cs-shadow-bottom" style="width: 100%;" />

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">VENDOR DETAILS</div>
		<div id="VendorDetails">
			<div class="FormInput_left" style="height:250px;">
				Vendor 1 (<?php echo $v1->Company_Name ?>)
			</div>
			<div class="FormInput_right" style="height:320px;">
				<div style="float:left;width:160px;">Currency</div>
				<div>
					<select id="kurs1" onChange="UbahKurs(1,this.value)" style="height:22px;width:60px;">
						<option value='Rp' <?php if ($fetch->V1_Cur == 'Rp') echo "SELECTED"; ?>>Rp</option>
						<option value='USD' <?php if ($fetch->V1_Cur == 'USD') echo "SELECTED"; ?>>USD</option>
					</select>
				</div>
				<div style="float:left;width:160px;">Pembulatan (<span id="kursnya1"><?php echo $fetch->V1_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="20" value="<?php echo $fetch->V1_Fee_Mgt + 0 ?>" id="V1_Fee_Mgt" /></div>
				<div style="float:left;width:160px;">Pembulatan Tanpa PPN</div>
				<div> <input type="checkbox" onchange="hitungbawah();" <?php if ($fetch->V1_Fee_VAT == '1') echo "checked='checked'"; ?> id="V1_Fee_VAT" /> Yes</div>
				<div style="float:left;width:160px;">PPN (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V1_VAT + 0 ?>" id="V1_VAT" /></div>
				<div style="float:left;width:160px;">PPH (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V1_PPH + 0 ?>" id="V1_PPH" /></div>
				<div style="float:left;width:160px;">Delivery Cost</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V1_DC + 0 ?>" id="V1_DC" /></div>
				<div style="float:left;width:160px;">Discount (<span id="kursnya2"><?php echo $fetch->V1_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V1_Discount + 0 ?>" id="V1_Discount" /></div>
				<div style="float:left;width:160px;">Notes</div>
				<div> <textarea id="V1_Note" cols="50" rows="2"><?php echo $fetch->V1_Note ?></textarea></div>
				<div style="float:left;width:160px;">Est. Total PO</div>
				<div> <span id="kursnya3" style="color:#F90"><?php echo $fetch->V1_Cur; ?></span>
					<label style="padding-left:4px;color:#F90" id="V1_label"><?php echo ($fetch->V1_Total) ?></label>
					<input type="text" style="display:none;" size="25" readonly="readonly" value="<?php echo $fetch->V1_Total ?>" id="V1_Total" />
				</div>

			</div>
			<div class="FormInput_left" style="height:250px;<?php echo $display2 ?>">
				Vendor 2 (<?php echo $v2->Company_Name ?>)
			</div>
			<div class="FormInput_right" style="height:320px;<?php echo $display2 ?>">
				<div style="float:left;width:160px;">Currency</div>
				<div>
					<select id="kurs2" onChange="UbahKurs(2,this.value)" style="height:22px;width:60px;">
						<option value='Rp' <?php if ($fetch->V2_Cur == 'Rp') echo "SELECTED"; ?>>Rp</option>
						<option value='USD' <?php if ($fetch->V2_Cur == 'USD') echo "SELECTED"; ?>>USD</option>
					</select>
				</div>
				<div style="float:left;width:160px;">Pembulatan (<span id="kursnya4"><?php echo $fetch->V2_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="20" value="<?php echo $fetch->V2_Fee_Mgt + 0 ?>" id="V2_Fee_Mgt" /></div>
				<div style="float:left;width:160px;">Pembulatan Tanpa PPN</div>
				<div> <input type="checkbox" onchange="hitungbawah();" <?php if ($fetch->V2_Fee_VAT == '1') echo "checked='checked'"; ?> id="V2_Fee_VAT" /> Yes</div>
				<div style="float:left;width:160px;">PPN (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V2_VAT + 0 ?>" id="V2_VAT" /></div>
				<div style="float:left;width:160px;">PPH (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V2_PPH + 0 ?>" id="V2_PPH" /></div>
				<div style="float:left;width:160px;">Delivery Cost</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V2_DC + 0 ?>" id="V2_DC" /></div>
				<div style="float:left;width:160px;">Discount (<span id="kursnya5"><?php echo $fetch->V2_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V2_Discount + 0 ?>" id="V2_Discount" /></div>
				<div style="float:left;width:160px;">Notes</div>
				<div> <textarea id="V2_Note" cols="50" rows="2"><?php echo $fetch->V2_Note ?></textarea></div>
				<div style="float:left;width:160px;">Est. Total PO</div>
				<div> <span id="kursnya6" style="color:#F90"><?php echo $fetch->V2_Cur; ?></span>
					<label style="padding-left:4px;color:#F90" id="V2_label"><?php echo ($fetch->V2_Total) ?></label>
					<input type="text" style="display:none;" size="25" readonly="readonly" value="<?php echo $fetch->V2_Total ?>" id="V2_Total" />
				</div>

			</div>
			<div class="FormInput_left" style="height:250px;<?php echo $display3 ?>">
				Vendor 3 (<?php echo $v3->Company_Name ?>)
			</div>
			<div class="FormInput_right" style="height:320px;<?php echo $display3 ?>">
				<div style="float:left;width:160px;">Currency</div>
				<div>
					<select id="kurs3" onChange="UbahKurs(3,this.value)" style="height:22px;width:60px;">
						<option value='Rp' <?php if ($fetch->V3_Cur == 'Rp') echo "SELECTED"; ?>>Rp</option>
						<option value='USD' <?php if ($fetch->V3_Cur == 'USD') echo "SELECTED"; ?>>USD</option>
					</select>
				</div>
				<div style="float:left;width:160px;">Pembulatan (<span id="kursnya7"><?php echo $fetch->V3_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="20" value="<?php echo $fetch->V3_Fee_Mgt + 0 ?>" id="V3_Fee_Mgt" /></div>
				<div style="float:left;width:160px;">Pembulatan Tanpa PPN</div>
				<div> <input type="checkbox" onchange="hitungbawah();" <?php if ($fetch->V3_Fee_VAT == '1') echo "checked='checked'"; ?> id="V3_Fee_VAT" /> Yes</div>
				<div style="float:left;width:160px;">PPN (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V3_VAT + 0 ?>" id="V3_VAT" /></div>
				<div style="float:left;width:160px;">PPH (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V3_PPH + 0 ?>" id="V3_PPH" /></div>
				<div style="float:left;width:160px;">Delivery Cost</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V3_DC + 0 ?>" id="V3_DC" /></div>
				<div style="float:left;width:160px;">Discount (<span id="kursnya8"><?php echo $fetch->V3_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V3_Discount + 0 ?>" id="V3_Discount" /></div>
				<div style="float:left;width:160px;">Notes</div>
				<div> <textarea id="V3_Note" cols="50" rows="2"><?php echo $fetch->V3_Note ?></textarea></div>
				<div style="float:left;width:160px;">Est. Total PO</div>
				<div> <span id="kursnya9" style="color:#F90"><?php echo $fetch->V3_Cur; ?></span>
					<label style="padding-left:4px;color:#F90" id="V3_label"><?php echo ($fetch->V3_Total) ?></label>
					<input type="text" style="display:none;" readonly="readonly" value="<?php echo $fetch->V3_Total ?>" id="V3_Total" />
				</div>

			</div>
			<div class="FormInput_left" style="height:250px;<?php echo $display4 ?>">
				Vendor 4 (<?php echo $v4->Company_Name ?>)
			</div>
			<div class="FormInput_right" style="height:320px;<?php echo $display4 ?>">
				<div style="float:left;width:160px;">Currency</div>
				<div>
					<select id="kurs4" onChange="UbahKurs(4,this.value)" style="height:22px;width:60px;">
						<option value='Rp' <?php if ($fetch->V4_Cur == 'Rp') echo "SELECTED"; ?>>Rp</option>
						<option value='USD' <?php if ($fetch->V4_Cur == 'USD') echo "SELECTED"; ?>>USD</option>
					</select>
				</div>
				<div style="float:left;width:160px;">Pembulatan (<span id="kursnya10"><?php echo $fetch->V4_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="20" value="<?php echo $fetch->V4_Fee_Mgt + 0 ?>" id="V4_Fee_Mgt" /></div>
				<div style="float:left;width:160px;">Pembulatan Tanpa PPN</div>
				<div> <input type="checkbox" onchange="hitungbawah();" <?php if ($fetch->V4_Fee_VAT == '1') echo "checked='checked'"; ?> id="V4_Fee_VAT" /> Yes</div>
				<div style="float:left;width:160px;">PPN (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V4_VAT + 0 ?>" id="V4_VAT" /></div>
				<div style="float:left;width:160px;">PPH (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V4_PPH + 0 ?>" id="V4_PPH" /></div>
				<div style="float:left;width:160px;">Delivery Cost</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V4_DC + 0 ?>" id="V4_DC" /></div>
				<div style="float:left;width:160px;">Discount (<span id="kursnya11"><?php echo $fetch->V4_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V4_Discount + 0 ?>" id="V4_Discount" /></div>
				<div style="float:left;width:160px;">Notes</div>
				<div> <textarea id="V4_Note" cols="50" rows="2"><?php echo $fetch->V4_Note ?></textarea></div>
				<div style="float:left;width:160px;">Est. Total PO</div>
				<div> <span id="kursnya12" style="color:#F90"><?php echo $fetch->V4_Cur; ?></span>
					<label style="padding-left:4px;color:#F90" id="V4_label"><?php echo ($fetch->V4_Total) ?></label>
					<input type="text" style="display:none" readonly="readonly" value="<?php echo $fetch->V4_Total ?>" id="V4_Total" />
				</div>

			</div>
			<div class="FormInput_left" style="height:250px;<?php echo $display5 ?>">
				Vendor 5 (<?php echo $v5->Company_Name ?>)
			</div>
			<div class="FormInput_right" style="height:320px;<?php echo $display5 ?>">
				<div style="float:left;width:160px;">Currency</div>
				<div>
					<select id="kurs5" onChange="UbahKurs(5,this.value)" style="height:22px;width:60px;">
						<option value='Rp' <?php if ($fetch->V5_Cur == 'Rp') echo "SELECTED"; ?>>Rp</option>
						<option value='USD' <?php if ($fetch->V5_Cur == 'USD') echo "SELECTED"; ?>>USD</option>
					</select>
				</div>
				<div style="float:left;width:160px;">Pembulatan (<span id="kursnya13"><?php echo $fetch->V5_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="20" value="<?php echo $fetch->V5_Fee_Mgt + 0 ?>" id="V5_Fee_Mgt" /></div>
				<div style="float:left;width:160px;">Pembulatan Tanpa PPN</div>
				<div> <input type="checkbox" onchange="hitungbawah();" <?php if ($fetch->V5_Fee_VAT == '1') echo "checked='checked'"; ?> id="V5_Fee_VAT" /> Yes</div>
				<div style="float:left;width:160px;">PPN (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V5_VAT + 0 ?>" id="V5_VAT" /></div>
				<div style="float:left;width:160px;">PPH (%)</div>
				<div> <input type="text" onchange="hitungbawah();" size="5" value="<?php echo $fetch->V5_PPH + 0 ?>" id="V5_PPH" /></div>
				<div style="float:left;width:160px;">Delivery Cost</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V5_DC + 0 ?>" id="V5_DC" /></div>
				<div style="float:left;width:160px;">Discount (<span id="kursnya14"><?php echo $fetch->V5_Cur; ?></span>)</div>
				<div> <input type="text" onchange="hitungbawah();" size="25" value="<?php echo $fetch->V5_Discount + 0 ?>" id="V5_Discount" /></div>
				<div style="float:left;width:160px;">Notes</div>
				<div> <textarea id="V5_Note" cols="50" rows="2"><?php echo $fetch->V5_Note ?></textarea></div>
				<div style="float:left;width:160px;">Est. Total PO</div>
				<div> <span id="kursnya15" style="color:#F90"><?php echo $fetch->V5_Cur; ?></span>
					<label style="padding-left:4px;color:#F90" id="V5_label"><?php echo ($fetch->V5_Total) ?></label>
					<input type="text" style="display:none" size="25" readonly="readonly" value="<?php echo $fetch->V5_Total ?>" id="V5_Total" />
				</div>

			</div>
		</div>
	</div>
</div>
<script>
	$("#CancelReq").click(function() {
		var disable_can = "<?php echo $disable_can; ?>";
		if (disable_can < 1) {
			dhtmlx.confirm('Are you sure want to reject this task?', 'Purchase Order', function(r) {

				if (r == true) {

					Note = $("#Note").val();
					if (Note != '') {
						LoadM('DivProcess', 'purchasing-order-reject1?Task_ID=<?php echo $_GET['ID']; ?>&Note=' + encodeURI(Note));
					} else {
						alert('Please fill note for this action!');
						$("#Note").focus();
					}
				}

			});
		} else {
			alert('The goods already received by purchasing department... PO cannot to be cancel!');
			return false;
		}
	});

	function gantimodel(idmodel, nilai) {


		$("#" + idmodel + "_label").html(number_format(nilai));
	}


	POMGTNEW = new dhtmlXGridObject('POMGTNEW');
	POMGTNEW.setImagePath("./dhtmlx/imgs/");
	POMGTNEW.setHeader("No.,Code,Description,Qty,Proposed,System Price,<?php echo $v1->Company_Name ?>,#cspan,#cspan,<?php echo $v2->Company_Name ?>,#cspan,#cspan,<?php echo $v3->Company_Name ?>,#cspan,#cspan,<?php echo $v4->Company_Name ?>,#cspan,#cspan,<?php echo $v5->Company_Name ?>,#cspan,#cspan,#cspan");
	POMGTNEW.attachHeader("&nbsp;,#rspan,#rspan,#rspan,#rspan,#rspan,Price @unit,Total,Recommended,Price @unit,Total,Recommended,Price @unit,Total,Recommended,Price @unit,Total,Recommended,Price @unit,Total,Recommended,PR No");
	POMGTNEW.setInitWidths("30,70,360,60,60,80,80,100,100,80,100,100,80,100,100,80,100,100,80,100,100,200");
	POMGTNEW.setColumnColor(",,,,,#F9FFBC,");
	POMGTNEW.enableEditEvents(true, false, true);

	POMGTNEW.setColumnHidden(9, <?php echo $hide2; ?>);
	POMGTNEW.setColumnHidden(10, <?php echo $hide2; ?>);
	POMGTNEW.setColumnHidden(11, <?php echo $hide2; ?>);
	POMGTNEW.setColumnHidden(12, <?php echo $hide3; ?>);
	POMGTNEW.setColumnHidden(13, <?php echo $hide3; ?>);
	POMGTNEW.setColumnHidden(14, <?php echo $hide3; ?>);
	POMGTNEW.setColumnHidden(15, <?php echo $hide4; ?>);
	POMGTNEW.setColumnHidden(16, <?php echo $hide4; ?>);
	POMGTNEW.setColumnHidden(17, <?php echo $hide4; ?>);
	POMGTNEW.setColumnHidden(18, <?php echo $hide5; ?>);
	POMGTNEW.setColumnHidden(19, <?php echo $hide5; ?>);
	POMGTNEW.setColumnHidden(20, <?php echo $hide5; ?>);

	POMGTNEW.setColAlign("center,center,left,right,right,right,right,right,center,right,right,center,right,right,center,right,right,center,right,right,center,right,right,center,right,right,center,right");
	POMGTNEW.setColTypes("ro,ro,ed,ron,edn,edn,edn,ron,ra_str,edn,ron,ra_str,edn,ron,ra_str,edn,ron,ra_str,edn,ron,ra_str,ro");
	POMGTNEW.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', "", "<div id='JumAmount1'>0</div>", "", "", "<div id='JumAmount2'>0</div>", "", "", "<div id='JumAmount3'>0</div>", "", "", "<div id='JumAmount4'>0</div>", "", "", "<div id='JumAmount5'>0</div>", "", ""]);
	POMGTNEW.attachFooter(["Metode Pembayaran", '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', "<?php echo $v1->Payment; ?>", '#cspan', "", "<?php echo $v2->Payment; ?>", '#cspan', "", "<?php echo $v3->Payment; ?>", '#cspan', "", "<?php echo $v4->Payment; ?>", '#cspan', "", "<?php echo $v5->Payment; ?>", '#cspan', "", "", ]);

	POMGTNEW.setNumberFormat("0,000", 3, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 4, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 5, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 6, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 7, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 9, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 10, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 12, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 13, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 15, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 16, ".", ",");
	POMGTNEW.setNumberFormat("0,000", 18, ".", ",");
	POMGTNEW.enableMultiline(true);

	POMGTNEW.attachEvent("onEditCell", HitungLagi);
	POMGTNEW.setColSorting("int,str,str,str,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int");
	POMGTNEW.setEditable(true);
	PPOMGTNEW = new dataProcessor("purchasing-order-approval-detail-update");
	PPOMGTNEW.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})
	PPOMGTNEW.setUpdateMode("Off");
	PPOMGTNEW.init(POMGTNEW);

	POMGTNEW.setSkin("dhx_skyblue");
	POMGTNEW.init();
	POMGTNEW.loadXML("xml-purchasing-order-approval-detail?ID=<?php echo $fetch->Task_ID ?>&TaskID=<?php echo $_GET['TaskID']; ?>", Updategrids);
	$("#SubmitPOMGTNEW").click(function() {


		Kursnya = "&kurs1=" + $("#kurs1").val() + "&kurs2=" + $("#kurs2").val() + "&kurs3=" + $("#kurs3").val() + "&kurs4=" + $("#kurs4").val() + "&kurs5=" + $("#kurs5").val();
		//PPOMGTNEW.sendData();
		submitdtl = 'data=';
		for (var i = 0; i < POMGTNEW.getRowsNum(); i++) {
			var id = POMGTNEW.getRowId(i);
			var no = id;
			var code = POMGTNEW.cells(id, 1).getValue();
			var desc = POMGTNEW.cells(id, 2).getValue();
			var qty = POMGTNEW.cells(id, 3).getValue();
			var proposed = POMGTNEW.cells(id, 4).getValue();
			var systemprice = POMGTNEW.cells(id, 5).getValue();
			//vendor1
			var price1 = POMGTNEW.cells(id, 6).getValue();
			var total1 = POMGTNEW.cells(id, 7).getValue();
			var rec1 = POMGTNEW.cells(id, 8).getValue();
			//vendor2
			var price2 = POMGTNEW.cells(id, 9).getValue();
			var total2 = POMGTNEW.cells(id, 10).getValue();
			var rec2 = POMGTNEW.cells(id, 11).getValue();
			//vendor3
			var price3 = POMGTNEW.cells(id, 12).getValue();
			var total3 = POMGTNEW.cells(id, 13).getValue();
			var rec3 = POMGTNEW.cells(id, 14).getValue();
			//vendor4
			var price4 = POMGTNEW.cells(id, 15).getValue();
			var total4 = POMGTNEW.cells(id, 16).getValue();
			var rec4 = POMGTNEW.cells(id, 17).getValue();
			//vendor5
			var price5 = POMGTNEW.cells(id, 18).getValue();
			var total5 = POMGTNEW.cells(id, 19).getValue();
			var rec5 = POMGTNEW.cells(id, 20).getValue();

			//ID
			var idpr = POMGTNEW.cells(id, 21).getValue();

			submitdtl = submitdtl + no + "#" + code + '#' + proposed + '#' + systemprice + '#' + price1 + '#' + total1 + '#' + rec1 + '#' + price2 + '#' + total2 + '#' + rec2 + '#' + price3 + '#' + total3 + '#' + rec3 + '#' + price4 + '#' + total4 + '#' + rec4 + '#' + price5 + '#' + total5 + '#' + rec5 + '#' + idpr + '#' + desc + '@';
		}

		var semuanya = "ID=<?php echo $fetch->Task_ID; ?>&Status=<?php echo str_replace(" ", "", $fetch->Status) ?>" + '&TaskID=<?php echo $_GET['TaskID']; ?>' + Kursnya + "&" + encodeURI(submitdtl);
		// LoadM("DivProcess","purchasing-order-price-input-detail-submit?ID=<?php echo $_GET['ID']; ?>&Status=<?php echo str_replace(" ", "", $fetch->Status) ?>" + '&TaskID=<?php echo $_GET['TaskID']; ?>' + Kursnya + "&src=" + semuanya);

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "purchasing-order-approval-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: semuanya,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {

				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("error");

			}
		});
		Note = $("#Note").val();
		Category = $("#Category").val();

		Note = $("#Note").val();
		if (Note == '') {
			alert("Note cannot empty!");
			$("#Note").focus();
			return false;
		} else if (Note.length < 2) {
			alert("Note cannot empty!");
			$("#Note").focus();
			return false;
		} else {

			dapat = getAllValues();

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
				},

				url: "purchasing-order-approval-detail-submit2?_token={{ csrf_token() }}",
				type: "POST",
				data: "TaskID=<?php echo $_GET['ID']; ?>&" + dapat,
				success: function(data, textStatus, jqXHR) {
					if (data == 'success') {
						alert('Approval was done!');
						location.href = './purchasing-order-approval';

						setTimeout(function() {


							location.reload();

						}, 1000);

					} else {

					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("error");

				}
			});

		}

	})


	function getAllValues() {
		var inputValues = [];
		$('#VendorDetails input,select,textarea').each(function() {
			var $this = $(this);

			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked == true) {
				//alert(this.id + "  " + this.checked);
				inputValues.push($(this).attr("id") + "=" + $(this).val());

			} else if (type == "text" || type == "hidden") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($this.is("textarea") || $this.is("select")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}

	function Updategrids() {
		var outgrid = 0;
		for (var i = 0; i < POMGTNEW.getRowsNum(); i++) {
			rowIdMov = POMGTNEW.getRowId(i);
			PPOMGTNEW.setUpdated(rowIdMov, true);
		}
		hitungbawah();
		return outgrid;
	}

	function sumColumnNya(ind) {
		var outnya = 0;
		for (var i = 0; i < POMGTNEW.getRowsNum(); i++) {
			outnya +=Math.ceil(parseFloat(POMGTNEW.cells2(i, ind).getValue()));

		}
		return outnya;
	}

	function HitungTotal() {
		var srQ = document.getElementById("JumAmount1");
		srQ.innerHTML = (number_format(sumColumnNya(7)));
		var srQ2 = document.getElementById("JumAmount2");
		srQ2.innerHTML = (number_format(sumColumnNya(10)));
		var srQ3 = document.getElementById("JumAmount3");
		srQ3.innerHTML = (number_format(sumColumnNya(13)));
		var srQ4 = document.getElementById("JumAmount4");
		srQ4.innerHTML = (number_format(sumColumnNya(16)));
		var srQ5 = document.getElementById("JumAmount5");
		srQ5.innerHTML = (number_format(sumColumnNya(19)));
		return true;
	}

	function HitungLagi(stage, rId, cInd, nValue, oValue) {
		if (cInd == 8 || cInd == 11 || cInd == 14 || cInd == 17 || cInd == 20) {
			hitungbawah();
		} else {
			if (stage == 2) {

				propose = parseFloat(POMGTNEW.cells(rId, 4).getValue());
				lima = POMGTNEW.cells(rId, 6).getValue();
				enam = POMGTNEW.cells(rId, 7).getValue();
				lapan = POMGTNEW.cells(rId, 9).getValue();
				smbilan = POMGTNEW.cells(rId, 10).getValue();
				sebelas = POMGTNEW.cells(rId, 12).getValue();
				duabelas = POMGTNEW.cells(rId, 13).getValue();
				empatbelas = POMGTNEW.cells(rId, 15).getValue();
				limabelas = POMGTNEW.cells(rId, 16).getValue();
				tujuhbelas = POMGTNEW.cells(rId, 18).getValue();
				lapanbelas = POMGTNEW.cells(rId, 19).getValue();
				enam = parseFloat(lima) * propose;

				smbilan = parseFloat(lapan) * propose;
				duabelas = parseFloat(sebelas) * propose;
				limabelas = parseFloat(empatbelas) * propose;
				lapanbelas = parseFloat(tujuhbelas) * propose;

				POMGTNEW.cells(rId, 7).setValue(enam);
				POMGTNEW.cells(rId, 10).setValue(smbilan);
				POMGTNEW.cells(rId, 13).setValue(duabelas);
				POMGTNEW.cells(rId, 16).setValue(limabelas);
				POMGTNEW.cells(rId, 19).setValue(lapanbelas);

				hitungbawah();
			}
		}
		HitungTotal();

		return true;
	}

	function hitungbawah() {
		jumlahkan("V1", 8);
		jumlahkan("V2", 11);
		jumlahkan("V3", 14);
		jumlahkan("V4", 17);
		jumlahkan("V5", 20);
	}

	function jumlahkan(urutan, nomors) {
		var datajum = sumColumnNya(nomors - 1);

		var feemgt = parseFloat($("#" + urutan + "_Fee_Mgt").val()) + 0;
		var feevat = $("#" + urutan + "_Fee_VAT").is(':checked');
		var restitusi = parseFloat($("#" + urutan + "_Discount").val()) + 0;
		var ppn = parseFloat($("#" + urutan + "_VAT").val()) + 0;
		var dcv = parseFloat($("#" + urutan + "_DC").val()) + 0;
		var pph = parseFloat($("#" + urutan + "_PPH").val()) + 0;

		if (feevat == '1' || feevat == 'on') {
			datajum = datajum - restitusi;

			ppn = datajum * (ppn / 100);
			totaldapat = feemgt + datajum + ppn;
			pph = totaldapat * (pph / 100);
			totalsemua = (totaldapat + dcv) - pph;
		} else {
			datajum = datajum - restitusi;
			ppn = (feemgt + datajum) * (ppn / 100);
			totaldapat = feemgt + datajum + ppn;
			pph = totaldapat * (pph / 100);
			totalsemua = (totaldapat + dcv) - pph;

		}
		totalsemua = Math.trunc(totalsemua);
		$("#" + urutan + "_Total").val(totalsemua);
		gantimodel(urutan, totalsemua);
	}
</script>


<div id="DivProcess"></div>