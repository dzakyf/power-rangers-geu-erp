@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>CNS</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard1">Procurement</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Supplier Non-UCO</span>
        </li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <div id="NonUcoSupplierGrid" style="width:auto;height:360px;background-color:white;overflow:hidden"></div>
        <div class="mt-2">
            <button type="button" class="btn btn-primary" onclick="NonUcoSupplierNew()">New Supplier</button>
            <button type="button" class="btn btn-info" onclick="NonUcoSupplierDetail()">Open Detail</button>
            <button type="button" class="btn btn-warning" onclick="NonUcoSupplierGrid.toExcel('./export/generate.php');">Excel</button>
        </div>
    </div>
</div>
<div id="NonUcoSupplierDiv"></div>

<script>
    function NonUcoSupplierNew() {
        LoadM('NonUcoSupplierDiv', 'cns-procurement-non-uco-supplier-new');
    }

    function NonUcoSupplierDetail() {
        var id = NonUcoSupplierGrid.getSelectedId();
        if (id == null) {
            alert('Pilih baris data');
            return;
        }
        LoadM('NonUcoSupplierDiv', 'cns-procurement-non-uco-supplier-detail?ID=' + id);
    }

    var NonUcoSupplierGrid = new dhtmlXGridObject('NonUcoSupplierGrid');
    NonUcoSupplierGrid.setImagePath('dhtmlx/imgs/');
    NonUcoSupplierGrid.setHeader('No.,Vendor ID,Nama Vendor,Phone,Emails,Kategori Supplier,Created');
    NonUcoSupplierGrid.attachHeader('#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
    NonUcoSupplierGrid.setInitWidths('50,150,260,150,320,160,150');
    NonUcoSupplierGrid.setColAlign('center,left,left,left,left,left,center');
    NonUcoSupplierGrid.setColSorting('int,str,str,str,str,str,str');
    NonUcoSupplierGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro');
    NonUcoSupplierGrid.enableSmartRendering(true, 100);
    NonUcoSupplierGrid.attachEvent('onRowDblClicked', NonUcoSupplierDetail);
    NonUcoSupplierGrid.setSkin('dhx_skyblue');
    NonUcoSupplierGrid.init();

    function refreshdata() {
        NonUcoSupplierGrid.clearAndLoad('xml-cns-procurement-non-uco-supplier');
    }

    refreshdata();
</script>
@endsection
