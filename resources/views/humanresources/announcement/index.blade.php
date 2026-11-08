@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div
                    class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between border-bottom">
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-1">
                            <i class="fa fa-newspaper text-primary me-2"></i>News & Announcement CMS
                        </h5>
                        <p class="text-muted small mb-0">Kelola publikasi berita, pengumuman, dan regulasi internal
                            perusahaan.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <a href="{{ route('announcement.mobile.index') }}" target="_blank"
                            class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fa fa-mobile-alt me-1"></i> Buka Tampilan Mobile
                        </a>
                        @if ($isHcga)
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"
                                onclick="openCreateModal()">
                                <i class="fa fa-plus-circle me-1"></i> Buat Berita / Pengumuman
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter bar -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Kategori</label>
                            <select id="filterCategory" class="form-select form-select-sm" onchange="reloadGrid()">
                                <option value="">-- Semua Kategori --</option>
                                <option value="ANNOUNCEMENT">Announcement</option>
                                <option value="NEWS">News</option>
                                <option value="POLICY">Policy & Guideline</option>
                                <option value="EVENT">Event</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Status</label>
                            <select id="filterStatus" class="form-select form-select-sm" onchange="reloadGrid()">
                                <option value="">-- Semua Status --</option>
                                <option value="FINAL">Published (Final)</option>
                                <option value="DRAFT">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Pencarian</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="filterSearch" class="form-control"
                                    placeholder="Cari judul atau isi berita...">
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

                    <!-- Grid Container -->
                    <div class="table-responsive border rounded bg-white p-2">
                        <div id="gridAnnouncement" style="width: 100%; height: 520px; background-color:white;"></div>
                    </div>

                    <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center text-muted small">
                        <div>
                            <span class="badge bg-primary text-white me-1">Double click baris</span> untuk melihat detail &
                            pengelolaan.
                        </div>
                        <div>
                            <span><i class="fa fa-info-circle text-info me-1"></i> Data diperbarui secara realtime dari
                                database.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container via AppModal atau Custom Dynamic Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title fw-bold text-dark" id="modalTitle">Form Pengumuman</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="modalBody">
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
        var myGrid;

        function initGrid() {
            myGrid = new dhtmlXGridObject('gridAnnouncement');
            myGrid.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
            myGrid.setHeader("ID,Kategori,Judul,Target,Pin,Status,Attach,Views,Pembuat,Tanggal Dibuat,Tanggal Publish");
            myGrid.setInitWidths("60,110,*,100,70,90,70,60,110,135,135");
            myGrid.setColAlign("center,left,left,center,center,center,center,center,left,center,center");
            myGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
            myGrid.setColSorting("int,str,str,str,str,str,str,int,str,str,str");
            myGrid.setSkin("dhx_skyblue");
            myGrid.enableAutoWidth(true);
            myGrid.init();

            myGrid.attachEvent("onRowDblClicked", function(rId, cInd) {
                openDetail(rId);
            });

            reloadGrid();
        }

        function reloadGrid() {
            if (!myGrid) return;
            var cat = encodeURIComponent($('#filterCategory').val() || '');
            var st = encodeURIComponent($('#filterStatus').val() || '');
            var q = encodeURIComponent($('#filterSearch').val() || '');

            var url = "{{ route('hrd.announcement.data') }}?category=" + cat + "&status=" + st + "&search=" + q;
            myGrid.clearAndLoad(url);
        }

        function resetFilter() {
            $('#filterCategory').val('');
            $('#filterStatus').val('');
            $('#filterSearch').val('');
            reloadGrid();
        }

        function openCreateModal() {
            $('#modalTitle').html('<i class="fa fa-plus-circle text-primary me-2"></i>Buat Berita / Pengumuman Baru');
            $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            var myModal = new bootstrap.Modal(document.getElementById('announcementModal'));
            myModal.show();

            $.get("{{ route('hrd.announcement.create') }}", function(html) {
                $('#modalBody').html(html);
            }).fail(function() {
                $('#modalBody').html('<div class="alert alert-danger">Gagal memuat form. Silakan coba lagi.</div>');
            });
        }

        function openEditModal(id) {
            $('#modalTitle').html('<i class="fa fa-edit text-primary me-2"></i>Edit Berita / Pengumuman');
            $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            var myModal = bootstrap.Modal.getInstance(document.getElementById('announcementModal'));
            if (!myModal) {
                myModal = new bootstrap.Modal(document.getElementById('announcementModal'));
            }
            myModal.show();

            $.get("{{ route('hrd.announcement.edit') }}?id=" + id, function(html) {
                $('#modalBody').html(html);
            }).fail(function() {
                $('#modalBody').html(
                    '<div class="alert alert-danger">Gagal memuat form edit. Silakan coba lagi.</div>');
            });
        }

        function openDetail(id) {
            $('#modalTitle').html('<i class="fa fa-eye text-primary me-2"></i>Detail Pengumuman');
            $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            var myModal = new bootstrap.Modal(document.getElementById('announcementModal'));
            myModal.show();

            $.get("{{ route('hrd.announcement.detail') }}?id=" + id, function(html) {
                $('#modalBody').html(html);
            }).fail(function() {
                $('#modalBody').html('<div class="alert alert-danger">Gagal memuat detail pengumuman.</div>');
            });
        }

        function deleteAnnouncement(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus pengumuman ini? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            $.ajax({
                url: "{{ route('hrd.announcement.delete') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    alert(res.message || 'Berhasil dihapus.');
                    var myModalEl = document.getElementById('announcementModal');
                    var modal = bootstrap.Modal.getInstance(myModalEl);
                    if (modal) modal.hide();
                    reloadGrid();
                },
                error: function(xhr) {
                    alert('Gagal menghapus data: ' + (xhr.responseJSON?.message || xhr.statusText));
                }
            });
        }

        $(document).ready(function() {
            initGrid();
            $('#filterSearch').on('keypress', function(e) {
                if (e.which == 13) {
                    reloadGrid();
                }
            });
        });
    </script>
@endsection
