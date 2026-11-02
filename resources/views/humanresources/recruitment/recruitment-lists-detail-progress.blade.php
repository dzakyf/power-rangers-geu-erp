<?php
error_reporting(0);
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

$id = $_GET['ID'];
$recruitment = DB::table('recruitment')->where('ID', $id)->first();
?>

@include('layout.plugins')
@include('layout.adminor.tab-head')
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />

<div class="container-fluid py-3">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-line-chart me-2"></i>Recruitment Progress & Assessment</h6>
        </div>
        <div class="card-body p-4">
            <form id="progressForm">
                @csrf
                <input type="hidden" name="ID" value="{{ $id }}">

                <div class="row g-4 mb-4">
                    <!-- Interview Section -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="fw-bold text-primary mb-3"><i class="fa fa-comments me-2"></i>Interview Schedule & Score</h6>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Jadwal Interview</label>
                                <input type="datetime-local" name="Interview_Schedule" class="form-control form-control-sm"
                                    value="{{ $recruitment->Interview_Schedule ? date('Y-m-d\TH:i', strtotime($recruitment->Interview_Schedule)) : '' }}">
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Hasil Interview (0 - 100)</label>
                                    <input type="number" id="Interview_Result" name="Interview_Result" class="form-control form-control-sm"
                                        min="0" max="100" step="0.01"
                                        value="{{ $recruitment->Interview_Result }}"
                                        placeholder="0 - 100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Hasil Kategori</label>
                                    <input type="text" id="Hasil_Kategori" class="form-control form-control-sm bg-white fw-bold" readonly
                                        value="{{ $recruitment->Hasil_Kategori }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Psikotest & MCU Section -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="fw-bold text-success mb-3"><i class="fa fa-stethoscope me-2"></i>Psikotest & Medical Check-Up (MCU)</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Hasil Psikotes</label>
                                <input type="text" name="Psikotes_Result" class="form-control form-control-sm"
                                    placeholder="Contoh: Lulus / Skor IQ"
                                    value="{{ $recruitment->Psikotes_Result }}">
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Jadwal MCU</label>
                                    <input type="datetime-local" name="MCU_Schedule" class="form-control form-control-sm"
                                        value="{{ $recruitment->MCU_Schedule ? date('Y-m-d\TH:i', strtotime($recruitment->MCU_Schedule)) : '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Hasil MCU</label>
                                    <input type="text" name="MCU_Result" class="form-control form-control-sm"
                                        placeholder="Contoh: Fit to work"
                                        value="{{ $recruitment->MCU_Result }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-primary px-4" onclick="saveProgress()">
                        <i class="fa fa-save me-1"></i> Save Progress
                    </button>
                </div>
                <div id="progressResult" class="mt-3"></div>
            </form>
        </div>
    </div>
</div>

<script>
function updateKategori() {
    var score = parseFloat($('#Interview_Result').val());
    if (!isNaN(score)) {
        $('#Hasil_Kategori').val(score < 70 ? 'TIDAK LULUS' : 'LULUS');
        if (score < 70) {
            $('#Hasil_Kategori').removeClass('text-success').addClass('text-danger');
        } else {
            $('#Hasil_Kategori').removeClass('text-danger').addClass('text-success');
        }
    } else {
        $('#Hasil_Kategori').val('').removeClass('text-success text-danger');
    }
}

$('#Interview_Result').on('input change', updateKategori);
updateKategori();

function saveProgress() {
    if (typeof $.blockUI === 'function') $.blockUI();
    var formData = $('#progressForm').serialize();
    $.ajax({
        url: '{{ url("/recruitment-lists-detail-progress-update") }}',
        type: 'POST',
        data: formData,
        complete: function() { 
            if (typeof $.unblockUI === 'function') $.unblockUI(); 
        },
        success: function(res) {
            if (res.status === 'success') {
                alert('Progress updated.');
                if (res.hasil_kategori) {
                    $('#Hasil_Kategori').val(res.hasil_kategori);
                    updateKategori();
                }
                if (window.parent.refreshdata) window.parent.refreshdata();
                if (window.parent.$('#divlistRecruitment')) window.parent.$('#divlistRecruitment').empty();
            } else {
                alert(res.message || 'Error saving progress.');
            }
        },
        error: function() {
            alert('Error saving progress.');
        }
    });
}
</script>
