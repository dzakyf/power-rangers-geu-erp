<!-- Tab 2: Family & Emergency Contact -->
<div class="row g-2">
    <!-- Kontak Darurat (Emergency Contact) -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-phone-volume text-danger"></i> Kontak Darurat (Emergency Contact)
            </div>
            @if(count($emergency) > 0)
                <div class="row g-2">
                    @foreach($emergency as $em)
                        <div class="col-12 border-bottom pb-2 mb-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $em->Contact_Name ?? '-' }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">
                                        Hubungan: <span class="badge bg-light text-dark border">{{ $em->Relation ?? '-' }}</span>
                                    </div>
                                </div>
                                @if(!empty($em->Contact_No))
                                    <a href="tel:{{ $em->Contact_No }}" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" style="font-size: 11px;">
                                        <i class="fa fa-phone me-1"></i> {{ $em->Contact_No }}
                                    </a>
                                @endif
                            </div>
                            @if(!empty($em->Address))
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    <i class="fa fa-map-pin me-1"></i> {{ $em->Address }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted small">
                    <i class="fa fa-circle-exclamation d-block mb-1 text-warning" style="font-size: 20px;"></i>
                    Belum ada data kontak darurat yang terdaftar.
                </div>
            @endif
        </div>
    </div>

    <!-- Susunan Keluarga -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-people-roof text-primary"></i> Data Anggota Keluarga
            </div>
            @if(count($family) > 0)
                <div class="row g-2">
                    @foreach($family as $f)
                        <div class="col-12 border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark" style="font-size: 14px;">{{ $f->Name ?? '-' }}</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill" style="font-size: 11px;">
                                    {{ $f->Relation ?? 'Keluarga' }}
                                </span>
                            </div>
                            <div class="row g-1 text-muted" style="font-size: 12px;">
                                <div class="col-6">
                                    <i class="fa fa-venus-mars me-1"></i> {{ $f->Gender == 'M' || $f->Gender == 'L' ? 'Laki-Laki' : 'Perempuan' }}
                                </div>
                                <div class="col-6">
                                    <i class="fa fa-calendar-day me-1"></i> {{ !empty($f->Birth_Date) && $f->Birth_Date != '0000-00-00' ? date('d/m/Y', strtotime($f->Birth_Date)) : '-' }}
                                </div>
                                @if(!empty($f->Education))
                                <div class="col-6">
                                    <i class="fa fa-graduation-cap me-1"></i> Pend: {{ $f->Education }}
                                </div>
                                @endif
                                @if(!empty($f->Job))
                                <div class="col-6">
                                    <i class="fa fa-briefcase me-1"></i> Pekerjaan: {{ $f->Job }}
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted small">
                    Belum ada data keluarga yang tercatat.
                </div>
            @endif
        </div>
    </div>
</div>

