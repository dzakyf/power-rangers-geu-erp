<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? '';
$companiesKeyed = collect($companies)->keyBy('Supplier_ID');
$consigneeAddr = !empty($dn->Consignee_Address) ? $dn->Consignee_Address : ($companiesKeyed->get($dn->Consignee)->Address ?? '');

?>
<div class="mt-3">
    <div class="cns-section-header">DETAIL DELIVERY NOTE</div>
    <div class="card cns-card">
        <div class="card-body">
            <div id="ContentPagesDN">
                <div class="FormInput_Form_Content">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="DN_No">DN No</label>
                                <input type="text" name="DN_No" id="DN_No" value="{{ $dn->DN_No }}" class="text-black form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Shipper">Entitas Penerbit</label>
                                <select name="Shipper" id="Shipper" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Entitas Penerbit --</option>
                                    @foreach (($issuers ?? $companies) as $c)
                                        <option value="{{ $c->Supplier_ID }}" {{ ($dn->Shipper ?? '') == $c->Supplier_ID ? 'selected' : '' }}>{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Delivery_Date">Date of Delivery / Issue</label>
                                <input type="text" name="Delivery_Date" id="Delivery_Date" value="{{ $dn->Delivery_Date }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="BC_No">Booking Confirmation (BC No)</label>
                                @php
                                    $bcCollection = collect($bookingConfirmations ?? []);
                                    $hasCurrentBc = !empty($dn->BC_No) && $bcCollection->contains('BC_No', $dn->BC_No);
                                @endphp
                                <select name="BC_No" id="BC_No" class="text-black form-control shadow-sm">
                                    <option value="">-- Pilih BC No --</option>
                                    @if (!empty($dn->BC_No) && !$hasCurrentBc)
                                        <option value="{{ $dn->BC_No }}" selected>{{ $dn->BC_No }}</option>
                                    @endif
                                    @foreach ($bcCollection as $bc)
                                        <option value="{{ $bc->BC_No }}"
                                            data-contract="{{ $bc->Contract_No ?? '' }}"
                                            data-consignee="{{ $bc->Consignee_Code ?? '' }}"
                                            data-consignee-address="{{ $bc->Buyer_Address ?: ($bc->Supplier_Address ?? '') }}"
                                            data-tonnage="{{ $bc->Quantity_MT ?? '' }}"
                                            {{ ($dn->BC_No ?? '') == $bc->BC_No ? 'selected' : '' }}>
                                            {{ $bc->BC_No }}{{ !empty($bc->Contract_No) ? ' - ' . $bc->Contract_No : '' }}{{ !empty($bc->Buyer_Name) ? ' (' . $bc->Buyer_Name . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Contract_No">Contract Number</label>
                                @php
                                    $contractCollection = collect($contracts ?? []);
                                    $hasCurrentContract = !empty($dn->Contract_No) && $contractCollection->contains('Contract_No', $dn->Contract_No);
                                @endphp
                                <select name="Contract_No" id="Contract_No" class="text-black form-control shadow-sm">
                                    <option value="">-- Pilih Contract Number --</option>
                                    @if (!empty($dn->Contract_No) && !$hasCurrentContract)
                                        <option value="{{ $dn->Contract_No }}" selected>{{ $dn->Contract_No }}</option>
                                    @endif
                                    @foreach ($contractCollection as $contract)
                                        <option value="{{ $contract->Contract_No }}"
                                            data-consignee="{{ $contract->Consignee_Code ?? '' }}"
                                            data-consignee-address="{{ $contract->Buyer_Address ?: ($contract->Supplier_Address ?? '') }}"
                                            data-tonnage="{{ $contract->Quantity_MT ?? '' }}"
                                            {{ ($dn->Contract_No ?? '') == $contract->Contract_No ? 'selected' : '' }}>
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
                                        <option value="{{ $c->Supplier_ID }}" data-address="{{ $c->Address }}" {{ $dn->Consignee == $c->Supplier_ID ? 'selected' : '' }}>{{ $c->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Supplier_ISCC_Ref">Supplier ISCC Reference</label>
                                <input type="text" name="Supplier_ISCC_Ref" id="Supplier_ISCC_Ref" value="{{ $dn->Supplier_ISCC_Ref }}" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Consignee_Address">Consignee Address</label>
                                <textarea name="Consignee_Address" id="Consignee_Address" rows="2" class="text-black text-black form-control" placeholder="Alamat Consignee">{{ $consigneeAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Product">Product</label>
                                <input type="text" name="Product" id="Product" value="{{ $dn->Product }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Total_Tonnage">Total Tonnage Delivered (MT)</label>
                                <input type="text" inputmode="decimal" name="Total_Tonnage" id="Total_Tonnage" value="{{ number_format((float) $dn->Total_Tonnage, 2, '.', ',') }}" class="text-black form-control shadow-sm cns-decimal" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Country_Of_Origin">Country of Origin</label>
                                <input type="text" name="Country_Of_Origin" id="Country_Of_Origin" value="{{ $dn->Country_Of_Origin }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="GHG_Value">GHG Value</label>
                                <textarea name="GHG_Value" id="GHG_Value" rows="2" class="text-black form-control shadow-sm">{{ $dn->GHG_Value }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="signatory_key">Approver / Penandatangan</label>
                                <select name="signatory_key" id="signatory_key" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Approver --</option>
                                    @foreach (($approvers ?? []) as $appr)
                                        <option value="{{ $appr->id }}" data-position="{{ $appr->Position ?? '' }}" {{ (string) ($dn->signatory_key ?? '') === (string) $appr->id ? 'selected' : '' }}>
                                            {{ $appr->Name }}{{ !empty($appr->Position) ? ' (' . $appr->Position . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="signatory_title">Jabatan Penandatangan</label>
                                <input type="text" name="signatory_title" id="signatory_title" value="{{ $dn->signatory_title ?? '' }}" class="text-black form-control shadow-sm" placeholder="Otomatis dari jabatan approver" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="Note">Note</label>
                                <textarea name="Note" id="Note" rows="2" class="text-black form-control shadow-sm">{{ $dn->Note }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                @if (($dn->Status ?? '') === 'APPROVED')
                    <span class="badge rounded-pill cns-status-badge bg-success mt-2">APPROVED</span>
                @else
                    <input type="button" id="UpdateDN" name="UpdateDN" value="Update" class="btn btn-success mt-2 mr-2" />
                @endif
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

    function applyBcToDnForm() {
        var selected = $('#BC_No').find('option:selected');
        if (!selected.val()) {
            return;
        }
        var contractNo = selected.data('contract') || '';
        var consignee = selected.data('consignee') || '';
        var consigneeAddress = selected.data('consignee-address') || '';
        var tonnage = selected.data('tonnage');

        if (contractNo) {
            if ($('#Contract_No option[value="' + contractNo + '"]').length === 0) {
                $('#Contract_No').append($('<option>', { value: contractNo, text: contractNo }));
            }
            $('#Contract_No').val(contractNo).trigger('change.select2');
        }
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
        $('#BC_No').select2({
            width: '100%',
            placeholder: '-- Pilih BC No --',
            allowClear: true
        });
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

    $('#BC_No').on('change', function () {
        applyBcToDnForm();
    });

    $('#Contract_No').on('change', function () {
        applyContractToDnForm();
    });

    $('#UpdateDN').click(function () {
        var $btn = $(this);
        if ($btn.prop('disabled')) {
            return;
        }
        $btn.prop('disabled', true);

        var inputValues = [];
        $('#ContentPagesDN input,#ContentPagesDN select,#ContentPagesDN textarea').each(function () {
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

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "cns-export-delivery-note-detail-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: inputValues.join('&'),
            success: function (data) {
                if (typeof data === 'string' && data.indexOf("success") !== -1) {
                    swal('Berhasil!', 'Delivery Note berhasil diupdate!', 'success');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                } else {
                    $btn.prop('disabled', false);
                    swal('Error', (data && data.message) ? data.message : 'Gagal mengupdate Delivery Note!', 'error');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error Internal! Silakan coba lagi.';
                swal('Error', msg, 'error');
            }
        });
    });
</script>
