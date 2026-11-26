@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Finance</h1>
</div>

<div class="card cns-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="card-title mb-0">Export Invoicing</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" class="btn btn-sm btn-primary" onclick="openExportInvoicingAction('submit')">Ajukan / Revisi (CNS)</button>
            <button type="button" class="btn btn-sm btn-success" onclick="openExportInvoicingAction('review')">Review / Approve / Reject (Finance)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshExportInvoicingGrid()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3 align-items-end">
            <div class="col-md-2">
                <label for="ei_tgl1" class="mb-1"><small>Stuffing Dari</small></label>
                <input type="date" id="ei_tgl1" class="text-black form-control form-control-sm" />
            </div>
            <div class="col-md-2">
                <label for="ei_tgl2" class="mb-1"><small>Stuffing Sampai</small></label>
                <input type="date" id="ei_tgl2" class="text-black form-control form-control-sm" />
            </div>
            <div class="col-md-3">
                <label for="ei_status" class="mb-1"><small>Status</small></label>
                <select id="ei_status" class="text-black form-control form-control-sm">
                    <option value="">Semua Status</option>
                    <option value="Ready to Submit">Ready to Submit</option>
                    <option value="pending FAT">pending FAT</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-primary" onclick="refreshExportInvoicingGrid()">Filter</button>
            </div>
        </div>

        <div class="cns-grid-wrapper">
            <div id="ExportInvoicingGrid" class="cns-grid" style="width:100%;height:380px;background-color:white;overflow:hidden;"></div>
        </div>
    </div>
</div>

<!-- Modal CNS Submit / Revise -->
<div class="modal fade" id="eiSubmitModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="eiSubmitForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pengajuan Export Invoice (CNS)</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#eiSubmitModal').modal('hide');" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ei_vessel_schedule_id" name="vessel_schedule_id" />
                    <div id="ei_reject_alert" class="alert alert-danger d-none">
                        <strong>Alasan Reject Sebelumnya:</strong> <span id="ei_prev_reject_reason"></span>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><small class="text-muted">Order No</small><div class="font-weight-bold" id="ei_info_order_no">-</div></div>
                        <div class="col-md-4"><small class="text-muted">Stuffing No</small><div class="font-weight-bold" id="ei_info_stuffing_no">-</div></div>
                        <div class="col-md-4"><small class="text-muted">Vessel</small><div class="font-weight-bold" id="ei_info_vessel">-</div></div>
                        <div class="col-md-4 mt-2"><small class="text-muted">Buyer</small><div class="font-weight-bold" id="ei_info_buyer">-</div></div>
                        <div class="col-md-4 mt-2"><small class="text-muted">Contract No</small><div class="font-weight-bold" id="ei_info_contract">-</div></div>
                        <div class="col-md-4 mt-2"><small class="text-muted">Packing List No</small><div class="font-weight-bold" id="ei_info_pl">-</div></div>
                    </div>
                    <hr />
                    <div class="form-group">
                        <label>Upload File Kontrak <span class="text-danger">*</span> <small class="text-muted">(PDF/Excel/Image, max 10MB)</small></label>
                        <input type="file" name="contract_file" id="ei_contract_file" class="text-black form-control-file" required />
                    </div>
                    <div class="form-group">
                        <label>Upload File Packing List <span class="text-danger">*</span> <small class="text-muted">(PDF/Excel/Image, max 10MB)</small></label>
                        <input type="file" name="packing_list_file" id="ei_pl_file" class="text-black form-control-file" required />
                    </div>
                    <div class="form-group">
                        <label>Catatan CNS (Opsional)</label>
                        <textarea name="cns_notes" id="ei_cns_notes" class="text-black form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#eiSubmitModal').modal('hide');">Tutup</button>
                    <button type="submit" class="btn btn-primary">Submit ke Finance (pending FAT)</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Finance Review (Approve / Reject) -->
<div class="modal fade" id="eiReviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Export Invoice (Finance / FAT)</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#eiReviewModal').modal('hide');" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ei_review_request_id" />
                <div class="row mb-3">
                    <div class="col-md-4"><small class="text-muted">Order No</small><div class="font-weight-bold" id="ei_rev_order_no">-</div></div>
                    <div class="col-md-4"><small class="text-muted">Contract No</small><div class="font-weight-bold" id="ei_rev_contract">-</div></div>
                    <div class="col-md-4"><small class="text-muted">Packing List No</small><div class="font-weight-bold" id="ei_rev_pl">-</div></div>
                    <div class="col-md-4 mt-2"><small class="text-muted">Buyer</small><div class="font-weight-bold" id="ei_rev_buyer">-</div></div>
                    <div class="col-md-4 mt-2"><small class="text-muted">Status</small><div class="font-weight-bold" id="ei_rev_status">-</div></div>
                    <div class="col-md-4 mt-2"><small class="text-muted">Diajukan Oleh</small><div class="font-weight-bold" id="ei_rev_submitted">-</div></div>
                </div>
                <div class="mb-3">
                    <strong>Dokumen Terlampir:</strong>
                    <div class="mt-1">
                        <a id="ei_rev_contract_link" href="#" target="_blank" class="btn btn-sm btn-outline-primary mr-2">Download Kontrak</a>
                        <a id="ei_rev_pl_link" href="#" target="_blank" class="btn btn-sm btn-outline-primary mr-2">Download Packing List</a>
                        <a id="ei_rev_transfer_proof_link" href="#" target="_blank" class="btn btn-sm btn-outline-success d-none">Lihat Bukti Transfer</a>
                    </div>
                </div>
                <div class="form-group">
                    <label>Catatan CNS</label>
                    <div class="text-black form-control-plaintext border rounded p-2 bg-light" id="ei_rev_cns_notes">-</div>
                </div>
                <div class="form-group" id="ei_rev_transfer_proof_group">
                    <label>Upload Bukti Transfer <span class="text-danger">*</span> <small class="text-muted">(Wajib jika Approve: PDF/Image, max 10MB)</small></label>
                    <input type="file" id="ei_rev_transfer_proof" accept=".pdf,.jpg,.jpeg,.png" class="text-black form-control-file" />
                </div>
                <div class="form-group">
                    <label>Catatan Finance (Opsional)</label>
                    <textarea id="ei_rev_finance_notes" class="text-black form-control" rows="2"></textarea>
                </div>
                <div class="form-group" id="ei_rev_reject_group">
                    <label>Alasan Reject <small class="text-muted">(Wajib diisi jika Reject)</small></label>
                    <textarea id="ei_rev_rejection_reason" class="text-black form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#eiReviewModal').modal('hide');">Tutup</button>
                <button type="button" id="ei_btn_reject" class="btn btn-danger" onclick="submitFinanceDecision('reject')">Reject</button>
                <button type="button" id="ei_btn_approve" class="btn btn-success" onclick="submitFinanceDecision('approve')">Approve</button>
            </div>
        </div>
    </div>
</div>

<script>
    var ExportInvoicingGrid = new dhtmlXGridObject('ExportInvoicingGrid');
    ExportInvoicingGrid.setImagePath("./dhtmlx/imgs/");
    ExportInvoicingGrid.setHeader("No,Order No,Stuffing No,Vessel,Stuffing Date,Buyer,Contract No,Packing List No,Status,Submitted");
    ExportInvoicingGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter");
    ExportInvoicingGrid.setInitWidthsP("4,10,10,11,9,14,11,11,10,10");
    ExportInvoicingGrid.setColAlign("center,left,left,left,center,left,left,left,center,left");
    ExportInvoicingGrid.setColSorting("int,str,str,str,str,str,str,str,str,str");
    ExportInvoicingGrid.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    ExportInvoicingGrid.setSkin("dhx_skyblue");
    ExportInvoicingGrid.init();

    function buildExportInvoicingXmlUrl(params) {
        var query = new URLSearchParams();
        if (params) {
            query.append('posStart', params.posStart);
            query.append('count', params.count);
            if (params.sortCol !== null && params.sortCol !== undefined) {
                query.append('sort_col', params.sortCol);
                query.append('sort_dir', params.sortDir || 'asc');
            }
            if (params.filters) {
                Object.keys(params.filters).forEach(function (colIdx) {
                    query.append('filter[' + colIdx + ']', params.filters[colIdx]);
                });
            }
        }
        var tgl1 = document.getElementById('ei_tgl1').value;
        var tgl2 = document.getElementById('ei_tgl2').value;
        var status = document.getElementById('ei_status').value;
        if (tgl1 && tgl2) {
            query.append('tgl1', tgl1);
            query.append('tgl2', tgl2);
        }
        if (status) {
            query.append('status', status);
        }
        return "{{ url('finance-export-invoicing-xml') }}" + (query.toString() ? '?' + query.toString() : '');
    }

    var ExportInvoicingPagination = initCnsGridPagination(ExportInvoicingGrid, {
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        getUrl: buildExportInvoicingXmlUrl
    });

    function refreshExportInvoicingGrid() {
        ExportInvoicingPagination.reload(true);
    }

    ExportInvoicingGrid.attachEvent("onRowDblClicked", function () {
        openExportInvoicingAction('auto');
    });

    function openExportInvoicingAction(mode) {
        var selectedId = ExportInvoicingGrid.getSelectedId();
        if (!selectedId) {
            swal('Perhatian', 'Pilih baris data terlebih dahulu.', 'warning');
            return;
        }

        $.get("{{ url('finance-export-invoicing-detail') }}/" + encodeURIComponent(selectedId), function (res) {
            var req = res.request;
            var status = req ? req.status : 'Ready to Submit';

            if (mode === 'auto') {
                mode = (status === 'Ready to Submit' || status === 'Rejected') ? 'submit' : 'review';
            }

            if (mode === 'submit') {
                if (req && req.status !== 'Rejected') {
                    swal('Info', 'Pengajuan sudah berstatus ' + req.status + ' dan tidak dapat diajukan ulang.', 'info');
                    return;
                }
                $('#ei_vessel_schedule_id').val(res.vessel_schedule.id);
                $('#ei_info_order_no').text(res.vessel_schedule.order_no || '-');
                $('#ei_info_stuffing_no').text(res.vessel_schedule.stuffing_no || '-');
                $('#ei_info_vessel').text(res.vessel_schedule.vessel || '-');
                $('#ei_info_buyer').text(res.linked.buyer_name || '-');
                $('#ei_info_contract').text(res.linked.contract_no || '-');
                $('#ei_info_pl').text(res.linked.packing_list_no || '-');
                $('#ei_contract_file').val('');
                $('#ei_pl_file').val('');
                $('#ei_cns_notes').val(req && req.cns_notes ? req.cns_notes : '');

                if (req && req.status === 'Rejected' && req.rejection_reason) {
                    $('#ei_prev_reject_reason').text(req.rejection_reason);
                    $('#ei_reject_alert').removeClass('d-none');
                } else {
                    $('#ei_reject_alert').addClass('d-none');
                }
                $('#eiSubmitModal').modal('show');
            } else {
                if (!req) {
                    swal('Info', 'Belum ada pengajuan invoice dari tim CNS untuk order ini.', 'info');
                    return;
                }
                $('#ei_review_request_id').val(req.id);
                $('#ei_rev_order_no').text(res.vessel_schedule.order_no || '-');
                $('#ei_rev_contract').text(req.contract_no || res.linked.contract_no || '-');
                $('#ei_rev_pl').text(req.packing_list_no || res.linked.packing_list_no || '-');
                $('#ei_rev_buyer').text(req.buyer_name || res.linked.buyer_name || '-');
                $('#ei_rev_status').text(req.status);
                $('#ei_rev_submitted').text((req.submitted_by || '-') + (req.submitted_at ? ' (' + req.submitted_at + ')' : ''));
                $('#ei_rev_contract_link').attr('href', req.contract_file_url).text('Kontrak: ' + req.contract_file_name);
                $('#ei_rev_pl_link').attr('href', req.packing_list_file_url).text('Packing List: ' + req.packing_list_file_name);
                if (req.transfer_proof_url) {
                    $('#ei_rev_transfer_proof_link')
                        .attr('href', req.transfer_proof_url)
                        .text('Bukti Transfer: ' + (req.transfer_proof_name || 'Lihat'))
                        .removeClass('d-none');
                } else {
                    $('#ei_rev_transfer_proof_link').attr('href', '#').addClass('d-none');
                }
                $('#ei_rev_transfer_proof').val('');
                $('#ei_rev_cns_notes').text(req.cns_notes || '-');
                $('#ei_rev_finance_notes').val(req.finance_notes || '');
                $('#ei_rev_rejection_reason').val(req.rejection_reason || '');

                var isPending = req.status === 'pending FAT';
                $('#ei_rev_transfer_proof_group').toggleClass('d-none', !isPending);
                $('#ei_btn_approve, #ei_btn_reject').toggleClass('d-none', !isPending).prop('disabled', !isPending);
                $('#eiReviewModal').modal('show');
            }
        });
    }

    $('#eiSubmitForm').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('_token', $('input[name="_token"]').val());

        $.ajax({
            url: "{{ url('finance-export-invoicing-submit') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#eiSubmitModal').modal('hide');
                swal('Berhasil', res.message, 'success');
                refreshExportInvoicingGrid();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengirim pengajuan.';
                swal('Error', msg, 'error');
            }
        });
    });

    function submitFinanceDecision(action) {
        var reqId = $('#ei_review_request_id').val();
        var url = action === 'approve'
            ? "{{ url('finance-export-invoicing-approve') }}/" + reqId
            : "{{ url('finance-export-invoicing-reject') }}/" + reqId;

        var formData = new FormData();
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('finance_notes', $('#ei_rev_finance_notes').val());

        if (action === 'approve') {
            var transferFile = $('#ei_rev_transfer_proof')[0].files[0];
            if (!transferFile) {
                swal('Perhatian', 'Upload bukti transfer terlebih dahulu sebelum Approve.', 'warning');
                return;
            }
            formData.append('transfer_proof', transferFile);
        } else {
            formData.append('rejection_reason', $('#ei_rev_rejection_reason').val());
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#eiReviewModal').modal('hide');
                swal('Berhasil', res.message, 'success');
                refreshExportInvoicingGrid();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memproses keputusan.';
                swal('Error', msg, 'error');
            }
        });
    }

    $(window).on('resize', function () {
        if (ExportInvoicingGrid) ExportInvoicingGrid.setSizes();
    });

    refreshExportInvoicingGrid();
</script>
@endsection
