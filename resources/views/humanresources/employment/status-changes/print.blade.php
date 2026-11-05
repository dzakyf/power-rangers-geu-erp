<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Perubahan Status - {{ $statusChange->form_number }}</title>
    <style>
        /* JANGAN pakai @page di sini: pada mPDF menghasilkan halaman kosong. Ukuran & margin diatur di controller. */
        body { font-family: Arial, sans-serif; color: #000; font-size: 8pt; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: middle; font-size: 8pt; }

        /* Bingkai luar halaman */
        .frame { border: 1px solid #000; padding: 14px 22px 22px 22px; }

        /* Header dokumen */
        .hdr { border: 1.5pt solid #000; }
        .hdr td { border: 0.5pt solid #000; padding: 2px 4px; }
        .hdr .logo { text-align: center; vertical-align: middle; height: 48px; }
        .hdr .title { text-align: center; font-size: 8pt; padding: 1px 4px; }
        .hdr .small { font-size: 6.5pt; }

        /* Info karyawan */
        .info { margin-top: 18px; margin-bottom: 14px; }
        .info td { padding: 1px 2px; border: none; }

        /* Tabel utama */
        .main { border: 1.5pt solid #000; }
        .main td, .main th { border: 0.5pt solid #777; padding: 1px 3px; height: 13px; }
        .main th.lbl { background: #eee; text-align: left; font-weight: bold; }
        .main th.head { text-align: center; font-weight: bold; padding: 1px 3px; }
        .main .desc-head { background: #eee; font-weight: bold; }
        .main .desc-body { height: 125px; vertical-align: top; padding: 4px; }

        /* Tabel status perkawinan (nested) */
        .marital { border-collapse: collapse; }
        .marital td { border: none; border-bottom: 0.5pt solid #777; padding: 0 2px; height: 13px; }
        .marital td.chk { width: 9%; white-space: nowrap; }
        .marital td.txt { width: 41%; border-left: 0.5pt solid #777; }
        .marital tr.last td { border-bottom: none; }

        /* Tanggal berlaku */
        .eff { margin-top: 14px; margin-bottom: 14px; }
        .eff td { border: none; padding: 1px 2px; }

        /* Tanda tangan */
        .sign { border: 0.5pt solid #777; margin-left: auto; margin-right: 0; width: 86%; }
        .sign td, .sign th { border: 0.5pt solid #777; text-align: center; padding: 2px 3px; font-weight: normal; font-size: 7.5pt; }
        .sign .space { height: 70px; }
        .sign .name { vertical-align: bottom; }
    </style>
</head>
<body>
@php
    // ---- Helper tampilan ----
    $join = $statusChange->join_date ? \Carbon\Carbon::parse($statusChange->join_date) : null;
    $tenure = '';
    if ($join) {
        $d = $join->diff(\Carbon\Carbon::now());
        $tenure = $d->y . ' tahun ' . $d->m . ' bulan ' . $d->d . ' hari';
    }
    $effective = $statusChange->effective_date ? \Carbon\Carbon::parse($statusChange->effective_date)->format('d/m/Y') : '';

    $rp = function ($v) {
        return 'Rp. ' . number_format((float) $v, 0, ',', '.') . ',-';
    };

    // Normalisasi "TK/1", "tk 1", "TK1" -> "TK1"
    $norm = function ($v) {
        return str_replace(['/', ' ', '-'], '', strtoupper(trim((string) $v)));
    };
    // Kotak centang: [X] jika cocok, [  ] jika tidak
    $box = function ($value, $code) use ($norm) {
        $v = $norm($value);
        $c = $norm($code);
        if ($c === 'TK' || $c === 'K') {
            $on = $v !== '' && strpos($v, $c) === 0;   // TK* atau K*
        } else {
            $on = $v === $c;
        }
        return $on ? '[ X ]' : '[ &nbsp;&nbsp;]';
    };

    $rows = [
        'position'          => 'Jabatan',
        'division'          => 'Divisi',
        'department'        => 'Departemen',
        'grade'             => 'Grade',
        'work_location'     => 'Lokasi kerja',
        'employment_status' => 'Status kekaryawanan',
    ];
    $maritalLeft  = ['TK/0', 'TK/1', 'TK/2', 'TK/3'];
    $maritalRight = ['K/0', 'K/1', 'K/2', 'K/3'];
@endphp

<div class="frame">

    {{-- ===== HEADER ===== --}}
    <table class="hdr">
        <tr>
            <td class="logo" rowspan="2" width="24%">
                {{-- $logo dikirim dari controller (PNG transparan sudah diratakan ke latar putih agar tidak jadi hitam di mPDF) --}}
                <img src="{{ $logo ?? public_path('assets/images/small-logo.png') }}" style="height: 42px;" alt="Logo">
            </td>
            <td class="title" colspan="4">Form Perubahan Status</td>
        </tr>
        <tr>
            <td width="17.5%">Process Owner : HCGA</td>
            <td width="17.5%">Revision :<br>#00</td>
            <td width="17.5%" class="small">Effective Date :<br>26 Juli 2024</td>
            <td width="23.5%">Doc ID:<br>FR/HCGA/39-00</td>
        </tr>
    </table>

    {{-- ===== INFO KARYAWAN ===== --}}
    <table class="info">
        <tr>
            <td width="12%">Nama</td>
            <td width="3%">:</td>
            <td width="45%">{{ $statusChange->employee_name }}</td>
            <td width="8%" align="right">Nomor</td>
            <td width="3%" align="center">:</td>
            <td width="29%">{{ $statusChange->form_number ?: '(diisi oleh HCGA)' }}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>:</td>
            <td colspan="4">{{ $statusChange->nik }}</td>
        </tr>
        <tr>
            <td>Tanggal join</td>
            <td>:</td>
            <td colspan="4">{{ $join ? $join->format('d/m/Y') : '' }}</td>
        </tr>
        <tr>
            <td>Masa kerja</td>
            <td>:</td>
            <td colspan="4">{{ $tenure }}</td>
        </tr>
    </table>

    {{-- ===== TABEL UTAMA ===== --}}
    <table class="main">
        <tr>
            <th class="head" width="25%">Identitas</th>
            <th class="head" width="37.5%">Saat ini</th>
            <th class="head" width="37.5%">Usulan</th>
        </tr>
        @foreach($rows as $field => $label)
            @php
                $curVal = $statusChange->{'current_' . $field};
                $propVal = $statusChange->{'proposed_' . $field};
                if ($field === 'position' && !empty($masterPositions)) {
                    $curVal = $masterPositions[$curVal] ?? $curVal;
                    $propVal = $masterPositions[$propVal] ?? $propVal;
                }
            @endphp
            <tr>
                <th class="lbl">{{ $label }}</th>
                <td>{{ $curVal }}</td>
                <td>{{ $propVal }}</td>
            </tr>
        @endforeach

        {{-- Status perkawinan (dan jumlah tanggungan) --}}
        <tr>
            <th class="lbl">Status perkawinan (dan jumlah<br>tanggungan)</th>
            @foreach(['current', 'proposed'] as $col)
                @php $val = $statusChange->{$col . '_marital_dependents'}; @endphp
                <td style="padding: 0;">
                    <table class="marital">
                        <tr>
                            <td class="chk">{!! $box($val, 'TK') !!}</td>
                            <td class="txt">Tidak kawin (TK)</td>
                            <td class="chk">{!! $box($val, 'K') !!}</td>
                            <td class="txt">Kawin (K)</td>
                        </tr>
                        @foreach($maritalLeft as $i => $tk)
                            <tr class="{{ $loop->last ? 'last' : '' }}">
                                <td class="chk">{!! $box($val, $tk) !!}</td>
                                <td class="txt">{{ $tk }}</td>
                                <td class="chk">{!! $box($val, $maritalRight[$i]) !!}</td>
                                <td class="txt">{{ $maritalRight[$i] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            @endforeach
        </tr>

        <tr>
            <th class="lbl">Salary</th>
            <td>{{ $rp($statusChange->current_salary) }}</td>
            <td>{{ $rp($statusChange->proposed_salary) }}</td>
        </tr>
        <tr>
            <th class="lbl">Benefit</th>
            <td>{{ $statusChange->current_benefit }}</td>
            <td>{{ $statusChange->proposed_benefit }}</td>
        </tr>

        {{-- Deskripsi (satu kotak penuh seperti di form) --}}
        <tr>
            <td colspan="3" class="desc-head">Deskripsi</td>
        </tr>
        <tr>
            <td colspan="3" class="desc-body">{!! nl2br(e($statusChange->proposed_description ?: $statusChange->current_description)) !!}</td>
        </tr>
    </table>

    {{-- ===== TANGGAL BERLAKU ===== --}}
    <table class="eff">
        <tr>
            <td width="38%">Perubahan status ini berlaku sejak tanggal</td>
            <td width="3%">:</td>
            <td>{{ $effective }}</td>
        </tr>
    </table>

    {{-- ===== TANDA TANGAN ===== --}}
    @php
        $signs = $signatures ?? [];
        $originSign = $signs['origin'] ?? ['name' => $statusChange->origin_department_head, 'position' => 'Dept. Head', 'qr_uri' => null];
        $destSign   = $signs['destination'] ?? ['name' => $statusChange->destination_department_head, 'position' => 'Dept. Head', 'qr_uri' => null];
        $hcgaSign   = $signs['hcga'] ?? ['name' => ($statusChange->hcga_verifier ?: 'Arie S. Wirawan'), 'position' => 'Senior Manager HCGA', 'qr_uri' => null];
        $dirSign    = $signs['director'] ?? ['name' => ($statusChange->director_approver ?: 'Dhimas Arif Wibowo'), 'position' => 'Direktur', 'qr_uri' => null];

        $proposerDisplay = $proposerName ?? ($signs['proposer']['name'] ?? ($statusChange->proposed_by ?: '-'));
    @endphp
    <table class="sign">
        <tr>
            <th rowspan="2" width="20%" style="vertical-align: middle;">Diusulkan oleh</th>
            <th colspan="2">Disetujui</th>
            <th rowspan="2" width="20%" style="vertical-align: middle;">Diverifikasi</th>
            <th rowspan="2" width="20%" style="vertical-align: middle;">Mengetahui</th>
        </tr>
        <tr>
            <th width="20%">Departemen Asal</th>
            <th width="20%">Departemen Tujuan</th>
        </tr>
        <tr>
            <td class="space" style="height: 60px; vertical-align: middle; text-align: center;">
                @if(!empty($proposerQrUri))
                    <img src="{{ $proposerQrUri }}" width="48" height="48" alt="QR Proposer" />
                @else
                    &nbsp;
                @endif
            </td>
            <td class="space" style="height: 60px; vertical-align: middle; text-align: center;">
                @if(!empty($originSign['qr_uri']))
                    <img src="{{ $originSign['qr_uri'] }}" width="48" height="48" alt="QR Dept Head Asal" />
                @elseif(($originSign['status'] ?? '') === 'CLAIMED')
                    <span style="color: #b45309; font-size: 7pt;">(Sedang Diklaim)</span>
                @elseif(($originSign['status'] ?? '') === 'REJECTED')
                    <span style="color: #dc2626; font-size: 7pt; font-weight: bold;">(DITOLAK)</span>
                @else
                    <span style="color: #999; font-size: 7pt;">(Menunggu Approval)</span>
                @endif
            </td>
            <td class="space" style="height: 60px; vertical-align: middle; text-align: center;">
                @if(!empty($destSign['qr_uri']))
                    <img src="{{ $destSign['qr_uri'] }}" width="48" height="48" alt="QR Dept Head Tujuan" />
                @elseif(($destSign['status'] ?? '') === 'CLAIMED')
                    <span style="color: #b45309; font-size: 7pt;">(Sedang Diklaim)</span>
                @elseif(($destSign['status'] ?? '') === 'REJECTED')
                    <span style="color: #dc2626; font-size: 7pt; font-weight: bold;">(DITOLAK)</span>
                @elseif(!empty($destSign['name']))
                    <span style="color: #999; font-size: 7pt;">(Menunggu Approval)</span>
                @else
                    <span style="color: #ccc; font-size: 7pt;">-</span>
                @endif
            </td>
            <td class="space" style="height: 60px; vertical-align: middle; text-align: center;">
                @if(!empty($hcgaSign['qr_uri']))
                    <img src="{{ $hcgaSign['qr_uri'] }}" width="48" height="48" alt="QR HCGA" />
                @elseif(($hcgaSign['status'] ?? '') === 'CLAIMED')
                    <span style="color: #b45309; font-size: 7pt;">(Sedang Diklaim)</span>
                @elseif(($hcgaSign['status'] ?? '') === 'REJECTED')
                    <span style="color: #dc2626; font-size: 7pt; font-weight: bold;">(DITOLAK)</span>
                @else
                    <span style="color: #999; font-size: 7pt;">(Menunggu Approval)</span>
                @endif
            </td>
            <td class="space" style="height: 60px; vertical-align: middle; text-align: center;">
                @if(!empty($dirSign['qr_uri']))
                    <img src="{{ $dirSign['qr_uri'] }}" width="48" height="48" alt="QR Direktur" />
                @elseif(($dirSign['status'] ?? '') === 'CLAIMED')
                    <span style="color: #b45309; font-size: 7pt;">(Sedang Diklaim)</span>
                @elseif(($dirSign['status'] ?? '') === 'REJECTED')
                    <span style="color: #dc2626; font-size: 7pt; font-weight: bold;">(DITOLAK)</span>
                @else
                    <span style="color: #999; font-size: 7pt;">(Menunggu Approval)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>{{ $proposerDisplay }}</strong></td>
            <td><strong>{{ $originSign['name'] ?: '-' }}</strong></td>
            <td><strong>{{ $destSign['name'] ?: '-' }}</strong></td>
            <td><strong>{{ $hcgaSign['name'] ?: '-' }}</strong></td>
            <td><strong>{{ $dirSign['name'] ?: '-' }}</strong></td>
        </tr>
        <tr>
            <td style="text-align: left;">Jabatan</td>
            <td>{{ $originSign['position'] ?? 'Dept. Head' }}</td>
            <td>{{ $destSign['position'] ?? 'Dept. Head' }}</td>
            <td>{{ $hcgaSign['position'] ?? 'Senior Manager HCGA' }}</td>
            <td>{{ $dirSign['position'] ?? 'Direktur' }}</td>
        </tr>
    </table>

</div>
</body>
</html>