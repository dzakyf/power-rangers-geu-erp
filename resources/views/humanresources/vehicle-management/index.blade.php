@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Vehicle Management & Document Reminder</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Human Capital & General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Asset & Vehicle Management</a></li>
            <li class="breadcrumb-item active"><span class="active">Vehicle Management</span></li>
        </ol>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mt-1 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Total Kendaraan</div>
                    <div class="fs-3 fw-bold text-primary">{{ $counts['total_vehicles'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Dokumen Expired</div>
                    <div class="fs-3 fw-bold text-danger">{{ $counts['expired'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Kritis (&le; 14 Hari)</div>
                    <div class="fs-3 fw-bold text-danger">{{ $counts['critical_14'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Segera (&le; 30 Hari)</div>
                    <div class="fs-3 fw-bold text-warning">{{ $counts['warning_30'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Perhatian (&le; 60 Hari)</div>
                    <div class="fs-3 fw-bold text-info">{{ $counts['notice_60'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body py-2 d-flex flex-column justify-content-between gap-1">
                    <div class="text-muted small fw-semibold">Aksi Reminder</div>
                    <button type="button" id="btnSyncReminder" class="btn btn-outline-primary btn-sm" onclick="syncVehicleReminders();">
                        <i class="fa fa-bell me-1"></i> Sinkron In-App
                    </button>
                    <button type="button" id="btnPreviewEmail" class="btn btn-outline-success btn-sm" onclick="openEmailPreviewModal();">
                        <i class="fa fa-envelope-open-text me-1"></i> Preview &amp; Kirim Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Banner Reminder STNK & KEUR -->
    @if(!empty($reminderItems))
    <div class="card border-warning shadow-sm mb-3">
        <div class="card-header bg-warning bg-opacity-25 d-flex justify-content-between align-items-center py-2">
            <div class="fw-bold text-dark">
                <i class="fa fa-triangle-exclamation text-danger me-2"></i>
                Pengingat Jatuh Tempo STNK (Tahunan / 5 Tahunan) &amp; KEUR/KIR ({{ count($reminderItems) }} item perlu perhatian)
            </div>
            <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#reminderCollapse" aria-expanded="true">
                Tampilkan / Sembunyikan
            </button>
        </div>
        <div class="collapse show" id="reminderCollapse">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:240px;overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>No Polisi</th>
                                <th>Asset No</th>
                                <th>Kendaraan</th>
                                <th>Dokumen</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>PIC</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reminderItems as $item)
                            <tr>
                                <td class="fw-bold">{{ $item['registration_no'] }}</td>
                                <td><code>{{ $item['asset_no'] }}</code></td>
                                <td>{{ $item['vehicle_name'] }}</td>
                                <td><span class="badge bg-dark text-white">{{ $item['doc_label'] }}</span></td>
                                <td>{{ $item['due_date'] }}</td>
                                <td><span class="badge {{ $item['badge_class'] }}">{{ $item['label'] }}</span></td>
                                <td>{{ $item['pic'] }}</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-xs btn-warning text-dark py-0 px-2 me-1" onclick="openTaxRequestModal('{{ $item['asset_no'] }}', '{{ $item['doc_type'] ?? '' }}', '{{ $item['due_date'] ?? '' }}');">
                                        <i class="fa fa-file-invoice-dollar me-1"></i> Ajukan Perpanjangan
                                    </button>
                                    <button type="button" class="btn btn-xs btn-primary py-0 px-2" onclick="openVehicleDetail('{{ $item['asset_no'] }}');">
                                        <i class="fa fa-edit me-1"></i> Detail / Update
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Grid Kendaraan -->
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa fa-car me-2 text-primary"></i>Daftar Kendaraan Operasional</h5>
                <small class="text-muted">Klik dua kali pada baris kendaraan untuk membuka detail aset serta pembaruan STNK &amp; KEUR/KIR.</small>
            </div>
            <div class="d-flex flex-wrap gap-1" id="docFilterGroup">
                <button type="button" class="btn btn-sm btn-primary active" onclick="setDocFilter('ALL', this);">Semua</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setDocFilter('ATTENTION', this);">Perlu Perhatian</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setDocFilter('EXPIRED', this);">Expired</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setDocFilter('CRITICAL', this);">Kritis &le;14 Hr</button>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="setDocFilter('WARNING', this);">Segera &le;30 Hr</button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="setDocFilter('NOTICE', this);">Perhatian &le;60 Hr</button>
            </div>
        </div>
        <div class="card-body">
            <div id="VEHICLEGRID" style="width:auto;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" class="btn btn-warning btn-sm" onclick="VEHICLEGRID.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-info btn-sm text-white" onclick="VEHICLERefresh();">
                    <i class="fa fa-sync me-1"></i> Refresh Data
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="VEHICLEDetailSelected();">
                    <i class="fa fa-eye me-1"></i> Detail &amp; Update STNK / KEUR
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="openTaxRequestFromGrid();">
                    <i class="fa fa-file-invoice-dollar me-1"></i> Ajukan Perpanjangan STNK / KEUR
                </button>
                <a href="hrd-vehicle-maintenance-index" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-wrench me-1"></i> Vehicle Maintenance
                </a>
                <a href="hrd-vehicle-operations" class="btn btn-outline-success btn-sm">
                    <i class="fa fa-truck me-1"></i> Logbook &amp; Fuel Tracking
                </a>
                <a href="hrd-asset-lists" class="btn btn-outline-dark btn-sm">
                    <i class="fa fa-boxes-stacked me-1"></i> Semua Asset Lists
                </a>
            </div>
        </div>
    </div>

    <div id="VEHICLEDTL" class="mt-3"></div>

    <!-- Daftar Pengajuan Perpanjangan STNK / KEUR (Terintegrasi Approval Engine) -->
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold"><i class="fa fa-file-invoice-dollar me-2 text-primary"></i>Pengajuan Perpanjangan STNK / KEUR &amp; Voucher Finance</h6>
                <small class="text-muted">Alur: Pengajuan GA &rarr; Approval HCGA Manager &rarr; Voucher Finance &rarr; Pembayaran &amp; Update Tanggal STNK/KEUR</small>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadTaxRequests();">
                <i class="fa fa-sync me-1"></i> Muat Ulang
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No Pengajuan</th>
                            <th>Kendaraan</th>
                            <th>Jenis</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end">Estimasi Biaya</th>
                            <th>Voucher Finance</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="TaxRequestsBody">
                        <tr><td colspan="8" class="text-center text-muted py-3">Memuat data pengajuan...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Pengajuan Perpanjangan STNK / KEUR -->
    <div class="modal fade" id="TaxRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title text-white">Ajukan Perpanjangan STNK / KEUR</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="FormTaxRequest" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Asset No Kendaraan <span class="text-danger">*</span></label>
                            <input type="text" id="Tax_Asset_No" name="Asset_No" class="form-control" readonly required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Dokumen <span class="text-danger">*</span></label>
                            <select id="Tax_Jenis_Pajak" name="Jenis_Pajak" class="form-select form-control" required>
                                <option value="STNK_TAHUNAN">STNK Tahunan</option>
                                <option value="STNK_5_TAHUNAN">STNK 5 Tahunan</option>
                                <option value="KEUR">KEUR / KIR</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Jatuh Tempo</label>
                            <input type="date" id="Tax_Jatuh_Tempo" name="Tanggal_Jatuh_Tempo" class="form-control" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Estimasi Biaya (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="Tax_Estimasi_Biaya" name="Estimasi_Biaya" class="form-control" value="0" min="0" required />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitTaxRequest();">Submit ke Approval HCGA</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran Pajak STNK / KEUR -->
    <div class="modal fade" id="TaxPayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title text-white">Konfirmasi Pembayaran &amp; Update Masa Berlaku</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="FormTaxPay" class="row g-3" enctype="multipart/form-data">
                        <input type="hidden" id="Pay_Tax_ID" name="id" />
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                            <input type="date" id="Pay_Date" name="paid_date" class="form-control" value="{{ date('Y-m-d') }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Masa Berlaku Baru <span class="text-danger">*</span></label>
                            <input type="date" id="Pay_New_Due_Date" name="new_due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label">No. Referensi / Keterangan</label>
                            <input type="text" id="Pay_Bukti" name="bukti_bayar" class="form-control" placeholder="ex: TRF/FIN/2026/001" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Upload File Bukti Bayar (Gambar / PDF)</label>
                            <input type="file" id="Pay_Bukti_File" name="bukti_file" class="form-control" accept="image/*,.pdf" />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="submitTaxPay();">Simpan &amp; Update STNK/KEUR</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Revisi & Ajukan Ulang (Resubmit) -->
    <div class="modal fade" id="TaxResubmitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h6 class="modal-title">Revisi &amp; Ajukan Ulang Perpanjangan STNK / KEUR</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="FormTaxResubmit" class="row g-3">
                        <input type="hidden" id="Resub_Tax_ID" name="id" />
                        <div class="col-12">
                            <label class="form-label">Catatan Penolakan Sebelumnya</label>
                            <input type="text" id="Resub_Note" class="form-control bg-light" readonly />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Jatuh Tempo</label>
                            <input type="date" id="Resub_Due_Date" name="Tanggal_Jatuh_Tempo" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Revisi Estimasi Biaya (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="Resub_Estimasi_Biaya" name="Estimasi_Biaya" class="form-control" min="0" required />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitTaxResubmit();">Ajukan Ulang ke HCGA Mgr</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview Kerangka & Kirim Email Reminder -->
    <div class="modal fade" id="EmailReminderPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title text-white"><i class="fa fa-envelope-open-text me-2"></i>Preview Kerangka Email Reminder</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="emailPreviewAlert" class="alert alert-info py-2 small d-none"></div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-primary text-white">Total Item Perlu Perhatian: <span id="emailPrevTotalItems">0</span></span>
                        <span class="badge bg-warning text-dark">Belum Terkirim Email: <span id="emailPrevUnsentCount">0</span></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted mb-1">Penerima (To)</label>
                        <div id="emailPrevRecipients" class="p-2 bg-light border rounded small text-dark">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted mb-1">Subject</label>
                        <input type="text" id="emailPrevSubject" class="form-control form-control-sm bg-light fw-semibold" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted mb-1">Kerangka / Isi Email</label>
                        <pre id="emailPrevBody" class="p-3 bg-light border rounded small mb-0" style="white-space:pre-wrap;font-family:Consolas,monospace;max-height:280px;overflow-y:auto;">Memuat preview...</pre>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkForceResendEmail" />
                        <label class="form-check-label small" for="chkForceResendEmail">
                            Paksa kirim ulang semua (termasuk item yang sudah pernah dikirim emailnya pada threshold saat ini)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="btnSendEmailManual" class="btn btn-success btn-sm" onclick="sendManualEmailReminders();">
                        <i class="fa fa-paper-plane me-1"></i> Kirim Email Reminder Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
var currentDocFilter = 'ALL';

function setDocFilter(status, btn) {
    currentDocFilter = status;
    var btns = document.querySelectorAll('#docFilterGroup button');
    btns.forEach(function (el) {
        el.classList.remove('active', 'btn-primary');
        if (!el.classList.contains('btn-outline-danger') && !el.classList.contains('btn-outline-warning') && !el.classList.contains('btn-outline-info')) {
            el.classList.add('btn-outline-primary');
        }
    });
    if (btn) {
        btn.classList.add('active');
    }
    VEHICLERefresh();
}

function openVehicleDetail(assetNo) {
    LoadM('VEHICLEDTL', 'hrd-asset-lists-detail?Task_ID=' + encodeURIComponent(assetNo));
    var el = document.getElementById('VEHICLEDTL');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function VEHICLEDetailSelected() {
    var selectedId = VEHICLEGRID.getSelectedId();
    if (!selectedId) {
        alert('Pilih baris kendaraan terlebih dahulu.');
        return;
    }
    var assetNo = VEHICLEGRID.cells(selectedId, 1).getValue();
    openVehicleDetail(assetNo);
}

function syncVehicleReminders() {
    var btn = document.getElementById('btnSyncReminder');
    btn.disabled = true;
    $.ajax({
        url: 'hrd-vehicle-management-sync-reminders',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        success: function (res) {
            alert(res.message || 'Reminder berhasil disinkronkan.');
            btn.disabled = false;
        },
        error: function (xhr) {
            alert('Gagal sinkronisasi reminder.');
            btn.disabled = false;
        }
    });
}

function openEmailPreviewModal() {
    var modalEl = document.getElementById('EmailReminderPreviewModal');
    $('#emailPreviewAlert').addClass('d-none').text('');
    $('#emailPrevRecipients').text('Memuat...');
    $('#emailPrevSubject').val('Memuat...');
    $('#emailPrevBody').text('Memuat preview...');
    $('#chkForceResendEmail').prop('checked', false);
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    $.get('hrd-vehicle-management-email-preview', function (res) {
        if (!res || res.status !== 'success' || !res.data) {
            $('#emailPrevBody').text('Gagal memuat preview email.');
            return;
        }
        var d = res.data;
        $('#emailPrevTotalItems').text(d.total_pending_items || 0);
        $('#emailPrevUnsentCount').text(d.unsent_count || 0);

        if (d.is_sample) {
            $('#emailPreviewAlert')
                .removeClass('d-none')
                .text('Saat ini tidak ada dokumen jatuh tempo (H-60 s/d Expired). Menampilkan contoh kerangka email.');
        }

        if (d.recipients && d.recipients.length > 0) {
            var badges = d.recipients.map(function (em) {
                return '<span class="badge bg-secondary text-white me-1 mb-1">' + $('<div>').text(em).html() + '</span>';
            }).join(' ');
            $('#emailPrevRecipients').html(badges);
        } else {
            $('#emailPrevRecipients').html('<span class="text-danger">Belum ada email penerima (HCGA/GA atau PIC) yang terdaftar di tabel users.</span>');
        }

        $('#emailPrevSubject').val(d.subject || '-');
        $('#emailPrevBody').text(d.body || '-');
    }).fail(function () {
        $('#emailPrevBody').text('Terjadi kesalahan saat memuat preview email.');
    });
}

function sendManualEmailReminders() {
    var btn = document.getElementById('btnSendEmailManual');
    var force = $('#chkForceResendEmail').is(':checked');
    btn.disabled = true;

    $.ajax({
        url: 'hrd-vehicle-management-send-email-reminders',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { force: force ? 1 : 0 },
        success: function (res) {
            alert(res.message || 'Pengiriman email reminder selesai.');
            btn.disabled = false;
            openEmailPreviewModal();
        },
        error: function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengirim email reminder.');
            btn.disabled = false;
        }
    });
}

VEHICLEGRID = new dhtmlXGridObject('VEHICLEGRID');
VEHICLEGRID.setImagePath("./dhtmlx/imgs/");
VEHICLEGRID.setHeader("No,Asset No,No Polisi,Kendaraan,Jenis,Lokasi,Departemen,PIC,KM Saat Ini,STNK Tahunan,Status STNK 1 Thn,STNK 5 Tahun,Status STNK 5 Thn,Jadwal KEUR/KIR,Status KEUR,Status Dokumen,Status Aset");
VEHICLEGRID.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter");
VEHICLEGRID.setInitWidths("50,130,105,210,120,105,110,120,90,105,130,105,130,110,130,115,95");
VEHICLEGRID.setColAlign("center,center,center,left,center,center,center,left,right,center,center,center,center,center,center,center,center");
VEHICLEGRID.setColSorting("int,str,str,str,str,str,str,str,int,date,str,date,str,date,str,str,str");
VEHICLEGRID.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro");
VEHICLEGRID.setNumberFormat("0,000", 8, ".", ",");
VEHICLEGRID.attachEvent("onRowDblClicked", VEHICLEDetailSelected);
VEHICLEGRID.enableSmartRendering(true, 150);
VEHICLEGRID.setSkin("dhx_skyblue");
VEHICLEGRID.init();

function VEHICLERefresh() {
    VEHICLEGRID.clearAndLoad('hrd-vehicle-management-data?doc_status=' + encodeURIComponent(currentDocFilter));
}

function openTaxRequestModal(assetNo, docType, dueDate) {
    $('#Tax_Asset_No').val(assetNo || '');
    var mapped = 'STNK_TAHUNAN';
    if ((docType || '').toUpperCase().indexOf('5') !== -1) { mapped = 'STNK_5_TAHUNAN'; }
    else if ((docType || '').toUpperCase().indexOf('KEUR') !== -1 || (docType || '').toUpperCase().indexOf('KIR') !== -1) { mapped = 'KEUR'; }
    $('#Tax_Jenis_Pajak').val(mapped);
    $('#Tax_Jatuh_Tempo').val(dueDate && dueDate !== '-' ? dueDate : '');
    $('#Tax_Estimasi_Biaya').val(0);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('TaxRequestModal')).show();
}

function openTaxRequestFromGrid() {
    var selectedId = VEHICLEGRID.getSelectedId();
    if (!selectedId) {
        alert('Pilih baris kendaraan di tabel terlebih dahulu.');
        return;
    }
    var assetNo = VEHICLEGRID.cells(selectedId, 1).getValue();
    var dueDate = VEHICLEGRID.cells(selectedId, 9).getValue();
    openTaxRequestModal(assetNo, 'STNK_TAHUNAN', dueDate);
}

function submitTaxRequest() {
    $.ajax({
        url: 'hrd-vehicle-management-tax-store',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: $('#FormTaxRequest').serialize(),
        success: function (res) {
            alert(res.message || 'Pengajuan berhasil dikirim.');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('TaxRequestModal')).hide();
            loadTaxRequests();
        },
        error: function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengirim pengajuan.');
        }
    });
}

function loadTaxRequests() {
    $.get('hrd-vehicle-management-tax-requests', function (res) {
        var rows = (res && res.data) ? res.data : [];
        var tbody = $('#TaxRequestsBody');
        if (!rows.length) {
            tbody.html('<tr><td colspan="8" class="text-center text-muted py-3">Belum ada pengajuan perpanjangan STNK/KEUR.</td></tr>');
            return;
        }
        var html = '';
        rows.forEach(function (r) {
            var aksi = '<span class="text-muted small">-</span>';
            if (r.Status === 'APPROVAL_HCGA') {
                var btns = [];
                if (r.can_approve === true) {
                    btns.push('<button class="btn btn-xs btn-success me-1" onclick="actTaxRequest(' + r.ID + ', \'APPROVE\')">Approve</button>');
                    btns.push('<button class="btn btn-xs btn-danger me-1" onclick="actTaxRequest(' + r.ID + ', \'REJECT\')">Reject</button>');
                }
                if (r.can_force_approve === true) {
                    btns.push('<button class="btn btn-xs btn-dark" onclick="forceApproveTax(' + r.ID + ')">Force Approve</button>');
                }
                aksi = btns.length ? btns.join('') : '<span class="text-muted small">Menunggu Approval HCGA Mgr</span>';
            } else if (r.Status === 'RETURNED' || r.Status === 'REJECTED') {
                aksi = '<button class="btn btn-xs btn-outline-primary" onclick="openTaxResubmitModal(' + r.ID + ', ' + Number(r.Estimasi_Biaya || 0) + ', \'' + (r.Tanggal_Jatuh_Tempo || '') + '\', \'' + ((r.Approval_Note || '').replace(/\'/g, "\\'")) + '\')">Revisi &amp; Ajukan Ulang</button>';
            } else if (r.Status === 'FINANCE_SUBMITTED') {
                aksi = '<a href="hrd-vehicle-management-tax-voucher?id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1">Cetak Voucher</a>'
                    + '<button class="btn btn-xs btn-success" onclick="openTaxPayModal(' + r.ID + ')">Bayar &amp; Update</button>';
            } else if (r.Status === 'PAID') {
                aksi = '<a href="hrd-vehicle-management-tax-voucher?id=' + r.ID + '" target="_blank" class="btn btn-xs btn-outline-secondary me-1">Voucher</a>';
                if (r.Bukti_Bayar && r.Bukti_Bayar.indexOf('/') !== -1) {
                    aksi += '<a href="{{ asset("filevendor") }}/' + r.Bukti_Bayar + '" target="_blank" class="btn btn-xs btn-outline-success">Bukti</a>';
                }
            }
            html += '<tr>'
                + '<td class="fw-bold">' + (r.Tax_Request_No || '') + '</td>'
                + '<td>' + (r.Registration_No || r.Asset_No) + '<br><small class="text-muted">' + (r.Product_Description || '') + '</small></td>'
                + '<td>' + (r.Jenis_Pajak || '') + '</td>'
                + '<td>' + (r.Tanggal_Jatuh_Tempo || '-') + '</td>'
                + '<td class="text-end">Rp ' + Number(r.Estimasi_Biaya || 0).toLocaleString('id-ID') + '</td>'
                + '<td>' + (r.Voucher_No || '-') + '</td>'
                + '<td><span class="badge bg-info text-white">' + (r.Status || '') + '</span></td>'
                + '<td class="text-end text-nowrap">' + aksi + '</td>'
                + '</tr>';
        });
        tbody.html(html);
    });
}

function actTaxRequest(id, action) {
    var note = action === 'REJECT' ? (prompt('Alasan penolakan:') || '') : '';
    $.ajax({
        url: 'hrd-vehicle-management-tax-approval',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { id: id, action: action, note: note },
        success: function (res) { alert(res.message); loadTaxRequests(); },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memproses approval.'); }
    });
}

function forceApproveTax(id) {
    var reason = prompt('Alasan Force Approve (wajib diisi):');
    if (!reason || !reason.trim()) {
        return;
    }
    $.ajax({
        url: 'hrd-vehicle-management-tax-force-approve',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: { id: id, reason: reason.trim() },
        success: function (res) { alert(res.message); loadTaxRequests(); },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal melakukan Force Approve.'); }
    });
}

function openTaxResubmitModal(id, estimasi, dueDate, note) {
    $('#Resub_Tax_ID').val(id);
    $('#Resub_Estimasi_Biaya').val(estimasi || 0);
    $('#Resub_Due_Date').val(dueDate || '');
    $('#Resub_Note').val(note || '-');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('TaxResubmitModal')).show();
}

function submitTaxResubmit() {
    $.ajax({
        url: 'hrd-vehicle-management-tax-resubmit',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: $('#FormTaxResubmit').serialize(),
        success: function (res) {
            alert(res.message);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('TaxResubmitModal')).hide();
            loadTaxRequests();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengajukan ulang.'); }
    });
}

function openTaxPayModal(id) {
    $('#Pay_Tax_ID').val(id);
    $('#Pay_Bukti_File').val('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('TaxPayModal')).show();
}

function submitTaxPay() {
    var fd = new FormData(document.getElementById('FormTaxPay'));
    $.ajax({
        url: 'hrd-vehicle-management-tax-pay',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            alert(res.message);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('TaxPayModal')).hide();
            loadTaxRequests();
            VEHICLERefresh();
        },
        error: function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mencatat pembayaran.'); }
    });
}

VEHICLERefresh();
loadTaxRequests();
</script>
@endsection
