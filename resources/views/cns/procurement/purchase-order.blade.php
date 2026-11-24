@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Purchase Order (PO)',
    'breadcrumbs' => [
        ['label' => 'Procurement'],
        ['label' => 'Purchase Order'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Purchase Order</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" id="poExport" name="poExport" class="btn btn-sm btn-success" onclick="POMgt.toExcel('./export/generate.php');">Export</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-filter-bar">
            <div>
                <label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
                <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control form-control-sm" style="width:auto;" />
            </div>
            <div>
                <label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
                <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" style="width:auto;" />
            </div>
            <div>
                <button type="button" onclick="refreshdata()" class="btn btn-sm btn-primary">Filter</button>
            </div>
        </div>
        <div class="cns-grid-wrapper">
            <div id="POMgt" class="cns-grid" style="width:auto;height:350px;background-color:white;overflow:hidden"></div>
        </div>
    </div>
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
    POMgt.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge,ro,ro,ro,ro,ro");
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