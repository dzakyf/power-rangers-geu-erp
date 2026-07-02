<div class="py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header p-2">
                    <h5 class="m-0">Detail Work Order</h5>
                </div>
                <div class="card-body">
                    <form id="WorkOrderForm">
                        @csrf

                        <div class="form-group">
                            <label for="POO_Id">Point of Origin (POO)</label>
                            <select class="form-control select2" id="POO_Id" name="POO_Id" style="width:100%"></select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Status">Status</label>
                                <select class="form-control" id="Status" name="Status">
                                    <option value="">-- Pilih Status --</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Work_Order_Date">Work Order Date</label>
                                <input type="date" class="form-control" id="Work_Order_Date" name="Work_Order_Date">
                            </div>
                        </div>

                        <!-- <hr> -->
                        <!-- <button type="button" class="btn btn-primary" id="SubmitWorkOrder">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button> -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $.get('master-work-order-status-data', function(res) {
            var $sel = $('#Status');
            $sel.empty().append($('<option>', { value: '', text: '-- Pilih Status --' }));
            $.each(res.data, function(_, item) {
                if (item.Is_Active === 'Yes') {
                    $sel.append($('<option>', {
                        value: item.Code,
                        text: item.Code + ' | ' + item.Name
                    }));
                }
            });
        });


        $('#POO_Id').select2({
            placeholder: '-- Pilih POO --',
            allowClear: true,
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
                                text: row.Name + ' | Dibuat oleh: ' + (row.Created_By_Name || '-')
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#SubmitWorkOrder').on('click', function() {
            var payload = new FormData(document.getElementById('WorkOrderForm'));

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