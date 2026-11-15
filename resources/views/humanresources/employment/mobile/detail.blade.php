<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Profile - Mobile</title>
    <!-- PWA Settings -->
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#064E3B">
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
        .avatar-box {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 3px solid var(--emerald-soft);
            object-fit: cover;
            background-color: var(--emerald-primary);
            box-shadow: 0 4px 12px rgba(6, 78, 59, 0.18);
            flex-shrink: 0;
        }
        .tab-nav-wrapper {
            background: #fff;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
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
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            padding: 7px 14px;
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
            background: #047857;
            color: #fff;
            border-color: #047857;
            box-shadow: 0 2px 8px rgba(4, 120, 87, 0.25);
        }
        .info-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
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
            color: #064E3B;
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
            color: #047857;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            border-radius: 12px;
            padding: 2px 8px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
    @include('humanresources.mobile.partials.theme')
</head>
<body>

    <div class="finora-shell pb-0">
        <div class="finora-topbar">
            <a href="{{ route('mobile.dashboard') }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Profil Karyawan</h1>
            <a href="{{ route('company-guideline.mobile.index') }}" class="finora-back-btn" title="Budaya & Peraturan"><i class="fas fa-compass"></i></a>
        </div>

        <!-- Finora Profile Identity Row -->
        <div class="d-flex align-items-center gap-3 mb-3 px-1">
            @if(!empty($employee->Foto) && file_exists(public_path('uploads/employee/' . $employee->Foto)))
                <img src="{{ asset('uploads/employee/' . $employee->Foto) }}" class="avatar-box" alt="Foto">
            @else
                <div class="avatar-box d-flex align-items-center justify-content-center text-white" style="font-size: 24px; font-weight: 700;">
                    {{ strtoupper(substr($employee->Name ?? 'E', 0, 1)) }}
                </div>
            @endif
            <div class="flex-grow-1">
                <div class="fw-bold fs-6 text-dark" style="line-height: 1.25;">{{ $employee->Name ?? 'Tanpa Nama' }}</div>
                <div class="text-muted small">{{ $employee->Position ?? 'Belum ditentukan' }}</div>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    @if($isOwnProfile)
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size: 10px;">
                            <i class="fa fa-user-check me-1"></i> Profil Saya
                        </span>
                    @elseif($isHcga)
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 10px;">
                            <i class="fa fa-shield-halved me-1"></i> Admin HCGA
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Finora Emerald Status Card -->
        <div class="finora-hero mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small opacity-75">Identitas Kepegawaian</span>
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-1" style="font-size: 11px;">
                    NIK: {{ $employee->NIK ?? '-' }}
                </span>
            </div>
            <div class="fw-bold fs-6 mb-1">{{ $employee->Department ?? 'N/A' }}</div>
            <div class="small opacity-75">
                <i class="fas fa-location-dot me-1"></i> {{ $employee->Location ?? 'Head Office' }}
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
            <div class="spinner-border text-success mb-2" role="status"></div>
            <span class="small">Memuat data profil...</span>
        </div>
    </div>

    @if($isOwnProfile || $isHcga)
    <!-- Modal Pengajuan / Edit Perubahan Data -->
    <div class="modal fade" id="modalRequestUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #064E3B 0%, #047857 100%);">
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
                        <button type="submit" class="btn btn-sm {{ $isHcga ? 'btn-dark' : 'btn-success' }} px-3" id="btnSubmitUpdate">
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
                    <div class="spinner-border text-success mb-2" role="status"></div>
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
    <!-- Account Security & Logout Section (Finora Profile Style) -->
    <div class="px-3 mt-3 mb-4" style="max-width: 480px; margin: 0 auto;">
        <div class="finora-card p-2">
            <a href="{{ route('employee.mobile.directory') }}" class="d-flex align-items-center justify-content-between p-2 text-decoration-none text-dark border-bottom">
                <span class="small fw-bold"><i class="fas fa-users text-success me-2"></i> Direktori Karyawan</span>
                <i class="fas fa-chevron-right text-muted small"></i>
            </a>
            <a href="{{ route('mobile.sessions') }}" class="d-flex align-items-center justify-content-between p-2 text-decoration-none text-dark border-bottom">
                <span class="small fw-bold"><i class="fas fa-shield-alt text-success me-2"></i> Keamanan &amp; Sesi Perangkat</span>
                <i class="fas fa-chevron-right text-muted small"></i>
            </a>
            <form method="POST" action="{{ route('mobile.logout') }}" class="p-2 mb-0">
                @csrf
                <button type="submit" class="finora-btn-soft w-100">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </button>
            </form>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'profile'])
</body>
</html>
