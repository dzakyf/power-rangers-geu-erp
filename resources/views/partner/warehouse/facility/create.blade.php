@extends('layout.mainlayout-bp')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="master-facility">Facility</a></li>
        <li class="breadcrumb-item active"><span class="active">Create</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card shadow-sm">
                <div class="card-header p-2">
                    <h5 class="m-0">Create Facility</h5>
                </div>
                <div class="card-body">
                    <form id="FacilityForm">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="type">Hub Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="">-- Select Hub Type --</option>
                                    <option value="mobil">Mobil</option>
                                    <option value="booth">Booth</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category">
                                    <option value="">-- Select Category --</option>
                                    <option value="inventory">Inventory</option>
                                    <option value="access">Access</option>
                                    <option value="support">Support</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control" id="description" name="description" placeholder="Enter description">
                        </div>

                        <div class="form-group">
                            <label>Detail</label>
                            <div id="detailList">
                                <div class="detail-item input-group mb-1">
                                    <input type="text" class="form-control" name="children[]" placeholder="Enter detail">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger remove-detail">−</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="addDetail">+ Add Detail</button>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="asset_type">Asset Type</label>
                                <select class="form-control" id="asset_type" name="asset_type">
                                    <option value="">-- Select Asset Type --</option>
                                    <option value="Asset">Asset</option>
                                    <option value="Bukan Asset">Bukan Asset</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="depreciation_year">Depreciation Year</label>
                                <input type="number" min="1" class="form-control" id="depreciation_year" name="depreciation_year" placeholder="e.g. 10">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="shelf_life">Waktu Pakai (Bulan)</label>
                                <input type="number" min="1" class="form-control" id="shelf_life" name="shelf_life" placeholder="e.g. 60">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="price">Harga Satuan</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="price" name="price" placeholder="e.g. 500000">
                            </div>
                        </div>

                        <hr>
                        <button type="button" class="btn btn-primary" id="SubmitFacility">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#addDetail').on('click', function() {
            $('#detailList').append(`
                <div class="detail-item input-group mb-1">
                    <input type="text" class="form-control" name="children[]" placeholder="Enter detail">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-detail">−</button>
                    </div>
                </div>
            `);
        });

        $(document).on('click', '.remove-detail', function() {
            if ($('.detail-item').length > 1) {
                $(this).closest('.detail-item').remove();
            }
        });

        $('#SubmitFacility').on('click', function() {
            var payload = new FormData(document.getElementById('FacilityForm'));

            $.ajax({
                url: 'master-facility-create-submit',
                type: 'POST',
                data: payload,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Facility berhasil dibuat!');
                        window.location.href = 'master-facility';
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                }
            });
        });
    });
</script>

@endsection