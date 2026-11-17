@if(!$header)
    <div class="alert alert-danger mt-3" style="font-size: 12px;">
        <i class="fas fa-exclamation-triangle mr-1"></i> Data Purchase Request tidak ditemukan.
    </div>
@else
<div class="card shadow-sm border-warning mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid #ffc107;">
    <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-edit mr-2"></i>Edit & Approval Purchase Request (Status: {{ $header->Status }}) — {{ $header->PR_No }}
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#PRDiv').html('');" title="Tutup Detail">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <form id="form-edit-pr" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="pr_no" value="{{ $header->PR_No }}">

            <!-- HEADER INFORMASI PR UTAMA (EDITABLE) -->
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
                    <label class="font-weight-bold text-muted">Location:</label>
                    <input type="text" class="form-control form-control-sm bg-white font-weight-bold" name="location" value="{{ $header->Location ?? Auth::user()->Location }}" readonly>
                </div>
            </div>

            
            <!-- INFORMASI TAMBAHAN (PIC, SITE, ESTIMATED BUDGET) -->
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="font-weight-bold">PIC (Penanggung Jawab):</label>
                    <input type="text" class="form-control form-control-sm" name="pic_name" value="{{ $header->PIC ?? $header->PIC_Name ?? '' }}" placeholder="Nama PIC..." required>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold">Phone PIC:</label>
                    <input type="text" class="form-control form-control-sm" name="pic_phone" value="{{ $header->PIC_Phone ?? '' }}" placeholder="No. Telepon...">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Email PIC:</label>
                    <input type="email" class="form-control form-control-sm" name="pic_email" value="{{ $header->PIC_Email ?? '' }}" placeholder="email@domain.com...">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Site Tujuan:</label>
                    <input type="text" class="form-control form-control-sm" name="site_destination" value="{{ $header->PIC_Address ?? '' }}" placeholder="Contoh: Site Bekasi..." required>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold text-primary">Estimated Budget (Rp):</label>
                    <input type="text" class="form-control form-control-sm font-weight-bold text-right text-primary bg-light" id="display_estimated_budget" value="{{ number_format($header->Estimated_Budget ?? 0, 0, ',', '.') }}" readonly>
                    <input type="hidden" id="estimated_budget" name="estimated_budget" value="{{ $header->Estimated_Budget ?? 0 }}">
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

            <!-- BAGIAN NOTE & HISTORY PERCAKAPAN NOTE -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold">Catatan / Note Baru:</label>
                    <textarea class="form-control form-control-sm" name="note" rows="2" placeholder="Tulis catatan atau instruksi tambahan untuk PR ini..." required></textarea>
                </div>
            </div>



           <!-- TAMPILAN RIWAYAT / HISTORY NOTE DARI pr_notes_history -->
            @if(isset($noteHistories) && count($noteHistories) > 0)
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold text-info"><i class="fas fa-history mr-1"></i> Riwayat Percakapan / History Note:</label>
                    <div class="border rounded p-2 bg-light" style="max-height: 160px; overflow-y: auto;">
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

            <!-- HEADER TABEL MULTI PRODUK -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-success font-weight-bold mb-0" style="font-size: 12px;">
                    <i class="fas fa-boxes mr-1"></i> Rincian Material / Multi Produk (Dapat Diubah)
                </h6>
                <button type="button" id="btn-tambah-baris-edit" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Item Produk
                </button>
            </div>

            <!-- TABEL EDITABLE ITEM -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white" id="tabel-multi-produk-edit" style="font-size: 12px;">
                    <thead class="thead-light text-center">
                        <tr>
                            <th style="width: 20%;">Product Code</th>
                            <th style="width: 17%;">Nama Item / Deskripsi</th>
                            <th style="width: 15%;">Spesifikasi (Spec)</th>
                            <th style="width: 6%;">Qty</th>
                            <th style="width: 7%;">Satuan</th>
                            <th style="width: 11%;">Est. Price (Rp)</th>
                            <th style="width: 11%;">Subtotal (Rp)</th>
                            <th style="width: 10%;">Lampiran</th>
                            <th style="width: 3%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-produk-edit">
                        @foreach($details as $item)
                            @php
                                // Sesuaikan dengan nama kolom asli di database (Default_Price)
                                $itemPrice = $item->Default_Price ?? $item->Price ?? 0;
                                $itemSubtotal = ($item->Qty ?? 1) * $itemPrice;
                            @endphp
                        <tr class="baris-item">
                            <td>
                                <select class="form-control form-control-sm select2-edit-produk" name="product_code[]" required style="width: 100%;">
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
                                <input type="number" class="form-control form-control-sm text-center input-qty" name="qty[]" value="{{ $item->Qty }}" min="1" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-center input-unit" name="unit[]" value="{{ $item->Unit }}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-right input-price" name="price[]" value="{{ $itemPrice }}" min="0" step="any" placeholder="0" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-right bg-light font-weight-bold input-subtotal" value="{{ number_format($itemSubtotal, 0, ',', '.') }}" readonly>
                            </td>
                            <td class="text-center align-middle">
                                    <input type="hidden" name="old_attachment[]" value="{{ $item->Attachment }}">
                                    @if(!empty($item->Attachment))
                                        <a href="{{ asset($item->Attachment) }}" target="_blank" class="btn btn-info text-white font-weight-bold mb-1 d-inline-block shadow-sm" style="padding: 1px 6px; font-size: 10px; line-height: 1.4; border-radius: 3px;" title="Lihat Lampiran">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    @endif
                                    <input type="file" class="form-control-file form-control-sm" name="attachment[]" style="font-size: 9px;">
                                </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-xs btn-hapus-baris-edit" title="Hapus Baris">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- TOMBOL SIMPAN PERUBAHAN ITEM -->
            <div class="text-right mb-4">
                <button type="button" id="btn-update-pr" class="btn btn-primary btn-sm font-weight-bold px-4 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan Item PR
                </button>
            </div>
        </form>

        <hr class="my-3">

        <!-- FORM AKSI APPROVAL / REJECT -->
        <div class="bg-light p-3 rounded border">
            <h6 class="font-weight-bold text-dark mb-2" style="font-size: 12px;">
                <i class="fas fa-check-double mr-1 text-success"></i> Tindakan Persetujuan (Approval)
            </h6>
            <div class="row align-items-end">
                <div class="col-md-7 mb-2 mb-md-0">
                    <label class="mb-1 text-muted">Catatan Approval / Alasan Penolakan (Opsional):</label>
                    <input type="text" id="approval_note" class="form-control form-control-sm" placeholder="Masukkan catatan persetujuan atau alasan jika ditolak...">
                </div>
                <div class="col-md-5 text-md-right">
                    <button type="button" class="btn btn-success btn-sm font-weight-bold px-3 mr-1" onclick="prosesApproval('APPROVED')">
                        <i class="fas fa-check mr-1"></i> Approve PR
                    </button>
                    <button type="button" class="btn btn-danger btn-sm font-weight-bold px-3" onclick="prosesApproval('REJECTED')">
                        <i class="fas fa-times mr-1"></i> Reject PR
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TEMPLATE OPTION PRODUCT CODE UNTUK CLONING BARIS BARU -->
<template id="template-options-produk-edit">
    <option value="">-- Cari Product Code --</option>
    @foreach($materials as $mat)
        <option value="{{ $mat->Product_Code }}">{{ $mat->Product_Code }} - {{ $mat->Description }}</option>
    @endforeach
</template>

<script>
$(document).ready(function() {
    // 1. Inisialisasi Select2
    function initSelect2Edit(element) {
        if ($.fn.select2) {
            $(element).select2({
                width: '100%',
                placeholder: '-- Ketik untuk mencari --',
                allowClear: true
            });
        }
    }

    initSelect2Edit('.select2-edit-search');
    initSelect2Edit('.select2-edit-produk');

    // 2. Event Change Product Code pada baris
    $(document).on('change', '.select2-edit-produk', function() {
        var selectEl = $(this);
        var kode     = selectEl.val();
        var row      = selectEl.closest('tr');

        var inputDesc = row.find('.input-desc');
        var inputSpec = row.find('.input-spec');
        var inputUnit = row.find('.input-unit');

        if (!kode) {
            inputDesc.val('');
            inputSpec.val('');
            inputUnit.val('PCS');
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

    // Fungsi Hitung Otomatis Qty x Price -> Estimated Budget
    function hitungTotalBudget() {
        var grandTotal = 0;

        $('#tbody-produk-edit tr.baris-item').each(function() {
            var qty   = parseFloat($(this).find('.input-qty').val()) || 0;
            var price = parseFloat($(this).find('.input-price').val()) || 0;
            var sub   = qty * price;

            $(this).find('.input-subtotal').val(sub.toLocaleString('id-ID'));
            grandTotal += sub;
        });

        $('#estimated_budget').val(grandTotal);
        $('#display_estimated_budget').val(grandTotal.toLocaleString('id-ID'));
    }

    // Trigger kalkulasi saat Qty atau Price diketik/diubah
    $(document).on('input change keyup', '.input-qty, .input-price', function() {
        hitungTotalBudget();
    });

    // Jalankan kalkulasi pertama kali saat form terbuka
    hitungTotalBudget();

    // Update Tombol Tambah Baris Baru (menyertakan kolom Price & Subtotal)
    $('#btn-tambah-baris-edit').off('click').on('click', function() {
        var optionsHtml = $('#template-options-produk-edit').html();
        var newRow = `
            <tr class="baris-item">
                <td>
                    <select class="form-control form-control-sm select2-edit-produk" name="product_code[]" required style="width: 100%;">
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
                    <input type="number" class="form-control form-control-sm text-center input-qty" name="qty[]" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-center input-unit" name="unit[]" value="PCS" readonly>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right input-price" name="price[]" value="0" min="0" step="any" placeholder="0" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-right bg-light font-weight-bold input-subtotal" value="0" readonly>
                </td>
                <td class="text-center align-middle">
                    <input type="hidden" name="old_attachment[]" value="">
                    <input type="file" class="form-control-file form-control-sm" name="attachment[]" style="font-size: 9px;">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-xs btn-hapus-baris-edit" title="Hapus Baris">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        var appendedRow = $(newRow).appendTo('#tbody-produk-edit');
        initSelect2Edit(appendedRow.find('.select2-edit-produk'));
        hitungTotalBudget();
    });

    // Update Hapus Baris agar total budget ikut dikalkulasi ulang saat item dihapus
    $(document).off('click', '.btn-hapus-baris-edit').on('click', '.btn-hapus-baris-edit', function() {
        if ($('#tbody-produk-edit tr').length > 1) {
            $(this).closest('tr').remove();
            hitungTotalBudget();
        } else {
            alert('Minimal harus ada 1 item rincian produk!');
        }
    });

    // 4. Tombol Hapus Baris
    $(document).on('click', '.btn-hapus-baris-edit', function() {
        if ($('#tbody-produk-edit tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('Minimal harus ada 1 item rincian produk!');
        }
    });

    // 5. Tombol Submit Update PR via AJAX
    $(document).off('click', '#btn-update-pr').on('click', '#btn-update-pr', function(e) {
        e.preventDefault();
        
        var formData = new FormData($('#form-edit-pr')[0]);

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
                var errMsg = "Gagal menyimpan perubahan.";
                if (err.responseJSON) {
                    if (err.responseJSON.message) {
                        errMsg = err.responseJSON.message;
                    } else if (err.responseJSON.errors) {
                        var errors = err.responseJSON.errors;
                        var firstKey = Object.keys(errors)[0];
                        errMsg = errors[firstKey][0];
                    }
                } else if (err.responseText) {
                    errMsg = "Terjadi kesalahan pada server (Status: " + err.status + "). Cek console browser.";
                }
                alert(errMsg);
                console.log("Detail Error:", err);
            }
        });
    });

}); // <-- PENUTUP $(document).ready YANG SEBELUMNYA KURANG

// Fungsi Approval / Reject (Diletakkan di luar document.ready agar bisa diakses global)
function prosesApproval(statusAction) {
    var label = (statusAction === 'APPROVED') ? 'menyetujui (APPROVE)' : 'menolak (REJECT)';
    if (!confirm('Apakah Anda yakin ingin ' + label + ' Purchase Request {{ $header->PR_No }}?')) {
        return;
    }

    $.post("{{ url('purchase-request-approve') }}", {
        _token: "{{ csrf_token() }}",
        pr_no: "{{ $header->PR_No }}",
        action: statusAction,
        approval_note: $('#approval_note').val()
    }, function(res) {
        if (res.status === 'success') {
            alert(res.message);
            $('#PRDiv').html('');
            if (typeof PRGrid !== 'undefined') {
                PRGrid.clearAll();
                PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
            }
        }
    }).fail(function() {
        alert('Terjadi kesalahan saat memproses approval.');
    });
}
</script>
@endif