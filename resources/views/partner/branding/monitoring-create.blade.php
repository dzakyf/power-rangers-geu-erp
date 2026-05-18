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
            <span class="active">Monitoring Promosi</span>
        </li>
    </ol>
</div>
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Form Monitoring Promosi</h5>
    <form id="createMonitoring" enctype="multipart/form-data" action="partner-branding-monitoring-submit">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="promotion_id">Nomor Promosi<span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="promotion_id" id="promotion_id" class="form-control" required>
                        <option value="">-- Pilih Promosi--</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="campaign_start_at">Tanggal Mulai <span style="color: red;">*</span></label>
                    <input type="date" name="campaign_start_at" id="campaign_start_at" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="campaign_end_at">Tanggal Selesai <span style="color: red;">*</span></label>
                    <input type="date" name="campaign_end_at" id="campaign_end_at" class="form-control" required />
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
                    <label for="budget_realization">Realisasi <span style="color: red;">*</span></label>
                    <input type="text" name="budget_realization" id="budget_realization" class="form-control" required />
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
                        <option value="RUNNING">Running</option>
                        <option value="COMPLETED">Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="sales">PIC <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="sales" id="sales" class="form-control" required>
                        <option value="">-- Pilih Sales--</option>
                    </select>
                </div>
            </div>
        </div>


        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        $('#createMonitoring').on('submit', function(e) {
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
                    alert("Berhasil membuat data monitoring");

                    $('#createMonitoring')[0].reset();

                },
                error: function(xhr, status, error) {
                    console.log('Error details:', xhr.responseText);

                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                }
            });
        });

        $('#promotion_id').select2({
            ajax: {
                url: 'partner-branding-promotion-data',
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
                        var id = $(this).attr('ID');

                        results.push({
                            id: id,
                            text: cells.eq(1).text(),
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

        $('#promotion_id').on('select2:select', function(e) {
            var data = e.params.data;
            console.log('Selected:', data);

        });

        $('#promotion_id').on('select2:unselect', function(e) {
            console.log('Unselected');
        });

        $('#sales').select2({
            ajax: {
                url: 'partner-data-sales',
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

                        var id = $(this).attr('ID');

                        // Get cell values
                        var cell1 = cells.eq(0).text();
                        var cell2 = cells.eq(1).text();

                        results.push({
                            id: id,
                            text: cell1,
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

        $('#sales').on('select2:select', function(e) {
            var data = e.params.data;
            console.log('Selected:', data);

        });

        $('#sales').on('select2:unselect', function(e) {
            console.log('Unselected');
        });
    });
</script>
@endsection