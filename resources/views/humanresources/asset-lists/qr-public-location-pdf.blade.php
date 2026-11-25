<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Inventaris Aset - {{ $locationCode }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }
        table.header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5pt solid #0d3b66;
            margin-bottom: 4mm;
        }
        table.header-table td {
            vertical-align: middle;
            padding-bottom: 2.5mm;
        }
        .hdr-logo {
            width: 24mm;
        }
        .hdr-logo img {
            height: 11mm;
            max-width: 24mm;
        }
        .hdr-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0d3b66;
        }
        .hdr-sub {
            font-size: 8pt;
            color: #4b5563;
            margin-top: 1mm;
        }
        .hdr-meta {
            text-align: right;
            font-size: 7.5pt;
            color: #4b5563;
        }
        table.summary-box {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border: 0.6pt solid #cbd5e1;
            margin-bottom: 4mm;
        }
        table.summary-box td {
            padding: 2mm 3mm;
            font-size: 8pt;
            border-right: 0.5pt solid #e2e8f0;
        }
        table.asset-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
        }
        table.asset-table th {
            background-color: #0d3b66;
            color: #ffffff;
            font-weight: bold;
            padding: 2mm 1.5mm;
            border: 0.5pt solid #0d3b66;
            text-align: left;
        }
        table.asset-table td {
            padding: 1.8mm 1.5mm;
            border: 0.5pt solid #cbd5e1;
            vertical-align: top;
        }
        table.asset-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 0.5mm 1.5mm;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 2px;
            background: #e2e8f0;
            color: #1e293b;
        }
        .badge-ok {
            background: #dcfce7;
            color: #166534;
        }
        .badge-warn {
            background: #fee2e2;
            color: #991b1b;
        }
        .empty-box {
            border: 0.8pt dashed #94a3b8;
            padding: 8mm;
            text-align: center;
            color: #475569;
            font-size: 9pt;
            margin-top: 4mm;
        }
        .footer-note {
            margin-top: 3mm;
            font-size: 7pt;
            color: #64748b;
        }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td class="hdr-logo">
            @if (!empty($logo))
                <img src="{{ $logo }}" alt="Logo">
            @endif
        </td>
        <td>
            <div class="hdr-title">DAFTAR INVENTARIS ASET — LOKASI / SITE</div>
            <div class="hdr-sub">Human Capital &amp; General Affairs (Dokumen QR Publik Siap Cetak)</div>
        </td>
        <td class="hdr-meta">
            <strong>Dicetak:</strong> {{ $generatedAt }}<br>
            <strong>Total Aset:</strong> {{ $assets->count() }} Unit
        </td>
    </tr>
</table>

<table class="summary-box">
    <tr>
        <td style="width: 25%;">
            <span style="color:#64748b;">Kode Lokasi / Site:</span><br>
            <strong>{{ $locationCode }}</strong>
        </td>
        <td style="width: 35%;">
            <span style="color:#64748b;">Nama / Deskripsi Lokasi:</span><br>
            <strong>{{ $warehouse->Description ?? $locationCode }}</strong>
        </td>
        <td style="width: 20%;">
            <span style="color:#64748b;">Area / Site Group:</span><br>
            <strong>{{ $warehouse->Location ?? '-' }}</strong>
        </td>
        <td style="width: 20%; border-right: none;">
            <span style="color:#64748b;">Kota:</span><br>
            <strong>{{ $warehouse->City ?? '-' }}</strong>
        </td>
    </tr>
</table>

@if ($assets->isEmpty())
    <div class="empty-box">
        Belum ada data aset yang terdaftar pada lokasi <strong>{{ $locationCode }}</strong>.
    </div>
@else
    <table class="asset-table">
        <thead>
            <tr>
                <th style="width: 3.5%; text-align:center;">No</th>
                <th style="width: 11%;">Asset No</th>
                <th style="width: 11%;">Kategori / Sub</th>
                <th style="width: 20%;">Kode &amp; Deskripsi Produk</th>
                <th style="width: 11%;">Brand / Model</th>
                <th style="width: 11%;">SN / No Plat</th>
                <th style="width: 8%;">Dept</th>
                <th style="width: 9%;">PIC</th>
                <th style="width: 8%;">Status / Kondisi</th>
                <th style="width: 7.5%;">Lampiran / Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assets as $idx => $asset)
                @php
                    $attCount = isset($attachmentsByAsset[$asset->Asset_No]) ? count($attachmentsByAsset[$asset->Asset_No]) : 0;
                    $brandModel = trim(((string) ($asset->Product_Brand ?? '')) . ' ' . ((string) ($asset->Model ?? '')));
                    $snPlat = [];
                    if (!empty($asset->Registration_No)) {
                        $snPlat[] = 'Plat: ' . $asset->Registration_No;
                    }
                    if (!empty($asset->SN)) {
                        $snPlat[] = 'SN: ' . $asset->SN;
                    }
                    $kondisi = strtoupper((string) ($asset->Kondisi_Aset ?: 'BAIK'));
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $asset->Asset_No }}</strong></td>
                    <td>
                        {{ $asset->Asset_Category ?: '-' }}<br>
                        <span style="color:#475569;">{{ $asset->Asset_Sub_Category ?: '-' }}</span>
                    </td>
                    <td>
                        @if (!empty($asset->Product_Code))
                            <span style="font-family: Consolas, monospace; color:#0d3b66;">{{ $asset->Product_Code }}</span><br>
                        @endif
                        {{ $asset->Product_Description ?: '-' }}
                    </td>
                    <td>{{ $brandModel !== '' ? $brandModel : '-' }}</td>
                    <td>{{ !empty($snPlat) ? implode(' / ', $snPlat) : '-' }}</td>
                    <td>{{ $asset->Asset_Location_Department ?: '-' }}</td>
                    <td>{{ $asset->User_Responsible ?: '-' }}</td>
                    <td>
                        <span class="badge">{{ $asset->Status ?: '-' }}</span><br>
                        <span class="badge {{ $kondisi === 'RUSAK' ? 'badge-warn' : 'badge-ok' }}" style="margin-top:0.8mm;">
                            {{ $kondisi }}
                        </span>
                    </td>
                    <td>
                        @if ($attCount > 0)
                            <strong>{{ $attCount }} file</strong><br>
                        @endif
                        {{ $asset->Note ?: '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="footer-note">
    * Dokumen ini hanya menampilkan data operasional &amp; inventaris (data finansial/legal dikecualikan). Lampiran gambar &amp; PDF aset (jika tersedia) digabungkan pada halaman berikutnya.
</div>

</body>
</html>
