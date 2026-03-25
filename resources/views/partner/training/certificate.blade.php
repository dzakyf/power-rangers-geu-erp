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
        <div style="position: absolute;left: calc(13.5cm - 0.333333 * 12 * 1cm);top:8cm;font-size:52pt;color:#a7852c" class="pinyon-script-regular">Dzaky Fauzan</div>
        <div style="position: absolute;left: calc(15.5cm - 0.333333 * 11 * 1cm);top:10.2cm;font-size:21pt;color:#a7852c" class="noto-serif">PARTICIPANT</div>
        <div style="position: absolute;left: calc(9.5cm - 0.333333 * 11 * 1cm);top:11.5cm;font-size:10.3pt;color:#554415;max-width:17cm;text-align:center" class="noto-serif">Has completed the “Operational SOP” training held on August 10-16, 2026. This training covered sourcing of UCO, logistic, and operational Hub GEU Express.</div>
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

    window.print();
</script>
</html>