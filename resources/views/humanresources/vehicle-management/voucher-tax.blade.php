<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Voucher Pengajuan Pajak STNK / KEUR - {{ $taxRequest->Voucher_No ?? $taxRequest->Tax_Request_No }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 24px; font-size: 13px; }
        .voucher-box { max-width: 780px; margin: 0 auto; border: 1px solid #333; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .meta { text-align: right; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f5f5f5; width: 32%; }
        .amount { font-size: 16px; font-weight: bold; color: #0d6efd; }
        .sign-grid { display: flex; justify-content: space-between; margin-top: 28px; text-align: center; }
        .sign-col { width: 30%; }
        .sign-line { border-top: 1px solid #333; margin-top: 56px; padding-top: 4px; font-size: 12px; }
        .no-print { margin-bottom: 14px; text-align: right; }
        @media print { .no-print { display: none; } body { margin: 0; } .voucher-box { border: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print();" style="padding:8px 16px;cursor:pointer;">Cetak / Simpan PDF</button>
    </div>

    <div class="voucher-box">
        <div class="header">
            <div>
                <div class="title">Voucher Pajak Kendaraan (STNK / KEUR)</div>
                <div>Human Capital &amp; General Affairs (HCGA)</div>
            </div>
            <div class="meta">
                <div><strong>No. Voucher:</strong> {{ $taxRequest->Voucher_No ?: '-' }}</div>
                <div><strong>No. Pengajuan:</strong> {{ $taxRequest->Tax_Request_No }}</div>
                <div><strong>Status:</strong> {{ $taxRequest->Status }}</div>
            </div>
        </div>

        <table>
            <tr>
                <th>No. Polisi / Asset No</th>
                <td><strong>{{ $taxRequest->Registration_No ?: '-' }}</strong> ({{ $taxRequest->Asset_No }})</td>
            </tr>
            <tr>
                <th>Kendaraan</th>
                <td>{{ trim(($taxRequest->Product_Description ?? '') . ' ' . ($taxRequest->Product_Brand ?? '') . ' ' . ($taxRequest->Model ?? '')) ?: '-' }}</td>
            </tr>
            <tr>
                <th>Lokasi / PIC</th>
                <td>{{ $taxRequest->Asset_Location ?: '-' }} / {{ $taxRequest->User_Responsible ?: '-' }}</td>
            </tr>
            <tr>
                <th>Jenis Dokumen</th>
                <td>{{ $taxRequest->Jenis_Pajak }}</td>
            </tr>
            <tr>
                <th>Tanggal Jatuh Tempo</th>
                <td>{{ $taxRequest->Tanggal_Jatuh_Tempo ?: '-' }}</td>
            </tr>
            <tr>
                <th>Estimasi Biaya</th>
                <td class="amount">Rp {{ number_format((float) $taxRequest->Estimasi_Biaya, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Pemohon / Tanggal</th>
                <td>{{ $taxRequest->Created_By }} / {{ $taxRequest->Created_At }}</td>
            </tr>
            @if(!empty($taxRequest->Tanggal_Bayar))
            <tr>
                <th>Tanggal Bayar / Bukti</th>
                <td>{{ $taxRequest->Tanggal_Bayar }} ({{ $taxRequest->Bukti_Bayar ?: '-' }})</td>
            </tr>
            @endif
        </table>

        @if(!empty($approvalLogs) && count($approvalLogs) > 0)
        <table>
            <thead>
                <tr>
                    <th>Tahap / Aksi</th>
                    <th>Oleh</th>
                    <th>Catatan</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvalLogs as $log)
                <tr>
                    <td>{{ $log->Action }}</td>
                    <td>{{ $log->Actor_Name ?: ($log->Actor_Username ?: '-') }}</td>
                    <td>{{ $log->Notes ?: '-' }}</td>
                    <td>{{ $log->Action_Date ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="sign-grid">
            <div class="sign-col">
                <div>Diajukan Oleh (GA)</div>
                <div class="sign-line">{{ $taxRequest->Created_By }}</div>
            </div>
            <div class="sign-col">
                <div>Disetujui Oleh (HCGA Mgr)</div>
                <div class="sign-line">Manager HCGA</div>
            </div>
            <div class="sign-col">
                <div>Diterima Oleh (Finance)</div>
                <div class="sign-line">Finance</div>
            </div>
        </div>
    </div>
</body>
</html>
