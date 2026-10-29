<?php
// Filter jenis pengajuan dari menu (?jenis=MUTATION / PROMOTION) atau dropdown filter.
$jenisQ = isset($_GET['jenis']) ? strtoupper(trim((string) $_GET['jenis'])) : '';
if (!in_array($jenisQ, ['MUTATION', 'PROMOTION'])) {
    $jenisQ = '';
}
?>

@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>FORM PERUBAHAN STATUS KARYAWAN</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Human Resources</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Form Perubahan Status</span>
            </li>
        </ol>
    </div>

    <div class="card border-primary shadow-sm mb-4">
        <div class="card-body">
            <!-- Filter: jenis + pencarian -->
            <div class="row mb-2">
                <div class="col-md-2 col-6">
                    <select id="STCH_F_JENIS" class="form-control form-control-sm">
                        <option value="">Semua Jenis</option>
                        <option value="MUTATION" {{ $jenisQ == 'MUTATION' ? 'selected' : '' }}>MUTATION</option>
                        <option value="PROMOTION" {{ $jenisQ == 'PROMOTION' ? 'selected' : '' }}>PROMOTION</option>
                    </select>
                </div>
                <div class="col-md-4 col-6">
                    <input type="text" id="STCH_F_Q" class="form-control form-control-sm" placeholder="Cari No. Form / Nama / NIK..." />
                </div>
                <div class="col-md-2 col-6">
                    <button type="button" id="STCH_Filter" class="btn btn-primary btn-sm btn-block">Cari</button>
                </div>
            </div>
            <!-- Toolbar -->
            <div id="STCH_TOOLBAR" class="d-flex flex-wrap align-items-center mb-2">
                <button type="button" id="STCH_New" class="btn btn-primary mr-2">
                    <i class="fas fa-plus mr-1"></i> New Data
                </button>
                <button type="button" id="STCH_Detail" class="btn btn-info mr-2">
                    <i class="fas fa-eye mr-1"></i> Detail Data
                </button>
                <button type="button" id="STCH_Edit" class="btn btn-warning mr-2">
                    <i class="fas fa-edit mr-1"></i> Edit Data
                </button>
                <button type="button" id="STCH_Print" class="btn btn-secondary mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Print
                </button>
                <button type="button" id="STCH_Delete" class="btn btn-danger mr-2">
                    <i class="fas fa-trash mr-1"></i> Hapus Data
                </button>
                <button type="button" id="STCH_Refresh" class="btn btn-success">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Data
                </button>
            </div>

            <!-- Grid -->
            <div id="STCH_GRID" style="width:auto;height:450px;background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Detail Panel -->
    <div id="STCH_DTL"></div>

    <script>
        // -- Menu dari mana halaman dibuka (-- / --) --
        var STCH_JENIS = '<?= $jenisQ ?>';

        // -- Grid object --
        STATUSCHANGE = new dhtmlXGridObject('STCH_GRID');
        STATUSCHANGE.setImagePath("./dhtmlx/imgs/");
        STATUSCHANGE.setSkin('dhx_skyblue');
        STATUSCHANGE.setEditable(false);
        STATUSCHANGE.enableSmartRendering(true, 150);

        STATUSCHANGE.setHeader(
            "No.,Nomor Form,Jenis,Nama,NIK,Tanggal Join,Jabatan Sekarang," +
            "Status Sekarang,Jabatan Usulan,Status Usulan,Gaji Usulan (Rp)," +
            "Tanggal Efektif,Tanggal Dibuat"
        );
        STATUSCHANGE.attachHeader(
            "&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter," +
            "#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
        );

        STATUSCHANGE.setInitWidths(
            "60,150,110,180,110,105,160,150,160,150,120,120,110"
        );
        STATUSCHANGE.setColAlign(
            "center,center,center,left,center,center,left,left,left,left,right,center,center"
        );
        STATUSCHANGE.setColSorting(
            "int,str,str,str,str,str,str,str,str,str,str,str,str"
        );
        STATUSCHANGE.setColTypes(
            "ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro"
        );

        // -- Row double-click -> Detail --
        STATUSCHANGE.attachEvent("onRowDblClicked", function(id) {
            LoadM('STCH_DTL', 'hrd-status-change-detail?ID=' + id);
        });

        // -- Load data --
        function STCHLoad() {
            var q = [];
            if ($('#STCH_F_Q').val()) q.push('q=' + encodeURIComponent($('#STCH_F_Q').val()));
            if ($('#STCH_F_JENIS').val()) q.push('jenis=' + encodeURIComponent($('#STCH_F_JENIS').val()));
            var url = 'hrd-status-change-data' + (q.length ? '?' + q.join('&') : '');
            STATUSCHANGE.clearAndLoad(url);
        }

        $('#STCH_Filter').on('click', STCHLoad);
        $('#STCH_F_Q').on('keydown', function(e) {
            if (e.key === 'Enter') STCHLoad();
        });
        $('#STCH_F_JENIS').on('change', STCHLoad);

        // -- Toolbar handlers --
        $('#STCH_New').on('click', function() {
            var jenis = $('#STCH_F_JENIS').val() || STCH_JENIS;
            LoadM('STCH_DTL', 'hrd-status-change-new' + (jenis ? '?jenis=' + encodeURIComponent(jenis) : ''));
        });

        function STCHSelectedId() {
            var id = STATUSCHANGE.getSelectedId();
            if (!id) {
                alert('Mohon pilih baris terlebih dahulu.');
                return null;
            }
            return id;
        }

        $('#STCH_Detail').on('click', function() {
            var id = STCHSelectedId();
            if (id === null) return;
            LoadM('STCH_DTL', 'hrd-status-change-detail?ID=' + id);
        });

        $('#STCH_Edit').on('click', function() {
            var id = STCHSelectedId();
            if (id === null) return;
            LoadM('STCH_DTL', 'hrd-status-change-edit?ID=' + id);
        });

        $('#STCH_Print').on('click', function() {
            var id = STCHSelectedId();
            if (id === null) return;
            window.open('hrd-status-change-print?ID=' + id, '_blank');
        });

        $('#STCH_Delete').on('click', function() {
            var id = STCHSelectedId();
            if (id === null) return;

            if (!confirm('Hapus form perubahan status ini?')) return;

            $.blockUI({ message: '<div class="small">Menghapus data...</div>' });

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                url: 'hrd-status-change-delete',
                type: 'POST',
                data: 'ID=' + encodeURIComponent(id),
                success: function(data) {
                    $.unblockUI();
                    if (data.status == 'success') {
                        alert('Data form perubahan status berhasil dihapus!');
                        STCHLoad();
                    } else {
                        alert(data.message || 'Terjadi kesalahan!');
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    $.unblockUI();
                    alert('Error: ' + (xhr.responseText || errorThrown));
                }
            });
        });

        $('#STCH_Refresh').on('click', function() {
            STCHLoad();
        });

        // -- Init --
        STCHLoad();
    </script>
@endsection