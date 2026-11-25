@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
	'title' => 'Master Company',
	'breadcrumbs' => [
		['label' => 'Master Data'],
		['label' => 'Master Company'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Data Master Company</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" id="NewProduct" onclick="NewCompany();">New Company</button>
			<button type="button" class="btn btn-sm btn-info" id="ImportCompanyBtn" data-bs-toggle="modal" data-bs-target="#ImportCompanyModal" data-toggle="modal" data-target="#ImportCompanyModal" onclick="$('#ImportCompanyModal').modal('show');">Import Excel</button>
			<button type="button" class="btn btn-sm btn-success" id="ExportProduct" onclick="MasterCompany.toExcel('./export/generate.php');">Excel</button>
			<button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata();">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label for="filter_type" class="d-block mb-1"><small>Pilih Jenis</small></label>
				<select id="filter_type" class="form-control form-control-sm" style="width:auto;">
					<option value="">All</option>
					<option value="CONSIGNEE">Consignee / Buyer</option>
					<option value="SUPPLIER">Vendor / Supplier</option>
				</select>
			</div>
			<div>
				<button type="button" class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
			</div>
		</div>

		<div class="cns-grid-wrapper">
			<div id="MasterCompany" class="cns-grid" style="width:auto; height:400px;"></div>
		</div>
	</div>
</div>

<div class="modal fade" id="ImportCompanyModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Import Master Company (Excel)</h5>
				<button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" onclick="$('#ImportCompanyModal').modal('hide');">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<a href="{{ url('cns-master-company-import-template') }}" class="btn btn-outline-primary btn-sm">Download Template Excel (.xlsx)</a>
					<small class="text-muted d-block mt-1">Wajib isi <code>Company_Name</code> dan <code>Email</code> (pisahkan dengan koma/titik koma jika lebih dari 1, maks 10). Baris dengan Company Name yang sudah ada akan dilewati.</small>
				</div>
				<div class="form-group">
					<label for="ImportCompanyFile">Pilih File Excel (.xlsx, .xls, .csv)</label>
					<input type="file" id="ImportCompanyFile" class="form-control-file" accept=".xlsx,.xls,.csv" />
				</div>
				<div id="ImportCompanyResult" class="mt-3" style="display:none;"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="$('#ImportCompanyModal').modal('hide');">Tutup</button>
				<button type="button" class="btn btn-primary" id="SubmitImportCompanyBtn" onclick="submitImportCompany();">Upload &amp; Import</button>
			</div>
		</div>
	</div>
</div>
<script>
	function NewCompany() {

		LoadM('MasterCompanyDet', 'cns-master-company-new');
	}

	function DetailCompany() {

		MasterCompanyID = MasterCompany.getSelectedId();
		LoadM('MasterCompanyDet', 'cns-master-company-detail?ID=' + MasterCompanyID);
	}

	var MasterCompanyPagination;

	function refreshdata() {
		if (MasterCompanyPagination) {
			MasterCompanyPagination.reload(true);
		}
	}

	function escapeImportHtml(str) {
		return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
	}

	function submitImportCompany() {
		const fileInput = document.getElementById('ImportCompanyFile');
		const resultBox = document.getElementById('ImportCompanyResult');
		if (!fileInput.files.length) {
			swal('Perhatian', 'Pilih file Excel terlebih dahulu.', 'warning');
			return;
		}

		const formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('file', fileInput.files[0]);

		const btn = document.getElementById('SubmitImportCompanyBtn');
		btn.disabled = true;
		resultBox.style.display = 'none';

		$.ajax({
			url: 'cns-master-company-import',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (res) {
				let html = `<div class="alert alert-success mb-2">Berhasil import <strong>${Number(res.imported)}</strong> company.</div>`;
				if (res.skipped && res.skipped.length) {
					html += `<div class="alert alert-warning mb-2"><strong>Dilewati (${res.skipped.length}):</strong><ul class="mb-0">` +
						res.skipped.map(r => `<li>Baris ${Number(r.row)} (${escapeImportHtml(r.company_name)}): ${escapeImportHtml(r.reason)}</li>`).join('') + '</ul></div>';
				}
				if (res.errors && res.errors.length) {
					html += `<div class="alert alert-danger mb-0"><strong>Gagal (${res.errors.length}):</strong><ul class="mb-0">` +
						res.errors.map(r => `<li>Baris ${Number(r.row)} (${escapeImportHtml(r.company_name || '-')}): ${escapeImportHtml(r.reason)}</li>`).join('') + '</ul></div>';
				}
				resultBox.innerHTML = html;
				resultBox.style.display = 'block';
				if (res.imported > 0) {
					refreshdata();
				}
			},
			error: function (xhr) {
				const msg = xhr.responseJSON?.message || 'Gagal mengimport file Excel.';
				resultBox.innerHTML = `<div class="alert alert-danger mb-0">${escapeImportHtml(msg)}</div>`;
				resultBox.style.display = 'block';
			},
			complete: function () {
				btn.disabled = false;
			}
		});
	}

	MasterCompany = new dhtmlXGridObject('MasterCompany');
	MasterCompany.setImagePath("./dhtmlx/imgs/");
	MasterCompany.setHeader("No.,Company ID,Company Name,Is Consignee,Is Supplier,Contact Person,City,Phone,Fax,Email,Sales,Sales No,Payment, Address, PIC, Letterhead, Footer, Logo, Inverse (Text) Logo,Stamps");
	MasterCompany.setInitWidths("50,150,220,120,120,120,150,180,180,150,280,200,200,200,200,200,200,100,100,100");
	MasterCompany.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	MasterCompany.setColAlign("center,left,left,left,center,center,center,center,left,center,left,left,left,left,left,left,left,center,center,center");
	MasterCompany.setColumnColor(",,,,,,,,,,,#E4FCCC,#FFEC94,#D6EDFD,#A7CFE9,#D6EDFD,#E8F2F9,#EEFD62,#F4FF89,#F9FFBC");
	MasterCompany.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	MasterCompany.attachEvent("onRowDblClicked", DetailCompany);
	MasterCompany.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	MasterCompany.enableMultiline(true);
	MasterCompany.setSkin("dhx_skyblue");
	MasterCompany.init();
	MasterCompanyPagination = initCnsGridPagination(MasterCompany, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		getUrl: function (params) {
			var q = new URLSearchParams();
			q.append('posStart', params.posStart);
			q.append('count', params.count);
			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.append('sort_col', params.sortCol);
				q.append('sort_dir', params.sortDir || 'asc');
			}
			if (params.filters) {
				Object.keys(params.filters).forEach(function (colIdx) {
					q.append('filter[' + colIdx + ']', params.filters[colIdx]);
				});
			}
			var type = document.getElementById('filter_type').value;
			if (type) q.append('type', type);
			return 'xml-cns-master-company?' + q.toString();
		}
	});
	refreshdata();
</script>
<div id="MasterCompanyDet"></div>

@endsection