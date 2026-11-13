@include('cns.master.partials.company-form', ['company' => $consignee, 'isEdit' => true])
@include('cns.master.partials.company-form-script', [
    'submitUrl' => 'cns-master-consignee-detail-submit',
    'successMessage' => 'Detail Consignee Updated!',
])
