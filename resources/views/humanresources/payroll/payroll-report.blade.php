@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-file-text me-2"></i>Report Payroll &amp; Bank Transfer Summary
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('payroll-tax') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
                            Payroll
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">
                        Payroll Report
                    </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ url('payroll-approved') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-check-circle me-1"></i> Approved Payroll
            </a>
            <a href="{{ url('payroll-generator') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-play me-1"></i> Generator
            </a>
            <a href="{{ url('payroll-tax') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-percent me-1"></i> Master Pajak
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-primary">Filter Data Laporan Payroll</h5>
                    <small class="text-muted">Pilih kriteria periode atau batch Task ID untuk menampilkan ringkasan
                        penggajian dan transfer bank.</small>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <form method="GET" action="{{ url('payroll-report') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Periode (Bulan):</label>
                        <select name="period" id="filter_period" class="form-select form-select-sm rounded-pill">
                            <option value="">-- Semua Periode --</option>
                            @foreach ($periods as $p)
                                <option value="{{ $p }}" {{ $selectedPeriod == $p ? 'selected' : '' }}>
                                    {{ $p }}</option>
                            @endforeach
                            @if (!in_array(date('Y-m'), $periods->toArray()))
                                <option value="{{ date('Y-m') }}" {{ $selectedPeriod == date('Y-m') ? 'selected' : '' }}>
                                    {{ date('Y-m') }} (Current)</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Task ID (Batch):</label>
                        <select name="task_id" id="filter_task_id" class="form-select form-select-sm rounded-pill">
                            <option value="">-- Semua Batch Penggajian --</option>
                            @foreach ($batches as $b)
                                <option value="{{ $b->Task_ID }}"
                                    {{ $selectedTaskId == $b->Task_ID ? 'selected' : '' }}>
                                    {{ $b->Task_ID }} ({{ $b->Salary_Period }} - {{ $b->Status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Departemen:</label>
                        <select name="department" id="filter_dept" class="form-select form-select-sm rounded-pill">
                            <option value="">-- Semua Dept --</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d }}" {{ $selectedDept == $d ? 'selected' : '' }}>
                                    {{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Site / Lokasi:</label>
                        <select name="site" id="filter_site" class="form-select form-select-sm rounded-pill">
                            <option value="">-- Semua Site --</option>
                            @foreach ($sites as $s)
                                <option value="{{ $s }}" {{ $selectedSite == $s ? 'selected' : '' }}>
                                    {{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Bank Rekening:</label>
                        <select name="bank" id="filter_bank" class="form-select form-select-sm rounded-pill">
                            <option value="">-- Semua Bank --</option>
                            @foreach ($banks as $bk)
                                <option value="{{ $bk->Bank_Name }}"
                                    {{ ($selectedBank ?? '') == $bk->Bank_Name ? 'selected' : '' }}>
                                    {{ $bk->Bank_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ url('payroll-report') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="fe fe-rotate-ccw me-1"></i> Reset Filter
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                            <i class="fe fe-filter me-1"></i> Tampilkan
                        </button>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm text-white"
                            onclick="exportExcel()">
                            <i class="fe fe-file-text me-1"></i> Export Excel
                        </button>
                    </div>
                </div>
        </div>
        </form>
    </div>
    <!-- Data Report Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div
            class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fe fe-list me-1 text-primary"></i> Data Rincian Payroll
                    <span class="badge bg-primary text-white ms-2">{{ count($reportData) }} Karyawan</span>
                </h6>
                <small class="text-muted">Total Nett (THP) Terhitung: <strong class="text-success">Rp
                        {{ number_format(collect($reportData)->sum('nett'), 0, ',', '.') }}</strong></small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                    onclick="openBlastModalReport()">
                    <i class="fe fe-send me-1"></i> Blasting Email Slip (Final)
                </button>
                <input type="text" id="tableSearch" class="form-control form-control-sm rounded-pill"
                    placeholder="Cari NIK / Nama / Dept..." style="width: 220px;" onkeyup="filterTable()">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-x: auto; overflow-y: auto;">
                <table class="table table-hover table-bordered table-striped align-middle mb-0 text-nowrap"
                    id="reportTable" style="font-size: 0.82rem;">
                    <thead class="table-light sticky-top" style="z-index: 2;">
                        <tr class="text-center align-middle">
                            <th rowspan="2" class="px-2" style="width: 35px;">
                                <input type="checkbox" id="checkAllReport" onchange="toggleSelectAllReport(this)" checked
                                    title="Pilih Semua Karyawan">
                            </th>
                            <th rowspan="2" class="px-2" style="width: 40px;">No</th>
                            <th rowspan="2" class="px-2" style="min-width: 130px;">Aksi Slip Gaji</th>
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
                        @forelse ($reportData as $row)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="report-row-check" value="{{ $row['nik'] }}"
                                        data-task="{{ $row['task_id'] }}" data-period="{{ $row['period'] }}"
                                        data-name="{{ $row['nama'] }}" checked>
                                </td>
                                <td class="text-center">{{ $row['no'] }}</td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ url('payslip-digital') }}?nik={{ urlencode($row['nik']) }}&task_id={{ urlencode($row['task_id']) }}&period={{ urlencode($row['period']) }}&source=final"
                                        target="_blank"
                                        class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 shadow-sm"
                                        title="Buka Payslip Digital Final">
                                        <i class="fa fa-file-text-o me-1"></i> Slip Digital
                                    </a>
                                    <button type="button"
                                        class="btn btn-xs btn-outline-success rounded-pill px-2 py-1 shadow-sm ms-1"
                                        onclick="sendSingleSlipEmail('{{ $row['nik'] }}', '{{ $row['period'] }}', '{{ $row['task_id'] }}', '{{ addslashes($row['nama']) }}')"
                                        title="Kirim Slip ke Email Karyawan Ini">
                                        <i class="fa fa-envelope"></i>
                                    </button>
                                </td>
                                <td class="text-center fw-semibold text-primary">{{ $row['nik'] }}</td>
                                <td class="fw-bold text-dark">{{ $row['nama'] }}</td>
                                <td>{{ $row['jabatan'] }}</td>
                                <td>{{ $row['departemen'] }}</td>
                                <td class="text-center"><span
                                        class="badge bg-light text-dark border">{{ $row['site'] }}</span></td>

                                <!-- Earnings -->
                                <td class="text-end font-monospace">{{ number_format($row['gaji_pokok'], 0, ',', '.') }}
                                </td>
                                <td class="text-end font-monospace">{{ number_format($row['cola'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace">{{ number_format($row['tunjangan'], 0, ',', '.') }}
                                </td>
                                <td class="text-end font-monospace">
                                    {{ number_format($row['tunj_kom_transport'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace">
                                    {{ number_format($row['tunj_sourcing'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace fw-bold text-primary">
                                    {{ number_format($row['total_salary'], 0, ',', '.') }}</td>

                                <!-- Kepegawaian -->
                                <td class="text-center">{{ $row['tanggal_join'] }}</td>
                                <td class="text-center small">{{ $row['masa_kerja'] }}</td>
                                <td class="text-end font-monospace">{{ number_format($row['gaji_per_day'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">{{ $row['total_hari_kerja'] }}</td>

                                <!-- Overtime -->
                                <td class="text-center">{{ $row['durasi_total_overtime'] }}</td>
                                <td class="text-end font-monospace">
                                    {{ number_format($row['nilai_bonus_overtime'], 0, ',', '.') }}</td>

                                <!-- Deduksi -->
                                <td class="text-center {{ $row['jml_unpaid_leave'] > 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $row['jml_unpaid_leave'] }}</td>
                                <td class="text-end font-monospace text-danger">
                                    {{ number_format($row['unpaid_leave'], 0, ',', '.') }}</td>
                                <td class="text-center {{ $row['jml_keterlambatan'] > 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $row['jml_keterlambatan'] }}</td>
                                <td class="text-end font-monospace text-danger">
                                    {{ number_format($row['keterlambatan'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace text-danger">
                                    {{ number_format($row['bpjs_ketenagakerjaan'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace text-danger">
                                    {{ number_format($row['bpjs_kesehatan'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace text-danger fw-semibold">
                                    {{ number_format($row['pph21'], 0, ',', '.') }}</td>

                                <!-- Totals -->
                                <td class="text-end font-monospace fw-bold text-success">
                                    {{ number_format($row['total'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace fw-bold text-danger">
                                    {{ number_format($row['deduksi'], 0, ',', '.') }}</td>
                                <td class="text-end font-monospace fw-bolder text-success"
                                    style="font-size: 0.95rem; background-color: rgba(40, 167, 69, 0.08);">
                                    {{ number_format($row['nett'], 0, ',', '.') }}
                                </td>

                                <!-- Terbilang -->
                                <td class="small fst-italic text-wrap" style="max-width: 250px;">
                                    {{ $row['terbilang_id'] }}</td>
                                <td class="small fst-italic text-wrap" style="max-width: 250px;">
                                    {{ $row['terbilang_en'] }}</td>

                                <!-- BCA -->
                                <td class="text-center font-monospace">{{ $row['bca_norek'] }}</td>
                                <td>{{ $row['bca_atas_nama'] }}</td>

                                <!-- Non BCA -->
                                <td class="text-center">{{ $row['selain_bca_bank'] ?: '-' }}</td>
                                <td class="text-center font-monospace">{{ $row['selain_bca_norek'] }}</td>
                                <td>{{ $row['selain_bca_atas_nama'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="39" class="text-center py-5 text-muted">
                                    <i class="fe fe-info fs-3 d-block mb-2 text-warning"></i>
                                    Tidak ada data payroll yang sesuai dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Modal Blasting Email Payslip Final -->
    <div class="modal fade" id="modalBlastEmailReport" tabindex="-1" aria-labelledby="modalBlastEmailReportLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="modalBlastEmailReportLabel">
                        <i class="fa fa-paper-plane me-2"></i>Blasting Email Slip Gaji (Final / Official)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small rounded-3 mb-3">
                        <i class="fa fa-info-circle me-1"></i>
                        Fitur ini mengirimkan payslip digital resmi (Final) secara massal langsung ke email masing-masing
                        karyawan yang dicentang di tabel laporan penggajian.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Filter Periode:</small>
                                <strong class="text-dark">{{ $selectedPeriod ?: 'Semua Periode' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Filter Batch / Task:</small>
                                <strong class="text-dark">{{ $selectedTaskId ?: 'Semua Batch' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Karyawan Tercentang:</small>
                                <strong id="blastReportCount" class="text-primary fs-6">0 Orang</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar Pengiriman -->
                    <div id="blastProgressBarWrapperReport" class="mb-3" style="display: none;">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span id="blastProgressTextReport">Memulai pengiriman email...</span>
                            <span id="blastProgressPercentReport" class="fw-bold text-primary">0%</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div id="blastProgressBarReport"
                                class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Live Results Table -->
                    <div class="table-responsive border rounded-3" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="blastResultsTableReport">
                            <thead class="table-light sticky-top">
                                <tr class="small text-muted">
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>NIK</th>
                                    <th>Nama Karyawan</th>
                                    <th>Email</th>
                                    <th style="width: 100px;" class="text-center">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="blastResultsBodyReport" class="small">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Klik tombol "Kirim Blasting
                                        Final Sekarang" untuk memulai proses pengiriman email.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal"
                        id="btnCancelBlastReport">Tutup</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                        id="btnStartBlastReport" onclick="startBlastingReport()">
                        <i class="fa fa-paper-plane me-1"></i> Kirim Blasting Final Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSelectAllReport(masterCheckbox) {
            var isChecked = !!masterCheckbox.checked;
            document.querySelectorAll('#reportTable tbody tr').forEach(function(row) {
                if (row.style.display !== 'none') {
                    var cb = row.querySelector('.report-row-check');
                    if (cb) cb.checked = isChecked;
                }
            });
        }

        function sendSingleSlipEmail(nik, period, taskId, name) {
            if (!confirm('Kirim slip gaji digital resmi ke email karyawan ' + name + ' (' + nik + ')?')) return;

            var btn = (typeof event !== 'undefined' && event.target) ? (event.target.closest('button') || event.target) :
                null;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            }

            $.ajax({
                url: '{{ url('api/payroll/send-payslip-email') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nik: nik,
                    period: period,
                    task_id: taskId,
                    source: 'final'
                },
                success: function(res) {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-envelope"></i>';
                    }
                    if (res && res.success) {
                        alert('Slip gaji berhasil dikirimkan ke: ' + res.recipient);
                    } else {
                        alert('Gagal mengirim: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-envelope"></i>';
                    }
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Terjadi kesalahan saat memproses pengiriman email.';
                    alert(msg);
                }
            });
        }

        var blastQueueReport = [];
        var blastIndexReport = 0;
        var blastSuccessReport = 0;
        var blastFailedReport = 0;
        var blastSkippedReport = 0;

        function openBlastModalReport() {
            blastQueueReport = [];
            $('.report-row-check:checked').each(function() {
                blastQueueReport.push({
                    nik: $(this).val(),
                    period: $(this).data('period'),
                    task_id: $(this).data('task'),
                    name: $(this).data('name')
                });
            });

            if (blastQueueReport.length === 0) {
                alert('Centang minimal 1 karyawan pada tabel untuk melakukan blasting email.');
                return;
            }

            $('#blastReportCount').text(blastQueueReport.length + ' Orang');
            $('#blastProgressBarWrapperReport').hide();
            $('#blastProgressBarReport').css('width', '0%').removeClass('bg-success').addClass(
                'bg-primary progress-bar-animated');
            $('#blastProgressPercentReport').text('0%');
            $('#blastProgressTextReport').text('Menunggu konfirmasi...');
            $('#blastResultsBodyReport').html(
                '<tr><td colspan="6" class="text-center py-4 text-muted">Klik tombol "Kirim Blasting Final Sekarang" untuk mengirim ke ' +
                blastQueueReport.length + ' karyawan terpilih.</td></tr>');

            $('#btnStartBlastReport').prop('disabled', false).html(
                '<i class="fa fa-paper-plane me-1"></i> Kirim Blasting Final Sekarang');
            $('#btnCancelBlastReport').prop('disabled', false);

            var modalEl = document.getElementById('modalBlastEmailReport');
            var bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }

        function startBlastingReport() {
            if (!blastQueueReport.length) return;
            if (!confirm('Yakin ingin mengirim email slip gaji digital resmi ke ' + blastQueueReport.length +
                    ' karyawan terpilih?')) return;

            $('#btnStartBlastReport').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin me-1"></i> Sedang Mengirim...');
            $('#btnCancelBlastReport').prop('disabled', true);
            $('#blastProgressBarWrapperReport').show();
            $('#blastResultsBodyReport').empty();

            blastIndexReport = 0;
            blastSuccessReport = 0;
            blastFailedReport = 0;
            blastSkippedReport = 0;

            processNextBlastChunkReport();
        }

        function processNextBlastChunkReport() {
            if (blastIndexReport >= blastQueueReport.length) {
                $('#btnStartBlastReport').prop('disabled', false).html('<i class="fa fa-check me-1"></i> Selesai');
                $('#btnCancelBlastReport').prop('disabled', false);
                $('#blastProgressBarReport').removeClass('progress-bar-animated').addClass('bg-success');
                $('#blastProgressTextReport').html('<strong class="text-success">Pengiriman Selesai!</strong> Berhasil: ' +
                    blastSuccessReport + ', Gagal: ' + blastFailedReport + ', Dilewati: ' + blastSkippedReport);
                return;
            }

            var chunkSize = 3;
            var chunkItems = blastQueueReport.slice(blastIndexReport, blastIndexReport + chunkSize);
            var chunkNiks = chunkItems.map(function(item) {
                return item.nik;
            });
            var samplePeriod = chunkItems[0].period || '{{ $selectedPeriod }}';
            var sampleTaskId = chunkItems[0].task_id || '{{ $selectedTaskId }}';

            $.ajax({
                url: '{{ url('api/payroll/blast-payslip-emails') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    niks: chunkNiks,
                    period: samplePeriod,
                    task_id: sampleTaskId,
                    source: 'final'
                },
                success: function(res) {
                    if (res && res.results) {
                        res.results.forEach(function(item) {
                            blastIndexReport++;
                            var badgeHtml = '';
                            if (item.status === 'SENT') {
                                blastSuccessReport++;
                                badgeHtml = '<span class="badge bg-success text-white">Terkirim</span>';
                            } else if (item.status === 'SKIPPED') {
                                blastSkippedReport++;
                                badgeHtml = '<span class="badge bg-warning text-dark">Dilewati</span>';
                            } else {
                                blastFailedReport++;
                                badgeHtml = '<span class="badge bg-danger text-white">Gagal</span>';
                            }

                            var rowHtml = '<tr>' +
                                '<td class="text-center">' + blastIndexReport + '</td>' +
                                '<td class="font-monospace fw-semibold">' + item.nik + '</td>' +
                                '<td>' + (item.name || '-') + '</td>' +
                                '<td class="small text-muted">' + (item.email || '-') + '</td>' +
                                '<td class="text-center">' + badgeHtml + '</td>' +
                                '<td class="small text-truncate" style="max-width: 200px;" title="' + (
                                    item.message || '') + '">' + (item.message || '-') + '</td>' +
                                '</tr>';
                            $('#blastResultsBodyReport').append(rowHtml);
                        });
                    } else {
                        blastIndexReport += chunkItems.length;
                        blastFailedReport += chunkItems.length;
                    }

                    var pct = Math.min(100, Math.round((blastIndexReport / blastQueueReport.length) * 100));
                    $('#blastProgressBarReport').css('width', pct + '%');
                    $('#blastProgressPercentReport').text(pct + '%');
                    $('#blastProgressTextReport').text('Mengirim ' + blastIndexReport + ' dari ' +
                        blastQueueReport.length + ' karyawan...');

                    var container = $('#blastResultsTableReport').parent();
                    container.scrollTop(container.prop('scrollHeight'));

                    setTimeout(processNextBlastChunkReport, 200);
                },
                error: function() {
                    blastIndexReport += chunkItems.length;
                    blastFailedReport += chunkItems.length;
                    var pct = Math.min(100, Math.round((blastIndexReport / blastQueueReport.length) * 100));
                    $('#blastProgressBarReport').css('width', pct + '%');
                    $('#blastProgressPercentReport').text(pct + '%');
                    setTimeout(processNextBlastChunkReport, 200);
                }
            });
        }

        function exportExcel() {
            var params = $('#filterForm').serialize();
            window.location.href = "{{ url('payroll-report-export-excel') }}?" + params;
        }

        function filterTable() {
            var input = document.getElementById("tableSearch");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("reportTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 2; i < tr.length; i++) {
                var visible = false;
                var tds = tr[i].getElementsByTagName("td");
                for (var j = 0; j < tds.length; j++) {
                    if (tds[j]) {
                        var txtValue = tds[j].textContent || tds[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            visible = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = visible ? "" : "none";
            }
        }
    </script>
@endsection
