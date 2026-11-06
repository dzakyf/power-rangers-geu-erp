<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? 'SYSTEM';
$priv = Auth::user()->Priv_ID ?? '';

if (!isset($recruitment)) {
    $id = $_GET['ID'] ?? null;
    $recruitment = DB::table('recruitment')->where('ID', $id)->first();
}
if (!isset($requisition) && $recruitment) {
    $requisition = DB::table('requisition')->where('Task_ID', $recruitment->Requisition_ID)->first();
}

// Normalisasi kolom opsional yang mungkin belum ada di tabel recruitment
if ($recruitment) {
    foreach (['MCU_Result', 'MCU_Schedule', 'MCU_Note', 'MCU_File', 'Salary_Expectation', 'Salary_Accepted',
              'Position', 'Start_Date', 'Offering_Note', 'Education_Major', 'Major', 'Phone', 'Handphone',
              'Birth_Place', 'Birth_Date', 'Gender', 'Last_Education', 'Created_Date', 'Status'] as $col) {
        if (!property_exists($recruitment, $col)) {
            $recruitment->$col = null;
        }
    }
}

$defaultSalary = $recruitment->Salary_Accepted ?? ($recruitment->Salary_Expectation ?? ($detail->Salary_Expectation ?? ''));
$defaultPosition = $requisition->Job_Position ?? ($recruitment->Position ?? '');
$defaultStartDate = $recruitment->Start_Date ?? date('Y-m-d', strtotime('+7 days'));
$defaultNote = $recruitment->Offering_Note ?? '';
?>

@if (!$recruitment)
    <div class="alert alert-warning d-flex align-items-center shadow-sm">
        <i class="fa fa-triangle-exclamation me-2 fs-18"></i>
        <span>Data kandidat Offering tidak ditemukan atau telah diproses.</span>
    </div>
@else
    @php
        $statusUpper = strtoupper((string) ($recruitment->Status ?? ''));
        $statusBadgeClass = 'bg-info text-white';
        if (in_array($statusUpper, ['HIRED', 'ACCEPT', 'APPROVED', 'COMPLETED'])) {
            $statusBadgeClass = 'bg-success text-white';
        } elseif (in_array($statusUpper, ['REJECT', 'REJECTED', 'OFFER DECLINED', 'DECLINED'])) {
            $statusBadgeClass = 'bg-danger text-white';
        } elseif (in_array($statusUpper, ['OFFERING', 'PENDING', 'WAITING'])) {
            $statusBadgeClass = 'bg-warning text-dark';
        }

        $age = '-';
        if (!empty($recruitment->Birth_Date) && $recruitment->Birth_Date !== '0000-00-00') {
            $timeBirth = strtotime($recruitment->Birth_Date);
            if ($timeBirth > 0) {
                $age = (string) floor((time() - $timeBirth) / 31556926) . ' Tahun';
            }
        }
    @endphp

    <div class="card shadow-sm border mb-4" id="OfferingDetailCard" data-candidate-id="{{ $recruitment->ID }}"
        data-task-id="{{ $recruitment->Task_ID }}" data-candidate-name="{{ $recruitment->Name }}">
        {{-- Card Header --}}
        <div
            class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <div class="d-flex align-items-center">
                <span class="avatar avatar-md bg-success-transparent text-success rounded-circle me-3">
                    <i class="fa fa-handshake fs-18"></i>
                </span>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        Kandidat #{{ $recruitment->Task_ID }} &mdash; <span
                            class="text-primary">{{ $recruitment->Name }}</span>
                    </h5>
                    <small class="text-muted">
                        Posisi: <strong class="text-dark">{{ $defaultPosition ?: '-' }}</strong> &bull; Terdaftar:
                        {{ $recruitment->Created_Date ?: '-' }}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $statusBadgeClass }} px-3 py-2 fs-12">
                    <i class="fa fa-file-contract me-1"></i> {{ $recruitment->Status ?: 'STAGE OFFERING' }}
                </span>
            </div>
        </div>

        {{-- Quick Action / Alert Bar --}}
        <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <i class="fa fa-file-signature text-success me-2 fs-16"></i>
                <span class="fw-semibold text-dark small">Penerbitan Surat Penawaran Kerja (Offering Letter):</span>
                <span class="badge bg-white text-dark border ms-2 small">
                    Ekspektasi Gaji Awal: <strong>Rp
                        {{ !empty($recruitment->Salary_Expectation) ? number_format((float) $recruitment->Salary_Expectation, 0, ',', '.') : '-' }}</strong>
                </span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn"
                    onclick="$('#divlistRecruitment').html('');">
                    <i class="fa fa-xmark me-1"></i> Tutup
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            {{-- SECTION 1: Biodata & Riwayat Evaluasi Lengkap (2-Column Grid) --}}
            <div class="row g-3 mb-4">
                {{-- Kolom Kiri: Biodata --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa fa-id-card me-2"></i>Informasi Biodata Kandidat
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted fw-semibold" style="width: 155px;">No. Task ID</th>
                                    <td class="fw-bold text-dark">: {{ $recruitment->Task_ID }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Nama Lengkap</th>
                                    <td class="fw-bold text-primary">: {{ $recruitment->Name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Jenis Kelamin</th>
                                    <td>: <span
                                            class="badge bg-light text-dark border">{{ $recruitment->Gender ?: '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Tempat, Tgl Lahir</th>
                                    <td>: {{ $recruitment->Birth_Place ?: '-' }},
                                        {{ $recruitment->Birth_Date ?: '-' }} <small
                                            class="text-muted">({{ $age }})</small></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Pendidikan Terakhir</th>
                                    <td>: <span class="fw-semibold">{{ $recruitment->Last_Education ?: '-' }}</span>
                                        &bull; {{ $recruitment->Education_Major ?? ($recruitment->Major ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Kontak HP / Telepon</th>
                                    <td>: <i
                                            class="fa fa-phone text-muted me-1"></i>{{ $recruitment->Handphone ?: ($recruitment->Phone ?: '-') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Kolom Kanan: Ringkasan Hasil Evaluasi & MCU --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa fa-clipboard-check me-2"></i>Ringkasan Hasil Seleksi &amp; Kesehatan
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted fw-semibold" style="width: 155px;">Posisi Dilamar</th>
                                    <td class="fw-bold text-dark">: {{ $defaultPosition ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Departemen</th>
                                    <td>: {{ $requisition->Department ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Hasil MCU</th>
                                    <td>: <span class="badge bg-success text-white"><i
                                                class="fa fa-heart-pulse me-1"></i>{{ $recruitment->MCU_Result ?: 'Fit to Work' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Tanggal MCU</th>
                                    <td>: {{ $recruitment->MCU_Schedule ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Catatan Medis MCU</th>
                                    <td>: <span
                                            class="text-dark">{{ $recruitment->MCU_Note ?: 'Tidak ada catatan khusus.' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Ekspektasi Gaji</th>
                                    <td class="fw-bold text-dark">: Rp
                                        {{ !empty($recruitment->Salary_Expectation) ? number_format((float) $recruitment->Salary_Expectation, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Form Keputusan Penerbitan Offering Letter --}}
            <div class="card border border-primary shadow-sm mb-0" id="cardOfferingDecision">
                <div
                    class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-13 text-white">
                        <i class="fa fa-handshake me-2"></i>Formulir Kompensasi &amp; Penerbitan Offering Letter
                    </span>
                    <span class="badge bg-white text-primary fw-bold">
                        Petugas PIC: {{ $Saya }}
                    </span>
                </div>
                <div class="card-body p-4 bg-white">
                    <form id="formOfferingLetter" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="id" value="{{ $recruitment->ID }}">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="offer_position" class="form-label small fw-semibold text-dark">
                                    Posisi Jabatan Diterima <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" name="position"
                                    id="offer_position" value="{{ $defaultPosition }}" required
                                    placeholder="Contoh: IT Support Officer">
                                <div class="form-text fs-11">Nama posisi resmi yang tercantum di surat penawaran.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="offer_salary" class="form-label small fw-semibold text-dark">
                                    Gaji / THP yang Ditawarkan (IDR) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light fw-bold text-dark">Rp</span>
                                    <input type="number" min="0" step="1000" class="form-control fw-bold"
                                        name="salary" id="offer_salary" value="{{ $defaultSalary }}" required
                                        placeholder="Contoh: 7500000">
                                </div>
                                <div class="form-text fs-11">Nominal gaji yang disepakati bersama kandidat.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="offer_start_date" class="form-label small fw-semibold text-dark">
                                    Tanggal Mulai Bekerja (Join Date) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i
                                            class="fa fa-calendar-check text-muted"></i></span>
                                    <input type="date" class="form-control" name="start_date"
                                        id="offer_start_date" value="{{ $defaultStartDate }}" required>
                                </div>
                                <div class="form-text fs-11">Tanggal hari pertama bergabung (Onboarding).</div>
                            </div>

                            <div class="col-12">
                                <label for="offer_note" class="form-label small fw-semibold text-dark">
                                    Catatan Penawaran &amp; Fasilitas Tambahan
                                </label>
                                <textarea class="form-control form-control-sm" rows="3" name="note" id="offer_note"
                                    placeholder="Tuliskan rincian tunjangan transport, masa probation, fasilitas kerja, atau syarat khusus lainnya...">{{ $defaultNote }}</textarea>
                            </div>
                        </div>

                        <div
                            class="border-top pt-3 d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4">
                            <span class="text-muted small">
                                <i class="fa fa-circle-info text-info me-1"></i> Menerbitkan Offering Letter akan
                                otomatis membuat nomor surat dan menetapkan status kandidat menjadi
                                <strong>HIRED</strong>.
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3"
                                    onclick="$('#divlistRecruitment').html('');">
                                    <i class="fa fa-xmark me-1"></i> Batal
                                </button>
                                <button type="button" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm"
                                    onclick="prosesDeclineOffering({{ $recruitment->ID }})">
                                    <i class="fa fa-xmark-circle me-1"></i> Kandidat Menolak Tawaran
                                </button>
                                <button type="button"
                                    class="btn btn-success btn-sm px-4 text-white fw-bold shadow-sm"
                                    onclick="prosesIssueOffering()">
                                    <i class="fa fa-check-double me-1"></i> Terbitkan Offering &amp; Nyatakan HIRED
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pastikan SweetAlert2 tersedia (layout Adminor tidak memuatnya secara global)
        function ensureSwalOffering(cb) {
            if (typeof Swal !== 'undefined' && Swal.fire) { cb(); return; }
            if (window.__swal2Loading) { window.__swal2Loading.push(cb); return; }
            window.__swal2Loading = [cb];
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            s.onload = function() {
                var q = window.__swal2Loading || []; window.__swal2Loading = null;
                q.forEach(function(f) { f(); });
            };
            s.onerror = function() {
                window.__swal2Loading = null;
                alert('Gagal memuat SweetAlert2. Periksa koneksi internet lalu coba lagi.');
            };
            document.head.appendChild(s);
        }

        function prosesIssueOffering() {
            ensureSwalOffering(_prosesIssueOffering);
        }

        function prosesDeclineOffering(id) {
            ensureSwalOffering(function() { _prosesDeclineOffering(id); });
        }

        function _prosesIssueOffering() {
            var salary = $('#offer_salary').val();
            var position = $('#offer_position').val();
            var startDate = $('#offer_start_date').val();

            if (!salary || !position || !startDate) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Perhatian', 'Harap lengkapi Posisi, Gaji yang disepakati, dan Tanggal Join Date!',
                    'warning');
                } else if (typeof showAppNotification === 'function') {
                    showAppNotification('Perhatian', 'Harap lengkapi Posisi, Gaji yang disepakati, dan Tanggal Join Date!',
                        'warning');
                } else {
                    alert('Harap lengkapi Posisi, Gaji yang disepakati, dan Tanggal Join Date!');
                }
                return;
            }

            Swal.fire({
                title: 'Terbitkan Offering Letter?',
                text: 'Kandidat akan resmi diterima sebagai karyawan (HIRED) dan nomor surat offering dibuat otomatis.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Terbitkan & HIRED',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Menerbitkan surat penawaran & mengupdate data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('hrd-recruitment-issue-offering-letter') }}",
                        type: "POST",
                        data: $('#formOfferingLetter').serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $(
                                'input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Selamat!',
                                html: (response.message ||
                                        'Offering Letter berhasil diterbitkan!') +
                                    '<br><br>Nomor Surat: <strong>' + (response.data ? response
                                        .data.offering_letter_number : '-') + '</strong>'
                            }).then(() => {
                                $('#divlistRecruitment').html('');
                                if (typeof RecruitmentListsRefresh === 'function') {
                                    RecruitmentListsRefresh();
                                } else if (typeof refreshdata === 'function') {
                                    refreshdata();
                                }
                            });
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Terjadi kesalahan sistem';
                            Swal.fire('Gagal', msg, 'error');
                        }
                    });
                }
            });
        }

        function _prosesDeclineOffering(id) {
            Swal.fire({
                title: 'Kandidat Menolak Penawaran?',
                text: 'Masukkan alasan penolakan offering letter oleh kandidat:',
                input: 'textarea',
                inputPlaceholder: 'Tuliskan alasan penolakan (misal: negosiasi gaji tidak tercapai, memilih tempat lain)...',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tandai Menolak',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan wajib diisi!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Menyimpan status penolakan penawaran...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('hrd-recruitment-decline-offering') }}",
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val(),
                            id: id,
                            reason: result.value
                        },
                        success: function(res) {
                            Swal.fire('Offering Ditolak', res.message ||
                                'Status kandidat diubah menjadi OFFER DECLINED.', 'info').then(
                        () => {
                                $('#divlistRecruitment').html('');
                                if (typeof RecruitmentListsRefresh === 'function') {
                                    RecruitmentListsRefresh();
                                } else if (typeof refreshdata === 'function') {
                                    refreshdata();
                                }
                            });
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                            Swal.fire('Gagal', msg, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endif
