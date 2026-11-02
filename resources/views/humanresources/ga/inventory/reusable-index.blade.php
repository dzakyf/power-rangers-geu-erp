@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .ga-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .ga-btn-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
</style>

<div class="main-content-header">
    <div>
        <h3 class="main-content-title tx-24 mg-b-5">Reusable Items & Peminjaman (Borrow & Return)</h3>
        <ol class="breadcrumb mg-b-0">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-inventory-ledger') }}">GA Inventory</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reusable Items</li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="row mb-3">
            <div class="col-12">
                <ul class="nav nav-pills" id="reusableTab" role="tablist" style="gap: 0.5rem;">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="units-tab" data-bs-toggle="pill" data-toggle="pill" href="#tabUnits" role="tab">Daftar Unit Barang</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="loans-tab" data-bs-toggle="pill" data-toggle="pill" href="#tabLoans" role="tab">Riwayat & Status Peminjaman</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="reusableTabContent">
            <!-- TAB 1: DAFTAR UNIT -->
            <div class="tab-pane fade show active" id="tabUnits" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="ga-toolbar">
                            <h5 class="card-title mb-0">Unit Terdaftar (Non-Asset & Reusable)</h5>
                            <div class="ga-btn-group">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegisterUnit" onclick="showReusableModal('#modalRegisterUnit')">
                                    <i class="mdi mdi-plus"></i> Daftarkan Unit Baru
                                </button>
                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalLoanUnit" onclick="showReusableModal('#modalLoanUnit')">
                                    <i class="mdi mdi-handshake"></i> Pinjamkan Unit
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="reloadUnitGrid()">
                                    <i class="mdi mdi-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="gridReusableUnits" style="width:100%;height:380px;background-color:white;"></div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: DAFTAR PEMINJAMAN -->
            <div class="tab-pane fade" id="tabLoans" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="ga-toolbar">
                            <h5 class="card-title mb-0">Status Peminjaman Unit</h5>
                            <div class="ga-btn-group">
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalReturnUnit" onclick="showReusableModal('#modalReturnUnit')">
                                    <i class="mdi mdi-keyboard-return"></i> Pengembalian Barang
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="reloadLoanGrid()">
                                    <i class="mdi mdi-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="gridReusableLoans" style="width:100%;height:380px;background-color:white;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftarkan Unit -->
<div class="modal fade" id="modalRegisterUnit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formRegisterUnit" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Daftarkan Unit Reusable Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Kode Unit / Barcode <span class="text-danger">*</span></label>
                    <input type="text" name="Unit_Code" class="form-control" placeholder="Contoh: UNIT/PRJ/001" required>
                </div>
                <div class="form-group mb-3">
                    <label>Kode Barang (Katalog) <span class="text-danger">*</span></label>
                    <select name="Product_Code" class="form-control" required>
                        <option value="">-- Pilih Barang --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->Product_Code }}">{{ $p->Product_Code }} - {{ $p->Description }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label>Serial Number (SN)</label>
                    <input type="text" name="Serial_Number" class="form-control" placeholder="Nomor seri pabrik">
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Kategori</label>
                        <select name="Category" class="form-control">
                            <option value="ELECTRONIC">Elektronik / AV</option>
                            <option value="TOOL">Perkakas / Alat Kerja</option>
                            <option value="FACILITY">Fasilitas / Kunci</option>
                            <option value="GENERAL" selected>Umum</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Kondisi Awal</label>
                        <select name="Condition_Status" class="form-control">
                            <option value="GOOD" selected>GOOD (Baik)</option>
                            <option value="FAIR">FAIR (Cukup)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label>Lokasi Standby</label>
                    <select name="Current_Location" class="form-control">
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Daftarkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pinjamkan Unit -->
<div class="modal fade" id="modalLoanUnit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formLoanUnit" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Form Peminjaman Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Unit ID / Kode Unit <span class="text-danger">*</span></label>
                    <input type="number" name="Unit_ID" class="form-control" placeholder="ID Unit (Pilih baris pada tabel)" required>
                </div>
                <div class="form-group mb-3">
                    <label>Nama Peminjam <span class="text-danger">*</span></label>
                    <input type="text" name="Borrower_Name" class="form-control" placeholder="Nama lengkap karyawan" required>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Departemen</label>
                        <input type="text" name="Borrower_Department" class="form-control" placeholder="Departemen">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Kontak / No Telp</label>
                        <input type="text" name="Borrower_Contact" class="form-control" placeholder="No HP/WA">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Tanggal Pinjam</label>
                        <input type="date" name="Date_Out" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Batas Pengembalian (Due Date)</label>
                        <input type="date" name="Due_Date" class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label>Keperluan / Catatan</label>
                    <textarea name="Purpose" class="form-control" rows="2" placeholder="Contoh: Rapat meeting room lantai 2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info text-white">Catat Peminjaman</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pengembalian Unit -->
<div class="modal fade" id="modalReturnUnit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formReturnUnit" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Form Pengembalian Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>ID Transaksi Peminjaman (Loan ID) <span class="text-danger">*</span></label>
                    <input type="number" name="Loan_ID" class="form-control" placeholder="ID Peminjaman (Pilih baris pada tabel Peminjaman)" required>
                </div>
                <div class="form-group mb-3">
                    <label>Kondisi Saat Dikembalikan</label>
                    <select name="Condition_In" class="form-control">
                        <option value="GOOD" selected>GOOD (Baik / Utuh)</option>
                        <option value="FAIR">FAIR (Berfungsi, terdapat tanda pemakaian)</option>
                        <option value="DAMAGED">DAMAGED (Rusak / Butuh Servis)</option>
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label>Catatan Hasil Pengecekan</label>
                    <textarea name="Return_Notes" class="form-control" rows="2" placeholder="Catatan kelengkapan / kondisi fisik"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Konfirmasi Pengembalian</button>
            </div>
        </form>
    </div>
</div>

<script>
var gridUnits, gridLoans;

function showReusableModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (typeof $ !== 'undefined' && $.fn.modal) {
        $(el).modal('show');
    }
}

function hideReusableModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        var instance = window.bootstrap.Modal.getInstance(el);
        if (instance) instance.hide();
    }
    if (typeof $ !== 'undefined' && $.fn.modal) {
        $(el).modal('hide');
    }
}

function initGrids() {
    gridUnits = new dhtmlXGridObject('gridReusableUnits');
    gridUnits.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridUnits.setHeader("No,Kode Unit,Kode Barang,Nama Barang,Serial Number,Asset No,Kondisi,Status,Lokasi,Peminjam Saat Ini");
    gridUnits.setInitWidths("50,140,110,200,120,110,90,110,120,160");
    gridUnits.setColAlign("center,left,left,left,left,left,center,center,left,left");
    gridUnits.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    gridUnits.setColSorting("int,str,str,str,str,str,str,str,str,str");
    gridUnits.setSkin("dhx_skyblue");
    gridUnits.init();
    gridUnits.clearAndLoad("{{ url('hrd-ga-inventory-reusable-unit-data') }}");

    gridUnits.attachEvent("onRowSelect", function(id) {
        $('#modalLoanUnit input[name="Unit_ID"]').val(id);
    });

    gridLoans = new dhtmlXGridObject('gridReusableLoans');
    gridLoans.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridLoans.setHeader("No,No Pinjam,Kode Unit,Barang,Nama Peminjam,Departemen,Tgl Pinjam,Batas Tgl,Status,Tgl Kembali");
    gridLoans.setInitWidths("50,160,130,180,160,120,100,100,100,140");
    gridLoans.setColAlign("center,left,left,left,left,left,center,center,center,center");
    gridLoans.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    gridLoans.setColSorting("int,str,str,str,str,str,str,str,str,str");
    gridLoans.setSkin("dhx_skyblue");
    gridLoans.init();
    gridLoans.clearAndLoad("{{ url('hrd-ga-inventory-reusable-loan-data') }}");

    gridLoans.attachEvent("onRowSelect", function(id) {
        $('#modalReturnUnit input[name="Loan_ID"]').val(id);
    });
}

function reloadUnitGrid() { gridUnits.clearAndLoad("{{ url('hrd-ga-inventory-reusable-unit-data') }}"); }
function reloadLoanGrid() { gridLoans.clearAndLoad("{{ url('hrd-ga-inventory-reusable-loan-data') }}"); }

$(document).ready(function() {
    initGrids();

    $('#reusableTab a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('#reusableTab .nav-link').removeClass('active');
        $(this).addClass('active');
        $('#reusableTabContent .tab-pane').removeClass('show active');
        $(target).addClass('show active');

        setTimeout(function() {
            if (target === '#tabUnits' && gridUnits) gridUnits.setSizes();
            if (target === '#tabLoans' && gridLoans) gridLoans.setSizes();
        }, 50);
    });

    $('#formRegisterUnit').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ url("hrd-ga-inventory-reusable-unit-save") }}', $(this).serialize(), function(resp) {
            alert(resp.message);
            hideReusableModal('#modalRegisterUnit');
            $('#formRegisterUnit')[0].reset();
            reloadUnitGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan.');
        });
    });

    $('#formLoanUnit').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ url("hrd-ga-inventory-reusable-loan-save") }}', $(this).serialize(), function(resp) {
            alert(resp.message);
            hideReusableModal('#modalLoanUnit');
            $('#formLoanUnit')[0].reset();
            reloadUnitGrid();
            reloadLoanGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan.');
        });
    });

    $('#formReturnUnit').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ url("hrd-ga-inventory-reusable-return-save") }}', $(this).serialize(), function(resp) {
            alert(resp.message);
            hideReusableModal('#modalReturnUnit');
            $('#formReturnUnit')[0].reset();
            reloadUnitGrid();
            reloadLoanGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan.');
        });
    });
});
</script>
@endsection

