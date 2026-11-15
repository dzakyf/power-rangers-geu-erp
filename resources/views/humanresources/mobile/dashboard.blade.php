<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Landing Dashboard</title>
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --emerald-dark: #064E3B;
            --emerald-primary: #047857;
            --emerald-soft: #ECFDF5;
            --bg-canvas: #F6F8F7;
            --text-dark: #111827;
            --text-muted: #6B7280;
            --border-soft: #E5EBE8;
        }
        body {
            background-color: var(--bg-canvas);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            padding-bottom: 92px;
        }
        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            padding: 16px 16px 24px;
        }
        .top-greeting {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .greeting-sub {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .greeting-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.25;
        }
        .avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #064E3B 0%, #047857 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(4, 120, 87, 0.25);
        }
        .hero-emerald-card {
            background: linear-gradient(135deg, #064E3B 0%, #047857 65%, #0F766E 100%);
            color: #FFFFFF;
            border-radius: 22px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 26px rgba(6, 78, 59, 0.22);
            margin-bottom: 18px;
        }
        .hero-emerald-card::after {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            right: -35px;
            top: -35px;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.16);
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .hero-time-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 14px;
        }
        .hero-clock-val {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .quick-actions-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .quick-action-btn {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            padding: 12px 6px;
            text-align: center;
            text-decoration: none;
            color: var(--text-dark);
            font-size: 11px;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .quick-action-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--emerald-soft);
            color: var(--emerald-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
        }
        .module-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .module-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 14px;
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .module-card:active {
            transform: scale(0.98);
        }
        .mod-icon {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .tone-mint { background: #ECFDF5; color: #059669; }
        .tone-emerald { background: #D1FAE5; color: #047857; }
        .tone-blue { background: #DBEAFE; color: #2563EB; }
        .tone-amber { background: #FEF3C7; color: #D97706; }
        .tone-purple { background: #EDE9FE; color: #7C3AED; }
        .tone-coral { background: #FEE2E2; color: #DC2626; }
        .tone-teal { background: #CCFBF1; color: #0D9488; }
        .tone-slate { background: #F1F5F9; color: #475569; }

        .mod-label {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
        }
        .mod-sub {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .mod-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #DC2626;
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
        }
        .feed-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 10px;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .btn-logout-soft {
            background: #ECFDF5;
            color: #064E3B;
            border: 1px solid #A7F3D0;
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 13px;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    @php
        $u = $dashboard['user'];
        $att = $dashboard['attendance_today'];
        $initials = collect(explode(' ', trim($u['name'])))->filter()->map(function ($w) {
            return strtoupper(substr($w, 0, 1));
        })->take(2)->implode('');
        if ($initials === '') { $initials = 'U'; }
    @endphp

    <div class="mobile-container">
        <!-- Greeting Header -->
        <div class="top-greeting">
            <div>
                <div class="greeting-sub">Selamat Datang,</div>
                <div class="greeting-name">{{ $u['name'] }}</div>
                <div class="small text-muted">{{ $u['position'] }} &bull; {{ $u['department'] }}</div>
            </div>
            <a href="{{ route('employee.mobile.my-profile') }}" class="avatar-circle" title="Profil Saya">
                {{ $initials }}
            </a>
        </div>

        <!-- Hero Emerald Card (Attendance & Approval Overview) -->
        <div class="hero-emerald-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="hero-badge">
                    <i class="fas fa-calendar-day"></i>
                    <span>{{ \Carbon\Carbon::parse($att['date'])->format('d M Y') }}</span>
                </span>
                <span class="hero-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Sanctum Active ({{ $dashboard['active_tokens_count'] }})</span>
                </span>
            </div>
            <div class="hero-time-row">
                <div>
                    <div class="small opacity-75">
                        Absensi Hari Ini
                        @if ($att['status'] === 'CLOCKED_IN')
                            <span class="badge bg-white bg-opacity-25 text-white ms-1" style="font-size:10px;">Sudah Clock In</span>
                        @elseif ($att['status'] === 'CLOCKED_OUT')
                            <span class="badge bg-white bg-opacity-25 text-white ms-1" style="font-size:10px;">Selesai</span>
                        @else
                            <span class="badge bg-white bg-opacity-25 text-white ms-1" style="font-size:10px;">Belum Absen</span>
                        @endif
                    </div>
                    <div class="hero-clock-val">
                        {{ $att['clock_in'] ? substr((string) $att['clock_in'], 0, 5) : '--:--' }}
                        <span class="fs-6 fw-normal opacity-75">s/d</span>
                        {{ $att['clock_out'] ? substr((string) $att['clock_out'], 0, 5) : '--:--' }}
                    </div>
                </div>
                @php
                    $isDoneToday = !empty($att['clock_in']) && !empty($att['clock_out']);
                    $isClockOutAction = !empty($att['clock_in']) && empty($att['clock_out']);
                    $flowAction = $isClockOutAction ? 'clock-out' : 'clock-in';
                @endphp
                @if ($isDoneToday)
                    <a href="{{ route('attendance.mobile.index') }}"
                       class="btn btn-light btn-sm rounded-pill fw-bold text-success px-3">
                        <i class="fas fa-check-circle me-1"></i> Selesai
                    </a>
                @else
                    <a href="{{ route('attendance.mobile.location', ['action' => $flowAction]) }}"
                       class="btn btn-light btn-sm rounded-pill fw-bold text-success px-3">
                        <i class="fas fa-fingerprint me-1"></i>
                        {{ $isClockOutAction ? 'Clock Out' : 'Clock In' }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-row">
            <a href="{{ route('attendance.mobile.index') }}" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-fingerprint"></i></span>
                <span>Absen</span>
            </a>
            <a href="{{ route('mobile.ess') }}" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-briefcase"></i></span>
                <span>ESS</span>
            </a>
            <a href="{{ route('approval.mobile.index') }}" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-check-circle"></i></span>
                <span>Approve ({{ $dashboard['pending_approvals_count'] }})</span>
            </a>
            <a href="{{ route('employee.mobile.my-profile') }}" class="quick-action-btn">
                <span class="quick-action-icon"><i class="fas fa-user"></i></span>
                <span>Profil</span>
            </a>
        </div>

        <!-- Main Navigation Menu Grid -->
        <div class="section-header">
            <h2 class="section-title">Menu Utama HCGA</h2>
            <span class="small text-muted">{{ count($dashboard['modules']) }} Layanan</span>
        </div>

        <div class="module-grid">
            @foreach ($dashboard['modules'] as $mod)
                <a href="{{ route($mod['route_name']) }}" class="module-card">
                    <span class="mod-icon tone-{{ $mod['color'] }}">
                        <i class="fas {{ $mod['icon'] }}"></i>
                    </span>
                    <div>
                        <div class="mod-label">{{ $mod['label'] }}</div>
                        <div class="mod-sub">{{ $mod['subtitle'] }}</div>
                    </div>
                    @if (!empty($mod['badge']) && $mod['badge'] > 0)
                        <span class="mod-badge">{{ $mod['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- Latest Announcements -->
        <div class="section-header">
            <h2 class="section-title">Pengumuman Terbaru</h2>
            <a href="{{ route('announcement.mobile.index') }}" class="small fw-bold text-success text-decoration-none">Lihat Semua &rarr;</a>
        </div>

        @forelse ($dashboard['latest_announcements'] as $ann)
            <a href="{{ route('announcement.mobile.detail', $ann->id ?? $ann->ID) }}" class="feed-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-bold small">{{ $ann->title ?? $ann->Title ?? 'Pengumuman' }}</div>
                        <div class="text-muted" style="font-size: 11px;">
                            {{ $ann->category ?? $ann->Category ?? 'Info' }} &bull;
                            {{ isset($ann->created_at) ? \Carbon\Carbon::parse($ann->created_at)->format('d M Y') : '' }}
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small mt-1"></i>
                </div>
            </a>
        @empty
            <div class="feed-card text-center text-muted small">
                Belum ada pengumuman terbaru.
            </div>
        @endforelse

        <!-- Logout Action -->
        <form method="POST" action="{{ route('mobile.logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn-logout-soft">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log Out</span>
            </button>
        </form>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'dashboard'])
</body>
</html>
