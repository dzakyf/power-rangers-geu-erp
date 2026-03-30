@php
    $start = new DateTime($training->training_date_from);
    $end = new DateTime($training->training_date_to);
    $date = "";

    // Same exact day
    if ($start == $end) {
        $date = $start->format('F j, Y');
    }else

    // Same year
    if ($start->format('Y') == $end->format('Y')) {

        // Same month
        if ($start->format('m') === $end->format('m')) {
            $date = $start->format('F j') . '-' . $end->format('j, Y');
        }

        // Different month but same year
        $date = $start->format('F j') . ' - ' . $end->format('F j, Y');
    }else 

    // Different year
    $date = $start->format('F j, Y') . ' - ' . $end->format('F j, Y');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Certificate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif&family=Pinyon+Script&display=swap" rel="stylesheet">
    <style>
        .pinyon-script-regular {
            font-family: "Pinyon Script", cursive;
            font-weight: 400;
            font-style: normal;
        }
        .noto-serif {
            font-family: "Noto Serif", serif;
            font-weight: 400;
            font-style: normal;
        }

    </style>
</head>
<body>
    <div style="position: relative">
        <img src="/geu/hcga/public/assets/images/SERTIFIKAT.png" style="width: 29.2cm" alt="Sertifikat">
        <div style="position: absolute;left: calc(13.5cm - 0.333333 * {{strlen($certificate->participant_name)}} * 1cm);top:8cm;font-size:52pt;color:#a7852c" class="pinyon-script-regular">{{ ucfirst(strtolower($certificate->participant_name)) }}</div>
        <div style="position: absolute;left: calc(15.5cm - 0.333333 * 11 * 1cm);top:10.2cm;font-size:21pt;color:#a7852c" class="noto-serif">PARTICIPANT</div>
        <div style="position: absolute;left: calc(9.5cm - 0.333333 * 11 * 1cm);top:11.5cm;font-size:10.3pt;color:#554415;max-width:17cm;text-align:center" class="noto-serif">Has completed the “{{ $training->training_name }}” training held on {{ $date }}. {{ $training->training_coverage != '' ? "This training covered " . $training->training_coverage : '' }}</div>
        <div style="position: absolute;left: calc(5cm - 0.333333 * {{strlen($certificate->signature1_name)}} * 1cm);top:16cm;font-size:19pt;color:#a7852c" class="noto-serif">{{ $signature->signature1_name ?? '' }}</div>
        <div style="position: absolute;left: calc(12.6cm - 0.333333 * {{strlen($certificate->signature2_name)}} * 1cm);top:16cm;font-size:19pt;color:#a7852c" class="noto-serif">{{ $signature->signature2_name ?? '' }}</div>
        <div style="position: absolute;left: calc(20cm - 0.333333 * {{strlen($certificate->signature3_name)}} * 1cm);top:16cm;font-size:19pt;color:#a7852c" class="noto-serif">{{ $signature->signature3_name ?? '' }}</div>
        <div style="position: absolute;left: calc(13cm - 0.333333 * {{strlen($certificate->signature1_position ?? '')}} * 1cm);top:17cm;font-size:16pt;color:#554415;max-width:17cm;text-align:center;font-style: italic;" class="noto-serif"> {{ $signature->signature1_position ?? '' }}</div>
        <div style="position: absolute;left: calc(5cm - 0.333333 * {{strlen($certificate->signature2_position ?? '')}} * 1cm);top:17cm;font-size:16pt;color:#554415;max-width:17cm;text-align:center;font-style: italic;" class="noto-serif"> {{ $signature->signature2_position ?? '' }}</div>
        <div style="position: absolute;left: calc(20cm - 0.333333 * {{strlen($certificate->signature3_position ?? '')}} * 1cm);top:17cm;font-size:16pt;color:#554415;max-width:17cm;text-align:center;font-style: italic;" class="noto-serif"> {{ $signature->signature3_position ?? '' }}</div>
    </div>
</body>
<script>
    var css = '@page { size: landscape; }',
    head = document.head || document.getElementsByTagName('head')[0],
    style = document.createElement('style');

    style.type = 'text/css';
    style.media = 'print';

    if (style.styleSheet){
    style.styleSheet.cssText = css;
    } else {
    style.appendChild(document.createTextNode(css));
    }

    head.appendChild(style);

    // window.print();
</script>
</html>