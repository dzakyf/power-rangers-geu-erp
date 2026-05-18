@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Work Order</span>
        </li>
    </ol>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">Work Order</div>
            <div class="card">
                <div class="card-body">
                    <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
                    <div>
                        <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                        <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                        <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
                        <input type="button" id="detailGrid1" name="detailGrid1" value="Detail Data" class="btn btn-info mt-2 mr-2" />

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div id="Grid1Detail" style="display:none;">
                @include('partner.work-order.edit')
            </div>
        </div>
    </div>
</div>

<script>
    var marketingId = null;

    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-work-order-create`
    }

    function initTableGrid1() {
        let tHeader = ['No', null, 'Nama POO', 'Status', 'Tanggal Work Order', 'Dibuat Pada']
        let tWidth = tHeader.map((e, i) => i == 1 ? 0 : '150')
        let headerAttachment = tHeader.map((e, i) => i <= 1 ? '#rspan' : '#text_filter')
        let colId = ['No', 'ID', 'Nama POO', 'Status', 'Tanggal Work Order', 'Dibuat Pada'].join(',')

        Grid1 = new dhtmlXGridObject('MembershipGrid');
        Grid1.setImagePath("./dhtmlx/imgs/");
        Grid1.setHeader(tHeader.join(','));
        Grid1.setInitWidths(tWidth.join(','));
        Grid1.attachHeader(headerAttachment);
        Grid1.setColTypes(tHeader.map((e, i) => {
            return 'ro';
        }).join(','));

        Grid1.setColumnIds(colId);
        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");


        Grid1.init();

        Grid1.clearAndLoad('partner-work-order-data?format=xml');
    }

    function getWorkOrderData() {
        $.ajax({
            url: 'partner-work-order-data?format=json&id=' + marketingId,
            type: 'GET',
            success: function(res) {
                var data = res.data[0];
                if (!data) return;

                var option = new Option(data.POO_Name, data.POO_Id, true, true);
                $('#POO_Id').append(option).trigger('change');
                $('#Status').val(data.Status);
                $('#Work_Order_Date').val(data.Work_Order_Date);
            },
            error: function() {
                alert('Gagal memuat data Work Order.');
            }
        });
    }

    $('#detailGrid1').on('click', function() {
        if (!marketingId) {
            alert('Pilih data terlebih dulu');
            return;
        }
        $('#Grid1Detail').show();
        getWorkOrderData();
    });

    function selectMitra(id, cellIdx) {
        marketingId = Grid1.getRowAttribute(id, 'ID');
    }
</script>
@endsection