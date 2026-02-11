@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>PROCUREMENT</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard1">Purchase</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Monitoring</span>
        </li>
    </ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
    <div id="POMgt" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
    <input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="POMgt.toExcel('./export/generate.php');" />

</div>
<div id="POMgtDiv"></div>

<script>
    function POMgtDoubleClick() {

        POMgtMID = POMgt.getSelectedId();
        LoadM('POMgtDiv', 'my-po-outstanding-detail?ID=' + POMgtMID);

    }
    POMgt = new dhtmlXGridObject('POMgt');
    POMgt.setImagePath("./dhtmlx/imgs/");
    POMgt.setHeader("No.,NO PR, Tanggal Pengajuan,Category,Sub Category,Purchase Type,Penerima,Request For,Priority,Status,Diajukan Oleh");
    POMgt.setInitWidths("70,200,100,100,100,100,100,100,100,200,100");
    POMgt.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
    POMgt.setColAlign("center,center,center,center,center,center,center,center,center,left,left");
    POMgt.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    POMgt.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
    POMgt.setEditable(true);
    POMgt.enableMultiline(true);
    POMgt.attachEvent("onRowDblClicked", POMgtDoubleClick);
    POMgt.setSkin("dhx_skyblue");
    POMgt.init();
    POMgt.loadXML("xml-my-po-outstanding");
</script>
@endsection