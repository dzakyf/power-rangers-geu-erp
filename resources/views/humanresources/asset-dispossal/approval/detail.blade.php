<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-clipboard-check me-2"></i>Approval Pemusnahan Aset</h6>
            <button type="button" id="CloseAssetDisposalDetail" name="CloseAssetDisposalDetail"
                class="btn btn-light btn-sm" onclick="$('#AssetDisposalDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Pemohon &amp;
                    Formulir</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nomor Formulir</label>
                        <input type="text" id="Disposal_No"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->Disposal_No }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tanggal Pengajuan</label>
                        <input type="text" id="Disposal_Date"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->Disposal_Date }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Pemohon</label>
                        <input type="text" id="User_Name"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->User_Name }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" id="User_Position"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->User_Position }}" />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Aset /
                    Equipment</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Aset / Equipment</label>
                        <input type="text" id="Asset_Name"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->Asset_Name }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor Seri</label>
                        <input type="text" id="Asset_Serial_No"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->Asset_Serial_No }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kondisi Aset /
                            Equipment</label>
                        <input type="text" id="Asset_Condition"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->Asset_Condition }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Lokasi Saat Ini</label>
                        <input type="text" id="Asset_Location"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->Asset_Location }}" />
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Deskripsi Aset /
                            Equipment</label>
                        <textarea id="Asset_Description" class="form-control form-control-sm bg-light" rows="2" readonly>{{ $ad->Asset_Description }}</textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Pemusnahan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Alasan Pemusnahan</label>
                        <textarea id="Disposal_Reason" class="form-control form-control-sm bg-light" rows="3" readonly>{{ $ad->Disposal_Reason }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Pemusnahan</label>
                        <input type="text" id="Disposal_Actual_Date"
                            class="form-control form-control-sm bg-light" readonly
                            value="{{ $ad->Disposal_Actual_Date }}" />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Persetujuan</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bagian</th>
                                <th>Nama</th>
                                <th style="width: 160px;">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Diajukan Oleh - Pemohon</strong></td>
                                <td><input type="text" id="Pemohon_Name"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->Pemohon_Name }}" /></td>
                                <td><input type="text" id="Pemohon_Date"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->Pemohon_Date }}" /></td>
                            </tr>
                            <tr>
                                <td><strong>Kepala Divisi</strong></td>
                                <td><input type="text" id="Kadiv_Name"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->Kadiv_Name }}" /></td>
                                <td><input type="text" id="Kadiv_Date"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->Kadiv_Date }}" /></td>
                            </tr>
                            <tr>
                                <td><strong>Ast. Man. Ops. Support &amp; Controller</strong></td>
                                <td><input type="text" id="AstOps_Name"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->AstOps_Name }}" /></td>
                                <td><input type="text" id="AstOps_Date"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->AstOps_Date }}" /></td>
                            </tr>
                            <tr>
                                <td><strong>VP Operation</strong></td>
                                <td><input type="text" id="VPO_Name"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->VPO_Name }}" /></td>
                                <td><input type="text" id="VPO_Date"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->VPO_Date }}" /></td>
                            </tr>
                            <tr>
                                <td><strong>Manager HCGA</strong></td>
                                <td><input type="text" id="HCGA_Name"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->HCGA_Name }}" /></td>
                                <td><input type="text" id="HCGA_Date"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->HCGA_Date }}" /></td>
                            </tr>
                            <tr>
                                <td><strong>Direktur</strong></td>
                                <td><input type="text" id="Direktur_Name"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->Direktur_Name }}" /></td>
                                <td><input type="text" id="Direktur_Date"
                                        class="form-control form-control-sm"
                                        value="{{ $ad->Direktur_Date }}" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approval Note</label>
                        <textarea id="Approval_Note" class="form-control form-control-sm" rows="2"
                            placeholder="Tambahkan catatan persetujuan atau penolakan..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" id="RejectAssetDisposalDetail"
                        name="RejectAssetDisposalDetail" class="btn btn-danger btn-sm"
                        onclick="approval(2)">
                        <i class="fa fa-times me-1"></i> Reject
                    </button>
                    <button type="button" id="SubmitAssetDisposalDetail"
                        name="SubmitAssetDisposalDetail" class="btn btn-success btn-sm"
                        onclick="approval(1)">
                        <i class="fa fa-check me-1"></i> Approve
                    </button>
                </div>
                <div id="WinCOA"></div>
            </div>
        </div>
    </div>
</div>

<script>
        function getAllValues() {
            var inputValues = [];
            $('#ContentPages input,select,textarea').each(function() {
                var id = $(this).attr("id");
                if (!id) return;
                var type = $(this).attr("type");
                if ((type == "checkbox" || type == "radio") && this.checked) {
                    inputValues.push(id + "=" + encodeURIComponent($(this).val()));
                } else if (type != "submit" && type != "button") {
                    inputValues.push(id + "=" + encodeURIComponent($(this).val()));
                }
            });
            return inputValues.join('&');
        }

    if (typeof dhtmlXCalendarObject !== 'undefined') {
        var ApprovalCal = new dhtmlXCalendarObject(["Pemohon_Date", "Kadiv_Date", "AstOps_Date", "VPO_Date",
            "HCGA_Date", "Direktur_Date"
        ]);
        ApprovalCal.setDateFormat("%Y-%m-%d");
    }


    function approval(stat) {
        stat = (stat == 1) ? "APPROVED" : "REJECTED";
        if (confirm("Yakin memproses formulir ini sebagai " + stat + "?")) {
            if ($.blockUI) $.blockUI();
            var dapat = getAllValues();
            dapat += "&status=" + stat;

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                url: "hrd-asset-dispossal-confirmation-submit?_token={{ csrf_token() }}",
                type: "POST",
                data: dapat,
                success: function(data) {
                    if (data.status == 'success') {
                        alert('Asset Disposal ' + stat + ' successfully for No: {{ $ad->Disposal_No }}!');
                        setTimeout(function() {
                            if ($.unblockUI) $.unblockUI();
                            if (typeof refreshData === 'function') refreshData();
                            $('#AssetDisposalDiv').html("");
                        }, 500);
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat memproses approval');
                        if ($.unblockUI) $.unblockUI();
                    }
                },
                error: function() {
                    alert("Error processing approval request");
                    if ($.unblockUI) $.unblockUI();
                }
            });
        }
    }
</script>

<div id="PRSupplierDet"></div>
