@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>PROCUREMENT</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/dashboard1">Master Product Code</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Data</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
	<div id="PRPRODUCTLIST" style="width:auto; height:300px;"></div>
	<div class="badan">
		<input type="button" name="ExportProduct" id="ExportProduct" value="Excel" class="btn btn-info mt-2 mr-2" onclick="PRProductList.toExcel('./export/generate.php');" />
		<input type="button" name="NewProduct" id="NewProduct" value="New Product" class="btn btn-success mt-2 mr-2" onclick="NewProducts();" />
		<input type="button" name="DeleteProduct" id="DeleteProduct" value="Delete Product" class="btn btn-danger mt-2 mr-2" />
	</div>
</div>

<div id="PRPRODUCTLISTDet"></div>

<script>
	function NewProducts() {

		LoadM('PRPRODUCTLISTDet', 'master-product-code-new');
	}

	function DetailProductList() {

		PRProductListID = PRProductList.getSelectedId();
		LoadM('PRPRODUCTLISTDet', 'master-product-code-detail?ID=' + PRProductListID);
	}

	PRProductList = new dhtmlXGridObject('PRPRODUCTLIST');
	PRProductList.setImagePath("dhtmlx/imgs/");
	PRProductList.setHeader("No.,Category,Sub Category,Type,Product Code,Description,Specification,Brand,Type,Unit Name,Path");
	PRProductList.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	PRProductList.setInitWidths("80,100,100,100,100,200,200,100,100,100,100");
	PRProductList.setColAlign("center,center,center,left,center,left,left,center,center,center,left");
	PRProductList.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	PRProductList.attachEvent("onRowSelect", DetailProductList);
	PRProductList.setColSorting("int,str,str,str,str,str,str,str");
	PRProductList.enableSmartRendering(true, 50);
	PRProductList.enableMultiline(true);
	PRProductList.setSkin("dhx_skyblue");
	PMasterGroup = new dataProcessor("master-product-code-update");
	PMasterGroup.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})
	PMasterGroup.setUpdateMode("Off");
	PMasterGroup.init(PRProductList);
	PRProductList.init();
	refreshdata();

	function refreshdata() {

		PRProductList.clearAndLoad("xml-master-product-code");


	}

	$("#DeleteProduct").click(function() {
		PRProductList.deleteSelectedRows();
		PMasterGroup.sendData();
		alert("Data were deleted!");
		refreshdata();


	});
</script>

@endsection