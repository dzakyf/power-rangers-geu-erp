<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Directory - Mobile</title>
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
        .header-directory {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 20px 18px 24px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
        }
        .employee-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 14px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.15s ease;
        }
        .employee-card:active {
            transform: scale(0.98);
        }
        .avatar-directory {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e2e8f0;
            flex-shrink: 0;
        }
        .search-input-wrapper {
            position: relative;
            margin-top: 14px;
        }
        .search-input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .search-input-wrapper input {
            padding-left: 38px;
            border-radius: 25px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .search-input-wrapper input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        .search-input-wrapper input:focus {
            background: #fff;
            color: #1e293b;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
        }
        .filter-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding: 12px 14px 4px;
            scrollbar-width: none;
        }
        .filter-scroll::-webkit-scrollbar {
            display: none;
        }
        .filter-chip {
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            background: #fff;
            color: #64748b;
            border: 1px solid #cbd5e1;
            text-decoration: none;
        }
        .filter-chip.active {
            background: #1e3a8a;
            color: #fff;
            border-color: #1e3a8a;
        }
    </style>
</head>
<body>

    <!-- Header Directory -->
    <div class="header-directory">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold mb-0 text-white"><i class="fa fa-users-gear me-2"></i> Direktori Kontak Karyawan</h5>
                <small class="text-white-50">{{ $isHcga ? 'Mode Admin / HCGA Portal' : 'Buku Telepon & Kontak Internal' }}</small>
            </div>
            <a href="{{ route('employee.mobile.my-profile') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1" style="font-size: 12px;">
                <i class="fa fa-user me-1"></i> Profil Saya
            </a>
        </div>

        <!-- Search Form -->
        <form action="{{ route('employee.mobile.directory') }}" method="GET" class="search-input-wrapper">
            <i class="fa fa-magnifying-glass"></i>
            <input type="text" name="search" class="form-control" placeholder="Cari nama, NIK, jabatan, divisi..." value="{{ $filters['search'] ?? '' }}">
        </form>
    </div>

    <!-- Quick Department Filters -->
    <div class="filter-scroll">
        <a href="{{ route('employee.mobile.directory') }}" class="filter-chip {{ empty($filters['department']) ? 'active' : '' }}">Semua Dept</a>
        @foreach($departments as $dept)
            @if(!empty($dept))
            <a href="{{ route('employee.mobile.directory', array_merge($filters, ['department' => $dept])) }}" 
               class="filter-chip {{ ($filters['department'] ?? '') === $dept ? 'active' : '' }}">
                {{ $dept }}
            </a>
            @endif
        @endforeach
    </div>

    <!-- Employee List Cards -->
    <div class="container-fluid px-3 pt-2">
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="small text-muted">Total: <strong>{{ $employees->total() }}</strong> Karyawan</span>
            @if($isHcga)
                <span class="badge bg-warning text-dark font-monospace" style="font-size: 10px;">HCGA Mode: Full Access</span>
            @else
                <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px;">Public Directory Mode</span>
            @endif
        </div>

        @forelse($employees as $emp)
            <a href="{{ route('employee.mobile.my-profile', ['task_id' => $emp->Task_ID]) }}" class="employee-card">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($emp->Foto) && file_exists(public_path('uploads/employee/' . $emp->Foto)))
                        <img src="{{ asset('uploads/employee/' . $emp->Foto) }}" class="avatar-directory" alt="Foto">
                    @else
                        <div class="avatar-directory d-flex align-items-center justify-content-center text-secondary fw-bold" style="font-size: 18px;">
                            {{ strtoupper(substr($emp->Name ?? 'E', 0, 1)) }}
                        </div>
                    @endif

                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark text-truncate" style="font-size: 14px;">{{ $emp->Name ?? '-' }}</span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2" style="font-size: 10px;">{{ $emp->NIK }}</span>
                        </div>
                        <div class="text-secondary small text-truncate mb-1" style="font-size: 12px;">
                            <i class="fa fa-briefcase me-1 text-muted"></i> {{ $emp->Position ?? 'Staff' }}
                        </div>
                        <div class="d-flex gap-1">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 10px;">
                                {{ $emp->Department ?? 'N/A' }}
                            </span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 10px;">
                                {{ $emp->Location ?? 'GEU' }}
                            </span>
                            @if(!empty($emp->Handphone))
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 10px;">
                                <i class="fab fa-whatsapp"></i> WA Aktif
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-muted"><i class="fa fa-chevron-right" style="font-size: 12px;"></i></div>
                </div>
            </a>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fa fa-user-slash d-block mb-2" style="font-size: 32px; color: #cbd5e1;"></i>
                <div class="fw-bold">Tidak ada karyawan yang ditemukan</div>
                <div class="small">Coba ubah kata kunci atau filter pencarian Anda.</div>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $employees->appends($filters)->links() }}
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('adminor/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
