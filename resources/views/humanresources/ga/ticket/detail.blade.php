@php
    $status = strtoupper((string) $ticket->Ticket_Status);
    $badge = [
        'OPEN' => 'secondary', 'TRIAGED' => 'info', 'ASSIGNED' => 'primary',
        'IN_PROGRESS' => 'warning', 'ON_HOLD' => 'dark', 'RESOLVED' => 'success',
        'CLOSED' => 'secondary', 'REJECTED' => 'danger', 'REOPENED' => 'warning',
    ][$status] ?? 'secondary';
    $canEdit = can_menu('SI01009', 'edit');
    $canCreateWo = can_menu('SI01010', 'create');
@endphp

<div class="mt-3" id="GATicketDetailContainer">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-2">Detail Tiket {{ $ticket->Ticket_No }}</h4>
            <span class="badge badge-{{ $badge }} bg-{{ $badge }} {{ $badge === 'warning' ? 'text-dark' : 'text-white' }} p-2">{{ $status }}</span>
        </div>
        <div class="card-body">
            <input type="hidden" id="GATicketDetailID" value="{{ $ticket->ID }}" />
            <div class="row">
                <div class="col-md-8">
                    <h5>{{ $ticket->Ticket_Subject }}</h5>
                    <p class="text-muted mb-1">{{ $ticket->Category_Name ?: 'Tanpa kategori' }} {{ $ticket->Subcategory_Name ? '/ ' . $ticket->Subcategory_Name : '' }}</p>
                    <p>{{ $ticket->Ga_Description ?: $ticket->Description }}</p>
                </div>
                <div class="col-md-4">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Prioritas</th><td>{{ $ticket->Priority }}</td></tr>
                        <tr><th>Reporter</th><td>{{ $ticket->Reporter_Name ?: $ticket->Reporter }} ({{ $ticket->Reporter_Department ?: $ticket->Department }})</td></tr>
                        <tr><th>Assignee</th><td>{{ $ticket->Assignee_Name ?: ($ticket->Team_Name ?: ($ticket->Vendor_Name ?: '-')) }}</td></tr>
                        <tr><th>SLA</th><td>{{ $ticket->Sla_Name ?: '-' }}</td></tr>
                        <tr><th>Response Due</th><td>{{ $ticket->Response_Due_At ?: '-' }} @if ($sla['Response_Overdue'])<span class="badge badge-danger bg-danger text-white">OVERDUE</span>@endif</td></tr>
                        <tr><th>Resolve Due</th><td>{{ $ticket->Resolve_Due_At ?: '-' }} @if ($sla['Resolve_Overdue'])<span class="badge badge-danger bg-danger text-white">OVERDUE</span>@endif</td></tr>
                        <tr><th>Dibuat</th><td>{{ $ticket->Created_Date }} oleh {{ $ticket->Created_By }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($canCreateWo && !in_array($status, ['CLOSED', 'REJECTED'], true))
                    <button type="button" class="btn btn-primary btn-sm" onclick="GATicketOpenWorkOrder()">Buat Work Order</button>
                @endif
                @if ($canEdit && !in_array($status, ['CLOSED', 'REJECTED'], true))
                    <button type="button" class="btn btn-warning btn-sm text-dark" onclick="GATicketShowTriage()">Triage</button>
                @endif
                <button type="button" class="btn btn-secondary btn-sm" onclick="GATicketCloseDetail()">Tutup</button>
            </div>
        </div>
    </div>

    <div id="GAWORKORDERFORM"></div>

    @if ($canEdit && in_array($status, ['OPEN', 'TRIAGED', 'REOPENED'], true))
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

    @if ($canEdit && !in_array($status, ['CLOSED', 'REJECTED'], true))
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
})();
</script>
