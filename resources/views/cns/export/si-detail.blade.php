<?php
    $containerIds = $containers->pluck('ID')->map(function ($id) {
        return (string) $id;
    })->all();
    $assignedIds = array_values(array_intersect($containerIds, array_map('strval', $assigned)));
    $slotCount = max((int) $si->Qty1, $containers->count());
    $companiesKeyed = $companies->keyBy('Supplier_ID');
    $shipperAddr = !empty($si->Shipper_Address) ? $si->Shipper_Address : ($companiesKeyed->get($si->Shipper)->Address ?? '');
    $consigneeAddr = !empty($si->Consignee_Address) ? $si->Consignee_Address : ($companiesKeyed->get($si->Consignee)->Address ?? '');
    $notifyAddr = !empty($si->Notify_Address) ? $si->Notify_Address : ($companiesKeyed->get($si->Notify)->Address ?? '');
?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SHIPPING INSTRUCTIONS</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                    <input type="hidden" name="id" id="id" value="{{ $si->id }}" />
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="Order_No">Order No / Booking Confirmation</label></strong>
                                <select name="Order_No" id="Order_No" class="text-black form-control shadow-sm">
                                    <option value="">-- Pilih Order --</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->Order_No }}" {{ (string) $si->Order_No === (string) $order->Order_No ? 'selected' : '' }}>
                                            {{ $order->Order_No }} @if($order->Vessel)- {{ $order->Vessel }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih order untuk memuat container dan jadwal.</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="SI_No">SI No</label></strong>
                                <input type="text" name="SI_No" id="SI_No" value="{{ $si->SI_No }}" class="text-black form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="SI_Date">SI Date</label></strong>
                                <input type="date" name="SI_Date" id="SI_Date" value="{{ $si->SI_Date ? date('Y-m-d', strtotime($si->SI_Date)) : '' }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <!-- <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="Contract_No">Contract No</label></strong>
                                <input type="text" name="Contract_No" id="Contract_No" value="{{ $si->Contract_No }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div> -->
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <strong><label for="Delivery_Terms">Delivery Terms</label></strong>
                                <input type="text" name="Delivery_Terms" id="Delivery_Terms" value="{{ $si->Delivery_Terms }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <strong><label for="Voyage">Voyage / Vessel</label></strong>
                                <input type="text" name="Voyage" id="Voyage" value="{{ $si->Voyage }}" class="text-black form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="Shipper">Shipper</label></strong>
                                <select name="Shipper" id="Shipper" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Shipper --</option>
                                    @foreach ($companies as $shipper)
                                        <option value="{{ $shipper->Supplier_ID }}" data-address="{{ $shipper->Address }}" {{ $si->Shipper == $shipper->Supplier_ID ? 'selected' : '' }}>{{ $shipper->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><label for="Shipper_Address">Shipper Address</label></strong>
                                <textarea name="Shipper_Address" id="Shipper_Address" rows="4" class="text-black form-control" placeholder="Alamat Shipper">{{ $shipperAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="Consignee">Consignee</label></strong>
                                <select name="Consignee" id="Consignee" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $consignee)
                                        <option value="{{ $consignee->Supplier_ID }}" data-address="{{ $consignee->Address }}" {{ $si->Consignee == $consignee->Supplier_ID ? 'selected' : '' }}>{{ $consignee->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><label for="Consignee_Address">Consignee Address</label></strong>
                                <textarea name="Consignee_Address" id="Consignee_Address" rows="4" class="text-black form-control" placeholder="Alamat Consignee">{{ $consigneeAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="Notify">Notify Party</label></strong>
                                <select name="Notify" id="Notify" class="text-black form-control shadow-sm" required>
                                    <option value="">-- Pilih Notify --</option>
                                    @foreach ($companies as $notify)
                                        <option value="{{ $notify->Supplier_ID }}" data-address="{{ $notify->Address }}" {{ $si->Notify == $notify->Supplier_ID ? 'selected' : '' }}>{{ $notify->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><label for="Notify_Address">Notify Party Address</label></strong>
                                <textarea name="Notify_Address" id="Notify_Address" rows="4" class="text-black form-control" placeholder="Alamat Notify Party">{{ $notifyAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="Destination_Port">Destination Port</label></strong>
                                <input type="text" id="Destination_Port" name="Destination_Port" class="text-black form-control" value="{{ $si->Destination_Port }}" />
                            </div>
                        </div>
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                <strong><label for="Qty1">Qty</label></strong>
                                <div class="row">
                                    <div class="col">
                                        <select name="Qty1" id="Qty1" class="text-black form-control shadow-sm" required>
                                            <option value="">-- Pilih Qty --</option>
                                            @foreach (range(1, 1000) as $item)
                                                <option value="{{ $item }}" {{ (string) $item === (string) $si->Qty1 ? 'selected' : '' }}>{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <select name="Qty2" id="Qty2" class="text-black form-control shadow-sm" required>
                                            <option value="">-- Pilih Qty --</option>
                                            <option value="20 FT" {{ $si->Qty2 == '20 FT' ? 'selected' : '' }}>20 FT</option>
                                            <option value="20 ISOTANK" {{ $si->Qty2 == '20 ISOTANK' ? 'selected' : '' }}>20 ISOTANK</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong><label for="ETD">ETD</label></strong>
                                        <input type="date" name="ETD" id="ETD" value="{{ $si->ETD ? date('Y-m-d', strtotime($si->ETD)) : '' }}" class="text-black form-control shadow-sm bg-white" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong><label for="ETA">ETA</label></strong>
                                        <input type="date" name="ETA" id="ETA" value="{{ $si->ETA ? date('Y-m-d', strtotime($si->ETA)) : '' }}" class="text-black form-control shadow-sm bg-white" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong><label for="Stuffing">Stuffing</label></strong>
                                        <input type="date" name="Stuffing" id="Stuffing" value="{{ $si->Stuffing ? date('Y-m-d', strtotime($si->Stuffing)) : '' }}" class="text-black form-control shadow-sm bg-white" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="PEB_No">PEB No</label></strong>
                                <input type="text" name="PEB_No" id="PEB_No" value="{{ $si->PEB_No }}" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="PEB_Date">PEB Date</label></strong>
                                <input type="date" name="PEB_Date" id="PEB_Date" value="{{ $si->PEB_Date ? date('Y-m-d', strtotime($si->PEB_Date)) : '' }}" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="BL_No">B/L No</label></strong>
                                <input type="text" name="BL_No" id="BL_No" value="{{ $si->BL_No }}" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="POL">Port of Loading</label></strong>
                                <input type="text" name="POL" id="POL" value="{{ $si->POL }}" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="POD">Port of Discharge</label></strong>
                                <input type="text" name="POD" id="POD" value="{{ $si->POD }}" class="text-black form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="Product_Name">Product Name</label></strong>
                                <input type="text" name="Product_Name" id="Product_Name" value="{{ $si->Product_Name }}" class="text-black form-control" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <strong><label for="HS_Code">HS Code</label></strong>
                                <select name="HS_Code" id="HS_Code" class="text-black form-control shadow-sm">
                                    <option value="">-- Pilih HS Code --</option>
                                    <option value="15180090" {{ $si->HS_Code == '15180090' ? 'selected' : '' }}>15180090</option>
                                    <option value="15119020" {{ $si->HS_Code == '15119020' ? 'selected' : '' }}>15119020</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 col-12">
                            <div class="row" id="containerList">
                                @for ($i = 1; $i <= $slotCount; $i++)
                                    @php
                                        $preselect = $assignedIds[$i - 1] ?? ($containerIds[$i - 1] ?? null);
                                    @endphp
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <strong><label for="Container{{ $i }}">Container {{ $i }}</label></strong>
                                            <select name="Container{{ $i }}" id="Container{{ $i }}" class="text-black form-control shadow-sm">
                                                <option value="">-- Pilih Container --</option>
                                                @foreach ($containers as $container)
                                                    <option value="{{ $container->ID }}" {{ (string) $preselect === (string) $container->ID ? 'selected' : '' }}>{{ $container->Container }} ({{ $container->Seal }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="divtom">
                <input type="button" id="SubmitCustDetail" name="SubmitCustDetail" value="Update SI" class="btn btn-success mt-2 mr-2" />
            </div>
        </div>
    </div>
    <div id="DivProcess"></div>
</div>

<script>
    var assignedContainerIds = @json($assignedIds);

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

    function renderSiContainers(containers, qty) {
        $('#containerList').html('');

        let options = '<option value="">-- Pilih Container --</option>';
        containers.forEach(function(container) {
            options += `<option value="${container.ID}">${container.Container} (${container.Seal})</option>`;
        });

        let counter = 1;
        while (counter <= qty) {
            $('#containerList').append(`
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <strong><label for="Container${counter}">Container ${counter}</label></strong>
                        <select name="Container${counter}" id="Container${counter}" class="text-black form-control shadow-sm">
                            ${options}
                        </select>
                    </div>
                </div>
            `);
            counter++;
        }

        for (let i = 1; i < counter; i++) {
            let assigned = assignedContainerIds[i - 1];
            let hasAssigned = assigned && containers.some(function(container) {
                return String(container.ID) === String(assigned);
            });

            if (hasAssigned) {
                $('#Container' + i).val(assigned);
            } else if (containers[i - 1]) {
                $('#Container' + i).val(containers[i - 1].ID);
            }
        }

        initShipmentInstructionSelect2('#containerList select');
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

    $(function() {
        initShipmentInstructionSelect2('#Order_No, #Qty1, #Qty2, #Shipper, #Consignee, #Notify, #Destination_Port, #HS_Code');
        initShipmentInstructionSelect2('#containerList select');
    });

    $('#Order_No').on('change', function() {
        let orderNo = $(this).val();

        if (!orderNo) {
            return;
        }

        $.blockUI();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "cns-export-shipping-instruction-detail-get-order",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                Order_No: orderNo,
                Contract_ID: "{{ $si->Contract_ID }}"
            },
            success: function(response) {
                assignedContainerIds = [];
                $('#ETA').val(response.ETA || '');
                $('#ETD').val(response.ETD || '');
                $('#Stuffing').val(response.Stuffing || '');
                $('#POL').val(response.POL || '');
                $('#POD').val(response.POD || '');
                $('#Voyage').val(response.Vessel || '');
                $('#Qty1').val(response.Qty1).trigger('change.select2');
                $('#Qty2').val(response.Qty2).trigger('change.select2');
                renderSiContainers(response.orderDetails || [], parseInt(response.Qty1, 10) || 0);
                $.unblockUI();
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.error) || 'Error fetching order details. Please try again.');
                $.unblockUI();
            }
        });
    });

    $('#SubmitCustDetail').click(function() {
        let gettingVars = getAllValues();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "cns-export-shipping-instruction-detail-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: gettingVars,
            success: function(data) {
                if (data.success) {
                    alert('Shipping Instruction berhasil diperbarui. Status: ' + data.status);
                    if (typeof refreshdata === 'function') {
                        refreshdata();
                    }
                    $('#divlistOpportunity').html('');
                } else {
                    alert('Shipping Instruction gagal diperbarui.');
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
