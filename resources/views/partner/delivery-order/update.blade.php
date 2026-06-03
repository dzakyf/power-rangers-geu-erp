@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Delivery Order</span></li>
    </ol>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3">Update Receipt</h5>
                </div>
                <div class="card-body">
                    <form id="UpdateReceiptForm">
                        @csrf
                        <input type="hidden" id="PO_Id" name="PO_Id">

                        <div class="form-group">
                            <label for="TotalBerat">Total Berat Aktual</label>
                            <input type="number" class="form-control" id="Actual_Qty" name="Actual_Qty" min="0" step="any">
                        </div>

                        <div class="form-group">
                            <label>GPS Location</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="Lat" name="Lat" placeholder="Latitude" readonly>
                                <input type="text" class="form-control" id="Lng" name="Lng" placeholder="Longitude" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btnGetGPS">Get GPS</button>
                                </div>
                            </div>
                            <small id="gpsStatus" class="text-muted"></small>
                        </div>

                        <div class="form-group">
                            <label for="Receipt_Photo">Photo Document</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="Receipt_Photo" name="Receipt_Photo" accept="image/*">
                                <label class="custom-file-label" for="Receipt_Photo">Choose file...</label>
                            </div>
                            <div id="photoPreview" class="mt-2" style="display:none;">
                                <img id="previewImg" src="" alt="Preview" style="max-width:100%;max-height:300px;border:1px solid #ddd;border-radius:4px;">
                            </div>
                        </div>

                        <div class="form-group">
                            @include('ui-components.signature-canvas-field', ['name' => 'Signature', 'label' => 'Signature'])
                        </div>

                        <button type="button" class="btn btn-primary mt-3" id="SubmitReceipt">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var poId = urlParams.get('po_id');
        if (poId) {
            $('#PO_Id').val(poId);
        }

        $('#btnGetGPS').on('click', function() {
            if (!navigator.geolocation) {
                $('#gpsStatus').text('Geolocation not supported by this browser.').css('color', 'red');
                return;
            }
            $('#gpsStatus').text('Getting location...').css('color', 'blue');
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    $('#Lat').val(position.coords.latitude);
                    $('#Lng').val(position.coords.longitude);
                    $('#gpsStatus').text('Location acquired.').css('color', 'green');
                },
                function(error) {
                    $('#gpsStatus').text('Error: ' + error.message).css('color', 'red');
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });

        $('#Receipt_Photo').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#photoPreview').show();
                };
                reader.readAsDataURL(file);
                $(this).next('.custom-file-label').text(file.name);
            } else {
                $('#photoPreview').hide();
                $(this).next('.custom-file-label').text('Choose file...');
            }
        });

        $('#SubmitReceipt').on('click', function() {
            var form = document.getElementById('UpdateReceiptForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var payload = new FormData(form);

            $.ajax({
                url: 'partner-delivery-order-update-submit',
                type: 'POST',
                data: payload,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Data berhasil disimpan!');
                        form.reset();
                        $('#photoPreview').hide();
                        $('#Receipt_Photo').next('.custom-file-label').text('Choose file...');
                        $('#Lat').val('');
                        $('#Lng').val('');
                        $('#gpsStatus').text('');
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
