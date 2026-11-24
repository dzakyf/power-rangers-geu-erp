<?php
$completed = collect($statuses)->filter(function ($item) {
    return $item['exists'];
})->count();
$total = count($statuses);
$percent = $total > 0 ? round(($completed / $total) * 100) : 0;
?>
<style>
    .export-timeline-wrap {
        overflow-x: auto;
        padding: 12px 4px 16px;
        margin-bottom: 16px;
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        background: #fafbfc;
    }
    .export-timeline {
        display: flex;
        align-items: flex-start;
        min-width: 1120px;
        padding: 4px 8px;
    }
    .export-timeline-step {
        flex: 1 1 0;
        position: relative;
        text-align: center;
        padding: 0 4px;
    }
    .export-timeline-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        width: 100%;
        height: 3px;
        background: #d1d5db;
        z-index: 1;
    }
    .export-timeline-step.is-done:not(:last-child)::after {
        background: #28a745;
    }
    .export-timeline-dot {
        position: relative;
        z-index: 2;
        width: 32px;
        height: 32px;
        line-height: 30px;
        margin: 0 auto 6px;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 700;
        background: #e9ecef;
        color: #6c757d;
        border: 2px solid #ced4da;
    }
    .export-timeline-step.is-done .export-timeline-dot {
        background: #28a745;
        color: #fff;
        border-color: #218838;
    }
    .export-timeline-title {
        font-size: 11px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 2px;
    }
    .export-timeline-doc {
        font-size: 10px;
        color: #4a5568;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 82px;
        margin: 0 auto;
    }
    .export-timeline-date {
        font-size: 10px;
        color: #718096;
    }
</style>

<div class="mt-3">
    <div class="cns-section-header">
        DETAIL EXPORT CHECKLIST - {{ $booking->Booking_No }} (BC: {{ $completed }}/{{ $total }} | Kontrak: {{ $contractProgress['percent'] }}% dari {{ $contractProgress['bc_count'] }} BC)
    </div>
    <div class="card cns-card">
        <div class="card-body">
            <div class="export-timeline-wrap">
                <div class="export-timeline">
                    @foreach(array_values($statuses) as $idx => $doc)
                        <div class="export-timeline-step {{ $doc['exists'] ? 'is-done' : '' }}" title="{{ $doc['label'] }}: {{ $doc['doc_no'] }} ({{ $doc['note'] }})">
                            <div class="export-timeline-dot">
                                {{ $doc['exists'] ? '✓' : ($idx + 1) }}
                            </div>
                            <div class="export-timeline-title">{{ $doc['short'] }}</div>
                            <div class="export-timeline-doc">{{ $doc['doc_no'] }}</div>
                            <div class="export-timeline-date">{{ $doc['date'] ?? '-' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Tahap / Dokumen Ekspor</th>
                            <th style="width: 110px;" class="text-center">Status</th>
                            <th>Nomor / Referensi</th>
                            <th style="width: 120px;">Tanggal</th>
                            <th>Keterangan</th>
                            <th style="width: 95px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_values($statuses) as $idx => $doc)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $doc['label'] }}</td>
                            <td class="text-center">
                                @if($doc['exists'])
                                    <span class="badge rounded-pillrounded-pill bg-success text-white font-weight-bold">Lengkap</span>
                                @else
                                    <span class="badge bg-success bg-warning text-white font-weight-bold">Belum</span>
                                @endif
                            </td>
                            <td>{{ $doc['doc_no'] }}</td>
                            <td>{{ $doc['date'] ?? '-' }}</td>
                            <td>{{ $doc['note'] }}</td>
                            <td class="text-center">
                                @if(!empty($doc['preview_url']))
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-preview-export-doc"
                                        data-url="{{ $doc['preview_url'] }}"
                                        data-title="{{ $doc['label'] }} ({{ $doc['doc_no'] }})"
                                    >Preview</button>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>
            <h6 class="font-weight-bold mb-2">Payment (Verifikasi Finance &amp; Approve CNS)</h6>
            <div class="alert {{ !empty($booking->Payment_Approved) ? 'alert-success' : (!empty($booking->Finance_Payment_Verified) ? 'alert-warning' : 'alert-secondary') }} d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    @if(!empty($booking->Payment_Approved))
                        <strong>Payment Approved</strong> oleh {{ $booking->Payment_Approved_By ?: '-' }}
                        ({{ $booking->Payment_Approved_At ? date('d F Y H:i', strtotime($booking->Payment_Approved_At)) : '-' }})
                        @if(!empty($booking->Finance_Payment_Ref)) — Ref: {{ $booking->Finance_Payment_Ref }} @endif
                    @elseif(!empty($booking->Finance_Payment_Verified))
                        <strong>Dana Masuk Terverifikasi Finance</strong>
                        ({{ $booking->Finance_Payment_By ?: 'Finance' }}{{ $booking->Finance_Payment_Date ? ' / ' . date('d F Y H:i', strtotime($booking->Finance_Payment_Date)) : '' }})
                        @if(!empty($booking->Finance_Payment_Ref)) — Ref: {{ $booking->Finance_Payment_Ref }} @endif
                    @else
                        <strong>Menunggu verifikasi dana masuk dari tim Finance.</strong>
                    @endif
                </div>
                @can('cns.export_checklist.edit')
                    @if(!empty($booking->Finance_Payment_Verified) && empty($booking->Payment_Approved))
                        <button type="button" class="btn btn-sm btn-primary mt-1 mt-md-0" id="ApprovePaymentBtn">Approve Payment</button>
                    @endif
                @endcan
            </div>

            <hr>
            <h6 class="font-weight-bold mb-3">Verifikasi Manual & Catatan</h6>

            <input type="hidden" id="checklist_booking_id" value="{{ $booking->ID }}" />
            <div class="form-group form-check">
                <input
                    type="checkbox"
                    class="form-check-input"
                    id="Checklist_Verified"
                    {{ !empty($booking->Checklist_Verified) ? 'checked' : '' }}
                    @cannot('cns.export_checklist.edit') disabled @endcannot
                />
                <label class="form-check-label font-weight-bold" for="Checklist_Verified">
                    Checklist Dokumen Ekspor Terverifikasi (Final Verified)
                </label>
            </div>

            <div class="form-group">
                <label for="Checklist_Notes"><strong>Catatan</strong></label>
                <textarea
                    id="Checklist_Notes"
                    class="form-control"
                    rows="3"
                    maxlength="2000"
                    placeholder="Tambahkan catatan kelengkapan dokumen atau tindak lanjut..."
                    @cannot('cns.export_checklist.edit') readonly @endcannot
                >{{ $booking->Checklist_Notes }}</textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small mb-2">
                    Terakhir diverifikasi oleh:
                    <span id="checklist_verified_meta">
                        {{ $booking->Checklist_Verified_By ?: '-' }}
                        {{ $booking->Checklist_Verified_At ? ' / ' . date('d F Y H:i', strtotime($booking->Checklist_Verified_At)) : '' }}
                    </span>
                </div>
                @can('cns.export_checklist.edit')
                <div>
                    <button type="button" class="btn btn-success" id="SaveExportChecklistBtn">Simpan Verifikasi</button>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportDocPreviewModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 92vw;">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between py-2 px-3">
                <h5 class="modal-title mb-0" id="exportDocPreviewTitle">Preview Dokumen</h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" id="exportDocPreviewNewTab" class="btn btn-sm btn-outline-primary mr-2">Buka di Tab Baru</a>
                    <button type="button" id="exportDocPreviewCloseBtn" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="line-height: 1; padding: 4px 10px; font-size: 16px;">&times;</button>
                </div>
            </div>
            <div class="modal-body p-0" style="height: 78vh;">
                <iframe id="exportDocPreviewFrame" src="about:blank" style="width: 100%; height: 100%; border: 0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    $('.btn-preview-export-doc').on('click', function () {
        var url = $(this).data('url');
        var title = $(this).data('title') || 'Preview Dokumen';
        $('#exportDocPreviewTitle').text('Preview - ' + title);
        $('#exportDocPreviewNewTab').attr('href', url);
        $('#exportDocPreviewFrame').attr('src', url);
        $('#exportDocPreviewModal').modal('show');
    });

    $('#exportDocPreviewCloseBtn').on('click', function () {
        $('#exportDocPreviewModal').modal('hide');
    });

    $('#exportDocPreviewModal').on('hidden.bs.modal', function () {
        $('#exportDocPreviewFrame').attr('src', 'about:blank');
    });

    $('#ApprovePaymentBtn').on('click', function () {
        $.ajax({
            url: 'cns-export-checklist-approve-payment',
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                id: $('#checklist_booking_id').val()
            },
            success: function (response) {
                swal('Berhasil!', response.message || 'Payment berhasil di-approve.', 'success').then(function () {
                    if (typeof refreshdata === 'function') {
                        refreshdata();
                    }
                    LoadM('divlistOpportunity', 'cns-export-checklist-detail?Task=' + encodeURIComponent($('#checklist_booking_id').val()));
                });
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal approve payment.';
                swal('Error', msg, 'error');
            }
        });
    });

    $('#SaveExportChecklistBtn').on('click', function () {
        var completedStages = {{ $completed }};
        var totalStages = {{ $total }};
        if (completedStages < totalStages) {
            swal('Perhatian', 'Data BC belum 100% (' + completedStages + '/' + totalStages + ' tahap). Lengkapi seluruh tahap terlebih dahulu sebelum menyimpan verifikasi.', 'warning');
            return;
        }

        $.ajax({
            url: 'cns-export-checklist-detail-submit',
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                id: $('#checklist_booking_id').val(),
                Checklist_Verified: $('#Checklist_Verified').is(':checked') ? 1 : 0,
                Checklist_Notes: $('#Checklist_Notes').val()
            },
            success: function (response) {
                swal('Berhasil!', response.message || 'Export checklist berhasil disimpan.', 'success').then(function () {
                    var meta = (response.verified_by || '-') + (response.verified_at ? ' / ' + response.verified_at : '');
                    $('#checklist_verified_meta').text(meta);
                    if (typeof refreshdata === 'function') {
                        refreshdata();
                    }
                });
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan export checklist.';
                swal('Error', msg, 'error');
            }
        });
    });
</script>
