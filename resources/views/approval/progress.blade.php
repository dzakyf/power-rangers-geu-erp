@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Partner</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Approval</span>
		</li>
	</ol>
</div>

@php
$stageLabels = [
    'DOCUMENT_CHECK' => 'Cek Dokumen',
    'MOU' => 'Penandatanganan MOU',
    'INVOICE' => 'Penerbitan Invoice DP',
    'PKS' => 'Perjanjian Kerjasama',
    'SURVEY' => 'Survey Lokasi',
    'SETUP' => 'Installment',
    'ACTIVE' => 'Aktif',
];
@endphp

<div class="row">
    <div class="col-md-9">
        <div class="mt-5 mb-5 p-4 bg-white shadow-sm">
            <div>
                <button class="btn btn-warning" type="button" onclick="window.history.back();">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </div>
            <div class="bs-stepper" id="customStepper">
                <div class="bs-stepper-header" role="tablist">
                    @foreach ($stageOrder as $i => $s)
                    @php
                        $stepNum = $i + 1;
                        $label = $stageLabels[$s] ?? $s;
                        $completed = $i < $currentStageIndex || $partner->onboarding_stage == 'ACTIVE';
                        $active = $i == $currentStageIndex;
                        $disabled = $i > $currentStageIndex;
                    @endphp
                    <div class="step @if($completed) completed @endif" data-target="#step{{ $stepNum }}">
                        <button type="button" class="step-trigger" role="tab" @if($disabled) disabled @endif>
                            <span class="bs-stepper-circle">@if($completed) &#10003; @else {{ $stepNum }} @endif</span>
                            <span class="bs-stepper-label">{{ $label }}</span>
                        </button>
                    </div>
                    @if (!$loop->last)
                    <div class="line"></div>
                    @endif
                    @endforeach
                </div>

                <div class="bs-stepper-content">
                    <div id="step1" class="content" role="tabpanel">
                        @include('approval.stepper.detail')
                        @if ($currentStage === 'DOCUMENT_CHECK')
                        <div class="mt-3">
                            <button class="btn btn-success" id="approveDocumentsBtn">Setujui Dokumen</button>
                        </div>
                        @endif
                    </div>
                    <div id="step2" class="content" role="tabpanel">
                        @include('approval.stepper.mou')
                    </div>
                    <div id="step3" class="content" role="tabpanel">
                        @include('approval.stepper.invoice-dp')
                        @if ($currentStage === 'INVOICE')
                        <button class="btn btn-primary" id="invoiceNextBtn">Next</button>
                        @endif
                    </div>
                    <div id="step4" class="content" role="tabpanel">
                        @include('approval.stepper.pks')
                    </div>
                    <div id="step5" class="content" role="tabpanel">
                        @include('approval.stepper.survey')
                    </div>
                    <div id="step6" class="content" role="tabpanel">
                        @include('approval.stepper.setup')
                    </div>
                    <div id="step7" class="content" role="tabpanel">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="mt-5 mb-5 p-4 bg-white shadow-sm">
            <h6 class="mb-3">Riwayat Progress</h6>
            @if ($onboardingLog->count() > 0)
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Tahap</th>
                        <th>Status</th>
                        <th>Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($onboardingLog as $log)
                    <tr>
                        <td>{{ $log->Stage_Label }}</td>
                        <td>{{ ucfirst($log->Status) }}</td>
                        <td>{{ $log->Completed_At ? \Carbon\Carbon::parse($log->Completed_At)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-muted">Belum ada progress.</p>
            @endif
        </div>
    </div>
</div>

<div id="divlistOpportunity"></div>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
@stack('mou-script')
@stack('pks-script')

<style>
.bs-stepper .step.completed .bs-stepper-circle {
    background-color: #28a745;
    color: #fff;
}
.bs-stepper .step.completed .bs-stepper-label {
    color: #28a745;
    font-weight: 600;
}
.bs-stepper .step button[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
var oppId = new URLSearchParams(window.location.search).get('id');
var currentStage = '{{ $currentStage }}';
var currentStageIndex = {{ $currentStageIndex }};
var stageOrder = {!! json_encode($stageOrder) !!};

function updateOnboardingLog(stage, status, notes, callback) {
    $.ajax({
        url: 'partner-onboarding-log/update',
        type: 'POST',
        data: {
            Opportunity_Id: oppId,
            Stage: stage,
            Status: status,
            Notes: notes || '',
            _token: '{{ csrf_token() }}'
        },
        success: function(res) {
            if (callback) callback(true);
        },
        error: function() {
            alert('Gagal memperbarui progress.');
            if (callback) callback(false);
        }
    });
}

$(document).ready(function() {
    $('#divlistOpportunity').show();

    var stepperElement = $('.bs-stepper')[0];
    window.stepper = new Stepper(stepperElement, {
        linear: false,
        animation: true,
        selectors: {
            steps: '.step',
            trigger: '.step-trigger',
            stepper: '.bs-stepper'
        }
    });

    // Jump to current stage (bs-stepper uses 1-based index)
    window.stepper.to(currentStageIndex + 1);

    // Approve Documents button (DOCUMENT_CHECK)
    $('#approveDocumentsBtn').on('click', function() {
        var btn = $(this).prop('disabled', true).text('Memproses...');
        updateOnboardingLog('DOCUMENT_CHECK', 'completed', 'Dokumen disetujui', function(success) {
            if (!success) {
                btn.prop('disabled', false).text('Setujui Dokumen');
                return;
            }
            updateOnboardingLog('MOU', 'in_progress', '', function(success) {
                if (success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).text('Setujui Dokumen');
                    alert('Gagal melanjutkan ke tahap MOU.');
                }
            });
        });
    });

    // Next button navigation
    $(document).on('click', '.btn-next', function(e) {
        e.preventDefault();
        window.stepper.next();
    });
});

function generateInvoice() {
    $.ajax({
        url: `{{ url('partner-prints-invoice') }}?id=${oppId}&type=downpayment`,
        type: "GET",
        success: function(response, status) {
            window.open(`{{ url('partner-prints-invoice') }}?id=${oppId}&type=downpayment`, '_blank');
            // Mark invoice issuance in the progress log and refresh the page
            updateOnboardingLog('INVOICE', 'completed', 'Invoice DP diterbitkan', function(success) {
                if (!success) { return; }
                updateOnboardingLog('PKS', 'in_progress', '', function(success) {
                    if (success) { location.reload(); }
                });
            });
        },
        error: function(err) {
            console.log(err);
            alert('Gagal membuat invoice.');
        }
    });
}

$('#invoiceNextBtn').on('click', function() {
    var btn = $(this).prop('disabled', true).text('Memproses...');
    updateOnboardingLog('INVOICE', 'completed', 'Invoice DP diterbitkan', function(success) {
        if (!success) {
            btn.prop('disabled', false).text('Next');
            return;
        }
        updateOnboardingLog('PKS', 'in_progress', '', function(success) {
            if (success) {
                location.reload();
            } else {
                btn.prop('disabled', false).text('Next');
                alert('Gagal melanjutkan ke tahap PKS.');
            }
        });
    });
});

$('.downloadMOU').on('click', function() {
    generateMOU();
});

function generateMOU() {
    $.ajax({
        url: `generate-mou?id=${oppId}`,
        type: "GET",
        xhrFields: { responseType: 'blob' },
        success: function(response, status, xhr) {
            var blob = new Blob([response]);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'mou.docx';
            link.click();
            window.URL.revokeObjectURL(link.href);
        },
        error: function(err) {
            alert("Telah terjadi kesalahan.");
        }
    });
}

function submitApproval(type) {
    let fd = new FormData();
    fd.append('id', oppId);
    fd.append('type', type);
    fd.append('_token', '{{ csrf_token() }}');
    $.ajax({
        url: "partner-approval-progress/submit",
        type: "POST",
        processData: false,
        contentType: false,
        data: fd,
        success: function() {
            alert("Berhasil melakukan aksi");
        },
        error: function() {
            alert("Telah terjadi kesalahan.");
        }
    });
}
</script>
@endsection
