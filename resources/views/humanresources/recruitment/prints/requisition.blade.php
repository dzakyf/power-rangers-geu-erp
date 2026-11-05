{{--
    View cetak Permintaan Tenaga Kerja (PTK) - FR/HCGA/19-15 (untuk mPDF).

    Catatan struktur (margin halaman tetap diatur dari controller mPDF, view ini tidak mengubahnya):
    - Seluruh form = 1 tabel pembungkus "doc" dengan 1 kolom.
        * Header (logo + judul + Doc ID) ada di file terpisah requisition_header.blade.php dan
          dipasang controller via $mpdf->SetHTMLHeader() -> tampil di setiap halaman.
        * <tbody> : tiap seksi = 1 baris; baris "Tugas dan Tanggung Jawab" = 1 <tr> per tugas
                    sehingga bisa lanjut ke halaman berikutnya.
        * Baris tanda tangan memakai page-break-inside: avoid -> judul Disiapkan/Diketahui/Disetujui
          tidak pernah terpisah dari penanda tangannya.
    - Padding hanya diberikan lewat <td> (mPDF tidak membaca padding pada <table>).
    - Kolom tanda tangan dinamis (1 s/d N step approval policy REQUISITION):
        Disiapkan oleh  : 1 kolom (Pemohon)
        Diketahui oleh  : N kolom (step intermediate)
        Disetujui oleh  : N kolom (step final)
--}}
@php
  // ---------------------------------------------------------------
  // Fallback data (jika view hanya dipanggil dengan $ptk)
  // ---------------------------------------------------------------
  $noPtk                  = $noPtk ?? ($ptk->Task_ID ?? '');
  $jumlahPermintaan       = $jumlahPermintaan ?? (!empty($ptk->Qty) ? $ptk->Qty . ' Orang' : '');
  $mulaiTanggal           = $mulaiTanggal ?? (!empty($ptk->Start_Date) ? date('d M Y', strtotime($ptk->Start_Date)) : '');
  $site                   = $site ?? ($ptk->Placement ?? ($ptk->Location ?? ''));
  $namaJabatan            = $namaJabatan ?? ($ptk->Job_Position ?? '');
  $bertanggungJawabKepada = $bertanggungJawabKepada ?? '';
  $membawahi              = $membawahi ?? '';
  $revision               = $revision ?? '#00';
  $effectiveDate          = $effectiveDate ?? '04-02-2025';
  $docId                  = $docId ?? 'FR/HCGA/19-15';

  // Baris "Tugas dan Tanggung Jawab": minimal 5 baris
  $tugasRows = array_values($tugasList ?? []);
  if (count($tugasRows) < 5) {
      $tugasRows = array_pad($tugasRows, 5, '');
  }

  // ---------------------------------------------------------------
  // Data tanda tangan
  // ---------------------------------------------------------------
  $prepared = $signatureColumns['prepared'] ?? [
      'step_label' => 'Pemohon',
      'name'       => $ptk->Created_By ?? '',
      'date'       => !empty($ptk->Created_Date) ? date('d/m/Y', strtotime($ptk->Created_Date)) : '',
      'status'     => 'SUBMITTED',
  ];
  $acknowledgedList = array_values($signatureColumns['acknowledged'] ?? []);
  $approvedList     = array_values($signatureColumns['approved'] ?? []);

  // Placeholder agar format tetap proporsional bila policy belum punya step
  if (empty($acknowledgedList)) {
      $acknowledgedList = [['step_label' => 'Atasan / Dept', 'name' => '', 'date' => '', 'status' => 'PENDING']];
  }
  if (empty($approvedList)) {
      $approvedList = [['step_label' => 'Manager HCGA', 'name' => '', 'date' => '', 'status' => 'PENDING']];
  }

  $ackCount = count($acknowledgedList);
  $appCount = count($approvedList);

  // Satu daftar datar: semua kolom tanda tangan berlebar sama
  $sigCols = [array_merge($prepared, ['group' => 'prepared'])];
  foreach ($acknowledgedList as $c) { $sigCols[] = array_merge($c, ['group' => 'ack']); }
  foreach ($approvedList as $c)     { $sigCols[] = array_merge($c, ['group' => 'app']); }
  $sigColW = round(100 / count($sigCols), 2);

  // ---------------------------------------------------------------
  // Helper kecil
  // ---------------------------------------------------------------
  // Kotak centang (X bila terpilih)
  $box = function ($checked = false) {
      return '<table align="center" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">'
           . '<tr><td class="cb">' . ($checked ? 'X' : '&nbsp;') . '</td></tr></table>';
  };

  // Tanda "( X )" untuk Staff / Non Staff
  $paren = function ($checked = false) {
      return '(&nbsp;' . ($checked ? 'X' : '&nbsp;&nbsp;') . '&nbsp;)';
  };

  // Stempel di atas nama: QR / badge status / kosong
  $stamp = function ($c) {
      if (!empty($c['qr_uri'])) {
          return '<img src="' . e($c['qr_uri']) . '" width="46" height="46" alt="QR" />';
      }
      $status = strtoupper($c['status'] ?? '');
      if (($c['group'] ?? '') === 'prepared' && !empty($c['name'])) {
          return '<span class="badge badge-ok">SUBMITTED</span>';
      }
      if (in_array($status, ['APPROVED', 'AUTO_APPROVED'], true)) {
          return '<span class="badge badge-ok">APPROVED</span>';
      }
      if ($status === 'REJECTED') {
          return '<span class="badge badge-no">REJECTED</span>';
      }
      return '&nbsp;';
  };
@endphp

<style>
  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5pt;
    color: #000;
  }

  /* ---------- Tabel pembungkus (1 kolom) ---------- */
  table.doc {
    width: 100%;
    border-collapse: collapse;
  }
  table.doc tr { page-break-inside: avoid; }

  /* Sel pembungkus: border kiri/kanan = bingkai luar form, tanpa padding */
  table.doc td.o {
    padding: 0;
    border-left: 1.2pt solid #000;
    border-right: 1.2pt solid #000;
    vertical-align: top;
  }
  table.doc td.last { border-bottom: 1.2pt solid #000; }

  /* ---------- Tabel isi di dalam sel pembungkus ---------- */
  table.inner {
    width: 100%;
    border-collapse: collapse;
  }
  table.inner td {
    padding: 1.2mm 2mm;
    vertical-align: middle;
    font-size: 8.5pt;
  }

  /* Header */
  table.head { border-top: 1.2pt solid #000; border-bottom: 0.75pt solid #000; }
  table.head td.logo   { text-align: center; border-right: 0.75pt solid #000; padding: 2mm; }
  table.head td.title  { text-align: center; font-size: 9.5pt; border-bottom: 0.75pt solid #000; padding: 1.2mm 2mm; }
  table.head td.meta   { font-size: 7.5pt; line-height: 1.3; vertical-align: top; padding: 1.2mm 2mm; border-right: 0.75pt solid #000; }
  table.head td.meta-last { font-size: 8.5pt; text-align: center; vertical-align: middle; border-right: 0; }

  /* Garis isian */
  td.ul { border-bottom: 0.75pt solid #000; }

  /* Spasi antar blok */
  tr.gap td { height: 2.5mm; padding: 0; font-size: 2pt; line-height: 2pt; }

  /* Judul seksi */
  td.sec-title { font-weight: bold; padding-top: 2mm; padding-bottom: 1mm; }

  /* Kotak centang */
  td.cb {
    width: 4mm;
    height: 4mm;
    border: 0.75pt solid #000;
    padding: 0;
    text-align: center;
    vertical-align: middle;
    font-size: 8pt;
    font-weight: bold;
  }

  /* Baris tugas: 1 <tr> per tugas, garis penuh dari tepi ke tepi */
  table.doc td.tg {
    border-top: 0.75pt solid #000;
    height: 5.5mm;
    padding: 0.8mm 2mm;
    font-size: 8.5pt;
    vertical-align: middle;
  }

  /* Kotak pemberitahuan merah muda */
  table.doc td.nt {
    background-color: #ea9999;
    border-top: 0.75pt solid #000;
    font-size: 8pt;
    line-height: 1.35;
    padding: 1mm 2mm;
  }

  /* Tanda tangan */
  table.sig {
    page-break-inside: avoid;
    border-collapse: separate;
    border-spacing: 2.5mm 0;
  }
  table.sig td { padding: 0.6mm 1mm; }
  td.sig-head  { text-align: center; padding-top: 2mm; }
  td.sig-label { text-align: center; font-size: 7pt; color: #333; }
  td.sig-stamp { text-align: center; vertical-align: middle; height: 20mm; }
  td.sig-name  { text-align: center; font-weight: bold; font-size: 8pt; padding-top: 1mm; }
  td.sig-date  { border-top: 0.75pt solid #000; font-size: 7.5pt; text-align: left; padding-bottom: 2mm; }

  span.badge    { font-size: 6.5pt; font-weight: bold; padding: 0.6mm 1.5mm; }
  span.badge-ok { border: 0.75pt solid #166534; color: #166534; }
  span.badge-no { border: 0.75pt solid #991b1b; color: #991b1b; }
</style>

{{-- Header hanya disisipkan untuk preview browser (?view=html).
     Untuk PDF, header dipasang oleh controller lewat $mpdf->SetHTMLHeader() agar tampil di SETIAP halaman. --}}
@if(!empty($inlineHeader))
  @include('humanresources.recruitment.prints.requisition_header')
@endif

<table class="doc" cellspacing="0" cellpadding="0">

  <tbody>

    {{-- =====================================================
         2. NO. PTK, STAFF/NON STAFF, SITE, KET, JUMLAH, TANGGAL, KAITAN PERMINTAAN
         ===================================================== --}}
    <tr>
      <td class="o">
        <table class="inner" cellspacing="0" cellpadding="0">
          {{-- No. PTK --}}
          <tr>
            <td width="15%">&nbsp;</td>
            <td width="9%">&nbsp;</td>
            <td width="31%" class="ul">No. PTK : <strong>{{ $noPtk }}</strong></td>
            <td colspan="2" width="45%">
              @if(!empty($ptk->Department))
                &nbsp;&nbsp;Departemen : <strong>{{ $ptk->Department }}</strong>
              @else
                &nbsp;
              @endif
            </td>
          </tr>
          <tr class="gap"><td colspan="5">&nbsp;</td></tr>

          {{-- Staff + Jumlah Permintaan --}}
          <tr>
            <td width="15%">Staff</td>
            <td width="9%" align="center">{!! $paren(!empty($isStaff)) !!}</td>
            <td width="31%">&nbsp;</td>
            <td width="25%" align="right">Jumlah Permintaan :&nbsp;</td>
            <td width="20%" class="ul">{{ $jumlahPermintaan }}</td>
          </tr>

          {{-- Non Staff --}}
          <tr>
            <td>Non Staff</td>
            <td align="center">{!! $paren(!empty($isNonStaff)) !!}</td>
            <td colspan="3">&nbsp;</td>
          </tr>

          {{-- Site + Mulai Tanggal --}}
          <tr>
            <td>Site :</td>
            <td colspan="2"><strong>{{ $site }}</strong></td>
            <td align="right">Mulai Tanggal / Join Date :&nbsp;</td>
            <td class="ul">{{ $mulaiTanggal }}</td>
          </tr>

          {{-- Ket --}}
          <tr>
            <td>Ket :</td>
            <td>{!! $box(!empty($isKaryawanBaru)) !!}</td>
            <td colspan="3">Karyawan Baru</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>{!! $box(!empty($isPenggantiKaryawan)) !!}</td>
            <td colspan="3">Pengganti Karyawan</td>
          </tr>
          <tr class="gap"><td colspan="5">&nbsp;</td></tr>

          {{-- Permintaan kerja berkaitan dengan --}}
          <tr>
            <td colspan="5" style="padding-left:15mm;">Permintaan kerja ini berkaitan dengan :</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>{!! $box(!empty($isPeningkatanVolume)) !!}</td>
            <td colspan="3">Ada peningkatan volume kerja</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>{!! $box(!empty($isPenggantiKeluar)) !!}</td>
            <td colspan="3">Pengganti tenaga kerja keluar</td>
          </tr>
          @if(empty($isPeningkatanVolume) && empty($isPenggantiKeluar) && !empty($ptk->Request_Desc))
            <tr>
              <td>&nbsp;</td>
              <td>{!! $box(true) !!}</td>
              <td colspan="3">Lainnya : {{ $ptk->Request_Desc }}</td>
            </tr>
          @endif
          <tr class="gap"><td colspan="5">&nbsp;</td></tr>
        </table>
      </td>
    </tr>

    {{-- =====================================================
         3. URAIAN TUGAS DAN JABATAN
         ===================================================== --}}
    <tr>
      <td class="o">
        <table class="inner" cellspacing="0" cellpadding="0">
          <tr>
            <td class="sec-title" colspan="3">URAIAN TUGAS DAN JABATAN</td>
          </tr>
          <tr>
            <td width="30%">Nama Jabatan</td>
            <td width="40%" class="ul">: {{ $namaJabatan }}</td>
            <td width="30%">&nbsp;</td>
          </tr>
          <tr>
            <td>Bertanggung jawab kepada</td>
            <td class="ul">: {{ $bertanggungJawabKepada }}</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>Membawahi langsung/ tak langsung</td>
            <td class="ul">: {{ $membawahi }}</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td class="sec-title" colspan="3" style="padding-top:4mm;">Tugas dan Tanggung Jawab</td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- Baris tugas: 1 <tr> per tugas (bisa lanjut ke halaman berikutnya) --}}
    @foreach ($tugasRows as $tugas)
      <tr>
        <td class="o tg">{!! $tugas !== '' ? e($tugas) : '&nbsp;' !!}</td>
      </tr>
    @endforeach

    {{-- =====================================================
         4. NOTICE (merah muda)
         ===================================================== --}}
    <tr>
      <td class="o nt">
        Mohon jika mengisi form PTK, lampirkan juga hal-hal berikut ini:<br>
        1. Penggantian kary : melampirkan jobdesc dan target<br>
        2. Penambahan kary : melampirkan struktur dept, jobdesc, target ybs, target dept before after
      </td>
    </tr>
    <tr>
      <td class="o nt">Struktur organisasi boleh berbentuk tulis tangan.</td>
    </tr>

    {{-- =====================================================
         5. PERSYARATAN KHUSUS
         ===================================================== --}}
    <tr>
      <td class="o">
        <table class="inner" cellspacing="0" cellpadding="0">
          <tr class="gap"><td colspan="2">&nbsp;</td></tr>
          <tr>
            <td colspan="2">PERSYARATAN KHUSUS</td>
          </tr>
          <tr>
            <td width="16%">Pendidikan :</td>
            <td width="84%">{{ $pendidikan ?? '' }}</td>
          </tr>
          <tr>
            <td>Pengalaman :</td>
            <td>{{ $pengalaman ?? '' }}</td>
          </tr>
          <tr>
            <td style="vertical-align:top;">Syarat Khusus :</td>
            <td style="vertical-align:top; height:22mm;">{!! nl2br(e($syaratKhusus ?? '')) !!}</td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- =====================================================
         6. TANDA TANGAN DINAMIS (mengikuti approval policy REQUISITION)
            Satu baris, page-break-inside: avoid -> judul & penanda tangan selalu 1 kesatuan.
         ===================================================== --}}
    <tr style="page-break-inside: avoid;">
      <td class="o last">
        <table class="inner sig" cellspacing="0" cellpadding="0" style="page-break-inside: avoid;">
          {{-- Judul kelompok --}}
          <tr>
            <td class="sig-head" colspan="1">Disiapkan oleh</td>
            <td class="sig-head" colspan="{{ $ackCount }}">Diketahui oleh</td>
            <td class="sig-head" colspan="{{ $appCount }}">Disetujui oleh</td>
          </tr>

          {{-- Label step --}}
          <tr>
            @foreach ($sigCols as $col)
              <td class="sig-label" width="{{ $sigColW }}%">{{ $col['step_label'] ?? '' }}</td>
            @endforeach
          </tr>

          {{-- QR / badge status --}}
          <tr>
            @foreach ($sigCols as $col)
              <td class="sig-stamp">{!! $stamp($col) !!}</td>
            @endforeach
          </tr>

          {{-- ( Nama ) --}}
          <tr>
            @foreach ($sigCols as $col)
              <td class="sig-name">
                (&nbsp;{!! !empty($col['name']) ? e($col['name']) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}&nbsp;)
              </td>
            @endforeach
          </tr>

          {{-- Tanggal --}}
          <tr>
            @foreach ($sigCols as $col)
              <td class="sig-date">Tanggal: {{ $col['date'] ?? '' }}</td>
            @endforeach
          </tr>
        </table>
      </td>
    </tr>

  </tbody>
</table>