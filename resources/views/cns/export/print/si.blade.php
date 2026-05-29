{{--
    View ini di-render oleh ShippingInstructionController dan di-pass ke mPDF.
    TIDAK perlu <html>, <head>, <body> — mPDF handle sendiri.
    Header & footer gambar di-set via SetHTMLHeader/SetHTMLFooter di controller.
--}}

<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: Arial, sans-serif;
    font-size: 9pt;
    color: #000;
  }

  /* =============================================
     TITLE BLOCK
     ============================================= */
  .title-block {
    width: 100%;
    border: 1px solid #000;
    margin-bottom: 5px;
    border-collapse: collapse;
  }
  .logo-cell {
    width: 28mm;
    border-right: 1px solid #000;
    padding: 4px;
    text-align: center;
    vertical-align: middle;
  }
  .logo-cell img {
    width: 18mm;
    height: 18mm;
  }
  .title-right {
    vertical-align: top;
  }
  .title-heading {
    background-color: #FFD700;
    text-align: center;
    font-weight: bold;
    font-size: 11pt;
    padding: 5px;
    letter-spacing: 1px;
  }
  .meta-row td {
    border-top: 1px solid #000;
    border-right: 1px solid #000;
    padding: 3px 5px;
    font-size: 7pt;
    font-weight: bold;
    width: 25%;
  }
  .meta-row td:last-child { border-right: none; }

  /* =============================================
     INFO SECTION (2 kolom pakai tabel)
     ============================================= */
  .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
  }
  .info-table > tbody > tr > td {
    width: 50%;
    vertical-align: top;
    padding-right: 3px;
  }
  .info-table > tbody > tr > td:last-child {
    padding-right: 0;
    padding-left: 3px;
  }

  /* Party boxes */
  .party-box {
    border: 1px solid #000;
    margin-bottom: 4px;
  }
  .party-label {
    background-color: #000;
    color: #fff;
    font-weight: bold;
    font-size: 7.5pt;
    padding: 2px 5px;
    width: 100%;
  }
  .party-value {
    padding: 4px 5px;
    font-size: 8pt;
    line-height: 1.5;
    min-height: 14mm;
  }

  /* BL box */
  .bl-box {
    border: 1px solid #000;
  }
  .bl-table { width: 100%; border-collapse: collapse; }
  .bl-table td { padding: 2px 5px; font-size: 7.5pt; }
  .bl-table .lbl { font-weight: bold; white-space: nowrap; width: 35mm; }

  /* Field table kanan */
  .field-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    margin-bottom: 4px;
  }
  .field-table td {
    border: 1px solid #000;
    padding: 2px 5px;
    font-size: 7.5pt;
    vertical-align: middle;
  }
  .field-table .flbl {
    font-weight: bold;
    white-space: nowrap;
    width: 28mm;
    background: #f5f5f5;
  }

  /* =============================================
     GOODS CONDITION
     ============================================= */
  .goods-note {
    font-size: 7.5pt;
    margin-bottom: 4px;
  }

  /* =============================================
     MAIN CARGO TABLE
     ============================================= */
  .main-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    margin-bottom: 6px;
  }
  .main-table th {
    background-color: #e0e0e0;
    border: 1px solid #000;
    padding: 3px 4px;
    font-size: 7.5pt;
    text-align: center;
    vertical-align: middle;
  }
  .main-table td {
    border: 1px solid #000;
    padding: 2px 4px;
    font-size: 7.5pt;
    vertical-align: top;
  }
  .main-table .center { text-align: center; }
  .main-table .right  { text-align: right; }
  .main-table .total-row td {
    font-weight: bold;
    background: #f0f0f0;
  }

  /* Shipping mark */
  .shipping-mark {
    font-size: 6.5pt;
    line-height: 1.5;
    text-align: center;
  }
  .shipping-mark .red { color: #cc0000; font-weight: bold; }
  .desc-cell { font-size: 6.5pt; line-height: 1.5; }
  .desc-cell .bold-red { color: #cc0000; font-weight: bold; }

  /* =============================================
     SIGNATURE
     ============================================= */
  .signature-table {
    width: 65%;
    margin-left: 35%;
    border-collapse: collapse;
    margin-top: 6px;
  }
  .signature-table td {
    width: 50%;
    padding: 4px 6px;
    font-size: 8pt;
    text-align: center;
    vertical-align: top;
  }
  .sig-spacer { height: 18mm; }
  .sig-name { text-decoration: underline; font-weight: 500; }
  .sig-title { font-size: 7.5pt; }
</style>
<div style="padding: 0 35px;">
  {{-- =====================================================
       TITLE BLOCK
       ===================================================== --}}
  <table class="title-block" cellspacing="0" cellpadding="0">
    <tr>
      <td class="logo-cell">
        <img src="{{ $companies[$shipment->Shipper]->Logo }}" alt="Logo" width="100px">
      </td>
      <td class="title-right">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="title-heading" colspan="4">
              <div>
                {{ $shipment->Status == 'FINAL' && $shipment->Final_SI_No != null ? 'FINAL ' : '' }}SHIPMENT INSTRUCTION
              </div>
            </td>
          </tr>
          <tr class="meta-row">
            <td>Process Owner :</td>
            <td>Revision: #00</td>
            <td>Effective Date:</td>
            <td style="border-right:none;">Doc ID:</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  
  {{-- =====================================================
       INFO SECTION — 2 kolom (pakai tabel agar mPDF support)
       ===================================================== --}}
  <table class="info-table" cellspacing="0" cellpadding="0">
    <tr>
  
      {{-- Kolom Kiri: Shipper, Consignee, Notify, B/L --}}
      <td>
  
        <div class="party-box">
          <div class="party-label">SHIPPER</div>
          <div class="party-value">
            {{ $companies[$shipment->Shipper]->Company_Name }}<br>
            {{ $companies[$shipment->Shipper]->Address }}<br>
            {{ $companies[$shipment->Shipper]->City }}
          </div>
        </div>
  
        <div class="party-box">
          <div class="party-label">CONSIGNEE</div>
          <div class="party-value">
            {{ $companies[$shipment->Consignee]->Company_Name }}<br>
            {{ $companies[$shipment->Consignee]->Address }}<br>
            {{ $companies[$shipment->Consignee]->City }}
          </div>
        </div>
  
        <div class="party-box">
          <div class="party-label">NOTIFY PARTY</div>
          <div class="party-value">
            {{ $companies[$shipment->Notify]->Company_Name }}<br>
            {{ $companies[$shipment->Notify]->Address }}<br>
            {{ $companies[$shipment->Notify]->City }}
          </div>
        </div>
  
        <div class="bl-box">
          <table class="bl-table">
            <tr><td class="lbl">B/L No</td><td>{{ $shipment->BL_No }}</td></tr>
            <tr><td class="lbl">Port of Loading</td><td>{{ $shipment->POL }}</td></tr>
            <tr><td class="lbl">Port of Discharge</td><td>{{ $shipment->POD }}</td></tr>
          </table>
        </div>
  
      </td>
  
      {{-- Kolom Kanan: SI fields + Shipment fields --}}
      <td>
  
        <table class="field-table">
          <tr>
            <td class="flbl">{{ $shipment->Status == 'FINAL' && $shipment->Final_SI_No != null ? 'Final ' : '' }}SI No.</td>
            <td>{{ $shipment->Status == 'FINAL' && $shipment->Final_SI_No != null ? $shipment->Final_SI_No : $shipment->SI_No }}</td>
          </tr>
          <tr>
            <td class="flbl">Date</td>
            <td>{{ date('F, d Y', strtotime($shipment->SI_Date)) }}</td>
          </tr>
          <tr>
            <td class="flbl" style="vertical-align:top;padding-top:5px;">To</td>
            <td style="min-height:12mm;vertical-align:top;padding-top:5px;">
              {{ $companies[$shipment->Destination_Letter]->Company_Name }}<br>
              {{ $companies[$shipment->Destination_Letter]->Address }}
            </td>
          </tr>
          <tr>
            <td class="flbl">Terms of Delivery</td>
            <td>{{ $shipment->Delivery_Terms }}</td>
          </tr>
          <tr>
            <td class="flbl">Contract No</td>
            <td>{{ $shipment->Contract_No }}</td>
          </tr>
        </table>
  
        <table class="field-table" style="margin-top:4px;">
          <tr><td class="flbl">Shipped by</td><td>{{ $shipment->Shipped_By }}</td></tr>
          <tr><td class="flbl">Voyage</td><td>{{ $shipment->Voyage }}</td></tr>
          <tr><td class="flbl">ETD</td><td>{{ date('F, d Y', strtotime($shipment->ETD)) }}</td></tr>
          <tr><td class="flbl">ETA</td><td>{{ date('F, d Y', strtotime($shipment->ETA)) }}</td></tr>
          <tr><td class="flbl">Stuffing date</td><td>{{ date('F, d Y', strtotime($shipment->Stuffing)) }}</td></tr>
          <tr><td class="flbl">PEB</td><td>{{ $shipment->PEB }}</td></tr>
          <tr><td class="flbl">Date PEB</td><td>{{ date('F, d Y', strtotime($shipment->PEB_Date)) }}</td></tr>
          <tr><td class="flbl">Quantity</td><td>{{ $shipment->Qty }}</td></tr>
        </table>
  
        <div class="goods-note" style="margin-top:4px;">
          A.&nbsp;&nbsp;&nbsp;{{ Str::contains($shipment->Qty2, 'FT') ? 'Good, Clean and Suitable for Flexibag' : 'Good and Clean' }}
        </div>
  
      </td>
    </tr>
  </table>
  
  {{-- =====================================================
       MAIN CARGO TABLE
       ===================================================== --}}
  <table class="main-table" cellspacing="0" cellpadding="0">
    <thead>
      <tr>
        <th rowspan="2" style="width:35mm;">SEQ</th>
        <th rowspan="2">DESCRIPTION</th>
        <th style="width:22mm;">NET WEIGHT</th>
        <th style="width:22mm;">GROSS WEIGHT</th>
        <th style="width:20mm;">QTY</th>
        <th style="width:20mm;">QTY</th>
      </tr>
      <tr>
        <th>(KG)</th>
        <th>(KG)</th>
        <th>(KGS)</th>
        <th>(TON)</th>
      </tr>
    </thead>
    <tbody>
  
      {{-- Summary row --}}
      <tr>
        <td style="border-bottom:none;">
          <div class="shipping-mark">
            SHIPPING MARK: FREIGHT PREPAID<br>
            SHIPPED ON BOARD<br>
            <span class="red">
              {{ number_format($mt, 2, ',', '.') }} MT {{ strtoupper($shipment->Product_Name) }}<br>
              {{ Str::contains($shipment->Qty2, 'FT') ? 'PACKING IN FLEXIBAG' : '' }}
            </span><br>
            GW {{ number_format($gross, 0, ',', '.') }} KGS<br>
            NW {{ number_format($netto, 0, ',', '.') }} KGS<br>
            HS Code : {{ $shipment->HS_Code }}<br>
            COUNTRY OF ORIGIN: INDONESIA
          </div>
        </td>
        <td style="border-bottom:none;vertical-align:middle;">
          <div class="desc-cell">
            <span class="bold-red">
              {{ number_format($mt, 2, ',', '.') }} MT {{ strtoupper($shipment->Product_Name) }}
              &nbsp;{{ Str::contains($shipment->Qty2, 'FT') ? 'PACKING IN FLEXIBAG' : '' }}
            </span><br>
            GW {{ number_format($gross, 0, ',', '.') }} KGS<br>
            NW {{ number_format($netto, 0, ',', '.') }} KGS<br>
            HS Code : {{ $shipment->HS_Code }}<br>
            COUNTRY OF ORIGIN: INDONESIA
          </div>
        </td>
        <td style="border-bottom:none;" class="center">&nbsp;</td>
        <td style="border-bottom:none;" class="center">&nbsp;</td>
        <td style="border-bottom:none;" class="center">&nbsp;</td>
        <td style="border-bottom:none;" class="center">&nbsp;</td>
      </tr>
  
      {{-- Container rows --}}
      @foreach ($vsds as $item)
      <tr>
        <td style="text-align:center;border-top:none;border-bottom:none;">{{ $loop->iteration }}</td>
        <td style="border-top:none;border-bottom:none;">Container No. {{ $item->Container }} / {{ $item->Seal }}</td>
        <td style="border-top:none;border-bottom:none;" class="center">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
        <td style="border-top:none;border-bottom:none;" class="center">{{ number_format($item->Gross_KG, 0, ',', '.') }}</td>
        <td style="border-top:none;border-bottom:none;" class="center">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
        <td style="border-top:none;border-bottom:none;" class="center">{{ number_format(round($item->Netto_KG / 1000, 2), 2, ',', '.') }}</td>
      </tr>
      @endforeach
  
      {{-- Total --}}
      <tr class="total-row">
        <td colspan="2" class="right" style="padding-right:8px;">Total</td>
        <td class="center">{{ number_format($netto, 0, ',', '.') }}</td>
        <td class="center">{{ number_format($gross, 0, ',', '.') }}</td>
        <td class="center">{{ number_format($netto, 0, ',', '.') }}</td>
        <td class="center">{{ number_format(round($netto / 1000, 2), 2, ',', '.') }}</td>
      </tr>
  
    </tbody>
  </table>
  
  {{-- =====================================================
       SIGNATURES
       ===================================================== --}}
  <table class="signature-table" cellspacing="0" cellpadding="0">
    <tr>
      <td>Best Regards,</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="sig-spacer">&nbsp;</td>
      <td class="sig-spacer">&nbsp;</td>
    </tr>
    <tr>
      <td>
        <span class="sig-name">Muhammad Abdul Jabar</span><br>
        <span class="sig-title">Commercial and Sustainability Specialist</span>
      </td>
      <td>
        <span class="sig-name">Hani Septyana</span><br>
        <span class="sig-title">Commercial and Sustainability Manager</span>
      </td>
    </tr>
  </table>
</div>