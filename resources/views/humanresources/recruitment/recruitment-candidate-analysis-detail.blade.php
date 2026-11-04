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
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="RecruitmentListsCloseDetail();" title="Tutup Formulir Detail">
                <i class="fa fa-xmark"></i>
            </button>
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
                                    <th class="text-muted fw-semibold align-top">Catatan HR</th>
                                    <td>: <div class="p-2 bg-light rounded text-secondary fs-12 mt-1">{{ $datahasil->Interview_Note ?: 'Tidak ada catatan khusus dari HR.' }}</div></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold align-top">Ulasan HR</th>
                                    <td>: <div class="p-2 bg-light rounded text-secondary fs-12 mt-1">{{ $datahasil->Interview_Result ?: 'Lolos kriteria awal HR.' }}</div></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SECTION 2: KUESIONER & PROFIL MENDALAM PELAMAR (ADVANCED INFORMATION)     --}}
            {{-- ========================================================================= --}}
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-13">
                        <i class="fa fa-clipboard-question text-primary me-2"></i>Kuesioner &amp; Ekspektasi Kerja Pelamar
                    </span>
                    <span class="badge bg-light text-muted border">
                        Jawaban Asli Kandidat
                    </span>
                </div>
                <div class="card-body p-3">
                    <ul class="nav nav-tabs nav-tabs-bordered mb-3" id="kuesionerUserTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="tab-user-motivasi" data-bs-toggle="tab" data-bs-target="#content-user-motivasi" type="button" role="tab" aria-controls="content-user-motivasi" aria-selected="true">
                                <i class="fa fa-compass me-1"></i> Motivasi &amp; Pengalaman
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="tab-user-karakter" data-bs-toggle="tab" data-bs-target="#content-user-karakter" type="button" role="tab" aria-controls="content-user-karakter" aria-selected="false">
                                <i class="fa fa-user-gear me-1"></i> Keahlian &amp; Karakteristik
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="tab-user-preferensi" data-bs-toggle="tab" data-bs-target="#content-user-preferensi" type="button" role="tab" aria-controls="content-user-preferensi" aria-selected="false">
                                <i class="fa fa-sliders me-1"></i> Preferensi Kerja &amp; Gaji
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2" id="kuesionerUserTabContent">
                        {{-- Tab 1: Motivasi & Pengalaman --}}
                        <div class="tab-pane fade show active" id="content-user-motivasi" role="tabpanel" aria-labelledby="tab-user-motivasi">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="Describe_Self" class="form-label small fw-semibold text-dark">
                                        1. Ceritakan tentang diri Anda (Describe Yourself)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Describe_Self" name="Describe_Self" rows="3">{{ $datahasil2->Describe_Self ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Left_Job" class="form-label small fw-semibold text-dark">
                                        2. Alasan meninggalkan pekerjaan terakhir (Why you left last job?)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Left_Job" name="Left_Job" rows="3">{{ $datahasil2->Left_Job ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Previous_Boss" class="form-label small fw-semibold text-dark">
                                        3. Pandangan tentang atasan terdahulu (What do you truly think about your previous boss?)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Previous_Boss" name="Previous_Boss" rows="3">{{ $datahasil2->Previous_Boss ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Describe_Know" class="form-label small fw-semibold text-dark">
                                        4. Pengetahuan tentang perusahaan kami (What do you know about our company?)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Describe_Know" name="Describe_Know" rows="3">{{ $datahasil2->Describe_Know ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Describe_Improve" class="form-label small fw-semibold text-dark">
                                        5. Upaya pengembangan keahlian terakhir (What did you do to improve your knowledge lately?)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Describe_Improve" name="Describe_Improve" rows="3">{{ $datahasil2->Describe_Improve ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Describe_Looking" class="form-label small fw-semibold text-dark">
                                        6. Hal yang dicari pada posisi ini (What are you looking for in this position?)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Describe_Looking" name="Describe_Looking" rows="3">{{ $datahasil2->Describe_Looking ?? '' }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="Describe_Interest" class="form-label small fw-semibold text-dark">
                                        7. Alasan tertarik bergabung dengan perusahaan (Why are you interested to work here?)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Describe_Interest" name="Describe_Interest" rows="2">{{ $datahasil2->Describe_Interest ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 2: Keahlian & Karakteristik --}}
                        <div class="tab-pane fade" id="content-user-karakter" role="tabpanel" aria-labelledby="tab-user-karakter">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="Describe_Strong" class="form-label small fw-semibold text-dark">
                                        <i class="fa fa-award text-success me-1"></i> Kelebihan / Kekuatan Utama (Strengths)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Describe_Strong" name="Describe_Strong" rows="3">{{ $datahasil2->Describe_Strong ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Describe_Weak" class="form-label small fw-semibold text-dark">
                                        <i class="fa fa-circle-exclamation text-warning me-1"></i> Kelemahan Terbesar &amp; Solusinya (Weaknesses)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Describe_Weak" name="Describe_Weak" rows="3">{{ $datahasil2->Describe_Weak ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Computer" class="form-label small fw-semibold text-dark">
                                        <i class="fa fa-laptop-code text-primary me-1"></i> Keahlian Komputer &amp; Aplikasi Teknis
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Computer" name="Computer" rows="3">{{ $datahasil2->Computer ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="Free_Time" class="form-label small fw-semibold text-dark">
                                        <i class="fa fa-umbrella-beach text-info me-1"></i> Pemanfaatan Waktu Luang / Hobi (Leisure Time)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Free_Time" name="Free_Time" rows="3">{{ $datahasil2->Free_Time ?? '' }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="Questions_Us" class="form-label small fw-semibold text-dark">
                                        <i class="fa fa-circle-question text-secondary me-1"></i> Pertanyaan Kandidat untuk Perusahaan (Do you have any questions?)
                                    </label>
                                    <textarea class="form-control form-control-sm bg-light" id="Questions_Us" name="Questions_Us" rows="2">{{ $datahasil2->Questions_Us ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 3: Preferensi Kerja & Gaji --}}
                        <div class="tab-pane fade" id="content-user-preferensi" role="tabpanel" aria-labelledby="tab-user-preferensi">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="Salary_Expectation" class="form-label small fw-semibold text-dark">
                                        Ekspektasi Gaji (Salary Expectation)
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light fw-bold">Rp</span>
                                        <input type="text" class="form-control" id="Salary_Expectation" name="Salary_Expectation" value="{{ $datahasil2->Salary_Expectation ?? '' }}" placeholder="Contoh: 5.000.000" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="Facility_Expectation" class="form-label small fw-semibold text-dark">
                                        Ekspektasi Fasilitas Lainnya
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="Facility_Expectation" name="Facility_Expectation" value="{{ $datahasil2->Facility_Expectation ?? '' }}" placeholder="Contoh: BPJS, Transport, Laptop" />
                                </div>
                                <div class="col-md-3">
                                    <label for="Environment" class="form-label small fw-semibold text-dark">
                                        Lingkungan Kerja Disukai
                                    </label>
                                    <select class="form-select form-select-sm" id="Environment" name="Environment">
                                        @php $envVal = $datahasil2->Environment ?? 'OFFICE'; @endphp
                                        <option value="OFFICE" {{ $envVal === 'OFFICE' ? 'selected' : '' }}>OFFICE</option>
                                        <option value="FIELD" {{ $envVal === 'FIELD' ? 'selected' : '' }}>FIELD</option>
                                        <option value="FACTORY" {{ $envVal === 'FACTORY' ? 'selected' : '' }}>FACTORY</option>
                                        <option value="WORKSHOP" {{ $envVal === 'WORKSHOP' ? 'selected' : '' }}>WORKSHOP</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="Overtime" class="form-label small fw-semibold text-dark">
                                        Bersedia Kerja Lembur?
                                    </label>
                                    <select class="form-select form-select-sm" id="Overtime" name="Overtime">
                                        @php $otVal = strtoupper((string) ($datahasil2->Overtime ?? 'YES')); @endphp
                                        <option value="YES" {{ $otVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                        <option value="NO" {{ $otVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="Holiday" class="form-label small fw-semibold text-dark">
                                        Bersedia Kerja Hari Libur?
                                    </label>
                                    <select class="form-select form-select-sm" id="Holiday" name="Holiday">
                                        @php $holVal = strtoupper((string) ($datahasil2->Holiday ?? 'YES')); @endphp
                                        <option value="YES" {{ $holVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                        <option value="NO" {{ $holVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="Shift_Work" class="form-label small fw-semibold text-dark">
                                        Nyaman Bekerja Shift?
                                    </label>
                                    <select class="form-select form-select-sm" id="Shift_Work" name="Shift_Work">
                                        @php $shiftVal = strtoupper((string) ($datahasil2->Shift_Work ?? 'YES')); @endphp
                                        <option value="YES" {{ $shiftVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                        <option value="NO" {{ $shiftVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="Out_Town" class="form-label small fw-semibold text-dark">
                                        Bersedia Dinas Luar Kota?
                                    </label>
                                    <select class="form-select form-select-sm" id="Out_Town" name="Out_Town">
                                        @php $outVal = strtoupper((string) ($datahasil2->Out_Town ?? 'YES')); @endphp
                                        <option value="YES" {{ $outVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                        <option value="NO" {{ $outVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="Describe_Long" class="form-label small fw-semibold text-dark">
                                        Target Lama Bekerja
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="Describe_Long" name="Describe_Long" value="{{ $datahasil2->Describe_Long ?? '' }}" placeholder="Contoh: > 3 Tahun" />
                                </div>
                                <div class="col-md-4">
                                    <label for="Describe_Target" class="form-label small fw-semibold text-dark">
                                        Target Karir 5 Tahun
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="Describe_Target" name="Describe_Target" value="{{ $datahasil2->Describe_Target ?? '' }}" placeholder="Contoh: Leader / Supervisor" />
                                </div>
                            </div>
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

                        {{-- Catatan Wawancara User --}}
                        <div class="col-md-6">
                            <label for="User_Interview_Note" class="form-label small fw-semibold text-dark">
                                Catatan Ringkas Pewawancara User (User Interview Note)
                            </label>
                            <textarea class="form-control form-control-sm" id="User_Interview_Note" name="User_Interview_Note" rows="4" placeholder="Tuliskan catatan teknis atau observasi kesesuaian tim...">{{ $datahasil->User_Interview_Note ?? '' }}</textarea>
                            <div class="form-text fs-11">Catatan evaluasi teknis proses wawancara user.</div>
                        </div>

                        {{-- Hasil / Ulasan Lengkap Wawancara User --}}
                        <div class="col-md-6">
                            <label for="User_Interview_Result" class="form-label small fw-semibold text-dark">
                                Ulasan Hasil Akhir Wawancara User (User Interview Result)
                            </label>
                            <textarea class="form-control form-control-sm" id="User_Interview_Result" name="User_Interview_Result" rows="4" placeholder="Tuliskan kesimpulan evaluasi teknis, kesiapan kerja, dan rekomendasi posisi...">{{ $datahasil->User_Interview_Result ?? '' }}</textarea>
                            <div class="form-text fs-11">Rangkuman hasil akhir untuk penentuan tahap berikutnya.</div>
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
