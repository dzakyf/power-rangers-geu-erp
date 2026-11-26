@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'Customs Clearance',
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Customs Clearance (Dokumen Ekspor per BC)</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <input type="button" id="DetailCustomsClearance" value="Upload / Detail Dokumen" class="btn btn-sm btn-info" onclick="CustomsClearanceDetail()" />
            <button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-filter-bar">
            <div>
                <label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
                <input type="date" id="tgl1" name="tgl1" value="" class="text-black form-control form-control-sm" style="width:auto;" />
            </div>
            <div>
                <label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
                <input type="date" id="tgl2" name="tgl2" value="" class="text-black form-control form-control-sm" style="width:auto;" />
            </div>
            <div>
                <button type="button" onclick="refreshdata()" class="btn btn-sm btn-primary">Filter</button>
            </div>
        </div>
        <div class="cns-grid-wrapper">
            <div id="CustomsClearanceGrid" class="cns-grid" style="width:100%;max-width:100%;height:360px;background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>
<div id="divlistCustomsClearance"></div>

<script>
    function CustomsClearanceDetail() {
        var selectedId = CustomsClearanceGrid.getSelectedId();
        if (selectedId != null) {
            LoadM('divlistCustomsClearance', 'cns-export-customs-clearance-detail?Task=' + encodeURIComponent(selectedId));
        } else {
            swal('Perhatian', 'Pilih baris data', 'warning');
        }
        return true;
    }

    CustomsClearanceGrid = new dhtmlXGridObject('CustomsClearanceGrid');
    CustomsClearanceGrid.setImagePath("./dhtmlx/imgs/");
    CustomsClearanceGrid.setHeader("No,Contract No,Booking Confirmation,Dokumen 3D,PEB,NPE,SPHIB,Kelengkapan,Catatan");
    CustomsClearanceGrid.attachHeader("#rspan,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#text_filter,#text_filter");
    CustomsClearanceGrid.setInitWidths("45,160,180,105,95,95,95,105,*");
    CustomsClearanceGrid.setColAlign("center,left,left,center,center,center,center,center,left");
    CustomsClearanceGrid.setColSorting("int,str,str,na,na,na,na,str,str");
    CustomsClearanceGrid.setColTypes("ron,ro,ro,cns_doc,cns_doc,cns_doc,cns_doc,ro,ro");
    CustomsClearanceGrid.enableMultiline(true);
    CustomsClearanceGrid.enableAutoHeight(true, 420);
    CustomsClearanceGrid.attachEvent("onRowDblClicked", CustomsClearanceDetail);
    CustomsClearanceGrid.setSkin("dhx_skyblue");
    CustomsClearanceGrid.init();

    var CustomsClearancePagination = initCnsGridPagination(CustomsClearanceGrid, {
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        serverFilter: false,
        getUrl: function (params) {
            var q = [
                'posStart=' + params.posStart,
                'count=' + params.count,
                'tgl1=' + encodeURIComponent($('#tgl1').val()),
                'tgl2=' + encodeURIComponent($('#tgl2').val())
            ];
            if (params.sortCol !== null && params.sortCol !== undefined) {
                q.push('sort_col=' + params.sortCol);
                q.push('sort_dir=' + (params.sortDir || 'asc'));
            }
            return 'xml-cns-export-customs-clearance?' + q.join('&');
        },
        onLoaded: function () {
            CustomsClearanceGrid.setSizes();
        }
    });
    refreshdata();

    $(window).on('resize', function () {
        CustomsClearanceGrid.setSizes();
    });

    function refreshdata() {
        CustomsClearancePagination.reload(1);
    }
</script>
@endsection
