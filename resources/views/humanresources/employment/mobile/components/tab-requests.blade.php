<!-- Tab 6: Requests Update & Status Approval -->
<div class="row g-2">
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-clock-rotate-left text-primary"></i> Daftar Pengajuan Perubahan Data Mandiri
            </div>
            @if(count($requests) > 0)
                <div class="row g-2">
                    @foreach($requests as $req)
                        <div class="col-12 border rounded-3 p-3 mb-2 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark" style="font-size: 13px;">{{ $req->request_no }}</span>
                                @if($req->status === 'APPROVED')
                                    <span class="badge bg-success rounded-pill px-2 py-1"><i class="fa fa-check me-1"></i> Disetujui</span>
                                @elseif($req->status === 'REJECTED')
                                    <span class="badge bg-danger rounded-pill px-2 py-1"><i class="fa fa-times me-1"></i> Ditolak</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="fa fa-clock me-1"></i> Menunggu Verifikasi</span>
                                @endif
                            </div>

                            <div class="small text-muted mb-1" style="font-size: 11px;">
                                <i class="fa fa-calendar me-1"></i> {{ date('d M Y H:i', strtotime($req->created_at)) }}
                            </div>

                            <div class="p-2 bg-white rounded border my-2">
                                <div class="text-muted small" style="font-size: 11px;">Kolom: <strong>{{ $req->field_name }}</strong> ({{ $req->field_category }})</div>
                                <div class="d-flex justify-content-between text-dark small mt-1">
                                    <div><span class="text-danger"><del>{{ $req->old_value ?: '(Kosong)' }}</del></span></div>
                                    <div class="text-muted"><i class="fa fa-arrow-right"></i></div>
                                    <div><span class="text-success fw-bold">{{ $req->new_value }}</span></div>
                                </div>
                                @if(!empty($req->reason))
                                    <div class="text-secondary small mt-1" style="font-size: 11px;">
                                        <em>Alasan: {{ $req->reason }}</em>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($req->attachment))
                                <div class="mb-2">
                                    <a href="{{ asset($req->attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 11px;">
                                        <i class="fa fa-paperclip me-1"></i> Lampiran Bukti
                                    </a>
                                </div>
                            @endif

                            @if($isHcga && $req->status === 'WAITING_APPROVAL')
                                <div class="d-flex gap-2 justify-content-end border-top pt-2 mt-2">
                                    <button class="btn btn-sm btn-outline-danger px-3 btn-action-approval" data-id="{{ $req->id }}" data-action="reject">
                                        <i class="fa fa-times me-1"></i> Tolak
                                    </button>
                                    <button class="btn btn-sm btn-success px-3 btn-action-approval" data-id="{{ $req->id }}" data-action="approve">
                                        <i class="fa fa-check me-1"></i> Setujui & Update
                                    </button>
                                </div>
                            @endif

                            @if(!empty($req->approval_note))
                                <div class="small text-muted mt-1" style="font-size: 11px;">
                                    Catatan Approval: {{ $req->approval_note }} (Oleh: {{ $req->approved_by }})
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    <i class="fa fa-check-circle text-success d-block mb-1" style="font-size: 24px;"></i>
                    Belum ada pengajuan perubahan data diri yang diajukan.
                </div>
            @endif
        </div>
    </div>
</div>

@if($isHcga)
<script>
    $('.btn-action-approval').on('click', function() {
        const id = $(this).data('id');
        const action = $(this).data('action');
        const actionLabel = action === 'approve' ? 'menyetujui' : 'menolak';

        if (!confirm(`Apakah Anda yakin ingin ${actionLabel} permohonan ini?`)) {
            return;
        }

        const note = prompt('Catatan / Keterangan approval (opsional):', '');

        $.ajax({
            url: "{{ url('employee/mobile/request-approval') }}/" + id,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                action: action,
                note: note
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    loadTab('requests');
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat memproses approval.');
            }
        });
    });
</script>
@endif

