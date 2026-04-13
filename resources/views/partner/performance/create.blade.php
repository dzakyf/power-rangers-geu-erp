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
            <span class="active">Legal Compliance</span>
        </li>
    </ol>
</div>
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Form Buat KPI</h5>
    <form id="kpiForm" action="partner-performance-kpi-submit" enctype="multipart/form-data">
        @csrf

        {{-- Work_Area --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Work_Area">Work Area <span style="color: red;">*</span></label>
                    <input type="text" name="Work_Area" id="Work_Area" class="form-control" required />
                </div>
            </div>
        </div>

        {{-- Indicator --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Indicator">Indicator <span style="color: red;">*</span></label>
                    <textarea name="Indicator" id="Indicator" class="form-control" rows="3" required></textarea>
                </div>
            </div>
        </div>

        {{-- Weight --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Weight">Weight (%) <span style="color: red;">*</span></label>
                    <input type="number" name="Weight" id="Weight" class="form-control" step="0.01" min="0" max="100" required />
                </div>
            </div>
        </div>

        {{-- Target --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Target">Target <span style="color: red;">*</span></label>
                    <textarea name="Target" id="Target" class="form-control" rows="3" required></textarea>
                </div>
            </div>
        </div>

        {{-- Departement --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Departement">Departement <span style="color: red;">*</span></label>
                    <input type="text" name="Departement" id="Departement" class="form-control" required />
                </div>
            </div>
        </div>

        {{-- Periode --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Periode">Periode <span style="color: red;">*</span></label>
                    <input type="date" name="Periode" id="Periode" class="form-control" required />
                </div>
            </div>
        </div>

        {{-- Created_At & Updated_At are handled server-side, hidden fields optional --}}
        <input type="hidden" name="Created_At" id="Created_At" value="{{ now() }}" />
        <input type="hidden" name="Updated_At" id="Updated_At" value="{{ now() }}" />

        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#kpiForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    alert("Berhasil membuat KPI");

                    $('#kpiForm')[0].reset();

                },
                error: function(xhr, status, error) {
                    console.log('Error details:', xhr.responseText);

                    // Handle validation errors
                    // if (xhr.status === 422) {
                    //     let errors = xhr.responseJSON.errors;
                    //     let errorMessage = "Validation errors:\n";
                    //     $.each(errors, function(field, messages) {
                    //         errorMessage += field + ": " + messages[0] + "\n";
                    //     });
                    //     alert(errorMessage);
                    // } else {
                    //     alert("Telah terjadi kesalahan. Silakan coba lagi.");
                    // }
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                }
            });
        });
    });
</script>
@endsection