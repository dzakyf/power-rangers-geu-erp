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
                <label for="recevied_date">Tanggal Terima</label>
				<input type="hidden" class="form-control" id="warehouse_task_id" name="warehouse_task_id" value="{{ $wh->warehouse_task_id }}" readonly>
				<input type="hidden" class="form-control" id="id" name="id" value="{{ $wh->id }}" readonly>
				<input type="text" class="form-control" id="received_date" name="received_date" value="{{ date('Y-m-d') }}" readonly>
            </div>
			<div class="col-md-6 mt-3">
				<label for="pickup_type">Tipe Penjemputan</label>
				<select name="pickup_type" id="pickup_type" class="form-control">
					<option value="PICKUP">PICKUP</option>
					<option value="MANDIRI">MANDIRI</option>
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="name_poo">Nama POO</label>
				<input type="text" id="name_poo" name="name_poo" class="form-control" value="{{ $wh->name_poo }}">
			</div>
			<div class="col-md-6 mt-3">
				<label for="source">Source</label>
				<select name="source" id="source" class="form-control">
					<option value="HUB GEU" {{ $wh->source == 'HUB GEU' ? 'selected' : '' }}>HUB GEU</option>
					<option value="MITRA" {{ $wh->source == 'MITRA' ? 'selected' : '' }}>MITRA</option>
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="coverage">Coverage</label>
				<select name="coverage" id="coverage" class="form-control">
					@foreach ($kelurahan as $item)
						<option value="{{ $item->District }}" {{ $wh->coverage == $item->District ? 'selected' : '' }}>{{ $item->District }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="received_qty">Volume diterima</label>
				<div class="row">
					<div class="col-10">
						<input type="text" id="received_qty" name="received_qty" class="form-control" value="{{ $wh->received_qty }}" onkeyup="showSeparators(this)">
					</div>
					<div class="col-2 my-auto text-danger font-weight-bold text-right pr-3">
						<span id="received_qty_text"></span>
					</div>
				</div>
			</div>
			<div class="col-md-6 mt-3">
				<label for="price">Harga / Liter</label>
				<div class="row">
					<div class="col-10">
						<input type="text" id="price" name="price" class="form-control" value="{{ $wh->price }}" onkeyup="showSeparators(this)" >
					</div>
					<div class="col-2 my-auto text-danger font-weight-bold text-right pr-3">
						Rp <span id="price_text"></span>
					</div>
				</div>
			</div>
			<div class="col-md-6 mt-3">
				<label for="total">Total diterima</label>
				<div class="row">
					<div class="col-10">
						<input type="text" id="total" name="total" class="form-control" value="{{ $wh->total }}" onkeyup="showSeparators(this)" readonly>
					</div>
					<div class="col-2 my-auto text-danger font-weight-bold text-right pr-3">
						Rp <span id="total_text"></span>
					</div>
				</div>
			</div>
			<div class="col-md-6 mt-3">
				<label for="rejected_qty">Volume Reject (jika ada)</label>
				<div class="row">
					<div class="col-10">
						<input type="text" id="rejected_qty" name="rejected_qty" class="form-control" value="{{ $wh->rejected_qty }}" onkeyup="showSeparators(this)">
					</div>
					<div class="col-2 my-auto text-danger font-weight-bold text-right pr-3">
						<span id="rejected_qty_text"></span>
					</div>
				</div>
			</div>
			<div class="col-md-6 mt-3">
				@if ($wh->attachment)
				<label for="lihatDocument">Lihat Dokumen</label>
				<div class="form-control mb-3" id="lihatDocument">
					{{ $wh->attachment }}
				</div>
				@endif
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
	$(document).ready(function () {
		$('#price').keyup();
		$('#received_qty').keyup();
		$('#total').keyup();
		$('#rejected_qty').keyup();
	})
	$('#lihatDocument').on('click', function() {
		window.open($(this).text(), "_blank");
	})
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
			url: "partner-warehouse-list-detail-detail-submit?_token={{ csrf_token() }}",
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