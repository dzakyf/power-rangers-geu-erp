@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">PURCHASING REQUEST</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">PR LISTS</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row no-gutters">
		 <div id="PurchaseManagement" style="width:auto;height:520px; background-color:white;overflow:hidden"></div>
		 <div id="divtom2">
			<input type="button" id="FormRequestProcurement" NAME="FormRequestProcurement" class="btn btn-primary mt-2 mr-2" value="Form Request" />
			<input type="button" id="DetailRequestProcurement" NAME="DetailRequestProcurement" class="btn btn-info mt-2 mr-2" value="Detail Request" />
			<input type="button" id="LogRequestProcurement" NAME="LogRequestProcurement" class="btn btn-success mt-2 mr-2" value="Log Request" />
			<input type="button" id="PrintPRProcurement" NAME="PrintPRProcurement" class="btn btn-dark mt-2 mr-2" value="Cetak PR" />
			<input type="button" id="PrintPOProcurement" NAME="PrintPOProcurement" class="btn btn-secondary mt-2 mr-2" value="Cetak PO" />
			<input type="button" id="CheckInventoryProcurement" NAME="CheckInventoryProcurement" class="btn btn-info mt-2 mr-2" value="Cek Penerimaan Barang (GR)" />
			<input type="button" id="ekselRequestProcurement" name="ekselRequestProcurement" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="PurchaseManagement.toExcel('./export/generate.php');"  />
			{{-- <input type="button" id="SubmitPRNewDetail" NAME="SubmitPRNewDetail" class="btn btn-success mt-2 mr-2" value="Update Priority &amp; Approve" />                 --}}
		</div>
		<script>
		

		PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
		PurchaseManagement.setImagePath("dhtmlx/imgs/");
		PurchaseManagement.setHeader("No.,PR No,PR Date,Category,Status,PO No,Remark,Date Trx,Created By");
		PurchaseManagement.setInitWidths("70,170,100,100,100,100,100,100,120,100");
		PurchaseManagement.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		PurchaseManagement.setColAlign("center,center,center,center,center,center,center,left,center,center");
		PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str");
		PurchaseManagement.setEditable(true);
		PurchaseManagement.setSkin("dhx_skyblue");
		PurchaseManagement.init();
		refreshData();

		$('#LogRequestProcurement').click(function () {
			let pmid = PurchaseManagement.getSelectedRowId();
			if(pmid == null) {
				alert('Mohon pilih baris');
				return;
			}
			let PR_No = PurchaseManagement.cells(pmid,1).getValue()
			$.blockUI();
			LoadM('PurchaseManagementDiv','hrd-procurement-log?PR_No=' + PR_No);
		});

		function refreshData() {
			PurchaseManagement.clearAndLoad("xml-hrd-procurement-index");
		}

		$('#FormRequestProcurement').on('click', function() {
			$.blockUI();
			LoadM('PurchaseManagementDiv','hrd-procurement-new');
		})

		$('#DetailRequestProcurement').on('click', function() {
			let pmid = PurchaseManagement.getSelectedRowId();
			if(pmid == null) {
				alert('Mohon pilih baris');
				return;
			}
			let PR_No = PurchaseManagement.cells(pmid,1).getValue()
			$.blockUI();
			LoadM('PurchaseManagementDiv','hrd-procurement-detail?PR_No=' + PR_No);
		})

		function selectedPRPO() {
			let pmid = PurchaseManagement.getSelectedRowId();
			if(pmid == null) { alert('Mohon pilih baris'); return null; }
			return {
				pr: PurchaseManagement.cells(pmid,1).getValue(),
				po: PurchaseManagement.cells(pmid,5).getValue()
			};
		}

		function streamPdf(url, title) {
			let w = window.open('', '_blank');
			if(!w) { alert('Popup diblokir, izinkan popup untuk preview PDF'); return; }
			w.document.write('<html><head><title>'+title+'</title></head><body style="margin:0"><iframe src="'+url+'" style="width:100vw;height:100vh;border:0"></iframe></body></html>');
		}

		$('#PrintPRProcurement').on('click', function() {
			let s = selectedPRPO(); if(!s) return;
			streamPdf('hrd-procurement-print-pr?PR_No=' + encodeURIComponent(s.pr), 'Cetak PR ' + s.pr);
		})

		$('#PrintPOProcurement').on('click', function() {
			let s = selectedPRPO(); if(!s) return;
			if(!s.po) { alert('PO belum diterbitkan untuk PR ini'); return; }
			streamPdf('hrd-procurement-print-po?PR_No=' + encodeURIComponent(s.pr) + '&PO_No=' + encodeURIComponent(s.po), 'Cetak PO ' + s.po);
		})

		$('#CheckInventoryProcurement').on('click', function() {
			let s = selectedPRPO(); if(!s) return;
			if(!s.po) {
				alert('PO belum diterbitkan untuk PR ini. Penerimaan barang memerlukan nomor PO yang sudah disetujui.');
				return;
			}
			$.blockUI();
			// Buka form penerimaan barang (good receive) dengan membawa nomor PO
			LoadM('PurchaseManagementDiv', 'inventory-incoming-new?PO_NO=' + encodeURIComponent(s.po));
		})
		
		</script>

</div>
<div id="PurchaseManagementDiv"></div>

@endsection
