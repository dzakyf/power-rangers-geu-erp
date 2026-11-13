@extends('layout.mainlayout-hrd')

@section('title', 'Buat Laporan Pelanggaran Karyawan')

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
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        font-size: 0.95rem;
        color: #1e293b;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
</style>

{{-- Breadcrumb Header Persis Style SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Laporan Pelanggaran Karyawan (Pengajuan SP)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd-disciplinary-sanction-index') }}">Surat Peringatan</a></li>
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Buat Laporan Pelanggaran</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hrd-disciplinary-sanction-index') }}" class="btn btn-outline-secondary sanction-btn shadow-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa fa-exclamation-circle fa-lg me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="sanction-page-wrapper">
    <div class="card sanction-card">
        <div class="sanction-card-header">
            <div>
                <h3 class="sanction-card-title">
                    <i class="fa fa-file-text-o text-primary"></i> Formulir Pelaporan Insiden & Pelanggaran
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Format acuan resmi FR/HCGA/10-00. Laporan ini akan diproses oleh HCGA untuk diverifikasi dan diterbitkan sanksinya.</small>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('hrd-disciplinary-sanction-store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{-- Dropdown Pilih Nama Karyawan --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Pilih Karyawan yang Dilaporkan <span class="text-danger">*</span></label>
                        <select name="User_ID" id="employee_select" class="form-select select2" required>
                            <option value="">-- Ketik / Cari Nama Karyawan --</option>
                            @if(!empty($employees))
                                @foreach($employees as $emp)
                                    @php
                                        $empName = $emp->Name ?? ($emp->name ?? ($emp->username ?? ''));
                                        $empId = $emp->id;
                                    @endphp
                                    <option value="{{ $empId }}"
                                        data-name="{{ $empName }}"
                                        data-position="{{ $emp->Position ?? '' }}"
                                        data-division="{{ $emp->Area ?? '' }}"
                                        data-department="{{ $emp->Department ?? '' }}"
                                        data-nik="{{ $emp->Emp_ID ?? '' }}"
                                        {{ old('User_ID') == $empId ? 'selected' : '' }}>
                                        {{ $empName }} - {{ $emp->Department ?? 'No Dept' }} ({{ $emp->Emp_ID ?? $emp->username }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="mt-1 small text-muted">
                            <span id="selected_nik_badge" class="badge bg-light text-dark border">
                                NIK: -
                            </span>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jabatan Karyawan</label>
                        <input type="text" id="input_jabatan" class="form-control bg-light" placeholder="Jabatan" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Divisi</label>
                        <input type="text" id="input_divisi" class="form-control bg-light" placeholder="Divisi" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Departemen</label>
                        <input type="text" id="input_departemen" class="form-control bg-light" placeholder="Departemen" readonly>
                    </div>

                    <hr class="my-3 text-muted">

                    <h5 class="fw-bold text-dark mb-3"><i class="fa fa-list-ol text-primary me-2"></i> Rincian Pelanggaran Perjanjian Kerja / Peraturan Perusahaan</h5>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Poin Pelanggaran 1 <span class="text-danger">*</span></label>
                        <textarea name="Violation_Point_1" class="form-control" rows="2" required placeholder="Contoh: Terbukti meninggalkan area kerja tanpa izin pimpinan pada jam operasional...">{{ old('Violation_Point_1') }}</textarea>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Poin Pelanggaran 2 (Opsional)</label>
                        <textarea name="Violation_Point_2" class="form-control" rows="2" placeholder="Pelanggaran tambahan kedua jika ada...">{{ old('Violation_Point_2') }}</textarea>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Poin Pelanggaran 3 (Opsional)</label>
                        <textarea name="Violation_Point_3" class="form-control" rows="2" placeholder="Pelanggaran tambahan ketiga jika ada...">{{ old('Violation_Point_3') }}</textarea>
                    </div>

                    <hr class="my-3 text-muted">

                    <h5 class="fw-bold text-dark mb-3"><i class="fa fa-balance-scale text-primary me-2"></i> Dasar Bukti & Klasifikasi Pelanggaran</h5>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Berdasarkan (Pilih Salah Satu) <span class="text-danger">*</span></label>
                        <select name="Evidence_Basis" class="form-select" required>
                            <option value="Laporan" {{ old('Evidence_Basis') == 'Laporan' ? 'selected' : '' }}>Laporan (Laporan tertulis atasan / rekan / vendor)</option>
                            <option value="Data" {{ old('Evidence_Basis') == 'Data' ? 'selected' : '' }}>Data (Rekaman log / CCTV / audit keuangan / absensi)</option>
                            <option value="Kesaksian" {{ old('Evidence_Basis') == 'Kesaksian' ? 'selected' : '' }}>Kesaksian (Pernyataan saksi mata langsung)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Sifat Pelanggaran <span class="text-danger">*</span></label>
                        <select name="Violation_Severity" class="form-select" required>
                            <option value="RINGAN" {{ old('Violation_Severity') == 'RINGAN' ? 'selected' : '' }}>Ringan (Teguran tata tertib / absensi)</option>
                            <option value="SEDANG" {{ old('Violation_Severity') == 'SEDANG' ? 'selected' : '' }}>Sedang (Pelanggaran SOP operasional / kelalaian kerja)</option>
                            <option value="BERAT" {{ old('Violation_Severity') == 'BERAT' ? 'selected' : '' }}>Berat (Pelanggaran fatal / integritas / kerugian finansial)</option>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Uraian Bukti & Kronologi Kejadian</label>
                        <textarea name="Evidence_Description" class="form-control" rows="3" placeholder="Jelaskan kronologi kejadian, tanggal kejadian, tempat kejadian, dan saksi terkait...">{{ old('Evidence_Description') }}</textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Masa Berlaku Sanksi (Bulan)</label>
                        <input type="number" name="Duration_Months" class="form-control" value="{{ old('Duration_Months', 6) }}" min="1" max="12">
                        <small class="text-muted">Standar UU / PP Ketenagakerjaan: 6 bulan</small>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold">Lampiran Berkas Bukti (PDF / Foto / Berita Acara)</label>
                        <input type="file" name="Evidence_File" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small class="text-muted">Maksimal 5MB (Format: PDF, JPG, PNG, DOCX)</small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('hrd-disciplinary-sanction-index') }}" class="btn btn-outline-secondary sanction-btn">
                        <i class="fa fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary sanction-btn shadow-sm">
                        <i class="fa fa-paper-plane"></i> Kirim Laporan ke HCGA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
        $('#employee_select').select2({
            placeholder: '-- Ketik / Cari Nama Karyawan --',
            allowClear: false,
            width: '100%'
        });
    }

    function populateEmployeeFields() {
        var select = document.getElementById('employee_select');
        if (!select) return;
        var selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            var pos = selectedOpt.getAttribute('data-position') || '-';
            var div = selectedOpt.getAttribute('data-division') || '-';
            var dept = selectedOpt.getAttribute('data-department') || '-';
            var nik = selectedOpt.getAttribute('data-nik') || '-';

            document.getElementById('input_jabatan').value = pos;
            document.getElementById('input_divisi').value = div;
            document.getElementById('input_departemen').value = dept;
            document.getElementById('selected_nik_badge').innerHTML = 'NIK: ' + nik;
        }
    }

    var empSelect = document.getElementById('employee_select');
    if (empSelect) {
        empSelect.addEventListener('change', populateEmployeeFields);
        if (typeof jQuery !== 'undefined') {
            $('#employee_select').on('select2:select', function () {
                populateEmployeeFields();
            });
        }
    }
});
</script>
@endsection

