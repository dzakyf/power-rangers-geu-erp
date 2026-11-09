@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumbs and Header -->
        <div class="row align-items-center mb-3">
            <div class="col-md-7">
                <div class="page-title-box">
                    <h5 class="fw-bold mb-1">
                        <i class="fa fa-compass text-primary me-2"></i>Corporate Culture & Regulation CMS
                    </h5>
                    <p class="text-muted small mb-0">Kelola Visi, Misi, Core Value, dan Peraturan Perusahaan</p>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-2 mt-md-0">
                <a href="{{ route('company-guideline.mobile.index') }}" target="_blank"
                    class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">
                    <i class="fa fa-mobile-alt me-1"></i> Buka Tampilan Mobile
                </a>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openCreateModal()">
                    <i class="fa fa-plus-circle me-1"></i> Tambah Panduan / Peraturan
                </button>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Kategori / Jenis</label>
                        <select id="filterType" class="form-select form-select-sm" onchange="applyFilter()">
                            <option value="">Semua Kategori</option>
                            <option value="VISI">Visi Perusahaan</option>
                            <option value="MISI">Misi Perusahaan</option>
                            <option value="CORE_VALUE">Core Value</option>
                            <option value="PERATURAN">Peraturan Perusahaan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                        <select id="filterStatus" class="form-select form-select-sm" onchange="applyFilter()">
                            <option value="">Semua Status</option>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Pencarian</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="filterSearch" class="form-control" placeholder="Cari judul, kode, konten..." onkeyup="if(event.keyCode==13) applyFilter()">
                            <button class="btn btn-outline-secondary" type="button" onclick="applyFilter()">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end pt-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="resetFilter()">
                            <i class="fa fa-undo me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Grid Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="small text-muted">
                        <i class="fa fa-info-circle me-1"></i> Klik ganda (double-click) pada baris tabel untuk melihat detail.
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="editSelectedRow()" title="Ubah Item Terpilih">
                            <i class="fa fa-edit me-1"></i> Ubah
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteSelectedRow()" title="Hapus Item Terpilih">
                            <i class="fa fa-trash me-1"></i> Hapus
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <div id="gridGuideline" style="width: 100%; height: 520px; background-color:white;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form / Detail -->
<div class="modal fade" id="guidelineModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h6 class="modal-title fw-bold text-dark" id="guidelineModalLabel">
                    <i class="fa fa-compass text-primary me-2"></i>Panduan & Peraturan Perusahaan
                </h6>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <div class="modal-body p-3" id="modalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var myGrid;

    $(document).ready(function() {
        initGrid();
        loadGridData();
    });

    function initGrid() {
        myGrid = new dhtmlXGridObject('gridGuideline');
        myGrid.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
        myGrid.setHeader("ID,Jenis,Kode,Judul,Urutan,Status,Lampiran,Diperbarui Oleh,Tanggal");
        myGrid.setInitWidths("60,130,110,*,70,90,90,140,130");
        myGrid.setColAlign("center,left,left,left,center,center,center,left,center");
        myGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        myGrid.setColSorting("int,str,str,str,int,str,str,str,str");
        myGrid.enableAutoWidth(true);
        myGrid.enableResizing("true,true,true,true,true,true,true,true,true");
        myGrid.setSkin("dhx_skyblue");
        myGrid.attachEvent("onRowDblClicked", function(rId, cInd) {
            openDetailModal(rId);
        });
        myGrid.init();
    }

    function loadGridData() {
        var type = $('#filterType').val();
        var status = $('#filterStatus').val();
        var search = $('#filterSearch').val();

        var url = "{{ route('hrd.company-guideline.data') }}?type=" + encodeURIComponent(type) + 
                  "&status=" + encodeURIComponent(status) + 
                  "&search=" + encodeURIComponent(search);
        myGrid.clearAndLoad(url);
    }

    function applyFilter() {
        loadGridData();
    }

    function resetFilter() {
        $('#filterType').val('');
        $('#filterStatus').val('');
        $('#filterSearch').val('');
        loadGridData();
    }

    function openCreateModal() {
        var modalEl = document.getElementById('guidelineModal');
        var myModal = new bootstrap.Modal(modalEl);
        $('#guidelineModalLabel').html('<i class="fa fa-plus-circle text-primary me-2"></i>Tambah Panduan / Peraturan');
        $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Memuat form...</p></div>');
        myModal.show();

        $.get("{{ route('hrd.company-guideline.create') }}", function(html) {
            $('#modalBody').html(html);
        }).fail(function() {
            $('#modalBody').html('<div class="alert alert-danger py-2">Gagal memuat form. Silakan coba kembali.</div>');
        });
    }

    function editSelectedRow() {
        var selectedId = myGrid.getSelectedRowId();
        if (!selectedId) {
            alert('Silakan pilih salah satu baris terlebih dahulu!');
            return;
        }
        openEditModal(selectedId);
    }

    function openEditModal(id) {
        var modalEl = document.getElementById('guidelineModal');
        var myModal = bootstrap.Modal.getInstance(modalEl);
        if (!myModal) {
            myModal = new bootstrap.Modal(modalEl);
        }
        $('#guidelineModalLabel').html('<i class="fa fa-edit text-primary me-2"></i>Ubah Panduan / Peraturan');
        $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Memuat data...</p></div>');
        myModal.show();

        $.get("{{ route('hrd.company-guideline.edit') }}?id=" + id, function(html) {
            $('#modalBody').html(html);
        }).fail(function() {
            $('#modalBody').html('<div class="alert alert-danger py-2">Gagal memuat form edit.</div>');
        });
    }

    function openDetailModal(id) {
        var modalEl = document.getElementById('guidelineModal');
        var myModal = bootstrap.Modal.getInstance(modalEl);
        if (!myModal) {
            myModal = new bootstrap.Modal(modalEl);
        }
        $('#guidelineModalLabel').html('<i class="fa fa-eye text-primary me-2"></i>Detail Panduan / Peraturan');
        $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Memuat detail...</p></div>');
        myModal.show();

        $.get("{{ route('hrd.company-guideline.detail') }}?id=" + id, function(html) {
            $('#modalBody').html(html);
        }).fail(function() {
            $('#modalBody').html('<div class="alert alert-danger py-2">Gagal memuat detail data.</div>');
        });
    }

    function deleteSelectedRow() {
        var selectedId = myGrid.getSelectedRowId();
        if (!selectedId) {
            alert('Silakan pilih baris yang ingin dihapus terlebih dahulu!');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            return;
        }

        $.ajax({
            url: "{{ route('hrd.company-guideline.delete') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: selectedId
            },
            success: function(resp) {
                if (resp.success) {
                    alert(resp.message);
                    loadGridData();
                } else {
                    alert('Gagal menghapus data.');
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat menghapus data.');
            }
        });
    }
</script>
@endsection

