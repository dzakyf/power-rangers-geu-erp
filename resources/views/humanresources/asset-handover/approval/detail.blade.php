<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-clipboard-check me-2"></i>Approval Form Handover</h6>
            <button type="button" id="CloseAssetHandoverDetail" name="CloseAssetHandoverDetail" class="btn btn-light btn-sm" onclick="$('#AssetHandoverDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <input type="hidden" id="ID" value="{{ $ah->ID }}" />
                @include('humanresources.asset-handover.partials.lifecycle-stepper', ['ah' => $ah])

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Handover &amp; Pembuat</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Handover No</label>
                        <input type="text" id="Handover_No" class="form-control form-control-sm bg-light" readonly value="{{ $ah->Handover_No }}" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Handover Date</label>
                        <input type="text" id="Handover_Date" class="form-control form-control-sm bg-light" readonly value="{{ date('Y-m-d', strtotime($ah->Handover_Date)) }}" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold" readonly value="{{ $ah->Status ?? 'CREATED' }}" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">User NIK</label>
                        <input type="text" id="User_NIK" class="form-control form-control-sm bg-light" value="{{ $ah->User_NIK }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">User Create</label>
                        <input type="text" id="User_Name" class="form-control form-control-sm bg-light" value="{{ $ah->User_Name }}" readonly />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Lead Pemberi, Penerima Barang (PIC) &amp; Catatan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Lead Pemberi (Stage 1)</label>
                        <input type="text" class="form-control form-control-sm bg-light" readonly value="{{ ($ah->Lead_Name ?? '-') . (!empty($ah->Lead_NIK) ? ' (' . $ah->Lead_NIK . ')' : '') }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC to Receive (Stage 2)</label>
                        <input type="text" id="PIC_Name" class="form-control form-control-sm bg-light" readonly value="{{ $ah->PIC_Name }}" />
                        <input type="hidden" id="PIC_NIK" value="{{ $ah->PIC_NIK }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Department</label>
                        <input type="text" id="PIC_Department" class="form-control form-control-sm bg-light" readonly value="{{ $ah->PIC_Department }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Phone</label>
                        <input type="text" id="PIC_Phone" class="form-control form-control-sm bg-light" readonly value="{{ $ah->PIC_Phone }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">References/FPB (Manual)</label>
                        <input type="text" id="Reference" class="form-control form-control-sm bg-light" readonly value="{{ $ah->Reference }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Remark</label>
                        <textarea id="Remark" class="form-control form-control-sm bg-light" rows="2" readonly>{{ $ah->Remark }}</textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Item Handover</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-2">4. Cetak BAST Sistem (mPDF) &amp; Lampiran Pendukung (Opsional)</h6>
                @include('humanresources.asset-handover.partials.bast-panel', [
                    'handoverNo'  => $ah->Handover_No,
                    'docType'     => 'HANDOVER',
                    'docs'        => $bastHandoverDocs ?? [],
                    'panelId'     => 'bastApprovalHandoverPanel',
                    'allowUpload' => true,
                ])

                @if(!empty($approvalTrail))
                    <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-2">5. Jenjang Approval (ApprovalWorkflowService)</h6>
                    <div class="border rounded">
                        @include('humanresources.approval.partials.trail-table', ['trail' => $approvalTrail])
                    </div>
                @endif

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approval Note @if(!empty($canAct))<span class="text-muted fw-normal">(wajib jika Reject)</span>@endif</label>
                        <textarea id="Approval_Note" class="form-control form-control-sm {{ empty($canAct) ? 'bg-light' : '' }}" rows="2" placeholder="Tambahkan catatan approval..." {{ empty($canAct) ? 'readonly' : '' }}>{{ empty($canAct) ? ($ah->Approval_Note ?? '') : '' }}</textarea>
                    </div>
                </div>

                @if(empty($canAct))
                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="fa fa-info-circle me-1"></i>
                        Saat ini bukan giliran Anda pada stage aktif, atau task approval sudah diproses.
                    </div>
                @else
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" id="RejectAssetHandoverDetail" name="RejectAssetHandoverDetail" class="btn btn-danger btn-sm" onclick="approval(2)">
                            <i class="fa fa-times me-1"></i> Reject
                        </button>
                        <button type="button" id="SubmitAssetHandoverDetail" name="SubmitAssetHandoverDetail" class="btn btn-success btn-sm" onclick="approval(1)">
                            <i class="fa fa-check me-1"></i> Approve
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    AssetHandoverDetail = new dhtmlXGridObject('gridAssetHandoverDetail');
    AssetHandoverDetail.setImagePath("dhtmlx/imgs/");
    AssetHandoverDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Product Brand,Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit,Total Price,Received Date,Asset No");
    AssetHandoverDetail.setInitWidths("50,100,100,140,80,120,120,150,110,90,140,100,100,0,0,100,130");
    AssetHandoverDetail.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ro,ro");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,left,center,center,right,right,center,center");
    AssetHandoverDetail.setColSorting("int,str,str,str,int,str,str,str,str,str,str,str,str,int,int,str,str");
    AssetHandoverDetail.setEditable(false);
    AssetHandoverDetail.setNumberFormat("0,000.00", 4, ".", ",");
    AssetHandoverDetail.setColumnHidden(13, true);
    AssetHandoverDetail.setColumnHidden(14, true);
    AssetHandoverDetail.setSkin("dhx_skyblue");
    AssetHandoverDetail.enableMultiline(true);
    AssetHandoverDetail.init();
    AssetHandoverDetail.clearAndLoad("hrd-asset-handover-confirmation-detail-data?Handover_No={{ urlencode($ah->Handover_No) }}", function() {
        var rowNo = 1;
        AssetHandoverDetail.forEachRow(function(rId) {
            if (!String(AssetHandoverDetail.cells(rId, 0).getValue() || '').trim()) {
                AssetHandoverDetail.cells(rId, 0).setValue(rowNo);
            }
            rowNo++;
        });
    });

    var isSubmittingApproval = false;

    function approval(stat) {
        if (isSubmittingApproval) return;
        var targetStatus = (stat == 1) ? "APPROVED" : "REJECTED";

        var note = ($("#Approval_Note").val() || '').trim();
        if (targetStatus === "REJECTED" && note === '') {
            alert("Mohon isi Approval Note saat menolak handover.");
            $("#Approval_Note").focus();
            return;
        }

        isSubmittingApproval = true;
        $("#SubmitAssetHandoverDetail, #RejectAssetHandoverDetail").prop('disabled', true);
        if ($.blockUI) $.blockUI();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-asset-handover-confirmation-submit",
            type: "POST",
            data: {
                Handover_No: "{{ $ah->Handover_No }}",
                status: targetStatus,
                Approval_Note: note
            },
            success: function(data) {
                if ($.unblockUI) $.unblockUI();
                if (data.status == 'success') {
                    alert(data.message || ('Asset Handover ' + targetStatus + ' berhasil diproses!'));
                    if (typeof refreshData === 'function') refreshData();
                    $('#AssetHandoverDiv').html("");
                } else {
                    isSubmittingApproval = false;
                    $("#SubmitAssetHandoverDetail, #RejectAssetHandoverDetail").prop('disabled', false);
                    alert(data.message || 'Terjadi kesalahan saat memproses approval');
                }
            },
            error: function(xhr) {
                isSubmittingApproval = false;
                $("#SubmitAssetHandoverDetail, #RejectAssetHandoverDetail").prop('disabled', false);
                if ($.unblockUI) $.unblockUI();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error processing approval request';
                alert(msg);
            }
        });
    }
</script>
