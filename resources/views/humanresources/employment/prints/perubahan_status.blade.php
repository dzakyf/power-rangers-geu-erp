<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  /* mPDF tidak support flexbox / grid, semua layout pakai <table> */
  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
  }
  table {
    border-collapse: collapse;
  }
  .header-table {
    width: 100%;
    margin-bottom: 10px;
  }
  .header-table td {
    border: 1px solid #000;
    padding: 4px 8px;
    vertical-align: middle;
  }
  .logo-cell {
    width: 15%;
    text-align: center;
    font-weight: bold;
    font-size: 16px;
    color: #2e7d32;
  }
  .title-row {
    text-align: center;
    font-weight: bold;
    font-size: 13px;
    margin-bottom: 10px;
  }

  .identity-info {
    width: 100%;
    margin-bottom: 10px;
  }
  .identity-info td {
    padding: 2px 4px;
    border: none;
  }
  .identity-info .label {
    width: 100px;
  }
  .identity-info .colon {
    width: 15px;
  }
  .fill-value {
    border-bottom: 1px solid #999;
    display: inline-block;
    min-width: 150px;
  }

  .main-table {
    width: 100%;
    margin-bottom: 10px;
  }
  .main-table th,
  .main-table td {
    border: 1px solid #000;
    padding: 5px 6px;
    vertical-align: top;
  }
  .main-table th {
    background-color: #f2f2f2;
    text-align: center;
    font-weight: bold;
  }
  .col-label {
    width: 16%;
    font-weight: bold;
    background-color: #fafafa;
  }

  /* Checkbox marital status - pakai table, bukan flex */
  .marital-table {
    width: 100%;
    border: none;
  }
  .marital-table td {
    border: none;
    padding: 1px 4px;
    font-size: 10px;
  }
  .checkbox-box {
    display: inline-block;
    width: 10px;
    height: 10px;
    border: 1px solid #000;
    text-align: center;
    line-height: 10px;
    font-size: 8px;
  }
  .checked {
    background-color: #000;
    color: #fff;
  }

  .deskripsi-cell {
    height: 80px;
    vertical-align: top;
  }

  .effective-row {
    margin: 8px 0 20px 0;
  }

  .sign-table {
    width: 100%;
  }
  .sign-table th {
    border: 1px solid #000;
    background-color: #f2f2f2;
    padding: 5px;
    text-align: center;
  }
  .sign-table td {
    border: 1px solid #000;
    padding: 5px 8px;
    text-align: center;
    vertical-align: bottom;
    height: 60px;
  }
  .sign-name-row td {
    height: auto;
    font-weight: bold;
    padding-top: 4px;
    border-top: none;
  }
  .sign-jabatan-row td {
    height: auto;
    font-size: 10px;
    padding-bottom: 6px;
    border-top: none;
  }
</style>
</head>
<body>

  <!-- HEADER -->
  <table class="header-table">
    <tr>
      <td class="logo-cell">GEU</td>
      <td style="width:30%;"><strong>Process Owner</strong> : HCGA</td>
      <td style="width:15%;"><strong>Revision</strong> : {{ $form->Revision ?? '#00' }}</td>
      <td style="width:20%;"><strong>Effective Date</strong> :<br>{{ $form->Effective_Date ?? '' }}</td>
      <td style="width:20%;"><strong>Doc ID</strong>:<br>{{ $form->Doc_ID ?? 'FR/HCGA/39-00' }}</td>
    </tr>
  </table>

  <div class="title-row">Form Perubahan Status</div>

  <!-- IDENTITY INFO -->
  <table class="identity-info">
    <tr>
      <td class="label">Nama</td>
      <td class="colon">:</td>
      <td style="width:35%;"><span class="fill-value">{{ $employee->Name ?? '' }}</span></td>
      <td class="label" style="width:80px;">Nomor</td>
      <td class="colon">:</td>
      <td>(diisi oleh HCGA)</td>
    </tr>
    <tr>
      <td class="label">NIK</td>
      <td class="colon">:</td>
      <td><span class="fill-value">{{ $employee->NIK ?? '' }}</span></td>
      <td colspan="3"></td>
    </tr>
    <tr>
      <td class="label">Tanggal join</td>
      <td class="colon">:</td>
      <td><span class="fill-value">{{ $employee->Join_Date ?? '' }}</span></td>
      <td colspan="3"></td>
    </tr>
    <tr>
      <td class="label">Masa kerja</td>
      <td class="colon">:</td>
      <td><span class="fill-value">{{ $masaKerja ?? '' }}</span></td>
      <td colspan="3"></td>
    </tr>
  </table>

  <!-- MAIN TABLE -->
  <table class="main-table">
    <tr>
      <th style="width:16%;">Identitas</th>
      <th style="width:42%;">Saat ini</th>
      <th style="width:42%;">Usulan</th>
    </tr>
    <tr>
      <td class="col-label">Jabatan</td>
      <td>{{ $current->Jabatan ?? '' }}</td>
      <td>{{ $proposed->Jabatan ?? '' }}</td>
    </tr>
    <tr>
      <td class="col-label">Divisi</td>
      <td>{{ $current->Divisi ?? '' }}</td>
      <td>{{ $proposed->Divisi ?? '' }}</td>
    </tr>
    <tr>
      <td class="col-label">Departemen</td>
      <td>{{ $current->Departemen ?? '' }}</td>
      <td>{{ $proposed->Departemen ?? '' }}</td>
    </tr>
    <tr>
      <td class="col-label">Grade</td>
      <td>{{ $current->Grade ?? '' }}</td>
      <td>{{ $proposed->Grade ?? '' }}</td>
    </tr>
    <tr>
      <td class="col-label">Lokasi kerja</td>
      <td>{{ $current->Lokasi_Kerja ?? '' }}</td>
      <td>{{ $proposed->Lokasi_Kerja ?? '' }}</td>
    </tr>
    <tr>
      <td class="col-label">Status kekaryawanan</td>
      <td>{{ $current->Status_Kekaryawanan ?? '' }}</td>
      <td>{{ $proposed->Status_Kekaryawanan ?? '' }}</td>
    </tr>
    <tr>
      <td class="col-label">Status perkawinan (dan jumlah tanggungan)</td>
      <td>
        @php
          // $current->Marital_Status contoh nilai: 'TK/0', 'K/2', dst.
          $currentMarital = $current->Marital_Status ?? '';
        @endphp
        <table class="marital-table">
          <tr>
            <td style="width:50%;">
              <span class="checkbox-box {{ str_starts_with($currentMarital,'TK') && $currentMarital === 'TK' ? 'checked' : '' }}">&nbsp;</span> Tidak kawin (TK)<br>
              <span class="checkbox-box {{ $currentMarital === 'TK/0' ? 'checked' : '' }}">&nbsp;</span> TK/0<br>
              <span class="checkbox-box {{ $currentMarital === 'TK/1' ? 'checked' : '' }}">&nbsp;</span> TK/1<br>
              <span class="checkbox-box {{ $currentMarital === 'TK/2' ? 'checked' : '' }}">&nbsp;</span> TK/2<br>
              <span class="checkbox-box {{ $currentMarital === 'TK/3' ? 'checked' : '' }}">&nbsp;</span> TK/3
            </td>
            <td style="width:50%;">
              <span class="checkbox-box {{ $currentMarital === 'K' ? 'checked' : '' }}">&nbsp;</span> Kawin (K)<br>
              <span class="checkbox-box {{ $currentMarital === 'K/0' ? 'checked' : '' }}">&nbsp;</span> K/0<br>
              <span class="checkbox-box {{ $currentMarital === 'K/1' ? 'checked' : '' }}">&nbsp;</span> K/1<br>
              <span class="checkbox-box {{ $currentMarital === 'K/2' ? 'checked' : '' }}">&nbsp;</span> K/2<br>
              <span class="checkbox-box {{ $currentMarital === 'K/3' ? 'checked' : '' }}">&nbsp;</span> K/3
            </td>
          </tr>
        </table>
      </td>
      <td>
        @php
          $proposedMarital = $proposed->Marital_Status ?? '';
        @endphp
        <table class="marital-table">
          <tr>
            <td style="width:50%;">
              <span class="checkbox-box {{ $proposedMarital === 'TK' ? 'checked' : '' }}">&nbsp;</span> Tidak kawin (TK)<br>
              <span class="checkbox-box {{ $proposedMarital === 'TK/0' ? 'checked' : '' }}">&nbsp;</span> TK/0<br>
              <span class="checkbox-box {{ $proposedMarital === 'TK/1' ? 'checked' : '' }}">&nbsp;</span> TK/1<br>
              <span class="checkbox-box {{ $proposedMarital === 'TK/2' ? 'checked' : '' }}">&nbsp;</span> TK/2<br>
              <span class="checkbox-box {{ $proposedMarital === 'TK/3' ? 'checked' : '' }}">&nbsp;</span> TK/3
            </td>
            <td style="width:50%;">
              <span class="checkbox-box {{ $proposedMarital === 'K' ? 'checked' : '' }}">&nbsp;</span> Kawin (K)<br>
              <span class="checkbox-box {{ $proposedMarital === 'K/0' ? 'checked' : '' }}">&nbsp;</span> K/0<br>
              <span class="checkbox-box {{ $proposedMarital === 'K/1' ? 'checked' : '' }}">&nbsp;</span> K/1<br>
              <span class="checkbox-box {{ $proposedMarital === 'K/2' ? 'checked' : '' }}">&nbsp;</span> K/2<br>
              <span class="checkbox-box {{ $proposedMarital === 'K/3' ? 'checked' : '' }}">&nbsp;</span> K/3
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="col-label">Salary</td>
      <td>Rp. {{ number_format($current->Salary ?? 0, 0, ',', '.') }},-</td>
      <td>Rp. {{ number_format($proposed->Salary ?? 0, 0, ',', '.') }},-</td>
    </tr>
    <tr>
      <td class="col-label">Benefit</td>
      <td>{{ $current->Benefit ?? '' }}</td>
      <td>{{ $proposed->Benefit ?? '' }}</td>
    </tr>
    <tr>
      <td class="col-label">Deskripsi</td>
      <td class="deskripsi-cell" colspan="2">{{ $deskripsi ?? '' }}</td>
    </tr>
  </table>

  <!-- EFFECTIVE DATE -->
  <div class="effective-row">
    Perubahan status ini berlaku sejak tanggal : <span class="fill-value">{{ $effectiveDate ?? '' }}</span>
  </div>

  <!-- SIGNATURES -->
  <table class="sign-table">
    <tr>
      <th style="width:33%;">Diusulkan oleh</th>
      <th style="width:34%;">Disetujui</th>
      <th style="width:33%;">Diverifikasi</th>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr class="sign-name-row">
      <td>{{ $proposedBy->Name ?? '(Nama)' }}</td>
      <td>{{ $approvedBy->Name ?? '(Nama)' }}</td>
      <td>{{ $verifiedBy->Name ?? 'Hendra Darmawan' }}</td>
    </tr>
    <tr class="sign-jabatan-row">
      <td>{{ $proposedBy->Jabatan ?? 'Jabatan' }}</td>
      <td>{{ $approvedBy->Jabatan ?? 'Jabatan' }}</td>
      <td>{{ $verifiedBy->Jabatan ?? 'Vice President Operation' }}</td>
    </tr>
  </table>

</body>
</html>