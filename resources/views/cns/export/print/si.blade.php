{{--
    View ini di-render oleh ShippingInstructionController dan di-pass ke mPDF.
    TIDAK perlu <html>, <head>, <body> — mPDF handle sendiri.
    Header & footer gambar di-set via SetHTMLHeader/SetHTMLFooter di controller.
 
    PENTING: mPDF tidak support CSS border pada <div> dengan baik.
    Semua box/border HARUS menggunakan <table> agar tampil konsisten.
--}}
 
<style>
  * { margin: 0; padding: 0; }
 
  body {
    font-family: Arial, sans-serif;
    font-size: 8.5pt;
    color: #000;
  }
 
  /* =============================================
     TITLE BLOCK
     ============================================= */
  .title-heading {
    text-align: center;
    font-weight: bold;
    font-size: 11pt;
    padding: 4px 5px;
    letter-spacing: 1px;
  }
 
  /* =============================================
     PARTY LABEL (header hitam dalam kotak)
     ============================================= */
  .party-label {
    background-color: #000;
    color: #fff;
    font-weight: bold;
    font-size: 7.5pt;
    padding: 2.5px 5px;
  }
 
  /* =============================================
     FIELD LABEL (kanan)
     ============================================= */
  .flbl {
    font-weight: bold;
    background: #f5f5f5;
    white-space: nowrap;
    width: 30mm;
    font-size: 7.5pt;
    padding: 2.5px 5px;
    vertical-align: middle;
  }
  .fval {
    font-size: 7.5pt;
    padding: 2.5px 5px;
    vertical-align: middle;
  }
 
  /* =============================================
     MAIN TABLE
     ============================================= */
  .th-gray {
    background-color: #e0e0e0;
    font-size: 7.5pt;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    padding: 2.5px 4px;
    /* border diset inline per-cell */
  }
  .td-data {
    font-size: 7.5pt;
    padding: 1.5px 4px;
    vertical-align: top;
    /* TIDAK ada border — hanya border-right diset inline */
  }
  .total-td {
    font-weight: bold;
    background-color: #e0e0e0;
    font-size: 7.5pt;
    padding: 2.5px 4px;
    /* border-top & border-right diset inline */
  }
 
  /* =============================================
     SHIPPING MARK & DESC
     ============================================= */
  .shipping-mark {
    font-size: 6.5pt;
    line-height: 1.6;
    text-align: center;
  }
  .red       { color: #cc0000; font-weight: bold; }
  .desc-cell { font-size: 6.5pt; line-height: 1.6; }
  .bold-red  { color: #cc0000; font-weight: bold; }
 
  /* =============================================
     SIGNATURE
     ============================================= */
  .sig-name  { text-decoration: underline; font-weight: bold; font-size: 8pt; }
  .sig-title { font-size: 7.5pt; }
</style>

@php
  $v = $print['values'];
  $shipper = $print['shipper'];
  $consignee = $print['consignee'];
  $notify = $print['notify'];
  $destination = $print['destination'];
@endphp

{{-- =====================================================
     WRAPPER — padding kiri kanan mengikuti margin mPDF
     ===================================================== --}}
<div style="margin:0 35px;">
  {{-- =====================================================
    1. TITLE BLOCK
    ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0"
        style="border:1px solid #000;border-collapse:collapse;margin-bottom:4px;">
    <tr>
      {{-- Logo --}}
      <td rowspan="2" width="28mm" style="border-right:1px solid #000;padding:4px;text-align:center;vertical-align:middle;">
        @if(!empty($shipper['logo_data_uri']))
          <img src="{{ $shipper['logo_data_uri'] }}" alt="Logo" width="130px" height="55px">
        @endif
      </td>
      {{-- Heading --}}
      <td colspan="4" class="title-heading" style="border-bottom:1px solid #000;">
        SHIPPING INSTRUCTION ({{ $v['status_label'] }})
      </td>
    </tr>
    <tr>
      {{-- Meta Cells --}}
      <td width="18%" style="border-right:1px solid #000;padding:2px 4px;font-size:7pt;font-weight:bold;">Process Owner :</td>
      <td width="18%" style="border-right:1px solid #000;padding:2px 4px;font-size:7pt;font-weight:bold;">Revision: #00</td>
      <td width="18%" style="border-right:1px solid #000;padding:2px 4px;font-size:7pt;font-weight:bold;">Effective Date:</td>
      <td width="18%" style="padding:2px 4px;font-size:7pt;font-weight:bold;">Doc ID:</td>
    </tr>
  </table>
  
  {{-- =====================================================
      2. INFO SECTION — 2 kolom
      ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0"
        style="border-collapse:collapse;margin-bottom:4px;">
    <tr>
  
      {{-- ── KOLOM KIRI ── --}}
      <td width="50%" style="vertical-align:top;padding-right:3px;">
  
        {{-- SHIPPER --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;margin-bottom:4px;">
          <tr>
            <td class="party-label">SHIPPER</td>
          </tr>
          <tr>
            <td style="padding:3px 5px;font-size:7.5pt;line-height:1.35;height:12mm;vertical-align:top;">
              {!! nl2br(e($shipper['name'])) !!}<br>
              {!! nl2br(e($shipper['address'])) !!}<br>
              {!! nl2br(e($shipper['city'])) !!}
            </td>
          </tr>
        </table>
  
        {{-- CONSIGNEE --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;margin-bottom:4px;">
          <tr>
            <td class="party-label">CONSIGNEE</td>
          </tr>
          <tr>
            <td style="padding:3px 5px;font-size:7.5pt;line-height:1.35;height:12mm;vertical-align:top;">
              {!! nl2br(e($consignee['name'])) !!}<br>
              {!! nl2br(e($consignee['address'])) !!}<br>
              {!! nl2br(e($consignee['city'])) !!}
            </td>
          </tr>
        </table>
  
        {{-- NOTIFY PARTY --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;margin-bottom:4px;">
          <tr>
            <td class="party-label">NOTIFY PARTY</td>
          </tr>
          <tr>
            <td style="padding:3px 5px;font-size:7.5pt;line-height:1.35;height:12mm;vertical-align:top;">
              {!! nl2br(e($notify['name'])) !!}<br>
              {!! nl2br(e($notify['address'])) !!}<br>
              {!! nl2br(e($notify['city'])) !!}
            </td>
          </tr>
        </table>
  
        {{-- B/L, Port of Loading, Port of Discharge --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;">
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:2.5px 5px;width:34mm;white-space:nowrap;">B/L No</td>
            <td style="font-size:7.5pt;padding:2.5px 5px;">{{ $v['bl_no'] }}</td>
          </tr>
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:2.5px 5px;white-space:nowrap;">Port of Loading</td>
            <td style="font-size:7.5pt;padding:2.5px 5px;">{{ $v['pol'] }}</td>
          </tr>
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:2.5px 5px;white-space:nowrap;">Port of Discharge</td>
            <td style="font-size:7.5pt;padding:2.5px 5px;">{{ $v['pod'] }}</td>
          </tr>
        </table>
  
      </td>
  
      {{-- ── KOLOM KANAN ── --}}
      <td width="50%" style="vertical-align:top;padding-left:3px;">
  
        {{-- SI / Date / To / Terms / Contract --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;margin-bottom:4px;">
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">
              {{ $print['is_final'] ? 'Final ' : '' }}SI No.
            </td>
            <td class="fval" style="border-bottom:1px solid #000;">
              {{ $v['si_no'] }}
            </td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Date</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['si_date'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;vertical-align:top;padding-top:3px;">To</td>
            <td class="fval" style="border-bottom:1px solid #000;vertical-align:top;padding-top:3px;height:10mm;line-height:1.35;">
              {!! nl2br(e($destination['name'])) !!}<br>
              {!! nl2br(e($destination['address'])) !!}
            </td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Terms of Delivery</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['delivery_terms'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;">Contract No</td>
            <td class="fval">{{ $v['contract_no'] }}</td>
          </tr>
        </table>
  
        {{-- Shipped by / Voyage / ETD / ETA / Stuffing / PEB --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;margin-bottom:4px;">
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Shipped by</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['shipped_by'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Voyage</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['voyage'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">ETD</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['etd'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">ETA</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['eta'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Stuffing date</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['stuffing'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">PEB</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['peb_no'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Date PEB</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $v['peb_date'] }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;">Quantity</td>
            <td class="fval">{{ $v['quantity'] }}</td>
          </tr>
        </table>
  
        {{-- Goods condition --}}
        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:2px;">
          <tr>
            <td style="font-size:7.5pt;padding:1px 0;font-weight:bold;">
              Remarks:
            </td>
          </tr>
          <tr>
            <td style="font-size:7.5pt;padding:1px 0;">
              Container condition: A.&nbsp;&nbsp;&nbsp;{{ Str::contains(strtoupper($v['package_type']), 'FT') ? 'Good, Clean and Suitable for Flexibag' : 'Good and Clean' }}
            </td>
          </tr>
        </table>
  
      </td>
    </tr>
  </table>
  
  {{-- =====================================================
      3. MAIN CARGO TABLE
      ===================================================== --}}
  {{--
    CARGO TABLE
    Kunci: $mpdf->splitTableBorderWidth = 1; di Controller
    <td> HANYA border-right, tanpa border top/bottom sama sekali.
    mPDF akan gambar garis bawah di akhir halaman dan garis atas
    di awal halaman berikutnya via splitTableBorderWidth.
  --}}
  <table width="100%" cellspacing="0" cellpadding="0"
        style="border-collapse:collapse;border:1px solid #000;margin-bottom:6px;">
    <thead>
      <tr>
        <td class="th-gray" style="width:38mm;border-right:1px solid #000;border-bottom:1px solid #000;" rowspan="2">SEQ</td>
        <td class="th-gray" style="border-right:1px solid #000;border-bottom:1px solid #000;" rowspan="2">DESCRIPTION</td>
        <td class="th-gray" style="width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">NET WEIGHT</td>
        <td class="th-gray" style="width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">GROSS WEIGHT</td>
        <td class="th-gray" style="width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">QTY</td>
        <td class="th-gray" style="width:21mm;border-bottom:1px solid #000;">QTY</td>
      </tr>
      <tr>
        <td class="th-gray" style="border-right:1px solid #000;border-bottom:1px solid #000;">(KG)</td>
        <td class="th-gray" style="border-right:1px solid #000;border-bottom:1px solid #000;">(KG)</td>
        <td class="th-gray" style="border-right:1px solid #000;border-bottom:1px solid #000;">(KGS)</td>
        <td class="th-gray" style="border-bottom:1px solid #000;">(TON)</td>
      </tr>
    </thead>
    <tbody>
  
      {{--
        KUNCI: Tidak ada border-top / border-bottom pada <td>.
        Hanya border-right sebagai pemisah kolom.
        Border luar tabel (atas, bawah, kiri, kanan) dihandle
        oleh atribut border="1" pada <table> — mPDF akan
        menggambar ulang border luar di setiap potongan halaman
        secara otomatis, termasuk garis bawah sebelum pindah halaman.
      --}}
  
      {{-- Summary row --}}
      @php
        $shipment = $print['shipment'] ?? null;
        $qtyCount = (int) (($shipment->Qty1 ?? null) ?: ($vsds->isNotEmpty() ? $vsds->count() : 10));
        $qty2 = $shipment->Qty2 ?? $v['package_type'] ?? '';
        $packageUnit = Str::contains(strtoupper($qty2), 'ISO') ? 'ISOTANKS' : 'FLEXIBAGS';
        $packageSingle = Str::contains(strtoupper($qty2), 'ISO') ? 'IN ISOTANK' : 'IN FLEXIBAG';
        $productTitle = !empty($v['Product_Name']) ? strtoupper($v['Product_Name']) : (!empty($shipment->Product_Name) ? strtoupper($shipment->Product_Name) : 'REFINED USED COOKING OIL');
        $deliveryTerms = $v['delivery_terms'] ?? ($shipment->Delivery_Terms ?? '');
        $pod = $v['pod'] ?? ($shipment->POD ?? '');
        $incotermLine = trim($deliveryTerms . ' ' . $pod);
        $incotermStr = !empty($incotermLine) ? strtoupper($incotermLine) : 'CIF PASIR GUDANG, MALAYSIA';
        $hsCode = !empty($v['hs_code']) ? $v['hs_code'] : (!empty($shipment->HS_Code) ? $shipment->HS_Code : '-');
      @endphp
      <tr>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;vertical-align:middle;font-weight:bold;font-size:7.5pt;padding:4px 2px;">
          {{ $productTitle }}
        </td>
        <td class="td-data" style="border-right:1px solid #000;padding:4px 5px;line-height:1.35;font-size:7pt;font-weight:bold;">
          INCOTERM: {{ $incotermStr }}<br>
          OCEAN FREIGHT PREPAID<br>
          {{ $qtyCount }} {{ $packageUnit }}<br>
          @if (!empty($v['Product_Name']) || !empty($shipment->Product_Name))
          PRODUCT: {{ $productTitle }}<br>
          @else
          PRODUCT: -<br>
          @endif
          QUANTITY : {{ number_format($mt, 2, '.', ',') }} METRIC TONS<br>
          PACKING: {{ $packageSingle }}<br>
          GW: {{ number_format($gross, 2, '.', ',') }} KGS<br>
          NW: {{ number_format($netto, 2, '.', ',') }} KGS<br>
          HS CODE: {{ $hsCode }}<br>
          COUNTRY OF ORIGIN: INDONESIA
        </td>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="text-align:center;">&nbsp;</td>
      </tr>
  
      {{-- Container rows — hanya border-right, tanpa border atas/bawah --}}
      @foreach ($vsds as $item)
      <tr>
        <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ $loop->iteration }}</td>
        <td class="td-data" style="border-right:1px solid #000;padding-left:5px;padding-top:1.5px;padding-bottom:1.5px;">Container No. {{ $item->Container }} / {{ $item->Seal }}</td>
        <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
        <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ number_format($item->Gross_KG, 0, ',', '.') }}</td>
        <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
        <td class="td-data" style="text-align:center;padding:1.5px 3px;">{{ number_format(round($item->Netto_KG / 1000, 2), 2, ',', '.') }}</td>
      </tr>
      @endforeach
  
      {{-- Total row — border-top untuk pemisah dari baris container --}}
      <tr>
        <td colspan="2" class="total-td" style="text-align:center;background-color:#e0e0e0;border-top:1px solid #000;border-right:1px solid #000;font-weight:bold;">TOTAL</td>
        <td class="total-td" style="text-align:center;background-color:#e0e0e0;border-top:1px solid #000;border-right:1px solid #000;font-weight:bold;">{{ number_format($netto, 0, ',', '.') }}</td>
        <td class="total-td" style="text-align:center;background-color:#e0e0e0;border-top:1px solid #000;border-right:1px solid #000;font-weight:bold;">{{ number_format($gross, 0, ',', '.') }}</td>
        <td class="total-td" style="text-align:center;background-color:#e0e0e0;border-top:1px solid #000;border-right:1px solid #000;font-weight:bold;">{{ number_format($netto, 0, ',', '.') }}</td>
        <td class="total-td" style="text-align:center;background-color:#e0e0e0;border-top:1px solid #000;font-weight:bold;">{{ number_format(round($netto / 1000, 2), 2, ',', '.') }}</td>
      </tr>
  
    </tbody>
  </table>
  
  {{-- =====================================================
      4. SIGNATURES
      ===================================================== --}}
  <table width="45%" cellspacing="0" cellpadding="0"
        style="border-collapse:collapse;margin-top:6px;">
    <tr>
      <td style="padding:2px 4px;font-size:7.5pt;text-align:center;">Best Regards,</td>
    </tr>
    <tr>
      <td style="height:15mm;padding:2px 4px;text-align:center;vertical-align:middle;">
        @if(!empty($print['approver_signature']) && in_array($v['status_label'], ['APPROVED', 'FINAL']))
          <img src="{{ $print['approver_signature'] }}" alt="Signature" style="max-height:14mm;max-width:45mm;">
        @else
          &nbsp;
        @endif
      </td>
    </tr>
    <tr>
      <td style="padding:2px 4px;font-size:7.5pt;text-align:center;">
        <span class="sig-name">{{ $print['approver']->Name ?? 'Hani Septyana' }}</span><br>
        <span class="sig-title">{{ !empty($print['approver']->Position) ? $print['approver']->Position : 'Commercial and Sustainability Manager' }}</span>
      </td>
    </tr>
  </table>
</div>