@php
    $status = strtoupper((string) $ticket->Ticket_Status);
    $mode = strtoupper((string) ($ticket->Workflow_Mode ?? 'OPERATIONAL'));
    $isVoucherMode = ($mode === 'WORK_ORDER_VOUCHER');
    $badge = [
        'OPEN' => 'secondary',
        'TRIAGED' => 'info',
        'ASSIGNED' => 'primary',
        'IN_PROGRESS' => 'warning',
        'ON_HOLD' => 'dark',
        'RESOLVED' => 'success',
        'CLOSED' => 'secondary',
        'REJECTED' => 'danger',
        'REOPENED' => 'warning',
        'WAITING_DEPT_HEAD' => 'warning',
        'GA_VERIFICATION' => 'info',
        'WAITING_BUDGET_APPROVAL' => 'warning',
        'READY_FOR_VOUCHER' => 'primary',
        'VOUCHER_CREATED' => 'info',
        'PAID' => 'success',
    ][$status] ?? 'secondary';
    $canCreate = can_menu('SI01013', 'create');
    $canEdit = can_menu('SI01013', 'edit');
    $canApprove = can_menu('SI01013', 'approve');
    $canVerifyOrVoucher = $canEdit || $canApprove;
    $canCreateWo = can_menu('SI01014', 'create');
    $prefVendorObj = null;
    if (!empty($ticket->Preferred_Vendor_ID) && isset($vendors)) {
        $prefVendorObj = $vendors->firstWhere('ID', (int) $ticket->Preferred_Vendor_ID);
    }
@endphp

<div class="mt-3" id="GATicketDetailContainer">
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title m-2 mb-0">Detail Tiket {{ $ticket->Ticket_No }}</h4>
                <small class="ms-2 text-muted">Mode: {{ $isVoucherMode ? 'Order Pekerjaan & Voucher Pembayaran' : 'Operasional GA' }}</small>
            </div>
            <span class="badge badge-{{ $badge }} bg-{{ $badge }} {{ $badge === 'warning' ? 'text-dark' : 'text-white' }} p-2">{{ $status }}</span>
        </div>
        <div class="card-body">
            @include('humanresources.ga.ticket.partials.stepper', ['steps' => $progressSteps ?? []])

            @if ($isVoucherMode && $status === 'GA_VERIFICATION' && (int) ($ticket->Approval_Revision_Count ?? 0) > 0)
                <div class="alert alert-warning mb-3">
                    <strong>Dikembalikan ke GA Verification (Revisi #{{ (int) $ticket->Approval_Revision_Count }}):</strong>
                    {{ $ticket->Rejection_Reason ?: 'Approval biaya sebelumnya meminta revisi.' }}
                    <div class="small mt-1 text-muted">Perbaiki nominal atau vendor di form Verifikasi GA di bawah; pengajuan ulang langsung menuju Approval Biaya tanpa mengulang approval Dept Head.</div>
                </div>
            @endif

            @if ($status === 'REJECTED' && !empty($ticket->Rejection_Reason))
                <div class="alert alert-danger mb-3">
                    <strong>Alasan Penolakan:</strong> {{ $ticket->Rejection_Reason }}
                </div>
            @endif

            <input type="hidden" id="GATicketDetailID" value="{{ $ticket->ID }}" />
            <div class="row">
                <div class="col-md-7">
                    <h5>{{ $ticket->Ticket_Subject }}</h5>
                    <p class="text-muted mb-1">{{ $ticket->Category_Name ?: 'Tanpa kategori' }} {{ $ticket->Subcategory_Name ? '/ ' . $ticket->Subcategory_Name : '' }}</p>
                    <p>{{ $ticket->Ga_Description ?: $ticket->Description }}</p>
                </div>
                <div class="col-md-5">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Prioritas</th><td>{{ $ticket->Priority }}</td></tr>
                        <tr><th>Reporter</th><td>{{ $ticket->Reporter_Name ?: $ticket->Reporter }} ({{ $ticket->Reporter_Department ?: $ticket->Department }})</td></tr>
                        @if ($isVoucherMode)
                            <tr><th>Estimasi Awal</th><td>Rp {{ number_format((float) ($ticket->Estimated_Amount ?? 0), 0, ',', '.') }}</td></tr>
                            <tr><th>Biaya Verifikasi</th><td class="fw-bold text-primary">{{ (float) ($ticket->Verified_Amount ?? 0) > 0 ? ('Rp ' . number_format((float) $ticket->Verified_Amount, 0, ',', '.')) : '-' }}</td></tr>
                            <tr><th>Vendor Pilihan</th><td>{{ $ticket->Preferred_Vendor_Name ?: '-' }}</td></tr>
                            <tr><th>No. Voucher</th><td class="fw-bold">{{ $ticket->Voucher_No ?: '-' }}</td></tr>
                        @else
                            <tr><th>Assignee</th><td>{{ $ticket->Assignee_Name ?: ($ticket->Team_Name ?: ($ticket->Vendor_Name ?: '-')) }}</td></tr>
                            <tr><th>SLA</th><td>{{ $ticket->Sla_Name ?: '-' }}</td></tr>
                            <tr><th>Response Due</th><td>{{ $ticket->Response_Due_At ?: '-' }} @if ($sla['Response_Overdue'])<span class="badge badge-danger bg-danger text-white">OVERDUE</span>@endif</td></tr>
                            <tr><th>Resolve Due</th><td>{{ $ticket->Resolve_Due_At ?: '-' }} @if ($sla['Resolve_Overdue'])<span class="badge badge-danger bg-danger text-white">OVERDUE</span>@endif</td></tr>
                        @endif
                        <tr><th>Dibuat</th><td>{{ $ticket->Created_Date }} oleh {{ $ticket->Created_By }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($canCreateWo && !in_array($status, ['CLOSED', 'REJECTED', 'PAID'], true))
                    <button type="button" class="btn btn-primary btn-sm" onclick="GATicketOpenWorkOrder()">Buat Work Order</button>
                @endif
                @if (!$isVoucherMode && $canEdit && !in_array($status, ['CLOSED', 'REJECTED', 'PAID'], true))
                    <button type="button" class="btn btn-warning btn-sm text-dark" onclick="GATicketShowTriage()">Triage</button>
                @endif
                @if ($isVoucherMode && in_array($status, ['WAITING_DEPT_HEAD', 'WAITING_BUDGET_APPROVAL'], true))
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="GATicketSyncApproval()">Sync Status Approval</button>
                @endif
                @if (!empty($voucher))
                    <a href="{{ url('hrd-ga-ticket-voucher-print?voucher_id=' . (int) $voucher->ID) }}" target="_blank" class="btn btn-outline-primary btn-sm">Cetak Voucher</a>
                @endif
                <button type="button" class="btn btn-secondary btn-sm" onclick="GATicketCloseDetail()">Tutup</button>
            </div>
        </div>
    </div>

    <div id="GAWORKORDERFORM"></div>

    @if ($isVoucherMode && $status === 'REJECTED' && empty($voucher) && ($canCreate || $canEdit))
        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning text-dark"><h5 class="card-title m-1">Ajukan Ulang ke Dept Head</h5></div>
            <div class="card-body">
                <form id="GAResubmitDeptForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Subjek Order <span class="text-danger">*</span></label>
                                <input type="text" name="Ticket_Subject" class="form-control" maxlength="250" value="{{ $ticket->Ticket_Subject }}" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Estimasi Biaya (IDR)</label>
                                <input type="number" name="Estimated_Amount" class="form-control" min="0" step="1000" value="{{ (float) ($ticket->Estimated_Amount ?? 0) }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Rekomendasi Vendor</label>
                                <select name="Preferred_Vendor_ID" class="form-control">
                                    <option value="">-- opsional --</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->ID }}" {{ (int) $ticket->Preferred_Vendor_ID === (int) $vendor->ID ? 'selected' : '' }}>{{ $vendor->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Deskripsi Revisi <span class="text-danger">*</span></label>
                                <textarea name="Description" class="form-control" rows="3" maxlength="5000" required>{{ $ticket->Ga_Description ?: $ticket->Description }}</textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm text-dark">Kirim Ulang ke Dept Head</button>
                </form>
            </div>
        </div>
    @endif

    @if ($isVoucherMode && $status === 'GA_VERIFICATION' && $canVerifyOrVoucher)
        <div class="card mt-3 border-primary">
            <div class="card-header bg-primary text-white"><h5 class="card-title m-1 text-white">GA Verification &amp; Ajukan Approval Biaya (Fase 2)</h5></div>
            <div class="card-body">
                <form id="GAVerifyForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Nominal Verifikasi GA (IDR) <span class="text-danger">*</span></label>
                                <input type="number" name="Verified_Amount" id="GAVerifiedAmountInput" class="form-control" min="1" step="any" value="{{ (float) ($ticket->Verified_Amount > 0 ? $ticket->Verified_Amount : ($ticket->Estimated_Amount > 0 ? $ticket->Estimated_Amount : '')) }}" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Vendor / Rekanan Final <span class="text-muted">(opsional)</span></label>
                                <select name="Preferred_Vendor_ID" class="form-control">
                                    <option value="">-- pilih vendor / isi saat voucher --</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->ID }}" {{ (int) $ticket->Preferred_Vendor_ID === (int) $vendor->ID ? 'selected' : '' }}>{{ $vendor->Company_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-light border py-2 mb-3 small" id="GATierPreviewBox">
                                <div class="fw-bold">Jalur Approval Biaya: <span id="GATierLabel">{{ $tierPreview['tier_label'] ?? '-' }}</span></div>
                                <div id="GATierStages">{{ implode(' | ', $tierPreview['stages'] ?? []) }}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Catatan Verifikasi GA <span class="text-muted">(opsional)</span></label>
                                <textarea name="GA_Verification_Note" class="form-control" rows="2" maxlength="2000" placeholder="Rincian survei harga / RAB / hasil pengecekan GA">{{ $ticket->GA_Verification_Note }}</textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Verifikasi &amp; Ajukan Approval Biaya</button>
                </form>
            </div>
        </div>
    @endif

    @if ($isVoucherMode && $status === 'READY_FOR_VOUCHER' && empty($voucher) && $canVerifyOrVoucher)
        <div class="card mt-3 border-success">
            <div class="card-header bg-success text-white"><h5 class="card-title m-1 text-white">Buat Voucher Pembayaran &amp; Kirim ke Finance</h5></div>
            <div class="card-body">
                <form id="GACreateVoucherForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Tipe Penerima</label>
                                <select name="Payee_Type" class="form-control">
                                    <option value="VENDOR" selected>Vendor / Supplier</option>
                                    <option value="INTERNAL">Internal / Reimbursement</option>
                                    <option value="STAFF">Karyawan / PIC</option>
                                    <option value="OTHER">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label>Pilih Vendor Master <span class="text-muted">(auto-fill bank)</span></label>
                                <select name="Supplier_ID" id="GAVoucherSupplierSelect" class="form-control">
                                    <option value="">-- pilih vendor atau isi manual --</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->ID }}"
                                            data-company="{{ $vendor->Company_Name }}"
                                            data-bank="{{ $vendor->Bank_Rupiah }}"
                                            data-account="{{ $vendor->Bank_Rupiah_Account }}"
                                            data-account-name="{{ $vendor->Bank_Rupiah_Name }}"
                                            {{ (int) $ticket->Preferred_Vendor_ID === (int) $vendor->ID ? 'selected' : '' }}>
                                            {{ $vendor->Company_Name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Nama Penerima / Vendor <span class="text-danger">*</span></label>
                                <input type="text" name="Payee_Name" id="GAVoucherPayeeName" class="form-control" maxlength="150" value="{{ $prefVendorObj ? $prefVendorObj->Company_Name : '' }}" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Jalur Voucher (Usulan ke Finance)</label>
                                <select name="Voucher_Channel" class="form-control">
                                    <option value="DIRECT" selected>Pembayaran Langsung (Cash/Bank)</option>
                                    <option value="AP">Hutang Dagang (AP / Tenor)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Metode Bayar</label>
                                <select name="Payment_Method" class="form-control">
                                    <option value="TRANSFER" selected>Transfer Bank</option>
                                    <option value="CASH">Cash</option>
                                    <option value="GIRO">Giro / Cek</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Nama Bank</label>
                                <input type="text" name="Bank_Name" id="GAVoucherBankName" class="form-control" maxlength="100" value="{{ $prefVendorObj ? ($prefVendorObj->Bank_Rupiah ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>No. Rekening</label>
                                <input type="text" name="Bank_Account_No" id="GAVoucherBankAccountNo" class="form-control" maxlength="80" value="{{ $prefVendorObj ? ($prefVendorObj->Bank_Rupiah_Account ?? '') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Atas Nama Rekening</label>
                                <input type="text" name="Bank_Account_Name" id="GAVoucherBankAccountName" class="form-control" maxlength="150" value="{{ $prefVendorObj ? ($prefVendorObj->Bank_Rupiah_Name ?? $prefVendorObj->Company_Name) : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Nominal Voucher (IDR) <span class="text-danger">*</span></label>
                                <input type="number" name="Amount" class="form-control" min="1" step="any" max="{{ (float) ($ticket->Verified_Amount ?: $ticket->Estimated_Amount) }}" value="{{ (float) ($ticket->Verified_Amount ?: $ticket->Estimated_Amount) }}" required />
                                <small class="text-muted">Maks. Rp {{ number_format((float) ($ticket->Verified_Amount ?: $ticket->Estimated_Amount), 0, ',', '.') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Jatuh Tempo Pembayaran</label>
                                <input type="date" name="Due_Date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Link Work Order <span class="text-muted">(opsional)</span></label>
                                <select name="Work_Order_ID" class="form-control">
                                    <option value="">-- tanpa WO --</option>
                                    @foreach ($workOrders as $wo)
                                        <option value="{{ $wo->ID }}">{{ $wo->WO_No }} - {{ $wo->Title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Lampiran Invoice / Penawaran</label>
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Keterangan Voucher</label>
                                <textarea name="Description" class="form-control" rows="2" maxlength="2000">{{ $ticket->Ticket_Subject }}</textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Terbitkan Voucher &amp; Kirim ke Finance</button>
                </form>
            </div>
        </div>
    @endif

    @if (!empty($voucher))
        <div class="card mt-3 border-info">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="card-title m-1">Informasi Voucher Pembayaran &mdash; {{ $voucher->Voucher_No }}</h5>
                <span class="badge {{ strtoupper((string) $voucher->Status) === 'PAID' ? 'bg-success text-white' : 'bg-info text-white' }}">{{ $voucher->Status }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th style="width:38%;">Penerima / Payee</th><td><strong>{{ $voucher->Payee_Name }}</strong> ({{ $voucher->Payee_Type }})</td></tr>
                            <tr><th>Rekening Transfer</th><td>{{ $voucher->Payment_Method }} &bull; {{ $voucher->Bank_Name ?: '-' }} {{ $voucher->Bank_Account_No ?: '' }} {{ $voucher->Bank_Account_Name ? '(' . $voucher->Bank_Account_Name . ')' : '' }}</td></tr>
                            <tr><th>Nominal Voucher</th><td class="fw-bold text-success">Rp {{ number_format((float) $voucher->Amount, 0, ',', '.') }}</td></tr>
                            <tr><th>Jatuh Tempo</th><td>{{ $voucher->Due_Date ?: '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th style="width:38%;">Dibuat Oleh</th><td>{{ $voucher->Created_By ?: '-' }} ({{ $voucher->Created_At }})</td></tr>
                            <tr><th>Tanggal Bayar Finance</th><td>{{ $voucher->Paid_Date ?: '-' }}</td></tr>
                            <tr><th>Referensi Bayar</th><td>{{ $voucher->Payment_Ref ?: '-' }}</td></tr>
                            <tr><th>Diproses Finance</th><td>{{ $voucher->Paid_By ?: '-' }} {{ $voucher->Paid_At ? '(' . $voucher->Paid_At . ')' : '' }}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="{{ url('hrd-ga-ticket-voucher-print?voucher_id=' . (int) $voucher->ID) }}" target="_blank" class="btn btn-sm btn-outline-primary">Cetak Voucher</a>
                    @if (!empty($voucher->Attachment_Path))
                        <a href="{{ asset('filevendor/' . $voucher->Attachment_Path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lampiran Invoice</a>
                    @endif
                    @if (!empty($voucher->Payment_Proof_Path))
                        <a href="{{ asset('filevendor/' . $voucher->Payment_Proof_Path) }}" target="_blank" class="btn btn-sm btn-outline-success">Bukti Bayar Finance</a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if (!$isVoucherMode && $canEdit && in_array($status, ['OPEN', 'TRIAGED', 'REOPENED'], true))
        <div class="card mt-3" id="GATicketTriageCard" style="display:none;">
            <div class="card-header"><h5 class="card-title m-1">Triage Tiket</h5></div>
            <div class="card-body">
                <form id="GATicketTriageForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Kategori</label>
                                <select name="Category_ID" class="form-control">
                                    <option value="">-- pilih --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->ID }}" {{ (int) $ticket->Category_ID === (int) $category->ID ? 'selected' : '' }}>{{ $category->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Subkategori</label>
                                <select name="Subcategory_ID" class="form-control">
                                    <option value="">-- pilih --</option>
                                    @foreach ($subcategories as $subcategory)
                                        <option value="{{ $subcategory->ID }}" {{ (int) $ticket->Subcategory_ID === (int) $subcategory->ID ? 'selected' : '' }}>{{ $subcategory->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Prioritas</label>
                                <select name="Priority" class="form-control">
                                    @foreach (['LOW', 'NORMAL', 'HIGH', 'URGENT'] as $priority)
                                        <option value="{{ $priority }}" {{ $ticket->Priority === $priority ? 'selected' : '' }}>{{ $priority }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Assign ke Team <span class="text-muted">(opsional)</span></label>
                                <select name="Assignee_Team_ID" class="form-control">
                                    <option value="">-- routing otomatis --</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->ID }}">{{ $team->Name }} ({{ $team->Team_Type }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Assign ke User <span class="text-muted">(opsional)</span></label>
                                <select name="Assignee_User_Id" class="form-control">
                                    <option value="">-- tidak ada --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->Name ?: $user->username }} ({{ $user->Department }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-light border py-2 mb-3 small">
                                Isi salah satu Team atau User. Jika keduanya kosong, sistem memakai routing otomatis.
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Catatan Triage <span class="text-muted">(opsional)</span></label>
                                <textarea name="Triage_Note" class="form-control" rows="2" maxlength="2000"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm text-dark">Simpan Triage</button>
                </form>
            </div>
        </div>
    @endif

    @if (!$isVoucherMode && $canEdit && !in_array($status, ['CLOSED', 'REJECTED'], true))
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title m-1">Assign / Reassign</h5></div>
            <div class="card-body">
                <form id="GATicketAssignForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Tipe Assignee <span class="text-danger">*</span></label>
                                <select name="Assignee_Type" id="GATicketAssigneeType" class="form-control" required>
                                    <option value="TEAM">Team</option>
                                    <option value="USER">User</option>
                                    <option value="VENDOR">Vendor</option>
                                </select>
                                <small class="form-text text-muted">Pilih tipe terlebih dahulu. Field wajib akan muncul di samping.</small>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-3 GAAssigneeField" data-assignee-type="TEAM">
                                <label>Team <span class="text-danger">*</span></label>
                                <select name="Assignee_Team_ID" class="form-control">
                                    <option value="">-- pilih team --</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->ID }}">{{ $team->Name }} ({{ $team->Team_Type }})</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Wajib jika tipe assignee = Team.</small>
                            </div>
                            <div class="form-group mb-3 GAAssigneeField" data-assignee-type="USER" style="display:none;">
                                <label>User <span class="text-danger">*</span></label>
                                <select name="Assignee_User_Id" class="form-control">
                                    <option value="">-- pilih user --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->Name ?: $user->username }} ({{ $user->Department }})</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Wajib jika tipe assignee = User.</small>
                            </div>
                            <div class="form-group mb-3 GAAssigneeField" data-assignee-type="VENDOR" style="display:none;">
                                <label>Vendor <span class="text-danger">*</span></label>
                                <select name="Assignee_Supplier_ID" class="form-control">
                                    <option value="">-- pilih vendor --</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->ID }}">{{ $vendor->Company_Name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Wajib jika tipe assignee = Vendor.</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Alasan <span class="text-muted">(opsional)</span></label>
                                <input type="text" name="Reason" class="form-control" maxlength="2000" placeholder="Contoh: eskalasi ke tim terkait" />
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Assignment</button>
                </form>
            </div>
        </div>
    @endif

    @if ($canEdit && !empty($allowedTransitions))
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title m-1">Ubah Status</h5></div>
            <div class="card-body">
                <form id="GATicketTransitionForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Status Baru</label>
                                <select name="status" class="form-control">
                                    @foreach ($allowedTransitions as $transition)
                                        <option value="{{ $transition }}">{{ $transition }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label>Catatan <span class="text-danger GATicketNoteRequired" style="display:none;">*</span> <span class="text-muted">(wajib untuk RESOLVED/REJECTED)</span></label>
                                <input type="text" name="note" id="GATicketStatusNote" class="form-control" maxlength="5000" placeholder="Wajib untuk RESOLVED/REJECTED" />
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Simpan Status</button>
                </form>
            </div>
        </div>
    @endif

    <div class="card mt-3">
        <div class="card-header"><h5 class="card-title m-1">Komentar</h5></div>
        <div class="card-body">
            <form id="GATicketCommentForm">
                @csrf
                <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                <div class="form-group mb-3">
                    <textarea name="Comment" class="form-control" rows="2" maxlength="5000" required></textarea>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="Is_Internal" value="1" class="form-check-input" id="GATicketInternalComment" />
                    <label class="form-check-label" for="GATicketInternalComment">Catatan internal</label>
                </div>
                <button type="submit" class="btn btn-info btn-sm text-white">Kirim Komentar</button>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <ul class="nav nav-tabs" id="GATicketDetailTabs" role="tablist">
                <li class="nav-item" role="presentation"><a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" href="#GATabHistory" role="tab">History</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" data-toggle="tab" href="#GATabApproval" role="tab">Approval Timeline</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" data-toggle="tab" href="#GATabAttachments" role="tab">Lampiran ({{ isset($attachments) ? $attachments->count() : 0 }})</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" data-toggle="tab" href="#GATabComments" role="tab">Komentar ({{ $comments->count() }})</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" data-toggle="tab" href="#GATabAssignments" role="tab">Assignment ({{ $assignments->count() }})</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" data-toggle="tab" href="#GATabWorkOrders" role="tab">Work Order ({{ $workOrders->count() }})</a></li>
            </ul>
            <div class="tab-content pt-3" id="GATicketDetailTabContent">
                <div class="tab-pane fade show active" id="GATabHistory" role="tabpanel">
                    <table class="table table-sm">
                        <thead><tr><th>Waktu</th><th>Dari</th><th>Ke</th><th>Oleh</th><th>Catatan</th></tr></thead>
                        <tbody>
                        @foreach ($history as $row)
                            <tr>
                                <td>{{ $row->Changed_At }}</td>
                                <td>{{ $row->From_Status ?: '-' }}</td>
                                <td>{{ $row->To_Status }}</td>
                                <td>{{ $row->Changed_By }}</td>
                                <td>{{ $row->Note }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="GATabApproval" role="tabpanel">
                    @php
                        $deptTimeline = $approvalTimelines['dept_head'] ?? null;
                        $budgetRevs = $approvalTimelines['budget_revisions'] ?? [];
                    @endphp
                    @if (!$deptTimeline && empty($budgetRevs))
                        <div class="text-muted small">Tiket ini tidak memiliki alur approval berjenjang.</div>
                    @endif
                    @if ($deptTimeline)
                        <h6 class="fw-bold text-primary">Fase 1: Approval Dept Head ({{ $deptTimeline['request']->Request_No }}) &mdash; <span class="badge bg-secondary text-white">{{ $deptTimeline['request']->Status }}</span></h6>
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="table-light"><tr><th>Stage</th><th>Posisi / Dept</th><th>Status</th><th>Waktu Aksi / Catatan</th></tr></thead>
                            <tbody>
                                @foreach ($deptTimeline['tasks'] as $t)
                                    <tr>
                                        <td>Stage {{ $t->Stage_Order }}</td>
                                        <td>{{ $t->Position_Name ?: ($t->Position_Code ?: '-') }} ({{ $t->Department ?: '-' }})</td>
                                        <td><span class="badge bg-info text-white">{{ $t->Status }}</span></td>
                                        <td>{{ $t->Acted_At ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (!empty($deptTimeline['logs']) && $deptTimeline['logs']->count() > 0)
                            <div class="small mb-3">
                                <strong>Log Fase 1:</strong>
                                @foreach ($deptTimeline['logs'] as $log)
                                    <div>&bull; [{{ $log->Action_Date }}] {{ $log->Actor_Name ?: ($log->Actor_Username ?: 'System') }} &rarr; <strong>{{ $log->Action }}</strong> {{ $log->Notes ? '(' . $log->Notes . ')' : '' }}</div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                    @foreach ($budgetRevs as $revTimeline)
                        <h6 class="fw-bold text-primary">Fase 2: Approval Biaya {{ $revTimeline['request']->Request_No }} (Rev #{{ (int) ($revTimeline['request']->Revision_No ?? 1) }}) &mdash; <span class="badge bg-secondary text-white">{{ $revTimeline['request']->Status }}</span></h6>
                        <div class="small text-muted mb-1">Nominal diajukan: Rp {{ number_format((float) ($revTimeline['request']->Amount ?? 0), 0, ',', '.') }}</div>
                        <table class="table table-sm table-bordered mb-2">
                            <thead class="table-light"><tr><th>Stage</th><th>Approver / Posisi</th><th>Status</th><th>Waktu</th></tr></thead>
                            <tbody>
                                @foreach ($revTimeline['tasks'] as $t)
                                    <tr>
                                        <td>Stage {{ $t->Stage_Order }}</td>
                                        <td>{{ $t->Position_Name ?: ($t->Position_Code ?: '-') }} ({{ $t->Department ?: '-' }})</td>
                                        <td><span class="badge bg-info text-white">{{ $t->Status }}</span></td>
                                        <td>{{ $t->Acted_At ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (!empty($revTimeline['logs']) && $revTimeline['logs']->count() > 0)
                            <div class="small mb-3">
                                @foreach ($revTimeline['logs'] as $log)
                                    <div>&bull; [{{ $log->Action_Date }}] {{ $log->Actor_Name ?: ($log->Actor_Username ?: 'System') }} &rarr; <strong>{{ $log->Action }}</strong> {{ $log->Notes ? '(' . $log->Notes . ')' : '' }}</div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="tab-pane fade" id="GATabAttachments" role="tabpanel">
                    @if (!isset($attachments) || $attachments->isEmpty())
                        <div class="text-muted small">Belum ada lampiran.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($attachments as $att)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $att->File_Name }}</strong>
                                        <small class="text-muted ms-2">{{ $att->Uploaded_By }} &bull; {{ $att->Created_At }}</small>
                                    </div>
                                    <a href="{{ url('hrd-ga-ticket-attachment?attachment_id=' . (int) $att->ID) }}" target="_blank" class="btn btn-sm btn-outline-primary">Unduh</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="tab-pane fade" id="GATabComments" role="tabpanel">
                    @foreach ($comments as $comment)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ $comment->User_Name ?: $comment->Created_By }}</strong>
                            <small class="text-muted">{{ $comment->Created_At }}</small>
                            @if ($comment->Is_Internal)<span class="badge badge-warning bg-warning text-dark">INTERNAL</span>@endif
                            <div>{{ $comment->Comment }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="tab-pane fade" id="GATabAssignments" role="tabpanel">
                    <table class="table table-sm">
                        <thead><tr><th>Waktu</th><th>Tipe</th><th>Assignee</th><th>Oleh</th><th>Alasan</th><th>Current</th></tr></thead>
                        <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->Assigned_At }}</td>
                                <td>{{ $assignment->Assignee_Type }}</td>
                                <td>{{ $assignment->User_Name ?: ($assignment->Team_Name ?: ($assignment->Vendor_Name ?: '-')) }}</td>
                                <td>{{ $assignment->Assigned_By }}</td>
                                <td>{{ $assignment->Reason }}</td>
                                <td>{{ $assignment->Is_Current ? 'YES' : 'NO' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="GATabWorkOrders" role="tabpanel">
                    <table class="table table-sm">
                        <thead><tr><th>WO No</th><th>Judul</th><th>Status</th><th>Vendor Cost</th><th>Dibuat</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($workOrders as $wo)
                            <tr>
                                <td>{{ $wo->WO_No }}</td>
                                <td>{{ $wo->Title }}</td>
                                <td>{{ $wo->Status }}</td>
                                <td>{{ number_format((float) $wo->Vendor_Cost, 2, ',', '.') }}</td>
                                <td>{{ $wo->Created_At }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-primary" onclick="LoadM('GAWORKORDERFORM', 'hrd-ga-work-order-detail?ID={{ $wo->ID }}')">Detail</button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var ticketId = {{ (int) $ticket->ID }};

    $('#GATicketDetailTabs a[href^="#"]').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('#GATicketDetailTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        $('#GATicketDetailTabContent > .tab-pane').removeClass('show active');
        $(target).addClass('show active');
    });

    window.GATicketShowTriage = function () {
        $('#GATicketTriageCard').toggle();
    };

    window.GATicketOpenWorkOrder = function () {
        LoadM('GAWORKORDERFORM', 'hrd-ga-work-order-create?ticket_id=' + ticketId);
    };

    window.GATicketCloseDetail = function () {
        if ($('#GATICKETDTL').length) {
            $('#GATICKETDTL').empty();
        } else if ($('#GAMYASSIGNDTL').length) {
            $('#GAMYASSIGNDTL').empty();
        } else {
            window.location.href = '{{ url('hrd-ga-ticket-index') }}';
        }
    };

    function syncAssigneeFields() {
        var type = $('#GATicketAssigneeType').val();
        $('.GAAssigneeField').each(function () {
            var field = $(this);
            var active = field.data('assignee-type') === type;
            field.toggle(active);
            field.find('select').prop('required', active);
            if (!active) {
                field.find('select').val('');
            }
        });
    }

    $('#GATicketAssigneeType').on('change', syncAssigneeFields);
    syncAssigneeFields();

    function syncStatusNote() {
        var status = $('#GATicketTransitionForm select[name=status]').val();
        var required = status === 'RESOLVED' || status === 'REJECTED';
        $('#GATicketStatusNote').prop('required', required);
        $('.GATicketNoteRequired').toggle(required);
    }

    $('#GATicketTransitionForm select[name=status]').on('change', syncStatusNote);
    syncStatusNote();

    function afterChange(message) {
        alert(message);
        if (window.GATicketReloadDetail) {
            window.GATicketReloadDetail(ticketId);
        } else {
            location.reload();
        }
        if (window.GATicketRefreshGrid) {
            window.GATicketRefreshGrid();
        }
    }

    function postForm(formSelector, url, successMessage) {
        var form = $(formSelector);
        var button = form.find('button[type=submit]');
        button.prop('disabled', true);
        $.post(url, form.serialize(), function (response) {
            if (response.status === 'success') {
                afterChange(response.message || successMessage);
            } else {
                alert(response.message || 'Gagal memproses.');
            }
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memproses.');
        }).always(function () {
            button.prop('disabled', false);
        });
    }

    $('#GATicketTriageForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GATicketTriageForm', 'hrd-ga-ticket-triage', 'Triage disimpan.');
    });

    $('#GATicketAssignForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GATicketAssignForm', 'hrd-ga-ticket-assign', 'Assignment disimpan.');
    });

    $('#GATicketTransitionForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GATicketTransitionForm', 'hrd-ga-ticket-status', 'Status diperbarui.');
    });

    $('#GATicketCommentForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GATicketCommentForm', 'hrd-ga-ticket-comment', 'Komentar dikirim.');
    });

    window.GATicketSyncApproval = function () {
        $.post('hrd-ga-ticket-approval-sync', {_token: '{{ csrf_token() }}', ID: ticketId}, function (response) {
            afterChange(response.message || 'Status disinkronkan.');
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal sinkronisasi approval.');
        });
    };

    function updateTierPreview() {
        var input = $('#GAVerifiedAmountInput');
        if (!input.length) return;
        var amount = parseFloat(input.val() || '0');
        var label = '< Rp 3.000.000 (1 Tahap)';
        var stages = '1. HCGA Manager (MHCGA)';
        if (amount >= 3000000 && amount <= 5000000) {
            label = 'Rp 3.000.000 - Rp 5.000.000 (3 Tahap)';
            stages = '1. HCGA Manager (MHCGA) | 2. FAT Manager (MFA) | 3. VP FAT (VPFAT)';
        } else if (amount > 5000000) {
            label = '> Rp 5.000.000 (4 Tahap)';
            stages = '1. HCGA Manager (MHCGA) | 2. FAT Manager (MFA) | 3. VP FAT (VPFAT) | 4. BOD (VPD)';
        }
        $('#GATierLabel').text(label);
        $('#GATierStages').text(stages);
    }

    $('#GAVerifiedAmountInput').on('input change', updateTierPreview);
    updateTierPreview();

    $('#GAVerifyForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GAVerifyForm', 'hrd-ga-ticket-verify', 'Verifikasi GA disimpan.');
    });

    $('#GAResubmitDeptForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GAResubmitDeptForm', 'hrd-ga-ticket-resubmit-dept', 'Order diajukan ulang ke Dept Head.');
    });

    $('#GAVoucherSupplierSelect').on('change', function () {
        var opt = $(this).find('option:selected');
        if (opt.val()) {
            $('#GAVoucherPayeeName').val(opt.data('company') || '');
            $('#GAVoucherBankName').val(opt.data('bank') || '');
            $('#GAVoucherBankAccountNo').val(opt.data('account') || '');
            $('#GAVoucherBankAccountName').val(opt.data('account-name') || opt.data('company') || '');
        }
    });

    $('#GACreateVoucherForm').on('submit', function (event) {
        event.preventDefault();
        var form = $(this);
        var button = form.find('button[type=submit]');
        var fd = new FormData(form[0]);
        button.prop('disabled', true);
        $.ajax({
            url: 'hrd-ga-ticket-voucher-create',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'success') {
                    afterChange(response.message || 'Voucher dibuat.');
                } else {
                    alert(response.message || 'Gagal membuat voucher.');
                }
            },
            error: function (xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal membuat voucher.');
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });
})();
</script>
