<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Reimbursement Mobile</title>
    <!-- Bootstrap 5 & FontAwesome & jQuery -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 90px;
        }
        .header-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 20px 18px 22px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.2);
        }
        .form-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .form-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .form-label-mobile {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 4px;
        }
        .expense-row-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            position: relative;
        }
        .bottom-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 12px 16px;
            box-shadow: 0 -3px 12px rgba(0,0,0,0.08);
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <!-- Header Form Mobile -->
    <div class="header-card">
        <div class="d-flex align-items-center mb-2">
            <a href="{{ route('reimbursement.mobile.index') }}" class="btn btn-sm btn-outline-light rounded-circle me-2" style="width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center;">
                <i class="fa fa-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold">Pengajuan Reimbursement</h5>
        </div>
        <small class="text-white-75">Isi rincian pengeluaran & upload bukti nota/kwitansi.</small>
    </div>

    <div class="container px-3 mt-3">
        <form id="rmbMobileForm" enctype="multipart/form-data">
            @csrf

            <!-- Section 1: Informasi Dokumen & Pemohon -->
            <div class="form-section-card">
                <div class="form-section-header">
                    <i class="fa fa-id-card text-primary"></i> 1. Informasi Pemohon
                </div>
                <div class="p-3">
                    <div class="mb-2">
                        <label class="form-label-mobile">No Reimbursement (Auto)</label>
                        <input type="text" id="Reimbursement_No" class="form-control form-control-sm bg-light fw-bold text-primary" name="Reimbursement_No" value="{{ $Reimbursement_No }}" readonly />
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label-mobile">NIK Pemohon</label>
                            <input type="text" id="User_Responsibility_ID" class="form-control form-control-sm bg-light" name="User_Responsibility_ID" value="{{ $user->Emp_ID ?: '' }}" readonly />
                        </div>
                        <div class="col-6">
                            <label class="form-label-mobile">Departemen <span class="text-danger">*</span></label>
                            <select id="Department" class="form-select form-select-sm" name="Department">
                                <option value="">-- Pilih --</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->Department }}" {{ $user->Department == $d->Department ? 'selected' : '' }}>{{ $d->Department }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label-mobile">Nama Karyawan Pengaju <span class="text-danger">*</span></label>
                        <input type="text" id="User_Responsibility_Name" class="form-control form-control-sm" name="User_Responsibility_Name" value="{{ $user->Name ?: $user->username }}" />
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label-mobile">Tanggal Reimbursement <span class="text-danger">*</span></label>
                            <input type="date" id="Reimbursement_Date" class="form-control form-control-sm" name="Reimbursement_Date" value="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-6">
                            <label class="form-label-mobile">No Ref / SPPD (Opsional)</label>
                            <input type="text" id="Reference_No" class="form-control form-control-sm" name="Reference_No" placeholder="SPPD/..." />
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label-mobile">Keperluan / Keterangan <span class="text-danger">*</span></label>
                        <textarea id="Description" class="form-control form-control-sm" name="Description" rows="2" placeholder="Tuliskan tujuan reimbursement..."></textarea>
                    </div>

                    <div>
                        <label class="form-label-mobile">Upload Bukti Nota / Kwitansi (PDF/Foto)</label>
                        <input type="file" id="Attachment_File" class="form-control form-control-sm" name="Attachment_File" accept=".pdf,.jpg,.jpeg,.png" />
                        <small class="text-muted" style="font-size:11px;">Maksimal 5MB (Format PDF, JPG, PNG)</small>
                    </div>
                </div>
            </div>

            <!-- Section 2: Rincian Biaya -->
            <div class="form-section-card">
                <div class="form-section-header justify-content-between">
                    <div><i class="fa fa-list-ol text-primary"></i> 2. Rincian Pengeluaran</div>
                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 rounded-pill" onclick="tambahBarisMobile();" style="font-size:12px;">
                        <i class="fa fa-plus"></i> Tambah
                    </button>
                </div>
                <div class="p-3">
                    <div id="expenseContainer">
                        <!-- Baris Pertama -->
                        <div class="expense-row-card" id="row_0">
                            <div class="mb-2">
                                <label class="form-label-mobile">Keterangan Pengeluaran</label>
                                <input type="text" id="Remark_0" class="form-control form-control-sm" placeholder="Contoh: Bensin, Tol, Parkir, dsb." />
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label-mobile">No Struk / Kwitansi</label>
                                    <input type="text" id="Ref_0" class="form-control form-control-sm" placeholder="No Nota" />
                                </div>
                                <div class="col-6">
                                    <label class="form-label-mobile">Nominal (Rp)</label>
                                    <input type="number" id="Amount_0" class="form-control form-control-sm text-end fw-bold" placeholder="0" onkeyup="hitungTotalMobile();" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Card -->
                    <div class="p-2 bg-light rounded-3 d-flex justify-content-between align-items-center mt-2 border">
                        <span class="small fw-bold text-muted">TOTAL KLAIM:</span>
                        <h5 class="mb-0 text-primary fw-bold">Rp <span id="TotalDisplay">0</span></h5>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <!-- Bottom Action Button -->
    <div class="bottom-action-bar">
        <a href="{{ route('reimbursement.mobile.index') }}" class="btn btn-outline-secondary w-50 fw-bold">
            Batal
        </a>
        <button type="button" class="btn btn-primary w-50 fw-bold shadow-sm" id="btnSubmitMobile">
            <i class="fa fa-paper-plane me-1"></i> Ajukan
        </button>
    </div>

    <script>
        var rowCount = 1;

        function tambahBarisMobile() {
            var html = '<div class="expense-row-card" id="row_' + rowCount + '">'
                + '<button type="button" class="btn-close position-absolute top-0 end-0 m-2" style="font-size:10px;" onclick="hapusBarisMobile(' + rowCount + ');"></button>'
                + '<div class="mb-2">'
                + '<label class="form-label-mobile">Keterangan Pengeluaran</label>'
                + '<input type="text" id="Remark_' + rowCount + '" class="form-control form-control-sm" placeholder="Keterangan pengeluaran" />'
                + '</div>'
                + '<div class="row g-2">'
                + '<div class="col-6">'
                + '<label class="form-label-mobile">No Struk / Kwitansi</label>'
                + '<input type="text" id="Ref_' + rowCount + '" class="form-control form-control-sm" placeholder="No Nota" />'
                + '</div>'
                + '<div class="col-6">'
                + '<label class="form-label-mobile">Nominal (Rp)</label>'
                + '<input type="number" id="Amount_' + rowCount + '" class="form-control form-control-sm text-end fw-bold" placeholder="0" onkeyup="hitungTotalMobile();" />'
                + '</div>'
                + '</div>'
                + '</div>';
            $('#expenseContainer').append(html);
            rowCount++;
        }

        function hapusBarisMobile(idx) {
            $('#row_' + idx).remove();
            hitungTotalMobile();
        }

        function hitungTotalMobile() {
            var total = 0;
            $("[id^='Amount_']").each(function() {
                var v = parseFloat($(this).val());
                if (!isNaN(v)) total += v;
            });
            $('#TotalDisplay').text(total.toLocaleString('id-ID'));
        }

        $('#btnSubmitMobile').click(function(e) {
            e.preventDefault();

            if ($.trim($('#Department').val()) === '') {
                alert('Pilih Departemen terlebih dahulu!');
                return;
            }
            if ($.trim($('#User_Responsibility_Name').val()) === '') {
                alert('Nama Karyawan wajib diisi!');
                return;
            }
            if ($.trim($('#Reimbursement_Date').val()) === '') {
                alert('Tanggal Reimbursement wajib diisi!');
                return;
            }
            if ($.trim($('#Description').val()) === '') {
                alert('Keperluan reimbursement wajib diisi!');
                return;
            }

            var hasRow = false;
            $("[id^='Amount_']").each(function() {
                var v = parseFloat($(this).val());
                if (!isNaN(v) && v > 0) hasRow = true;
            });
            if (!hasRow) {
                alert('Rincian biaya wajib diisi minimal 1 baris dengan nominal > 0!');
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
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: '{{ url("hrd-reimbursement-new-submit") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Pengajuan Reimbursement Berhasil Dikirim!');
                        location.href = '{{ route("reimbursement.mobile.index") }}';
                    } else {
                        alert('Gagal: ' + res.message);
                        $btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Ajukan');
                    }
                },
                error: function(xhr) {
                    var msg = 'Terjadi kesalahan sistem';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Ajukan');
                }
            });
        });
    </script>

</body>
</html>

