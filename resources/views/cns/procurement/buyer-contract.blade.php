@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>CNS</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard1">Procurement</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Kontrak Buyer &amp; Supplier</span>
        </li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-3">
                <label for="filter_contract" class="mb-1">Kontrak</label>
                <select id="filter_contract" class="form-control">
                    <option value="all">Semua</option>
                    <option value="with">Menggunakan kontrak</option>
                    <option value="without">Tidak menggunakan kontrak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_group" class="mb-1">Kelompok Pembelian</label>
                <select id="filter_group" class="form-control">
                    <option value="all">Semua</option>
                    <option value="UCO">UCO</option>
                    <option value="Non-UCO">Non UCO</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_product" class="mb-1">Jenis Produk</label>
                <select id="filter_product" class="form-control">
                    <option value="all">Semua</option>
                    <option value="UCO">UCO</option>
                    <option value="Flexibag">Flexibag</option>
                    <option value="Besi Penyangga">Besi Penyangga</option>
                    <option value="Kemasan dan Ampas">Kemasan dan Ampas</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-primary mr-2" onclick="refreshdata()">Filter</button>
                <button type="button" class="btn btn-light" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div id="BuyerContractGrid" style="width:auto;height:360px;background-color:white;overflow:hidden"></div>
        <div class="mt-2">
            <button type="button" class="btn btn-primary" onclick="BuyerContractNew()">New Data</button>
            <button type="button" class="btn btn-info" onclick="OpenProcurementDetail()">Open Detail</button>
            <button type="button" class="btn btn-warning" onclick="BuyerContractGrid.toExcel('./export/generate.php');">Excel</button>
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
            alert('Pilih baris data');
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
    BuyerContractGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,link');
    BuyerContractGrid.enableSmartRendering(true, 100);
    BuyerContractGrid.enableMultiline(true);
    BuyerContractGrid.attachEvent('onRowDblClicked', OpenProcurementDetail);
    BuyerContractGrid.setSkin('dhx_skyblue');
    BuyerContractGrid.init();

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
