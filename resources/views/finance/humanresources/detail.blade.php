@if(!$item)
<div class="alert alert-warning d-flex align-items-center mb-0">
    <i class="fe fe-alert-triangle me-2 fs-18"></i>
    <span>Data detail tidak ditemukan atau telah diproses.</span>
</div>
@else
<div class="card shadow-sm border mt-3" id="FinanceDetailCard">
    {{-- Card Header --}}
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle me-3">
                <i class="fe fe-file-text fs-18"></i>
            </span>
            <div>
                @if($type === 'workflow')
                    <h5 class="mb-0 fw-bold text-dark">Task #{{ $item->Task_ID }} &mdash; <span class="text-primary">{{ $item->Request_No }}</span></h5>
                    <small class="text-muted"><span class="fw-semibold">{{ $item->Request_Type }}</span> &bull; Diajukan: {{ $item->Submitted_At ?: '-' }}</small>
                @elseif($type === 'maintenance')
                    <h5 class="mb-0 fw-bold text-dark">Voucher Perawatan &mdash; <span class="text-primary">{{ $item->Voucher_No ?: $item->Schedule_No }}</span></h5>
                    <small class="text-muted">No. Schedule: {{ $item->Schedule_No }} &bull; Aset: {{ $item->Asset_No }}</small>
                @else
                    <h5 class="mb-0 fw-bold text-dark">Voucher Pajak STNK/KEUR &mdash; <span class="text-primary">{{ $item->Voucher_No ?: $item->Tax_Request_No }}</span></h5>
                    <small class="text-muted">No. Pengajuan: {{ $item->Tax_Request_No }} &bull; Kendaraan: {{ $item->Registration_No ?: $item->Asset_No }}</small>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $badgeStatus = $type === 'workflow' ? ($item->Task_Status ?? $item->Status) : ($item->Status ?? '-');
            @endphp
            <span class="badge bg-info text-white px-3 py-2">{{ $badgeStatus }}</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#FinanceApprovalDetail').empty();" title="Tutup Detail">
                <i class="fa fa-times"></i> Tutup
            </button>
        </div>
    </div>

    {{-- Quick Action Bar --}}
    <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <i class="fa fa-bolt text-warning me-2"></i>
            <span class="fw-semibold text-secondary small">Aksi Cepat Finance:</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($type === 'workflow')
                <button type="button" class="btn btn-sm btn-success" onclick="actFinanceWorkflow({{ $item->Task_ID }}, 'APPROVE', true);">
                    <i class="fa fa-check me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="actFinanceWorkflow({{ $item->Task_ID }}, 'REJECT', true);">
                    <i class="fa fa-times me-1"></i> Reject
                </button>
            @elseif($type === 'maintenance')
                @if($item->Status === 'FINANCE_SUBMITTED')
                    <button type="button" class="btn btn-sm btn-success" onclick="confirmMaintVoucher({{ $item->ID }}, true);">
                        <i class="fa fa-check-circle me-1"></i> Verifikasi &amp; Setujui Jadwal
                    </button>
                @endif
            @else
                <a href="hrd-vehicle-management-tax-voucher?id={{ $item->ID }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-print me-1"></i> Cetak Voucher
                </a>
                @if($item->Status === 'FINANCE_SUBMITTED')
                    <button type="button" class="btn btn-sm btn-success" onclick="openFinanceTaxPayModal({{ $item->ID }});">
                        <i class="fa fa-money-bill-wave me-1"></i> Bayar &amp; Selesai
                    </button>
                @elseif(!empty($item->Bukti_Bayar) && strpos($item->Bukti_Bayar, '/') !== false)
                    <a href="{{ asset('filevendor/' . $item->Bukti_Bayar) }}" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="fa fa-file-alt me-1"></i> Lihat Bukti Bayar
                    </a>
                @endif
            @endif
        </div>
    </div>

    {{-- Body Content --}}
    <div class="card-body">
        @if($type === 'workflow')
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold text-primary mb-3"><i class="fa fa-info-circle me-1"></i> Informasi Pengajuan</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th class="text-muted" style="width:38%">No. Request</th><td class="fw-bold">{{ $item->Request_No }}</td></tr>
                            <tr><th class="text-muted">Pemohon</th><td>{{ $item->Requester_Name ?: $item->Requester_Username }}</td></tr>
                            <tr><th class="text-muted">Departemen</th><td>{{ $item->Department ?: '-' }}</td></tr>
                            <tr><th class="text-muted">Lokasi / Company</th><td>{{ $item->Location ?: '-' }} / {{ $item->Company_ID ?: '-' }}</td></tr>
                            <tr><th class="text-muted">Nominal</th><td class="fw-bold text-success">Rp {{ number_format((float) ($item->Amount ?? 0), 0, ',', '.') }}</td></tr>
                            <tr><th class="text-muted">Deskripsi</th><td>{{ $item->Description ?: '-' }}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold text-primary mb-3"><i class="fa fa-list-alt me-1"></i> Rincian Konteks &amp; Policy</h6>
                        @if(!empty($context))
                            <div class="row g-2 mb-3">
                                @foreach($context as $ck => $cv)
                                    @if(!is_array($cv) && !is_object($cv) && $cv !== '')
                                    <div class="col-6">
                                        <div class="small text-muted">{{ $ck }}</div>
                                        <div class="fw-semibold small">{{ $cv }}</div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Pipeline Tahap Policy</h6>
                        @if(!empty($policy['steps']))
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                @foreach($policy['steps'] as $st)
                                    @php $isCurrent = ((int) ($st['Stage_Order'] ?? 0) === (int) ($item->Current_Stage ?? 0)); @endphp
                                    <span class="badge {{ $isCurrent ? 'bg-primary text-white' : 'bg-light text-dark border' }} px-2 py-1">
                                        Stage {{ $st['Stage_Order'] ?? '-' }}: {{ $st['Label'] ?? ($st['Position_Code'] ?? '-') }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="small text-muted">Stage aktif: {{ $item->Current_Stage ?? 1 }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="border rounded p-3">
                        <h6 class="fw-bold text-primary mb-2"><i class="fa fa-history me-1"></i> Riwayat Approval (Timeline)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light">
                                    <tr><th>Stage</th><th>Aksi</th><th>Oleh</th><th>Catatan</th><th>Waktu</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $l)
                                    <tr>
                                        <td>Stage {{ $l->Stage_Order ?: '-' }}</td>
                                        <td><span class="badge bg-info text-white">{{ $l->Action }}</span></td>
                                        <td>{{ $l->Actor_Name ?: ($l->Actor_Username ?: 'System') }}</td>
                                        <td>{{ $l->Notes ?: '-' }}</td>
                                        <td>{{ $l->Action_Date ?: '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-2">Belum ada riwayat aksi.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($type === 'maintenance')
            <div class="row g-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered mb-0">
                        <tr><th style="width:38%">No. Voucher</th><td class="fw-bold text-primary">{{ $item->Voucher_No ?: '-' }}</td></tr>
                        <tr><th>No. Schedule</th><td>{{ $item->Schedule_No }}</td></tr>
                        <tr><th>Aset</th><td>{{ $item->Asset_No }} - {{ $item->Asset_Name ?: '-' }}</td></tr>
                        <tr><th>Lokasi / PIC Aset</th><td>{{ $item->Asset_Location ?: '-' }} / {{ $item->User_Responsible ?: '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered mb-0">
                        <tr><th style="width:38%">Jenis &amp; Frekuensi</th><td>{{ $item->Jenis_Perawatan }} ({{ $item->Frekuensi }})</td></tr>
                        <tr><th>Jadwal Berikutnya</th><td>{{ $item->Next_Schedule_Date }}</td></tr>
                        <tr><th>PIC Internal / Vendor</th><td>{{ $item->PIC_Internal ?: '-' }} {{ $item->Vendor_Name ? ' / ' . $item->Vendor_Name : '' }}</td></tr>
                        <tr><th>Estimasi Biaya</th><td class="fw-bold text-success">Rp {{ number_format((float) $item->Estimasi_Biaya, 0, ',', '.') }}</td></tr>
                    </table>
                </div>
                <div class="col-12">
                    <div class="p-2 bg-light border rounded small">
                        <strong>Catatan Approval HCGA Manager:</strong> {{ $item->Approval_Note ?: '-' }}
                    </div>
                </div>
            </div>
        @else
            <div class="row g-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered mb-0">
                        <tr><th style="width:38%">No. Voucher</th><td class="fw-bold text-primary">{{ $item->Voucher_No ?: '-' }}</td></tr>
                        <tr><th>No. Pengajuan</th><td>{{ $item->Tax_Request_No }}</td></tr>
                        <tr><th>No. Polisi / Asset</th><td><strong>{{ $item->Registration_No ?: '-' }}</strong> ({{ $item->Asset_No }})</td></tr>
                        <tr><th>Kendaraan</th><td>{{ trim(($item->Product_Description ?? '') . ' ' . ($item->Product_Brand ?? '') . ' ' . ($item->Model ?? '')) ?: '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered mb-0">
                        <tr><th style="width:38%">Jenis Dokumen</th><td><span class="badge bg-dark text-white">{{ $item->Jenis_Pajak }}</span></td></tr>
                        <tr><th>Tanggal Jatuh Tempo</th><td>{{ $item->Tanggal_Jatuh_Tempo ?: '-' }}</td></tr>
                        <tr><th>Estimasi Biaya</th><td class="fw-bold text-success">Rp {{ number_format((float) $item->Estimasi_Biaya, 0, ',', '.') }}</td></tr>
                        <tr><th>Catatan HCGA Mgr</th><td>{{ $item->Approval_Note ?: '-' }}</td></tr>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
