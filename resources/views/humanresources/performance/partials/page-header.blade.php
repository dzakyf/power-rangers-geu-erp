<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            {{ $title ?? 'Performance Management' }}
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('hrd-performance-review-index') }}" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="{{ $parentUrl ?? url('hrd-performance-kpi-index') }}" style="color: rgba(255, 255, 255, 0.8) !important;">{{ $parentLabel ?? 'Performance' }}</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">{{ $current ?? '' }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa {{ $icon ?? 'fa-chart-line' }} me-1"></i> {{ $badgeText ?? 'Performance & Appraisal' }}
        </span>
    </div>
</div>
<script>
    if (typeof window.Swal === 'undefined') {
        window.Swal = {
            fire: function(titleOrOpts, text, icon) {
                var title = typeof titleOrOpts === 'object' ? (titleOrOpts.title || '') : (titleOrOpts || '');
                var body = typeof titleOrOpts === 'object' ? (titleOrOpts.text || '') : (text || '');
                var msg = body ? (title + '\n' + body) : title;
                if (typeof titleOrOpts === 'object' && titleOrOpts.showCancelButton) {
                    var ok = window.confirm(msg);
                    return Promise.resolve({ isConfirmed: ok, value: ok });
                }
                window.alert(msg);
                return Promise.resolve({ isConfirmed: true, value: true });
            }
        };
    }
</script>
