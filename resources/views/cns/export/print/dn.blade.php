<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? '';
$issuer = $issuer ?? ($companies[$dn->Shipper] ?? null);
if (!$issuer) {
    $issuer = DB::table('supplier')->where('Is_Company', 1)->first()
        ?? DB::table('supplier')->first();
}
$consignee = $companies[$dn->Consignee] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ISCC EU WASTE DELIVERY NOTE</title>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    @media print {
        .container {
            width: 93%;
            margin-top: 0;
            margin-left: 0;
            margin-right: 0;
            box-shadow: none;
        }
        .sheet {
            background: #ffffff;
        }
    }

    .title {
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        text-decoration: underline;
        margin-bottom: 30px;
    }

    hr {
        border: none;
        border-top: 1px solid #aaa;
        margin: 10px 0;
    }

    table.info-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    table.info-table td {
        padding: 6px 4px;
        vertical-align: top;
    }

    table.info-table td.label-col {
        width: 200px;
        font-weight: normal;
    }

    table.info-table td.sep-col {
        width: 20px;
        text-align: center;
    }

    .declaration-box {
        margin-top: 24px;
        font-size: 13px;
        text-align: justify;
        line-height: 1.7;
    }

    .signature-block {
        margin-top: 40px;
        font-size: 13px;
    }

    .signature-block .city-date {
        margin-bottom: 4px;
    }

    .signature-block .company {
        font-weight: bold;
        margin-bottom: 60px;
    }

    .signature-block .signer-name {
        text-decoration: underline;
    }
</style>
<link rel="stylesheet" href="/assets/paper.css" />
</head>
<body class="A4">
<div class="sheet padding-10mm">

    <!-- Header with letterhead -->
    @if(!empty($issuer->Letterhead))
    <div style="margin:-1cm;margin-bottom:.5cm;margin-top:0">
        <img src="{{ asset($issuer->Letterhead) }}" alt="Header" width="100%">
    </div>
    @else
    <table style="width:100%;margin-bottom:20px;">
        <tr>
            <td style="vertical-align:top;">
                <strong style="font-size:15px;color:#333;">{{ $issuer->Company_Name ?? 'PT. GREEN ENERGI UTAMA' }}</strong><br>
                <span style="font-size:11px;">{!! nl2br(e($issuer->Address ?? "A2 Pergudangan Tanrise K-Walk\n65153 Malang, East Java, Indonesia")) !!}</span>
            </td>
            <td style="text-align:right;vertical-align:top;">
                @if(!empty($issuer->Logo))
                    <img src="{{ asset($issuer->Logo) }}" alt="Logo" height="60">
                @else
                    <img src="{{ asset('/assets/images/large-logo.png') }}" alt="Logo" height="60">
                @endif
            </td>
        </tr>
    </table>
    @endif

    <hr>

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
        <div class="company">{{ $issuer->Company_Name ?? 'PT GREEN ENERGI UTAMA' }}</div>
        <br><br><br>
        <div class="signer-name">{{ !empty($issuer->Contact_Person) ? $issuer->Contact_Person : 'Puput Deni Iswara' }}</div>
        <div>{{ !empty($issuer->Sales_Name) ? $issuer->Sales_Name : 'CMO' }}</div>
    </div>

    @if(!empty($issuer->Footer))
    <div style="margin-top:20px;">
        <img src="{{ asset($issuer->Footer) }}" alt="Footer" width="100%">
    </div>
    @endif

</div>
</body>
</html>
