@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>MASTER DATA</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/procurement-dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active"><span>Master Produk (Product Code)</span></li>
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
                <i class="fas fa-plus mr-1"></i> Form Tambah Produk
            </button>
            <a href="{{ url('product-export-excel') }}" class="btn btn-info btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- GRID CONTAINER DHTMLX -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white py-2">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-boxes mr-2"></i> Daftar Master Produk (Tabel: <code>product_code</code>) &mdash; <span class="text-warning">Tips: Double-klik pada baris tabel untuk mengedit data</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div id="ProductGrid" style="width:100%; height:450px; background-color:white; overflow:hidden"></div>
        </div>
    </div>

    <!-- CARD FORM TAMBAH PRODUK -->
    <div class="card shadow-sm border-success mb-4" id="form-card-container" style="display: none; border-top: 3px solid #28a745;">
        <div class="card-header bg-success text-white py-2">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-plus-circle mr-1"></i> Form Input Master Produk Baru
            </h6>
        </div>
        <div class="card-body bg-light" style="font-size: 12px;">
            <form id="form-tambah-produk" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">Product Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="product_code" readonly placeholder="" required>
                    </div>
                    <div class="col-md-5 form-group mb-2">
                        <label class="font-weight-bold">Nama Produk / Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="description" placeholder="Masukkan nama produk..." required>
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold">Spesifikasi (Spec)</label>
                        <input type="text" class="form-control form-control-sm" name="specification" placeholder="Spesifikasi...">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold">Satuan (UOM) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="uom" value="PCS" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Main Category <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="main_category" required>
                            <option value="ASSET">ASSET</option>
                            <option value="NON ASSET">NON ASSET</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="category" required>
                            <option value="BARANG">BARANG</option>
                            <option value="JASA">JASA</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Status Produk <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="status_Muncul" required>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="NONACTIVE">NONACTIVE</option>
                        </select>
                    </div>
                </div>

                <div class="row border-top pt-2 mt-2">
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Metode Input Foto:</label><br>
                        <div class="custom-control custom-radio custom-control-inline mt-1">
                            <input type="radio" id="typeUpload" name="photo_type" value="upload" class="custom-control-input" checked>
                            <label class="custom-control-label" for="typeUpload">Upload File ke Server</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline mt-1">
                            <input type="radio" id="typeLink" name="photo_type" value="link" class="custom-control-input">
                            <label class="custom-control-label" for="typeLink">Gunakan Link / URL</label>
                        </div>
                    </div>
                    <div class="col-md-8 form-group mb-2" id="wrapper-upload">
                        <label class="font-weight-bold">Pilih File Foto (JPG/PNG, Maks: 3MB)</label>
                        <input type="file" class="form-control-file form-control-sm" name="photo_file" accept="image/*">
                    </div>
                    <div class="col-md-8 form-group mb-2" id="wrapper-link" style="display: none;">
                        <label class="font-weight-bold">Link / URL Gambar Eksternal</label>
                        <input type="url" class="form-control form-control-sm" name="photo_link" placeholder="https://example.com/gambar-produk.jpg">
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="button" class="btn btn-secondary btn-sm mr-1" onclick="$('#form-card-container').hide();">Batal</button>
                    <button type="button" id="btn-simpan-produk" class="btn btn-success btn-sm font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Produk Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD FORM EDIT PRODUK -->
    <div class="card shadow-sm border-warning mb-4" id="form-edit-container" style="display: none; border-top: 3px solid #ffc107;">
        <div class="card-header bg-warning text-dark py-2">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-edit mr-1"></i> Form Edit Master Produk (ID: <span id="edit-id-label"></span>)
            </h6>
        </div>
        <div class="card-body bg-light" style="font-size: 12px;">
            <form id="form-edit-produk" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="edit_product_id" name="product_id">
                <div class="row">
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">Product Code (Readonly)</label>
                        <input type="text" class="form-control form-control-sm bg-white" id="edit_product_code" readonly>
                    </div>
                    <div class="col-md-5 form-group mb-2">
                        <label class="font-weight-bold">Nama Produk / Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="edit_description" name="description" required>
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold">Spesifikasi (Spec)</label>
                        <input type="text" class="form-control form-control-sm" id="edit_specification" name="specification">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold">Satuan (UOM) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="edit_uom" name="uom" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Main Category <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="edit_main_category" name="main_category" required>
                            <option value="ASSET">ASSET</option>
                            <option value="NON ASSET">NON ASSET</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="edit_category" name="category" required>
                            <option value="BARANG">BARANG</option>
                            <option value="JASA">JASA</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold">Status Produk <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="edit_status" name="status_Muncul" required>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="NONACTIVE">NONACTIVE</option>
                        </select>
                    </div>
                </div>

                <div class="row border-top pt-2 mt-2 align-items-center">
                    <div class="col-md-3 form-group mb-2">
                        <label class="font-weight-bold">Ganti Foto:</label><br>
                        <div class="custom-control custom-radio custom-control-inline mt-1">
                            <input type="radio" id="editTypeUpload" name="photo_type" value="upload" class="custom-control-input" checked>
                            <label class="custom-control-label" for="editTypeUpload">Upload File Baru</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline mt-1">
                            <input type="radio" id="editTypeLink" name="photo_type" value="link" class="custom-control-input">
                            <label class="custom-control-label" for="editTypeLink">Gunakan Link URL</label>
                        </div>
                    </div>
                    
                    <!-- Input File / Link -->
                    <div class="col-md-5 form-group mb-2" id="edit-wrapper-upload">
                        <label class="font-weight-bold">Pilih File Foto Baru (Opsional)</label>
                        <input type="file" class="form-control-file form-control-sm" name="photo_file" accept="image/*">
                    </div>
                    <div class="col-md-5 form-group mb-2" id="edit-wrapper-link" style="display: none;">
                        <label class="font-weight-bold">Link / URL Gambar Eksternal Baru</label>
                        <input type="url" class="form-control form-control-sm" name="photo_link" placeholder="https://example.com/gambar.jpg">
                    </div>

                    <!-- KOTAK PREVIEW FOTO SAAT INI -->
                    
						<div class="col-md-4 form-group mb-2 text-center">
						    <label class="font-weight-bold d-block text-left">Foto Saat Ini:</label>
						    <div class="border rounded p-1 bg-white d-inline-block shadow-sm" style="min-width: 100px; min-height: 80px;">
						        <span class="text-muted d-block pt-3" id="no-foto-text" style="font-size: 11px;">Tidak ada foto</span>
						        <!-- Tambahkan style cursor pointer dan onclick event -->
						        <img id="edit-foto-preview" src="" alt="Preview Foto" style="max-height: 90px; max-width: 100%; object-fit: contain; display: none; cursor: pointer;" title="Klik untuk memperbesar foto" onclick="openImageModal(this.src)">
						    </div>
						    <small class="text-muted d-block mt-1" style="font-size: 10px;">Klik foto untuk memperbesar</small>
						</div>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="button" class="btn btn-secondary btn-sm mr-1" onclick="$('#form-edit-container').hide();">Batal</button>
                    <button type="button" id="btn-update-produk" class="btn btn-warning btn-sm font-weight-bold px-4 text-dark">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>


 
<
<!-- MODAL ZOOM / PREVIEW FOTO BESAR -->
<div class="modal fade" id="modalZoomFoto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document"> <!-- Menggunakan modal-xl agar ukuran modal lebih lebar -->
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 py-2">
                <h6 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-image mr-1"></i> Preview Foto Produk Ukuran Penuh
                </h6>
                <button type="button" class="close text-white" onclick="$('#modalZoomFoto').modal('hide');" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Padding dan penyesuaian ukuran max-width / max-height -->
            <div class="modal-body text-center p-3">
                <img id="img-zoomed" src="" class="rounded shadow" style="max-width: 100%; max-height: 75vh; width: auto; height: auto; object-fit: contain;"><br>
                <button type="button" class="btn btn-secondary btn-sm mt-3 px-4 font-weight-bold" onclick="$('#modalZoomFoto').modal('hide');">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var ProductGrid = new dhtmlXGridObject('ProductGrid');
ProductGrid.setImagePath("./dhtmlx/imgs/");
ProductGrid.setHeader("No.,Product Code,Nama Produk / Description,Spesifikasi (Spec),Satuan (UOM),Category,Status");
ProductGrid.setInitWidths("70,160,320,250,100,100,90");
ProductGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProductGrid.setColAlign("center,center,left,left,center,center,center");
ProductGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
ProductGrid.setColSorting("int,str,str,str,str,str,str");
ProductGrid.setEditable(false);
ProductGrid.enableMultiline(true);
ProductGrid.setSkin("dhx_skyblue");
ProductGrid.init();

// Memuat data XML dari endpoint Controller
ProductGrid.load("/geu/hcga/public/xml-product-list");

// --- EVENT DOUBLE-CLICK PADA BARIS GRID ---
ProductGrid.attachEvent("onRowDblClicked", function(rId, cInd) {
    editProduct(rId);
});

// Fungsi Kontrol Tampilan Form Tambah
function showAddForm() {
    $('#form-edit-container').hide();
    $('#form-card-container').toggle();
}

// Toggle Upload/Link pada Form Tambah
$('input[name="photo_type"]').on('change', function() {
    if ($(this).val() === 'upload') {
        $('#wrapper-upload').show();
        $('#wrapper-link').hide();
    } else {
        $('#wrapper-upload').hide();
        $('#wrapper-link').show();
    }
});

// Toggle Upload/Link pada Form Edit
$(document).on('change', '#form-edit-produk input[name="photo_type"]', function() {
    if ($(this).val() === 'upload') {
        $('#edit-wrapper-upload').show();
        $('#edit-wrapper-link').hide();
    } else {
        $('#edit-wrapper-upload').hide();
        $('#edit-wrapper-link').show();
    }
});

// Fungsi Ambil Data untuk Diedit
function editProduct(id) {
    $('#form-card-container').hide();
    $.get("{{ url('product-edit') }}/" + id, function(res) {
        if (res.status === 'success') {
            var p = res.data;
            $('#edit-id-label').text(p.ID);
            $('#edit_product_id').val(p.ID);
            $('#edit_product_code').val(p.Product_Code);
            $('#edit_description').val(p.Description);
            $('#edit_specification').val(p.Specification);
            $('#edit_uom').val(p.Unit_Name);
            $('#edit_main_category').val(p.Main_Category);
            $('#edit_category').val(p.Category);
            $('#edit_status').val(p.status_Muncul);

            // Cek apakah data foto (p.Foto) ada isinya di database
            if (p.Foto) {
                // Tentukan apakah berupa URL eksternal (http...) atau file lokal
                var imgSrc = (p.Foto.indexOf('http') === 0) ? p.Foto : "{{ asset('') }}" + p.Foto;
                
                // Masukkan ke input link jika berupa URL eksternal, dan set radio button-nya
                if (p.Foto.indexOf('http') === 0) {
                    $('#editTypeLink').prop('checked', true).trigger('change');
                    $('input[name="photo_link"]').val(p.Foto);
                } else {
                    $('#editTypeUpload').prop('checked', true).trigger('change');
                }

                // Tampilkan gambar pada kotak preview
                $('#edit-foto-preview').attr('src', imgSrc).show();
                $('#no-foto-text').hide();
            } else {
                $('#edit-foto-preview').hide();
                $('#no-foto-text').show();
            }

            $('#form-edit-container').show();
            $('html, body').animate({ scrollTop: $('#form-edit-container').offset().top - 70 }, 500);
        }
    }).fail(function() {
        alert("Gagal memuat data produk untuk diedit.");
    });
}

// Fungsi untuk membuka modal zoom foto
function openImageModal(imgUrl) {
    if (imgUrl) {
        $('#img-zoomed').attr('src', imgUrl);
        $('#modalZoomFoto').modal('show');
    }
}

// AJAX Simpan Produk Baru
$(document).ready(function() {
    $('#btn-simpan-produk').on('click', function() {
        var formData = new FormData($('#form-tambah-produk')[0]);

        $.ajax({
            url: "{{ url('product-store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    $('#form-card-container').hide();
                    $('#form-tambah-produk')[0].reset();
                    ProductGrid.clearAll();
                    ProductGrid.load("/geu/hcga/public/xml-product-list");
                }
            },
            error: function(err) {
                var errMsg = "Gagal menyimpan produk baru. Pastikan Product Code belum terdaftar.";
                if (err.responseJSON && err.responseJSON.message) {
                    errMsg = err.responseJSON.message;
                }
                alert(errMsg);
            }
        });
    });

    // AJAX Update / Simpan Perubahan Produk
    $('#btn-update-produk').on('click', function() {
        var id = $('#edit_product_id').val();
        var formData = new FormData($('#form-edit-produk')[0]);

        $.ajax({
            url: "{{ url('product-update') }}/" + id,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    $('#form-edit-container').hide();
                    ProductGrid.clearAll();
                    ProductGrid.load("/geu/hcga/public/xml-product-list");
                }
            },
            error: function(err) {
                var errMsg = "Gagal memperbarui data produk.";
                if (err.responseJSON && err.responseJSON.message) {
                    errMsg = err.responseJSON.message;
                }
                alert(errMsg);
            }
        });
    });
});
</script>
@endsection