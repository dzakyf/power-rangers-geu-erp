<!-- Tab 5: Documents & History -->
<div class="row g-2">
    <!-- Dokumen Kepegawaian -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-folder-closed text-primary"></i> Berkas & Dokumen Terlampir
            </div>
            @if(count($documents) > 0)
                <div class="list-group list-group-flush">
                    @foreach($documents as $doc)
                        <div class="list-group-item px-0 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 13px;">{{ $doc->Document_Name ?? $doc->Name ?? 'Dokumen' }}</div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    Tipe: {{ $doc->Document_Type ?? 'File' }}
                                </div>
                            </div>
                            @if(!empty($doc->Document_Path) || !empty($doc->File_Path))
                                @php $filePath = $doc->Document_Path ?? $doc->File_Path; @endphp
                                <a href="{{ asset($filePath) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11px;">
                                    <i class="fa fa-eye me-1"></i> Lihat
                                </a>
                            @else
                                <span class="badge bg-light text-muted border">No File</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted small">Belum ada dokumen yang diunggah.</div>
            @endif
        </div>
    </div>

    <!-- Riwayat Kepegawaian (Mutasi/Promosi/SP) -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-clock-rotate-left text-primary"></i> Riwayat Mutasi / Karir
            </div>
            @if(count($history) > 0)
                <div class="timeline-wrapper">
                    @foreach($history as $h)
                        <div class="border-start border-warning border-3 ps-3 pb-3 mb-2">
                            <div class="fw-bold text-dark" style="font-size: 13px;">{{ $h->Action_Type ?? 'Perubahan Status' }}</div>
                            <div class="text-muted small" style="font-size: 11px;">
                                {{ !empty($h->Effective_Date) ? date('d/m/Y', strtotime($h->Effective_Date)) : '-' }}
                            </div>
                            <div class="small text-secondary mt-1" style="font-size: 12px;">
                                {{ $h->Notes ?? $h->Description ?? '-' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted small">Belum ada riwayat mutasi / perubahan karir.</div>
            @endif
        </div>
    </div>

    <!-- KHUSUS HCGA: Recruitment Archive -->
    @if($is_hcga)
    <div class="col-12">
        <div class="info-card border-warning" style="background: #fffbeb;">
            <div class="card-section-title text-warning">
                <i class="fa fa-user-tie"></i> Arsip Rekrutmen (Khusus Tim HCGA)
            </div>
            @if($recruitment)
                <div class="row g-2 small">
                    <div class="col-6">
                        <div class="text-muted" style="font-size: 11px;">Nomor Rekrutmen:</div>
                        <div class="fw-bold text-dark">{{ $recruitment->Task_ID ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted" style="font-size: 11px;">Hasil Seleksi:</div>
                        <span class="badge bg-success">{{ $recruitment->Result ?? 'ACCEPTED' }}</span>
                    </div>
                    <div class="col-12 border-top pt-2">
                        <div class="text-muted" style="font-size: 11px;">Catatan Interview / HR:</div>
                        <div class="text-dark">{{ $recruitment->Interview_Note ?? 'Tidak ada catatan khusus.' }}</div>
                    </div>
                </div>
            @else
                <div class="text-muted small">Data berkas rekrutmen awal tidak ditemukan di sistem.</div>
            @endif
        </div>
    </div>
    @endif
</div>

