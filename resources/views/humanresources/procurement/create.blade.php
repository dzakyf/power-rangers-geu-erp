<div class="mt-3 animate__animated animate__fadeIn">
    <div class="card shadow-sm border-0 mb-4">
        <!-- Card Header -->
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary text-white p-2 me-2 rounded-circle">
                    <i class="fa fa-file-text-o fa-lg"></i>
                </span>
                <div>
                    <h5 class="card-title mb-0 font-weight-bold text-dark">Form Pengajuan Purchase Request (PR)</h5>
                    <small class="text-muted">Buat permohonan pengadaan barang atau jasa baru</small>
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="$('#PurchaseManagementDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>

        <div class="card-body p-4">
            <div id="ContentPages">
                <!-- Section 1: Dokumen & User Pemohon -->
                <div class="p-3 mb-3 bg-light rounded-3 border">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-id-card-o me-2 text-primary"></i>Informasi Dokumen &amp; Pemohon</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Nomor PR</label>
                            <input type="text" id="PR_No" class="form-control form-control-sm bg-white font-weight-bold text-primary" readonly value="<?php echo $PR_No; ?>" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Tanggal Pengajuan</label>
                            <input type="text" id="PR_Date" class="form-control form-control-sm bg-white" readonly value="<?php echo date('Y-m-d'); ?>" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold small text-muted">User Pemohon (NIK &amp; Nama)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-white font-weight-semibold" style="max-width:140px;" id="User_Request" value="<?php echo $NIK; ?>" readonly />
                                <input type="text" class="form-control bg-white font-weight-semibold" id="User_Request_Name" value="<?php echo $Saya; ?>" readonly />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Budgeting, Vendor, & Parameter -->
                <div class="p-3 mb-3 bg-light rounded-3 border">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-building-o me-2 text-primary"></i>Budgeting, Vendor &amp; Klasifikasi</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold small text-muted">Departemen (Pembebanan Budget)</label>
                            <select id="Department" class="form-control form-control-sm">
                                <?php foreach ($Department as $Data): ?>
                                    <option value="<?php echo $Data->Initial; ?>" <?php echo ($dpt == $Data->Initial) ? 'selected' : ''; ?>>
                                        <?php echo $Data->Department . ' - ' . $Data->Initial_Code . ' - ' . $Data->Initial; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold small text-muted">Pilihan Vendor / Supplier Rekanan</label>
                            <select name="Supplier_ID" id="Supplier_ID" class="form-control form-control-sm">
                                <option value="">-- Pilih Vendor Rekanan --</option>
                                <?php foreach ($company as $item): ?>
                                    <option value="<?php echo $item->Supplier_ID; ?>">
                                        <?php echo $item->Supplier_ID . ' - ' . $item->Company_Name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Kategori</label>
                            <select id="Category" class="form-control form-control-sm">
                                <option value="">Pilih Kategori</option>
                                <option>ASSET</option>
                                <option selected>NON ASSET</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Jenis Pengadaan</label>
                            <select id="Sub_Category" class="form-control form-control-sm">
                                <option value="">Pilih Jenis</option>
                                <option selected>BARANG</option>
                                <option>JASA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Tingkat Prioritas</label>
                            <select name="Priority_Status" id="Priority_Status" class="form-control form-control-sm">
                                <option>NORMAL</option>
                                <option>URGENT</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Total Estimasi Budget (Rp)</label>
                            <input type="number" id="Estimated_Budget" class="form-control form-control-sm font-weight-bold text-success" value="0" min="0" required />
                        </div>
                    </div>
                </div>

                <!-- Section 3: PIC & Pengiriman -->
                <div class="p-3 mb-3 bg-light rounded-3 border">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-map-marker me-2 text-primary"></i>PIC Penerima &amp; Lokasi Pengiriman</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold small text-muted">Nama PIC Penerima</label>
                            <input type="text" id="PIC" class="form-control form-control-sm" required placeholder="Contoh: Budi Santoso" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold small text-muted">Nomor Telepon PIC</label>
                            <input type="text" id="PIC_Phone" class="form-control form-control-sm" required placeholder="08xxxxxxxxxx" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold small text-muted">Alamat Pengiriman</label>
                            <input type="text" id="PIC_Address" class="form-control form-control-sm" required placeholder="Alamat lengkap tujuan kirim" />
                        </div>
                    </div>
                </div>

                <!-- Section 4: Tabel Rincian Produk -->
                <div class="p-3 mb-3 bg-white rounded-3 border">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-cubes me-2 text-primary"></i>Daftar Rincian Produk / Jasa</h6>
                            <small class="text-muted">Isi nama item, spesifikasi detail, satuan, serta estimasi harga</small>
                        </div>
                        <button type="button" id="addProductRow" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-plus me-1"></i> Tambah Item Baris
                        </button>
                    </div>

                    <div class="table-responsive border rounded-3 overflow-hidden shadow-xs mt-3">
                        <table class="table table-bordered table-hover align-middle mb-0" id="productTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:40px;">No</th>
                                    <th style="width:220px;">Deskripsi Item <span class="text-danger">*</span></th>
                                    <th class="text-center" style="width:90px;">Qty <span class="text-danger">*</span></th>
                                    <th style="width:250px;">Spesifikasi Lengkap</th>
                                    <th class="text-center" style="width:90px;">Satuan</th>
                                    <th style="width:130px;">Estimasi Harga Satuan</th>
                                    <th style="width:140px;">Subtotal (Rp)</th>
                                    <th style="width:140px;">Referensi / Keterangan</th>
                                    <th style="width:140px;">Lampiran File</th>
                                    <th class="text-center" style="width:50px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <tr class="product-row">
                                    <td class="text-center text-muted">1</td>
                                    <td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Nama / deskripsi barang" required /></td>
                                    <td><input type="number" name="Qty[]" class="form-control form-control-sm qty text-center" value="1" min="1" /></td>
                                    <td><textarea name="Specification[]" class="form-control form-control-sm specification" rows="1" placeholder="Merk, spek, dimensi..."></textarea></td>
                                    <td><input type="text" name="Unit[]" class="form-control form-control-sm unit text-center" value="PCS" /></td>
                                    <td><input type="number" name="Price[]" class="form-control form-control-sm price text-end" value="0" min="0" step="1000" /></td>
                                    <td><input type="text" name="Total[]" class="form-control form-control-sm total text-end bg-light font-weight-bold" value="0" readonly /></td>
                                    <td><input type="text" name="Reference[]" class="form-control form-control-sm reference" placeholder="Link / Info" /></td>
                                    <td><input type="file" name="Image[]" class="form-control form-control-sm image" accept="image/*,.pdf" /></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row p-1" title="Hapus baris">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Cards -->
                    <div class="d-flex flex-wrap justify-content-end align-items-center gap-3 mt-3 pt-2">
                        <div class="px-3 py-2 bg-light rounded border text-muted small">
                            Total Item: <strong id="totalProducts" class="text-dark">1</strong> Produk
                        </div>
                        <div class="px-3 py-2 bg-light rounded border text-muted small">
                            Total Estimasi: <strong id="totalEstimate" class="text-success font-weight-bold h6 mb-0 ms-1">Rp 0</strong>
                        </div>
                    </div>
                </div>

                <!-- Action Footer Buttons -->
                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="$('#PurchaseManagementDiv').html('')">
                        <i class="fa fa-arrow-left me-1"></i> Batal &amp; Kembali
                    </button>
                    <button type="button" id="SubmitPRDetail" class="btn btn-success px-4 text-white shadow-xs">
                        <i class="fa fa-check-circle me-1"></i> Simpan &amp; Ajukan Purchase Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if($.fn.select2) {
            $('#Supplier_ID').select2({ width: '100%' });
            $('#Department').select2({ width: '100%' });
        }
        
        var productCount = 1;

        function calculateTotal(row) {
            var qty = parseFloat(row.find('.qty').val()) || 0;
            var price = parseFloat(row.find('.price').val()) || 0;
            var sub = qty * price;
            row.find('.total').val(sub);
            updateTotalEstimate();
        }

        $(document).on('input change', '#productTableBody .qty, #productTableBody .price', function() {
            calculateTotal($(this).closest('.product-row'));
        });

        function updateTotalEstimate() {
            var total = 0;
            $('#productTableBody .total').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalEstimate').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
            $('#Estimated_Budget').val(total);
        }

        $('#addProductRow').click(function() {
            productCount++;
            var newRow = `
                <tr class="product-row">
                    <td class="text-center text-muted">${productCount}</td>
                    <td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Nama / deskripsi barang" required /></td>
                    <td><input type="number" name="Qty[]" class="form-control form-control-sm qty text-center" value="1" min="1" /></td>
                    <td><textarea name="Specification[]" class="form-control form-control-sm specification" rows="1" placeholder="Merk, spek, dimensi..."></textarea></td>
                    <td><input type="text" name="Unit[]" class="form-control form-control-sm unit text-center" value="PCS" /></td>
                    <td><input type="number" name="Price[]" class="form-control form-control-sm price text-end" value="0" min="0" step="1000" /></td>
                    <td><input type="text" name="Total[]" class="form-control form-control-sm total text-end bg-light font-weight-bold" value="0" readonly /></td>
                    <td><input type="text" name="Reference[]" class="form-control form-control-sm reference" placeholder="Link / Info" /></td>
                    <td><input type="file" name="Image[]" class="form-control form-control-sm image" accept="image/*,.pdf" /></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row p-1" title="Hapus baris">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#productTableBody').append(newRow);
            $('#totalProducts').text($('#productTableBody tr.product-row').length);
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#productTableBody tr.product-row').length > 1) {
                $(this).closest('tr').remove();
                $('#productTableBody tr.product-row').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
                $('#totalProducts').text($('#productTableBody tr.product-row').length);
                updateTotalEstimate();
            } else {
                alert('Minimal harus ada 1 item produk.');
            }
        });

        $('#SubmitPRDetail').click(function() {
            var isValid = true;
            var emptyFields = [];

            if (!$('#PIC').val()) { isValid = false; emptyFields.push('PIC Penerima'); }
            if (!$('#PIC_Phone').val()) { isValid = false; emptyFields.push('Telepon PIC'); }
            if (!$('#PIC_Address').val()) { isValid = false; emptyFields.push('Alamat Pengiriman'); }
            if (!$('#Department').val()) { isValid = false; emptyFields.push('Departemen Budgeting'); }

            $('#productTableBody tr.product-row').each(function(idx) {
                var desc = $(this).find('.product-desc').val();
                if (!desc || desc.trim() === '') {
                    isValid = false;
                    emptyFields.push('Deskripsi Produk (baris ' + (idx + 1) + ')');
                }
            });

            if (!isValid) {
                alert('Mohon lengkapi field wajib berikut:\n- ' + emptyFields.join('\n- '));
                return;
            }

            $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Mengirim Pengajuan Purchase Request...</h4>' });

            var formData = new FormData();
            var allInputs = getAllValues();
            for (var key in allInputs) {
                formData.append(key, allInputs[key]);
            }

            var gridData = [];
            var fileIndex = 0;

            $('#productTableBody tr.product-row').each(function(idx) {
                var fileInput = $(this).find('.image')[0];
                var fileKey = '';
                
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    fileKey = 'file_' + fileIndex;
                    formData.append(fileKey, fileInput.files[0]);
                    fileIndex++;
                }
                
                gridData.push({
                    Product_Code: $(this).find('.product-code').val() || '',
                    Product_Desc: $(this).find('.product-desc').val(),
                    Qty: $(this).find('.qty').val(),
                    Specification: $(this).find('.specification').val(),
                    Unit: $(this).find('.unit').val(),
                    Price: $(this).find('.price').val(),
                    Total: $(this).find('.total').val(),
                    Reference: $(this).find('.reference').val(),
                    Category: $('#Category').val() || '',
                    Image: fileKey
                });
            });

            formData.append('GridData', JSON.stringify(gridData));
            formData.append('RowCount', gridData.length);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: "hrd-procurement-new-submit",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $.unblockUI();
                    if (data == 'success') {
                        alert('Purchase Request berhasil diajukan! (' + gridData.length + ' item)');
                        refreshData();
                        $('#PurchaseManagementDiv').html('');
                    } else {
                        alert('Gagal: ' + data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $.unblockUI();
                    alert('Terjadi kesalahan sistem: ' + errorThrown);
                }
            });
        });
    });

    function getAllValues() {
        var inputValues = {};
        $('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
            var type = $(this).attr('type');
            var id = $(this).attr('id');
            var value = $(this).val();
            if ((type == 'checkbox' || type == 'radio')) {
                if (this.checked) {
                    inputValues[id] = value;
                }
            } else if (type != 'button' && type != 'submit') {
                inputValues[id] = value;
            }
        });
        return inputValues;
    }
    $.unblockUI();
</script>
