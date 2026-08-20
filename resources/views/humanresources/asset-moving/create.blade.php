<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Asset Moving Transaction</h4>
        </div>
        <div class="card-body">
            <div class="row">

                <!-- Transaction Details -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="No_Trx">No Trx</label>
                        <input type="text" id="No_Trx" class="form-control shadow-sm" name="No_Trx" value="{{ $NoTrx }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="DateTrx">Date Trx</label>
                        <input type="datetime-local" id="DateTrx" class="form-control shadow-sm" name="DateTrx" value="{{ date("Y-m-d") }}" />
                    </div>
                </div>

                <!-- User Information -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="User_NIP">User NIP</label>
                        <input type="text" id="User_NIP" class="form-control shadow-sm" name="User_NIP" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="UserTrx">User Trx</label>
                        <input type="text" id="UserTrx" class="form-control shadow-sm" name="UserTrx" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Position">Position</label>
                        <input type="text" id="Position" class="form-control shadow-sm" name="Position" value="" />
                    </div>
                </div>

                <!-- Asset Information -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Asset_No">Asset No</label>
                        <select name="Asset_No" id="Asset_No" class="form-control shadow-sm" >
                            <option value="">-- Pilih Asset - </option>
                            @foreach ($assets as $item)
                                <option value="{{ $item->Asset_No }}">{{ $item->Asset_No . " - " . $item->Product_Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="SN">Serial Number (SN)</label>
                        <input type="text" id="SN" class="form-control shadow-sm bg-white" name="SN" value="" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Product_Description">Product Description</label>
                        <input type="text" id="Product_Description" class="form-control shadow-sm bg-white" name="Product_Description" value="" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Asset_Description">Asset Description</label>
                        <input type="text" id="Asset_Description" class="form-control shadow-sm" name="Asset_Description" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Last_Stat">Last Stat</label>
                        <input type="text" id="Last_Stat" class="form-control shadow-sm" name="Last_Stat" value="" />
                    </div>
                </div>

                <!-- Moving Details -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Current_Location">Current Location</label>
                        <input type="text" id="Current_Location" class="form-control shadow-sm" name="Current_Location" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="New_Location">New Location</label>
                        <input type="text" id="New_Location" class="form-control shadow-sm" name="New_Location" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Moving_Date">Moving Date</label>
                        <input type="date" id="Moving_Date" class="form-control shadow-sm" name="Moving_Date" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Moving_Reason">Moving Reason</label>
                        <input type="text" id="Moving_Reason" class="form-control shadow-sm" name="Moving_Reason" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Moving_Purpose">Moving Purpose</label>
                        <input type="text" id="Moving_Purpose" class="form-control shadow-sm" name="Moving_Purpose" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Status">Status</label>
                        <input type="text" id="Status" class="form-control shadow-sm" name="Status" value="" />
                    </div>
                </div>

                <!-- Recipient Information -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_Name">Recipient Name</label>
                        <input type="text" id="Receipent_Name" class="form-control shadow-sm" name="Receipent_Name" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_NIK">Recipient NIK</label>
                        <input type="text" id="Receipent_NIK" class="form-control shadow-sm" name="Receipent_NIK" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_Department">Recipient Department</label>
                        <input type="text" id="Receipent_Department" class="form-control shadow-sm" name="Receipent_Department" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Receipent_Position">Recipient Position</label>
                        <input type="text" id="Receipent_Position" class="form-control shadow-sm" name="Receipent_Position" value="" />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 mt-2">
                    <button class="btn btn-success" id="submitBtn">Submit</button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    $('#submitBtn').click(function(e) {
    e.preventDefault();

    $.ajax({
        url: 'hrd-asset-moving-submit', // Update with your actual endpoint URL
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
            console.log(data);
            if (data.status === 'success') {
                alert('Data berhasil disimpan');
                setTimeout(function() {
                    if (typeof reloadgroup === 'function') {
                        reloadgroup();
                    }
                    $('#WorkCalendarDiv').html('');
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