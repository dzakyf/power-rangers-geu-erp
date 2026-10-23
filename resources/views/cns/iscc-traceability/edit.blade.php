<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT TRACEABILITY DATA</div>

		<div class="mb-3">
			<strong>POO Name:</strong> {{ $fragment->poo_name }}<br />
			<strong>Supplier:</strong> {{ $fragment->supplier_name }}<br />
			<strong>Surat Jalan Date:</strong> {{ $fragment->delivery_date ? date('d-M-Y', strtotime($fragment->delivery_date)) : '' }}<br />
			<strong>Quantity:</strong> {{ number_format($fragment->quantity_kg, 2) }} Kg<br />
			<strong>Status:</strong> {{ $fragment->delivery_status }}
		</div>

		<form id="traceEditForm">
			@csrf
			<input type="hidden" name="fragment_id" value="{{ $fragment->id }}" />
			<div class="form-group">
				<label for="quantity_kg">Quantity (Kg)</label>
				<input type="number" step="0.01" min="0" name="quantity_kg" id="quantity_kg" class="form-control" value="{{ $fragment->quantity_kg }}" />
			</div>
			<div class="form-group">
				<label for="full_address">Full Address</label>
				<textarea name="full_address" id="full_address" class="form-control" rows="2">{{ $fragment->full_address }}</textarea>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="lat">Latitude</label>
					<input type="text" name="lat" id="lat" class="form-control" value="{{ $fragment->lat }}" />
				</div>
				<div class="form-group col-md-6">
					<label for="lng">Longitude</label>
					<input type="text" name="lng" id="lng" class="form-control" value="{{ $fragment->lng }}" />
				</div>
			</div>
			<div class="form-group">
				<label for="oil_documentation">Oil Documentation / Notes</label>
				<textarea name="oil_documentation" id="oil_documentation" class="form-control" rows="4">{{ $fragment->oil_documentation }}</textarea>
			</div>
			<input type="button" value="Save Changes" class="btn btn-primary" onclick="saveFragmentEdit()" />
		</form>

		<hr />

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PHOTOS (max 3)</div>
		<div id="photoList" class="mt-2">
			@foreach ($photos as $photo)
				<div class="d-inline-block position-relative mr-2 mb-2" id="photo-{{ $photo->ID }}">
					<img src="{{ asset('filevendor/' . $photo->Path) }}" width="120" height="120" style="object-fit:cover;border:1px solid #ccc;" />
					<button type="button" class="btn btn-danger btn-sm" style="position:absolute;top:0;right:0;" onclick="deletePhoto({{ $photo->ID }})">X</button>
				</div>
			@endforeach
		</div>
		@if (count($photos) < 3)
			<form id="photoForm" class="mt-2" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="fragment_id" value="{{ $fragment->id }}" />
				<input type="file" name="photos[]" id="photos" class="form-control-file" accept="image/*" multiple />
				<input type="button" value="Upload Photos" class="btn btn-success mt-2" onclick="uploadPhotos()" />
			</form>
		@endif
	</div>
</div>

<script>
	function saveFragmentEdit() {
		var formData = new FormData(document.getElementById('traceEditForm'));
		$.ajax({
			url: 'cns-iscc-traceability-update',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (resp) {
				if (resp.status === 'success') {
					dhtmlx.alert('Data saved.');
					refreshdata();
				} else {
					dhtmlx.alert(resp.message || 'Failed to save.');
				}
			},
			error: function (xhr) {
				var msg = 'Failed to save.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					msg = xhr.responseJSON.message;
				}
				dhtmlx.alert(msg);
			}
		});
	}

	function uploadPhotos() {
		var formData = new FormData(document.getElementById('photoForm'));
		$.ajax({
			url: 'cns-iscc-traceability-photo-upload',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (resp) {
				if (resp.status === 'success') {
					dhtmlx.alert(resp.message);
					reloadEdit();
				} else {
					dhtmlx.alert(resp.message || 'Failed to upload.');
				}
			},
			error: function (xhr) {
				var msg = 'Failed to upload.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					msg = xhr.responseJSON.message;
				}
				dhtmlx.alert(msg);
			}
		});
	}

	function deletePhoto(photoId) {
		dhtmlx.confirm('Delete this photo?', 'Delete Photo', function (r) {
			if (!r) return;
			$.ajax({
				url: 'cns-iscc-traceability-photo-delete',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					photo_id: photoId
				},
				success: function (resp) {
					if (resp.status === 'success') {
						dhtmlx.alert('Photo deleted.');
						reloadEdit();
					} else {
						dhtmlx.alert(resp.message || 'Failed to delete.');
					}
				}
			});
		});
	}

	function reloadEdit() {
		LoadM('divTraceDetail', 'cns-iscc-traceability-edit/' + {{ $fragment->id }});
	}
</script>
