@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="main-content-header">
    <h1>My Profile</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/candidate/dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active"><span>Profile</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <form id="profileForm">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Task ID</label>
                        <input type="text" class="form-control" value="{{ $rec->Task_ID ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" value="{{ $rec->Name ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Position Applied</label>
                        <input type="text" class="form-control" value="{{ $rec->Position ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Gender</label>
                        <input type="text" class="form-control" value="{{ $rec->Gender ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Birth Date</label>
                        <input type="text" class="form-control" value="{{ $rec->Birth_Date ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Birth Place</label>
                        <input type="text" class="form-control" value="{{ $rec->Birth_Place ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>KTP</label>
                        <input type="text" class="form-control" value="{{ $rec->KTP ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Last Education</label>
                        <input type="text" class="form-control" value="{{ $rec->Last_Education ?? '' }} - {{ $rec->Last_Education_From ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Religion</label>
                        <input type="text" class="form-control" value="{{ $rec->Religion ?? '' }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Marital Status</label>
                        <input type="text" class="form-control" value="{{ $rec->Marital_Status ?? '' }}" readonly>
                    </div>
                </div>
            </div>
            <hr>
            <h5>Editable Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="Address" class="form-control">{{ $rec->Address ?? '' }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" id="City" class="form-control" value="{{ $rec->City ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="Phone" class="form-control" value="{{ $rec->Phone ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>WhatsApp</label>
                        <input type="text" id="Handphone" class="form-control" value="{{ $rec->Handphone ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="Email" class="form-control" value="{{ $rec->Email ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="button" id="saveProfile" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
$('#saveProfile').click(function() {
    var data = {
        Address: $('#Address').val(),
        City: $('#City').val(),
        Phone: $('#Phone').val(),
        Handphone: $('#Handphone').val(),
        Email: $('#Email').val(),
        _token: '{{ csrf_token() }}'
    };
    $.ajax({
        url: '{{ url("/candidate/profile-update") }}',
        type: 'POST',
        data: data,
        success: function(res) {
            if (res.indexOf('success') !== -1) {
                alert('Profile updated successfully!');
            } else {
                alert('Error updating profile');
            }
        },
        error: function() {
            alert('Error! Please try again.');
        }
    });
});
</script>
@endsection
