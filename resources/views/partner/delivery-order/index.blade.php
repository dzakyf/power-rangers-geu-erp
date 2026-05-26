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
                <input type="button" value="Update Receipt" class="btn btn-info mt-2 mr-2" onclick="openUpdateReceipt()" />
                <input type="button" value="Update Note" class="btn btn-secondary mt-2 mr-2" onclick="openUpdateDeliveryNote()" />
                <input type="button" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="DeliveryOrderGrid.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>

<div id="divUpdateReceipt" style="display:none;" class="mt-3">
    @include('partner.delivery-order.update-receipt')
</div>

<div id="divUpdateDeliveryNote" style="display:none;" class="mt-3">
    @include('partner.delivery-order.update-delivery-note')
</div>

<script>
    var selectedId = null;

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
        let tHeader = ['No', null, 'DO Code', 'DO Date', 'Site', 'POO Name', 'Status'];
        let tWidth = tHeader.map((e, i) => i === 1 ? 0 : '150');
        let tFilter = tHeader.map((e, i) => i <= 1 ? '#rspan' : '#text_filter');

        DeliveryOrderGrid = new dhtmlXGridObject('DeliveryOrderGrid');
        DeliveryOrderGrid.setImagePath('./dhtmlx/imgs/');
        DeliveryOrderGrid.setHeader(tHeader.join(','));
        DeliveryOrderGrid.setInitWidths(tWidth.join(','));
        DeliveryOrderGrid.attachHeader(tFilter);
        DeliveryOrderGrid.setColTypes(tHeader.map(() => 'ro').join(','));
        DeliveryOrderGrid.enableSmartRendering(true, 150);
        DeliveryOrderGrid.attachEvent('onRowSelect', function(id) {
            selectedId = DeliveryOrderGrid.getRowAttribute(id, 'ID');
        });
        DeliveryOrderGrid.setSkin('dhx_skyblue');
        DeliveryOrderGrid.init();
        DeliveryOrderGrid.clearAndLoad('partner-delivery-order-data?format=xml');
    }
</script>
@endsection