@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Finance</a></li>
        <li class="breadcrumb-item"><a href="#">Operasional</a></li>
        <li class="breadcrumb-item active"><span class="active">Cost</span></li>
    </ol>
</div>

<div class="mt-3">
    <ul class="nav nav-tabs" id="costTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="acquisition-tab" data-toggle="tab" href="#acquisition" role="tab">Acquisition Cost</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pickup-tab" data-toggle="tab" href="#pickup" role="tab">Pickup Cost</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- Acquisition Cost Tab -->
        <div class="tab-pane fade show active" id="acquisition" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Acquisition Cost content</p>
                </div>
            </div>
        </div>

        <!-- Pickup Cost Tab -->
        <div class="tab-pane fade" id="pickup" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div id="pickupCostGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" id="btnTopup">
                            <i class="fas fa-plus mr-1"></i>Topup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var pickupCostGrid;
    var selectedRouteId = null;

    function initPickupCostGrid() {
        pickupCostGrid = new dhtmlXGridObject('pickupCostGrid');
        pickupCostGrid.setImagePath("./dhtmlx/imgs/");
        pickupCostGrid.setHeader("No,Route Code, PO Code, Nominal, Status, Dibuat Pada");
        pickupCostGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        pickupCostGrid.setInitWidths("60,*,*,*,*,*");
        pickupCostGrid.setColAlign("center,left,left,right,left, left");
        pickupCostGrid.setColSorting("int,str,str,str,str,str");
        pickupCostGrid.setColTypes("ron,ro,ro,ro,ro,ro");
        pickupCostGrid.setSkin("dhx_skyblue");
        pickupCostGrid.enableAutoWidth(true);

        pickupCostGrid.attachEvent("onRowSelect", function(id) {
            selectedRouteId = pickupCostGrid.getRowAttribute(id, "ID");
        });
        pickupCostGrid.init();
        pickupCostGrid.load('partner-pickup-route-data?format=xml');
    }

    $(document).ready(function() {
        initPickupCostGrid();

        $('#btnTopup').on('click', function() {
            var formHtml = '' +
                '<form id="topupForm" enctype="multipart/form-data">' +
                '<div class="form-group">' +
                '<label>Saldo Kas Besar</label>' +
                '<input type="text" class="form-control" value="Rp 300,000" disabled />' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Pilih Rekening</label>' +
                '<select class="form-control" id="topupRekening" name="rekening">' +
                '<option value="">-- Pilih --</option>' +
                '<option value="BCA">BCA</option>' +
                '<option value="MANDIRI">Mandiri</option>' +
                '<option value="BRI">BRI</option>' +
                '</select>' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Nominal</label>' +
                '<input type="number" class="form-control" id="topupNominal" name="nominal" placeholder="Masukkan nominal" />' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Bukti Transfer</label>' +
                '<div class="custom-file">' +
                '<input type="file" class="custom-file-input" id="topupFile" name="bukti_transfer" accept="image/*,.pdf" />' +
                '<label class="custom-file-label" for="topupFile">Choose file...</label>' +
                '</div>' +
                '</div>' +
                '</form>';

            AppModal.open({
                title: 'Topup Kas Besar',
                size: 'md',
                body: formHtml,
                actions: [{
                        label: 'Close',
                        class: 'btn-secondary'
                    },
                    {
                        label: 'Submit',
                        class: 'btn-primary',
                        id: 'btnSubmitTopup',
                        onClick: function() {
                            var formData = new FormData(document.getElementById('topupForm'));
                            formData.append('_token', '{{ csrf_token() }}');
                            formData.append('id', selectedRouteId);
                            $.ajax({
                                url: 'finance-cost-approve-route',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(res) {
                                    alert('Topup berhasil!');
                                    AppModal.close();
                                },
                                error: function(xhr) {
                                    alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Gagal'));
                                }
                            });
                            return false;
                        }
                    }
                ]
            });

            $('#topupFile').on('change', function() {
                var fileName = this.files[0] ? this.files[0].name : 'Choose file...';
                $(this).next('.custom-file-label').text(fileName);
            });
        });
    });
</script>
@endsection