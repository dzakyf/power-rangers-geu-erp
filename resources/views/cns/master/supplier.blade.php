@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
	'title' => 'Master Supplier',
	'breadcrumbs' => [
		['label' => 'Master Data'],
		['label' => 'Master Supplier'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Data Master Supplier</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" id="NewProduct" onclick="NewCompany();">New Company</button>
			<button type="button" class="btn btn-sm btn-info" id="ImportSupplierBtn" data-bs-toggle="modal" data-bs-target="#ImportSupplierModal" data-toggle="modal" data-target="#ImportSupplierModal" onclick="$('#ImportSupplierModal').modal('show');">Import Excel</button>
			<button type="button" class="btn btn-sm btn-success" id="ExportProduct" onclick="MasterCompany.toExcel('./export/generate.php');">Excel</button>
			<button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata();">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-grid-wrapper">
			<div id="MasterCompany" class="cns-grid" style="width:auto; height:400px;"></div>
		</div>
	</div>
</div>

@include('cns.master.partials.import-modal', [
	'modalId' => 'ImportSupplierModal',
	'title' => 'Import Master Supplier (Excel)',
	'templateUrl' => url('cns-master-supplier-import-template'),
	'importUrl' => url('cns-master-supplier-import'),
	'itemLabel' => 'supplier',
	'hint' => 'Wajib isi <code>Company_Name</code>. Baris dengan Company Name yang sudah ada akan dilewati.',
])
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
			q.append('type', 'SUPPLIER');
			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.append('sort_col', params.sortCol);
				q.append('sort_dir', params.sortDir || 'asc');
			}
			if (params.filters) {
				Object.keys(params.filters).forEach(function (colIdx) {
					q.append('filter[' + colIdx + ']', params.filters[colIdx]);
				});
			}
			return 'xml-cns-master-company?' + q.toString();
		}
	});
	refreshdata();
</script>
<div id="MasterCompanyDet"></div>

@endsection