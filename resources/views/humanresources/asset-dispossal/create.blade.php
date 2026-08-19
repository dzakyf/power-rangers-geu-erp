<div class="mt-3">
    <div class="card">
        <div class="card-header my-2 ml-2">
            <h6 class="d-inline-block">Form Pemusnahan Aset</h6>
			<span class="float-right">
				<input type="button" id="CloseAssetDisposalDetail" class="btn btn-secondary mr-2 py-2 px-3 my-2" NAME="CloseAssetDisposalDetail" value="Close" onclick="$('#AssetDisposalDiv').html('')" />
			</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div id="ContentPages">
                    <div class="FormInput_Form_Content">
                        <h6 style="font-weight:bold; margin-bottom:10px;">Identitas Pemohon</h6>
                        <div class="FormInput_left">
                            Nama
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="User_Name" size="40" value="<?php echo $Name ?>" readonly="readonly" />
                        </div>
                        <div class="FormInput_left">
                            Jabatan
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="User_Position" size="40" value="<?php echo $Position ?>" readonly="readonly" />
                        </div>
                        <div class="FormInput_left">
                            Tanggal Pengajuan
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_Date" readonly="readonly" size="15" value="<?php echo date('Y-m-d'); ?>" />
                        </div>
                        <div class="FormInput_left">
                            Nomor Formulir
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_No" size="30" readonly="readonly" value="<?php echo $Disposal_No; ?>" />
                            <input type="hidden" id="User_NIK" value="<?php echo $NIK ?>" />
                        </div>

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Informasi Aset / Equipment</h6>
                        <div class="FormInput_left">
                            Nama Aset/equipment
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Name" size="40" required />
                        </div>
                        <div class="FormInput_left">
                            Nomor Seri
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Serial_No" size="40" />
                        </div>
                        <div class="FormInput_left" style="height:60px;">
                            Deskripsi Aset/equipment
                        </div>
                        <div class="FormInput_right" style="height:60px;">
                            <textarea id="Asset_Description" cols="80" rows="2"></textarea>
                        </div>
                        <div class="FormInput_left">
                            Kondisi Aset/equipment
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Condition" size="40" />
                        </div>
                        <div class="FormInput_left">
                            Lokasi Saat Ini
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Location" size="40" />
                        </div>

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Detail Pemusnahan</h6>
                        <div class="FormInput_left" style="height:80px;">
                            Alasan Pemusnahan
                        </div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Disposal_Reason" cols="80" rows="3" required></textarea>
                        </div>
                        <div class="FormInput_left">
                            Tanggal Pemusnahan
                        </div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_Actual_Date" size="15"  />
                        </div>
                        <div id="divtom">
                            <input type="submit" id="SubmitAssetDisposalDetail" class="btn btn-success mt-2 mr-2" NAME="SubmitAssetDisposalDetail" value="Submit" />
                        </div>
                        <div id="WinCOA"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,select,textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if (type == "submit" || type == "button") {
			} else {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})

		return inputValues.join('&');
	}

	var DisposalCal = new dhtmlXCalendarObject(["Disposal_Actual_Date"]);
	DisposalCal.setDateFormat("%Y-%m-%d");

	$("#SubmitAssetDisposalDetail").click(function() {
		let Asset_Name = $("#Asset_Name").val();
		let Disposal_Reason = $("#Disposal_Reason").val();
		if (Asset_Name == '') {
			alert("Kolom Nama Aset/equipment Harus Diisi!");
			$("#Asset_Name").focus();
			return false;
		}
		if (Disposal_Reason == '') {
			alert("Kolom Alasan Pemusnahan Harus Diisi!");
			$("#Disposal_Reason").focus();
			return false;
		}

		$.blockUI();
		dapat = getAllValues();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			url: "hrd-asset-dispossal-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: dapat,
			success: function(data, textStatus, jqXHR) {
				if (data.status == 'success') {
					alert('Form Pemusnahan Aset Submitted with no <?php echo $Disposal_No ?>!');
					setTimeout(function() {
						$.unblockUI();
						refreshData();
						$('#AssetDisposalDiv').html("");
					}, 1000);
				} else {
					alert(data.message || 'Terjadi kesalahan saat menyimpan data');
					$.unblockUI();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("error");
				$.unblockUI();
			}
		});
	});
</script>

<div id="PRSupplierDet"></div>