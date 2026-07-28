<style>
    .select2-container,
    .select2-container .selection,
    .select2-container .select2-selection,
    .select2-container .select2-selection--single {
        width: 100% !important;
        display: block !important;
    }
</style>

<div class="row">
    <div class="py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="m-3">Update Point of Origin (POO)</h5>
            </div>
            <div class="card-body">
                <form id="POOForm">
                    @csrf
                    <input type="hidden" class="form-control" id="ID" name="ID">
                    <h6 class="text-muted font-weight-bold mb-3">General Information</h6>
                    <div class="form-row">
                        <div class="form-group col-3">
                            <label for="Name">Name</label>
                            <input type="text" class="form-control" id="Name" name="Name">
                        </div>
                        <div class="form-group col-3">
                            <label for="Type">Type</label>
                            <input type="text" class="form-control" id="Type" name="Type" value="SUPPLIER" placeholder="e.g. SUPPLIER">
                        </div>
                        <div class="form-group col-3">
                            <label for="Category">Category</label>
                            <select class="form-control select2" id="Category" name="Category" style="width:100%">
                                <option value="">-- Pilih --</option>
                            </select>
                        </div>
                        <div class="form-group col-3">
                            <label for="UCO_Type">UCO Type</label>
                            <input type="text" class="form-control" id="UCO_Type" name="UCO_Type">
                        </div>
                        <div class="form-group col-3">
                            <label for="Price">Price</label>
                            <input type="number" class="form-control" id="Price" name="Price">
                        </div>
                        <div class="form-group col-3">
                            <label for="Price_Unit">Price Unit</label>
                            <input type="text" class="form-control" id="Price_Unit" name="Price_Unit">
                        </div>
                        <div class="form-group col-3">
                            <label for="Cycle">Cycle</label>
                            <input type="text" class="form-control" id="Cycle" name="Cycle">
                        </div>
                        <div class="form-group col-3">
                            <label for="Point">Point</label>
                            <input type="number" class="form-control" id="Point" name="Point">
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted font-weight-bold mb-3">PIC Information</h6>
                    <div class="form-row">
                        <div class="form-group col-3">
                            <label for="PIC">PIC</label>
                            <input type="text" class="form-control" id="PIC" name="PIC">
                        </div>
                        <div class="form-group col-3">
                            <label for="Employee">Employee</label>
                            <input type="text" class="form-control" id="Employee" name="Employee">
                        </div>
                        <div class="form-group col-3">
                            <label for="Phone">Phone</label>
                            <input type="text" class="form-control" id="Phone" name="Phone">
                        </div>
                        <div class="form-group col-3">
                            <label for="Position">Position</label>
                            <input type="text" class="form-control" id="Position" name="Position">
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted font-weight-bold mb-3">Location</h6>
                    <div class="form-row">
                        <div class="form-group col-3">
                            <label for="Province">Province</label>
                            <select class="form-control select2" id="Province" name="Province" style="width:100%"></select>
                        </div>
                        <div class="form-group col-3">
                            <label for="City">City</label>
                            <select class="form-control select2" id="City" name="City" style="width:100%" disabled></select>
                        </div>
                        <div class="form-group col-3">
                            <label for="Subdistrict">Subdistrict</label>
                            <select class="form-control select2" id="Subdistrict" name="Subdistrict" style="width:100%" disabled></select>
                        </div>
                        <div class="form-group col-3">
                            <label for="Village">Village</label>
                            <select class="form-control select2" id="Village" name="Village" style="width:100%" disabled></select>
                        </div>
                        <div class="form-group col-12">
                            <label for="Full_Address">Full Address</label>
                            <input type="text" class="form-control" id="Full_Address" name="Full_Address">
                        </div>
                        <div class="form-group col-6">
                            <label for="Location_Coordinate">Location Coordinate</label>
                            <input type="text" class="form-control" id="Location_Coordinate" name="Location_Coordinate" placeholder="lat,lng">
                        </div>
                        <div class="form-group col-6">
                            <label for="Nearest_Warehouse">Nearest Warehouse</label>
                            <input type="text" class="form-control" id="Nearest_Warehouse" name="Nearest_Warehouse">
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted font-weight-bold mb-3">Bank Information</h6>
                    <div class="form-row">
                        <div class="form-group col-4">
                            <label for="Bank_Name">Bank Name</label>
                            <select class="form-control select2" id="Bank_Name" name="Bank_Name" style="width:100%">
                                <option value="">-- Pilih Bank --</option>
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="Bank_Account_Number">Account Number</label>
                            <input type="text" class="form-control" id="Bank_Account_Number" name="Bank_Account_Number">
                        </div>
                        <div class="form-group col-4">
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
                    }
, extra);
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return {
                                id: row[valueField],
                                text: row[labelField]
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

        $.get('master-bank-data', function(res) {
            if (res.status === 'success') {
                var bankSel = $('#Bank_Name');
                bankSel.empty().append('<option value="">-- Pilih Bank --</option>');
                res.data.forEach(function(item) {
                    bankSel.append($('<option>', { value: item.text, text: item.text }));
                });
            }
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

        $.get('master-category-data', function(res) {
            var cat = $('#Category');
            cat.empty().append($('<option>', {
                value: '',
                text: '-- Pilih --'
            }));
            $.each(res.data, function(_, item) {
                cat.append($('<option>', {
                    value: item.Name,
                    text: item.Name
                }));
            });
        });

        $('#SubmitPOO').on('click', function() {
            clearFieldErrors();
            var form = document.getElementById('POOForm');
            var payload = new FormData(form);

            $.ajax({
                url: 'master-poo-update-submit',
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

    function getPOOData() {
        var id = new URLSearchParams(window.location.search).get('id');
        if (!id) return;


        $.ajax({
            url: 'master-poo-data?format=json&id=' + id,
            type: 'GET',
            success: function(res) {
                var data = res.data[0];
                if (!data) return;

                console.log(data)

                $('#ID').val(data.ID)
                $('#Type').val(data.Type);
                $('#Name').val(data.Name);
                $('#PIC').val(data.PIC);
                $('#Code').val(data.Code);
                $('#Employee').val(data.Employee);
                $('#Phone').val(data.Phone);
                $('#Position').val(data.Position);
                $('#Category').val(data.Category).trigger('change');
                $('#UCO_Type').val(data.UCO_Type);
                $('#Price').val(data.Price);
                $('#Price_Unit').val(data.Price_Unit);
                $('#Cycle').val(data.Cycle);
                $('#Full_Address').val(data.Full_Address);
                $('#Location_Coordinate').val(data.Location_Coordinate);
                $('#Nearest_Warehouse').val(data.Nearest_Warehouse);
                $('#Bank_Account_Number').val(data.Bank_Account_Number);
                $('#Bank_Account_Name').val(data.Bank_Account_Name);
                // Load banks and set value when data is ready
                $.get('master-bank-data', function(res) {
                    if (res.status === 'success') {
                        var bankSel = $('#Bank_Name');
                        bankSel.empty().append('<option value="">-- Pilih Bank --</option>');
                        res.data.forEach(function(item) {
                            bankSel.append($('<option>', { value: item.text, text: item.text }));
                        });
                        bankSel.val(data.Bank_Name);
                    }
                });
                $('#Point').val(data.Point);

                // Prefill chained selects in order
                setSelect2Val('#Province', data.Province);
                $('#City').prop('disabled', false);
                makeSelect2('#City', 'master-city', function() {
                    return {
                        province: $('#Province').val()
                    };
                }, 'City', 'City');
                setSelect2Val('#City', data.City);

                $('#Subdistrict').prop('disabled', false);
                makeSelect2('#Subdistrict', 'master-district', function() {
                    return {
                        province: $('#Province').val(),
                        city: $('#City').val()
                    };
                }, 'District', 'District');
                setSelect2Val('#Subdistrict', data.Subdistrict);

                $('#Village').prop('disabled', false);
                makeSelect2('#Village', 'master-subdistrict', function() {
                    return {
                        province: $('#Province').val(),
                        city: $('#City').val(),
                        district: $('#Subdistrict').val()
                    };
                }, 'Sub_District', 'Sub_District');
                setSelect2Val('#Village', data.Village);
            },
            error: function() {
                alert('Gagal memuat data POO.');
            }
        });
    }
</script>