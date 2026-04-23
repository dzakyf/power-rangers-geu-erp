<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

function getRomawi($bln)
{
	switch ($bln) {
		case 1:
			return "I";
			break;
		case 2:
			return "II";
			break;
		case 3:
			return "III";
			break;
		case 4:
			return "IV";
			break;
		case 5:
			return "V";
			break;
		case 6:
			return "VI";
			break;
		case 7:
			return "VII";
			break;
		case 8:
			return "VIII";
			break;
		case 9:
			return "IX";
			break;
		case 10:
			return "X";
			break;
		case 11:
			return "XI";
			break;
		case 12:
			return "XII";
			break;
	}
}

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$counter = DB::table("shipment_instructions")->where('SI_Date', "LIKE", date("Y-m"). "%")->count();

$SINo = "SI/EXP/" . sprintf('%03d',$counter + 1) . "/" . getRomawi(date("n")) . "/" . date("Y");

?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SHIPMENT INSTRUCTION</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<div class="col-12">
							<div class="form-group">
								<label for="Final_SI_No">SI No</label>
                                <select name="Final_SI_No" id="Final_SI_No" class="form-control" required>
                                    <option value="">-- Pilih SI No --</option>
                                    @foreach ($sis as $si)
                                        <option value="{{ $si->Final_SI_No }}">{{ $si->Final_SI_No }}</option>
                                    @endforeach
                                </select>
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Order_No">Order No</label>
                                <input type="text" name="Order_No" id="Order_No" value="" class="form-control" required readonly />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="SI_Date">SI Date</label>
                                <input name="SI_Date" id="SI_Date" class="form-control" value="{{ date('Y-m-d') }}" required />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Qty1">Qty</label>
								<div class="row">
									<div class="col">
										<select name="Qty1" id="Qty1" class="form-control" required>
											<option value="">-- Pilih Qty --</option>
											@foreach (range(1,1000) as $item)
											<option value="{{$item}}">{{$item}}</option>
											@endforeach
										</select>
									</div>
									<div class="col">
										<select name="Qty2" id="Qty2" class="form-control" required>
											<option value="">-- Pilih Qty --</option>
											<option value="20 FT">20 FT</option>
											<option value="20 ISOTANK">20 ISOTANK</option>
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
                                        <input type="text" name="Hour_From" id="Hour_From" value="" class="form-control" required readonly />
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="Hour_To" id="Hour_To" value="" class="form-control" required readonly />
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Shipper">Shipper</label>
                                <select name="Shipper" id="Shipper" class="form-control" required>
                                    <option value="">-- Pilih Shipper --</option>
                                    @foreach ($companies as $shipper)
                                        <option value="{{ $shipper->Company_ID }}">{{ $shipper->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Consignee">Consignee</label>
                                <select name="Consignee" id="Consignee" class="form-control" required>
                                    <option value="">-- Pilih Consignee --</option>
                                    @foreach ($companies as $consignee)
                                        <option value="{{ $consignee->Company_ID }}">{{ $consignee->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Notify">Notify Party</label>
                                <select name="Notify" id="Notify" class="form-control" required>
                                    <option value="">-- Pilih Notify --</option>
                                    @foreach ($companies as $notify)
                                        <option value="{{ $notify->Company_ID }}">{{ $notify->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Destination_Letter">Destination Letter</label>
                                <select name="Destination_Letter" id="Destination_Letter" class="form-control" required>
                                    <option value="">-- Pilih Destination Letter --</option>
                                    @foreach ($companies as $destination_Letter)
                                        <option value="{{ $destination_Letter->Company_ID }}">{{ $destination_Letter->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Delivery_Terms">Delivery Terms</label>
                                <input type="text" name="Delivery_Terms" id="Delivery_Terms" value="" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Contract_No">Contract No</label>
                                <input type="text" name="Contract_No" id="Contract_No" value="" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="Voyage">Voyage</label>
                                <input type="text" name="Voyage" id="Voyage" value="" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ETD">ETD</label>
                                        <input type="text" name="ETD" id="ETD" value="{{ date("Y-m-d") }}" class="form-control bg-white" required readonly />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ETA">ETA</label>
                                        <input type="text" name="ETA" id="ETA" value="{{ date("Y-m-d") }}" class="form-control bg-white" required readonly />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Stuffing">Stuffing</label>
                                        <input type="text" name="Stuffing" id="Stuffing" value="{{ date("Y-m-d") }}" class="form-control bg-white" required readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="PEB_No">PEB No</label>
                                <input type="text" name="PEB_No" id="PEB_No" value="" class="form-control" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="PEB_Date">PEB Date</label>
                                <input type="text" name="PEB_Date" id="PEB_Date" value="{{ date("Y-m-d") }}" class="form-control" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="BL_No">B/L No</label>
                                <input type="text" name="BL_No" id="BL_No" value="" class="form-control" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="POL">Port of Loading</label>
                                <input type="text" name="POL" id="POL" value="" class="form-control" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="POD">Port of Discharge</label>
                                <input type="text" name="POD" id="POD" value="" class="form-control" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="HS_Code">HS Code</label>
                                <input type="text" name="HS_Code" id="HS_Code" value="" class="form-control" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row" id="containerList">
                                <select name="Container1" id="Container1" class="form-control">
                                    <option value="">-- Pilih Container --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
			                {{-- <div id="ShipmentInstructionNew" style="width:auto;height:320px; background-color:white;overflow:hidden"></div> --}}
                        </div>
					</div>
				</div>
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Publish" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
    
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Stuffing", "PEB_Date", "SI_Date", "ETD", "ETA"]);
	myCalendar.setDateFormat("%Y-%m-%d");

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
	// 	ShipmentInstructionNew.clearAndLoad('xml-cns-export-shipment-instruction-new?Order_No=' + $('#Order_No').find('option:selected').val());

	// }

    $('#SI_No').on('change', function() {
        // refreshdata();  
        $.blockUI();
        $.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
            url: "cns-export-shipment-instruction-new-get-order",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                SI_No: $('#SI_No').find('option:selected').val()
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
                            <select name="Container${counter}" id="Container${counter}" class="form-control">
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

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Stuffing", "PEB_Date", "ETD", "ETA", "SI_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "cns-export-shipment-instruction-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Announcement Submitted!');

					// location.href = "partner-announcement";
                    refreshdata();
                    $('#divlistOpportunity').html('')
				} else {
					alert('Announcement Failed to Submit!');
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