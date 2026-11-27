<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $bastNo }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            line-height: 1.4;
        }
        .header-box {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .title {
            font-size: 13pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9pt;
            color: #4b5563;
            margin-top: 2px;
        }
        .badge-official {
            display: inline-block;
            padding: 2px 8px;
            font-size: 8pt;
            font-weight: bold;
            color: #065f46;
            background: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 3px;
        }
        .badge-draft {
            display: inline-block;
            padding: 2px 8px;
            font-size: 8pt;
            font-weight: bold;
            color: #92400e;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
            font-size: 9pt;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 12px 0 6px 0;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }
        .grid-table th, .grid-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .grid-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-align: left;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .muted { color: #6b7280; font-size: 8pt; }
        .sign-box {
            border: 1px solid #cbd5e1;
            padding: 8px;
            height: 92px;
            vertical-align: top;
        }
    </style>
</head>
<body>
@php
    $isReturn = $docType === 'RETURN';
    $docTitle = $isReturn
        ? 'BERITA ACARA SERAH TERIMA PENGEMBALIAN ASET (BAST RETURN)'
        : 'BERITA ACARA SERAH TERIMA ASET (BAST HANDOVER)';

    $party1Nik  = $isReturn ? ($handover->Return_Lead_NIK ?: $handover->PIC_NIK) : ($handover->Lead_NIK ?: $handover->User_NIK);
    $party1Name = $isReturn ? ($handover->Return_Lead_Name ?: $handover->PIC_Name) : ($handover->Lead_Name ?: $handover->User_Name);
    $party1Emp  = $employees[$party1Nik] ?? null;

    $party2Nik  = $isReturn ? ($handover->Return_Receiver_NIK ?: '-') : ($handover->PIC_NIK ?: '-');
    $party2Name = $isReturn ? ($handover->Return_Receiver_Name ?: '-') : ($handover->PIC_Name ?: '-');
    $party2Emp  = $employees[$party2Nik] ?? null;
@endphp

<div class="header-box">
    <table>
        <tr>
            <td style="width: 72%;">
                <div class="title">{{ $docTitle }}</div>
                <div class="subtitle">Nomor BAST Sistem: <strong>{{ $bastNo }}</strong> &bull; Ref Transaksi: <strong>{{ $handover->Handover_No }}</strong></div>
            </td>
            <td style="width: 28%; text-align: right; vertical-align: middle;">
                @if($isOfficial)
                    <span class="badge-official">DOKUMEN RESMI (TERVERIFIKASI)</span>
                @else
                    <span class="badge-draft">DRAFT — BELUM FINAL</span>
                @endif
            </td>
        </tr>
    </table>
</div>

<p style="margin: 0 0 8px 0;">
    Pada tanggal <strong>{{ $handover->Handover_Date ? date('d M Y', strtotime($handover->Handover_Date)) : '-' }}</strong>,
    melalui sistem ERP HCGA telah dilakukan pencatatan {{ $isReturn ? 'pengembalian' : 'serah terima' }} aset perusahaan antara para pihak berikut:
</p>

<table class="grid-table" style="margin-bottom: 10px;">
    <thead>
        <tr>
            <th style="width: 50%;">Pihak Pertama ({{ $isReturn ? 'Yang Mengembalikan / PIC' : 'Yang Menyerahkan / Lead Pemberi' }})</th>
            <th style="width: 50%;">Pihak Kedua ({{ $isReturn ? 'Penerima Pengembalian' : 'Penerima Aset (PIC)' }})</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <strong>Nama:</strong> {{ $party1Name }}<br>
                <strong>NIK:</strong> {{ $party1Nik ?: '-' }}<br>
                <strong>Jabatan / Dept:</strong> {{ $party1Emp->Position ?? '-' }} / {{ $party1Emp->Department ?? ($handover->PIC_Department ?: '-') }}
            </td>
            <td>
                <strong>Nama:</strong> {{ $party2Name }}<br>
                <strong>NIK:</strong> {{ $party2Nik ?: '-' }}<br>
                <strong>Jabatan / Dept:</strong> {{ $party2Emp->Position ?? '-' }} / {{ $party2Emp->Department ?? ($isReturn ? 'HCGA' : ($handover->PIC_Department ?: '-')) }}
            </td>
        </tr>
    </tbody>
</table>

<table class="meta-table" style="margin-bottom: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <tr>
        <td style="width: 50%;"><strong>User Pembuat:</strong> {{ $handover->User_Name }} ({{ $handover->User_NIK }})</td>
        <td style="width: 50%;"><strong>Referensi / FPB:</strong> {{ $handover->Reference ?: '-' }}</td>
    </tr>
    <tr>
        <td><strong>Status Transaksi:</strong> {{ $handover->Status }}</td>
        <td><strong>Catatan:</strong> {{ $handover->Remark ?: '-' }}</td>
    </tr>
</table>

<div class="section-title">{{ $isReturn ? 'Rincian Barang / Aset yang Dikembalikan' : 'Rincian Barang / Aset yang Diserahterimakan' }}</div>
<table class="grid-table">
    <thead>
        <tr>
            <th style="width: 28px;" class="text-center">No</th>
            <th style="width: 110px;">Asset No / SN</th>
            <th style="width: 85px;">Kode Produk</th>
            <th>Deskripsi &amp; Spesifikasi</th>
            <th style="width: 55px;" class="text-right">Qty</th>
            <th style="width: 85px;">Kondisi</th>
            <th style="width: 80px;">Tgl {{ $isReturn ? 'Kembali' : 'Terima' }}</th>
            <th style="width: 80px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @php $rowNo = 0; @endphp
        @foreach($items as $it)
            @if($isReturn && !in_array(strtoupper((string) ($it->Status ?? '')), ['RETURN_PENDING', 'RETURNED'], true))
                @continue
            @endif
            @php $rowNo++; @endphp
            <tr>
                <td class="text-center">{{ $rowNo }}</td>
                <td>
                    <strong>{{ $it->Asset_No ?? '-' }}</strong>
                    @if(!empty($it->Asset_SN))
                        <br><span class="muted">SN: {{ $it->Asset_SN }}</span>
                    @endif
                </td>
                <td>{{ $it->Product_Code }}</td>
                <td>
                    <strong>{{ $it->Description }}</strong>
                    @if(!empty($it->Product_Brand) || !empty($it->Specification))
                        <br><span class="muted">{{ trim(($it->Product_Brand ?? '') . ' ' . ($it->Specification ?? '')) }}</span>
                    @endif
                    @if($isReturn && !empty($it->Returned_Remark))
                        <br><span class="muted">Catatan Return: {{ $it->Returned_Remark }}</span>
                    @endif
                </td>
                <td class="text-right">
                    {{ $isReturn ? ($it->Returned_Qty ?? $it->Qty) : $it->Qty }} {{ $it->Unit }}
                </td>
                <td>{{ $isReturn ? ($it->Returned_Condition ?: $it->Condition) : $it->Condition }}</td>
                <td>{{ $isReturn ? ($it->Returned_Date ?: '-') : ($it->Received_Date ?: '-') }}</td>
                <td>{{ $it->Status ?: '-' }}</td>
            </tr>
        @endforeach
        @if($rowNo === 0)
            <tr>
                <td colspan="8" class="text-center muted">
                    {{ $isReturn ? 'Belum ada item pengembalian tercatat.' : 'Tidak ada item tercatat.' }}
                </td>
            </tr>
        @endif
    </tbody>
</table>

<div class="section-title">Riwayat Persetujuan Digital (ApprovalWorkflowService)</div>
<table class="grid-table" style="margin-bottom: 12px;">
    <thead>
        <tr>
            <th style="width: 55px;">Stage</th>
            <th>Peran / Posisi</th>
            <th style="width: 110px;">Status</th>
            <th>Eksekutor / Sistem</th>
            <th style="width: 115px;">Waktu</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($approvalTrail as $ap)
            <tr>
                <td>Stage {{ $ap->stage_order }}</td>
                <td>{{ $ap->label }}</td>
                <td><strong>{{ $ap->status_label ?? $ap->status }}</strong></td>
                <td>{{ $ap->actor_name ?: '-' }}</td>
                <td>{{ $ap->acted_at ?: '-' }}</td>
                <td>{{ $ap->notes ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center muted">Belum ada data approval workflow (transaksi legacy / draft awal).</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="page-break-inside: avoid;">
<table>
    <tr>
        <td style="width: 38%; padding-right: 6px;">
            <div class="sign-box">
                <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 4px;">Pihak Pertama ({{ $isReturn ? 'PIC Return' : 'Lead Pemberi' }})</div>
                <div class="muted" style="margin-bottom: 24px;">Disetujui secara elektronik / sistem</div>
                <div style="font-weight: bold; font-size: 9pt;">{{ $party1Name }}</div>
                <div class="muted">NIK: {{ $party1Nik ?: '-' }}</div>
            </div>
        </td>
        <td style="width: 38%; padding-right: 6px;">
            <div class="sign-box">
                <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 4px;">Pihak Kedua ({{ $isReturn ? 'Penerima Return' : 'PIC Penerima' }})</div>
                <div class="muted" style="margin-bottom: 24px;">Disetujui secara elektronik / sistem</div>
                <div style="font-weight: bold; font-size: 9pt;">{{ $party2Name }}</div>
                <div class="muted">NIK: {{ $party2Nik ?: '-' }}</div>
            </div>
        </td>
        <td style="width: 24%; text-align: center; border: 1px solid #cbd5e1; padding: 6px; vertical-align: middle;">
            <img src="{{ $qrBase64 }}" style="width: 82px; height: 82px;" alt="QR Verifikasi" />
            <div class="muted" style="margin-top: 3px; font-size: 7pt;">Scan QR untuk verifikasi keabsahan BAST sistem</div>
        </td>
    </tr>
</table>
</div>

<div class="muted" style="margin-top: 8px; text-align: right;">
    Dicetak otomatis oleh Sistem ERP HCGA pada {{ $generatedAt }} &bull; {{ $bastNo }}
</div>
</body>
</html>
