<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Perubahan Status</title>
<style>
  @page {
    size: A4;
    margin: 15mm;
  }
  * { box-sizing: border-box; }
  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
    margin: 0;
    padding: 0;
  }
  .container {
    border: 1px solid #000;
    padding: 10px;
  }

  /* HEADER */
  table.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
  }
  table.header-table td {
    border: 1px solid #000;
    padding: 4px 8px;
    vertical-align: middle;
  }
  .logo-cell {
    width: 15%;
    text-align: center;
  }
  .logo-cell img {
    max-width: 60px;
    max-height: 50px;
  }
  .logo-text {
    font-weight: bold;
    font-size: 16px;
    color: #2e7d32;
  }
  .title-cell {
    text-align: center;
    font-weight: bold;
    font-size: 13px;
  }
  .meta-label {
    font-size: 10px;
    color: #333;
  }

  /* IDENTITY SECTION */
  .identity-info {
    width: 100%;
    margin-bottom: 12px;
    border-collapse: collapse;
  }
  .identity-info td {
    padding: 2px 4px;
    font-size: 11px;
  }
  .identity-info .label {
    width: 100px;
  }
  .identity-info .colon {
    width: 15px;
  }

  /* MAIN TABLE */
  table.main-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
  }
  table.main-table th,
  table.main-table td {
    border: 1px solid #000;
    padding: 5px 6px;
    vertical-align: middle;
  }
  table.main-table th {
    background-color: #f2f2f2;
    text-align: center;
    font-weight: bold;
  }
  .col-label {
    width: 16%;
    font-weight: bold;
    background-color: #fafafa;
  }
  .input-line {
    width: 100%;
    border: none;
    border-bottom: 1px solid #999;
    font-size: 11px;
    padding: 2px;
    background: transparent;
  }
  .checkbox-row {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 2px 0;
  }
  .checkbox-box {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 1px solid #000;
    text-align: center;
    line-height: 12px;
    font-size: 9px;
  }
  .marital-cell {
    display: flex;
    justify-content: space-between;
  }
  .marital-col {
    width: 48%;
  }

  .deskripsi-cell {
    height: 90px;
    vertical-align: top;
  }

  /* EFFECTIVE DATE */
  .effective-row {
    margin: 10px 0 20px 0;
    font-size: 11px;
  }
  .effective-row .input-line {
    width: 250px;
    display: inline-block;
  }

  /* SIGNATURE TABLE */
  table.sign-table {
    width: 100%;
    border-collapse: collapse;
  }
  table.sign-table th {
    border: 1px solid #000;
    background-color: #f2f2f2;
    padding: 5px;
    text-align: center;
  }
  table.sign-table td {
    border: 1px solid #000;
    padding: 5px 8px;
    text-align: center;
    vertical-align: bottom;
    height: 70px;
  }
  .sign-name-row td {
    height: auto;
    font-weight: bold;
    padding-top: 4px;
  }
  .sign-jabatan-row td {
    height: auto;
    font-size: 10px;
    padding-bottom: 6px;
  }

  @media print {
    body { -webkit-print-color-adjust: exact; }
  }
</style>
</head>
<body>
<div class="container">

  <!-- HEADER -->
  <table class="header-table">
    <tr>
      <td class="logo-cell">
        <div class="logo-text">GEU</div>
      </td>
      <td style="width:30%;">
        <div><strong>Process Owner</strong> : HCGA</div>
      </td>
      <td style="width:15%;">
        <div><strong>Revision</strong> :</div>
        <div>#00</div>
      </td>
      <td style="width:20%;">
        <div><strong>Effective Date</strong> :</div>
        <div>26 Juli 2024</div>
      </td>
      <td style="width:20%;">
        <div><strong>Doc ID</strong>:</div>
        <div>FR/HCGA/39-00</div>
      </td>
    </tr>
  </table>

  <div class="title-cell" style="margin-bottom:12px;">Form Perubahan Status</div>

  <!-- IDENTITY INFO -->
  <table class="identity-info">
    <tr>
      <td class="label">Nama</td>
      <td class="colon">:</td>
      <td style="width:35%;"><input type="text" class="input-line" name="nama"></td>
      <td class="label" style="width:80px;">Nomor</td>
      <td class="colon">:</td>
      <td>(diisi oleh HCGA)</td>
    </tr>
    <tr>
      <td class="label">NIK</td>
      <td class="colon">:</td>
      <td><input type="text" class="input-line" name="nik"></td>
      <td colspan="3"></td>
    </tr>
    <tr>
      <td class="label">Tanggal join</td>
      <td class="colon">:</td>
      <td><input type="text" class="input-line" name="tanggal_join" value="29/01/2023"></td>
      <td colspan="3"></td>
    </tr>
    <tr>
      <td class="label">Masa kerja</td>
      <td class="colon">:</td>
      <td><input type="text" class="input-line" name="masa_kerja" value="3 tahun 5 bulan 8 hari"></td>
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
      <td><input type="text" class="input-line" name="jabatan_saat_ini"></td>
      <td><input type="text" class="input-line" name="jabatan_usulan"></td>
    </tr>
    <tr>
      <td class="col-label">Divisi</td>
      <td><input type="text" class="input-line" name="divisi_saat_ini"></td>
      <td><input type="text" class="input-line" name="divisi_usulan"></td>
    </tr>
    <tr>
      <td class="col-label">Departemen</td>
      <td><input type="text" class="input-line" name="departemen_saat_ini"></td>
      <td><input type="text" class="input-line" name="departemen_usulan"></td>
    </tr>
    <tr>
      <td class="col-label">Grade</td>
      <td><input type="text" class="input-line" name="grade_saat_ini"></td>
      <td><input type="text" class="input-line" name="grade_usulan"></td>
    </tr>
    <tr>
      <td class="col-label">Lokasi kerja</td>
      <td><input type="text" class="input-line" name="lokasi_kerja_saat_ini"></td>
      <td><input type="text" class="input-line" name="lokasi_kerja_usulan"></td>
    </tr>
    <tr>
      <td class="col-label">Status kekaryawanan</td>
      <td><input type="text" class="input-line" name="status_kekaryawanan_saat_ini"></td>
      <td><input type="text" class="input-line" name="status_kekaryawanan_usulan"></td>
    </tr>
    <tr>
      <td class="col-label">Status perkawinan (dan jumlah tanggungan)</td>
      <td>
        <div class="marital-cell">
          <div class="marital-col">
            <div class="checkbox-row"><span class="checkbox-box"></span> Tidak kawin (TK)</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/0</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/1</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/2</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/3</div>
          </div>
          <div class="marital-col">
            <div class="checkbox-row"><span class="checkbox-box"></span> Kawin (K)</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/0</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/1</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/2</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/3</div>
          </div>
        </div>
      </td>
      <td>
        <div class="marital-cell">
          <div class="marital-col">
            <div class="checkbox-row"><span class="checkbox-box"></span> Tidak kawin (TK)</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/0</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/1</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/2</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> TK/3</div>
          </div>
          <div class="marital-col">
            <div class="checkbox-row"><span class="checkbox-box"></span> Kawin (K)</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/0</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/1</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/2</div>
            <div class="checkbox-row"><span class="checkbox-box"></span> K/3</div>
          </div>
        </div>
      </td>
    </tr>
    <tr>
      <td class="col-label">Salary</td>
      <td>Rp. <input type="text" class="input-line" style="width:80%;display:inline-block;" name="salary_saat_ini" value="0"></td>
      <td>Rp. <input type="text" class="input-line" style="width:80%;display:inline-block;" name="salary_usulan" value="0"></td>
    </tr>
    <tr>
      <td class="col-label">Benefit</td>
      <td><input type="text" class="input-line" name="benefit_saat_ini"></td>
      <td><input type="text" class="input-line" name="benefit_usulan"></td>
    </tr>
    <tr>
      <td class="col-label">Deskripsi</td>
      <td class="deskripsi-cell" colspan="2"></td>
    </tr>
  </table>

  <!-- EFFECTIVE DATE -->
  <div class="effective-row">
    Perubahan status ini berlaku sejak tanggal : <input type="text" class="input-line" name="tanggal_berlaku">
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
      <td>(Nama)</td>
      <td>(Nama)</td>
      <td>Hendra Darmawan</td>
    </tr>
    <tr class="sign-jabatan-row">
      <td>Jabatan</td>
      <td>Jabatan</td>
      <td>Vice President Operation</td>
    </tr>
  </table>
  <table class="sign-table" style="margin-top:-1px;">
    <tr>
      <td colspan="2"></td>
      <td>Arie S. Wirawan</td>
    </tr>
    <tr>
      <td colspan="2"></td>
      <td>Senior Manager HCGA</td>
    </tr>
  </table>

</div>
</body>
</html>