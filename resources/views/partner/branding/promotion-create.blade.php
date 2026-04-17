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
            <span class="active">Promosi</span>
        </li>
    </ol>
</div>
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Form Pengajuan Promosi</h5>
    <form id="createPromotion" enctype="multipart/form-data" action="partner-branding-promotion-submit">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="department">Department <span style="color: red;">*</span></label>
                    <input type="text" name="department" id="department" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="type">Jenis Promosi <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="type" id="type" class="form-control" required>
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
                    <label for="platform">Platform Marketing <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="platform" id="platform" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="GOOGLE_ADS">Google Ads</option>
                        <option value="TIKTOK">TikTok</option>
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
                    <label for="marketing_id">Materi Marketing<span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="marketing_id" id="marketing_id" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
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
                    <label for="audience">Target Promosi <span style="color: red;">*</span></label>
                    <input type="text" name="audience" id="audience" class="form-control" required />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="estimation">Estimasi Budget<span style="color: red;">*</span></label>
                    <input type="text" name="estimation" id="estimation" class="form-control" required />
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
                        <option value="DRAFT">Draft</option>
                        <option value="SUBMIT">Submit</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Voucher">Dokumen Pendukung</label>
                    <input type="file" name="Voucher[]" id="RAB" class="form-control" accept=".png, .pdf, image/png, application/pdf" />
                </div>
            </div>
        </div> -->
        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        $('#createPromotion').on('submit', function(e) {
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
                xhrFields: {
                    responseType: 'blob' // ← tell jQuery to expect binary data
                },
                success: function(response, status, xhr) {
                    // ── Get filename from response header or use default ──
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'voucher-promosi.pdf';

                    if (disposition) {
                        const match = disposition.match(/filename="(.+)"/);
                        if (match) filename = match[1];
                    }

                    // ── Create blob URL and trigger download ──────────────
                    const blob = new Blob([response], {
                        type: 'application/pdf'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    a.click();

                    window.URL.revokeObjectURL(url); // cleanup
                    alert("Berhasil membuat data promosi");
                },
                error: function(xhr, status, error) {
                    // ── Error response is also a blob, need to parse it ───
                    const reader = new FileReader();
                    reader.onload = function() {
                        try {
                            const json = JSON.parse(reader.result);
                            console.log('Error details:', json);
                            alert('Error: ' + (json.error ?? error));
                        } catch (e) {
                            console.log('Error details:', reader.result);
                        }
                    };
                    reader.readAsText(xhr.responseJSON ?? xhr.response);
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                }
            });
        });

        $('#marketing_id').select2({
            ajax: {
                url: 'partner-branding-marketing-data',
                dataType: 'xml',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(xmlData) {
                    var results = [];

                    $(xmlData).find('row').each(function() {
                        var cells = $(this).find('cell');
                        console.log(cells)

                        results.push({
                            id: cells.eq(1).text(),
                            text: cells.eq(2).text(),
                            cell2: cells.eq(2).text(),
                        });
                    });

                    return {
                        results: results,
                        pagination: {
                            more: results.length === 20
                        }
                    };
                }
            },
            minimumInputLength: 0,
            placeholder: 'Cari Marketing Data...',
            allowClear: true
        });

        $('#marketing_id').on('select2:select', function(e) {
            var data = e.params.data;
            console.log('Selected:', data);

        });

        $('#marketing_id').on('select2:unselect', function(e) {
            console.log('Unselected');
        });
    });
</script>
@endsection