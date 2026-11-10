@include('cns.vs.partials.vessel-schedule-form', [
    'vs' => null,
    'contracts' => $contracts,
    'vendors' => $vendors,
    'bookings' => $bookings,
    'q88' => null,
    'isEdit' => false,
])

@include('cns.vs.partials.vessel-schedule-form-script', [
    'submitUrl' => 'cns-vessel-schedule-new-submit',
    'isEdit' => false,
    'vs' => null,
])
