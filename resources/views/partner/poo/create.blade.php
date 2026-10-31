@extends('layout.mainlayout-bp')


<style>
    .select2-container.is-invalid {
        border: 1px solid #dc3545;
        border-radius: 4px;
    }

    .select2-container,
    .select2-container .selection,
    .select2-container .select2-selection,
    .select2-container .select2-selection--single {
        width: 100% !important;
        display: block !important;
    }
</style>

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="partner-poo">Point of Origin</a></li>
        <li class="breadcrumb-item active"><span class="active">Create</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3 d-inline-block">Create Point of Origin</h5>
                    <button class="btn btn-warning float-right m-3 px-3 py-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
                </div>
                <div class="card-body">
                    <form id="POOForm">
                        @csrf

                        <h6 class="text-muted font-weight-bold mb-3">General Information</h6>
                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label for="Name">Name</label>
                                <input type="text" class="form-control" id="Name" name="Name">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Type">Type</label>
                                <select class="form-control" id="Type" name="Type">
                                    <option value="SUPPLIER" selected>SUPPLIER</option>
                                    <option value="MITRA">MITRA</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Category">Category</label>
                                <select class="form-control select2" id="Category" name="Category" style="width:100%">
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="UCO_Type">UCO Type</label>
                                <select class="form-control" id="UCO_Type" name="UCO_Type">
                                    <option value="CAIR" selected>Cair</option>
                                    <option value="PADAT">Padat</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Price">Price</label>
                                <input type="number" class="form-control" id="Price" name="Price">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Price_Unit">Price Unit</label>
                                <select class="form-control" id="Price_Unit" name="Price_Unit">
                                    <option value="L" selected>L</option>
                                    <option value="KG">KG</option>
                                    <option value="TON">TON</option>
                                    <option value="KEMASAN">KEMASAN</option>
                                    <option value="RP">RP</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Cycle">Cycle</label>
                                <select class="form-control" id="Cycle" name="Cycle">
                                    <option value="">-- Pilih --</option>
                                    <option value="1_week">1 Minggu</option>
                                    <option value="2_week">2 Minggu</option>
                                    <option value="3_week">3 Minggu</option>
                                    <option value="4_week">4 Minggu</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Point">Point</label>
                                <input type="number" class="form-control" id="Point" name="Point">
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted font-weight-bold mb-3">PIC Information</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="PIC">PIC</label>
                                <input type="text" class="form-control" id="PIC" name="PIC">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Employee">Employee</label>
                                <input type="text" class="form-control" id="Employee" name="Employee">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Phone">Phone</label>
                                <input type="text" class="form-control" id="Phone" name="Phone">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Position">Position</label>
                                <input type="text" class="form-control" id="Position" name="Position">
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted font-weight-bold mb-3">Location</h6>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="Province">Province</label>
                                <select class="form-control select2" id="Province" name="Province" style="width:100%"></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="City">City</label>
                                <select class="form-control select2" id="City" name="City" style="width:100%" disabled></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Subdistrict">Subdistrict</label>
                                <select class="form-control select2" id="Subdistrict" name="Subdistrict" style="width:100%" disabled></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Village">Village</label>
                                <select class="form-control select2" id="Village" name="Village" style="width:100%" disabled></select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="Full_Address">Full Address</label>
                                <input type="text" class="form-control" id="Full_Address" name="Full_Address">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Location_Coordinate">Location Coordinate</label>
                                <input type="text" class="form-control" id="Location_Coordinate" name="Location_Coordinate" placeholder="lat,lng">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Nearest_Warehouse">Nearest Warehouse</label>
                                <select class="form-control select2" id="Nearest_Warehouse" name="Nearest_Warehouse" style="width:100%"></select>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted font-weight-bold mb-3">Bank Information</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Bank_Name">Bank Name</label>
                                <select class="form-control select2" id="Bank_Name" name="Bank_Name" style="width:100%">
                                    <option value="">-- Pilih Bank --</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Bank_Account_Number">Account Number</label>
                                <input type="text" class="form-control" id="Bank_Account_Number" name="Bank_Account_Number">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Bank_Account_Name">Account Name</label>
                                <input type="text" class="form-control" id="Bank_Account_Name" name="Bank_Account_Name">
                            </div>
                        </div>

                        <hr>
                        <button type="button" class="btn btn-primary" id="SubmitPOO">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    function makeSelect2(selector, url, params, valueField, labelField) {
        $(selector).select2({
            placeholder: '-- Pilih --',
            allowClear: true,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    var extra = typeof params === 'function' ? params() : params;
                    return Object.assign({
                        q: p.term || ''
                    }, extra);
                },
                processResults: function(res) {
                    console.log(res)
                    return {
                        results: res.data.map(function(row) {
                            let data = row
                            let text = row[labelField]
                            return {
                                id: row[valueField],
                                text: text
                            };
                        })
                    };
                },
                cache: true
            }
        });
    }

    function setSelect2Val(selector, value) {
        var option = new Option(value, value, true, true);
        $(selector).append(option).trigger('change');
    }

    function resetSelect2(selector) {
        $(selector).val(null).trigger('change').prop('disabled', true);
        $(selector).empty().trigger('change');
    }


    function clearFieldErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.select2-container.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback.server').remove();
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        Object.keys(errors).forEach(function(field) {
            var $input = $('[name="' + field + '"]');
            var $group = $input.closest('.form-group');
            var $select2 = $group.find('.select2-container');

            if ($select2.length) {
                $select2.addClass('is-invalid');
            } else {
                $input.addClass('is-invalid');
            }

            $group.append(
                '<div class="invalid-feedback server d-block">' + errors[field][0] + '</div>'
            );
        });
    }

    $(document).ready(function() {

        makeSelect2('#Province', 'master-province', {}, 'Province', 'Province');

        // Load categories
        $.get('master-category-data', function(res) {
            var cat = $('#Category');
            $.each(res.data, function(_, item) {
                cat.append($('<option>', {
                    value: item.Name,
                    text: item.Name
                }));
            });
        });

        $('#Province').on('change', function() {
            resetSelect2('#City');
            resetSelect2('#Subdistrict');
            resetSelect2('#Village');

            var province = $(this).val();
            if (!province) return;

            $('#City').prop('disabled', false);
            makeSelect2('#City', 'master-city', function() {
                return {
                    province: $('#Province').val()
                };
            }, 'City', 'City');
        });

        $('#City').on('change', function() {
            resetSelect2('#Subdistrict');
            resetSelect2('#Village');

            var city = $(this).val();
            if (!city) return;

            $('#Subdistrict').prop('disabled', false);
            makeSelect2('#Subdistrict', 'master-district', function() {
                return {
                    province: $('#Province').val(),
                    city: $('#City').val()
                };
            }, 'District', 'District');
        });

        $('#Subdistrict').on('change', function() {
            resetSelect2('#Village');

            var district = $(this).val();
            if (!district) return;

            $('#Village').prop('disabled', false);
            makeSelect2('#Village', 'master-subdistrict', function() {
                return {
                    province: $('#Province').val(),
                    city: $('#City').val(),
                    district: $('#Subdistrict').val()
                };
            }, 'Sub_District', 'Sub_District');
        });



        makeSelect2('#Nearest_Warehouse', 'partner-warehouse-data', {}, 'id', 'warehouse_location');

        $.get('master-bank-data', function(res) {
            if (res.status === 'success') {
                var bankSel = $('#Bank_Name');
                bankSel.empty().append('<option value="">-- Pilih Bank --</option>');
                res.data.forEach(function(item) {
                    bankSel.append($('<option>', {
                        value: item.text,
                        text: item.text
                    }));
                });
            }
        });
        $('#SubmitPOO').on('click', function() {
            clearFieldErrors();
            var form = document.getElementById('POOForm');
            var payload = new FormData(form);

            $.ajax({
                url: 'master-poo-create-submit',
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
                        window.location.href = 'master-poo';
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        showFieldErrors(xhr.responseJSON.errors);
                    } else {
                        alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                    }
                }
            });
        });
    });
</script>

@endsection