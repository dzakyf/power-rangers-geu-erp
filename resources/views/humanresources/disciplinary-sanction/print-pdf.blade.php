<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Sanksi - {{ $sanction->No_Trx }}</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.4;
        }
        .table-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .table-header td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 9pt;
            vertical-align: middle;
        }
        .header-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            padding: 6px;
        }
        .title-bar {
            background-color: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 4px;
            font-size: 11pt;
            margin-bottom: 12px;
        }
        .table-info {
            width: 100%;
            margin-bottom: 12px;
        }
        .table-info td {
            padding: 2px 4px;
            font-size: 10.5pt;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
        }
        .checkbox-group {
            margin: 6px 0;
            font-size: 10.5pt;
        }
        .box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 9pt;
            font-weight: bold;
            margin-right: 4px;
        }
        .commitment-box {
            border: 1px solid #000;
            min-height: 55px;
            padding: 6px 8px;
            font-size: 10pt;
            margin-top: 4px;
            margin-bottom: 12px;
            background-color: #fafafa;
        }
        .table-signature {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            text-align: center;
        }
        .table-signature td {
            padding: 4px;
            font-size: 10pt;
            vertical-align: top;
        }
        .sig-space {
            height: 60px;
        }
        .sig-name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 130px;
        }
        .tembusan {
            margin-top: 10px;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    {{-- Header Tabel FR/HCGA/10-00 --}}
    <table class="table-header">
        <tr>
            <td rowspan="2" style="width: 25%; text-align: center;">
                <strong style="font-size: 18pt; color: #166534; letter-spacing: 1px;">GEU</strong><br>
                <span style="font-size: 7.5pt; color: #444;">PT. GRAHA ENERGI NUSANTARA</span>
            </td>
            <td colspan="3" class="header-title">
                Form Surat Sanksi
            </td>
        </tr>
        <tr>
            <td style="width: 25%;">
                <strong>Process Owner :</strong> HCGA<br>
                <strong>Revision :</strong> #00
            </td>
            <td style="width: 25%;">
                <strong>Effective Date :</strong><br>
                8 Januari 2024
            </td>
            <td style="width: 25%;">
                <strong>Doc ID :</strong><br>
                FR/HCGA/10-00
            </td>
        </tr>
    </table>

    <div class="title-bar">
        Surat Sanksi
    </div>

    <div style="margin-bottom: 6px; font-weight: bold;">
        Diberikan kepada karyawan tersebut dibawah ini :
    </div>

    <table class="table-info">
        <tr>
            <td style="width: 22%;">Nomor</td>
            <td style="width: 3%;">:</td>
            <td style="width: 75%; font-weight: bold;">{{ $sanction->No_Trx }}</td>
        </tr>
        <tr>
            <td>Nama karyawan</td>
            <td>:</td>
            <td class="dotted-line">{{ $sanction->Nama }}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>:</td>
            <td class="dotted-line">{{ $sanction->NIK ?? '-' }}</td>
        </tr>
        <tr>
            <td>Posisi</td>
            <td>:</td>
            <td class="dotted-line">{{ $sanction->Jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Divisi / Dept.</td>
            <td>:</td>
            <td class="dotted-line">{{ $sanction->Divisi ?? ($sanction->Departemen ?? '-') }}</td>
        </tr>
    </table>

    <div style="margin-bottom: 4px;">
        Bahwa pada Saudara telah melakukan pelanggaran Perjanjian Kerja / Peraturan Perusahaan, yaitu :
    </div>

    <div style="margin-left: 15px; margin-bottom: 8px;">
        @if(!empty($points) && count($points) > 0)
            @foreach($points as $idx => $p)
                @if(!empty(trim($p)))
                    <div style="margin-bottom: 3px;">{{ $idx + 1 }}. {{ trim($p) }}</div>
                @endif
            @endforeach
        @else
            <div>1. -</div>
            <div>2. -</div>
        @endif
    </div>

    <div style="margin-bottom: 6px;">
        Hal tersebut berdasarkan : <strong>{{ $sanction->Evidence_Basis }}</strong>
        @if(!empty($sanction->Evidence_Description))
            <span style="font-style: italic;">({{ $sanction->Evidence_Description }})</span>
        @endif
    </div>

    <div style="margin-bottom: 4px;">
        Setelah dilakukan pemeriksaan dan klarifikasi, terbukti bahwa perbuatan Saudara termasuk <strong>pelanggaran yang bersifat :</strong>
    </div>

    <div class="checkbox-group" style="margin-left: 20px;">
        <span class="box">{{ $sanction->Violation_Severity === 'RINGAN' ? 'X' : '' }}</span> Ringan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="box">{{ $sanction->Violation_Severity === 'SEDANG' ? 'X' : '' }}</span> Sedang &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="box">{{ $sanction->Violation_Severity === 'BERAT' ? 'X' : '' }}</span> Berat
    </div>

    <div style="margin-top: 6px; margin-bottom: 4px;">
        Sesuai dengan Peraturan yang berlaku di perusahaan, maka kepada Saudara diberikan :
    </div>

    <div class="checkbox-group" style="margin-left: 20px;">
        <span class="box">{{ $sanction->Sanction_Level === 'LISAN' ? 'X' : '' }}</span> Peringatan Lisan &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="box">{{ $sanction->Sanction_Level === 'SP1' ? 'X' : '' }}</span> Peringatan Pertama &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="box">{{ $sanction->Sanction_Level === 'SP2' ? 'X' : '' }}</span> Peringatan Kedua &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="box">{{ $sanction->Sanction_Level === 'SP3' ? 'X' : '' }}</span> Peringatan Ketiga
    </div>

    <div style="margin-top: 6px; margin-bottom: 8px;">
        Dengan jangka waktu : <strong>{{ $sanction->Duration_Months }} bulan</strong>, mulai dari tanggal <strong>{{ date('d F Y', strtotime($sanction->Valid_From)) }}</strong> hingga <strong>{{ date('d F Y', strtotime($sanction->Valid_Until)) }}</strong>.
    </div>

    <div style="font-size: 9.5pt; text-align: justify; margin-bottom: 6px;">
        Bilamana dikemudian hari ternyata Saudara berbuat pelanggaran yang serupa dan atau dalam bentuk lain dengan kategori lebih ringan dan atau lebih berat, maka perusahaan akan mengambil tindakan yang lebih tegas sesuai dengan ketentuan yang berlaku.
    </div>

    <div style="font-size: 9.5pt; text-align: justify; margin-bottom: 8px;">
        Perusahaan berharap pada waktu mendatang Saudara dapat memperbaiki sikap kerja dan bersungguh-sungguh menjadi contoh yang baik bagi karyawan lain.
    </div>

    <div style="font-size: 10pt; margin-bottom: 4px;">
        Surat peringatan ini diberikan oleh: <strong>{{ $sanction->Reporter_Nama }}</strong> &nbsp;&nbsp;&nbsp;&nbsp; Jabatan: <strong>{{ $sanction->Reporter_Jabatan ?? 'Pimpinan Departemen' }}</strong>
    </div>

    <div style="font-size: 10pt; font-weight: bold; margin-bottom: 2px;">
        Komitmen karyawan :
    </div>
    <div class="commitment-box">
        {{ $sanction->Employee_Commitment ?: '(Karyawan belum mencatatkan komitmen perbaikan)' }}
    </div>

    <div style="font-size: 10pt; font-weight: bold; margin-bottom: 4px;">
        Dinyatakan sah apabila telah ditanda-tangani oleh :
    </div>

    <table class="table-signature">
        <tr>
            <td colspan="2" style="width: 50%; font-weight: bold;">Dibuat Oleh,</td>
            <td style="width: 50%; font-weight: bold;">Mengetahui,</td>
        </tr>
        <tr>
            <td class="sig-space" style="width: 25%;">
                @if(!empty($sanction->Employee_Signed_At))
                    <div style="color: #166534; font-size: 8pt; margin-top: 15px;">
                        [SIGNED DIGITALLY]<br>
                        {{ date('d/m/Y H:i', strtotime($sanction->Employee_Signed_At)) }}
                    </div>
                @endif
            </td>
            <td class="sig-space" style="width: 25%;">
                @if(!empty($sanction->HCGA_Approved_At))
                    <div style="color: #166534; font-size: 8pt; margin-top: 15px;">
                        [VERIFIED HCGA]<br>
                        {{ date('d/m/Y H:i', strtotime($sanction->HCGA_Approved_At)) }}
                    </div>
                @endif
            </td>
            <td class="sig-space" style="width: 50%;">
                <div style="color: #166534; font-size: 8pt; margin-top: 15px;">
                    [APPROVED DEPT]<br>
                    {{ date('d/m/Y H:i', strtotime($sanction->created_at)) }}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="sig-name">{{ $sanction->Nama }}</span><br>
                Karyawan
            </td>
            <td>
                <span class="sig-name">HCGA</span><br>
                Human Capital & GA
            </td>
            <td>
                <span class="sig-name">{{ $sanction->Reporter_Nama }}</span><br>
                Pimpinan Departemen
            </td>
        </tr>
    </table>

    <div class="tembusan">
        <strong>Tembusan :</strong><br>
        1. Karyawan ybs.<br>
        2. HCGA
    </div>

</body>
</html>

