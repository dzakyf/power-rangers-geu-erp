<?php
use Illuminate\Support\Facades\Auth;
$LSP  = Auth::user()->Location;
$Saya = Auth::user()->username;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CERTIFICATE OF ORIGIN</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; background: #f5f5f5; margin: 0; padding: 0; }
    @media print {
        body { background: #fff; }
        .sheet { box-shadow: none; }
    }
    .sheet { background: #fff; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    td, th { padding: 5px 7px; vertical-align: top; }
    .bd { border: 1px solid #666; }
    .title { text-align: center; font-size: 15px; font-weight: bold; padding: 8px 0; }
    .lbl { font-size: 10px; color: #333; }
    .thead-bg { background: #e8e8e8; font-weight: bold; text-align: center; font-size: 10px; }
    hr { border: none; border-top: 1px solid #999; margin: 4px 0; }
</style>
<link rel="stylesheet" href="/assets/paper.css" />
</head>
<body class="A4">
<div class="sheet padding-10mm">

    <div class="title">CERTIFICATE OF ORIGIN</div>

    {{-- Top table: Exporter | Pages + Invoice + Contract | Logo --}}
    <table>
        <tr>
            {{-- Exporter --}}
            <td class="bd" style="width:40%;min-height:100px;" rowspan="3">
                <div class="lbl">Exporter</div>
                <strong>PT GREEN ENERGI UTAMA</strong><br>
                Pergudangan Tanrise K Walk A2 Karanglo no 69<br>
                65153, Malang, Jawa Timur, Indonesia
            </td>
            {{-- Pages --}}
            <td class="bd" style="width:35%;">
                <div style="float:right;">Pages<br><strong>1 of 1</strong></div>
            </td>
            {{-- Logo --}}
            <td class="bd" style="width:25%;text-align:center;vertical-align:middle;" rowspan="5">
                <img src="{{ asset('/assets/images/large-logo.png') }}" alt="GEU Logo" style="max-width:130px;max-height:130px;">
                <br><span style="font-size:9px;color:#666;">member of Green New Corporation</span>
            </td>
        </tr>
        <tr>
            <td class="bd">
                <div class="lbl">Export Invoice Number &amp; Date</div>
                <div>{{ $coo->Fin_Inv_No }} {{ $coo->Fin_Inv_Date ? '/ '.$coo->Fin_Inv_Date : '' }}</div>
            </td>
        </tr>
        <tr>
            <td class="bd">
                <div class="lbl">Contract Reference</div>
                <div>{{ $coo->Contract_No }}</div>
            </td>
        </tr>
        {{-- Consignee --}}
        <tr>
            <td class="bd" colspan="2" style="min-height:80px;">
                <div class="lbl">Consignee</div>
                <strong>{{ isset($companies[$coo->Consignee]) ? strtoupper($companies[$coo->Consignee]->Company_Name) : '' }}</strong><br>
                {{ isset($companies[$coo->Consignee]) ? $companies[$coo->Consignee]->Address : '' }}
            </td>
        </tr>
        {{-- Method / Type --}}
        <tr>
            <td class="bd" style="width:30%;">
                <div class="lbl">Method of Dispatch</div>
                <div>{{ $coo->Method_Of_Dispatch }}</div>
            </td>
            <td class="bd">
                <div class="lbl">Type of Shipment</div>
                <div>{{ $coo->Shipment_Type }}</div>
            </td>
        </tr>
        <tr>
            <td class="bd">
                <div class="lbl">Vessel</div>
                <div>{{ $coo->Vessel }}</div>
            </td>
            <td class="bd" colspan="2">
                <div class="lbl">Voyage No</div>
                <div>{{ $coo->Voyage_No }}</div>
            </td>
        </tr>
        <tr>
            <td class="bd">
                <div class="lbl">Port of Loading</div>
                <div>{{ $coo->Port_Of_Loading }}</div>
            </td>
            <td class="bd" colspan="2">
                <div class="lbl">Date of Departure</div>
                <div>{{ $coo->Date_Of_Departure ? date('d F Y', strtotime($coo->Date_Of_Departure)) : '' }}</div>
            </td>
        </tr>
        <tr>
            <td class="bd">
                <div class="lbl">Port of Discharge</div>
                <div>{{ $coo->Port_Of_Discharge }}</div>
            </td>
            <td class="bd" colspan="2">
                <div class="lbl">Final Destination</div>
                <div>{{ $coo->Final_Destination }}</div>
            </td>
        </tr>
    </table>

    {{-- Cargo table --}}
    <table>
        <tr>
            <th class="bd thead-bg" style="width:28%;text-align:center;">Container Nos. W/Seal Nos.<br>Marks &amp; Numbers</th>
            <th class="bd thead-bg" style="width:17%;text-align:center;">Kind &amp; No of Packages</th>
            <th class="bd thead-bg" style="width:28%;text-align:center;">Description of Goods</th>
            <th class="bd thead-bg" style="width:12%;text-align:center;">HS Code</th>
            <th class="bd thead-bg" style="width:15%;text-align:center;">Net Weight (Kg)</th>
        </tr>
        <tr>
            <td class="bd" style="vertical-align:top;min-height:80px;">
                @foreach($coo->Details as $dtl)
                    {{ $dtl->Container_No }} / {{ $dtl->Seal_No }}<br>
                @endforeach
            </td>
            <td class="bd" style="text-align:center;vertical-align:top;">{{ $coo->Kind_Packages }}</td>
            <td class="bd" style="vertical-align:top;font-weight:bold;">{{ $coo->Good_Description }}</td>
            <td class="bd" style="text-align:center;vertical-align:top;">{{ $coo->HS_Code }}</td>
            <td class="bd" style="text-align:right;vertical-align:top;">{{ number_format($coo->Net_Weight, 0, '.', ',') }}</td>
        </tr>
    </table>

    {{-- Declaration --}}
    <table>
        <tr>
            <td class="bd" colspan="2">
                <strong>Declaration By The Exporter</strong>
                <ul style="margin:4px 0;padding-left:18px;font-size:10px;">
                    <li>I declare that the goods were produced/manufactured at Indonesia</li>
                    <li>I certify that information provided in this certificate is based on facts and is accurate and I assume the responsibility for proving such represantation. I understand that I am reliable for any false statement or material omission made on or in concern with this documents.</li>
                    <li>I agree to maintain and present upon request documentation necessary to support this certificate and to inform, in writing, al persons to whom this certificate was given of any changes that would affect accuracy or validity of this certificate.</li>
                    <li>This certificate consists of one page including all attachments</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td class="bd" style="width:50%;">
                <div class="lbl">Place and Date of Issue</div>
                <div>{{ $coo->Place_Of_Issue }}, {{ $coo->Issue_Date ? date('d F Y', strtotime($coo->Issue_Date)) : '............................' }}</div>
            </td>
            <td class="bd" style="width:50%;">
                <div class="lbl">Name of Authorized Signatory</div>
                <div>Puput Deni Iswara</div>
            </td>
        </tr>
        <tr>
            <td class="bd" style="height:80px;">
                <div class="lbl">Signatory Company</div>
                <div>PT Green Energi Utama</div>
                <br>
                <div class="lbl">Company Stamp</div>
            </td>
            <td class="bd" style="height:80px;">
                <div class="lbl">Signature</div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
