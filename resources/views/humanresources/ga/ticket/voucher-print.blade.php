<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Pembayaran {{ $voucher->Voucher_No }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #d1d5db;
            padding: 24px 28px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .toolbar {
            max-width: 820px;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
        }
        .btn-close {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
        }
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px 0;
            letter-spacing: 0.4px;
        }
        .doc-sub {
            color: #4b5563;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
            background: {{ strtoupper((string) $voucher->Status) === 'PAID' ? '#198754' : '#0d6efd' }};
            color: #ffffff;
        }
        .grid-2 {
            display: flex;
            gap: 16px;
            margin-bottom: 14px;
        }
        .box {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px 12px;
        }
        .box-title {
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .info-table td.lbl {
            width: 38%;
            color: #4b5563;
            font-weight: 600;
        }
        .data-table th, .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background: #f9fafb;
            font-size: 11px;
            text-transform: uppercase;
            color: #374151;
        }
        .text-right {
            text-align: right !important;
        }
        .amount-highlight {
            background: #ecfdf5;
            border: 1px solid #10b981;
            padding: 10px 14px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .amount-highlight .val {
            font-size: 18px;
            font-weight: 700;
            color: #065f46;
        }
        .signatures {
            display: flex;
            gap: 12px;
            margin-top: 22px;
        }
        .sig-box {
            flex: 1;
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: center;
            min-height: 82px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sig-role {
            font-weight: 700;
            font-size: 11px;
            color: #374151;
        }
        .sig-name {
            font-size: 11px;
            border-top: 1px dotted #9ca3af;
            padding-top: 4px;
            color: #111827;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .sheet {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div>
            <strong>Preview Voucher Pembayaran GA Order Pekerjaan</strong>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" class="btn-print" onclick="window.print()">Cetak / Print PDF</button>
            <button type="button" class="btn-close" onclick="window.close()">Tutup Tab</button>
        </div>
    </div>

    <div class="sheet">
        <div class="doc-header">
            <div>
                <h1 class="doc-title">PAYMENT VOUCHER &mdash; GENERAL AFFAIRS</h1>
                <div class="doc-sub">
                    Order Pekerjaan &amp; Tagihan Vendor &bull; Company: <strong>{{ $voucher->Company_ID ?: 'GEU' }}</strong> &bull; Lokasi: <strong>{{ $voucher->Location ?: '-' }}</strong>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:15px;font-weight:700;margin-bottom:4px;">{{ $voucher->Voucher_No }}</div>
                <span class="status-badge">{{ strtoupper((string) $voucher->Status) }}</span>
            </div>
        </div>

        <div class="grid-2">
            <div class="box">
                <div class="box-title">Informasi Tiket &amp; Order Pekerjaan</div>
                <table class="info-table">
                    <tr><td class="lbl">No. Tiket</td><td><strong>{{ $voucher->Ticket_No }}</strong></td></tr>
                    <tr><td class="lbl">Subjek Order</td><td>{{ $voucher->Ticket_Subject }}</td></tr>
                    <tr><td class="lbl">Departemen / Cost Center</td><td>{{ $voucher->Department ?: '-' }} / {{ $voucher->Cost_Center ?: '-' }}</td></tr>
                    <tr><td class="lbl">Pemohon</td><td>{{ $voucher->Reporter_Name ?: $voucher->Reporter }}</td></tr>
                    @if (!empty($voucher->Work_Order_No))
                        <tr><td class="lbl">No. Work Order</td><td>{{ $voucher->Work_Order_No }}</td></tr>
                    @endif
                    <tr><td class="lbl">Verifikasi GA</td><td>{{ $voucher->GA_Verified_By ?: '-' }} ({{ $voucher->GA_Verified_At ?: '-' }})</td></tr>
                </table>
            </div>
            <div class="box">
                <div class="box-title">Penerima Pembayaran (Payee / Vendor)</div>
                <table class="info-table">
                    <tr><td class="lbl">Tipe Penerima</td><td>{{ $voucher->Payee_Type ?: 'VENDOR' }}</td></tr>
                    <tr><td class="lbl">Nama Penerima</td><td><strong>{{ $voucher->Payee_Name }}</strong></td></tr>
                    <tr><td class="lbl">Metode Bayar</td><td>{{ $voucher->Payment_Method ?: 'TRANSFER' }}</td></tr>
                    <tr><td class="lbl">Bank</td><td>{{ $voucher->Bank_Name ?: '-' }}</td></tr>
                    <tr><td class="lbl">No. Rekening</td><td>{{ $voucher->Bank_Account_No ?: '-' }}</td></tr>
                    <tr><td class="lbl">Atas Nama</td><td>{{ $voucher->Bank_Account_Name ?: '-' }}</td></tr>
                    <tr><td class="lbl">Jatuh Tempo</td><td>{{ $voucher->Due_Date ?: '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="amount-highlight">
            <div>
                <div style="font-weight:700;color:#065f46;">TOTAL NOMINAL VOUCHER PEMBAYARAN</div>
                <div style="font-size:11px;color:#047857;">
                    Estimasi Awal: Rp {{ number_format((float) ($voucher->Estimated_Amount ?? 0), 0, ',', '.') }}
                    &bull; Verifikasi Disetujui: Rp {{ number_format((float) ($voucher->Verified_Amount ?? $voucher->Amount), 0, ',', '.') }}
                </div>
            </div>
            <div class="val">Rp {{ number_format((float) $voucher->Amount, 0, ',', '.') }}</div>
        </div>

        <div class="box" style="margin-bottom:14px;">
            <div class="box-title">Rincian Pembayaran</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:65%;">Deskripsi Pekerjaan / Tagihan</th>
                        <th>No. Ref Tiket</th>
                        <th class="text-right">Jumlah (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {{ $voucher->Description ?: $voucher->Ticket_Subject }}
                            @if (!empty($voucher->GA_Verification_Note))
                                <div style="color:#4b5563;font-size:11px;margin-top:4px;">Catatan Verifikasi GA: {{ $voucher->GA_Verification_Note }}</div>
                            @endif
                        </td>
                        <td>{{ $voucher->Ticket_No }}</td>
                        <td class="text-right"><strong>Rp {{ number_format((float) $voucher->Amount, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box" style="margin-bottom:14px;">
            <div class="box-title">Riwayat Persetujuan Berjenjang (Fase 1 &amp; Fase 2)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fase</th>
                        <th>Stage</th>
                        <th>Aksi</th>
                        <th>Approver</th>
                        <th>Waktu</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasApprovalLog = false; @endphp
                    @if (!empty($timelines['dept_head']['logs']))
                        @foreach ($timelines['dept_head']['logs'] as $log)
                            @if (in_array(strtoupper((string) $log->Action), ['APPROVE', 'APPROVED', 'REJECT', 'REJECTED', 'RETURNED'], true))
                                @php $hasApprovalLog = true; @endphp
                                <tr>
                                    <td>Fase 1 (Dept Head)</td>
                                    <td>Stage {{ $log->Stage_Order ?: '-' }}</td>
                                    <td>{{ $log->Action }}</td>
                                    <td>{{ $log->Actor_Name ?: ($log->Actor_Username ?: 'System') }}</td>
                                    <td>{{ $log->Action_Date ?: '-' }}</td>
                                    <td>{{ $log->Notes ?: '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    @if (!empty($timelines['budget']['logs']))
                        @foreach ($timelines['budget']['logs'] as $log)
                            @if (in_array(strtoupper((string) $log->Action), ['APPROVE', 'APPROVED', 'REJECT', 'REJECTED', 'RETURNED'], true))
                                @php $hasApprovalLog = true; @endphp
                                <tr>
                                    <td>Fase 2 (Biaya &amp; Tier)</td>
                                    <td>Stage {{ $log->Stage_Order ?: '-' }}</td>
                                    <td>{{ $log->Action }}</td>
                                    <td>{{ $log->Actor_Name ?: ($log->Actor_Username ?: 'System') }}</td>
                                    <td>{{ $log->Action_Date ?: '-' }}</td>
                                    <td>{{ $log->Notes ?: '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    @if (!$hasApprovalLog)
                        <tr><td colspan="6" style="text-align:center;color:#6b7280;">Belum ada log persetujuan tercatat.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if (strtoupper((string) $voucher->Status) === 'PAID')
            <div class="box" style="margin-bottom:14px;background:#f0fdf4;border-color:#16a34a;">
                <div class="box-title" style="color:#166534;">Konfirmasi Pelunasan Finance</div>
                <table class="info-table">
                    <tr><td class="lbl">Tanggal Bayar</td><td><strong>{{ $voucher->Paid_Date ?: '-' }}</strong></td></tr>
                    <tr><td class="lbl">Referensi Pembayaran</td><td>{{ $voucher->Payment_Ref ?: '-' }}</td></tr>
                    <tr><td class="lbl">Diproses Oleh</td><td>{{ $voucher->Paid_By ?: '-' }} ({{ $voucher->Paid_At ?: '-' }})</td></tr>
                </table>
            </div>
        @endif

        <div class="signatures">
            <div class="sig-box">
                <div class="sig-role">Dibuat Oleh (GA)</div>
                <div style="font-size:10px;color:#6b7280;">{{ $voucher->Created_At }}</div>
                <div class="sig-name">{{ $voucher->Created_By ?: '-' }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-role">Diverifikasi Oleh (GA)</div>
                <div style="font-size:10px;color:#6b7280;">{{ $voucher->GA_Verified_At ?: '-' }}</div>
                <div class="sig-name">{{ $voucher->GA_Verified_By ?: '-' }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-role">Disetujui (HCGA / FAT / BOD)</div>
                <div style="font-size:10px;color:#6b7280;">Digital Approval Workflow</div>
                <div class="sig-name">{{ !empty($timelines['budget']['request']->Status) ? $timelines['budget']['request']->Status : '-' }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-role">Dibayarkan Oleh (Finance)</div>
                <div style="font-size:10px;color:#6b7280;">{{ $voucher->Paid_At ?: 'Menunggu Pembayaran' }}</div>
                <div class="sig-name">{{ $voucher->Paid_By ?: '( .......................... )' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
