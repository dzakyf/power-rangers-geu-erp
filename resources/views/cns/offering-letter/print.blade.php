<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10.5px;
        color: #111;
        line-height: 1.35;
        margin: 0;
        padding: 0;
    }
    .title {
        font-size: 14px;
        font-weight: bold;
        text-decoration: underline;
        text-align: center;
        margin-bottom: 3px;
    }
    .offer-no {
        text-align: center;
        font-weight: bold;
        margin-bottom: 12px;
        font-size: 11px;
    }
    p {
        margin: 0 0 6px 0;
        font-size: 10.5px;
    }
    table.main-table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0 10px 0;
    }
    table.main-table td, table.main-table th {
        border: 1px solid #333;
        padding: 3.5px 6px;
        vertical-align: top;
        text-align: left;
        font-size: 10px;
    }
    table.main-table td.label {
        width: 28%;
        font-weight: bold;
        background: #f8f9fa;
    }
    table.spec-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 2px;
    }
    table.spec-table th, table.spec-table td {
        border: 1px solid #666;
        padding: 2.5px 5px;
        font-size: 9.5px;
    }
    table.spec-table th {
        background: #eee;
        text-align: left;
    }
    .signature {
        margin-top: 12px;
    }
    .sig-space {
        height: 45px;
    }
</style>

<div class="title">OFFERING LETTER</div>
<div class="offer-no">{{ $letter->offer_no }}</div>

<p>Dear {{ $letter->buyer_name }},</p>

<p>
    We are {{ $letter->seller_name }}, {{ $letter->certificate }} certified company currently engaged in the export sector,
    providing raw materials for renewable energy based on used cooking oil (UCO) waste and focusing on caring for the environment.<br>
    We intend to submit an offer to extend our partnership on waste material with the detailed specifications below:
</p>

<table class="main-table">
    <tr>
        <td class="label">Seller</td>
        <td>{{ $letter->seller_name }}</td>
    </tr>
    <tr>
        <td class="label">Buyer</td>
        <td>{{ $letter->buyer_name }}</td>
    </tr>
    <tr>
        <td class="label">Product</td>
        <td>{{ $letter->product }}</td>
    </tr>
    <tr>
        <td class="label">HS Code</td>
        <td>{{ $letter->hs_code }}</td>
    </tr>
    <tr>
        <td class="label">Certificate</td>
        <td>{{ $letter->certificate }}</td>
    </tr>
    <tr>
        <td class="label">Guaranteed Specification</td>
        <td>
            <table class="spec-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">TEST ITEM</th>
                        <th style="width: 50%;">SPECIFICATION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Free Fatty Acids (ISO660)</td><td>{{ $letter->spec_ffa ?: '-' }}</td></tr>
                    <tr><td>MnI (ASTM D 6304-C; ISO 663)</td><td>{{ $letter->spec_mni ?: '-' }}</td></tr>
                    <tr><td>Unsaponifiable</td><td>{{ $letter->spec_unsaponifiable ?: '-' }}</td></tr>
                    <tr><td>Iodine Value (AOCS Cd_Id_92)</td><td>{{ $letter->spec_iodine_value ?: '-' }}</td></tr>
                    <tr><td>Sulphur (ASTM D 5453)</td><td>{{ $letter->spec_sulphur ?: '-' }}</td></tr>
                    <tr><td>Total Metals</td><td>{{ $letter->spec_total_metals ?: '-' }}</td></tr>
                    <tr><td>Phosphorus</td><td>{{ $letter->spec_phosphorus ?: '-' }}</td></tr>
                    <tr><td>Total Chloride</td><td>{{ $letter->spec_total_chloride ?: '-' }}</td></tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td class="label">Quantity</td>
        <td>{{ number_format((float) $letter->quantity_mt, 2) }} MT {{ $letter->quantity_note }}</td>
    </tr>
    <tr>
        <td class="label">Final Quantity and Quality</td>
        <td>{{ $letter->final_qty_quality }}</td>
    </tr>
    <tr>
        <td class="label">Price/MT</td>
        <td>{{ $letter->price_currency }} {{ number_format((float) $letter->price_per_mt, 2) }} / MT</td>
    </tr>
    <tr>
        <td class="label">Loading Period</td>
        <td>{{ \App\CnsOfferingLetter::formatLoadingPeriod($letter->loading_period) }}</td>
    </tr>
    <tr>
        <td class="label">Delivery Term</td>
        <td>{{ $letter->delivery_term === 'CIF' && $letter->destination_port ? 'CIF ' . $letter->destination_port : $letter->delivery_term }}</td>
    </tr>
    <tr>
        <td class="label">Packaging</td>
        <td>{{ $letter->packaging }}</td>
    </tr>
    <tr>
        <td class="label">Payment Terms</td>
        <td>{!! nl2br(e($letter->payment_terms)) !!}</td>
    </tr>
    <tr>
        <td class="label">Load Port Independent Inspector</td>
        <td>{{ $letter->load_port_inspector }}</td>
    </tr>
    <tr>
        <td class="label">Offer Validity</td>
        <td>
            @if($letter->valid_until)
                This offer shall remain valid until {{ $letter->valid_until->format('d F Y') }}.
            @else
                This offer is currently DRAFT (valid 14 calendar days from approval date).
            @endif
        </td>
    </tr>
</table>

<p>We look forward to your response. Should you have any questions regarding this offer, please feel free to reach out. Thank you.</p>

<div class="signature">
    <p>{{ $letter->place_of_issue }}, {{ $letter->offer_date ? $letter->offer_date->format('d F Y') : '' }}.</p>
    <p><strong>{{ $letter->seller_name }}</strong></p>
    <div class="sig-space">
        @if(!empty($signatureUri) && $letter->status === \App\CnsOfferingLetter::STATUS_APPROVED)
            <img src="{{ $signatureUri }}" alt="Signature" style="max-height: 40px; max-width: 150px;">
        @endif
    </div>
    <p><strong>{{ $letter->signatory_name }}</strong><br><strong>{{ $letter->signatory_title }}</strong></p>
</div>
