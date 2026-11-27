@if(!$task)
<div class="alert alert-warning d-flex align-items-center">
    <i class="fe fe-alert-triangle me-2 fs-18"></i>
    <span>Task approval tidak ditemukan atau telah diproses.</span>
</div>
@else
@php
    $taskStatus = strtoupper((string) $task->Task_Status);
    $statusBadgeClass = 'bg-secondary text-white';
    if ($taskStatus === 'PENDING') {
        $statusBadgeClass = 'bg-warning text-dark';
    } elseif ($taskStatus === 'CLAIMED') {
        $statusBadgeClass = 'bg-primary text-white';
    } elseif ($taskStatus === 'APPROVED') {
        $statusBadgeClass = 'bg-success text-white';
    } elseif ($taskStatus === 'REJECTED') {
        $statusBadgeClass = 'bg-danger text-white';
    } elseif ($taskStatus === 'HELD') {
        $statusBadgeClass = 'bg-secondary text-white';
    }
@endphp

<div class="card shadow-sm border" id="ApprovalTaskDetailCard" data-task-id="{{ $task->ID }}" data-request-id="{{ $task->Request_ID }}" data-request-no="{{ $task->Request_No }}" data-request-type="{{ $task->Request_Type }}" data-requester="{{ $task->Requester_Name }}" data-position="{{ $task->Position_Code }} - {{ $task->Position_Name }}">
    {{-- Card Header --}}
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle me-3">
                <i class="fe fe-file-text fs-18"></i>
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    Task #{{ $task->ID }} &mdash; <span class="text-primary">{{ $task->Request_No }}</span>
                </h5>
                <small class="text-muted">
                    <span class="fw-semibold">{{ $task->Request_Type }}</span> &bull; Diajukan pada: {{ $task->Submitted_At ?: '-' }}
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $statusBadgeClass }} px-3 py-2 fs-12">
                <i class="fe {{ $taskStatus === 'APPROVED' ? 'fe-check-circle' : ($taskStatus === 'REJECTED' ? 'fe-x-circle' : 'fe-clock') }} me-1"></i>
                {{ $taskStatus === 'CLAIMED' ? ($isMyClaim ? 'CLAIMED BY YOU' : 'CLAIMED') : $taskStatus }}
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ApprovalInboxCloseDetail();" title="Tutup Detail">
                <i class="fe fe-x"></i>
            </button>
        </div>
    </div>

    {{-- Quick Action Bar --}}
    <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <i class="fe fe-zap text-warning me-2 fs-16"></i>
            <span class="fw-semibold text-secondary small">Aksi Cepat untuk Task ini:</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(strtoupper(trim((string) $task->Request_Type)) === 'EXIT_CLEARANCE')
                @php
                    $clearanceRecord = DB::table('exit_clearances')->where('Approval_Request_ID', $task->Request_ID)->first();
                    $clearanceViewUrl = $clearanceRecord ? route('hrd-exit-clearance-view', $clearanceRecord->id) : url('hrd-exit-clearance-index');
                @endphp
                <a href="{{ $clearanceViewUrl }}" target="_blank" class="btn btn-info btn-sm text-white shadow-sm">
                    <i class="fe fe-edit me-1"></i> Buka / Isi Form Exit Interview
                </a>
            @endif

            @if(strtoupper(trim((string) $task->Request_Type)) === 'PAYROLL_THR')
                <a href="{{ url('payroll-thr-approval') }}" target="_blank" class="btn btn-info btn-sm text-white shadow-sm">
                    <i class="fe fe-gift me-1"></i> Lihat Detail Batch THR
                </a>
            @endif

            @if($canAct)
            <button type="button" class="btn btn-success btn-sm shadow-sm" onclick="ApprovalInboxAct('APPROVE', {{ $task->ID }});">
                <i class="fe fe-check me-1"></i> Setujui (Approve)
            </button>
            <button type="button" class="btn btn-danger btn-sm shadow-sm" onclick="ApprovalInboxAct('REJECT', {{ $task->ID }});">
                <i class="fe fe-x me-1"></i> Tolak (Reject)
            </button>
            @endif

            @if($canClaim)
            <button type="button" class="btn btn-primary btn-sm shadow-sm" onclick="ApprovalInboxClaim({{ $task->ID }});">
                <i class="fe fe-user-check me-1"></i> Claim Task
            </button>
            @endif

            @if($canRelease)
            <button type="button" class="btn btn-warning btn-sm shadow-sm" onclick="ApprovalInboxRelease({{ $task->ID }});">
                <i class="fe fe-unlock me-1"></i> Release Claim
            </button>
            @endif

            @if(!empty($canForceApprove))
            <button type="button" class="btn btn-dark btn-sm text-white shadow-sm" onclick="ApprovalInboxForceApprove({{ $task->ID }});" title="Bypass seluruh sisa stage approval">
                <i class="fa fa-check-double me-1"></i> Force Approve
            </button>
            @endif
        </div>
    </div>

    {{-- Card Body --}}
    <div class="card-body">
        {{-- Section 1: Detail Grid --}}
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                        <i class="fe fe-info me-2"></i>Informasi Permohonan
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted fw-semibold" style="width: 150px;">No. Request</th>
                                <td class="fw-bold text-dark">: {{ $task->Request_No }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Tipe Permohonan</th>
                                <td>: <span class="badge bg-light text-dark border">{{ $task->Request_Type }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Pemohon</th>
                                <td class="fw-semibold">: {{ $task->Requester_Name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Departemen</th>
                                <td>: {{ $task->Department ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Lokasi / Company</th>
                                <td>: {{ $task->Location ?: '-' }} / {{ $task->Company_ID ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Nominal / Nilai</th>
                                <td class="fw-bold text-success">: Rp {{ number_format((float) $task->Amount, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Revisi / Status Dokumen</th>
                                <td>: Rev {{ $task->Revision_No }} &bull; <span class="badge bg-info text-white">{{ $task->Request_Status }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                        <i class="fe fe-sliders me-2"></i>Status & Konfigurasi Approval
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted fw-semibold" style="width: 150px;">Posisi Approver</th>
                                <td class="fw-bold text-dark">: {{ $task->Position_Code }} - {{ $task->Position_Name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Stage / Level</th>
                                <td>: Stage {{ $task->Stage_Order }} (Level {{ $task->Level_Number }})</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Mode Approval</th>
                                <td>: <span class="badge bg-light text-dark border">{{ $task->Approval_Mode }}{{ $task->Quorum ? ' (Quorum: ' . $task->Quorum . ')' : '' }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Status Task</th>
                                <td>: <span class="badge {{ $statusBadgeClass }}">{{ $task->Task_Status }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Claimed By / At</th>
                                <td>: {{ $task->Claimed_By ? ('User #' . $task->Claimed_By . ' (' . ($task->Claimed_At ?: '-') . ')') : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Catatan Tertahan (Held)</th>
                                <td>: <span class="{{ $task->Held_Reason ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $task->Held_Reason ?: '-' }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Section 2: Description --}}
        <div class="mt-4">
            <h6 class="fw-bold text-dark mb-2">
                <i class="fe fe-align-left me-2 text-primary"></i>Deskripsi / Catatan Permohonan
            </h6>
            <div class="p-3 bg-light rounded border text-muted" style="min-height: 50px;">
                {{ $task->Description ? nl2br(e($task->Description)) : 'Tidak ada keterangan tambahan dari pemohon.' }}
            </div>
        </div>

        {{-- Section 2b: Payroll & Bank Transfer Breakdown (untuk PAYROLL / SALARY / PAYROLL_THR) --}}
        @if(!empty($payrollBreakdown))
            <div class="row">
                @include('humanresources.payroll.partials.approval-payroll-breakdown', ['payrollBreakdown' => $payrollBreakdown])
            </div>
        @endif


        {{{-- Section 2d: Rincian Produk Berdasarkan PR --}}
        @if(!empty($prItems) && count($prItems) > 0)
            @php
                $prHeader = $prContext ?? null;
                $prNoLabel = is_object($prHeader) ? ($prHeader->PR_No ?? $task->Request_No) : ($prHeader['PR_No'] ?? $task->Request_No);
                $prTotal = 0;
            @endphp
            <div class="mt-4 border rounded p-3 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="fe fe-shopping-cart me-2"></i>Rincian Produk Berdasarkan PR — {{ $prNoLabel }}
                    </h6>
                    <span class="badge bg-light text-secondary border">{{ count($prItems) }} Item</span>
                </div>

                @if(!empty($prHeader))
                    @php
                        $prDept = is_object($prHeader) ? ($prHeader->Department ?? '-') : ($prHeader['Department'] ?? '-');
                        $prPic  = is_object($prHeader) ? ($prHeader->PIC ?? '-') : ($prHeader['PIC'] ?? '-');
                        $prUrg  = is_object($prHeader) ? ($prHeader->Urgency ?? '-') : ($prHeader['Urgency'] ?? '-');
                        $prNote = is_object($prHeader) ? ($prHeader->Note ?? '-') : ($prHeader['Note'] ?? '-');
                    @endphp
                    <div class="row g-2 small mb-3">
                        <div class="col-md-4"><strong>Departemen PR:</strong> {{ $prDept ?: '-' }}</div>
                        <div class="col-md-4"><strong>PIC / Pemohon:</strong> {{ $prPic ?: '-' }}</div>
                        <div class="col-md-4"><strong>Urgency:</strong> {{ $prUrg ?: '-' }}</div>
                        @if(!empty($prNote) && $prNote !== '-')
                            <div class="col-12"><strong>Catatan PR:</strong> {{ $prNote }}</div>
                        @endif
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th style="width: 120px;">Kode Produk</th>
                                <th>Nama / Spesifikasi Produk</th>
                                <th class="text-end" style="width: 55px;">Qty</th>
                                <th style="width: 75px;">Satuan</th>
                                <th class="text-end text-nowrap" style="width: 150px;">Harga Satuan</th>
                                <th class="text-end text-nowrap" style="width: 155px;">Subtotal</th>
                                <th class="text-center text-nowrap" style="width: 130px;">Lampiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prItems as $idx => $it)
                                @php
                                    $row = (array) $it;
                                    $qty = (float) ($row['Qty'] ?? $row['qty'] ?? 0);
                                    $price = (float) (
                                        $row['Default_Price']
                                        ?? $row['Price']
                                        ?? $row['Unit_Price']
                                        ?? $row['Master_UnitPrice']
                                        ?? $row['Master_Price']
                                        ?? 0
                                    );
                                    $subtotal = isset($row['Subtotal']) && is_numeric($row['Subtotal'])
                                        ? (float) $row['Subtotal']
                                        : (isset($row['Total']) && is_numeric($row['Total']) ? (float) $row['Total'] : ($qty * $price));
                                    $prTotal += $subtotal;

                                    $code = $row['Product_Code'] ?? $row['Item_Code'] ?? '-';
                                    $name = $row['Product_Desc'] ?? $row['Master_Description'] ?? $row['Description'] ?? '-';
                                    $spec = $row['Specification'] ?? $row['Master_Specification'] ?? '';
                                    $brand = $row['Product_Brand'] ?? '';
                                    $cat = $row['Category'] ?? '';
                                    $stock = $row['Qty_Stock'] ?? null;
                                    $unit = $row['Unit'] ?? $row['Master_Unit_Name'] ?? $row['UOM'] ?? 'PCS';

                                    $metaParts = array_filter([
                                        $brand ? 'Brand: ' . $brand : '',
                                        $cat ? 'Kategori: ' . $cat : '',
                                        ($stock !== null && $stock !== '') ? 'Stok: ' . $stock : '',
                                    ]);

                                    // Parse lampiran per item PR (kolom Attachment di purchase_request_dtl)
                                    $rawAtt = trim((string) ($row['Attachment'] ?? ''));
                                    $itemFiles = [];
                                    if ($rawAtt !== '') {
                                        $decodedJson = json_decode($rawAtt, true);
                                        $parts = is_array($decodedJson) ? $decodedJson : explode(',', $rawAtt);

                                        foreach ($parts as $part) {
                                            $rawVal = is_array($part)
                                                ? trim((string) ($part['url'] ?? $part['path'] ?? $part['file'] ?? ''))
                                                : trim((string) $part, " \t\n\r\0\x0B\"'[]");

                                            if ($rawVal === '') {
                                                continue;
                                            }

                                            if (preg_match('/^https?:\/\//i', $rawVal)) {
                                                $fileUrl = $rawVal;
                                            } else {
                                                $normalized = ltrim(str_replace('\\', '/', $rawVal), '/');
                                                if (str_starts_with($normalized, 'public/')) {
                                                    $normalized = substr($normalized, 7);
                                                }
                                                $fileUrl = url($normalized);
                                            }

                                            $fileName = basename(parse_url($fileUrl, PHP_URL_PATH) ?: $rawVal);
                                            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                                            $itemFiles[] = [
                                                'file_name'  => $fileName ?: $rawVal,
                                                'ext'        => $ext,
                                                'url'        => $fileUrl,
                                                'is_preview' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf'], true),
                                            ];
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td><code class="fw-semibold">{{ $code }}</code></td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $name }}</div>
                                        @if($spec)
                                            <div class="text-muted small">Spec: {{ $spec }}</div>
                                        @endif
                                        @if(!empty($metaParts))
                                            <div class="text-secondary" style="font-size: 0.74rem;">{{ implode(' • ', $metaParts) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') }}</td>
                                    <td>{{ $unit }}</td>
                                    <td class="text-end text-nowrap">Rp {{ number_format($price, 2, ',', '.') }}</td>
                                    <td class="text-end text-nowrap fw-semibold">Rp {{ number_format($subtotal, 2, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if(!empty($itemFiles))
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                @foreach($itemFiles as $fIdx => $f)
                                                    <div class="d-inline-flex align-items-center gap-1" title="{{ $f['file_name'] }}">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary py-0 px-2"
                                                                title="Lihat Lampiran: {{ $f['file_name'] }}"
                                                                onclick="ApprovalOpenAttachmentPreview(@json($f['url']), @json($f['file_name']), @json($f['ext']), {{ $f['is_preview'] ? 'true' : 'false' }});">
                                                            <i class="fe fe-eye"></i>
                                                        </button>
                                                        <span class="text-truncate d-inline-block text-start small" style="max-width: 78px;">
                                                            {{ $f['file_name'] }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end fw-bold">Total Estimasi PR</th>
                                <th class="text-end text-nowrap fw-bold text-success">Rp {{ number_format($prTotal, 2, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
        

        {{-- Section 2c: Detail & Dokumen BAST Asset Handover / Return --}}
        @if(!empty($assetHandoverContext) && !empty($assetHandoverContext['handover']))
            @php
                $ahCtx = $assetHandoverContext['handover'];
                $ahDocType = $assetHandoverContext['doc_type'];
                $ahItems = $assetHandoverContext['items'] ?? collect();
                $ahBastDocs = $assetHandoverContext['bast_docs'] ?? [];
                $ahBastPrintUrl = $assetHandoverContext['bast_print_url'] ?? url('hrd-asset-handover-bast-print?Handover_No=' . urlencode($ahCtx->Handover_No) . '&doc_type=' . $ahDocType);
            @endphp
            <div class="mt-4 border rounded p-3 bg-white">
                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                    <i class="fe fe-package me-2"></i>Rincian Serah Terima Aset ({{ $ahDocType === 'RETURN' ? 'Pengembalian' : 'Handover' }}) — {{ $ahCtx->Handover_No }}
                </h6>
                <div class="row g-2 small mb-3">
                    <div class="col-md-4"><strong>Lead Pemberi:</strong> {{ $ahCtx->Lead_Name ?? '-' }} ({{ $ahCtx->Lead_NIK ?? '-' }})</div>
                    <div class="col-md-4"><strong>PIC Penerima:</strong> {{ $ahCtx->PIC_Name ?: '-' }} ({{ $ahCtx->PIC_Department ?: '-' }})</div>
                    <div class="col-md-4"><strong>Status Handover:</strong> <span class="badge bg-info text-white">{{ $ahCtx->Status }}</span></div>
                    @if($ahDocType === 'RETURN')
                        <div class="col-md-4"><strong>PIC Approval Return:</strong> {{ $ahCtx->Return_Lead_Name ?? '-' }} ({{ $ahCtx->Return_Lead_NIK ?? '-' }})</div>
                        <div class="col-md-4"><strong>Penerima Pengembalian:</strong> {{ $ahCtx->Return_Receiver_Name ?? '-' }} ({{ $ahCtx->Return_Receiver_NIK ?? '-' }})</div>
                    @endif
                </div>

                @if($ahItems->isNotEmpty())
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Asset No</th>
                                    <th>Kode Produk</th>
                                    <th>Deskripsi</th>
                                    <th class="text-end">Qty</th>
                                    <th>Kondisi</th>
                                    <th>Status Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ahItems as $idx => $it)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $it->Asset_No ?? '-' }}</td>
                                        <td>{{ $it->Product_Code }}</td>
                                        <td>{{ $it->Description }}</td>
                                        <td class="text-end">{{ $it->Qty }} {{ $it->Unit }}</td>
                                        <td>{{ $ahDocType === 'RETURN' ? ($it->Returned_Condition ?: $it->Condition) : $it->Condition }}</td>
                                        <td><span class="badge bg-secondary text-white">{{ $it->Status ?: '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @include('humanresources.asset-handover.partials.bast-panel', [
                    'handoverNo'   => $ahCtx->Handover_No,
                    'docType'      => $ahDocType,
                    'docs'         => $ahBastDocs,
                    'bastPrintUrl' => $ahBastPrintUrl,
                    'panelId'      => 'bastInboxPanel_' . (int) $task->ID,
                    'allowUpload'  => true,
                ])
            </div>
        @endif

        {{-- Section 3: Candidates --}}
        <div class="mt-4">
            <h6 class="fw-bold text-dark mb-2 d-flex justify-content-between align-items-center">
                <span><i class="fe fe-users me-2 text-primary"></i>Kandidat Approver Posisi Ini</span>
                <span class="badge bg-light text-secondary border">{{ $candidates->count() }} Kandidat</span>
            </h6>
            <div class="table-responsive border rounded">
                <table class="table table-sm table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama Pegawai</th>
                            <th>Username</th>
                            <th>Posisi / Dept</th>
                            <th>Tipe Kandidat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($candidates as $candidate)
                        @php
                            $isCurrentUser = ((int) $candidate->User_Id === (int) Auth::id());
                        @endphp
                        <tr class="{{ $isCurrentUser ? 'table-primary' : '' }}">
                            <td class="ps-3 fw-semibold">
                                {{ $candidate->User_Name }}
                                @if($isCurrentUser)
                                    <span class="badge bg-primary text-white ms-1">Anda</span>
                                @endif
                            </td>
                            <td><code>{{ $candidate->username }}</code></td>
                            <td>{{ $candidate->Position ?: '-' }} @if($candidate->Department) &bull; <small class="text-muted">{{ $candidate->Department }}</small> @endif</td>
                            <td>
                                <span class="badge {{ $candidate->Candidate_Type === 'HOLDER' ? 'bg-info text-white' : 'bg-warning text-dark' }}">
                                    {{ $candidate->Candidate_Type }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $candidate->Status === 'ACTIVE' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                    {{ $candidate->Status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">Belum ada kandidat aktif yang terhubung.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 3.5: Jenjang Approval Lengkap (Policy Plan & Status) --}}
        @if(!empty($approvalTrail))
        <div class="mt-4">
            <h6 class="fw-bold text-dark mb-2">
                <i class="fe fe-git-branch me-2 text-primary"></i>Jenjang Approval Lengkap (Termasuk Stage Dilewati / Mendatang)
            </h6>
            <div class="border rounded">
                @include('humanresources.approval.partials.trail-table', ['trail' => $approvalTrail])
            </div>
        </div>
        @endif

        {{-- Section 4: History / Audit Logs --}}
        <div class="mt-4">
            <h6 class="fw-bold text-dark mb-2">
                <i class="fe fe-activity me-2 text-primary"></i>Riwayat Persetujuan (Audit Trail)
            </h6>
            <div class="table-responsive border rounded">
                <table class="table table-sm table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 170px;">Waktu</th>
                            <th>Oleh</th>
                            <th>Aksi</th>
                            <th>Stage</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                            $actionUpper = strtoupper((string) $log->Action);
                            $actionBadge = 'bg-secondary text-white';
                            if (in_array($actionUpper, ['APPROVE', 'APPROVED', 'FINAL_APPROVED', 'FORCE_APPROVED'])) {
                                $actionBadge = 'bg-success text-white';
                            } elseif (in_array($actionUpper, ['REJECT', 'REJECTED'])) {
                                $actionBadge = 'bg-danger text-white';
                            } elseif (in_array($actionUpper, ['CLAIMED', 'CLAIM'])) {
                                $actionBadge = 'bg-primary text-white';
                            } elseif (in_array($actionUpper, ['SUBMITTED'])) {
                                $actionBadge = 'bg-info text-white';
                            } elseif (in_array($actionUpper, ['CLAIM_RELEASED', 'RELEASED'])) {
                                $actionBadge = 'bg-warning text-dark';
                            }
                        @endphp
                        <tr>
                            <td class="ps-3 small text-muted">{{ $log->Action_Date }}</td>
                            <td class="fw-semibold">{{ $log->Approver_Name ?: ($log->Is_System ? 'SYSTEM' : ('User #' . $log->Approver_Id)) }}</td>
                            <td><span class="badge {{ $actionBadge }}">{{ $log->Action }}</span></td>
                            <td>Stage {{ $log->Stage_Order ?: '-' }}</td>
                            <td class="text-secondary">{{ $log->Notes ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat aktivitas pada permohonan ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 5: Admin Self-Approval Exception --}}
        @if($isAdmin && in_array(strtoupper((string) $task->Request_Status), ['IN_PROGRESS', 'BLOCKED'], true))
        <div class="card border border-warning mt-4 bg-light">
            <div class="card-header bg-warning-transparent text-warning-dark border-bottom py-2">
                <strong class="d-flex align-items-center">
                    <i class="fe fe-shield me-2"></i>Grant Self-Approval Exception (Administrator)
                </strong>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Berikan izin khusus jika pemohon perlu menyetujui dokumen sendiri untuk situasi mendesak/khusus.
                </p>
                <form method="POST" action="{{ url('hrd-approval-self-exception') }}">
                    @csrf
                    <input type="hidden" name="Request_ID" value="{{ $task->Request_ID }}" />
                    <input type="hidden" name="User_Id" value="{{ $task->Requester_Id }}" />
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Alasan Exception (Wajib)</label>
                            <input type="text" name="Reason" class="form-control form-control-sm" placeholder="Jelaskan dasar pemberian exception..." required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Berlaku Sampai (Opsional)</label>
                            <input type="datetime-local" name="Expires_At" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                <i class="fe fe-check me-1"></i> Berikan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endif
