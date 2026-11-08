<div class="mt-3 animate__animated animate__fadeIn">
    <div class="card shadow-sm border-0 mb-4">
        <!-- Header -->
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-warning text-dark p-2 me-2 rounded-circle">
                    <i class="fa fa-check-square-o fa-lg"></i>
                </span>
                <div>
                    <h5 class="card-title mb-0 font-weight-bold text-dark">Form Evaluasi &amp; Persetujuan PR</h5>
                    <small class="text-muted">Nomor Dokumen: <strong class="text-primary">{{ $pr->PR_No }}</strong>
                        &bull; Status: <span
                            class="badge bg-warning text-dark px-2 py-0.5 rounded-pill">{{ $pr->Status }}</span></small>
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="$('#PurchaseManagementDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>

        <div class="card-body p-4">
            <div id="ContentPages">
                <input type="hidden" id="ID" name="ID" value="{{ $pr->ID }}" />

                <!-- Section 1: Dokumen & User Pemohon -->
                <div class="p-3 mb-3 bg-light rounded-3 border">
                    <h6 class="font-weight-bold text-dark mb-3"><i
                            class="fa fa-id-card-o me-2 text-primary"></i>Informasi Dokumen &amp; Pemohon</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Nomor PR</label>
                            <input type="text" id="PR_No" name="PR_No"
                                class="form-control form-control-sm bg-white font-weight-bold text-primary" readonly
                                value="{{ $pr->PR_No }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Tanggal Pengajuan</label>
                            <input type="text" id="PR_Date" name="PR_Date"
                                class="form-control form-control-sm bg-white" readonly
                                value="{{ date('Y-m-d', strtotime($pr->PR_Date)) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold small text-muted">User Pemohon (NIK &amp;
                                Nama)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-white font-weight-semibold"
                                    style="max-width:140px;" id="User_Request" name="User_Request"
                                    value="{{ $pr->User_Request }}" readonly />
                                <input type="text" class="form-control bg-white font-weight-semibold"
                                    id="User_Request_Name" name="User_Request_Name" value="{{ $pr->User_Request_Name }}"
                                    readonly />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Budgeting, Vendor, & Parameter -->
                <div class="p-3 mb-3 bg-light rounded-3 border">
                    <h6 class="font-weight-bold text-dark mb-3"><i
                            class="fa fa-building-o me-2 text-primary"></i>Budgeting, Vendor &amp; Klasifikasi</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold small text-muted">Departemen (Pembebanan
                                Budget)</label>
                            <select id="Department" name="Department" class="form-control form-control-sm">
                                @foreach ($Department as $Data)
                                    <option value="{{ $Data->Initial }}"
                                        {{ $pr->Department == $Data->Initial ? 'selected' : '' }}>
                                        {{ $Data->Department }} - {{ $Data->Initial_Code }} - {{ $Data->Initial }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold small text-muted">Pilihan Vendor / Supplier
                                Rekanan</label>
                            <select name="Supplier_ID" id="Supplier_ID" class="form-control form-control-sm">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach ($company as $item)
                                    <option value="{{ $item->Supplier_ID }}"
                                        {{ $pr->Supplier_ID == $item->Supplier_ID ? 'selected' : '' }}>
                                        {{ $item->Supplier_ID }} - {{ $item->Company_Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Kategori</label>
                            <select id="Category" name="Category" class="form-control form-control-sm">
                                <option value="">Pilih Kategori</option>
                                <option {{ $pr->Category == 'ASSET' ? 'selected' : '' }}>ASSET</option>
                                <option {{ $pr->Category == 'NON ASSET' ? 'selected' : '' }}>NON ASSET</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Jenis Pengadaan</label>
                            <select id="Sub_Category" name="Sub_Category" class="form-control form-control-sm">
                                <option value="">Pilih Jenis</option>
                                <option {{ $pr->Sub_Category == 'BARANG' ? 'selected' : '' }}>BARANG</option>
                                <option {{ $pr->Sub_Category == 'JASA' ? 'selected' : '' }}>JASA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Tingkat Prioritas</label>
                            <select name="Priority_Status" id="Priority_Status" class="form-control form-control-sm">
                                <option {{ $pr->Priority_Status == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                                <option {{ $pr->Priority_Status == 'URGENT' ? 'selected' : '' }}>URGENT</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold small text-muted">Total Estimasi Budget
                                (Rp)</label>
                            <input type="number" id="Estimated_Budget" name="Estimated_Budget"
                                class="form-control form-control-sm font-weight-bold text-success"
                                value="{{ (float) $pr->Estimated_Budget }}" min="0" required />
                        </div>
                    </div>
                </div>

                <!-- Section 3: PIC & Pengiriman -->
                <div class="p-3 mb-3 bg-light rounded-3 border">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-map-marker me-2 text-primary"></i>PIC
                        Penerima &amp; Lokasi Pengiriman</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold small text-muted">Nama PIC Penerima</label>
                            <input type="text" id="PIC" name="PIC" class="form-control form-control-sm"
                                required placeholder="Nama penerima" value="{{ $pr->PIC }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold small text-muted">Nomor Telepon PIC</label>
                            <input type="text" id="PIC_Phone" name="PIC_Phone"
                                class="form-control form-control-sm" required placeholder="Nomor telepon"
                                value="{{ $pr->PIC_Phone }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold small text-muted">Alamat Pengiriman</label>
                            <input type="text" id="PIC_Address" name="PIC_Address"
                                class="form-control form-control-sm" required placeholder="Alamat lengkap"
                                value="{{ $pr->PIC_Address }}" />
                        </div>
                    </div>
                </div>

                <!-- Section 4: Catatan & Approval Remark -->
                <div class="p-3 mb-3 bg-light rounded-3 border">
                    <label for="Remark" class="form-label font-weight-semibold text-dark"><i
                            class="fa fa-commenting-o me-2 text-primary"></i>Catatan Approver / Review Remark</label>
                    <textarea id="Remark" name="Remark" class="form-control form-control-sm" rows="3"
                        placeholder="Tuliskan catatan alasan persetujuan, penyesuaian data, atau penolakan..."></textarea>
                    @if (!empty($pr->Note))
                        <div class="alert alert-secondary py-2 px-3 mt-2 mb-0 small">
                            <strong><i class="fa fa-history me-1"></i> Riwayat Catatan Sebelumnya:</strong><br>
                            {!! nl2br(e($pr->Note)) !!}
                        </div>
                    @endif
                </div>

                <!-- Section 5: Tabel Rincian Produk -->
                <div class="p-3 mb-3 bg-white rounded-3 border">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="font-weight-bold text-dark mb-0"><i
                                    class="fa fa-cubes me-2 text-primary"></i>Verifikasi Daftar Rincian Barang / Jasa
                            </h6>
                            <small class="text-muted">Approver dapat menyesuaikan kuantitas atau estimasi harga sebelum
                                memberikan persetujuan</small>
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
                                    <th class="text-center" style="width:90px;">Qty <span
                                            class="text-danger">*</span></th>
                                    <th style="width:250px;">Spesifikasi Lengkap</th>
                                    <th class="text-center" style="width:90px;">Satuan</th>
                                    <th style="width:130px;">Harga Satuan</th>
                                    <th style="width:140px;">Subtotal (Rp)</th>
                                    <th style="width:140px;">Referensi / Ket</th>
                                    <th style="width:140px;">Lampiran</th>
                                    <th class="text-center" style="width:50px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                @forelse($pr->details as $index => $dtl)
                                    <tr class="product-row">
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td><input type="text" name="Product_Desc[]"
                                                class="form-control form-control-sm product-desc"
                                                placeholder="Deskripsi" value="{{ $dtl->Product_Desc }}" /></td>
                                        <td><input type="number" name="Qty[]"
                                                class="form-control form-control-sm qty text-center"
                                                value="{{ $dtl->Qty }}" min="1" /></td>
                                        <td>
                                            <textarea name="Specification[]" class="form-control form-control-sm specification" rows="1">{{ $dtl->Specification }}</textarea>
                                        </td>
                                        <td><input type="text" name="Unit[]"
                                                class="form-control form-control-sm unit text-center"
                                                value="{{ $dtl->Unit ?: 'PCS' }}" /></td>
                                        <td><input type="number" name="Price[]"
                                                class="form-control form-control-sm price text-end"
                                                value="{{ (float) ($dtl->Default_Price ?: 0) }}" min="0"
                                                step="1000" /></td>
                                        <td><input type="text" name="Total[]"
                                                class="form-control form-control-sm total text-end bg-light font-weight-bold"
                                                value="{{ (float) ($dtl->Qty * ($dtl->Default_Price ?: 0)) }}"
                                                readonly /></td>
                                        <td><input type="text" name="Reference[]"
                                                class="form-control form-control-sm reference"
                                                value="{{ $dtl->Reference }}" /></td>
                                        <td>
                                            @if (!empty($dtl->Attachment))
                                                <a href="{{ asset('storage/' . $dtl->Attachment) }}" target="_blank"
                                                    class="btn btn-xs btn-outline-info mb-1 w-100"><i
                                                        class="fa fa-download me-1"></i> Unduh</a>
                                            @endif
                                            <input type="file" name="Image[]"
                                                class="form-control form-control-sm image" />
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm remove-row p-1"
                                                title="Hapus baris">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="product-row">
                                        <td class="text-center text-muted">1</td>
                                        <td><input type="text" name="Product_Desc[]"
                                                class="form-control form-control-sm product-desc"
                                                placeholder="Deskripsi" /></td>
                                        <td><input type="number" name="Qty[]"
                                                class="form-control form-control-sm qty text-center" value="1"
                                                min="1" /></td>
                                        <td>
                                            <textarea name="Specification[]" class="form-control form-control-sm specification" rows="1"></textarea>
                                        </td>
                                        <td><input type="text" name="Unit[]"
                                                class="form-control form-control-sm unit text-center"
                                                value="PCS" /></td>
                                        <td><input type="number" name="Price[]"
                                                class="form-control form-control-sm price text-end" value="0"
                                                min="0" step="1000" /></td>
                                        <td><input type="text" name="Total[]"
                                                class="form-control form-control-sm total text-end bg-light font-weight-bold"
                                                value="0" readonly /></td>
                                        <td><input type="text" name="Reference[]"
                                                class="form-control form-control-sm reference" value="" /></td>
                                        <td><input type="file" name="Image[]"
                                                class="form-control form-control-sm image" /></td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm remove-row p-1"
                                                title="Hapus baris">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Cards -->
                    <div class="d-flex flex-wrap justify-content-end align-items-center gap-3 mt-3 pt-2">
                        <div class="px-3 py-2 bg-light rounded border text-muted small">
                            Total Item: <strong id="totalProducts"
                                class="text-dark">{{ count($pr->details) ?: 1 }}</strong> Produk
                        </div>
                        <div class="px-3 py-2 bg-light rounded border text-muted small">
                            Total Estimasi: <strong id="totalEstimate"
                                class="text-success font-weight-bold h6 mb-0 ms-1">Rp 0</strong>
                        </div>
                    </div>
                </div>

                <!-- Action Footer Buttons -->
                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top mt-4">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="$('#PurchaseManagementDiv').html('')">
                        <i class="fa fa-arrow-left me-1"></i> Batal &amp; Kembali
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" id="SubmitPRDetailReject" onclick="approvalFunc(2)"
                            class="btn btn-outline-danger px-4">
                            <i class="fa fa-times-circle me-1"></i> Tolak (Reject)
                        </button>
                        @if ($pr->Status == 'CHANGES_REVIEWED_BY_CREATOR')
                            <button type="button" id="SubmitPRDetailApproveChanges" onclick="approvalFunc(3)"
                                class="btn btn-warning text-dark px-4 shadow-xs">
                                <i class="fa fa-check-circle me-1"></i> Setujui Perubahan
                            </button>
                        @else
                            <button type="button" id="SubmitPRDetail" onclick="approvalFunc(1)"
                                class="btn btn-success px-4 text-white shadow-xs">
                                <i class="fa fa-check me-1"></i> Setujui (Approve)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $.unblockUI();
        if ($.fn.select2) {
            $('#Supplier_ID').select2({
                width: '100%'
            });
            $('#Department').select2({
                width: '100%'
            });
        }

        var productCount = $('#productTableBody tr.product-row').length || 1;

        function calculateTotal(row) {
            var qty = parseFloat(row.find('.qty').val()) || 0;
            var price = parseFloat(row.find('.price').val()) || 0;
            row.find('.total').val(qty * price);
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

        updateTotalEstimate();

        $('#addProductRow').click(function() {
            productCount++;
            var newRow = `
                <tr class="product-row">
                    <td class="text-center text-muted">${productCount}</td>
                    <td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Deskripsi" /></td>
                    <td><input type="number" name="Qty[]" class="form-control form-control-sm qty text-center" value="1" min="1" /></td>
                    <td><textarea name="Specification[]" class="form-control form-control-sm specification" rows="1" placeholder="Spesifikasi"></textarea></td>
                    <td><input type="text" name="Unit[]" class="form-control form-control-sm unit text-center" value="PCS" /></td>
                    <td><input type="number" name="Price[]" class="form-control form-control-sm price text-end" value="0" min="0" step="1000" /></td>
                    <td><input type="text" name="Total[]" class="form-control form-control-sm total text-end bg-light font-weight-bold" value="0" readonly /></td>
                    <td><input type="text" name="Reference[]" class="form-control form-control-sm reference" value="" /></td>
                    <td><input type="file" name="Image[]" class="form-control form-control-sm image" /></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row p-1" title="Hapus baris">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#productTableBody').append(newRow);
            renumberRows();
            calculateTotal($('#productTableBody tr:last'));
        });

        $(document).off('click', '.remove-row').on('click', '.remove-row', function() {
            var row = $(this).closest('.product-row');
            if ($('#productTableBody tr.product-row').length > 1) {
                row.remove();
                renumberRows();
                updateTotalEstimate();
            } else {
                alert('Minimal harus ada 1 item produk!');
            }
        });

        function renumberRows() {
            var i = 1;
            $('#productTableBody tr.product-row').each(function() {
                $(this).find('td:first').text(i);
                i++;
            });
            productCount = i - 1;
            $('#totalProducts').text(productCount);
        }
    });

    function getAllValues() {
        var inputValues = {};
        $('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
            var type = $(this).attr("type");
            var id = $(this).attr("id");
            var value = $(this).val();
            if (id && type != "button" && type != "submit") {
                if (type == "checkbox" || type == "radio") {
                    if (this.checked) inputValues[id] = value;
                } else {
                    inputValues[id] = value;
                }
            }
        });
        return inputValues;
    }

    function approvalFunc(stat) {
        var PIC = $("#PIC").val();
        var PIC_Phone = $("#PIC_Phone").val();
        var PIC_Address = $("#PIC_Address").val();
        var Estimated_Budget = $("#Estimated_Budget").val();
        var Remark = $("#Remark").val();

        if (stat == 2 && (!Remark || Remark.trim() === '')) {
            alert("Harap berikan catatan alasan penolakan pada kolom 'Approval Note / Keterangan'!");
            $("#Remark").focus();
            return false;
        }

        if (!PIC) {
            alert("Kolom Penerima Barang (PIC to Receive) Harus Diisi!");
            $("#PIC").focus();
            return false;
        }
        if (!PIC_Address || PIC_Address.length < 3) {
            alert("Kolom alamat PIC Minimal 3 Karakter!");
            $("#PIC_Address").focus();
            return false;
        }
        if (!PIC_Phone) {
            alert("Kolom nomor telepon PIC Harus Diisi!");
            $("#PIC_Phone").focus();
            return false;
        }
        if (!Estimated_Budget || parseFloat(Estimated_Budget) <= 0) {
            alert("Kolom Estimated Budget Harus Diisi!");
            $("#Estimated_Budget").focus();
            return false;
        }

        var confirmMsg = (stat == 2) ? 'Apakah Anda yakin ingin menolak PR ini?' :
            'Apakah Anda yakin ingin menyetujui PR ini?';
        if (!confirm(confirmMsg)) {
            return false;
        }

        $.blockUI({
            message: '<h4><i class="fa fa-spinner fa-spin"></i> Memproses Approval...</h4>'
        });

        var dapat = getAllValues();
        dapat['stat'] = stat;

        var gridData = [];
        $('#productTableBody tr.product-row').each(function() {
            gridData.push({
                Product_Desc: $(this).find('.product-desc').val(),
                Qty: $(this).find('.qty').val(),
                Specification: $(this).find('.specification').val(),
                Unit: $(this).find('.unit').val(),
                Price: $(this).find('.price').val(),
                Total: $(this).find('.total').val(),
                Reference: $(this).find('.reference').val(),
            });
        });

        dapat['GridData'] = JSON.stringify(gridData);
        dapat['RowCount'] = gridData.length;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "hrd-procurement-approval-detail-submit?_token={{ csrf_token() }}",
            type: "POST",
            data: dapat,
            success: function(data) {
                $.unblockUI();
                if (data == 'success') {
                    if (stat == 3) {
                        alert(
                            'Perubahan data PR No {{ $pr->PR_No }} telah disetujui. PR akan kembali ke alur approval.');
                    } else if (stat == 2) {
                        alert('Purchase Request REJECTED!');
                    } else {
                        alert('Purchase Request APPROVED! PR No: {{ $pr->PR_No }}');
                    }
                    if (typeof refreshData === 'function') refreshData();
                    $('#PurchaseManagementDiv').html("");
                } else {
                    alert('Gagal: ' + data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $.unblockUI();
                alert("Terjadi kesalahan sistem: " + errorThrown);
            }
        });
    }
</script>
