<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$cekakun = DB::table('cash_handler')->where('NIK_handler', $Saya)->first();
if ($cekakun->Account_No == '') {

	echo "You Don't have access to this menu!";
	exit;
}

$Saya =  Auth::user()->username;

$Fetch = DB::table("cash_advance")->where("Task_ID", $_GET['ID'])->first();


?>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CASH ADVANCE DETAIL OUTSTANDING</div>
<div id="AdvanceForm">
	<div class="FormInput_Form_Content">

		<div class="FormInput_left">

			Tanggal Permintaan

		</div>

		<div class="FormInput_right">

			<input type="text" id="Advance_Date" size="15" value="<?php echo $Fetch->Advance_Date ?>" disabled="disabled" />
			:: <input type="text" id="ID" size="5" value="<?php echo $Fetch->ID ?>" disabled="disabled" />

		</div>

		<div class="FormInput_left">

			Peminjam/Penanggung Jawab

		</div>
		<div class="FormInput_right">
			<input type="text" size="10" id="User_Responsibility" value="<?php echo $Fetch->User_Responsibility ?>" disabled="disabled" /> :: <input type="text" readonly="readonly" size="30" id="User_Responsibility_Name" value="<?php echo $Fetch->User_Responsibility_Name ?>" disabled="disabled" />:: <input type="text" readonly="readonly" value="<?php echo $Fetch->User_Reference ?>" size="30" id="User_Reference" disabled="disabled" /> <input type="hidden" readonly="readonly" size="20" id="User_Responsibility_ID" value="<?php echo $Fetch->User_Responsibility_ID ?>" />:: <input type="hidden" readonly="readonly" size="20" id="Acc_No" value="<?php echo $Fetch->Acc_No ?>" />

		</div>
		<div class="FormInput_left">

			Departement

		</div>
		<div class="FormInput_right">

			<input type="text" id="Department" size="40" value="<?php echo $Fetch->Department ?>" readonly="readonly" />

		</div>
		<div class="FormInput_left">

			Nilai

		</div>
		<div class="FormInput_right">

			<input type="text" id="Advance_Val" size="20" value="<?php echo $Fetch->Cash_Val ?>" readonly="readonly" />

		</div>
		<div class="FormInput_left">

			Referensi

		</div>
		<div class="FormInput_right">

			<input type="text" id="Reference" size="40" value="<?php echo $Fetch->Reference ?>" />

		</div>
		<div class="FormInput_left">

			Dari Cash Handler

		</div>
		<div class="FormInput_right">
			<select id="Cash_Account">
				<?php
				$Query = DB::table("cash_handler")->where("Status", 'ACTIVE')->where("Position", 'CASHIER')->get();
				foreach ($Query as $Data) {
					echo "<option value='$Data->Name_Handler'>$Data->Account_No - $Data->Name_Handler</option>";
				}
				?>

			</select>
		</div>
		<div class="FormInput_left">

			Tanggal Pembayaran

		</div>

		<div class="FormInput_right">

			<input type="text" id="Date_Payment" size="15" value="<?php echo date("Y-m-d"); ?>" />

		</div>

		<div class="FormInput_left" style="height:80px;">

			Note

		</div>
		<div class="FormInput_right" style="height:80px;">
			<textarea id="Description" cols="40" rows="3"><?php echo $Fetch->Description ?></textarea>

		</div>
		<div class="FormInput_right" style="height:80px;">

			<input type="button" value="Submit" id="SubmitAdvance" onclick="SubmitAdvance()" class="btn btn-success mt-2 mr-2" />

		</div>

	</div>
</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>
	$("#Advance_Val").on("keyup", function(event) {

		// 1.
		var selection = window.getSelection().toString();
		if (selection !== '') {
			return;
		}

		// 2.
		if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
			return;
		}
		var $this = $(this);
		var input = $this.val();

		// 2
		var input = input.replace(/[\D\s\._\-]+/g, "");

		// 3
		input = input ? parseInt(input, 10) : 0;

		// 4
		$this.val(function() {
			return (input === 0) ? "" : input.toLocaleString("en-US");
		});

	});

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Advance_Date", "Date_Payment"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$("#User_Responsibility").coolautosuggest({
		url: "finance-bank-advance-search-user?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#User_Responsibility"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {

				var Description = result.description;
				Expr = Description.split('<br>');
				$("#Acc_No").val(result.thumbnail);
				$("#User_Responsibility_ID").val(result.id);
				$("#User_Responsibility").val(result.thumbnail);
				$("#User_Reference").val(Expr[2]);
				$("#User_Responsibility_Name").val(result.data);
				$("#Department").val(Expr[1]);
				$("#Counterpart_Code").focus();

			}
		}
	});
	$("#Reference").coolautosuggest({
		url: "reference-search?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#Reference"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {

				var Description = result.description;
				Expr = Description.split('<br>');
				$("#Reference").val(result.id);

			}
		}
	});

	function getAllValues() {
		var inputValues = [];
		$('#AdvanceForm input,select,textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if (type != "button" || type != "submit") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($this.is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})

		return inputValues.join('&');
	}

	function SubmitAdvance() {
		dapat = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "finance-cash-advance-detail-outstanding-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: dapat,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {
					alert('Cash Advance Issued!');

					setTimeout(function() {

						location.href = 'finance-cash-advance-outstanding';

					}, 1000);

				} else {
					alert(data);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("error");

			}
		});
	}
</script>