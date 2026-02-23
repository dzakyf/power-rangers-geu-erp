<div class="card my-1">
    <div class="card-body">
        @if ($partner->register_as == "individual")
            @include('approval.stepper.detail-individual')       
        @elseif ($partner->register_as == "company")
            @include('approval.stepper.detail-company')
        @endif
        
        <p class="font-weight-bold"><strong>Status Approval: {{ optional($status)->Action }}</strong></p>

    </div>
</div>