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
<title>WEIGHT BRIDGE TICKET</title>
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
        .sheet {
          background: #ffffff;
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
<body class="A4 landscape">
  @for($i = 0; $i < ceil(count($wbt->Details) / 2); $i++)
    <div class="sheet padding-10mm" style="padding-top:0;">
        <!-- Header -->
        <div style="margin:-1cm;margin-bottom:.1cm;margin-top:0">
          <img src="{{$companies[$wbt->Shipper]->Letterhead}}" alt="Header" width="100%">
        </div>

        <div class="header">
            <div class="title">WEIGHT BRIDGE TICKET</div>
        </div>
        
        <table style="width:50%">
          <tr>
            <td>Contract No</td>
            <td>: {{ $wbt->Contract_No }}</td>
          </tr>
          <tr>
            <td>Vessel / Voyage</td>
            <td>: {{ $wbt->Voyage }}</td>
          </tr>
          <tr>
            <td>Date of Departure</td>
            <td>: {{ $wbt->Departure_Date }}</td>
          </tr>
          <tr>
            <td>B/L No</td>
            <td>: {{ $wbt->BL_No }}</td>
          </tr>
        </table>

        <table style="width:100%">
          <tr>
            <td style="text-align:center">Container ({{ $wbt->Details[$i * 2]->Container }})</td>
            @if (isset($wbt->Details[($i * 2) + 1]))
              <td style="text-align:center">Container ({{ $wbt->Details[($i * 2) + 1]->Container }})</td>
            @else
              <td style="text-align:center">&nbsp;</td>
            @endif
          </tr>
          <tr>
            <td style="text-align:center"><img src="{{ $wbt->Details[$i * 2]->Path_Loc }}" alt="" height="200px"></td>
            @if (isset($wbt->Details[($i * 2) + 1]))
              <td style="text-align:center"><img src="{{ $wbt->Details[($i * 2) + 1]->Path_Loc }}" alt="" height="200px"></td>
            @else
              <td style="text-align:center">&nbsp;</td>
            @endif
          </tr>
        </table>
        <div>
          <table>
            <tr>
              <td>
                <!-- Footer -->
                <div class="footer-fixed" style="margin-left:-1.3cm;margin-bottom:0;">
                    @if ($companies[$wbt->Shipper]->Footer != '')
                        <img src="{{$companies[$wbt->Shipper]->Footer}}" alt="Footer" style="width: 100%">
                    @endif
                </div>
              </td>
            </tr>
          </table>
        </div>
    </div>
  @endfor
</body>
</html>