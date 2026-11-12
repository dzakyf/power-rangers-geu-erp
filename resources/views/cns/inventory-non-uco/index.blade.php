@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Inventory</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item active"><span class="active">Inventory Non-UCO</span></li>
    </ol>
</div>

<style>
    #NonUcoGrid .xhdr {
        height: auto !important;
        min-height: 0 !important;
    }
    #NonUcoGrid table.hdr td {
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        padding: 5px 6px !important;
        line-height: 1.2 !important;
        height: auto !important;
    }
    #NonUcoGrid table.hdr td.filter {
        padding: 3px 4px !important;
        font-weight: normal !important;
    }
    #NonUcoGrid table.hdr td div.hdrcell {
        padding: 0 4px !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }
    #NonUcoGrid table.hdr td.filter input,
    #NonUcoGrid table.hdr td.filter select {
        width: 100% !important;
        height: 22px !important;
        padding: 1px 4px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
        box-sizing: border-box !important;
    }
    #NonUcoGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
    .badge-balance-card {
        background-color: #ffffff;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 10px 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .badge-balance-title {
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: bold;
    }
    .badge-balance-val {
        font-size: 20px;
        font-weight: bold;
        color: #174375;
    }
</style>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">INVENTORY NON-UCO (MUTASI STOK MASUK & KELUAR)</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Kartu Saldo Stok per Barang -->
    <!-- <div class="d-flex flex-wrap gap-2 w-100 mb-3 mt-2">
        @forelse($summaries as $sum)
            <div class="badge-balance-card mr-2 mb-2" style="min-width: 170px;">
                <div class="badge-balance-title">
                    <span class="badge badge-light border">{{ $sum['category'] }}</span> {{ $sum['item_name'] }}
                </div>
                <div class="badge-balance-val">
                    {{ number_format((float) $sum['current_balance'], 2, '.', ',') }} <small class="text-muted" style="font-size: 13px;">{{ $sum['unit'] }}</small>
                </div>
            </div>
        @empty
            <div class="badge-balance-card mr-2 mb-2" style="min-width: 170px;">
                <div class="badge-balance-title">Flexibag 24KL</div>
                <div class="badge-balance-val">
                    {{ number_format((float) $currentBalance, 2, '.', ',') }} <small class="text-muted" style="font-size: 13px;">PCS</small>
                </div>
            </div>
        @endforelse
    </div> -->

    <div class="card w-100">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-end flex-wrap gap-2">
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                        <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:140px;" />
                    </div>
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                        <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:140px;" />
                    </div>
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Kategori</small></label>
                        <select id="filterCategory" class="form-control form-control-sm" style="width:140px;" onchange="applyFilters()">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Nama Barang</small></label>
                        <select id="filterItemName" class="form-control form-control-sm" style="width:170px;" onchange="applyFilters()">
                            <option value="">Semua Barang</option>
                            @foreach($items as $itm)
                                <option value="{{ $itm }}">{{ $itm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="m-1">
                        <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                        <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                    </div>
                </div>

                <!-- <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#modalManualMutation">
                        <i class="fa fa-minus-circle mr-1"></i> Catat Pengeluaran Gudang (Manual)
                    </button>
                    <a href="{{ route('cns-procurement-goods-receipt.create') }}" class="btn btn-sm btn-success ml-2">
                        <i class="fa fa-plus-circle mr-1"></i> + Penerimaan Barang (GR)
                    </a>
                </div> -->
            </div>

            <div id="NonUcoGrid" style="width:100%;height:450px; background-color:white;overflow:hidden" class="mt-2"></div>
        </div>
    </div>
</div>

<!-- Modal Catat Mutasi Manual -->
<div class="modal fade" id="modalManualMutation" tabindex="-1" role="dialog" aria-labelledby="modalManualMutationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('cns-inventory-non-uco.manual-mutation') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalManualMutationLabel"><i class="fa fa-pencil-square-o mr-1"></i> Catat Pengeluaran / Pemakaian Gudang</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="transaction_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Kategori Barang</label>
                            <input type="text" name="category" class="form-control form-control-sm" placeholder="Contoh: Flexibag / Besi Penyangga" value="Flexibag" />
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control form-control-sm" placeholder="Contoh: Flexibag 24KL" value="Flexibag 24KL" required />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Tipe Mutasi</label>
                            <select name="type" class="form-control form-control-sm" required>
                                <option value="OUT" selected>OUT (Keluar / Pemakaian)</option>
                                <option value="IN">IN (Penyesuaian Masuk)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Kuantiti <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" name="qty" class="form-control form-control-sm font-weight-bold text-right cns-decimal" required />
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Satuan</label>
                            <input type="text" name="unit" class="form-control form-control-sm text-center" value="PCS" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">No. Referensi / No. Dokumen Pemakaian <span class="text-danger">*</span></label>
                        <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="Contoh: PK-GUDANG-2026-001" required />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Keterangan / Alasan Pemakaian</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Tujuan pengeluaran / lokasi pemakaian..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">Simpan Mutasi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var NonUcoGrid;
    $(document).ready(function() {
        initNonUcoGrid();
        loadGridData();
    });

    function initNonUcoGrid() {
        NonUcoGrid = new dhtmlXGridObject('NonUcoGrid');
        NonUcoGrid.setImagePath("./dhtmlx/imgs/");
        NonUcoGrid.setSkin("dhx_skyblue");
        NonUcoGrid.setHeader("No,Tanggal,Kategori,Nama Barang,No. Referensi,Tipe Mutasi,Qty IN,Qty OUT,Saldo Akhir,Satuan,Keterangan,Dicatat Oleh");
        NonUcoGrid.attachHeader("#rspan,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#rspan,#rspan,#rspan,#select_filter,#text_filter,#text_filter");
        NonUcoGrid.setInitWidths("45,100,120,170,180,105,100,100,110,75,*,120");
        NonUcoGrid.setColAlign("center,center,left,left,left,center,right,right,right,center,left,left");
        NonUcoGrid.setColSorting("int,date,str,str,str,str,int,int,int,str,str,str");
        NonUcoGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        NonUcoGrid.enableSmartRendering(true);
        NonUcoGrid.init();
        NonUcoGrid.setSizes();
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                if (NonUcoGrid) NonUcoGrid.setSizes();
            });
        }
    }

    function loadGridData() {
        var params = [];
        var start    = $('#filterStartDate').val();
        var end      = $('#filterEndDate').val();
        var category = $('#filterCategory').val();
        var itemName = $('#filterItemName').val();

        if (start)    params.push('start_date=' + encodeURIComponent(start));
        if (end)      params.push('end_date=' + encodeURIComponent(end));
        if (category) params.push('category=' + encodeURIComponent(category));
        if (itemName) params.push('item_name=' + encodeURIComponent(itemName));

        var url = "{{ route('cns-inventory-non-uco.xml') }}" + (params.length ? '?' + params.join('&') : '');
        NonUcoGrid.clearAll();
        NonUcoGrid.load(url);
    }

    function applyFilters() {
        loadGridData();
    }

    function resetFilters() {
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        $('#filterCategory').val('');
        $('#filterItemName').val('');
        loadGridData();
    }
</script>
@endsection
