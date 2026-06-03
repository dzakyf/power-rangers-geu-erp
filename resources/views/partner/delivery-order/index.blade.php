@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Delivery Order</span></li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DELIVERY ORDER</div>
    <div class="card">
        <div class="card-body">
            <div id="DeliveryOrderGrid" style="width:auto;height:320px;background-color:white;overflow:hidden" class="mb-2"></div>
            <div>
                <input type="button" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="window.location.href='partner-delivery-order-create'" />

                <!-- <input type="button" value="Update Receipt" class="btn btn-info mt-2 mr-2" onclick="openUpdateReceipt()" /> -->
                <!-- <input type="button" value="Update Note" class="btn btn-secondary mt-2 mr-2" onclick="openUpdateDeliveryNote()" /> -->
                <input type="button" value="Lihat PO" class="btn btn-secondary mt-2 mr-2" onclick="openPickupOrder()" />
                <input type="button" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="DeliveryOrderGrid.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>


<div id="pickupOrderCard" style="display:none;" class="mt-3">
    <div class="card">
        <div class="card-header">
            <h5 class="m-1">PICKUP ORDER</h5>
        </div>
        <div class="card-body">
            <div id="PickupOrderGrid" style="width:auto;height:320px;background-color:white;overflow:hidden" class="mb-2"></div>

            <div>
                <input type="button" value="Update" class="btn btn-info mt-2 mr-2" onclick="redirectToUpdateDeliveryOrder()" />
            </div>
        </div>
    </div>
</div>

<script>
    var selectedId = null;
    var selectedPOId = null;

    function redirectToUpdateDeliveryOrder() {
        window.location.href = `partner-delivery-order-update?po_id=${selectedPOId}`
    }

    function openUpdateReceipt() {
        if (!selectedId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        $('#divUpdateReceipt').show();
        $('#divUpdateDeliveryNote').hide();

        $('#DO_Id').val(selectedId);
        $('[data-sig-id]', '#divUpdateReceipt').each(function() {
            Signature.init($(this).data('sig-id'));
        });
        $('html, body').animate({
            scrollTop: $('#divUpdateReceipt').offset().top
        }, 400);
    }

    function openUpdateDeliveryNote() {
        if (!selectedId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        $('#divUpdateDeliveryNote').show();
        $('#divUpdateReceipt').hide();
        $('#DN_DO_Id').val(selectedId);
        $('html, body').animate({
            scrollTop: $('#divUpdateDeliveryNote').offset().top
        }, 400);
    }

    $(document).ready(function() {
        initGrid();
    });

    function initGrid() {
        DeliveryOrderGrid = new dhtmlXGridObject('DeliveryOrderGrid');
        DeliveryOrderGrid.setImagePath('./dhtmlx/imgs/');
        DeliveryOrderGrid.setHeader("No, DO Code, POO Name, PO Code, DO Date");
        DeliveryOrderGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
        DeliveryOrderGrid.setInitWidths("60,120,150,120,100");
        DeliveryOrderGrid.setColAlign("center,left,left,center,left");
        DeliveryOrderGrid.setColSorting("int,str,str,str,str");
        DeliveryOrderGrid.setColTypes("ron,ro,ro,ro,ro");
        DeliveryOrderGrid.enableSmartRendering(true, 150);
        DeliveryOrderGrid.attachEvent('onRowSelect', function(id) {
            selectedId = DeliveryOrderGrid.getRowAttribute(id, 'ID');
        });
        DeliveryOrderGrid.setSkin('dhx_skyblue');
        DeliveryOrderGrid.init();
        DeliveryOrderGrid.clearAndLoad('partner-delivery-order-data?format=xml');
    }

    function initPickupOrderGrid(woId) {
        $('#pickupOrderCard').show();
        PickupOrderGrid = new dhtmlXGridObject('PickupOrderGrid');
        PickupOrderGrid.setImagePath('./dhtmlx/imgs/');
        PickupOrderGrid.setHeader("No, PO Code, POO Name, Qty, Address");
        PickupOrderGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
        PickupOrderGrid.setInitWidths("60,120,*,80,200");
        PickupOrderGrid.setColAlign("center,left,left,center,left");
        PickupOrderGrid.setColSorting("int,str,str,int,str");
        PickupOrderGrid.setColTypes("ron,ro,ro,ro,ro");
        PickupOrderGrid.setSkin('dhx_skyblue');
        PickupOrderGrid.attachEvent('onRowSelect', function(id) {
            selectedPOId = PickupOrderGrid.getRowAttribute(id, 'ID')
        })
        PickupOrderGrid.init();
        PickupOrderGrid.clearAndLoad(`partner-pickup-data?format=xml&delivery_order_id=${woId}`);
        $('html, body').animate({
            scrollTop: $('#pickupOrderCard').offset().top
        }, 400);
    }

    function openPickupOrder() {
        if (!selectedId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        initPickupOrderGrid(selectedId);
    }
</script>
@endsection