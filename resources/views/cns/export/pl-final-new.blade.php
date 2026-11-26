<div class="mt-3">
	<div class="cns-section-header">NEW FINAL PACKING LIST (BY BC)</div>
	<div class="card cns-card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<input type="hidden" name="Is_Final" id="Is_Final" value="1" />
					<input type="hidden" name="Final_SI_No" id="Final_SI_No" value="" />
					<div class="row">
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Packing_List_No">Packing List No</label>
								<input type="text" name="Packing_List_No" id="Packing_List_No" value="{{ $plno }}" class="text-black form-control shadow-sm" required readonly />
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="BC_No">Nomor BC (Booking Confirmation)</label>
								<select name="BC_No" id="BC_No" class="text-black form-control shadow-sm" required>
									<option value="">-- Pilih Nomor BC --</option>
									@foreach ($bcNumbers as $bc)
										<option value="{{ $bc }}" {{ ($selectedBcNo ?? '') === $bc ? 'selected' : '' }}>{{ $bc }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Order_No">Order No</label>
								<input type="text" name="Order_No" id="Order_No" value="" class="text-black form-control shadow-sm" readonly />
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="PL_Date">PL Date</label>
								<input type="date" name="PL_Date" id="PL_Date" class="text-black form-control shadow-sm" value="{{ date('Y-m-d') }}" required />
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label for="Qty1">Qty</label>
								<div class="row">
									<div class="col">
										<select name="Qty1" id="Qty1" class="text-black form-control shadow-sm" required>
											<option value="">-- Pilih Qty --</option>
											@foreach (range(1,1000) as $item)
												<option value="{{ $item }}">{{ $item }}</option>
											@endforeach
										</select>
									</div>
									<div class="col">
										<select name="Qty2" id="Qty2" class="text-black form-control shadow-sm" required>
											<option value="">-- Pilih Qty --</option>
											<option value="20 FT">20 FT</option>
											<option value="20 ISOTANK">20 ISOTANK</option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Shipper">Shipper</label>
								<select name="Shipper" id="Shipper" class="text-black form-control shadow-sm">
									<option value="">-- Pilih Shipper --</option>
									@foreach ($companies as $shipper)
										<option value="{{ $shipper->Supplier_ID }}">{{ $shipper->Company_Name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Consignee">Consignee</label>
								<select name="Consignee" id="Consignee" class="text-black form-control shadow-sm">
									<option value="">-- Pilih Consignee --</option>
									@foreach ($companies as $consignee)
										<option value="{{ $consignee->Supplier_ID }}">{{ $consignee->Company_Name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Notify">Notify Party</label>
								<select name="Notify" id="Notify" class="text-black form-control shadow-sm">
									<option value="">-- Pilih Notify --</option>
									@foreach ($companies as $notify)
										<option value="{{ $notify->Supplier_ID }}">{{ $notify->Company_Name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Destination_Port">Destination Port</label>
								<input type="text" id="Destination_Port" name="Destination_Port" class="text-black form-control shadow-sm" value="" />
							</div>
						</div>

						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="Delivery_Terms">Delivery Terms</label>
								<input type="text" name="Delivery_Terms" id="Delivery_Terms" value="" class="text-black form-control shadow-sm" />
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="Contract_No">Contract No</label>
								<input type="text" name="Contract_No" id="Contract_No" value="" class="text-black form-control shadow-sm" />
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="Voyage">Voyage</label>
								<input type="text" name="Voyage" id="Voyage" value="" class="text-black form-control shadow-sm" />
							</div>
						</div>

						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="ETD">ETD</label>
								<input type="text" name="ETD" id="ETD" value="{{ date('Y-m-d') }}" class="text-black form-control shadow-sm bg-white" readonly />
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="ETA">ETA</label>
								<input type="text" name="ETA" id="ETA" value="{{ date('Y-m-d') }}" class="text-black form-control shadow-sm bg-white" readonly />
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="Stuffing">Stuffing</label>
								<input type="text" name="Stuffing" id="Stuffing" value="{{ date('Y-m-d') }}" class="text-black form-control shadow-sm bg-white" readonly />
							</div>
						</div>

						<div class="col-md-6 col-12">
							<div class="form-group">
								<label for="PEB_No">PEB No</label>
								<input type="text" name="PEB_No" id="PEB_No" value="" class="text-black form-control shadow-sm" />
							</div>
						</div>
						<div class="col-md-6 col-12">
							<div class="form-group">
								<label for="PEB_Date">PEB Date</label>
								<input type="text" name="PEB_Date" id="PEB_Date" value="{{ date('Y-m-d') }}" class="text-black form-control shadow-sm" />
							</div>
						</div>

						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="BL_No">B/L No</label>
								<input type="text" name="BL_No" id="BL_No" value="" class="text-black form-control shadow-sm" />
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="POL">Port of Loading</label>
								<input type="text" name="POL" id="POL" value="" class="text-black form-control shadow-sm" />
							</div>
						</div>
						<div class="col-md-4 col-12">
							<div class="form-group">
								<label for="POD">Port of Discharge</label>
								<input type="text" name="POD" id="POD" value="" class="text-black form-control shadow-sm" />
							</div>
						</div>

						<div class="col-md-6 col-12">
							<div class="form-group">
								<label for="Product_Name">Product Name</label>
								<select name="Product_Name" id="Product_Name" class="text-black form-control shadow-sm">
									<option value="">-- Pilih Product --</option>
									@foreach ($masProducts as $item)
										<option value="{{ $item->description }}">{{ $item->description }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-6 col-12">
							<div class="form-group">
								<label for="HS_Code">HS Code</label>
								<select name="HS_Code" id="HS_Code" class="text-black form-control shadow-sm">
									<option value="">-- Pilih HS Code --</option>
									<option value="15180090">15180090</option>
									<option value="15119020">15119020</option>
								</select>
							</div>
						</div>

						<div class="col-md-6 col-12">
							<div class="form-group">
								<label for="Total_Netto_KG">Total Netto Weight (NW - KG)</label>
								<input type="number" step="0.01" name="Total_Netto_KG" id="Total_Netto_KG" value="0" class="text-black form-control shadow-sm" readonly />
							</div>
						</div>
						<div class="col-md-6 col-12">
							<div class="form-group">
								<label for="Total_Gross_KG">Total Gross Weight (GW - KG)</label>
								<input type="number" step="0.01" name="Total_Gross_KG" id="Total_Gross_KG" value="0" class="text-black form-control shadow-sm" readonly />
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label for="Description">Description (Otomatis terisi NW & GW)</label>
								<textarea name="Description" id="Description" rows="4" class="text-black form-control shadow-sm" placeholder="Description akan terisi otomatis saat Nomor BC dipilih"></textarea>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label for="Remarks">Remarks</label>
								<textarea name="Remarks" id="Remarks" rows="2" class="text-black form-control shadow-sm" placeholder="Remarks"></textarea>
							</div>
						</div>

						<div class="col-12 mt-2">
							<label>Daftar Kontainer Stuffing (Berdasarkan Nomor BC)</label>
							<div class="table-responsive">
								<table class="table table-sm table-bordered" id="bcContainerTable">
									<thead class="thead-light">
										<tr>
											<th>No</th>
											<th>Container (Seal)</th>
											<th class="text-right">Tare (KG)</th>
											<th class="text-right">Netto (KG)</th>
											<th class="text-right">Netto (MT)</th>
											<th class="text-right">Gross (KG)</th>
											<th class="text-right">Gross (MT)</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="7" class="text-center text-muted">Pilih Nomor BC untuk menarik data Stuffing.</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="divtom" class="mt-3">
				<input type="button" id="SubmitFinalPL" name="SubmitFinalPL" value="Submit Final PL" class="btn btn-success mt-2 mr-2" />
				<button type="button" class="btn btn-outline-secondary mt-2" onclick="$('#divlistOpportunity').html('')">Batal</button>
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>
</div>

<script>
	var myCalendar = new dhtmlXCalendarObject(["Stuffing", "PEB_Date", "ETD", "ETA"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	function formatNumberID(num) {
		return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function loadStuffingByBc(bcNo) {
		if (!bcNo) {
			$('#bcContainerTable tbody').html('<tr><td colspan="7" class="text-center text-muted">Pilih Nomor BC untuk menarik data Stuffing.</td></tr>');
			$('#Total_Netto_KG').val('0');
			$('#Total_Gross_KG').val('0');
			$('#Description').val('');
			return;
		}

		$.blockUI();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "cns-export-packing-list-get-bc",
			type: "POST",
			data: {
				_token: "{{ csrf_token() }}",
				bc_no: bcNo
			},
			success: function(response) {
				if (response.header) {
					Object.keys(response.header).forEach(function(key) {
						if (response.header[key] !== null && response.header[key] !== undefined && response.header[key] !== '') {
							$('#' + key).val(response.header[key]);
						}
					});
				}

				if (response.totals) {
					$('#Total_Netto_KG').val(response.totals.netto_kg);
					$('#Total_Gross_KG').val(response.totals.gross_kg);
				}

				if (response.description !== undefined) {
					$('#Description').val(response.description);
				}

				var rowsHtml = '';
				if (response.containers && response.containers.length > 0) {
					response.containers.forEach(function(item, idx) {
						var seal = item.Seal ? ' (' + item.Seal + ')' : '';
						rowsHtml += '<tr>' +
							'<td>' + (idx + 1) + '</td>' +
							'<td>' + (item.Container || '-') + seal + '</td>' +
							'<td class="text-right">' + formatNumberID(item.Tare) + '</td>' +
							'<td class="text-right">' + formatNumberID(item.Netto_KG) + '</td>' +
							'<td class="text-right">' + formatNumberID(item.Netto_MT) + '</td>' +
							'<td class="text-right">' + formatNumberID(item.Gross_KG) + '</td>' +
							'<td class="text-right">' + formatNumberID(item.Gross_MT) + '</td>' +
							'</tr>';
					});
					rowsHtml += '<tr class="font-weight-bold bg-light">' +
						'<td colspan="2" class="text-center">TOTAL</td>' +
						'<td class="text-right">' + formatNumberID(response.totals.tare) + '</td>' +
						'<td class="text-right">' + formatNumberID(response.totals.netto_kg) + '</td>' +
						'<td class="text-right">' + formatNumberID(response.totals.netto_mt) + '</td>' +
						'<td class="text-right">' + formatNumberID(response.totals.gross_kg) + '</td>' +
						'<td class="text-right">' + formatNumberID(response.totals.gross_mt) + '</td>' +
						'</tr>';
				} else {
					rowsHtml = '<tr><td colspan="7" class="text-center text-muted">Belum ada data kontainer Stuffing pada Nomor BC ini.</td></tr>';
				}
				$('#bcContainerTable tbody').html(rowsHtml);
				$.unblockUI();
			},
			error: function(xhr) {
				$.unblockUI();
				var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Gagal menarik data Stuffing berdasarkan Nomor BC.';
				swal('Error', msg, 'error');
			}
		});
	}

	$('#BC_No').on('change', function() {
		loadStuffingByBc($(this).val());
	});

	if ($('#BC_No').val()) {
		loadStuffingByBc($('#BC_No').val());
	}

	$('#SubmitFinalPL').click(function() {
		if (!$('#BC_No').val()) {
			swal('Perhatian', 'Silakan pilih Nomor BC terlebih dahulu.', 'warning');
			return;
		}

		var payload = $('#ContentPages input, #ContentPages select, #ContentPages textarea').serialize();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "cns-export-packing-list-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: payload,
			success: function(data) {
				if (String(data).indexOf("success") !== -1) {
					swal('Berhasil!', 'Final Packing List berhasil dibuat!', 'success');
					if (typeof refreshdata === 'function') {
						refreshdata();
					}
					$('#divlistOpportunity').html('');
				} else {
					swal('Error', 'Final Packing List gagal disimpan!', 'error');
				}
			},
			error: function() {
				swal('Error', 'Error Internal! Please Try again!', 'error');
			}
		});
	});
</script>
