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
                        <label class="form-label small fw-semibold">PIC to Receive <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Name" class="form-control form-control-sm" value="{{ $ah->PIC_Name }}" required />
                        <input type="hidden" id="PIC_NIK" value="{{ $ah->PIC_NIK }}" />
                        <div class="form-text">Penerima Barang</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Department <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Department" class="form-control form-control-sm" required value="{{ $ah->PIC_Department }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Phone <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Phone" class="form-control form-control-sm" required value="{{ $ah->PIC_Phone }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">References/FPB (Manual)</label>
                        <input type="text" id="Reference" class="form-control form-control-sm" value="{{ $ah->Reference }}" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Remark <span class="text-danger">*</span></label>
                        <textarea id="Remark" class="form-control form-control-sm" rows="2" required>{{ $ah->Remark }}</textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Item Handover (Centang &amp; Isi Tanggal Penerimaan)</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approval Note <span class="text-danger">*</span></label>
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

    var WinPR;
    var WPR;

    function loadPR_Product(text, url, width, height) {
        WinPR = new dhtmlXWindows();
        WinPR.attachViewportTo("ContentPages");
        WPR = WinPR.createWindow("WPR", 0, 0, width, height);
        WPR.keepInViewport(false);
        WPR.setText(text);
        WPR.attachURL(url, true);
        WPR.button("minmax1").hide();
        WPR.button("park").hide();
        WPR.centerOnScreen();
        WPR.allowMove();
    }

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
    AssetHandoverDetail.setHeader("Checkbox,Purchase Type,Product Code,Product Desc,Qty,Product Brand, Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit, Total Price,Received Date,Status");
    AssetHandoverDetail.setInitWidths("50,100,100,100,200,200,200,200,200,100,200,100,100,0,0,100,0");
    AssetHandoverDetail.setColTypes("ch,ro,ro,ro,edn,ed,ed,ed,ed,ed,ed,ro,ro,edn,ron,dhxCalendar,ro");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,center,center,center,right,right,center,center");
    AssetHandoverDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
    AssetHandoverDetail.setEditable(true);
    AssetHandoverDetail.setNumberFormat("0,000.00", 4, ".", ",");
    AssetHandoverDetail.setNumberFormat("0,000.00", 13, ".", ",");
    AssetHandoverDetail.setNumberFormat("0,000.00", 14, ".", ",");
    AssetHandoverDetail.setDateFormat("%Y-%m-%d");
    AssetHandoverDetail.enableKeyboardSupport(true);
    AssetHandoverDetail.setColumnHidden(13, true);
    AssetHandoverDetail.setColumnHidden(14, true);
    AssetHandoverDetail.setColumnHidden(16, true);
    AssetHandoverDetail.setSkin("dhx_skyblue");
    AssetHandoverDetail.attachEvent("onRowDblClicked", loadPrProduct);
    AssetHandoverDetail.enableMultiline(true);

    PAssetHandoverDetail = new dataProcessor("hrd-asset-handover-confirmation-submit");
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
    AssetHandoverDetail.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
        if (stage == 2) {
            if (cInd == 4 || cInd == 13) {
                let qty = Number(AssetHandoverDetail.cells(rId, 4).getValue());
                let price = Number(AssetHandoverDetail.cells(rId, 13).getValue());
                if (qty == '' || price == '') {
                    AssetHandoverDetail.cells(rId, 14).setValue('');
                    return true;
                }
                AssetHandoverDetail.cells(rId, 14).setValue(qty * price);
            }
        }
        return true;
    });

    refreshDataDetail();
    function refreshDataDetail() {
        AssetHandoverDetail.clearAndLoad("hrd-asset-handover-confirmation-detail-data?Handover_No={{ $ah->Handover_No }}");
    }

    function loadPrProduct(rId, cInd, event) {
        if (cInd == 2 || cInd == 3)
            loadPR_Product('Product Lists', 'hrd-asset-handover-product', 1024, 450);
        return true;
    }

    function approval(stat) {
        if (stat == 1) {
            stat = "APPROVED";
        } else if (stat == 2) {
            stat = "REJECTED";
        }
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
                    let date = AssetHandoverDetail.cells(ele, 15).getValue();
                    if (date == '') {
                        alert("Tanggal penerimaan wajib diisi pada item yang dipilih");
                        broke = 1;
                        break;
                    }
                    AssetHandoverDetail.cells(ele, 16).setValue(stat);
                    PAssetHandoverDetail.setUpdated(ele, true, "updated");
                }
            }
        }

        if (broke) return;

        if (checkedCount === 0) {
            alert("Pilih minimal satu item detail (centang checkbox) yang ingin di-approve/reject");
            return;
        }

        PAssetHandoverDetail.sendData();
        if ($.blockUI) $.blockUI();

        var dapat = getAllValues();
        dapat += "&status=" + stat;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-asset-handover-confirmation-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: dapat,
            success: function(data) {
                if (data.status == 'success') {
                    alert('Asset Handover ' + stat + ' successfully for No: {{ $ah->Handover_No }}!');
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
                alert("Error processing approval request");
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
