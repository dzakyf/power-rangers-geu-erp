@include('cns.master.partials.company-form', ['company' => $company, 'isEdit' => true])
@include('cns.master.partials.company-form-script', [
    'submitUrl' => 'cns-master-company-detail-submit',
    'successMessage' => 'Detail Company Updated!',
])
