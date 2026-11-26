<div class="mt-3">
    <div class="cns-section-header">
        CUSTOMS CLEARANCE - {{ $booking->Booking_No }} ({{ $uploadedCount }}/4 Dokumen)
    </div>
    <div class="card cns-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 pb-2 border-bottom" style="gap: 0.75rem;">
                <div>
                    <div><strong>Contract No:</strong> {{ $contract ? $contract->Contract_No : '-' }}</div>
                    <div><strong>Booking Confirmation:</strong> {{ $booking->Booking_No }}</div>
                </div>
                <div class="text-md-right">
                    <div>
                        <strong>Kelengkapan:</strong>
                        <span class="badge {{ $uploadedCount === 4 ? 'bg-success' : 'bg-warning' }} text-white">
                            {{ $uploadedCount }}/4 Dokumen
                        </span>
                    </div>
                    <small class="text-muted">
                        Terakhir diperbarui: {{ $clearance->updated_by ?: '-' }}
                        {{ $clearance->updated_at ? ' / ' . $clearance->updated_at->format('d F Y H:i') : '' }}
                    </small>
                </div>
            </div>

            <form id="CustomsClearanceForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="booking_id" id="cc_booking_id" value="{{ $booking->ID }}" />

                <div class="row">
                    @foreach($docTypes as $docType => $docLabel)
                        @php
                            $docPath = $clearance->{"{$docType}_path"};
                            $docName = $clearance->{"{$docType}_name"} ?: ($docPath ? basename($docPath) : null);
                            $docUrl = ($clearance->exists && !empty($docPath))
                                ? url("cns-export-customs-clearance-file/{$clearance->id}/{$docType}")
                                : null;
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="cc_{{ $docType }}" class="mb-0 font-weight-bold">{{ $docLabel }}</label>
                                    @if($docUrl)
                                        <span class="badge bg-success text-white">Sudah Upload</span>
                                    @else
                                        <span class="badge bg-secondary text-white">Belum</span>
                                    @endif
                                </div>

                                @if($docUrl)
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded" style="gap: 0.5rem;">
                                        <span class="small text-truncate" title="{{ $docName }}">{{ $docName }}</span>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary flex-shrink-0"
                                            onclick='window.cnsOpenDocPreview(@json($docUrl), @json($docName))'
                                        >Preview</button>
                                    </div>
                                @endif

                                <input
                                    type="file"
                                    name="{{ $docType }}"
                                    id="cc_{{ $docType }}"
                                    class="form-control form-control-sm"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    @cannot('cns.customs_clearance.edit') disabled @endcannot
                                />
                                <small class="text-muted d-block mt-1">
                                    Format: PDF, JPG, JPEG, PNG (maks. 10 MB). Kosongkan jika tidak mengganti file.
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-group mt-2">
                    <label for="cc_notes"><strong>Catatan</strong></label>
                    <textarea
                        name="notes"
                        id="cc_notes"
                        class="text-black form-control"
                        rows="3"
                        maxlength="2000"
                        placeholder="Tambahkan catatan proses customs clearance..."
                        @cannot('cns.customs_clearance.edit') readonly @endcannot
                    >{{ $clearance->notes }}</textarea>
                </div>

                @can('cns.customs_clearance.edit')
                    <div class="text-right">
                        <button type="submit" class="btn btn-success" id="SaveCustomsClearanceBtn">Simpan Dokumen</button>
                    </div>
                @endcan
            </form>
        </div>
    </div>
</div>

<script>
    $('#CustomsClearanceForm').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#SaveCustomsClearanceBtn');
        var formData = new FormData(this);

        $btn.prop('disabled', true);
        $.ajax({
            url: 'cns-export-customs-clearance-save',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                swal('Berhasil!', response.message || 'Dokumen Customs Clearance berhasil disimpan.', 'success').then(function () {
                    if (typeof refreshdata === 'function') {
                        refreshdata();
                    }
                    LoadM('divlistCustomsClearance', 'cns-export-customs-clearance-detail?Task=' + encodeURIComponent($('#cc_booking_id').val()));
                });
            },
            error: function (xhr) {
                var msg = 'Gagal menyimpan dokumen Customs Clearance.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                        if (firstKey && xhr.responseJSON.errors[firstKey].length) {
                            msg = xhr.responseJSON.errors[firstKey][0];
                        }
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                swal('Error', msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });
</script>
