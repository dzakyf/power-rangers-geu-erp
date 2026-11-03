<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">VESSEL SCHEDULE</div>
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Order No</label>
						<input type="text" value="{{ $vs->Order_No }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Qty</label>
						<input type="text" value="{{ $vs->Qty }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Vessel</label>
						<input type="text" value="{{ $vs->Vessel }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Stuffing</label>
						<input type="text" value="{{ $vs->Stuffing ? date('d F Y', strtotime($vs->Stuffing)) : '' }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>PoL</label>
						<input type="text" value="{{ $vs->POL }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>PoD</label>
						<input type="text" value="{{ $vs->POD }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Closing CY</label>
						<input type="text" value="{{ $vs->Closing_CY ? date('d F Y', strtotime($vs->Closing_CY)) : '' }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>ETD / ETA</label>
						<input type="text" value="{{ ($vs->ETD ? date('d F Y', strtotime($vs->ETD)) : '') . ($vs->ETA ? ' / ' . date('d F Y', strtotime($vs->ETA)) : '') }}" class="form-control bg-white" readonly />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DELIVERY ORDER</div>
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Contract No</label>
						<input type="text" value="{{ $vs->Contract_No }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label>Qty Kontrak (MT)</label>
						<input type="text" value="{{ $contract ? number_format((float) $contract->Quantity_MT, 3, '.', '') : '-' }}" class="form-control bg-white" readonly />
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label>File DO Saat Ini</label>
						<div id="currentDeliveryOrder" class="form-control bg-white" style="min-height:38px;height:auto;">
							@if($deliveryOrder)
								<a href="{{ asset('filevendor/' . $deliveryOrder->Path) }}" target="_blank" rel="noopener">{{ $deliveryOrder->Filename }}</a>
							@else
								Tidak ada DO.
							@endif
						</div>
					</div>
				</div>
			</div>
			<form id="DeliveryOrderForm" enctype="multipart/form-data">
				<div class="row">
					<div class="col-md-4 col-12">
						<div class="form-group">
							<label for="DO_No">Nomor DO</label>
							<input type="text" name="DO_No" id="DO_No" value="{{ $vs->DO_No }}" class="form-control shadow-sm" />
						</div>
					</div>
					<div class="col-md-4 col-12">
						<div class="form-group">
							<label for="DO_File">Upload DO (PDF/JPG/JPEG)</label>
							<input type="file" name="DO_File" id="DO_File" class="form-control-file" accept=".pdf,.jpg,.jpeg" />
							<small class="text-muted">Kosongkan jika hanya ingin mengubah status/catatan. File baru akan mengganti file lama.</small>
						</div>
					</div>
					<div class="col-md-2 col-12">
						<div class="form-group">
							<label for="DO_Status">Status Qty</label>
							<select name="DO_Status" id="DO_Status" class="form-control shadow-sm">
								<option value="">-- Pilih Status --</option>
								<option value="KURANG" {{ $vs->DO_Status === 'KURANG' ? 'selected' : '' }}>KURANG</option>
								<option value="SESUAI" {{ $vs->DO_Status === 'SESUAI' ? 'selected' : '' }}>SESUAI</option>
								<option value="MELEBIH" {{ $vs->DO_Status === 'MELEBIH' ? 'selected' : '' }}>MELEBIH</option>
							</select>
						</div>
					</div>
					<div class="col-md-2 col-12">
						<div class="form-group">
							<label for="DO_Note">Catatan</label>
							<input type="text" name="DO_Note" id="DO_Note" value="{{ $vs->DO_Note }}" class="form-control shadow-sm" />
						</div>
					</div>
				</div>
				<small class="text-muted">Buka file DO untuk membandingkan qty aktual pada DO dengan qty kontrak. Status diisi manual.</small>
				<div class="mt-3">
					<button type="submit" id="SaveDeliveryOrder" class="btn btn-success">Simpan DO</button>
					<small class="text-muted ml-2">
						Terakhir diubah:
						{{ $vs->DO_Updated_By ?: '-' }}
						{{ $vs->DO_Updated_Date ? ' / ' . date('d F Y H:i', strtotime($vs->DO_Updated_Date)) : '' }}
					</small>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CHECKLIST STATUS PENGIRIMAN</div>
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Pickup_Container" {{ $vs->Checklist_Pickup_Container ? 'checked' : '' }} />
						<label class="form-check-label" for="Checklist_Pickup_Container">Pickup Container</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Stuffing_Process" {{ $vs->Checklist_Stuffing_Process ? 'checked' : '' }} />
						<label class="form-check-label" for="Checklist_Stuffing_Process">Stuffing Process</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Rilis_NPE" {{ $vs->Checklist_Rilis_NPE ? 'checked' : '' }} />
						<label class="form-check-label" for="Checklist_Rilis_NPE">Rilis NPE</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Get_In_CY" {{ $vs->Checklist_Get_In_CY ? 'checked' : '' }} />
						<label class="form-check-label" for="Checklist_Get_In_CY">Proses Get In CY</label>
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="Checklist_Done_Kapal_Berangkat" {{ $vs->Checklist_Done_Kapal_Berangkat ? 'checked' : '' }} />
						<label class="form-check-label" for="Checklist_Done_Kapal_Berangkat">Done/Kapal Berangkat</label>
					</div>
				</div>
			</div>
			<div class="mt-3">
				<button type="button" class="btn btn-success" id="SaveStuffingChecklist">Simpan Checklist</button>
				<small class="text-muted ml-2">
					Terakhir diubah:
					{{ $vs->Checklist_Updated_By ?: '-' }}
					{{ $vs->Checklist_Updated_Date ? ' / ' . date('d F Y H:i', strtotime($vs->Checklist_Updated_Date)) : '' }}
				</small>
			</div>
		</div>
	</div>
</div>

<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STUFFING CONTAINER</div>
	<div class="card">
		<div class="card-body">
			<div id="StuffingDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div class="mt-3">
				<button type="button" class="btn btn-primary mr-3" onclick="addStuffingContainer()">Add Container</button>
				<button type="button" class="btn btn-info mr-3" onclick="editStuffingContainer()">Edit Container</button>
				<button type="button" class="btn btn-danger mr-3" onclick="deleteStuffingContainer()">Delete Container</button>
			</div>
		</div>
	</div>
</div>

<div class="card mt-3" id="StuffingContainerFormCard" style="display:none;">
	<div class="card-body">
		<div class="JudulContent" id="StuffingContainerFormTitle" style="color:#699;background-image:url(hdr_back.gif);">BUAT FORM STUFFING CONTAINER</div>
		<form id="StuffingContainerForm" enctype="multipart/form-data">
			<input type="hidden" name="id" id="Stuffing_ID" value="" />
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Order_No">Order No</label>
						<input type="text" name="Order_No" id="Order_No" class="form-control bg-white" value="{{ $vs->Order_No }}" readonly />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Date">Tanggal</label>
						<input type="date" name="Date" id="Date" class="form-control shadow-sm" required />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Time_From">Jam Awal</label>
						<input type="time" name="Time_From" id="Time_From" class="form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Time_To">Jam Akhir</label>
						<input type="time" name="Time_To" id="Time_To" class="form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Police_No">Nopol</label>
						<input type="text" name="Police_No" id="Police_No" class="form-control shadow-sm" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Container">Kontainer</label>
						<input type="text" name="Container" id="Container" class="form-control shadow-sm" required />
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Seal">Seal</label>
						<input type="text" name="Seal" id="Seal" class="form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Tare">Tare</label>
						<input type="number" step="0.01" name="Tare" id="Tare" class="form-control shadow-sm" value="0" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Netto_KG">Netto (Kg)</label>
						<input type="number" step="0.01" name="Netto_KG" id="Netto_KG" class="form-control shadow-sm" value="0" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Netto_MT">Netto (MT)</label>
						<input type="text" name="Netto_MT" id="Netto_MT" class="form-control bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Gross_KG">Gross (Kg)</label>
						<input type="text" name="Gross_KG" id="Gross_KG" class="form-control bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Gross_MT">Gross (MT)</label>
						<input type="text" name="Gross_MT" id="Gross_MT" class="form-control bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Note">Note</label>
						<input type="text" name="Note" id="Note" class="form-control shadow-sm" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Weighbridge_Note">Upload Nota Timbang (Order)</label>
						<input type="file" name="Weighbridge_Note" id="Weighbridge_Note" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" />
						<small class="text-muted">PDF/JPG/JPEG/PNG, maksimal 8 MB. Kosongkan jika tidak ingin mengganti nota.</small>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label>Nota Timbang Saat Ini</label>
						<div id="currentWeighbridgeNote" class="form-control bg-white" style="min-height:38px;height:auto;">Tidak ada nota.</div>
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
						<input type="file" name="Flexibag_Photos[]" id="Flexibag_Photos" class="form-control-file" accept=".jpg,.jpeg,.png" multiple />
						<small class="text-muted">5-6 foto, maksimal 7 foto.</small>
						<div id="existingFlexibagPhotos" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Loading_Start_Photo">Foto Loading Start</label>
						<input type="file" name="Loading_Start_Photo" id="Loading_Start_Photo" class="form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Saat selang masih terhubung ke kontainer.</small>
						<div id="existingLoadingStartPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Loading_Done_Photo">Foto Loading Done</label>
						<input type="file" name="Loading_Done_Photo" id="Loading_Done_Photo" class="form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Saat selang terlepas dan flexibag menggembung.</small>
						<div id="existingLoadingDonePhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Seal_Customs_Photo">Foto Seal Bea Cukai</label>
						<input type="file" name="Seal_Customs_Photo" id="Seal_Customs_Photo" class="form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Seal bea cukai terpasang di belakang kontainer.</small>
						<div id="existingSealCustomsPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Seal_Shipping_Photo">Foto Seal Pelayaran</label>
						<input type="file" name="Seal_Shipping_Photo" id="Seal_Shipping_Photo" class="form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Seal sudah terpasang.</small>
						<div id="existingSealShippingPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Seal_Surveyor_Photo">Foto Seal Surveyor</label>
						<input type="file" name="Seal_Surveyor_Photo" id="Seal_Surveyor_Photo" class="form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Seal sudah terpasang.</small>
						<div id="existingSealSurveyorPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Road_Letter_Photo">Foto Surat Jalan</label>
						<input type="file" name="Road_Letter_Photo" id="Road_Letter_Photo" class="form-control-file" accept=".jpg,.jpeg,.png" />
						<small class="text-muted">Surat jalan sudah terisi lengkap.</small>
						<div id="existingRoadLetterPhoto" class="mt-2 small text-muted">Belum ada foto.</div>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Weighbridge_Photo">Foto Nota Timbang</label>
						<input type="file" name="Weighbridge_Photo" id="Weighbridge_Photo" class="form-control-file" accept=".jpg,.jpeg,.png" />
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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">VESSEL SCHEDULE LOG</div>
	<div class="card">
		<div class="card-body">
			<div id="StuffingDetailLog" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>

<script>
	var StuffingDetail;

	StuffingDetail = new dhtmlXGridObject('StuffingDetail');
	StuffingDetail.setImagePath("./dhtmlx/imgs/");
	StuffingDetail.setHeader("Pilih,No,Order No,Jam Awal (Jam:Menit),Jam Akhir (Jam:Menit),Tanggal,Nopol,Kontainer,Seal,Tare,Netto (Kg),Netto (MT),Gross (Kg),Gross (MT),Note,Nota Timbang");
	StuffingDetail.attachHeader("#rspan,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	StuffingDetail.setInitWidths("45,60,120,90,90,100,160,140,140,90,100,100,100,100,180,100");
	StuffingDetail.setColAlign("center,center,left,center,center,center,left,left,left,right,right,right,right,right,left,left");
	StuffingDetail.setColSorting("str,int,str,str,str,str,str,str,str,int,int,int,int,int,str,str");
	StuffingDetail.setColTypes("ch,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
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
		let tare = parseFloat($('#Tare').val()) || 0;
		let netto = parseFloat($('#Netto_KG').val()) || 0;
		let nettoMT = netto / 1000;
		let gross = tare + netto;

		$('#Netto_MT').val(nettoMT.toFixed(2));
		$('#Gross_KG').val(gross.toFixed(2));
		$('#Gross_MT').val((gross / 1000).toFixed(2));
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

	function resetStuffingForm() {
		$('#StuffingContainerForm')[0].reset();
		$('#Stuffing_ID').val('');
		$('#Date').val('{{ $vs->ETD ?: date('Y-m-d') }}');
		$('#Tare, #Netto_KG').val('0');
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
			alert('Pilih baris data yang ingin diedit.');
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
				$('#Tare').val(response.Tare);
				$('#Netto_KG').val(response.Netto_KG);
				$('#Note').val(response.Note);
				calculateStuffingFormValues();
				setCurrentWeighbridgeNote(response.Note_Url, response.Note_Name);
				renderContainerPhotos(response.photos || {});
				openStuffingForm('EDIT FORM STUFFING CONTAINER');
			},
			error: function() {
				alert('Gagal mengambil data container.');
			}
		});
	}

	function deleteStuffingContainer() {
		let checked = StuffingDetail.getCheckedRows(0);
		let ids = checked ? checked.split(',').filter(function(id) { return id !== ''; }) : [];

		if (ids.length === 0) {
			alert('Pilih container yang ingin dihapus.');
			return;
		}

		if (!confirm('Hapus ' + ids.length + ' container terpilih?')) {
			return;
		}

		$.ajax({
			url: 'cns-stuffing-detail-delete?task={{ $vs->id }}',
			type: 'POST',
			data: {_token: '{{ csrf_token() }}', ids: ids},
			headers: {'Accept': 'application/json'},
			success: function(response) {
				alert(response.message || 'Container berhasil dihapus.');
				$('#StuffingContainerFormCard').hide();
				refreshdataDetail();
				refreshdataDataLog();
			},
			error: function(xhr) {
				if (xhr.responseJSON && xhr.responseJSON.message) {
					alert(xhr.responseJSON.message);
				} else {
					alert('Gagal menghapus container.');
				}
			}
		});
	}

	$('#CancelStuffingContainer').on('click', function() {
		$('#StuffingContainerFormCard').hide();
	});

	$('#StuffingContainerForm').on('submit', function(e) {
		e.preventDefault();

		let formData = new FormData(this);
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
				alert(response.message || 'Data berhasil disimpan.');
				$('#StuffingContainerFormCard').hide();
				resetStuffingForm();
				refreshdataDetail();
				refreshdataDataLog();
			},
			error: function(xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					let messages = [];
					Object.keys(xhr.responseJSON.errors).forEach(function(key) {
						messages.push(xhr.responseJSON.errors[key].join('\n'));
					});
					alert(messages.join('\n'));
				} else if (xhr.responseJSON && xhr.responseJSON.message) {
					alert(xhr.responseJSON.message);
				} else {
					alert('Gagal menyimpan data.');
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

		if (!confirm('Hapus foto ini?')) {
			return;
		}

		let photoId = $(this).data('photo-id');

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
				alert('Foto berhasil dihapus.');
				let row = $('.delete-container-photo[data-photo-id="' + photoId + '"]').closest('div');
				let target = row.parent();
				row.remove();

				if (target.children().length === 0) {
					target.text('Belum ada foto.');
				}
			},
			error: function(xhr) {
				alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus foto.');
			}
		});
	});

	$('#DeliveryOrderForm').on('submit', function(e) {
		e.preventDefault();

		let formData = new FormData(this);
		formData.append('_token', '{{ csrf_token() }}');
		let button = $('#SaveDeliveryOrder');

		button.prop('disabled', true).text('Menyimpan...');

		$.ajax({
			url: 'cns-stuffing-detail-delivery-order?task={{ $vs->id }}',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: {'Accept': 'application/json'},
			success: function(response) {
				alert(response.message || 'Delivery Order berhasil disimpan.');

				if (response.url) {
					$('#currentDeliveryOrder').html(
						$('<a>', {
							href: response.url,
							target: '_blank',
							rel: 'noopener'
						}).text(response.name || 'Lihat Delivery Order')
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
					alert(messages.join('\n'));
				} else if (xhr.responseJSON && xhr.responseJSON.message) {
					alert(xhr.responseJSON.message);
				} else {
					alert('Gagal menyimpan Delivery Order.');
				}
			},
			complete: function() {
				button.prop('disabled', false).text('Simpan DO');
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
				alert(response.message || 'Checklist berhasil disimpan.');
				refreshdataDataLog();
				if (typeof refreshdata === 'function') {
					refreshdata();
				}
			},
			error: function(xhr) {
				alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan checklist.');
			}
		});
	});

	StuffingDetailLog = new dhtmlXGridObject('StuffingDetailLog');
	StuffingDetailLog.setImagePath("./dhtmlx/imgs/");
	StuffingDetailLog.setHeader("No, Action By, Action Date, Action Status, Order (DO)/Booking Confirmation (BC),Qty,Kapal,POL,POD,Stuffing,Checking,Closing CY,ETD,ATD,ETA,ATA,Estimated Day(s), Actual Day(s), Note, Container (Seal)");
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
