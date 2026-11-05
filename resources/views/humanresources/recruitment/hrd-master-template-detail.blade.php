<?php
use Illuminate\Support\Facades\DB;

$id = request('ID');
$datahasil = DB::table("recruitment_master_template")->where("ID", $id)->first();
$messageJson = $datahasil ? json_encode($datahasil->Message ?? '') : json_encode('');
?>

@if(!$datahasil)
    <div class="alert alert-danger rounded-4 shadow-sm my-3 p-3 d-flex align-items-center">
        <i class="fe fe-alert-triangle me-2 fs-4"></i>
        <span>Data template tidak ditemukan atau telah dihapus.</span>
    </div>
@else
<div class="card border-0 shadow-sm rounded-4 mt-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-primary text-white me-2">ID: {{ $datahasil->Template_ID }}</span>
            <h6 class="fw-bold d-inline mb-0 text-dark">
                <i class="fe fe-edit me-1 text-primary"></i> Edit Template Notifikasi Rekrutmen
            </h6>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="$('#divlistRecruitment').html('');">
                <i class="fe fe-x me-1"></i> Tutup Form
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <form id="FormEditTemplate">
            @csrf
            <div class="row g-3">
                <!-- Kolom Kiri: Template ID & Message Type -->
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="Template_ID" class="form-label fw-semibold text-muted small">
                            <i class="fe fe-tag me-1"></i> Template ID
                        </label>
                        <input type="text" id="Template_ID" name="Template_ID" class="form-control bg-light fw-bold text-dark" value="{{ $datahasil->Template_ID }}" readonly />
                        <div class="form-text text-muted small">ID bersifat unik dan tidak dapat diubah.</div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="Message_Type" class="form-label fw-semibold text-muted small">
                            <i class="fe fe-mail me-1"></i> Tipe Pesan (Channel)
                        </label>
                        <select id="Message_Type" name="Message_Type" class="form-select">
                            <option value="EMAIL" {{ ($datahasil->Message_Type ?? '') == 'EMAIL' ? 'selected' : '' }}>📧 EMAIL</option>
                            <option value="SMS" {{ ($datahasil->Message_Type ?? '') == 'SMS' ? 'selected' : '' }}>📱 SMS / WhatsApp</option>
                        </select>
                    </div>

                    <!-- Kotak Informasi Tag Dinamis / Placeholder -->
                    <div class="card bg-light border-0 rounded-3 p-3">
                        <h6 class="fw-bold small text-primary mb-2">
                            <i class="fe fe-info me-1"></i> Variabel Dinamis Tersedia:
                        </h6>
                        <p class="small text-muted mb-2" style="font-size: 12px; line-height: 1.5;">
                            Gunakan tag khusus di bawah ini dalam pesan. Sistem akan otomatis menggantinya dengan data kandidat asli saat notifikasi dikirimkan:
                        </p>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11px;" onclick="insertPlaceholder('#_namakandidat_#')" title="Klik untuk menyisipkan">
                                <code>#_namakandidat_#</code>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" style="font-size: 11px;" onclick="insertPlaceholder('#_posisi_#')" title="Klik untuk menyisipkan">
                                <code>#_posisi_#</code>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" style="font-size: 11px;" onclick="insertPlaceholder('#_jadwal_#')" title="Klik untuk menyisipkan">
                                <code>#_jadwal_#</code>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Editor Pesan -->
                <div class="col-md-8">
                    <div class="form-group mb-2">
                        <label class="form-label fw-semibold text-muted small">
                            <i class="fe fe-message-square me-1"></i> Isi Pesan Template
                        </label>
                        <div class="border rounded-3 overflow-hidden shadow-sm" style="background: #ffffff;">
                            <div id="tambahan" style="width: 100%; height: 320px;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="$('#divlistRecruitment').html('');">
                            Batal
                        </button>
                        <button type="button" id="SaveDoc" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="fe fe-save me-1"></i> Simpan Perubahan Template
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var myEditor = new dhtmlXEditor({
    parent: "tambahan",
    toolbar: true,
    iconsPath: "./dhtmlx/imgs/",
    content: ""
});

// Set isi editor setelah initialization
myEditor.attachEvent("onReady", function(){
    myEditor.setContent({!! $messageJson !!});
});
setTimeout(function(){
    myEditor.setContent({!! $messageJson !!});
}, 300);

function insertPlaceholder(tag) {
    try {
        var current = myEditor.getContent();
        myEditor.setContent(current + " " + tag + " ");
    } catch(e) {
        console.error(e);
    }
}

$("#SaveDoc").off('click').on('click', function() {
    var $btn = $(this);
    var content = myEditor.getContent();
    
    if (!content || content.trim() === '') {
        alert('Isi pesan template tidak boleh kosong!');
        return;
    }

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...');

    var postData = {
        _token: "{{ csrf_token() }}",
        Template_ID: $("#Template_ID").val(),
        Message_Type: $("#Message_Type").val(),
        Tambahan: content
    };

    $.ajax({
        url: "hrd-master-template-detail-submit",
        type: "POST",
        data: postData,
        success: function(data) {
            if (data && data.indexOf("success") !== -1) {
                alert('Template berhasil diperbarui!');
                refreshdata();
                $('#divlistRecruitment').html('');
            } else {
                alert('Gagal memperbarui template: ' + data);
                $btn.prop('disabled', false).html('<i class="fe fe-save me-1"></i> Simpan Perubahan Template');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Terjadi kesalahan koneksi! Silakan coba lagi.');
            $btn.prop('disabled', false).html('<i class="fe fe-save me-1"></i> Simpan Perubahan Template');
        }
    });
});
</script>
@endif
