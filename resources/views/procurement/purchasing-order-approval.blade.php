@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Purchase Order</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Approval</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		<div id="POMgt" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>

	</div>
<div id="POMgtDiv"></div>

<script>
function POMgtDoubleClick() {

	POMgtMID = POMgt.getSelectedId();
	LoadM('POMgtDiv','purchasing-order-approval-detail?ID=' + POMgtMID);

}
POMgt = new dhtmlXGridObject('POMgt');
POMgt.setImagePath("./dhtmlx/imgs/");
POMgt.setHeader("No.,PR No,PR Date,Category,Sub Category,Purchase Type,Request By,Request For,Priority,Note Priority,Status,Date Trx,Aging(days)");
POMgt.setInitWidths("70,200,100,100,100,100,100,100,100,100,100,120,100");
POMgt.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
POMgt.setColAlign("center,center,center,center,center,center,center,center,center,center,left,center,center");
POMgt.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
POMgt.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
POMgt.setEditable(true);
POMgt.enableMultiline(true);
POMgt.attachEvent("onRowDblClicked", POMgtDoubleClick);
POMgt.setSkin("dhx_skyblue");
POMgt.init();
POMgt.loadXML("xml-purchasing-order-approval");

</script>
@endsection
