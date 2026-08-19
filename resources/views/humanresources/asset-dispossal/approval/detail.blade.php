<div class="mt-3">
    <div class="card">
        <div class="card-header my-2 ml-2">
            <h6 class="d-inline-block">Approval Pemusnahan Aset</h6>
			<span class="float-right">
				<input type="button" id="CloseAssetDisposalDetail" class="btn btn-secondary mr-2 py-2 px-3 my-2" NAME="CloseAssetDisposalDetail" value="Close" onclick="$('#AssetDisposalDiv').html('')" />
			</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div id="ContentPages">
                    <div class="FormInput_Form_Content">
                        <div class="FormInput_left">Nomor Formulir</div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_No" size="30" readonly="readonly" value="{{ $ad->Disposal_No }}" />
                        </div>
                        <div class="FormInput_left">Tanggal Pengajuan</div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_Date" readonly="readonly" size="15" value="{{ $ad->Disposal_Date }}" />
                        </div>
                        <div class="FormInput_left">Pemohon</div>
                        <div class="FormInput_right">
                            <input type="text" id="User_Name" size="40" readonly="readonly" value="{{ $ad->User_Name }}" /> &nbsp;
                            <input type="text" id="User_Position" size="30" readonly="readonly" value="{{ $ad->User_Position }}" />
                        </div>

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Informasi Aset / Equipment</h6>
                        <div class="FormInput_left">Nama Aset/equipment</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Name" size="40" readonly="readonly" value="{{ $ad->Asset_Name }}" />
                        </div>
                        <div class="FormInput_left">Nomor Seri</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Serial_No" size="40" readonly="readonly" value="{{ $ad->Asset_Serial_No }}" />
                        </div>
                        <div class="FormInput_left" style="height:60px;">Deskripsi Aset/equipment</div>
                        <div class="FormInput_right" style="height:60px;">
                            <textarea id="Asset_Description" cols="80" rows="2" readonly="readonly">{{ $ad->Asset_Description }}</textarea>
                        </div>
                        <div class="FormInput_left">Kondisi Aset/equipment</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Condition" size="40" readonly="readonly" value="{{ $ad->Asset_Condition }}" />
                        </div>
                        <div class="FormInput_left">Lokasi Saat Ini</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Location" size="40" readonly="readonly" value="{{ $ad->Asset_Location }}" />
                        </div>

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Detail Pemusnahan</h6>
                        <div class="FormInput_left" style="height:80px;">Alasan Pemusnahan</div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Disposal_Reason" cols="80" rows="3" readonly="readonly">{{ $ad->Disposal_Reason }}</textarea>
                        </div>
                        <div class="FormInput_left">Tanggal Pemusnahan</div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_Actual_Date" size="15" readonly="readonly" value="{{ $ad->Disposal_Actual_Date }}" />
                        </div>

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Persetujuan</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bagian</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Diajukan Oleh&nbsp;-&nbsp;Pemohon</strong></td>
                                    <td><input type="text" id="Pemohon_Name" size="30" value="{{ $ad->Pemohon_Name }}" /></td>
                                    <td><input type="text" id="Pemohon_Date" size="12" value="{{ $ad->Pemohon_Date }}" /></td>
                                </tr>
                                <tr>
                                    <td><strong>Kepala Divisi</strong></td>
                                    <td><input type="text" id="Kadiv_Name" size="30" value="{{ $ad->Kadiv_Name }}" /></td>
                                    <td><input type="text" id="Kadiv_Date" size="12" value="{{ $ad->Kadiv_Date }}" /></td>
                                </tr>
                                <tr>
                                    <td><strong>Ast. Man. Ops. Support &amp; Controller</strong></td>
                                    <td><input type="text" id="AstOps_Name" size="30" value="{{ $ad->AstOps_Name }}" /></td>
                                    <td><input type="text" id="AstOps_Date" size="12" value="{{ $ad->AstOps_Date }}" /></td>
                                </tr>
                                <tr>
                                    <td><strong>VP Operation</strong></td>
                                    <td><input type="text" id="VPO_Name" size="30" value="{{ $ad->VPO_Name }}" /></td>
                                    <td><input type="text" id="VPO_Date" size="12" value="{{ $ad->VPO_Date }}" /></td>
                                </tr>
                                <tr>
                                    <td><strong>Manager HCGA</strong></td>
                                    <td><input type="text" id="HCGA_Name" size="30" value="{{ $ad->HCGA_Name }}" /></td>
                                    <td><input type="text" id="HCGA_Date" size="12" value="{{ $ad->HCGA_Date }}" /></td>
                                </tr>
                                <tr>
                                    <td><strong>Direktur</strong></td>
                                    <td><input type="text" id="Direktur_Name" size="30" value="{{ $ad->Direktur_Name }}" /></td>
                                    <td><input type="text" id="Direktur_Date" size="12" value="{{ $ad->Direktur_Date }}" /></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="FormInput_left" style="height:80px;">Approval Note</div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Approval_Note" cols="80" rows="2"></textarea>
                        </div>
                        <div class="FormInput_left"></div>
                        <div class="FormInput_right" style="height: 60px;">
                            <input type="button" id="RejectAssetDisposalDetail" class="btn btn-danger mt-2 mr-2" NAME="RejectAssetDisposalDetail" onclick="approval(2)" value="Reject" />
                            <input type="button" id="SubmitAssetDisposalDetail" class="btn btn-success mt-2 mr-2" NAME="SubmitAssetDisposalDetail" onclick="approval(1)" value="Approve" />
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

	var ApprovalCal = new dhtmlXCalendarObject(["Pemohon_Date","Kadiv_Date","AstOps_Date","VPO_Date","HCGA_Date","Direktur_Date"]);
	ApprovalCal.setDateFormat("%Y-%m-%d");

	function approval(stat) {
		stat = (stat == 1) ? "APPROVED" : "REJECTED";
		let nama = $("#User_Name").val();
		let tgl = new Date().toISOString().slice(0,10);
		if (confirm("Yakin memproses formulir ini sebagai " + stat + "?")) {
			$.blockUI();
			dapat = getAllValues();
			dapat += "&status=" + stat;

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
				},
				url: "hrd-asset-dispossal-confirmation-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: dapat,
				success: function(data, textStatus, jqXHR) {
					if (data.status == 'success') {
						alert('Asset Disposal ' + stat + ' successfully for No: {{ $ad->Disposal_No }}!');
						setTimeout(function() {
							$.unblockUI();
							if (typeof refreshData === 'function') {
								refreshData();
							}
							$('#AssetDisposalDiv').html("");
						}, 1000);
					} else {
						alert(data.message || 'Terjadi kesalahan saat memproses approval');
						$.unblockUI();
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("Error processing approval request");
					$.unblockUI();
				}
			});
		}
	}
</script>

<div id="PRSupplierDet"></div>