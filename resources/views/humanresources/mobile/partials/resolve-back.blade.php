@php
    $fromSource = request()->query('from');
    $backTargets = [
        'dashboard' => route('mobile.dashboard'),
        'ess'       => route('mobile.ess'),
        'profile'   => route('employee.mobile.my-profile'),
    ];
    $resolvedBackUrl = $backTargets[$fromSource] ?? ($defaultBack ?? route('mobile.dashboard'));
    $fromParams = in_array($fromSource, ['dashboard', 'ess', 'profile'], true) ? ['from' => $fromSource] : [];
    \Illuminate\Support\Facades\View::share('resolvedBackUrl', $resolvedBackUrl);
    \Illuminate\Support\Facades\View::share('fromParams', $fromParams);
@endphp
