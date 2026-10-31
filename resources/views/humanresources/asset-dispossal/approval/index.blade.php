@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>GENERAL AFFAIRS</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">ASSETS</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">ASSET DISPOSAL APPROVAL</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row no-gutters">
		 <div id="AssetDisposal" style="width:auto;height:520px; background-color:white;overflow:hidden"></div>
		 <div id="divtom2">
			<input type="button" id="DetailAssetDisposal" NAME="DetailAssetDisposal" class="btn btn-info mt-2 mr-2" value="Detail &amp; Approval" />
			<input type="button" id="ekselAssetDisposal" name="ekselAssetDisposal" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="AssetDisposal.toExcel('./export/generate.php');"  />
		</div>
		<script>

		AssetDisposal = new dhtmlXGridObject('AssetDisposal');
		AssetDisposal.setImagePath("dhtmlx/imgs/");
		AssetDisposal.setHeader("No.,Disposal No, Tanggal Pengajuan, Pemohon, Jabatan, Nama Aset, Nomor Seri, Kondisi, Lokasi, Alasan, Tanggal Pemusnahan, Status");
		AssetDisposal.setInitWidths("70,170,100,100,100,150,100,100,100,200,120,80");
		AssetDisposal.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		AssetDisposal.setColAlign("center,center,center,center,center,left,center,center,left,left,center,center");
		AssetDisposal.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		AssetDisposal.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
		AssetDisposal.setEditable(true);
		AssetDisposal.setSkin("dhx_skyblue");
		AssetDisposal.init();
		refreshData();

		function refreshData() {
			AssetDisposal.clearAndLoad("hrd-asset-dispossal-confirmation-data");
		}

		$('#DetailAssetDisposal').on('click', function() {
			let pmid = AssetDisposal.getSelectedRowId();
			if(pmid == null) {
				alert('Mohon pilih baris');
				return;
			}
			let Disposal_No = AssetDisposal.cells(pmid,1).getValue()
			LoadM('AssetDisposalDiv','hrd-asset-dispossal-confirmation-detail?Disposal_No=' + Disposal_No);
		})
		
		</script>

</div>
<div id="AssetDisposalDiv"></div>

@endsection