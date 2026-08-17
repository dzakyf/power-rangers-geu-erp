<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$partner = DB::table('users')
    ->where('Position', 'MITRA')
    ->where('Status', 'ACTIVE')
    ->get();
 
$kotakab = DB::table('master_kodepos')->select('City')->groupBy("City")->get();

?>
<div class="card mt-3">
	<div class="card-body">
		<h5>Partner Warehouse New</h5>
		<div class="row" id="ContentPages">
            <div class="col-md-6">
                <label for="Warehouse_Location">Lokasi Gudang</label>
                <select name="Warehouse_Location" id="Warehouse_Location" class="form-control">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach ($kotakab as $item)
                        <option value="{{ $item->City }}">{{ $item->City }}</option>
                    @endforeach
                </select>
            </div>
			<div class="col-md-6">
				<label for="Partner_Name">Mitra</label>
                <select name="Partner_Name" id="Partner_Name" class="form-control">
                    <option value="">-- Pilih Mitra --</option>
                    @foreach ($partner as $item)
                        <option value="{{ $item->Emp_ID }}">{{ $item->Name }}</option>
                    @endforeach
                </select>
			</div>
			<div class="col-md-6">
				<label for="warehouse_name">Warehouse Name</label>
				<input type="text" name="warehouse_name" id="warehouse_name" class="form-control" placeholder="Nama Gudang">
			</div>
			<div class="col-md-6">
				<label for="Capacity">Tipe Gudang</label>
				<select name="Warehouse_Type" id="Warehouse_Type" class="form-control">
                    <option value="">-- Pilih Tipe Gudang --</option>
					<option value="booth">booth</option>
					<option value="mobil">mobil</option>
				</select>
			</div>
			<div class="col-md-6">
				<label for="Capacity">Kapasitas</label>
				<input type="text" name="Capacity" id="Capacity" class="form-control">
			</div>
			<div class="col-md-6">
				<label for="lat">Latitude</label>
				<input type="text" name="lat" id="lat" class="form-control" placeholder="e.g. -6.200000">
			</div>
			<div class="col-md-6">
				<label for="lng">Longitude</label>
				<input type="text" name="lng" id="lng" class="form-control" placeholder="e.g. 106.816666">
			</div>
			<div class="col-12 mt-3">
				<button class="btn btn-success" id="SubmitCustDetail">Submit</button>
			</div>
		</div>
	</div>
</div>


<script>
	$('#Warehouse_Location').select2();
	$('#Partner_Name').select2();
	$('#SubmitCustDetail').click(function() {
		$.blockUI({ message: '<h5>Processing...</h5>' });

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-warehouse-list-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					$.unblockUI();
					alert('Data Opportunity Submitted!');

					location.href = "partner-warehouse-list";
				} else {
					alert('Opportunity Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				$.unblockUI();
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