@if (in_array(auth()->user()->Position ?? '', ['Administrator', 'BOD', 'SUPERADMIN'], true))
    <a class="dropdown-item" href="#" onclick="forceApproveRequest({{ $idVar }})">Force Approve</a>
@endif
