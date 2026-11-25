<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Aset - {{ $asset->Asset_No }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            font-size: 8.5pt;
            margin: 0;
            padding: 0;
        }
        table.header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5pt solid #0d3b66;
            margin-bottom: 5mm;
        }
        table.header-table td {
            vertical-align: middle;
            padding-bottom: 3mm;
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
        .section-title {
            background-color: #0d3b66;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 2mm 3mm;
            margin-top: 4mm;
            margin-bottom: 0;
        }
        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }
        table.detail-table td {
            padding: 2mm 2.5mm;
            border: 0.5pt solid #cbd5e1;
            vertical-align: top;
        }
        table.detail-table td.lbl {
            width: 22%;
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
        }
        table.detail-table td.val {
            width: 28%;
            color: #111827;
        }
        .badge {
            display: inline-block;
            padding: 0.8mm 2mm;
            font-size: 7.5pt;
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
        table.att-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        table.att-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            text-align: left;
            padding: 1.8mm 2.5mm;
            border: 0.5pt solid #cbd5e1;
        }
        table.att-table td {
            padding: 1.8mm 2.5mm;
            border: 0.5pt solid #cbd5e1;
        }
        .footer-note {
            margin-top: 5mm;
            font-size: 7.2pt;
            color: #64748b;
            border-top: 0.5pt solid #e2e8f0;
            padding-top: 2mm;
        }
    </style>
</head>
<body>

@php
    $kondisi = strtoupper((string) ($asset->Kondisi_Aset ?: 'BAIK'));
    $isVehicle = stripos((string) ($asset->Asset_Category ?? ''), 'VEHICLE') !== false
        || stripos((string) ($asset->Asset_Sub_Category ?? ''), 'VEHICLE') !== false;
@endphp

<table class="header-table">
    <tr>
        <td class="hdr-logo">
            @if (!empty($logo))
                <img src="{{ $logo }}" alt="Logo">
            @endif
        </td>
        <td>
            <div class="hdr-title">LEMBAR INFORMASI ASET — {{ $asset->Asset_No }}</div>
            <div class="hdr-sub">Human Capital &amp; General Affairs (Dokumen QR Publik Siap Cetak)</div>
        </td>
        <td class="hdr-meta">
            <strong>Dicetak:</strong> {{ $generatedAt }}<br>
            <span class="badge">{{ $asset->Status ?: '-' }}</span>
            <span class="badge {{ $kondisi === 'RUSAK' ? 'badge-warn' : 'badge-ok' }}">{{ $kondisi }}</span>
        </td>
    </tr>
</table>

<div class="section-title">1. Identitas &amp; Spesifikasi Operasional</div>
<table class="detail-table">
    <tr>
        <td class="lbl">Asset No</td>
        <td class="val" colspan="3"><strong>{{ $asset->Asset_No }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Deskripsi Produk</td>
        <td class="val" colspan="3"><strong>{{ $asset->Product_Description ?: '-' }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Category</td>
        <td class="val">{{ $asset->Asset_Category ?: '-' }}</td>
        <td class="lbl">Sub Category</td>
        <td class="val">{{ $asset->Asset_Sub_Category ?: '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Asset Type</td>
        <td class="val">{{ $asset->Asset_Type ?: '-' }}</td>
        <td class="lbl">Sub Product Type</td>
        <td class="val">{{ $asset->Sub_Product_Type ?: '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Brand</td>
        <td class="val">{{ $asset->Product_Brand ?: '-' }}</td>
        <td class="lbl">Model / Unit</td>
        <td class="val">{{ trim(((string) ($asset->Model ?? '')) . ' ' . ((string) ($asset->Unit_Name ?? ''))) ?: '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Serial Number (SN)</td>
        <td class="val" @if (!$isVehicle) colspan="3" @endif>{{ $asset->SN ?: '-' }}</td>
        @if ($isVehicle)
            <td class="lbl">No Polisi / Plat</td>
            <td class="val">{{ $asset->Registration_No ?: '-' }}</td>
        @endif
    </tr>
</table>

<div class="section-title">2. Lokasi &amp; Penanggung Jawab</div>
<table class="detail-table">
    <tr>
        <td class="lbl">Asset Location / Site</td>
        <td class="val"><strong>{{ $asset->Asset_Location ?: '-' }}</strong></td>
        <td class="lbl">Department</td>
        <td class="val">{{ $asset->Asset_Location_Department ?: '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">User Responsible (PIC)</td>
        <td class="val">{{ $asset->User_Responsible ?: '-' }}</td>
        <td class="lbl">Kota</td>
        <td class="val">{{ $asset->Asset_Location_City ?: '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Alamat Lokasi</td>
        <td class="val" colspan="3">{{ $asset->Asset_Location_Address ?: '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Catatan (Note)</td>
        <td class="val" colspan="3">{{ $asset->Note ?: '-' }}</td>
    </tr>
</table>

<div class="section-title">3. Daftar Lampiran Dokumentasi ({{ count($attachments) }} File)</div>
@if (empty($attachments))
    <table class="att-table">
        <tr>
            <td style="color:#64748b;">Tidak ada lampiran foto/dokumen yang tersimpan untuk aset ini.</td>
        </tr>
    </table>
@else
    <table class="att-table">
        <thead>
            <tr>
                <th style="width: 7%; text-align:center;">No</th>
                <th style="width: 48%;">Nama File</th>
                <th style="width: 15%;">Format</th>
                <th style="width: 30%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attachments as $idx => $att)
                @php
                    $ext = strtoupper((string) ($att->Extension ?: pathinfo((string) ($att->Path ?? ''), PATHINFO_EXTENSION)));
                    $mergedInfo = in_array($ext, ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP', 'PDF'], true)
                        ? 'Digabungkan di halaman berikutnya'
                        : 'Dokumen non-gambar/non-PDF';
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $att->Filename ?: basename((string) ($att->Path ?? '')) }}</td>
                    <td>{{ $ext ?: '-' }}</td>
                    <td>{{ $mergedInfo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="footer-note">
    * Dokumen ini hanya menampilkan data operasional &amp; inventaris (informasi finansial/legal seperti nilai perolehan, leasing, rekening, dan BPKB tidak ditampilkan).
</div>

</body>
</html>
