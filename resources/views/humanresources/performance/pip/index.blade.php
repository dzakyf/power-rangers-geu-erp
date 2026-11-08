@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-review-index') }}">Performance</a></li>
        <li class="breadcrumb-item active"><span class="active">Performance Improvement Plan (PIP)</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Rencana Perbaikan Kinerja (PIP Plan)</h5>
        <div>
            <span class="badge bg-danger p-2 me-2">Otomatis Terbit Bagi Review Grade D / Skor &lt; 60</span>
            <button type="button" class="btn btn-secondary" onclick="reloadGrid()">
                <i class="feather-refresh-cw me-1"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="gridPipContainer" style="width: 100%; height: 500px; background-color: white;"></div>
    </div>
</div>

<script>
    var myGrid;
    document.addEventListener("DOMContentLoaded", function () {
        myGrid = new dhtmlXGridObject('gridPipContainer');
        myGrid.setImagePath("{{ asset('dhtmlx/skins/skyblue/imgs/') }}/");
        myGrid.setHeader("No PIP,Karyawan,Departemen,Mentor / Atasan,Ref Review,Durasi,Rentang Waktu,Hasil Akhir,Status");
        myGrid.setInitWidths("150,180,140,160,140,90,180,120,120");
        myGrid.setColAlign("left,left,left,left,center,center,center,center,center");
        myGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        myGrid.setColSorting("str,str,str,str,str,int,str,str,str");
        myGrid.enableAutoWidth(true);
        myGrid.init();

        myGrid.attachEvent("onRowDblClicked", function(rId, cInd){
            window.location.href = "{{ url('hrd-performance-pip-detail') }}/" + rId;
        });

        reloadGrid();
    });

    function reloadGrid() {
        var url = "{{ url('hrd-performance-pip-data') }}";
        myGrid.clearAndLoad(url);
    }
</script>
@endsection

