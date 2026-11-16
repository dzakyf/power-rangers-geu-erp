@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>MASTER DATA</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/procurement-dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active"><span>Master Supplier</span></li>
        </ol>
    </div>

    <!-- TOMBOL AKSI DI ATAS -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ url('procurement-dashboard') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>
        <div>
            <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2 shadow-sm" onclick="showAddForm()">
                <i class="fas fa-plus mr-1"></i> Form Tambah Supplier
            </button>
        </div>
    </div>

    <!-- GRID CONTAINER DHTMLX -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white py-2">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-truck mr-2"></i> Daftar Master Supplier &mdash; <span class="text-warning">Tips: Double-klik pada baris tabel untuk mengedit data</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div id="SupplierGrid" style="width:100%; height:450px; background-color:white; overflow:hidden"></div>
        </div>
    </div>

    <!-- CARD FORM TAMBAH SUPPLIER (DIRAPIKAN) -->
    <div class="card shadow-sm border-success mb-4" id="form-card-container" style="display: none; border-top: 3px solid #28a745;">
        <div class="card-header bg-success text-white py-2">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-plus-circle mr-1"></i> Form Input Master Supplier Baru
            </h6>
        </div>
        <div class="card-body bg-light" style="font-size: 12px;">
            <form id="form-tambah-supplier">
                @csrf
                <div class="row">
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">Supplier ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="supplier_id" placeholder="Contoh: SUP-001" required>
                    </div>
                    <div class="col-md-5 form-group mb-2">
                        <label class="font-weight-bold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="company_name" placeholder="Nama perusahaan supplier..." required>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Contact Person</label>
                        <input type="text" class="form-control form-control-sm" name="contact_person" placeholder="Nama kontak...">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">City</label>
                        <input type="text" class="form-control form-control-sm" name="city" placeholder="Kota...">
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">Phone / HP</label>
                        <input type="text" class="form-control form-control-sm" name="phone" placeholder="Nomor telepon...">
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Email</label>
                        <input type="email" class="form-control form-control-sm" name="email" placeholder="Alamat email...">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="status" required>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="NONACTIVE">NONACTIVE</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">NPWP</label>
                        <input type="text" class="form-control form-control-sm" name="NPWP" placeholder="Nomor NPWP...">
                    </div>
                    <div class="col-md-8 form-group mb-2">
                        <label class="font-weight-bold">Address</label>
                        <input type="text" class="form-control form-control-sm" name="address" placeholder="Alamat lengkap...">
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="button" class="btn btn-secondary btn-sm mr-1" onclick="$('#form-card-container').hide();">Batal</button>
                    <button type="button" id="btn-simpan-supplier" class="btn btn-success btn-sm font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Supplier Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD FORM EDIT SUPPLIER (DIRAPIKAN) -->
    <div class="card shadow-sm border-warning mb-4" id="form-edit-container" style="display: none; border-top: 3px solid #ffc107;">
        <div class="card-header bg-warning text-dark py-2">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-edit mr-1"></i> Form Edit Master Supplier (ID: <span id="edit-id-label"></span>)
            </h6>
        </div>
        <div class="card-body bg-light" style="font-size: 12px;">
            <form id="form-edit-supplier">
                @csrf
                <input type="hidden" id="edit_supplier_db_id" name="supplier_db_id">
                <div class="row">
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">Supplier ID (Readonly)</label>
                        <input type="text" class="form-control form-control-sm bg-white" id="edit_supplier_id" readonly>
                    </div>
                    <div class="col-md-5 form-group mb-2">
                        <label class="font-weight-bold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="edit_company_name" name="company_name" required>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Contact Person</label>
                        <input type="text" class="form-control form-control-sm" id="edit_contact_person" name="contact_person">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">City</label>
                        <input type="text" class="form-control form-control-sm" id="edit_city" name="city">
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">Phone</label>
                        <input type="text" class="form-control form-control-sm" id="edit_phone" name="phone">
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Email</label>
                        <input type="email" class="form-control form-control-sm" id="edit_email" name="email">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="edit_status" name="status" required>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="NONACTIVE">NONACTIVE</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">NPWP</label>
                        <input type="text" class="form-control form-control-sm" id="edit_npwp" name="NPWP">
                    </div>
                    <div class="col-md-8 form-group mb-2">
                        <label class="font-weight-bold">Address</label>
                        <input type="text" class="form-control form-control-sm" id="edit_address" name="address">
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="button" class="btn btn-secondary btn-sm mr-1" onclick="$('#form-edit-container').hide();">Batal</button>
                    <button type="button" id="btn-update-supplier" class="btn btn-warning btn-sm font-weight-bold px-4 text-dark">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
var SupplierGrid = new dhtmlXGridObject('SupplierGrid');
SupplierGrid.setImagePath("./dhtmlx/imgs/");
SupplierGrid.setHeader("No.,Supplier ID,Company Name,Contact Person,City,Address,Phone,Email,NPWP,Status");
SupplierGrid.setInitWidths("60,150,250,160,250,150,150,180,100,100");
SupplierGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
SupplierGrid.setColAlign("center,center,left,left,left,center,left,left,left,center");
SupplierGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
SupplierGrid.setColSorting("int,str,str,str,str,str,str,str,str,str");
SupplierGrid.setEditable(false);
SupplierGrid.enableMultiline(true);
SupplierGrid.setSkin("dhx_skyblue");
SupplierGrid.init();

// Memuat data XML dari Controller
SupplierGrid.load("/geu/hcga/public/xml-supplier-list");

// Event Double-Click untuk Edit Baris
SupplierGrid.attachEvent("onRowDblClicked", function(rId, cInd) {
    editSupplier(rId);
});

function showAddForm() {
    $('#form-edit-container').hide();
    $('#form-card-container').toggle();
}

function editSupplier(id) {
    $('#form-card-container').hide();
    $.get("{{ url('supplier-edit') }}/" + id, function(res) {
        if (res.status === 'success') {
            var s = res.data;
            $('#edit-id-label').text(s.ID);
            $('#edit_supplier_db_id').val(s.ID);
            $('#edit_supplier_id').val(s.Supplier_ID);
            $('#edit_company_name').val(s.Company_Name);
            $('#edit_contact_person').val(s.Contact_Person);
            $('#edit_city').val(s.City);
            $('#edit_phone').val(s.Phone);
            $('#edit_email').val(s.Email);
            $('#edit_npwp').val(s.NPWP); // Ditambahkan agar data NPWP ikut terpanggil saat edit
            $('#edit_address').val(s.Address);
            $('#edit_status').val(s.Status);

            $('#form-edit-container').show();
            $('html, body').animate({ scrollTop: $('#form-edit-container').offset().top - 70 }, 500);
        }
    }).fail(function() {
        alert("Gagal memuat data supplier.");
    });
}

$(document).ready(function() {
    // Simpan Supplier Baru
    $('#btn-simpan-supplier').on('click', function() {
        var formData = $('#form-tambah-supplier').serialize();

        $.ajax({
            url: "{{ url('supplier-store') }}",
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    $('#form-card-container').hide();
                    $('#form-tambah-supplier')[0].reset();
                    SupplierGrid.clearAll();
                    SupplierGrid.load("/geu/hcga/public/xml-supplier-list");
                }
            },
            error: function(err) {
                var errMsg = "Gagal menyimpan supplier baru.";
                if (err.responseJSON) {
                    if (err.responseJSON.message) {
                        errMsg = err.responseJSON.message;
                    } else if (err.responseJSON.errors) {
                        var errors = err.responseJSON.errors;
                        var firstKey = Object.keys(errors)[0];
                        errMsg = errors[firstKey][0];
                    }
                }
                alert(errMsg);
            }
        });
    });

    // Update Supplier
    $('#btn-update-supplier').on('click', function() {
        var id = $('#edit_supplier_db_id').val();
        var formData = $('#form-edit-supplier').serialize();

        $.post("{{ url('supplier-update') }}/" + id, formData, function(res) {
            if (res.status === 'success') {
                alert(res.message);
                $('#form-edit-container').hide();
                SupplierGrid.clearAll();
                SupplierGrid.load("/geu/hcga/public/xml-supplier-list");
            }
        }).fail(function(err) {
            var errMsg = "Gagal memperbarui data supplier.";
            if (err.responseJSON && err.responseJSON.message) {
                errMsg = err.responseJSON.message;
            }
            alert(errMsg);
        });
    });
});
</script>
@endsection