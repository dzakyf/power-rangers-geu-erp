<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-hands-helping me-2"></i>Form Handover</h6>
            <button type="button" id="CloseAssetHandoverDetail" name="CloseAssetHandoverDetail" class="btn btn-light btn-sm" onclick="$('#AssetHandoverDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Handover &amp; Pembuat</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Handover No</label>
                        <input type="text" id="Handover_No" class="form-control form-control-sm bg-light" readonly value="{{ $Handover_No }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Handover Date</label>
                        <input type="text" id="Handover_Date" class="form-control form-control-sm bg-light" readonly value="{{ date('Y-m-d') }}" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">User NIK</label>
                        <input type="text" id="User_NIK" class="form-control form-control-sm bg-light" value="{{ $NIK }}" readonly />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">User Create</label>
                        <input type="text" id="User_Name" class="form-control form-control-sm bg-light" value="{{ $Saya }}" readonly />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Penerima Barang (PIC) &amp; Catatan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC to Receive <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Name" class="form-control form-control-sm" value="" placeholder="Ketik nama penerima barang..." required />
                        <input type="hidden" id="PIC_NIK" value="" />
                        <div class="form-text">Pilih dari dropdown autocomplete penerima barang.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Department <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Department" class="form-control form-control-sm" required value="" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Phone <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Phone" class="form-control form-control-sm" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">References (Manual)</label>
                        <input type="text" id="Reference" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Remark <span class="text-danger">*</span></label>
                        <textarea id="Remark" class="form-control form-control-sm" rows="2" required placeholder="Minimal 5 karakter..."></textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Item Handover</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>
                <div id="divtom" class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <button type="button" id="AddAssetHandoverDetail" name="AddAssetHandoverDetail" class="btn btn-info btn-sm text-white">
                        <i class="fa fa-plus me-1"></i> Add New
                    </button>
                    <button type="button" id="DelAssetHandoverDetail" name="DelAssetHandoverDetail" class="btn btn-danger btn-sm">
                        <i class="fa fa-trash me-1"></i> Delete
                    </button>
                    <button type="button" id="SubmitAssetHandoverDetail" name="SubmitAssetHandoverDetail" class="btn btn-success btn-sm ms-auto">
                        <i class="fa fa-check me-1"></i> Submit
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
    AssetHandoverDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Product Brand, Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit, Total Price,Received Date");
    AssetHandoverDetail.setInitWidths("50,100,100,100,200,200,200,200,200,100,200,100,100,0,0,100");
    AssetHandoverDetail.setColTypes("ro,ed,ed,ed,edn,ed,ed,ed,ed,ed,ed,ed,ed,edn,edn,dhxCalendar");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,center,center,center,right,right,center");
    AssetHandoverDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
    AssetHandoverDetail.setEditable(true);
    AssetHandoverDetail.setNumberFormat("0,000.00", 4, ".", ",");
    AssetHandoverDetail.setNumberFormat("0,000.00", 13, ".", ",");
    AssetHandoverDetail.setNumberFormat("0,000.00", 14, ".", ",");
    AssetHandoverDetail.setDateFormat("%Y-%m-%d");
    AssetHandoverDetail.enableKeyboardSupport(true);
    AssetHandoverDetail.setColumnHidden(13, true);
    AssetHandoverDetail.setColumnHidden(14, true);
    AssetHandoverDetail.setSkin("dhx_skyblue");
    AssetHandoverDetail.attachEvent("onRowDblClicked", loadPrProduct);
    AssetHandoverDetail.enableMultiline(true);

    PAssetHandoverDetail = new dataProcessor("hrd-asset-handover-new-update");
    PAssetHandoverDetail.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        payload: {
            "Handover_No": "{{ $Handover_No }}"
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

    if (typeof dhtmlXCalendarObject !== 'undefined') {
        var SHPREVDAY_Cal = new dhtmlXCalendarObject(["Handover_Date"]);
        SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");
    }

    $("#AddAssetHandoverDetail").click(function() {
        let ids = AssetHandoverDetail.uid();
        AssetHandoverDetail.addRow(ids, [AssetHandoverDetail.getRowsNum() + 1, '', '', '', '', '', '', '', '', '', '']);
        AssetHandoverDetail.selectRowById(ids);
        loadPR_Product('Product Lists', 'hrd-asset-handover-product', 1024, 450);
    });

    function loadPrProduct(rId, cInd, event) {
        if (cInd == 2 || cInd == 3)
            loadPR_Product('Product Lists', 'hrd-asset-handover-product', 1024, 450);
        return true;
    }

    $("#DelAssetHandoverDetail").click(function() {
        AssetHandoverDetail.deleteSelectedRows();
    });

    $("#SubmitAssetHandoverDetail").click(function() {
        var count = AssetHandoverDetail.getRowsNum();
        if (count == 0) {
            alert('Masukkan Item Request Terlebih Dahulu!');
            return false;
        }
        var Remark = $("#Remark").val();
        var PIC_NIK = $("#PIC_NIK").val();
        var PIC_Department = $("#PIC_Department").val();

        if (PIC_NIK == '') {
            alert("Kolom Penerima Barang (PIC to Receive) Harus Diisi sesuai Dropdown yang muncul!");
            $("#PIC_Name").focus();
            return false;
        } else if (PIC_Department == '') {
            alert("Kolom Department (PIC Department) Harus Diisi!");
            $("#PIC_Department").focus();
            return false;
        } else if (Remark == '') {
            alert("Kolom catatan (Remark) Harus Diisi!");
            $("#Remark").focus();
            return false;
        } else if (Remark.length < 5) {
            alert("Kolom catatan (Remark) minimal 5 karakter!");
            $("#Remark").focus();
            return false;
        }

        PAssetHandoverDetail.sendData();
        if ($.blockUI) $.blockUI();

        var dapat = getAllValues();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-asset-handover-new-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: dapat,
            success: function(data) {
                if (data.status == 'success') {
                    alert('Form Handover Submitted with no {{ $Handover_No }}!');
                    setTimeout(function() {
                        if ($.unblockUI) $.unblockUI();
                        if (typeof refreshData === 'function') refreshData();
                        $('#AssetHandoverDiv').html("");
                    }, 800);
                } else {
                    if ($.unblockUI) $.unblockUI();
                    alert(data.message || 'Gagal menyimpan handover');
                }
            },
            error: function() {
                if ($.unblockUI) $.unblockUI();
                alert("error");
            }
        });
    });

    function selesai() {
        if ($.unblockUI) $.unblockUI();
        return true;
    }
</script>

<div id="PRSupplierDet"></div>
