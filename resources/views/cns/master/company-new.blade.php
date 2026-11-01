@include('cns.master.partials.company-form', ['company' => null, 'isEdit' => false])
@include('cns.master.partials.company-form-script', [
    'submitUrl' => 'cns-master-company-new-submit',
    'successMessage' => 'New Company Created!',
])
