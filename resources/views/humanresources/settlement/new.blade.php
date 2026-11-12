@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    @csrf

    <style>
        .settlement-page-wrapper {
            margin-bottom: 2.5rem;
        }


        .settlement-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .settlement-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.9rem 1.25rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 10px 10px 0 0;
        }

        .settlement-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .settlement-btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.55rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .settlement-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .form-label-custom {
            font-size: 0.825rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .table-expense th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .total-summary-card {
            background: #f8fafc;
            border-left: 4px solid #0d6efd;
            padding: 1rem 1.25rem;
            border-radius: 8px;
        }
    </style>

    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                Travel, Expense & Settlement
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('hrd-settlement-index') }}">Settlement</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Buat
                        Pengajuan Settlement</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ url('hrd-settlement-index') }}" class="btn btn-outline-secondary settlement-btn">
                <i class="fa fa-arrow-left"></i> Kembali ke List
            </a>
        </div>
    </div>

    <div class="settlement-page-wrapper">
        <div class="card settlement-card">
            <div class="card-body p-4">
                <form id="settlementForm" enctype="multipart/form-data">

                    <!-- Section 1: Informasi Dasar -->
                    <div class="settlement-section-card">
                        <div class="settlement-section-header">
                            <i class="fa fa-id-card text-primary"></i> Data Pokok & Referensi Pengajuan
                        </div>
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="STTL_No" class="form-label-custom">No. Settlement</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i
                                                class="fa fa-barcode"></i></span>
                                        <input type="text" id="STTL_No"
                                            class="form-control bg-light font-weight-bold" name="STTL_No"
                                            value="{{ $STTL_No }}" readonly />
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="Requester_NIK" class="form-label-custom">NIK Pemohon</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i
                                                class="fa fa-id-badge"></i></span>
                                        <input type="text" id="Requester_NIK" class="form-control bg-light"
                                            name="Requester_NIK" value="{{ $user->Emp_ID ?: '-' }}" readonly />
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="Requester_Name" class="form-label-custom">Nama Karyawan Pemohon
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i
                                                class="fa fa-user"></i></span>
                                        <input type="text" id="Requester_Name" class="form-control"
                                            name="Requester_Name" value="{{ $user->Name ?: $user->username }}"
                                            required />
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="Department" class="form-label-custom">Departemen <span
                                            class="text-danger">*</span></label>
                                    <select id="Department" class="form-control select2" name="Department"
                                        required>
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d->Department }}">{{ $d->Department }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 col-12">
                                    <label for="SPPD_ID" class="form-label-custom">Referensi SPPD
                                        (Opsional)</label>
                                    <select id="SPPD_ID" class="form-control select2">
                                        <option value="">-- Tanpa Referensi SPPD --</option>
                                        @foreach ($sppdList ?? collect() as $sp)
                                            <option value="{{ $sp->ID }}">{{ $sp->SPPD_No }} —
                                                {{ $sp->Destination }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-primary mt-1 d-block fw-semibold" id="sppdInfo"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Rincian Pengeluaran -->
                    <div class="settlement-section-card">
                        <div class="settlement-section-header justify-content-between">
                            <span><i class="fa fa-receipt text-primary"></i> Rincian Penggunaan Biaya
                                Real</span>
                            <button type="button" class="btn btn-primary btn-sm settlement-btn"
                                onclick="tambahRow();">
                                <i class="fa fa-plus"></i> Tambah Item Biaya
                            </button>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table id="tblExpense"
                                    class="table table-bordered table-expense align-middle mb-2">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 220px;">Jenis Pengeluaran <span
                                                    class="text-danger">*</span></th>
                                            <th style="width: 200px;">Jumlah (Rp) <span
                                                    class="text-danger">*</span></th>
                                            <th>Keterangan / Keperluan</th>
                                            <th style="width: 60px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" id="Expense_0" class="form-control"
                                                    placeholder="Contoh: Tiket, Penginapan, Makan" required />
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span
                                                        class="input-group-text bg-light text-muted">Rp</span>
                                                    <input type="text" id="Cost_0"
                                                        class="form-control text-end cost-input"
                                                        placeholder="0" onkeyup="hitungTotal();" required />
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" id="Desc_0" class="form-control"
                                                    placeholder="Catatan tambahan keperluan pengeluaran" />
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="hapusRow(this);" title="Hapus baris">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="total-summary-card mt-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-3 col-6">
                                        <span class="text-muted small text-uppercase fw-bold">Rincian Real (Manual):</span>
                                        <div class="h5 mb-0 fw-bold text-primary">
                                            Rp <span id="TotalManualCost">0</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6" id="MatrixAllowanceBox" style="display: none;">
                                        <span class="text-muted small text-uppercase fw-bold">Tunjangan Matriks SPPD:</span>
                                        <div class="h5 mb-0 fw-bold text-info">
                                            + Rp <span id="MatrixAllowanceNominal">0</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.725rem;">(Diperhitungkan otomatis)</small>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <span class="text-muted small text-uppercase fw-bold">Total Diperhitungkan:</span>
                                        <div class="h4 mb-0 fw-bold text-dark">
                                            Rp <span id="TotalCost">0</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 text-md-end" id="SelisihBox" style="display: none;">
                                        <span class="text-muted small text-uppercase fw-bold" id="SelisihLabel">Reimburse / Lebih Bayar:</span>
                                        <div class="h5 mb-0 fw-bold" id="SelisihNominal">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Advance & Pencairan / Dokumen -->
                    <div class="settlement-section-card">
                        <div class="settlement-section-header">
                            <i class="fa fa-money-check-alt text-primary"></i> Advance, Rekening & Bukti
                            Struk/Invoice
                        </div>
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="Advance_Nominal" class="form-label-custom">Biaya Uang Muka /
                                        Advance yang Diterima</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">Rp</span>
                                        <input type="text" id="Advance_Nominal" class="form-control"
                                            name="Advance_Nominal"
                                            placeholder="0 (Kosongkan jika reimbursement murni)"
                                            onkeyup="hitungTotal();" />
                                    </div>
                                    <small class="text-muted">Isi nominal dana awal yang telah ditransfer
                                        sebelumnya jika ada.</small>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="Bank" class="form-label-custom">Rekening Bank untuk
                                        Pencairan / Pengembalian</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i
                                                class="fa fa-university"></i></span>
                                        <input type="text" id="Bank" class="form-control"
                                            name="Bank"
                                            placeholder="Contoh: BCA 1234567890 a.n. Nama Karyawan" />
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <label for="Invoice_File" class="form-label-custom">Berkas Bukti / Invoice
                                        / Struk Pengeluaran <span class="text-danger">*</span></label>
                                    <input type="file" id="Invoice_File" name="Invoice_File"
                                        class="form-control" accept=".pdf,.jpg,.jpeg,.png" required />
                                    <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Format
                                        berkas yang didukung: <strong>PDF, JPG, JPEG, PNG</strong> (Ukuran
                                        maksimal 5MB). Lampirkan rekapan atau kwitansi resmi.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4">
                        <a href="{{ url('hrd-settlement-index') }}" class="btn btn-secondary settlement-btn">
                            <i class="fa fa-times"></i> Batal
                        </a>
                        <button type="button" class="btn btn-success settlement-btn" id="submitBtn">
                            <i class="fa fa-paper-plane"></i> Submit Request Settlement
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @php
        $sppdMap = collect($sppdList ?? [])->mapWithKeys(function ($s) use ($sppdCosts, $sppdMatrixCosts) {
            $matrixCost = (float) ($sppdMatrixCosts[$s->ID] ?? 0);
            return [
                $s->ID => [
                    'no'             => $s->SPPD_No,
                    'dept'           => $s->Department,
                    'dest'           => $s->Destination,
                    'cost'           => (float) $s->Estimated_Cost,
                    'matrix_cost'    => $matrixCost,
                    'requester_name' => $s->Requester_Name ?? '',
                    'requester_nik'  => $s->Requester_NIK ?? '',
                    'items'          => $sppdCosts[$s->ID] ?? []
                ],
            ];
        });
    @endphp

    <script>
        var SPPD_MAP = {!! json_encode($sppdMap) !!};
        var currentMatrixAllowance = 0;

        $(function() {
            $('#SPPD_ID').select2({
                width: '100%',
                placeholder: '-- Tanpa Referensi SPPD --',
                allowClear: true
            });
            $('#Department').select2({
                width: '100%',
                placeholder: '-- Pilih Departemen --',
                allowClear: true
            });

            $('#SPPD_ID').on('change', function() {
                var s = SPPD_MAP[$(this).val()];
                if (s) {
                    currentMatrixAllowance = Number(s.matrix_cost || 0);
                    if (s.dept) {
                        $('#Department').val(s.dept).trigger('change');
                    }
                    if (s.cost) {
                        $('#Advance_Nominal').val(s.cost);
                    }
                    if (s.requester_name) {
                        $('#Requester_Name').val(s.requester_name);
                    }
                    if (s.requester_nik) {
                        $('#Requester_NIK').val(s.requester_nik);
                    }
                    var infoHtml = '<i class="fa fa-info-circle me-1"></i> Ref SPPD: ' + s.no +
                        ' | Pemohon: ' + (s.requester_name ? (s.requester_name + ' (' + s.requester_nik + ')') : '-') +
                        ' | Tujuan: ' + (s.dest || '-') + ' | Estimasi Biaya SPPD: Rp ' + Number(s.cost || 0).toLocaleString('id-ID');

                    if (currentMatrixAllowance > 0) {
                        infoHtml += ' | <span class="badge bg-primary text-white ms-1">Tunjangan Matriks: Rp ' + currentMatrixAllowance.toLocaleString('id-ID') + '</span>';
                    }

                    // Tarik detail rincian pengeluaran dari SPPD jika tersedia (hanya yang non-matrix/manual)
                    if (s.items && s.items.length > 0) {
                        infoHtml += ' <span class="badge bg-success text-white ms-1"><i class="fa fa-check-circle me-1"></i>' + s.items.length + ' item manual dimuat</span>';
                        populateExpensesFromSppd(s.items);
                    } else {
                        infoHtml += ' <span class="badge bg-info text-white ms-1"><i class="fa fa-info-circle me-1"></i>Biaya otomatis matriks SPPD tidak ditarik ke rincian</span>';
                        resetExpensesToSingleEmptyRow();
                    }
                    $('#sppdInfo').html(infoHtml);
                } else {
                    currentMatrixAllowance = 0;
                    $('#sppdInfo').text('');
                    resetExpensesToSingleEmptyRow();
                }
            });
        });

        function resetExpensesToSingleEmptyRow() {
            var $tbody = $("#tblExpense tbody");
            $tbody.empty();
            rowExpense = 0;
            var html = "<tr>" +
                "<td><input type='text' id='Expense_0' class='form-control' placeholder='Contoh: Tiket, Penginapan, Makan' required /></td>" +
                "<td><div class='input-group'><span class='input-group-text bg-light text-muted'>Rp</span><input type='text' id='Cost_0' class='form-control text-end cost-input' placeholder='0' onkeyup='hitungTotal();' required /></div></td>" +
                "<td><input type='text' id='Desc_0' class='form-control' placeholder='Catatan tambahan keperluan pengeluaran' /></td>" +
                "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash'></i></button></td>" +
                "</tr>";
            $tbody.append(html);
            rowExpense = 1;
            hitungTotal();
        }

        function populateExpensesFromSppd(items) {
            var $tbody = $("#tblExpense tbody");
            $tbody.empty();
            rowExpense = 0;

            items.forEach(function(item, idx) {
                var costVal = Number(item.Cost || 0);
                var html = "<tr>" +
                    "<td><input type='text' id='Expense_" + rowExpense +
                    "' class='form-control' value='" + (item.Expense || '') + "' placeholder='Contoh: Tiket, Hotel, BBM' required /></td>" +
                    "<td><div class='input-group'><span class='input-group-text bg-light text-muted'>Rp</span><input type='text' id='Cost_" +
                    rowExpense +
                    "' class='form-control text-end cost-input' value='" + costVal + "' placeholder='0' onkeyup='hitungTotal();' required /></div></td>" +
                    "<td><input type='text' id='Desc_" + rowExpense +
                    "' class='form-control' value='" + (item.Description || '') + "' placeholder='Keterangan keperluan' /></td>" +
                    "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash'></i></button></td>" +
                    "</tr>";
                $tbody.append(html);
                rowExpense++;
            });

            hitungTotal();
        }

        var rowExpense = 1;

        function tambahRow() {
            var html = "<tr>" +
                "<td><input type='text' id='Expense_" + rowExpense +
                "' class='form-control' placeholder='Contoh: Tiket, Hotel, BBM' required /></td>" +
                "<td><div class='input-group'><span class='input-group-text bg-light text-muted'>Rp</span><input type='text' id='Cost_" +
                rowExpense +
                "' class='form-control text-end cost-input' placeholder='0' onkeyup='hitungTotal();' required /></div></td>" +
                "<td><input type='text' id='Desc_" + rowExpense +
                "' class='form-control' placeholder='Keterangan keperluan' /></td>" +
                "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash'></i></button></td>" +
                "</tr>";
            $("#tblExpense tbody").append(html);
            rowExpense++;
        }

        function hapusRow(btn) {
            if ($("#tblExpense tbody tr").length <= 1) {
                alert("Minimal harus ada satu baris rincian biaya!");
                return;
            }
            $(btn).closest("tr").remove();
            hitungTotal();
        }

        function hitungTotal() {
            var totalManual = 0;
            $("[id^='Cost_']").each(function() {
                var raw = $(this).val().replace(/\./g, "").replace(/,/g, ".");
                var v = parseFloat(raw);
                if (!isNaN(v)) totalManual += v;
            });

            var matrixVal = currentMatrixAllowance || 0;
            var totalDiperhitungkan = totalManual + matrixVal;

            $("#TotalManualCost").text(totalManual.toLocaleString("id-ID"));
            $("#TotalCost").text(totalDiperhitungkan.toLocaleString("id-ID"));

            if (matrixVal > 0) {
                $("#MatrixAllowanceBox").show();
                $("#MatrixAllowanceNominal").text(matrixVal.toLocaleString("id-ID"));
            } else {
                $("#MatrixAllowanceBox").hide();
            }

            // Kalkulasi selisih dengan advance
            var advanceRaw = $("#Advance_Nominal").val().replace(/\./g, "").replace(/,/g, ".");
            var advanceVal = parseFloat(advanceRaw) || 0;

            if (advanceVal > 0) {
                $("#SelisihBox").show();
                var selisih = totalDiperhitungkan - advanceVal;
                if (selisih > 0) {
                    $("#SelisihLabel").text("Kurang Bayar (Reimbursement Karyawan):");
                    $("#SelisihNominal").html("<span class='text-danger'>Rp " + selisih.toLocaleString("id-ID") +
                    "</span>");
                } else if (selisih < 0) {
                    $("#SelisihLabel").text("Lebih Bayar (Pengembalian ke Perusahaan):");
                    $("#SelisihNominal").html("<span class='text-success'>Rp " + Math.abs(selisih).toLocaleString("id-ID") +
                        "</span>");
                } else {
                    $("#SelisihLabel").text("Status Biaya:");
                    $("#SelisihNominal").html("<span class='text-primary'>Pas (Sesuai Advance)</span>");
                }
            } else {
                $("#SelisihBox").hide();
            }
        }

        $('#submitBtn').click(function(e) {
            e.preventDefault();

            if ($.trim($("#Department").val()) === "") {
                alert("Field Departemen wajib dipilih!");
                $("#Department").focus();
                return;
            }
            if ($.trim($("#Requester_Name").val()) === "") {
                alert("Nama Karyawan Pemohon wajib diisi!");
                $("#Requester_Name").focus();
                return;
            }
            if ($("#Invoice_File")[0].files.length === 0) {
                alert("Berkas Invoice / Kwitansi pengeluaran wajib diupload!");
                $("#Invoice_File").focus();
                return;
            }

            var hasRow = false;
            $("[id^='Cost_']").each(function() {
                var v = parseFloat($(this).val().replace(/\./g, "").replace(/,/g, "."));
                if (!isNaN(v) && v > 0) hasRow = true;
            });
            if (!hasRow) {
                alert("Rincian penggunaan biaya wajib diisi minimal satu baris dengan nilai lebih dari 0!");
                return;
            }

            var formData = new FormData();
            formData.append('STTL_No', $('#STTL_No').val());
            formData.append('Requester_NIK', $('#Requester_NIK').val());
            formData.append('Department', $('#Department').val());
            formData.append('Requester_Name', $('#Requester_Name').val());
            formData.append('SPPD_ID', $('#SPPD_ID').val());
            formData.append('Advance_Nominal', $('#Advance_Nominal').val());
            formData.append('Bank', $('#Bank').val());
            formData.append('Invoice_File', $('#Invoice_File')[0].files[0]);

            $("[id^='Expense_']").each(function() {
                var idx = $(this).attr('id').replace('Expense_', '');
                formData.append('Expense_' + idx, $(this).val());
                formData.append('Cost_' + idx, $('#Cost_' + idx).val());
                formData.append('Desc_' + idx, $('#Desc_' + idx).val());
            });

            var $btn = $('#submitBtn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: 'hrd-settlement-new-submit',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $btn.prop('disabled', false).html(
                        '<i class="fa fa-paper-plane"></i> Submit Request Settlement');
                    if (data.status === 'success') {
                        alert('Request Settlement Berhasil Dikirim!');
                        location.href = 'hrd-settlement-index';
                    } else {
                        alert('Gagal submit pengajuan: ' + data.message);
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(
                        '<i class="fa fa-paper-plane"></i> Submit Request Settlement');
                    var msg = 'Terjadi kesalahan sistem saat mengirim data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        });
    </script>
@endsection
