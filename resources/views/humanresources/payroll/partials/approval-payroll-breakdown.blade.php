@if(!empty($payrollBreakdown))
@php
    $pbMode = strtoupper((string) ($payrollBreakdown['mode'] ?? 'PAYROLL'));
    $pbRows = $payrollBreakdown['rows'] ?? [];
    $pbSummary = $payrollBreakdown['bank_summary'] ?? [];
    $pbTaskId = $payrollBreakdown['task_id'] ?? '-';
    $pbPeriod = $payrollBreakdown['period'] ?? '-';
    $pbStatus = strtoupper((string) ($payrollBreakdown['status'] ?? '-'));
    $pbIsFinal = in_array($pbStatus, ['APPROVED', 'COMPLETED', 'POSTED'], true);
    $pbSlipSource = $pbIsFinal ? 'final' : '';
    $pbUniqueId = 'pb_' . preg_replace('/[^A-Za-z0-9]/', '_', (string) $pbTaskId) . '_' . mt_rand(100, 999);
    $pbByBank = $pbSummary['by_bank'] ?? [];
@endphp

<div class="col-12 mt-3" id="PayrollBreakdownWrapper_{{ $pbUniqueId }}">
    <div class="card border shadow-sm rounded-3 mb-0">
        {{-- Header Card --}}
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-1 text-dark d-flex align-items-center flex-wrap gap-2">
                    <span>
                        <i class="fa fa-money-check-alt text-primary me-1"></i>
                        @if($pbMode === 'PAYROLL_THR')
                            Rincian Penerima THR &amp; Rekening Transfer Bank
                        @else
                            Data Rincian Payroll &amp; Rekening Transfer Bank
                        @endif
                    </span>
                    <span class="badge bg-primary text-white">{{ $pbSummary['total_employees'] ?? count($pbRows) }} Karyawan</span>
                    <span class="badge bg-light text-dark border">Batch: {{ $pbTaskId }}</span>
                    <span class="badge bg-info text-white">Periode: {{ $pbPeriod }}</span>
                </h6>
                <small class="text-muted">
                    Total Nett (THP) Terhitung:
                    <strong class="text-success">Rp {{ number_format((float) ($pbSummary['total_nett'] ?? 0), 0, ',', '.') }}</strong>
                    @if($pbMode === 'PAYROLL_THR' && !empty($payrollBreakdown['event_name']))
                        &bull; Event: <strong>{{ $payrollBreakdown['event_name'] }}</strong>
                    @elseif(!empty($payrollBreakdown['salary_group']) && $payrollBreakdown['salary_group'] !== '-')
                        &bull; Salary Group: <strong>{{ $payrollBreakdown['salary_group'] }}</strong>
                    @endif
                </small>
            </div>

            {{-- Filter & Export Controls (Tanpa Blasting Email) --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select id="bankFilter_{{ $pbUniqueId }}" class="form-select form-select-sm rounded-pill" style="width: 190px;" onchange="filterPayrollBreakdownTable('{{ $pbUniqueId }}')">
                    <option value="ALL">-- Semua Rekening Bank --</option>
                    <option value="BCA_ONLY">Rekening BCA ({{ $pbSummary['bca_count'] ?? 0 }})</option>
                    <option value="NON_BCA_ONLY">Semua Non-BCA ({{ $pbSummary['non_bca_count'] ?? 0 }})</option>
                    @if(($pbSummary['unmapped_count'] ?? 0) > 0)
                        <option value="UNMAPPED_ONLY">Belum Ada Rekening ({{ $pbSummary['unmapped_count'] }})</option>
                    @endif
                    @foreach($pbByBank as $bk)
                        @if(empty($bk['is_bca']))
                            <option value="BANK:{{ $bk['bank'] }}">{{ $bk['bank'] }} ({{ $bk['count'] }})</option>
                        @endif
                    @endforeach
                </select>

                <input type="text" id="searchInput_{{ $pbUniqueId }}" class="form-control form-control-sm rounded-pill"
                    placeholder="Cari NIK / Nama / Dept / Norek..." style="width: 230px;"
                    oninput="filterPayrollBreakdownTable('{{ $pbUniqueId }}')">

                @if($pbMode === 'PAYROLL_THR' && !empty($payrollBreakdown['batch_id']))
                    <a href="{{ url('payroll-thr-export-excel') }}?batch_id={{ urlencode((string) $payrollBreakdown['batch_id']) }}"
                        target="_blank" class="btn btn-success btn-sm rounded-pill px-3 text-white shadow-sm">
                        <i class="fa fa-file-excel me-1"></i> Export Excel
                    </a>
                @else
                    <a href="{{ url('payroll-report-export-excel') }}?task_id={{ urlencode((string) $pbTaskId) }}&period={{ urlencode((string) $pbPeriod) }}"
                        target="_blank" class="btn btn-success btn-sm rounded-pill px-3 text-white shadow-sm">
                        <i class="fa fa-file-excel me-1"></i> Export Excel
                    </a>
                @endif
            </div>
        </div>

        {{-- Ringkasan Transfer Bank (BCA vs Non-BCA) --}}
        <div class="card-body bg-light border-bottom py-2 px-3">
            <div class="row g-2 align-items-stretch">
                <div class="col-md-4">
                    <div class="p-2 bg-white border rounded-3 h-100 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold"><i class="fa fa-university text-primary me-1"></i>Transfer Rekening BCA</div>
                            <div class="fw-bold text-primary fs-6">Rp {{ number_format((float) ($pbSummary['bca_total'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                        <span class="badge bg-primary text-white px-2 py-1">{{ $pbSummary['bca_count'] ?? 0 }} Rekening</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2 bg-white border rounded-3 h-100 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold"><i class="fa fa-exchange-alt text-dark me-1"></i>Transfer Rekening Non-BCA</div>
                            <div class="fw-bold text-dark fs-6">Rp {{ number_format((float) ($pbSummary['non_bca_total'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                        <span class="badge bg-dark text-white px-2 py-1">{{ $pbSummary['non_bca_count'] ?? 0 }} Rekening</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2 bg-white border rounded-3 h-100 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold"><i class="fa fa-wallet text-success me-1"></i>Total Disbursement (Nett THP)</div>
                            <div class="fw-bold text-success fs-6">Rp {{ number_format((float) ($pbSummary['total_nett'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                        <span class="badge bg-success text-white px-2 py-1">{{ $pbSummary['total_employees'] ?? count($pbRows) }} Penerima</span>
                    </div>
                </div>
            </div>

            @if(!empty($pbByBank))
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2 pt-1">
                    <small class="text-muted fw-semibold"><i class="fa fa-tags me-1"></i>Rincian per Bank:</small>
                    @foreach($pbByBank as $bk)
                        <span class="badge {{ !empty($bk['is_bca']) ? 'bg-primary text-white' : 'bg-white text-dark border' }} px-2 py-1">
                            {{ $bk['bank'] }}: <strong>{{ $bk['count'] }} org</strong> &mdash; Rp {{ number_format((float) $bk['total'], 0, ',', '.') }}
                        </span>
                    @endforeach
                    @if(($pbSummary['unmapped_count'] ?? 0) > 0)
                        <span class="badge bg-danger text-white px-2 py-1">
                            <i class="fa fa-exclamation-circle me-1"></i>Belum Ada Rekening: {{ $pbSummary['unmapped_count'] }} org (Rp {{ number_format((float) ($pbSummary['unmapped_total'] ?? 0), 0, ',', '.') }})
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Tabel Rincian --}}
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 560px; overflow-x: auto; overflow-y: auto;">
                @if($pbMode === 'PAYROLL_THR')
                    {{-- TABEL RINCIAN PAYROLL THR + REKENING BANK --}}
                    <table class="table table-hover table-bordered table-striped align-middle mb-0 text-nowrap"
                        id="table_{{ $pbUniqueId }}" style="font-size: 0.82rem;">
                        <thead class="table-light sticky-top" style="z-index: 2;">
                            <tr class="text-center align-middle">
                                <th rowspan="2" class="px-2" style="width: 40px;">No</th>
                                <th rowspan="2" class="px-2" style="min-width: 110px;">Aksi Slip THR</th>
                                <th rowspan="2" class="px-2">NIK</th>
                                <th rowspan="2" class="px-3 text-start">Nama</th>
                                <th rowspan="2" class="px-2">Jabatan</th>
                                <th rowspan="2" class="px-2">Departemen</th>
                                <th rowspan="2" class="px-2">Site</th>
                                <th rowspan="2" class="px-2">Agama</th>
                                <th colspan="4" class="bg-info text-white py-1">KEPEGAWAIAN &amp; SKEMA THR</th>
                                <th colspan="4" class="bg-primary text-white py-1">KOMPONEN THR (EARNINGS)</th>
                                <th colspan="2" class="bg-danger text-white py-1">PAJAK PPh 21 THR</th>
                                <th rowspan="2" class="bg-success text-white px-3 fw-bold">Nett THR (THP)</th>
                                <th colspan="2" class="bg-secondary text-white py-1">TERBILANG</th>
                                <th colspan="2" class="bg-primary text-white py-1">REKENING BCA</th>
                                <th colspan="3" class="bg-dark text-white py-1">REKENING NON-BCA</th>
                            </tr>
                            <tr class="text-center align-middle small">
                                <th>Tanggal Join</th>
                                <th>Masa Kerja</th>
                                <th>Skema THR</th>
                                <th>Prorata (%)</th>

                                <th>Basis Upah</th>
                                <th>THR Sistem</th>
                                <th>Adjustment</th>
                                <th>THR Bruto</th>

                                <th>Tunj. PPh</th>
                                <th>PPh 21 THR</th>

                                <th>Bahasa Indonesia</th>
                                <th>Bahasa Inggris</th>

                                <th>No. Rekening</th>
                                <th>Atas Nama</th>

                                <th>Nama Bank</th>
                                <th>No. Rekening #2</th>
                                <th>Atas Nama #2</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pbRows as $row)
                                @php
                                    $hasBca = (!empty($row['bca_norek']) && $row['bca_norek'] !== '-');
                                    $hasNonBca = (!$hasBca && !empty($row['selain_bca_norek']) && $row['selain_bca_norek'] !== '-');
                                    $rowBankCat = $hasBca ? 'BCA' : ($hasNonBca ? 'NON_BCA' : 'UNMAPPED');
                                    $rowBankName = $hasBca ? 'BCA' : strtoupper(trim((string) ($row['selain_bca_bank'] ?? '')));
                                @endphp
                                <tr data-bank-cat="{{ $rowBankCat }}" data-bank-name="{{ $rowBankName }}">
                                    <td class="text-center">{{ $row['no'] }}</td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ url('payslip-thr') }}?batch_id={{ urlencode((string) ($payrollBreakdown['batch_id'] ?? 0)) }}&nik={{ urlencode((string) $row['nik']) }}"
                                            target="_blank"
                                            class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 shadow-sm"
                                            title="Buka Payslip THR Digital">
                                            <i class="fa fa-file-text-o me-1"></i> Slip THR
                                        </a>
                                    </td>
                                    <td class="text-center fw-semibold text-primary">{{ $row['nik'] }}</td>
                                    <td class="fw-bold text-dark">{{ $row['nama'] }}</td>
                                    <td>{{ $row['jabatan'] }}</td>
                                    <td>{{ $row['departemen'] }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $row['site'] }}</span></td>
                                    <td class="text-center">{{ $row['agama'] ?? '-' }}</td>

                                    <td class="text-center">{{ $row['tanggal_join'] }}</td>
                                    <td class="text-center small">{{ $row['masa_kerja'] }}</td>
                                    <td>{{ $row['skema_thr'] }}</td>
                                    <td class="text-end font-monospace">{{ number_format((float) ($row['prorate_percent'] ?? 0), 2, ',', '.') }}%</td>

                                    <td class="text-end font-monospace">{{ number_format((float) ($row['basis_upah'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format((float) ($row['thr_sistem'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format((float) ($row['adjustment'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-bold text-primary">{{ number_format((float) ($row['thr_bruto'] ?? 0), 0, ',', '.') }}</td>

                                    <td class="text-end font-monospace text-info">{{ number_format((float) ($row['tunjangan_pph'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger fw-semibold">{{ number_format((float) ($row['pph21_thr'] ?? 0), 0, ',', '.') }}</td>

                                    <td class="text-end font-monospace fw-bolder text-success" style="font-size: 0.95rem; background-color: rgba(40, 167, 69, 0.08);">
                                        {{ number_format((float) ($row['nett_thr'] ?? 0), 0, ',', '.') }}
                                    </td>

                                    <td class="small fst-italic text-wrap" style="max-width: 240px;">{{ $row['terbilang_id'] }}</td>
                                    <td class="small fst-italic text-wrap" style="max-width: 240px;">{{ $row['terbilang_en'] }}</td>

                                    <td class="text-center font-monospace fw-semibold text-primary">{{ $row['bca_norek'] }}</td>
                                    <td>{{ $row['bca_atas_nama'] }}</td>

                                    <td class="text-center">{{ ($row['selain_bca_bank'] ?? '') ?: '-' }}</td>
                                    <td class="text-center font-monospace fw-semibold">{{ $row['selain_bca_norek'] }}</td>
                                    <td>{{ $row['selain_bca_atas_nama'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="26" class="text-center py-4 text-muted">
                                        Tidak ada data rincian karyawan pada batch THR ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($pbRows) > 0)
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="12" class="text-end">TOTAL KESELURUHAN:</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('basis_upah'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('thr_sistem'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('adjustment'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-primary">{{ number_format(collect($pbRows)->sum('thr_bruto'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-info">{{ number_format(collect($pbRows)->sum('tunjangan_pph'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format(collect($pbRows)->sum('pph21_thr'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-success" style="font-size: 0.95rem;">
                                        Rp {{ number_format(collect($pbRows)->sum('nett_thr'), 0, ',', '.') }}
                                    </td>
                                    <td colspan="7"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                @else
                    {{-- TABEL RINCIAN PAYROLL REGULER (SAMA PERSIS DENGAN payroll-report.blade.php) --}}
                    <table class="table table-hover table-bordered table-striped align-middle mb-0 text-nowrap"
                        id="table_{{ $pbUniqueId }}" style="font-size: 0.82rem;">
                        <thead class="table-light sticky-top" style="z-index: 2;">
                            <tr class="text-center align-middle">
                                <th rowspan="2" class="px-2" style="width: 40px;">No</th>
                                <th rowspan="2" class="px-2" style="min-width: 115px;">Aksi Slip Gaji</th>
                                <th rowspan="2" class="px-2">NIK</th>
                                <th rowspan="2" class="px-3 text-start">Nama</th>
                                <th rowspan="2" class="px-2">Jabatan</th>
                                <th rowspan="2" class="px-2">Departemen</th>
                                <th rowspan="2" class="px-2">Site</th>
                                <th colspan="6" class="bg-primary text-white py-1">PENGHASILAN (EARNINGS)</th>
                                <th colspan="4" class="bg-info text-white py-1">DATA KEPEGAWAIAN &amp; HARI KERJA</th>
                                <th colspan="2" class="bg-warning text-dark py-1">LEMBUR (OVERTIME)</th>
                                <th colspan="7" class="bg-danger text-white py-1">POTONGAN &amp; DEDUKSI</th>
                                <th rowspan="2" class="bg-success text-white px-3">Total Penghasilan</th>
                                <th rowspan="2" class="bg-danger text-white px-3">Total Deduksi</th>
                                <th rowspan="2" class="bg-success text-white px-3 fw-bold">Nett (THP)</th>
                                <th colspan="2" class="bg-secondary text-white py-1">TERBILANG</th>
                                <th colspan="2" class="bg-primary text-white py-1">REKENING BCA</th>
                                <th colspan="3" class="bg-dark text-white py-1">REKENING NON-BCA</th>
                            </tr>
                            <tr class="text-center align-middle small">
                                <!-- Earnings -->
                                <th>Gaji Pokok</th>
                                <th>Cost of Living (COLA)</th>
                                <th>Tunjangan</th>
                                <th>Tunj. Kom &amp; Transport</th>
                                <th>Tunj. Sourcing</th>
                                <th>Total Salary</th>

                                <!-- Kepegawaian -->
                                <th>Tanggal Join</th>
                                <th>Masa Kerja</th>
                                <th>Gaji / Day</th>
                                <th>Hari Kerja</th>

                                <!-- Overtime -->
                                <th>Durasi Overtime (Jam)</th>
                                <th>Nilai Bonus Overtime</th>

                                <!-- Deduksi -->
                                <th>Jml Alpha</th>
                                <th>Unpaid Leave (Rp)</th>
                                <th>Jml Terlambat</th>
                                <th>Keterlambatan (Rp)</th>
                                <th>BPJS TK</th>
                                <th>BPJS Kes</th>
                                <th>PPh21</th>

                                <!-- Terbilang -->
                                <th>Bahasa Indonesia</th>
                                <th>Bahasa Inggris</th>

                                <!-- BCA -->
                                <th>No. Rekening</th>
                                <th>Atas Nama</th>

                                <!-- Non BCA -->
                                <th>Nama Bank</th>
                                <th>No. Rekening #2</th>
                                <th>Atas Nama #2</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pbRows as $row)
                                @php
                                    $hasBca = (!empty($row['bca_norek']) && $row['bca_norek'] !== '-');
                                    $hasNonBca = (!$hasBca && !empty($row['selain_bca_norek']) && $row['selain_bca_norek'] !== '-');
                                    $rowBankCat = $hasBca ? 'BCA' : ($hasNonBca ? 'NON_BCA' : 'UNMAPPED');
                                    $rowBankName = $hasBca ? 'BCA' : strtoupper(trim((string) ($row['selain_bca_bank'] ?? '')));
                                @endphp
                                <tr data-bank-cat="{{ $rowBankCat }}" data-bank-name="{{ $rowBankName }}">
                                    <td class="text-center">{{ $row['no'] }}</td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ url('payslip-digital') }}?nik={{ urlencode($row['nik']) }}&task_id={{ urlencode($row['task_id']) }}&period={{ urlencode($row['period']) }}{{ $pbSlipSource ? '&source=' . $pbSlipSource : '' }}"
                                            target="_blank"
                                            class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 shadow-sm"
                                            title="Buka Payslip Digital">
                                            <i class="fa fa-file-text-o me-1"></i> Slip Digital
                                        </a>
                                    </td>
                                    <td class="text-center fw-semibold text-primary">{{ $row['nik'] }}</td>
                                    <td class="fw-bold text-dark">{{ $row['nama'] }}</td>
                                    <td>{{ $row['jabatan'] }}</td>
                                    <td>{{ $row['departemen'] }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $row['site'] }}</span></td>

                                    <!-- Earnings -->
                                    <td class="text-end font-monospace">{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format($row['cola'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format($row['tunjangan'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format($row['tunj_kom_transport'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format($row['tunj_sourcing'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-bold text-primary">{{ number_format($row['total_salary'], 0, ',', '.') }}</td>

                                    <!-- Kepegawaian -->
                                    <td class="text-center">{{ $row['tanggal_join'] }}</td>
                                    <td class="text-center small">{{ $row['masa_kerja'] }}</td>
                                    <td class="text-end font-monospace">{{ number_format($row['gaji_per_day'], 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $row['total_hari_kerja'] }}</td>

                                    <!-- Overtime -->
                                    <td class="text-center">{{ $row['durasi_total_overtime'] }}</td>
                                    <td class="text-end font-monospace">{{ number_format($row['nilai_bonus_overtime'], 0, ',', '.') }}</td>

                                    <!-- Deduksi -->
                                    <td class="text-center {{ $row['jml_unpaid_leave'] > 0 ? 'text-danger fw-bold' : '' }}">{{ $row['jml_unpaid_leave'] }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format($row['unpaid_leave'], 0, ',', '.') }}</td>
                                    <td class="text-center {{ $row['jml_keterlambatan'] > 0 ? 'text-danger fw-bold' : '' }}">{{ $row['jml_keterlambatan'] }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format($row['keterlambatan'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format($row['bpjs_ketenagakerjaan'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format($row['bpjs_kesehatan'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger fw-semibold">{{ number_format($row['pph21'], 0, ',', '.') }}</td>

                                    <!-- Totals -->
                                    <td class="text-end font-monospace fw-bold text-success">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-bold text-danger">{{ number_format($row['deduksi'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-bolder text-success"
                                        style="font-size: 0.95rem; background-color: rgba(40, 167, 69, 0.08);">
                                        {{ number_format($row['nett'], 0, ',', '.') }}
                                    </td>

                                    <!-- Terbilang -->
                                    <td class="small fst-italic text-wrap" style="max-width: 250px;">{{ $row['terbilang_id'] }}</td>
                                    <td class="small fst-italic text-wrap" style="max-width: 250px;">{{ $row['terbilang_en'] }}</td>

                                    <!-- BCA -->
                                    <td class="text-center font-monospace fw-semibold text-primary">{{ $row['bca_norek'] }}</td>
                                    <td>{{ $row['bca_atas_nama'] }}</td>

                                    <!-- Non BCA -->
                                    <td class="text-center">{{ $row['selain_bca_bank'] ?: '-' }}</td>
                                    <td class="text-center font-monospace fw-semibold">{{ $row['selain_bca_norek'] }}</td>
                                    <td>{{ $row['selain_bca_atas_nama'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="38" class="text-center py-5 text-muted">
                                        <i class="fe fe-info fs-3 d-block mb-2 text-warning"></i>
                                        Tidak ada data rincian payroll yang ditemukan untuk batch ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($pbRows) > 0)
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="7" class="text-end">TOTAL KESELURUHAN:</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('gaji_pokok'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('cola'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('tunjangan'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('tunj_kom_transport'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('tunj_sourcing'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-primary">{{ number_format(collect($pbRows)->sum('total_salary'), 0, ',', '.') }}</td>
                                    <td colspan="5"></td>
                                    <td class="text-end font-monospace">{{ number_format(collect($pbRows)->sum('nilai_bonus_overtime'), 0, ',', '.') }}</td>
                                    <td class="text-center">{{ collect($pbRows)->sum('jml_unpaid_leave') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format(collect($pbRows)->sum('unpaid_leave'), 0, ',', '.') }}</td>
                                    <td class="text-center">{{ collect($pbRows)->sum('jml_keterlambatan') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format(collect($pbRows)->sum('keterlambatan'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format(collect($pbRows)->sum('bpjs_ketenagakerjaan'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format(collect($pbRows)->sum('bpjs_kesehatan'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format(collect($pbRows)->sum('pph21'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-success">{{ number_format(collect($pbRows)->sum('total'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format(collect($pbRows)->sum('deduksi'), 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-success" style="font-size: 0.95rem;">
                                        Rp {{ number_format(collect($pbRows)->sum('nett'), 0, ',', '.') }}
                                    </td>
                                    <td colspan="7"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof window.filterPayrollBreakdownTable !== 'function') {
        window.filterPayrollBreakdownTable = function(uid) {
            var searchEl = document.getElementById('searchInput_' + uid);
            var bankEl = document.getElementById('bankFilter_' + uid);
            var tableEl = document.getElementById('table_' + uid);
            if (!tableEl) return;

            var q = (searchEl ? searchEl.value : '').toUpperCase().trim();
            var bankFilter = bankEl ? bankEl.value : 'ALL';
            var tbody = tableEl.querySelector('tbody');
            if (!tbody) return;

            var rows = tbody.getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                var tr = rows[i];
                var bankCat = tr.getAttribute('data-bank-cat');
                if (!bankCat) continue; // skip empty state row

                var bankName = tr.getAttribute('data-bank-name') || '';
                var bankMatch = true;
                if (bankFilter === 'BCA_ONLY') {
                    bankMatch = (bankCat === 'BCA');
                } else if (bankFilter === 'NON_BCA_ONLY') {
                    bankMatch = (bankCat === 'NON_BCA');
                } else if (bankFilter === 'UNMAPPED_ONLY') {
                    bankMatch = (bankCat === 'UNMAPPED');
                } else if (bankFilter.indexOf('BANK:') === 0) {
                    var targetBank = bankFilter.substring(5);
                    bankMatch = (bankName === targetBank);
                }

                var textMatch = true;
                if (q !== '') {
                    var rowText = (tr.textContent || tr.innerText || '').toUpperCase();
                    textMatch = (rowText.indexOf(q) > -1);
                }

                tr.style.display = (bankMatch && textMatch) ? '' : 'none';
            }
        };
    }
</script>
@endif
