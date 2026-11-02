@php
    $status = strtoupper((string) $wo->Status);
    $badge = [
        'DRAFT' => 'secondary', 'OPEN' => 'info', 'WAITING_APPROVAL' => 'warning',
        'APPROVED' => 'success', 'REJECTED' => 'danger', 'ASSIGNED' => 'primary',
        'IN_PROGRESS' => 'warning', 'COMPLETED' => 'success', 'CANCELLED' => 'dark',
    ][$status] ?? 'secondary';
    $canEdit = can_menu('SI01010', 'edit');
    $canApprove = can_menu('SI01010', 'approve');
@endphp

<div class="mt-3" id="GAWODetailContainer">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-2">Work Order {{ $wo->WO_No }}</h4>
            <span class="badge badge-{{ $badge }} bg-{{ $badge }} {{ $badge === 'warning' ? 'text-dark' : 'text-white' }} p-2">{{ $status }}</span>
        </div>
        <div class="card-body">
            <input type="hidden" id="GAWODetailID" value="{{ $wo->ID }}" />
            <div class="row">
                <div class="col-md-8">
                    <h5>{{ $wo->Title }}</h5>
                    <p class="text-muted mb-1">
                        Tiket:
                        @if ($wo->Ticket_ID)
                            <a href="#" onclick="GAWOOpenTicket({{ (int) $wo->Ticket_ID }}); return false;">{{ $wo->Ticket_No }}</a>
                            - {{ $wo->Ticket_Subject }}
                        @else
                            -
                        @endif
                    </p>
                    <p>{{ $wo->Description }}</p>
                    @if ($wo->Result_Note)
                        <div class="alert alert-success"><strong>Hasil:</strong> {{ $wo->Result_Note }}</div>
                    @endif
                </div>
                <div class="col-md-4">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Assignee</th><td>{{ $wo->Assignee_Name ?: ($wo->Team_Name ?: ($wo->Vendor_Name ?: '-')) }}</td></tr>
                        <tr><th>Vendor</th><td>{{ $wo->Vendor_Name ?: '-' }}</td></tr>
                        <tr><th>Planned</th><td>{{ $wo->Planned_Start ?: '-' }} s/d {{ $wo->Planned_End ?: '-' }}</td></tr>
                        <tr><th>Actual</th><td>{{ $wo->Actual_Start ?: '-' }} s/d {{ $wo->Actual_End ?: '-' }}</td></tr>
                        <tr><th>Vendor Cost</th><td>{{ number_format((float) $wo->Vendor_Cost, 2, ',', '.') }}</td></tr>
                        <tr><th>Total Cost</th><td><strong>{{ number_format((float) $totalCost, 2, ',', '.') }}</strong></td></tr>
                        <tr><th>Approval Request</th><td>{{ $wo->Approval_Request_ID ?: '-' }}</td></tr>
                    </table>
                </div>
            </div>

            @php
                $ticketStatus = strtoupper((string) $wo->Ticket_Status);
            @endphp
            @if ($status === 'COMPLETED' && $wo->Ticket_ID)
                @if ($ticketStatus === 'RESOLVED')
                    <div class="alert alert-warning mt-3">
                        <strong>Tiket menunggu closing.</strong> Work order sudah selesai dan tiket otomatis berstatus <strong>RESOLVED</strong>.
                        Tim GA perlu menutup tiket melalui Detail Tiket (RESOLVED -&gt; CLOSED).
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-primary" onclick="GAWOOpenTicket({{ (int) $wo->Ticket_ID }})">Buka Detail Tiket</button>
                        </div>
                    </div>
                @elseif ($ticketStatus === 'CLOSED')
                    <div class="alert alert-success mt-3">Work order selesai dan tiket sudah <strong>CLOSED</strong>.</div>
                @endif
            @endif

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($canApprove && !in_array($status, ['WAITING_APPROVAL', 'APPROVED', 'COMPLETED', 'CANCELLED'], true))
                    <button type="button" class="btn btn-warning btn-sm text-dark" onclick="GAWOSubmitApproval()">Ajukan Approval</button>
                @endif
                @if ($wo->Approval_Request_ID)
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="GAWOSyncApproval()">Sync Status Approval</button>
                @endif
                <button type="button" class="btn btn-secondary btn-sm" onclick="GAWOCloseDetail()">Tutup</button>
            </div>
        </div>
    </div>

    @if ($canEdit && !empty($allowedTransitions))
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title m-1">Ubah Status Work Order</h5></div>
            <div class="card-body">
                <form id="GAWOTransitionForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $wo->ID }}" />
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
                                <label>Catatan</label>
                                <input type="text" name="note" class="form-control" maxlength="5000" placeholder="Wajib untuk COMPLETED" />
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Simpan Status</button>
                </form>
            </div>
        </div>
    @endif

    @if ($canEdit && !in_array($status, ['COMPLETED', 'CANCELLED'], true))
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title m-1">Tambah Biaya</h5></div>
            <div class="card-body">
                <form id="GAWOCostForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $wo->ID }}" />
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label>Tipe</label>
                                <select name="Cost_Type" class="form-control">
                                    <option value="MATERIAL">Material</option>
                                    <option value="LABOR">Labor</option>
                                    <option value="VENDOR">Vendor</option>
                                    <option value="OTHER" selected>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label>Deskripsi</label>
                                <input type="text" name="Description" class="form-control" maxlength="200" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Amount</label>
                                <input type="number" step="0.01" min="0" name="Amount" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm mb-3">Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card mt-3">
        <div class="card-body">
            <ul class="nav nav-tabs" id="GAWODetailTabs" role="tablist">
                <li class="nav-item" role="presentation"><a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" href="#GAWOTabMaterials" role="tab">Material ({{ $materials->count() }})</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" data-toggle="tab" href="#GAWOTabCosts" role="tab">Biaya ({{ $costs->count() }})</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" data-toggle="tab" href="#GAWOTabHistory" role="tab">History ({{ $history->count() }})</a></li>
            </ul>
            <div class="tab-content pt-3" id="GAWODetailTabContent">
                <div class="tab-pane fade show active" id="GAWOTabMaterials" role="tabpanel">
                    <table class="table table-sm">
                        <thead><tr><th>Product Code</th><th>Deskripsi</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Total</th><th>Stock Request</th></tr></thead>
                        <tbody>
                        @foreach ($materials as $material)
                            <tr>
                                <td>{{ $material->Product_Code }}</td>
                                <td>{{ $material->Product_Desc }}</td>
                                <td>{{ $material->Qty }}</td>
                                <td>{{ $material->Unit }}</td>
                                <td>{{ number_format((float) $material->Unit_Cost, 2, ',', '.') }}</td>
                                <td>{{ number_format((float) $material->Total_Cost, 2, ',', '.') }}</td>
                                <td>{{ $material->Stock_Request_No ?: '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="GAWOTabCosts" role="tabpanel">
                    <table class="table table-sm">
                        <thead><tr><th>Waktu</th><th>Tipe</th><th>Deskripsi</th><th>Amount</th><th>Oleh</th></tr></thead>
                        <tbody>
                        @foreach ($costs as $cost)
                            <tr>
                                <td>{{ $cost->Created_At }}</td>
                                <td>{{ $cost->Cost_Type }}</td>
                                <td>{{ $cost->Description }}</td>
                                <td>{{ number_format((float) $cost->Amount, 2, ',', '.') }}</td>
                                <td>{{ $cost->Created_By }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="GAWOTabHistory" role="tabpanel">
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
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var woId = {{ (int) $wo->ID }};

    $('#GAWODetailTabs a[href^="#"]').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('#GAWODetailTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        $('#GAWODetailTabContent > .tab-pane').removeClass('show active');
        $(target).addClass('show active');
    });

    window.GAWOOpenTicket = function (ticketId) {
        if ($('#GATICKETDTL').length) {
            LoadM('GATICKETDTL', 'hrd-ga-ticket-detail?partial=1&ID=' + encodeURIComponent(ticketId));
            var target = document.getElementById('GATICKETDTL');
            if (target && target.scrollIntoView) {
                target.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        } else {
            window.open('{{ url('hrd-ga-ticket-detail') }}?ID=' + encodeURIComponent(ticketId), '_blank');
        }
    };

    window.GAWOCloseDetail = function () {
        if ($('#GAWODTL').length) {
            $('#GAWODTL').empty();
        } else if ($('#GAWORKORDERFORM').length) {
            $('#GAWORKORDERFORM').empty();
        } else {
            window.location.href = '{{ url('hrd-ga-work-order-index') }}';
        }
    };

    function afterChange(message) {
        alert(message);
        if (window.GAWOReloadDetail) {
            window.GAWOReloadDetail(woId);
        } else {
            location.reload();
        }
        if (window.GAWORefreshGrid) {
            window.GAWORefreshGrid();
        }
    }

    function postForm(selector, url, successMessage) {
        var form = $(selector);
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

    window.GAWOSubmitApproval = function () {
        $.post('hrd-ga-work-order-approval-submit', {ID: woId, _token: '{{ csrf_token() }}'}, function (response) {
            if (response.status === 'success') {
                afterChange(response.message || 'Diajukan ke approval.');
            } else {
                alert(response.message || 'Gagal mengajukan approval.');
            }
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengajukan approval.');
        });
    };

    window.GAWOSyncApproval = function () {
        $.post('hrd-ga-work-order-approval-sync', {ID: woId, _token: '{{ csrf_token() }}'}, function (response) {
            if (response.status === 'success') {
                afterChange(response.message || 'Status approval disinkronkan.');
            } else {
                alert(response.message || 'Gagal sinkronisasi approval.');
            }
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal sinkronisasi approval.');
        });
    };

    $('#GAWOTransitionForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GAWOTransitionForm', 'hrd-ga-work-order-status', 'Status work order diperbarui.');
    });

    $('#GAWOCostForm').on('submit', function (event) {
        event.preventDefault();
        postForm('#GAWOCostForm', 'hrd-ga-work-order-cost-add', 'Biaya ditambahkan.');
    });
})();
</script>
