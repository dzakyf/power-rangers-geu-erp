@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Cost Allocation per Shipment',
    'breadcrumbs' => [
        ['label' => 'Export Management'],
        ['label' => 'Cost Control'],
        ['label' => 'Cost Allocation per Shipment'],
    ],
])

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Shipments</div>
                <div class="h5 mb-0 font-weight-bold">{{ number_format($summary['count']) }} <small class="text-muted">({{ $summary['final_count'] }} Final)</small></div>
                <div class="small text-muted">{{ number_format($summary['total_volume_mt'], 2) }} MT &bull; {{ number_format($summary['total_containers']) }} Ctr</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Cost (USD / IDR)</div>
                <div class="h5 mb-0 font-weight-bold">$ {{ number_format($summary['total_cost_usd'], 2) }}</div>
                <div class="small text-muted">Rp {{ number_format($summary['total_cost_idr'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Cost / MT</div>
                <div class="h5 mb-0 font-weight-bold">$ {{ number_format($summary['avg_cost_per_mt_usd'], 2) }} <small class="text-muted">/ MT</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-left-warning shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Breakdown (USD)</div>
                <div class="small">
                    Freight: <strong>${{ number_format($summary['freight_usd'], 2) }}</strong> |
                    Trucking: <strong>${{ number_format($summary['trucking_usd'], 2) }}</strong><br>
                    Surveyor: <strong>${{ number_format($summary['surveyor_usd'], 2) }}</strong> |
                    Port: <strong>${{ number_format($summary['port_usd'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Cost Allocation per Shipment</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            @if(function_exists('cns_can') && cns_can('cost_allocation_shipment', 'create'))
                <a href="{{ route('cns-export-cost-allocation.create') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> New</a>
            @endif
            <button type="button" class="btn btn-sm btn-info" onclick="detailAllocation()"><i class="fa fa-eye"></i> Detail / Edit</button>
            @if(function_exists('cns_can') && cns_can('cost_allocation_shipment', 'delete'))
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteAllocation()"><i class="fa fa-trash"></i> Delete</button>
            @endif
            <a href="{{ route('cns-export-cost-allocation.export-csv', request()->query()) }}" class="btn btn-sm btn-warning"><i class="fa fa-file-excel-o"></i> Export CSV</a>
            <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-outline-secondary">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('cns-export-cost-allocation.index') }}" class="form-row row cns-filter-bar align-items-end">
            <div class="col-md-2 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Dari Tanggal</label>
                <input type="date" name="tgl1" class="form-control form-control-sm" value="{{ $filters['tgl1'] }}">
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Sampai Tanggal</label>
                <input type="date" name="tgl2" class="form-control form-control-sm" value="{{ $filters['tgl2'] }}">
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">-- Semua --</option>
                    <option value="DRAFT" {{ $filters['status'] === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                    <option value="FINAL" {{ $filters['status'] === 'FINAL' ? 'selected' : '' }}>FINAL</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-6 mb-2">
                <label class="small font-weight-bold mb-1">Cari No. Alokasi / SI</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="CA/EXP/... atau SI_No" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-2 col-sm-12 mb-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filter</button>
            </div>
        </form>

        <div class="cns-grid-wrapper">
            <div id="CostAllocationGrid" class="cns-grid" style="width:auto;height:360px; background-color:white;overflow:hidden"></div>
            <div id="costAllocPagingBoxHidden" style="display:none;"></div>
            <div id="costAllocPagingBox" class="mt-2"></div>
        </div>
    </div>
</div>

@php
    $gridRows = [];
    foreach ($allocations as $idx => $row) {
        $meta = $siMeta[$row->si_no] ?? [];
        $vesselPod = trim(($meta['vessel_name'] ?? '-') . ' (' . ($meta['pod'] ?? '-') . ')');
        $gridRows[] = [
            'id' => $row->id,
            'data' => [
                $idx + 1,
                $row->allocation_no,
                $row->si_no,
                $row->final_si_no ?: '-',
                $meta['consignee'] ?? '-',
                $vesselPod,
                number_format((float) $row->volume_mt, 2) . ' MT / ' . (int) $row->container_count . ' Ctr',
                number_format((float) $row->total_freight_usd, 2),
                number_format((float) $row->total_trucking_usd, 2),
                number_format((float) $row->total_surveyor_usd, 2),
                number_format((float) $row->total_port_usd, 2),
                '$' . number_format((float) $row->total_cost_usd, 2),
                'Rp ' . number_format((float) $row->total_cost_idr, 0, ',', '.'),
                '$' . number_format((float) $row->cost_per_mt_usd, 2),
                $row->isFinal() ? 'FINAL' : 'DRAFT',
            ],
        ];
    }
@endphp

<script>
    var editUrlBase = '{{ url("cns-export-cost-allocation-edit") }}';
    var deleteUrlBase = '{{ url("cns-export-cost-allocation-delete") }}';

    var CostAllocationGrid = new dhtmlXGridObject('CostAllocationGrid');
    CostAllocationGrid.setImagePath('./dhtmlx/imgs/');
    CostAllocationGrid.setHeader('No,Allocation No,SI No,Final SI No,Consignee,Vessel & POD,MT / Ctr,Freight (USD),Trucking (USD),Surveyor (USD),Port (USD),Total (USD),Total (IDR),Cost / MT,Status');
    CostAllocationGrid.attachHeader('#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
    CostAllocationGrid.setInitWidths('45,150,145,145,170,180,120,105,105,105,105,115,135,100,85');
    CostAllocationGrid.setColAlign('center,left,left,left,left,left,right,right,right,right,right,right,right,right,center');
    CostAllocationGrid.setColSorting('int,str,str,str,str,str,str,str,str,str,str,str,str,str,str');
    CostAllocationGrid.setColTypes('ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge');
    CostAllocationGrid.attachEvent('onRowDblClicked', function (id) {
        window.location.href = editUrlBase + '/' + id;
    });
    CostAllocationGrid.setSkin('dhx_skyblue');
    CostAllocationGrid.enablePaging(true, 25, 5, 'costAllocPagingBoxHidden', false);
    CostAllocationGrid.init();
    CostAllocationGrid.parse(@json(['rows' => $gridRows]), 'json');

    var CostAllocationPagination = initCnsGridPagination(CostAllocationGrid, {
        containerId: 'costAllocPagingBox',
        pageSize: 25,
        clientPaging: true,
        serverSort: false,
        serverFilter: false
    });

    function detailAllocation() {
        var id = CostAllocationGrid.getSelectedId();
        if (!id) {
            swal('Perhatian', 'Pilih baris data terlebih dahulu.', 'warning');
            return false;
        }
        window.location.href = editUrlBase + '/' + id;
        return true;
    }

    function deleteAllocation() {
        var id = CostAllocationGrid.getSelectedId();
        if (!id) {
            swal('Perhatian', 'Pilih baris data terlebih dahulu.', 'warning');
            return false;
        }
        var no = CostAllocationGrid.cells(id, 1).getValue();
        var status = CostAllocationGrid.cells(id, 14).getValue();
        if (status === 'FINAL') {
            swal('Informasi', 'Cost Allocation ' + no + ' berstatus FINAL dan tidak dapat dihapus.', 'info');
            return false;
        }
        swal({
            title: 'Hapus Cost Allocation?',
            text: 'Data ' + no + ' akan dihapus permanen.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }, function (confirmed) {
            if (!confirmed) return;
            $.ajax({
                url: deleteUrlBase + '/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        swal('Berhasil!', res.message, 'success');
                        setTimeout(function () { window.location.reload(); }, 600);
                    } else {
                        swal('Error', res.message || 'Gagal menghapus data.', 'error');
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus data.';
                    swal('Error', msg, 'error');
                }
            });
        });
        return true;
    }
</script>
@endsection
