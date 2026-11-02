@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <div>
        <h3 class="main-content-title tx-24 mg-b-5">Stock Opname & Penyesuaian Stok (With Price)</h3>
        <ol class="breadcrumb mg-b-0">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-inventory-ledger') }}">GA Inventory</a></li>
            <li class="breadcrumb-item active" aria-current="page">Penyesuaian Stok</li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <!-- FORM ADJUSTMENT -->
    <div class="col-md-5 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Form Koreksi / Opname</h5>
                <p class="text-muted small mb-3">Mencatat selisih fisik vs sistem dengan penilaian harga satuan (WAC).</p>

                <form id="formAdjustment" class="forms-sample">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="Product_Code">Barang / Item <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="Product_Code" name="Product_Code" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->Product_Code }}">{{ $p->Product_Code }} - {{ $p->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="Location">Lokasi <span class="text-danger">*</span></label>
                            <select class="form-control" id="Location" name="Location" required>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="WH_ID">Gudang</label>
                            <select class="form-control" id="WH_ID" name="WH_ID">
                                <option value="">(Utama)</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh }}">{{ $wh }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="Type">Jenis Penyesuaian <span class="text-danger">*</span></label>
                            <select class="form-control" id="Type" name="Type" required>
                                <option value="IN">IN (Selisih Lebih / Penambahan)</option>
                                <option value="OUT">OUT (Selisih Kurang / Pemotongan)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="Qty">Jumlah Selisih <span class="text-danger">*</span></label>
                            <input type="number" step="any" min="0.0001" class="form-control" id="Qty" name="Qty" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="Unit_Cost">Harga Satuan / Unit Cost (Rp)</label>
                        <input type="number" step="any" class="form-control" id="Unit_Cost" name="Unit_Cost" placeholder="Kosongkan untuk otomatis memakai WAC sistem">
                        <small class="text-muted">Untuk penyesuaian OUT, sistem selalu menggunakan HPP/WAC berjalan.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="Reason">Alasan Penyesuaian / Temuan Opname <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="Reason" name="Reason" rows="2" required placeholder="Contoh: Selisih fisik hasil stock opname akhir bulan"></textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" id="btnSubmitAdj" class="btn btn-primary">
                            <i class="mdi mdi-check"></i> Simpan Penyesuaian
                        </button>
                        <a href="{{ url('hrd-ga-inventory-ledger') }}" class="btn btn-secondary">Lihat Ledger</a>
                    </div>
                </form>

                <div id="adjAlert" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- RIWAYAT ADJUSTMENT -->
    <div class="col-md-7 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="card-title mb-0">Riwayat Stock Adjustment (With Value)</h5>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="reloadAdjGrid()">
                        <i class="mdi mdi-refresh"></i> Refresh
                    </button>
                </div>
                <div id="gridAdjustments" style="width:100%;height:430px;background-color:white;"></div>
            </div>
        </div>
    </div>
</div>

<script>
var gridAdj;

function initAdjGrid() {
    gridAdj = new dhtmlXGridObject('gridAdjustments');
    gridAdj.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridAdj.setHeader("No,No Koreksi,Tanggal,Kode Barang,Nama Barang,Tipe,Qty,Hrg Satuan,Total Nilai,Lokasi,Alasan,Petugas");
    gridAdj.setInitWidths("40,140,90,100,160,60,70,90,100,80,160,90");
    gridAdj.setColAlign("center,left,center,left,left,center,right,right,right,left,left,left");
    gridAdj.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro");
    gridAdj.setNumberFormat("0,000.00", 6, ".", ",");
    gridAdj.setNumberFormat("0,000.00", 7, ".", ",");
    gridAdj.setNumberFormat("0,000.00", 8, ".", ",");
    gridAdj.setColSorting("int,str,date,str,str,str,int,int,int,str,str,str");
    gridAdj.setSkin("dhx_skyblue");
    gridAdj.init();
    reloadAdjGrid();
}

function reloadAdjGrid() {
    gridAdj.clearAndLoad("{{ url('hrd-ga-inventory-adjustment-data') }}");
}

$(document).ready(function() {
    initAdjGrid();

    $('#formAdjustment').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitAdj');
        $btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: "{{ url('hrd-ga-inventory-adjustment-save') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Simpan Penyesuaian');
                $('#adjAlert').html('<div class="alert alert-success">' + resp.message + '</div>');
                $('#formAdjustment')[0].reset();
                reloadAdjGrid();
            },
            error: function(err) {
                $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Simpan Penyesuaian');
                var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan penyesuaian.';
                $('#adjAlert').html('<div class="alert alert-danger">' + msg + '</div>');
            }
        });
    });
});
</script>
@endsection

