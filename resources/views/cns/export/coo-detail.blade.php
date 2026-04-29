<?php
use Illuminate\Support\Facades\Auth;
$Saya = Auth::user()->username;
?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;">DETAIL CERTIFICATE OF ORIGIN</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPagesCOODtl">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>COO No</label>
                            <input type="text" name="COO_No" id="COO_No" value="{{ $coo->COO_No }}" class="form-control shadow-sm" required readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Export Invoice Number</label>
                            <input type="text" name="Fin_Inv_No" id="Fin_Inv_No" value="{{ $coo->Fin_Inv_No }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Export Invoice Date</label>
                            <input type="text" name="Fin_Inv_Date" id="Fin_Inv_Date" value="{{ $coo->Fin_Inv_Date }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Contract Reference</label>
                            <input type="text" name="Contract_No" id="Contract_No" value="{{ $coo->Contract_No }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Consignee</label>
                            <select name="Consignee" id="Consignee" class="form-control shadow-sm" required>
                                <option value="">-- Pilih Consignee --</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->Supplier_ID }}" {{ $coo->Consignee==$c->Supplier_ID?'selected':'' }}>{{ $c->Company_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Method of Dispatch</label>
                            <input type="text" name="Method_Of_Dispatch" id="Method_Of_Dispatch" value="{{ $coo->Method_Of_Dispatch }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type of Shipment</label>
                            <input type="text" name="Shipment_Type" id="Shipment_Type" value="{{ $coo->Shipment_Type }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Vessel</label>
                            <input type="text" name="Vessel" id="Vessel" value="{{ $coo->Vessel }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Voyage No</label>
                            <input type="text" name="Voyage_No" id="Voyage_No" value="{{ $coo->Voyage_No }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Port of Loading</label>
                            <input type="text" name="Port_Of_Loading" id="Port_Of_Loading" value="{{ $coo->Port_Of_Loading }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date of Departure</label>
                            <input type="text" name="Date_Of_Departure" id="Date_Of_Departure" value="{{ $coo->Date_Of_Departure }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Port of Discharge</label>
                            <input type="text" name="Port_Of_Discharge" id="Port_Of_Discharge" value="{{ $coo->Port_Of_Discharge }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Final Destination</label>
                            <input type="text" name="Final_Destination" id="Final_Destination" value="{{ $coo->Final_Destination }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kind &amp; No of Packages</label>
                            <input type="text" name="Kind_Packages" id="Kind_Packages" value="{{ $coo->Kind_Packages }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Description of Goods</label>
                            <input type="text" name="Good_Description" id="Good_Description" value="{{ $coo->Good_Description }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>HS Code</label>
                            <input type="text" name="HS_Code" id="HS_Code" value="{{ $coo->HS_Code }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Net Weight (Kg)</label>
                            <input type="number" step="0.001" name="Net_Weight" id="Net_Weight" value="{{ $coo->Net_Weight }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Place of Issue</label>
                            <input type="text" name="Place_Of_Issue" id="Place_Of_Issue" value="{{ $coo->Place_Of_Issue }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Issue Date</label>
                            <input type="text" name="Issue_Date" id="Issue_Date" value="{{ $coo->Issue_Date }}" class="form-control shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="Note" id="Note" rows="2" class="form-control shadow-sm">{{ $coo->Note }}</textarea>
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
                                <button type="button" class="btn btn-success btn-sm" onclick="addContainerRowDtl()">+ Baris</button>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="containerBodyDtl">
                        @forelse($coo->Details as $dtl)
                        <tr>
                            <td><input type="text" name="containers[]" class="form-control form-control-sm" value="{{ $dtl->Container_No }}" /></td>
                            <td><input type="text" name="seals[]" class="form-control form-control-sm" value="{{ $dtl->Seal_No }}" /></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
                        </tr>
                        @empty
                        <tr>
                            <td><input type="text" name="containers[]" class="form-control form-control-sm" /></td>
                            <td><input type="text" name="seals[]" class="form-control form-control-sm" /></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                <input type="button" id="UpdateCOO" value="Update" class="btn btn-success mt-2 mr-2" />
            </div>
        </div>
    </div>
</div>

<script>
    var myCalCOODtl;
    myCalCOODtl = new dhtmlXCalendarObject(["Fin_Inv_Date","Date_Of_Departure","Issue_Date"]);
    myCalCOODtl.setDateFormat("%Y-%m-%d");

    function addContainerRowDtl() {
        $('#containerBodyDtl').append('<tr>' +
            '<td><input type="text" name="containers[]" class="form-control form-control-sm" /></td>' +
            '<td><input type="text" name="seals[]" class="form-control form-control-sm" /></td>' +
            '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>' +
        '</tr>');
    }
    function removeRow(btn) { $(btn).closest('tr').remove(); }

    $('#UpdateCOO').click(function () {
        $.blockUI();
        var formData = new FormData();
        $('#ContentPagesCOODtl input,#ContentPagesCOODtl select,#ContentPagesCOODtl textarea').not(':button').each(function() {
            var name = $(this).attr('name');
            var val  = $(this).val();
            if (name && val !== undefined) {
                formData.append(name, val);
            }
        });
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            url: "cns-export-certificate-origin-detail-submit",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                $.unblockUI();
                if (data.indexOf("success") !== -1) {
                    alert('Certificate of Origin berhasil diupdate!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                } else {
                    alert('Gagal mengupdate!');
                }
            },
            error: function () {
                $.unblockUI();
                alert("Error Internal! Silakan coba lagi.");
            }
        });
    });
</script>
