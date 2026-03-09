<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<div class="card mt-3">
	<div class="card-body">
		<h5>Partner Warehouse Detail</h5>
		<div class="row" id="ContentPages1">
            <div class="col-md-6 mt-3">
				<input type="hidden" class="form-control" id="id" name="id" value="{{ $whj->id }}" readonly>
				<input type="hidden" class="form-control" id="warehouse_task_id" name="warehouse_task_id" value="{{ request()->Task }}" readonly>
                <label for="posting_date">Tanggal Journal</label>
				<input type="text" class="form-control bg-white" id="posting_date" name="posting_date" value="{{ date('Y-m-d', strtotime($whj->posting_date)) }}" readonly>
            </div>
			<div class="col-md-6 mt-3">
				<label for="account_no">Nama Akun</label>
				<select name="account_no" id="account_no" class="form-control">
					<option value="">-- Pilih Akun --</option>
					@foreach ($masterAccount as $item)
					<option value="{{ $item->account_no }}"{{$whj->account_no == $item->account_no ? ' selected' : ''}}>{{ $item->account_name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="nominal">Nominal</label>
				<input type="text" id="nominal" name="nominal" class="form-control" value="{{ $whj->nominal }}">
			</div>
			<div class="col-md-6 mt-3">
				<label for="reference">Referensi</label>
				<input type="text" id="reference" name="reference" class="form-control" value="{{ $whj->reference }}">
			</div>
			<div class="col-md-6 mt-3">
				<label for="remark">Catatan</label>
				<textarea name="remark" id="remark" rows="5" class="form-control">{{ $whj->remark }}</textarea>
			</div>
			<div class="col-md-6 mt-3">
				<div id="attachments" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
				<input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="attachment" />
			</div>
			<div class="col-12 mt-3">
				<button class="btn btn-success" id="SubmitCustDetailList">Submit</button>
			</div>
		</div>
	</div>
</div>


<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["posting_date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	function showSeparators(ele)
	{
		const currencyFormatter = new Intl.NumberFormat('en-US', {
			style: 'decimal',
			useGrouping: true
		});
		let id = $(ele).attr('id')
		let val = $('#'+id).val()
		if (['received_qty','total','price'].includes(id)) {
			let qty = $('#received_qty').val()
			let price = $('#price').val()
			if (qty != '' && price != '') {
				let total = qty * price;
				$('#total').val(total);
				$('#total_text').html(currencyFormatter.format(total))
			}
		}
		$("#" + id + "_text").html(currencyFormatter.format(val))
		
	}
	var vaultAttachment = new dhx.Vault("attachments", {
		uploader: {
			target: "upload-receiveinvoice.php"
		},
		filesLimit: 1

	});

	vaultAttachment.events.on("uploadComplete", function(obj) {
		console.log("upload complete")
		$("#attachment").val(obj[0].name);
	});

	vaultAttachment.events.on("uploadBegin", function(obj) {
		console.log("upload begin")
		$("#attachment").val("");

	});
	vaultAttachment.events.on("beforeAdd", function(fileItem) {
		if (vaultAttachment.data.getLength() >= 1) {
			alert('Only can upload 1 file per checkpoint.');
			return false;
		}
	});


	vaultAttachment.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop().toLowerCase();

		var allowed = ["pdf","jpg","jpeg","png","gif","webp"];

		var predicate = allowed.includes(extension);

		if (!predicate) {
			dhtmlx.alert("Hanya boleh mengupload file PDF atau gambar (JPG, PNG, GIF, WEBP)");
		}
		return predicate;
	});
	$('#SubmitCustDetailList').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-warehouse-journal-detail-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Opportunity Submitted!');
					refreshdataDetail();
					$('#divlistOpportunityDetail').html('')
				} else {
					alert('Opportunity Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages1 input,#ContentPages1 select,#ContentPages1 textarea').each(function() {
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