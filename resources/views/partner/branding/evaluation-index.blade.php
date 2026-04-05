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
    <h5>Form Evaluasi Promosi</h5>
    <form id="createMarketingForm" enctype="multipart/form-data" action="partner-branding-evaluation-submit">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Platform">Platform<span style="color: red;">*</span></label>
                    <input type="text" name="Platform" id="Platform" class="form-control" readonly />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Budget_Realization">Budget Realization <span style="color: red;">*</span></label>
                    <input type="text" name="Budget_Realization" id="Budget_Realization" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Total_Leads">Total Leads</label>
                    <input type="text" name="Total_Leads" id="Total_Leads" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Cost_Per_Lead">Cost Per Lead</label>
                    <input type="text" name="Cost_Per_Lead" id="Cost_Per_Lead" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Total_Deal">Total Deal</label>
                    <input type="text" name="Total_Deal" id="Total_Deal" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Conversion_Rate">Conversion Rate</label>
                    <input type="text" name="Conversion_Rate" id="Conversion_Rate" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Cost_Per_Acquisition">Cost Per Acquisition</label>
                    <input type="text" name="Cost_Per_Acquisition" id="Cost_Per_Acquisition" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Price_Deal_Lead">Price Deal Lead</label>
                    <input type="text" name="Price_Deal_Lead" id="Price_Deal_Lead" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Revenue">Revenue</label>
                    <input type="text" name="Revenue" id="Revenue" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ROI">ROI</label>
                    <input type="text" name="ROI" id="ROI" class="form-control" readonly />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Recommendation">Rekomendasi</label>
                    <input type="text" name="Recommendation" id="Recommendation" class="form-control" />
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </form>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function getInitialData() {
        let fd = new FormData()

        $.ajax({
            url: `partner-branding-evaluation-data?promotion_id=${1}`,
            type: 'GET',
            dataType: 'xml',
            success: function(xml) {
                const fields = [
                    null,
                    null,
                    'Platform',
                    'Budget_Realization',
                    'Total_Leads',
                    'Cost_Per_Lead',
                    'Total_Deal',
                    'Conversion_Rate',
                    'Cost_Per_Acquisition',
                    'Price_Deal_Lead',
                    'Revenue',
                    'ROI'
                ];

                $(xml).find('row').each(function() {
                    const cells = $(this).find('cell');
                    cells.each(function(index) {
                        const fieldName = fields[index + 1]; // +1 to skip ID at index 0
                        if (fieldName) {
                            const value = $(this).text();
                            $('#' + fieldName).val(value);
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch evaluation data:', error);
            }
        });
    }

    $(document).ready(function() {
        getInitialData()
        $('#createMarketingForm').on('submit', function(e) {
            e.preventDefault();

            const params = new URLSearchParams(window.location.search);
            const promotionId = params.get('promotion_id');

            let formData = new FormData(this);
            formData.append('Promotion_Id', promotionId)

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    alert("Berhasil mengupdate data promosi");

                    // $('#createMarketingForm')[0].reset();

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