@extends('layout.mainlayout-cns')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>PROCUREMENT</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard1">Purchase Order</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">PO List</span>
        </li>
    </ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
    <div class="mb-2">
        Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
        s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
        <button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
    </div>
    <div id="POMgt" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
    <input type="button" id="poExport" NAME="poExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="POMgt.toExcel('./export/generate.php');" />

</div>
<div id="POMgtDiv"></div>

<script>
    function POMgtDoubleClick() {
        POMgtMID = POMgt.getSelectedId();
        POID = POMgt.cells(POMgtMID, 1).getValue();

        POMgtMID = POMgt.getSelectedId();
        LoadM('POMgtDiv', 'cns-procurement-purchase-order-detail?ID=' + POID);

    }
    POMgt = new dhtmlXGridObject('POMgt');
    POMgt.setImagePath("./dhtmlx/imgs/");
    POMgt.setHeader("No.,System No,PR No,PO No,Supplier,PO Date,Category,Sub Category,Purchase Type,Request By, Department, Status, Created By, Product");
    POMgt.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    POMgt.setInitWidths("50,0,165,165,210,0,0,0,120,110,0,0,100,450");
    POMgt.setColAlign("left,left,center,center,left,center,center,center,center,center,left,left");
    POMgt.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    POMgt.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
    POMgt.setColumnColor(",,,,,,,,,,,,,#E4FCCC");
    POMgt.setEditable(false);
    POMgt.enableMultiline(false);
    POMgt.attachEvent("onRowDblClicked", POMgtDoubleClick);
    POMgt.setSkin("dhx_skyblue");
    POMgt.init();
    refreshdata();
    function refreshdata() {
        POMgt.clearAndLoad("xml-cns-procurement-purchase-order?tgl1=" + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
    }
</script>
@endsection