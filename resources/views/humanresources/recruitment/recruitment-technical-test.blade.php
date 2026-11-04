@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
/* Custom Technical Test UX styling (Reference: Approval Engine UI) */
.approval-metric-card {
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.06);
}
.approval-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.status-pill-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.quick-action-btn {
    transition: all 0.15s ease;
    font-weight: 500;
}
.quick-action-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}
.quick-action-btn:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed;
}
#RecruitmentLists {
    border-radius: 4px;
}
</style>

{{-- Modern Breadcrumb Header --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Technical Test (Tes Kompetensi &amp; Keahlian)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Technical Test</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-laptop-code me-1"></i> Stage: TECHNICAL_TEST Active
        </span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Quick Status Metrics --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-info-transparent text-info rounded-3 me-3">
                    <i class="fa fa-code fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Antrean Tes</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountTotal">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-warning-transparent text-warning rounded-3 me-3">
                    <i class="fa fa-clock fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Perlu Penilaian</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountPending">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-success-transparent text-success rounded-3 me-3">
                    <i class="fa fa-square-poll-vertical fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Skor Diinput</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountScored">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-secondary-transparent text-secondary rounded-3 me-3">
                    <i class="fa fa-arrow-pointer fs-20"></i>
                </div>
                <div class="overflow-hidden">
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Kandidat Terpilih</h6>
                    <div id="metricSelectedCandidate" class="fw-bold text-dark text-truncate fs-13" title="Belum dipilih">Belum dipilih</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Grid Table Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fa fa-laptop-code text-primary me-2"></i>Daftar Kandidat Tahap Tes Teknik &amp; Keahlian
            </h6>
            <small class="text-muted">Kandidat yang telah lolos Final Wawancara User dan menunggu evaluasi/skor tes kompetensi teknik.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata()" title="Muat ulang data antrean">
                <i class="fa fa-rotate me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="btnBukaEvaluasi" onclick="bukaDetailTerpilih()" title="Input / Lihat evaluasi tes teknik baris terpilih">
                <i class="fa fa-folder-open me-1"></i> Buka Evaluasi
            </button>
            <button type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" class="btn btn-sm btn-success text-white" onclick="RecruitmentLists.toExcel('./export/generate.php');" title="Export grid ke file Excel">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="RecruitmentLists" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between text-muted fs-12">
        <div>
            <i class="fa fa-circle-info text-info me-1"></i> Klik sekali untuk memilih, atau klik ganda pada baris untuk langsung membuka formulir penilaian tes teknik.
        </div>
        <div id="grid-status-info" class="fw-semibold">
            Status: Siap
        </div>
    </div>
</div>

<!-- Container Detail Technical Test -->
<div id="divlistRecruitment"></div>

<script>
    var RecruitmentLists;
    var currentRecruitmentId = null;

    function doInitGridRecruitment() {
        if (RecruitmentLists) {
            try {
                RecruitmentLists.destructor();
            } catch(e) {}
        }

        RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
        RecruitmentLists.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
        RecruitmentLists.setHeader("No.,ID Reg,Nama Lengkap,Jenis Kelamin,Tgl Lahir,Posisi Dilamar,Pendidikan,Skor Tes,No PO,Deskripsi PO,Status,Aging (Hari)");
        RecruitmentLists.attachHeader("#rspan,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter");
        RecruitmentLists.setInitWidths("50,135,190,100,105,170,110,85,120,160,130,90");
        RecruitmentLists.setColAlign("center,left,left,center,center,left,center,center,left,left,center,center");
        RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,int");
        RecruitmentLists.enableSmartRendering(true, 150);
        RecruitmentLists.enableKeyboardSupport(true);
        RecruitmentLists.setSkin("dhx_skyblue");
        RecruitmentLists.enableMultiselect(false);

        // Single click event
        RecruitmentLists.attachEvent("onRowSelect", function(rId, cInd) {
            currentRecruitmentId = rId;
            updateSelectedCandidateIndicator(rId);
        });

        // Double click event -> buka detail
        RecruitmentLists.attachEvent("onRowDblClicked", function(rId, cInd) {
            currentRecruitmentId = rId;
            updateSelectedCandidateIndicator(rId);
            bukaDetail(rId, true);
        });

        // Event selesai load data XML
        RecruitmentLists.attachEvent("onXLE", function() {
            calculateMetrics();

            if (currentRecruitmentId) {
                try {
                    if (RecruitmentLists.getRowIndex(currentRecruitmentId) !== -1) {
                        RecruitmentLists.selectRowById(currentRecruitmentId, false, true, false);
                        updateSelectedCandidateIndicator(currentRecruitmentId);
                    } else {
                        currentRecruitmentId = null;
                        updateSelectedCandidateIndicator(null);
                    }
                } catch(e) {
                    currentRecruitmentId = null;
                    updateSelectedCandidateIndicator(null);
                }
            } else {
                updateSelectedCandidateIndicator(null);
            }
        });

        RecruitmentLists.init();
        refreshdata();
    }

    function refreshdata() {
        if (!RecruitmentLists) return;
        $('#grid-status-info').html('<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data...</span>');
        RecruitmentLists.clearAndLoad("{{ url('xml-hrd-recruitment-technical-test') }}", function() {
            var rowCount = RecruitmentLists.getRowsNum();
            $('#grid-status-info').html('Total Kandidat Tes Teknik: <strong>' + rowCount + '</strong>');
            calculateMetrics();
        });
    }

    function calculateMetrics() {
        if (!RecruitmentLists) return;
        var total = RecruitmentLists.getRowsNum();
        var scoredCount = 0;
        var pendingCount = 0;

        for (var i = 0; i < total; i++) {
            var rId = RecruitmentLists.getRowId(i);
            var scoreVal = $.trim(RecruitmentLists.cells(rId, 7).getValue());

            if (scoreVal !== '' && scoreVal !== '-' && !isNaN(parseFloat(scoreVal))) {
                scoredCount++;
            } else {
                pendingCount++;
            }
        }

        $('#metricCountTotal').text(total);
        $('#metricCountPending').text(pendingCount);
        $('#metricCountScored').text(scoredCount);
    }

    function updateSelectedCandidateIndicator(id) {
        var el = document.getElementById('metricSelectedCandidate');
        if (!el) return;

        if (!id || !RecruitmentLists) {
            el.innerHTML = '<span class="text-muted fw-normal">Belum dipilih</span>';
            el.title = 'Belum dipilih';
            return;
        }

        try {
            var name = RecruitmentLists.cells(id, 2).getValue() || '-';
            var pos = RecruitmentLists.cells(id, 5).getValue() || '-';
            el.innerHTML = '<span class="text-primary">' + name + '</span> <small class="text-muted">(' + pos + ')</small>';
            el.title = name + ' (' + pos + ')';
        } catch(e) {
            el.innerHTML = '<span class="text-primary">ID: #' + id + '</span>';
            el.title = 'ID: #' + id;
        }
    }

    function bukaDetailTerpilih() {
        if (!RecruitmentLists) return;
        var rId = RecruitmentLists.getSelectedRowId();
        if (!rId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Baris',
                    text: 'Silakan pilih salah satu kandidat pada tabel antrean terlebih dahulu.'
                });
            } else {
                alert('Silakan pilih salah satu kandidat pada tabel antrean terlebih dahulu.');
            }
            return;
        }
        bukaDetail(rId, true);
    }

    function bukaDetail(rId, autoScroll) {
        currentRecruitmentId = rId;
        updateSelectedCandidateIndicator(rId);

        var container = document.getElementById('divlistRecruitment');
        container.innerHTML = '<div class="card shadow-sm border p-4 text-center my-3">' +
            '<div class="spinner-border text-primary mx-auto mb-2" role="status"></div>' +
            '<div class="text-muted small">Memuat formulir evaluasi tes teknik &amp; profil kandidat...</div>' +
        '</div>';

        LoadM('divlistRecruitment', "{{ url('hrd-recruitment-technical-test-detail') }}?ID=" + encodeURIComponent(rId));

        if (autoScroll) {
            setTimeout(function() {
                var detailEl = document.getElementById('divlistRecruitment');
                if (detailEl) {
                    detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 300);
        }
    }

    function RecruitmentListsCloseDetail() {
        $('#divlistRecruitment').html('');
    }

    $(document).ready(function() {
        doInitGridRecruitment();
    });
</script>
@endsection
