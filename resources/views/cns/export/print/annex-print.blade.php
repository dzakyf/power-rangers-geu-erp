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
    background: #f5f5f5;
    color: #000;
    padding: 10mm 0;
    display: flex;
    justify-content: center;
  }

  .page {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 8mm 10mm;
    background: #fff;
    box-shadow: 0 0 15px rgba(0,0,0,0.15);
  }

  /* Table & layout base */
  .main-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    margin-bottom: 3px;
  }
  .main-table td {
    border: 1px solid #000;
    padding: 4px 6px;
    vertical-align: top;
    font-size: 8pt;
  }
  .section-header {
    background-color: #f0f0f0;
    font-weight: bold;
    padding: 5px 8px;
  }
  .yellow-bg {
    background-color: #FFFF00;
    font-weight: bold;
  }
  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 8px;
  }
  .carrier-section, .recovery-facility-box {
    border: 1px solid #000;
    padding: 6px;
    margin-bottom: 6px;
  }
  .carrier-title {
    background-color: #f0f0f0;
    font-weight: bold;
    padding: 4px;
    margin: -6px -6px 6px -6px;
    border-bottom: 1px solid #000;
    font-size: 8pt;
  }
  .field-row {
    display: grid;
    grid-template-columns: 100px 1fr;
    margin-bottom: 3px;
  }
  .field-label {
    font-weight: bold;
    font-size: 7.5pt;
    padding-right: 5px;
  }
  .field-value {
    border-bottom: 1px dotted #aaa;
    min-height: 16px;
    font-size: 7.5pt;
    padding-left: 2px;
  }
  .signature-line {
    border-bottom: 1px solid #000;
    width: 160px;
    margin: 0 auto 4px auto;
    height: 22px;
  }
  .waste-id-table {
    width: 100%;
    border-collapse: collapse;
  }
  .waste-id-table td {
    border: 1px solid #000;
    padding: 4px;
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
    margin: 6px 0;
  }
  .countries-table td {
    border: 1px solid #000;
    padding: 5px;
    text-align: center;
    font-weight: bold;
  }
  .countries-table .country-name {
    background-color: #e8e8e8;
    font-size: 7.5pt;
    font-weight: normal;
    margin-top: 4px;
  }
  .declaration-box {
    border: 1px solid #000;
    padding: 8px 10px;
    margin-top: 6px;
    font-size: 7.5pt;
    line-height: 1.5;
  }

  /* Print Styles */
  @media print {
    @page {
      size: A4;
      padding: 3mm;
    }
    body {
      margin: 0;
      padding: 0;
      background: #fff;
    }
    .page {
      width: 210mm;
      min-height: 297mm;
      padding: 0;
      box-shadow: none;
      margin: 0;
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
        <div class="field-value">{{ $shipment->arranger_name ?? '' }}</div>
      </div>
      <div class="field-row">
        <div class="field-label">Address:</div>
        <div class="field-value">{{ $shipment->arranger_address ?? '' }}</div>
      </div>
      <div class="field-row">
        <div class="field-label">Contact person:</div>
        <div class="field-value">{{ $shipment->arranger_contact ?? '' }}</div>
      </div>
      <div class="field-row">
        <div class="field-label">Tel.:</div>
        <div class="field-value">{{ $shipment->arranger_tel ?? '' }}</div>
      </div>
      <div class="field-row">
        <div class="field-label">E-mail:</div>
        <div class="field-value">{{ $shipment->arranger_email ?? '' }}</div>
      </div>
    </div>

    <!-- Section 2: Importer/Consignee -->
    <div class="carrier-section">
      <div class="carrier-title">2. Importeur / Consignee:</div>
      <div class="field-row">
        <div class="field-label">Name:</div>
        <div class="field-value">{{ $shipment->consignee_name ?? '' }}</div>
      </div>
      <div class="field-row">
        <div class="field-label">EMAIL:</div>
        <div class="field-value">{{ $shipment->consignee_email ?? '' }}</div>
      </div>
      <div class="field-row">
        <div class="field-label">TELP.:</div>
        <div class="field-value">{{ $shipment->consignee_telp ?? '' }}</div>
      </div>
    </div>

    <!-- Section 3: Actual Date Shipment -->
    <div class="carrier-section yellow-bg">
      <div class="">3. Actual date of shipment: {{ $shipment->actual_date ?? '22/01/2026' }}</div>
    </div>

    <!-- Section 4: Actual Quantity -->
    <div class="carrier-section yellow-bg">
      <div class="">4. Actual quantity: ....... kg</div>
    </div>

  </div>

  <!-- Section 3, 4, 5: Date, Quantity, Carriers -->
  <table class="main-table" style="margin-bottom: 5px;">
    <tr>
      <td style="vertical-align: top;">
        <!-- 5a) 1e Carrier -->
        <div class="carrier-title">5 a) 1e Carrier</div>
        <div class="field-row">
          <div class="field-label">Name:</div>
          <div class="field-value">{{ $shipment->carrier1_name ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">{{ $shipment->carrier1_address ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Phone:</div>
          <div class="field-value">{{ $shipment->carrier1_phone ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">{{ $shipment->carrier1_email ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Means of transport:</div>
          <div class="field-value">SEA</div>
        </div>
        <div class="field-row">
          <div class="field-label">Date of transfer:</div>
          <div class="field-value">{{ $shipment->carrier1_date ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Carriers licence:</div>
          <div class="field-value">{{ $shipment->carrier1_licence ?? '' }}</div>
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
          <div class="field-value">{{ $shipment->carrier2_name ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">{{ $shipment->carrier2_address ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Contact person:</div>
          <div class="field-value">{{ $shipment->carrier2_contact ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Tel:</div>
          <div class="field-value">{{ $shipment->carrier2_tel ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Fax:</div>
          <div class="field-value">{{ $shipment->carrier2_fax ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">{{ $shipment->carrier2_email ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Date of transfer:</div>
          <div class="field-value">{{ $shipment->carrier2_date ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Carriers licence:</div>
          <div class="field-value">{{ $shipment->carrier2_licence ?? '' }}</div>
        </div>
        <div class="signature-box" style="margin-top: 10px;">
          <div>Signature:</div>
          <div class="signature-line"></div>
        </div>
      </td>
      <td rowspan="4" style="width: 34%; vertical-align: top;">
        <!-- 5c) 3e Carrier -->
        <div class="carrier-title">5 c) 3e Carrier Name: Address:</div>
        <div class="field-row">
          <div class="field-label">Name:</div>
          <div class="field-value">{{ $shipment->carrier3_name ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">{{ $shipment->carrier3_address ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Contact person:</div>
          <div class="field-value">{{ $shipment->carrier3_contact ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Tel:</div>
          <div class="field-value">{{ $shipment->carrier3_tel ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Fax:</div>
          <div class="field-value">{{ $shipment->carrier3_fax ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">{{ $shipment->carrier3_email ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Means of transport:</div>
          <div class="field-value">{{ $shipment->carrier3_transport ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Date of transfer:</div>
          <div class="field-value">{{ $shipment->carrier3_date ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Carriers licence:</div>
          <div class="field-value">{{ $shipment->carrier3_licence ?? '' }}</div>
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
        <div class="field-label" style="margin: 5px 0">Original Producer(s), new producer(s) or collector</div>
        <div class="field-row">
          <div class="field-label">Name:</div>
          <div class="field-value">{{ $shipment->waste_generator_name ?? 'PT GREEN ENERGI UTAMA' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">{{ $shipment->waste_generator_address ?? 'PERGUDANGAN TANRISE K WALK A2 NO 69 65153, SINGO SARI, MALANG, EAST JAVA, INDONESIA' }}</div>
        </div>
      </div>

      <!-- 7. Recovery Facility -->
      <div class="recovery-facility-box">
        <div class="carrier-title">7. Recovery Facility Laboratory Name:</div>
        <div class="field-row">
          <div class="field-label">Address:</div>
          <div class="field-value">{{ $shipment->recovery_address ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Contact person:</div>
          <div class="field-value">{{ $shipment->recovery_contact ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Tel.:</div>
          <div class="field-value">{{ $shipment->recovery_tel ?? '' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">E-mail:</div>
          <div class="field-value">{{ $shipment->recovery_email ?? '' }}</div>
        </div>
      </div>
    </div>

    <!-- Right side: 8, 9, 10 -->
    <div>
      <!-- 8. Recovery operation -->
      <div class="carrier-section">
        <div class="carrier-title">8. Recovery operation or if appropriate disposal operation in the case of waste referred to in article 3(4)</div>
        <div class="field-value" style="min-height: 40px;">R-code / D-code R-3</div>
      </div>

      <!-- 9. Usual description -->
      <div class="carrier-section">
        <div class="carrier-title">9. Usual description of the waste:</div>
        <div class="field-value" style="min-height: 30px;">Used Cooking Oil - Not for feed / food</div>
      </div>

      <!-- 10. Waste identification -->
      <div class="carrier-section">
        <div class="carrier-title">10. Waste identification (fill in relevant codes):</div>
        <table class="waste-id-table">
          <tr>
            <td class="lbl">i) Basel annex IX: B3065</td>
            <td>{{ $shipment->basel_annex ?? 'B3065' }}</td>
          </tr>
          <tr>
            <td class="lbl">ii) OECD-Code (if different from i): EWC CODE200125 Edible Oils</td>
            <td>{{ $shipment->oecd_code ?? 'EWC CODE200125' }}</td>
          </tr>
          <tr>
            <td class="lbl">iii) EC list of wastes: 200125</td>
            <td>{{ $shipment->ec_list ?? '200125' }}</td>
          </tr>
          <tr>
            <td class="lbl">iv) National Code:</td>
            <td>{{ $shipment->national_code ?? '' }}</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <!-- Section 11: Countries/state concerned -->
  <table class="countries-table" style="margin: 5px 0;">
    <tr style="background-color: #f0f0f0;">
      <td colspan="3" style="text-align: left; font-weight: bold;">11. Countries/state concerned:</td>
    </tr>
    <tr>
      <td style="width: 33%;">
        Export / dispatch:
        <div class="country-name">{{ $shipment->export_country ?? 'Surabaya - Indonesia' }}</div>
      </td>
      <td style="width: 34%;">
        Transit
        <div class="country-name">{{ $shipment->transit_country ?? '' }}</div>
      </td>
      <td style="width: 33%;">
        Import / destination
        <div class="country-name">{{ $shipment->import_destination ?? 'Pasir Gudang, Johor, Malaysia' }}</div>
      </td>
    </tr>
  </table>

  <!-- Section 12: Declaration -->
  <div class="declaration-box">
    <strong>12. Declaration of the person who arranges the shipment:</strong> I certify that the above information is complete and correct to my best knowledge. I also certify that effective written contractual obligations have been entered into with the consignee (not required in the case of waste referred to in article 3(4):
    <div style="margin-top: 10px;">
      Name: <strong>{{ $shipment->declarant_name ?? 'Hani Septyana' }}</strong> 
      Date: <strong>{{ $shipment->declarant_date ?? '19/01/2026' }}</strong> 
      Signature: _________________________
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
    <div class="carrier-title">14. Shipment received at recovery facility or laboratory Quantity received: tonnes: kg</div>
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
  <div style="margin-top: 10px; text-align: center; font-size: 7pt; color: #666;">
    TO BE COMPLETED BY THE RECOVERY FACILITY OR BY THE LABORATORY
  </div>

</div>
</body>
</html>