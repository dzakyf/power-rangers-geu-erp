<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Pelatihan - {{ $certificate->certificate_no }}</title>
    <style>
        @page { margin: 20px; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .cert-border {
            border: 8px solid #1e3a8a;
            padding: 28px 40px;
            min-height: 610px;
            position: relative;
        }
        .cert-inner {
            border: 2px solid #cbd5e1;
            padding: 28px 36px;
            text-align: center;
        }
        .title {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #1e3a8a;
            margin-bottom: 6px;
        }
        .subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
        }
        .presented {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .participant-name {
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2px solid #1e3a8a;
            display: inline-block;
            padding: 4px 28px;
            margin-bottom: 16px;
        }
        .desc {
            font-size: 14px;
            line-height: 1.6;
            margin: 10px auto 22px auto;
            max-width: 720px;
        }
        .training-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
        }
        .footer-table {
            width: 100%;
            margin-top: 32px;
        }
        .footer-table td {
            vertical-align: bottom;
            text-align: center;
        }
        .qr-box {
            text-align: left;
            font-size: 10px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="cert-border">
        <div class="cert-inner">
            <div class="title">SERTIFIKAT PELATIHAN</div>
            <div class="subtitle">Nomor: {{ $certificate->certificate_no }}</div>

            <div class="presented">Diberikan kepada:</div>
            <div class="participant-name">{{ $participant->employee_name }}</div>
            <div style="font-size: 12px; color: #475569; margin-bottom: 16px;">
                {{ $participant->department ? 'Departemen: ' . $participant->department : '' }}
            </div>

            <div class="desc">
                Telah mengikuti dan dinyatakan <strong>LULUS EVALUASI</strong> pada program pelatihan:<br>
                <span class="training-title">{{ $training->Training_Name }}</span><br>
                Diselenggarakan pada tanggal {{ date('d M Y', strtotime($training->Start_Date)) }}
                s/d {{ date('d M Y', strtotime($training->End_Date)) }}
                dengan skor akhir <strong>{{ number_format($participant->final_score, 2) }}</strong>.
            </div>

            <table class="footer-table">
                <tr>
                    <td class="qr-box" style="width: 35%;">
                        <img src="{{ $qrBase64 }}" alt="QR Verification" style="width: 95px; height: 95px;"><br>
                        Scan QR untuk verifikasi keaslian sertifikat
                    </td>
                    <td style="width: 30%;"></td>
                    <td style="width: 35%;">
                        Diterbitkan pada: {{ date('d M Y', strtotime($certificate->issued_date)) }}<br><br><br>
                        <strong>_________________________</strong><br>
                        Human Capital &amp; General Affairs
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
