<!-- Tab 3: Education & Experience -->
<div class="row g-2">
    <!-- Riwayat Pendidikan Formal -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-graduation-cap text-primary"></i> Riwayat Pendidikan Formal
            </div>
            @if(count($education) > 0)
                <div class="timeline-wrapper">
                    @foreach($education as $edu)
                        <div class="border-start border-primary border-3 ps-3 pb-3 mb-2 position-relative">
                            <div class="fw-bold text-dark" style="font-size: 14px;">{{ $edu->Education_Name ?? '-' }}</div>
                            <div class="text-primary fw-semibold" style="font-size: 12px;">{{ $edu->Education_Major ?? '-' }}</div>
                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                <i class="fa fa-calendar me-1"></i> Periode: {{ $edu->Education_Year_Start ?? '' }} - {{ $edu->Education_Year_End ?? 'Selesai' }}
                                @if(!empty($edu->GPA))
                                    | IPK/Nilai: <strong>{{ $edu->GPA }}</strong>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted small">Belum ada riwayat pendidikan.</div>
            @endif
        </div>
    </div>

    <!-- Pengalaman Kerja Sebelumnya -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-briefcase text-primary"></i> Pengalaman Kerja Sebelumnya
            </div>
            @if(count($experience) > 0)
                <div>
                    @foreach($experience as $exp)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $exp->Job_Title ?? '-' }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $exp->Company_Name ?? '-' }}</div>
                                </div>
                                <span class="badge bg-light text-secondary border" style="font-size: 10px;">
                                    {{ $exp->Year_Start ?? '' }} - {{ $exp->Year_End ?? '' }}
                                </span>
                            </div>
                            @if(!empty($exp->Job_Description))
                                <div class="text-secondary small mt-1" style="font-size: 11px;">
                                    {{ $exp->Job_Description }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted small">Belum ada data pengalaman kerja.</div>
            @endif
        </div>
    </div>

    <!-- Keahlian & Keterampilan Lainnya -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-award text-warning"></i> Keahlian & Sertifikasi
            </div>
            @if(count($skills) > 0)
                <div class="d-flex flex-wrap gap-1">
                    @foreach($skills as $sk)
                        <span class="badge bg-light text-dark border p-2" style="font-size: 12px;">
                            <i class="fa fa-check-circle text-success me-1"></i> {{ $sk->Skill_Name ?? $sk->Name ?? '-' }}
                            @if(!empty($sk->Level))
                                <span class="text-muted ms-1">({{ $sk->Level }})</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted small">Belum ada keahlian khusus terdaftar.</div>
            @endif
        </div>
    </div>
</div>

