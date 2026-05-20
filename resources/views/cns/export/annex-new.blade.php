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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ANNEX VII</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<!-- Visible mapping inputs to match ShipmentInstructionController::newShipmentInstructionSubmit -->
						<div class="col-md-3 form-group">
							<label for="SI_No">SI No</label>
							<input type="text" id="SI_No" name="SI_No" class="form-control" value="{{ old('SI_No', $shipment->SI_No ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="Order_No">Order No</label>
							<input type="text" id="Order_No" name="Order_No" class="form-control" value="{{ old('Order_No', $shipment->Order_No ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="Qty1">Qty 1</label>
							<input type="text" id="Qty1" name="Qty1" class="form-control" value="{{ old('Qty1', $shipment->Qty1 ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="Qty2">Qty 2</label>
							<input type="text" id="Qty2" name="Qty2" class="form-control" value="{{ old('Qty2', $shipment->Qty2 ?? '') }}" />
						</div>
						<!-- Annex VII form inputs (numbered and labeled to match annex-print) -->
						<div class="col-12">
							<h5>1. Person who arranges shipment</h5>
						</div>
						<div class="col-md-6 form-group">
							<label for="Shipper">Name</label>
							<input type="text" id="Shipper" name="Shipper" class="form-control" value="{{ old('Shipper', $shipment->Shipper ?? $shipment->arranger_name ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="arranger_address">Address</label>
							<input type="text" id="arranger_address" name="arranger_address" class="form-control" value="{{ old('arranger_address', $shipment->arranger_address ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="arranger_contact">Contact person</label>
							<input type="text" id="arranger_contact" name="arranger_contact" class="form-control" value="{{ old('arranger_contact', $shipment->arranger_contact ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="arranger_tel">Tel.</label>
							<input type="text" id="arranger_tel" name="arranger_tel" class="form-control" value="{{ old('arranger_tel', $shipment->arranger_tel ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="arranger_email">E-mail</label>
							<input type="email" id="arranger_email" name="arranger_email" class="form-control" value="{{ old('arranger_email', $shipment->arranger_email ?? '') }}" />
						</div>

						<div class="col-12 mt-3">
							<h5>2. Importer / Consignee</h5>
						</div>
						<div class="col-md-6 form-group">
							<label for="Consignee">Name</label>
							<input type="text" id="Consignee" name="Consignee" class="form-control" value="{{ old('Consignee', $shipment->Consignee ?? $shipment->consignee_name ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="consignee_email">E-mail</label>
							<input type="email" id="consignee_email" name="consignee_email" class="form-control" value="{{ old('consignee_email', $shipment->consignee_email ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="consignee_telp">Tel.</label>
							<input type="text" id="consignee_telp" name="consignee_telp" class="form-control" value="{{ old('consignee_telp', $shipment->consignee_telp ?? '') }}" />
						</div>

						<div class="col-12 mt-3">
							<h5>3. Actual date of shipment / 4. Actual quantity</h5>
						</div>
						<div class="col-md-6 form-group">
							<label for="SI_Date">Actual date of shipment</label>
							<input type="date" id="SI_Date" name="SI_Date" class="form-control" value="{{ old('SI_Date', isset($shipment->SI_Date) ? 
								(is_string($shipment->SI_Date) ? $shipment->SI_Date : ($shipment->SI_Date->format('Y-m-d') ?? '')) : '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="actual_quantity">Actual quantity (kg)</label>
							<input type="text" id="actual_quantity" name="actual_quantity" class="form-control" value="{{ old('actual_quantity', $shipment->actual_quantity ?? $shipment->actual_qty ?? '') }}" />
						</div>

						<!-- SI technical fields mapped to controller columns -->
						<div class="col-12 mt-2"><h6>SI Details</h6></div>
						<div class="col-md-3 form-group">
							<label for="ETD">ETD</label>
							<input type="date" id="ETD" name="ETD" class="form-control" value="{{ old('ETD', isset($shipment->ETD) ? (is_string($shipment->ETD) ? $shipment->ETD : ($shipment->ETD->format('Y-m-d') ?? '')) : '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="ETA">ETA</label>
							<input type="date" id="ETA" name="ETA" class="form-control" value="{{ old('ETA', isset($shipment->ETA) ? (is_string($shipment->ETA) ? $shipment->ETA : ($shipment->ETA->format('Y-m-d') ?? '')) : '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="Stuffing">Stuffing</label>
							<input type="text" id="Stuffing" name="Stuffing" class="form-control" value="{{ old('Stuffing', $shipment->Stuffing ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="POL">POL</label>
							<input type="text" id="POL" name="POL" class="form-control" value="{{ old('POL', $shipment->POL ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="POD">POD</label>
							<input type="text" id="POD" name="POD" class="form-control" value="{{ old('POD', $shipment->POD ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="PEB_Date">PEB Date</label>
							<input type="date" id="PEB_Date" name="PEB_Date" class="form-control" value="{{ old('PEB_Date', isset($shipment->PEB_Date) ? (is_string($shipment->PEB_Date) ? $shipment->PEB_Date : ($shipment->PEB_Date->format('Y-m-d') ?? '')) : '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="PEB_No">PEB No</label>
							<input type="text" id="PEB_No" name="PEB_No" class="form-control" value="{{ old('PEB_No', $shipment->PEB_No ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="BL_No">BL No</label>
							<input type="text" id="BL_No" name="BL_No" class="form-control" value="{{ old('BL_No', $shipment->BL_No ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="HS_Code">HS Code</label>
							<input type="text" id="HS_Code" name="HS_Code" class="form-control" value="{{ old('HS_Code', $shipment->HS_Code ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="Contract_No">Contract No</label>
							<input type="text" id="Contract_No" name="Contract_No" class="form-control" value="{{ old('Contract_No', $shipment->Contract_No ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="Delivery_Terms">Delivery Terms</label>
							<input type="text" id="Delivery_Terms" name="Delivery_Terms" class="form-control" value="{{ old('Delivery_Terms', $shipment->Delivery_Terms ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="Voyage">Voyage</label>
							<input type="text" id="Voyage" name="Voyage" class="form-control" value="{{ old('Voyage', $shipment->Voyage ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="Destination_Letter">Destination Letter</label>
							<input type="text" id="Destination_Letter" name="Destination_Letter" class="form-control" value="{{ old('Destination_Letter', $shipment->Destination_Letter ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="Notify">Notify</label>
							<input type="text" id="Notify" name="Notify" class="form-control" value="{{ old('Notify', $shipment->Notify ?? '') }}" />
						</div>

						<div class="col-12 mt-3">
							<h5>5. Carriers (a, b, c)</h5>
						</div>
						<!-- Carrier 1 -->
						<div class="col-12"><strong>5 a) 1st Carrier</strong></div>
						<div class="col-md-6 form-group">
							<label for="carrier1_name">Name</label>
							<input type="text" id="carrier1_name" name="carrier1_name" class="form-control" value="{{ old('carrier1_name', $shipment->carrier1_name ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier1_address">Address</label>
							<input type="text" id="carrier1_address" name="carrier1_address" class="form-control" value="{{ old('carrier1_address', $shipment->carrier1_address ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier1_phone">Phone</label>
							<input type="text" id="carrier1_phone" name="carrier1_phone" class="form-control" value="{{ old('carrier1_phone', $shipment->carrier1_phone ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier1_email">E-mail</label>
							<input type="email" id="carrier1_email" name="carrier1_email" class="form-control" value="{{ old('carrier1_email', $shipment->carrier1_email ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier1_date">Date of transfer</label>
							<input type="date" id="carrier1_date" name="carrier1_date" class="form-control" value="{{ old('carrier1_date', $shipment->carrier1_date ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier1_licence">Carrier's licence</label>
							<input type="text" id="carrier1_licence" name="carrier1_licence" class="form-control" value="{{ old('carrier1_licence', $shipment->carrier1_licence ?? '') }}" />
						</div>

						<!-- Carrier 2 -->
						<div class="col-12 mt-2"><strong>5 b) 2nd Carrier</strong></div>
						<div class="col-md-6 form-group">
							<label for="carrier2_name">Name</label>
							<input type="text" id="carrier2_name" name="carrier2_name" class="form-control" value="{{ old('carrier2_name', $shipment->carrier2_name ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier2_address">Address</label>
							<input type="text" id="carrier2_address" name="carrier2_address" class="form-control" value="{{ old('carrier2_address', $shipment->carrier2_address ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier2_contact">Contact person</label>
							<input type="text" id="carrier2_contact" name="carrier2_contact" class="form-control" value="{{ old('carrier2_contact', $shipment->carrier2_contact ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier2_tel">Tel</label>
							<input type="text" id="carrier2_tel" name="carrier2_tel" class="form-control" value="{{ old('carrier2_tel', $shipment->carrier2_tel ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier2_fax">Fax</label>
							<input type="text" id="carrier2_fax" name="carrier2_fax" class="form-control" value="{{ old('carrier2_fax', $shipment->carrier2_fax ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier2_email">E-mail</label>
							<input type="email" id="carrier2_email" name="carrier2_email" class="form-control" value="{{ old('carrier2_email', $shipment->carrier2_email ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier2_date">Date of transfer</label>
							<input type="date" id="carrier2_date" name="carrier2_date" class="form-control" value="{{ old('carrier2_date', $shipment->carrier2_date ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier2_licence">Carrier's licence</label>
							<input type="text" id="carrier2_licence" name="carrier2_licence" class="form-control" value="{{ old('carrier2_licence', $shipment->carrier2_licence ?? '') }}" />
						</div>

						<!-- Carrier 3 -->
						<div class="col-12 mt-2"><strong>5 c) 3rd Carrier</strong></div>
						<div class="col-md-6 form-group">
							<label for="carrier3_name">Name</label>
							<input type="text" id="carrier3_name" name="carrier3_name" class="form-control" value="{{ old('carrier3_name', $shipment->carrier3_name ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier3_address">Address</label>
							<input type="text" id="carrier3_address" name="carrier3_address" class="form-control" value="{{ old('carrier3_address', $shipment->carrier3_address ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier3_contact">Contact person</label>
							<input type="text" id="carrier3_contact" name="carrier3_contact" class="form-control" value="{{ old('carrier3_contact', $shipment->carrier3_contact ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier3_tel">Tel</label>
							<input type="text" id="carrier3_tel" name="carrier3_tel" class="form-control" value="{{ old('carrier3_tel', $shipment->carrier3_tel ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="carrier3_fax">Fax</label>
							<input type="text" id="carrier3_fax" name="carrier3_fax" class="form-control" value="{{ old('carrier3_fax', $shipment->carrier3_fax ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier3_email">E-mail</label>
							<input type="email" id="carrier3_email" name="carrier3_email" class="form-control" value="{{ old('carrier3_email', $shipment->carrier3_email ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier3_transport">Means of transport</label>
							<input type="text" id="carrier3_transport" name="carrier3_transport" class="form-control" value="{{ old('carrier3_transport', $shipment->carrier3_transport ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier3_date">Date of transfer</label>
							<input type="date" id="carrier3_date" name="carrier3_date" class="form-control" value="{{ old('carrier3_date', $shipment->carrier3_date ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="carrier3_licence">Carrier's licence</label>
							<input type="text" id="carrier3_licence" name="carrier3_licence" class="form-control" value="{{ old('carrier3_licence', $shipment->carrier3_licence ?? '') }}" />
						</div>

						<!-- Containers (map to Container1..Container5) -->
						<div class="col-12 mt-2"><h6>Containers</h6></div>
						<div class="col-md-4 form-group">
							<label for="Container1">Container 1 (ID)</label>
							<input type="text" id="Container1" name="Container1" class="form-control" value="{{ old('Container1', $shipment->Container1 ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="Container2">Container 2 (ID)</label>
							<input type="text" id="Container2" name="Container2" class="form-control" value="{{ old('Container2', $shipment->Container2 ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="Container3">Container 3 (ID)</label>
							<input type="text" id="Container3" name="Container3" class="form-control" value="{{ old('Container3', $shipment->Container3 ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="Container4">Container 4 (ID)</label>
							<input type="text" id="Container4" name="Container4" class="form-control" value="{{ old('Container4', $shipment->Container4 ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="Container5">Container 5 (ID)</label>
							<input type="text" id="Container5" name="Container5" class="form-control" value="{{ old('Container5', $shipment->Container5 ?? '') }}" />
						</div>

						<div class="col-12 mt-3"><h5>6. Waste generator / 7. Recovery facility</h5></div>
						<div class="col-md-6 form-group">
							<label for="waste_generator_name">Waste generator - Name</label>
							<input type="text" id="waste_generator_name" name="waste_generator_name" class="form-control" value="{{ old('waste_generator_name', $shipment->waste_generator_name ?? $shipment->waste_generator ?? '') }}" />
							<label for="waste_generator_address" class="mt-2">Address</label>
							<input type="text" id="waste_generator_address" name="waste_generator_address" class="form-control" value="{{ old('waste_generator_address', $shipment->waste_generator_address ?? '') }}" />
						</div>
						<div class="col-md-6 form-group">
							<label for="recovery_address">Recovery facility - Address</label>
							<input type="text" id="recovery_address" name="recovery_address" class="form-control" value="{{ old('recovery_address', $shipment->recovery_address ?? '') }}" />
							<label for="recovery_contact" class="mt-2">Contact person</label>
							<input type="text" id="recovery_contact" name="recovery_contact" class="form-control" value="{{ old('recovery_contact', $shipment->recovery_contact ?? '') }}" />
							<label for="recovery_tel" class="mt-2">Tel.</label>
							<input type="text" id="recovery_tel" name="recovery_tel" class="form-control" value="{{ old('recovery_tel', $shipment->recovery_tel ?? '') }}" />
							<label for="recovery_email" class="mt-2">E-mail</label>
							<input type="email" id="recovery_email" name="recovery_email" class="form-control" value="{{ old('recovery_email', $shipment->recovery_email ?? '') }}" />
						</div>

						<div class="col-12 mt-3"><h5>8, 9, 10. Recovery operation / Description / Waste identification</h5></div>
						<div class="col-md-12 form-group">
							<label for="recovery_operation">8. Recovery operation (R-/D-code)</label>
							<input type="text" id="recovery_operation" name="recovery_operation" class="form-control" value="{{ old('recovery_operation', $shipment->recovery_operation ?? $shipment->recovery_operation_code ?? '') }}" />
						</div>
						<div class="col-md-12 form-group">
							<label for="Product_Name">9. Usual description of the waste / Product Name</label>
							<textarea id="Product_Name" name="Product_Name" class="form-control" rows="2">{{ old('Product_Name', $shipment->Product_Name ?? $shipment->product_description ?? $shipment->usual_description ?? '') }}</textarea>
						</div>
						<div class="col-md-3 form-group">
							<label for="basel_annex">10 i) Basel annex IX</label>
							<input type="text" id="basel_annex" name="basel_annex" class="form-control" value="{{ old('basel_annex', $shipment->basel_annex ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="oecd_code">10 ii) OECD-Code</label>
							<input type="text" id="oecd_code" name="oecd_code" class="form-control" value="{{ old('oecd_code', $shipment->oecd_code ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="ec_list">10 iii) EC list of wastes</label>
							<input type="text" id="ec_list" name="ec_list" class="form-control" value="{{ old('ec_list', $shipment->ec_list ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="national_code">10 iv) National Code</label>
							<input type="text" id="national_code" name="national_code" class="form-control" value="{{ old('national_code', $shipment->national_code ?? '') }}" />
						</div>

						<div class="col-12 mt-3"><h5>11. Countries / state concerned</h5></div>
						<div class="col-md-4 form-group">
							<label for="export_country">Export / dispatch</label>
							<input type="text" id="export_country" name="export_country" class="form-control" value="{{ old('export_country', $shipment->export_country ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="transit_country">Transit</label>
							<input type="text" id="transit_country" name="transit_country" class="form-control" value="{{ old('transit_country', $shipment->transit_country ?? '') }}" />
						</div>
						<div class="col-md-4 form-group">
							<label for="import_destination">Import / destination</label>
							<input type="text" id="import_destination" name="import_destination" class="form-control" value="{{ old('import_destination', $shipment->import_destination ?? '') }}" />
						</div>

						<div class="col-12 mt-3"><h5>12. Declaration</h5></div>
						<div class="col-md-6 form-group">
							<label for="declarant_name">Name</label>
							<input type="text" id="declarant_name" name="declarant_name" class="form-control" value="{{ old('declarant_name', $shipment->declarant_name ?? $shipment->declarant ?? '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label for="declarant_date">Date</label>
							<input type="date" id="declarant_date" name="declarant_date" class="form-control" value="{{ old('declarant_date', isset($shipment->declarant_date) ? (is_string($shipment->declarant_date) ? $shipment->declarant_date : ($shipment->declarant_date->format('Y-m-d') ?? '')) : '') }}" />
						</div>
						<div class="col-md-3 form-group">
							<label>Signature (on print)</label>
							<div class="form-control" style="height:38px;">&nbsp;</div>
						</div>

						<div class="col-12 mt-3"><h5>13-14. Receipt / Shipment received (signatures to be completed on print)</h5></div>
						<div class="col-12 form-group">
							<small class="text-muted">Sections 13 and 14 will be completed on the printed annex by the consignee and recovery facility.</small>
						</div>
					</div>
				</div>
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