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
$counter = DB::table("commercial_invoices")->where('Inv_Date', "LIKE", date("Y-m"). "%")->count();

$InvNo = sprintf('%03d',$counter + 1) . "/INV-GEU/" . getRomawi(date("n")) . "/" . date("Y");

?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SHIPMENT INSTRUCTION</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="Final_SI_No">Final SI No</label>
                                <select name="Final_SI_No" id="Final_SI_No" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Final SI --</option>
                                    @foreach ($sis as $si)
                                        <option value="{{ $si->Final_SI_No }}">{{ $si->Final_SI_No }}</option>
                                    @endforeach
                                </select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="Inv_No">Inv No</label>
                                <input type="text" name="Inv_No" id="Inv_No" value="{{ $InvNo }}" class="form-control shadow-sm" required readonly />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="Inv_Date">Inv Date</label>
                                <input name="Inv_Date" id="Inv_Date" class="form-control shadow-sm" value="{{ date('Y-m-d') }}" required />
							</div>
						</div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Description">Description of Cargo</label>
                                <input type="text" name="Description" id="Description" value="" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Qty">Qty (MT)</label>
                                <input type="text" name="Qty" id="Qty" value="0" onchange="calculateTotal()" class="form-control shadow-sm" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Unit_Price">Unit Price (MT)</label>
                                <input type="text" name="Unit_Price" id="Unit_Price" value="0" class="form-control shadow-sm" onchange="calculateTotal()" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Amount">Amount</label>
                                <input type="text" name="Amount" id="Amount" value="0" class="form-control shadow-sm" onchange="calculateTotal()" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Advance_Payment">Advance Payment</label>
                                <input type="text" name="Advance_Payment" id="Advance_Payment" value="0" class="form-control shadow-sm" onchange="calculateTotal()" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Other">Other</label>
                                <input type="text" name="Other" id="Other" value="0" class="form-control shadow-sm" onchange="calculateTotal()" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Total">Total</label>
                                <input type="text" name="Total" id="Total" value="0" class="form-control shadow-sm" onchange="calculateTotal()" required />
                            </div>
                        </div>
					</div>
				</div>
				{{-- STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION --}}
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
    function calculateTotal()
    {
        let qty = parseFloat($('#Qty').val());
        let unitPrice = parseFloat($('#Unit_Price').val());
        let advance = parseFloat($('#Advance_Payment').val());
        let other = parseFloat($('#Other').val());
        let subs = qty * unitPrice;

        $('#Amount').val(subs)
        $('#Subtotal').val(subs)
        $('#Total').val((subs - advance) + other);
    }
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Inv_Date"]);
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
	// 	ShipmentInstructionNew.clearAndLoad('xml-cns-export-commercial-invoice?Order_No=' + $('#Order_No').find('option:selected').val());

	// }

    $('#Final_SI_No').on('change', function() {
        // refreshdata();  
        $.blockUI();
        $.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
            url: "cns-export-commercial-invoice-get-si",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                Final_SI_No: $('#Final_SI_No').find('option:selected').val()
            },
            success: function(response) {
                $('#Qty').val(response.Netto_MT ?? 0);
                $('#Description').val(response.Product_Name ?? "");
                $('#Consignee').find('option[value="'+response.Consignee+'"]').attr('selected', true);
                $.unblockUI();
            },
            error:function() {
                alert('Error fetching si details. Please try again.');
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
			url: "cns-export-commercial-invoice-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Commercial Invoice Submitted!');

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