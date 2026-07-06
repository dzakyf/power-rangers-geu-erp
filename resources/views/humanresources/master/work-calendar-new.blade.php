<div class="mt-3">
	<div class="card">
		<div class="card-header">
			<h4 class="card-title m-2">Master Work Calendar</h4>
		</div>
		<div class="card-body">
			<!-- Stats Card -->
			<div class="row">
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Name">Name</label>
						<input type="text" id="Name" class="form-control" name="Name" />
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Employee_Type">Employee Type</label>
						<select name="Employee_Type" id="Employee_Type" class="form-control">
							<option value="OFFICE">OFFICE</option>
							<option value="SHIFT">SHIFT</option>
						</select>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Weekly_Hours">Weely Hours</label>
						<input type="number" id="Weekly_Hours" class="form-control" name="Weekly_Hours" />
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

$('#submitBtn').click(function() {
	$.ajax({
		url: 'hrd-master-work-calendar-new-submit',
		type: 'POST',
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		data: {
			Name: $('#Name').val(),
			Employee_Type: $('#Employee_Type').val(),
			Weekly_Hours: $('#Weekly_Hours').val(),
		},
		success: function(data) {
			console.log(data);
			if (data.status === 'success') {
				alert('Data berhasil disimpan');
				setTimeout(function() {
					reloadgroup();
					$('#WorkCalendarDiv').html('');
				}, 500);

			} else {
				alert('Terjadi kesalahan saat menyimpan data');
			}
		}
	});
});


</script>
