<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-clipboard-check me-2"></i>Asset Moving Approval</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#DATAASSETSDTL').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <input type="hidden" id="ID" name="ID" value="{{ $assetMoving->ID ?? '' }}" />

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Transaksi &amp; Pemohon</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="No_Trx" class="form-label small fw-semibold">No Trx</label>
                    <input type="text" id="No_Trx" class="form-control form-control-sm bg-light" name="No_Trx" value="{{ $assetMoving->No_Trx ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="DateTrx" class="form-label small fw-semibold">Date Trx</label>
                    <input type="datetime-local" id="DateTrx" class="form-control form-control-sm bg-light" name="DateTrx" value="{{ isset($assetMoving->DateTrx) ? \Carbon\Carbon::parse($assetMoving->DateTrx)->format('Y-m-d\TH:i') : '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="Status" class="form-label small fw-semibold">Status</label>
                    <input type="text" id="Status" class="form-control form-control-sm bg-light fw-bold" name="Status" value="{{ $assetMoving->Status ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="User_NIP" class="form-label small fw-semibold">User NIP</label>
                    <input type="text" id="User_NIP" class="form-control form-control-sm bg-light" name="User_NIP" value="{{ $assetMoving->User_NIP ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="UserTrx" class="form-label small fw-semibold">User Trx</label>
                    <input type="text" id="UserTrx" class="form-control form-control-sm bg-light" name="UserTrx" value="{{ $assetMoving->UserTrx ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="Position" class="form-label small fw-semibold">Position</label>
                    <input type="text" id="Position" class="form-control form-control-sm bg-light" name="Position" value="{{ $assetMoving->Position ?? '' }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Aset</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="Asset_No" class="form-label small fw-semibold">Asset No</label>
                    <input type="text" id="Asset_No" class="form-control form-control-sm bg-light" name="Asset_No" value="{{ $assetMoving->Asset_No ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="SN" class="form-label small fw-semibold">Serial Number (SN)</label>
                    <input type="text" id="SN" class="form-control form-control-sm bg-light" name="SN" value="{{ $assetMoving->SN ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="Last_Stat" class="form-label small fw-semibold">Last Stat</label>
                    <input type="text" id="Last_Stat" class="form-control form-control-sm bg-light" name="Last_Stat" value="{{ $assetMoving->Last_Stat ?? '' }}" readonly />
                </div>
                <div class="col-md-6">
                    <label for="Product_Description" class="form-label small fw-semibold">Product Description</label>
                    <input type="text" id="Product_Description" class="form-control form-control-sm bg-light" name="Product_Description" value="{{ $assetMoving->Product_Description ?? '' }}" readonly />
                </div>
                <div class="col-md-6">
                    <label for="Asset_Description" class="form-label small fw-semibold">Asset Description</label>
                    <input type="text" id="Asset_Description" class="form-control form-control-sm bg-light" name="Asset_Description" value="{{ $assetMoving->Asset_Description ?? '' }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Pemindahan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="Current_Location" class="form-label small fw-semibold">Current Location</label>
                    <input type="text" id="Current_Location" class="form-control form-control-sm bg-light" name="Current_Location" value="{{ $assetMoving->Current_Location ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="New_Location" class="form-label small fw-semibold">New Location</label>
                    <input type="text" id="New_Location" class="form-control form-control-sm bg-light" name="New_Location" value="{{ $assetMoving->New_Location ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label for="Moving_Date" class="form-label small fw-semibold">Moving Date</label>
                    <input type="date" id="Moving_Date" class="form-control form-control-sm bg-light" name="Moving_Date" value="{{ isset($assetMoving->Moving_Date) ? \Carbon\Carbon::parse($assetMoving->Moving_Date)->format('Y-m-d') : '' }}" readonly />
                </div>
                <div class="col-md-6">
                    <label for="Moving_Reason" class="form-label small fw-semibold">Moving Reason</label>
                    <input type="text" id="Moving_Reason" class="form-control form-control-sm bg-light" name="Moving_Reason" value="{{ $assetMoving->Moving_Reason ?? '' }}" readonly />
                </div>
                <div class="col-md-6">
                    <label for="Moving_Purpose" class="form-label small fw-semibold">Moving Purpose</label>
                    <input type="text" id="Moving_Purpose" class="form-control form-control-sm bg-light" name="Moving_Purpose" value="{{ $assetMoving->Moving_Purpose ?? '' }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Informasi Penerima</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="Receipent_Name" class="form-label small fw-semibold">Recipient Name</label>
                    <input type="text" id="Receipent_Name" class="form-control form-control-sm bg-light" name="Receipent_Name" value="{{ $assetMoving->Receipent_Name ?? '' }}" readonly />
                </div>
                <div class="col-md-3">
                    <label for="Receipent_NIK" class="form-label small fw-semibold">Recipient NIK</label>
                    <input type="text" id="Receipent_NIK" class="form-control form-control-sm bg-light" name="Receipent_NIK" value="{{ $assetMoving->Receipent_NIK ?? '' }}" readonly />
                </div>
                <div class="col-md-3">
                    <label for="Receipent_Department" class="form-label small fw-semibold">Recipient Department</label>
                    <input type="text" id="Receipent_Department" class="form-control form-control-sm bg-light" name="Receipent_Department" value="{{ $assetMoving->Receipent_Department ?? '' }}" readonly />
                </div>
                <div class="col-md-3">
                    <label for="Receipent_Position" class="form-label small fw-semibold">Recipient Position</label>
                    <input type="text" id="Receipent_Position" class="form-control form-control-sm bg-light" name="Receipent_Position" value="{{ $assetMoving->Receipent_Position ?? '' }}" readonly />
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label for="Approval_Note" class="form-label small fw-semibold">Approval Note</label>
                    <textarea name="Approval_Note" id="Approval_Note" class="form-control form-control-sm" rows="3" placeholder="Tambahkan catatan approval (opsional)..."></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-danger btn-sm" id="rejectBtn" onclick="Approval(event, 2)">
                    <i class="fa fa-times me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-success btn-sm" id="approveBtn" onclick="Approval(event, 1)">
                    <i class="fa fa-check me-1"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function Approval(e, stat) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();

        $.ajax({
            url: 'hrd-asset-moving-approval-approval',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: {
                Data: $('#ID').val(),
                Approval: stat,
                Note: $('#Approval_Note').val()
            },
            success: function(data) {
                if (data.status === 'success') {
                    alert('Data berhasil disimpan');
                    setTimeout(function() {
                        if (typeof refreshdata === 'function') {
                            refreshdata();
                        }
                        $('#DATAASSETSDTL').html('');
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
    }
</script>
