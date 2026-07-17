@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="partner-outstock">Outstock</a></li>
        <li class="breadcrumb-item active"><span class="active">Detail - {{ $outstock->Code }}</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    {{-- Summary --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="m-3">{{ $outstock->Code }} — {{ $outstock->Warehouse_Name ?? '-' }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Tanggal:</strong> {{ $outstock->Date }}</div>
                <div class="col-md-3"><strong>Fleet:</strong> {{ $outstock->Fleet_Number ?? '-' }}</div>
                <div class="col-md-3"><strong>Kategori:</strong> {{ $outstock->Vehicle_Category ?? '-' }}</div>
                <div class="col-md-3"><strong>Status:</strong> {{ $outstock->Status }}</div>
            </div>
        </div>
    </div>

    {{-- Delivery Note buttons (visible when LOADING) --}}
    @if($outstock->Status === 'LOADING')
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="m-3">Surat Jalan</h5>
        </div>
        <div class="card-body">
            @foreach($warehouses as $wh)
            <a href="{{ url('partner-outstock-delivery-note?id=' . $outstock->ID . '&warehouse_id=' . $wh->id) }}" class="btn btn-outline-primary btn-sm mr-2 mb-2" target="_blank">
                > Surat Jalan - {{ $wh->warehouse_location }}
            </a>
            @endforeach
        </div>
    </div>
    @endif
    {{-- Loading Section --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="m-3">Loading</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm" id="loadingTable">
                <thead>
                    <tr>
                        <th>Warehouse</th>
                        <th>Container</th>
                        <th>Tanggal</th>
                        <th>System Qty</th>
                        <th>Actual Qty</th>
                        <th>Notes</th>
                        <th>Foto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loading as $l)
                    <tr data-id="{{ $l->ID }}">
                        <td>
                            @php
                                $container = \App\MasterContainer::find($l->Container_ID);
                                $warehouseId = $container ? $container->Warehouse_ID : null;
                                $whName = $warehouseId ? optional(\App\WarehousePartner::find($warehouseId))->warehouse_location : '-';
                            @endphp
                            <input type="text" class="form-control form-control-sm" value="{{ $whName }}" readonly>
                        </td>
                        <td>
                            <input type="hidden" class="loading-container" value="{{ $l->Container_ID }}">
                            <input type="text" class="form-control form-control-sm" value="{{ $l->Container_Name ?? '-' }}" readonly>
                        </td>
                        <td><input type="date" class="form-control form-control-sm loading-date" value="{{ $l->Date }}"></td>
                        <td><input type="text" class="form-control form-control-sm" value="{{ $l->System_Qty ?? 0 }}" readonly></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm loading-actual" value="{{ $l->Actual_Qty }}"></td>
                        <td><input type="text" class="form-control form-control-sm loading-notes" value="{{ $l->Notes }}"></td>
                        <td>
                            @if($l->Photo_Path)
                            <a href="{{ asset('filevendor/' . $l->Photo_Path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                            @endif
                            <input type="file" class="form-control-file loading-photo" accept="image/*" data-id="{{ $l->ID }}" style="font-size:12px;">
                        </td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-loading-row">X</button></td>
                    </tr>
                    @empty
                    <tr class="loading-empty-row">
                        <td colspan="8" class="text-center text-muted">Belum ada data loading. Tambah container di bawah.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <button type="button" class="btn btn-success btn-sm" id="btnAddLoadingRow">+ Tambah Container</button>
            <button type="button" class="btn btn-primary btn-sm ml-2" id="btnSaveLoading">Simpan Loading</button>
        </div>
    </div>

    {{-- Handover Section (visible only after LOADING) --}}
    @if($outstock->Status !== 'DRAFT')
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="m-3">Handover</h5>
        </div>
        <div class="card-body">
            <form id="handoverForm">
                <input type="hidden" name="outstock_id" value="{{ $outstock->ID }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fleet</label>
                            <input type="text" class="form-control" value="{{ $outstock->Fleet_Number ?? '-' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="Date" value="{{ $handover->Date ?? date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Actual Qty</label>
                            <input type="number" step="0.01" class="form-control" name="Actual_Qty" value="{{ $handover->Actual_Qty ?? 0 }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Destination</label>
                            <input type="text" class="form-control" name="Destination" value="{{ $handover->Destination ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Notes</label>
                            <input type="text" class="form-control" name="Notes" value="{{ $handover->Notes ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Foto</label><br>
                            @if($handover && $handover->Photo_Path)
                            <a href="{{ asset('filevendor/' . $handover->Photo_Path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                            @endif
                            <input type="file" class="form-control-file" id="handoverPhoto" accept="image/*">
                            <input type="hidden" id="handoverId" value="{{ $handover->ID ?? '' }}">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" id="btnSaveHandover">Simpan & Selesaikan Outstock</button>
            </form>
        </div>
    </div>
    @endif

</div>

<script>
    var containers = {!! json_encode($containers) !!};
    var warehouseMap = {};
    @foreach($warehouses as $wh)
        warehouseMap[{{ $wh->id }}] = '{{ $wh->warehouse_location }}';
    @endforeach
    var outstockId = {{ $outstock->ID }};

    $('#btnAddLoadingRow').on('click', function() {
        var emptyRow = $('.loading-empty-row');
        if (emptyRow.length) emptyRow.remove();

        var opts = '<option value="">-- Pilih --</option>';
        containers.forEach(function(c) {
            var whName = c.Warehouse_ID ? (warehouseMap[c.Warehouse_ID] || 'WH#' + c.Warehouse_ID) : '-';
            opts += '<option value="' + c.ID + '" data-wh="' + whName + '">' + c.Name + ' (' + whName + ')</option>';
        });

        var row = '<tr>' +
            '<td><span class="new-row-wh">-</span></td>' +
            '<td><select class="form-control form-control-sm loading-container">' + opts + '</select></td>' +
            '<td><input type="date" class="form-control form-control-sm loading-date" value="{{ date('Y-m-d') }}"></td>' +
            '<td><input type="text" class="form-control form-control-sm" value="{{ $l->System_Qty ?? 0 }}" readonly></td>' +
            '<td><input type="number" step="0.01" class="form-control form-control-sm loading-actual" value="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm loading-notes"></td>' +
            '<td><input type="file" class="form-control-file loading-photo" accept="image/*" style="font-size:12px;"></td>' +
            '<td><button type="button" class="btn btn-sm btn-danger remove-loading-row">X</button></td>' +
            '</tr>';
        $('#loadingTable tbody').append(row);
    });

    $(document).on('change', '.loading-container', function() {
        var selected = $(this).find('option:selected');
        var whName = selected.data('wh') || '-';
        $(this).closest('tr').find('.new-row-wh').text(whName);
    });

    $(document).on('click', '.remove-loading-row', function() {
        var row = $(this).closest('tr');
        var id = row.data('id');
        if (id && !confirm('Hapus baris ini?')) return;
        row.remove();
    });

    $(document).on('change', '.loading-photo', function() {
        var input = this;
        var row = $(input).closest('tr');
        var id = row.data('id');
        if (!id) {
            alert('Simpan loading terlebih dahulu sebelum upload foto');
            input.value = '';
            return;
        }
        var fd = new FormData();
        fd.append('id', id);
        fd.append('photo', input.files[0]);
        fd.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: 'partner-outstock-loading-upload-photo',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) alert('Foto uploaded');
                else alert('Gagal: ' + (res.message || ''));
            },
            error: function() { alert('Upload failed'); }
        });
    });

    $('#btnSaveLoading').on('click', function() {
        var rows = [];
        $('#loadingTable tbody tr').each(function() {
            var containerId = $(this).find('.loading-container').val();
            if (!containerId) return;
            rows.push({
                container_id: containerId,
                date: $(this).find('.loading-date').val(),
                actual_qty: $(this).find('.loading-actual').val(),
                notes: $(this).find('.loading-notes').val()
            });
        });

        $.ajax({
            url: 'partner-outstock-loading-submit',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                outstock_id: outstockId,
                rows: rows
            },
            success: function(res) {
                if (res.success) {
                    alert('Loading saved');
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            },
            error: function() { alert('Error'); }
        });
    });

    $('#handoverPhoto').on('change', function() {
        var id = $('#handoverId').val();
        if (!id) {
            alert('Simpan handover terlebih dahulu');
            this.value = '';
            return;
        }
        var fd = new FormData();
        fd.append('id', id);
        fd.append('photo', this.files[0]);
        fd.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: 'partner-outstock-handover-upload-photo',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) alert('Foto uploaded');
                else alert('Gagal');
            }
        });
    });

    $('#btnSaveHandover').on('click', function() {
        if (!confirm('Simpan handover dan selesaikan outstock?')) return;

        $.ajax({
            url: 'partner-outstock-handover-submit',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                outstock_id: outstockId,
                Date: $('input[name="Date"]').val(),
                Actual_Qty: $('input[name="Actual_Qty"]').val(),
                Destination: $('input[name="Destination"]').val(),
                Notes: $('input[name="Notes"]').val()
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    window.location.href = 'partner-outstock';
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            },
            error: function() { alert('Error'); }
        });
    });
</script>
@endsection
