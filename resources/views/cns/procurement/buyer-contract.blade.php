@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Kontrak Buyer & Supplier',
    'breadcrumbs' => [
        ['label' => 'Procurement'],
        ['label' => 'Kontrak Buyer & Supplier'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Kontrak Buyer &amp; Supplier</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" class="btn btn-sm btn-primary" onclick="BuyerContractNew()">New Data</button>
            <button type="button" class="btn btn-sm btn-info" onclick="OpenProcurementDetail()">Open Detail</button>
            <button type="button" class="btn btn-sm btn-success" onclick="BuyerContractGrid.toExcel('./export/generate.php');">Excel</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row cns-filter-bar align-items-end">
            <div class="col-md-3">
                <label for="filter_contract" class="d-block mb-1"><small>Kontrak</small></label>
                <select id="filter_contract" class="text-black form-control form-control-sm">
                    <option value="all">Semua</option>
                    <option value="with">Menggunakan kontrak</option>
                    <option value="without">Tidak menggunakan kontrak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_group" class="d-block mb-1"><small>Kelompok Pembelian</small></label>
                <select id="filter_group" class="text-black form-control form-control-sm">
                    <option value="all">Semua</option>
                    <option value="UCO">UCO</option>
                    <option value="Non-UCO">Non UCO</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_product" class="d-block mb-1"><small>Jenis Produk</small></label>
                <select id="filter_product" class="text-black form-control form-control-sm">
                    <option value="all">Semua</option>
                    <option value="UCO">UCO</option>
                    <option value="Flexibag">Flexibag</option>
                    <option value="Besi Penyangga">Besi Penyangga</option>
                    <option value="Kemasan dan Ampas">Kemasan dan Ampas</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end" style="gap: 0.5rem;">
                <button type="button" class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div class="cns-grid-wrapper">
            <div id="BuyerContractGrid" class="cns-grid" style="width:auto;height:360px;background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>
<div id="BuyerContractDiv"></div>
<div id="NonUcoSupplierDiv"></div>

<script>
    function BuyerContractNew() {
        LoadM('BuyerContractDiv', 'cns-procurement-buyer-contract-new');
    }

    function OpenProcurementDetail() {
        var id = BuyerContractGrid.getSelectedId();
        if (id == null) {
            swal('Perhatian', 'Pilih baris data', 'warning');
            return;
        }

        if (id.indexOf('C-') === 0) {
            LoadM('BuyerContractDiv', 'cns-procurement-buyer-contract-detail?ID=' + id.substring(2));
            return;
        }

        if (id.indexOf('S-') === 0) {
            LoadM('NonUcoSupplierDiv', 'cns-procurement-non-uco-supplier-detail?ID=' + id.substring(2));
        }
    }

    function resetFilters() {
        $('#filter_contract').val('all');
        $('#filter_group').val('all');
        $('#filter_product').val('all');
        refreshdata();
    }

    function refreshdata() {
        if (typeof BuyerContractPagination !== 'undefined' && BuyerContractPagination) {
            BuyerContractPagination.reload(1);
            return;
        }
        var params = $.param({
            contract: $('#filter_contract').val(),
            group: $('#filter_group').val(),
            product: $('#filter_product').val()
        });

        BuyerContractGrid.clearAndLoad('xml-cns-procurement-buyer-contract?' + params);
    }

    var BuyerContractGrid = new dhtmlXGridObject('BuyerContractGrid');
    BuyerContractGrid.setImagePath('dhtmlx/imgs/');
    BuyerContractGrid.setHeader('No,Nama Supplier / Buyer,Alamat,Telepon,Email,Kelompok Pembelian,Jenis Produk,Kontrak,Nomor Kontrak,Loading Period,Contract Qty (MT),Delivered (MT),Outstanding (MT),Status Kontrak,File');
    BuyerContractGrid.attachHeader('#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan');
    BuyerContractGrid.setInitWidths('50,220,260,130,240,130,150,80,150,190,130,130,140,180,100');
    BuyerContractGrid.setColAlign('center,left,left,left,left,center,left,center,left,left,right,right,right,left,center');
    BuyerContractGrid.setColSorting('int,str,str,str,str,str,str,str,str,str,str,str,str,str,str');
    BuyerContractGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge,cns_doc');
    BuyerContractGrid.enableMultiline(true);
    BuyerContractGrid.attachEvent('onRowDblClicked', OpenProcurementDetail);
    BuyerContractGrid.setSkin('dhx_skyblue');
    BuyerContractGrid.init();

    var BuyerContractPagination = initCnsGridPagination(BuyerContractGrid, {
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        serverSort: false,
        serverFilter: false,
        getUrl: function (params) {
            return 'xml-cns-procurement-buyer-contract?' + $.param({
                posStart: params.posStart,
                count: params.count,
                contract: $('#filter_contract').val(),
                group: $('#filter_group').val(),
                product: $('#filter_product').val()
            });
        }
    });

    (function () {
        var params = new URLSearchParams(window.location.search);
        var contract = params.get('contract');
        var group = params.get('group');
        var product = params.get('product');

        if (contract) { $('#filter_contract').val(contract); }
        if (group) { $('#filter_group').val(group); }
        if (product) { $('#filter_product').val(product); }

        $('#filter_contract, #filter_group, #filter_product').on('change', refreshdata);
        refreshdata();
    })();
</script>
@endsection
