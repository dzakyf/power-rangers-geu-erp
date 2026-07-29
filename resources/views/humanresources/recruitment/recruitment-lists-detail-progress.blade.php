<?php
error_reporting(0);
use Illuminate\Support\Facades\Auth;
$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

$id = $_GET['ID'];
$recruitment = DB::table('recruitment')->where('ID', $id)->first();
?>

@include('layout.plugins')
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />

<div class="p-3">
    <form id="progressForm">
        @csrf
        <input type="hidden" name="ID" value="{{ $id }}">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Jadwal Interview</label>
                <input type="datetime-local" name="Interview_Schedule" class="form-control"
                    value="{{ $recruitment->Interview_Schedule ? date('Y-m-d\TH:i', strtotime($recruitment->Interview_Schedule)) : '' }}">
            </div>
            <div class="col-md-6">
                <label>Hasil Interview (0 - 100)</label>
                <input type="number" id="Interview_Result" name="Interview_Result" class="form-control"
                    min="0" max="100" step="0.01"
                    value="{{ $recruitment->Interview_Result }}"
                    placeholder="0 - 100">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Hasil Kategori</label>
                <input type="text" id="Hasil_Kategori" class="form-control" readonly
                    value="{{ $recruitment->Hasil_Kategori }}">
            </div>
            <div class="col-md-6">
                <label>Hasil Psikotes</label>
                <input type="text" name="Psikotes_Result" class="form-control"
                    value="{{ $recruitment->Psikotes_Result }}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Jadwal MCU</label>
                <input type="datetime-local" name="MCU_Schedule" class="form-control"
                    value="{{ $recruitment->MCU_Schedule ? date('Y-m-d\TH:i', strtotime($recruitment->MCU_Schedule)) : '' }}">
            </div>
            <div class="col-md-6">
                <label>Hasil MCU</label>
                <input type="text" name="MCU_Result" class="form-control"
                    value="{{ $recruitment->MCU_Result }}">
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button type="button" class="btn btn-primary" onclick="saveProgress()">Save</button>
            </div>
        </div>
        <div id="progressResult" class="mt-3"></div>
    </form>
</div>

<script>
function updateKategori() {
    var score = parseFloat($('#Interview_Result').val());
    if (!isNaN(score)) {
        $('#Hasil_Kategori').val(score < 70 ? 'TIDAK LULUS' : 'LULUS');
    } else {
        $('#Hasil_Kategori').val('');
    }
}

$('#Interview_Result').on('input change', updateKategori);

function saveProgress() {
    $.blockUI();
    var formData = $('#progressForm').serialize();
    $.ajax({
        url: '{{ url("/recruitment-lists-detail-progress-update") }}',
        type: 'POST',
        data: formData,
        complete: function() { $.unblockUI(); },
        success: function(res) {
            if (res.status === 'success') {
                alert('Progress updated.');
                if (res.hasil_kategori) {
                    $('#Hasil_Kategori').val(res.hasil_kategori);
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
