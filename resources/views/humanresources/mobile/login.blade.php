<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Sign In</title>
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
            background: linear-gradient(180deg, #ECFDF5 0%, #F6F8F7 48%, #FFFFFF 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 16px;
        }
        .login-shell {
            width: 100%;
            max-width: 410px;
        }
        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 999px;
            padding: 7px 14px;
            font-weight: 700;
            font-size: 13px;
            color: var(--emerald-dark);
            box-shadow: 0 4px 12px rgba(6, 78, 59, 0.06);
        }
        .brand-pill i {
            color: var(--emerald-primary);
        }
        .hero-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        .hero-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 24px;
        }
        .auth-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 24px;
            padding: 24px 20px;
            box-shadow: 0 12px 32px rgba(6, 78, 59, 0.08);
        }
        .form-label-sm {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
        }
        .input-group-modern {
            position: relative;
        }
        .input-group-modern .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 14px;
        }
        .input-group-modern input {
            padding-left: 40px;
            padding-right: 40px;
            height: 46px;
            border-radius: 14px;
            border: 1px solid #D1D5DB;
            font-size: 14px;
            background: #F9FAFB;
        }
        .input-group-modern input:focus {
            background: #FFFFFF;
            border-color: var(--emerald-primary);
            box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.14);
        }
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #6B7280;
            padding: 4px;
        }
        .btn-emerald {
            background: linear-gradient(135deg, #064E3B 0%, #047857 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 999px;
            height: 48px;
            font-weight: 700;
            font-size: 15px;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(4, 120, 87, 0.25);
            transition: transform 0.15s ease;
        }
        .btn-emerald:active {
            transform: scale(0.98);
        }
        .security-note {
            margin-top: 18px;
            background: var(--emerald-soft);
            color: var(--emerald-dark);
            border-radius: 14px;
            padding: 10px 12px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="text-center">
            <span class="brand-pill">
                <i class="fas fa-leaf"></i>
                <span>HCGA Mobile Portal</span>
            </span>
            <h1 class="hero-title">Take Control of<br>Your Workday</h1>
            <p class="hero-subtitle">Akses Absensi, ESS, Approval, SPPD &amp; Reimbursement dalam satu portal aman.</p>
        </div>

        <div class="auth-card">
            @if (session('status'))
                <div class="alert alert-success py-2 px-3 rounded-3 small mb-3">
                    <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 rounded-3 small mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('mobile.login.submit') }}" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label-sm">Username</label>
                    <div class="input-group-modern">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control"
                               value="{{ old('username') }}" placeholder="Masukkan username Anda" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label-sm">Password</label>
                    <div class="input-group-modern">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Masukkan password" required>
                        <button type="button" class="toggle-pw" id="togglePwBtn" aria-label="Tampilkan Password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small text-muted" for="remember">Ingat perangkat ini</label>
                    </div>
                    <span class="small text-muted"><i class="fas fa-shield-alt text-success me-1"></i>Sanctum</span>
                </div>

                <button type="submit" class="btn-emerald">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="security-note">
                <i class="fas fa-lock"></i>
                <span>Dilindungi rate-limit, validasi akun aktif, dan audit sesi Laravel Sanctum.</span>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePwBtn').addEventListener('click', function () {
            var pw = document.getElementById('password');
            var icon = this.querySelector('i');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pw.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
