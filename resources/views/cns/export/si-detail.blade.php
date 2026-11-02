@php
    $containerIds = $containers->pluck('ID')->map(function ($id) {
        return (string) $id;
    })->all();
    $assignedIds = array_values(array_intersect($containerIds, array_map('strval', $assigned)));
    $slotCount = max((int) $si->Qty1, $containers->count());
@endphp
<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SHIPMENT INSTRUCTION</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Order_No">Order No / Booking Confirmation</label>
                                <select name="Order_No" id="Order_No" class="form-control shadow-sm">
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
                                <label for="SI_No">SI No</label>
                                <input type="text" name="SI_No" id="SI_No" value="{{ $si->SI_No }}" class="form-control shadow-sm" required readonly />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="SI_Date">SI Date</label>
                                <input type="date" name="SI_Date" id="SI_Date" value="{{ $si->SI_Date ? date('Y-m-d', strtotime($si->SI_Date)) : '' }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Contract_No">Contract No</label>
                                <input type="text" name="Contract_No" id="Contract_No" value="{{ $si->Contract_No }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Consignee">Consignee</label>
                                <select name="Consignee" id="Consignee" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $consignee)
                                        <option value="{{ $consignee->Supplier_ID }}" {{ $si->Consignee == $consignee->Supplier_ID ? 'selected' : '' }}>{{ $consignee->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Delivery_Terms">Delivery Terms</label>
                                <input type="text" name="Delivery_Terms" id="Delivery_Terms" value="{{ $si->Delivery_Terms }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Shipper">Shipper</label>
                                <select name="Shipper" id="Shipper" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Shipper --</option>
                                    @foreach ($companies as $shipper)
                                        <option value="{{ $shipper->Supplier_ID }}" {{ $si->Shipper == $shipper->Supplier_ID ? 'selected' : '' }}>{{ $shipper->Company_Name }}</option>
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
                                        <option value="{{ $notify->Supplier_ID }}" {{ $si->Notify == $notify->Supplier_ID ? 'selected' : '' }}>{{ $notify->Company_Name }}</option>
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
                                        <option value="{{ $destinationLetter->Supplier_ID }}" {{ $si->Destination_Letter == $destinationLetter->Supplier_ID ? 'selected' : '' }}>{{ $destinationLetter->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Voyage">Voyage / Vessel</label>
                                <input type="text" name="Voyage" id="Voyage" value="{{ $si->Voyage }}" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Qty1">Qty</label>
                                <div class="row">
                                    <div class="col">
                                        <select name="Qty1" id="Qty1" class="form-control shadow-sm" required>
                                            <option value="">-- Pilih Qty --</option>
                                            @foreach (range(1, 1000) as $item)
                                                <option value="{{ $item }}" {{ (string) $item === (string) $si->Qty1 ? 'selected' : '' }}>{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <select name="Qty2" id="Qty2" class="form-control shadow-sm" required>
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
                                        <label for="ETD">ETD</label>
                                        <input type="date" name="ETD" id="ETD" value="{{ $si->ETD ? date('Y-m-d', strtotime($si->ETD)) : '' }}" class="form-control shadow-sm bg-white" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ETA">ETA</label>
                                        <input type="date" name="ETA" id="ETA" value="{{ $si->ETA ? date('Y-m-d', strtotime($si->ETA)) : '' }}" class="form-control shadow-sm bg-white" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Stuffing">Stuffing</label>
                                        <input type="date" name="Stuffing" id="Stuffing" value="{{ $si->Stuffing ? date('Y-m-d', strtotime($si->Stuffing)) : '' }}" class="form-control shadow-sm bg-white" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="PEB_No">PEB No</label>
                                <input type="text" name="PEB_No" id="PEB_No" value="{{ $si->PEB_No }}" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="PEB_Date">PEB Date</label>
                                <input type="date" name="PEB_Date" id="PEB_Date" value="{{ $si->PEB_Date ? date('Y-m-d', strtotime($si->PEB_Date)) : '' }}" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="BL_No">B/L No</label>
                                <input type="text" name="BL_No" id="BL_No" value="{{ $si->BL_No }}" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="POL">Port of Loading</label>
                                <input type="text" name="POL" id="POL" value="{{ $si->POL }}" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="POD">Port of Discharge</label>
                                <input type="text" name="POD" id="POD" value="{{ $si->POD }}" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Product_Name">Product Name</label>
                                <select name="Product_Name" id="Product_Name" class="form-control shadow-sm">
                                    <option value="">-- Pilih Product --</option>
                                    @foreach ($masProducts as $item)
                                        <option value="{{ $item->description }}" {{ $item->description == $si->Product_Name ? 'selected' : '' }}>{{ $item->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="HS_Code">HS Code</label>
                                <select name="HS_Code" id="HS_Code" class="form-control shadow-sm">
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
                                            <label for="Container{{ $i }}">Container {{ $i }}</label>
                                            <select name="Container{{ $i }}" id="Container{{ $i }}" class="form-control shadow-sm">
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
                        <label for="Container${counter}">Container ${counter}</label>
                        <select name="Container${counter}" id="Container${counter}" class="form-control shadow-sm">
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

    $(function() {
        initShipmentInstructionSelect2('#Order_No, #Qty1, #Qty2, #Shipper, #Consignee, #Notify, #Destination_Letter, #Product_Name, #HS_Code');
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
            url: "cns-export-shipment-instruction-detail-get-order",
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
            url: "cns-export-shipment-instruction-detail-submit?_token={{ csrf_token() }}",
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
