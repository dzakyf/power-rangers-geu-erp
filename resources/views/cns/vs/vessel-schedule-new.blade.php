<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">VESSEL SCHEDULE</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<div class="col-12">
							<div class="form-group">
								<label for="Order_No">Order No</label>
								<input type="text" name="Order_No" id="Order_No" value="" class="form-control" required />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Qty1">Qty</label>
								<div class="row">
									<div class="col">
										<select name="Qty1" id="Qty1" class="form-control" required>
											<option value="">-- Pilih Qty --</option>
											@foreach (range(1,1000) as $item)
											<option value="{{$item}}">{{$item}}</option>
											@endforeach
										</select>
									</div>
									<div class="col">
										<select name="Qty2" id="Qty2" class="form-control" required>
											<option value="">-- Pilih Qty --</option>
											<option value="20 FT">20 FT</option>
											<option value="20 ISOTANK">20 ISOTANK</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Vessel">Vessel</label>
								<input type="text" name="Vessel" id="Vessel" value="" class="form-control" required />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="POL">POL</label>
								<input type="text" name="POL" id="POL" value="" class="form-control" required />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="POD">POD</label>
								<input type="text" name="POD" id="POD" value="" class="form-control" required />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Stuffing">Stuffing</label>
								<input type="text" name="Stuffing" id="Stuffing" value="{{ date('Y-m-d') }}" class="form-control" required readonly />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Checking_CY">Checking_CY</label>
								<input type="text" name="Checking_CY" id="Checking_CY" value="{{ date('Y-m-d') }}" class="form-control" required readonly />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="ETD">ETD</label>
								<input type="text" name="ETD" id="ETD" value="{{ date('Y-m-d') }}" class="form-control" required readonly />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="ETA">ETA</label>
								<input type="text" name="ETA" id="ETA" value="{{ date('Y-m-d') }}" class="form-control" required readonly />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Note">Remark</label>
								<textarea name="Note" id="Note" rows="5" class="form-control"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Publish" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Stuffing", "Checking_CY", "ETD", "ATD", "ETA", "ATA"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "cns-vessel-schedule-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Announcement Submitted!');

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