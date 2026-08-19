@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>GENERAL AFFAIRS</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">ASSETS</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">HANDOVER CONFIRMATION</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row no-gutters">
		 <div id="AssetHandover" style="width:auto;height:520px; background-color:white;overflow:hidden"></div>
		 <div id="divtom2">
			<input type="button" id="DetailAssetHandover" NAME="DetailAssetHandover" class="btn btn-info mt-2 mr-2" value="Detail Handover" />
			<input type="button" id="ekselAssetHandover" name="ekselAssetHandover" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="AssetHandover.toExcel('./export/generate.php');"  />
			{{-- <input type="button" id="SubmitPRNewDetail" NAME="SubmitPRNewDetail" class="btn btn-success mt-2 mr-2" value="Update Priority &amp; Approve" />                 --}}
		</div>
		<script>
		

		AssetHandover = new dhtmlXGridObject('AssetHandover');
		AssetHandover.setImagePath("dhtmlx/imgs/");
		AssetHandover.setHeader("No.,Handover No, Handover Date, User Name, User NIK, PIC Name, PIC Department, PIC Phone, Reference, Remark, Products");
		AssetHandover.setInitWidths("70,170,100,100,100,100,100,100,120,100");
		AssetHandover.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		AssetHandover.setColAlign("center,center,center,center,center,center,center,left,center,center");
		AssetHandover.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		AssetHandover.setColSorting("int,str,str,str,str,str,str,str,str");
		AssetHandover.setEditable(true);
		AssetHandover.setSkin("dhx_skyblue");
		AssetHandover.init();
		refreshData();

		function refreshData() {
			AssetHandover.clearAndLoad("hrd-asset-handover-confirmation-data");
		}

		$('#DetailAssetHandover').on('click', function() {
			let pmid = AssetHandover.getSelectedRowId();
			if(pmid == null) {
				alert('Mohon pilih baris');
				return;
			}
			let Handover_No = AssetHandover.cells(pmid,1).getValue()
			LoadM('AssetHandoverDiv','hrd-asset-handover-confirmation-detail?Handover_No=' + Handover_No);
		})
		
		</script>

</div>
<div id="AssetHandoverDiv"></div>

@endsection
