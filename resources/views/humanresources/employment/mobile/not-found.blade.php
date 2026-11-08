<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Employee Profile Tidak Ditemukan</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card-box {
            background: #fff;
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="card-box">
        <div class="text-warning mb-3">
            <i class="fa fa-triangle-exclamation" style="font-size: 50px;"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Profil Karyawan Belum Terhubung</h5>
        <p class="text-muted small mb-4">
            {{ $message ?? 'Akun Anda belum memiliki data kepegawaian (employment) yang terdaftar di sistem ERP.' }}
        </p>

        @if($isHcga)
            <a href="{{ route('employee.mobile.directory') }}" class="btn btn-primary rounded-pill w-100 py-2">
                <i class="fa fa-users me-1"></i> Buka Direktori Karyawan
            </a>
        @else
            <a href="{{ url('/') }}" class="btn btn-outline-secondary rounded-pill w-100 py-2">
                <i class="fa fa-home me-1"></i> Kembali ke Beranda
            </a>
        @endif
    </div>
</body>
</html>

