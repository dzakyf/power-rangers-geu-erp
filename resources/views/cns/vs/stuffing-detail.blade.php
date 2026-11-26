<div class="mt-3">
	<div class="cns-section-header">VESSEL SCHEDULE</div>
	<div class="card cns-card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Order No</label>
						<input type="text" value="{{ $vs->Order_No }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Qty</label>
						<input type="text" value="{{ $vs->Qty }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Vessel</label>
						<input type="text" value="{{ $vs->Vessel }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Stuffing</label>
						<input type="text" value="{{ $vs->Stuffing ? date('d F Y', strtotime($vs->Stuffing)) : '' }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>PoL</label>
						<input type="text" value="{{ $vs->POL }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>PoD</label>
						<input type="text" value="{{ $vs->POD }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Closing CY</label>
						<input type="text" value="{{ $vs->Closing_CY ? date('d F Y', strtotime($vs->Closing_CY)) : '' }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>ETD / ETA</label>
						<input type="text" value="{{ ($vs->ETD ? date('d F Y', strtotime($vs->ETD)) : '') . ($vs->ETA ? ' / ' . date('d F Y', strtotime($vs->ETA)) : '') }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="mt-3">
	<div class="cns-section-header">BOOKING CONFIRMATION</div>
	<div class="card cns-card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Contract No</label>
						<input type="text" value="{{ $vs->Contract_No }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Qty Kontrak (MT)</label>
						<input type="text" value="{{ $contract ? number_format((float) $contract->Quantity_MT, 2, '.', ',') : '-' }}" class="text-black form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label>File BC Saat Ini</label>
						<div id="currentBookingConfirmation" class="text-black form-control bg-white" style="min-height:38px;height:auto;">
							@if($bookingConfirmation)
								<a href="{{ asset('filevendor/' . $bookingConfirmation->Path) }}" target="_blank" rel="noopener">{{ $bookingConfirmation->Filename }}</a>
							@else
								Tidak ada BC.
							@endif
						</div>
					</div>
				</div>
			</div>
			<form id="BookingConfirmationForm" enctype="multipart/form-data">
				<div class="row">
					<div class="col-md-4 col-12">
						<div class="form-group">
							<label for="BC_No">Nomor BC</label>
							<input type="text" name="BC_No" id="BC_No" value="{{ $vs->BC_No ?: ($vs->Booking_No ?: $vs->Order_No) }}" class="text-black form-control shadow-sm" />
						</div>
					</div>
					<div class="col-md-4 col-12">
						<div class="form-group">
							<label for="BC_File">Upload BC (PDF/JPG/JPEG)</label>
							<input type="file" name="BC_File" id="BC_File" class="text-black form-control-file" accept=".pdf,.jpg,.jpeg" />
							<small class="text-muted">Kosongkan jika hanya ingin mengubah status/catatan. File baru akan mengganti file lama.</small>
						</div>
					</div>
					<div class="col-md-2 col-12">
						<div class="form-group">
							<label for="BC_Status">Status Qty</label>
							<select name="BC_Status" id="BC_Status" class="text-black form-control shadow-sm">
								<option value="">-- Pilih Status --</option>
								<option value="KURANG" {{ $vs->BC_Status === 'KURANG' ? 'selected' : '' }}>KURANG</option>
								<option value="SESUAI" {{ $vs->BC_Status === 'SESUAI' ? 'selected' : '' }}>SESUAI</option>
								<option value="MELEBIH" {{ $vs->BC_Status === 'MELEBIH' ? 'selected' : '' }}>MELEBIH</option>
							</select>
						</div>
					</div>
					<div class="col-md-2 col-12">
						<div class="form-group">
							<label for="BC_Note">Catatan</label>
							<input type="text" name="BC_Note" id="BC_Note" value="{{ $vs->BC_Note }}" class="text-black form-control shadow-sm" />
						</div>
					</div>
				</div>
				<small class="text-muted">Buka file BC untuk membandingkan qty aktual pada BC dengan qty kontrak. Status diisi manual.</small>
				<div class="mt-3">
					<button type="submit" id="SaveBookingConfirmation" class="btn btn-success">Simpan BC</button>
					<small class="text-muted ml-2">
						Terakhir diubah:
						{{ $vs->BC_Updated_By ?: '-' }}
						{{ $vs->BC_Updated_Date ? ' / ' . date('d F Y H:i', strtotime($vs->BC_Updated_Date)) : '' }}
					</small>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="mt-3">
	<div class="cns-section-header">CHECKLIST STATUS PENGIRIMAN</div>
	<div class="card cns-card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Pickup_Container" {{ $vs->Checklist_Pickup_Container ? 'checked' : '' }} {{ $vs->Checklist_Done_Kapal_Berangkat ? 'disabled' : '' }} />
						<label class="form-check-label" for="Checklist_Pickup_Container">Pickup Container</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Stuffing_Process" {{ $vs->Checklist_Stuffing_Process ? 'checked' : '' }} {{ $vs->Checklist_Done_Kapal_Berangkat ? 'disabled' : '' }} />
						<label class="form-check-label" for="Checklist_Stuffing_Process">Stuffing Process</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Rilis_NPE" {{ $vs->Checklist_Rilis_NPE ? 'checked' : '' }} {{ ($vs->Checklist_Done_Kapal_Berangkat && $vs->Checklist_Rilis_NPE) ? 'disabled' : '' }} />
						<label class="form-check-label" for="Checklist_Rilis_NPE">Rilis NPE</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Get_In_CY" {{ $vs->Checklist_Get_In_CY ? 'checked' : '' }} {{ $vs->Checklist_Done_Kapal_Berangkat ? 'disabled' : '' }} />
						<label class="form-check-label" for="Checklist_Get_In_CY">Proses Get In CY</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Done_Kapal_Berangkat" {{ $vs->Checklist_Done_Kapal_Berangkat ? 'checked' : '' }} {{ $vs->Checklist_Done_Kapal_Berangkat ? 'disabled' : '' }} />
						<label class="form-check-label" for="Checklist_Done_Kapal_Berangkat">Done/Kapal Berangkat</label>
					</div>
				</div>
			</div>
			<div class="mt-3">
				<button type="button" class="btn btn-success" id="SaveStuffingChecklist" {{ ($vs->Checklist_Done_Kapal_Berangkat && $vs->Checklist_Rilis_NPE) ? 'disabled' : '' }}>Simpan Checklist</button>
				<small class="text-muted ml-2">
					Terakhir diubah:
					{{ $vs->Checklist_Updated_By ?: '-' }}
					{{ $vs->Checklist_Updated_Date ? ' / ' . date('d F Y H:i', strtotime($vs->Checklist_Updated_Date)) : '' }}
				</small>
				@if($vs->Checklist_Done_Kapal_Berangkat)
					<div class="text-info mt-1"><small>Status pengiriman sudah final (Done/Kapal Berangkat). Checklist terkunci dan stok Flexi Bag otomatis terpotong.</small></div>
				@endif
			</div>
		</div>
	</div>
</div>

<div class="mt-3">
	<div class="cns-section-header">STUFFING CONTAINER</div>
	<div class="card cns-card">
		<div class="card-body">
			<div class="cns-grid-wrapper">
				<div id="StuffingDetail" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			</div>
			<div class="mt-3">
				<button type="button" class="btn btn-primary mr-3" onclick="addStuffingContainer()">Add Container</button>
				<button type="button" class="btn btn-info mr-3" onclick="editStuffingContainer()">Edit Container</button>
				<button type="button" class="btn btn-danger mr-3" onclick="deleteStuffingContainer()">Delete Container</button>
			</div>
		</div>
	</div>
</div>

<div class="card cns-card mt-3" id="StuffingContainerFormCard" style="display:none;">
	<div class="card-body">
		<div class="cns-section-header" id="StuffingContainerFormTitle">BUAT FORM STUFFING CONTAINER</div>
		<form id="StuffingContainerForm" enctype="multipart/form-data">
			<input type="hidden" name="id" id="Stuffing_ID" value="" />
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Order_No">Order No</label>
						<input type="text" name="Order_No" id="Order_No" class="text-black form-control bg-white" value="{{ $vs->Order_No }}" readonly />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Date">Tanggal</label>
						<input type="date" name="Date" id="Date" class="text-black form-control shadow-sm" required />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Time_From">Jam Awal</label>
						<input type="time" name="Time_From" id="Time_From" class="text-black form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Time_To">Jam Akhir</label>
						<input type="time" name="Time_To" id="Time_To" class="text-black form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Police_No">Nopol</label>
						<input type="text" name="Police_No" id="Police_No" class="text-black form-control shadow-sm" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Container">Kontainer</label>
						<input type="text" name="Container" id="Container" class="text-black form-control shadow-sm" required />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Seal">Seal</label>
						<input type="text" name="Seal" id="Seal" class="text-black form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Serial_Number">Serial Number Flexibag</label>
						<select name="serial_number" id="Serial_Number" class="text-black form-control shadow-sm">
							<option value="">-- Pilih SN (Opsional) --</option>
							@foreach(($availableSerialNumbers ?? []) as $sn)
								<option value="{{ $sn }}">{{ $sn }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Tare">Tare</label>
						<input type="text" inputmode="decimal" name="Tare" id="Tare" class="text-black form-control shadow-sm cns-decimal" value="0.00" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Netto_KG">Netto (Kg)</label>
						<input type="text" inputmode="decimal" name="Netto_KG" id="Netto_KG" class="text-black form-control shadow-sm cns-decimal" value="0.00" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Netto_MT">Netto (MT)</label>
						<input type="text" name="Netto_MT" id="Netto_MT" class="text-black form-control bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Gross_KG">Gross (Kg)</label>
						<input type="text" name="Gross_KG" id="Gross_KG" class="text-black form-control bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Gross_MT">Gross (MT)</label>
						<input type="text" name="Gross_MT" id="Gross_MT" class="text-black form-control bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Note">Note</label>
						<input type="text" name="Note" id="Note" class="text-black form-control shadow-sm" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Weighbridge_Note">Upload Nota Timbang (Order)</label>
						<input type="file" name="Weighbridge_Note" id="Weighbridge_Note" class="text-black form-control-file" accept=".pdf,.jpg,.jpeg,.png" />
						<small class="text-muted">PDF/JPG/JPEG/PNG, maksimal 8 MB. Kosongkan jika tidak ingin mengganti nota.</small>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label>Nota Timbang Saat Ini</label>
						<div id="currentWeighbridgeNote" class="text-black form-control bg-white" style="min-height:38px;height:auto;">Tidak ada nota.</div>
					</div>
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-12">
					<h6 class="font-weight-bold border-bottom pb-1" style="color:#1a56a0;">Foto Dokumentasi Container</h6>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Flexibag_Photos">Foto Pemasangan Flexibag</label>
						<input type="file" name="Flexibag_Photos[]" id="Flexibag_Photos" class="text-black form-control-file" accept=".jpg,.jpeg,.png" multiple />
						<small class="text-muted">5-6 foto, maksimal 7 foto.</small>
						<div id="existingFlexibagPhotos" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Loading_Start_Photo">Foto Loading Start</label>
						<input type="file" name="Loading_Start_Photo" id="Loading_Start_Photo" class="text-black form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Saat selang masih terhubung ke kontainer.</small>
						<div id="existingLoadingStartPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Loading_Done_Photo">Foto Loading Done</label>
						<input type="file" name="Loading_Done_Photo" id="Loading_Done_Photo" class="text-black form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Saat selang terlepas dan flexibag menggembung.</small>
						<div id="existingLoadingDonePhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Seal_Customs_Photo">Foto Seal Bea Cukai</label>
						<input type="file" name="Seal_Customs_Photo" id="Seal_Customs_Photo" class="text-black form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Seal bea cukai terpasang di belakang kontainer.</small>
						<div id="existingSealCustomsPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Seal_Shipping_Photo">Foto Seal Pelayaran</label>
						<input type="file" name="Seal_Shipping_Photo" id="Seal_Shipping_Photo" class="text-black form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Seal sudah terpasang.</small>
						<div id="existingSealShippingPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Seal_Surveyor_Photo">Foto Seal Surveyor</label>
						<input type="file" name="Seal_Surveyor_Photo" id="Seal_Surveyor_Photo" class="text-black form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Seal sudah terpasang.</small>
						<div id="existingSealSurveyorPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Road_Letter_Photo">Foto Surat Jalan</label>
						<input type="file" name="Road_Letter_Photo" id="Road_Letter_Photo" class="text-black form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Surat jalan sudah terisi lengkap.</small>
						<div id="existingRoadLetterPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Weighbridge_Photo">Foto Nota Timbang</label>
						<input type="file" name="Weighbridge_Photo" id="Weighbridge_Photo" class="text-black form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Foto yang jelas, jangan buram, jangan berbayang.</small>
						<div id="existingWeighbridgePhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
			</div>
			<button type="submit" id="SaveStuffingContainer" class="btn btn-success mt-2 mr-2">Simpan</button>
			<button type="button" id="CancelStuffingContainer" class="btn btn-secondary mt-2">Batal</button>
		</form>
	</div>
</div>

<div class="mt-3">
	<div class="cns-section-header">VESSEL SCHEDULE LOG</div>
	<div class="card cns-card">
		<div class="card-body">
			<div class="cns-grid-wrapper">
				<div id="StuffingDetailLog" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			</div>
		</div>
	</div>
</div>

<script>
	var StuffingDetail;

	StuffingDetail = new dhtmlXGridObject('StuffingDetail');
	StuffingDetail.setImagePath("./dhtmlx/imgs/");
	StuffingDetail.setHeader("Pilih,No,No Stuffing,Order No,Jam Awal (Jam:Menit),Jam Akhir (Jam:Menit),Tanggal,Nopol,Kontainer,Seal,Serial Number,Tare,Netto (Kg),Netto (MT),Gross (Kg),Gross (MT),Note,Nota Timbang");
	StuffingDetail.attachHeader("#rspan,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	StuffingDetail.setInitWidths("45,60,160,120,90,90,100,160,140,140,140,90,100,100,100,100,180,100");
	StuffingDetail.setColAlign("center,center,left,left,center,center,center,left,left,left,left,right,right,right,right,right,left,left");
	StuffingDetail.setColSorting("str,int,str,str,str,str,str,str,str,str,str,int,int,int,int,int,str,str");
	StuffingDetail.setColTypes("ch,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	StuffingDetail.enableSmartRendering(true, 150);
	StuffingDetail.attachEvent("onRowDblClicked", function(rowId, cellIndex) {
		if (cellIndex === 0) {
			return;
		}

		editStuffingContainer(rowId);
	});
	StuffingDetail.setSkin("dhx_skyblue");
	StuffingDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		StuffingDetail.clearAndLoad('xml-cns-stuffing-detail?task={{ $vs->Task_ID }}&has_checkbox=1');
	}

	function calculateStuffingFormValues() {
		let tare = cnsParseNumber($('#Tare').val(), 0);
		let netto = cnsParseNumber($('#Netto_KG').val(), 0);
		let nettoMT = netto / 1000;
		let gross = tare + netto;

		$('#Netto_MT').val(cnsFormatNumber(nettoMT, 2));
		$('#Gross_KG').val(cnsFormatNumber(gross, 2));
		$('#Gross_MT').val(cnsFormatNumber(gross / 1000, 2));
	}

	$('#Tare, #Netto_KG').on('input change', calculateStuffingFormValues);

	function setCurrentWeighbridgeNote(url, name) {
		let container = $('#currentWeighbridgeNote').empty();

		if (url) {
			$('<a>', {
				href: url,
				target: '_blank',
				rel: 'noopener'
			}).text(name || 'Lihat Nota Timbang').appendTo(container);
		} else {
			container.text('Tidak ada nota.');
		}
	}

	var containerPhotoTargets = {
		flexibag: 'existingFlexibagPhotos',
		loading_start: 'existingLoadingStartPhoto',
		loading_done: 'existingLoadingDonePhoto',
		seal_customs: 'existingSealCustomsPhoto',
		seal_shipping: 'existingSealShippingPhoto',
		seal_surveyor: 'existingSealSurveyorPhoto',
		road_letter: 'existingRoadLetterPhoto',
		weighbridge: 'existingWeighbridgePhoto'
	};

	function renderContainerPhotos(photos) {
		Object.keys(containerPhotoTargets).forEach(function(key) {
			let target = $('#' + containerPhotoTargets[key]).empty();
			let items = (photos && photos[key]) ? photos[key] : [];

			if (items.length === 0) {
				target.text('Belum ada foto.');
				return;
			}

			items.forEach(function(photo) {
				let row = $('<div>').addClass('d-flex align-items-center mb-1');
				$('<a>', {
					href: photo.url,
					target: '_blank',
					rel: 'noopener'
				}).text(photo.filename).appendTo(row);
				$('<button>', {
					type: 'button',
					class: 'btn btn-sm btn-link text-danger ml-2 delete-container-photo',
					'data-photo-id': photo.ID
				}).text('Hapus').appendTo(row);
				target.append(row);
			});
		});
	}

	var availableSerialNumbers = @json($availableSerialNumbers ?? []);

	function renderSerialNumberOptions(options, selectedValue) {
		let select = $('#Serial_Number').empty();
		select.append($('<option>', {value: '', text: '-- Pilih SN (Opsional) --'}));
		(options || []).forEach(function(sn) {
			select.append($('<option>', {value: sn, text: sn}));
		});
		select.val(selectedValue || '');
	}

	function resetStuffingForm() {
		$('#StuffingContainerForm')[0].reset();
		$('#Stuffing_ID').val('');
		renderSerialNumberOptions(availableSerialNumbers, '');
		$('#Date').val('{{ $vs->ETD ?: date('Y-m-d') }}');
		$('#Tare, #Netto_KG').val('0.00');
		calculateStuffingFormValues();
		setCurrentWeighbridgeNote(null, null);
		renderContainerPhotos({});
	}

	function openStuffingForm(title) {
		$('#StuffingContainerFormTitle').text(title);
		$('#StuffingContainerFormCard').show();
		document.getElementById('StuffingContainerFormCard').scrollIntoView({behavior: 'smooth', block: 'start'});
	}

	function loadNoteInfo() {
		$.ajax({
			url: 'cns-stuffing-detail-note-info',
			type: 'GET',
			data: {Order_No: '{{ $vs->Order_No }}'},
			headers: {'Accept': 'application/json'},
			success: function(response) {
				setCurrentWeighbridgeNote(response.url, response.name);
			},
			error: function() {
				setCurrentWeighbridgeNote(null, null);
			}
		});
	}

	function addStuffingContainer() {
		resetStuffingForm();
		openStuffingForm('BUAT FORM STUFFING CONTAINER');
		loadNoteInfo();
	}

	function editStuffingContainer(rowId) {
		let id = (typeof rowId === 'string' || typeof rowId === 'number')
			? rowId
			: StuffingDetail.getSelectedId();

		if (!id) {
			swal('Perhatian', 'Pilih baris data yang ingin diedit.', 'warning');
			return;
		}

		$.ajax({
			url: 'cns-stuffing-detail-data',
			type: 'GET',
			data: {task: '{{ $vs->id }}', ID: id},
			headers: {'Accept': 'application/json'},
			success: function(response) {
				resetStuffingForm();
				$('#Stuffing_ID').val(response.ID);
				$('#Time_From').val(response.Time_From);
				$('#Time_To').val(response.Time_To);
				$('#Date').val(response.Date);
				$('#Police_No').val(response.Police_No);
				$('#Container').val(response.Container);
				$('#Seal').val(response.Seal);
				renderSerialNumberOptions(response.available_serial_numbers || availableSerialNumbers, response.serial_number || '');
				$('#Tare').val(cnsFormatNumber(response.Tare, 2));
				$('#Netto_KG').val(cnsFormatNumber(response.Netto_KG, 2));
				$('#Note').val(response.Note);
				calculateStuffingFormValues();
				setCurrentWeighbridgeNote(response.Note_Url, response.Note_Name);
				renderContainerPhotos(response.photos || {});
				openStuffingForm('EDIT FORM STUFFING CONTAINER');
			},
			error: function() {
				swal('Error', 'Gagal mengambil data container.', 'error');
			}
		});
	}

	function deleteStuffingContainer() {
		let checked = StuffingDetail.getCheckedRows(0);
		let ids = checked ? checked.split(',').filter(function(id) { return id !== ''; }) : [];

		if (ids.length === 0) {
			swal('Perhatian', 'Pilih container yang ingin dihapus.', 'warning');
			return;
		}

		swal({
			title: 'Konfirmasi',
			text: 'Hapus ' + ids.length + ' container terpilih?',
			icon: 'warning',
			buttons: ['Batal', 'Ya'],
			dangerMode: true
		}).then(function(confirmed) {
			if (!confirmed) {
				return;
			}

			$.ajax({
				url: 'cns-stuffing-detail-delete?task={{ $vs->id }}',
				type: 'POST',
				data: {_token: '{{ csrf_token() }}', ids: ids},
				headers: {'Accept': 'application/json'},
				success: function(response) {
					if (response.available_serial_numbers) {
						availableSerialNumbers = response.available_serial_numbers;
					}
					swal('Berhasil!', response.message || 'Container berhasil dihapus.', 'success').then(function() {
						$('#StuffingContainerFormCard').hide();
						refreshdataDetail();
						refreshdataDataLog();
					});
				},
				error: function(xhr) {
					if (xhr.responseJSON && xhr.responseJSON.message) {
						swal('Error', xhr.responseJSON.message, 'error');
					} else {
						swal('Error', 'Gagal menghapus container.', 'error');
					}
				}
			});
		});
	}

	$('#CancelStuffingContainer').on('click', function() {
		$('#StuffingContainerFormCard').hide();
	});

	$('#StuffingContainerForm').on('submit', function(e) {
		e.preventDefault();

		let formData = new FormData(this);
		formData.set('Tare', cnsParseNumber($('#Tare').val(), 0));
		formData.set('Netto_KG', cnsParseNumber($('#Netto_KG').val(), 0));
		formData.set('Netto_MT', cnsParseNumber($('#Netto_MT').val(), 0));
		formData.set('Gross_KG', cnsParseNumber($('#Gross_KG').val(), 0));
		formData.set('Gross_MT', cnsParseNumber($('#Gross_MT').val(), 0));
		formData.append('_token', '{{ csrf_token() }}');
		let button = $('#SaveStuffingContainer');

		button.prop('disabled', true).text('Menyimpan...');

		$.ajax({
			url: 'cns-stuffing-detail-save?task={{ $vs->id }}',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: {'Accept': 'application/json'},
			success: function(response) {
				if (response.available_serial_numbers) {
					availableSerialNumbers = response.available_serial_numbers;
				}
				swal('Berhasil!', response.message || 'Data berhasil disimpan.', 'success').then(function() {
					$('#StuffingContainerFormCard').hide();
					resetStuffingForm();
					refreshdataDetail();
					refreshdataDataLog();
				});
			},
			error: function(xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					let messages = [];
					Object.keys(xhr.responseJSON.errors).forEach(function(key) {
						messages.push(xhr.responseJSON.errors[key].join('\n'));
					});
					swal('Error', messages.join('\n'), 'error');
				} else if (xhr.responseJSON && xhr.responseJSON.message) {
					swal('Error', xhr.responseJSON.message, 'error');
				} else {
					swal('Error', 'Gagal menyimpan data.', 'error');
				}
			},
			complete: function() {
				button.prop('disabled', false).text('Simpan');
			}
		});
	});

	$('#StuffingContainerForm').on('click', '.delete-container-photo', function() {
		let containerId = $('#Stuffing_ID').val();

		if (!containerId) {
			return;
		}

		let photoId = $(this).data('photo-id');

		swal({
			title: 'Konfirmasi',
			text: 'Hapus foto ini?',
			icon: 'warning',
			buttons: ['Batal', 'Ya'],
			dangerMode: true
		}).then(function(confirmed) {
			if (!confirmed) {
				return;
			}

			$.ajax({
				url: 'cns-stuffing-detail-photo-delete?task={{ $vs->id }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					container_id: containerId,
					photo_id: photoId
				},
				headers: {'Accept': 'application/json'},
				success: function() {
					swal('Berhasil!', 'Foto berhasil dihapus.', 'success').then(function() {
						let row = $('.delete-container-photo[data-photo-id="' + photoId + '"]').closest('div');
						let target = row.parent();
						row.remove();

						if (target.children().length === 0) {
							target.text('Belum ada foto.');
						}
					});
				},
				error: function(xhr) {
					swal('Error', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus foto.', 'error');
				}
			});
		});
	});

	$('#BookingConfirmationForm').on('submit', function(e) {
		e.preventDefault();

		let formData = new FormData(this);
		formData.append('_token', '{{ csrf_token() }}');
		let button = $('#SaveBookingConfirmation');

		button.prop('disabled', true).text('Menyimpan...');

		$.ajax({
			url: 'cns-stuffing-detail-booking-confirmation?task={{ $vs->id }}',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: {'Accept': 'application/json'},
			success: function(response) {
				swal('Berhasil!', response.message || 'Booking Confirmation berhasil disimpan.', 'success');

				if (response.url) {
					$('#currentBookingConfirmation').html(
						$('<a>', {
							href: response.url,
							target: '_blank',
							rel: 'noopener'
						}).text(response.name || 'Lihat Booking Confirmation')
					);
				}

				refreshdataDataLog();
			},
			error: function(xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					let messages = [];
					Object.keys(xhr.responseJSON.errors).forEach(function(key) {
						messages.push(xhr.responseJSON.errors[key].join('\n'));
					});
					swal('Error', messages.join('\n'), 'error');
				} else if (xhr.responseJSON && xhr.responseJSON.message) {
					swal('Error', xhr.responseJSON.message, 'error');
				} else {
					swal('Error', 'Gagal menyimpan Booking Confirmation.', 'error');
				}
			},
			complete: function() {
				button.prop('disabled', false).text('Simpan BC');
			}
		});
	});

	$('#SaveStuffingChecklist').on('click', function() {
		$.ajax({
			url: 'cns-stuffing-detail-checklist?task={{ $vs->id }}',
			type: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				Checklist_Pickup_Container: $('#Checklist_Pickup_Container').is(':checked') ? 1 : 0,
				Checklist_Stuffing_Process: $('#Checklist_Stuffing_Process').is(':checked') ? 1 : 0,
				Checklist_Rilis_NPE: $('#Checklist_Rilis_NPE').is(':checked') ? 1 : 0,
				Checklist_Get_In_CY: $('#Checklist_Get_In_CY').is(':checked') ? 1 : 0,
				Checklist_Done_Kapal_Berangkat: $('#Checklist_Done_Kapal_Berangkat').is(':checked') ? 1 : 0
			},
			headers: {'Accept': 'application/json'},
			success: function(response) {
				swal('Berhasil!', response.message || 'Checklist berhasil disimpan.', 'success').then(function() {
					if ($('#Checklist_Done_Kapal_Berangkat').is(':checked')) {
						location.reload();
						return;
					}
					refreshdataDataLog();
					if (typeof refreshdata === 'function') {
						refreshdata();
					}
				});
			},
			error: function(xhr) {
				swal('Error', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan checklist.', 'error');
			}
		});
	});

	StuffingDetailLog = new dhtmlXGridObject('StuffingDetailLog');
	StuffingDetailLog.setImagePath("./dhtmlx/imgs/");
	StuffingDetailLog.setHeader("No, Action By, Action Date, Action Status, Booking Confirmation (BC),Qty,Kapal,POL,POD,Stuffing,Checking,Closing CY,ETD,ATD,ETA,ATA,Estimated Day(s), Actual Day(s), Note, Container (Seal)");
	StuffingDetailLog.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	StuffingDetailLog.setInitWidths("60,100,100,200,150,100,200,150,150,100,100,100,100,100,100,100,100,100,100,800");
	StuffingDetailLog.setColAlign("center,center,center,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	StuffingDetailLog.setDateFormat("%d %F %Y");
	StuffingDetailLog.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	StuffingDetailLog.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	StuffingDetailLog.enableSmartRendering(true, 150);
	StuffingDetailLog.setSkin("dhx_skyblue");
	StuffingDetailLog.init();
	refreshdataDataLog();

	function refreshdataDataLog() {
		StuffingDetailLog.clearAndLoad('xml-cns-vessel-schedule-detail-log?task={{ $vs->id }}');
	}
</script>
