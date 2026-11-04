@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf
    <!-- Header -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Matriks Roster &amp; Shift Kerja
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human
                            Capital</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Roster
                            &amp; Attendance</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">Shift Matrix</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-users-gear me-1"></i> Multi-Shift &amp; Roster Schedule
            </span>
        </div>
    </div>

    <!-- Filter & Toolbar Card -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <form method="GET" action="{{ url('hrd-roster-schedule-matrix') }}"
                    class="d-flex align-items-center flex-wrap gap-2">
                    <label class="form-label mb-0 fw-semibold fs-13 text-secondary">
                        <i class="fa fa-calendar-alt text-primary me-1"></i> Periode:
                    </label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $yearMonth }}"
                    style="width: 160px;">

                    <label class="form-label mb-0 fw-semibold fs-13 text-secondary ms-2">
                        <i class="fa fa-calendar-check text-info me-1"></i> Kalender:
                    </label>
                    <select name="calendar_id" class="form-select form-select-sm" style="width: 200px;"
                        onchange="this.form.submit()">
                        <option value="">-- Semua Kalender --</option>
                        @foreach ($calendars as $cal)
                            <option value="{{ $cal->ID }}" {{ $selectedCalendarId == $cal->ID ? 'selected' : '' }}>
                                {{ $cal->Name }} ({{ $cal->Employee_Type }})
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-filter me-1"></i> Tampilkan
                    </button>
                    @if (!empty($selectedCalendarId))
                        <a href="{{ url('hrd-roster-schedule-matrix?month=' . $yearMonth) }}"
                            class="btn btn-sm btn-outline-secondary" title="Hapus Filter Kalender">
                            <i class="fa fa-rotate-left"></i> Reset
                        </a>
                    @endif
                </form>

                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="filterEmpInput" class="form-control border-start-0"
                            placeholder="Cari nama / NIK..." onkeyup="filterEmployeeRows()">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#assignRosterModal">
                        <i class="fa fa-user-plus me-1"></i> Tugaskan Roster
                    </button>
                    <a href="{{ url('hrd-master-work-calendar') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-calendar-days me-1"></i> Master Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend Shift Colors & Summary Statistics -->
    <div class="row g-2 mb-3 align-items-center">
        <div class="col-lg-8 col-md-12">
            <div class="d-flex flex-wrap align-items-center gap-2 fs-12 bg-white p-2 px-3 rounded shadow-sm border">
                <span class="text-muted fw-semibold me-1"><i class="fa fa-tags text-primary me-1"></i>Legenda Shift:</span>
                <span class="badge bg-primary text-white px-2 py-1"><i class="fa fa-sun me-1"></i>Pagi (SHIFT-1)</span>
                <span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-cloud-sun me-1"></i>Sore (SHIFT-2)</span>
                <span class="badge bg-dark text-white px-2 py-1"><i class="fa fa-moon me-1"></i>Malam (SHIFT-3 /
                    Cross-Day)</span>
                <span class="badge bg-danger text-white px-2 py-1"><i class="fa fa-bed me-1"></i>OFF / Libur</span>
                <span class="badge bg-info text-white px-2 py-1"><i class="fa fa-building me-1"></i>Office</span>
                <span class="badge bg-success text-white px-2 py-1"><i class="fa fa-user-check me-1"></i>Cuti/Izin</span>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 text-lg-end text-start">
            <span class="fs-12 text-muted bg-white p-2 px-3 rounded shadow-sm border d-inline-block">
                Total Karyawan Terjadwal: <strong class="text-primary">{{ $assignedUsers->count() }}</strong> Orang
            </span>
        </div>
    </div>

    <!-- Matrix Table Container -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 650px; overflow-x: auto; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-center" id="rosterTable"
                    style="font-size: 11px;">
                    <thead class="bg-light sticky-top" style="z-index: 10;">
                        <tr>
                            <th style="min-width: 220px; position: sticky; left: 0; background: #f8f9fa; z-index: 12;"
                                class="text-start border-end">
                                <div class="fw-bold text-dark">Data Karyawan</div>
                                <div class="text-muted fs-10 fw-normal">Nama, NIK &amp; Departemen</div>
                            </th>
                            @foreach ($dates as $d)
                                @php
                                    $dayNum = date('N', strtotime($d));
                                    $isWeekend = $dayNum == 6 || $dayNum == 7;
                                @endphp
                                <th
                                    style="min-width: 48px; {{ $isWeekend ? 'background-color: #ffeef0; color: #dc3545;' : '' }}">
                                    <div class="fw-bold">{{ date('d', strtotime($d)) }}</div>
                                    <div class="text-muted fs-10">{{ substr(date('D', strtotime($d)), 0, 2) }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedUsers as $assign)
                            <tr class="employee-row"
                                data-search="{{ strtolower(($assign->employee_name ?? '') . ' ' . $assign->user_id . ' ' . ($assign->department ?? '')) }}">
                                <td style="position: sticky; left: 0; background: #ffffff; z-index: 9;"
                                    class="text-start py-2 border-end shadow-sm">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 200px;"
                                        title="{{ $assign->employee_name ?? $assign->user_id }}">
                                        {{ $assign->employee_name ?? $assign->user_id }}
                                    </div>
                                    <div class="text-muted fs-10">
                                        <span class="badge bg-light text-dark border me-1">{{ $assign->user_id }}</span>
                                        {{ $assign->department ?? ($assign->pattern->pattern_name ?? 'Roster') }}
                                    </div>
                                </td>
                                @foreach ($dates as $d)
                                    @php
                                        $userSched = $schedules[$assign->user_id][$d][0] ?? null;
                                        $shiftCode = $userSched->shift->shift_code ?? '-';
                                        $shiftId = $userSched->shift_id ?? null;
                                        $isOverride = ($userSched->source_type ?? '') === 'MANUAL_OVERRIDE';
                                        $isLeave = ($userSched->source_type ?? '') === 'LEAVE';

                                        $badgeClass = 'bg-secondary text-white';
                                        if ($isLeave) {
                                            $badgeClass = 'bg-success text-white';
                                        } elseif ($shiftCode === 'SHIFT-1') {
                                            $badgeClass = 'bg-primary text-white';
                                        } elseif ($shiftCode === 'SHIFT-2') {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif ($shiftCode === 'SHIFT-3') {
                                            $badgeClass = 'bg-dark text-white';
                                        } elseif ($shiftCode === 'OFF') {
                                            $badgeClass = 'bg-danger text-white';
                                        } elseif ($shiftCode === 'OFFICE') {
                                            $badgeClass = 'bg-info text-white';
                                        }
                                    @endphp
                                    <td class="p-1 cursor-pointer shift-cell"
                                        onclick="openEditShiftModal('{{ $assign->user_id }}', '{{ $assign->employee_name ?? $assign->user_id }}', '{{ $d }}', '{{ $shiftId }}', '{{ $shiftCode }}')"
                                        title="Klik untuk ubah shift: {{ $assign->employee_name ?? $assign->user_id }} ({{ $d }})">
                                        <span
                                            class="badge {{ $badgeClass }} w-100 py-1 position-relative shadow-none border"
                                            style="font-size: 10px; cursor: pointer;">
                                            {{ $isLeave ? 'CUTI' : $shiftCode }}
                                            @if ($isOverride)
                                                <span
                                                    class="position-absolute top-0 start-100 translate-middle p-1 bg-warning border border-light rounded-circle"
                                                    title="Telah Diubah Manual"></span>
                                            @endif
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dates) + 1 }}" class="py-5 text-center text-muted">
                                    <i class="fa fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold">Belum Ada Penugasan Roster Aktif</h6>
                                    <p class="fs-12 mb-3">Karyawan belum ditugaskan ke pola siklus shift untuk periode ini.
                                    </p>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                        data-bs-target="#assignRosterModal">
                                        <i class="fa fa-user-plus me-1"></i> Tugaskan Roster Karyawan Sekarang
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div
            class="card-footer bg-light py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between text-muted fs-11">
            <div>
                <i class="fa fa-mouse-pointer text-primary me-1"></i> Klik pada kotak shift untuk melakukan <strong>Tukar
                    Shift / Manual Override</strong> tanggal tertentu.
            </div>
            <div>
                <span class="badge bg-warning p-1 rounded-circle me-1 d-inline-block"
                    style="width: 8px; height: 8px;"></span> = Jadwal hasil perubahan manual
            </div>
        </div>
    </div>

    <!-- Modal Edit Shift Manual (Tukar Shift) -->
    <div class="modal fade" id="editShiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title fw-bold fs-14"><i class="fa fa-pencil me-2"></i>Ubah Shift Manual</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <form id="formEditShift">
                        <div class="mb-2">
                            <label class="form-label fs-12 fw-semibold mb-1">Nama Karyawan</label>
                            <input type="text" id="modal_employee_name" class="form-control form-control-sm bg-light"
                                readonly>
                            <input type="hidden" id="modal_user_id">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fs-12 fw-semibold mb-1">Tanggal Kerja</label>
                            <input type="text" id="modal_work_date" class="form-control form-control-sm bg-light"
                                readonly>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fs-12 fw-semibold mb-1">Pilih Shift Baru</label>
                            <select id="modal_shift_id" class="form-select form-select-sm" required>
                                @foreach ($shifts as $sf)
                                    <option value="{{ $sf->id }}">
                                        {{ $sf->shift_code }} - {{ $sf->shift_name }}
                                        ({{ substr($sf->start_time, 0, 5) }} - {{ substr($sf->end_time, 0, 5) }})
                                        {{ $sf->is_cross_day ? ' [Cross-Day]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fs-12 fw-semibold mb-1">Alasan Penyesuaian (Notes)</label>
                            <input type="text" id="modal_notes" class="form-control form-control-sm"
                                placeholder="Contoh: Tukar shift dengan rekan / tugas khusus">
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="submitEditShift()">
                        <i class="fa fa-save me-1"></i> Simpan Penyesuaian
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Assign Pattern ke Karyawan -->
    <div class="modal fade" id="assignRosterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title fw-bold fs-14"><i class="fa fa-user-plus me-2"></i>Tugaskan Pola Roster Baru
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <form id="formAssignRoster">
                        <div class="mb-2">
                            <label class="form-label fs-12 fw-semibold mb-1">Pilih Karyawan</label>
                            <select id="assign_user_id" class="form-select form-select-sm select2-roster" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($employeeList as $emp)
                                    <option value="{{ $emp->username ?: $emp->Emp_ID }}">
                                        {{ $emp->Name }} ({{ $emp->Emp_ID }}) - {{ $emp->Department }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fs-12 fw-semibold mb-1">Pola Siklus Roster</label>
                            <select id="assign_pattern_id" class="form-select form-select-sm" required>
                                @foreach ($patterns as $pt)
                                    @php
                                        $calName = $pt->calendar_id
                                            ? $calendars->firstWhere('ID', $pt->calendar_id)->Name ?? ''
                                            : '';
                                    @endphp
                                    <option value="{{ $pt->id }}">
                                        {{ $pt->pattern_name }} (Siklus {{ $pt->cycle_days }} Hari)
                                        {{ $calName ? '[' . $calName . ']' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-2">
                                <label class="form-label fs-12 fw-semibold mb-1">Tanggal Mulai Berlaku</label>
                                <input type="date" id="assign_start_date" class="form-control form-control-sm"
                                    value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label fs-12 fw-semibold mb-1">Mulai Siklus Hari Ke-</label>
                                <input type="number" id="assign_start_seq" class="form-control form-control-sm"
                                    value="1" min="1" max="30">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-sm btn-success" onclick="submitAssignRoster()">
                        <i class="fa fa-bolt me-1"></i> Tugaskan &amp; Generate Jadwal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .shift-cell:hover {
            background-color: #e9ecef !important;
            transition: background 0.15s ease-in-out;
        }
    </style>

    <script>
        function filterEmployeeRows() {
            var q = $('#filterEmpInput').val().toLowerCase();
            $('.employee-row').each(function() {
                var search = $(this).attr('data-search') || '';
                if (search.indexOf(q) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function openEditShiftModal(userId, employeeName, workDate, currentShiftId, currentShiftCode) {
            $('#modal_user_id').val(userId);
            $('#modal_employee_name').val(employeeName + ' (' + userId + ')');
            $('#modal_work_date').val(workDate);
            if (currentShiftId) {
                $('#modal_shift_id').val(currentShiftId);
            }
            $('#modal_notes').val('');
            var modal = new bootstrap.Modal(document.getElementById('editShiftModal'));
            modal.show();
        }

        function submitEditShift() {
            var userId = $('#modal_user_id').val();
            var workDate = $('#modal_work_date').val();
            var shiftId = $('#modal_shift_id').val();
            var notes = $('#modal_notes').val();

            $.ajax({
                url: "{{ url('hrd-roster-schedule-matrix-update') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    work_date: workDate,
                    shift_id: shiftId,
                    notes: notes
                },
                success: function(res) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1200,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        alert(res.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                    alert('Error: ' + msg);
                }
            });
        }

        function submitAssignRoster() {
            var userId = $('#assign_user_id').val();
            var patternId = $('#assign_pattern_id').val();
            var startDate = $('#assign_start_date').val();
            var startSeq = $('#assign_start_seq').val();

            if (!userId) {
                alert('Silakan pilih karyawan terlebih dahulu.');
                return;
            }

            $.ajax({
                url: "{{ url('hrd-roster-schedule-matrix-assign') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    pattern_id: patternId,
                    start_date: startDate,
                    start_day_sequence: startSeq
                },
                success: function(res) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Penugasan Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        alert(res.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message :
                        'Terjadi kesalahan saat menugaskan roster.';
                    alert('Error: ' + msg);
                }
            });
        }
    </script>
@endsection
