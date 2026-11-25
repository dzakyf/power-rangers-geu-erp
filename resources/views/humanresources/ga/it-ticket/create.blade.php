@php
    $selectedMode = in_array($defaultMode ?? '', ['IT_SUPPORT', 'IT_INCIDENT', 'IT_DEVICE_REQUEST'], true)
        ? $defaultMode
        : 'IT_SUPPORT';
@endphp
<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Form Pengajuan Tiket IT Support</h4>
        </div>
        <div class="card-body">
            <form id="ITTicketForm" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="Company_ID" value="{{ auth()->user()->Company_ID }}" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Tipe Kebutuhan IT <span class="text-danger">*</span></label>
                            <select name="Workflow_Mode" id="ITTicketWorkflowMode" class="form-control" required>
                                <option value="IT_SUPPORT" {{ $selectedMode === 'IT_SUPPORT' ? 'selected' : '' }}>1. Tiket Bantuan IT (Langsung ke Antrean IT)</option>
                                <option value="IT_INCIDENT" {{ $selectedMode === 'IT_INCIDENT' ? 'selected' : '' }}>2. Gangguan System / Incident (Alert ke Dashboard IT)</option>
                                <!-- option value="IT_DEVICE_REQUEST" {{ $selectedMode === 'IT_DEVICE_REQUEST' ? 'selected' : '' }}>3. Pengajuan Perangkat IT (Lewat Approval Manager)</option -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label>Tipe Tindakan (Action Type) <span class="text-danger">*</span></label>
                            <select name="Action_Type" id="ITTicketActionType" class="form-control" required>
                                <option value="CORRECTIVE" selected>Corrective Action (Perbaikan / Insiden)</option>
                                <option value="PREVENTIVE">Preventive Action (Pencegahan / Berkala)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label>Prioritas</label>
                            <select name="Priority" class="form-control">
                                <option value="LOW">Low</option>
                                <option value="NORMAL" selected>Normal</option>
                                <option value="HIGH">High</option>
                                <option value="URGENT">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12" id="ITDeviceApprovalAlert" style="display:none;">
                        <div class="alert alert-info py-2">
                            <i class="fa fa-info-circle me-1"></i>
                            <strong>Pengajuan Perangkat IT</strong> akan dikirim ke <strong>Approval Manager</strong> terlebih dahulu sebelum diproses oleh tim IT Support.
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label>Subjek <span class="text-danger">*</span></label>
                            <input type="text" name="Ticket_Subject" class="form-control" maxlength="250" placeholder="Contoh: Laptop tidak bisa terhubung VPN / Permintaan Laptop Staff Baru" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Lokasi <span class="text-danger">*</span></label>
                            <input type="text" name="Location" class="form-control" value="{{ auth()->user()->Location }}" maxlength="50" required />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Kategori IT</label>
                            <select name="Category_ID" id="ITTicketCategory" class="form-control">
                                <option value="">-- otomatis sesuai tipe --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->ID }}" data-code="{{ $category->Code }}">{{ $category->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Subkategori IT</label>
                            <select name="Subcategory_ID" id="ITTicketSubcategory" class="form-control">
                                <option value="">-- pilih subkategori --</option>
                                @foreach ($subcategories as $subcategory)
                                    <option value="{{ $subcategory->ID }}" data-parent="{{ $subcategory->Parent_ID }}">{{ $subcategory->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12" id="ITDeviceSpecWrap" style="display:none;">
                        <div class="form-group mb-3">
                            <label>Spesifikasi / Kebutuhan Perangkat IT</label>
                            <textarea name="Device_Spec" class="form-control" rows="2" maxlength="5000" placeholder="Contoh: Laptop RAM 16GB SSD 512GB + Mouse Wireless untuk staff baru Finance"></textarea>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label>Deskripsi Masalah / Kebutuhan <span class="text-danger">*</span></label>
                            <textarea name="Description" class="form-control" rows="4" maxlength="5000" required></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Atas Nama (on behalf)</label>
                            <select name="On_Behalf_Of_User_Id" class="form-control">
                                <option value="">-- diri sendiri --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->Name ?: $user->username }} ({{ $user->Department }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Department</label>
                            <input type="text" name="Department" class="form-control" maxlength="100" value="{{ auth()->user()->Department }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Telepon / Ext Reporter</label>
                            <input type="text" name="Reporter_Phone" class="form-control" maxlength="50" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label>Lampiran Screenshot / Dokumen Pendukung <span class="text-muted">(opsional, maks 5 file @ 5MB)</span></label>
                            <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" />
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Kirim Tiket IT</button>
                    <button type="button" class="btn btn-secondary" onclick="ITTicketCancelForm()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    window.ITTicketCancelForm = function () {
        if ($('#ITTICKETDTL1').length) {
            $('#ITTICKETDTL1').empty();
        } else {
            window.location.href = '{{ url('hrd-it-ticket-index') }}';
        }
    };

    var subcategoryOptions = $('#ITTicketSubcategory option').clone();
    function filterItSubcategories() {
        var parent = $('#ITTicketCategory').val();
        var currentVal = $('#ITTicketSubcategory').val();
        $('#ITTicketSubcategory').empty().append(subcategoryOptions.filter(function () {
            var dataParent = $(this).attr('data-parent');
            return !dataParent || !parent || String(dataParent) === String(parent);
        }));
        if ($('#ITTicketSubcategory option[value="' + currentVal + '"]').length) {
            $('#ITTicketSubcategory').val(currentVal);
        } else {
            $('#ITTicketSubcategory').val('');
        }
    }
    $('#ITTicketCategory').on('change', filterItSubcategories);

    function syncModeFields() {
        var mode = $('#ITTicketWorkflowMode').val();
        var isDev = (mode === 'IT_DEVICE_REQUEST');
        $('#ITDeviceApprovalAlert').toggle(isDev);
        $('#ITDeviceSpecWrap').toggle(isDev);
    }
    $('#ITTicketWorkflowMode').on('change', syncModeFields);
    syncModeFields();

    $('#ITTicketForm').on('submit', function (event) {
        event.preventDefault();
        var formEl = this;
        var button = $(this).find('button[type=submit]');
        var fd = new FormData(formEl);
        button.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: 'hrd-it-ticket-submit',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'success') {
                    alert(response.message + ' No: ' + response.ticket_no);
                    if ($('#ITTICKETDTL1').length) {
                        formEl.reset();
                        $('#ITTICKETDTL1').empty();
                        if (window.ITTicketRefreshGrid) {
                            window.ITTicketRefreshGrid();
                        }
                    } else {
                        window.location.href = '{{ url('hrd-it-ticket-detail') }}?ID=' + encodeURIComponent(response.ticket_id);
                    }
                } else {
                    alert(response.message || 'Gagal menyimpan tiket IT.');
                }
            },
            error: function (xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan tiket IT.';
                alert(message);
            },
            complete: function () {
                button.prop('disabled', false).text('Kirim Tiket IT');
            }
        });
    });
})();
</script>
