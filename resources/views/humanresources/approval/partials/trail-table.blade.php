<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3" style="width:90px;">Stage</th>
                <th>Jabatan / Posisi</th>
                <th style="width:185px;">Status</th>
                <th>Eksekutor / Kandidat</th>
                <th style="width:160px;">Waktu</th>
                <th>Keterangan / Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($trail as $ap)
                @php
                    $apSt = strtoupper((string) ($ap->status ?? ''));
                    $apBadge = [
                        'APPROVED'       => 'bg-success text-white',
                        'FORCE_APPROVED' => 'bg-dark text-white',
                        'SKIPPED_FORCE'  => 'text-dark',
                        'SKIPPED'        => 'bg-info text-white',
                        'AUTO_APPROVED'  => 'bg-info text-white',
                        'PENDING'        => 'text-dark',
                        'CLAIMED'        => 'text-dark',
                        'REJECTED'       => 'bg-danger text-white',
                        'HELD'           => 'bg-danger text-white',
                        'UPCOMING'       => 'bg-light text-dark border',
                    ][$apSt] ?? 'bg-secondary text-white';
                    $apBadgeStyle = in_array($apSt, ['SKIPPED_FORCE', 'PENDING', 'CLAIMED'], true)
                        ? 'background-color:#ffe69c;color:#664d03 !important;'
                        : '';
                @endphp
                <tr>
                    <td class="ps-3 fw-semibold">Stage {{ $ap->stage_order }}</td>
                    <td>
                        {{ $ap->label }}
                        @if (!empty($ap->position_code))
                            <code class="ms-1">{{ $ap->position_code }}</code>
                        @endif
                    </td>
                    <td><span class="badge {{ $apBadge }}" @if($apBadgeStyle !== '') style="{{ $apBadgeStyle }}" @endif>{{ $ap->status_label ?? $apSt }}</span></td>
                    <td>{{ $ap->actor_name ?: '-' }}</td>
                    <td class="small text-muted">{{ $ap->acted_at ?: '-' }}</td>
                    <td class="small">{{ $ap->notes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
