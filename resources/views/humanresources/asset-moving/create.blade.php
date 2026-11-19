<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-exchange-alt me-2"></i>Asset Moving Transaction</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#DATAASSETSDTL1').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Transaksi &amp; Pemohon</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="No_Trx" class="form-label small fw-semibold">No Trx</label>
                    <input type="text" id="No_Trx" class="form-control form-control-sm bg-light" name="No_Trx" value="{{ $NoTrx }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="User_NIP" class="form-label small fw-semibold">User NIP</label>
                    <input type="text" id="User_NIP" class="form-control form-control-sm" name="User_NIP" value="{{ $NIK }}" />
                </div>
                <div class="col-md-4">
                    <label for="Position" class="form-label small fw-semibold">Position</label>
                    <input type="text" id="Position" class="form-control form-control-sm" name="Position" value="{{ $Position }}" />
                </div>
                <div class="col-md-6">
                    <label for="Vendor_Origin" class="form-label small fw-semibold">Vendor Afiliasi Asal</label>
                    <select name="Vendor_Origin" id="Vendor_Origin" class="form-select form-select-sm">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach ($Supplier as $item)
                            <option value="{{ $item->Supplier_ID }}">{{ $item->Supplier_ID . " - " . $item->Company_Name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Aset</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="Asset_No" class="form-label small fw-semibold">Asset No</label>
                    <select name="Asset_No" id="Asset_No" class="form-select form-select-sm">
                        <option value="">-- Pilih Asset --</option>
                        @foreach ($assets as $item)
                            <option value="{{ $item->Asset_No }}">{{ $item->Asset_No . " - " . $item->Product_Description }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="SN" class="form-label small fw-semibold">Serial Number (SN)</label>
                    <input type="text" id="SN" class="form-control form-control-sm bg-light" name="SN" value="" readonly />
                </div>
                <div class="col-md-3">
                    <label for="Last_Stat" class="form-label small fw-semibold">Last Stat</label>
                    <input type="text" id="Last_Stat" class="form-control form-control-sm" name="Last_Stat" value="" />
                </div>
                <div class="col-md-6">
                    <label for="Product_Description" class="form-label small fw-semibold">Product Description</label>
                    <input type="text" id="Product_Description" class="form-control form-control-sm bg-light" name="Product_Description" value="" readonly />
                </div>
                <div class="col-md-6">
                    <label for="Asset_Description" class="form-label small fw-semibold">Asset Description</label>
                    <input type="text" id="Asset_Description" class="form-control form-control-sm" name="Asset_Description" value="" />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Pemindahan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="Current_Location" class="form-label small fw-semibold">Current Location</label>
                    <input type="text" id="Current_Location" class="form-control form-control-sm" name="Current_Location" value="" />
                </div>
                <div class="col-md-4">
                    <label for="New_Location" class="form-label small fw-semibold">New Location</label>
                    <input type="text" id="New_Location" class="form-control form-control-sm" name="New_Location" value="" />
                </div>
                <div class="col-md-4">
                    <label for="Moving_Date" class="form-label small fw-semibold">Moving Date</label>
                    <input type="date" id="Moving_Date" class="form-control form-control-sm" name="Moving_Date" value="" />
                </div>
                <div class="col-md-4">
                    <label for="Moving_Reason" class="form-label small fw-semibold">Moving Reason</label>
                    <input type="text" id="Moving_Reason" class="form-control form-control-sm" name="Moving_Reason" value="" />
                </div>
                <div class="col-md-4">
                    <label for="Moving_Purpose" class="form-label small fw-semibold">Moving Purpose</label>
                    <input type="text" id="Moving_Purpose" class="form-control form-control-sm" name="Moving_Purpose" value="" />
                </div>
                <div class="col-md-4">
                    <label for="Status" class="form-label small fw-semibold">Status</label>
                    <input type="text" id="Status" class="form-control form-control-sm" name="Status" value="" />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Informasi Penerima</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="Vendor_End" class="form-label small fw-semibold">Vendor Afiliasi Tujuan</label>
                    <select name="Vendor_End" id="Vendor_End" class="form-select form-select-sm">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach ($Supplier as $item)
                            <option value="{{ $item->Supplier_ID }}">{{ $item->Supplier_ID . " - " . $item->Company_Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="Receipent_Name" class="form-label small fw-semibold">Recipient Name</label>
                    <input type="text" id="Receipent_Name" class="form-control form-control-sm" name="Receipent_Name" value="" />
                </div>
                <div class="col-md-4">
                    <label for="Receipent_NIK" class="form-label small fw-semibold">Recipient NIK</label>
                    <input type="text" id="Receipent_NIK" class="form-control form-control-sm" name="Receipent_NIK" value="" />
                </div>
                <div class="col-md-4">
                    <label for="Receipent_Department" class="form-label small fw-semibold">Recipient Department</label>
                    <input type="text" id="Receipent_Department" class="form-control form-control-sm" name="Receipent_Department" value="" />
                </div>
                <div class="col-md-4">
                    <label for="Receipent_Position" class="form-label small fw-semibold">Recipient Position</label>
                    <input type="text" id="Receipent_Position" class="form-control form-control-sm" name="Receipent_Position" value="" />
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm" onclick="$('#DATAASSETSDTL1').html('')">
                    <i class="fa fa-times me-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-success btn-sm" id="submitBtn">
                    <i class="fa fa-check me-1"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$('#submitBtn').click(function(e) {
    e.preventDefault();

    $.ajax({
        url: 'hrd-asset-moving-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: {
            No_Trx: $('#No_Trx').val(),
            Position: $('#Position').val(),
            DateTrx: $('#DateTrx').val(),
            User_NIP: $('#User_NIP').val(),
            UserTrx: $('#UserTrx').val(),
            Asset_No: $('#Asset_No').val(),
            Product_Description: $('#Product_Description').val(),
            SN: $('#SN').val(),
            Asset_Description: $('#Asset_Description').val(),
            Last_Stat: $('#Last_Stat').val(),
            Current_Location: $('#Current_Location').val(),
            Moving_Reason: $('#Moving_Reason').val(),
            Moving_Purpose: $('#Moving_Purpose').val(),
            New_Location: $('#New_Location').val(),
            Moving_Date: $('#Moving_Date').val(),
            Receipent_Name: $('#Receipent_Name').val(),
            Receipent_NIK: $('#Receipent_NIK').val(),
            Receipent_Department: $('#Receipent_Department').val(),
            Receipent_Position: $('#Receipent_Position').val(),
            Status: $('#Status').val()
        },
        success: function(data) {
            if (data.status === 'success') {
                alert('Data berhasil disimpan');
                setTimeout(function() {
                    if (typeof AssetMovingRefresh === 'function') {
                        AssetMovingRefresh();
                    }
                    $('#DATAASSETSDTL1').html('');
                }, 500);
            } else {
                alert('Terjadi kesalahan saat menyimpan data');
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            alert('Terjadi kesalahan server saat menyimpan data');
        }
    });
});
</script>
