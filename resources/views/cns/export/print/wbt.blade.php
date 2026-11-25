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
            width: 100%;
            margin-top: 0;
            margin-left: 0;
            margin-right: 0;
            box-shadow: none;
        }
        .sheet {
          background: #ffffff;
        }
    }

    .wbt-sheet {
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        padding: 0;
    }

    .wbt-header,
    .wbt-top,
    .wbt-footer {
        flex-shrink: 0;
        margin: 0;
        padding: 0;
    }

    .wbt-body {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        padding: 6px 10mm;
    }

    .wbt-grid {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr;
        border-top: 1px solid #000;
        border-left: 1px solid #000;
        margin-top: 8px;
    }

    .wbt-slot {
        display: flex;
        flex-direction: column;
        min-height: 0;
        min-width: 0;
        border-right: 1px solid #000;
        border-bottom: 1px solid #000;
    }

    .wbt-slot-head {
        flex-shrink: 0;
        text-align: center;
        padding: 6px 8px;
        border-bottom: 1px solid #000;
        font-size: 13px;
    }

    .wbt-slot-nopol {
        flex-shrink: 0;
        text-align: center;
        padding: 4px 8px;
        border-bottom: 1px solid #000;
        font-size: 12px;
        color: #555;
    }

    .wbt-slot-photo {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        overflow: hidden;
    }

    .wbt-slot-photo img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
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
    $letterheadPath = $shipper->Letterhead ?? $shipper->LetterHead ?? '';
    $footerPath = $shipper->Footer ?? '';
@endphp

@for($i = 0; $i < $totalPages; $i++)
    <div class="sheet wbt-sheet">
        <!-- Header -->
        <div class="wbt-header">
            @if(!empty($letterheadPath))
                <img src="{{ asset($letterheadPath) }}" alt="Header" style="width: 100%; display: block; margin: 0; padding: 0;">
            @endif
        </div>

        <div class="wbt-body">
            <div class="wbt-top">
                <div class="header">
                    <div class="title" style="margin-top: 0;">WEIGHT BRIDGE TICKET</div>
                    <div style="font-size: 14px; font-weight: normal;">{{ $wbt->WBT_No }}</div>
                </div>

                <table style="width:50%; margin-bottom: 4px;">
                  <tr>
                    <td style="width: 150px; padding: 3px 8px;">Contract No</td>
                    <td style="padding: 3px 8px;">: {{ $wbt->Contract_No ?: '-' }}</td>
                  </tr>
                  <tr>
                    <td style="padding: 3px 8px;">Vessel / Voyage</td>
                    <td style="padding: 3px 8px;">: {{ $wbt->Voyage ?: '-' }}</td>
                  </tr>
                  <tr>
                    <td style="padding: 3px 8px;">Date of Departure</td>
                    <td style="padding: 3px 8px;">: {{ !empty($wbt->Departure_Date) ? date('d F Y', strtotime($wbt->Departure_Date)) : '-' }}</td>
                  </tr>
                  <tr>
                    <td style="padding: 3px 8px;">B/L No</td>
                    <td style="padding: 3px 8px;">: {{ $wbt->BL_No ?: '-' }}</td>
                  </tr>
                </table>
            </div>

            @if($totalDetails > 0)
                <div class="wbt-grid">
                    @for($col = 0; $col < 2; $col++)
                        @php $item = $details[($i * 2) + $col] ?? null; @endphp
                        <div class="wbt-slot">
                            @if($item)
                                <div class="wbt-slot-head">
                                    <strong>Container ({{ $item->Container ?? '-' }})</strong>
                                    @if(!empty($item->Seal))
                                        <br><span style="font-size: 12px; color: #555;">Seal: {{ $item->Seal }}</span>
                                    @endif
                                </div>
                                <div class="wbt-slot-nopol">
                                    Nopol: {{ !empty($item->Police_No) ? $item->Police_No : '-' }}
                                </div>
                                <div class="wbt-slot-photo">
                                    @if(!empty($item->Path_Loc))
                                        <img src="{{ asset($item->Path_Loc) }}" alt="">
                                    @else
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc; color: #888;">
                                            No photo attached
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="wbt-slot-head">&nbsp;</div>
                                <div class="wbt-slot-nopol">&nbsp;</div>
                                <div class="wbt-slot-photo">&nbsp;</div>
                            @endif
                        </div>
                    @endfor
                </div>
            @else
                <div style="flex: 1 1 auto; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc; color: #888; margin-top: 8px;">
                    Tidak ada foto timbangan kontainer yang terlampir.
                </div>
            @endif
        </div>

        <div class="wbt-footer">
            <!-- Footer -->
            @if (!empty($footerPath))
                <img src="{{ asset($footerPath) }}" alt="Footer" style="width: 100%; display: block; margin: 0; padding: 0;">
            @endif
        </div>
    </div>
@endfor
</body>
</html>
