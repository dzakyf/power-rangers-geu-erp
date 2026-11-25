@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Supplier Non-UCO',
    'breadcrumbs' => [
        ['label' => 'Procurement'],
        ['label' => 'Supplier Non-UCO'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Supplier Non-UCO</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" class="btn btn-sm btn-primary" onclick="NonUcoSupplierNew()">New Supplier</button>
            <button type="button" class="btn btn-sm btn-info" onclick="NonUcoSupplierDetail()">Open Detail</button>
            <button type="button" class="btn btn-sm btn-success" onclick="NonUcoSupplierGrid.toExcel('./export/generate.php');">Excel</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-grid-wrapper">
            <div id="NonUcoSupplierGrid" class="cns-grid" style="width:auto;height:360px;background-color:white;overflow:hidden"></div>
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
            swal('Perhatian', 'Pilih baris data', 'warning');
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
