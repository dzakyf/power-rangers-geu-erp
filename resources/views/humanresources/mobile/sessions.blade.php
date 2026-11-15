<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Keamanan & Sesi Perangkat - HCGA Mobile</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #F6F8F7;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #111827;
            padding-bottom: 92px;
        }

        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            padding: 16px;
        }

        .header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .back-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1px solid #E5EBE8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #064E3B;
            text-decoration: none;
        }

        .security-banner {
            background: linear-gradient(135deg, #064E3B 0%, #047857 100%);
            color: #FFFFFF;
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 16px;
        }

        .token-card {
            background: #FFFFFF;
            border: 1px solid #E5EBE8;
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="mobile-container">
        <div class="header-bar">
            <a href="{{ route('mobile.dashboard') }}" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Keamanan &amp; Sesi Perangkat</h1>
            <span style="width:38px;"></span>
        </div>

        @if (session('status'))
            <div class="alert alert-success py-2 px-3 rounded-3 small">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger py-2 px-3 rounded-3 small">{{ session('error') }}</div>
        @endif

        <div class="security-banner">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold"><i class="fas fa-shield-alt me-1"></i> Laravel Sanctum Protection</span>
                <span class="badge bg-light text-success rounded-pill">{{ $tokens->count() }} Token Aktif</span>
            </div>
            <p class="small mb-0 opacity-75">
                Token API otomatis kadaluarsa dalam 7 hari. Cabut perangkat yang tidak dikenal segera.
            </p>
        </div>

        @if ($tokens->count() > 0)
            <form method="POST" action="{{ route('mobile.sessions.revoke-all') }}" class="mb-3"
                onsubmit="return confirm('Cabut seluruh token perangkat API Anda?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill fw-bold">
                    <i class="fas fa-ban me-1"></i> Cabut Semua Token Perangkat
                </button>
            </form>
        @endif

        @forelse ($tokens as $tok)
            <div class="token-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-bold small">
                            <i class="fas fa-mobile-alt text-success me-1"></i>
                            {{ $tok['name'] }}
                        </div>
                        <div class="text-muted" style="font-size: 11px;">
                            IP: {{ $tok['ip_address'] ?? '-' }} &bull;
                            Dipakai:
                            {{ $tok['last_used_at'] ? \Carbon\Carbon::parse($tok['last_used_at'])->diffForHumans() : 'Belum pernah' }}
                        </div>
                        <div class="mt-1">
                            @foreach ($tok['abilities'] as $ab)
                                <span class="badge bg-success bg-opacity-10 text-success"
                                    style="font-size: 10px;">{{ $ab }}</span>
                            @endforeach
                        </div>
                    </div>
                    <form method="POST" action="{{ route('mobile.sessions.revoke', $tok['id']) }}"
                        onsubmit="return confirm('Cabut token perangkat ini?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-pill fw-bold">
                            Cabut
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="token-card text-center text-muted small">
                Belum ada token API perangkat yang aktif.
            </div>
        @endforelse
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'profile'])
</body>

</html>
