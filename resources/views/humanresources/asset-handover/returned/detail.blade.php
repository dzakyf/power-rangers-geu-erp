<div class="mt-3">
    <div class="card border-warning shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fa fa-undo me-2"></i>Detail Pengembalian Handover</h6>
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

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Item Pengembalian (Centang &amp; Isi Jumlah/Tanggal Pengembalian)</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approval Note</label>
                        <textarea id="Approval_Note" class="form-control form-control-sm" rows="2" required placeholder="Catatan pengembalian (opsional)..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" id="SubmitReturnAssetHandoverDetail" name="SubmitReturnAssetHandoverDetail" class="btn btn-warning btn-sm text-dark fw-semibold" onclick="submitReturn()">
                        <i class="fa fa-paper-plane me-1"></i> Submit Pengembalian
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

    AssetHandoverDetail = new dhtmlXGridObject('gridAssetHandoverDetail');
    AssetHandoverDetail.setImagePath("dhtmlx/imgs/");
    AssetHandoverDetail.setHeader("Pilih,Purchase Type,Product Code,Product Desc,Qty,Product Brand,Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit,Total Price,Received Date,Status,Returned Qty,Returned Condition,Returned Date,Returned Remark");
    AssetHandoverDetail.setInitWidths("50,100,100,120,60,100,100,150,100,80,120,100,100,0,0,100,100,100,120,110,150");
    AssetHandoverDetail.setColTypes("ch,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,edn,ed,dhxCalendar,txt");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,left,center,center,right,right,center,center,right,left,center,left");
    AssetHandoverDetail.setColSorting("int,str,str,str,int,str,str,str,str,str,str,str,str,int,int,str,str,int,str,str,str");
    AssetHandoverDetail.setEditable(true);
    AssetHandoverDetail.setDateFormat("%Y-%m-%d");
    AssetHandoverDetail.enableKeyboardSupport(true);
    AssetHandoverDetail.setColumnHidden(13, true);
    AssetHandoverDetail.setColumnHidden(14, true);
    AssetHandoverDetail.setSkin("dhx_skyblue");
    AssetHandoverDetail.enableMultiline(true);

    PAssetHandoverDetail = new dataProcessor("hrd-asset-handover-returned-detail-submit");
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
        AssetHandoverDetail.clearAndLoad("hrd-asset-handover-returned-detail-data?Handover_No={{ $ah->Handover_No }}");
    }

    function submitReturn() {
        let broke = 0;
        let checkedCount = 0;
        let rowIds = AssetHandoverDetail.getAllRowIds();
        if (rowIds) {
            let ids = rowIds.split(',');
            for (let i = 0; i < ids.length; i++) {
                const ele = ids[i];
                const check = AssetHandoverDetail.cells(ele, 0).getValue();
                if (check == 1 || check == '1' || check == true) {
                    checkedCount++;
                    let retQty = AssetHandoverDetail.cells(ele, 17).getValue();
                    let retDate = AssetHandoverDetail.cells(ele, 19).getValue();
                    if (retQty <= 0) {
                        alert("Jumlah pengembalian (Returned Qty) harus lebih besar dari 0");
                        broke = 1;
                        break;
                    }
                    if (retDate == '') {
                        alert("Tanggal pengembalian (Returned Date) wajib diisi");
                        broke = 1;
                        break;
                    }
                    PAssetHandoverDetail.setUpdated(ele, true, "updated");
                }
            }
        }

        if (broke) return;

        if (checkedCount === 0) {
            alert("Pilih minimal satu item barang yang ingin dikembalikan (centang checkbox)");
            return;
        }

        PAssetHandoverDetail.sendData();
        if ($.blockUI) $.blockUI();

        setTimeout(function() {
            if ($.unblockUI) $.unblockUI();
            alert('Pengajuan pengembalian barang berhasil dikirimkan!');
            if (typeof refreshData === 'function') refreshData();
            $('#AssetHandoverDiv').html("");
        }, 1200);
    }

    function selesai() {
        if ($.unblockUI) $.unblockUI();
        return true;
    }
</script>

<div id="PRSupplierDet"></div>
