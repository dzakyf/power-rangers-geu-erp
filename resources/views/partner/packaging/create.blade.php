@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="master-packaging">Kemasan</a></li>
        <li class="breadcrumb-item active"><span class="active">Create</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3 d-inline-block">Create Kemasan</h5>
                    <button class="btn btn-warning float-right m-3 px-3 py-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
                </div>
                <div class="card-body">
                    <form id="PackagingForm">
                        @csrf
                        <div class="form-group">
                            <label for="Name">Name <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="Name" name="Name" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Capacity">Capacity</label>
                                <input type="number" step="0.01" class="form-control" id="Capacity" name="Capacity">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Unit">Unit</label>
                                <input type="text" class="form-control" id="Unit" name="Unit" value="L">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="Description">Description</label>
                            <textarea class="form-control" id="Description" name="Description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="Status">Status</label>
                            <select class="form-control" id="Status" name="Status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" id="SubmitPackaging">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#SubmitPackaging').on('click', function() {
        var form = document.getElementById('PackagingForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        $.ajax({
            url: 'master-packaging-submit',
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil disimpan!');
                    window.location.href = 'master-packaging';
                } else {
                    alert('Gagal: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    });
</script>
@endsection
