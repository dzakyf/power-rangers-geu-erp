<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SHIPMENT INSTRUCTION</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Contract_ID">Kontrak Buyer</label>
                                <select name="Contract_ID" id="Contract_ID" class="form-control shadow-sm" required>
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
                                <label for="SI_No">SI No</label>
                                <input type="text" name="SI_No" id="SI_No" value="{{ $SINo }}" class="form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="SI_Date">SI Date</label>
                                <input type="date" name="SI_Date" id="SI_Date" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Contract_No">Contract No</label>
                                <input type="text" name="Contract_No" id="Contract_No" value="" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Consignee">Consignee</label>
                                <select name="Consignee" id="Consignee" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $consignee)
                                        <option value="{{ $consignee->Supplier_ID }}">{{ $consignee->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Delivery_Terms">Delivery Terms</label>
                                <input type="text" name="Delivery_Terms" id="Delivery_Terms" value="" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Shipper">Shipper</label>
                                <select name="Shipper" id="Shipper" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Shipper --</option>
                                    @foreach ($companies as $shipper)
                                        <option value="{{ $shipper->Supplier_ID }}">{{ $shipper->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Notify">Notify Party</label>
                                <select name="Notify" id="Notify" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Notify --</option>
                                    @foreach ($companies as $notify)
                                        <option value="{{ $notify->Supplier_ID }}">{{ $notify->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Destination_Letter">Destination Letter</label>
                                <select name="Destination_Letter" id="Destination_Letter" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Destination Letter --</option>
                                    @foreach ($companies as $destinationLetter)
                                        <option value="{{ $destinationLetter->Supplier_ID }}">{{ $destinationLetter->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="PEB_Date">PEB Date</label>
                                <input type="date" name="PEB_Date" id="PEB_Date" value="" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="PEB_No">PEB No</label>
                                <input type="text" name="PEB_No" id="PEB_No" value="" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="BL_No">B/L No</label>
                                <input type="text" name="BL_No" id="BL_No" value="" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Product_Name">Product Name</label>
                                <select name="Product_Name" id="Product_Name" class="form-control shadow-sm">
                                    <option value="">-- Pilih Product --</option>
                                    @foreach ($masProducts as $item)
                                        <option value="{{ $item->description }}">{{ $item->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="HS_Code">HS Code</label>
                                <select name="HS_Code" id="HS_Code" class="form-control shadow-sm">
                                    <option value="">-- Pilih HS Code --</option>
                                    <option value="15180090">15180090</option>
                                    <option value="15119020">15119020</option>
                                </select>
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
            if ($(this).hasClass('select2-hidden-accessible')) {
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

    function applyContractToSiForm() {
        let selected = $('#Contract_ID').find('option:selected');

        $('#Contract_No').val(selected.data('contract-no') || '');
        $('#Delivery_Terms').val(selected.data('delivery-terms') || '');
        $('#Consignee').val(selected.data('consignee') || '').trigger('change.select2');
    }

    $(function() {
        initShipmentInstructionSelect2('#Contract_ID, #Shipper, #Consignee, #Notify, #Destination_Letter, #Product_Name, #HS_Code');
    });

    $('#Contract_ID').on('change', function() {
        applyContractToSiForm();
    });

    $('#SI_Date').on('change', function() {
        $.ajax({
            url: "cns-export-shipment-instruction-new-get-si-no",
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
            url: "cns-export-shipment-instruction-new-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: gettingVars,
            success: function(data) {
                if (data.status === 'success') {
                    alert('Draft Shipping Instruction berhasil dibuat.');
                    if (typeof refreshdata === 'function') {
                        refreshdata();
                    }
                    $('#divlistOpportunity').html('');
                } else {
                    alert('Draft Shipping Instruction gagal dibuat.');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    alert(Object.values(xhr.responseJSON.errors).flat().join('\n'));
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert(xhr.responseJSON.message);
                } else {
                    alert('Error Internal! Please Try again!');
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
