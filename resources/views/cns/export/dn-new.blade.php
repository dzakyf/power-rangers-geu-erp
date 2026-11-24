<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? '';

?>
<div class="mt-3">
    <div class="cns-section-header">NEW DELIVERY NOTE</div>
    <div class="card cns-card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="DN_No">DN No</label>
                                <input type="text" name="DN_No" id="DN_No" value="{{ $DnNo }}" class="text-black form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Shipper">Entitas Penerbit</label>
                                <select name="Shipper" id="Shipper" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Entitas Penerbit --</option>
                                    @foreach (($issuers ?? $companies) as $c)
                                        <option value="{{ $c->Supplier_ID }}">{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Delivery_Date">Date of Delivery / Issue</label>
                                <input type="text" name="Delivery_Date" id="Delivery_Date" value="{{ date('Y-m-d') }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Contract_No">Contract Number</label>
                                <select name="Contract_No" id="Contract_No" class="text-black form-control shadow-sm">
                                    <option value="">-- Pilih Contract Number --</option>
                                    @foreach (($contracts ?? []) as $contract)
                                        <option value="{{ $contract->Contract_No }}"
                                            data-consignee="{{ $contract->Consignee_Code ?? '' }}"
                                            data-consignee-address="{{ $contract->Buyer_Address ?: ($contract->Supplier_Address ?? '') }}"
                                            data-tonnage="{{ $contract->Quantity_MT ?? '' }}">
                                            {{ $contract->Contract_No }} - {{ $contract->Buyer_Name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Consignee">Consignee (Delivered to)</label>
                                <select name="Consignee" id="Consignee" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->Supplier_ID }}" data-address="{{ $c->Address }}">{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Supplier_ISCC_Ref">Supplier ISCC Reference</label>
                                <input type="text" name="Supplier_ISCC_Ref" id="Supplier_ISCC_Ref" value="" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Consignee_Address">Consignee Address</label>
                                <textarea name="Consignee_Address" id="Consignee_Address" rows="2" class="text-black text-black form-control" placeholder="Alamat Consignee" value=""></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Product">Product</label>
                                <input type="text" name="Product" id="Product" value="Used Cooking Oil (UCO) entirely of Vege-Oil Origin" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Total_Tonnage">Total Tonnage Delivered (MT)</label>
                                <input type="text" inputmode="decimal" name="Total_Tonnage" id="Total_Tonnage" value="0.00" class="text-black form-control shadow-sm cns-decimal" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Country_Of_Origin">Country of Origin</label>
                                <input type="text" name="Country_Of_Origin" id="Country_Of_Origin" value="Indonesia" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="GHG_Value">GHG Value</label>
                                <textarea name="GHG_Value" id="GHG_Value" rows="2" class="text-black form-control shadow-sm">Disaggregated default value used for UCO for either cultivation, Transport and/or processing</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="signatory_key">Approver / Penandatangan</label>
                                <select name="signatory_key" id="signatory_key" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Approver --</option>
                                    @foreach (($approvers ?? []) as $appr)
                                        <option value="{{ $appr->id }}" data-position="{{ $appr->Position ?? '' }}">
                                            {{ $appr->Name }}{{ !empty($appr->Position) ? ' (' . $appr->Position . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="signatory_title">Jabatan Penandatangan</label>
                                <input type="text" name="signatory_title" id="signatory_title" value="" class="text-black form-control shadow-sm" placeholder="Otomatis dari jabatan approver" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="Note">Note</label>
                                <textarea name="Note" id="Note" rows="2" class="text-black form-control shadow-sm"></textarea>
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

    function applyContractToDnForm() {
        var selected = $('#Contract_No').find('option:selected');
        if (!selected.val()) {
            return;
        }
        var consignee = selected.data('consignee') || '';
        var consigneeAddress = selected.data('consignee-address') || '';
        var tonnage = selected.data('tonnage');

        if (consignee) {
            $('#Consignee').val(consignee).trigger('change');
        }
        if (consigneeAddress) {
            $('#Consignee_Address').val(consigneeAddress);
        }
        if (tonnage !== undefined && tonnage !== '') {
            var formatted = typeof cnsFormatNumber === 'function'
                ? cnsFormatNumber(tonnage, 2)
                : parseFloat(tonnage).toFixed(2);
            $('#Total_Tonnage').val(formatted);
        }
    }

    if ($.fn.select2) {
        $('#Contract_No').select2({
            width: '100%',
            placeholder: '-- Pilih Contract Number --',
            allowClear: true
        });
        $('#signatory_key').select2({
            width: '100%',
            placeholder: '-- Pilih Approver --',
            allowClear: true
        });
    }

    $('#signatory_key').on('change', function () {
        var pos = $(this).find('option:selected').data('position') || '';
        $('#signatory_title').val(pos);
    });

    $('#Contract_No').on('change', function () {
        applyContractToDnForm();
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
                    swal('Berhasil!', 'Delivery Note berhasil disimpan!', 'success');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                } else {
                    swal('Error', 'Gagal menyimpan Delivery Note!', 'error');
                }
            },
            error: function () {
                swal('Error', 'Error Internal! Silakan coba lagi.', 'error');
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
                var val = $(this).hasClass('cns-decimal') ? cnsParseNumber($(this).val()) : $(this).val();
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent(val));
            } else if ($(this).is("textarea")) {
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent($(this).val()));
            }
        });
        return inputValues.join('&');
    }
</script>
