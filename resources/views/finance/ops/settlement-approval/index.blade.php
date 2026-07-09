@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Finance</a></li>
        <li class="breadcrumb-item"><a href="#">Cost Management</a></li>
        <li class="breadcrumb-item active"><span class="active">Pickup Settlement</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-3">
                <h5>Approval Settlement</h5>
            </div>
            <div class="col-md-auto ml-auto p-2">
                <button id="btnApproveSettlement" class="btn btn-success"><i class="fa fa-check"></i> Approve</button>
                <button id="btnRejectSettlement" class="btn btn-danger"><i class="fa fa-times"></i> Reject</button>
                <button id="btnDetailSettlement" class="btn btn-info"><i class="fa fa-info-circle"></i> Detail</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div style="width:100%;overflow-x:auto;">
            <div id="settlementApprovalGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<script>
    var approvalGrid;
    var selectedRouteId = null;

    function initApprovalGrid() {
        approvalGrid = new dhtmlXGridObject('settlementApprovalGrid');
        approvalGrid.setImagePath("./dhtmlx/imgs/");
        approvalGrid.setHeader("No,Route Code, Total Biaya, Status, Dibuat");
        approvalGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
        approvalGrid.setInitWidths("50,*,150,130,150");
        approvalGrid.setColAlign("center,left,right,center,left");
        approvalGrid.setColSorting("int,str,int,str,str");
        approvalGrid.setColTypes("ron,ro,ro,ro,ro");
        approvalGrid.setSkin("dhx_skyblue");
        approvalGrid.attachEvent("onRowSelect", function(id) {
            selectedRouteId = approvalGrid.getRowAttribute(id, "ID");
        });
        approvalGrid.init();
        approvalGrid.load('partner-pickup-route-data?status=SETTLEMENT_PENDING&format=xml');
    }

    $(document).ready(function() {
        initApprovalGrid();

        $('#btnDetailSettlement').on('click', function() {
            if (!selectedRouteId) {
                alert('Pilih route terlebih dahulu');
                return;
            }

            $.get('finance-settlement-detail', { route_id: selectedRouteId }, function(res) {
                if (res.status !== 'success') {
                    alert('Data tidak ditemukan');
                    return;
                }

                var d = res.data;
                var route = d.route;
                var settlements = d.settlements || [];

                var bodyHtml = '';
                bodyHtml += '<div class="mb-3 p-3 bg-light rounded">';
                bodyHtml += '<div class="row">';
                bodyHtml += '<div class="col-md-4"><strong>Route Code:</strong> ' + (route.Code || '-') + '</div>';
                bodyHtml += '<div class="col-md-4"><strong>Total Cost:</strong> Rp ' + (route.Total_Cost || '0') + '</div>';
                bodyHtml += '<div class="col-md-4"><strong>Status:</strong> <span class="badge badge-warning">' + (route.Status || '-') + '</span></div>';
                bodyHtml += '</div></div>';

                if (settlements.length === 0) {
                    bodyHtml += '<p class="text-muted">No settlement data found.</p>';
                } else {
                    bodyHtml += '<h6 class="font-weight-bold mb-2">Settlement Details</h6>';
                    bodyHtml += '<table class="table table-sm table-bordered">';
                    bodyHtml += '<thead class="thead-light"><tr><th>Category</th><th>Nominal</th><th>Notes</th><th>Files</th></tr></thead>';
                    bodyHtml += '<tbody>';

                    settlements.forEach(function(s) {
                        var filesHtml = '';
                        if (s.files && s.files.length > 0) {
                            s.files.forEach(function(f) {
                                filesHtml += '<a href="' + f + '" target="_blank" class="btn btn-sm btn-outline-primary mr-1"><i class="fa fa-file"></i></a>';
                            });
                        } else {
                            filesHtml = '<span class="text-muted">-</span>';
                        }
                        bodyHtml += '<tr>';
                        bodyHtml += '<td>' + (s.Description || '-') + '</td>';
                        bodyHtml += '<td>Rp ' + (s.Nominal || '0') + '</td>';
                        bodyHtml += '<td>' + (s.Notes || '-') + '</td>';
                        bodyHtml += '<td>' + filesHtml + '</td>';
                        bodyHtml += '</tr>';
                    });

                    bodyHtml += '</tbody></table>';
                }

                AppModal.open({
                    title: 'Detail Settlement - ' + (route.Code || ''),
                    size: 'xl',
                    body: bodyHtml,
                    actions: [{ label: 'Close', class: 'btn-secondary', onClick: function() { AppModal.close(); } }]
                });
            });
        });

        $('#btnApproveSettlement').on('click', function() {
            if (!selectedRouteId) {
                alert('Pilih route terlebih dahulu');
                return;
            }
            if (!confirm('Approve settlement ini?')) return;

            $.post('finance-settlement-action', {
                _token: '{{ csrf_token() }}',
                route_id: selectedRouteId,
                action: 'approve'
            }, function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    approvalGrid.load('partner-pickup-route-data?status=SETTLEMENT_PENDING&format=xml');
                } else {
                    alert('Error: ' + (res.message || 'Unknown error'));
                }
            });
        });

        $('#btnRejectSettlement').on('click', function() {
            if (!selectedRouteId) {
                alert('Pilih route terlebih dahulu');
                return;
            }
            var notes = prompt('Alasan penolakan:');
            if (!notes) return;

            $.post('finance-settlement-action', {
                _token: '{{ csrf_token() }}',
                route_id: selectedRouteId,
                action: 'reject',
                notes: notes
            }, function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    approvalGrid.load('partner-pickup-route-data?status=SETTLEMENT_PENDING&format=xml');
                } else {
                    alert('Error: ' + (res.message || 'Unknown error'));
                }
            });
        });
    });
</script>
@endsection
