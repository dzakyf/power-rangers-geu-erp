<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

?>

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
            <span class="active">Broadcast</span>
        </li>
    </ol>
</div>
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Form broadcast</h5>
    <form id="broadcastForm" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Title">Judul <span style="color: red;">*</span></label>
                    <input type="text" name="Title" id="Title" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Type">Jenis <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="Type" id="Type" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="sistem">Sistem</option>
                        <option value="operasional">Operasional</option>
                        <option value="kemitraan">Kemitraan</option>
                        <option value="keuangan">Keuangan</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Short_Desc">Ringkasan <span style="color: red;">*</span></label>
                    <input type="text" name="Short_Desc" id="Short_Desc" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Long_Desc">Detail <span style="color: red;">*</span></label>
                    <input type="text" name="Long_Desc" id="Long_Desc" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Effective_Date">Tanggal Berlaku <span style="color: red;">*</span></label>
                    <input type="date" name="Effective_Date" id="Effective_Date" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Status">Status Kebijakan <span style="color: red;">*</span></label>
                    <select name="Status" id="Status" class="form-control" required>
                        <option value="">-- Pilih Status--</option>
                        <option value="ACTIVE">Aktif</option>
                        <option value="INACTIVE">Tidak Aktif</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Doc">Dokumen Pendukung</label>
                    <input type="file" name="Doc" id="Doc" class="form-control" />
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#broadcastForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: "broadcast-submit",
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
@endsection