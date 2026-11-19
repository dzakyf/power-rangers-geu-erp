<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $poHeader->PO_No }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.35;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #444;
            padding: 5px 6px;
        }
        .header-green {
            background-color: #2e7d32;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .sub-green {
            background-color: #e8f5e9;
            font-weight: bold;
            color: #1b5e20;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .no-border td { border: none; }
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            background-color: #e8f5e9;
            border: 1px solid #2e7d32;
            color: #2e7d32;
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- JUDUL & INFO DOKUMEN PO -->
    <table>
        <tr>
            <td style="width: 52%; text-align: center; vertical-align: middle;">
                <span style="font-size: 16px; font-weight: bold; color: #2e7d32; letter-spacing: 0.5px;">PURCHASE ORDER (PO)</span><br>
                <span style="font-size: 9px; color: #555;">Dokumen Resmi Pemesanan Barang / Jasa</span>
            </td>
            <td style="width: 25%; vertical-align: top;">
                <strong>No. PO:</strong><br>
                <span style="color: #1b5e20; font-weight: bold;">{{ $poHeader->PO_No }}</span><br><br>
                <strong>Ref. PR:</strong><br>
                {{ $poHeader->PR_No ?? '-' }}
            </td>
            <td style="width: 23%; vertical-align: top;">
                <strong>Tanggal PO:</strong><br>
                {{ !empty($poHeader->PO_Date) ? date('d M Y', strtotime($poHeader->PO_Date)) : '-' }}<br><br>
                <strong>Termin / Mata Uang:</strong><br>
                {{ $poHeader->Term_Payment ?? 'NET 30' }} ({{ $poHeader->Currency ?? 'IDR' }})
            </td>
        </tr>
    </table>

    <!-- INFORMASI VENDOR & ALAMAT PENGIRIMAN -->
    <table>
        <tr class="sub-green">
            <td style="width: 50%;">KEPADA YTH. (VENDOR / SUPPLIER):</td>
            <td style="width: 50%;">ALAMAT PENGIRIMAN & KONTAK PIC:</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <strong>{{ $poHeader->Company_Name ?? $poHeader->Supplier }}</strong> ({{ $poHeader->Supplier }})<br>
                UP / PIC Vendor : {{ $poHeader->Supplier_PIC ?? $poHeader->Contact_Person ?? '-' }}<br>
                Telepon : {{ $poHeader->Supplier_Phone ?? '-' }}<br>
                Email : {{ $poHeader->Supplier_Email ?? '-' }}
            </td>
            <td style="vertical-align: top;">
                <strong>Site / Lokasi Tujuan : {{ $poHeader->PO_Dest ?? $poHeader->Location ?? '-' }}</strong><br>
                Alamat Pengiriman : {{ $poHeader->PIC_Address ?? '-' }}<br>
                PIC Penerima : {{ $poHeader->PIC ?? '-' }} ({{ $poHeader->Department ?? '-' }})<br>
                Telp PIC : {{ $poHeader->PIC_Phone ?? '-' }}
            </td>
        </tr>
    </table>

    <p style="margin: 6px 0 8px 0;">
        Dengan hormat, mohon dapat dikirimkan barang / jasa sesuai dengan rincian pesanan berikut ini:
    </p>

    <!-- TABEL RINCIAN ITEM PO -->
    <table>
        <thead>
            <tr class="header-green">
                <th style="width: 5%;">No.</th>
                <th style="width: 14%;">Kode Item</th>
                <th style="width: 34%;">Deskripsi Barang / Jasa</th>
                <th style="width: 7%;">Qty</th>
                <th style="width: 8%;">Satuan</th>
                <th style="width: 15%;">Harga Satuan (Rp)</th>
                <th style="width: 17%;">Total Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poItems as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center"><strong>{{ $item->Product_Code }}</strong></td>
                <td class="text-left">
                    {{ $item->Product_Desc }}
                    @if(!empty($item->Specification))
                        <br><span style="font-size: 8.5px; color: #555;">Spek: {{ $item->Specification }}</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->Qty, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->Unit }}</td>
                <td class="text-right">{{ number_format($item->Price ?? 0, 0, ',', '.') }}</td>
                <td class="text-right"><strong>{{ number_format($item->Total ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach

            <!-- RINGKASAN NOMINAL -->
            <tr>
                <td colspan="6" class="text-right"><strong>Subtotal (Rp)</strong></td>
                <td class="text-right">{{ number_format($poHeader->Subtotal ?? 0, 0, ',', '.') }}</td>
            </tr>
            @if(($poHeader->Discount ?? 0) > 0)
            <tr>
                <td colspan="6" class="text-right"><strong>Diskon (Rp)</strong></td>
                <td class="text-right">- {{ number_format($poHeader->Discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="6" class="text-right"><strong>PPN (Rp)</strong></td>
                <td class="text-right">{{ number_format($poHeader->PPN ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><strong>Ongkos Kirim / Delivery Fee (Rp)</strong></td>
                <td class="text-right">{{ number_format($poHeader->Delivery_Fee ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="sub-green">
                <td colspan="6" class="text-right" style="font-size: 11px;"><strong>GRAND TOTAL PO (Rp)</strong></td>
                <td class="text-right" style="font-size: 11px;"><strong>Rp {{ number_format($poHeader->Total ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- SYARAT & KETENTUAN -->
    <table>
        <tr>
            <td>
                <strong>Syarat &amp; Ketentuan Purchase Order:</strong><br>
                1. Harap cantumkan Nomor PO (<strong>{{ $poHeader->PO_No }}</strong>) pada Surat Jalan (Delivery Order) dan Invoice.<br>
                2. Barang / jasa yang dikirimkan harus sesuai dengan spesifikasi dan kuantitas yang tertera pada PO ini.<br>
                3. Pembayaran diproses setelah barang diterima dengan baik (Goods Received) dan dokumen penagihan lengkap.<br>
                4. Catatan / Referensi: {{ $poHeader->Remark ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- KOLOM TANDA TANGAN -->
    <table class="no-border" style="margin-top: 25px; text-align: center;">
        <tr>
            <td style="width: 33%;">
                Dibuat Oleh,<br>
                <strong>Procurement / Purchasing</strong>
                <br><br><br><br><br>
                ( {{ $poHeader->User_Trx ?? '......................................' }} )
            </td>
            <td style="width: 34%;">
                Disetujui Oleh,<br>
                <strong>Management / Finance</strong>
                <br><br><br><br><br>
                ( ...................................... )
            </td>
            <td style="width: 33%;">
                Konfirmasi Vendor,<br>
                <strong>{{ $poHeader->Company_Name ?? $poHeader->Supplier }}</strong>
                <br><br><br><br><br>
                ( ...................................... )
            </td>
        </tr>
    </table>
</body>
</html>