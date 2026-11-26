@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
	'title' => 'Master Consignee',
	'breadcrumbs' => [
		['label' => 'Master Data'],
		['label' => 'Master Consignee'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Data Master Consignee</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" id="NewConsigneeBtn" onclick="NewConsignee();">New Consignee</button>
			<button type="button" class="btn btn-sm btn-info" id="ImportConsigneeBtn" data-bs-toggle="modal" data-bs-target="#ImportConsigneeModal" data-toggle="modal" data-target="#ImportConsigneeModal" onclick="$('#ImportConsigneeModal').modal('show');">Import Excel</button>
			<button type="button" class="btn btn-sm btn-success" id="ExportConsigneeBtn" onclick="MasterConsignee.toExcel('./export/generate.php');">Excel</button>
			<button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata();">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label for="filter_search" class="d-block mb-1"><small>Filter Name / Company ID</small></label>
				<input type="text" id="filter_search" class="text-black form-control form-control-sm" style="width:220px;" placeholder="Cari Name / Company ID..." onkeydown="if(event.key==='Enter'){refreshdata();}" />
			</div>
			<div>
				<button type="button" class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
			</div>
		</div>

		<div class="cns-grid-wrapper">
			<div id="MasterConsignee" class="cns-grid" style="width:auto; height:400px;"></div>
		</div>
	</div>
</div>

@include('cns.master.partials.import-modal', [
	'modalId' => 'ImportConsigneeModal',
	'title' => 'Import Master Consignee (Excel)',
	'templateUrl' => url('cns-master-consignee-import-template'),
	'importUrl' => url('cns-master-consignee-import'),
	'itemLabel' => 'consignee',
	'hint' => 'Wajib isi <code>Company_Name</code>. Baris dengan Company Name yang sudah ada akan dilewati.',
])
<script>
	function NewConsignee() {
		LoadM('MasterCompanyDet', 'cns-master-consignee-new');
	}

	function DetailConsignee() {
		MasterConsigneeID = MasterConsignee.getSelectedId();
		LoadM('MasterCompanyDet', 'cns-master-consignee-detail?ID=' + MasterConsigneeID);
	}

	var MasterConsigneePagination;

	function refreshdata() {
		if (MasterConsigneePagination) {
			MasterConsigneePagination.reload(true);
		}
	}

	MasterConsignee = new dhtmlXGridObject('MasterConsignee');
	MasterConsignee.setImagePath("./dhtmlx/imgs/");
	MasterConsignee.setHeader("No.,Company ID,Company Name,Is Consignee,Is Supplier,Contact Person,City,Phone,Fax,Email,Sales,Sales No,Payment, Address, PIC, Letterhead, Footer, Logo, Inverse (Text) Logo,Stamps");
	MasterConsignee.setInitWidths("50,150,220,120,120,120,150,180,180,150,280,200,200,200,200,200,200,100,100,100");
	MasterConsignee.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	MasterConsignee.setColAlign("center,left,left,left,center,center,center,center,left,center,left,left,left,left,left,left,left,center,center,center");
	MasterConsignee.setColumnColor(",,,,,,,,,,,#E4FCCC,#FFEC94,#D6EDFD,#A7CFE9,#D6EDFD,#E8F2F9,#EEFD62,#F4FF89,#F9FFBC");
	MasterConsignee.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	MasterConsignee.attachEvent("onRowDblClicked", DetailConsignee);
	MasterConsignee.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	MasterConsignee.enableMultiline(true);
	MasterConsignee.setSkin("dhx_skyblue");
	MasterConsignee.init();
	MasterConsigneePagination = initCnsGridPagination(MasterConsignee, {
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
			var search = document.getElementById('filter_search').value.trim();
			if (search) q.append('search', search);
			return 'xml-cns-master-consignee?' + q.toString();
		}
	});
	refreshdata();
</script>
<div id="MasterCompanyDet"></div>

@endsection
