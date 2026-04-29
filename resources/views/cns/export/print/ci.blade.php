<?php
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Database\Eloquent\Builder;

    
    $LSP = Auth::user()->Location;
    $Saya =  Auth::user()->username;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>COMMERCIAL INVOICE</title>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 800px;
        margin-top: 20px;
        margin-left: auto;
        margin-right: auto;
        background: #ffffff;
        padding: 40px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    @media print {
        .container {
            width: 93%;
            margin-top: 0;
            margin-left: 0;
            margin-right: 0;
            box-shadow: none;
        }
    }

    .header {
        text-align: center;
        position: relative;
    }

    .logo {
        position: absolute;
        right: 0px;
        top: 0;
        font-size: 36px;
        font-weight: bold;
        text-align: right;
    }

    .company-name {
        font-size: 14px;
        margin-bottom: 10px;
    }

    .title {
        font-size: 20px;
        font-weight: bold;
        margin-top: 10px;
    }

    hr {
        border: none;
        border-top: 1px solid #aaa;
        margin: 10px 0;
    }

    .section-title {
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 5px;
        padding-bottom: 10px;
        border-bottom: 1px solid #2b6cb0;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 40px;
    }

    .grid-3-cust {
        display: grid;
        grid-template-columns: 1fr 0.5fr 1.5fr;
        gap: 40px;
    }

    .label {
        font-weight: bold;
    }

    .blue {
        color: #2b6cb0;
    }

    .job-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 14px;
    }

    th {
        background: #dcdcdc;
        text-align: left;
        padding: 8px;
        font-weight: bold;
    }

    td {
        padding: 8px;
    }

    .footer {
        position: absolute;
        right: 45%;
        left: auto;
        bottom: 3%;
        text-align: center;
        font-size: 12px;
        margin-top: 40px;
        color: #555;
    }
</style>
<link rel="stylesheet" href="paper.css" />
</head>
<body class="A4">

    <div class="sheet padding-10mm">

        <div class="header">
            <div class="title">COMMERCIAL INVOICE</div>
        </div>
        
        <hr>
        <div>
            Invoice No: {{ $invoice->Inv_No }}
        </div>
        <hr>

        <table>
            <tr style="border-top:none;">
                <td width="70%" style="vertical-align: top;">
                    <h3 style="margin:0;margin-bottom:10px;color:red;">PT GREEN ENERGI UTAMA</h3>
                    <div>
                        GRAHA AKTIVA - 4TH FLOOR, SUITE 403 A, <br />
                        JALAN HR. RASUNA SAID BLOK X-1 KAV. 03 000/000
                    </div>
                </td>
                <td style="text-align:right;margin-top:0">
                    <img src="{{ asset('/assets/images/large-logo.png') }}" alt="Logo" class="large-logo" height="70" align="center">
                </td>
            </tr>
        </table>

        <hr>
        <span>{{ date("F, d Y",strtotime($invoice->Inv_Date)) }}</span>
        <hr style="margin-bottom:0">
        <span style="font-size: 18pt;color:red;">Consignee :</span>

        <div style="min-height: 200px;">
            {{$companies[$invoice->Consignee]->Company_Name}}
            <div>
                {{$companies[$invoice->Consignee]->Address}}
            </div>
        </div>

        <div>
            <table>
                <thead>
                    <tr>
                        <th style="border:1px solid black;">DESCRIPTION OF CARGO</th>
                        <th style="border:1px solid black;">QTY (MT)</th>
                        <th style="border:1px solid black;">UNIT PRICE (MT)</th>
                        <th style="border:1px solid black;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border:1px solid black;">{{ $invoice->Description }}</td>
                        <td style="border:1px solid black;"><span style="float:right">{{ number_format($invoice->Qty,2,",",".") }}</span></td>
                        <td style="border:1px solid black;">$<span style="float:right">{{ number_format($invoice->Unit_Price,2,",",".") }}</span></td>
                        <td style="border:1px solid black;">$<span style="float:right">{{ number_format($invoice->Amount,2,",",".") }}</span></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>SUBTOTAL</td>
                        <td>$<span style="float:right">{{ number_format($invoice->Amount,2,",",".") }}</span></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>ADVANCE PAYMENT</td>
                        <td>$<span style="float:right">{{ number_format($invoice->Advance_Payment,2,",",".") }}</span></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>OTHER</td>
                        <td>$<span style="float:right">{{ number_format($invoice->Other,2,",",".") }}</span></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;font-weight:bold;">AMOUNT TO BE PAID</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;font-weight:bold;">$<span style="float:right">{{ number_format($invoice->Total,2,",",".") }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            PAYMENT INFORMATION
            <hr>
            <div>
                Name of Bank : PT BANK CENTRAL ASIA Tbk
            </div>
            <div>
                Name Account : PT Green Energi Utama
            </div>
            <div>
                No. Account : 5215905168
            </div>
        </div>

    </div>

</body>
</html>