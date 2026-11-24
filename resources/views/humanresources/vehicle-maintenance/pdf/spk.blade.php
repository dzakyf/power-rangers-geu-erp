<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK Perawatan Kendaraan - {{ $data->No_Trx }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            color: #222;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #1f4e78;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header h2 {
            margin: 0;
            color: #1f4e78;
            font-size: 16px;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 10px;
            color: #555;
        }
        .section-title {
            background: #eef3f8;
            color: #1f4e78;
            font-weight: bold;
            padding: 5px 8px;
            margin: 12px 0 6px;
            border-left: 3px solid #1f4e78;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.info td {
            padding: 4px 6px;
            border: 1px solid #d9d9d9;
            vertical-align: top;
        }
        table.info td.label {
            width: 22%;
            background: #fafafa;
            font-weight: bold;
        }
        table.approval th, table.approval td {
            padding: 5px 6px;
            border: 1px solid #d9d9d9;
            text-align: left;
            font-size: 10px;
        }
        table.approval th {
            background: #1f4e78;
            color: #fff;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            background: #1f4e78;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
        }
        .footer {
            margin-top: 18px;
            font-size: 9px;
            color: #777;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SURAT PERINTAH KERJA (SPK) PERAWATAN KENDARAAN</h2>
        <p>Human Capital &amp; General Affairs (HCGA) — PT Graha Energi Utama</p>
    </div>

    <div class="section-title">1. Informasi Pengajuan</div>
    <table class="info">
        <tr>
            <td class="label">No Transaksi</td>
            <td>{{ $data->No_Trx ?? '-' }}</td>
            <td class="label">Status</td>
            <td><span class="badge">{{ $data->Status ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="label">Tanggal Pengajuan</td>
            <td>{{ $data->Tanggal_Pengajuan ?: substr((string) $data->created_at, 0, 10) }}</td>
            <td class="label">Nomor Formulir</td>
            <td>{{ $data->Nomor_Formulir ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pengaju</td>
            <td>{{ $data->Nama_Pengaju ?? $data->User_Trx }}</td>
            <td class="label">Jabatan / Dept</td>
            <td>{{ $data->Jabatan ?? '-' }} / {{ $data->Department ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">2. Data Kendaraan</div>
    <table class="info">
        <tr>
            <td class="label">Asset No</td>
            <td>{{ $data->Asset_No ?? '-' }}</td>
            <td class="label">Plat Nomor</td>
            <td><strong>{{ $data->Plat_Nomor ?: '-' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nama Kendaraan</td>
            <td>{{ $data->Product_Description ?? '-' }}</td>
            <td class="label">Kategori / SN</td>
            <td>{{ $data->Kategori_Kendaraan ?: '-' }} / {{ $data->SN ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">KM Saat Pengajuan</td>
            <td>{{ number_format((int) ($data->Kilometer_Saat_Ini ?? 0), 0, ',', '.') }} km</td>
            <td class="label">KM Selesai</td>
            <td>{{ $data->Kilometer_Selesai ? number_format((int) $data->Kilometer_Selesai, 0, ',', '.') . ' km' : '-' }}</td>
        </tr>
    </table>

    <div class="section-title">3. Rincian Pekerjaan &amp; Biaya</div>
    <table class="info">
        <tr>
            <td class="label">Jenis Perawatan</td>
            <td>{{ ucfirst((string) ($data->Jenis_Perawatan ?? '-')) }}</td>
            <td class="label">Vendor / Bengkel</td>
            <td>{{ $data->Vendor_Bengkel ?: '-' }}</td>
        </tr>
        @if(($data->Jenis_Perawatan ?? '') === 'preventif')
        <tr>
            <td class="label">Tipe Service</td>
            <td>{{ $data->Tipe_Service_Preventif ?: '-' }}</td>
            <td class="label">Interval Bulan</td>
            <td>{{ $data->Jadwal_Perawatan_Bulan ? $data->Jadwal_Perawatan_Bulan . ' bulan' : '-' }}</td>
        </tr>
        @else
        <tr>
            <td class="label">Kerusakan Ditemui</td>
            <td colspan="3">{{ $data->Kerusakan_Yang_Ditemui ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Perkiraan Penyebab</td>
            <td>{{ $data->Penyebab_Diperkirakan ?: '-' }}</td>
            <td class="label">Komponen Diganti</td>
            <td>{{ $data->Komponen_Yang_Diganti ?: '-' }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Kondisi Awal</td>
            <td colspan="3">{{ $data->Kondisi_Awal ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Estimasi Biaya</td>
            <td>Rp {{ number_format((float) ($data->Estimasi_Biaya ?? 0), 0, ',', '.') }}</td>
            <td class="label">Biaya Aktual</td>
            <td>{{ $data->Biaya_Aktual !== null ? 'Rp ' . number_format((float) $data->Biaya_Aktual, 0, ',', '.') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Catatan Penyelesaian</td>
            <td colspan="3">{{ $data->Catatan_Penyelesaian ?: '-' }}</td>
        </tr>
    </table>

    <div class="section-title">4. Riwayat Persetujuan</div>
    <table class="approval">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Stage / Level</th>
                <th>Approver</th>
                <th>Aksi</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($timeline as $item)
                <tr>
                    <td>{{ $item['acted_at'] ?: '-' }}</td>
                    <td>{{ $item['stage'] }}</td>
                    <td>{{ $item['actor'] }} ({{ $item['dept'] }})</td>
                    <td>{{ $item['action'] }}</td>
                    <td>{{ $item['notes'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada riwayat approval.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh sistem HCGA ERP pada {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
