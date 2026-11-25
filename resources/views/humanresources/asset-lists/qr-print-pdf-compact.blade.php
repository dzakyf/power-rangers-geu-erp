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
table.sticker-row {
    width: 100%;
    border-collapse: separate;
    border-spacing: 3.5mm;
    table-layout: fixed;
    page-break-inside: avoid;
    margin: 0 0 2mm 0;
}
table.sticker-row td.cell-box {
    width: 50%;
    padding: 0;
    vertical-align: top;
}
table.sticker-card {
    width: 100%;
    border-collapse: collapse;
    border: 1.2pt solid #007bff;
    background-color: #ffffff;
    table-layout: fixed;
    height: 48mm;
}
table.sticker-card td {
    vertical-align: middle;
    padding: 0;
}
.qr-col {
    width: 38mm;
    text-align: center;
    padding: 2mm 2.5mm !important;
    border-right: 0.8pt dashed #bce0fd;
    background-color: #fcfdfe;
}
.qr-image {
    width: 32mm;
    height: 32mm;
    display: block;
    margin: 0 auto;
}
.info-col {
    padding: 3mm 4mm !important;
    vertical-align: top !important;
}
.title-text {
    font-size: 8.5pt;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 2mm;
    border-bottom: 0.6pt solid #e0e0e0;
    padding-bottom: 1.5mm;
    line-height: 1.2;
}
.meta-text {
    font-size: 7pt;
    color: #333;
    line-height: 1.4;
}
.code-badge {
    display: inline-block;
    background-color: #e3f2fd;
    color: #0d47a1;
    padding: 1.5mm 2.5mm;
    border-radius: 2mm;
    font-family: Consolas, monospace;
    font-size: 6.8pt;
    font-weight: bold;
    margin-top: 2mm;
}
@elseif ($part === 'body')
@foreach ($selectedItems->chunk(2) as $chunk)
    <table class="sticker-row">
        <tr>
            @foreach ($chunk as $item)
                @php
                    $code = $mode === 'location' ? (string) $item->code : (string) $item->Asset_No;
                    $title = $mode === 'location'
                        ? (string) $item->name
                        : (string) ($item->Product_Description ?: $item->Asset_No);
                @endphp
                <td class="cell-box">
                    <table class="sticker-card">
                        <tr>
                            <td class="qr-col">
                                <img class="qr-image" src="{{ $qrImages[$code] }}" alt="{{ $code }}">
                            </td>
                            <td class="info-col">
                                <div class="title-text">{{ $title }}</div>
                                <div class="meta-text">
                                    @if ($mode === 'location')
                                        <strong>Tipe:</strong> ASSET LOCATION<br>
                                        <strong>Area:</strong> {{ $item->subtitle ?: '-' }}<br>
                                        <strong>Total Aset:</strong> {{ (int) $item->asset_count }} Unit
                                    @else
                                        <strong>Kategori:</strong> {{ $item->Asset_Sub_Category ?: ($item->Asset_Category ?: '-') }}<br>
                                        <strong>Lokasi:</strong> {{ $item->Asset_Location ?: '-' }}<br>
                                        <strong>SN/Plat:</strong> {{ $item->Registration_No ?: ($item->SN ?: '-') }}
                                    @endif
                                </div>
                                <div>
                                    <span class="code-badge">{{ $code }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            @endforeach
            @for ($i = $chunk->count(); $i < 2; $i++)
                <td class="cell-box"></td>
            @endfor
        </tr>
    </table>
@endforeach
@else
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stiker QR {{ $mode === 'location' ? 'Lokasi Asset' : 'Asset HCGA' }} (Compact)</title>
    <style>
        @include('humanresources.asset-lists.qr-print-pdf-compact', ['part' => 'css'])
    </style>
</head>
<body>
    @include('humanresources.asset-lists.qr-print-pdf-compact', ['part' => 'body'])
</body>
</html>
@endif
