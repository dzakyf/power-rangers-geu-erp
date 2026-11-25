<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2 flex-wrap gap-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            {{ $title ?? 'GA Ticketing Management' }}
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                @php $crumbs = $breadcrumbs ?? []; @endphp
                @foreach ($crumbs as $index => $crumb)
                    @php $isLast = ($index === count($crumbs) - 1); @endphp
                    @if ($isLast)
                        <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">
                            {{ $crumb['label'] }}
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $crumb['url'] ?? '#' }}" style="color: rgba(255, 255, 255, 0.8) !important;">{{ $crumb['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if (!empty($badgeLabel))
            <span class="badge d-none d-md-inline-flex align-items-center px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa {{ $badgeIcon ?? 'fa-ticket-alt' }} me-1"></i> {{ $badgeLabel }}
            </span>
        @endif
        <button type="button"
                class="btn btn-sm shadow-sm fw-semibold"
                style="background: rgba(255, 255, 255, 0.95); color: #1e293b; border: 1px solid rgba(255, 255, 255, 0.6);"
                onclick="openGaTicketingGuide('{{ $defaultGuideTab ?? '' }}')">
            <i class="fa fa-book-open text-primary me-1"></i> {{ $guideButtonLabel ?? 'Panduan Penggunaan' }}
        </button>
    </div>
</div>

@include('humanresources.ga.ticket.partials.guide-modal')
