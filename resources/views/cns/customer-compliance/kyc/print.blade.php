<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KYC - {{ $kyc->kyc_no }}</title>
    <style>
        @page {
            margin-top: 10mm;
            margin-bottom: 12mm;
            margin-left: 15mm;
            margin-right: 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #111;
            line-height: 1.35;
        }
        .header-img {
            width: 100%;
            max-height: 100px;
            margin-bottom: 8px;
        }
        .footer-img {
            width: 100%;
            max-height: 100px;
        }
        .form-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-top: 4px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .doc-no {
            text-align: center;
            font-size: 9.5pt;
            color: #555;
            margin-bottom: 8px;
        }
        .meta-box {
            margin-bottom: 10px;
            padding: 5px 8px;
            border-bottom: 1.5px solid #2e7d32;
            font-size: 10pt;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
            color: #1b5e20;
        }
        table.kyc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.kyc-table th, table.kyc-table td {
            border: 1px solid #444;
            padding: 5px 7px;
            vertical-align: top;
            font-size: 9pt;
        }
        table.kyc-table td.col-label {
            width: 42%;
            background-color: #f7f9f7;
            font-weight: normal;
        }
        table.kyc-table td.col-val {
            width: 58%;
        }
        .docs-section {
            margin-top: 10px;
            font-size: 9.5pt;
        }
        .docs-section h4 {
            font-size: 10pt;
            margin-bottom: 4px;
            color: #1b5e20;
        }
        .docs-list {
            margin-left: 15px;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .docs-list li {
            margin-bottom: 3px;
        }
        .status-badge {
            font-size: 8.5pt;
            font-weight: bold;
            color: #2e7d32;
        }
    </style>
</head>
<body>

    @if(!empty($headerUri))
        <div style="text-align: center;">
            <img src="{{ $headerUri }}" class="header-img" alt="Letterhead GEU">
        </div>
    @endif

    <div class="form-title">KNOW-YOUR-CUSTOMER FORM</div>
    <div class="doc-no">Doc. No: {{ $kyc->kyc_no }}</div>

    <div class="meta-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <strong>Name of person filling in this form:</strong><br>
                    {{ $kyc->filler_name }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Date:</strong><br>
                    {{ $kyc->form_date ? $kyc->form_date->format('d F Y') : '-' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- General Company Information -->
    <div class="section-title">General Company Information</div>
    <table class="kyc-table">
        <tbody>
            <tr>
                <td class="col-label">a) Company name according to official Commercial Register</td>
                <td class="col-val"><strong>{{ $kyc->company_name }}</strong></td>
            </tr>
            <tr>
                <td class="col-label">b) Company registration number</td>
                <td class="col-val">{{ $kyc->company_reg_number ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">c) Date of incorporation</td>
                <td class="col-val">{{ $kyc->incorporation_date ? $kyc->incorporation_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">d) Registered address</td>
                <td class="col-val">{!! nl2br(e($kyc->registered_address ?: '-')) !!}</td>
            </tr>
            <tr>
                <td class="col-label">e) Address of Admin + Accounting</td>
                <td class="col-val">{!! nl2br(e($kyc->admin_accounting_address ?: '-')) !!}</td>
            </tr>
            <tr>
                <td class="col-label">f) Type of legal entity</td>
                <td class="col-val">{{ $kyc->legal_entity_type ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">g) Value Added Tax (VAT) number</td>
                <td class="col-val">{{ $kyc->vat_number ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">h) Website address</td>
                <td class="col-val">{{ $kyc->website ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">i) Contact person for trading</td>
                <td class="col-val">{{ $kyc->trading_contact_person ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">j) Contact details for i)</td>
                <td class="col-val">{{ $kyc->trading_contact_details ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">k) Contact person for finance</td>
                <td class="col-val">{{ $kyc->finance_contact_person ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">l) Contact details for k)</td>
                <td class="col-val">{{ $kyc->finance_contact_details ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Ownership & Management -->
    <div class="section-title">Ownership & Management</div>
    <table class="kyc-table">
        <tbody>
            <tr>
                <td class="col-label">a) Business activities</td>
                <td class="col-val">{!! nl2br(e($kyc->business_activities ?: '-')) !!}</td>
            </tr>
            <tr>
                <td class="col-label">b) Countries of activities</td>
                <td class="col-val">{!! nl2br(e($kyc->countries_of_activities ?: '-')) !!}</td>
            </tr>
        </tbody>
    </table>

    <!-- Supporting Documents -->
    <div class="docs-section">
        <div class="section-title" style="font-size: 10pt;">Please enclose the following supporting documents (scanned copies):</div>
        <table class="kyc-table">
            <tbody>
                <tr>
                    <td class="col-label">- Commercial/Company register extract</td>
                    <td class="col-val">
                        @if($kyc->doc_register_extract)
                            <span class="status-badge">[√] Enclosed</span> ({{ $kyc->doc_register_extract_name ?: 'File attached' }})
                        @else
                            <span style="color: #777;">[ ] Not attached</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="col-label">- Sustainability certificate (e.g. ISCC) or trading certificate</td>
                    <td class="col-val">
                        @if($kyc->doc_certificate)
                            <span class="status-badge">[√] Enclosed</span> ({{ $kyc->doc_certificate_name ?: 'File attached' }})
                        @else
                            <span style="color: #777;">[ ] Not attached</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(!empty($footerUri))
        <div style="text-align: center; margin-top: 10px;">
            <img src="{{ $footerUri }}" class="footer-img" alt="Footer GEU">
        </div>
    @endif

</body>
</html>
