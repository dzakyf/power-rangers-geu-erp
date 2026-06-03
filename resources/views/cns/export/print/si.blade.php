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
    font-size: 9pt;
    color: #000;
  }
 
  /* =============================================
     TITLE BLOCK
     ============================================= */
  .title-heading {
    background-color: #FFD700;
    text-align: center;
    font-weight: bold;
    font-size: 11pt;
    padding: 5px;
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
    padding: 3px 6px;
  }
 
  /* =============================================
     FIELD LABEL (kanan)
     ============================================= */
  .flbl {
    font-weight: bold;
    background: #f5f5f5;
    white-space: nowrap;
    width: 32mm;
    font-size: 7.5pt;
    padding: 3px 5px;
    vertical-align: middle;
  }
  .fval {
    font-size: 7.5pt;
    padding: 3px 5px;
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
    padding: 3px 4px;
    border: 1px solid #000;
  }
  .td-data {
    font-size: 7.5pt;
    padding: 2px 4px;
    vertical-align: top;
    border: 1px solid #000;
  }
  .total-td {
    font-weight: bold;
    background: #f0f0f0;
    font-size: 7.5pt;
    padding: 3px 4px;
    border: 1px solid #000;
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

{{-- =====================================================
     WRAPPER — padding kiri kanan mengikuti margin mPDF
     ===================================================== --}}
<div style="margin:0 35px;">
  {{-- =====================================================
       1. TITLE BLOCK
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0"
        style="border:1px solid #000;border-collapse:collapse;margin-bottom:5px;">
    <tr>
      {{-- Logo --}}
      <td width="28mm" style="border-right:1px solid #000;padding:6px;text-align:center;vertical-align:middle;">
        <img src="{{ $companies[$shipment->Shipper]->Logo }}" alt="Logo" width="65px" height="65px">
      </td>
      {{-- Heading + Meta --}}
      <td style="vertical-align:top;">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="4" class="title-heading">
              {{ $shipment->Status == 'FINAL' && $shipment->Final_SI_No != null ? 'FINAL ' : '' }}SHIPPING INSTRUCTION
            </td>
          </tr>
          <tr>
            <td width="25%" style="border-top:1px solid #000;border-right:1px solid #000;padding:3px 5px;font-size:7pt;font-weight:bold;">Process Owner :</td>
            <td width="25%" style="border-top:1px solid #000;border-right:1px solid #000;padding:3px 5px;font-size:7pt;font-weight:bold;">Revision: #00</td>
            <td width="25%" style="border-top:1px solid #000;border-right:1px solid #000;padding:3px 5px;font-size:7pt;font-weight:bold;">Effective Date:</td>
            <td width="25%" style="border-top:1px solid #000;padding:3px 5px;font-size:7pt;font-weight:bold;">Doc ID:</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  
  {{-- =====================================================
      2. INFO SECTION — 2 kolom
      ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0"
        style="border-collapse:collapse;margin-bottom:5px;">
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
            <td style="padding:5px 6px;font-size:8pt;line-height:1.6;height:20mm;vertical-align:top;">
              {{ $companies[$shipment->Shipper]->Company_Name }}<br>
              {{ $companies[$shipment->Shipper]->Address }}<br>
              {{ $companies[$shipment->Shipper]->City }}
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
            <td style="padding:5px 6px;font-size:8pt;line-height:1.6;height:20mm;vertical-align:top;">
              {{ $companies[$shipment->Consignee]->Company_Name }}<br>
              {{ $companies[$shipment->Consignee]->Address }}<br>
              {{ $companies[$shipment->Consignee]->City }}
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
            <td style="padding:5px 6px;font-size:8pt;line-height:1.6;height:20mm;vertical-align:top;">
              {{ $companies[$shipment->Notify]->Company_Name }}<br>
              {{ $companies[$shipment->Notify]->Address }}<br>
              {{ $companies[$shipment->Notify]->City }}
            </td>
          </tr>
        </table>
  
        {{-- B/L, Port of Loading, Port of Discharge --}}
        <table width="100%" cellspacing="0" cellpadding="0"
              style="border:1px solid #000;border-collapse:collapse;">
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:3px 6px;width:38mm;white-space:nowrap;">B/L No</td>
            <td style="font-size:7.5pt;padding:3px 6px;">{{ $shipment->BL_No }}</td>
          </tr>
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:3px 6px;white-space:nowrap;">Port of Loading</td>
            <td style="font-size:7.5pt;padding:3px 6px;">{{ $shipment->POL }}</td>
          </tr>
          <tr>
            <td style="font-weight:bold;font-size:7.5pt;padding:3px 6px;white-space:nowrap;">Port of Discharge</td>
            <td style="font-size:7.5pt;padding:3px 6px;">{{ $shipment->POD }}</td>
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
            <td class="fval" style="border-bottom:1px solid #000;">{{ date('F, d Y', strtotime($shipment->SI_Date)) }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;vertical-align:top;padding-top:5px;">To</td>
            <td class="fval" style="border-bottom:1px solid #000;vertical-align:top;padding-top:5px;height:14mm;">
              {{ $companies[$shipment->Destination_Letter]->Company_Name }}<br>
              {{ $companies[$shipment->Destination_Letter]->Address }}
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
            <td class="fval" style="border-bottom:1px solid #000;">{{ date('F, d Y', strtotime($shipment->ETD)) }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">ETA</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ date('F, d Y', strtotime($shipment->ETA)) }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Stuffing date</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ date('F, d Y', strtotime($shipment->Stuffing)) }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">PEB</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ $shipment->PEB }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;border-bottom:1px solid #000;">Date PEB</td>
            <td class="fval" style="border-bottom:1px solid #000;">{{ date('F, d Y', strtotime($shipment->PEB_Date)) }}</td>
          </tr>
          <tr>
            <td class="flbl" style="border-right:1px solid #000;">Quantity</td>
            <td class="fval">{{ $shipment->Qty }}</td>
          </tr>
        </table>
  
        {{-- Goods condition --}}
        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
          <tr>
            <td style="font-size:7.5pt;padding:2px 0;">
              A.&nbsp;&nbsp;&nbsp;{{ Str::contains($shipment->Qty2, 'FT') ? 'Good, Clean and Suitable for Flexibag' : 'Good and Clean' }}
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
    KUNCI anti-terpotong di mPDF:
    1. Atribut "border" pada <table> → mPDF menggambar ulang border luar
      di setiap potongan halaman (atas & bawah).
    2. <thead> dengan repeat="1" → header kolom diulang di halaman berikutnya.
    3. Semua <td> pakai border 4 sisi → tidak ada garis yang hilang.
  --}}
  <table width="100%" cellspacing="0" cellpadding="0"
        border="1"
        style="border:1px solid #000;border-collapse:collapse;margin-bottom:6px;">
    <thead>
      <tr>
        <td class="th-gray" style="width:35mm;border-right:1px solid #000;border-bottom:1px solid #000;" rowspan="2">SEQ</td>
        <td class="th-gray" style="border-right:1px solid #000;border-bottom:1px solid #000;" rowspan="2">DESCRIPTION</td>
        <td class="th-gray" style="width:22mm;border-right:1px solid #000;border-bottom:1px solid #000;">NET WEIGHT</td>
        <td class="th-gray" style="width:22mm;border-right:1px solid #000;border-bottom:1px solid #000;">GROSS WEIGHT</td>
        <td class="th-gray" style="width:20mm;border-right:1px solid #000;border-bottom:1px solid #000;">QTY</td>
        <td class="th-gray" style="width:20mm;border-bottom:1px solid #000;">QTY</td>
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
        Summary row — border LENGKAP di semua sisi.
        Baris ini dan container rows pakai border penuh agar
        tidak ada garis yang hilang saat terpotong halaman.
      --}}
      <tr>
        <td class="td-data" style="border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">
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
        <td class="td-data" style="border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;vertical-align:middle;">
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
        <td class="td-data" style="border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">&nbsp;</td>
        <td class="td-data" style="border-top:1px solid #000;border-bottom:1px solid #000;text-align:center;">&nbsp;</td>
      </tr>
  
      {{-- Container rows — border LENGKAP semua sisi --}}
      @foreach ($vsds as $item)
      <tr>
        <td class="td-data" style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">{{ $loop->iteration }}</td>
        <td class="td-data" style="border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Container No. {{ $item->Container }} / {{ $item->Seal }}</td>
        <td class="td-data" style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
        <td class="td-data" style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">{{ number_format($item->Gross_KG, 0, ',', '.') }}</td>
        <td class="td-data" style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">{{ number_format($item->Netto_KG, 0, ',', '.') }}</td>
        <td class="td-data" style="text-align:center;border-top:1px solid #000;border-bottom:1px solid #000;">{{ number_format(round($item->Netto_KG / 1000, 2), 2, ',', '.') }}</td>
      </tr>
      @endforeach
  
      {{-- Total row --}}
      <tr>
        <td colspan="2" class="total-td" style="text-align:right;padding-right:8px;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Total</td>
        <td class="total-td" style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">{{ number_format($netto, 0, ',', '.') }}</td>
        <td class="total-td" style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">{{ number_format($gross, 0, ',', '.') }}</td>
        <td class="total-td" style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">{{ number_format($netto, 0, ',', '.') }}</td>
        <td class="total-td" style="text-align:center;border-top:1px solid #000;border-bottom:1px solid #000;">{{ number_format(round($netto / 1000, 2), 2, ',', '.') }}</td>
      </tr>
  
    </tbody>
  </table>
  
  {{-- =====================================================
      4. SIGNATURES
      ===================================================== --}}
  <table width="65%" cellspacing="0" cellpadding="0"
        style="margin-left:35%;border-collapse:collapse;margin-top:6px;">
    <tr>
      <td width="50%" style="padding:4px 6px;font-size:8pt;text-align:center;">Best Regards,</td>
      <td width="50%" style="padding:4px 6px;font-size:8pt;text-align:center;">&nbsp;</td>
    </tr>
    <tr>
      <td style="height:20mm;padding:4px 6px;">&nbsp;</td>
      <td style="height:20mm;padding:4px 6px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="padding:4px 6px;font-size:8pt;text-align:center;">
        <span class="sig-name">Muhammad Abdul Jabar</span><br>
        <span class="sig-title">Commercial and Sustainability Specialist</span>
      </td>
      <td style="padding:4px 6px;font-size:8pt;text-align:center;">
        <span class="sig-name">Hani Septyana</span><br>
        <span class="sig-title">Commercial and Sustainability Manager</span>
      </td>
    </tr>
  </table>
</div>