<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Procurement</title>
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
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            animation: cardFadeIn 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .btn-back-soft {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }
        .btn-back-soft:hover {
            background-color: var(--emerald-soft);
            border-color: #A7F3D0;
            color: var(--emerald-dark);
        }
        .btn-back-soft:active {
            transform: scale(0.92);
        }
        .page-title {
            font-size: 17px;
            font-weight: 800;
            margin: 0;
        }
        .page-sub {
            font-size: 12px;
            color: var(--text-muted);
        }
        .proc-menu-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .proc-item-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 14px 16px;
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            opacity: 0;
            animation: cardFadeIn 0.42s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1),
                        box-shadow 0.22s ease,
                        border-color 0.22s ease;
        }
        .proc-menu-list .proc-item-card:nth-child(1) { animation-delay: 0.05s; }
        .proc-menu-list .proc-item-card:nth-child(2) { animation-delay: 0.10s; }
        .proc-menu-list .proc-item-card:nth-child(3) { animation-delay: 0.15s; }
        .proc-menu-list .proc-item-card:nth-child(4) { animation-delay: 0.20s; }
        .proc-menu-list .proc-item-card:nth-child(5) { animation-delay: 0.25s; }

        .proc-item-card:hover {
            transform: translateY(-2px);
            border-color: #C7D8D0;
            box-shadow: 0 8px 18px rgba(6, 78, 59, 0.07);
            color: var(--text-dark);
        }
        .proc-item-card:active {
            transform: scale(0.98);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }
        .proc-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .proc-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .proc-item-card:hover .proc-icon {
            transform: scale(1.08) rotate(-3deg);
        }
        .proc-chevron {
            color: #9CA3AF;
            font-size: 12px;
            transition: transform 0.22s ease, color 0.22s ease;
        }
        .proc-item-card:hover .proc-chevron {
            transform: translateX(4px);
            color: var(--emerald-primary);
        }
        .tone-emerald { background: #D1FAE5; color: #047857; }
        .tone-blue    { background: #DBEAFE; color: #2563EB; }
        .tone-purple  { background: #EDE9FE; color: #7C3AED; }
        .tone-amber   { background: #FEF3C7; color: #D97706; }
        .tone-teal    { background: #CCFBF1; color: #0D9488; }

        .proc-label {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
        }
        .proc-desc {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .top-bar,
            .proc-item-card,
            .proc-icon,
            .proc-chevron,
            .btn-back-soft {
                animation: none !important;
                transition: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>
    @php
        $menus = [
            [
                'title' => 'Procurement PR',
                'desc'  => 'Buat PR (Ops & Non-Ops) & kelola pengajuan',
                'icon'  => 'fa-file-alt',
                'tone'  => 'emerald',
                'url'   => url('mobile/mobile-pr-create'),
            ],
            [
                'title' => 'Edit PR',
                'desc'  => 'Attachment (foto, drawing, RAB), keterangan, Hold / Cancel',
                'icon'  => 'fa-edit',
                'tone'  => 'blue',
                'url'   => route('procurement.mobile.pr.edit.list'),
            ],
            [
                'title' => 'Approval PR',
                'desc'  => 'Edit PIC approval, Approved / Pending / Cancel & komentar',
                'icon'  => 'fa-check-double',
                'tone'  => 'purple',
                'url'   => route('procurement.mobile.pr.approval'),
            ],
            [
                'title' => 'Tracking PR',
                'desc'  => 'Workflow approval, status (On proses, Hold, Cancel) & Confirm PO',
                'icon'  => 'fa-route',
                'tone'  => 'amber',
                'url'   => route('procurement.mobile.pr.tracking'),
            ],
            [
                'title' => 'Good Received',
                'desc'  => 'Confirm PO, Claim, GR, BAST & surat jalan penerimaan',
                'icon'  => 'fa-boxes',
                'tone'  => 'teal',
                'url'   => url('mobile/procurement/po/good-received'),
            ],
        ];
    @endphp

    <div class="mobile-container">
        <div class="top-bar">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('mobile.dashboard') }}" class="btn-back-soft" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="page-title">Procurement</h1>
                    <div class="page-sub">Alur PR (Ops / Non-Ops), Edit, Approval, Tracking &amp; GR</div>
                </div>
            </div>
        </div>

        <div class="proc-menu-list">
            @foreach ($menus as $m)
                <a href="{{ $m['url'] }}" class="proc-item-card">
                    <div class="proc-item-left">
                        <span class="proc-icon tone-{{ $m['tone'] }}">
                            <i class="fas {{ $m['icon'] }}"></i>
                        </span>
                        <div>
                            <div class="proc-label">{{ $m['title'] }}</div>
                            <div class="proc-desc">{{ $m['desc'] }}</div>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right proc-chevron"></i>
                </a>
            @endforeach
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'dashboard'])
</body>
</html>