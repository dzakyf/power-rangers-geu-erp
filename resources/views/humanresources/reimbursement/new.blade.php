@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    @csrf

    <style>
        .rmb-page-wrapper {
            margin-bottom: 2.5rem;
        }

        .rmb-hero-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            border-radius: 14px;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.15);
            color: #fff;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
        }

        .rmb-hero-header h2 {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .rmb-hero-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .rmb-breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        .rmb-breadcrumb .breadcrumb-item,
        .rmb-breadcrumb .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
        }

        .rmb-breadcrumb .breadcrumb-item.active {
            color: #fff;
            font-weight: 600;
        }

        .rmb-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .rmb-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .rmb-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.9rem 1.25rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rmb-btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.55rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .rmb-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .form-label-custom {
            font-size: 0.825rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .table-expense thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            vertical-align: middle;
        }

        .total-summary-card {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 1.1rem 1.25rem;
            border-radius: 8px;
        }
    </style>

    <div class="rmb-page-wrapper">
        <!-- Hero Header -->
        <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
            <div>
                <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                    <i class="fa fa-receipt text-white me-2"></i> New Reimbursement
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                        <li class="breadcrumb-item"><a href="#">Kompensasi & Benefit</a></li>
                        <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Reimbursement
                            Management</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Main Form Container -->
        <div class="card rmb-card">
            <div class="card-body p-4">

                <!-- Section 1: Informasi Pengaju & Reimbursement -->
                <div class="rmb-section-card shadow-sm">
                    <div class="rmb-section-header">
                        <i class="fa fa-id-card text-primary"></i> 1. Informasi Dokumen & Pemohon
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-md-6 col-12">
                                <label for="Reimbursement_No" class="form-label-custom">No Reimbursement (Otomatis)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-barcode"></i></span>
                                    <input type="text" id="Reimbursement_No"
                                        class="form-control bg-light fw-bold text-primary" name="Reimbursement_No"
                                        value="{{ $Reimbursement_No }}" readonly />
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="User_Responsibility_ID" class="form-label-custom">NIK Pemohon</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-id-badge"></i></span>
                                    <input type="text" id="User_Responsibility_ID" class="form-control bg-light"
                                        name="User_Responsibility_ID" value="{{ $user->Emp_ID ?: '' }}" readonly />
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Department" class="form-label-custom">Departemen <span
                                        class="text-danger">*</span></label>
                                <select id="Department" class="form-control select2" name="Department" style="width:100%;">
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->Department }}">{{ $d->Department }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="User_Responsibility_Name" class="form-label-custom">Nama Karyawan Pengaju <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-user"></i></span>
                                    <input type="text" id="User_Responsibility_Name" class="form-control"
                                        name="User_Responsibility_Name" value="{{ $user->Name ?: $user->username }}" />
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Reference_No" class="form-label-custom">No Referensi / SPPD / STTL
                                    (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="fa fa-paperclip"></i></span>
                                    <input type="text" id="Reference_No" class="form-control" name="Reference_No"
                                        placeholder="Contoh: SPPD/GA/2603/001 atau STTL/..." />
                                </div>
                                <small class="text-muted">Isi nomor SPPD / Settlement jika reimbursement ini terhubung
                                    dengan dinas luar.</small>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Reimbursement_Date" class="form-label-custom">Tanggal Reimbursement <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="fa fa-calendar-alt"></i></span>
                                    <input type="text" id="Reimbursement_Date" class="form-control"
                                        name="Reimbursement_Date" placeholder="YYYY-MM-DD" value="{{ date('Y-m-d') }}" />
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Attachment_File" class="form-label-custom">Upload Berkas / Bukti Nota / Kwitansi (PDF, JPG, PNG)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-cloud-upload-alt"></i></span>
                                    <input type="file" id="Attachment_File" class="form-control" name="Attachment_File" accept=".pdf,.jpg,.jpeg,.png" />
                                </div>
                                <small class="text-muted">Format yang didukung: PDF, JPG, PNG (Maksimal 5MB).</small>
                            </div>

                            <div class="col-12">
                                <label for="Description" class="form-label-custom">Keterangan / Keperluan Reimbursement
                                    <span class="text-danger">*</span></label>
                                <textarea id="Description" class="form-control" name="Description" rows="3"
                                    placeholder="Tuliskan tujuan dan penjelasan reimbursement secara lengkap..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Rincian Biaya -->
                <div class="rmb-section-card shadow-sm">
                    <div class="rmb-section-header justify-content-between flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-list-ol text-primary"></i> 2. Rincian Pengeluaran / Biaya
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rmb-btn" onclick="tambahRow();">
                            <i class="fa fa-plus"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="table-responsive">
                            <table id="tblRincian"
                                class="table table-hover table-bordered table-expense align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="24%">No Bukti / Reference</th>
                                        <th width="44%">Deskripsi / Keterangan Pengeluaran</th>
                                        <th width="24%">Jumlah Nominal (Rp)</th>
                                        <th width="8%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" id="Ref_0" class="form-control form-control-sm"
                                                placeholder="No Struk / Nota / Kwitansi" />
                                        </td>
                                        <td>
                                            <input type="text" id="Remark_0" class="form-control form-control-sm"
                                                placeholder="Keterangan pengeluaran" />
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" id="Amount_0"
                                                    class="form-control text-end fw-semibold amount-input" placeholder="0"
                                                    onkeyup="formatAmountInput(this); hitungTotal();" />
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="hapusRow(this);" title="Hapus baris">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Ringkasan Total Biaya -->
                        <div class="row mt-4 align-items-center">
                            <div class="col-md-6 text-muted small">
                                <i class="fa fa-info-circle me-1 text-primary"></i> Pastikan nota atau bukti fisik/digital
                                pengeluaran disimpan untuk diserahkan ke tim Finance.
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="total-summary-card d-flex justify-content-between align-items-center shadow-sm">
                                    <div>
                                        <div class="text-muted small fw-semibold">TOTAL BIAYA REIMBURSEMENT</div>
                                        <h4 class="mb-0 text-primary fw-bold">Rp <span id="TotalCost">0</span></h4>
                                    </div>
                                    <i class="fa fa-wallet fa-2x text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Form -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
                    <a href="hrd-reimbursement-index" class="btn btn-secondary rmb-btn">
                        <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                    <button type="button" class="btn btn-success rmb-btn px-4 shadow-sm" id="submitBtn">
                        <i class="fa fa-paper-plane"></i> Kirim Pengajuan Reimbursement
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "-- Pilih Departemen --",
                allowClear: true
            });
        });

        var myCalendar;
        myCalendar = new dhtmlXCalendarObject(["Reimbursement_Date"]);
        myCalendar.setDateFormat("%Y-%m-%d");

        var rowRincian = 1;

        function tambahRow() {
            var html = "<tr>" +
                "<td><input type='text' id='Ref_" + rowRincian +
                "' class='form-control form-control-sm' placeholder='No Struk / Nota / Kwitansi' /></td>" +
                "<td><input type='text' id='Remark_" + rowRincian +
                "' class='form-control form-control-sm' placeholder='Keterangan pengeluaran' /></td>" +
                "<td><div class='input-group input-group-sm'><span class='input-group-text'>Rp</span><input type='text' id='Amount_" +
                rowRincian +
                "' class='form-control text-end fw-semibold amount-input' placeholder='0' onkeyup='formatAmountInput(this); hitungTotal();' /></div></td>" +
                "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash-alt'></i></button></td>" +
                "</tr>";
            $("#tblRincian tbody").append(html);
            rowRincian++;
        }

        function hapusRow(btn) {
            if ($("#tblRincian tbody tr").length <= 1) {
                alert("Minimal harus ada satu baris rincian biaya!");
                return;
            }
            $(btn).closest("tr").remove();
            hitungTotal();
        }

        function formatAmountInput(elem) {
            var val = $(elem).val().replace(/[^0-9]/g, '');
            if (val === '') {
                $(elem).val('');
            } else {
                var num = parseInt(val, 10);
                $(elem).val(num.toLocaleString('id-ID'));
            }
        }

        function hitungTotal() {
            var total = 0;
            $("[id^='Amount_']").each(function() {
                var raw = $(this).val().replace(/\./g, "").replace(/,/g, ".");
                var v = parseFloat(raw);
                if (!isNaN(v)) total += v;
            });
            $("#TotalCost").text(total.toLocaleString("id-ID"));
        }

        $('#submitBtn').click(function(e) {
            e.preventDefault();

            var listId = ['Department', 'User_Responsibility_Name', 'Reimbursement_Date', 'Description'];
            for (var i = 0; i < listId.length; i++) {
                if ($.trim($("#" + listId[i]).val()) === "") {
                    var labelName = listId[i].replace(/_/g, " ");
                    alert("Field " + labelName + " wajib diisi!");
                    $("#" + listId[i]).focus();
                    return;
                }
            }

            var hasRow = false;
            $("[id^='Amount_']").each(function() {
                var raw = $(this).val().replace(/\./g, "").replace(/,/g, ".");
                var v = parseFloat(raw);
                if (!isNaN(v) && v > 0) hasRow = true;
            });
            if (!hasRow) {
                alert("Rincian biaya wajib diisi minimal satu baris dengan nominal lebih dari 0!");
                return;
            }

            var formData = new FormData();
            formData.append('Reimbursement_No', $('#Reimbursement_No').val());
            formData.append('User_Responsibility_ID', $('#User_Responsibility_ID').val());
            formData.append('Department', $('#Department').val());
            formData.append('User_Responsibility_Name', $('#User_Responsibility_Name').val());
            formData.append('Reference_No', $('#Reference_No').val());
            formData.append('Reimbursement_Date', $('#Reimbursement_Date').val());
            formData.append('Description', $('#Description').val());

            var fileInput = $('#Attachment_File')[0];
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                formData.append('Attachment_File', fileInput.files[0]);
            }

            $("[id^='Amount_']").each(function() {
                var idx = $(this).attr('id').replace('Amount_', '');
                formData.append('Amount_' + idx, $(this).val());
                formData.append('Remark_' + idx, $('#Remark_' + idx).val());
                formData.append('Ref_' + idx, $('#Ref_' + idx).val());
            });

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: 'hrd-reimbursement-new-submit',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Request Reimbursement Berhasil Diajukan!');
                        location.href = 'hrd-reimbursement-index';
                    } else {
                        alert('Gagal submit! ' + res.message);
                        $btn.prop('disabled', false).html(
                            '<i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan Reimbursement');
                    }
                },
                error: function(xhr) {
                    var msg = 'Terjadi kesalahan server';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                    $btn.prop('disabled', false).html(
                        '<i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan Reimbursement');
                }
            });
        });
    </script>
@endsection
