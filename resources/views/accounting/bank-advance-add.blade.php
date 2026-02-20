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



?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW ADVANCE</div>
<div id="AdvanceForm">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Date Request

		</div>

		<div class="FormInput_right">

			<input type="text" id="Trx_Date" readonly="readonly" size="15" value="<?php echo date('Y-m-d'); ?>" />

		</div>

		<div class="FormInput_left">

			User Responsibility

		</div>
		<div class="FormInput_right">
			<input type="text" size="20" id="User_Responsibility" /> :: <input type="text" readonly="readonly" size="40" id="User_Responsibility_Name" />

		</div>
		<div class="FormInput_left">

			Department

		</div>
		<div class="FormInput_right">

			<input type="text" id="Department" size="40" readonly="readonly" />

		</div>
		<div class="FormInput_left">

			Cash Value

		</div>
		<div class="FormInput_right">

			<input type="text" id="Advance_Val" size="20" value="0" />

		</div>


		<div class="FormInput_left">

			Reference

		</div>
		<div class="FormInput_right">

			<input type="text" id="Reference" size="40" />

		</div>
		<div class="FormInput_left" style="height:80px;">

			Note

		</div>
		<div class="FormInput_right" style="height:80px;">
			<textarea id="Note" cols="40" rows="3"></textarea>

		</div>
		<div class="FormInput_right">

			<input type="button" value="Submit" id="SubmitAdvance" />

		</div>

	</div>
</div>
<script>
	$("#User_Responsibility").coolautosuggest({
		url: "accounting-bank-advance-search-user?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#User_Responsibility"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {
				$("#User_Responsibility").val(result.id);
				$("#User_Responsibility_Name").val(result.data);
				$("#Department").val(result.thumbnail);
				$("#Counterpart_Code").focus();

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

	function SubmitTransferan() {
		dapat = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "accounting-bank-advance-add-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: dapat,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {
					alert('Bank Advance Task Created!');

					setTimeout(function() {

						$("#DivCashDetail").html('');

						refreshdata();
					}, 1000);

				} else {

				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("error");

			}
		});
	}
</script>