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
        .header-green,
        .header-green th {
            background-color: #e8f5e9;
            color: #1b5e20;
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
        .badge-urgency {
            display: inline-block;
            padding: 1px 6px;
            background-color: #fef3c7;
            border: 1px solid #d97706;
            color: #92400e;
            font-weight: bold;
            font-size: 8.5px;
            border-radius: 3px;
            margin-top: 2px;
        }
        .supplier-ack-text {
            font-size: 9.5px;
            color: #111;
            line-height: 1.35;
            text-transform: uppercase;
        }
        .vendor-sign-line {
            margin-top: 38px;
            border-top: 1px solid #222;
            width: 210px;
            padding-top: 3px;
            font-size: 9px;
            color: #111;
            text-transform: uppercase;
        }
        .computer-gen-box {
            border: none;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            color: #111;
            line-height: 1.4;
            background-color: transparent;
        }
    </style>
</head>
<body>
    @php
        $urgencyVal = $poHeader->Urgency
            ?? $poHeader->PR_Urgency
            ?? ($prUrgency ?? '-');
        $urgencyLabel = is_string($urgencyVal) && trim($urgencyVal) !== ''
            ? strtoupper(trim($urgencyVal))
            : '-';

        $subtotalVal    = floatval($poHeader->Subtotal ?? 0);
        $discountVal    = floatval($poHeader->Discount ?? 0);
        $ppnVal         = floatval($poHeader->PPN ?? 0);
        $deliveryFeeVal = floatval($poHeader->Delivery_Fee ?? 0);
        $grandTotalVal  = floatval($poHeader->Total ?? 0);
    @endphp

    <!-- DOCUMENT TITLE & PO INFO -->
    <table>
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: middle;">
                <span style="font-size: 16px; font-weight: bold; color: #2e7d32; letter-spacing: 0.5px;">PURCHASE ORDER (PO)</span><br>
                <span style="font-size: 9px; color: #555;">Official Order Document for Goods / Services</span>
            </td>
            <td style="width: 26%; vertical-align: top;">
                <strong>PO Number:</strong><br>
                <span style="color: #1b5e20; font-weight: bold;">{{ $poHeader->PO_No }}</span><br><br>
                <strong>PR Reference:</strong><br>
                {{ $poHeader->PR_No ?? '-' }}<br><br>
            </td>
            <td style="width: 24%; vertical-align: top;">
                <strong>PO Date:</strong><br>
                {{ !empty($poHeader->PO_Date) ? date('d M Y', strtotime($poHeader->PO_Date)) : '-' }}<br><br>
                <strong>Terms / Currency:</strong><br>
                {{ $poHeader->Term_Payment ?? 'NET 30' }} ({{ $poHeader->Currency ?? 'IDR' }})<br><br>

                @if($urgencyLabel !== '-')
                    <strong>PO Type:</strong> {{ $urgencyLabel }}
                @else
                    <span style="color: #666;">Urgency: -</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- VENDOR INFO & DELIVERY ADDRESS -->
    <table>
        <tr class="sub-green">
            <td style="width: 50%;">TO (VENDOR / SUPPLIER):</td>
            <td style="width: 50%;">DELIVERY ADDRESS &amp; PIC CONTACT:</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <strong>{{ $poHeader->Company_Name ?? $poHeader->Supplier }}</strong> ({{ $poHeader->Supplier }})<br>
                Attn / Vendor PIC : {{ $poHeader->Supplier_PIC ?? $poHeader->Contact_Person ?? '-' }}<br>
                Phone : {{ $poHeader->Supplier_Phone ?? '-' }}<br>
                Email : {{ $poHeader->Supplier_Email ?? '-' }}
            </td>
            <td style="vertical-align: top;">
                <strong>Site / Destination : {{ $poHeader->PO_Dest ?? $poHeader->Location ?? '-' }}</strong><br>
                Delivery Address : {{ $poHeader->PIC_Address ?? '-' }}<br>
                Receiving PIC : {{ $poHeader->PIC ?? '-' }} ({{ $poHeader->Department ?? '-' }})<br>
                PIC Phone : {{ $poHeader->PIC_Phone ?? '-' }}
            </td>
        </tr>
    </table>

    <p style="margin: 6px 0 8px 0;">
        Dear Sir / Madam, please supply the goods / services in accordance with the order details below:
    </p>

    <!-- PO ITEMS TABLE -->
    <table>
        <thead>
            <tr class="header-green">
                <th style="width: 5%;">No.</th>
                <th style="width: 14%;">Item Code</th>
                <th style="width: 34%;">Description of Goods / Services</th>
                <th style="width: 7%;">Qty</th>
                <th style="width: 8%;">Unit</th>
                <th style="width: 15%;">Unit Price (Rp)</th>
                <th style="width: 17%;">Total Price (Rp)</th>
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
                        <br><span style="font-size: 8.5px; color: #555;">Spec: {{ $item->Specification }}</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->Qty, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->Unit }}</td>
                <td class="text-right">{{ number_format($item->Price ?? 0, 0, ',', '.') }}</td>
                <td class="text-right"><strong>{{ number_format($item->Total ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach

            <!-- AMOUNT SUMMARY -->
            <tr>
                <td colspan="6" class="text-right"><strong>Subtotal (Rp)</strong></td>
                <td class="text-right">{{ number_format($subtotalVal, 0, ',', '.') }}</td>
            </tr>
            @if($discountVal > 0)
            <tr>
                <td colspan="6" class="text-right"><strong>Discount (Rp)</strong></td>
                <td class="text-right">- {{ number_format($discountVal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($ppnVal > 0)
            <tr>
                <td colspan="6" class="text-right"><strong>VAT / PPN (Rp)</strong></td>
                <td class="text-right">{{ number_format($ppnVal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($deliveryFeeVal > 0)
            <tr>
                <td colspan="6" class="text-right"><strong>Delivery Fee (Rp)</strong></td>
                <td class="text-right">{{ number_format($deliveryFeeVal, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="sub-green">
                <td colspan="6" class="text-right" style="font-size: 11px;"><strong>GRAND TOTAL PO (Rp)</strong></td>
                <td class="text-right" style="font-size: 11px;"><strong>Rp {{ number_format($grandTotalVal, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- TERMS & CONDITIONS -->
    <table>
        <tr>
            <td>
                <strong>Terms &amp; Conditions of Purchase Order:</strong><br>
                1. Please state the PO Number (<strong>{{ $poHeader->PO_No }}</strong>) on the Delivery Order and Invoice.<br>
                2. Delivered goods / services must match the specifications and quantities stated in this Purchase Order.<br>
                3. Payment will be processed upon satisfactory receipt of goods (Goods Received) and complete billing documents.<br>
                4. Notes / References: {{ $poHeader->Remark ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- SUPPLIER ACKNOWLEDGEMENT & COMPUTER GENERATED NOTE -->
    <table class="no-border" style="margin-top: 8px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 14px;">
                <div class="supplier-ack-text">
                    SUPPLIER ONLY :<br>
                    PLEASE ACKNOWLEDGE THIS ORDER BY RETURNING A SIGNED AND STAMPED COPY OF THIS PURCHASE ORDER
                </div>
                <br> <br> <br>
                <div class="vendor-sign-line">
                    NAME, STAMP &amp; SIGNATURE
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; padding-top: 4px;">
                <div class="computer-gen-box">
                    This PO is computer generated.<br>
                    No Signature required by the {{ $poHeader->Company_Name ?? $poHeader->Supplier }}.
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
