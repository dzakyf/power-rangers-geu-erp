@php
    $part = $part ?? 'full';
@endphp
@if ($part === 'css')
body {
    font-family: Arial, sans-serif;
    color: #111;
    font-size: 8pt;
    margin: 0;
    padding: 0;
}
table.grid-row {
    width: 100%;
    border-collapse: separate;
    border-spacing: 4mm;
    table-layout: fixed;
    page-break-inside: avoid;
    margin: 0 0 2mm 0;
}
table.grid-row td {
    width: 50%;
    padding: 0;
    vertical-align: top;
}
.card-frame {
    border: 1.2pt solid #0d3b66;
    background-color: #ffffff;
    height: 82mm;
    padding: 0;
    box-sizing: border-box;
}
table.card-hdr {
    width: 100%;
    border-collapse: collapse;
    background-color: #f4f7fa;
    border-bottom: 1pt solid #0d3b66;
}
table.card-hdr td {
    padding: 3mm 3.5mm;
    vertical-align: middle;
}
.logo-box {
    width: 25mm;
    text-align: left;
}
.logo-box img {
    height: 9mm;
    max-width: 25mm;
}
.hdr-title {
    text-align: right;
    font-size: 7pt;
    font-weight: bold;
    color: #0d3b66;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
table.card-body {
    width: 100%;
    border-collapse: collapse;
    margin-top: 2.5mm;
}
table.card-body td {
    vertical-align: top;
    padding: 0 3mm;
}
.qr-col {
    width: 40mm;
    text-align: center;
}
.qr-img {
    width: 36mm;
    height: 36mm;
    display: block;
    margin: 0 auto;
    border: 0.5pt solid #ddd;
    padding: 1mm;
    background: #fff;
}
.qr-scan-hint {
    font-size: 6pt;
    color: #555;
    margin-top: 1.5mm;
    font-style: italic;
}
.info-col {
    padding-left: 2mm !important;
}
.point-name {
    font-size: 9.5pt;
    font-weight: bold;
    color: #0d3b66;
    line-height: 1.2;
    margin-bottom: 2mm;
}
.point-badge {
    display: inline-block;
    background-color: #e3ebf3;
    color: #0d3b66;
    font-size: 6.5pt;
    font-weight: bold;
    padding: 1mm 2mm;
    border-radius: 2px;
    margin-bottom: 2mm;
}
table.meta-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7pt;
    margin-top: 1mm;
}
table.meta-table td {
    padding: 0.8mm 1mm;
    border-bottom: 0.3pt dotted #ccc;
}
table.meta-table td.lbl {
    width: 32%;
    color: #666;
    font-weight: normal;
}
table.meta-table td.val {
    width: 68%;
    color: #111;
    font-weight: bold;
}
table.card-ftr {
    width: 100%;
    border-collapse: collapse;
    background-color: #f9fafb;
    border-top: 0.5pt solid #e5e7eb;
    margin-top: 2mm;
}
table.card-ftr td {
    padding: 1.5mm 3.5mm;
    font-size: 6pt;
    color: #777;
}
@elseif ($part === 'body')
@foreach ($selectedItems->chunk(2) as $chunk)
    <table class="grid-row">
        <tr>
            @foreach ($chunk as $item)
                @php
                    $code = $mode === 'location' ? (string) $item->code : (string) $item->Asset_No;
                    $title = $mode === 'location'
                        ? (string) $item->name
                        : (string) ($item->Product_Description ?: $item->Asset_No);
                    $badge = $mode === 'location'
                        ? 'ASSET LOCATION / SITE'
                        : strtoupper((string) ($item->Asset_Sub_Category ?: ($item->Asset_Category ?: 'ASSET')));
                @endphp
                <td>
                    <div class="card-frame">
                        <table class="card-hdr">
                            <tr>
                                <td class="logo-box">
                                    <img src="{{ $logo ?? public_path('assets/images/small-logo.png') }}" alt="Logo" height="40" width="40">
                                </td>
                                <td class="hdr-title">
                                    Asset Management HCGA<br>
                                    <span style="font-size: 5.5pt; color: #666; font-weight: normal;">
                                        {{ $mode === 'location' ? 'QR Inventaris Lokasi' : 'QR Identitas Aset' }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <table class="card-body">
                            <tr>
                                <td class="qr-col">
                                    <img class="qr-img" src="{{ $qrImages[$code] }}" alt="{{ $code }}">
                                    <div class="qr-scan-hint">Scan via kamera HP</div>
                                    <div style="font-family: Consolas, monospace; font-size: 6.8pt; font-weight: bold; margin-top: 1mm; color: #222;">
                                        {{ $code }}
                                    </div>
                                </td>
                                <td class="info-col">
                                    <div class="point-badge">{{ $badge }}</div>
                                    <div class="point-name">{{ $title }}</div>

                                    @if ($mode === 'location')
                                        <table class="meta-table">
                                            <tr>
                                                <td class="lbl">Kode Site</td>
                                                <td class="val">{{ $item->code }}</td>
                                            </tr>
                                            <tr>
                                                <td class="lbl">Area / Grup</td>
                                                <td class="val">{{ $item->subtitle ?: '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="lbl">Kota</td>
                                                <td class="val">{{ $item->city ?: '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="lbl">Jml Aset</td>
                                                <td class="val">{{ (int) $item->asset_count }} Unit</td>
                                            </tr>
                                        </table>
                                    @else
                                        @php
                                            $brandModel = trim(((string) ($item->Product_Brand ?? '')) . ' ' . ((string) ($item->Model ?? '')));
                                            $snOrPlat = $item->Registration_No ?: ($item->SN ?: '-');
                                        @endphp
                                        <table class="meta-table">
                                            <tr>
                                                <td class="lbl">Lokasi</td>
                                                <td class="val">{{ $item->Asset_Location ?: '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="lbl">Brand/Model</td>
                                                <td class="val">{{ $brandModel !== '' ? $brandModel : '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="lbl">SN / Plat</td>
                                                <td class="val">{{ $snOrPlat }}</td>
                                            </tr>
                                            <tr>
                                                <td class="lbl">Status</td>
                                                <td class="val">{{ $item->Status ?: '-' }} / {{ $item->Kondisi_Aset ?: 'BAIK' }}</td>
                                            </tr>
                                        </table>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <table class="card-ftr">
                            <tr>
                                <td style="text-align: left;">
                                    Scan QR untuk melihat PDF inventaris &amp; lampiran
                                </td>
                                <td style="text-align: right;">
                                    HCGA Dept
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            @endforeach
            @for ($i = $chunk->count(); $i < 2; $i++)
                <td></td>
            @endfor
        </tr>
    </table>
@endforeach
@else
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu QR {{ $mode === 'location' ? 'Lokasi Asset' : 'Asset HCGA' }}</title>
    <style>
        @include('humanresources.asset-lists.qr-print-pdf', ['part' => 'css'])
    </style>
</head>
<body>
    @include('humanresources.asset-lists.qr-print-pdf', ['part' => 'body'])
</body>
</html>
@endif
