<style>
    * { margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 9pt; color: #000; }
    table { border-collapse: collapse; width: 100%; }
    .kop-doc td { border: 1px solid #000; }
    .title { text-align: center; font-weight: bold; font-size: 13pt; padding: 6px; color: #1b5e20; }
    .meta { font-size: 7.5pt; padding: 4px 6px; vertical-align: top; line-height: 1.4; }
    .info-box td { border: 1px solid #000; font-size: 8pt; padding: 5px 6px; vertical-align: top; }
    .info-head { background: #e8f5e9; font-weight: bold; }
    .items th, .items td { border: 1px solid #000; font-size: 8pt; padding: 5px; }
    .items th { background: #689f38; color: #fff; text-align: center; font-weight: bold; }
    .num { text-align: center; }
    .r { text-align: right; }
    .sig td { font-size: 8pt; text-align: center; padding: 6px; vertical-align: top; }
</style>

<div style="margin: 0 15px;">
    <!-- KOTAK JUDUL & METADATA DOKUMEN (MIRIP FORMAT PR) -->
    <table class="kop-doc" cellspacing="0" cellpadding="0">
        <tr>
            <td width="58%" class="title">REQUEST FOR QUOTATION (RFQ)</td>
            <td width="22%" class="meta">
                <strong>No. RFQ:</strong><br>{{ $rfq->RFQ_No }}<br>
                <strong>Ref. PR:</strong><br>{{ $rfq->PR_No }}
            </td>
            <td width="20%" class="meta">
                <strong>Tanggal RFQ:</strong><br>{{ date('d M Y', strtotime($rfq->RFQ_Date)) }}<br>
                <strong>Department:</strong><br>{{ $rfq->Department ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- INFORMASI VENDOR & LOKASI PENGIRIMAN -->
    <table class="info-box" cellspacing="0" cellpadding="0" style="margin-top: 8px;">
        <tr>
            <td width="50%" class="info-head">KEPADA YTH. (VENDOR / SUPPLIER):</td>
            <td width="50%" class="info-head">LOKASI PENGIRIMAN & KONTAK PIC:</td>
        </tr>
        <tr>
            <td>
                <strong>{{ $rfq->Company_Name ?? $rfq->Supplier_ID }}</strong> ({{ $rfq->Supplier_ID }})<br>
                Alamat : {{ $rfq->Supplier_Address ?? '-' }}<br>
                UP / CP : {{ $rfq->Contact_Person ?? '-' }}<br>
                Telp / Email : {{ $rfq->Phone ?? '-' }} / {{ $rfq->Email ?? '-' }}
            </td>
            <td>
                <strong>Site Tujuan :</strong> {{ $rfq->Site_Tujuan ?? '-' }}<br>
                PIC Pemohon : {{ $rfq->PIC ?? '-' }}<br>
                Telp PIC : {{ $rfq->PIC_Phone ?? '-' }}<br>
                Email PIC : {{ $rfq->PIC_Email ?? '-' }}
            </td>
        </tr>
    </table>

    <p style="font-size: 8pt; margin-top: 8px; margin-bottom: 5px;">
        Dengan hormat, mohon dapat diberikan penawaran harga terbaik beserta ketersediaan stok dan estimasi waktu pengiriman untuk item barang/jasa berikut:
    </p>

    <!-- TABEL ITEM BARANG / JASA -->
    <table class="items" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="14%">Item Code</th>
                <th width="26%">Description of Goods</th>
                <th width="18%">Specification</th>
                <th width="7%">Qty</th>
                <th width="8%">Unit</th>
                <th width="11%">Unit Price (Rp)</th>
                <th width="11%">Total Price (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td class="num"><strong>{{ $item->Product_Code }}</strong></td>
                <td>{{ $item->Product_Desc }}</td>
                <td>{{ $item->Specification ?: '-' }}</td>
                <td class="num">{{ $item->Qty }}</td>
                <td class="num">{{ $item->Unit }}</td>
                <td class="r" style="color: #777;">....................</td>
                <td class="r" style="color: #777;">....................</td>
            </tr>
            @endforeach
            @for($k = count($items); $k < 5; $k++)
            <tr>
                <td class="num">{{ $k + 1 }}</td>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
            </tr>
            @endfor
        </tbody>
    </table>

    <!-- SYARAT & CATATAN -->
    <div style="border: 1px solid #000; padding: 6px; margin-top: 8px; font-size: 7.5pt; background: #fafafa;">
        <strong>Syarat & Ketentuan Penawaran:</strong><br>
        1. Harap cantumkan Nomor Surat Penawaran (Quotation), masa berlaku harga (Validity), serta termin pembayaran (Payment Terms).<br>
        2. Mohon diinformasikan apakah harga sudah termasuk PPN 11% dan ongkos kirim ke lokasi tujuan.<br>
        @if(!empty($rfq->PR_Note))
            3. Catatan Tambahan: {{ $rfq->PR_Note }}
        @endif
    </div>

    <!-- KOLOM TANDA TANGAN -->
    <table class="sig" cellspacing="0" cellpadding="0" style="margin-top: 15px;">
        <tr>
            <td width="50%">
                Hormat Kami,<br>
                <strong>Procurement / Purchasing Dept.</strong>
                <br><br><br><br><br>
                ( ........................................ )<br>
                <small>PT. Green Energi Utama</small>
            </td>
            <td width="50%">
                Konfirmasi Penawaran Vendor,<br>
                <strong>{{ $rfq->Company_Name ?? 'Vendor / Supplier' }}</strong>
                <br><br><br><br><br>
                ( ........................................ )<br>
                <small>Nama, Tanda Tangan & Stempel Perusahaan</small>
            </td>
        </tr>
    </table>
</div>