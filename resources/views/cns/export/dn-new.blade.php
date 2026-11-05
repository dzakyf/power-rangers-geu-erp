<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? '';

?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW DELIVERY NOTE</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="DN_No">DN No</label>
                                <input type="text" name="DN_No" id="DN_No" value="{{ $DnNo }}" class="form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Shipper">Entitas Penerbit</label>
                                <select name="Shipper" id="Shipper" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Entitas Penerbit --</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->Supplier_ID }}">{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Delivery_Date">Date of Delivery / Issue</label>
                                <input type="text" name="Delivery_Date" id="Delivery_Date" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Consignee">Consignee (Delivered to)</label>
                                <select name="Consignee" id="Consignee" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->Supplier_ID }}" data-address="{{ $c->Address }}">{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Consignee_Address">Consignee Address</label>
                                <textarea name="Consignee_Address" id="Consignee_Address" rows="2" class="text-black form-control" placeholder="Alamat Consignee" value=""></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Supplier_ISCC_Ref">Supplier ISCC Reference</label>
                                <input type="text" name="Supplier_ISCC_Ref" id="Supplier_ISCC_Ref" value="" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Contract_No">Contract Number</label>
                                <input type="text" name="Contract_No" id="Contract_No" value="" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Product">Product</label>
                                <input type="text" name="Product" id="Product" value="Used Cooking Oil (UCO) entirely of Vege-Oil Origin" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Total_Tonnage">Total Tonnage Delivered (MT)</label>
                                <input type="number" step="0.001" name="Total_Tonnage" id="Total_Tonnage" value="0" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Country_Of_Origin">Country of Origin</label>
                                <input type="text" name="Country_Of_Origin" id="Country_Of_Origin" value="Indonesia" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="GHG_Value">GHG Value</label>
                                <textarea name="GHG_Value" id="GHG_Value" rows="2" class="form-control shadow-sm">Disaggregated default value used for UCO for either cultivation, Transport and/or processing</textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="Note">Note</label>
                                <textarea name="Note" id="Note" rows="2" class="form-control shadow-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="divtom">
                <input type="button" id="SubmitDN" name="SubmitDN" value="Submit" class="btn btn-success mt-2 mr-2" />
            </div>
        </div>
    </div>
    <div id="DivProcess"></div>
</div>

<script>
    var myCalendarDN;
    myCalendarDN = new dhtmlXCalendarObject(["Delivery_Date"]);
    myCalendarDN.setDateFormat("%Y-%m-%d");

    function updateAddressFromSelectedCompany(selectSelector, addressSelector) {
        var selectedOpt = $(selectSelector).find('option:selected');
        var addr = selectedOpt.data('address');
        if (typeof addr !== 'undefined') {
            $(addressSelector).val(addr || '');
        }
    }

    $('#Consignee').on('change', function () {
        updateAddressFromSelectedCompany('#Consignee', '#Consignee_Address');
    });

    $('#SubmitDN').click(function () {
        gettingVars = getAllValues();

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "cns-export-delivery-note-new-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: gettingVars,
            success: function (data) {
                if (data.indexOf("success") !== -1) {
                    alert('Delivery Note berhasil disimpan!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                } else {
                    alert('Gagal menyimpan Delivery Note!');
                }
            },
            error: function () {
                alert("Error Internal! Silakan coba lagi.");
            }
        });
    });

    function getAllValues() {
        var inputValues = [];
        $('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function () {
            var type = $(this).attr("type");
            if ((type == "checkbox" || type == "radio") && this.checked) {
                inputValues.push($(this).attr("name") + "=" + $(this).val());
            } else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent($(this).val()));
            } else if ($(this).is("textarea")) {
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent($(this).val()));
            }
        });
        return inputValues.join('&');
    }
</script>
