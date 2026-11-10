<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receipt - {{ $gr->gr_no }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0b3954;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 11px;
            color: #555;
        }
        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0 5px 0;
            text-decoration: underline;
        }
        .doc-no {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px;
            vertical-align: top;
            font-size: 11px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            font-size: 11px;
        }
        .items-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .sign-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
        }
        .sign-box {
            height: 70px;
        }
        .print-btn-bar {
            margin-bottom: 15px;
            text-align: right;
        }
        @media print {
            .print-btn-bar {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar">
        <button onclick="window.print()" style="padding: 6px 16px; font-weight: bold; cursor: pointer;">Cetak Dokumen</button>
    </div>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <div class="company-name">PT. GREEN ENERGI UTAMA</div>
                    <div class="company-sub">Pelindo</div>
                    <div class="company-sub">Jl. Perak Timur No.620, Perak Utara, Kec. Pabean Cantian, Surabaya, Jawa Timur 60165</div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom;">
                    <div style="font-size: 11px; font-weight: bold;">STATUS: {{ $gr->status }}</div>
                    <div style="font-size: 10px; color: #666;">Tgl Cetak: {{ date('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">BUKTI PENERIMAAN BARANG (GOODS RECEIPT SLIP)</div>
    <div class="doc-no">Nomor: {{ $gr->gr_no }}</div>

    <table class="meta-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">No. Purchase Order</td>
            <td style="width: 32%;">: {{ $gr->po_no }}</td>
            <td style="width: 18%; font-weight: bold;">Tanggal Terima</td>
            <td style="width: 32%;">: {{ $gr->receipt_date ? date('d/m/Y', strtotime($gr->receipt_date)) : '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Supplier / Vendor</td>
            <td>: {{ $supplier->Company_Name ?? ($po->Supplier ?? '-') }}</td>
            <td style="font-weight: bold;">No. Surat Jalan Vendor</td>
            <td>: {{ $gr->delivery_note_no ?: '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Supir / Ekspedisi</td>
            <td>: {{ $gr->driver_name ?: '-' }}</td>
            <td style="font-weight: bold;">No. Polisi Truk</td>
            <td>: {{ $gr->vehicle_no ?: '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Petugas Gudang</td>
            <td>: {{ $gr->received_by ?: '-' }}</td>
            <td style="font-weight: bold;">Catatan / Remark</td>
            <td>: {{ $gr->notes ?: '-' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Barang / Deskripsi</th>
                <th style="width: 120px;">Kategori</th>
                <th style="width: 90px;">Qty PO</th>
                <th style="width: 100px;">Qty Diterima</th>
                <th style="width: 60px;">Satuan</th>
                <th style="width: 150px;">Kondisi / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gr->details as $idx => $item)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold;">{{ $item->item_name }}</td>
                    <td style="text-align: center;">{{ $item->category ?: 'General' }}</td>
                    <td style="text-align: right;">{{ number_format($item->qty_ordered, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($item->qty_received, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $item->unit }}</td>
                    <td>{{ $item->notes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                <div>Diserahkan Oleh,</div>
                <div>(Pengemudi / Kurir)</div>
                <div class="sign-box"></div>
                <div>( {{ $gr->driver_name ?: '................................' }} )</div>
            </td>
            <td>
                <div>Diterima Oleh,</div>
                <div>(Petugas Gudang)</div>
                <div class="sign-box"></div>
                <div>( {{ $gr->received_by ?: '................................' }} )</div>
            </td>
            <td>
                <div>Mengetahui / Menyetujui,</div>
                <div>(Supervisor / Ka. Gudang)</div>
                <div class="sign-box"></div>
                <div>( {{ $gr->approved_by ?: '................................' }} )</div>
            </td>
        </tr>
    </table>
</body>
</html>
