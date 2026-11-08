<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Offering Letter - {{ $offeringNumber ?? ($recruitment->Task_ID ?? 'Document') }}</title>
    <style>
        @page {
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Calibri', 'Arial', 'Helvetica', sans-serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000000;
        }
        .header-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .header-ref {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 18px;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 12px;
        }
        .content-table th, .content-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .content-table td.label-col {
            width: 30%;
            font-weight: normal;
        }
        .inner-salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .inner-salary-table td {
            border: none;
            padding: 2px 4px;
            font-size: 10pt;
        }
        .salary-total-row {
            font-weight: bold;
            border-top: 1px solid #000000 !important;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border: none;
        }
        .signature-table td {
            border: none;
            vertical-align: top;
            font-size: 11pt;
        }
        .signature-box {
            height: 60px;
        }
    </style>
</head>
<body>

    <div class="header-title">OFFERING LETTER</div>
    <div class="header-ref">No. {{ $offeringNumber }}</div>

    <p style="margin-top: 0; margin-bottom: 12px;">
        Kepada Yth.<br>
        <strong>{{ $recruitment->Name }}</strong><br>
        Di Tempat
    </p>

    <p style="margin-top: 0; margin-bottom: 10px; text-align: justify;">
        Kami ucapkan selamat telah berhasil melewati proses rekrutmen yang dilakukan di PT Green Energi Utama dengan baik, hingga tahap Surat Penawaran ini.
    </p>

    <p style="margin-top: 0; margin-bottom: 10px; text-align: justify;">
        Dengan antusiasme, kami bermaksud mengajukan penawaran pekerjaan ini pada <strong>{{ $recruitment->Name }}</strong>, dengan detail sebagai berikut:
    </p>

    <table class="content-table">
        <tr>
            <td class="label-col">Posisi</td>
            <td>{{ $position }}</td>
        </tr>
        <tr>
            <td class="label-col">Grading</td>
            <td>{{ $grading }}</td>
        </tr>
        <tr>
            <td class="label-col">Lokasi Kerja</td>
            <td>{{ $workLocation }}</td>
        </tr>
        <tr>
            <td class="label-col">Status Kekaryawanan</td>
            <td>{{ $contractType }}</td>
        </tr>
        <tr>
            <td class="label-col">Pelaporan Langsung</td>
            <td>{{ $directSupervisor }}</td>
        </tr>
        <tr>
            <td class="label-col">Hari dan Jam Kerja</td>
            <td style="white-space: pre-line; text-align: justify;">{!! nl2br(e($workingSchedule)) !!}</td>
        </tr>
        <tr>
            <td class="label-col">Penggajian</td>
            <td>
                @if(!empty($salaryPeriodNote))
                    <div style="margin-bottom: 5px;">{{ $salaryPeriodNote }}</div>
                @else
                    <div style="margin-bottom: 5px;">Periode Penggajian 26 sd 25 (bulan ke bulan) dibayarkan tgl 1-5 (bulan berjalan)</div>
                @endif

                <table class="inner-salary-table">
                    @if(!empty($salaryDetails) && count($salaryDetails) > 0)
                        @foreach($salaryDetails as $item)
                            @php
                                $itemLabel = $item['label'] ?? 'Komponen';
                                $itemVal = (float)($item['amount'] ?? 0);
                            @endphp
                            @if($itemVal > 0)
                                <tr>
                                    <td style="width: 48%;">{{ $itemLabel }}</td>
                                    <td style="width: 4%;">:</td>
                                    <td style="width: 48%; text-align: right;">Rp {{ number_format($itemVal, 0, ',', '.') }},-</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td style="width: 48%;">Gaji Pokok</td>
                            <td style="width: 4%;">:</td>
                            <td style="width: 48%; text-align: right;">Rp {{ number_format($salaryBase, 0, ',', '.') }},-</td>
                        </tr>
                        @if($salaryCola > 0)
                        <tr>
                            <td>Cost Of Living Allowance (COLA)</td>
                            <td>:</td>
                            <td style="text-align: right;">Rp {{ number_format($salaryCola, 0, ',', '.') }},-</td>
                        </tr>
                        @endif
                        @if($salaryAllowance > 0)
                        <tr>
                            <td>Tunjangan Operasional / Jabatan</td>
                            <td>:</td>
                            <td style="text-align: right;">Rp {{ number_format($salaryAllowance, 0, ',', '.') }},-</td>
                        </tr>
                        @endif
                    @endif

                    <tr class="salary-total-row">
                        <td><strong>Total Salary</strong></td>
                        <td>:</td>
                        <td style="text-align: right;"><strong>Rp {{ number_format($salaryTotal, 0, ',', '.') }},- gross</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label-col">Kompensasi Benefit</td>
            <td style="white-space: pre-line; text-align: justify;">{!! nl2br(e($benefitDesc)) !!}</td>
        </tr>
        <tr>
            <td class="label-col">Ketentuan</td>
            <td style="white-space: pre-line; text-align: justify;">{!! nl2br(e($offeringTerms)) !!}</td>
        </tr>
    </table>

    <p style="margin-top: 12px; margin-bottom: 20px; text-align: justify;">
        Silahkan cetak surat penawaran resmi ini, tandatangani basah bermaterai, scan dokumen dan kembalikan kepada kami dalam jangka waktu 1x24 jam. Atas perhatiannya, kami ucapkan terima kasih.
    </p>

    {{-- TANDA TANGAN DUA BELAH PIHAK SESUAI TEMPLATE CONTOH --}}
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: left;">
                {{ $cityDate }}<br>
                <strong>PT. GREEN ENERGI UTAMA</strong>
                <div class="signature-box"></div>
                <strong>( Human Capital Department )</strong>
            </td>
            <td style="width: 50%; text-align: left; padding-left: 20px;">
                Menyetujui isi surat penawaran ini dan<br>
                akan join tanggal : <strong>{{ $startDateFormatted }}</strong>
                <div class="signature-box" style="line-height: 60px; color: #777; font-size: 8.5pt;">
                    <em>[Materai Rp 10.000]</em>
                </div>
                <strong>{{ $recruitment->Name }}</strong><br>
                <span>Kandidat</span>
            </td>
        </tr>
    </table>

</body>
</html>
