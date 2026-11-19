<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-trash-alt me-2"></i>Form Pemusnahan Aset (Asset Disposal)</h6>
            <button type="button" id="CloseAssetDisposalDetail" class="btn btn-light btn-sm" onclick="$('#AssetDisposalDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <h6 class="fw-bold text-primary mb-3">1. Identitas Pemohon</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nomor Formulir</label>
                        <input type="text" id="Disposal_No" class="form-control form-control-sm" readonly value="{{ $Disposal_No }}" />
                        <input type="hidden" id="User_NIK" value="{{ $NIK }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tanggal Pengajuan</label>
                        <input type="text" id="Disposal_Date" class="form-control form-control-sm" readonly value="{{ date('Y-m-d') }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nama Pemohon</label>
                        <input type="text" id="User_Name" class="form-control form-control-sm" readonly value="{{ $Name }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" id="User_Position" class="form-control form-control-sm" readonly value="{{ $Position }}" />
                    </div>
                </div>

                <h6 class="fw-bold text-primary mb-3">2. Informasi Aset / Equipment</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Pilih dari Master Asset List</label>
                        <select id="Asset_No" class="form-select form-select-sm" onchange="onDisposalAssetSelect();">
                            <option value="">-- Pilih Asset No (opsional / ketik manual) --</option>
                            @foreach(($assets ?? []) as $a)
                                <option value="{{ $a->Asset_No }}"
                                    data-name="{{ $a->Product_Description }}"
                                    data-sn="{{ $a->SN }}"
                                    data-desc="{{ trim(($a->Product_Brand ?? '') . ' ' . ($a->Model ?? '')) }}"
                                    data-cond="{{ $a->Kondisi_Aset ?? 'BAIK' }}"
                                    data-loc="{{ $a->Asset_Location ?? '-' }}"
                                    data-val="{{ $a->Buy_Val ?? 0 }}">
                                    {{ $a->Asset_No }} — {{ $a->Product_Description }} ({{ $a->Asset_Location ?: '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Aset / Equipment <span class="text-danger">*</span></label>
                        <input type="text" id="Asset_Name" class="form-control form-control-sm" required />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nomor Seri (SN)</label>
                        <input type="text" id="Asset_Serial_No" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Kondisi Aset</label>
                        <input type="text" id="Asset_Condition" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Lokasi Saat Ini</label>
                        <input type="text" id="Asset_Location" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nilai Buku (Rp)</label>
                        <input type="number" id="Book_Value" class="form-control form-control-sm" value="0" min="0" />
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Deskripsi Aset / Equipment</label>
                        <textarea id="Asset_Description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary mb-3">3. Detail Pemusnahan</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Alasan Pemusnahan <span class="text-danger">*</span></label>
                        <textarea id="Disposal_Reason" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Rencana Tanggal Pemusnahan</label>
                        <input type="text" id="Disposal_Actual_Date" class="form-control form-control-sm" placeholder="YYYY-MM-DD" />
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" id="SubmitAssetDisposalDetail" class="btn btn-success btn-sm">
                        <i class="fa fa-check me-1"></i> Submit Pengajuan Pemusnahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function onDisposalAssetSelect() {
    var opt = $('#Asset_No option:selected');
    if (!opt.val()) return;
    $('#Asset_Name').val(opt.data('name') || opt.val());
    $('#Asset_Serial_No').val(opt.data('sn') || opt.val());
    $('#Asset_Description').val(opt.data('desc') || '');
    $('#Asset_Condition').val(opt.data('cond') || 'RUSAK BERAT');
    $('#Asset_Location').val(opt.data('loc') || '-');
    $('#Book_Value').val(opt.data('val') || 0);
}

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
    var DisposalCal = new dhtmlXCalendarObject(["Disposal_Actual_Date"]);
    DisposalCal.setDateFormat("%Y-%m-%d");
}

$("#SubmitAssetDisposalDetail").click(function() {
    if ($("#Asset_Name").val() === '') {
        alert("Kolom Nama Aset/equipment Harus Diisi!");
        $("#Asset_Name").focus();
        return false;
    }
    if ($("#Disposal_Reason").val() === '') {
        alert("Kolom Alasan Pemusnahan Harus Diisi!");
        $("#Disposal_Reason").focus();
        return false;
    }

    if ($.blockUI) $.blockUI();
    $.ajax({
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        url: "hrd-asset-dispossal-new-submit?_token={{ csrf_token() }}",
        type: "POST",
        data: getAllValues(),
        success: function(data) {
            if ($.unblockUI) $.unblockUI();
            if (data.status == 'success') {
                alert('Form Pemusnahan Aset berhasil diajukan dengan No: {{ $Disposal_No }}!');
                if (typeof refreshData === 'function') refreshData();
                $('#AssetDisposalDiv').html("");
            } else {
                alert(data.message || 'Terjadi kesalahan saat menyimpan data');
            }
        },
        error: function() {
            if ($.unblockUI) $.unblockUI();
            alert("Gagal mengirim pengajuan pemusnahan.");
        }
    });
});
</script>
<div id="PRSupplierDet"></div>
