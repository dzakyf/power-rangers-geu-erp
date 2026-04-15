@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">UCO Inbound</span></li>
    </ol>
</div>

<div class="row">
    <div class="col-8">
        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3">UCO Inbound Form</h5>
                </div>
                <div class="card-body">
                    <form id="UcoInboundForm" action="partner-uco-inbound-timbang-submit" method="POST">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="POO_Name">POO Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="POO_Name" name="POO_Name" placeholder="POO Name" required>
                            </div>

                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Weight">Weight <span style="color: red;">*</span></label>
                                <input type="number" class="form-control" id="Weight" name="Weight" placeholder="Weight" step="0.01" min="0" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Unit">Unit <span style="color: red;">*</span></label>
                                <select class="form-control" id="Unit" name="Unit" required>
                                    <option value="">-- Select Unit --</option>
                                    <option value="KG">Kg</option>
                                    <option value="TON">Ton</option>
                                    <option value="LITER">Liter</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Inbound_Date">Inbound Date <span style="color: red;">*</span></label>
                                <input type="date" class="form-control" id="Inbound_Date" name="Inbound_Date" required>
                            </div>
                        </div>



                        <hr>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        $('#Created_At').val(now);
        $('#Updated_At').val(now);

        $('#UcoInboundForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    alert('Data berhasil disimpan.');
                    $('#UcoInboundForm')[0].reset();

                },
                error: function(xhr) {
                    console.error('Failed to submit:', xhr.responseText);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                }
            });
        });
    });
</script>

@endsection