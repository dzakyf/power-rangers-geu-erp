@if(!$detail || empty($detail['item']))
<div class="alert alert-warning mb-0">Data detail tiket/pengajuan tidak ditemukan.</div>
@else
@php
    $cat = $detail['category'];
    $item = $detail['item'];
    $asset = $detail['asset'];
    $timeline = $detail['timeline'];
@endphp
<div class="card border-primary shadow-sm">
    <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 text-white">
                <i class="fa fa-clipboard-list me-2"></i>Detail {{ str_replace('_', ' ', $cat) }}:
                {{ $item->Ticket_No ?? $item->No_Trx ?? $item->Request_No ?? $item->PR_No ?? $item->Disposal_No ?? ('#' . $item->ID) }}
            </h5>
        </div>
        <button type="button" class="btn btn-light btn-sm" onclick="$('#AssetTicketingDetail').empty();">
            <i class="fa fa-times me-1"></i> Tutup
        </button>
    </div>

    @if($cat === 'DAMAGE_TICKET' && !empty($item->Asset_No))
    <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small fw-semibold text-secondary">
            <i class="fa fa-bolt text-warning me-1"></i> Eskalasi 1-Klik dari Tiket Kerusakan:
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold" onclick="escalateAssetTicket({{ $item->ID }}, 'FIXING');">
                <i class="fa fa-tools me-1"></i> Eskalasi ke Perbaikan (Asset Fixing)
            </button>
            <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="escalateAssetTicket({{ $item->ID }}, 'REPLACEMENT');">
                <i class="fa fa-cart-plus me-1"></i> Ajukan Pengadaan Pengganti (PR)
            </button>
        </div>
    </div>
    @endif

    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold text-primary mb-2"><i class="fa fa-info-circle me-1"></i> Informasi Dokumen</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" style="width:38%">No Dokumen</th><td class="fw-bold">{{ $item->Ticket_No ?? $item->No_Trx ?? $item->Request_No ?? $item->PR_No ?? $item->Disposal_No ?? '-' }}</td></tr>
                        <tr><th class="text-muted">Status</th><td><span class="badge bg-info text-white">{{ $item->Ticket_Status ?? $item->Status ?? '-' }}</span></td></tr>
                        <tr><th class="text-muted">Keterangan / Alasan</th><td>{{ $item->Ticket_Subject ?? $item->Jenis_Perbaikan ?? $item->Moving_Reason ?? $item->Disposal_Reason ?? $item->Remark ?? '-' }}</td></tr>
                        @if(!empty($item->Description) || !empty($item->Alasan_Perkiraan))
                        <tr><th class="text-muted">Detail</th><td>{{ $item->Description ?? $item->Alasan_Perkiraan }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold text-primary mb-2"><i class="fa fa-box me-1"></i> Informasi Aset Terkait</h6>
                    @if($asset)
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" style="width:38%">Asset No</th><td class="fw-bold">{{ $asset->Asset_No }}</td></tr>
                        <tr><th class="text-muted">Nama Aset</th><td>{{ $asset->Product_Description ?: '-' }}</td></tr>
                        <tr><th class="text-muted">Lokasi / Pemegang</th><td>{{ $asset->Asset_Location ?: '-' }} / {{ $asset->User_Responsible ?: '-' }}</td></tr>
                        <tr>
                            <th class="text-muted">Status / Kondisi</th>
                            <td>
                                <span class="badge bg-secondary text-white">{{ $asset->Status ?: '-' }}</span>
                                <span class="badge bg-warning text-dark">{{ $asset->Kondisi_Aset ?: 'BAIK' }}</span>
                            </td>
                        </tr>
                    </table>
                    @else
                    <div class="text-muted small">Tidak terhubung ke nomor aset spesifik (Pengadaan Baru).</div>
                    @endif
                </div>
            </div>

            @if($timeline)
            <div class="col-12">
                <div class="border rounded p-3">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa fa-project-diagram me-1"></i> Visual Lifecycle Approval ({{ $timeline['request']->Request_Type }})</h6>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        @foreach($timeline['tasks'] as $t)
                            @php
                                $tStat = strtoupper((string) $t->Status);
                                $bClass = $tStat === 'APPROVED' ? 'bg-success text-white' : ($tStat === 'REJECTED' ? 'bg-danger text-white' : (in_array($tStat, ['PENDING', 'CLAIMED'], true) ? 'bg-warning text-dark' : 'bg-light text-dark border'));
                            @endphp
                            <span class="badge {{ $bClass }} px-3 py-2">
                                Stage {{ $t->Stage_Order }}: {{ $t->Position_Name ?: ($t->Position_Code ?: 'Approver') }} ({{ $tStat }})
                            </span>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                                <tr><th>Stage</th><th>Aksi</th><th>Oleh</th><th>Catatan</th><th>Waktu</th></tr>
                            </thead>
                            <tbody>
                                @forelse($timeline['logs'] as $l)
                                <tr>
                                    <td>Stage {{ $l->Stage_Order ?: '-' }}</td>
                                    <td><span class="badge bg-info text-white">{{ $l->Action }}</span></td>
                                    <td>{{ $l->Actor_Name ?: ($l->Actor_Username ?: 'System') }}</td>
                                    <td>{{ $l->Notes ?: '-' }}</td>
                                    <td>{{ $l->Action_Date ?: '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-2">Menunggu proses approval pertama.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function escalateAssetTicket(ticketId, targetType) {
    var promptMsg = targetType === 'FIXING'
        ? 'Masukkan estimasi biaya perbaikan (Rp, isi 0 jika internal/tanpa biaya):'
        : 'Masukkan alasan / catatan pengadaan pengganti:';
    var inputVal = prompt(promptMsg, targetType === 'FIXING' ? '0' : '');
    if (inputVal === null) return;

    var payload = { ticket_id: ticketId, target_type: targetType };
    if (targetType === 'FIXING') payload.Estimasi_Biaya = parseFloat(inputVal) || 0;
    else payload.Remark = inputVal;

    $.ajax({
        url: 'hrd-asset-ticketing-escalate',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: payload,
        success: function(res) {
            alert(res.message || 'Eskalasi berhasil dibuat.');
            if (typeof loadAssetTicketingData === 'function') loadAssetTicketingData();
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal melakukan eskalasi.');
        }
    });
}
</script>
@endif
