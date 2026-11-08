<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Profile - Mobile</title>
    <!-- PWA Settings -->
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="HCGA Mobile">
    <link rel="apple-touch-icon" href="{{ asset('adminor/assets/img/brand/favicon.png') }}">
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 70px;
        }
        .header-profile {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 24px 18px 28px;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.25);
            position: relative;
        }
        .avatar-box {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.85);
            object-fit: cover;
            background-color: #334155;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .tab-nav-wrapper {
            background: #fff;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border-bottom: 1px solid #e2e8f0;
        }
        .tab-scroll {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 14px;
            scrollbar-width: none;
        }
        .tab-scroll::-webkit-scrollbar {
            display: none;
        }
        .tab-pill-btn {
            font-size: 13px;
            font-weight: 600;
            border-radius: 20px;
            padding: 6px 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .tab-pill-btn.active {
            background: #1e3a8a;
            color: #fff;
            border-color: #1e3a8a;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.3);
        }
        .info-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            word-break: break-word;
        }
        .card-section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 6px;
        }
        .spinner-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 20px;
            color: #64748b;
        }
        .btn-edit-field {
            font-size: 11px;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 2px 8px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- Header Profil -->
    <div class="header-profile">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('employee.mobile.directory') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1" style="font-size: 12px;">
                    <i class="fa fa-arrow-left me-1"></i> Direktori
                </a>
                <span class="badge bg-primary bg-opacity-25 border border-primary border-opacity-50 text-white rounded-pill px-2 py-1" style="font-size: 11px;">
                    <i class="fa fa-id-badge me-1"></i> {{ $employee->NIK ?? '-' }}
                </span>
            </div>
            @if($isOwnProfile)
                <span class="badge bg-success bg-opacity-25 border border-success border-opacity-50 text-success rounded-pill px-2 py-1" style="font-size: 11px;">
                    <i class="fa fa-user-check me-1"></i> Profil Saya
                </span>
            @elseif($isHcga)
                <span class="badge bg-warning bg-opacity-25 border border-warning border-opacity-50 text-warning rounded-pill px-2 py-1" style="font-size: 11px;">
                    <i class="fa fa-shield-halved me-1"></i> Mode Admin HCGA
                </span>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            @if(!empty($employee->Foto) && file_exists(public_path('uploads/employee/' . $employee->Foto)))
                <img src="{{ asset('uploads/employee/' . $employee->Foto) }}" class="avatar-box" alt="Foto">
            @else
                <div class="avatar-box d-flex align-items-center justify-content-center text-white" style="font-size: 28px; font-weight: 700;">
                    {{ strtoupper(substr($employee->Name ?? 'E', 0, 1)) }}
                </div>
            @endif

            <div class="flex-grow-1">
                <h5 class="fw-bold mb-1 text-white" style="font-size: 17px; line-height: 1.3;">{{ $employee->Name ?? 'Tanpa Nama' }}</h5>
                <div class="text-white-50 small mb-1" style="font-size: 12px;">
                    <i class="fa fa-briefcase me-1"></i> {{ $employee->Position ?? 'Belum ditentukan' }}
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-secondary bg-opacity-50 text-white rounded-pill px-2 py-1" style="font-size: 10px;">
                        {{ $employee->Department ?? 'N/A' }}
                    </span>
                    <span class="badge bg-info bg-opacity-50 text-white rounded-pill px-2 py-1" style="font-size: 10px;">
                        {{ $employee->Location ?? 'Head Office' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Pill Navigation (Sticky & Touch-Friendly) -->
    <div class="tab-nav-wrapper">
        <div class="tab-scroll">
            <button class="tab-pill-btn active" data-tab="profile">
                <i class="fa fa-user"></i> {{ ($isOwnProfile || $isHcga) ? 'Data Diri' : 'Jabatan & Kontak' }}
            </button>

            <!-- HANYA TAMPIL UNTUK DIRI SENDIRI ATAU TIM HCGA/ADMIN -->
            @if($isOwnProfile || $isHcga)
            <button class="tab-pill-btn" data-tab="family">
                <i class="fa fa-users"></i> Keluarga & Darurat
            </button>
            <button class="tab-pill-btn" data-tab="education">
                <i class="fa fa-graduation-cap"></i> Edukasi & Pengalaman
            </button>
            <button class="tab-pill-btn" data-tab="benefit">
                <i class="fa fa-shield-alt"></i> Asuransi & Cuti
            </button>
            <button class="tab-pill-btn" data-tab="document">
                <i class="fa fa-folder-open"></i> Dokumen & Riwayat
            </button>
            <button class="tab-pill-btn" data-tab="requests">
                <i class="fa fa-clock-rotate-left"></i> {{ $isHcga ? 'Audit & Approval' : 'Pengajuan Update' }}
            </button>
            @endif
        </div>
    </div>

    <!-- Tab Content Container (Lazy Loaded via AJAX) -->
    <div class="container-fluid px-3 pt-3" id="tabContentContainer">
        <div class="spinner-loading">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <span class="small">Memuat data profil...</span>
        </div>
    </div>

    @if($isOwnProfile || $isHcga)
    <!-- Modal Pengajuan / Edit Perubahan Data -->
    <div class="modal fade" id="modalRequestUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header {{ $isHcga ? 'bg-dark' : 'bg-primary' }} text-white py-3">
                    <h6 class="modal-title fw-bold" id="modalUpdateTitle">
                        <i class="fa {{ $isHcga ? 'fa-user-pen' : 'fa-edit' }} me-1"></i> 
                        {{ $isHcga ? 'Ubah Data Karyawan (HCGA Admin)' : 'Ajukan Perubahan Data' }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formUpdateData" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $employee->Task_ID }}">
                    <input type="hidden" name="field_category" id="modalCategory">
                    <input type="hidden" name="field_name" id="modalField">
                    <input type="hidden" name="old_value" id="modalOldValue">

                    <div class="modal-body p-3">
                        @if($isHcga)
                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="fa fa-shield-halved me-1"></i> Sebagai <strong>Admin/HCGA</strong>, perubahan data yang Anda simpan akan langsung diperbarui ke sistem tanpa menunggu workflow approval.
                            </div>
                        @else
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fa fa-info-circle me-1"></i> Perubahan data akan diteruskan ke tim HCGA untuk diverifikasi dan disetujui secara berjenjang.
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted mb-1">Data Saat Ini</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="displayOldValue" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-1">Nilai / Data Baru <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="new_value" id="modalNewValue" required placeholder="Masukkan data perubahan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-1">Alasan / Catatan Perubahan</label>
                            <textarea class="form-control form-control-sm" name="reason" rows="2" placeholder="{{ $isHcga ? 'Alasan admin melakukan update' : 'Contoh: Pindah alamat domisili, pergantian nomor WhatsApp' }}"></textarea>
                        </div>

                        @if(!$isHcga)
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark mb-1">Bukti / Dokumen Pendukung (Opsional)</label>
                            <input type="file" class="form-control form-control-sm" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text" style="font-size: 11px;">Maks 2MB (Foto KTP, Buku Tabungan, KK, dll.)</div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm {{ $isHcga ? 'btn-dark' : 'btn-primary' }} px-3" id="btnSubmitUpdate">
                            {{ $isHcga ? 'Simpan Perubahan' : 'Kirim Pengajuan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Scripts -->
    <script src="{{ asset('adminor/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        const taskId = "{{ $employee->Task_ID }}";
        const loadTabUrl = "{{ route('employee.mobile.tab-content') }}";
        let activeTab = 'profile';

        function loadTab(tabName) {
            activeTab = tabName;
            $('#tabContentContainer').html(`
                <div class="spinner-loading">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <span class="small">Memuat data...</span>
                </div>
            `);

            $.ajax({
                url: loadTabUrl,
                type: 'GET',
                data: { task_id: taskId, tab: tabName },
                success: function(response) {
                    $('#tabContentContainer').html(response);
                },
                error: function(xhr) {
                    $('#tabContentContainer').html(`
                        <div class="alert alert-danger m-3 small">
                            <i class="fa fa-triangle-exclamation me-1"></i> Gagal memuat data rincian (${xhr.status}). Silakan coba lagi.
                        </div>
                    `);
                }
            });
        }

        $(document).ready(function() {
            // Load tab default pertama kali
            loadTab(activeTab);

            // Tab switch handler
            $('.tab-pill-btn').on('click', function() {
                $('.tab-pill-btn').removeClass('active');
                $(this).addClass('active');
                const tab = $(this).data('tab');
                loadTab(tab);
            });

            // Modal edit trigger
            $(document).on('click', '.btn-trigger-edit', function() {
                const category = $(this).data('category');
                const field = $(this).data('field');
                const fieldLabel = $(this).data('label');
                const value = $(this).data('value') || '';

                $('#modalCategory').val(category);
                $('#modalField').val(field);
                $('#modalOldValue').val(value);
                $('#displayOldValue').val(value || '(Kosong)');
                $('#modalNewValue').val('');
                $('#modalUpdateTitle').html(`<i class="fa fa-edit me-1"></i> Ubah ${fieldLabel}`);

                const modal = new bootstrap.Modal(document.getElementById('modalRequestUpdate'));
                modal.show();
            });

            // Form submit pengajuan / direct update
            $('#formUpdateData').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btnSubmitUpdate');
                const originalText = btn.text();
                btn.prop('disabled', true).text('Menyimpan...');

                const formData = new FormData(this);

                $.ajax({
                    url: "{{ route('employee.mobile.submit-update') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        btn.prop('disabled', false).text(originalText);
                        if (res.success) {
                            alert(res.message);
                            bootstrap.Modal.getInstance(document.getElementById('modalRequestUpdate')).hide();
                            $('#formUpdateData')[0].reset();
                            // Reload tab aktif
                            loadTab(activeTab);
                        } else {
                            alert(res.message || 'Terjadi kesalahan.');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text(originalText);
                        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memproses data.';
                        alert(err);
                    }
                });
            });
        });
    </script>
</body>
</html>
