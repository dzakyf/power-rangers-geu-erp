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
            <span class="active">Outstanding</span>
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
        LoadM('POMgtDiv', 'po-outstanding-detail?ID=' + POMgtMID);

    }
    POMgt = new dhtmlXGridObject('POMgt');
    POMgt.setImagePath("./dhtmlx/imgs/");
    POMgt.setHeader("No.,PR No,PR Date,Tanggal Terima, Category,Sub Category,Purchase Type,Request By,Request For,Note,Status,Aging(days)");
    POMgt.setInitWidths("70,200,100,100,100,100,100,100,100,300,100,120,100");
    POMgt.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter");
    POMgt.setColAlign("center,center,center,center,center,center,center,center,center,left,status,center,center");
    POMgt.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    POMgt.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,int,int");
    POMgt.setEditable(true);
    POMgt.enableMultiline(true);
    POMgt.attachEvent("onRowDblClicked", POMgtDoubleClick);
    POMgt.setSkin("dhx_skyblue");
    POMgt.init();
    POMgt.loadXML("xml-po-outstanding");
</script>
@endsection