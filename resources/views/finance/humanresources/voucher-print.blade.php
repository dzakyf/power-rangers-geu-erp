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
            background: {{ in_array(strtoupper((string) $voucher->Status), ['PAID', 'CLOSED'], true) ? '#198754' : '#0d6efd' }};
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
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 22px;
        }
        .sig-box {
            flex: 1;
            min-width: 150px;
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
            <strong>Preview Cetak Voucher &mdash; {{ $voucher->Voucher_No }}</strong>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" class="btn-print" onclick="window.print()">Cetak / Print PDF</button>
            <button type="button" class="btn-close" onclick="window.close()">Tutup Tab</button>
        </div>
    </div>

    <div class="sheet">
        <div class="doc-header">
            <div>
                <h1 class="doc-title">{!! $print_title !!}</h1>
                <div class="doc-sub">
                    {{ $print_subtitle }} &bull; Company: <strong>{{ $voucher->Company_ID ?: 'GEU' }}</strong> &bull; Lokasi: <strong>{{ $voucher->Location ?: '-' }}</strong>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:15px;font-weight:700;margin-bottom:4px;">{{ $voucher->Voucher_No }}</div>
                <span class="status-badge">{{ strtoupper((string) $voucher->Status) }}</span>
                <div style="font-size:10px;color:#4b5563;margin-top:4px;">
                    Jalur: <strong>{{ $voucher->Final_Channel ?: $voucher->Requested_Channel }}</strong>
                    @if ($voucher->Final_Channel === 'DIRECT' && $voucher->Payment_Method)
                        ({{ $voucher->Payment_Method }})
                    @endif
                </div>
            </div>
        </div>

        <div class="grid-2">
            <div class="box">
                <div class="box-title">Informasi Dokumen &amp; Referensi</div>
                <table class="info-table">
                    <tr><td class="lbl">No. Dokumen Asal</td><td><strong>{{ $voucher->Source_No ?: $voucher->Voucher_No }}</strong></td></tr>
                    @if (!empty($voucher->PO_References))
                        <tr><td class="lbl">Ref. Purchase Order</td><td>{{ $voucher->PO_References }} {{ $voucher->PR_No ? '(PR: ' . $voucher->PR_No . ')' : '' }}</td></tr>
                    @endif
                    <tr><td class="lbl">No. Faktur / Ref</td><td>{{ $voucher->Reference ?: '-' }}</td></tr>
                    <tr><td class="lbl">Departemen / Kategori</td><td>{{ $voucher->Department ?: '-' }} / {{ $voucher->Expense_Category ?: '-' }}</td></tr>
                    <tr><td class="lbl">Pemohon / Dibuat</td><td>{{ $voucher->Created_By ?: '-' }} ({{ $voucher->Created_At }})</td></tr>
                    @if (!empty($voucher->Legacy_AP_Task_ID))
                        <tr><td class="lbl">Task ID AP Legacy</td><td>{{ $voucher->Legacy_AP_Task_ID }}</td></tr>
                    @endif
                    @if (!empty($voucher->Journal_No))
                        <tr><td class="lbl">No. Jurnal HO</td><td>{{ $voucher->Journal_No }}</td></tr>
                    @endif
                </table>
            </div>
            <div class="box">
                <div class="box-title">Penerima Pembayaran (Vendor / Payee)</div>
                <table class="info-table">
                    <tr><td class="lbl">Nama Penerima</td><td><strong>{{ $voucher->Vendor_Name ?: '-' }}</strong> {{ $voucher->Vendor_ID ? '(' . $voucher->Vendor_ID . ')' : '' }}</td></tr>
                    <tr><td class="lbl">Tipe Transfer</td><td>{{ $voucher->Bank_Type ?: 'DIRECT TRANSFER' }}</td></tr>
                    <tr><td class="lbl">Bank</td><td>{{ $voucher->Bank_Name ?: '-' }}</td></tr>
                    <tr><td class="lbl">No. Rekening</td><td>{{ $voucher->Bank_Acc ?: '-' }}</td></tr>
                    <tr><td class="lbl">Atas Nama</td><td>{{ $voucher->Bank_Acc_Name ?: '-' }}</td></tr>
                    <tr><td class="lbl">Tgl Invoice / Terima</td><td>{{ $voucher->Inv_Vendor_Date ?: '-' }} / {{ $voucher->Date_Received ?: '-' }}</td></tr>
                    <tr><td class="lbl">Jatuh Tempo</td><td><strong>{{ $voucher->Due_Date ?: '-' }}</strong></td></tr>
                </table>
            </div>
        </div>

        <div class="amount-highlight">
            <div>
                <div style="font-weight:700;color:#065f46;">TOTAL NOMINAL VOUCHER</div>
                <div style="font-size:11px;color:#047857;">
                    Subtotal: Rp {{ number_format((float) $voucher->Subtotal, 0, ',', '.') }}
                    &bull; PPN: Rp {{ number_format((float) $voucher->PPN, 0, ',', '.') }}
                    &bull; PPh: -Rp {{ number_format((float) $voucher->PPH, 0, ',', '.') }}
                    @if ((float) $voucher->Paid > 0)
                        &bull; Terbayar: Rp {{ number_format((float) $voucher->Paid, 0, ',', '.') }}
                    @endif
                </div>
            </div>
            <div class="val">Rp {{ number_format((float) $voucher->Total, 0, ',', '.') }}</div>
        </div>

        <div class="box" style="margin-bottom:14px;">
            <div class="box-title">Rincian Tagihan / Biaya</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:65%;">Perihal &amp; Deskripsi Pembayaran</th>
                        <th>Komponen</th>
                        <th class="text-right">Jumlah (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $voucher->Subject_Payment ?: '-' }}</strong>
                            @if (!empty($voucher->Description))
                                <div style="color:#4b5563;font-size:11px;margin-top:4px;">{{ $voucher->Description }}</div>
                            @endif
                            @if (!empty($voucher->Finance_Note))
                                <div style="color:#0369a1;font-size:11px;margin-top:4px;">Catatan Finance: {{ $voucher->Finance_Note }}</div>
                            @endif
                        </td>
                        <td>
                            Subtotal<br>
                            @if ((float) $voucher->DP > 0) DP<br> @endif
                            @if ((float) $voucher->Delivery_Costs > 0) Delivery<br> @endif
                            @if ((float) $voucher->Management_Fee > 0) Mgmt Fee<br> @endif
                            @if ((float) $voucher->Materai > 0) Materai<br> @endif
                            @if ((float) $voucher->PPN > 0) PPN<br> @endif
                            @if ((float) $voucher->PPH > 0) Potongan PPh<br> @endif
                        </td>
                        <td class="text-right">
                            Rp {{ number_format((float) $voucher->Subtotal, 0, ',', '.') }}<br>
                            @if ((float) $voucher->DP > 0) Rp {{ number_format((float) $voucher->DP, 0, ',', '.') }}<br> @endif
                            @if ((float) $voucher->Delivery_Costs > 0) Rp {{ number_format((float) $voucher->Delivery_Costs, 0, ',', '.') }}<br> @endif
                            @if ((float) $voucher->Management_Fee > 0) Rp {{ number_format((float) $voucher->Management_Fee, 0, ',', '.') }}<br> @endif
                            @if ((float) $voucher->Materai > 0) Rp {{ number_format((float) $voucher->Materai, 0, ',', '.') }}<br> @endif
                            @if ((float) $voucher->PPN > 0) Rp {{ number_format((float) $voucher->PPN, 0, ',', '.') }}<br> @endif
                            @if ((float) $voucher->PPH > 0) -Rp {{ number_format((float) $voucher->PPH, 0, ',', '.') }}<br> @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box" style="margin-bottom:14px;">
            <div class="box-title">Rincian Akun COA (Debet &amp; Kredit)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun (COA) &amp; Kelompok Trial Balance</th>
                        <th>Keterangan</th>
                        <th class="text-right">Debet</th>
                        <th class="text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumDeb = 0; $sumCre = 0; @endphp
                    @forelse ($lines as $ln)
                        @php
                            $lnArr = (array) $ln;
                            $side = strtoupper((string) ($lnArr['Line_Side'] ?? 'DEBET'));
                            $amt = (float) ($lnArr['Amount'] ?? 0);
                            if ($side === 'DEBET') { $sumDeb += $amt; } else { $sumCre += $amt; }
                        @endphp
                        <tr>
                            <td><strong>{{ $lnArr['Acc_No'] ?? '-' }}</strong></td>
                            <td>
                                {{ $lnArr['Acc_Name'] ?? '-' }}
                                @if (!empty($lnArr['Trial_Balance_Header_Name']))
                                    <div style="font-size:10px;color:#6b7280;">{{ $lnArr['Trial_Balance_Header_No'] ?? '' }} - {{ $lnArr['Trial_Balance_Header_Name'] }}</div>
                                @endif
                            </td>
                            <td>{{ $lnArr['Remark'] ?? '-' }}</td>
                            <td class="text-right">{{ $side === 'DEBET' ? 'Rp ' . number_format($amt, 0, ',', '.') : '-' }}</td>
                            <td class="text-right">{{ $side === 'CREDIT' ? 'Rp ' . number_format($amt, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:#6b7280;">Belum ada rincian COA.</td></tr>
                    @endforelse
                    @if (!empty($lines))
                        <tr style="background:#f9fafb;font-weight:700;">
                            <td colspan="3" class="text-right">TOTAL</td>
                            <td class="text-right">Rp {{ number_format($sumDeb, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($sumCre, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if (($voucher->Final_Channel ?: $voucher->Requested_Channel) === 'AP' && !empty($tenors))
            <div class="box" style="margin-bottom:14px;">
                <div class="box-title">Jadwal Tenor / Jatuh Tempo Pembayaran AP</div>
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Label Termin</th><th>Jatuh Tempo</th><th class="text-right">Nominal</th><th class="text-right">Terbayar</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($tenors as $t)
                            @php $tArr = (array) $t; @endphp
                            <tr>
                                <td>{{ $tArr['Tenor_Seq'] ?? '-' }}</td>
                                <td>{{ $tArr['Tenor_Label'] ?? '-' }}</td>
                                <td>{{ $tArr['Due_Date'] ?? '-' }}</td>
                                <td class="text-right">Rp {{ number_format((float) ($tArr['Amount'] ?? 0), 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format((float) ($tArr['Paid_Amount'] ?? 0), 0, ',', '.') }}</td>
                                <td>{{ $tArr['Status'] ?? 'OPEN' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (!empty($payments))
            <div class="box" style="margin-bottom:14px;background:#f0fdf4;border-color:#16a34a;">
                <div class="box-title" style="color:#166534;">Riwayat Pembayaran Finance / Treasury</div>
                <table class="data-table">
                    <thead>
                        <tr><th>Tanggal</th><th>Jalur</th><th>Ref Cash/Bank &amp; Jurnal</th><th>Keterangan</th><th class="text-right">Nominal</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $p)
                            <tr>
                                <td>{{ $p->Payment_Date }}</td>
                                <td>{{ $p->Payment_Channel }} ({{ $p->Payment_Acc_No }})</td>
                                <td>{{ $p->Legacy_Bank_No ?: ($p->Legacy_Cash_No ?: '-') }} &bull; {{ $p->Journal_No ?: '-' }}</td>
                                <td>{{ $p->Payment_Ref ?: ($p->Note ?: '-') }}</td>
                                <td class="text-right">Rp {{ number_format((float) $p->Amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="signatures">
            <div class="sig-box">
                <div class="sig-role">Diajukan Oleh</div>
                <div style="font-size:10px;color:#6b7280;">{{ $voucher->Created_At }}</div>
                <div class="sig-name">{{ $voucher->Created_By ?: '-' }}</div>
            </div>
            @foreach ($print_approvers ?? [] as $ap)
                <div class="sig-box">
                    <div class="sig-role">
                        {{ $ap->label }}
                        @if (!empty($ap->position_code))
                            <span style="color:#6b7280;font-weight:normal;">({{ $ap->position_code }})</span>
                        @endif
                    </div>
                    <div style="font-size:10px;color:#6b7280;">
                        @php $apStatus = strtoupper((string) ($ap->status ?? '')); @endphp
                        @if (in_array($apStatus, ['APPROVED', 'AUTO_APPROVED', 'FORCE_APPROVED'], true))
                            {{ $ap->acted_at ?: 'Disetujui' }}
                        @elseif ($apStatus === 'SKIPPED_FORCE')
                            Dilewati (Force Approve) &bull; {{ $ap->acted_at ?: '' }}
                        @elseif ($apStatus === 'SKIPPED')
                            Dilewati Sistem &bull; {{ $ap->acted_at ?: '' }}
                        @elseif ($apStatus === 'UPCOMING')
                            Menunggu Giliran
                        @else
                            Menunggu ({{ $ap->status_label ?? $ap->status }})
                        @endif
                    </div>
                    <div class="sig-name">{{ $ap->actor_name ?: '( .......................... )' }}</div>
                </div>
            @endforeach
            @if (!empty($show_verified_box))
                <div class="sig-box">
                    <div class="sig-role">Diverifikasi Oleh (Finance)</div>
                    <div style="font-size:10px;color:#6b7280;">{{ $voucher->Verified_At ?: 'Menunggu Verifikasi' }}</div>
                    <div class="sig-name">{{ $voucher->Verified_By ?: '( .......................... )' }}</div>
                </div>
            @endif
            @if (!empty($show_treasury_box))
                <div class="sig-box">
                    <div class="sig-role">Dibayarkan Oleh (Treasury)</div>
                    <div style="font-size:10px;color:#6b7280;">{{ in_array(strtoupper((string) $voucher->Status), ['PAID', 'CLOSED'], true) ? 'Lunas' : 'Menunggu Pembayaran' }}</div>
                    <div class="sig-name">{{ $voucher->Verified_By ?: '( .......................... )' }}</div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
