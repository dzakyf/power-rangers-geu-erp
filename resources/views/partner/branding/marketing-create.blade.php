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
            <span class="active">Marketing</span>
        </li>
    </ol>
</div>
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Form Master Materi Marketing</h5>
    <form id="createMarketingForm" enctype="multipart/form-data" action="partner-branding-marketing-submit">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="name">Nama Materi Promosi <span style="color: red;">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="type">Jenis Materi <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="type" id="type" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="BANNER">Banner</option>
                        <option value="VIDEO">Video</option>
                        <option value="POSTER">Poster</option>
                        <option value="DIGITAL_ADS">Digital Ads</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="platform">Platform <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="platform" id="platform" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="ONLINE">Online</option>
                        <option value="OFFLINE">Offline</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="channel">Channel <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="channel" id="channel" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="GOOGLE_ADS">Google Ads</option>
                        <option value="TikTok">TikTok</option>
                        <option value="FANPAGE">Fanpage</option>
                        <option value="EVENT">Event</option>
                        <option value="INSTAGRAM">Instagram</option>
                        <option value="FACEBOOK">Facebook</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="status">Status <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="status" id="status" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="ACTIVE">Aktif</option>
                        <option value="NONACTIVE">Non-Aktif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="description">Deskripsi <span style="color: red;">*</span></label>
                    <input type="text" name="description" id="description" class="form-control" required />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Media_Type">Tipe Media <span style="color: red;">*</span></label>
                    <select name="Media_Type" id="Media_Type" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="URL">Url</option>
                        <option value="UPLOAD">Upload Dokumen</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="media_url" class="row" style="display:none;">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="media_url">Media Url</label>
                    <input type="text" name="media_url" id="media_url" class="form-control" />
                </div>
            </div>
        </div>

        <div id="design" class="row" style="display:none;">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="design">Dokumen Pendukung</label>
                    <input type="file" name="design" id="design" class="form-control" />
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#Media_Type').on('change', function(e) {
            let value = $(this).val()

            if (value === "URL") {
                $("#media_url").show()
                $("#design").hide()
            }
            if (value === "UPLOAD") {
                $("#media_url").hide()
                $("#design").show()
            }
        })

        $('#createMarketingForm').on('submit', function(e) {
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
                    alert("Berhasil membuat data marketing");

                    $('#broadcastForm')[0].reset();

                },
                error: function(xhr, status, error) {
                    console.log('Error details:', xhr.responseText);

                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                }
            });
        });
    });
</script>
@endsection