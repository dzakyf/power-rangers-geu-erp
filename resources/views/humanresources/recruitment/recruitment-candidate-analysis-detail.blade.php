@if(!$datahasil)
<div class="alert alert-warning d-flex align-items-center shadow-sm">
    <i class="fa fa-triangle-exclamation me-2 fs-18"></i>
    <span>Data kandidat wawancara user tidak ditemukan atau telah diproses.</span>
</div>
@else
@php
    $statusUpper = strtoupper((string) ($datahasil->Status ?? ''));
    $userInterviewStatusUpper = strtoupper((string) ($datahasil->User_Interview_Status ?? ''));
    
    $statusBadgeClass = 'bg-secondary text-white';
    if (in_array($statusUpper, ['ACCEPT', 'INTERVIEWED', 'BUSINESS_REVIEW', 'TECHNICAL_TEST', 'APPROVED', 'COMPLETED'])) {
        $statusBadgeClass = 'bg-success text-white';
    } elseif (in_array($statusUpper, ['REJECT', 'REJECTED', 'CANCELED', 'DECLINED'])) {
        $statusBadgeClass = 'bg-danger text-white';
    } elseif (in_array($statusUpper, ['PENDING', 'WAITING', 'INTERVIEW_USER'])) {
        $statusBadgeClass = 'bg-warning text-dark';
    } else {
        $statusBadgeClass = 'bg-info text-white';
    }

    $tipe = $datahasil->Recruitment_Type ?? '';
    $pesanNext = ($tipe == 'PRODUCTION') ? "Menunggu Review dari Unit Bisnis" : "Melanjutkan ke Tes Teknik / Tahap Selanjutnya";

    // Format Usia
    $age = '-';
    if (!empty($datahasil->Birth_Date) && $datahasil->Birth_Date !== '0000-00-00') {
        $timeBirth = strtotime($datahasil->Birth_Date);
        if ($timeBirth > 0) {
            $age = (string) floor((time() - $timeBirth) / 31556926) . ' Tahun';
        }
    }

    $saya = Auth::user()->username ?? 'SYSTEM';
@endphp

<div class="card shadow-sm border" id="CandidateAnalysisDetailCard" data-candidate-id="{{ $datahasil->ID }}" data-task-id="{{ $datahasil->Task_ID }}" data-candidate-name="{{ $datahasil->Name }}" data-position="{{ $datahasil->Position }}">
    {{-- Card Header --}}
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-info-transparent text-info rounded-circle me-3">
                <i class="fa fa-user-tie fs-18"></i>
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    Kandidat #{{ $datahasil->Task_ID }} &mdash; <span class="text-primary">{{ $datahasil->Name }}</span>
                </h5>
                <small class="text-muted">
                    Posisi: <strong class="text-dark">{{ $requisition->Job_Position ?? ($datahasil->Position ?: '-') }}</strong> &bull; Terdaftar: {{ $datahasil->Created_Date ?: '-' }}
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $statusBadgeClass }} px-3 py-2 fs-12">
                <i class="fa {{ in_array($statusUpper, ['ACCEPT', 'BUSINESS_REVIEW', 'TECHNICAL_TEST']) ? 'fa-check-circle' : (in_array($statusUpper, ['REJECT']) ? 'fa-times-circle' : 'fa-clock') }} me-1"></i>
                {{ $datahasil->Status ?: 'INTERVIEW USER' }}
            </span>
        </div>
    </div>

    {{-- Quick Action / Alert Bar --}}
    <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <i class="fa fa-user-check text-warning me-2 fs-16"></i>
            <span class="fw-semibold text-secondary small">Evaluasi Wawancara User (Atasan/Departemen):</span>
            <span class="badge bg-white text-dark border ms-2 small">
                Status User Interview: <strong>{{ $datahasil->User_Interview_Status ?: 'Belum Diputuskan' }}</strong>
            </span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm shadow-sm quick-action-btn" onclick="scrollToUserDecision();">
                <i class="fa fa-pen-to-square me-1"></i> Form Keputusan Interview User
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" onclick="RecruitmentListsCloseDetail();">
                <i class="fa fa-xmark me-1"></i> Tutup
            </button>
        </div>
    </div>

    {{-- Form Wrapper --}}
    <form id="formCandidateAnalysisDetail" onsubmit="return false;">
        <div class="card-body p-4" id="ContentPages">
            {{-- Hidden Control Inputs --}}
            <input type="hidden" id="ID" name="ID" value="{{ $datahasil->ID }}" />
            <input type="hidden" id="Recruitment_Type" name="Recruitment_Type" value="{{ $datahasil->Recruitment_Type }}" />
            <input type="hidden" id="Status" name="Status" value="{{ $datahasil->Status }}" />
            <input type="hidden" id="User_Interview_File" name="User_Interview_File" value="{{ $datahasil->User_Interview_File ?? '' }}" />

            {{-- ========================================================================= --}}
            {{-- SECTION 1: PROFIL KANDIDAT & HASIL TAHAP SEBELUMNYA (HR INTERVIEW)        --}}
            {{-- ========================================================================= --}}
            <div class="row g-3 mb-4">
                {{-- Kolom Kiri: Biodata Diri --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa fa-id-card me-2"></i>Informasi Biodata Kandidat
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted fw-semibold" style="width: 160px;">No. Task ID</th>
                                    <td class="fw-bold text-dark">: {{ $datahasil->Task_ID }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Nama Lengkap</th>
                                    <td class="fw-bold text-primary">: {{ $datahasil->Name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Jenis Kelamin</th>
                                    <td>: <span class="badge bg-light text-dark border">{{ $datahasil->Gender ?: '-' }}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Tempat, Tgl Lahir</th>
                                    <td>: {{ $datahasil->Birth_Place ?: '-' }}, {{ $datahasil->Birth_Date ?: '-' }} <small class="text-muted">({{ $age }})</small></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">No. KTP / NIK</th>
                                    <td>: <code>{{ $datahasil->KTP ?: '-' }}</code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Pendidikan Terakhir</th>
                                    <td>: <span class="fw-semibold">{{ $datahasil->Last_Education ?: '-' }}</span> &bull; {{ $datahasil->Education_Major ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Asal Institusi / Kampus</th>
                                    <td>: {{ $datahasil->Last_Education_From ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Kontak HP / Telepon</th>
                                    <td>: <i class="fa fa-phone text-muted me-1"></i>{{ $datahasil->Handphone ?: ($datahasil->Phone ?: '-') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Kolom Kanan: Posisi & Catatan Hasil Wawancara HR Sebelumnya --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa fa-clipboard-check me-2"></i>Hasil Tahap Interview HR (Evaluasi Awal)
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted fw-semibold" style="width: 160px;">Posisi Dilamar</th>
                                    <td class="fw-bold text-dark">: {{ $requisition->Job_Position ?? ($datahasil->Position ?: '-') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Status Interview HR</th>
                                    <td>: <span class="badge bg-success text-white">{{ $datahasil->Interview_Status ?: 'ACCEPT' }}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Pewawancara HR</th>
                                    <td>: {{ $datahasil->Interview_By ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Tgl Interview HR</th>
                                    <td>: {{ $datahasil->Interview_Date ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold align-top">Catatan Penilaian HR</th>
                                    <td>: <div class="p-2 bg-light rounded text-secondary fs-12 mt-1">{{ !empty($datahasil->Interview_Note) ? $datahasil->Interview_Note : ($datahasil->Interview_Result ?: 'Tidak ada catatan penilaian dari HR.') }}</div></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SECTION 2: PERTANYAAN WAJIB (HASIL INTERVIEW HR - READ ONLY)              --}}
            {{-- ========================================================================= --}}
            @php
                $usiaReadOnly = !empty($datahasil2->Usia) ? $datahasil2->Usia : $age;
                $cityFallback = $datahasil->City_Living ?? ($datahasil->City ?? '');
                $addrFallback = $datahasil->Address_Living ?? ($datahasil->Address ?? '');
                $defaultDomisili = trim($addrFallback . ($cityFallback ? ', ' . $cityFallback : ''), ', ');
                $domisiliReadOnly = !empty($datahasil2->Domisili) ? $datahasil2->Domisili : ($defaultDomisili ?: '-');
            @endphp
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-13">
                        <i class="fa fa-clipboard-question text-primary me-2"></i>Pertanyaan Wajib
                    </span>
                    <span class="badge bg-light text-muted border">
                        Hasil Input Interview HR (Read-Only)
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="small text-muted fw-semibold mb-1">1. Last Salary (Gaji Terakhir)</div>
                            <div class="p-2 bg-light border rounded small text-dark">{{ !empty($datahasil2->Last_Salary) ? 'Rp ' . $datahasil2->Last_Salary : '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted fw-semibold mb-1">2. Expected Salary (Ekspektasi Gaji)</div>
                            <div class="p-2 bg-light border rounded small text-dark">{{ !empty($datahasil2->Salary_Expectation) ? 'Rp ' . $datahasil2->Salary_Expectation : '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted fw-semibold mb-1">3. Usia</div>
                            <div class="p-2 bg-light border rounded small text-dark">{{ $usiaReadOnly ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted fw-semibold mb-1">4. Domisili</div>
                            <div class="p-2 bg-light border rounded small text-dark">{{ $domisiliReadOnly }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted fw-semibold mb-1">6. Notice Period (Estimasi Waktu Bergabung)</div>
                            <div class="p-2 bg-light border rounded small text-dark">{{ !empty($datahasil2->Notice_Period) ? $datahasil2->Notice_Period : '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted fw-semibold mb-1">5. Keahlian Komputer (Software Tertentu yang Relevan dengan Kebutuhan Posisi)</div>
                            <div class="p-2 bg-light border rounded small text-dark">{{ !empty($datahasil2->Computer) ? $datahasil2->Computer : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SECTION 3: KEPUTUSAN & EVALUASI WAWANCARA USER (FINAL INTERVIEW DECISION)   --}}
            {{-- ========================================================================= --}}
            <div class="card border border-primary shadow-sm mb-3" id="cardUserInterviewDecision">
                <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-13 text-white">
                        <i class="fa fa-clipboard-check me-2"></i>Keputusan &amp; Evaluasi Wawancara User (User Interview Decision)
                    </span>
                    <span class="badge bg-white text-primary fw-bold">
                        Pewawancara User: {{ $saya }}
                    </span>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="row g-3">
                        {{-- Tanggal Wawancara User --}}
                        <div class="col-md-4">
                            <label for="User_Interview_Date" class="form-label small fw-semibold text-dark">
                                Tanggal Wawancara User <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fa fa-calendar-day text-muted"></i></span>
                                <input type="date" class="form-control" id="User_Interview_Date" name="User_Interview_Date" value="{{ !empty($datahasil->User_Interview_Date) && $datahasil->User_Interview_Date !== '0000-00-00' ? $datahasil->User_Interview_Date : date('Y-m-d') }}" required />
                            </div>
                            <div class="form-text fs-11">Tanggal pelaksanaan interview bersama user.</div>
                        </div>

                        {{-- Keputusan Status Wawancara User --}}
                        <div class="col-md-4">
                            <label for="User_Interview_Status" class="form-label small fw-semibold text-dark">
                                Keputusan Status Wawancara User <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm fw-semibold" id="User_Interview_Status" name="User_Interview_Status" required>
                                <option value="">-- Pilih Keputusan --</option>
                                <option value="ACCEPT" {{ in_array($userInterviewStatusUpper, ['ACCEPT', 'ACCEPTED']) ? 'selected' : '' }}>ACCEPT (Lolos / Direkomendasikan)</option>
                                <option value="REJECT" {{ in_array($userInterviewStatusUpper, ['REJECT', 'REJECTED']) ? 'selected' : '' }}>REJECT (Tidak Lolos)</option>
                                <option value="PENDING" {{ $userInterviewStatusUpper === 'PENDING' ? 'selected' : '' }}>PENDING (Pertimbangan Tambahan)</option>
                                <option value="OFFER DECLINED" {{ $userInterviewStatusUpper === 'OFFER DECLINED' ? 'selected' : '' }}>OFFER DECLINED (Kandidat Menolak Penawaran)</option>
                                <option value="INVITATION DECLINED" {{ $userInterviewStatusUpper === 'INVITATION DECLINED' ? 'selected' : '' }}>INVITATION DECLINED (Kandidat Menolak Undangan)</option>
                            </select>
                            <div class="invalid-feedback" id="feedbackUserStatus">
                                Harap tentukan keputusan status wawancara user.
                            </div>
                        </div>

                        {{-- Pewawancara Info --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">
                                Pewawancara (User Interviewer)
                            </label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $datahasil->User_Interview_By ?: $saya }}" readonly />
                            <div class="form-text fs-11">User PIC atasan / departemen pengguna.</div>
                        </div>

                        {{-- Catatan Penilaian User (Gabungan Catatan Ringkas & Ulasan Hasil Akhir) --}}
                        <div class="col-12">
                            <label for="User_Interview_Note" class="form-label small fw-semibold text-dark">
                                Catatan Penilaian User
                            </label>
                            <textarea class="form-control form-control-sm" id="User_Interview_Note" name="User_Interview_Note" rows="4" placeholder="Tuliskan ringkasan hasil wawancara user serta penilaian terhadap kompetensi teknis, pengalaman, kemampuan, dan kesesuaian kandidat dengan kebutuhan posisi...">{{ !empty($datahasil->User_Interview_Note) ? $datahasil->User_Interview_Note : ($datahasil->User_Interview_Result ?? '') }}</textarea>
                            <div class="form-text fs-11">Digunakan untuk mencatat ringkasan hasil wawancara user serta penilaian kompetensi, pengalaman, kemampuan, dan kesesuaian kandidat dengan kebutuhan posisi.</div>
                        </div>

                        {{-- Unggah Berkas Rekap Wawancara User --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-cloud-arrow-up text-primary me-1"></i> Unggah Lembar Penilaian / Skor Wawancara User</span>
                                <span class="badge bg-light text-muted border">Maksimal 3 Berkas (PDF / Image)</span>
                            </label>

                            @if(!empty($datahasil->User_Interview_File))
                            <div class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <i class="fa fa-file-circle-check text-info me-2 fs-15"></i>
                                    <span>Berkas terlampir saat ini: <strong>{{ $datahasil->User_Interview_File }}</strong></span>
                                </div>
                                <a href="doc_recruitment/interview/{{ $datahasil->User_Interview_File }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 fs-11">
                                    <i class="fa fa-download me-1"></i> Unduh / Lihat Berkas
                                </a>
                            </div>
                            @endif

                            <div id="vaultUser" class="border rounded bg-light p-2" style="height:140px; width:100%; overflow:hidden;"></div>
                            <div id="vaultUserFileStatus" class="small mt-1 text-muted">
                                @if(!empty($datahasil->User_Interview_File))
                                    File aktif: <code>{{ $datahasil->User_Interview_File }}</code>. Unggah berkas baru melalui kotak di atas jika ingin mengganti.
                                @else
                                    Belum ada berkas diunggah. Klik atau tarik berkas ke kotak di atas.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="card-footer bg-light py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="text-muted small">
                        <i class="fa fa-info-circle text-info me-1"></i>
                        {{ $pesanNext }}
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="RecruitmentListsCloseDetail();">
                            <i class="fa fa-xmark me-1"></i> Batal / Tutup
                        </button>
                        <button type="button" id="SubmitUserInterview" name="SubmitUserInterview" class="btn btn-success btn-sm text-white shadow-sm quick-action-btn" onclick="submitUserInterviewEvaluation();">
                            <i class="fa fa-check me-1"></i> Simpan Hasil Wawancara User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
/**
 * Scroll halus ke kartu keputusan interview user
 */
function scrollToUserDecision() {
    var el = document.getElementById('cardUserInterviewDecision');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        var statusSelect = document.getElementById('User_Interview_Status');
        if (statusSelect) statusSelect.focus();
    }
}

// Inisialisasi Vault Uploader jika tersedia
(function initVaultUserUploader() {
    try {
        if (typeof dhx !== 'undefined' && dhx.Vault) {
            var vault = new dhx.Vault("vaultUser", {
                uploader: {
                    target: "upload-rekrut-interview.php"
                },
                filesLimit: 1
            });

            vault.events.on("uploadComplete", function(obj) {
                if (obj && obj[0] && obj[0].name) {
                    $("#User_Interview_File").val(obj[0].name);
                    $('#vaultUserFileStatus').html('<span class="text-success fw-semibold"><i class="fa fa-circle-check me-1"></i> Berkas siap disimpan: ' + obj[0].name + '</span>');
                }
            });

            vault.events.on("uploadBegin", function(obj) {
                $("#User_Interview_File").val("");
                $('#vaultUserFileStatus').html('<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Mengunggah berkas...</span>');
            });

            vault.events.on("beforeAdd", function(fileItem) {
                if (vault.data.getLength() >= 3) {
                    if (typeof showAppNotification === 'function') {
                        showAppNotification('Batas Berkas', 'Maksimal upload 3 file. Satukan dalam format PDF jika ingin mengunggah beberapa dokumen.', 'warning');
                    } else {
                        alert('Maksimal upload 3 file. Satukan dalam format PDF jika ingin mengunggah beberapa dokumen.');
                    }
                    return false;
                }
            });
        }
    } catch (e) {
        console.warn('dhx.Vault initialization skipped or fallback needed:', e);
    }
})();

/**
 * Submit Evaluasi Wawancara User Kandidat via AJAX
 */
function submitUserInterviewEvaluation() {
    var statusSelect = document.getElementById('User_Interview_Status');
    var statusVal = statusSelect ? statusSelect.value.trim() : '';

    if (!statusVal) {
        if (statusSelect) {
            statusSelect.classList.add('is-invalid');
            statusSelect.focus();
        }
        if (typeof showAppNotification === 'function') {
            showAppNotification('Peringatan', 'Harap tentukan keputusan status wawancara user terlebih dahulu.', 'warning');
        } else {
            alert('Harap tentukan keputusan status wawancara user terlebih dahulu.');
        }
        return false;
    }
    if (statusSelect) statusSelect.classList.remove('is-invalid');

    var submitBtn = document.getElementById('SubmitUserInterview');
    var originalBtnHtml = '';
    if (submitBtn) {
        originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...';
    }

    // Mengumpulkan seluruh nilai input dari formulir
    var inputValues = [];
    $('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
        var el = $(this);
        var fieldId = el.attr('id') || el.attr('name');
        var type = el.attr('type');

        if (!fieldId || type === 'button' || type === 'submit') {
            return;
        }

        if (type === 'checkbox' || type === 'radio') {
            if (this.checked) {
                inputValues.push(encodeURIComponent(fieldId) + '=' + encodeURIComponent(el.val()));
            }
        } else {
            inputValues.push(encodeURIComponent(fieldId) + '=' + encodeURIComponent(el.val()));
        }
    });

    var queryString = inputValues.join('&');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        url: "recruitment-candidate-analysis-detail-submit?_token={{ csrf_token() }}",
        type: "POST",
        data: queryString,
        success: function(data, textStatus, jqXHR) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }

            if (data && data.indexOf("success") !== -1) {
                var candidateName = "{{ $datahasil->Name }}";
                var pesanDetail = (statusVal === 'PENDING') 
                    ? 'Kandidat ' + candidateName + ' ditangguhkan dan dipindahkan ke antrean Pending.' 
                    : 'Evaluasi wawancara user untuk ' + candidateName + ' berhasil disimpan! {{ $pesanNext }}';

                if (typeof showAppNotification === 'function') {
                    showAppNotification('Berhasil', pesanDetail, 'success');
                } else {
                    alert(pesanDetail);
                }

                // Refresh data grid tabel utama
                if (typeof RecruitmentListsRefresh === 'function') {
                    RecruitmentListsRefresh();
                } else if (typeof refreshdata === 'function') {
                    refreshdata();
                }

                // Tutup atau muat ulang detail yang diperbarui
                setTimeout(function() {
                    if (typeof RecruitmentListsDetail === 'function') {
                        RecruitmentListsDetail("{{ $datahasil->ID }}", false);
                    }
                }, 400);

            } else {
                if (typeof showAppNotification === 'function') {
                    showAppNotification('Gagal', 'Gagal menyimpan keputusan interview user. Silakan periksa kembali data Anda.', 'danger');
                } else {
                    alert('Interview User Failed to Submit!');
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
            if (typeof showAppNotification === 'function') {
                showAppNotification('Error Server', 'Terjadi kesalahan sistem internal. Silakan coba kembali.', 'danger');
            } else {
                alert("Error Internal! Please Try again!");
            }
        }
    });

    return false;
}
</script>
@endif
