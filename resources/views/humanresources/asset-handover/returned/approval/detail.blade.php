<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-clipboard-check me-2"></i>Approval Pengembalian Handover</h6>
            <button type="button" id="CloseAssetHandoverDetail" name="CloseAssetHandoverDetail" class="btn btn-light btn-sm" onclick="$('#AssetHandoverDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <input type="hidden" id="ID" value="{{ $ah->ID }}" />
                @include('humanresources.asset-handover.partials.lifecycle-stepper', ['ah' => $ah])

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Handover &amp; Pihak Pengembalian</h6>
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
                        <label class="form-label small fw-semibold">Status Transaksi</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold" readonly value="{{ $ah->Status ?? '-' }}" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">PIC Approval Return (Stage 1)</label>
                        <input type="text" class="form-control form-control-sm bg-light" readonly value="{{ ($ah->Return_Lead_Name ?? $ah->PIC_Name) . (!empty($ah->Return_Lead_NIK) ? ' (' . $ah->Return_Lead_NIK . ')' : '') }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Penerima Pengembalian (Stage 2)</label>
                        <input type="text" class="form-control form-control-sm bg-light" readonly value="{{ ($ah->Return_Receiver_Name ?? '-') . (!empty($ah->Return_Receiver_NIK) ? ' (' . $ah->Return_Receiver_NIK . ')' : '') }}" />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Detail Item Pengembalian</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-2">3. Cetak BAST Return (mPDF) &amp; Lampiran Pendukung (Opsional)</h6>
                @include('humanresources.asset-handover.partials.bast-panel', [
                    'handoverNo'  => $ah->Handover_No,
                    'docType'     => 'RETURN',
                    'docs'        => $bastReturnDocs ?? [],
                    'panelId'     => 'bastReturnApprovalPanel',
                    'allowUpload' => true,
                ])

                @if(!empty($returnApprovalTrail))
                    <h6 class="fw-bold border-bottom pb-2 mt-4 mb-2" style="color:#9a6700;">4. Jenjang Approval Pengembalian (ApprovalWorkflowService)</h6>
                    <div class="border rounded">
                        @include('humanresources.approval.partials.trail-table', ['trail' => $returnApprovalTrail])
                    </div>
                @endif

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approval Note @if(!empty($canAct))<span class="text-muted fw-normal">(wajib jika Reject)</span>@endif</label>
                        <textarea id="Approval_Note" class="form-control form-control-sm {{ empty($canAct) ? 'bg-light' : '' }}" rows="2" placeholder="Tambahkan catatan approval..." {{ empty($canAct) ? 'readonly' : '' }}>{{ empty($canAct) ? ($ah->Approval_Note ?? '') : '' }}</textarea>
                    </div>
                </div>

                @if(strtoupper((string) ($ah->Status ?? '')) === 'RETURNED')
                    <div class="alert alert-success small mt-3 mb-0">
                        <i class="fa fa-check-circle me-1"></i>
                        Pengembalian aset telah selesai disetujui (<strong>RETURNED</strong>) dan kepemilikan aset pada daftar aset telah dikembalikan ke HR / GA / Pool.
                    </div>
                @elseif(empty($canAct))
                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="fa fa-info-circle me-1"></i>
                        Saat ini bukan giliran Anda pada stage aktif, atau approval pengembalian sudah selesai diproses.
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
    AssetHandoverDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Product Brand,Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit,Total Price,Received Date,Status,Returned Qty,Returned Condition,Returned Date,Returned Remark,Asset No");
    AssetHandoverDetail.setInitWidths("45,100,100,120,60,100,100,150,100,80,120,100,100,0,0,100,105,100,120,110,150,120");
    AssetHandoverDetail.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,ron,ro,ro,ro,ro");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,left,center,center,right,right,center,center,right,left,center,left,center");
    AssetHandoverDetail.setColSorting("int,str,str,str,int,str,str,str,str,str,str,str,str,int,int,str,str,int,str,str,str,str");
    AssetHandoverDetail.setEditable(false);
    AssetHandoverDetail.setColumnHidden(13, true);
    AssetHandoverDetail.setColumnHidden(14, true);
    AssetHandoverDetail.setSkin("dhx_skyblue");
    AssetHandoverDetail.enableMultiline(true);
    AssetHandoverDetail.init();
    AssetHandoverDetail.clearAndLoad("hrd-asset-handover-returned-approval-detail-data?Handover_No={{ urlencode($ah->Handover_No) }}", function() {
        var rowNo = 1;
        AssetHandoverDetail.forEachRow(function(rId) {
            AssetHandoverDetail.cells(rId, 0).setValue(rowNo);
            rowNo++;
        });
    });

    var isSubmittingReturnApproval = false;

    function approval(stat) {
        if (isSubmittingReturnApproval) return;
        var targetStatus = (stat == 1) ? "RETURNED" : "APPROVED";

        var note = ($("#Approval_Note").val() || '').trim();
        if (targetStatus !== "RETURNED" && note === '') {
            alert("Mohon isi Approval Note saat menolak pengembalian.");
            $("#Approval_Note").focus();
            return;
        }

        isSubmittingReturnApproval = true;
        $("#SubmitAssetHandoverDetail, #RejectAssetHandoverDetail").prop('disabled', true);
        if ($.blockUI) $.blockUI();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-asset-handover-returned-approval-submit",
            type: "POST",
            data: {
                Handover_No: "{{ $ah->Handover_No }}",
                status: targetStatus,
                Approval_Note: note
            },
            success: function(data) {
                if ($.unblockUI) $.unblockUI();
                if (data.status == 'success') {
                    alert(data.message || 'Approval pengembalian barang berhasil diproses!');
                    if (typeof refreshData === 'function') refreshData();
                    $('#AssetHandoverDiv').html("");
                } else {
                    isSubmittingReturnApproval = false;
                    $("#SubmitAssetHandoverDetail, #RejectAssetHandoverDetail").prop('disabled', false);
                    alert(data.message || 'Terjadi kesalahan saat memproses approval');
                }
            },
            error: function(xhr) {
                isSubmittingReturnApproval = false;
                $("#SubmitAssetHandoverDetail, #RejectAssetHandoverDetail").prop('disabled', false);
                if ($.unblockUI) $.unblockUI();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error processing return approval request';
                alert(msg);
            }
        });
    }
</script>
