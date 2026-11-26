@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('dashboard1') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('purchase-request-list') }}">Purchase Request (PR)</a></li>
            <li class="breadcrumb-item active"><span>Create Purchase Request</span></li>
        </ol>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="font-size: 12px;">
            <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Gagal:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-success text-white py-2">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-plus-circle mr-2"></i>Form Pembuatan Purchase Request (PR) Baru
            </h6>
        </div>
        <div class="card-body bg-light">
            <form action="{{ url('purchase-request-store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- HEADER INFORMASI PR -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-4">
                        <label class="font-weight-bold">Nomor PR:</label>
                        <input type="text" class="form-control form-control-sm font-weight-bold text-primary" name="pr_no" value="{{ $generated_pr_no }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Tanggal PR:</label>
                        <input type="date" class="form-control form-control-sm" name="pr_date" value="{{ old('pr_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Department (Pembebanan Budget):</label>
                        <select class="form-control form-control-sm select2-search" name="department" required style="width: 100%;">
                            <option value="">-- Cari / Pilih Department --</option>
                            @if(isset($departments) && count($departments) > 0)
                                @foreach($departments as $dept)
                                    @php
                                        $selectedDept = old('department', $userDepartment);
                                        $isSelected = ($dept->Department_ID == $selectedDept || strtoupper($dept->Department) == strtoupper($selectedDept) || strtoupper($dept->Department_ID) == strtoupper($selectedDept)) ? 'selected' : '';
                                    @endphp
                                    <option value="{{ $dept->Department_ID }}" {{ $isSelected }}>
                                        {{ $dept->Department }} ({{ $dept->Department_ID }})
                                    </option>
                                @endforeach
                            @else
                                <option value="ICT" {{ old('department', $userDepartment) == 'ICT' ? 'selected' : '' }}>IT (ICT)</option>
                                <option value="FIN" {{ old('department', $userDepartment) == 'FIN' ? 'selected' : '' }}>Financial (FIN)</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- KATEGORI SK APPROVAL & PENGADAAN -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-3">
                        <label class="font-weight-bold text-danger">Jenis Pengadaan (SK Approval): *</label>
                        <select class="form-control form-control-sm border-danger font-weight-bold" name="supplies_type" required>
                            <option value="OPS" {{ old('supplies_type') == 'OPS' ? 'selected' : '' }}>A. GA - Procurement OPS</option>
                            <option value="NON-OPS" {{ old('supplies_type', 'NON-OPS') == 'NON-OPS' ? 'selected' : '' }}>B. Procurement Non-OPS</option>
                            <option value="NON-OPS-CNS" {{ old('supplies_type') == 'NON-OPS-CNS' ? 'selected' : '' }}>C. Procurement Non-OPS - CNS</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Kategori Anggaran (Main Category):</label>
                        <select class="form-control form-control-sm" name="main_category" required>
                            <option value="NON ASSET" {{ old('main_category') == 'NON ASSET' ? 'selected' : '' }}>NON ASSET</option>
                            <option value="ASSET" {{ old('main_category') == 'ASSET' ? 'selected' : '' }}>ASSET (Pengadaan Aset Baru)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Kategori Pengadaan:</label>
                        <select class="form-control form-control-sm" name="procurement_category" required>
                            @foreach(['Rutin', 'Pengadaan Aset Baru', 'Pemeliharaan Insidentil', 'Project', 'ETC'] as $pCat)
                                <option value="{{ $pCat }}" {{ old('procurement_category', 'Rutin') == $pCat ? 'selected' : '' }}>{{ $pCat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Sub Kategori (Category):</label>
                        <select class="form-control form-control-sm" name="category" required>
                            <option value="BARANG" {{ old('category') == 'BARANG' ? 'selected' : '' }}>BARANG</option>
                            <option value="JASA" {{ old('category') == 'JASA' ? 'selected' : '' }}>JASA</option>
                        </select>
                    </div>
                </div>

                
                <!-- URGENSI, HOPE DATE, & COST CENTER -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-4">
                        <label class="font-weight-bold">Tingkat Urgensi PR:</label>
                        <select class="form-control form-control-sm" id="urgency" name="urgency" required>
                            @foreach(['Normal', 'Emergency', 'Urgent', 'Planned', 'Project'] as $urg)
                                <option value="{{ $urg }}" {{ old('urgency', 'Normal') == $urg ? 'selected' : '' }}>{{ $urg }}</option>
                            @endforeach
                        </select>
                        <small id="leadtime_info" class="text-muted d-block mt-1"></small>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Hope Date (Target Kedatangan):</label>
                        <input type="date" class="form-control form-control-sm" id="hope_date" name="hope_date" value="{{ old('hope_date', date('Y-m-d', strtotime('+7 days'))) }}">
                        <small id="leadtime_calc_note" class="text-info d-block mt-1"></small>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Cost Center:</label>
                        <input type="text" class="form-control form-control-sm" name="cost_center" value="{{ old('cost_center') }}" placeholder="Contoh: CC-HO-01 / Project Site...">
                    </div>
                </div>

                <!-- INFORMASI TAMBAHAN (PIC, SITE, ESTIMATED BUDGET) -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-2">
                        <label class="font-weight-bold">PIC (Penanggung Jawab):</label>
                        <input type="text" class="form-control form-control-sm" name="pic_name" value="{{ old('pic_name', Auth::user()->Name ?? '') }}" placeholder="Nama PIC..." required>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">Phone PIC:</label>
                        <input type="text" class="form-control form-control-sm" name="PIC_Phone" value="{{ old('PIC_Phone') }}" placeholder="No. Telepon...">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Email PIC:</label>
                        <input type="email" class="form-control form-control-sm" name="pic_email" value="{{ old('pic_email') }}" placeholder="email@domain.com...">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Site Tujuan:</label>
                        <input type="text" class="form-control form-control-sm" name="site_destination" value="{{ old('site_destination') }}" placeholder="Contoh: Site Bekasi..." required>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold text-primary">Estimated Budget (Rp):</label>
                        <input type="text" class="form-control form-control-sm font-weight-bold text-right text-primary bg-white" id="display_estimated_budget" value="0" readonly>
                        <input type="hidden" id="estimated_budget" name="estimated_budget" value="0">
                    </div>
                </div>

                <!-- BAGIAN NOTE -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-12">
                        <label class="font-weight-bold">Catatan / Note Baru:</label>
                        <textarea class="form-control form-control-sm" name="note" rows="3" placeholder="Tulis catatan atau instruksi tambahan untuk PR ini...">{{ old('note') }}</textarea>
                    </div>
                </div>

                <hr class="my-3">

                <!-- HEADER TABEL MULTI PRODUK -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-success font-weight-bold mb-0" style="font-size: 13px;">
                        <i class="fas fa-boxes mr-1"></i> Daftar Rincian Material / Multi Produk
                    </h6>
                    <button type="button" id="btn-tambah-baris" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Item Produk
                    </button>
                </div>

                <!-- TABEL RINCIAN ITEM MULTI PRODUK -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm bg-white" id="tabel-multi-produk" style="font-size: 12px;">
                        <thead class="thead-light text-center">
                            <tr>
                                <th style="width: 20%;">Product Code</th>
                                <th style="width: 17%;">Nama Item / Deskripsi</th>
                                <th style="width: 14%;">Spesifikasi (Spec)</th>
                                <th style="width: 6%;">Qty</th>
                                <th style="width: 7%;">Satuan</th>
                                <th style="width: 11%;">Est. Price (Rp)</th>
                                <th style="width: 11%;">Subtotal (Rp)</th>
                                <th style="width: 5%;">Foto</th>
                                <th style="width: 6%;">Lampiran</th>
                                <th style="width: 3%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-produk">
                            <!-- BARIS PERTAMA (DEFAULT) -->
                            <tr class="baris-item">
                                <td>
                                    <select class="form-control form-control-sm select2-produk" name="product_code[]" required style="width: 100%;">
                                        <option value="">-- Cari Product Code --</option>
                                        @foreach($materials as $mat)
                                            <option value="{{ $mat->Product_Code }}">{{ $mat->Product_Code }} - {{ $mat->Description }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm input-desc" name="product_desc[]" placeholder="Otomatis..." readonly required>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm input-spec" name="specification[]" placeholder="Otomatis / isi manual...">
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
                                <td class="text-center align-middle box-foto">
                                    <span class="text-muted" style="font-size: 10px;">-</span>
                                </td>
                                <td class="align-middle">
                                    <input type="file" class="form-control-file form-control-sm" name="attachment[]" style="font-size: 9px;">
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-danger btn-xs btn-hapus-baris" title="Hapus Baris" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TOMBOL AKSI -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold px-4 py-2 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Purchase Request
                    </button>
                    <a href="{{ url('purchase-request-list') }}" class="btn btn-secondary btn-sm font-weight-bold px-3 py-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar PR
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TEMPLATE OPTION PRODUCT CODE TERSEMBUNYI UNTUK CLONING BARIS BARU -->
    <template id="template-options-produk">
        <option value="">-- Cari Product Code --</option>
        @foreach($materials as $mat)
            <option value="{{ $mat->Product_Code }}">{{ $mat->Product_Code }} - {{ $mat->Description }}</option>
        @endforeach
    </template>

    <script>
    $(document).ready(function() {
        // 1. Inisialisasi Select2
        function initSelect2(element) {
            if ($.fn.select2) {
                $(element).select2({
                    width: '100%',
                    placeholder: '-- Ketik untuk mencari --',
                    allowClear: true
                });
            }
        }

        initSelect2('.select2-search');
        initSelect2('.select2-produk');

        // 2. Fungsi Hitung Otomatis Qty x Price -> Estimated Budget
        function hitungTotalBudgetCreate() {
            var grandTotal = 0;

            $('#tbody-produk tr.baris-item').each(function() {
                var qty   = parseFloat($(this).find('.input-qty').val()) || 0;
                var price = parseFloat($(this).find('.input-price').val()) || 0;
                var sub   = qty * price;

                $(this).find('.input-subtotal').val(sub.toLocaleString('id-ID'));
                grandTotal += sub;
            });

            $('#estimated_budget').val(grandTotal);
            $('#display_estimated_budget').val(grandTotal.toLocaleString('id-ID'));
        }

        // Trigger kalkulasi saat Qty atau Price diubah
        $(document).on('input change keyup', '.input-qty, .input-price', function() {
            hitungTotalBudgetCreate();
        });

        // 3. Event Change pada Product Code
        $(document).on('change', '.select2-produk', function() {
            var selectEl = $(this);
            var kode     = selectEl.val();
            var row      = selectEl.closest('tr');

            var inputDesc = row.find('.input-desc');
            var inputSpec = row.find('.input-spec');
            var inputUnit = row.find('.input-unit');
            var boxFoto   = row.find('.box-foto');

            if (!kode) {
                inputDesc.val('');
                inputSpec.val('');
                inputUnit.val('PCS');
                boxFoto.html('<span class="text-muted" style="font-size: 10px;">-</span>');
                return;
            }

            $.get("{{ url('get-material-detail') }}", { code: kode }, function(res) {
                if (res.status === 'success') {
                    inputDesc.val(res.nama);
                    inputSpec.val(res.spec);
                    inputUnit.val(res.uom);

                    if (res.image) {
                        boxFoto.html('<a href="' + res.image + '" target="_blank" class="btn btn-info text-white font-weight-bold shadow-sm" style="padding: 1px 6px; font-size: 10px; line-height: 1.4; border-radius: 3px;" title="Lihat Foto Produk"><i class="fas fa-eye"></i></a>');
                    } else {
                        boxFoto.html('<span class="text-muted" style="font-size: 10px;">-</span>');
                    }
                }
            });
        });

        // 4. Tombol Tambah Baris Produk Baru
        $('#btn-tambah-baris').on('click', function() {
            var optionsHtml = $('#template-options-produk').html();
            var newRow = `
                <tr class="baris-item">
                    <td>
                        <select class="form-control form-control-sm select2-produk" name="product_code[]" required style="width: 100%;">
                            ${optionsHtml}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm input-desc" name="product_desc[]" placeholder="Otomatis..." readonly required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm input-spec" name="specification[]" placeholder="Otomatis / isi manual...">
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
                    <td class="text-center align-middle box-foto">
                        <span class="text-muted" style="font-size: 10px;">-</span>
                    </td>
                    <td class="align-middle">
                        <input type="file" class="form-control-file form-control-sm" name="attachment[]" style="font-size: 9px;">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-xs btn-hapus-baris" title="Hapus Baris">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            var appendedRow = $(newRow).appendTo('#tbody-produk');
            initSelect2(appendedRow.find('.select2-produk'));
            hitungTotalBudgetCreate();
        });

        // 5. Tombol Hapus Baris Produk
        $(document).on('click', '.btn-hapus-baris', function() {
            if ($('#tbody-produk tr').length > 1) {
                $(this).closest('tr').remove();
                hitungTotalBudgetCreate();
            }
        });
    });


function addBusinessDays(dateStr, daysToAdd) {
    if (!dateStr) return '';
    var parts = dateStr.split('-'); // YYYY-MM-DD
    if (parts.length !== 3) return '';

    var date = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    var remaining = parseInt(daysToAdd, 10) || 0;
    var step = remaining >= 0 ? 1 : -1;
    remaining = Math.abs(remaining);

    while (remaining > 0) {
        date.setDate(date.getDate() + step);
        var dayOfWeek = date.getDay(); // 0 = Minggu, 6 = Sabtu
        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
            remaining--;
        }
    }

    var yyyy = date.getFullYear();
    var mm   = String(date.getMonth() + 1).padStart(2, '0');
    var dd   = String(date.getDate()).padStart(2, '0');
    return yyyy + '-' + mm + '-' + dd;
}

function countBusinessDaysBetween(startStr, endStr) {
    if (!startStr || !endStr) return 0;
    var sParts = startStr.split('-');
    var eParts = endStr.split('-');
    var start = new Date(sParts[0], sParts[1] - 1, sParts[2]);
    var end   = new Date(eParts[0], eParts[1] - 1, eParts[2]);
    if (end <= start) return 0;

    var cur = new Date(start.getTime());
    var count = 0;
    while (cur < end) {
        cur.setDate(cur.getDate() + 1);
        var d = cur.getDay();
        if (d !== 0 && d !== 6) count++;
    }
    return count;
}

function syncLeadTimeWithUrgency(autoSetHopeDate) {
    var prDate  = $('input[name="pr_date"]').val();
    var urgency = $('#urgency').val();

    // Mapping aturan lead time (hari kerja, tanpa Sabtu & Minggu)
    var ruleMap = {
        'Emergency': { days: 1, label: 'Lead time ≤ 1 hari kerja (Kritis / Darurat)', mode: 'max' },
        'Urgent':    { days: 3, label: 'Lead time ≤ 3 hari kerja (Mendesak)', mode: 'max' },
        'Normal':    { days: 5, label: 'Lead time ≤ 5 hari kerja (Pengadaan rutin)', mode: 'max' },
        'Planned':   { days: 7, label: 'Lead time ≥ 7 hari kerja (Terencana)', mode: 'min' },
        'Project':   { days: 7, label: 'Sesuai jadwal pelaksanaan proyek', mode: 'free' }
    };

    var rule = ruleMap[urgency] || ruleMap['Normal'];
    $('#leadtime_info').text(rule.label);

    if (prDate && autoSetHopeDate && rule.mode !== 'free') {
        var targetDate = addBusinessDays(prDate, rule.days);
        $('#hope_date').val(targetDate);
    }

    var hopeVal = $('#hope_date').val();
    if (prDate && hopeVal) {
        var bizDays = countBusinessDaysBetween(prDate, hopeVal);
        $('#leadtime_calc_note').text('Estimasi durasi dari Tanggal PR: ' + bizDays + ' hari kerja (Sabtu & Minggu tidak dihitung)');
    }
}

$(document).ready(function() {
    // Saat pertama kali buka form, sinkronkan info tanpa menimpa old('hope_date') jika ada
    syncLeadTimeWithUrgency(false);

    // Saat Urgensi atau Tanggal PR berubah, otomatis hitung ulang Hope Date sesuai hari kerja
    $('#urgency, input[name="pr_date"]').on('change', function() {
        syncLeadTimeWithUrgency(true);
    });

    // Saat Hope Date diubah manual, perbarui informasi jumlah hari kerja
    $('#hope_date').on('change', function() {
        syncLeadTimeWithUrgency(false);
    });
});

function validateLeadTime(showAlert) {
    var prDate  = $('input[name="pr_date"]').val();
    var hopeVal = $('#hope_date').val();
    var urgency = $('#urgency').val();

    if (!prDate || !hopeVal) {
        return true;
    }

    // Jika Hope Date lebih awal dari Tanggal PR
    if (hopeVal < prDate) {
        var msgBefore = 'Hope Date tidak boleh lebih awal dari Tanggal PR (' + prDate + ').';
        $('#leadtime_calc_note').removeClass('text-info').addClass('text-danger').text(msgBefore);
        if (showAlert) alert(msgBefore);
        return false;
    }

    var bizDays = countBusinessDaysBetween(prDate, hopeVal);
    var isValid = true;
    var msg = 'Estimasi durasi dari Tanggal PR: ' + bizDays + ' hari kerja (Sabtu & Minggu tidak dihitung)';
    var errorMsg = '';

    if (urgency === 'Emergency' && bizDays > 1) {
        isValid = false;
        errorMsg = 'Untuk urgensi Emergency, lead time maksimal 1 hari kerja (saat ini: ' + bizDays + ' hari kerja).';
    } else if (urgency === 'Urgent' && bizDays > 3) {
        isValid = false;
        errorMsg = 'Untuk urgensi Urgent, lead time maksimal 3 hari kerja (saat ini: ' + bizDays + ' hari kerja).';
    } else if (urgency === 'Normal' && bizDays > 5) {
        isValid = false;
        errorMsg = 'Untuk urgensi Normal, lead time maksimal 5 hari kerja (saat ini: ' + bizDays + ' hari kerja).';
    } else if (urgency === 'Planned' && bizDays < 7) {
        isValid = false;
        errorMsg = 'Untuk urgensi Planned, lead time minimal 7 hari kerja (saat ini: ' + bizDays + ' hari kerja).';
    }

    if (!isValid) {
        $('#leadtime_calc_note').removeClass('text-info').addClass('text-danger').text(errorMsg);
        if (showAlert) {
            alert(errorMsg);
        }
        return false;
    }

    $('#leadtime_calc_note').removeClass('text-danger').addClass('text-info').text(msg);
    return true;
}

    </script>
@endsection