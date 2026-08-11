<div class="mt-3">
	<div class="card">
		<div class="card-header">
			<h4 class="card-title m-2">Master Approval</h4>
		</div>
		<div class="card-body">
			<!-- Stats Card -->
			<div class="row">
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Directorat">Directorat</label>
						<input type="hidden" id="ID" name="ID" value="{{ $detail->ID }}">
						<select name="Directorat" id="Directorat" class="form-control">
							<option value="">Pilih Directorat</option>
							@foreach($directorats as $directorat)
								<option value="{{ $directorat->Directorat }}" {{ $detail->Directorat == $directorat->Directorat ? 'selected' : '' }}>{{ $directorat->Directorat }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Department">Department</label>
						<select name="Department" id="Department" class="form-control">
							<option value="">Pilih Department</option>
							@foreach ($departments as $department)
							<option value="{{$department->Initial}}" {{ $detail->Department == $department->Initial ? 'selected' : '' }}>{{ $department->Department }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Name">Name</label>
						<select name="Name" id="Name" class="form-control">
							<option value="">Pilih Nama Karyawan</option>
							@foreach ($employee as $item)
							<option value="{{ $item->NIK }}" {{ $getUser->Emp_ID == $item->NIK ? 'selected' : '' }}>{{ $item->Name }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Menu">Menu</label>
						<div class="row">
							@foreach ($approvalMenu as $item)
							<div class="col-md-6 mt-auto">
								<input type="checkbox" name="Menu[]" id="Menu{{$loop->iteration}}" value="{{ $item->Menu_ID }}" {{ in_array($item->Menu_ID, $detail->Menu_List) ? 'checked' : '' }}>
								<label for="Menu{{$loop->iteration}}">
									{{ $item->Menu_Name }} - {{ $item->Menu_Modules }}
								</label>
							</div>
							@endforeach
						</div>
						{{-- <label for="Level">Level</label>
						<select name="Level" id="Level" class="form-control">
							<option value="" {{ $detail->Level == '' ? 'selected' : '' }}>Pilih Level</option>
							<option value="0" {{ $detail->Level == 0 ? 'selected' : '' }}>0 - Requestor</option>
							<option value="1" {{ $detail->Level == 1 ? 'selected' : '' }}>1 - Approval Atasan (Department)</option>
							<option value="2" {{ $detail->Level == 2 ? 'selected' : '' }}>2 - Approval dan Requestor HR</option>
							<option value="3" {{ $detail->Level == 3 ? 'selected' : '' }}>3 - Approval Atasasn HR</option>
							<option value="4" {{ $detail->Level == 4 ? 'selected' : '' }}>4 - Approval Director</option>
						</select> --}}
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Status">Status</label>
						<select name="Status" id="Status" class="form-control">
							<option{{ $detail->Status == 'ACTIVE' ? ' selected' : '' }}>ACTIVE</option>
							<option{{ $detail->Status == 'INACTIVE' ? ' selected' : '' }}>INACTIVE</option>
						</select>
					</div>
				</div>
				<div class="col-12">
					<button class="btn btn-success" id="submitBtn">Submit</button>
				</div>
			</div>
		</div>
	</div>

</div>
<script>

$('#Directorat').change(function() {
	var directorat = $(this).val();
	$.ajax({
		url: 'hrd-get-departments',
		type: 'POST',
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		data: {
			directorat: directorat
		},
		success: function(data) {
			$('#Department').html('<option value="">Pilih Department</option>');
			$.each(data, function(key, value) {
				$('#Department').append('<option value="' + value.Initial + '">' + value.Department + '</option>');
			});
		}
	});
});

$('#Department').change(function() {
	var department = $(this).val();
	var directorat = $('#Directorat').val();
	$.ajax({
		url: 'hrd-get-employee',
		type: 'POST',
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		data: {
			directorat: directorat,
			department: department
		},
		success: function(data) {
			$('#Name').html('<option value="">Pilih Nama Karyawan</option>');
			$.each(data, function(key, value) {
				$('#Name').append('<option value="' + value.NIK + '">' + value.Name + '</option>');
			});
		}
	});
});

$('#submitBtn').click(function() {
	var valuesByName = $("input[name='Menu[]']:checked").map(function() {
		return this.value;
	}).get();
	$.ajax({
		url: 'hrd-master-approval-detail-submit',
		type: 'POST',
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		data: {
			ID: $('#ID').val(),
			directorat: $('#Directorat').val(),
			department: $('#Department').val(),
			name: $('#Name').val(),
			menu: valuesByName
		},
		success: function(data) {
			console.log(data);
			if (data.status === 'success') {
				alert('Data berhasil disimpan');
				setTimeout(function() {
					reloadgroup();
					$('#MasterSectionDiv').html('');
				}, 500);

			} else {
				alert('Terjadi kesalahan saat menyimpan data');
			}
		}
	});
});


</script>
