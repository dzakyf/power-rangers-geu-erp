@if(auth()->user()->Priv_ID != 'MITRA')
<div class="card mt-3">
	<div class="card-body">
		<h5>Partner Warehouse</h5>
		<div class="row" id="ContentPages">
			<div class="col-md-6">
				<label for="Warehouse_Location">Warehouse Location</label>
				<input type="hidden" value="{{ $wh->warehouse_task_id }}" name="warehouse_task_id" id="warehouse_task_id">
				<input type="hidden" value="{{ $wh->id }}" name="id" id="id">
				<select name="Warehouse_Location" id="Warehouse_Location" class="form-control">
					<option value="">-- Pilih Lokasi --</option>
					@foreach ($kotakab as $item)
					<option value="{{ $item->City }}" {{ $item->City == $wh->warehouse_location ? ' selected' : '' }}>{{ $item->City }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6">
				<label for="Partner_Name">Mitra</label>
				<select name="Partner_Name" id="Partner_Name" class="form-control">
					<option value="">-- Pilih Mitra --</option>
					@foreach ($partner as $item)
					<option value="{{ $item->lead_id }}" {{ $item->lead_id == $wh->partner_task_id ? ' selected' : '' }}>{{$item->full_name}}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6">
				<label for="warehouse_name">Warehouse Name</label>
				<input type="text" name="warehouse_name" id="warehouse_name" class="form-control" value="{{ $wh->warehouse_name }}">
			</div>
			<div class="col-md-6">
				<label for="Capacity">Tipe Gudang</label>
				<select name="Warehouse_Type" id="Warehouse_Type" class="form-control">
					<option value="booth" {{ $wh->warehouse_type == 'booth' ? ' selected' : '' }}>booth</option>
					<option value="mobil" {{ $wh->warehouse_type == 'mobil' ? ' selected' : '' }}>mobil</option>
				</select>
			</div>
			<div class="col-md-6">
				<label for="Capacity">Kapasitas</label>
				<input type="text" name="Capacity" id="Capacity" class="form-control" value="{{ $wh->capacity }}">
			</div>
			<div class="col-md-6">
				<label for="lat">Latitude</label>
				<input type="text" name="lat" id="lat" class="form-control" value="{{ $wh->lat }}">
			</div>
			<div class="col-md-6">
				<label for="lng">Longitude</label>
				<input type="text" name="lng" id="lng" class="form-control" value="{{ $wh->lng }}">
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
</script>
@endif

<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CONTAINER SYSTEM QUANTITY</div>
		<div class="row mt-3">
			<div class="col-md-4">
				<label class="mb-0">Total System Qty (containers)</label>
				<h4>{{ number_format($totalSystemQty ?? 0, 2, ',', '.') }} L</h4>
			</div>
		</div>
		<div class="table-responsive mt-2">
			<table class="table table-sm table-bordered mb-0">
				<thead>
					<tr>
						<th>Container</th>
						<th class="text-right">Capacity (L)</th>
						<th class="text-right">System Qty (L)</th>
					</tr>
				</thead>
				<tbody>
					@forelse($whContainers ?? [] as $c)
					<tr>
						<td>{{ $c->Name }}</td>
						<td class="text-right">{{ number_format((float) $c->Volume, 2, ',', '.') }}</td>
						<td class="text-right">{{ number_format((float) $c->System_Qty, 2, ',', '.') }}</td>
					</tr>
					@empty
					<tr>
						<td colspan="3" class="text-center text-muted">Belum ada container</td>
					</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UCO OPERATION LOG</div>
		<div id="WarehouseListDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseListDetailDet" name="ekselWarehouseListDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseListDetail.toExcel('./export/generate.php');" />
			<!-- <input type="button" id="newWarehouseListDetail" name="newWarehouseListDetail" value="Update Warehouse Detail" class="btn btn-info mt-2 mr-2" onclick="WarehouseListDetailKlik()" /> -->
			<!-- <input type="button" id="detailWarehouseListDetail" name="newWarehouseListDetail" value="New Warehouse Detail" class="btn btn-success mt-2 mr-2" onclick="WarehouseListDetailNew()" /> -->
		</div>
	</div>
</div>

<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CONTAINER</div>
		<div id="ContainerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" value="Add Container" class="btn btn-success mt-2 mr-2" onclick="showContainerForm()" />
			<input type="button" value="Edit Container" class="btn btn-info mt-2 mr-2" onclick="editContainer()" />
			<input type="button" value="Delete Container" class="btn btn-danger mt-2 mr-2" onclick="deleteContainer()" />
		</div>
	</div>
</div>

<div id="ContainerFormCard" class="card mt-3" style="display:none;">
	<div class="card-body">
		<h6 id="ContainerFormTitle">Add Container</h6>
		<form id="ContainerForm">
			@csrf
			<input type="hidden" name="id" id="container_id">
			<input type="hidden" name="Warehouse_ID" id="container_Warehouse_ID" value="{{ $wh->id }}">
			<div class="form-row">
				<div class="form-group col-md-4">
					<label for="container_Name">Name</label>
					<input type="text" class="form-control" id="container_Name" name="Name" required>
				</div>
				<div class="form-group col-md-4">
					<label for="container_Volume">Volume (KG)</label>
					<input type="number" step="0.01" class="form-control" id="container_Volume" name="Volume">
				</div>
				<div class="form-group col-md-4">
					<label for="container_Status">Status</label>
					<select class="form-control" id="container_Status" name="Status">
						<option value="active">Active</option>
						<option value="inactive">Inactive</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="container_Note">Note</label>
				<textarea class="form-control" id="container_Note" name="Note" rows="2"></textarea>
			</div>
			<button type="button" class="btn btn-primary" id="SubmitContainer">Save</button>
			<button type="button" class="btn btn-secondary ml-2" onclick="$('#ContainerFormCard').hide()">Cancel</button>
		</form>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-warehouse-list-detail-update?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Warehouse Submitted!');

					location.href = "partner-warehouse-list";
				} else {
					alert('Warehouse Failed to Submit!');
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

	function WarehouseListDetailNew() {

		LoadM('divlistOpportunityDetail', 'partner-warehouse-list-detail-new?Task={{ $taskId ?? "" }}');
		return true;
	}

	function WarehouseListDetailKlik() {

		WarehouseListDetailID = WarehouseListDetail.getSelectedId();
		if (WarehouseListDetailID != null) {
			LoadM('divlistOpportunityDetail', 'partner-warehouse-list-detail-detail?Task=' + WarehouseListDetailID);
		} else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseListDetail = new dhtmlXGridObject('WarehouseListDetail');
	WarehouseListDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseListDetail.setHeader("No, Tanggal - Waktu, Tipe, Keterangan, Qty In, Qty Out, Total Ampas, Saldo");
	WarehouseListDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
	WarehouseListDetail.setInitWidths("50,150,100,240,100,100,100,120");
	WarehouseListDetail.setColAlign("center,left,left,left,right,right,right,right");
	WarehouseListDetail.setColSorting("int,str,str,str,int,int,int,str");
	WarehouseListDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
	WarehouseListDetail.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#cspan");
	WarehouseListDetail.enableSmartRendering(true, 150);
	// WarehouseListDetail.attachEvent("onRowDblClicked", WarehouseListDetailKlik);
	WarehouseListDetail.setSkin("dhx_skyblue");
	WarehouseListDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		WarehouseListDetail.clearAndLoad('xml-partner-warehouse-list-detail?Task={{ $taskId ?? "" }}');

	}

	var selectedContainerId = null;

	function initContainerGrid() {
		WarehouseContainer = new dhtmlXGridObject('ContainerGrid');
		WarehouseContainer.setImagePath("./dhtmlx/imgs/");
		WarehouseContainer.setHeader("No,Name,Volume,Note,Warehouse,Status");
		WarehouseContainer.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		WarehouseContainer.setInitWidths("60,150,100,200,150,100");
		WarehouseContainer.setColAlign("center,left,right,left,left,center");
		WarehouseContainer.setColTypes("ro,ro,ro,ro,ro,ro");
		WarehouseContainer.setSkin("dhx_skyblue");
		WarehouseContainer.attachEvent("onRowSelect", function(id) {
			selectedContainerId = WarehouseContainer.getRowAttribute(id, 'ID');
		});
		WarehouseContainer.init();
		WarehouseContainer.clearAndLoad('master-container-data?format=xml&warehouse_id={{ $wh->id }}');
	}

	function showContainerForm() {
		$('#ContainerFormTitle').text('Add Container');
		$('#container_id').val('');
		$('#container_Name').val('');
		$('#container_Volume').val('');
		$('#container_Note').val('');
		$('#container_Status').val('active');
		$('#ContainerFormCard').show();
	}

	function editContainer() {
		if (!selectedContainerId) {
			alert('Pilih data terlebih dahulu');
			return;
		}
		$.get('master-container-data', {
			format: 'json',
			id: selectedContainerId
		}, function(res) {
			var d = res.data && res.data[0];
			if (!d) {
				alert('Data tidak ditemukan');
				return;
			}
			$('#ContainerFormTitle').text('Edit Container');
			$('#container_id').val(d.ID);
			$('#container_Name').val(d.Name);
			$('#container_Volume').val(d.Volume);
			$('#container_Note').val(d.Note);
			$('#container_Status').val(d.Status);
			$('#ContainerFormCard').show();
		});
	}

	function deleteContainer() {
		if (!selectedContainerId) {
			alert('Pilih data terlebih dahulu');
			return;
		}
		if (!confirm('Yakin ingin menghapus container ini?')) return;
		$.ajax({
			url: 'master-container-delete',
			type: 'DELETE',
			data: {
				_token: '{{ csrf_token() }}',
				id: selectedContainerId
			},
			success: function(res) {
				if (res.success) {
					alert('Data berhasil dihapus');
					WarehouseContainer.clearAndLoad('master-container-data?format=xml&warehouse_id={{ $wh->id }}');
					selectedContainerId = null;
				}
			}
		});
	}

	$('#SubmitContainer').on('click', function() {
		var form = document.getElementById('ContainerForm');
		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}
		var id = $('#container_id').val();
		var fd = new FormData(form);
		fd.append('_token', '{{ csrf_token() }}');
		var url = id ? 'master-container-edit' : 'master-container-submit';
		$.ajax({
			url: url,
			type: 'POST',
			data: fd,
			processData: false,
			contentType: false,
			success: function(res) {
				if (res.success) {
					alert('Data berhasil disimpan!');
					$('#ContainerFormCard').hide();
					WarehouseContainer.clearAndLoad('master-container-data?format=xml&warehouse_id={{ $wh->id }}');
				} else {
					alert('Gagal: ' + (res.message || 'Unknown error'));
				}
			},
			error: function(xhr) {
				alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
			}
		});
	});

	initContainerGrid();
</script>