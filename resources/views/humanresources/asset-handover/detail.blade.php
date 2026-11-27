<div class="mt-3">
    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-file-alt me-2"></i>Detail Form Handover</h6>
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

                @php
                    $ahStatusUpper = strtoupper((string) ($ah->Status ?? 'CREATED'));
                    $isLocked = in_array($ahStatusUpper, ['APPROVED', 'RETURNED'], true);
                @endphp

                @if($isLocked)
                    <div class="alert alert-secondary small py-2 mb-3">
                        <i class="fa fa-lock me-1"></i>
                        Transaksi ini berstatus <strong>{{ $ahStatusUpper }}</strong>. Data handover dan daftar item terkunci (read-only).
                    </div>
                @endif

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Lead Pemberi, Penerima Barang (PIC) &amp; Catatan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Lead Pemberi (Stage 1)</label>
                        <input type="text" id="Lead_Name" class="form-control form-control-sm {{ $isLocked ? 'bg-light' : '' }}" value="{{ $ah->Lead_Name ?? '' }}" placeholder="Ketik nama Lead pemberi..." {{ $isLocked ? 'readonly' : '' }} />
                        <input type="hidden" id="Lead_NIK" value="{{ $ah->Lead_NIK ?? '' }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC to Receive (Stage 2) <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Name" class="form-control form-control-sm {{ $isLocked ? 'bg-light' : '' }}" value="{{ $ah->PIC_Name }}" required {{ $isLocked ? 'readonly' : '' }} />
                        <input type="hidden" id="PIC_NIK" value="{{ $ah->PIC_NIK }}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Department <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Department" class="form-control form-control-sm {{ $isLocked ? 'bg-light' : '' }}" required value="{{ $ah->PIC_Department }}" {{ $isLocked ? 'readonly' : '' }} />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">PIC Phone <span class="text-danger">*</span></label>
                        <input type="text" id="PIC_Phone" class="form-control form-control-sm {{ $isLocked ? 'bg-light' : '' }}" required value="{{ $ah->PIC_Phone }}" {{ $isLocked ? 'readonly' : '' }} />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">References/FPB (Manual)</label>
                        <input type="text" id="Reference" class="form-control form-control-sm {{ $isLocked ? 'bg-light' : '' }}" value="{{ $ah->Reference }}" {{ $isLocked ? 'readonly' : '' }} />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Remark <span class="text-danger">*</span></label>
                        <textarea id="Remark" class="form-control form-control-sm {{ $isLocked ? 'bg-light' : '' }}" rows="2" required {{ $isLocked ? 'readonly' : '' }}>{{ $ah->Remark }}</textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Item Handover</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>
                @if(!$isLocked)
                    <div id="divtom" class="d-flex flex-wrap align-items-center gap-2 mt-3">
                        <button type="button" id="AddAssetHandoverDetail" name="AddAssetHandoverDetail" class="btn btn-info btn-sm text-white">
                            <i class="fa fa-plus me-1"></i> Add New
                        </button>
                        <button type="button" id="DelAssetHandoverDetail" name="DelAssetHandoverDetail" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash me-1"></i> Delete
                        </button>
                        <button type="button" id="SubmitAssetHandoverDetail" name="SubmitAssetHandoverDetail" class="btn btn-success btn-sm ms-auto">
                            <i class="fa fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                @endif

                <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-2">4. Cetak BAST Sistem (mPDF) &amp; Lampiran Pendukung (Opsional)</h6>
                @include('humanresources.asset-handover.partials.bast-panel', [
                    'handoverNo' => $ah->Handover_No,
                    'docType'    => 'HANDOVER',
                    'docs'       => $bastHandoverDocs ?? [],
                    'panelId'    => 'bastDetailHandoverPanel',
                    'allowUpload'=> true,
                ])

                @if(!empty($bastReturnDocs) || in_array(strtoupper((string) ($ah->Status ?? '')), ['APPROVED', 'RETURNED'], true))
                    @include('humanresources.asset-handover.partials.bast-panel', [
                        'handoverNo' => $ah->Handover_No,
                        'docType'    => 'RETURN',
                        'docs'       => $bastReturnDocs ?? [],
                        'panelId'    => 'bastDetailReturnPanel',
                        'allowUpload'=> true,
                    ])
                @endif

                @if(!empty($approvalTrail))
                    <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-2">5. Jenjang Approval Handover (ApprovalWorkflowService)</h6>
                    <div class="border rounded">
                        @include('humanresources.approval.partials.trail-table', ['trail' => $approvalTrail])
                    </div>
                @endif

                @if(!empty($returnApprovalTrail))
                    <h6 class="fw-bold border-bottom pb-2 mt-4 mb-2" style="color:#9a6700;">6. Jenjang Approval Pengembalian (ApprovalWorkflowService)</h6>
                    <div class="border rounded">
                        @include('humanresources.approval.partials.trail-table', ['trail' => $returnApprovalTrail])
                    </div>
                @endif

                <div id="WinCOA"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script>
    var isHandoverLocked = {{ $isLocked ? 'true' : 'false' }};

    if (!isHandoverLocked) {
        $("#Lead_Name").coolautosuggest({
            url: "hrd-asset-handover-search-employee?chars=",
            showThumbnail: false,
            showDescription: true,
            idField: $("#Lead_NIK"),
            width: 500,
            minChars: 2,
            onSelected: function(result) {
                if (result != null) {
                    $("#Lead_Name").val(result.data);
                    $("#Lead_NIK").val(result.id);
                }
            }
        }).on('input', function() {
            $("#Lead_NIK").val('');
        });

        $("#PIC_Name").coolautosuggest({
            url: "hrd-asset-handover-search-employee?chars=",
            showThumbnail: false,
            showDescription: true,
            idField: $("#PIC_NIK"),
            width: 500,
            minChars: 2,
            onSelected: function(result) {
                if (result != null) {
                    $("#PIC_Name").val(result.data);
                    $("#PIC_NIK").val(result.id);
                    $("#PIC_Department").val(result.thumbnail || '');
                    $("#PIC_Phone").val(result.phone || '');
                }
            }
        }).on('input', function() {
            $("#PIC_NIK").val('');
        });
    }

    var WinPR;
    var WPR;
    window.__ahPendingNewRowId = null;

    function loadPR_Product(text, url, width, height) {
        if (isHandoverLocked) return;
        if (WinPR && typeof WinPR.unload === 'function') {
            try { WinPR.unload(); } catch (e) {}
        }
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
        WPR.attachEvent("onClose", function() {
            if (window.__ahPendingNewRowId && AssetHandoverDetail.getRowById(window.__ahPendingNewRowId)) {
                var code = String(AssetHandoverDetail.cells(window.__ahPendingNewRowId, 2).getValue() || '').trim();
                var desc = String(AssetHandoverDetail.cells(window.__ahPendingNewRowId, 3).getValue() || '').trim();
                if (code === '' && desc === '') {
                    AssetHandoverDetail.deleteRow(window.__ahPendingNewRowId);
                }
            }
            window.__ahPendingNewRowId = null;
            return true;
        });
    }

    function getAllValues() {
        var inputValues = [];
        $('#ContentPages input,select,textarea').each(function() {
            var id = $(this).attr("id");
            if (!id) return;
            var type = $(this).attr("type");
            if (type === "file") return;
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
    AssetHandoverDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Product Brand, Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit, Total Price,Received Date,Status,Asset No");
    AssetHandoverDetail.setInitWidths("50,100,100,140,80,120,120,150,110,90,140,100,100,0,0,100,0,130");
    AssetHandoverDetail.setColTypes("ro,ro,ro,ro,edn,ed,ed,ed,ed,ed,ed,ro,ro,edn,ron,dhxCalendar,ro,ed");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,center,center,center,right,right,center,center,center");
    AssetHandoverDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,int,int,str,str,str");
    AssetHandoverDetail.setEditable(!isHandoverLocked);
    AssetHandoverDetail.setNumberFormat("0,000.00", 4, ".", ",");
    AssetHandoverDetail.setNumberFormat("0,000.00", 13, ".", ",");
    AssetHandoverDetail.setNumberFormat("0,000.00", 14, ".", ",");
    AssetHandoverDetail.setDateFormat("%Y-%m-%d");
    AssetHandoverDetail.enableKeyboardSupport(true);
    AssetHandoverDetail.setColumnHidden(13, true);
    AssetHandoverDetail.setColumnHidden(14, true);
    AssetHandoverDetail.setColumnHidden(16, true);
    AssetHandoverDetail.setSkin("dhx_skyblue");
    if (!isHandoverLocked) {
        AssetHandoverDetail.attachEvent("onRowDblClicked", loadPrProduct);
    }
    AssetHandoverDetail.enableMultiline(true);

    PAssetHandoverDetail = new dataProcessor("hrd-asset-handover-new-update");
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

    var isSubmittingHandoverUpdate = false;
    var pendingAfterDetailSync = null;
    var detailSyncError = false;

    PAssetHandoverDetail.attachEvent("onAfterUpdate", function(sid, action) {
        if (action === 'error' || action === 'invalid') {
            detailSyncError = true;
        }
        return true;
    });
    PAssetHandoverDetail.attachEvent("onAfterUpdateFinish", function() {
        if (typeof pendingAfterDetailSync === 'function') {
            var cb = pendingAfterDetailSync;
            pendingAfterDetailSync = null;
            cb(detailSyncError);
        }
    });
    if (window.dhx4 && typeof window.dhx4.attachEvent === 'function') {
        window.dhx4.attachEvent("onLoadXMLError", function() {
            if (isSubmittingHandoverUpdate && typeof pendingAfterDetailSync === 'function') {
                pendingAfterDetailSync = null;
                isSubmittingHandoverUpdate = false;
                $("#SubmitAssetHandoverDetail").prop('disabled', false);
                if ($.unblockUI) $.unblockUI();
                alert('Gagal menyimpan detail item handover. Silakan coba lagi.');
            }
        });
    }

    AssetHandoverDetail.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
        if (isHandoverLocked) return false;
        var itemSt = String(AssetHandoverDetail.cells(rId, 16).getValue() || '').toUpperCase().trim();
        if (itemSt === 'APPROVED' || itemSt === 'RETURNED') {
            return false;
        }
        if (stage == 2) {
            if (cInd == 4 || cInd == 13) {
                var rawQty = String(AssetHandoverDetail.cells(rId, 4).getValue() || '').trim();
                var rawPrice = String(AssetHandoverDetail.cells(rId, 13).getValue() || '').trim();
                if (rawQty === '') {
                    AssetHandoverDetail.cells(rId, 14).setValue(0);
                    return true;
                }
                var qty = Number(rawQty);
                var price = rawPrice === '' ? 0 : Number(rawPrice);
                if (isNaN(qty) || isNaN(price)) {
                    AssetHandoverDetail.cells(rId, 14).setValue(0);
                    return true;
                }
                AssetHandoverDetail.cells(rId, 14).setValue(qty * price);
            }
        }
        return true;
    });

    refreshDataDetail();
    function refreshDataDetail() {
        AssetHandoverDetail.clearAndLoad("hrd-asset-handover-detail-data?Handover_No={{ urlencode($ah->Handover_No) }}", function() {
            if (isHandoverLocked) {
                AssetHandoverDetail.forEachRow(function(rId) {
                    AssetHandoverDetail.lockRow(rId, true);
                });
            }
        });
    }

    $("#AddAssetHandoverDetail").click(function() {
        if (isHandoverLocked) return;
        let ids = AssetHandoverDetail.uid();
        window.__ahPendingNewRowId = ids;
        AssetHandoverDetail.addRow(ids, [AssetHandoverDetail.getRowsNum() + 1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        AssetHandoverDetail.selectRowById(ids);
        loadPR_Product('Product Lists', 'hrd-asset-handover-product', 1024, 450);
    });

    function loadPrProduct(rId, cInd, event) {
        if (isHandoverLocked) return false;
        var itemSt = String(AssetHandoverDetail.cells(rId, 16).getValue() || '').toUpperCase().trim();
        if (itemSt === 'APPROVED' || itemSt === 'RETURNED') {
            alert('Item yang sudah disetujui / dikembalikan tidak dapat diubah.');
            return false;
        }
        if (cInd == 2 || cInd == 3) {
            window.__ahPendingNewRowId = null;
            loadPR_Product('Product Lists', 'hrd-asset-handover-product', 1024, 450);
        }
        return true;
    }

    $("#DelAssetHandoverDetail").click(function() {
        if (isHandoverLocked) return;
        let selectedId = AssetHandoverDetail.getSelectedRowId();
        if (!selectedId) {
            alert("Pilih baris item yang ingin dihapus.");
            return;
        }
        let itemStatus = AssetHandoverDetail.cells(selectedId, 16) ? String(AssetHandoverDetail.cells(selectedId, 16).getValue() || '').toUpperCase().trim() : "";
        let receivedDate = AssetHandoverDetail.cells(selectedId, 15) ? AssetHandoverDetail.cells(selectedId, 15).getValue() : "";
        if ((itemStatus === 'APPROVED' && (receivedDate !== "" && receivedDate !== null && receivedDate !== 'null')) || itemStatus === 'RETURNED') {
            alert("Barang yang sudah disetujui / dilakukan handover tidak dapat dihapus!");
            return;
        }
        AssetHandoverDetail.deleteSelectedRows();
    });

    function submitDetailHeader() {
        var dapat = getAllValues();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-asset-handover-detail-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: dapat,
            success: function(data) {
                isSubmittingHandoverUpdate = false;
                $("#SubmitAssetHandoverDetail").prop('disabled', false);
                if ($.unblockUI) $.unblockUI();
                if (data.status == 'success') {
                    alert('Asset Handover updated with no {{ $ah->Handover_No }}!');
                    if (typeof refreshData === 'function') refreshData();
                    $('#AssetHandoverDiv').html("");
                } else {
                    alert(data.message || 'Gagal menyimpan handover');
                }
            },
            error: function(xhr) {
                isSubmittingHandoverUpdate = false;
                $("#SubmitAssetHandoverDetail").prop('disabled', false);
                if ($.unblockUI) $.unblockUI();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error update handover';
                alert(msg);
            }
        });
    }

    $("#SubmitAssetHandoverDetail").click(function() {
        if (isHandoverLocked || isSubmittingHandoverUpdate) return false;

        AssetHandoverDetail.editStop();
        var count = AssetHandoverDetail.getRowsNum();
        if (count == 0) {
            alert('Masukkan Item Request Terlebih Dahulu!');
            return false;
        }

        var invalidRowMsg = null;
        AssetHandoverDetail.forEachRow(function(rId) {
            if (invalidRowMsg) return;
            var code = String(AssetHandoverDetail.cells(rId, 2).getValue() || '').trim();
            var desc = String(AssetHandoverDetail.cells(rId, 3).getValue() || '').trim();
            var qty = Number(AssetHandoverDetail.cells(rId, 4).getValue());
            if (code === '' && desc === '') {
                invalidRowMsg = 'Terdapat baris item yang masih kosong. Silakan pilih produk atau hapus baris kosong.';
            } else if (isNaN(qty) || qty <= 0) {
                invalidRowMsg = 'Qty untuk item "' + (desc || code) + '" harus lebih besar dari 0.';
            }
        });
        if (invalidRowMsg) {
            alert(invalidRowMsg);
            return false;
        }

        var Remark = ($("#Remark").val() || '').trim();
        var Lead_Name = ($("#Lead_Name").val() || '').trim();
        var Lead_NIK = ($("#Lead_NIK").val() || '').trim();
        var PIC_NIK = ($("#PIC_NIK").val() || '').trim();
        var PIC_Department = ($("#PIC_Department").val() || '').trim();

        if (Lead_Name !== '' && Lead_NIK === '') {
            alert("Kolom Lead Pemberi harus dipilih sesuai Dropdown yang muncul!");
            $("#Lead_Name").focus();
            return false;
        } else if (PIC_NIK == '') {
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

        isSubmittingHandoverUpdate = true;
        $("#SubmitAssetHandoverDetail").prop('disabled', true);
        if ($.blockUI) $.blockUI();

        if (PAssetHandoverDetail.getSyncState()) {
            submitDetailHeader();
        } else {
            detailSyncError = false;
            pendingAfterDetailSync = function(hasErr) {
                if (hasErr) {
                    isSubmittingHandoverUpdate = false;
                    $("#SubmitAssetHandoverDetail").prop('disabled', false);
                    if ($.unblockUI) $.unblockUI();
                    alert('Sebagian detail item gagal disimpan. Silakan periksa kembali.');
                    return;
                }
                submitDetailHeader();
            };
            PAssetHandoverDetail.sendData();
        }
    });

    function selesai() {
        if ($.unblockUI) $.unblockUI();
        return true;
    }
</script>

<div id="PRSupplierDet"></div>
