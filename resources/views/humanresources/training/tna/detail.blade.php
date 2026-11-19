<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Detail TNA: {{ $tna->tna_no }}</h5>
            <small class="text-muted">{{ $tna->department }} | Tahun {{ $tna->fiscal_year }} | Status: <span class="badge bg-info text-white">{{ $tna->status }}</span></small>
        </div>
        <div>
            @if(in_array($tna->status, ['DRAFT', 'REJECTED']))
                <button type="button" class="btn btn-warning btn-sm" onclick="openTnaForm({{ $tna->id }})">Edit</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitTnaApproval({{ $tna->id }})">Ajukan Approval</button>
            @elseif($tna->status === 'APPROVED')
                <a href="{{ url('hrd-training-index') }}?tna_id={{ $tna->id }}" class="btn btn-success btn-sm">Buat Jadwal Training dari TNA ini</a>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divTnaContainer').html('')">Tutup</button>
        </div>
    </div>
    <div class="card-body">
        <p><strong>Judul:</strong> {{ $tna->title }}</p>
        <p><strong>Latar Belakang:</strong> {{ $tna->background ?: '-' }}</p>

        <h6 class="mt-3">Daftar Kebutuhan Pelatihan Karyawan</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Karyawan</th>
                        <th>Gap Kompetensi</th>
                        <th>Rekomendasi Training</th>
                        <th>Program Terkait</th>
                        <th>Prioritas</th>
                        <th>Target</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tna->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $item->employee_name }} ({{ $item->employee_username }})</td>
                            <td>{{ $item->competency_gap }}</td>
                            <td>{{ $item->recommended_training }}</td>
                            <td>{{ $item->program->program_name ?? '-' }}</td>
                            <td><span class="badge bg-secondary text-white">{{ $item->priority }}</span></td>
                            <td>{{ $item->target_quarter ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function submitTnaApproval(id) {
        if (!confirm('Ajukan TNA ini ke Approval Inbox?')) return;
        $.post('{{ url("hrd-tna-submit-approval") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }, function(res) {
            alert(res.message);
            refreshTnaGrid();
            LoadM('divTnaContainer', 'hrd-tna-detail?id=' + id);
        }).fail(function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengajukan approval.');
        });
    }
</script>
