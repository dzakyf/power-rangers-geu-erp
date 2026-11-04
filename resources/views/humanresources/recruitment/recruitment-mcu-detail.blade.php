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

$mcuSchedule = !empty($recruitment->MCU_Schedule) ? substr($recruitment->MCU_Schedule, 0, 10) : date('Y-m-d');
$mcuResult = $recruitment->MCU_Result ?? '';
$mcuNote = $recruitment->MCU_Note ?? '';
$mcuFile = $recruitment->MCU_File ?? '';
?>

@if (!$recruitment)
    <div class="alert alert-warning d-flex align-items-center shadow-sm">
        <i class="fa fa-triangle-exclamation me-2 fs-18"></i>
        <span>Data kandidat MCU tidak ditemukan atau telah diproses.</span>
    </div>
@else
    @php
        $statusUpper = strtoupper((string) ($recruitment->Status ?? ''));
        $statusBadgeClass = 'bg-info text-white';
        if (in_array($statusUpper, ['ACCEPT', 'OFFERING', 'APPROVED', 'COMPLETED', 'HIRED'])) {
            $statusBadgeClass = 'bg-success text-white';
        } elseif (in_array($statusUpper, ['REJECT', 'REJECTED', 'UNFIT', 'CANCELED'])) {
            $statusBadgeClass = 'bg-danger text-white';
        } elseif (in_array($statusUpper, ['MCU', 'PENDING', 'WAITING'])) {
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

    <div class="card shadow-sm border mb-4" id="McuDetailCard" data-candidate-id="{{ $recruitment->ID }}"
        data-task-id="{{ $recruitment->Task_ID }}" data-candidate-name="{{ $recruitment->Name }}">
        {{-- Card Header --}}
        <div
            class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <div class="d-flex align-items-center">
                <span class="avatar avatar-md bg-danger-transparent text-danger rounded-circle me-3">
                    <i class="fa fa-notes-medical fs-18"></i>
                </span>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        Kandidat #{{ $recruitment->Task_ID }} &mdash; <span
                            class="text-primary">{{ $recruitment->Name }}</span>
                    </h5>
                    <small class="text-muted">
                        Posisi: <strong
                            class="text-dark">{{ $requisition->Job_Position ?? ($recruitment->Position ?: '-') }}</strong>
                        &bull; Terdaftar: {{ $recruitment->Created_Date ?: '-' }}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $statusBadgeClass }} px-3 py-2 fs-12">
                    <i class="fa fa-heart-pulse me-1"></i> {{ $recruitment->Status ?: 'STAGE MCU' }}
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="$('#divlistRecruitment').html('');" title="Tutup Formulir Detail">
                    <i class="fa fa-xmark"></i>
                </button>
            </div>
        </div>

        {{-- Quick Action / Alert Bar --}}
        <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <i class="fa fa-stethoscope text-danger me-2 fs-16"></i>
                <span class="fw-semibold text-dark small">Evaluasi Pemeriksaan Kesehatan (Medical Check Up):</span>
                <span class="badge bg-white text-dark border ms-2 small">
                    Hasil MCU: <strong>{{ $mcuResult ?: 'Belum Diinput' }}</strong>
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
            {{-- SECTION 1: Biodata & Riwayat Evaluasi Sebelumnya (2-Column Grid) --}}
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

                {{-- Kolom Kanan: Ringkasan Tahap Sebelumnya --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa fa-clipboard-check me-2"></i>Ringkasan Evaluasi Tahap Sebelumnya
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted fw-semibold" style="width: 155px;">Posisi Dilamar</th>
                                    <td class="fw-bold text-dark">:
                                        {{ $requisition->Job_Position ?? ($recruitment->Position ?: '-') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Departemen</th>
                                    <td>: {{ $requisition->Department ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Hasil Psikotest</th>
                                    <td>: <span
                                            class="badge bg-success text-white">{{ $recruitment->Psikotes_Result ?? ($recruitment->Psikotest_Result ?? 'LOLOS') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Interview HR / User</th>
                                    <td>: <span
                                            class="badge bg-success text-white">{{ $recruitment->User_Interview_Status ?? ($recruitment->Interview_Status ?? 'ACCEPT') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Skor Tes Teknik</th>
                                    <td>: <span class="fw-semibold text-success"><i
                                                class="fa fa-check-circle me-1"></i>{{ $recruitment->Technical_Score ?? ($recruitment->Test_Score ?? 'Memenuhi Syarat') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">No. Referensi PTK</th>
                                    <td>: <code>{{ $recruitment->Requisition_ID ?: '-' }}</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Form Keputusan & Hasil Pemeriksaan MCU --}}
            <div class="card border border-primary shadow-sm mb-0" id="cardMcuDecision">
                <div
                    class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-13 text-white">
                        <i class="fa fa-heart-pulse me-2"></i>Formulir Hasil &amp; Keputusan Medical Check Up (MCU)
                    </span>
                    <span class="badge bg-white text-primary fw-bold">
                        Petugas PIC: {{ $Saya }}
                    </span>
                </div>
                <div class="card-body p-4 bg-white">
                    <form id="formMcuEvaluation" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="id" value="{{ $recruitment->ID }}">
                        <input type="hidden" name="mcu_file" id="mcu_file" value="{{ $mcuFile }}">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="mcu_schedule" class="form-label small fw-semibold text-dark">
                                    Tanggal Pelaksanaan MCU <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i
                                            class="fa fa-calendar-day text-muted"></i></span>
                                    <input type="date" class="form-control" name="schedule" id="mcu_schedule"
                                        value="{{ $mcuSchedule }}" required>
                                </div>
                                <div class="form-text fs-11">Tanggal pemeriksaan medis di klinik/rumah sakit rekanan.
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label for="mcu_result" class="form-label small fw-semibold text-dark">
                                    Kesimpulan Hasil Pemeriksaan / Lab MCU <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" name="result"
                                    id="mcu_result" list="mcuResultSuggestions" value="{{ $mcuResult }}" required
                                    placeholder="Contoh: Fit to Work / Fit with Note / Unfit">
                                <datalist id="mcuResultSuggestions">
                                    <option value="Fit to Work (Memenuhi Syarat Medis)">
                                    <option value="Fit with Note (Sehat dengan Catatan Medis)">
                                    <option value="Temporary Unfit (Perlu Pemeriksaan Lanjutan)">
                                    <option value="Unfit (Tidak Memenuhi Syarat Medis)">
                                </datalist>
                                <div class="form-text fs-11">Pilih dari saran atau ketik hasil kesimpulan dokter
                                    pemeriksa.</div>
                            </div>

                            <div class="col-12">
                                <label for="mcu_note" class="form-label small fw-semibold text-dark">
                                    Catatan Hasil MCU / Rekomendasi Klinik
                                </label>
                                <textarea class="form-control form-control-sm" rows="3" name="note" id="mcu_note"
                                    placeholder="Tuliskan catatan medis, hasil laboratorium penting, atau rekomendasi dokter...">{{ $mcuNote }}</textarea>
                            </div>

                            {{-- Upload Berkas Hasil MCU --}}
                            <div class="col-12">
                                <div class="border rounded p-3 bg-light">
                                    <label class="form-label small fw-semibold text-dark d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fa fa-cloud-arrow-up text-primary me-1"></i> Unggah Berkas Hasil MCU / Surat Keterangan Sehat</span>
                                        <span class="badge bg-white text-muted border">Maksimal 1 Berkas (PDF / Image / ZIP)</span>
                                    </label>

                                    @if (!empty($mcuFile))
                                        <div class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-between mb-2">
                                            <div>
                                                <i class="fa fa-file-circle-check text-info me-2 fs-15"></i>
                                                <span>Berkas aktif: <strong>{{ basename($mcuFile) }}</strong></span>
                                            </div>
                                            <a href="{{ url($mcuFile) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 fs-11">
                                                <i class="fa fa-download me-1"></i> Unduh / Buka Berkas
                                            </a>
                                        </div>
                                    @endif

                                    <div id="vaultMcu" class="border rounded bg-white p-2" style="height:140px; width:100%; overflow:hidden;"></div>
                                    <div id="vaultMcuFileStatus" class="small mt-2 text-muted">
                                        @if (!empty($mcuFile))
                                            File tersimpan. Tarik file baru ke kotak di atas jika ingin memperbarui.
                                        @else
                                            Belum ada berkas hasil MCU diunggah. Klik atau tarik berkas ke area di atas.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-top pt-3 d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4">
                            <span class="text-muted small">
                                <i class="fa fa-circle-info text-info me-1"></i> Menyetujui MCU akan otomatis
                                mengalihkan status kandidat ke tahap <strong>Offering Letter</strong>.
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3"
                                    onclick="$('#divlistRecruitment').html('');">
                                    <i class="fa fa-xmark me-1"></i> Batal
                                </button>
                                <button type="button" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm"
                                    onclick="prosesRejectMcu({{ $recruitment->ID }})">
                                    <i class="fa fa-xmark-circle me-1"></i> Tolak / Unfit
                                </button>
                                <button type="button"
                                    class="btn btn-success btn-sm px-4 text-white fw-bold shadow-sm"
                                    onclick="prosesApproveMcu()">
                                    <i class="fa fa-check-circle me-1"></i> Loloskan ke Offering Letter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inisialisasi dhx.Vault untuk Upload Berkas MCU
        (function initVaultMcu() {
            try {
                if (typeof dhx !== 'undefined' && dhx.Vault) {
                    var vault = new dhx.Vault("vaultMcu", {
                        uploader: { target: "{{ url('upload-rekrut-mcu.php') }}" },
                        filesLimit: 1
                    });
                    vault.events.on("uploadBegin", function() {
                        $("#mcu_file").val("");
                        $('#vaultMcuFileStatus').html('<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Sedang mengunggah berkas...</span>');
                    });
                    vault.events.on("uploadComplete", function(obj) {
                        if (obj && obj[0] && obj[0].name) {
                            $("#mcu_file").val(obj[0].name);
                            $('#vaultMcuFileStatus').html('<span class="text-success fw-semibold"><i class="fa fa-circle-check me-1"></i> Berkas siap disimpan: ' + obj[0].name.split('/').pop() + '</span>');
                        }
                    });
                }
            } catch (e) {
                console.warn('dhx.Vault MCU initialization skipped:', e);
            }
        })();

        // Pastikan SweetAlert2 tersedia (layout Adminor tidak memuatnya secara global)
        function ensureSwalMcu(cb) {
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

        function prosesApproveMcu() {
            ensureSwalMcu(_prosesApproveMcu);
        }

        function prosesRejectMcu(id) {
            ensureSwalMcu(function() { _prosesRejectMcu(id); });
        }

        function _prosesApproveMcu() {
            var result = $('#mcu_result').val();
            if (!result || result.trim() === '') {
                $('#mcu_result').addClass('is-invalid').focus();
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Perhatian', 'Harap isi hasil pemeriksaan MCU terlebih dahulu!', 'warning');
                } else if (typeof showAppNotification === 'function') {
                    showAppNotification('Perhatian', 'Harap isi hasil pemeriksaan MCU terlebih dahulu!', 'warning');
                } else {
                    alert('Harap isi hasil pemeriksaan MCU terlebih dahulu!');
                }
                return;
            }
            $('#mcu_result').removeClass('is-invalid');

            Swal.fire({
                title: 'Konfirmasi Kelulusan MCU?',
                text: 'Kandidat dinyatakan lolos pemeriksaan kesehatan dan siap diproses ke tahap Offering Letter.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Loloskan ke Offering',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Menyimpan data MCU...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('hrd-recruitment-approve-mcu') }}",
                        type: "POST",
                        data: $('#formMcuEvaluation').serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $(
                                'input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message ||
                                    'MCU berhasil disetujui. Kandidat lanjut ke tahap Offering Letter.'
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

        function _prosesRejectMcu(id) {
            Swal.fire({
                title: 'Tolak Kandidat pada Tahap MCU?',
                text: 'Masukkan alasan Unfit / penolakan medis:',
                input: 'textarea',
                inputPlaceholder: 'Tuliskan catatan medis penolakan...',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tolak MCU',
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
                        text: 'Menyimpan status penolakan MCU...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('hrd-recruitment-reject-mcu') }}",
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val(),
                            id: id,
                            reason: result.value
                        },
                        success: function(res) {
                            Swal.fire('MCU Ditolak', res.message || 'Kandidat dinyatakan Unfit.',
                                'info').then(() => {
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
