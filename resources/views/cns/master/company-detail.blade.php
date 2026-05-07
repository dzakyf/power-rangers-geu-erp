<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$priv =  Auth::user()->Priv_ID;
$fetch = DB::table("supplier")->where("ID", $_GET['ID'])->first();
$sobjf = DB::select(DB::raw("SHOW FIELDS FROM supplier"));

echo "<script>";

foreach ($sobjf as $objf) {
	$idobj = $objf->Field;
	$isinya = $fetch->$idobj;
	if ($idobj != 'Logo' && $idobj != 'Logo_Inverse' && $idobj != 'LetterHead' && $idobj != 'Footer' && $idobj != 'Stamps' && $idobj != 'Is_Consignee' && $idobj != 'Created_By' && $idobject != 'Created_Date') {
		echo "$('#$idobj').val('$isinya'); \n";
	}
}

echo "</script>";

?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">NEW COMPANY</div>
	<div class="card">
		<div class="card-body pt-2">
			Please fill in the form below to create a new company.
			<div id="ContentPages">
				<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
					<tr>
						<td align="center">
							<table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
								<tr height="30">
									<td class="badan" width="150">
										<label for="Company_Name">
											Company Name
										</label>
									</td>
									<td class="badan" width="10">:</td>
									<td class="badan_kiri" width='250'>
										<input type="hidden" size="30" id="ID" style="border:none;outline:none;" />
										<input type="text" size="30" id="Company_Name" style="border:1px solid lightgrey;border-radius:10px;outline:none;" />

									</td>
									<td class="badan" width="100">
										<label for="Contact_Person">
											Contact Person
										</label>
									</td>
									<td class="badan" width="10">:</td>
									<td class="badan_kiri" width='250'>
										<input type="text" size="30" id="Contact_Person" style="border:1px solid lightgrey;border-radius:10px;outline:none;" />
									</td>
								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Address">
											Address
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'>
										<input type="text" size="40" id="Address" style="border:1px solid lightgrey;border-radius:10px;outline:none;" />
									</td>
									<td class="badan">
										<label for="Street">
											Street
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'>
										<input type="text" size="40" id="Street" tabindex='7' style="border:1px solid lightgrey;border-radius:10px;outline:none;" />
									</td>
								</tr>
								<tr height="30">
									<td class="badan">
										<label for="City">
											City
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="15" id="City" tabindex='2' style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>
									<td class="badan">
										<label for="Phone">
											Phone
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="20" id="Phone" tabindex='8' style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>
								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Fax">
											Fax
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="20" id="Fax" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>
									<td class="badan">
										<label for="HP">
											HP
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="20" id="HP" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>
								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Email">
											Email
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="30" id="Email" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

									<td class="badan">
										<label for="NPWP">
											NPWP
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="30" id="NPWP" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Sales_Name">
											Sales Name
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="30" id="Sales_Name" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

									<td class="badan">
										<label for="Sales_Phone">
											Sales No
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="20" id="Sales_Phone" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Payment">
											Payment Method
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="50" id="Payment" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

									<td class="badan">
										<label for="Note">
											Note
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="50" id="Note" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Bank_Rupiah">
											Bank Name (Rp)
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="40" id="Bank_Rupiah" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

									<td class="badan">
										<label for="Bank_Rupiah_Account">
											Bank Account (Rp)
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="20" id="Bank_Rupiah_Account" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Bank_Rupiah_Name">
											Bank Account Name (Rp)
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="30" id="Bank_Rupiah_Name" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

									<td class="badan">
										<label for="Bank_Foreign">
											Bank Foreign
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="40" id="Bank_Foreign" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Bank_Foreign_Account">
											Bank Foreign Account
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="20" id="Bank_Foreign_Account" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

									<td class="badan">
										<label for="Bank_Foreign_Name">
											Bank Foreign Name
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="30" id="Bank_Foreign_Name" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Bank_Foreign_Currency">
											Bank Foreign Currency
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="text" size="5" value="" id="Bank_Foreign_Currency" style="border:1px solid lightgrey;border-radius:10px;outline:none;" /></td>

									<td class="badan">
										<label for="LetterHead">
											Upload Header
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="file" id="LetterHead" style="border:none;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Footer">
											Footer
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'>
										<input type="file" id="Footer" style="border:none;outline:none;" />
									</td>

									<td class="badan">
										<label for="Logo">
											Logo
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="file" id="Logo" style="border:none;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan">
										<label for="Logo_Inverse">
											Logo Inverse
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="file" id="Logo_Inverse" style="border:none;outline:none;" /></td>

									<td class="badan">
										<label for="Stamps">
											Stamps
										</label>
									</td>
									<td class="badan">:</td>
									<td class="badan_kiri" width='250'><input type="file" id="Stamps" style="border:none;outline:none;" /></td>

								</tr>
								<tr height="30">
									<td class="badan" valign="top"><label>Type</label></td>
									<td class="badan" valign="top">:</td>
									<td class="badan_kiri" width='250'>
										<div><input type="checkbox" id="Is_Consignee" {{ $company->Is_Consignee ? 'checked' : '' }} />&nbsp;<label for="Is_Consignee">Consignee/Buyer</label></div>
										<div><input type="checkbox" id="Is_Supplier" {{ $company->Is_Supplier ? 'checked' : '' }} />&nbsp;<label for="Is_Supplier">Vendor/Supplier</label></div>
									</td>
									<td class="badan"></td>
									<td class="badan"></td>
									<td class="badan_kiri" width='250'>
										<input type="button" class="btn btn-success my-2 mr-2 p-2" id="UpdateData" NAME="UpdateData" onclick="Submitkan()" value="Submit Data" />
									</td>
								</tr>

							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>


<script>
	function isValidImage(file) {
		const allowedExtensions = ['jpg', 'jpeg', 'png'];
		const fileName = file.name.toLowerCase();
		const extension = fileName.split('.').pop();

		return allowedExtensions.includes(extension);
	}

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,select,textarea').each(function() {
			var id = $(this).attr("id");
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push(id + "=" + $(this).val());
			} else if (type != "button" || type != "submit") {
				inputValues.push(id + "=" + $(this).val());
			} else if ($this.is("textarea")) {
				inputValues.push(id + "=" + $(this).val());
			}
		})

		return inputValues.join('&');
	}

	function Submitkan() {
		dapat = getAllValues();
		let formData = new FormData();

		formData.append('_token', '{{ csrf_token() }}')
		formData.append('param', dapat)

		formData.append('Is_Supplier', $('#Is_Supplier').is(':checked') ? 1 : 0);
		formData.append('Is_Consignee', $('#Is_Consignee').is(':checked') ? 1 : 0);

		formData.append('LetterHead', $('#LetterHead')[0].files[0]);
		if ($('#LetterHead')[0].files[0] && !isValidImage($('#LetterHead')[0].files[0])) {
			dhtmlx.alert('File Kop Surat harus jpg, jpeg, atau png');
		}
		formData.append('Footer', $('#Footer')[0].files[0]);
		if ($('#Footer')[0].files[0] && !isValidImage($('#Footer')[0].files[0])) {
			dhtmlx.alert('File Footer harus jpg, jpeg, atau png');
		}
		formData.append('Logo', $('#Logo')[0].files[0]);
		if ($('#Logo')[0].files[0] && !isValidImage($('#Logo')[0].files[0])) {
			dhtmlx.alert('File Logo harus jpg, jpeg, atau png');
		}
		formData.append('Logo_Inverse', $('#Logo_Inverse')[0].files[0]);
		if ($('#Logo_Inverse')[0].files[0] && !isValidImage($('#Logo_Inverse')[0].files[0])) {
			dhtmlx.alert('File Logo Inverse harus jpg, jpeg, atau png');
		}
		formData.append('Stamps', $('#Stamps')[0].files[0]);
		if ($('#Stamps')[0].files[0] && !isValidImage($('#Stamps')[0].files[0])) {
			dhtmlx.alert('File Stamps harus jpg, jpeg, atau png');
		}

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "cns-master-company-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			cache: false,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {
					alert('Detail Company Updated!');

					setTimeout(function() {

						refreshdata();
						$("#PRCompanyDet").html('');

					}, 500);

				} else {

				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR, textStatus, errorThrown)
				alert("error");

			}
		});
	}
</script>