<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Surat</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
            line-height: 1.6;
            color: #000;
        }
        .container {
            width: 100%;
            max-width: 700px;
            margin: auto;
        }
        .content {
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Yth. Bapak/Ibu,</p>

        <div class="content">
            <p>
                Dengan ini kami menyampaikan bahwa berdasarkan kesepakatan yang telah dibuat,
                seluruh pihak diharapkan dapat menjalankan kewajibannya masing-masing.
                Apabila terdapat kendala, harap segera dikomunikasikan.
            </p>
            <table>
                <tr>
                    <td>Nama Training</td>
                    <td>:</td>
                    <td>{{ $training->training_name }}</td>
                </tr>
                <tr>
                    <td>Tanggal Training</td>
                    <td>:</td>
                    <td>{{ $training->date }}</td>
                </tr>
                <tr>
                    <td>Waktu Training</td>
                    <td>:</td>
                    <td>{{ $training->time }}</td>
                </tr>
                <tr>
                    <td>Tempat Training</td>
                    <td>:</td>
                    <td>{{ ucwords($training->method) }}</td>
                </tr>
            </table>
            <p>Diharapkan Bapak/Ibu dapat mengikuti kegiatan ini sesuai jadwal yang telah ditentukan.</p>
            <p>Atas perhatian dan partisipasinya, kami ucapkan terima kasih.</p>
        </div>

        <br>

        <p>Hormat kami,</p>
        <p><strong>{{ auth()->user()->Name }}</strong></p>
    </div>
</body>
</html>