
<div class="card mt-3">
	<div class="card-body">
		<h5>Partner Warehouse Out New</h5>
		<div class="row" id="ContentPages">
            <div class="col-md-6 mb-3">
                <label for="request_date">Tanggal Keluar</label>
				<input type="text" name="request_date" id="request_date" class="form-control bg-white" value="{{ date('Y-m-d') }}" readonly>
            </div>
			<div class="col-md-6 mb-3">
				<input type="hidden" name="warehouse_task_id" id="warehouse_task_id" value="{{ $task }}">
			</div>
			<div class="col-md-6 mb-3">
				<label for="proposed_qty">Jumlah stock keluar</label>
				<input type="text" name="proposed_qty" id="proposed_qty" class="form-control" onkeyup="calculateTotal()">
			</div>
			<div class="col-md-6 mb-3">
				<label for="price">Harga stock saat ini <span class="text-secondary">(sesuai dengan tanggal keluar)</span></label>
				<input type="text" name="price" id="price" class="form-control" value="{{ $masterPrice->price }}" readonly>
			</div>
			<div class="col-md-6 mb-3">
				<label for="total">Total Harga</label>
				<input type="text" name="total" id="total" class="form-control">
			</div>
			<div class="col-md-6 mb-3">
				<label for="reference">Referensi</label>
				<input type="text" name="reference" id="reference" class="form-control">
			</div>
			<div class="col-12 mb-3">
				<label for="remark">Catatan</label>
				<textarea name="remark" id="remark" rows="3" class="form-control"></textarea>
			</div>
			<div class="col-12 mt-3">
				<button class="btn btn-success" id="SubmitCustDetail">Submit</button>
			</div>
		</div>
	</div>
</div>


<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["request_date"]);
	myCalendar.setDateFormat("%Y-%m-%d");
	myCalendar.attachEvent("onChange", function(date, state) {
		if(state) {
			date = new Date(date);
			let year = date.getFullYear();
			let month = date.getMonth() + 1;
			let datenow = date.getDate();
			date = year + "-" + String(month).padStart(2, '0') + "-" + String(datenow).padStart(2, "0");
			$.ajax({
				url:"partner-get-master-price",
				type:"POST",
				data:{_token: "{{ csrf_token() }}",date},
				success:function(res){
					let data = JSON.parse(res);
					if (data.status == 'success') {
						$('#price').val(data.data.price);
						calculateTotal();
					}else {
						alert("tanggal belum terset untuk harga");
					}
				}
			})
		}
	});

	function calculateTotal()
	{
		let price = $('#price').val();
		let qty = $('#proposed_qty').val();
		if (qty != '' && qty != 0 && price != '' && price != 0) {
			$('#total').val(parseFloat(price) * parseFloat(qty));
		}
	}

	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-warehouse-request-detail-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Opportunity Submitted!');
					refreshdataDetail();
					$('#divlistOpportunityDetail').html('');
				} else {
					alert('Opportunity Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var msg = "Error Internal! Please Try again!";
				if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
					msg = jqXHR.responseJSON.error;
				}
				alert(msg);

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