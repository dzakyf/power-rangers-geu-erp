@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Surat Jalan</span></li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SURAT JALAN</div>
    <div class="card">
        <div class="card-body">
            <div class="mb-2 d-flex align-items-center">
                <select id="doStatusFilter" class="form-control form-control-sm" style="width:200px;margin-right:8px;">
                    <option value="">-- Semua Status --</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm" id="btnFilterDO">Filter</button>
            </div>
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
        if (!selectedPOId) {
            alert('Pilih data Pickup Order terlebih dahulu.');
            return;
        }
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

        $('#btnFilterDO').on('click', function() {
            var url = 'partner-delivery-order-data?format=xml';
            var status = $('#doStatusFilter').val();
            if (status) url += '&status=' + status;
            DeliveryOrderGrid.clearAll();
            DeliveryOrderGrid.load(url);
        });

        $.get('master-delivery-order-status-list', function(res) {
            var $sel = $('#doStatusFilter');
            (res.data || []).forEach(function(s) {
                $sel.append('<option value="' + s + '">' + s + '</option>');
            });
        });
    });

    function initGrid() {

        DeliveryOrderGrid = new dhtmlXGridObject('DeliveryOrderGrid');
        DeliveryOrderGrid.setImagePath('./dhtmlx/imgs/');
        DeliveryOrderGrid.setHeader("No, Code Surat Jalan, POO Name, Code PO, Status, Surat Jalan Date");
        DeliveryOrderGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        DeliveryOrderGrid.setInitWidths("60,120,150,120,100,100");
        DeliveryOrderGrid.setColAlign("center,left,left,center,center,left");
        DeliveryOrderGrid.setColSorting("int,str,str,str,str,str");
        DeliveryOrderGrid.setColTypes("ron,ro,ro,ro,ro,ro");
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

        let tHeader = ['No', 'PO Number', 'Type', 'Coverage', 'Site', 'Package', 'Qty', 'Price', 'Description', 'Dibuat Pada']
        let tWidth = tHeader.map((e, i) => i == 1 ? '120' : '150')
        let headerAttachment = tHeader.map((e, i) => i <= 1 ? '#rspan' : '#text_filter')
        let colId = ['No', 'PO_Number', 'Type', 'Coverage', 'Site', 'Package', 'Qty', 'Price', 'Description', 'Dibuat Pada'].join(',')
        let colAlign = tHeader.map((e, i) => i == 0 ? 'center' : (i == 6 || i == 7) ? 'right' : 'left')
        let colSorting = tHeader.map((e, i) => i == 0 ? 'int' : 'str')
        let colType = tHeader.map((e, i) => i == 0 ? 'ron' : 'ro')
        PickupOrderGrid = new dhtmlXGridObject('PickupOrderGrid');
        PickupOrderGrid.setImagePath('./dhtmlx/imgs/');
        PickupOrderGrid.setHeader(tHeader.join(','));
        PickupOrderGrid.attachHeader(headerAttachment.join(','));
        PickupOrderGrid.setInitWidths(tWidth.join(','));
        PickupOrderGrid.setColAlign(colAlign.join(','));
        PickupOrderGrid.setColSorting(colSorting.join(','));
        PickupOrderGrid.setColTypes(colType.join(','));
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