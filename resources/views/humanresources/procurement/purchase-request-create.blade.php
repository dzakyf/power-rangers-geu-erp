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
                        <input type="date" class="form-control form-control-sm" name="pr_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Department (Pembebanan Budget):</label>
                        <select class="form-control form-control-sm select2-search" name="department" required style="width: 100%;">
                            <option value="">-- Cari / Pilih Department --</option>
                            @if(isset($departments) && count($departments) > 0)
                                @foreach($departments as $dept)
                                    @php
                                        $isSelected = ($dept->Department_ID == $userDepartment || strtoupper($dept->Department) == strtoupper($userDepartment) || strtoupper($dept->Department_ID) == strtoupper($userDepartment)) ? 'selected' : '';
                                    @endphp
                                    <option value="{{ $dept->Department_ID }}" {{ $isSelected }}>
                                        {{ $dept->Department }} ({{ $dept->Department_ID }})
                                    </option>
                                @endforeach
                            @else
                                <option value="ICT" {{ $userDepartment == 'ICT' ? 'selected' : '' }}>IT (ICT)</option>
                                <option value="FIN" {{ $userDepartment == 'FIN' ? 'selected' : '' }}>Financial (FIN)</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- INFORMASI TAMBAHAN (PIC, SITE, ESTIMATED BUDGET) -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-2">
                        <label class="font-weight-bold">PIC (Penanggung Jawab):</label>
                        <input type="text" class="form-control form-control-sm" name="pic_name" value="{{ Auth::user()->Name ?? '' }}" placeholder="Nama PIC..." required>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">Phone PIC:</label>
                        <input type="text" class="form-control form-control-sm" name="PIC_Phone" placeholder="No. Telepon...">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Email PIC:</label>
                        <input type="email" class="form-control form-control-sm" name="pic_email" placeholder="email@domain.com...">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Site Tujuan:</label>
                        <input type="text" class="form-control form-control-sm" name="site_destination" placeholder="Contoh: Site Bekasi..." required>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold text-primary">Estimated Budget (Rp):</label>
                        <input type="text" class="form-control form-control-sm font-weight-bold text-right text-primary bg-white" id="display_estimated_budget" value="0" readonly>
                        <input type="hidden" id="estimated_budget" name="estimated_budget" value="0">
                    </div>
                </div>

                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-6">
                        <label class="font-weight-bold">Kategori Anggaran (Main Category):</label>
                        <select class="form-control form-control-sm" name="main_category" required>
                            <option value="ASSET">ASSET</option>
                            <option value="NON ASSET">NON ASSET</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">Sub Kategori (Category):</label>
                        <select class="form-control form-control-sm" name="category" required>
                            <option value="BARANG">BARANG</option>
                            <option value="JASA">JASA</option>
                        </select>
                    </div>
                </div>

                <!-- BAGIAN NOTE -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-md-12">
                        <label class="font-weight-bold">Catatan / Note Baru:</label>
                        <textarea class="form-control form-control-sm" name="note" rows="3" placeholder="Tulis catatan atau instruksi tambahan untuk PR ini..."></textarea>
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
    </script>
@endsection