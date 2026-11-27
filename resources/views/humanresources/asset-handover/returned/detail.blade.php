<div class="mt-3">
    <div class="card border-warning shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#fff3cd;color:#664d03;border-bottom:1px solid #ffecb5;">
            <h6 class="mb-0 fw-bold" style="color:#664d03;"><i class="fa fa-undo me-2"></i>Detail Pengembalian Handover</h6>
            <button type="button" id="CloseAssetHandoverDetail" name="CloseAssetHandoverDetail" class="btn btn-light btn-sm" onclick="$('#AssetHandoverDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <input type="hidden" id="ID" value="{{ $ah->ID }}" />
                @include('humanresources.asset-handover.partials.lifecycle-stepper', ['ah' => $ah])

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Handover &amp; Pemegang Saat Ini</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Handover No</label>
                        <input type="text" id="Handover_No" class="form-control form-control-sm bg-light" readonly value="{{ $ah->Handover_No }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Handover Date</label>
                        <input type="text" id="Handover_Date" class="form-control form-control-sm bg-light" readonly value="{{ date('Y-m-d', strtotime($ah->Handover_Date)) }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">PIC Pemegang Aset</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $ah->PIC_Name }} ({{ $ah->PIC_Department }})" readonly />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Status Transaksi</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $ah->Status }}" readonly />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Pihak Approval Pengembalian (2 Tahap via ApprovalWorkflowService)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">PIC Approval Pengembalian (Stage 1) <span class="text-danger">*</span></label>
                        <input type="text" id="Return_Lead_Name" class="form-control form-control-sm" value="{{ $ah->Return_Lead_Name ?: $ah->PIC_Name }}" placeholder="Ketik nama PIC Approval pengembalian..." required />
                        <input type="hidden" id="Return_Lead_NIK" value="{{ $ah->Return_Lead_NIK ?: $ah->PIC_NIK }}" />
                        <div class="form-text">Diambil dari tabel employment (jika sama dengan pembuat pengajuan return, Stage 1 otomatis dilewati ke Penerima Pengembalian).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Penerima Pengembalian Aset (Stage 2) <span class="text-danger">*</span></label>
                        <input type="text" id="Return_Receiver_Name" class="form-control form-control-sm" value="{{ $ah->Return_Receiver_Name ?? '' }}" placeholder="Ketik nama penerima pengembalian (GA/ICT)..." required />
                        <input type="hidden" id="Return_Receiver_NIK" value="{{ $ah->Return_Receiver_NIK ?? '' }}" />
                        <div class="form-text">Approver Tahap 2 dari tabel employment (pihak yang menerima aset kembali).</div>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Item Pengembalian (Centang &amp; Isi Jumlah/Tanggal Pengembalian)</h6>
                <div id="gridAssetHandoverDetail" style="width:100%;height:220px;background-color:white;overflow:hidden"></div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-2">4. Cetak BAST Return (mPDF) &amp; Lampiran Pendukung (Opsional)</h6>
                @include('humanresources.asset-handover.partials.bast-panel', [
                    'handoverNo'  => $ah->Handover_No,
                    'docType'     => 'RETURN',
                    'docs'        => $bastReturnDocs ?? [],
                    'panelId'     => 'bastReturnDetailPanel',
                    'allowUpload' => true,
                ])

                @if(!empty($returnApprovalTrail))
                    <h6 class="fw-bold border-bottom pb-2 mt-4 mb-2" style="color:#9a6700;">5. Jenjang Approval Pengembalian (ApprovalWorkflowService)</h6>
                    <div class="border rounded">
                        @include('humanresources.approval.partials.trail-table', ['trail' => $returnApprovalTrail])
                    </div>
                @endif

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Catatan Pengembalian (Approval Note)</label>
                        <textarea id="Approval_Note" class="form-control form-control-sm" rows="2" placeholder="Catatan pengembalian (opsional)..."></textarea>
                    </div>
                </div>

                <div id="returnLockBanner" class="alert alert-info small py-2 mb-3 {{ strtoupper((string) ($ah->Status ?? '')) === 'RETURNED' ? '' : 'd-none' }}">
                    <i class="fa fa-check-circle me-1"></i>
                    Seluruh aset pada transaksi ini sudah berstatus <strong>RETURNED</strong>. Pengembalian selesai diproses.
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" id="SubmitReturnAssetHandoverDetail" name="SubmitReturnAssetHandoverDetail" class="btn btn-warning btn-sm text-dark fw-semibold" onclick="submitReturn()" {{ strtoupper((string) ($ah->Status ?? '')) === 'RETURNED' ? 'disabled' : '' }}>
                        <i class="fa fa-paper-plane me-1"></i> Submit Pengembalian
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script>
    var isFullyReturned = {{ strtoupper((string) ($ah->Status ?? '')) === 'RETURNED' ? 'true' : 'false' }};

    $("#Return_Lead_Name").coolautosuggest({
        url: "hrd-asset-handover-search-employee?chars=",
        showThumbnail: false,
        showDescription: true,
        idField: $("#Return_Lead_NIK"),
        width: 500,
        minChars: 2,
        onSelected: function(result) {
            if (result != null) {
                $("#Return_Lead_Name").val(result.data);
                $("#Return_Lead_NIK").val(result.id);
            }
        }
    }).on('input', function() {
        $("#Return_Lead_NIK").val('');
    });

    $("#Return_Receiver_Name").coolautosuggest({
        url: "hrd-asset-handover-search-employee?chars=",
        showThumbnail: false,
        showDescription: true,
        idField: $("#Return_Receiver_NIK"),
        width: 500,
        minChars: 2,
        onSelected: function(result) {
            if (result != null) {
                $("#Return_Receiver_Name").val(result.data);
                $("#Return_Receiver_NIK").val(result.id);
            }
        }
    }).on('input', function() {
        $("#Return_Receiver_NIK").val('');
    });

    AssetHandoverDetail = new dhtmlXGridObject('gridAssetHandoverDetail');
    AssetHandoverDetail.setImagePath("dhtmlx/imgs/");
    AssetHandoverDetail.setHeader("Pilih,Purchase Type,Product Code,Product Desc,Qty,Product Brand,Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit,Total Price,Received Date,Status,Returned Qty,Returned Condition,Returned Date,Returned Remark,Asset No");
    AssetHandoverDetail.setInitWidths("50,100,100,120,60,100,100,150,100,80,120,100,100,0,0,100,100,100,120,110,150,120");
    AssetHandoverDetail.setColTypes("ch,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,edn,ed,dhxCalendar,txt,ro");
    AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,left,center,center,right,right,center,center,right,left,center,left,center");
    AssetHandoverDetail.setColSorting("int,str,str,str,int,str,str,str,str,str,str,str,str,int,int,str,str,int,str,str,str,str");
    AssetHandoverDetail.setEditable(!isFullyReturned);
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

    function isReturnRowLocked(rId) {
        var st = String(AssetHandoverDetail.cells(rId, 16).getValue() || '').toUpperCase().trim();
        return st === 'RETURNED' || st === 'RETURN_PENDING';
    }

    AssetHandoverDetail.attachEvent("onEditCell", function(stage, rId, cInd) {
        if (isFullyReturned || isReturnRowLocked(rId)) {
            return false;
        }
        return true;
    });

    AssetHandoverDetail.attachEvent("onCheck", function(rId, cInd, state) {
        if (isFullyReturned || isReturnRowLocked(rId)) {
            AssetHandoverDetail.cells(rId, 0).setValue(0);
            return false;
        }
        return true;
    });

    var isSubmittingReturn = false;
    var pendingAfterReturnSync = null;
    var returnSyncError = false;

    PAssetHandoverDetail.attachEvent("onAfterUpdate", function(sid, action) {
        if (action === 'error' || action === 'invalid') {
            returnSyncError = true;
        }
        return true;
    });
    PAssetHandoverDetail.attachEvent("onAfterUpdateFinish", function() {
        if (typeof pendingAfterReturnSync === 'function') {
            var cb = pendingAfterReturnSync;
            pendingAfterReturnSync = null;
            cb(returnSyncError);
        }
    });
    if (window.dhx4 && typeof window.dhx4.attachEvent === 'function') {
        window.dhx4.attachEvent("onLoadXMLError", function() {
            if (isSubmittingReturn && typeof pendingAfterReturnSync === 'function') {
                pendingAfterReturnSync = null;
                isSubmittingReturn = false;
                $("#SubmitReturnAssetHandoverDetail").prop('disabled', false);
                if ($.unblockUI) $.unblockUI();
                alert('Gagal menyimpan detail pengembalian barang. Silakan coba lagi.');
            }
        });
    }

    refreshDataDetail();
    function refreshDataDetail() {
        AssetHandoverDetail.clearAndLoad("hrd-asset-handover-returned-detail-data?Handover_No={{ urlencode($ah->Handover_No) }}", function() {
            var returnableCount = 0;
            var pendingCount = 0;
            AssetHandoverDetail.forEachRow(function(rId) {
                var st = String(AssetHandoverDetail.cells(rId, 16).getValue() || '').toUpperCase().trim();
                if (st === 'RETURNED' || st === 'RETURN_PENDING') {
                    if (st === 'RETURN_PENDING') pendingCount++;
                    AssetHandoverDetail.cells(rId, 0).setValue(0);
                    var chkCell = AssetHandoverDetail.cells(rId, 0);
                    if (chkCell && typeof chkCell.disabledF === 'function') {
                        chkCell.disabledF(true);
                    }
                    AssetHandoverDetail.lockRow(rId, true);
                } else {
                    returnableCount++;
                }
            });
            if (returnableCount === 0) {
                $("#SubmitReturnAssetHandoverDetail").prop('disabled', true);
                if (pendingCount > 0 && !isFullyReturned) {
                    $("#returnLockBanner")
                        .removeClass('d-none alert-info')
                        .addClass('alert-warning')
                        .html('<i class="fa fa-clock me-1"></i> Seluruh item yang tersisa sedang menunggu proses approval pengembalian (<strong>RETURN_PENDING</strong>).');
                } else {
                    $("#returnLockBanner").removeClass('d-none');
                }
            }
        });
    }

    function sendReturnHeader(returnLeadNik, returnLeadName, returnReceiverNik, returnReceiverName) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-asset-handover-returned-submit",
            type: "POST",
            data: {
                Handover_No: "{{ $ah->Handover_No }}",
                Return_Lead_NIK: returnLeadNik,
                Return_Lead_Name: returnLeadName,
                Return_Receiver_NIK: returnReceiverNik,
                Return_Receiver_Name: returnReceiverName,
                Approval_Note: $("#Approval_Note").val() || ''
            },
            success: function(res) {
                if ($.unblockUI) $.unblockUI();
                if (res && res.status === 'success') {
                    alert(res.message || 'Pengajuan pengembalian barang berhasil dikirimkan!');
                    if (typeof refreshData === 'function') refreshData();
                    $('#AssetHandoverDiv').html("");
                } else {
                    isSubmittingReturn = false;
                    $("#SubmitReturnAssetHandoverDetail").prop('disabled', false);
                    alert((res && res.message) ? res.message : 'Gagal mengirim pengajuan pengembalian');
                }
            },
            error: function(xhr) {
                isSubmittingReturn = false;
                $("#SubmitReturnAssetHandoverDetail").prop('disabled', false);
                if ($.unblockUI) $.unblockUI();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengirim pengajuan pengembalian';
                alert(msg);
            }
        });
    }

    function submitReturn() {
        if (isSubmittingReturn || isFullyReturned) return;

        AssetHandoverDetail.editStop();

        var returnLeadNik     = ($("#Return_Lead_NIK").val() || '').trim();
        var returnLeadName    = ($("#Return_Lead_Name").val() || '').trim();
        var returnReceiverNik = ($("#Return_Receiver_NIK").val() || '').trim();
        var returnReceiverName= ($("#Return_Receiver_Name").val() || '').trim();

        if (returnLeadNik === '') {
            alert("PIC Approval Pengembalian (Stage 1) wajib dipilih dari dropdown!");
            $("#Return_Lead_Name").focus();
            return;
        }
        if (returnReceiverNik === '') {
            alert("Penerima Pengembalian Aset (Stage 2) wajib dipilih dari dropdown!");
            $("#Return_Receiver_Name").focus();
            return;
        }

        var checkedRows = [];
        var uncheckedRows = [];
        var validationError = null;
        var rowIds = AssetHandoverDetail.getAllRowIds();
        if (rowIds) {
            var ids = rowIds.split(',');
            for (var i = 0; i < ids.length; i++) {
                var ele = ids[i];
                if (!ele) continue;
                var check = AssetHandoverDetail.cells(ele, 0).getValue();
                if (check == 1 || check == '1' || check === true) {
                    if (isReturnRowLocked(ele)) {
                        validationError = "Item yang sudah RETURN_PENDING / RETURNED tidak dapat diajukan kembali.";
                        break;
                    }
                    var origQty = Number(AssetHandoverDetail.cells(ele, 4).getValue()) || 0;
                    var retQtyRaw = String(AssetHandoverDetail.cells(ele, 17).getValue() || '').trim();
                    var retQty = Number(retQtyRaw);
                    var retCond = String(AssetHandoverDetail.cells(ele, 18).getValue() || '').trim();
                    var retDate = String(AssetHandoverDetail.cells(ele, 19).getValue() || '').trim();

                    if (retQtyRaw === '' || isNaN(retQty) || retQty <= 0) {
                        validationError = "Jumlah pengembalian (Returned Qty) harus berupa angka lebih besar dari 0.";
                        break;
                    }
                    if (origQty > 0 && retQty > origQty) {
                        validationError = "Jumlah pengembalian (Returned Qty) tidak boleh melebihi Qty awal (" + origQty + ").";
                        break;
                    }
                    if (retCond === '') {
                        validationError = "Kondisi pengembalian (Returned Condition) wajib diisi.";
                        break;
                    }
                    if (retDate === '') {
                        validationError = "Tanggal pengembalian (Returned Date) wajib diisi.";
                        break;
                    }
                    checkedRows.push(ele);
                } else {
                    uncheckedRows.push(ele);
                }
            }
        }

        if (validationError) {
            alert(validationError);
            return;
        }

        if (checkedRows.length === 0) {
            alert("Pilih minimal satu item barang yang ingin dikembalikan (centang checkbox)");
            return;
        }

        for (var u = 0; u < uncheckedRows.length; u++) {
            PAssetHandoverDetail.setUpdated(uncheckedRows[u], false);
        }
        for (var c = 0; c < checkedRows.length; c++) {
            PAssetHandoverDetail.setUpdated(checkedRows[c], true, "updated");
        }

        isSubmittingReturn = true;
        $("#SubmitReturnAssetHandoverDetail").prop('disabled', true);
        if ($.blockUI) $.blockUI();

        if (PAssetHandoverDetail.getSyncState()) {
            sendReturnHeader(returnLeadNik, returnLeadName, returnReceiverNik, returnReceiverName);
        } else {
            returnSyncError = false;
            pendingAfterReturnSync = function(hasErr) {
                if (hasErr) {
                    isSubmittingReturn = false;
                    $("#SubmitReturnAssetHandoverDetail").prop('disabled', false);
                    if ($.unblockUI) $.unblockUI();
                    alert('Sebagian detail pengembalian gagal disimpan. Silakan periksa kembali.');
                    return;
                }
                sendReturnHeader(returnLeadNik, returnLeadName, returnReceiverNik, returnReceiverName);
            };
            PAssetHandoverDetail.sendData();
        }
    }
</script>
