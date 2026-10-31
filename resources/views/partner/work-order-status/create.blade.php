@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="master-work-order-status">Work Order Status</a></li>
        <li class="breadcrumb-item active"><span class="active">Create</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3">Create Work Order Status</h5>
                </div>
                <div class="card-body">
                    <form id="StatusForm">
                        @csrf
                        <div class="form-group">
                            <label for="Code">Code <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="Code" name="Code" required>
                        </div>
                        <div class="form-group">
                            <label for="Name">Name <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="Name" name="Name" required>
                        </div>
                        <div class="form-group">
                            <label for="Description">Description</label>
                            <textarea class="form-control" id="Description" name="Description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="Is_Active">Is Active</label>
                            <select class="form-control" id="Is_Active" name="Is_Active">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" id="SubmitStatus">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#SubmitStatus').on('click', function() {
        var form = document.getElementById('StatusForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        $.ajax({
            url: 'master-work-order-status-submit',
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil disimpan!');
                    window.location.href = 'master-work-order-status';
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
