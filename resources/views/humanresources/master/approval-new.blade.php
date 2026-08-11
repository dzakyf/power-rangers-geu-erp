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
						<select name="Directorat" id="Directorat" class="form-control">
							<option value="">Pilih Directorat</option>
							@foreach($directorats as $directorat)
								<option value="{{ $directorat->Directorat }}">{{ $directorat->Directorat }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Department">Department</label>
						<select name="Department" id="Department" class="form-control">
							<option value="">Pilih Department</option>
						</select>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Name">Name</label>
						<select name="Name" id="Name" class="form-control">
							<option value="">Pilih Nama Karyawan</option>
						</select>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<!-- div class="form-group">
						<label for="Level">Level</label>
						<select name="Level" id="Level" class="form-control">
							<option value="">Pilih Level</option>
							<option value="0">0 - Requestor</option>
							<option value="1">1 - Approval Atasan (Department)</option>
							<option value="2">2 - Approval dan Requestor HR</option>
							<option value="3">3 - Approval Atasasn HR</option>
							<option value="4">4 - Approval Director</option>
						</select>
					</div -->
					<div class="form-group">
						<label for="Menu">Menu</label>
						<div class="row">
							@foreach ($approvalMenu as $item)
							<div class="col-md-6 mt-auto">
								<input type="checkbox" name="Menu[]" id="Menu{{$loop->iteration}}" value="{{ $item->Menu_ID }}">
								<label for="Menu{{$loop->iteration}}">
									{{ $item->Menu_Name }} - {{ $item->Menu_Modules }}
								</label>
							</div>
							@endforeach
						</div>
						{{-- <select name="Menu" id="Menu" class="form-control">
							@foreach ($approvalMenu as $item)
								<option value="{{ $item->Menu_ID }}">{{ $item->Menu_Name }} - {{ $item->Menu_Modules }}</option>
							@endforeach
						</select> --}}

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
	// $(document).ready(function() {
	// 	$('#Menu').select2();
	// });
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
		url: 'hrd-master-approval-new-submit',
		type: 'POST',
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		data: {
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
