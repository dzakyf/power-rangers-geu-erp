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
                <span class="active">Payment Monitoring</span>
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
	LoadM('POMgtDiv','po-payment-monitoring-detail?ID=' + POMgtMID);

}
POMgt = new dhtmlXGridObject('POMgt');
POMgt.setImagePath("./dhtmlx/imgs/");
POMgt.setHeader("No,Tgl Terima,PR No,Nama,Department Request,Peruntukkan,PO No,Total (Rp),Supplier,Due Date,Tgl Kirim PO,Status PO,Produk,Qty,Uom,Region Name,Project Name,Catatan Peruntukkan,Subgrid,Reference PO,Last Receiving");
POMgt.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,&nbsp;,&nbsp;,#text_filter,#text_filter,,&nbsp;,&nbsp;,&nbsp;,#text_filter,&nbsp;,#text_filter,#text_filter,#text_filter");
POMgt.setInitWidths("40,90,180,120,120,120,165,120,120,120,120,200,300,100,100,150,170,230,620,1,200,160");
POMgt.setColAlign("center,center,left,left,left,left,center,right,left,center,center,left,left,left,left,left,left,left,left,left,left,center");
POMgt.setColumnColor(",,,,,,#E4FCCC,,,,,,,,,#FFEC94,#D15C077,#A7CFE9,#D6EDFD,#E8F2F9,#EEFD62,#F4FF89,#F9FFBC,#F4F9C4,#FBFDE5,#F3D5BD,#F6E4CC,,,,,#9CB770,#AFD07E,#BDDF88,#CBEF96,#D4F99E,#DBFFA4,,,#90E1DE,#D2FAF9,,,");
POMgt.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
POMgt.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
POMgt.setNumberFormat("0,000",7,".",",");
POMgt.setEditable(false);
POMgt.attachEvent("onRowDblClicked", POMgtDoubleClick);
POMgt.setSkin("dhx_skyblue");
POMgt.init();
POMgt.loadXML("xml-po-payment-monitoring");

</script>
@endsection
