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
  }
  .td-data {
    font-size: 7.5pt;
    padding: 1.5px 4px;
    vertical-align: top;
  }
  .total-td {
    font-weight: bold;
    background-color: #e0e0e0;
    font-size: 7.5pt;
    padding: 2.5px 4px;
  }

  /* =============================================
     SIGNATURE
     ============================================= */
  .sig-name  { text-decoration: underline; font-weight: bold; font-size: 8pt; }
  .sig-title { font-size: 7.5pt; }
</style>

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
        @php
          $shipperLogo = $companies[$shipment->Shipper]->Logo ?? null;
          $logoPath = $shipperLogo ? public_path($shipperLogo) : null;
        @endphp
        @if ($logoPath && is_file($logoPath) && file_exists($logoPath))
          <img src="{{ $logoPath }}" alt="Logo" width="130px" height="55px">
        @endif
      </td>
      {{-- Heading --}}
      <td colspan="4" class="title-heading" style="border-bottom:1px solid #000;">
        PACKING LIST  
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
              {{ $companies[$shipment->Shipper]->Company_Name ?? '' }}<br>
              {!! nl2br(e(!empty($shipment->Shipper_Address) ? $shipment->Shipper_Address : ($companies[$shipment->Shipper]->Address ?? ''))) !!}<br>
              {{ $companies[$shipment->Shipper]->City ?? '' }}
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
              {{ $companies[$shipment->Consignee]->Company_Name ?? '' }}<br>
              {!! nl2br(e(!empty($shipment->Consignee_Address) ? $shipment->Consignee_Address : ($companies[$shipment->Consignee]->Address ?? ''))) !!}<br>
              {{ $companies[$shipment->Consignee]->City ?? '' }}
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
              {{ $companies[$shipment->Notify]->Company_Name ?? '' }}<br>
              {!! nl2br(e(!empty($shipment->Notify_Address) ? $shipment->Notify_Address : ($companies[$shipment->Notify]->Address ?? ''))) !!}<br>
              {{ $companies[$shipment->Notify]->City ?? '' }}
            </td>
          </tr>
        </table>
  
        {{-- B/L, Port of Loading, Port of Discharge --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;">
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:2.5px 5px;width:34mm;white-space:nowrap;">B/L No</td>
            <td style="font-size:7.5pt;padding:2.5px 5px;">{{ $shipment->BL_No }}</td>
          </tr>
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:2.5px 5px;white-space:nowrap;">Port of Loading</td>
            <td style="font-size:7.5pt;padding:2.5px 5px;">{{ $shipment->POL }}</td>
          </tr>
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:2.5px 5px;white-space:nowrap;">Port of Discharge</td>
            <td style="font-size:7.5pt;padding:2.5px 5px;">{{ $shipment->POD }}</td>
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
              {{ $shipment->Status == 'FINAL' && $shipment->Final_SI_No != null ? 'Final ' : '' }}SI No.
            </td>
            <td class="fval" style="border-bottom:1px solid #000;">
              {{ $shipment->Status == 'FINAL' && $shipment->Final_SI_No != null ? $shipment->Final_SI_No : $shipment->SI_No }}
            </td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Date</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->SI_Date ? date('F, d Y', strtotime($shipment->SI_Date)) : '-' }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;vertical-align:top;padding-top:3px;">To</td>
            <td class="fval" style="border-bottom:1px solid #000;vertical-align:top;padding-top:3px;height:10mm;line-height:1.35;">
              {{ $companies[$shipment->Destination_Port]->Company_Name ?? '' }}<br>
              {{ $companies[$shipment->Destination_Port]->Address ?? '' }}
            </td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Terms of Delivery</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->Delivery_Terms }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;">Contract No</td>
            <td class="fval">{{ $shipment->Contract_No }}</td>
          </tr>
        </table>
  
        {{-- Shipped by / Voyage / ETD / ETA / Stuffing / PEB --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;margin-bottom:4px;">
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Shipped by</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->Shipped_By }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Voyage</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->Voyage }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">ETD</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->ETD ? date('F, d Y', strtotime($shipment->ETD)) : '-' }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">ETA</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->ETA ? date('F, d Y', strtotime($shipment->ETA)) : '-' }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Stuffing date</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->Stuffing ? date('F, d Y', strtotime($shipment->Stuffing)) : '-' }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">PEB</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->PEB_No ?? $shipment->PEB ?? '-' }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Date PEB</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->PEB_Date ? date('F, d Y', strtotime($shipment->PEB_Date)) : '-' }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;">Quantity</td>
            <td class="fval">{{ $shipment->Qty }}</td>
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
              Container condition: A.&nbsp;&nbsp;&nbsp;{{ Str::contains($shipment->Qty2, 'FT') ? 'Good, Clean and Suitable for Flexibag' : 'Good and Clean' }}
            </td>
          </tr>
        </table>
  
      </td>
    </tr>
  </table>
  
  {{-- =====================================================
      3. MAIN CARGO TABLE
      ===================================================== --}}
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
      {{-- Summary row --}}
      @php
        $qtyCount = (int) ($shipment->Qty1 ?: ($vsds->isNotEmpty() ? $vsds->count() : 10));
        $packageUnit = Str::contains(strtoupper($shipment->Qty2 ?? ''), 'ISO') ? 'ISOTANKS' : 'FLEXIBAGS';
        $packageSingle = Str::contains(strtoupper($shipment->Qty2 ?? ''), 'ISO') ? 'IN ISOTANK' : 'IN FLEXIBAG';
        $productTitle = !empty($shipment->Product_Name) ? strtoupper($shipment->Product_Name) : 'REFINED USED COOKING OIL';
        $incotermLine = trim(($shipment->Delivery_Terms ?? '') . ' ' . ($shipment->POD ?? ''));
        $incotermStr = !empty($incotermLine) ? strtoupper($incotermLine) : 'CIF PASIR GUDANG, MALAYSIA';
      @endphp
      <tr>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;vertical-align:middle;font-weight:bold;font-size:7.5pt;padding:4px 2px;">
          <!-- {{ $productTitle }} -->
        </td>
        <td class="td-data" style="border-right:1px solid #000;padding:4px 5px;line-height:1.35;font-size:7pt;font-weight:bold;">
          REFINED USED COOKING OIL
          <!-- INCOTERM: {{ $incotermStr }}<br>
          OCEAN FREIGHT PREPAID<br>
          {{ $qtyCount }} {{ $packageUnit }}<br>
          @if (!empty($shipment->Product_Name))
          PRODUCT: {{ strtoupper($shipment->Product_Name) }}<br>
          @else
          PRODUCT: -<br>
          @endif
          QUANTITY : {{ number_format($mt, 2, '.', ',') }} METRIC TONS<br>
          PACKING: {{ $packageSingle }}<br>
          GW: {{ number_format($gross, 2, '.', ',') }} KGS<br>
          NW: {{ number_format($netto, 2, '.', ',') }} KGS<br>
          HS CODE: {{ $shipment->HS_Code ?: '15119020' }}<br>
          COUNTRY OF ORIGIN: INDONESIA -->
        </td>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="border-right:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="text-align:center;">&nbsp;</td>
      </tr>

      {{-- Container rows — hanya border-right, tanpa border atas/bawah --}}
      @if ($vsds->isNotEmpty())
        @foreach ($vsds as $item)
        <tr>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ $loop->iteration }}</td>
          <td class="td-data" style="border-right:1px solid #000;padding-left:5px;padding-top:1.5px;padding-bottom:1.5px;">Container {{ $loop->iteration }} / {{ $item->Container ?: '...' }}{{ !empty($item->Seal) ? ' / ' . $item->Seal : '' }}</td>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ number_format($item->Gross_KG, 0, ',', '.') }}</td>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
          <td class="td-data" style="text-align:center;padding:1.5px 3px;">{{ number_format(round($item->Netto_KG / 1000, 2), 2, ',', '.') }}</td>
        </tr>
        @endforeach
      @elseif ($qtyCount > 0)
        @for ($i = 1; $i <= $qtyCount; $i++)
        <tr>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">{{ $i }}</td>
          <td class="td-data" style="border-right:1px solid #000;padding-left:5px;padding-top:1.5px;padding-bottom:1.5px;">Container {{ $i }} / ...</td>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">&nbsp;</td>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">&nbsp;</td>
          <td class="td-data" style="text-align:center;border-right:1px solid #000;padding:1.5px 3px;">&nbsp;</td>
          <td class="td-data" style="text-align:center;padding:1.5px 3px;">&nbsp;</td>
        </tr>
        @endfor
      @endif

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
      <td style="height:12mm;padding:2px 4px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="padding:2px 4px;font-size:7.5pt;text-align:center;">
        <span class="sig-name">Hani Septyana</span><br>
        <span class="sig-title">Commercial and Sustainability Manager</span>
      </td>
    </tr>
  </table>
</div>
