<div class="card my-1">
    <div>

        <h5>Step 1</h5>
        <p>Cek data diri & dokumen...</p>

        <div class="card-body">
            @if (optional($partner)->register_as == "individual")
            @include('approval.stepper.detail-individual')
            @elseif (optional($partner)->register_as == "company")
            @include('approval.stepper.detail-company')
            @endif

            <p class="font-weight-bold"><strong>Status Approval: {{ optional($status)->Action }}</strong></p>

        </div>

        <div class="row">

        </div>

        <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action
        </button>
        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
            <a class="dropdown-item" href="#" onclick="submitApproval('APPROVED')">Approve</a>
            <a class="dropdown-item" href="#" onclick="submitApproval('REJECTED')">Reject</a>
        </div>



        @if(optional($status)->Action == "APPROVED")
        <button class="btn btn-primary btn-next">Next</button>
        @endif

    </div>
</div>