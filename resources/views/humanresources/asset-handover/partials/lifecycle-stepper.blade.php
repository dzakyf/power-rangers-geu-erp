@php
    $ahStatus = strtoupper((string) ($ah->Status ?? 'CREATED'));
    $hasReturnPending = !empty($ah->Return_Approval_Request_ID);
    $hasPartialReturned = false;
    try {
        if (!$hasReturnPending) {
            $hasReturnPending = \Illuminate\Support\Facades\DB::table('asset_handover_dtl')
                ->where('Handover_No', $ah->Handover_No)
                ->where('Status', 'RETURN_PENDING')
                ->exists();
        }
        $hasPartialReturned = \Illuminate\Support\Facades\DB::table('asset_handover_dtl')
            ->where('Handover_No', $ah->Handover_No)
            ->where('Status', 'RETURNED')
            ->exists();
    } catch (\Throwable $e) {
        // Safe fallback when rendered without active DB connection
    }

    $activeStep = 1;
    if ($ahStatus === 'WAITING_APPROVAL') {
        $activeStep = 2;
    } elseif ($ahStatus === 'APPROVED' && $hasReturnPending) {
        $activeStep = 4;
    } elseif ($ahStatus === 'APPROVED') {
        $activeStep = 3;
    } elseif ($ahStatus === 'RETURNED') {
        $activeStep = 5;
    }

    $isHalted = in_array($ahStatus, ['REJECTED', 'CANCELLED'], true);

    $steps = [
        1 => ['title' => 'Created', 'desc' => 'Draft & Item'],
        2 => ['title' => 'Handover Approval', 'desc' => 'Lead → PIC'],
        3 => ['title' => 'Active at Employee', 'desc' => $hasPartialReturned ? 'Sebagian Kembali' : 'Dipegang PIC'],
        4 => ['title' => 'Return Approval', 'desc' => 'PIC → HR/GA Pool'],
        5 => ['title' => 'Returned to HR', 'desc' => 'Kembali ke Pool'],
    ];
@endphp

<div class="border rounded bg-light p-2 mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1">
        <span class="small fw-bold text-dark">
            <i class="fa fa-route text-primary me-1"></i> Siklus Hidup Aset (Full-Cycle Status)
        </span>
        @if($isHalted)
            <span class="badge bg-danger text-white">{{ $ahStatus }}</span>
        @elseif($ahStatus === 'RETURNED')
            <span class="badge bg-primary text-white">RETURNED (Selesai di HR/GA Pool)</span>
        @elseif($hasReturnPending)
            <span class="badge" style="background-color:#ffe69c;color:#664d03;">RETURN IN APPROVAL</span>
        @elseif($ahStatus === 'APPROVED')
            <span class="badge bg-success text-white">ACTIVE AT EMPLOYEE{{ $hasPartialReturned ? ' (PARTIAL RETURN)' : '' }}</span>
        @elseif($ahStatus === 'WAITING_APPROVAL')
            <span class="badge" style="background-color:#ffe69c;color:#664d03;">WAITING APPROVAL</span>
        @else
            <span class="badge bg-secondary text-white">{{ $ahStatus }}</span>
        @endif
    </div>
    <div class="row g-1 text-center">
        @foreach($steps as $num => $st)
            @php
                $done = !$isHalted && ($num < $activeStep || ($ahStatus === 'RETURNED' && $num === 5));
                $current = !$isHalted && $num === $activeStep && !($ahStatus === 'RETURNED' && $num === 5);
                $boxStyle = $done
                    ? 'background-color:#d1e7dd;color:#0f5132;border-color:#badbcc;'
                    : ($current
                        ? 'background-color:#cfe2ff;color:#084298;border-color:#9ec5fe;'
                        : 'background-color:#ffffff;color:#6c757d;border-color:#dee2e6;');
            @endphp
            <div class="col-6 col-md">
                <div class="border rounded py-1 px-2 h-100" style="{{ $boxStyle }}">
                    <div class="small fw-bold" style="font-size: 0.76rem;">
                        @if($done)<i class="fa fa-check-circle me-1"></i>@elseif($current)<i class="fa fa-dot-circle me-1"></i>@else{{ $num }}. @endif
                        {{ $st['title'] }}
                    </div>
                    <div style="font-size: 0.68rem; opacity: 0.9;">{{ $st['desc'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
