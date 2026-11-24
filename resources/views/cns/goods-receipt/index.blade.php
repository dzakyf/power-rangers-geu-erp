@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'Goods Receipt',
    'breadcrumbs' => [
        ['label' => 'Procurement'],
        ['label' => 'Goods Receipt'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">Goods Receipt (Penerimaan Barang Non-UCO)</h5>
        <div class="d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route('cns-procurement-goods-receipt.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus-circle"></i> Buat GR
            </a>
            @if($canApprove)
                <button type="button" class="btn btn-success btn-sm font-weight-bold" id="btnApproveGR" onclick="approveSelectedGR()">
                    <i class="fa fa-check-circle mr-1"></i> Approve GR
                </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="cns-filter-bar">
            <div>
                <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:145px;" />
            </div>
            <div>
                <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:145px;" />
            </div>
            <div>
                <label class="d-block mb-1"><small>Status</small></label>
                <select id="filterStatus" class="form-control form-control-sm" style="width:130px;">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">DRAFT</option>
                    <option value="APPROVED">APPROVED</option>
                    <option value="VOID">VOID</option>
                </select>
            </div>
            <div>
                <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div id="GRGridWrapper" class="cns-grid-wrapper">
            <div id="GRGrid" class="cns-grid" style="width:100%;height:450px;background-color:white;overflow:hidden;"></div>
        </div>
        <div class="mt-2 text-muted small">
            <i class="fa fa-info-circle"></i> Double-click baris tabel untuk melihat detail atau cetak Berita Acara. Untuk menyetujui, pilih baris berstatus DRAFT lalu klik <b>Approve GR</b>.
        </div>
    </div>
</div>

<script>
    var GRGrid;
    var GRPagination;
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
        GRGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,cns_badge,ro,ro");
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

        GRPagination = initCnsGridPagination(GRGrid, {
            pageSize: 25,
            pageSizeOptions: [10, 25, 50, 100],
            getUrl: function (params) {
                var q = [];
                q.push('posStart=' + params.posStart);
                q.push('count=' + params.count);

                var start = $('#filterStartDate').val();
                var end   = $('#filterEndDate').val();
                var status = $('#filterStatus').val();

                if (start)  q.push('start_date=' + encodeURIComponent(start));
                if (end)    q.push('end_date=' + encodeURIComponent(end));
                if (status) q.push('status=' + encodeURIComponent(status));

                if (params.sortCol !== null && params.sortCol !== undefined) {
                    q.push('sort_col=' + params.sortCol);
                    q.push('sort_dir=' + (params.sortDir || 'asc'));
                }

                if (params.filters) {
                    for (var colIdx in params.filters) {
                        q.push('filters[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
                    }
                }

                return "{{ route('cns-procurement-goods-receipt.xml') }}?" + q.join('&');
            },
            onLoaded: function () {
                resizeGRGrid();
            }
        });
    }

    function loadGridData() {
        if (GRPagination) {
            GRPagination.reload(true);
        }
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
            swal('Peringatan', 'Pilih baris data terlebih dahulu pada tabel Goods Receipt!', 'warning');
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
            swal('Peringatan', "Goods Receipt " + (grNo || rowId) + " ini sudah berstatus " + status.trim().toUpperCase() + "!", 'warning');
            return false;
        }

        swal({
            title: "Konfirmasi Approval",
            text: "Apakah Anda yakin ingin menyetujui (Approve) Goods Receipt " + (grNo || rowId) + "? Stok barang Non-UCO akan otomatis bertambah.",
            icon: "warning",
            buttons: ["Batal", "Ya, Setujui"]
        }).then(function(result) {
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
                        swal('Sukses', response.message || "Goods Receipt berhasil di-approve.", 'success').then(function() {
                            loadGridData();
                        });
                    } else {
                        swal('Peringatan', response.message || "Gagal menyetujui Goods Receipt.", 'warning');
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
                    swal('Error', errMsg, 'error');
                }
            });
        });
    }
</script>
@endsection
