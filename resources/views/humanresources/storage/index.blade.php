@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            {{ $moduleTitle }}
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Storage Management</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">{{ $moduleType === 'REGULATION' ? 'Regulasi' : 'Informasi' }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0">
        <a href="{{ route('announcement.mobile.index') }}" target="_blank"
            class="btn btn-sm rounded-pill px-3 shadow-sm"
            style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-mobile-alt me-1"></i> Feed Mobile
        </a>
        @if ($isHcga)
            <button type="button" class="btn btn-sm rounded-pill px-3 shadow-sm"
                style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;"
                onclick="openSettingsModal()">
                <i class="fa fa-cog me-1"></i> Setting Approval
                <span class="badge bg-light text-dark ms-1">{{ $approvalMode }}</span>
            </button>
            <button type="button" class="btn btn-light text-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm"
                onclick="openCreateModal()">
                <i class="fa fa-plus-circle me-1"></i> Tambah {{ $moduleType === 'REGULATION' ? 'Regulasi' : 'Informasi' }}
            </button>
        @endif
    </div>
</div>

    <div class="row">
        <div class="col-12">
            <!-- Main Card -->
            <div class="card shadow-sm border-0 mb-3">
                <!-- KPI Summary -->
                <div class="card-body pb-2">
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-light text-center">
                                <div class="small text-muted">Total Dokumen</div>
                                <div class="fs-5 fw-bold text-dark">{{ $stats['total'] }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-success bg-opacity-10 text-center">
                                <div class="small fw-semibold">Aktif Berlaku</div>
                                <div class="fs-5 fw-bold">{{ $stats['active'] }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-info bg-opacity-10 text-center">
                                <div class="small fw-semibold">Terjadwal (Scheduled)</div>
                                <div class="fs-5 fw-bold">{{ $stats['scheduled'] }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-warning bg-opacity-10 text-center">
                                <div class="small fw-semibold">Waiting Approval</div>
                                <div class="fs-5 fw-bold">{{ $stats['waiting_approval'] }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-secondary bg-opacity-10 text-center">
                                <div class="small fw-semibold">Draft</div>
                                <div class="fs-5 fw-bold">{{ $stats['draft'] }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-danger bg-opacity-10 text-center">
                                <div class="small fw-semibold">Expired / Revoked</div>
                                <div class="fs-5 fw-bold">{{ $stats['expired'] + $stats['revoked'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Tipe Dokumen</label>
                            <select id="filterSubType" class="form-select form-select-sm" onchange="reloadGrid()">
                                <option value="">-- Semua Tipe --</option>
                                @foreach($subTypes as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted">Status Efektif</label>
                            <select id="filterStatus" class="form-select form-select-sm" onchange="reloadGrid()">
                                <option value="">-- Semua Status --</option>
                                <option value="ACTIVE">ACTIVE (Berlaku)</option>
                                <option value="SCHEDULED">SCHEDULED (Akan Berlaku)</option>
                                <option value="EXPIRED">EXPIRED (Kedaluwarsa)</option>
                                <option value="WAITING_APPROVAL">WAITING APPROVAL</option>
                                <option value="DRAFT">DRAFT</option>
                                <option value="REJECTED">REJECTED</option>
                                <option value="REVOKED">REVOKED (Dicabut)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted">Target</label>
                            <select id="filterAudience" class="form-select form-select-sm" onchange="reloadGrid()">
                                <option value="">-- Semua --</option>
                                <option value="ALL">Semua Karyawan</option>
                                <option value="DEPARTMENT">Departemen Tertentu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Pencarian</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="filterSearch" class="form-control" placeholder="Cari judul / nomor dokumen...">
                                <button class="btn btn-outline-secondary" type="button" onclick="reloadGrid()">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilter()">
                                <i class="fa fa-redo me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- dhtmlXGrid Container -->
                    <div class="table-responsive border rounded bg-white p-2">
                        <div id="gridStorage" style="width: 100%; height: 500px; background-color:white;"></div>
                    </div>

                    <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center text-muted small">
                        <div>
                            <span class="badge bg-primary text-white me-1">Double click baris</span> untuk membuka halaman detail, riwayat versi, preview PDF, dan tracking pembaca.
                        </div>
                        <div>
                            <span><i class="fa fa-info-circle text-info me-1"></i> Dokumen berstatus <strong>SCHEDULED / EXPIRED / REVOKED</strong> otomatis disembunyikan dari feed karyawan di Mobile Apps.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div class="modal fade" id="storageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title fw-bold text-dark" id="storageModalTitle">Form Storage Management</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="storageModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var storageGrid;
        var moduleType = "{{ $moduleType }}";

        function initStorageGrid() {
            storageGrid = new dhtmlXGridObject('gridStorage');
            storageGrid.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
            storageGrid.setHeader("ID,Tipe,No. Dokumen,Judul,Versi,Masa Berlaku,Target Audience,Status Efektif,Sync Mobile,Pembuat,Update Terakhir");
            storageGrid.setInitWidths("55,110,140,*,75,180,130,115,85,110,135");
            storageGrid.setColAlign("center,center,left,left,center,left,left,center,center,left,center");
            storageGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
            storageGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
            storageGrid.setSkin("dhx_skyblue");
            storageGrid.enableAutoWidth(true);
            storageGrid.init();

            storageGrid.attachEvent("onRowDblClicked", function(rId) {
                window.location.href = "{{ route('hrd.storage.detail') }}?id=" + rId;
            });

            reloadGrid();
        }

        function reloadGrid() {
            if (!storageGrid) return;
            var sub = encodeURIComponent($('#filterSubType').val() || '');
            var st = encodeURIComponent($('#filterStatus').val() || '');
            var aud = encodeURIComponent($('#filterAudience').val() || '');
            var q = encodeURIComponent($('#filterSearch').val() || '');

            var url = "{{ route('hrd.storage.data') }}?module_type=" + moduleType
                + "&sub_type=" + sub
                + "&effective_status=" + st
                + "&target_audience=" + aud
                + "&search=" + q;

            storageGrid.clearAndLoad(url);
        }

        function resetFilter() {
            $('#filterSubType').val('');
            $('#filterStatus').val('');
            $('#filterAudience').val('');
            $('#filterSearch').val('');
            reloadGrid();
        }

        function showStorageModal(titleHtml, url) {
            $('#storageModalTitle').html(titleHtml);
            $('#storageModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            var el = document.getElementById('storageModal');
            var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            modal.show();

            $.get(url, function(html) {
                $('#storageModalBody').html(html);
            }).fail(function(xhr) {
                $('#storageModalBody').html('<div class="alert alert-danger">Gagal memuat data: ' + (xhr.responseJSON?.message || xhr.statusText) + '</div>');
            });
        }

        function openCreateModal() {
            showStorageModal(
                '<i class="fa fa-plus-circle text-primary me-2"></i>Tambah {{ $moduleType === "REGULATION" ? "Regulasi" : "Informasi" }} Baru (v1)',
                "{{ route('hrd.storage.create') }}?module_type=" + moduleType
            );
        }

        function openSettingsModal() {
            showStorageModal(
                '<i class="fa fa-cog text-primary me-2"></i>Pengaturan Alur Approval Storage Management',
                "{{ route('hrd.storage.settings') }}"
            );
        }

        $(document).ready(function() {
            initStorageGrid();
            $('#filterSearch').on('keypress', function(e) {
                if (e.which == 13) {
                    reloadGrid();
                }
            });
        });
    </script>
@endsection
