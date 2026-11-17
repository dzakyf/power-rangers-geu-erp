<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Sertifikat Training HCGA</title>
    <link rel="stylesheet" href="{{ asset('adminor/assets/plugins/bootstrap/css/bootstrap.min.css') }}">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Verifikasi Sertifikat Digital Training HCGA</h5>
                    </div>
                    <div class="card-body">
                        @if($certificate)
                            <div class="alert alert-success">
                                <strong>VALID &amp; TERVERIFIKASI</strong> — Sertifikat ini resmi diterbitkan oleh HCGA.
                            </div>
                            <table class="table table-bordered table-sm">
                                <tr><th>No. Sertifikat</th><td>{{ $certificate->certificate_no }}</td></tr>
                                <tr><th>Nama Peserta</th><td>{{ $certificate->participant->employee_name ?? '-' }}</td></tr>
                                <tr><th>Departemen</th><td>{{ $certificate->participant->department ?? '-' }}</td></tr>
                                <tr><th>Program Pelatihan</th><td>{{ $certificate->training->Training_Name ?? '-' }}</td></tr>
                                <tr><th>Tanggal Pelatihan</th><td>{{ $certificate->training->Start_Date ?? '-' }} s/d {{ $certificate->training->End_Date ?? '-' }}</td></tr>
                                <tr><th>Tanggal Terbit</th><td>{{ $certificate->issued_date ? $certificate->issued_date->format('d M Y') : '-' }}</td></tr>
                            </table>
                        @else
                            <div class="alert alert-danger">
                                <strong>TIDAK DITEMUKAN</strong> — Token verifikasi sertifikat tidak valid atau tidak terdaftar.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
