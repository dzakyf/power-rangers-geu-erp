<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

?>

@extends('layout.mainlayout-bp')

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
            <span class="active">CAPEX</span>
        </li>
    </ol>
</div>
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5 class="d-inline-block">Form CAPEX</h5>
    <button class="btn btn-warning float-right m-3 px-3 py-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
    <form id="capexForm" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <label for="Mitra_Id">Pilih Mitra <span style="color: red;">*</span></label>
                <select id="Mitra_Id" class="custom-select">
                    <option></option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Qty">Qty <span style="color: red;">*</span></label>
                    <input type="number" name="Qty" id="Qty" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Price">Price <span style="color: red;">*</span></label>
                    <input type="number" name="Price" id="Price" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Total">Total <span style="color: red;">*</span></label>
                    <input type="number" name="Total" id="Total" class="form-control" required readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Description">Asset / Description <span style="color: red;">*</span></label>
                    <input type="text" name="Description" id="Description" class="form-control" required />
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let opportunityId = new URLSearchParams(window.location.search).get('id') ?? null;

    $('#Qty, #Price').on('input', function() {
        let qty = parseFloat($('#Qty').val()) || 0;
        let price = parseFloat($('#Price').val()) || 0;
        $('#Total').val(qty * price);
    });


    $(document).ready(function() {
        $('#capexForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            formData.append('opportunity_id', opportunityId)

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: "partner-capex-submit",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    alert("Berhasil membuat capex");

                    $('#capexForm')[0].reset();

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

        $('#Mitra_Id').select2({
            ajax: {
                url: 'partner-data-active-mitra',
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
                        var opportunityId = $(this).attr('Opportunity_Id');

                        results.push({
                            id: cells.eq(0).text(),
                            text: cells.eq(1).text(),
                            cell2: cells.eq(2).text(),
                            opportunityId: opportunityId,
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
            placeholder: 'Cari Mitra...',
            allowClear: true
        });

        $('#Mitra_Id').on('select2:select', function(e) {
            var data = e.params.data;

            opportunityId = data.opportunityId
            console.log('Selected:', opportunityId);
        });

        $('#Mitra_Id').on('select2:unselect', function(e) {
            console.log('Unselected');
        });
    });
</script>
@endsection