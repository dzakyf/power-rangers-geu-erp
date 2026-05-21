@extends('layout.mainlayout-cns')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>CNS</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/dashboard1">Master Company</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Data</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="badan mb-2">
	<!-- <div class="row  m-1 align-items-end">
		<div class="col-auto">
			<label for="Region" class="d-block mb-1">Pilih Jenis</label>
			<select id="filter_type" class="form-control d-inline-block w-auto mr-2">
				<option value="">All</option>
				<option value="CONSIGNEE">Consignee / Buyer</option>
				<option value="SUPPLIER">Vendor / Supplier</option>
			</select>

			<button type="button" class="btn btn-primary" onclick="refreshdata()">Filter</button>
		</div>
	</div> -->

	<div class="m-2">
		<div id="MasterCompany" style="width:auto; height:400px;"></div>
	</div>
	<div class="m-2">
		<button class="btn btn-primary" id="NewProduct" onclick="NewCompany();">New Company</button>
		<button class="btn btn-success" id="ExportProduct" onclick="MasterCompany.toExcel('./export/generate.php');">Excel</button>
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

	function refreshdata() {
		const type = 'SUPPLIER';
		const url = type ? `xml-cns-master-company?type=${type}` : 'xml-cns-master-company';
		MasterCompany.clearAndLoad(url);
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
	MasterCompany.enableSmartRendering(true, 50);
	MasterCompany.enableMultiline(true);
	MasterCompany.setSkin("dhx_skyblue");
	MasterCompany.init();
	refreshdata();
</script>
<div id="MasterCompanyDet"></div>

@endsection