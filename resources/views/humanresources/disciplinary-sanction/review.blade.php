@extends('layout.mainlayout-hrd')

@section('title', 'Verifikasi & Review Sanksi HCGA')

@section('content-wrapper')
@csrf
<style>
    .sanction-page-wrapper {
        margin-bottom: 2rem;
    }
    .sanction-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        overflow: hidden;
    }
    .sanction-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .sanction-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .sanction-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.15rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .sanction-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
</style>

{{-- Breadcrumb Header Persis Style SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Verifikasi Sanksi & Jadwal Klarifikasi (HCGA)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd-disciplinary-sanction-index') }}">Surat Peringatan</a></li>
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Verifikasi ({{ $sanction->No_Trx }})</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hrd-disciplinary-sanction-detail', $sanction->id) }}" class="btn btn-outline-secondary sanction-btn">
            <i class="fa fa-arrow-left"></i> Kembali ke Detail
        </a>
    </div>
</div>

<div class="sanction-page-wrapper">
    {{-- Card Rekomendasi Smart Default Policy --}}
    <div class="card sanction-card mb-4" style="border: 1px solid #bfdbfe !important;">
        <div class="sanction-card-header" style="background-color: #eff6ff;">
            <div class="sanction-card-title text-primary">
                <i class="fa fa-magic"></i>
                <span>Smart Policy & Rekomendasi Sanksi</span>
            </div>
            <span class="badge bg-primary text-white">Rekomendasi Sistem: {{ $recommendation['recommended_level'] }}</span>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="fw-bold text-dark mb-1">Analisis Riwayat Karyawan:</h6>
                    <p class="text-muted mb-2">{{ $recommendation['reason'] }}</p>
                    <div class="small text-muted">
                        Riwayat SP Aktif: <strong>{{ $recommendation['active_count'] }} sanksi</strong> aktif tercatat dalam sistem.
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    @if($recommendation['requires_meeting'])
                        <div class="alert alert-warning mb-0 py-2 px-3 small text-start">
                            <i class="fa fa-exclamation-triangle me-1"></i> Wajib Sidang / Klarifikasi (Tatap Muka di Kantor / Daring).
                        </div>
                    @else
                        <div class="alert alert-info mb-0 py-2 px-3 small text-start">
                            <i class="fa fa-info-circle me-1"></i> Tidak wajib sidang tatap muka, dapat langsung diterbitkan SP.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Form Verifikasi HCGA --}}
    <div class="card sanction-card">
        <div class="sanction-card-header">
            <div>
                <h3 class="sanction-card-title">
                    <i class="fa fa-check-square-o text-primary"></i> Keputusan & Jadwal Klarifikasi HCGA
                </h3>
                <small class="text-muted">Karyawan: <strong>{{ $sanction->Nama }}</strong> ({{ $sanction->Departemen }}) | Pelapor: <strong>{{ $sanction->Reporter_Nama }}</strong></small>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('hrd-disciplinary-sanction-review-process', $sanction->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Sifat Pelanggaran Final <span class="text-danger">*</span></label>
                        <select name="Violation_Severity" class="form-select" required>
                            <option value="RINGAN" {{ old('Violation_Severity', $sanction->Violation_Severity) == 'RINGAN' ? 'selected' : '' }}>Ringan</option>
                            <option value="SEDANG" {{ old('Violation_Severity', $sanction->Violation_Severity) == 'SEDANG' ? 'selected' : '' }}>Sedang</option>
                            <option value="BERAT" {{ old('Violation_Severity', $sanction->Violation_Severity) == 'BERAT' ? 'selected' : '' }}>Berat</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tingkat Sanksi yang Ditetapkan <span class="text-danger">*</span></label>
                        <select name="Sanction_Level" class="form-select" required>
                            <option value="LISAN" {{ old('Sanction_Level', $recommendation['recommended_level']) == 'LISAN' ? 'selected' : '' }}>Peringatan Lisan</option>
                            <option value="SP1" {{ old('Sanction_Level', $recommendation['recommended_level']) == 'SP1' ? 'selected' : '' }}>Surat Peringatan Pertama (SP 1)</option>
                            <option value="SP2" {{ old('Sanction_Level', $recommendation['recommended_level']) == 'SP2' ? 'selected' : '' }}>Surat Peringatan Kedua (SP 2)</option>
                            <option value="SP3" {{ old('Sanction_Level', $recommendation['recommended_level']) == 'SP3' ? 'selected' : '' }}>Surat Peringatan Ketiga (SP 3 - Terakhir)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Mulai Berlaku <span class="text-danger">*</span></label>
                        <input type="date" name="Valid_From" class="form-control" value="{{ old('Valid_From', $sanction->Valid_From ?? date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Masa Berlaku Sanksi (Bulan) <span class="text-danger">*</span></label>
                        <input type="number" name="Duration_Months" class="form-control" value="{{ old('Duration_Months', $sanction->Duration_Months ?? 6) }}" min="1" max="12" required>
                    </div>

                    <hr class="my-3 text-muted">

                    <h5 class="fw-bold text-dark mb-3"><i class="fa fa-calendar text-primary me-2"></i> Pengaturan Klarifikasi / Sidang Pelanggaran</h5>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Metode Pertemuan <span class="text-danger">*</span></label>
                        <select name="Meeting_Type" id="meeting_type" class="form-select" required onchange="toggleMeetingFields()">
                            <option value="NONE" {{ old('Meeting_Type', $sanction->Meeting_Type) == 'NONE' ? 'selected' : '' }}>Tidak Ada Sidang (Langsung Terbit SP)</option>
                            <option value="OFFLINE" {{ old('Meeting_Type', $sanction->Meeting_Type) == 'OFFLINE' ? 'selected' : '' }}>Tatap Muka di Kantor</option>
                            <option value="ZOOM" {{ old('Meeting_Type', $sanction->Meeting_Type) == 'ZOOM' ? 'selected' : '' }}>Virtual Meeting (Zoom / Google Meet)</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3 meeting-field">
                        <label class="form-label fw-bold">Tanggal & Waktu Pertemuan</label>
                        <input type="datetime-local" name="Meeting_Date" id="meeting_date" class="form-control" value="{{ old('Meeting_Date', $sanction->Meeting_Date ? date('Y-m-d\TH:i', strtotime($sanction->Meeting_Date)) : '') }}">
                    </div>

                    <div class="col-md-4 mb-3 meeting-field">
                        <label class="form-label fw-bold">Ruangan Kantor / Tautan Zoom</label>
                        <input type="text" name="Meeting_Location" id="meeting_location" class="form-control" placeholder="Contoh: R. Meeting HCGA / https://zoom.us/j/..." value="{{ old('Meeting_Location', $sanction->Meeting_Location) }}">
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Catatan Verifikasi HCGA</label>
                        <textarea name="HCGA_Verifier_Notes" class="form-control" rows="3" placeholder="Catatan hasil verifikasi bukti, pertimbangan sanksi, atau instruksi tindak lanjut...">{{ old('HCGA_Verifier_Notes', $sanction->HCGA_Verifier_Notes) }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('hrd-disciplinary-sanction-detail', $sanction->id) }}" class="btn btn-outline-secondary sanction-btn">
                        <i class="fa fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary sanction-btn shadow-sm">
                        <i class="fa fa-check-circle"></i> Simpan Verifikasi & Kirim SP ke Karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleMeetingFields() {
    var type = document.getElementById('meeting_type').value;
    var fields = document.querySelectorAll('.meeting-field');
    fields.forEach(function (el) {
        if (type === 'NONE') {
            el.style.opacity = '0.4';
        } else {
            el.style.opacity = '1';
        }
    });
}
document.addEventListener('DOMContentLoaded', toggleMeetingFields);
</script>
@endsection

