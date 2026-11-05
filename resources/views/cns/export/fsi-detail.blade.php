<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$companiesKeyed = $companies->keyBy('Supplier_ID');
$shipperAddr = !empty($shipment->Shipper_Address) ? $shipment->Shipper_Address : ($companiesKeyed->get($shipment->Shipper)->Address ?? '');
$consigneeAddr = !empty($shipment->Consignee_Address) ? $shipment->Consignee_Address : ($companiesKeyed->get($shipment->Consignee)->Address ?? '');
$notifyAddr = !empty($shipment->Notify_Address) ? $shipment->Notify_Address : ($companiesKeyed->get($shipment->Notify)->Address ?? '');
?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW FINAL SHIPMENT INSTRUCTION</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="SI_No">SI No</label>
                                <select name="SI_No" id="SI_No" class="form-control bg-white" readonly>
                                    <option value="{{ $shipment->SI_No }}">{{ $shipment->SI_No }}</option>
                                </select>
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="SI_Date">Final SI Date</label>
                                <input name="SI_Date" id="SI_Date" class="form-control bg-white" value="{{ $shipment->Final_SI_Date ?? date('Y-m-d') }}" readonly />
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="Order_No">Order No</label>
                                <input type="text" name="Order_No" id="Order_No" value="{{ $shipment->Order_No ?? '' }}" class="form-control bg-white" readonly />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Qty1">Qty</label>
								<div class="row">
									<div class="col-6">
										<select name="Qty1" id="Qty1" class="form-control bg-white" readonly>
											<option value="">-- Pilih Qty --</option>
											@foreach (range(1,1000) as $item)
											<option value="{{$item}}" {{ $shipment->Qty1 == $item ? 'selected' : '' }}>{{$item}}</option>
											@endforeach
										</select>
									</div>
									<div class="col-6">
										<select name="Qty2" id="Qty2" class="form-control bg-white" readonly>
											<option value="">-- Pilih Qty --</option>
											<option value="20 FT" {{ $shipment->Qty2 == '20 FT' ? 'selected' : '' }}>20 FT</option>
											<option value="20 ISOTANK" {{ $shipment->Qty2 == '20 ISOTANK' ? 'selected' : '' }}>20 ISOTANK</option>
										</select>
									</div>
								</div>
							</div>
						</div>
                        {{-- <div class="col-12">
                            <div class="form-group">
                                <label for="Jam">Jam</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" name="Hour_From" id="Hour_From" value="" class="form-control bg-white" readonly />
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="Hour_To" id="Hour_To" value="" class="form-control bg-white" readonly />
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        <input type="hidden" name="id" id="id" value="{{ $shipment->id }}" />
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Shipper">Shipper</label>
                                <select name="Shipper" id="Shipper" class="form-control">
                                    <option value="">-- Pilih Shipper --</option>
                                    @foreach ($companies as $shipper)
                                        <option value="{{ $shipper->Supplier_ID }}" data-address="{{ $shipper->Address }}" {{ $shipment->Shipper == $shipper->Supplier_ID ? 'selected' : '' }}>{{ $shipper->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Shipper_Address">Shipper Address</label>
                                <textarea name="Shipper_Address" id="Shipper_Address" rows="2" class="form-control" placeholder="Alamat Shipper">{{ $shipperAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Consignee">Consignee</label>
                                <select name="Consignee" id="Consignee" class="form-control">
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $consignee)
                                        <option value="{{ $consignee->Supplier_ID }}" data-address="{{ $consignee->Address }}" {{ $shipment->Consignee == $consignee->Supplier_ID ? 'selected' : '' }}>{{ $consignee->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Consignee_Address">Consignee Address</label>
                                <textarea name="Consignee_Address" id="Consignee_Address" rows="2" class="form-control" placeholder="Alamat Consignee">{{ $consigneeAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="Notify">Notify Party</label>
                                <select name="Notify" id="Notify" class="form-control">
                                    <option value="">-- Pilih Notify --</option>
                                    @foreach ($companies as $notify)
                                        <option value="{{ $notify->Supplier_ID }}" data-address="{{ $notify->Address }}" {{ $shipment->Notify == $notify->Supplier_ID ? 'selected' : '' }}>{{ $notify->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Notify_Address">Notify Party Address</label>
                                <textarea name="Notify_Address" id="Notify_Address" rows="2" class="form-control" placeholder="Alamat Notify Party">{{ $notifyAddr }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Destination_Port">Destination Port</label>
                                <input type="text" id="Destination_Port" name="Destination_Port" class="form-control" value="{{ $shipment->Destination_Port }}" />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Delivery_Terms">Delivery Terms</label>
                                <input type="text" name="Delivery_Terms" id="Delivery_Terms" value="{{ $shipment->Delivery_Terms }}" class="form-control bg-white" readonly />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Contract_No">Contract No</label>
                                <input type="text" name="Contract_No" id="Contract_No" value="{{ $shipment->Contract_No }}" class="form-control bg-white" readonly />
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label for="Voyage">Voyage</label>
                                <input type="text" name="Voyage" id="Voyage" value="{{ $shipment->Voyage }}" class="form-control bg-white" readonly />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label for="ETD">ETD</label>
                                        <input type="text" name="ETD" id="ETD" value="{{ $shipment->ETD }}" class="form-control bg-white" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label for="ETA">ETA</label>
                                        <input type="text" name="ETA" id="ETA" value="{{ $shipment->ETA }}" class="form-control bg-white" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label for="Stuffing">Stuffing</label>
                                        <input type="text" name="Stuffing" id="Stuffing" value="{{ $shipment->Stuffing }}" class="form-control bg-white" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="PEB_No">PEB No</label>
                                <input type="text" name="PEB_No" id="PEB_No" value="{{ $shipment->PEB_No }}" class="form-control bg-white" />
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="PEB_Date">PEB Date</label>
                                <input type="text" name="PEB_Date" id="PEB_Date" value="{{ $shipment->PEB_Date }}" class="form-control bg-white" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="BL_No">B/L No</label>
                                <input type="text" name="BL_No" id="BL_No" value="{{ $shipment->BL_No }}" class="form-control bg-white" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="POL">Port of Loading</label>
                                <input type="text" name="POL" id="POL" value="{{ $shipment->POL }}" class="form-control bg-white" />
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="POD">Port of Discharge</label>
                                <input type="text" name="POD" id="POD" value="{{ $shipment->POD }}" class="form-control bg-white" />
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Product_Name">Product Name</label>
                                <input type="text" name="Product_Name" id="Product_Name" value="{{ $shipment->Product_Name }}" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="HS_Code">HS Code</label>
                                <select name="HS_Code" id="HS_Code" class="form-control bg-white" disabled>
                                    <option value="">-- Pilih HS Code --</option>
                                    <option value="15180090" {{ $shipment->HS_Code == '15180090' ? 'selected' : '' }}>15180090</option>
                                    <option value="15119020" {{ $shipment->HS_Code == '15119020' ? 'selected' : '' }}>15119020</option>
                                </select>
                            </div>
                        </div>
                        @foreach ($shipment->orderDetails as $item)
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Container{{$loop->iteration}}">Container {{$loop->iteration}}</label>
                                <select name="Container{{$loop->iteration}}" id="Container{{$loop->iteration}}" class="form-control bg-white" disabled>
                                        <option value="{{ $item->ID }}">{{ $item->Container }} ({{ $item->Seal }})</option>
                                </select>
                            </div>
                        </div>
                        @endforeach
                        <div class="col-12">
			                {{-- <div id="ShipmentInstructionNew" style="width:auto;height:320px; background-color:white;overflow:hidden"></div> --}}
                        </div>
					</div>
				</div>
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Save Final SI" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
	// ShipmentInstructionNew = new dhtmlXGridObject('ShipmentInstructionNew');
	// ShipmentInstructionNew.setImagePath("./dhtmlx/imgs/");
	// ShipmentInstructionNew.setHeader("No, Dari Jam, Hingga Jam, Nopol, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross (Kg), Gross (MT), Qty (Kgs) Note");
	// ShipmentInstructionNew.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// // ShipmentInstructionNew.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	// ShipmentInstructionNew.setInitWidths("60,150,100,200,150,150,100,100,100,100,100,100,auto");
	// ShipmentInstructionNew.setColAlign("center,left,left,left,left,left,right,right,right,right,right,right,left");
	// ShipmentInstructionNew.setColSorting("int,str,str,str,str,str,int,int,int,int,int,int,str");
	// ShipmentInstructionNew.setColTypes("ro,ed,ed,ed,ed,ed,edn,edn,edn,edn,edn,edn,ed");
	// ShipmentInstructionNew.enableSmartRendering(true, 150);
	// ShipmentInstructionNew.setNumberFormat("0,000", 6, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 7, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 8, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 9, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 10, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 11, ".", ",");
	// // ShipmentInstructionNew.attachEvent("onRowDblClicked", ShipmentInstructionNewKlik);
	// ShipmentInstructionNew.setSkin("dhx_skyblue");
	// ShipmentInstructionNew.init();

	// function refreshdata() {
	// 	ShipmentInstructionNew.clearAndLoad('xml-cns-export-shipment-instruction-detail?Order_No=' + $('#Order_No').find('option:selected').val());

	// }

    $('#Order_No').on('change', function() {
        // refreshdata();  
        $.blockUI();
        $.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
            url: "cns-export-shipment-instruction-detail-get-order",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                Order_No: $('#Order_No').find('option:selected').val()
            },
            success: function(response) {
                $('#ETA').val(response.ETA);
                $('#ETD').val(response.ETD);
                $('#Stuffing').val(response.Stuffing);
                $('#POL').val(response.POL);
                $('#POD').val(response.POD);
                $('#Voyage').val(response.Vessel);
                $('#Qty1').val(response.Qty1);
                $('#Qty2').val(response.Qty2);
                $('#containerList').html('');
                let counter = 1;
                let string = "";
                response.orderDetails.forEach(function(container, index) {
                    string += `<option value="${container.ID}">${container.Container} (${container.Seal})</option>`;
                });
                while (counter <= response.Qty1) {
                    $('#containerList').append(`
                    <div class="col-12">
                        <div class="form-group">
                            <label for="Container${counter}">Container ${counter}</label>
                            <select name="Container${counter}" id="Container${counter}" class="form-control bg-white">
                                <option value="">-- Pilih Container --</option>
                        `+ string + `
                                    </select>
                                </div>
                            </div>
                    `);
                    counter++;
                }
                $.unblockUI();
            },
            error:function() {
                alert('Error fetching order details. Please try again.');
                $.unblockUI();
            }
        });
    });

	function refreshdata() {
		ShipmentInstruction.clearAndLoad('xml-cns-export-final-shipment-instruction?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
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

	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "cns-export-final-shipment-instruction-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					alert('Data Final SI Updated!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
				} else {
					alert('Final SI Failed to Update!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");
			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}
</script>