<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Form Tiket GA</h4>
        </div>
        <div class="card-body">
            @if (!empty($qrPoint))
                <div class="alert alert-info">
                    <div><strong>QR Point: {{ $qrPoint->Name }}</strong> ({{ $qrPoint->QR_Code }})</div>
                    <div class="small mt-1">
                        {{ $qrPoint->Point_Type }}
                        @if ($qrPoint->Building) | {{ $qrPoint->Building }} @endif
                        @if ($qrPoint->Floor) | Lt. {{ $qrPoint->Floor }} @endif
                        @if ($qrPoint->Room) | {{ $qrPoint->Room }} @endif
                    </div>
                    <div class="small text-muted mt-1">Lokasi dan perusahaan mengikuti QR point. Anda tetap dapat mengubahnya bila perlu.</div>
                </div>
            @endif
            <form id="GATicketForm" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <input type="hidden" name="QR_Point_ID" value="{{ $qrPointId ?: '' }}" />
                <input type="hidden" name="Source" value="{{ !empty($qrPoint) ? 'QR' : 'FORM' }}" />
                <input type="hidden" name="Company_ID" value="{{ $qrPoint && $qrPoint->Company_ID ? $qrPoint->Company_ID : auth()->user()->Company_ID }}" />
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Tipe Alur Tiket <span class="text-danger">*</span></label>
                            <select name="Workflow_Mode" id="GATicketWorkflowMode" class="form-control" required>
                                <option value="OPERATIONAL" selected>Tiket Operasional GA</option>
                                <option value="WORK_ORDER_VOUCHER">Order Pekerjaan &amp; Voucher Pembayaran</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-3">
                            <label>Subjek <span class="text-danger">*</span></label>
                            <input type="text" name="Ticket_Subject" class="form-control" maxlength="250" required />
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
                    <div class="col-md-12" id="GAVoucherModeFields" style="display:none;">
                        <div class="alert alert-info py-2 mb-3">
                            <strong>Alur Order Pekerjaan &amp; Voucher Pembayaran:</strong>
                            Approval Dept Head Pemohon &rarr; GA Verification (finalisasi biaya &amp; vendor) &rarr; Approval Biaya Berjenjang (&lt;3jt HCGA Mgr; 3&ndash;5jt +FAT Mgr +VP FAT; &gt;5jt +BOD) &rarr; Pembuatan Voucher Pembayaran &rarr; Pelunasan Finance.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Estimasi Biaya Awal (IDR) <span class="text-muted">(opsional, akan difinalisasi GA)</span></label>
                                    <input type="number" name="Estimated_Amount" id="GATicketEstimatedAmount" class="form-control" min="0" step="1000" placeholder="0" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Rekomendasi Vendor / Rekanan <span class="text-muted">(opsional)</span></label>
                                    <select name="Preferred_Vendor_ID" class="form-control">
                                        <option value="">-- pilih vendor --</option>
                                        @foreach ($vendors ?? [] as $vendor)
                                            <option value="{{ $vendor->ID }}">{{ $vendor->Company_Name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Kategori</label>
                            <select name="Category_ID" id="GATicketCategory" class="form-control">
                                <option value="">-- pilih kategori --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->ID }}">{{ $category->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Subkategori</label>
                            <select name="Subcategory_ID" id="GATicketSubcategory" class="form-control">
                                <option value="">-- pilih subkategori --</option>
                                @foreach ($subcategories as $subcategory)
                                    <option value="{{ $subcategory->ID }}" data-parent="{{ $subcategory->Parent_ID }}">{{ $subcategory->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label>Deskripsi <span class="text-danger">*</span></label>
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
                            <label>Telepon Reporter</label>
                            <input type="text" name="Reporter_Phone" class="form-control" maxlength="50" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Cost Center <span class="text-muted">(opsional)</span></label>
                            <input type="text" name="Cost_Center" class="form-control" maxlength="50" value="{{ auth()->user()->Department }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Department <span class="text-muted">(opsional)</span></label>
                            <input type="text" name="Department" class="form-control" maxlength="100" value="{{ auth()->user()->Department }}" />
                        </div>
                    </div>
                    <div class="col-md-4" id="GAOperationalAssigneeWrap">
                        <div class="form-group mb-3">
                            <label>Assignee (opsional)</label>
                            <select name="Assignee_User_Id" class="form-control">
                                <option value="">-- routing otomatis --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->Name ?: $user->username }} ({{ $user->Department }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Lokasi <span class="text-danger">*</span></label>
                            <input type="text" name="Location" class="form-control" value="{{ $qrPoint && $qrPoint->Location ? $qrPoint->Location : auth()->user()->Location }}" maxlength="50" required />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label>Lampiran Pendukung <span class="text-muted">(opsional, maks. 5 file, @5MB: pdf/jpg/png/doc/xls)</span></label>
                            <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" />
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Simpan Tiket</button>
                    <button type="button" class="btn btn-secondary" onclick="GATicketCancelForm()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    window.GATicketCancelForm = function () {
        if ($('#GATICKETDTL1').length) {
            $('#GATICKETDTL1').empty();
        } else {
            window.location.href = '{{ url('hrd-ga-ticket-index') }}';
        }
    };

    var subcategoryOptions = $('#GATicketSubcategory option').clone();

    function filterSubcategories() {
        var parent = $('#GATicketCategory').val();
        var currentVal = $('#GATicketSubcategory').val();
        $('#GATicketSubcategory').empty().append(subcategoryOptions.filter(function () {
            var dataParent = $(this).attr('data-parent');
            return !dataParent || !parent || String(dataParent) === String(parent);
        }));
        if ($('#GATicketSubcategory option[value="' + currentVal + '"]').length) {
            $('#GATicketSubcategory').val(currentVal);
        } else {
            $('#GATicketSubcategory').val('');
        }
    }

    function syncWorkflowMode() {
        var isVoucher = $('#GATicketWorkflowMode').val() === 'WORK_ORDER_VOUCHER';
        $('#GAVoucherModeFields').toggle(isVoucher);
        $('#GAOperationalAssigneeWrap').toggle(!isVoucher);
    }

    $('#GATicketCategory').on('change', filterSubcategories);
    $('#GATicketWorkflowMode').on('change', syncWorkflowMode);
    syncWorkflowMode();

    $('#GATicketForm').on('submit', function (event) {
        event.preventDefault();
        var form = $(this);
        var button = form.find('button[type=submit]');
        var fd = new FormData(form[0]);
        button.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: 'hrd-ga-ticket-submit',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'success') {
                    alert(response.message + ' No: ' + response.ticket_no);
                    if ($('#GATICKETDTL1').length) {
                        form[0].reset();
                        syncWorkflowMode();
                        $('#GATICKETDTL1').empty();
                        if (typeof GATicket !== 'undefined') {
                            GATicket.clearAndLoad('hrd-ga-ticket-data');
                        }
                    } else {
                        window.location.href = '{{ url('hrd-ga-ticket-detail') }}?ID=' + encodeURIComponent(response.ticket_id);
                    }
                } else {
                    alert(response.message || 'Gagal menyimpan tiket.');
                }
            },
            error: function (xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan tiket.';
                alert(message);
            },
            complete: function () {
                button.prop('disabled', false).text('Simpan Tiket');
            }
        });
    });
})();
</script>
