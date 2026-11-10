<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? '';
$companiesKeyed = collect($companies)->keyBy('Supplier_ID');
$consigneeAddr = !empty($dn->Consignee_Address) ? $dn->Consignee_Address : ($companiesKeyed->get($dn->Consignee)->Address ?? '');

?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DELIVERY NOTE</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPagesDN">
                <div class="FormInput_Form_Content">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="DN_No">DN No</label>
                                <input type="text" name="DN_No" id="DN_No" value="{{ $dn->DN_No }}" class="form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Shipper">Entitas Penerbit</label>
                                <select name="Shipper" id="Shipper" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Entitas Penerbit --</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->Supplier_ID }}" {{ ($dn->Shipper ?? '') == $c->Supplier_ID ? 'selected' : '' }}>{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Delivery_Date">Date of Delivery / Issue</label>
                                <input type="text" name="Delivery_Date" id="Delivery_Date" value="{{ $dn->Delivery_Date }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Consignee">Consignee (Delivered to)</label>
                                <select name="Consignee" id="Consignee" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->Supplier_ID }}" data-address="{{ $c->Address }}" {{ $dn->Consignee == $c->Supplier_ID ? 'selected' : '' }}>{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Consignee_Address">Consignee Address</label>
                                <textarea name="Consignee_Address" id="Consignee_Address" rows="2" class="text-black form-control" placeholder="Alamat Consignee">{{ $consigneeAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Supplier_ISCC_Ref">Supplier ISCC Reference</label>
                                <input type="text" name="Supplier_ISCC_Ref" id="Supplier_ISCC_Ref" value="{{ $dn->Supplier_ISCC_Ref }}" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Contract_No">Contract Number</label>
                                <input type="text" name="Contract_No" id="Contract_No" value="{{ $dn->Contract_No }}" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Product">Product</label>
                                <input type="text" name="Product" id="Product" value="{{ $dn->Product }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Total_Tonnage">Total Tonnage Delivered (MT)</label>
                                <input type="number" step="0.001" name="Total_Tonnage" id="Total_Tonnage" value="{{ $dn->Total_Tonnage }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Country_Of_Origin">Country of Origin</label>
                                <input type="text" name="Country_Of_Origin" id="Country_Of_Origin" value="{{ $dn->Country_Of_Origin }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="GHG_Value">GHG Value</label>
                                <textarea name="GHG_Value" id="GHG_Value" rows="2" class="form-control shadow-sm">{{ $dn->GHG_Value }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="Note">Note</label>
                                <textarea name="Note" id="Note" rows="2" class="form-control shadow-sm">{{ $dn->Note }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <input type="button" id="UpdateDN" name="UpdateDN" value="Update" class="btn btn-success mt-2 mr-2" />
            </div>
        </div>
    </div>
    <div id="DivProcess"></div>
</div>

<script>
    var myCalendarDNDtl;
    myCalendarDNDtl = new dhtmlXCalendarObject(["Delivery_Date"]);
    myCalendarDNDtl.setDateFormat("%Y-%m-%d");

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

    $('#UpdateDN').click(function () {
        var inputValues = [];
        $('#ContentPagesDN input,#ContentPagesDN select,#ContentPagesDN textarea').each(function () {
            var type = $(this).attr("type");
            if ((type == "checkbox" || type == "radio") && this.checked) {
                inputValues.push($(this).attr("name") + "=" + $(this).val());
            } else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent($(this).val()));
            } else if ($(this).is("textarea")) {
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent($(this).val()));
            }
        });

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "cns-export-delivery-note-detail-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: inputValues.join('&'),
            success: function (data) {
                if (data.indexOf("success") !== -1) {
                    alert('Delivery Note berhasil diupdate!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                } else {
                    alert('Gagal mengupdate Delivery Note!');
                }
            },
            error: function () {
                alert("Error Internal! Silakan coba lagi.");
            }
        });
    });
</script>
