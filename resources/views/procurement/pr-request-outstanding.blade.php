@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">PURCHASING REQUEST</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">OUTSTANDING LISTS</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="PurchaseManagement" style="width:auto;height:520px; background-color:white;overflow:hidden"></div>
		 <div id="divtom2">
				<input type="button" id="SubmitPRNewDetail" NAME="SubmitPRNewDetail" class="btn btn-success mt-2 mr-2" value="Update Priority &amp; Approve" />                
			</div>
		<script>
		

		PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
		PurchaseManagement.setImagePath("dhtmlx/imgs/");
		PurchaseManagement.setHeader("No.,PR No,PR Date,Category,Sub Category,Purchase Type,PRIORITY,REMARK,Date Trx,Created By");
		PurchaseManagement.setInitWidths("70,170,100,100,100,100,100,100,120,100");
		PurchaseManagement.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		PurchaseManagement.setColAlign("center,center,center,center,center,center,center,left,center,center");
		PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,coro,ed,ro,ro");
		PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str");
		PurchaseManagement.setEditable(true);
		PPurchaseManagement = new dataProcessor("purchasing-request-outstanding-update");
		PPurchaseManagement.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		
		})
		PPurchaseManagement.setUpdateMode("Off");
		PPurchaseManagement.init(PurchaseManagement);
		PurchaseManagement.setSkin("dhx_skyblue");
		PurchaseManagement.getCombo(6).put('NORMAL','NORMAL');
		PurchaseManagement.getCombo(6).put('URGENT','URGENT');
		PurchaseManagement.init();
		PurchaseManagement.loadXML("xml-pr-request-outstanding");
		
		
$("#SubmitPRNewDetail").click(function() {
	dhtmlx.confirm('Confirm Update Priority?', 'Purchase Request', function(r) {
					
			if (r==true){
				$.blockUI(); 
				PPurchaseManagement.sendData();

				setTimeout(function(){
					$.unblockUI(); 

					location.href='purchasing-request-outstanding';
				},1000);
				
			}
	});
})
		
		</script>

</div>
<div id="PurchaseManagementDiv"></div>

@endsection
