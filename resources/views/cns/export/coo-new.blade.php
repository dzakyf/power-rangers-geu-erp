<?php
use Illuminate\Support\Facades\Auth;
$Saya = Auth::user()->username;
?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;">NEW CERTIFICATE OF ORIGIN</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPagesCOO">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>WBT No</label>
                            <select name="WBT_No" id="WBT_No" class="form-control shadow-sm">
                                <option value="">-- Pilih WBT --</option>
                                @foreach ($wbts as $item)
                                <option value="{{ $item->WBT_No }}">{{ $item->WBT_No }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="hidden" name="COO_No" id="COO_No" value="{{ $CooNo }}" class="form-control shadow-sm" required readonly />
                            <label>Export Invoice Number</label>
                            <input type="text" name="Fin_Inv_No" id="Fin_Inv_No" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Export Invoice Date</label>
                            <input type="text" name="Fin_Inv_Date" id="Fin_Inv_Date" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Contract Reference</label>
                            <input type="text" name="Contract_No" id="Contract_No" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Consignee</label>
                            <select name="Consignee" id="Consignee" class="form-control shadow-sm" required>
                                <option value="">-- Pilih Consignee --</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->Supplier_ID }}">{{ $c->Company_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Method of Dispatch</label>
                            <input type="text" name="Method_Of_Dispatch" id="Method_Of_Dispatch" value="Sea" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type of Shipment</label>
                            <input type="text" name="Shipment_Type" id="Shipment_Type" value="CIF" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Vessel</label>
                            <input type="text" name="Vessel" id="Vessel" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Voyage No</label>
                            <input type="text" name="Voyage_No" id="Voyage_No" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Port of Loading</label>
                            <input type="text" name="Port_Of_Loading" id="Port_Of_Loading" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date of Departure</label>
                            <input type="text" name="Date_Of_Departure" id="Date_Of_Departure" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Port of Discharge</label>
                            <input type="text" name="Port_Of_Discharge" id="Port_Of_Discharge" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Final Destination</label>
                            <input type="text" name="Final_Destination" id="Final_Destination" value="" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kind &amp; No of Packages</label>
                            <input type="text" name="Kind_Packages" id="Kind_Packages" value="" placeholder="e.g. 5 x Container" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Description of Goods</label>
                            <input type="text" name="Good_Description" id="Good_Description" value="REFINED USED COOKING OIL" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>HS Code</label>
                            <select name="HS_Code" id="HS_Code" class="form-control shadow-sm">
                                <option value="">-- Pilih HS Code --</option>
                                <option value="15180090">15180090</option>
                                <option value="15119020" selected>15119020</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Net Weight (Kg)</label>
                            <input type="text" inputmode="decimal" name="Net_Weight" id="Net_Weight" value="0.00" class="form-control shadow-sm cns-decimal" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Place of Issue</label>
                            <input type="text" name="Place_Of_Issue" id="Place_Of_Issue" value="Surabaya" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Issue Date</label>
                            <input type="text" name="Issue_Date" id="Issue_Date" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="Note" id="Note" rows="2" class="form-control shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Container Details --}}
                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Container Nos. W / Seal Nos.</h6>
                <table class="table table-bordered table-sm" id="containerTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:45%">Container No</th>
                            <th style="width:45%">Seal No</th>
                            <th style="width:10%">
                                <button type="button" class="btn btn-success btn-sm" onclick="addContainerRow()">+ Baris</button>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="containerBody">
                        <tr>
                            <td><input type="text" name="containers[]" class="form-control form-control-sm" /></td>
                            <td><input type="text" name="seals[]" class="form-control form-control-sm" /></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="divtom">
                <input type="button" id="SubmitCOO" value="Submit" class="btn btn-success mt-2 mr-2" />
            </div>
        </div>
    </div>
    <div id="DivProcess"></div>
</div>

<script>
    var myCalCOO;
    myCalCOO = new dhtmlXCalendarObject(["Fin_Inv_Date","Date_Of_Departure","Issue_Date"]);
    myCalCOO.setDateFormat("%Y-%m-%d");

    function addContainerRow() {
        $('#containerBody').append('<tr>' +
            '<td><input type="text" name="containers[]" class="form-control form-control-sm" /></td>' +
            '<td><input type="text" name="seals[]" class="form-control form-control-sm" /></td>' +
            '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>' +
        '</tr>');
    }
    function removeRow(btn) {
        $(btn).closest('tr').remove();
    }

    $('#WBT_No').on('change', function() {
        $.blockUI();
        $.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
            url: "cns-export-certificate-origin-new-get-wbt",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                WBT_No: $('#WBT_No').find('option:selected').val()
            },
            success: function(response) {
                if (response.status != 'error') {
                    Object.keys(response.data.wbt).forEach(function(key) {
                        if (key != 'orderDetails' && key != 'WBT_No') {
                            $('#' + key).val(response[key]);
                        }
                    });
                    $('#containerBody').html('');
                    let counter = 1;
                    let string = "";
                    response.data.details.forEach(function(container, index) {
                        $('#containerBody').append('<tr>' +
                            '<td><input type="text" name="containers[]" class="form-control form-control-sm" value="'+container.Container+'" /></td>' +
                            '<td><input type="text" name="seals[]" class="form-control form-control-sm" value="'+container.Seal+'" /></td>' +
                            '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>' +
                        '</tr>');
                    });
                }
                $.unblockUI();
            },
            error:function() {
                alert('Error fetching order details. Please try again.');
                $.unblockUI();
            }
        });
    })

    $('#SubmitCOO').click(function () {
        $.blockUI();
        var formData = new FormData();
        $('#ContentPagesCOO input,#ContentPagesCOO select,#ContentPagesCOO textarea').not(':button').each(function() {
            var name = $(this).attr('name');
            var val  = $(this).hasClass('cns-decimal') ? cnsParseNumber($(this).val()) : $(this).val();
            if (name && val !== undefined) {
                formData.append(name, val);
            }
        });
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            url: "cns-export-certificate-origin-new-submit",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                $.unblockUI();
                if (data.indexOf("success") !== -1) {
                    alert('Certificate of Origin berhasil disimpan!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                } else {
                    alert('Gagal menyimpan!');
                }
            },
            error: function () {
                $.unblockUI();
                alert("Error Internal! Silakan coba lagi.");
            }
        });
    });
</script>
