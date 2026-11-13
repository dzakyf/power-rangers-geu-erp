@include('cns.master.partials.company-form', ['company' => null, 'isEdit' => false, 'defaultIsConsignee' => true])
@include('cns.master.partials.company-form-script', [
    'submitUrl' => 'cns-master-consignee-new-submit',
    'successMessage' => 'New Consignee Created!',
])
