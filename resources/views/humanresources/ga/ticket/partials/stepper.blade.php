@if (!empty($steps))
<style>
    .ticket-stepper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: stretch;
        margin-bottom: 1rem;
    }
    .ticket-step {
        flex: 1 1 130px;
        position: relative;
        padding: 0.6rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
        color: #6c757d;
        font-size: 0.82rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ticket-step-num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        flex-shrink: 0;
        background: #dee2e6;
        color: #495057;
    }
    .ticket-step.is-done {
        background-color: #ecfdf5;
        border-color: #198754;
        color: #146c43;
    }
    .ticket-step.is-done .ticket-step-num {
        background: #198754;
        color: #fff;
    }
    .ticket-step.is-active {
        background-color: #eef4ff;
        border-color: #0d6efd;
        color: #0a58ca;
        font-weight: 600;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.12);
    }
    .ticket-step.is-active .ticket-step-num {
        background: #0d6efd;
        color: #fff;
    }
    .ticket-step.is-warning {
        background-color: #fff8e1;
        border-color: #ffc107;
        color: #856404;
        font-weight: 600;
    }
    .ticket-step.is-warning .ticket-step-num {
        background: #ffc107;
        color: #212529;
    }
    .ticket-step.is-rejected {
        background-color: #fef2f2;
        border-color: #dc3545;
        color: #b02a37;
        font-weight: 600;
    }
    .ticket-step.is-rejected .ticket-step-num {
        background: #dc3545;
        color: #fff;
    }
</style>
<div class="ticket-stepper" aria-label="Progress Tracking">
    @foreach ($steps as $idx => $step)
        @php
            $st = $step['state'] ?? 'pending';
            $cls = $st === 'done' ? 'is-done' : ($st === 'active' ? 'is-active' : ($st === 'warning' ? 'is-warning' : ($st === 'rejected' ? 'is-rejected' : '')));
        @endphp
        <div class="ticket-step {{ $cls }}">
            <span class="ticket-step-num">
                @if ($st === 'done')
                    <i class="fa fa-check"></i>
                @elseif ($st === 'rejected')
                    <i class="fa fa-times"></i>
                @else
                    {{ $idx + 1 }}
                @endif
            </span>
            <div class="lh-sm">{{ $step['label'] }}</div>
        </div>
    @endforeach
</div>
@endif
