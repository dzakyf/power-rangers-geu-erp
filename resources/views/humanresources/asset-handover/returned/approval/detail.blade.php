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

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Handover &amp; Pembuat</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Handover No</label>
                        <input type="text" id="Handover_No" class="form-control form-control-sm bg-light" readonly value="{{ $ah->Handover_No }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Handover Date</label>
                        <input type="text" id="Handover_Date" class="form-control form-control-sm bg-light" readonly value="{{ date('Y-m-d', strtotime($ah->Handover_Date)) }}" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">User NIK</label>
                        <input type="text" id="User_NIK" class="form-control form-control-sm bg-light" value="{{ $ah->User_NIK }}" readonly />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">User Create</label>
                        <input type="text" id="User_Name" class="form-control form-control-sm bg-light" value="{{ $ah->User_Name }}" readonly />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Penerima Barang (PIC) &amp; Catatan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC to Receive</label>
                        <input type="text" id="PIC_Name" class="form-control form-control-sm" value="{{ $ah->PIC_Name }}" required />
                        <input type="hidden" id="PIC_NIK" value="{{ $ah->PIC_NIK }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Department</label>
                        <input type="text" id="PIC_Department" class="form-control form-control-sm" required value="{{ $ah->PIC_Department }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Phone</label>
                        <input type="text" id="PIC_Phone" class="form-control form-control-sm" required value="{{ $ah->PIC_Phone }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">References/FPB (Manual)</label>
                        <input type="text" id="Reference" class="form-control form-control-sm" value="{{ $ah->Reference }}" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Remark</label>
                        <textarea id="Remark" class="form-control form-control-sm" rows="2" required>{{ $ah->Remark }}</textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Item Pengembalian</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approval Note</label>
                        <textarea id="Approval_Note" class="form-control form-control-sm" rows="2" required placeholder="Tambahkan catatan approval..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" id="RejectAssetHandoverDetail" name="RejectAssetHandoverDetail" class="btn btn-danger btn-sm" onclick="approval(2)">
                        <i class="fa fa-times me-1"></i> Reject
                    </button>
                    <button type="button" id="SubmitAssetHandoverDetail" name="SubmitAssetHandoverDetail" class="btn btn-success btn-sm" onclick="approval(1)">
                        <i class="fa fa-check me-1"></i> Approve
                    </button>
                </div>
                <div id="WinCOA"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script>
    $("#PIC_Name").coolautosuggest({
        url: "finance-ap-search-employee?chars=",
        showThumbnail: false,
        showDescription: true,
        idField: $("#PIC_Name"),
        width: 500,
        minChars: 1,
        onSelected: function(result) {
            if (result != null) {
                $("#PIC_Name").val(result.data);
                $("#PIC_NIK").val(result.id);
                $("#PIC_Department").val(result.thumbnail);
                $("#PIC_Phone").val(result.phone);
            }
        }
    });

    function getAllValues() {
        var inputValues = [];
        $('#ContentPages input,select,textarea').each(function() {
            var id = $(this).attr("id");
            if (!id) return;
            var type = $(this).attr("type");
            if ((type == "checkbox" || type == "radio") && this.checked) {
                inputValues.push(id + "=" + encodeURIComponent($(this).val()));
            } else if (type != "button" && type != "submit") {
                inputValues.push(id + "=" + encodeURIComponent($(this).val()));
            }
        });
        return inputValues.join('&');
    }

    AssetHandoverDetail = new dhtmlXGridObject('gridAssetHandoverDetail');
    AssetHandoverDetail.setImagePath("dhtmlx/imgs/");
    AssetHandoverDetail.setHeader("Setuju,Purchase Type,Product Code,Product Desc,Qty,Product Brand,Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit,Total Price,Received Date,Status,Returned Qty,Returned Condition,Returned Date,Returned Remark");
    AssetHandoverDetail.setInitWidths("50,100,100,120,60,100,100,150,100,80,120,100,100,0,0,100,100,100,120,110,150");
    AssetHandoverDetail.setColTypes("ch,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,ron,ro,ro,ro");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,left,center,center,right,right,center,center,right,left,center,left");
    AssetHandoverDetail.setColSorting("int,str,str,str,int,str,str,str,str,str,str,str,str,int,int,str,str,int,str,str,str");
    AssetHandoverDetail.setEditable(true);
    AssetHandoverDetail.setDateFormat("%Y-%m-%d");
    AssetHandoverDetail.enableKeyboardSupport(true);
    AssetHandoverDetail.setColumnHidden(13, true);
    AssetHandoverDetail.setColumnHidden(14, true);
    AssetHandoverDetail.setSkin("dhx_skyblue");
    AssetHandoverDetail.enableMultiline(true);

    PAssetHandoverDetail = new dataProcessor("hrd-asset-handover-returned-approval-submit");
    PAssetHandoverDetail.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        payload: {
            "Handover_No": "{{ $ah->Handover_No }}"
        }
    });
    PAssetHandoverDetail.setUpdateMode("Off");
    PAssetHandoverDetail.init(AssetHandoverDetail);
    AssetHandoverDetail.init();

    refreshDataDetail();
    function refreshDataDetail() {
        AssetHandoverDetail.clearAndLoad("hrd-asset-handover-returned-approval-detail-data?Handover_No={{ $ah->Handover_No }}");
    }

    function approval(stat) {
        let targetStatus = (stat == 1) ? "RETURNED" : "APPROVED";
        let checkedCount = 0;
        let rowIds = AssetHandoverDetail.getAllRowIds();
        if (rowIds) {
            let ids = rowIds.split(',');
            for (let i = 0; i < ids.length; i++) {
                const ele = ids[i];
                const check = AssetHandoverDetail.cells(ele, 0).getValue();
                if (check == 1 || check == '1' || check == true) {
                    checkedCount++;
                    AssetHandoverDetail.cells(ele, 16).setValue(targetStatus);
                    PAssetHandoverDetail.setUpdated(ele, true, "updated");
                }
            }
        }

        if (checkedCount === 0) {
            alert("Pilih minimal satu item pengembalian yang ingin di-approve/reject (centang checkbox)");
            return;
        }

        PAssetHandoverDetail.sendData();
        if ($.blockUI) $.blockUI();

        var dapat = getAllValues();
        dapat += "&status=" + targetStatus;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-asset-handover-returned-approval-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: dapat,
            success: function(data) {
                if (data.status == 'success') {
                    alert('Approval pengembalian barang berhasil diproses!');
                    setTimeout(function() {
                        if ($.unblockUI) $.unblockUI();
                        if (typeof refreshData === 'function') refreshData();
                        $('#AssetHandoverDiv').html("");
                    }, 800);
                } else {
                    alert(data.message || 'Terjadi kesalahan saat memproses approval');
                    if ($.unblockUI) $.unblockUI();
                }
            },
            error: function() {
                alert("Error processing return approval request");
                if ($.unblockUI) $.unblockUI();
            }
        });
    }

    function selesai() {
        if ($.unblockUI) $.unblockUI();
        return true;
    }
</script>

<div id="PRSupplierDet"></div>
