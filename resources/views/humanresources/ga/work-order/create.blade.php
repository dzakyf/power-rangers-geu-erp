@php
    $defaultAssigneeType = $ticket ? strtoupper((string) $ticket->Assignee_Type) : '';
    if ($defaultAssigneeType === '' && $ticket) {
        if (!empty($ticket->Assignee_Team_ID)) {
            $defaultAssigneeType = 'TEAM';
        } elseif (!empty($ticket->Assignee_User_Id)) {
            $defaultAssigneeType = 'USER';
        } elseif (!empty($ticket->Assignee_Supplier_ID)) {
            $defaultAssigneeType = 'VENDOR';
        }
    }
    $defaultTeamId = $ticket ? (int) $ticket->Assignee_Team_ID : 0;
    $defaultUserId = $ticket ? (int) $ticket->Assignee_User_Id : 0;
    $defaultSupplierId = $ticket ? (int) $ticket->Assignee_Supplier_ID : 0;
@endphp
<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Form Work Order GA</h4>
        </div>
        <div class="card-body">
            @if ($ticket)
                <div class="alert alert-info">
                    Tiket: <strong>{{ $ticket->Ticket_No }}</strong> - {{ $ticket->Ticket_Subject }}
                    ({{ $ticket->Ticket_Status }})
                </div>
            @endif

            <form id="GAWOForm" autocomplete="off">
                @csrf
                @if ($ticket)
                    <input type="hidden" name="Ticket_ID" value="{{ $ticket->ID }}" />
                @else
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Tiket <span class="text-danger">*</span></label>
                                <select name="Ticket_ID" class="form-control" required>
                                    <option value="">-- pilih tiket GA --</option>
                                    @foreach ($tickets as $ticketOption)
                                        <option value="{{ $ticketOption->ID }}">{{ $ticketOption->Ticket_No }} - {{ $ticketOption->Ticket_Subject }} ({{ $ticketOption->Ticket_Status }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label>Judul <span class="text-danger">*</span></label>
                            <input type="text" name="Title" class="form-control" maxlength="200" value="{{ $ticket ? $ticket->Ticket_Subject : '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Tipe Assignee <span class="text-muted">(opsional)</span></label>
                            <select name="Assignee_Type" id="GAWOAssigneeType" class="form-control">
                                <option value="">-- belum ditentukan --</option>
                                <option value="TEAM" {{ $defaultAssigneeType === 'TEAM' ? 'selected' : '' }}>Team</option>
                                <option value="USER" {{ $defaultAssigneeType === 'USER' ? 'selected' : '' }}>User</option>
                                <option value="VENDOR" {{ $defaultAssigneeType === 'VENDOR' ? 'selected' : '' }}>Vendor</option>
                            </select>
                            <small class="form-text text-muted">Pilih tipe untuk menampilkan field assignee yang sesuai.</small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div id="GAWOAssigneeHint" class="alert alert-light border py-2 mb-3 small">
                            Belum ada assignee. Work order tetap bisa disimpan sebagai draft, lalu di-assign kemudian.
                        </div>
                        <div class="form-group mb-3 GAWOAssigneeField" data-assignee-type="TEAM" style="display:none;">
                            <label>Team <span class="text-danger">*</span></label>
                            <select name="Team_ID" class="form-control">
                                <option value="">-- pilih team --</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->ID }}" {{ $defaultAssigneeType === 'TEAM' && $defaultTeamId === (int) $team->ID ? 'selected' : '' }}>{{ $team->Name }} ({{ $team->Team_Type }})</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Wajib jika tipe assignee = Team.</small>
                        </div>
                        <div class="form-group mb-3 GAWOAssigneeField" data-assignee-type="USER" style="display:none;">
                            <label>User <span class="text-danger">*</span></label>
                            <select name="User_Id" class="form-control">
                                <option value="">-- pilih user --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ $defaultAssigneeType === 'USER' && $defaultUserId === (int) $user->id ? 'selected' : '' }}>{{ $user->Name ?: $user->username }} ({{ $user->Department }})</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Wajib jika tipe assignee = User.</small>
                        </div>
                        <div class="form-group mb-3 GAWOAssigneeField" data-assignee-type="VENDOR" style="display:none;">
                            <label>Vendor <span class="text-danger">*</span></label>
                            <select name="Supplier_ID" class="form-control">
                                <option value="">-- pilih vendor --</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->ID }}" {{ $defaultAssigneeType === 'VENDOR' && $defaultSupplierId === (int) $vendor->ID ? 'selected' : '' }}>{{ $vendor->Company_Name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Wajib jika tipe assignee = Vendor.</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label>Deskripsi Pekerjaan</label>
                            <textarea name="Description" class="form-control" rows="3" maxlength="5000">{{ $ticket ? ($ticket->Ga_Description ?: $ticket->Description) : '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label>Planned Start</label>
                            <input type="date" name="Planned_Start" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label>Planned End</label>
                            <input type="date" name="Planned_End" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label>Vendor Cost</label>
                            <input type="number" step="0.01" min="0" name="Vendor_Cost" class="form-control" value="0" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label>Cost Center</label>
                            <input type="text" name="Cost_Center" class="form-control" maxlength="50" value="{{ $ticket ? $ticket->Cost_Center : '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Department (biaya)</label>
                            <input type="text" name="Department" class="form-control" maxlength="100" value="{{ $ticket ? ($ticket->Ga_Department ?: $ticket->Department) : '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-2 mb-3">
                            <input type="checkbox" name="Submit_Approval" value="1" class="form-check-input" id="GAWOSubmitApproval" />
                            <label class="form-check-label" for="GAWOSubmitApproval">Ajukan approval setelah simpan</label>
                        </div>
                    </div>
                </div>

                <hr />
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Material</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="GAWOAddMaterial()">Tambah Material</button>
                </div>
                <table class="table table-sm mt-2">
                    <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Deskripsi</th>
                        <th style="width:90px">Qty</th>
                        <th style="width:90px">Unit</th>
                        <th style="width:140px">Unit Cost</th>
                        <th style="width:50px"></th>
                    </tr>
                    </thead>
                    <tbody id="GAWOMaterialBody"></tbody>
                </table>

                <hr />
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Biaya Lain</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="GAWOAddCost()">Tambah Biaya</button>
                </div>
                <table class="table table-sm mt-2">
                    <thead>
                    <tr>
                        <th style="width:140px">Tipe Biaya</th>
                        <th>Deskripsi</th>
                        <th style="width:160px">Amount</th>
                        <th style="width:50px"></th>
                    </tr>
                    </thead>
                    <tbody id="GAWOCostBody"></tbody>
                </table>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Simpan Work Order</button>
                    <button type="button" class="btn btn-secondary" onclick="GAWOCancelForm()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var materialIndex = 0;
    var costIndex = 0;
    var assigneeType = $('#GAWOAssigneeType');

    window.GAWOCancelForm = function () {
        if ($('#GAWORKORDERFORM').length && $('#GAWORKORDERFORM').children().length) {
            $('#GAWORKORDERFORM').empty();
        } else if ($('#GAWODTL').length) {
            $('#GAWODTL').empty();
        } else {
            window.location.href = '{{ url('hrd-ga-work-order-index') }}';
        }
    };

    function syncAssigneeFields() {
        var type = assigneeType.val();
        var hasActiveField = false;

        $('.GAWOAssigneeField').each(function () {
            var field = $(this);
            var active = field.data('assignee-type') === type;
            field.toggle(active);
            field.find('select').prop('required', active);
            if (!active) {
                field.find('select').val('');
            } else {
                hasActiveField = true;
            }
        });

        $('#GAWOAssigneeHint').toggle(!hasActiveField);
    }

    assigneeType.on('change', syncAssigneeFields);
    syncAssigneeFields();

    window.GAWOAddMaterial = function () {
        var index = materialIndex++;
        var row = '<tr>' +
            '<td><input type="text" name="materials[' + index + '][Product_Code]" class="form-control form-control-sm" maxlength="60" /></td>' +
            '<td><input type="text" name="materials[' + index + '][Product_Desc]" class="form-control form-control-sm" maxlength="200" /></td>' +
            '<td><input type="number" step="0.01" min="0" name="materials[' + index + '][Qty]" class="form-control form-control-sm" value="0" /></td>' +
            '<td><input type="text" name="materials[' + index + '][Unit]" class="form-control form-control-sm" maxlength="25" /></td>' +
            '<td><input type="number" step="0.01" min="0" name="materials[' + index + '][Unit_Cost]" class="form-control form-control-sm" value="0" /></td>' +
            '<td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove();">&times;</button></td>' +
            '</tr>';
        $('#GAWOMaterialBody').append(row);
    };

    window.GAWOAddCost = function () {
        var index = costIndex++;
        var row = '<tr>' +
            '<td><select name="costs[' + index + '][Cost_Type]" class="form-control form-control-sm">' +
            '<option value="MATERIAL">Material</option><option value="LABOR">Labor</option><option value="VENDOR">Vendor</option><option value="OTHER" selected>Other</option>' +
            '</select></td>' +
            '<td><input type="text" name="costs[' + index + '][Description]" class="form-control form-control-sm" maxlength="200" /></td>' +
            '<td><input type="number" step="0.01" min="0" name="costs[' + index + '][Amount]" class="form-control form-control-sm" value="0" /></td>' +
            '<td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove();">&times;</button></td>' +
            '</tr>';
        $('#GAWOCostBody').append(row);
    };

    $('#GAWOForm').on('submit', function (event) {
        event.preventDefault();
        var form = $(this);
        var button = form.find('button[type=submit]');
        button.prop('disabled', true).text('Menyimpan...');

        $.post('hrd-ga-work-order-submit', form.serialize(), function (response) {
            if (response.status === 'success') {
                alert(response.message + ' No: ' + response.wo_no);
                if (window.GAWOReloadDetail) {
                    window.GAWOReloadDetail(response.wo_id);
                } else if ($('#GAWORKORDERFORM').length) {
                    $('#GAWORKORDERFORM').empty();
                    if (window.GATicketReloadDetail && $('#GATicketDetailID').length) {
                        window.GATicketReloadDetail($('#GATicketDetailID').val());
                    }
                } else {
                    $('#GAWODTL').empty();
                }
                if (window.GAWORefreshGrid) {
                    window.GAWORefreshGrid();
                }
                if (window.GATicketRefreshGrid) {
                    window.GATicketRefreshGrid();
                }
            } else {
                alert(response.message || 'Gagal menyimpan work order.');
            }
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan work order.');
        }).always(function () {
            button.prop('disabled', false).text('Simpan Work Order');
        });
    });
})();
</script>
