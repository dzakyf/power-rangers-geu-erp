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
		<div id="PRSupplier" style="width:auto; height:400px;"></div>
		<div class="badan">
			<input type="button" name="ExportProduct" id="ExportProduct" value="Excel" onclick="PRSupplier.toExcel('./export/generate.php');" />
			<input type="button" name="ExportProduct2" id="ExportProduct2" value="Update" onclick="updates();" />
			
		</div>
		<script>
		
			PRSupplier = new dhtmlXGridObject('PRSupplier');
			PRSupplier.setImagePath("./dhtmlx/imgs/");
			PRSupplier.setHeader("No.,AP Salary Group,Supplier Name,Contact Person,City,Phone,Fax,Email,Sales,Sales No,Payment,Category,Sub Category,Group Category,NPWP");
			PRSupplier.setInitWidths("50,150,220,120,120,120,150,180,180,150,280,200,200,200,120");
			PRSupplier.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
			PRSupplier.setColAlign("center,left,left,left,center,center,center,center,left,center,left,left,left,left,center");
			PRSupplier.setColumnColor(",,,,,,,,,,,#E4FCCC,#FFEC94,#D6EDFD,#A7CFE9,#D6EDFD,#E8F2F9,#EEFD62,#F4FF89,#F9FFBC,#F4F9C4,#FBFDE5,#F3D5BD,#F6E4CC,,,,,#9CB770,#AFD07E,#BDDF88,#CBEF96,#D4F99E,#DBFFA4,,,#90E1DE,#D2FAF9,,");
			PRSupplier.setColTypes("ro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			PRSupplier.setColSorting("int,str,str,str,str,str,str,str,str");
			PRSupplier.enableSmartRendering(true, 50);
			PPRSupplier = new dataProcessor("accounting-master-vendor-update");
				PPRSupplier.setTransactionMode({
				 mode: "POST",
				  total:true,
				  headers:{
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
				  }	,
				  
				})
				
			PPRSupplier.init(PRSupplier);
			PRSupplier.setSkin("dhx_skyblue");
			PRSupplier.init();
			refreshdata();
			function refreshdata(){
			PRSupplier.clearAndLoad("xml-accounting-master-vendor");	

			}
			function updates(){
			PPRSupplier.sendData();	
			setTimeout(function(){
				refreshdata();
			},2000);

			}
		</script>
	</div>
	<div id="PRSupplierDet"></div>

@endsection
