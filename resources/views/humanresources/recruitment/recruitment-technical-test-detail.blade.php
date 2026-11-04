<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

$saya = Auth::user()->username ?? 'SYSTEM';

if (!isset($recruitment)) {
    $id = $_GET['ID'] ?? $_GET['id'] ?? null;
    $recruitment = DB::table("recruitment")->where("ID", $id)->first();
}
if (!isset($requisition) && $recruitment && !empty($recruitment->Requisition_ID)) {
    $requisition = DB::table("requisition")->where("Task_ID", $recruitment->Requisition_ID)->first();
}
if (!isset($recruitmentDetail) && $recruitment && !empty($recruitment->Task_ID)) {
    if (Schema::hasTable('recruitment_detail')) {
        $recruitmentDetail = DB::table('recruitment_detail')->where('Task_ID', $recruitment->Task_ID)->first();
    } else {
        $recruitmentDetail = null;
    }
}

$technicalScore = $recruitment->Technical_Score ?? ($recruitment->Test_Score ?? '');
$technicalResult = $recruitment->Test_Result ?? '';
$technicalNote = $recruitment->Test_Note ?? '';
$technicalFile = $recruitment->Technical_File ?? '';
$technicalStatus = $recruitment->Technical_Test_Status ?? ($recruitment->Status ?? 'TECHNICAL_TEST');

// Perhitungan Usia
$age = '-';
if (!empty($recruitment->Birth_Date) && $recruitment->Birth_Date !== '0000-00-00') {
    $timeBirth = strtotime($recruitment->Birth_Date);
    if ($timeBirth > 0) {
        $age = (string) floor((time() - $timeBirth) / 31556926) . ' Tahun';
    }
}
?>

@if(!$recruitment)
    <div class="alert alert-danger shadow-sm d-flex align-items-center">
        <i class="fa fa-triangle-exclamation me-2 fs-18"></i>
        <span>Data kandidat tidak ditemukan atau telah diproses.</span>
    </div>
@else
<div class="card shadow-sm border mb-4 animate__animated animate__fadeIn" id="TechnicalTestDetailCard">
    {{-- Card Header --}}
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle me-3">
                <i class="fa fa-laptop-code fs-18"></i>
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    Evaluasi Tes Teknik: <span class="text-primary">{{ $recruitment->Name }}</span>
                    <small class="text-muted fw-normal">({{ $recruitment->Task_ID }})</small>
                </h5>
                <small class="text-muted">
                    Posisi: <strong class="text-dark">{{ $requisition->Job_Position ?? ($recruitment->Position ?? '-') }}</strong>
                    &bull; No PO: <strong>{{ $requisition->PO_No ?? ($recruitment->Reference_No ?? '-') }}</strong>
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info text-white px-3 py-2 fs-12">
                <i class="fa fa-code-branch me-1"></i> Status: {{ $technicalStatus }}
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="RecruitmentListsCloseDetail();" title="Tutup Formulir Evaluasi">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
    </div>

    {{-- Quick Action Alert Bar --}}
    <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <i class="fa fa-circle-check text-success me-2 fs-16"></i>
            <span class="fw-semibold text-secondary small">Penilaian Kompetensi &amp; Hasil Uji Praktik Teknis Kandidat:</span>
            <span class="badge bg-white text-dark border ms-2 small">
                Skor Saat Ini: <strong>{{ $technicalScore !== '' ? $technicalScore : 'Belum Dinilai' }}</strong>
            </span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm shadow-sm quick-action-btn" onclick="scrollToEvaluationForm();">
                <i class="fa fa-pen-to-square me-1"></i> Form Skor &amp; Keputusan
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" onclick="RecruitmentListsCloseDetail();">
                <i class="fa fa-xmark me-1"></i> Tutup
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        {{-- ========================================================================= --}}
        {{-- SECTION 1: PROFIL KANDIDAT & RIWAYAT INTERVIEW SEBELUMNYA                 --}}
        {{-- ========================================================================= --}}
        <div class="row g-3 mb-4">
            {{-- Biodata Kandidat --}}
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white shadow-sm">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                        <i class="fa fa-id-card me-2"></i>Informasi Biodata Kandidat
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0 fs-13">
                            <tbody>
                                <tr>
                                    <td class="text-muted text-nowrap" style="width: 140px;">No. Registrasi / ID</td>
                                    <td class="fw-bold text-dark">: {{ $recruitment->Task_ID }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-nowrap">Nama Lengkap</td>
                                    <td class="fw-semibold text-dark">: {{ $recruitment->Name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-nowrap">Jenis Kelamin</td>
                                    <td>: {{ $recruitment->Gender }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-nowrap">Tanggal Lahir / Usia</td>
                                    <td>: {{ $recruitment->Birth_Date }} ({{ $age }})</td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-nowrap">Pendidikan Terakhir</td>
                                    <td>: {{ $recruitment->Last_Education ?? '-' }} {{ !empty($recruitment->Major) ? ' - ' . $recruitment->Major : '' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-nowrap">Kontak (HP / Email)</td>
                                    <td>: {{ $recruitment->Handphone ?? '-' }} / {{ $recruitment->Email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-nowrap">Lokasi Rekrutmen</td>
                                    <td>: <span class="badge bg-light text-dark border">{{ $recruitment->Location ?? '-' }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Rekap Hasil Tahapan Sebelumnya (Psikotest & Interview User) --}}
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white shadow-sm">
                    <h6 class="fw-bold text-info mb-3 border-bottom pb-2">
                        <i class="fa fa-timeline me-2"></i>Riwayat Evaluasi Tahap Sebelumnya
                    </h6>
                    <div class="p-2 mb-2 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark fs-12"><i class="fa fa-brain text-purple me-1"></i> Psikotest</span>
                            <span class="badge bg-success text-white fs-11">{{ $recruitment->Psikotest_Status ?? 'ACCEPT' }}</span>
                        </div>
                        <div class="small text-muted">
                            Skor IQ / Kesimpulan: <strong>{{ $recruitment->Psikotest_Score ?? '-' }}</strong> &bull; {{ $recruitment->Psikotest_Conclusion ?? 'Memenuhi syarat' }}
                        </div>
                    </div>

                    <div class="p-2 mb-2 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark fs-12"><i class="fa fa-user-tie text-warning me-1"></i> Wawancara HR &amp; User</span>
                            <span class="badge bg-success text-white fs-11">LOLOS WAWANCARA</span>
                        </div>
                        <div class="small text-muted">
                            Catatan HR: <em>"{{ $recruitment->Interview_Result ?? $recruitment->Interview_Conclusion ?? 'Lolos ke user' }}"</em>
                        </div>
                        @if(!empty($recruitment->User_Interview_Result) || !empty($recruitment->User_Interview_Note))
                        <div class="small text-dark mt-1 border-top pt-1">
                            <strong>Rekomendasi User:</strong> <em>"{{ $recruitment->User_Interview_Result ?? $recruitment->User_Interview_Note }}"</em>
                        </div>
                        @endif
                    </div>

                    @if(!empty($recruitmentDetail->Computer))
                    <div class="p-2 bg-light rounded border">
                        <div class="small fw-bold text-dark mb-1"><i class="fa fa-microchip text-primary me-1"></i> Penguasaan Komputer / Software (Kuesioner):</div>
                        <div class="small text-muted">{{ $recruitmentDetail->Computer }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTION 2: FORMULIR PENILAIAN & UPLOAD BERKAS TES TEKNIK                  --}}
        {{-- ========================================================================= --}}
        <form id="formTechnicalTest" onsubmit="return false;">
            @csrf
            <input type="hidden" name="id" id="technical_recruitment_id" value="{{ $recruitment->ID }}">
            <input type="hidden" name="technical_file" id="technical_file" value="{{ $technicalFile }}">

            <div class="card border mb-3 shadow-sm" id="sectionEvaluationForm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-pen-ruler text-primary me-2"></i>Formulir Penilaian Hasil Tes Teknik
                    </h6>
                    <span class="badge bg-light text-dark border">
                        Penguji: <strong>{{ $saya }}</strong>
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-dark">
                                Skor Tes Teknik (0 - 100) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-percent"></i></span>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" name="score" id="tech_score" value="{{ $technicalScore }}" required placeholder="Contoh: 85.50">
                            </div>
                            <div class="form-text fs-11">Skala 0 sampai 100 dengan desimal.</div>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-bold small text-dark">
                                Ringkasan Hasil Evaluasi / Kesimpulan Penguji <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="result" id="tech_result" value="{{ $technicalResult }}" placeholder="Contoh: Menguasai problem-solving, logika pemrograman, dan arsitektur data dengan sangat memuaskan." required>
                            <div class="form-text fs-11">Kesimpulan singkat mengenai kecakapan dan kompetensi teknis kandidat.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">
                                Catatan Tambahan Penguji / Rekomendasi Teknis
                            </label>
                            <textarea class="form-control" rows="3" name="note" id="tech_note" placeholder="Tuliskan catatan teknis mendalam mengenai kelebihan atau area improvement kandidat...">{{ $technicalNote }}</textarea>
                            <div class="form-text fs-11">Ulasan detail yang dapat digunakan untuk evaluasi masa probation nantinya.</div>
                        </div>
                    </div>

                    {{-- Upload Berkas Lembar Soal / Hasil Jawaban Tes Teknik --}}
                    <div class="border rounded p-3 bg-light mb-2">
                        <label class="form-label small fw-semibold text-dark d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fa fa-cloud-arrow-up text-primary me-1"></i> Unggah Berkas / Lembar Soal &amp; Jawaban Tes Teknik</span>
                            <span class="badge bg-white text-muted border">Maksimal 1 Berkas (PDF / ZIP / Image)</span>
                        </label>

                        @if(!empty($technicalFile))
                        <div class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <i class="fa fa-file-circle-check text-info me-2 fs-15"></i>
                                <span>Berkas aktif: <strong>{{ $technicalFile }}</strong></span>
                            </div>
                            <a href="{{ url('doc_recruitment/technical/' . $technicalFile) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 fs-11">
                                <i class="fa fa-download me-1"></i> Unduh / Buka Berkas
                            </a>
                        </div>
                        @endif

                        <div id="vaultTechnical" class="border rounded bg-white p-2" style="height:140px; width:100%; overflow:hidden;"></div>
                        <div id="vaultTechFileStatus" class="small mt-2 text-muted">
                            @if(!empty($technicalFile))
                                File tersimpan: <code>{{ $technicalFile }}</code>. Tarik file baru ke kotak di atas jika ingin memperbarui.
                            @else
                                Belum ada berkas lembar tes diunggah. Klik atau tarik berkas hasil tes ke area di atas.
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="text-muted small">
                        <i class="fa fa-circle-info text-info me-1"></i>
                        Menyetujui evaluasi tes teknik akan secara otomatis meloloskan kandidat ke tahap <strong>MCU (Medical Check Up)</strong>.
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="RecruitmentListsCloseDetail();">
                            <i class="fa fa-xmark me-1"></i> Tutup
                        </button>
                        <button type="button" class="btn btn-danger btn-sm quick-action-btn" onclick="prosesRejectTechnical({{ $recruitment->ID }});">
                            <i class="fa fa-times-circle me-1"></i> Tidak Lolos / Tolak
                        </button>
                        <button type="button" class="btn btn-success btn-sm text-white shadow-sm quick-action-btn" onclick="prosesApproveTechnical();">
                            <i class="fa fa-check-circle me-1"></i> Loloskan ke Tahap MCU
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function scrollToEvaluationForm() {
    var el = document.getElementById('sectionEvaluationForm');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        $('#tech_score').focus();
    }
}

// Inisialisasi dhx.Vault untuk Upload Berkas Tes Teknik
(function initVaultTechnical() {
    try {
        if (typeof dhx !== 'undefined' && dhx.Vault) {
            var vault = new dhx.Vault("vaultTechnical", {
                uploader: {
                    target: "{{ url('upload-rekrut-tech.php') }}"
                },
                filesLimit: 1
            });

            vault.events.on("uploadComplete", function(obj) {
                if (obj && obj[0] && obj[0].name) {
                    $("#technical_file").val(obj[0].name);
                    $('#vaultTechFileStatus').html('<span class="text-success fw-semibold"><i class="fa fa-circle-check me-1"></i> Berkas siap disimpan: ' + obj[0].name + '</span>');
                }
            });

            vault.events.on("uploadBegin", function(obj) {
                $("#technical_file").val("");
                $('#vaultTechFileStatus').html('<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Sedang mengunggah berkas...</span>');
            });
        }
    } catch(e) {
        console.warn('dhx.Vault initialization skipped or fallback needed:', e);
    }
})();

// Pastikan SweetAlert2 tersedia (layout Adminor tidak memuat Swal secara global)
function ensureSwalTechnical(cb) {
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

function prosesApproveTechnical() {
    ensureSwalTechnical(_prosesApproveTechnical);
}

function _prosesApproveTechnical() {
    var score = $('#tech_score').val();
    if (!score || score === '') {
        Swal.fire('Perhatian', 'Harap masukkan skor tes teknik (0 - 100) terlebih dahulu!', 'warning');
        $('#tech_score').focus();
        return;
    }

    var resultSummary = $('#tech_result').val();
    if (!resultSummary || $.trim(resultSummary) === '') {
        Swal.fire('Perhatian', 'Harap isi ringkasan hasil evaluasi tes teknik terlebih dahulu!', 'warning');
        $('#tech_result').focus();
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Lolos Tes Teknik?',
        html: 'Kandidat <strong>{{ addslashes($recruitment->Name) }}</strong> akan dinyatakan lulus dengan skor <strong>' + score + '</strong> dan dialihkan ke tahap <strong>MCU (Medical Check Up)</strong>.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa fa-check-circle me-1"></i> Ya, Loloskan ke MCU',
        cancelButtonText: 'Batal'
    }).then((res) => {
        if (res.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Menyimpan evaluasi dan mengalihkan status kandidat...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: "{{ url('hrd-recruitment-approve-technical-test') }}",
                type: "POST",
                data: $('#formTechnicalTest').serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Evaluasi tes teknik berhasil disimpan. Kandidat masuk ke antrean MCU.'
                    }).then(() => {
                        RecruitmentListsCloseDetail();
                        refreshdata();
                    });
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem saat menyimpan evaluasi.';
                    Swal.fire('Gagal Memproses', msg, 'error');
                }
            });
        }
    });
}

function prosesRejectTechnical(id) {
    ensureSwalTechnical(function() { _prosesRejectTechnical(id); });
}

function _prosesRejectTechnical(id) {
    Swal.fire({
        title: 'Tolak Kandidat pada Tes Teknik?',
        html: 'Kandidat <strong>{{ addslashes($recruitment->Name) }}</strong> akan dinyatakan tidak lolos tes teknik.<br><br>Tuliskan alasan penolakan secara jelas:',
        input: 'textarea',
        inputPlaceholder: 'Tuliskan alasan kandidat tidak lolos tes teknik...',
        inputAttributes: {
            'aria-label': 'Tuliskan alasan penolakan'
        },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fa fa-times-circle me-1"></i> Tolak Kandidat',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value || $.trim(value) === '') {
                return 'Alasan penolakan wajib diisi!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Menyimpan status penolakan kandidat...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: "{{ url('hrd-recruitment-reject-technical-test') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
                    id: id,
                    reason: result.value
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Kandidat Ditolak',
                        text: response.message || 'Kandidat telah dinyatakan tidak lolos tahapan tes teknik.'
                    }).then(() => {
                        RecruitmentListsCloseDetail();
                        refreshdata();
                    });
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem saat memperbarui status penolakan.';
                    Swal.fire('Gagal Menolak', msg, 'error');
                }
            });
        }
    });
}
</script>
@endif
