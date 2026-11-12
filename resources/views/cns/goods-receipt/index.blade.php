@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Procurement</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ url('cns-procurement-purchase-order') }}">Purchase Order</a></li>
        <li class="breadcrumb-item active"><span class="active">Goods Receipt</span></li>
    </ol>
</div>

<style>
    #GRGridWrapper {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    #GRGrid {
        box-sizing: border-box !important;
        max-width: 100% !important;
    }
    #GRGrid .xhdr {
        height: auto !important;
        min-height: 0 !important;
    }
    #GRGrid table.hdr td {
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        padding: 5px 6px !important;
        line-height: 1.2 !important;
        height: auto !important;
    }
    #GRGrid table.hdr td.filter {
        padding: 3px 4px !important;
        font-weight: normal !important;
    }
    #GRGrid table.hdr td div.hdrcell {
        padding: 0 4px !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }
    #GRGrid table.hdr td.filter input,
    #GRGrid table.hdr td.filter select {
        width: 100% !important;
        height: 22px !important;
        padding: 1px 4px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
        box-sizing: border-box !important;
    }
    #GRGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
    .badge-status-draft { background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .badge-status-approved { background-color: #28a745; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .badge-status-void { background-color: #dc3545; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
</style>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">GOODS RECEIPT (PENERIMAAN BARANG NON-UCO)</div>
    
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

    <div class="card w-100 px-0">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-end flex-wrap gap-2">
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                        <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                        <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Status</small></label>
                        <select id="filterStatus" class="form-control form-control-sm" style="width:130px;">
                            <option value="">Semua Status</option>
                            <option value="DRAFT">DRAFT</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="VOID">VOID</option>
                        </select>
                    </div>
                    <div class="m-1">
                        <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                        <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </div>

            <div id="GRGridWrapper" class="mt-2">
                <div id="GRGrid" style="width:100%;height:450px;background-color:white;overflow:hidden;"></div>
            </div>
            <div class="d-flex align-items-center mt-2 flex-wrap gap-2">
                <a href="{{ route('cns-procurement-goods-receipt.create') }}" class="btn btn-primary mr-2">
                    Buat GR
                </a>
                @if($canApprove)
                    <button type="button" class="btn btn-success font-weight-bold" id="btnApproveGR" onclick="approveSelectedGR()">
                        Approve GR
                    </button>
                @endif
            </div>
            <div class="mt-2 text-muted small">
                <i class="fa fa-info-circle"></i> Double-click baris tabel untuk melihat detail atau cetak Berita Acara. Untuk menyetujui, pilih baris berstatus DRAFT lalu klik <b>Approve GR</b>.
            </div>
        </div>
    </div>
</div>

<script>
    var GRGrid;
    var lastGRGridWrapperWidth = 0;

    $(document).ready(function() {
        initGRGrid();
        loadGridData();

        $(window).on('resize', function() {
            resizeGRGrid();
        });

        var wrapper = document.getElementById('GRGridWrapper');
        if (wrapper && typeof ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(function() {
                if (wrapper.clientWidth && wrapper.clientWidth !== lastGRGridWrapperWidth) {
                    lastGRGridWrapperWidth = wrapper.clientWidth;
                    resizeGRGrid();
                }
            });
            ro.observe(wrapper);
        }
    });

    function resizeGRGrid() {
        var wrapper = document.getElementById('GRGridWrapper');
        if (!GRGrid || !GRGrid.hdr || !GRGrid.hdr.rows[0] || !wrapper || !wrapper.clientWidth) {
            return;
        }
        lastGRGridWrapperWidth = wrapper.clientWidth;
        GRGrid.initCellWidth[7] = "*";
        GRGrid.setSizes();
        if (GRGrid.getColWidth(7) < 180) {
            GRGrid.initCellWidth[7] = "180";
            GRGrid.cellWidthPX[7] = 180;
            GRGrid.setSizes();
        }
    }

    function initGRGrid() {
        GRGrid = new dhtmlXGridObject('GRGrid');
        GRGrid.setImagePath("./dhtmlx/imgs/");
        GRGrid.setSkin("dhx_skyblue");
        GRGrid.setHeader("No,No. GR,No. PO,Supplier,Tanggal Terima,No. Surat Jalan,Driver / Plat,Item Diterima,Status,Diterima Oleh,Disetujui Oleh");
        GRGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter");
        GRGrid.setInitWidths("45,145,145,160,115,130,135,*,95,110,110");
        GRGrid.setColAlign("center,center,center,left,center,left,left,left,center,center,center");
        GRGrid.setColSorting("int,str,str,str,date,str,str,str,str,str,str");
        GRGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        GRGrid.enableSmartRendering(true);
        GRGrid.attachEvent("onRowDblClicked", function(rowId) {
            window.location.href = "{{ url('cns-procurement-goods-receipt-detail') }}/" + rowId;
        });
        GRGrid.init();
        resizeGRGrid();
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                resizeGRGrid();
            });
        }
    }

    function loadGridData() {
        var params = [];
        var start = $('#filterStartDate').val();
        var end   = $('#filterEndDate').val();
        var status = $('#filterStatus').val();

        if (start) params.push('start_date=' + encodeURIComponent(start));
        if (end)   params.push('end_date=' + encodeURIComponent(end));
        if (status) params.push('status=' + encodeURIComponent(status));

        var url = "{{ route('cns-procurement-goods-receipt.xml') }}" + (params.length ? '?' + params.join('&') : '');
        GRGrid.clearAll();
        GRGrid.load(url, function() {
            resizeGRGrid();
        });
    }

    function applyFilters() {
        loadGridData();
    }

    function resetFilters() {
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        $('#filterStatus').val('');
        loadGridData();
    }

    function approveSelectedGR() {
        var rowId = GRGrid.getSelectedRowId();
        if (!rowId) {
            dhtmlx.alert({
                title: "Peringatan",
                text: "Pilih baris data terlebih dahulu pada tabel Goods Receipt!",
                ok: "OK"
            });
            return false;
        }

        var status = "";
        try {
            status = GRGrid.cells(rowId, 8).getValue();
        } catch (cellErr) {
            console.warn("Gagal membaca status:", cellErr);
        }

        var grNo = "";
        try {
            grNo = GRGrid.cells(rowId, 1).getValue();
        } catch (cellErr) {
            console.warn("Gagal membaca grNo:", cellErr);
        }

        if (status && status.trim().toUpperCase() !== 'DRAFT') {
            dhtmlx.alert({
                title: "Peringatan",
                text: "Goods Receipt " + (grNo || rowId) + " ini sudah berstatus " + status.trim().toUpperCase() + "!",
                ok: "OK"
            });
            return false;
        }

        dhtmlx.confirm({
            title: "Konfirmasi Approval",
            text: "Apakah Anda yakin ingin menyetujui (Approve) Goods Receipt " + (grNo || rowId) + "? Stok barang Non-UCO akan otomatis bertambah.",
            ok: "Ya, Setujui",
            cancel: "Batal",
            callback: function(result) {
                if (!result) {
                    return;
                }

                if (typeof $.blockUI === 'function') {
                    $.blockUI();
                }

                var approveBtn = $('#btnApproveGR');
                approveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memproses...');

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('cns-procurement-goods-receipt-approve') }}/" + rowId,
                    type: "POST",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (typeof $.unblockUI === 'function') {
                            $.unblockUI();
                        }
                        approveBtn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Approve GR');
                        if (response && response.status === 'success') {
                            dhtmlx.alert({
                                title: "Sukses",
                                text: response.message || "Goods Receipt berhasil di-approve.",
                                ok: "OK",
                                callback: function() {
                                    loadGridData();
                                }
                            });
                        } else {
                            dhtmlx.alert({
                                title: "Peringatan",
                                text: response.message || "Gagal menyetujui Goods Receipt.",
                                ok: "OK"
                            });
                        }
                    },
                    error: function(xhr) {
                        if (typeof $.unblockUI === 'function') {
                            $.unblockUI();
                        }
                        approveBtn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Approve GR');
                        var errMsg = "Terjadi kesalahan sistem saat menyetujui Goods Receipt.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        dhtmlx.alert({
                            title: "Error",
                            text: errMsg,
                            ok: "OK"
                        });
                    }
                });
            }
        });
    }
</script>

@if(session('success'))
<script>
    $(document).ready(function() {
        dhtmlx.alert({
            title: "Sukses",
            text: "{{ session('success') }}",
            ok: "OK"
        });
    });
</script>
@endif
@if(session('error'))
<script>
    $(document).ready(function() {
        dhtmlx.alert({
            title: "Error",
            text: "{{ session('error') }}",
            ok: "OK"
        });
    });
</script>
@endif
@endsection
