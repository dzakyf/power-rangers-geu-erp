@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>CNS</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard1">Procurement</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Kontrak Buyer (UCO)</span>
        </li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <div id="BuyerContractGrid" style="width:auto;height:360px;background-color:white;overflow:hidden"></div>
        <div class="mt-2">
            <button type="button" class="btn btn-primary" onclick="BuyerContractNew()">New Contract</button>
            <button type="button" class="btn btn-info" onclick="BuyerContractDetail()">Open Detail</button>
            <button type="button" class="btn btn-warning" onclick="BuyerContractGrid.toExcel('./export/generate.php');">Excel</button>
        </div>
    </div>
</div>
<div id="BuyerContractDiv"></div>

<script>
    function BuyerContractNew() {
        LoadM('BuyerContractDiv', 'cns-procurement-buyer-contract-new');
    }

    function BuyerContractDetail() {
        var id = BuyerContractGrid.getSelectedId();
        if (id == null) {
            alert('Pilih baris data');
            return;
        }
        LoadM('BuyerContractDiv', 'cns-procurement-buyer-contract-detail?ID=' + id);
    }

    var BuyerContractGrid = new dhtmlXGridObject('BuyerContractGrid');
    BuyerContractGrid.setImagePath('dhtmlx/imgs/');
    BuyerContractGrid.setHeader('No.,Contract No,Buyer,Quantity,Loading Period,Incoterms,Price/MT,Status,Bookings,Created');
    BuyerContractGrid.attachHeader('#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
    BuyerContractGrid.setInitWidths('50,150,200,170,190,90,130,180,80,130');
    BuyerContractGrid.setColAlign('center,left,left,left,left,center,left,left,center,center');
    BuyerContractGrid.setColSorting('int,str,str,str,str,str,str,str,int,str');
    BuyerContractGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
    BuyerContractGrid.enableSmartRendering(true, 100);
    BuyerContractGrid.attachEvent('onRowDblClicked', BuyerContractDetail);
    BuyerContractGrid.setSkin('dhx_skyblue');
    BuyerContractGrid.init();

    function refreshdata() {
        BuyerContractGrid.clearAndLoad('xml-cns-procurement-buyer-contract');
    }

    refreshdata();
</script>
@endsection
