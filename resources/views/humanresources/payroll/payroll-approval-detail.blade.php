@php
    $batchId = $batchId ?? request()->input('ID');
    $tasks = isset($approval['tasks']) ? $approval['tasks'] : [];
    $policyCode = $approval['policy_code'] ?? 'PAYROLL-STANDARD-POLICY';
    $policyName = $approval['policy_name'] ?? 'Dynamic Payroll Approval Policy';
    $currentStage = $approval['current_stage'] ?? 1;
    $totalStages = $approval['total_stages'] ?? 1;
    $displayStatus = $approval['display_status'] ?? ($salaryMain->Status ?? '-');
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom pt-3 pb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-1 text-primary d-flex align-items-center flex-wrap gap-2">
                <span><i class="fe fe-layers me-1"></i> Detail & Alur Approval Batch Payroll:</span>
                <span class="badge bg-primary text-white">{{ $batchId }}</span>
                <span class="badge bg-info text-white">{{ $displayStatus }}</span>
            </h6>
            <small class="text-muted">
                Policy Sistem: <strong>{{ $policyCode }}</strong> — {{ $policyName }}
                @if(!empty($approval['request_id']))
                    | Request ID: <code>#{{ $approval['request_id'] }}</code>
                @endif
            </small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(!empty($approval['can_approve']) && !in_array($approval['request_status'] ?? '', ['APPROVED', 'REJECTED', 'CANCELLED'], true))
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 text-white shadow-sm" onclick="approvesalary('{{ $batchId }}');">
                    <i class="fa fa-check-circle me-1"></i> Approve Stage {{ $currentStage }}/{{ $totalStages }}
                </button>
                <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 text-dark" onclick="rejectsalary('{{ $batchId }}');">
                    <i class="fa fa-reply me-1"></i> Reject
                </button>
            @endif
            <button type="button" id="ekselPODListDet" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="PayrollDetail.toExcel('{{ asset('export/generate.php') }}');">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="$('#divlistProjectOrgx').empty();" title="Tutup">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div class="card-body p-3">
        <!-- Stepper Tahapan Approval Dinamis (ApprovalWorkflowService) -->
        @if(!empty($tasks))
            <div class="mb-3 p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                        <i class="fe fe-git-commit me-1 text-primary"></i>Tahapan Persetujuan Sistem (Dynamic Approval Stages)
                    </span>
                    <a href="{{ url('hrd-approval-policy') }}" class="text-primary text-decoration-none" style="font-size: 0.78rem;">
                        <i class="fe fe-settings me-1"></i>Ubah Urutan/Jabatan di Approval Policy
                    </a>
                </div>
                <div class="row g-2">
                    @foreach($tasks as $t)
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
                                        @if(!empty($t->Position_Code))
                                            <code>({{ $t->Position_Code }})</code>
                                        @endif
                                    </div>
                                    @if(!empty($t->Acted_Display))
                                        <div class="text-success fw-semibold mt-1">
                                            <i class="fa fa-user-check me-1"></i>Oleh: {{ $t->Acted_Display }}
                                            @if(!empty($t->Acted_At))
                                                <small class="text-muted">({{ date('d/m/Y H:i', strtotime($t->Acted_At)) }})</small>
                                            @endif
                                        </div>
                                    @elseif(!empty($t->Claimed_Display))
                                        <div class="text-primary mt-1">
                                            <i class="fa fa-user-clock me-1"></i>Di-claim: {{ $t->Claimed_Display }}
                                        </div>
                                    @elseif(!empty($t->Candidate_Names))
                                        <div class="mt-1">
                                            <strong>Kandidat:</strong> {{ implode(', ', $t->Candidate_Names) }}
                                        </div>
                                    @endif
                                    @if(!empty($t->Notes))
                                        <div class="fst-italic text-secondary mt-1">
                                            "{{ $t->Notes }}"
                                        </div>
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
                <i class="fe fe-users me-1"></i>Daftar Rincian Gaji Karyawan dalam Batch Ini
            </span>
            <small class="text-muted">Klik ganda (double-click) pada baris karyawan untuk membuka Slip Gaji Digital.</small>
        </div>
        <div id="PayrollDetail" style="width:100%; height:320px; background-color:white; overflow:hidden; border: 1px solid #e2e8f0; border-radius: 8px;"></div>
        <div id="GroupPayroll_ListsDiv" class="mt-3"></div>
    </div>
</div>

<script>
    function PayrollDetailKlik() {
        var selId = PayrollDetail.getSelectedId();
        if (!selId) return true;
        var nik = PayrollDetail.cells(selId, 2).getValue();
        if (nik != '') {
            window.open('{{ url('print-salary') }}?Task_ID={{ urlencode($batchId) }}&NIK=' + encodeURIComponent(nik), '_blank');
        }
        return true;
    }

    PayrollDetail = new dhtmlXGridObject('PayrollDetail');
    PayrollDetail.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    PayrollDetail.setHeader("No,Name,NIK,Gender,Group Category,Salary Group,Position,Basic Salary,Allowance,Overtime,KJK,WHT Allowance,Deduction,PPH,THP");
    PayrollDetail.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
    PayrollDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
    PayrollDetail.setInitWidths("65,170,105,80,110,130,130,115,115,110,100,115,115,110,125");
    PayrollDetail.setColAlign("center,left,center,center,center,center,left,right,right,right,right,right,right,right,right");
    PayrollDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron");
    PayrollDetail.setColSorting("int,str,str,str,str,str,str,int,int,int,int,int,int,int,int");
    PayrollDetail.setNumberFormat("0,000", 7, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 8, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 9, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 10, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 11, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 12, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 13, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 14, ".", ",");
    PayrollDetail.attachEvent("onRowDblClicked", PayrollDetailKlik);
    PayrollDetail.setSkin("dhx_skyblue");
    PayrollDetail.setEditable(false);
    PayrollDetail.enableMultiline(true);
    PayrollDetail.init();
    PayrollDetail.clearAndLoad('{{ url('xml-payroll-approval-detail') }}?Task_ID={{ urlencode($batchId) }}');
</script>
