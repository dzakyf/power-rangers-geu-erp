<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Information Accompanying Shipments of Waste</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: Arial, sans-serif;
      font-size: 8pt;
      background: #fff;
      color: #000;
      padding: 10mm 0;
    }

    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      padding: 0 5mm;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    /* Table base styles */
    .main-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000;
      margin-bottom: 2px;
    }

    .main-table td {
      border: 1px solid #000;
      padding: 3px 5px;
      vertical-align: top;
      font-size: 8pt;
    }

    .section-header {
      background-color: #f0f0f0;
      font-weight: bold;
      padding: 4px 6px;
    }

    .yellow-bg {
      background-color: #FFFF00;
      font-weight: bold;
    }

    .label {
      font-weight: bold;
      white-space: nowrap;
      width: 30%;
    }

    .value {
      width: 70%;
    }

    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5px;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 0;
    }

    .full-width {
      grid-column: 1 / -1;
    }

    .carrier-section {
      border: 1px solid #000;
      padding: 5px;
      margin-bottom: 5px;
    }

    .carrier-title {
      background-color: #f0f0f0;
      font-weight: bold;
      padding: 3px;
      margin: -5px -5px 5px -5px;
      border-bottom: 1px solid #000;
      font-size: 8pt;
    }

    .field-row {
      display: grid;
      grid-template-columns: 100px 1fr;
      margin-bottom: 2px;
    }

    .field-label {
      font-weight: bold;
      font-size: 7.5pt;
      padding-right: 5px;
    }

    .field-value {
      border-bottom: 1px dotted #999;
      min-height: 15px;
      font-size: 7.5pt;
    }

    .signature-box {
      margin-top: 10px;
      text-align: center;
    }

    .signature-line {
      border-bottom: 1px solid #000;
      width: 150px;
      margin: 0 auto 3px auto;
      height: 20px;
    }

    .checkbox-item {
      display: inline-block;
      margin-right: 15px;
      margin-bottom: 3px;
    }

    .waste-id-table {
      width: 100%;
      border-collapse: collapse;
    }

    .waste-id-table td {
      border: 1px solid #000;
      padding: 3px;
      font-size: 7.5pt;
    }

    .waste-id-table .lbl {
      font-weight: bold;
      width: 45%;
      background-color: #f9f9f9;
    }

    .countries-table {
      width: 100%;
      border-collapse: collapse;
      margin: 5px 0;
    }

    .countries-table td {
      border: 1px solid #000;
      padding: 4px;
      text-align: center;
      font-weight: bold;
    }

    .countries-table .country-name {
      background-color: #e8e8e8;
      font-size: 7.5pt;
      font-weight: normal;
    }

    .declaration-box {
      border: 1px solid #000;
      padding: 8px;
      margin-top: 5px;
      font-size: 7.5pt;
      line-height: 1.4;
    }

    .recovery-facility-box {
      border: 1px solid #000;
      padding: 5px;
      margin-top: 5px;
    }

    @media print {
      @page {
        size: A4;
        margin: 10mm;
      }

      body {
        margin: 0;
        padding: 0;
      }

      .page {
        width: 210mm;
        min-height: 297mm;
        padding: 0;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>
<div class="page">

  <!-- Header Title -->
  <table class="main-table" style="margin-bottom: 5px;">
    <tr>
      <td class="section-header" style="text-align: center; font-size: 9pt;">
        INFORMATION ACCOMPANYING SHIPMENTS OF WASTE AS REFERRED TO IN ARTICLE 3(2) AND (4)
      </td>
    </tr>
  </table>

  <!-- Section 1 & 2: Person and Importer -->
  <div class="grid-2">
    <!-- Section 1: Person who arranges shipment -->
    <div class="carrier-section">
      <div class="carrier-title">1. Person, who arranges shipment:</div>
      <div class="field-row">
        <div class="field-label">Name:</div>
        <div class="field-value">PT GREEN ENERGI UTAMA</div>
      </div>
      <div class="field-row">
        <div class="field-label">Address:</div>
        <div class="field-value">Jl. Industri Raya No. 88, Surabaya, East Java 60119, Indonesia</div>
      </div>
      <div class="field-row">
        <div class="field-label">Contact person:</div>
        <div class="field-value">Budi Santoso</div>
      </div>
      <div class="field-row">
        <div class="field-label">Tel.:</div>
        <div class="field-value">+62 31 5551234</div>
      </div>
      <div class="field-row">
        <div class="field-label">E-mail:</div>
        <div class="field-value">export@greenenergi.co.id</div>
      </div>
    </div>

    <!-- Section 2: Importer/Consignee -->
    <div class="carrier-section">
      <div class="carrier-title">2. Importeur / Consignee:</div>
      <div class="field-row">
        <div class="field-label">Name:</div>
        <div class="field-value">RENEWABLE ENERGY SDN BHD</div>
      </div>
      <div class="field-row">
        <div class="field-label">EMAIL:</div>
        <div class="field-value">imports@renewableenergy.com.my</div>
      </div>
      <div class="field-row">
        <div class="field-label">TELP.:</div>
        <div class="field-value">+60 7 5551234</div>
      </div>
    </div>
  </div>

  <!-- Section 3, 4, 5: Date, Quantity, Carriers -->
  <table class="main-table" style="margin-bottom: 5px;">
    <tr>
      <td class="yellow-bg" style="width: 33%;">3. Actual date of shipment: 22/01/2026</td>
      <td class="yellow-bg" style="width: 33%;">4. Actual quantity: 25,000 kg</td>
      <td rowspan="4" style="width: 34%; vertical-align: top;">
        <!-- 5c) 3e Carrier -->
        <div class="carrier-title">5 c) 3e Carrier Name: Address:</div>
        <div class="field-row">
          <div class="field-label">Name:</div>
          <div class="field-value">MALAYSIA SHIPPING LINES</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">Port Klang, Selangor, Malaysia</div>
        </div>
        <div class="field-row">
          <div class="field-label">Contact person:</div>
          <div class="field-value">Ahmad Ibrahim</div>
        </div>
        <div class="field-row">
          <div class="field-label">Tel:</div>
          <div class="field-value">+60 3 5551234</div>
        </div>
        <div class="field-row">
          <div class="field-label">Fax:</div>
          <div class="field-value">+60 3 5551235</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">ops@malaysiashipping.com</div>
        </div>
        <div class="field-row">
          <div class="field-label">Means of transport:</div>
          <div class="field-value">SEA - Vessel</div>
        </div>
        <div class="field-row">
          <div class="field-label">Date of transfer:</div>
          <div class="field-value">24/01/2026</div>
        </div>
        <div class="field-row">
          <div class="field-label">Carriers licence:</div>
          <div class="field-value">MY-SHIP-2025-8899</div>
        </div>
        <div class="signature-box" style="margin-top: 10px;">
          <div>Signature:</div>
          <div class="signature-line"></div>
        </div>
      </td>
    </tr>
    <tr>
      <td style="vertical-align: top;">
        <!-- 5a) 1e Carrier -->
        <div class="carrier-title">5 a) 1e Carrier</div>
        <div class="field-row">
          <div class="field-label">Name:</div>
          <div class="field-value">TANJUNG PERAK PORT AUTHORITY</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">Tanjung Perak, Surabaya 60165, Indonesia</div>
        </div>
        <div class="field-row">
          <div class="field-label">Phone:</div>
          <div class="field-value">+62 31 3291234</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">ops@tanjungperak.co.id</div>
        </div>
        <div class="field-row">
          <div class="field-label">Means of transport:</div>
          <div class="field-value">SEA</div>
        </div>
        <div class="field-row">
          <div class="field-label">Date of transfer:</div>
          <div class="field-value">22/01/2026</div>
        </div>
        <div class="field-row">
          <div class="field-label">Carriers licence:</div>
          <div class="field-value">ID-PORT-2025-1122</div>
        </div>
        <div class="signature-box" style="margin-top: 10px;">
          <div>Signature:</div>
          <div class="signature-line"></div>
        </div>
      </td>
      <td style="vertical-align: top;">
        <!-- 5b) 2e Carrier -->
        <div class="carrier-title">5 b) 2e Carrier Name: Address:</div>
        <div class="field-row">
          <div class="field-label">Name:</div>
          <div class="field-value">SAMUDERA SHIPPING LINE</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">Jakarta Utara 14410, Indonesia</div>
        </div>
        <div class="field-row">
          <div class="field-label">Contact person:</div>
          <div class="field-value">Dewi Kusuma</div>
        </div>
        <div class="field-row">
          <div class="field-label">Tel:</div>
          <div class="field-value">+62 21 5551234</div>
        </div>
        <div class="field-row">
          <div class="field-label">Fax:</div>
          <div class="field-value">+62 21 5551235</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">operations@samuderashipping.co.id</div>
        </div>
        <div class="field-row">
          <div class="field-label">Date of transfer:</div>
          <div class="field-value">23/01/2026</div>
        </div>
        <div class="field-row">
          <div class="field-label">Carriers licence:</div>
          <div class="field-value">ID-SHIP-2025-3344</div>
        </div>
        <div class="signature-box" style="margin-top: 10px;">
          <div>Signature:</div>
          <div class="signature-line"></div>
        </div>
      </td>
    </tr>
  </table>

  <!-- Section 6, 7, 8, 9, 10 -->
  <div class="grid-2">
    <!-- Left side: 6, 7 -->
    <div>
      <!-- 6. Waste generator -->
      <div class="carrier-section">
        <div class="carrier-title">6. Waste generator (3)</div>
        <div class="field-row">
          <div class="field-label">Original Producer(s), new producer(s) or collector</div>
        </div>
        <div class="field-row">
          <div class="field-label">Name:</div>
          <div class="field-value">PT GREEN ENERGI UTAMA</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">PERGUDANGAN TANRISE K WALK A2 NO 69 65153, SINGO SARI, MALANG, EAST JAVA, INDONESIA</div>
        </div>
      </div>

      <!-- 7. Recovery Facility -->
      <div class="recovery-facility-box">
        <div class="carrier-title">7. Recovery Facility ☑ Laboratory ☐ Name: RENEWABLE ENERGY LAB</div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">Lot 15, Jalan Industri 3, Pasir Gudang, 81700 Johor, Malaysia</div>
        </div>
        <div class="field-row">
          <div class="field-label">Contact person:</div>
          <div class="field-value">Lim Wei Jian</div>
        </div>
        <div class="field-row">
          <div class="field-label">Tel.:</div>
          <div class="field-value">+60 7 2551234</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">lab@renewableenergy.com.my</div>
        </div>
      </div>
    </div>

    <!-- Right side: 8, 9, 10 -->
    <div>
      <!-- 8. Recovery operation -->
      <div class="carrier-section">
        <div class="carrier-title">8. Recovery operation or if appropriate disposal operation in the case of waste referred to in article 3(4)</div>
        <div class="field-value" style="min-height: 40px;">R-code / D-code R-3 (Recycling/reclamation of organic substances which are not used as solvents)</div>
      </div>

      <!-- 9. Usual description -->
      <div class="carrier-section">
        <div class="carrier-title">9. Usual description of the waste:</div>
        <div class="field-value" style="min-height: 30px;">Used Cooking Oil – Not for feed / food</div>
      </div>

      <!-- 10. Waste identification -->
      <div class="carrier-section">
        <div class="carrier-title">10. Waste identification (fill in relevant codes):</div>
        <table class="waste-id-table">
          <tr>
            <td class="lbl">i) Basel annex IX: B3065</td>
            <td>B3065</td>
          </tr>
          <tr>
            <td class="lbl">ii) OECD-Code (if different from i): EWC CODE200125 Edible Oils</td>
            <td>EWC CODE200125</td>
          </tr>
          <tr>
            <td class="lbl">iii) EC list of wastes: 200125</td>
            <td>200125</td>
          </tr>
          <tr>
            <td class="lbl">iv) National Code:</td>
            <td>ID-WASTE-2025-UCO-001</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <!-- Section 11: Countries/state concerned -->
  <table class="countries-table">
    <tr style="background-color: #f0f0f0;">
      <td colspan="3" style="text-align: left; font-weight: bold;">11. Countries/state concerned:</td>
    </tr>
    <tr>
      <td style="width: 33%;">
        Export / dispatch:
        <div class="country-name">Surabaya - Indonesia</div>
      </td>
      <td style="width: 34%;">
        Transit
        <div class="country-name">Singapore Strait</div>
      </td>
      <td style="width: 33%;">
        Import / destination
        <div class="country-name">Pasir Gudang, Johor, Malaysia</div>
      </td>
    </tr>
  </table>

  <!-- Section 12: Declaration -->
  <div class="declaration-box">
    <strong>12. Declaration of the person who arranges the shipment:</strong> I certify that the above information is complete and correct to my best knowledge. I also certify that effective written contractual obligations have been entered into with the consignee (not required in the case of waste referred to in article 3(4):
    <div style="margin-top: 10px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
      <div>
        <strong>Name:</strong> Hani Septyana
      </div>
      <div>
        <strong>Date:</strong> 19/01/2026
      </div>
      <div>
        <strong>Signature:</strong> _________________________
      </div>
    </div>
  </div>

  <!-- Section 13: Signature upon receipt -->
  <div class="carrier-section" style="margin-top: 5px;">
    <div class="carrier-title">13. Signature upon receipt of the waste by the consignee:</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">
      <div>
        <div>Name:</div>
        <div class="signature-line"></div>
      </div>
      <div>
        <div>Date:</div>
        <div class="signature-line"></div>
      </div>
      <div>
        <div>Signature:</div>
        <div class="signature-line"></div>
      </div>
    </div>
  </div>

  <!-- Section 14: Shipment received -->
  <div class="carrier-section" style="margin-top: 5px;">
    <div class="carrier-title">14. Shipment received at recovery facility ☐ or labotoro ☐ Quantity received: tonnes: kg</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">
      <div>
        <div>Name:</div>
        <div class="signature-line"></div>
      </div>
      <div>
        <div>Date:</div>
        <div class="signature-line"></div>
      </div>
      <div>
        <div>Signature:</div>
        <div class="signature-line"></div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div style="margin-top: 10px; text-align: center; font-size: 7pt; color: #666; border-top: 1px solid #ccc; padding-top: 5px;">
    TO BE COMPLETED BY THE RECOVERY FACILITY OR BY THE LABORATORY
  </div>

</div>

<script>
  // Optional: Print button
  window.addEventListener('load', function() {
    console.log('Document loaded. Press Ctrl+P to print or save as PDF');
  });
</script>

</body>
</html>