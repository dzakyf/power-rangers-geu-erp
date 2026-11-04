<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? '';
$priv = Auth::user()->Priv_ID ?? '';

// Fallback jika controller tidak passing variabel
if (!isset($recruitment)) {
    $id = $_GET['ID'] ?? null;
    $recruitment = DB::table("recruitment")->where("ID", $id)->first();
}
if (!isset($requisition) && $recruitment) {
    $requisition = DB::table("requisition")->where("Task_ID", $recruitment->Requisition_ID)->first();
}

// Menentukan nilai hasil psikotest dengan fallback aman ke Psikotes_Result / Psikotest_Result
$hasilPsikotest = '';
if ($recruitment) {
    $hasilPsikotest = $recruitment->Psikotes_Result ?? ($recruitment->Psikotest_Result ?? '');
}
?>

@if(!$recruitment)
    <div class="alert alert-danger shadow-sm">
        <i class="fa fa-triangle-exclamation me-2"></i> Data kandidat tidak ditemukan.
    </div>
@else
<div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
    <!-- Header Card -->
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-white p-2">
                <i class="fa fa-brain fs-14"></i>
            </span>
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    Form Hasil Psikotest: {{ $recruitment->Name }} ({{ $recruitment->Task_ID }})
                </h6>
                <small class="text-muted">Posisi: <strong>{{ $requisition->Job_Position ?? ($recruitment->Position ?? '-') }}</strong> | Status: <span class="badge bg-info text-white">{{ $recruitment->Status }}</span></small>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divlistRecruitment').html('');">
                <i class="fa fa-xmark me-1"></i> Tutup Form
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Informasi Ringkas Kandidat -->
        <div class="row g-3 mb-4 p-3 bg-light rounded border">
            <div class="col-md-3">
                <small class="text-muted d-block">Nama Lengkap</small>
                <span class="fw-semibold text-dark">{{ $recruitment->Name }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Jenis Kelamin / Lahir</small>
                <span class="fw-semibold text-dark">{{ $recruitment->Gender }} ({{ $recruitment->Birth_Date ? date('d-m-Y', strtotime($recruitment->Birth_Date)) : '-' }})</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Pendidikan Terakhir</small>
                <span class="fw-semibold text-dark">{{ $recruitment->Last_Education ?? '-' }} - {{ $recruitment->Last_Education_From ?? '-' }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Kontak Telepon</small>
                <span class="fw-semibold text-dark">{{ $recruitment->Handphone ?: ($recruitment->Phone ?: '-') }}</span>
            </div>
        </div>

        <form id="formPsikotestDetail" method="POST" onsubmit="return false;">
            @csrf
            <input type="hidden" id="ID" name="ID" value="{{ $recruitment->ID }}">
            <input type="hidden" id="Psikotest_File" name="Psikotest_File" value="{{ $recruitment->Psikotest_File }}">

            <!-- Bagian Unggah dan Nilai Psikotest -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-xs">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa fa-cloud-arrow-up text-primary me-2"></i>Berkas Hasil Psikotest
                        </h6>
                        <p class="text-muted fs-13 mb-3">
                            Unggah lembar hasil / laporan psikotes kandidat (PDF/JPG/PNG).
                        </p>
                        
                        <!-- DHTMLX Vault Container -->
                        <div id="vault_psikotest" class="with-header border rounded mb-2" style="height:140px; margin:0px; width:100%;"></div>
                        
                        <div class="input-group input-group-sm mt-2">
                            <span class="input-group-text bg-light text-muted"><i class="fa fa-paperclip"></i></span>
                            <input type="text" id="Psikotest_File_Display" class="form-control bg-light" value="{{ $recruitment->Psikotest_File }}" placeholder="Belum ada berkas diunggah" readonly>
                            @if(!empty($recruitment->Psikotest_File))
                                <a href="{{ asset($recruitment->Psikotest_File) }}" target="_blank" class="btn btn-outline-primary" id="btnPreviewPsikoFile" title="Lihat Berkas">
                                    <i class="fa fa-eye"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-xs">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa fa-star text-warning me-2"></i>Penilaian &amp; Catatan Psikotest
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13 mb-1">Skor Psikotest (0 - 100)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="Psikotest_Score" name="Psikotest_Score" value="{{ $recruitment->Psikotest_Score }}" placeholder="Contoh: 85.5">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13 mb-1">Kesimpulan / Hasil Psikotest</label>
                            <select class="form-select" id="Psikotest_Result" name="Psikotest_Result">
                                <option value="" {{ empty($hasilPsikotest) ? 'selected' : '' }}>-- Pilih Kesimpulan --</option>
                                <option value="DISARANKAN" {{ ($hasilPsikotest == 'DISARANKAN') ? 'selected' : '' }}>DISARANKAN</option>
                                <option value="DAPAT DIPERTIMBANGKAN" {{ ($hasilPsikotest == 'DAPAT DIPERTIMBANGKAN') ? 'selected' : '' }}>DAPAT DIPERTIMBANGKAN</option>
                                <option value="TIDAK DISARANKAN" {{ ($hasilPsikotest == 'TIDAK DISARANKAN') ? 'selected' : '' }}>TIDAK DISARANKAN</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13 mb-1">Catatan / Keterangan Psikotes</label>
                            <textarea class="form-control" id="Psikotest_Note" name="Psikotest_Note" rows="3" placeholder="Masukkan catatan hasil evaluasi psikotes...">{{ $recruitment->Psikotest_Note }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap align-items-center justify-content-between border-top pt-3 mt-4 gap-2">
                <div>
                    <button type="button" class="btn btn-outline-secondary" onclick="$('#divlistRecruitment').html('');">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnSavePsiko" class="btn btn-primary" onclick="simpanHasilPsikotest()">
                        <i class="fa fa-floppy-disk me-1"></i> Simpan Skor &amp; Berkas
                    </button>
                    <button type="button" id="btnApprovePsiko" class="btn btn-success text-white" onclick="luluskanKandidat()">
                        <i class="fa fa-circle-check me-1"></i> Setujui &amp; Lanjut ke Interview HR
                    </button>
                    <button type="button" id="btnRejectPsiko" class="btn btn-outline-danger" onclick="tolakKandidat()">
                        <i class="fa fa-circle-xmark me-1"></i> Tolak Kandidat
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Inisialisasi Vault untuk berkas psikotest
if (typeof dhx !== 'undefined' && typeof dhx.Vault !== 'undefined') {
    var vault_psikotest = new dhx.Vault("vault_psikotest", {
        uploader: {
            target: "upload-rekrut-psikotest.php"
        },
        filesLimit: 1
    });

    vault_psikotest.events.on("uploadComplete", function(obj) {
        if (obj && obj[0] && obj[0].name) {
            $("#Psikotest_File").val(obj[0].name);
            $("#Psikotest_File_Display").val(obj[0].name);
        }
    });

    vault_psikotest.events.on("uploadBegin", function(obj) {
        $("#Psikotest_File").val("");
        $("#Psikotest_File_Display").val("Mengunggah...");
    });
}

function simpanHasilPsikotest() {
    var id = $("#ID").val();
    var score = $("#Psikotest_Score").val();
    var result = $("#Psikotest_Result").val();
    var note = $("#Psikotest_Note").val();
    var file = $("#Psikotest_File").val();

    $("#btnSavePsiko").prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ route('hrd-recruitment-psikotest-detail-submit') }}",
        type: "POST",
        data: {
            ID: id,
            Psikotest_Score: score,
            Psikotest_Result: result,
            Psikotest_Note: note,
            Psikotest_File: file
        },
        success: function(response) {
            $("#btnSavePsiko").prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i> Simpan Skor & Berkas');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data hasil psikotest berhasil disimpan.',
                    confirmButtonColor: '#0d6efd'
                });
            } else {
                alert('Data hasil psikotest berhasil disimpan.');
            }
            if (typeof refreshdata === 'function') {
                refreshdata();
            }
        },
        error: function(jqXHR) {
            $("#btnSavePsiko").prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i> Simpan Skor & Berkas');
            var errMsg = 'Gagal menyimpan data psikotest.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errMsg = jqXHR.responseJSON.message;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
            } else {
                alert(errMsg);
            }
        }
    });
}

function luluskanKandidat() {
    var id = $("#ID").val();
    var score = $("#Psikotest_Score").val();
    var result = $("#Psikotest_Result").val();
    var note = $("#Psikotest_Note").val();
    var file = $("#Psikotest_File").val();

    if (!score) {
        alert("Harap masukkan skor psikotest terlebih dahulu sebelum menyetujui.");
        return;
    }

    if (confirm("Apakah Anda yakin ingin menyetujui hasil psikotes kandidat ini dan melanjutkan ke tahap Interview HR?")) {
        $("#btnApprovePsiko").prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memproses...');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('hrd-recruitment-approve-psikotest') }}",
            type: "POST",
            data: {
                id: id,
                score: score,
                result: result,
                note: note,
                psikotest_file: file
            },
            success: function(resp) {
                $("#btnApprovePsiko").prop('disabled', false).html('<i class="fa fa-circle-check me-1"></i> Setujui & Lanjut ke Interview HR');
                var res = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (res.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Psikotest Disetujui',
                            text: res.message,
                            confirmButtonColor: '#198754'
                        }).then(function() {
                            $('#divlistRecruitment').html('');
                            refreshdata();
                        });
                    } else {
                        alert(res.message);
                        $('#divlistRecruitment').html('');
                        refreshdata();
                    }
                } else {
                    alert(res.message || 'Gagal memproses kelulusan psikotes.');
                }
            },
            error: function(jqXHR) {
                $("#btnApprovePsiko").prop('disabled', false).html('<i class="fa fa-circle-check me-1"></i> Setujui & Lanjut ke Interview HR');
                var msg = 'Terjadi kesalahan sistem saat menyetujui psikotes.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    msg = jqXHR.responseJSON.message;
                }
                alert(msg);
            }
        });
    }
}

function tolakKandidat() {
    var id = $("#ID").val();
    var reason = prompt("Masukkan alasan penolakan kandidat pada tahap psikotest:");
    if (reason === null) return;
    if (reason.trim() === '') {
        alert("Alasan penolakan tidak boleh kosong.");
        return;
    }

    $("#btnRejectPsiko").prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memproses...');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ url('hrd-recruitment-reject-psikotest') }}",
        type: "POST",
        data: {
            id: id,
            reason: reason
        },
        success: function(resp) {
            $("#btnRejectPsiko").prop('disabled', false).html('<i class="fa fa-circle-xmark me-1"></i> Tolak Kandidat');
            var res = (typeof resp === 'string') ? JSON.parse(resp) : resp;
            if (res.status === 'success') {
                alert(res.message);
                $('#divlistRecruitment').html('');
                refreshdata();
            } else {
                alert(res.message || 'Gagal menolak kandidat.');
            }
        },
        error: function(jqXHR) {
            $("#btnRejectPsiko").prop('disabled', false).html('<i class="fa fa-circle-xmark me-1"></i> Tolak Kandidat');
            var msg = 'Gagal menolak kandidat.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                msg = jqXHR.responseJSON.message;
            }
            alert(msg);
        }
    });
}
</script>
@endif
