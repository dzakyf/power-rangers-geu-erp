{{--
    View ini di-render oleh Controller terkait dan di-pass ke mPDF.
    TIDAK perlu <html>, <head>, <body> — mPDF handle sendiri.

    PENTING: mPDF tidak support CSS border pada <div> dengan baik.
    Semua box/border HARUS menggunakan <table> agar tampil konsisten.

    Variabel yang diharapkan (sesuaikan dengan controller):
    $noPtk, $jumlahPermintaan, $mulaiTanggal, $site,
    $isStaff, $isNonStaff, $isKaryawanBaru, $isPenggantiKaryawan,
    $isPeningkatanVolume, $isPenggantiKeluar,
    $namaJabatan, $bertanggungJawabKepada, $membawahi,
    $tugasList (array baris tugas & tanggung jawab),
    $pendidikan, $pengalaman, $syaratKhusus,
    $docId, $revision, $effectiveDate
--}}

<style>
  * { margin: 0; padding: 0; }

  body {
    font-family: Arial, sans-serif;
    font-size: 9pt;
    color: #000;
  }

  /* =============================================
     TITLE BLOCK
     ============================================= */
  .title-heading {
    text-align: center;
    font-weight: bold;
    font-size: 10pt;
    padding: 4px;
  }

  .meta-cell {
    font-size: 7pt;
    font-weight: bold;
    padding: 3px 5px;
    vertical-align: top;
  }

  /* =============================================
     FIELD LABEL / VALUE
     ============================================= */
  .flbl {
    font-size: 8pt;
    padding: 2px 4px;
    vertical-align: bottom;
    white-space: nowrap;
  }
  .fval {
    font-size: 8pt;
    padding: 2px 4px;
    vertical-align: bottom;
    border-bottom: 1px solid #000;
  }
  .chk {
    font-size: 8pt;
    padding: 1px 4px;
    vertical-align: middle;
  }
  .chkbox {
    width: 10px;
    height: 10px;
    border: 1px solid #000;
    display: inline-block;
    text-align: center;
    font-weight: bold;
    font-size: 7pt;
    line-height: 10px;
  }

  /* =============================================
     SECTION HEADER
     ============================================= */
  .section-title {
    font-weight: bold;
    font-size: 8.5pt;
    padding: 4px 2px 2px 2px;
  }

  /* =============================================
     TUGAS TABLE (garis-garis kosong)
     ============================================= */
  .tugas-row td {
    border-bottom: 1px solid #000;
    height: 14px;
    font-size: 8pt;
    padding: 1px 4px;
  }

  /* =============================================
     NOTICE BOX (merah)
     ============================================= */
  .notice-box {
    background-color: #f4b6b1;
    font-size: 7.5pt;
    padding: 4px 6px;
    line-height: 1.5;
  }

  /* =============================================
     SIGNATURE
     ============================================= */
  .sig-head {
    text-align: center;
    font-size: 8pt;
    font-weight: bold;
    padding: 4px;
  }
  .sig-paren {
    text-align: center;
    font-size: 9pt;
    padding: 4px;
  }
  .sig-line {
    border-bottom: 1px solid #000;
  }
  .sig-tgl {
    font-size: 7.5pt;
    padding: 4px;
  }
</style>

{{-- =====================================================
     WRAPPER
     ===================================================== --}}
<div style="border:1px solid black;">

  {{-- =====================================================
       1. TITLE BLOCK
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="border-bottom:1px solid #000;">
    <tr>
      {{-- Logo --}}
      <td width="18%" style="border-right:1px solid #000;padding:6px;text-align:center;vertical-align:middle;">
        <img src="{{ 'assets/images/large-logo.png' }}" alt="Logo" width="70px">
      </td>
      {{-- Heading + Meta --}}
      <td style="vertical-align:top;">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="4" class="title-heading" style="border-bottom:1px solid #000;">
              Permintaan Tenaga Kerja
            </td>
          </tr>
          <tr>
            <td width="22%" class="meta-cell" style="border-right:1px solid #000;">Process Owner : HCGA</td>
            <td width="16%" class="meta-cell" style="border-right:1px solid #000;">Revision :<br>{{ $revision ?? '#00' }}</td>
            <td width="20%" class="meta-cell" style="border-right:1px solid #000;">Effective Date :<br>{{ $effectiveDate ?? '' }}</td>
            <td width="42%" class="meta-cell">Doc ID :<br>{{ $docId ?? 'FR/HCGA/19-15' }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- =====================================================
       2. NO. PTK
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="border-bottom:1px solid #000;">
    <tr>
      <td class="flbl" style="padding:3px 6px;">No. PTK : {{ $noPtk ?? '' }}</td>
    </tr>
  </table>

  {{-- =====================================================
       3. STAFF / NON STAFF / SITE / JUMLAH / TANGGAL
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="padding:4px 6px;">
    <tr>
      <td width="55%" style="padding:2px 6px;">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="chk" width="18%">Staff</td>
            <td class="chk" width="10%">({{ !empty($isStaff) ? 'X' : '' }})</td>
            <td></td>
          </tr>
          <tr>
            <td class="chk">Non Staff</td>
            <td class="chk">({{ !empty($isNonStaff) ? 'X' : '' }})</td>
            <td></td>
          </tr>
          <tr>
            <td class="flbl">Site :</td>
            <td colspan="2" class="fval" style="border-bottom: 0;">&nbsp;{{ $site ?? '' }}</td>
          </tr>
          <tr>
            <td class="flbl" style="vertical-align:top;">Ket :</td>
            <td colspan="2">
              <table cellspacing="0" cellpadding="0">
                <tr>
                  <td class="chk"><span class="chkbox">{{ !empty($isKaryawanBaru) ? 'X' : '' }}</span></td>
                  <td class="chk">Karyawan Baru</td>
                </tr>
                <tr>
                  <td class="chk"><span class="chkbox">{{ !empty($isPenggantiKaryawan) ? 'X' : '' }}</span></td>
                  <td class="chk">Pengganti Karyawan</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
      <td width="45%" style="padding:2px 6px;vertical-align:top;">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="flbl">Jumlah Permintaan : <span class="fval">{!! $jumlahPermintaan ?? "&nbsp;" !!}</span></td>
          </tr>
          <tr>
            <td class="flbl">Mulai Tanggal / Join Date : <span class="fval">{!! $mulaiTanggal ?? "&nbsp;" !!}</span></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- =====================================================
       4. PERMINTAAN KERJA BERKAITAN DENGAN
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="padding:2px 6px;">
    <tr>
      <td class="chk" colspan="2">Permintaan kerja ini berkaitan dengan :</td>
    </tr>
    <tr>
      <td class="chk" width="4%"><span class="chkbox">{{ !empty($isPeningkatanVolume) ? 'X' : '' }}</span></td>
      <td class="chk">Ada peningkatan volume kerja</td>
    </tr>
    <tr>
      <td class="chk"><span class="chkbox">{{ !empty($isPenggantiKeluar) ? 'X' : '' }}</span></td>
      <td class="chk">Pengganti tenaga kerja keluar</td>
    </tr>
  </table>

  {{-- =====================================================
       5. URAIAN TUGAS DAN JABATAN
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="padding:2px 6px;">
    <tr>
      <td class="section-title" colspan="2">URAIAN TUGAS DAN JABATAN</td>
    </tr>
    <tr>
      <td class="flbl" width="30%">Nama Jabatan</td>
      <td class="fval">&nbsp;{{ $namaJabatan ?? '' }}</td>
    </tr>
    <tr>
      <td class="flbl">Bertanggung jawab kepada</td>
      <td class="fval">&nbsp;{{ $bertanggungJawabKepada ?? '' }}</td>
    </tr>
    <tr>
      <td class="flbl">Membawahi langsung/ tak langsung</td>
      <td class="fval">&nbsp;{{ $membawahi ?? '' }}</td>
    </tr>
  </table>

  {{-- =====================================================
       6. TUGAS DAN TANGGUNG JAWAB (garis kosong)
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="padding:4px 6px 0 6px;">
    <tr>
      <td class="section-title">Tugas dan Tanggung Jawab</td>
    </tr>
  </table>
  <table width="100%" cellspacing="0" cellpadding="0" style="padding:0 6px;">
    @php
      // Jumlah baris kosong yang ditampilkan mengikuti template asli (± 8 baris).
      $tugasRows = $tugasList ?? array_fill(0, 8, '');
    @endphp
    @foreach ($tugasRows as $tugas)
      <tr class="tugas-row">
        <td>{{ $tugas ?: '' }}</td>
      </tr>
    @endforeach
  </table>

  {{-- =====================================================
       7. NOTICE BOX (merah)
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:6px;">
    <tr>
      <td class="notice-box">
        Mohon jika mengisi form PTK, lampirkan juga hal-hal berikut ini:<br>
        1. Penggantian kary : melampirkan jobdesc dan target<br>
        2. Penambahan kary : melampirkan struktur dept, jobdesc, target ybs, target dept before after<br>
        Struktur organisasi boleh berbentuk tulis tangan.
      </td>
    </tr>
  </table>

  {{-- =====================================================
       8. PERSYARATAN KHUSUS
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="padding:4px 6px;">
    <tr>
      <td class="section-title">PERSYARATAN KHUSUS</td>
    </tr>
    <tr>
      <td class="flbl" style="width:20%;">Pendidikan :</td>
      <td class="fval">&nbsp;{{ $pendidikan ?? '' }}</td>
    </tr>
    <tr>
      <td class="flbl">Pengalaman :</td>
      <td class="fval">&nbsp;{{ $pengalaman ?? '' }}</td>
    </tr>
    <tr>
      <td class="flbl">Syarat Khusus :</td>
      <td class="fval">&nbsp;{{ $syaratKhusus ?? '' }}</td>
    </tr>
  </table>

  {{-- spacer supaya tanda tangan tidak terlalu mepet --}}
  <table width="100%" cellspacing="0" cellpadding="0">
    <tr><td style="height:40px;">&nbsp;</td></tr>
  </table>

  {{-- =====================================================
       9. SIGNATURES
       ===================================================== --}}
  <table width="100%" cellspacing="0" cellpadding="0" style="padding:4px 6px 10px 6px;">
    <tr>
      <td width="33%" class="sig-head">Disiapkan oleh</td>
      <td width="34%" class="sig-head">Diketahui oleh</td>
      <td width="33%" class="sig-head">Disetujui oleh</td>
    </tr>
    <tr>
      <td style="height:22mm;">&nbsp;</td>
      <td style="height:22mm;">&nbsp;</td>
      <td style="height:22mm;">&nbsp;</td>
    </tr>
    <tr>
      <td class="sig-paren">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="15%">(</td>
            <td class="sig-line">&nbsp;</td>
            <td width="15%">)</td>
          </tr>
        </table>
      </td>
      <td class="sig-paren">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="12%">(</td>
            <td class="sig-line">&nbsp;</td>
            <td width="6%">)</td>
            <td width="6%">(</td>
            <td class="sig-line">&nbsp;</td>
            <td width="12%">)</td>
          </tr>
        </table>
      </td>
      <td class="sig-paren">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="15%">(</td>
            <td class="sig-line">&nbsp;</td>
            <td width="15%">)</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="sig-tgl">Tanggal: <span class="sig-line" style="display:inline-block;width:60%;">&nbsp;</span></td>
      <td class="sig-tgl">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="50%">Tanggal: <span class="sig-line" style="display:inline-block;width:55%;">&nbsp;</span></td>
            <td width="50%">Tanggal: <span class="sig-line" style="display:inline-block;width:55%;">&nbsp;</span></td>
          </tr>
        </table>
      </td>
      <td class="sig-tgl">Tanggal: <span class="sig-line" style="display:inline-block;width:60%;">&nbsp;</span></td>
    </tr>
  </table>

</div>