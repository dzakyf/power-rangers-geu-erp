@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>PROCUREMENT</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/dashboard1">Master Vendor</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Data</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
	<div id="MasterCompany" style="width:auto; height:400px;"></div>
	<div class="badan">
		<input type="button" name="ExportProduct" id="ExportProduct" value="Excel" onclick="MasterCompany.toExcel('./export/generate.php');" />
		<input type="button" name="NewProduct" id="NewProduct" value="New Supplier" onclick="NewSupplier();" />
	</div>
	<script>
		function NewSupplier() {

			LoadM('MasterCompanyDet', 'purchasing-master-vendor-new');
		}

		function DetailSupplier() {

			MasterCompanyID = MasterCompany.getSelectedId();
			LoadM('MasterCompanyDet', 'purchasing-master-vendor-detail?ID=' + MasterCompanyID);
		}

		function refreshdata() {
			MasterCompany.clearAndLoad("xml-purchasing-master-vendor");

		}
		MasterCompany = new dhtmlXGridObject('MasterCompany');
		MasterCompany.setImagePath("./dhtmlx/imgs/");
		MasterCompany.setHeader("No.,Supplier ID,Supplier Name,Contact Person,City,Phone,Fax,Email,Sales,Sales No,Payment,Category,Sub Category,Group Category, Address, PIC, Letterhead, Footer, Logo, Inverse (Text) Logo");
		MasterCompany.setInitWidths("50,150,220,120,120,120,150,180,180,150,280,200,200,200, 200, 200");
		MasterCompany.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		MasterCompany.setColAlign("center,left,left,left,center,center,center,center,left,center,left,left,left,left,left,left");
		MasterCompany.setColumnColor(",,,,,,,,,,,#E4FCCC,#FFEC94,#D6EDFD,#A7CFE9,#D6EDFD,#E8F2F9,#EEFD62,#F4FF89,#F9FFBC,#F4F9C4,#FBFDE5,#F3D5BD,#F6E4CC,,,,,#9CB770,#AFD07E,#BDDF88,#CBEF96,#D4F99E,#DBFFA4,,,#90E1DE,#D2FAF9,");
		MasterCompany.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		MasterCompany.attachEvent("onRowSelect", DetailSupplier);
		MasterCompany.setColSorting("int,str,str,str,str,str,str,str,str,str");
		MasterCompany.enableSmartRendering(true, 50);
		MasterCompany.enableMultiline(true);
		MasterCompany.setSkin("dhx_skyblue");
		MasterCompany.init();
		refreshdata();
	</script>
</div>
<div id="MasterCompanyDet"></div>

@endsection