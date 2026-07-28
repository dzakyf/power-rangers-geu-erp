@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Profile</span>
        </li>
    </ol>
</div>

<div class="row">
    <div class="container py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="m-3">User Data Form</h5>
            </div>
            <div class="card-body">
                <form id="UpdateProfileForm" action="partner-profile-update" method="POST">
                    @csrf
                    <input type="hidden" class="form-control" id="User_Id" name="User_Id" placeholder="Username">

                    <h6 class="text-muted font-weight-bold mb-3">Account Information</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="Emp_ID">Employee ID</label>
                            <input type="text" class="form-control" id="Emp_ID" name="Emp_ID" placeholder="Employee ID" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="Priv_ID">Privilege / Role</label>
                            <select class="form-control" id="Priv_ID" name="Priv_ID">
                                <option value="">-- Select Role --</option>
                                <option value="ADMIN">Admin</option>
                                <option value="SALES">Sales</option>
                                <option value="MITRA">Mitra</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="Status">Status</label>
                            <select class="form-control" id="Status" name="Status">
                                <option value="">-- Select Status --</option>
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="created_at">Created At</label>
                            <input type="date" class="form-control" id="created_at" name="created_at" readonly>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted font-weight-bold mb-3">Personal Information</h6>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="Name">Full Name</label>
                            <input type="text" class="form-control" id="Name" name="Name" placeholder="Full Name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="Contact">Contact / Phone</label>
                            <input type="text" class="form-control" id="Contact" name="Contact" placeholder="Contact Number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="Address">Address</label>
                            <input type="text" class="form-control" id="Address" name="Address" placeholder="Full Address">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="City">City</label>
                            <input type="text" class="form-control" id="City" name="City" placeholder="City">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="Location">Location</label>
                            <input type="text" class="form-control" id="Location" name="Location" placeholder="Location">
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted font-weight-bold mb-3">Employment Information</h6>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="Position">Position</label>
                            <input type="text" class="form-control" id="Position" name="Position" placeholder="Position" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="Department">Department</label>
                            <input type="text" class="form-control" id="Department" name="Department" placeholder="Department" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="Area">Area</label>
                            <input type="text" class="form-control" id="Area" name="Area" placeholder="Area" readonly>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted font-weight-bold mb-3">Bank Information</h6>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="Bank_Name">Bank Name</label>
                            <select class="form-control select2" id="Bank_Name" name="Bank_Name" style="width:100%">
                                <option value="">-- Pilih Bank --</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="Bank_Account_No">Account Number</label>
                            <input type="text" class="form-control" id="Bank_Account_No" name="Bank_Account_No" placeholder="Account Number">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="Bank_Account_Name">Account Name</label>
                            <input type="text" class="form-control" id="Bank_Account_Name" name="Bank_Account_Name" placeholder="Account Holder Name">
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="reset" class="btn btn-secondary ml-2">Reset</button>

                </form>
            </div>
        </div>
    </div>
</div>

<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#Bank_Name').select2({
            placeholder: '-- Pilih Bank --',
            allowClear: true,
            width: '100%'
        });
        $.ajax({
            url: 'partner-profile-data',
            method: 'GET',
            success: function(response) {
                if (response.status !== 'success') return;

                const data = response.data;

                // Account Information
                $('#User_Id').val(data.id);
                $('#username').val(data.username);
                $('#Emp_ID').val(data.Emp_ID);
                $('#Priv_ID').val(data.Priv_ID);
                $('#Status').val(data.Status);
                $('#created_at').val(data.created_at ? data.created_at.split(' ')[0] : '');

                // Personal Information
                $('#Name').val(data.Name);
                $('#Contact').val(data.Contact);
                $('#email').val(data.email);
                $('#Address').val(data.Address);
                $('#City').val(data.City);
                $('#Location').val(data.Location);

                // Employment Information
                $('#Position').val(data.Position);
                $('#Department').val(data.Department);
                $('#Area').val(data.Area);

                // Bank Information
                // Load banks then set selected value
                $.get('master-bank-data', function(res) {
                    if (res.status === 'success') {
                        var bankSel = $('#Bank_Name');
                        bankSel.empty().append('<option value="">-- Pilih Bank --</option>');
                        res.data.forEach(function(item) {
                            bankSel.append($('<option>', { value: item.text, text: item.text }));
                        });
                        bankSel.val(data.Bank_Name);
                    }
                });
                $('#Bank_Account_No').val(data.Bank_Account_No);
                $('#Bank_Account_Name').val(data.Bank_Account_Name);
            },
            error: function(xhr) {
                console.error('Failed to fetch profile data:', xhr.responseText);
            }
        });

        $('UpdateProfileForm').on('submit', function(e) {
            e.preventDefault();

            let fd = new FormData(this)

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Profile updated successfully');
                    } else {
                        alert('Update failed: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to update profile:', xhr.responseText);
                    alert('An error occurred while updating profile');
                }
            });
        });
    });
</script>

@endsection