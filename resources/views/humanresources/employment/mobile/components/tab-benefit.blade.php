<!-- Tab 4: Insurance & Leave -->
<div class="row g-2">
    <!-- Hak Cuti & Saldo Cuti -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-umbrella-beach text-success"></i> Saldo Cuti Karyawan
            </div>
            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-2">
                <div>
                    <div class="text-muted small">Sisa Cuti Tahunan</div>
                    <h3 class="fw-bold text-success mb-0">{{ $emp->Sisa_Cuti ?? 0 }} <span style="font-size: 14px; font-weight: normal;">Hari</span></h3>
                </div>
                <div class="text-end">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 p-2">
                        <i class="fa fa-calendar-check me-1"></i> Aktif
                    </span>
                </div>
            </div>
            @if(count($leave) > 0)
                <div class="small fw-bold text-muted mb-2">Riwayat Pengajuan Cuti:</div>
                <div class="list-group list-group-flush">
                    @foreach($leave as $l)
                        <div class="list-group-item px-0 py-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold" style="font-size: 13px;">{{ $l->Leave_Type ?? 'Cuti' }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $l->Total_Days ?? 1 }} Hari</span>
                            </div>
                            <div class="text-muted small" style="font-size: 11px;">
                                {{ !empty($l->Start_Date) ? date('d/m/Y', strtotime($l->Start_Date)) : '' }} s/d {{ !empty($l->End_Date) ? date('d/m/Y', strtotime($l->End_Date)) : '' }}
                                @if(!empty($l->Reason)) - <em>{{ $l->Reason }}</em> @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Asuransi & BPJS -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-shield-halved text-primary"></i> BPJS & Asuransi Kesehatan
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="info-label">BPJS Ketenagakerjaan</div>
                    <div class="info-value">{{ $emp->bpjs_tk ?? '-' }}</div>
                </div>
                <div class="col-6">
                    <div class="info-label">BPJS Kesehatan</div>
                    <div class="info-value">{{ $emp->bpjs_kes ?? '-' }}</div>
                </div>
            </div>

            @if(count($insurances) > 0)
                <div class="border-top pt-2 mt-3">
                    <div class="info-label mb-2">Asuransi Tambahan / Swasta</div>
                    @foreach($insurances as $ins)
                        <div class="p-2 bg-light rounded-2 mb-2">
                            <div class="fw-bold text-dark" style="font-size: 13px;">{{ $ins->Insurance_Name ?? '-' }}</div>
                            <div class="small text-muted" style="font-size: 11px;">
                                No. Polis: {{ $ins->Policy_No ?? '-' }} | Kelas: {{ $ins->Class ?? '-' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

