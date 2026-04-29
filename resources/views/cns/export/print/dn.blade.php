<?php
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;
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
    @if(isset($companies[$dn->Consignee]) && $companies[$dn->Consignee]->Letterhead)
    <div style="margin:-1cm;margin-bottom:.5cm;margin-top:0">
        <img src="{{ $companies[$dn->Consignee]->Letterhead ?? '' }}" alt="Header" width="100%" style="display:none;">
    </div>
    @endif

    <table style="width:100%;margin-bottom:20px;">
        <tr>
            <td style="vertical-align:top;">
                <strong style="font-size:15px;color:#333;">PT. GREEN ENERGI UTAMA</strong><br>
                <span style="font-size:11px;">A2 Pergudangan Tanrise K-Walk<br>65153 Malang, East Java, Indonesia</span>
            </td>
            <td style="text-align:right;vertical-align:top;">
                <img src="{{ asset('/assets/images/large-logo.png') }}" alt="Logo" height="60">
            </td>
        </tr>
    </table>

    <hr>

    <div class="title">ISCC EU Waste Delivery Note</div>

    <table class="info-table">
        <tr>
            <td class="label-col">Receipt</td>
            <td class="sep-col">:</td>
            <td>PT GREEN ENERGI UTAMA</td>
        </tr>
        <tr>
            <td class="label-col">Receipt Address</td>
            <td class="sep-col">:</td>
            <td>PERGUDANGAN TANRISE K WALK A2 NO 69 65153, SINGOSARI, MALANG, EAST JAVA, INDONESIA</td>
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
            <td>{{ $dn->Total_Tonnage ? number_format($dn->Total_Tonnage, 3, '.', ',') . ' MT' : '.......... MT' }}</td>
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
        <strong>{{ isset($companies[$dn->Consignee]) ? strtoupper($companies[$dn->Consignee]->Company_Name) : '...............' }}</strong>,
        against the above contract has been collected in accordance with ISCC EU Waste and Residues rules.
        The material has not been stored in tanks with products other than UCO.
        The waste or residues supplied consist only of biomass defined as the biodegradable fraction of product,
        waste and residues from biological origin from agriculture (including vegetable and animal substances),
        forestry and related industries including fisheries and aquaculture, as well as the biodegrable fraction
        of industrial and muricipal waste.
    </div>

    <div class="signature-block">
        <div class="city-date">Surabaya, {{ $dn->Delivery_Date ? date('d F Y', strtotime($dn->Delivery_Date)) : date('d F Y') }}</div>
        <div class="company">PT GREEN ENERGI UTAMA</div>
        <br><br><br>
        <div class="signer-name">Puput Deni Iswara</div>
        <div>CMO</div>
    </div>

</div>
</body>
</html>
