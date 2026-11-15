@if(!$header)
    <div class="alert alert-danger mt-3" style="font-size: 12px;">
        <i class="fas fa-exclamation-triangle mr-1"></i> Data Purchase Request tidak ditemukan.
    </div>
@else
<div class="card shadow-sm border-success mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid #28a745;">
    <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-check-circle mr-2"></i>Detail & Flow Process PR (Status: {{ $header->Status }}) — {{ $header->PR_No }}
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#PRDiv').html('');" title="Tutup Detail">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <form id="form-edit-approved-pr" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="pr_no" value="{{ $header->PR_No }}">

            <!-- HEADER INFORMASI PR UTAMA -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="font-weight-bold text-muted">Nomor PR:</label>
                    <input type="text" class="form-control form-control-sm font-weight-bold text-primary" value="{{ $header->PR_No }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Tanggal PR:</label>
                    <input type="date" class="form-control form-control-sm" name="pr_date" value="{{ $header->PR_Date }}" required>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Department:</label>
                    <select class="form-control form-control-sm select2-edit-search" name="department" required style="width: 100%;">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->Department_ID }}" {{ ($dept->Department_ID == $header->Department || $dept->Department == $header->Department) ? 'selected' : '' }}>
                                {{ $dept->Department }} (ID: {{ $dept->Department_ID }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-muted">Disetujui Oleh:</label>
                    <input type="text" class="form-control form-control-sm bg-white font-weight-bold text-success" value="{{ $header->Approval_User ?? 'Admin' }} ({{ $header->Approval_Date ?? '-' }})" readonly>
                </div>
            </div>

            <!-- INFORMASI TAMBAHAN (PIC, SITE, KATEGORI ANGGARAN) -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="font-weight-bold">PIC (Penanggung Jawab):</label>
                    <input type="text" class="form-control form-control-sm" name="pic_name" value="{{ $header->PIC ?? $header->PIC_Name ?? '' }}" placeholder="Nama PIC..." required>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Phone PIC:</label>
                    <input type="text" class="form-control form-control-sm" name="pic_phone" value="{{ $header->PIC_Phone ?? '' }}" placeholder="No. Telepon / WhatsApp...">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Email PIC:</label>
                    <input type="email" class="form-control form-control-sm" name="pic_email" value="{{ $header->PIC_Email ?? '' }}" placeholder="email@domain.com...">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Site Tujuan:</label>
                    <input type="text" class="form-control form-control-sm" name="site_destination" value="{{ $header->PIC_Address ?? '' }}" placeholder="Contoh: Site Bekasi / Head Office..." required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold">Kategori Anggaran (Main Category):</label>
                    <select class="form-control form-control-sm" name="main_category" required>
                        <option value="ASSET" {{ ($header->Category == 'ASSET') ? 'selected' : '' }}>ASSET</option>
                        <option value="NON ASSET" {{ ($header->Category == 'NON ASSET') ? 'selected' : '' }}>NON ASSET</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold">Sub Kategori (Category):</label>
                    <select class="form-control form-control-sm" name="category" required>
                        <option value="BARANG" {{ ($header->Sub_Category == 'BARANG') ? 'selected' : '' }}>BARANG</option>
                        <option value="JASA" {{ ($header->Sub_Category == 'JASA') ? 'selected' : '' }}>JASA</option>
                    </select>
                </div>
            </div>



            <!-- CATATAN & RIWAYAT PERCAKAPAN -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold">Catatan / Note:</label>
                    <textarea class="form-control form-control-sm" name="note" rows="2" placeholder="Tulis catatan...">{{ $header->Note ?? '' }}</textarea>
                </div>
            </div>

            @if(isset($noteHistories) && count($noteHistories) > 0)
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold text-info"><i class="fas fa-history mr-1"></i> Riwayat Percakapan / History Note:</label>
                    <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                        @foreach($noteHistories as $hist)
                            <div class="border-bottom mb-2 pb-1 bg-white p-2 rounded shadow-sm">
                                <div class="d-flex justify-content-between">
                                    <span class="font-weight-bold text-primary" style="font-size: 11px;">
                                        <i class="fas fa-user-circle mr-1"></i> {{ $hist->Created_By ?? 'System' }} 
                                        <span class="badge badge-secondary ml-1" style="font-size: 9px;">{{ $hist->Action_Status ?? 'NOTE' }}</span>
                                    </span>
                                    <span class="text-muted" style="font-size: 10px;">{{ $hist->Created_Date ?? '' }}</span>
                                </div>
                                <p class="mb-0 text-dark mt-1" style="font-size: 11.5px;">{{ $hist->Note }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <hr class="my-3">

            <!-- RINCIAN MATERIAL -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-success font-weight-bold mb-0" style="font-size: 12px;">
                    <i class="fas fa-boxes mr-1"></i> Rincian Material / Multi Produk (Approved Items)
                </h6>
                <button type="button" id="btn-tambah-baris-approved" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Item Produk
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white" id="tabel-multi-produk-approved" style="font-size: 12px;">
                    <thead class="thead-light text-center">
                        <tr>
                            <th style="width: 22%;">Product Code</th>
                            <th style="width: 20%;">Nama Item / Deskripsi</th>
                            <th style="width: 18%;">Spesifikasi (Spec)</th>
                            <th style="width: 7%;">Qty</th>
                            <th style="width: 8%;">Satuan</th>
                            <th style="width: 10%;">Lampiran / Foto</th>
                            <th style="width: 12%;">Ganti File Baru</th>
                            <th style="width: 3%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-produk-approved">
                        @foreach($details as $item)
                        <tr class="baris-item">
                            <td>
                                <select class="form-control form-control-sm select2-approved-produk" name="product_code[]" required style="width: 100%;">
                                    <option value="">-- Cari Product Code --</option>
                                    @foreach($materials as $mat)
                                        <option value="{{ $mat->Product_Code }}" {{ $mat->Product_Code == $item->Product_Code ? 'selected' : '' }}>
                                            {{ $mat->Product_Code }} - {{ $mat->Description }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm input-desc" name="product_desc[]" value="{{ $item->Product_Desc }}" readonly required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm input-spec" name="specification[]" value="{{ $item->Specification }}">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-center" name="qty[]" value="{{ $item->Qty }}" min="1" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-center input-unit" name="unit[]" value="{{ $item->Unit }}" readonly>
                            </td>
                            <td class="text-center align-middle">
                                <input type="hidden" name="old_attachment[]" value="{{ $item->Attachment }}">
                                @if(!empty($item->Attachment))
                                    <a href="{{ asset($item->Attachment) }}" target="_blank" class="badge badge-info p-1">Lihat File</a>
                                @else
                                    <span class="text-muted" style="font-size: 10px;">-</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <input type="file" class="form-control-file form-control-sm" name="attachment[]" style="font-size: 10px;">
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-xs btn-hapus-baris-approved" title="Hapus Baris">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-right mb-3">
                <button type="button" id="btn-update-approved-pr" class="btn btn-success btn-sm font-weight-bold px-4 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan Item
                </button>
            </div>
        </form>

        <hr class="my-3">

        <!-- PROSES LANJUTAN KE RFQ -->
        <!-- PROSES LANJUTAN KE RFQ (MULTI-VENDOR) -->
        <div class="bg-light p-3 rounded border border-success">
            <h6 class="font-weight-bold text-success mb-2" style="font-size: 12px;">
                <i class="fas fa-share mr-1"></i> Proses Lanjutan Procurement Flow (Multi-Vendor RFQ)
            </h6>
            
            <div class="row align-items-end mb-3">
                <div class="col-md-7 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark mb-1">Pilih Vendor / Supplier Tujuan:</label>
                    <select id="supplier_id_rfq" class="form-control form-control-sm select2-edit-search" style="width: 100%;">
                        <option value="">-- Cari & Pilih Vendor / Supplier Lainnya --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->Supplier_ID }}">
                                {{ $sup->Supplier_ID }} - {{ $sup->Company_Name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark mb-1">Tanggal RFQ (RFQ Date):</label>
                    <input type="date" id="rfq_date_input" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-2 text-md-right">
                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm px-3 py-1.5 w-100" onclick="lanjutKeRFQ('{{ $header->PR_No }}')">
                        <i class="fas fa-plus mr-1"></i> Buat RFQ
                    </button>
                </div>
            </div>

            <!-- TABEL DAFTAR RFQ YANG SUDAH DIBUAT (MULTI-VENDOR LIST) -->
            @if(isset($listRfqs) && count($listRfqs) > 0)
            <div class="mt-3">
                <label class="font-weight-bold text-primary" style="font-size: 11.5px;">
                    <i class="fas fa-list mr-1"></i> Daftar RFQ Terkirim ke Vendor (Multi-Vendor):
                </label>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm bg-white mb-0" style="font-size: 11.5px;">
                        <thead class="thead-light text-center">
                            <tr>
                                <th style="width: 25%;">No. RFQ</th>
                                <th style="width: 15%;">Tanggal RFQ</th>
                                <th style="width: 15%;">ID Vendor</th>
                                <th style="width: 35%;">Nama Perusahaan (Supplier)</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listRfqs as $rfq)
                            <tr>
                                <td class="font-weight-bold text-primary text-center">{{ $rfq->RFQ_No }}</td>
                                <td class="text-center">{{ $rfq->RFQ_Date }}</td>
                                <td class="text-center">{{ $rfq->Supplier_ID }}</td>
                                <td>{{ $rfq->Company_Name }}</td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $rfq->Status }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="alert alert-warning py-1 px-2 mb-0" style="font-size: 11px;">
                <i class="fas fa-info-circle mr-1"></i> Belum ada RFQ yang dibuat untuk PR ini. Silakan pilih vendor di atas dan klik "Buat RFQ".
            </div>
            @endif
        </div>

<!-- TEMPLATE OPTION PRODUCT CODE -->
<template id="template-options-approved">
    <option value="">-- Cari Product Code --</option>
    @foreach($materials as $mat)
        <option value="{{ $mat->Product_Code }}">{{ $mat->Product_Code }} - {{ $mat->Description }}</option>
    @endforeach
</template>

<script>
$(document).ready(function() {
    function initSelect2Approved(element) {
        if ($.fn.select2) {
            $(element).select2({ width: '100%', placeholder: '-- Ketik untuk mencari --', allowClear: true });
        }
    }

    initSelect2Approved('.select2-edit-search');
    initSelect2Approved('.select2-approved-produk');

    // Event Change Product Code
    $(document).on('change', '.select2-approved-produk', function() {
        var selectEl = $(this);
        var kode     = selectEl.val();
        var row      = selectEl.closest('tr');

        var inputDesc = row.find('.input-desc');
        var inputSpec = row.find('.input-spec');
        var inputUnit = row.find('.input-unit');

        if (!kode) {
            inputDesc.val(''); inputSpec.val(''); inputUnit.val('PCS');
            return;
        }

        $.get("{{ url('get-material-detail') }}", { code: kode }, function(res) {
            if (res.status === 'success') {
                inputDesc.val(res.nama);
                inputSpec.val(res.spec);
                inputUnit.val(res.uom);
            }
        });
    });

    // Tambah Baris
    $('#btn-tambah-baris-approved').on('click', function() {
        var optionsHtml = $('#template-options-approved').html();
        var newRow = `
            <tr class="baris-item">
                <td>
                    <select class="form-control form-control-sm select2-approved-produk" name="product_code[]" required style="width: 100%;">
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm input-desc" name="product_desc[]" placeholder="Otomatis..." readonly required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm input-spec" name="specification[]" placeholder="Spesifikasi...">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center" name="qty[]" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-center input-unit" name="unit[]" value="PCS" readonly>
                </td>
                <td class="text-center align-middle">
                    <input type="hidden" name="old_attachment[]" value="">
                    <span class="text-muted" style="font-size: 10px;">Baru</span>
                </td>
                <td class="align-middle">
                    <input type="file" class="form-control-file form-control-sm" name="attachment[]" style="font-size: 10px;">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-xs btn-hapus-baris-approved" title="Hapus Baris">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        var appendedRow = $(newRow).appendTo('#tbody-produk-approved');
        initSelect2Approved(appendedRow.find('.select2-approved-produk'));
    });

    // Hapus Baris
    $(document).on('click', '.btn-hapus-baris-approved', function() {
        if ($('#tbody-produk-approved tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('Minimal harus ada 1 item rincian produk!');
        }
    });

    // Simpan Perubahan Item PR Approved via AJAX
    $(document).off('click', '#btn-update-approved-pr').on('click', '#btn-update-approved-pr', function(e) {
        e.preventDefault();
        var formData = new FormData($('#form-edit-approved-pr')[0]);

        $.ajax({
            url: "{{ url('purchase-request-update') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    if (typeof PRGrid !== 'undefined') {
                        PRGrid.clearAll();
                        PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
                    }
                }
            },
            error: function(err) {
                alert('Gagal menyimpan perubahan.');
            }
        });
    });
});


// Fungsi Lanjut ke RFQ (Multi-Vendor)
// Fungsi Lanjut ke RFQ (Memastikan form tertutup setelah sukses)
function lanjutKeRFQ(prNo) {
    var supplierId = $('#supplier_id_rfq').val();
    var rfqDate    = $('#rfq_date_input').val();

    if (!supplierId) {
        alert('Silakan pilih Vendor / Supplier terlebih dahulu!');
        $('#supplier_id_rfq').focus();
        return;
    }

    if (!rfqDate) {
        alert('Silakan tentukan Tanggal RFQ terlebih dahulu!');
        $('#rfq_date_input').focus();
        return;
    }

    if (!confirm('Apakah Anda yakin ingin membuat RFQ untuk Purchase Request ' + prNo + ' kepada vendor terpilih?')) {
        return;
    }

    $.post("{{ url('purchase-request-to-rfq') }}", {
        _token: "{{ csrf_token() }}",
        pr_no: prNo,
        supplier_id: supplierId,
        rfq_date: rfqDate
    }, function(res) {
        if (res.status === 'success') {
            alert(res.message);
            
            // CARA 1: Kosongkan kontainer utama PRDiv
            $('#PRDiv').html('');
            
            // CARA 2: Jika kontainer pembungkusnya memiliki ID lain, kita sembunyikan card-nya secara langsung
            $('#form-edit-approved-pr').closest('.card').fadeOut(300, function() {
                $(this).remove();
            });

            // Refresh grid utama di belakang
            if (typeof PRGrid !== 'undefined') {
                PRGrid.clearAll();
                PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
            }
        } else {
            alert(res.message || 'Gagal memproses RFQ.');
        }
    }).fail(function(err) {
        var errMsg = "Terjadi kesalahan saat memproses RFQ.";
        if (err.responseJSON && err.responseJSON.message) {
            errMsg = err.responseJSON.message;
        }
        alert(errMsg);
    });
}
</script>
@endif