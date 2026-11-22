@php
    $batchId = (int)($batchId ?? request()->input('ID', 0));
    $tasks = isset($approval['tasks']) ? $approval['tasks'] : [];
    $policyCode = $approval['policy_code'] ?? 'PAYROLL-THR-STANDARD-POLICY';
    $policyName = $approval['policy_name'] ?? 'Dynamic Payroll THR Policy';
    $currentStage = $approval['current_stage'] ?? 1;
    $totalStages = $approval['total_stages'] ?? 1;
    $displayStatus = $approval['display_status'] ?? ($batch->status ?? '-');
    $batchNo = $batch ? $batch->batch_no : ('#' . $batchId);
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom pt-3 pb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-1 text-primary d-flex align-items-center flex-wrap gap-2">
                <span><i class="fe fe-gift me-1"></i> Detail &amp; Alur Approval Batch THR:</span>
                <span class="badge bg-primary text-white">{{ $batchNo }}</span>
                <span class="badge bg-info text-white">{{ $displayStatus }}</span>
            </h6>
            <small class="text-muted">
                Policy Sistem: <strong>{{ $policyCode }}</strong> — {{ $policyName }}
                @if (!empty($approval['request_id']))
                    | Request ID: <code>#{{ $approval['request_id'] }}</code>
                @endif
            </small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if (!empty($approval['can_approve']) && !in_array($approval['request_status'] ?? '', ['APPROVED', 'REJECTED', 'CANCELLED'], true))
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 text-white shadow-sm" onclick="approveThrBatch({{ $batchId }});">
                    <i class="fa fa-check-circle me-1"></i> Approve Stage {{ $currentStage }}/{{ $totalStages }}
                </button>
                <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 text-dark" onclick="rejectThrBatch({{ $batchId }});">
                    <i class="fa fa-reply me-1"></i> Reject
                </button>
            @endif
            <a href="{{ url('payroll-thr-export-excel') }}?batch_id={{ $batchId }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="$('#divThrApprovalDetail').empty();" title="Tutup">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div class="card-body p-3">
        @if ($batch)
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <div class="p-2 rounded-3 border bg-light">
                        <small class="text-muted d-block">Event / Periode THR</small>
                        <strong class="text-dark">{{ $batch->thr_event_name }} ({{ $batch->salary_period }})</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded-3 border bg-light">
                        <small class="text-muted d-block">Karyawan Eligible</small>
                        <strong class="text-dark">{{ $batch->total_employees }} Orang</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded-3 border bg-light">
                        <small class="text-muted d-block">Total Bruto / PPh 21</small>
                        <strong class="text-dark">Rp {{ number_format((float)$batch->total_amount, 0, ',', '.') }}</strong>
                        <small class="text-danger">(PPh: Rp {{ number_format((float)($batch->total_pph21 ?? 0), 0, ',', '.') }})</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded-3 border bg-light">
                        <small class="text-muted d-block">Total Nett THR (THP)</small>
                        <strong class="text-success">Rp {{ number_format((float)(($batch->total_net_amount ?? 0) > 0 ? $batch->total_net_amount : $batch->total_amount), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        @endif

        @if (!empty($tasks))
            <div class="mb-3 p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                        <i class="fe fe-git-commit me-1 text-primary"></i> Tahapan Persetujuan Sistem (Dynamic Approval Stages)
                    </span>
                    <a href="{{ url('hrd-approval-policy') }}" class="text-primary text-decoration-none" style="font-size: 0.78rem;">
                        <i class="fe fe-settings me-1"></i> Ubah Urutan/Jabatan di Approval Policy
                    </a>
                </div>
                <div class="row g-2">
                    @foreach ($tasks as $t)
                        @php
                            $st = strtoupper((string)($t->Status ?? 'PENDING'));
                            $isApproved = in_array($st, ['APPROVED', 'AUTO_APPROVED'], true);
                            $isActive = ((int)$t->Stage_Order === (int)$currentStage) && in_array($st, ['PENDING', 'CLAIMED', 'HELD'], true);
                            $isRejected = in_array($st, ['REJECTED', 'RETURNED', 'CANCELLED'], true);

                            $borderColor = '#cbd5e1';
                            $bgCard = '#ffffff';
                            $badgeClass = 'bg-secondary text-white';
                            $statusText = $st;

                            if ($isApproved) {
                                $borderColor = '#10b981';
                                $bgCard = '#ecfdf5';
                                $badgeClass = 'bg-success text-white';
                                $statusText = 'APPROVED';
                            } elseif ($isActive) {
                                $borderColor = '#3b82f6';
                                $bgCard = '#eff6ff';
                                $badgeClass = 'bg-primary text-white';
                                $statusText = ($st === 'CLAIMED') ? 'CLAIMED (AKTIF)' : 'MENUNGGU APPROVAL';
                            } elseif ($isRejected) {
                                $borderColor = '#ef4444';
                                $bgCard = '#fef2f2';
                                $badgeClass = 'bg-danger text-white';
                            }
                        @endphp
                        <div class="col-md-4 col-sm-6">
                            <div class="p-2 rounded-3 h-100" style="background: {{ $bgCard }}; border: 1.5px solid {{ $borderColor }};">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark" style="font-size: 0.8rem;">
                                        Stage {{ $t->Stage_Order }}: {{ $t->Step_Label ?? $t->Position_Name }}
                                    </span>
                                    <span class="badge {{ $badgeClass }}" style="font-size: 0.68rem;">{{ $statusText }}</span>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <div>
                                        <strong>Jabatan:</strong>
                                        {{ $t->Position_Name ?: '-' }}
                                        @if (!empty($t->Position_Code))
                                            <code>({{ $t->Position_Code }})</code>
                                        @endif
                                    </div>
                                    @if (!empty($t->Acted_Display))
                                        <div class="text-success fw-semibold mt-1">
                                            <i class="fa fa-user-check me-1"></i> Oleh: {{ $t->Acted_Display }}
                                            @if (!empty($t->Acted_At))
                                                <small class="text-muted">({{ date('d/m/Y H:i', strtotime($t->Acted_At)) }})</small>
                                            @endif
                                        </div>
                                    @elseif (!empty($t->Claimed_Display))
                                        <div class="text-primary mt-1">
                                            <i class="fa fa-user-clock me-1"></i> Di-claim: {{ $t->Claimed_Display }}
                                        </div>
                                    @elseif (!empty($t->Candidate_Names))
                                        <div class="mt-1">
                                            <strong>Kandidat:</strong> {{ implode(', ', $t->Candidate_Names) }}
                                        </div>
                                    @endif
                                    @if (!empty($t->Notes))
                                        <div class="fst-italic text-secondary mt-1">"{{ $t->Notes }}"</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-secondary" style="font-size: 0.85rem;">
                <i class="fe fe-users me-1"></i> Daftar Penerima THR dalam Batch Ini
            </span>
            <small class="text-muted">Klik ganda (double-click) pada baris karyawan untuk membuka Preview Slip THR.</small>
        </div>
        <div id="ThrApprovalDetailGrid" style="width:100%; height:320px; background-color:white; overflow:hidden; border: 1px solid #e2e8f0; border-radius: 8px;"></div>
    </div>
</div>

<script>
    function ThrApprovalDetailKlik() {
        var selId = ThrApprovalDetailGrid.getSelectedId();
        if (!selId) return true;
        var nik = ThrApprovalDetailGrid.cells(selId, 1).getValue() || selId;
        if (nik !== '') {
            window.open('{{ url('payslip-thr') }}?batch_id={{ $batchId }}&nik=' + encodeURIComponent(nik), '_blank');
        }
        return true;
    }

    ThrApprovalDetailGrid = new dhtmlXGridObject('ThrApprovalDetailGrid');
    ThrApprovalDetailGrid.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    ThrApprovalDetailGrid.setHeader("No,NIK,Nama Karyawan,Department,Agama,Masa Kerja,Skema THR,Prorata,Basis Upah,THR Sistem,Adjustment,THR Bruto,PPh 21 THR,Nett THR (THP)");
    ThrApprovalDetailGrid.attachHeader("#rspan,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
    ThrApprovalDetailGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
    ThrApprovalDetailGrid.setInitWidths("50,100,175,115,85,120,180,70,110,110,105,115,105,120");
    ThrApprovalDetailGrid.setColAlign("center,left,left,left,center,center,left,right,right,right,right,right,right,right");
    ThrApprovalDetailGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron");
    ThrApprovalDetailGrid.setColSorting("int,str,str,str,str,str,str,str,int,int,int,int,int,int");
    ThrApprovalDetailGrid.setNumberFormat("0,000", 8, ".", ",");
    ThrApprovalDetailGrid.setNumberFormat("0,000", 9, ".", ",");
    ThrApprovalDetailGrid.setNumberFormat("0,000", 10, ".", ",");
    ThrApprovalDetailGrid.setNumberFormat("0,000", 11, ".", ",");
    ThrApprovalDetailGrid.setNumberFormat("0,000", 12, ".", ",");
    ThrApprovalDetailGrid.setNumberFormat("0,000", 13, ".", ",");
    ThrApprovalDetailGrid.attachEvent("onRowDblClicked", ThrApprovalDetailKlik);
    ThrApprovalDetailGrid.setSkin("dhx_skyblue");
    ThrApprovalDetailGrid.setEditable(false);
    ThrApprovalDetailGrid.enableMultiline(true);
    ThrApprovalDetailGrid.init();
    ThrApprovalDetailGrid.clearAndLoad('{{ url('xml-payroll-thr-approval-detail') }}?batch_id={{ $batchId }}');
</script>
