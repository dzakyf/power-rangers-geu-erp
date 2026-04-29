<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shipping Instruction</title>
  <style>
    * { margin: 0; padding: 0; }

    body {
      font-family: Arial, sans-serif;
      font-size: 9pt;
      background: #fff;
      color: #000;
      padding: 10mm 0;
      background-color: #e0e0e0;
    }

    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      padding: 0 10mm;
      background: #fff;
    }

    /* Header & Footer */
    .header-text, .footer-text {
      font-size: 8pt;
      color: #555;
      margin-bottom: 4px;
    }
    .footer-text { margin-top: 8px; }

    /* Title block */
    .title-block {
      display: grid;
      grid-template-columns: 120px 1fr;
      border: 1px solid #000;
      margin-bottom: 6px;
    }
    .logo-cell {
      border-right: 1px solid #000;
      padding: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 8pt;
      color: #666;
      min-height: 55px;
    }
    .title-right {
      display: flex;
      flex-direction: column;
    }
    .title-heading {
      background-color: #FFD700;
      text-align: center;
      font-weight: bold;
      font-size: 11pt;
      padding: 5px;
      letter-spacing: 1px;
    }
    .meta-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr;
      border-top: 1px solid #000;
      flex: 1;
    }
    .meta-cell {
      padding: 4px 6px;
      font-size: 7.5pt;
      border-right: 1px solid #000;
      min-height: 28px;
    }
    .meta-cell:last-child { border-right: none; }
    .meta-label { font-weight: bold; margin-bottom: 2px; }

    /* Two-column section: left parties, right fields */
    .info-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
      margin-bottom: 6px;
    }

    /* Party boxes (Shipper, Consignee, Notify Party) */
    .party-group { display: flex; flex-direction: column; gap: 6px; }

    .party-box {
      border: 1px solid #000;
    }
    .party-label {
      background-color: #000;
      color: #fff;
      font-weight: bold;
      font-size: 8pt;
      padding: 3px 6px;
    }
    .party-value {
      padding: 6px;
      min-height: 44px;
      font-size: 8.5pt;
    }

    /* B/L box at bottom left */
    .bl-box {
      border: 1px solid #000;
    }
    .bl-table { width: 100%; border-collapse: collapse; }
    .bl-table td {
      padding: 2px 5px;
      font-size: 6pt;
      border: none;
    }
    .bl-table .lbl { font-weight: bold; white-space: nowrap; }

    /* Right-side fields */
    .fields-group { display: flex; flex-direction: column; gap: 6px; }

    .field-table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
    .field-table td {
      border: 1px solid #000;
      padding: 3px 6px;
      font-size: 6pt;
      vertical-align: middle;
    }
    .field-table .flbl {
      font-weight: bold;
      white-space: nowrap;
      width: 90px;
      background: #f5f5f5;
    }
    .field-table .fval { }
    .field-table .tall td { min-height: 38px; }

    /* Goods / description section */
    .goods-header {
      font-size: 6pt;
      margin: 4px 0 2px 0;
    }

    .main-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000;
      margin-bottom: 6px;
    }
    .main-table th {
      background-color: #e0e0e0;
      border: 1px solid #000;
      padding: 3px 5px;
      font-size: 8pt;
      text-align: center;
      vertical-align: middle;
    }
    .main-table td {
      border: 1px solid #000;
      padding: 3px 5px;
      font-size: 8pt;
      vertical-align: top;
    }
    .main-table .center { text-align: center; }
    .main-table .right  { text-align: right; }
    .main-table .total-row td {
      font-weight: bold;
      background: #f0f0f0;
      /* text-align: right; */
    }
    /* .main-table .total-row td:first-child { text-align: right; } */

    /* Shipping mark / description cell */
    .shipping-mark {
      font-size: 5.5pt;
      line-height: 1.5;
      text-align: center;
    }
    .shipping-mark .red { color: #cc0000; font-weight: bold; }

    .desc-cell { font-size: 5.5pt; line-height: 1.5; }
    .desc-cell .bold-red { color: #cc0000; font-weight: bold; }

    /* Signature */
    .signature-section {
      margin-left: 5cm;
      margin-top: 4px;
      display: grid;
      grid-template-columns: 1fr 1fr;
    }
    .sig-left { padding: 4px 6px; font-size: 8pt; }
    .sig-right { padding: 4px 6px; font-size: 8pt; text-align: center; }
    .sig-name { text-decoration: underline; font-weight: 500; }
    .sig-title { font-size: 7.5pt; }

    .layout-table{
        width:100%;
        border-collapse:collapse;
    }

    .layout-table thead{
        display:table-header-group;
    }

    .layout-table tfoot{
        display:table-footer-group;
    }

    .layout-table tbody{
        display:table-row-group;
    }
    /* Print styles */
    @media print {

      @page {
        size: A4;
        margin: 0;
      }

      body {
        margin: 0;
        padding: 0;
        background-color: #fff;
      }

      .page {
        width: 210mm;
        min-height: 297mm;
        padding: 0 10mm;
        box-shadow: 0;
      }

      .footer{
          margin-top:auto;
      }
      /* Beri ruang agar konten tidak tertutup footer */

    }
  </style>
</head>
@php
    $gross = 0;
    $netto = 0;
    $mt = 0;
    foreach ($vsds as $key => $value) {
      $gross += $value->Gross_KG;
      $netto += $value->Netto_KG;
      $mt += round($value->Netto_KG/1000,2);
    }
@endphp
<body>
<div class="page" style="">

  <table class="layout-table" style="width:100%">
    <thead>
      <tr>
        <td>
          <!-- Header -->
          <div class="header-text" style="margin:-1cm;margin-bottom:.1cm;margin-top:0">
            <img src="{{$companies[$shipment->Shipper]->Letterhead}}" alt="Header" width="100%">
          </div>
        </td>
      </tr>
    </thead>
    <tbody class="content">
      <tr>
        <td>
          <!-- Title Block -->
          <div class="title-block">
            <div class="logo-cell">
                <div style="position:relative">
                    <img style="position: absolute;left:-35px;top:-33px" src="{{$companies[$shipment->Shipper]->Logo}}" alt="Logo" width="70px" height="70px">

                </div>
            </div>
            <div class="title-right">
              <div class="title-heading">FINAL SHIPPING INSTRUCTION</div>
              <div class="meta-row">
                <div class="meta-cell">
                  <div class="meta-label">Process Owner :</div>
                </div>
                <div class="meta-cell">
                  <div class="meta-label">Revision: #00</div>
                </div>
                <div class="meta-cell">
                  <div class="meta-label">Effective Date:</div>
                </div>
                <div class="meta-cell">
                  <div class="meta-label">Doc ID:</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Info Section -->
          <div class="info-section">

            <!-- Left: Party boxes -->
            <div class="party-group">
              <div class="party-box">
                <div class="party-label">SHIPPER</div>
                <div class="party-value">
                    {{$companies[$shipment->Shipper]->Company_Name}}<br>
                    {{$companies[$shipment->Shipper]->Address}}<br>
                    {{$companies[$shipment->Shipper]->City}}
                </div>
              </div>
              <div class="party-box">
                <div class="party-label">CONSIGNEE</div>
                <div class="party-value">
                    {{$companies[$shipment->Consignee]->Company_Name}}<br>
                    {{$companies[$shipment->Consignee]->Address}}<br>
                    {{$companies[$shipment->Consignee]->City}}
                </div>
              </div>
              <div class="party-box">
                <div class="party-label">NOTIFY PARTY</div>
                <div class="party-value">
                    {{$companies[$shipment->Notify]->Company_Name}}<br>
                    {{$companies[$shipment->Notify]->Address}}<br>
                    {{$companies[$shipment->Notify]->City}}
                </div>
              </div>
              <!-- B/L, Port of Loading, Port of Discharge -->
              <div class="bl-box">
                <table class="bl-table">
                  <tr>
                    <td class="lbl">B/L No</td>
                    <td>{{ $shipment->BL_No }}</td>
                  </tr>
                  <tr>
                    <td class="lbl">Port of Loading</td>
                    <td>{{ $shipment->POL }}</td>
                  </tr>
                  <tr>
                    <td class="lbl">Port of Discharge</td>
                    <td>{{ $shipment->POD }}</td>
                  </tr>
                </table>
              </div>
            </div>

            <!-- Right: Fields -->
            <div class="fields-group">
              <!-- SI fields -->
              <table class="field-table">
                <tr>
                  <td class="flbl">SI No.</td>
                  <td class="fval">{{ $shipment->Final_SI_No }}</td>
                </tr>
                <tr>
                  <td class="flbl">Date</td>
                  <td class="fval">{{ date("F, d Y",strtotime($shipment->Final_SI_Date)) }}</td>
                </tr>
                <tr>
                  <td class="flbl" style="vertical-align:top; padding-top:8px;">To</td>
                  <td class="fval" style="min-height:40px;">
                    {{ $companies[$shipment->Destination_Letter]->Company_Name }} <br>
                    {{ $companies[$shipment->Destination_Letter]->Address }}
                  </td>
                </tr>
                <tr>
                  <td class="flbl">Terms of<br>Delivery</td>
                  <td class="fval">{{ $shipment->Delivery_Terms }}</td>
                </tr>
                <tr>
                  <td class="flbl">Contract_No</td>
                  <td class="fval">{{ $shipment->Contract_No }}</td>
                </tr>
              </table>

              <!-- Shipment fields -->
              <table class="field-table">
                <tr>
                  <td class="flbl">Shipped by</td>
                  <td class="fval">{{ $shipment->Shipped_By }}</td>
                </tr>
                <tr>
                  <td class="flbl">Voyage</td>
                  <td class="fval">{{ $shipment->Voyage }}</td>
                </tr>
                <tr>
                  <td class="flbl">ETD</td>
                  <td class="fval">{{ date("F, d Y",strtotime($shipment->ETD)) }}</td>
                </tr>
                <tr>
                  <td class="flbl">ETA</td>
                  <td class="fval">{{ date("F, d Y",strtotime($shipment->ETA)) }}</td>
                </tr>
                <tr>
                  <td class="flbl">Stuffing date</td>
                  <td class="fval">{{ date("F, d Y",strtotime($shipment->Stuffing)) }}</td>
                </tr>
                <tr>
                  <td class="flbl">PEB</td>
                  <td class="fval">{{ $shipment->PEB }}</td>
                </tr>
                <tr>
                  <td class="flbl">Date PEB</td>
                  <td class="fval">{{ date("F, d Y",strtotime($shipment->PEB_Date)) }}</td>
                </tr>
                <tr>
                  <td class="flbl">Quantity</td>
                  <td class="fval">{{ $shipment->Qty }}</td>
                </tr>
              </table>

              <table class="field-table" style="border:none !important;">
                <tr>
                    <td class="flbl" style="border:none !important;">&nbsp;</td>
                    <td class="fval" style="border:none !important;">
                        <!-- Goods Condition -->
                        <div class="goods-header">A.&nbsp;&nbsp;&nbsp;{{ Str::contains($shipment->Qty2, 'FT') ? 'Good, Clean and Suitable for Flexibag' : 'Good and Clean' }}</div>
                    </td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Main cargo table -->
          <table class="main-table">
            <thead>
              <tr>
                <th rowspan="2" style="width:160px;">SEQ</th>
                <th rowspan="2">DESCRIPTION</th>
                <th>NET WEIGHT</th>
                <th>GROSS WEIGHT</th>
                <th>QTY</th>
                <th>QTY</th>
              </tr>
              <tr>
                <th>(KG)</th>
                <th>(KG)</th>
                <th>(KGS)</th>
                <th>(TON)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="border-bottom:none;">
                  <div class="shipping-mark">
                    SHIPPING MARK: FREIGHT PREPAID<br>
                    SHIPPED ON BOARD<br>
                    <span class="red">{{ number_format($mt, 2, ',', '.') }} MT {{ strtoupper($shipment->Product_Name) }}<br>
                    {{ Str::contains($shipment->Qty2, 'FT') ? 'PACKING IN FLEXIBAG' : '' }}</span><br>
                    GW {{ number_format($gross, 0, ',', '.') }} KGS<br>
                    NW {{ number_format($netto, 0, ',', '.') }} KGS<br>
                    HS Code : {{ $shipment->HS_Code }}<br>
                    COUNTRY OF ORIGIN: INDONESIA<br>
                  </div>
                </td>
                <td style="border-bottom:none;vertical-align:middle;">
                  <div class="desc-cell">
                    <span class="bold-red">
                      {{ number_format($mt, 2, ',', '.') }} MT {{ strtoupper($shipment->Product_Name) }}&nbsp;{{ Str::contains($shipment->Qty2, 'FT') ? 'PACKING IN FLEXIBAG' : '' }}
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
              @foreach ($vsds as $item)
              <tr>
                <td style="font-size:7.5pt;text-align:center;border-top:none;border-bottom:none;">{{ $loop->iteration }}</td>
                <td style="border-top:none;border-bottom:none;">
                    Container No. {{$item->Container}} / {{$item->Seal}}
                </td>
                <td style="border-top:none;border-bottom:none;" class="center">{{ number_format($item->Netto_KG,0,',','.')}}</td>
                <td style="border-top:none;border-bottom:none;" class="center"> {{ number_format($item->Gross_KG,0,',','.') }} </td>
                <td style="border-top:none;border-bottom:none;" class="center">{{ number_format($item->Netto_KG,0,',','.') }}</td>
                <td style="border-top:none;border-bottom:none;" class="center">{{ number_format(round($item->Netto_KG/1000,2),2,',','.') }}</td>
              </tr> 
              @endforeach
              <tr class="total-row">
                <td colspan="2" style="text-align:right; padding-right:8px;">Total</td>
                <td class="center">{{ number_format($netto,0,',','.')}}</td>
                <td class="center"> {{ number_format($gross,0,',','.') }} </td>
                <td class="center">{{ number_format($netto,0,',','.') }}</td>
                <td class="center">{{ number_format(round($netto/1000,2),2,',','.') }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Signatures -->
          <div class="signature-section">
            <div class="sig-left">
              <p style="margin-bottom: 70px;">Best Regards,</p>
              <p class="sig-name">Muhammad Abdul Jabar</p>
              <p class="sig-title">Commercial and Sustainability Specialist</p>
            </div>
            <div class="sig-right">
              <p style="margin-bottom: 70px;">&nbsp;</p>
              <p class="sig-name">Hani Septyana</p>
              <p class="sig-title">Commercial and Sustainability Manager</p>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="footer-space"></div>
        </td>
      </tr>
    </tbody>
    <tfoot class="footer">
      <tr>
        <td>
          <!-- Footer -->
          <div class="footer-fixed" style="margin-left:-1cm;margin-bottom:0;">
              @if ($companies[$shipment->Shipper]->Footer != '')
                  <img src="{{$companies[$shipment->Shipper]->Footer}}" alt="Footer" style="width: 21cm;">
              @endif
          </div>
        </td>
      </tr>
    </tfoot>
  </table>

</div>
<script>

function adjustFooterSpace(){

    const page = document.querySelector('.page');
    const content = document.querySelector('.content');
    const footer = document.querySelector('.footer');
    const spacer = document.querySelector('.footer-space');

    if(!page || !content || !footer || !spacer) return;

    const pageHeight = page.offsetHeight;
    const contentHeight = content.offsetHeight;
    const footerHeight = footer.offsetHeight;

    const totalUsed = contentHeight + footerHeight;

    if(totalUsed >= pageHeight){
        /* konten sudah penuh halaman */
        spacer.style.height = "0px";
        return;
    }

    
    const remaining = pageHeight - totalUsed;
    
    spacer.style.height = remaining + "mm";
}


/* jalankan setelah DOM siap */
window.addEventListener("load", () => {
    setTimeout(adjustFooterSpace,150);
});

/* jika resize */
window.addEventListener("resize", adjustFooterSpace);

/* sebelum print */
window.addEventListener("beforeprint", adjustFooterSpace);

</script>
</body>
</html>
