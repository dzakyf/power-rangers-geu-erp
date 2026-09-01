<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

?>

@csrf
<!-- Main Content Header -->
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Detail Panduan</h5>
    <form id="broadcastForm" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <label for="Title">Judul <span style="color: red;">*</span></label>
                <input type="text" name="Title" id="Title" value="{{ $data[0]->Title}}" class="form-control" required />
                <!-- <select name="Title" id="Title" class="form-control" required>
                    <option value="">-- Pilih Jenis--</option>
                    <option value="1">Akses CCTV</option>
                    <option value="2">ERP</option>
                    <option value="3">Pengajuan Bagi Hasil</option>
                    <option value="4">Renewal, Exit, dan Transfer Kemitraan</option>
                </select> -->
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Short_Desc">Ringkasan <span style="color: red;">*</span></label>
                    <input type="text" name="Short_Desc" id="Short_Desc" value="{{ $data[0]->Short_Desc}}" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Short_Desc">Versi Panduan <span style="color: red;">*</span></label>
                    <input type="text" name="Version" id="Version" class="form-control" value="{{ $data[0]->Version}}" required />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Effective_Date">Tanggal Berlaku <span style="color: red;">*</span></label>
                    <input type="date" name="Effective_Date" id="Effective_Date" class="form-control" value="{{ $data[0]->Effective_Date}}" required />
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Short_Desc">Change Log <span style="color: red;">*</span></label>
                    <textarea class="form-control" id="Changelog" name="Changelog" rows="3" value="{{ $data[0]->Changelog}}"></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Short_Desc">Files <span style="color: red;">*</span></label>
                    @foreach($data[0]->Files as $File)
                    <div>
                        <a href="{{ $File->Url }}">File</a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>


        <!-- <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button> -->
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#broadcastForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: "panduan-submit",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    alert("Berhasil membuat broadcast");

                    $('#broadcastForm')[0].reset();

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