@extends('layout.mainlayout-mitra')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="partner-workorder">Work Order</a></li>
        <li class="breadcrumb-item active"><span class="active">Create</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card shadow-sm">
                <div class="card-header p-2">
                    <h5 class="m-0">Create Work Order</h5>
                </div>
                <div class="card-body">
                    <form id="WorkOrderForm">
                        @csrf

                        <div class="form-group">
                            <label for="POO_Id">Point of Origin (POO)</label>
                            <select class="form-control" id="POO_Id" name="POO_Id"></select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Status">Status</label>
                                <select class="form-control" id="Status" name="Status">
                                    <option value="A1" selected>A1 | Atur Jadwal Penjemputan</option>

                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Work_Order_Date">Work Order Date</label>
                                <input type="date" class="form-control" id="Work_Order_Date" name="Work_Order_Date">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="bypass" name="bypass" value="1">
                                <label class="form-check-label" for="bypass">Bypass — Auto-create Pickup Order &amp; Attach to DO</label>
                            </div>
                        </div>

                        <div id="bypassFields" style="display:none;">
                            <hr>
                            <h6>Pickup Order Details (Bypass)</h6>

                            <div class="form-group">
                                <label for="DO_Id">Delivery Order</label>
                                <select class="form-control" id="DO_Id" name="DO_Id" style="width:100%"></select>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="Site">Site (auto from POO City)</label>
                                    <input type="text" class="form-control" id="Site" name="Site" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Price">Price (auto from POO)</label>
                                    <input type="text" class="form-control" id="Price" name="Price" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="Type_Pickup">Type Pickup</label>
                                    <input type="text" class="form-control" id="Type_Pickup" name="Type_Pickup">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Coverage">Route / Coverage</label>
                                    <input type="text" class="form-control" id="Coverage" name="Coverage">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="Package">Package</label>
                                    <input type="text" class="form-control" id="Package" name="Package">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Qty">Qty</label>
                                    <input type="number" class="form-control" id="Qty" name="Qty" min="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="Description">Description</label>
                                <textarea class="form-control" id="Description" name="Description" rows="3"></textarea>
                            </div>
                        </div>

                        <hr>
                        <button type="button" class="btn btn-primary" id="SubmitWorkOrder">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#POO_Id').select2({
            placeholder: '-- Pilih POO --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'master-poo-data',
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    return {
                        format: 'json',
                        q: p.term || ''
                    };
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return {
                                id: row.ID,
                                text: row.Name + ' | Dibuat oleh: ' + (row.Created_By_Name || '-'),
                                city: row.City,
                                price: row.Price
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#POO_Id').on('select2:select', function(e) {
            var data = e.params.data;
            $('#Site').val(data.city || '');
            $('#Price').val(data.price || '');
        });

        var doSelect2Initialized = false;

        $('#bypass').on('change', function() {
            if ($(this).is(':checked')) {
                $('#bypassFields').slideDown(400, function() {
                    if (!doSelect2Initialized) {
                        $('#DO_Id').select2({
                            placeholder: '-- Pilih Delivery Order --',
                            allowClear: true,
                            width: '100%',
                            ajax: {
                                url: 'partner-delivery-order-data',
                                dataType: 'json',
                                delay: 250,
                                data: function(p) {
                                    return {
                                        format: 'json',
                                        status: 'PROGRESS',
                                        q: p.term || ''
                                    };
                                },
                                processResults: function(res) {
                                    return {
                                        results: res.data.map(function(row) {
                                            return {
                                                id: row.ID,
                                                text: row.DO_Code
                                            };
                                        })
                                    };
                                },
                                cache: true
                            }
                        });
                        doSelect2Initialized = true;
                    }
                });
            } else {
                $('#bypassFields').slideUp();
            }
        });

        $('#SubmitWorkOrder').on('click', function() {
            var form = document.getElementById('WorkOrderForm');
            var payload = new FormData(form);

            $.ajax({
                url: 'partner-work-order-create-submit',
                type: 'POST',
                data: payload,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Work order berhasil dibuat!');
                        window.location.href = 'partner-work-order';
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                }
            });
        });
    });
</script>

@endsection