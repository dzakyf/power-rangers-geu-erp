@extends('layout.mainlayout-cns')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>CNS</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/dashboard1">Master Consignee</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Data</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="badan mb-2">
	<div class="row m-1 align-items-end">
		<div class="col-auto">
			<label for="filter_search" class="d-block mb-1">Filter Name / Company ID</label>
			<input type="text" id="filter_search" class="form-control d-inline-block w-auto mr-2" placeholder="Cari Name / Company ID..." onkeydown="if(event.key==='Enter'){refreshdata();}" />

			<button type="button" class="btn btn-primary" onclick="refreshdata()">Filter</button>
		</div>
	</div>

	<div class="m-2">
		<div id="MasterConsignee" style="width:auto; height:400px;"></div>
	</div>
	<div class="m-2">
		<button class="btn btn-primary" id="NewConsigneeBtn" onclick="NewConsignee();">New Consignee</button>
		<button class="btn btn-success" id="ExportConsigneeBtn" onclick="MasterConsignee.toExcel('./export/generate.php');">Excel</button>
	</div>
</div>
<script>
	function NewConsignee() {
		LoadM('MasterCompanyDet', 'cns-master-consignee-new');
	}

	function DetailConsignee() {
		MasterConsigneeID = MasterConsignee.getSelectedId();
		LoadM('MasterCompanyDet', 'cns-master-consignee-detail?ID=' + MasterConsigneeID);
	}

	function refreshdata() {
		const search = document.getElementById('filter_search').value.trim();
		const url = search ? `xml-cns-master-consignee?search=${encodeURIComponent(search)}` : 'xml-cns-master-consignee';
		MasterConsignee.clearAndLoad(url);
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
	MasterConsignee.enableSmartRendering(true, 50);
	MasterConsignee.enableMultiline(true);
	MasterConsignee.setSkin("dhx_skyblue");
	MasterConsignee.init();
	refreshdata();
</script>
<div id="MasterCompanyDet"></div>

@endsection
