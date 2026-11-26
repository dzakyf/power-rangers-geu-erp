@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .po-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
    }
    .po-selected-badge {
        font-size: 12px;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 600;
    }
    .po-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
</style>

<div class="main-content-header">
    <h1>PROCUREMENT</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Home</a></li>
        <li class="breadcrumb-item"><span>Procurement</span></li>
        <li class="breadcrumb-item active"><span>Purchase Order List</span></li>
    </ol>
</div>

<!-- Toolbar Aksi Berbasis Klik Button (Tanpa Double-Click) -->
<div class="po-toolbar mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px;">
        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
            <!-- 1. Lihat Detail PO -->
            <button type="button" id="BtnDetailPO" class="btn btn-primary btn-sm font-weight-bold" disabled>
                <i class="fa fa-eye mr-1"></i> Detail PO
            </button>

            <!-- 2. Edit PO (Revisi / Ubah Qty, Harga, Item, Vendor, TOP, Catatan) -->
            <button type="button" id="BtnEditPO" class="btn btn-warning btn-sm font-weight-bold text-dark" disabled>
                <i class="fa fa-edit mr-1"></i> Edit PO
            </button>

            <!-- 3. Approve PO -->
            <button type="button" id="BtnApprovePO" class="btn btn-success btn-sm font-weight-bold" disabled>
                <i class="fa fa-check mr-1"></i> Approve PO
            </button>

            <!-- 4. Cancel / Batalkan PO -->
            <button type="button" id="BtnCancelPO" class="btn btn-outline-danger btn-sm font-weight-bold" disabled>
                <i class="fa fa-ban mr-1"></i> Cancel PO
            </button>

            <!-- 5. Preview PO -->
            <button type="button" id="BtnPreviewPO" class="btn btn-info btn-sm font-weight-bold text-white" disabled>
                <i class="fa fa-file-alt mr-1"></i> Preview PO
            </button>

            <!-- 6. Print / Download PDF PO -->
            <button type="button" id="BtnPrintPO" class="btn btn-dark btn-sm font-weight-bold" disabled>
                <i class="fa fa-print mr-1"></i> Print PDF
            </button>

            <!-- 7. Kirim Otomatis via WA & Email sesuai Master Vendor -->
            <button type="button" id="BtnSendWA" class="btn btn-outline-success btn-sm font-weight-bold" disabled>
                <i class="fab fa-whatsapp mr-1"></i> Kirim WA
            </button>
            <button type="button" id="BtnSendEmail" class="btn btn-outline-primary btn-sm font-weight-bold" disabled>
                <i class="fa fa-envelope mr-1"></i> Kirim Email
            </button>
            <!-- Tombol Create PO Emergency (Selalu aktif tanpa harus pilih baris) -->
            <button type="button" id="BtnCreateEmergencyPO" class="btn btn-danger btn-sm font-weight-bold">
                <i class="fa fa-bolt mr-1"></i> Create PO Emergency
            </button>
            <div class="border-right mx-1" style="height:24px;"></div>

        </div>

        <div class="d-flex align-items-center" style="gap: 8px;">
            <span id="SelectedPOInfo" class="po-selected-badge">Belum ada PO dipilih</span>
            <button type="button" id="BtnRefreshPO" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </div>
    </div>
</div>


<div class="modal fade" id="ModalCreateEmergencyPO" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h5 class="modal-title font-weight-bold" style="font-size:15px;">
                    <i class="fa fa-bolt mr-1"></i> Create PO Emergency (Bypass PR / RFQ / QTN)
                </h5>
                <button type="button" class="close text-white btn-close-emg-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="FormCreateEmergencyPO">
                @csrf
                <div class="modal-body" id="EmergencyPOBody">
                    <div class="text-center py-4 text-muted">Memuat form PO Emergency...</div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" id="BtnCancelEmergencyPO" class="btn btn-secondary btn-sm btn-close-emg-modal" data-dismiss="modal" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" id="BtnSubmitEmergencyPO" class="btn btn-danger btn-sm font-weight-bold">
                        <i class="fa fa-save mr-1"></i> Terbitkan PO Emergency
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Grid Container -->
<div class="row">
    <div class="col-12">
        <div id="POGrid" style="width:100%; height:400px; background-color:white; overflow:hidden; border:1px solid #cbd5e1; border-radius:6px;"></div>
    </div>
</div>

<!-- Area Panel Dinamis (Detail / Edit / Preview PO) -->
<div id="POActionPanel" class="mt-3"></div>

<!-- Modal Approve PO -->
<div class="modal fade" id="ModalApprovePO" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title" style="font-size:15px;"><i class="fa fa-check-circle mr-1"></i> Persetujuan Purchase Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="FormApprovePO">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="po_no" id="Approve_PO_No">
                    <p class="mb-2">Setujui Purchase Order nomor: <strong id="Approve_PO_Label">-</strong>?</p>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Catatan Approval (Opsional)</label>
                        <textarea name="note" id="Approve_Note" rows="3" class="form-control form-control-sm" placeholder="Tambahkan catatan persetujuan jika diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold"><i class="fa fa-check mr-1"></i> Ya, Setujui PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancel PO -->
<div class="modal fade" id="ModalCancelPO" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h5 class="modal-title" style="font-size:15px;"><i class="fa fa-ban mr-1"></i> Batalkan Purchase Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="FormCancelPO">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="po_no" id="Cancel_PO_No">
                    <div class="alert alert-warning py-2 small">
                        PO yang dibatalkan akan berstatus <strong>CANCELLED</strong> dan itemnya dapat diproses ulang apabila belum ada penerimaan barang (GR).
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea name="cancel_reason" id="Cancel_Reason" rows="3" class="form-control form-control-sm" required placeholder="Contoh: Salah input harga / item dialihkan ke vendor lain..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold"><i class="fa fa-ban mr-1"></i> Konfirmasi Cancel PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit PO -->
<div class="modal fade" id="ModalEditPO" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark py-2">
                <h5 class="modal-title font-weight-bold" style="font-size:15px;">
                    <i class="fa fa-edit mr-1"></i> Edit Purchase Order — <span id="Edit_PO_Title">-</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="FormEditPO">
                @csrf
                <div class="modal-body" id="EditPOBody">
                    <div class="text-center py-4 text-muted">Memuat data Purchase Order...</div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                        <i class="fa fa-save mr-1"></i> Simpan Perubahan PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let selectedPoNo = null;

    var POGrid = new dhtmlXGridObject('POGrid');
    POGrid.setImagePath("./dhtmlx/imgs/");
    POGrid.setHeader("No.,No. PO,Tanggal PO,Ref. PR,Vendor / Supplier,Department,Termin,Grand Total (Rp),Status PO,Status Receive,Dibuat Oleh");
    POGrid.setInitWidths("50,165,100,165,240,100,95,130,95,105,100");
    POGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter");
    POGrid.setColAlign("center,center,center,center,left,center,center,right,center,center,center");
    POGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    POGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
    POGrid.setEditable(false);
    POGrid.enableMultiline(true);
    POGrid.setSkin("dhx_skyblue");
    POGrid.init();

    function setActionButtonsEnabled(enabled) {
        $('#BtnDetailPO, #BtnEditPO, #BtnApprovePO, #BtnCancelPO, #BtnPreviewPO, #BtnPrintPO, #BtnSendWA, #BtnSendEmail')
            .prop('disabled', !enabled);
    }

    function loadPOGrid() {
        selectedPoNo = null;
        setActionButtonsEnabled(false);
        $('#SelectedPOInfo').text('Belum ada PO dipilih');
        POGrid.clearAll();
        POGrid.load("{{ url('xml-purchase-order-list') }}");
    }

    loadPOGrid();

    // Klik 1 kali pada baris grid -> pilih PO & aktifkan tombol aksi (tanpa double-click)
    POGrid.attachEvent("onRowSelect", function(rowId) {
        if (!rowId) return;
        selectedPoNo = rowId;
        setActionButtonsEnabled(true);
        $('#SelectedPOInfo').text('Terpilih: ' + selectedPoNo);
    });

    function requireSelectedPO() {
        const currentId = POGrid.getSelectedRowId() || selectedPoNo;
        if (!currentId) {
            alert('Silakan klik satu baris Purchase Order pada tabel terlebih dahulu.');
            return null;
        }
        selectedPoNo = currentId;
        return currentId;
    }

    // 1. Button Detail PO
    $('#BtnDetailPO').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        $('#POActionPanel').html('<div class="alert alert-info py-2">Memuat rincian Purchase Order <strong>' + poNo + '</strong>...</div>');
        $('#POActionPanel').load("{{ url('po-detail') }}?PO_No=" + encodeURIComponent(poNo));
    });

    // 2. Button Edit PO (Revisi item, harga, hapus item/semua item, vendor, termin, catatan)
    $('#BtnEditPO').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        $('#Edit_PO_Title').text(poNo);
        $('#EditPOBody').html('<div class="text-center py-4 text-muted">Memuat form edit PO...</div>');
        $('#ModalEditPO').modal('show');

        $.get("{{ url('po-edit-form') }}", { PO_No: poNo })
            .done(function(html) {
                $('#EditPOBody').html(html);
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat form edit PO.';
                $('#EditPOBody').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
            });
    });

    // Submit Form Edit PO
    $(document).on('submit', '#FormEditPO', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: "{{ url('po-update') }}",
            method: "POST",
            data: form.serialize(),
            success: function(res) {
                alert(res.message || 'Purchase Order berhasil diperbarui.');
                $('#ModalEditPO').modal('hide');
                loadPOGrid();
                if ($('#POActionPanel').children().length > 0) {
                    $('#POActionPanel').load("{{ url('po-detail') }}?PO_No=" + encodeURIComponent(selectedPoNo));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal memperbarui Purchase Order.');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Simpan Perubahan PO');
            }
        });
    });

    // 3. Button Approve PO
    $('#BtnApprovePO').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        $('#Approve_PO_No').val(poNo);
        $('#Approve_PO_Label').text(poNo);
        $('#Approve_Note').val('');
        $('#ModalApprovePO').modal('show');
    });

    $('#FormApprovePO').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ url('po-approve') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message || 'PO berhasil di-approve.');
                $('#ModalApprovePO').modal('hide');
                loadPOGrid();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal memproses approval PO.');
            }
        });
    });

    // 4. Button Cancel PO
    $('#BtnCancelPO').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        $('#Cancel_PO_No').val(poNo);
        $('#Cancel_Reason').val('');
        $('#ModalCancelPO').modal('show');
    });

    $('#FormCancelPO').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ url('po-cancel') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message || 'PO berhasil dibatalkan.');
                $('#ModalCancelPO').modal('hide');
                loadPOGrid();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal membatalkan PO.');
            }
        });
    });

    // 5. Button Preview PO (Inline Preview + Ringkasan Vendor, Item, Harga, TOP, Diskon, PPN, PPH, Catatan)
    $('#BtnPreviewPO').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        const pdfUrl = "{{ url('po-print') }}?PO_No=" + encodeURIComponent(poNo);
        const previewHtml = `
            <div class="po-panel-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="mb-0 font-weight-bold" style="font-size:15px;">
                        <i class="fa fa-file-pdf text-danger mr-1"></i> Preview Dokumen PO: ${poNo}
                    </h5>
                    <div>
                        <a href="${pdfUrl}" target="_blank" class="btn btn-dark btn-sm">
                            <i class="fa fa-external-link-alt mr-1"></i> Buka Tab Baru
                        </a>
                        <button type="button" class="btn btn-light btn-sm border" onclick="$('#POActionPanel').html('')">
                            Tutup
                        </button>
                    </div>
                </div>
                <iframe src="${pdfUrl}" style="width:100%; height:520px; border:1px solid #e2e8f0; border-radius:6px;"></iframe>
            </div>
        `;
        $('#POActionPanel').html(previewHtml);
    });

    // 6. Button Print PDF PO
    $('#BtnPrintPO').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        const printUrl = "{{ url('po-print') }}?PO_No=" + encodeURIComponent(poNo);
        window.open(printUrl, '_blank');
    });

    // 7. Button Kirim Otomatis via WhatsApp (sesuai data master vendor)
    $('#BtnSendWA').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        $.post("{{ url('po-send-whatsapp') }}", {
            _token: "{{ csrf_token() }}",
            po_no: poNo
        }).done(function(res) {
            if (res.wa_url) {
                window.open(res.wa_url, '_blank');
            }
            alert(res.message || 'Tautan WhatsApp PO berhasil disiapkan.');
        }).fail(function(xhr) {
            alert(xhr.responseJSON?.message || 'Gagal menyiapkan pengiriman WhatsApp PO.');
        });
    });

    // 8. Button Kirim Otomatis via Email (sesuai data master vendor + lampiran PDF)
    $('#BtnSendEmail').on('click', function() {
        const poNo = requireSelectedPO();
        if (!poNo) return;

        const btn = $('#BtnSendEmail');
        btn.prop('disabled', true).text('Mengirim...');

        $.post("{{ url('po-send-email') }}", {
            _token: "{{ csrf_token() }}",
            po_no: poNo
        }).done(function(res) {
            alert(res.message || 'Email PO berhasil dikirim ke vendor.');
        }).fail(function(xhr) {
            alert(xhr.responseJSON?.message || 'Gagal mengirim Email PO.');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fa fa-envelope mr-1"></i> Kirim Email');
        });
    });

    // Button Refresh Grid
    $('#BtnRefreshPO').on('click', function() {
        loadPOGrid();
        $('#POActionPanel').html('');
    });
});


   // Buka Modal Create PO Emergency
$('#BtnCreateEmergencyPO').on('click', function() {
    $('#EmergencyPOBody').html('<div class="text-center py-4 text-muted">Memuat form PO Emergency...</div>');
    $('#ModalCreateEmergencyPO').modal('show');

    $.get("{{ url('po-emergency-form') }}")
        .done(function(html) {
            $('#EmergencyPOBody').html(html);
        })
        .fail(function(xhr) {
            const msg = xhr.responseJSON?.message || 'Gagal memuat form PO Emergency.';
            $('#EmergencyPOBody').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
        });
});

// Submit Form Create PO Emergency
$(document).off('submit', '#FormCreateEmergencyPO').on('submit', '#FormCreateEmergencyPO', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitBtn = $('#BtnSubmitEmergencyPO');
    submitBtn.prop('disabled', true).text('Menyimpan...');

    $.ajax({
        url: "{{ url('po-emergency-store') }}",
        method: "POST",
        data: form.serialize(),
        success: function(res) {
            alert(res.message || 'PO Emergency berhasil diterbitkan!');
            closeEmergencyModal();
            loadPOGrid();
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Gagal menerbitkan PO Emergency (status ' + xhr.status + ').';
            alert(msg);
            submitBtn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Terbitkan PO Emergency');
        }
    });
});

function closeEmergencyModal() {
    const modalEl = $('#ModalCreateEmergencyPO');
    if (typeof modalEl.modal === 'function') {
        modalEl.modal('hide');
    }
    modalEl.removeClass('show').hide();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
    $('#BtnSubmitEmergencyPO').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Terbitkan PO Emergency');
}

$(document).off('click', '.btn-close-emg-modal, #BtnCancelEmergencyPO').on('click', '.btn-close-emg-modal, #BtnCancelEmergencyPO', function(e) {
    e.preventDefault();
    closeEmergencyModal();
});

function loadPOGrid() {
    selectedPoNo = null;
    setActionButtonsEnabled(false);
    $('#SelectedPOInfo').text('Belum ada PO dipilih');
    POGrid.clearAll();
    const xmlUrl = "{{ url('xml-purchase-order-list') }}" + "?_t=" + new Date().getTime();
    POGrid.load(xmlUrl);
}


</script>
@endsection