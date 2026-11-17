<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Exit Clearance - {{ $clearance->No_Trx }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 4px;
            border: 1px solid #000;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .content-table th, .content-table td {
            border: 1px solid #000;
            padding: 3px 5px;
        }
        .content-table th {
            background-color: #eaeaea;
            font-weight: bold;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
        }
        .signature-box {
            height: 55px;
        }
        .notes-box {
            border: 1px solid #000;
            padding: 5px;
            min-height: 40px;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

    {{-- Header Form --}}
    <table class="header-table">
        <tr>
            <td colspan="4" class="title">Form Exit Interview & Clearance</td>
        </tr>
        <tr>
            <td><strong>Process Owner:</strong> HCGA</td>
            <td><strong>Revision:</strong> #00</td>
            <td><strong>Effective Date:</strong> 8 Januari 2024</td>
            <td><strong>Doc ID:</strong> FR/HCGA/07-00</td>
        </tr>
        <tr>
            <td colspan="2"><strong>No. Dokumen:</strong> {{ $clearance->No_Trx }}</td>
            <td colspan="2"><strong>Ref Resign:</strong> {{ $resignation->No_Trx ?? '-' }}</td>
        </tr>
    </table>

    {{-- Profil Karyawan --}}
    <table class="content-table">
        <tr>
            <td style="width: 15%;"><strong>NAMA</strong></td>
            <td style="width: 35%;">: {{ $clearance->Nama }}</td>
            <td style="width: 15%;"><strong>DEPARTEMEN</strong></td>
            <td style="width: 35%;">: {{ $clearance->Departemen ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>JABATAN</strong></td>
            <td>: {{ $clearance->Jabatan ?? '-' }}</td>
            <td><strong>TGL MASUK</strong></td>
            <td>: {{ $clearance->Join_Date ? date('d/m/Y', strtotime($clearance->Join_Date)) : '-' }}</td>
        </tr>
        <tr>
            <td><strong>DIVISI</strong></td>
            <td>: {{ $clearance->Divisi ?? '-' }}</td>
            <td><strong>TGL KELUAR</strong></td>
            <td>: {{ $clearance->Exit_Date ? date('d/m/Y', strtotime($clearance->Exit_Date)) : '-' }}</td>
        </tr>
    </table>

    {{-- Ceklist Karyawan Keluar (17 Butir Item) --}}
    <div class="section-title">E. CEKLIST KARYAWAN KELUAR (INVENTARIS & KEWAJIBAN)</div>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Item</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 20%;">Keterangan</th>
                <th style="width: 15%;">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checklist as $idx => $item)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->Item_Name }}</td>
                    <td style="text-align: center;">{{ $item->Status }}</td>
                    <td>{{ $item->Keterangan ?? '-' }}</td>
                    <td>{{ $item->Penanggung_Jawab ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Catatan Hasil Wawancara Exit Interview & Verifikasi --}}
    <div class="section-title">CATATAN HASIL WAWANCARA EXIT INTERVIEW & VERIFIKASI</div>
    <div class="notes-box">
        <strong>Hasil Wawancara Exit Interview:</strong><br>
        {{ $clearance->exit_interview_notes ?? 'Belum ada catatan wawancara.' }}
    </div>

    <div class="notes-box">
        <strong>Catatan Manager / Dept. Head:</strong><br>
        {{ $clearance->manager_notes ?? 'Tidak ada catatan khusus.' }}
    </div>

    <div class="notes-box">
        <strong>Catatan HCGA:</strong><br>
        {{ $clearance->hcga_notes ?? 'Verifikasi administrasi & cut-off disetujui.' }}
    </div>

    {{-- Pernyataan & Tanda Tangan --}}
    <div style="font-size: 10px; margin-top: 6px; margin-bottom: 6px;">
        <em>Saya menyatakan bahwa seluruh aset perusahaan telah saya kembalikan dan seluruh kewajiban kepada perusahaan telah saya selesaikan. Saya juga berkomitmen untuk menjaga kerahasiaan informasi perusahaan setelah berakhirnya hubungan kerja.</em>
    </div>

    <table class="signature-table">
        <tr>
            <td><strong>Dibuat</strong><br>Karyawan</td>
            <td><strong>Diketahui</strong><br>Manager / Dept. Head</td>
            <td><strong>Diverifikasi</strong><br>HCGA</td>
        </tr>
        <tr>
            <td class="signature-box">
                <br><br><br>
                ( {{ $clearance->Nama }} )<br>
                Tgl: {{ $clearance->Employee_Submitted_At ? date('d/m/Y', strtotime($clearance->Employee_Submitted_At)) : date('d/m/Y') }}
            </td>
            <td class="signature-box">
                <br><br><br>
                ( ......................................... )<br>
                Tgl: {{ $clearance->Manager_Interview_At ? date('d/m/Y', strtotime($clearance->Manager_Interview_At)) : '......................' }}
            </td>
            <td class="signature-box">
                <br><br><br>
                ( ......................................... )<br>
                Tgl: {{ $clearance->Hcga_Verified_At ? date('d/m/Y', strtotime($clearance->Hcga_Verified_At)) : '......................' }}
            </td>
        </tr>
    </table>

</body>
</html>

