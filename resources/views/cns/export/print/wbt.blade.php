<?php
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Database\Eloquent\Builder;

    $LSP = Auth::user()->Location ?? '';
    $Saya =  Auth::user()->username ?? '';
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
<link rel="stylesheet" href="{{ asset('paper.css') }}" />
</head>
<body class="A4 landscape">
@php
    $details = $wbt->Details ?? collect();
    $totalDetails = count($details);
    $totalPages = max(1, (int) ceil($totalDetails / 2));
    $shipper = $companies[$wbt->Shipper] ?? null;
@endphp

@for($i = 0; $i < $totalPages; $i++)
    <div class="sheet padding-10mm" style="padding-top:0;">
        <!-- Header -->
        <div style="margin:-1cm;margin-bottom:.1cm;margin-top:0">
            @if(!empty($shipper->Letterhead))
                <img src="{{ asset($shipper->Letterhead) }}" alt="Header" width="100%">
            @endif
        </div>

        <div class="header">
            <div class="title">WEIGHT BRIDGE TICKET</div>
            <div style="font-size: 14px; font-weight: normal; margin-top: 4px;">{{ $wbt->WBT_No }}</div>
        </div>
        
        <table style="width:50%">
          <tr>
            <td style="width: 150px;">Contract No</td>
            <td>: {{ $wbt->Contract_No ?: '-' }}</td>
          </tr>
          <tr>
            <td>Vessel / Voyage</td>
            <td>: {{ $wbt->Voyage ?: '-' }}</td>
          </tr>
          <tr>
            <td>Date of Departure</td>
            <td>: {{ !empty($wbt->Departure_Date) ? date('d F Y', strtotime($wbt->Departure_Date)) : '-' }}</td>
          </tr>
          <tr>
            <td>B/L No</td>
            <td>: {{ $wbt->BL_No ?: '-' }}</td>
          </tr>
        </table>

        @if($totalDetails > 0)
            <table style="width:100%">
              <tr>
                <td style="text-align:center; width: 50%;">
                    <strong>Container ({{ $details[$i * 2]->Container ?? '-' }})</strong>
                    @if(!empty($details[$i * 2]->Seal))
                        <br><span style="font-size: 12px; color: #555;">Seal: {{ $details[$i * 2]->Seal }}</span>
                    @endif
                </td>
                @if (isset($details[($i * 2) + 1]))
                  <td style="text-align:center; width: 50%;">
                    <strong>Container ({{ $details[($i * 2) + 1]->Container ?? '-' }})</strong>
                    @if(!empty($details[($i * 2) + 1]->Seal))
                        <br><span style="font-size: 12px; color: #555;">Seal: {{ $details[($i * 2) + 1]->Seal }}</span>
                    @endif
                  </td>
                @else
                  <td style="text-align:center; width: 50%;">&nbsp;</td>
                @endif
              </tr>
              <tr>
                <td style="text-align:center; vertical-align: middle;">
                    @if(!empty($details[$i * 2]->Path_Loc))
                        <img src="{{ asset($details[$i * 2]->Path_Loc) }}" alt="" style="max-height: 220px; max-width: 95%;">
                    @else
                        <div style="height: 180px; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc; color: #888;">
                            No photo attached
                        </div>
                    @endif
                </td>
                @if (isset($details[($i * 2) + 1]))
                  <td style="text-align:center; vertical-align: middle;">
                    @if(!empty($details[($i * 2) + 1]->Path_Loc))
                        <img src="{{ asset($details[($i * 2) + 1]->Path_Loc) }}" alt="" style="max-height: 220px; max-width: 95%;">
                    @else
                        <div style="height: 180px; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc; color: #888;">
                            No photo attached
                        </div>
                    @endif
                  </td>
                @else
                  <td style="text-align:center">&nbsp;</td>
                @endif
              </tr>
            </table>
        @else
            <div style="padding: 40px; text-align: center; border: 1px dashed #ccc; color: #888; margin-top: 20px;">
                Tidak ada foto timbangan kontainer yang terlampir.
            </div>
        @endif

        <div>
          <table style="width:100%;">
            <tr>
              <td>
                <!-- Footer -->
                <div class="footer-fixed" style="margin-left:-1.3cm;margin-bottom:0;">
                    @if (!empty($shipper->Footer))
                        <img src="{{ asset($shipper->Footer) }}" alt="Footer" style="width: 100%">
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
