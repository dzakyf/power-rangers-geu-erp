<?php
use Illuminate\Support\Facades\DB;

$issuer = $issuer ?? ($companies[$dn->Shipper] ?? null);
if (!$issuer) {
    $issuer = DB::table('supplier')->where('Is_Company', 1)->first()
        ?? DB::table('supplier')->first();
}
$consignee = $companies[$dn->Consignee] ?? null;
?>
{{--
    View ini di-render oleh DeliveryNoteController dan di-pass ke mPDF (A4).
    Header & footer gambar di-set via SetHTMLHeader/SetHTMLFooter di controller.
--}}
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        color: #000;
    }

    .dn-content {
        padding: 6mm 15mm;
    }

    .title {
        font-size: 15pt;
        font-weight: bold;
        text-align: center;
        text-decoration: underline;
        margin-bottom: 22px;
    }

    table.info-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
    }

    table.info-table td {
        padding: 5px 4px;
        vertical-align: top;
    }

    table.info-table td.label-col {
        width: 190px;
    }

    table.info-table td.sep-col {
        width: 16px;
        text-align: center;
    }

    .declaration-box {
        margin-top: 20px;
        font-size: 10pt;
        text-align: justify;
        line-height: 1.6;
    }

    .signature-block {
        margin-top: 32px;
        font-size: 10pt;
    }

    .signature-block .city-date {
        margin-bottom: 4px;
    }

    .signature-block .company {
        font-weight: bold;
        margin-bottom: 52px;
    }

    .signature-block .signer-name {
        text-decoration: underline;
    }
</style>

<div class="dn-content">
    <div class="title">ISCC EU Waste Delivery Note</div>

    <table class="info-table">
        <tr>
            <td class="label-col">Receipt</td>
            <td class="sep-col">:</td>
            <td>{{ $issuer->Company_Name ?? 'PT GREEN ENERGI UTAMA' }}</td>
        </tr>
        <tr>
            <td class="label-col">Receipt Address</td>
            <td class="sep-col">:</td>
            <td>{{ $issuer->Address ?? 'PERGUDANGAN TANRISE K WALK A2 NO 69 65153, SINGOSARI, MALANG, EAST JAVA, INDONESIA' }}</td>
        </tr>
        <tr>
            <td class="label-col">Supplier ISCC Reference</td>
            <td class="sep-col">:</td>
            <td>{{ $dn->Supplier_ISCC_Ref ?: '...................................' }}</td>
        </tr>
        <tr>
            <td class="label-col">Contract Number</td>
            <td class="sep-col">:</td>
            <td>{{ $dn->Contract_No ?: '...................................' }}</td>
        </tr>
        <tr>
            <td class="label-col">Product</td>
            <td class="sep-col">:</td>
            <td>{{ $dn->Product }}</td>
        </tr>
        <tr>
            <td class="label-col">Total Tonnage Delivered</td>
            <td class="sep-col">:</td>
            <td>{{ $dn->Total_Tonnage ? number_format((float) $dn->Total_Tonnage, 2, '.', ',') . ' MT' : '.......... MT' }}</td>
        </tr>
        <tr>
            <td class="label-col">Date Of Delivery/Issue</td>
            <td class="sep-col">:</td>
            <td>{{ $dn->Delivery_Date ? date('F d, Y', strtotime($dn->Delivery_Date)) : '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Country Of Origin</td>
            <td class="sep-col">:</td>
            <td>{{ $dn->Country_Of_Origin }}</td>
        </tr>
        <tr>
            <td class="label-col">GHG Value</td>
            <td class="sep-col">:</td>
            <td>{{ $dn->GHG_Value }}</td>
        </tr>
    </table>

    <div class="declaration-box">
        We hereby declare that the material. Delivered to
        <strong>{{ $consignee ? strtoupper($consignee->Company_Name) : (isset($companies[$dn->Consignee]) ? strtoupper($companies[$dn->Consignee]->Company_Name) : '...............') }}</strong>,
        against the above contract has been collected in accordance with ISCC EU Waste and Residues rules.
        The material has not been stored in tanks with products other than UCO.
        The waste or residues supplied consist only of biomass defined as the biodegradable fraction of product,
        waste and residues from biological origin from agriculture (including vegetable and animal substances),
        forestry and related industries including fisheries and aquaculture, as well as the biodegrable fraction
        of industrial and muricipal waste.
    </div>

    <div class="signature-block">
        <div class="city-date">{{ $issuer->City ?? 'Surabaya' }}, {{ $dn->Delivery_Date ? date('d F Y', strtotime($dn->Delivery_Date)) : date('d F Y') }}</div>
        <div class="company" style="margin-bottom: 10px;">{{ $issuer->Company_Name ?? 'PT GREEN ENERGI UTAMA' }}</div>
        @if (!empty($approverSignature))
            <div style="height: 55px; margin-bottom: 6px;">
                <img src="{{ $approverSignature }}" alt="Signature" style="max-height: 52px; max-width: 160px;">
            </div>
        @else
            <br><br><br>
        @endif
        <div class="signer-name">{{ !empty($dn->signatory_name) ? $dn->signatory_name : (!empty($approver->Name) ? $approver->Name : (!empty($issuer->Contact_Person) ? $issuer->Contact_Person : 'Puput Deni Iswara')) }}</div>
        <div>{{ !empty($dn->signatory_title) ? $dn->signatory_title : (!empty($approver->Position) ? $approver->Position : (!empty($issuer->Sales_Name) ? $issuer->Sales_Name : 'CMO')) }}</div>
    </div>
</div>
