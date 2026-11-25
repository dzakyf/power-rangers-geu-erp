@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'Inventory Non-UCO',
    'breadcrumbs' => [
        ['label' => 'Inventory Non-UCO'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">Inventory Non-UCO (Mutasi Stok Masuk &amp; Keluar)</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadGridData()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-filter-bar">
            <div>
                <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:140px;" />
            </div>
            <div>
                <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:140px;" />
            </div>
            <div>
                <label class="d-block mb-1"><small>Kategori</small></label>
                <select id="filterCategory" class="form-control form-control-sm" style="width:140px;" onchange="applyFilters()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="d-block mb-1"><small>Nama Barang</small></label>
                <select id="filterItemName" class="form-control form-control-sm" style="width:170px;" onchange="applyFilters()">
                    <option value="">Semua Barang</option>
                    @foreach($items as $itm)
                        <option value="{{ $itm }}">{{ $itm }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                <button type="button" class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div class="cns-grid-wrapper">
            <div id="NonUcoGrid" class="cns-grid" style="width:100%;height:450px; background-color:white;overflow:hidden"></div>
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

    var NonUcoPagination;

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
        NonUcoGrid.init();
        NonUcoGrid.setSizes();
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                if (NonUcoGrid) NonUcoGrid.setSizes();
            });
        }

        NonUcoPagination = initCnsGridPagination(NonUcoGrid, {
            pageSize: 25,
            pageSizeOptions: [10, 25, 50, 100],
            getUrl: function (params) {
                var q = [];
                q.push('posStart=' + params.posStart);
                q.push('count=' + params.count);

                var start    = $('#filterStartDate').val();
                var end      = $('#filterEndDate').val();
                var category = $('#filterCategory').val();
                var itemName = $('#filterItemName').val();

                if (start)    q.push('start_date=' + encodeURIComponent(start));
                if (end)      q.push('end_date=' + encodeURIComponent(end));
                if (category) q.push('category=' + encodeURIComponent(category));
                if (itemName) q.push('item_name=' + encodeURIComponent(itemName));

                if (params.sortCol !== null && params.sortCol !== undefined) {
                    q.push('sort_col=' + params.sortCol);
                    q.push('sort_dir=' + (params.sortDir || 'asc'));
                }

                if (params.filters) {
                    for (var colIdx in params.filters) {
                        q.push('filters[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
                    }
                }

                return "{{ route('cns-inventory-non-uco.xml') }}?" + q.join('&');
            }
        });
    }

    function loadGridData() {
        if (NonUcoPagination) {
            NonUcoPagination.reload(true);
        }
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
