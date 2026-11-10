{{-- 
    Payroll Suite Hub Navigation
    Status: HIDDEN (Di-hide sementara agar tampilan lebih ringkas dan tidak membingungkan user).
    Untuk mengaktifkan kembali, ubah nilai $enablePayrollNav menjadi true.
--}}
@php
    $enablePayrollNav = false;
@endphp

@if($enablePayrollNav)
@php
    $currentActive = isset($activePage) ? $activePage : 'tax';
    $navItems = [
        [
            'key' => 'tax',
            'title' => 'Master Tax & Deduction',
            'subtitle' => 'TER & Ps. 17',
            'icon' => 'fa-calculator',
            'url' => url('payroll-tax'),
            'badge' => 'PPh 21',
            'badge_color' => 'bg-danger'
        ],
        [
            'key' => 'list',
            'title' => 'Master Benefits & Costs',
            'subtitle' => 'Komponen Gaji',
            'icon' => 'fa-list-check',
            'url' => url('payroll-list'),
            'badge' => null
        ],
        [
            'key' => 'group',
            'title' => 'Master Group',
            'subtitle' => 'Pengelompokan',
            'icon' => 'fa-layer-group',
            'url' => url('payroll-group'),
            'badge' => null
        ],
        [
            'key' => 'template',
            'title' => 'Master Template',
            'subtitle' => 'Template Payroll',
            'icon' => 'fa-file-invoice',
            'url' => url('payroll-template-master'),
            'badge' => null
        ],
        [
            'key' => 'employee',
            'title' => 'Salary Setup',
            'subtitle' => 'Setup Gaji Pegawai',
            'icon' => 'fa-user-gear',
            'url' => url('payroll-employee'),
            'badge' => 'Pegawai',
            'badge_color' => 'bg-info'
        ],
        [
            'key' => 'generator',
            'title' => 'Payroll Generator',
            'subtitle' => 'Proses Penggajian',
            'icon' => 'fa-bolt',
            'url' => url('payroll-generator'),
            'badge' => 'Generate',
            'badge_color' => 'bg-warning text-dark'
        ],
        [
            'key' => 'approval',
            'title' => 'Payroll Approval',
            'subtitle' => 'Verifikasi & Approval',
            'icon' => 'fa-clock-rotate-left',
            'url' => url('payroll-approval'),
            'badge' => 'Inbox',
            'badge_color' => 'bg-secondary'
        ],
        [
            'key' => 'approved',
            'title' => 'Approved Payroll',
            'subtitle' => 'Arsip & Slip Gaji',
            'icon' => 'fa-circle-check',
            'url' => url('approved-payroll'),
            'badge' => 'Disetujui',
            'badge_color' => 'bg-success'
        ],
    ];
@endphp

<!-- Payroll Suite Hub Navigation -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    <div class="card-body p-2 p-md-3">
        <div class="d-flex align-items-center justify-content-between mb-2 px-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill small">
                    <i class="fa fa-cubes me-1"></i> HCGA Payroll Suite
                </span>
                <span class="text-muted small d-none d-md-inline">Sistem Penggajian Terpadu & Terintegrasi Presensi</span>
            </div>
            <small class="text-muted fw-semibold">
                <i class="fa fa-circle text-success me-1 fa-fade"></i> TER PP 58/2023 & UU HPP
            </small>
        </div>
        <div class="d-flex flex-nowrap overflow-auto gap-2 pb-1" style="scrollbar-width: thin;">
            @foreach($navItems as $item)
                @php
                    $isActive = ($currentActive === $item['key']);
                @endphp
                <a href="{{ $item['url'] }}" 
                   class="btn {{ $isActive ? 'btn-primary text-white shadow-sm' : 'btn-outline-light text-dark border bg-light' }} text-start rounded-3 px-3 py-2 flex-shrink-0 text-decoration-none transition"
                   style="min-width: 170px;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <i class="fa {{ $item['icon'] }} {{ $isActive ? 'text-white' : 'text-primary' }} fs-5"></i>
                        @if(!empty($item['badge']))
                            <span class="badge {{ !empty($item['badge_color']) ? $item['badge_color'] : 'bg-primary' }} rounded-pill" style="font-size: 0.65rem;">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </div>
                    <div class="fw-bold text-truncate" style="font-size: 0.82rem;">{{ $item['title'] }}</div>
                    <div class="small {{ $isActive ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.72rem;">{{ $item['subtitle'] }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif
