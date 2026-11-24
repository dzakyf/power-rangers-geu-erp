<div class="mt-3">
    <div class="cns-section-header">SHIPPING INSTRUCTIONS</div>
    <div class="card cns-card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Contract_ID">Kontrak Buyer</label></strong>
                                <select name="Contract_ID" id="Contract_ID" class=" text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Kontrak --</option>
                                    @foreach ($contracts as $contract)
                                        <option value="{{ $contract->ID }}"
                                            data-contract-no="{{ $contract->Contract_No }}"
                                            data-delivery-terms="{{ $contract->Incoterms }}"
                                            data-consignee="{{ $contract->Consignee_Code }}">
                                            {{ $contract->Contract_No }} - {{ $contract->Buyer_Name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="SI_No">SI No</label></strong>
                                <input type="text" name="SI_No" id="SI_No" value="{{ $SINo }}" class="text-black form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="SI_Date">SI Date</label></strong>
                                <input type="date" name="SI_Date" id="SI_Date" value="{{ date('Y-m-d') }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <!-- <div class="col-md-6 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Contract_No">Contract No</label></strong>
                                <input type="text" name="Contract_No" id="Contract_No" value="" class="text-black form-control shadow-sm" required />
                            </div>
                        </div> -->
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Delivery_Terms">Delivery Terms</label></strong>
                                <textarea name="Delivery_Terms" id="Delivery_Terms" value="" class="text-black form-control shadow-sm" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Shipper">Shipper</label></strong>
                                <select name="Shipper" id="Shipper" class="text-black form-control" required>
                                    <option value="">-- Pilih Shipper --</option>
                                    @foreach ($companies as $shipper)
                                        <option value="{{ $shipper->Supplier_ID }}" data-address="{{ $shipper->Address }}">{{ $shipper->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><label class="font-bold" for="Shipper_Address">Shipper Address</label></strong>
                                <textarea name="Shipper_Address" id="Shipper_Address" rows="4" class="text-black form-control" placeholder="Alamat Shipper"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Consignee">Consignee</label></strong>
                                <select name="Consignee" id="Consignee" class="text-black form-control" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $consignee)
                                        <option value="{{ $consignee->Supplier_ID }}" data-address="{{ $consignee->Address }}">{{ $consignee->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><label class="font-bold" for="Consignee_Address">Consignee Address</label></strong>
                                <textarea name="Consignee_Address" id="Consignee_Address" rows="4" class="text-black form-control" placeholder="Alamat Consignee" value=""></textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Notify">Notify Party</label></strong>
                                <select name="Notify" id="Notify" class="text-black form-control" required>
                                    <option value="">-- Pilih Notify --</option>
                                    @foreach ($companies as $notify)
                                        <option value="{{ $notify->Supplier_ID }}" data-address="{{ $notify->Address }}">{{ $notify->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><label class="font-bold" for="Notify_Address">Notify Party Address</label></strong>
                                <textarea name="Notify_Address" id="Notify_Address" rows="4" class="text-black form-control" placeholder="Alamat Notify Party"></textarea>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Destination_Port">Destination Port</label></strong>
                                <input type="text" id="Destination_Port" name="Destination_Port" class="text-black form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="PEB_Date">PEB Date</label></strong>
                                <input type="date" name="PEB_Date" id="PEB_Date" value="" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="PEB_No">PEB No</label></strong>
                                <input type="text" name="PEB_No" id="PEB_No" value="" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="BL_No">B/L No</label></strong>
                                <input type="text" name="BL_No" id="BL_No" value="" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Product_Name">Product Name</label></strong>
                                <input type="text" name="Product_Name" id="Product_Name" value="" class="text-black form-control" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="HS_Code">HS Code</label></strong>
                                <select name="HS_Code" id="HS_Code" class="text-black form-control shadow-sm">
                                    <option value="">-- Pilih HS Code --</option>
                                    <option value="15180090">15180090</option>
                                    <option value="15119020">15119020</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <strong><label class="font-bold" for="Remarks">Remarks</label></strong>
                                <textarea name="Remarks" id="Remarks" rows="3" class="text-black form-control shadow-sm" placeholder="Remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="divtom">
                <input type="button" id="SubmitCustDetail" name="SubmitCustDetail" value="Create Draft SI" class="btn btn-success mt-2 mr-2" />
            </div>
        </div>
    </div>
    <div id="DivProcess"></div>
</div>

<script>
    function initShipmentInstructionSelect2(selector) {
        $(selector).each(function() {
            if (!$(this).is('select') || $(this).hasClass('select2-hidden-accessible')) {
                return;
            }

            let placeholder = $(this).find('option[value=""]').first().text() || 'Cari...';

            $(this).select2({
                width: '100%',
                placeholder: placeholder,
                allowClear: true
            });
        });
    }

    function updateAddressFromSelectedCompany(selectSelector, addressSelector) {
        var selectedOpt = $(selectSelector).find('option:selected');
        var addr = selectedOpt.data('address');
        if (typeof addr !== 'undefined') {
            $(addressSelector).val(addr || '');
        }
    }

    $('#Shipper').on('change', function() {
        updateAddressFromSelectedCompany('#Shipper', '#Shipper_Address');
    });

    $('#Consignee').on('change', function() {
        updateAddressFromSelectedCompany('#Consignee', '#Consignee_Address');
    });

    $('#Notify').on('change', function() {
        updateAddressFromSelectedCompany('#Notify', '#Notify_Address');
    });

    function applyContractToSiForm() {
        let selected = $('#Contract_ID').find('option:selected');

        $('#Contract_No').val(selected.data('contract-no') || '');
        $('#Delivery_Terms').val(selected.data('delivery-terms') || '');
        $('#Consignee').val(selected.data('consignee') || '').trigger('change').trigger('change.select2');
    }

    $(function() {
        initShipmentInstructionSelect2('#Contract_ID, #Shipper, #Consignee, #Notify, #Destination_Port, #HS_Code');
    });

    $('#Contract_ID').on('change', function() {
        applyContractToSiForm();
    });

    $('#SI_Date').on('change', function() {
        $.ajax({
            url: "cns-export-shipping-instruction-new-get-si-no",
            type: "POST",
            data: {_token: "{{ csrf_token() }}", date: $(this).val()},
            success: function(res) {
                $('#SI_No').val(res);
            }
        });
    });

    $('#SubmitCustDetail').click(function() {
        let gettingVars = getAllValues();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "cns-export-shipping-instruction-new-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: gettingVars,
            success: function(data) {
                if (data.status === 'success') {
                    swal('Berhasil!', 'Draft Shipping Instructions berhasil dibuat.', 'success');
                    if (typeof refreshdata === 'function') {
                        refreshdata();
                    }
                    $('#divlistOpportunity').html('');
                } else {
                    swal('Error', 'Draft Shipping Instructions gagal dibuat.', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    swal('Perhatian', Object.values(xhr.responseJSON.errors).flat().join('\n'), 'warning');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    swal('Error', xhr.responseJSON.message, 'error');
                } else {
                    swal('Error', 'Error Internal! Please Try again!', 'error');
                }
            }
        });
    });

    function getAllValues() {
        var inputValues = [];
        $('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
            var type = $(this).attr("type");
            if ((type == "checkbox" || type == "radio") && this.checked) {
                inputValues.push($(this).attr("name") + "=" + $(this).val());
            } else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent($(this).val() ?? ''));
            } else if ($(this).is("textarea")) {
                inputValues.push($(this).attr("id") + "=" + encodeURIComponent($(this).val() ?? ''));
            }
        })
        return inputValues.join('&');
    }
</script>
