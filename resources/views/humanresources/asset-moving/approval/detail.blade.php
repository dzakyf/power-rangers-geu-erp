<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Asset Moving Form</h4>
        </div>
        <div class="card-body">
            <div class="row">

                <!-- Transaction Details -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="No_Trx">No Trx</label>
                        <input type="hidden" id="ID" class="form-control" name="ID" value="{{ $assetMoving->ID ?? '' }}" />
                        <input type="text" id="No_Trx" class="form-control" name="No_Trx" value="{{ $assetMoving->No_Trx ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="DateTrx">Date Trx</label>
                        <input type="datetime-local" id="DateTrx" class="form-control" name="DateTrx" value="{{ isset($assetMoving->DateTrx) ? \Carbon\Carbon::parse($assetMoving->DateTrx)->format('Y-m-d\TH:i') : '' }}" />
                    </div>
                </div>

                <!-- User Information -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="User_NIP">User NIP</label>
                        <input type="text" id="User_NIP" class="form-control" name="User_NIP" value="{{ $assetMoving->User_NIP ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="UserTrx">User Trx</label>
                        <input type="text" id="UserTrx" class="form-control" name="UserTrx" value="{{ $assetMoving->UserTrx ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Position">Position</label>
                        <input type="text" id="Position" class="form-control" name="Position" value="{{ $assetMoving->Position ?? '' }}" />
                    </div>
                </div>

                <!-- Asset Information -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Asset_No">Asset No</label>
                        <input type="text" id="Asset_No" class="form-control" name="Asset_No" value="{{ $assetMoving->Asset_No ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="SN">Serial Number (SN)</label>
                        <input type="text" id="SN" class="form-control" name="SN" value="{{ $assetMoving->SN ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Product_Description">Product Description</label>
                        <input type="text" id="Product_Description" class="form-control" name="Product_Description" value="{{ $assetMoving->Product_Description ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Asset_Description">Asset Description</label>
                        <input type="text" id="Asset_Description" class="form-control" name="Asset_Description" value="{{ $assetMoving->Asset_Description ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Last_Stat">Last Stat</label>
                        <input type="text" id="Last_Stat" class="form-control" name="Last_Stat" value="{{ $assetMoving->Last_Stat ?? '' }}" />
                    </div>
                </div>

                <!-- Movement Details -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Current_Location">Current Location</label>
                        <input type="text" id="Current_Location" class="form-control" name="Current_Location" value="{{ $assetMoving->Current_Location ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="New_Location">New Location</label>
                        <input type="text" id="New_Location" class="form-control" name="New_Location" value="{{ $assetMoving->New_Location ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Moving_Date">Moving Date</label>
                        <input type="date" id="Moving_Date" class="form-control" name="Moving_Date" value="{{ isset($assetMoving->Moving_Date) ? \Carbon\Carbon::parse($assetMoving->Moving_Date)->format('Y-m-d') : '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Moving_Reason">Moving Reason</label>
                        <input type="text" id="Moving_Reason" class="form-control" name="Moving_Reason" value="{{ $assetMoving->Moving_Reason ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Moving_Purpose">Moving Purpose</label>
                        <input type="text" id="Moving_Purpose" class="form-control" name="Moving_Purpose" value="{{ $assetMoving->Moving_Purpose ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Status">Status</label>
                        <input type="text" id="Status" class="form-control" name="Status" value="{{ $assetMoving->Status ?? '' }}" />
                    </div>
                </div>

                <!-- Recipient Information -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_Name">Recipient Name</label>
                        <input type="text" id="Receipent_Name" class="form-control" name="Receipent_Name" value="{{ $assetMoving->Receipent_Name ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_NIK">Recipient NIK</label>
                        <input type="text" id="Receipent_NIK" class="form-control" name="Receipent_NIK" value="{{ $assetMoving->Receipent_NIK ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_Department">Recipient Department</label>
                        <input type="text" id="Receipent_Department" class="form-control" name="Receipent_Department" value="{{ $assetMoving->Receipent_Department ?? '' }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_Position">Recipient Position</label>
                        <input type="text" id="Receipent_Position" class="form-control" name="Receipent_Position" value="{{ $assetMoving->Receipent_Position ?? '' }}" />
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label for="Receipent_Position">Approval Note</label>
                        <textarea name="Approval_Note" id="Approval_Note" class="form-control" rows="5"></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 mt-2">
                    <button class="btn btn-success" id="approveBtn" onclick="Approval(this,1)">Approve</button>
                    <button class="btn btn-danger" id="rejectBtn" onclick="Approval(this,2)">Reject</button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>

    function Approval(e,stat) {
        e.preventDefault();

        $.ajax({
            url: 'hrd-asset-moving-approval-approval', // ganti dengan endpoint route Anda
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: {
                Data: $('#ID').val(),
                Approval: stat,
                Note: $('#Approval_Note').val();
            },
            success: function(data) {
                console.log(data);
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