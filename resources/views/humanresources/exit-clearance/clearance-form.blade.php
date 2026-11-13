@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
    .exit-page-wrapper {
        margin-bottom: 2rem;
    }
    .exit-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        overflow: hidden;
    }
    .exit-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .exit-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .exit-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.15rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .exit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
</style>

{{-- Breadcrumb Header persis style Index SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Formulir Exit Clearance & Kuesioner Akhir
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd-exit-clearance-index') }}">Exit Clearance</a></li>
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">{{ $clearance->No_Trx }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hrd-exit-clearance-index') }}" class="btn btn-outline-secondary exit-btn shadow-sm">
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

<form action="{{ route('hrd-exit-clearance-form-submit', $clearance->id) }}" method="POST">
    @csrf

    {{-- Profil Karyawan --}}
    <div class="exit-page-wrapper">
        <div class="card exit-card mb-4">
            <div class="exit-card-header">
                <div>
                    <h3 class="exit-card-title">
                        <i class="fa fa-user-circle text-primary"></i> Data Karyawan & Status Pemohon
                    </h3>
                    <small class="text-muted">Doc ID: <strong>FR/HCGA/07-00</strong> | No. Clearance: <span class="badge bg-primary text-white">{{ $clearance->No_Trx }}</span></small>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="text-muted small d-block mb-1">Nama Karyawan</label>
                        <span class="fw-bold fs-6 text-dark">{{ $clearance->Nama }}</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block mb-1">Jabatan</label>
                        <span class="fw-bold fs-6 text-dark">{{ $clearance->Jabatan ?? '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block mb-1">Divisi / Departemen</label>
                        <span class="fw-bold fs-6 text-dark">{{ $clearance->Divisi ?? '-' }} / {{ $clearance->Departemen ?? '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block mb-1">Tanggal Masuk (Join Date)</label>
                        <span class="fw-semibold text-secondary">{{ $clearance->Join_Date ? date('d M Y', strtotime($clearance->Join_Date)) : '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block mb-1">Tanggal Efektif Keluar (Last Day)</label>
                        <span class="fw-bold text-danger">{{ $clearance->Exit_Date ? date('d M Y', strtotime($clearance->Exit_Date)) : '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block mb-1">No. Pengajuan Resign</label>
                        <span class="badge bg-light text-dark border">{{ $resignation->No_Trx ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bagian A: Alasan Pengakhiran Hubungan Kerja --}}
        <div class="card exit-card mb-4">
            <div class="exit-card-header">
                <div>
                    <h3 class="exit-card-title">
                        <i class="fa fa-check-square-o text-primary"></i> A. ALASAN PENGAKHIRAN HUBUNGAN KERJA
                    </h3>
                    <small class="text-muted">Pilihlah skala 1 s/d 5 (5: Alasan Terkuat, 4: Cukup Kuat, 3: Sedikit Menentukan, 2: Sangat Sedikit Menentukan, 1: Sama Sekali Bukan Alasan)</small>
                </div>
            </div>
            <div class="card-body p-4">
                @php
                    $reasonsList = [
                        'gaji' => 'Gaji',
                        'tunjangan' => 'Tunjangan dari perusahaan',
                        'strategi' => 'Arahan kebijakan dan strategi perusahaan',
                        'tantangan' => 'Tantangan kerja',
                        'beban_kerja' => 'Beban kerja',
                        'karir' => 'Kesempatan mengembangkan karir',
                        'waktu_kerja' => 'Waktu kerja',
                        'atasan' => 'Hubungan kerja dengan atasan langsung',
                        'tim' => 'Kerjasama tim',
                        'jarak' => 'Jarak tempuh kerja',
                        'lingkungan' => 'Lingkungan dan iklim kerja',
                        'peluang_lain' => 'Kesempatan dan peluang di perusahaan lain yang lebih baik'
                    ];
                @endphp

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50%;">Faktor Penentu Keputusan</th>
                                <th class="text-center" style="width: 10%;">1 (Bukan Alasan)</th>
                                <th class="text-center" style="width: 10%;">2</th>
                                <th class="text-center" style="width: 10%;">3</th>
                                <th class="text-center" style="width: 10%;">4</th>
                                <th class="text-center" style="width: 10%;">5 (Terkuat)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reasonsList as $key => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <td class="text-center">
                                            <input type="radio" name="reasons_ratings[{{ $key }}]" value="{{ $i }}" class="form-check-input" required>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Alasan lain yang belum tercakup di atas:</label>
                    <textarea name="other_uncovered_reasons" class="form-control" rows="2" placeholder="Tulis alasan tambahan jika ada..."></textarea>
                </div>

                <h6 class="fw-bold mb-3 text-dark">Jika alasan pengajuan Anda adalah pindah ke perusahaan lain, apakah yang Anda dapatkan:</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nilai Tunjangan Gaji:</label>
                        <input type="text" name="new_company_offers[gaji]" class="form-control form-control-sm" placeholder="Contoh: Kenaikan 30%">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Total Cuti yang Diberikan:</label>
                        <input type="text" name="new_company_offers[cuti]" class="form-control form-control-sm" placeholder="Contoh: 14 hari + cuti khusus">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Aturan Hari dan Jam Kerja:</label>
                        <input type="text" name="new_company_offers[jam_kerja]" class="form-control form-control-sm" placeholder="Contoh: 5 hari kerja / Fleksibel">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Total Bonus:</label>
                        <input type="text" name="new_company_offers[bonus]" class="form-control form-control-sm" placeholder="Contoh: Bonus performa tahunan">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Lain - Lain:</label>
                        <input type="text" name="new_company_offers[lain_lain]" class="form-control form-control-sm" placeholder="Fasilitas kesehatan, asuransi keluarga, dll">
                    </div>
                </div>
            </div>
        </div>

        {{-- Bagian B: Mengenai Perusahaan --}}
        <div class="card exit-card mb-4">
            <div class="exit-card-header">
                <div>
                    <h3 class="exit-card-title">
                        <i class="fa fa-building-o text-primary"></i> B. MENGENAI PERUSAHAAN
                    </h3>
                    <small class="text-muted">Pilihlah skala 1 s/d 5 (5: Istimewa, 4: Baik, 3: Cukup, 2: Buruk, 1: Sangat Buruk)</small>
                </div>
            </div>
            <div class="card-body p-4">
                @php
                    $companyFactors = [
                        'kebijakan' => 'Kebijakan perusahaan secara keseluruhan',
                        'sosialisasi' => 'Sosialisasi peraturan perusahaan',
                        'fasilitas' => 'Fasilitas lain',
                        'tunjangan' => 'Tunjangan-tunjangan yang diberikan perusahaan',
                        'keluhan' => 'Kesediaan perusahaan mendengarkan keluhan karyawan dan melakukan perubahan',
                        'ekspektasi' => 'Pemahaman karyawan atas hasil kerja yang diharapkan perusahaan',
                        'potensi' => 'Kesempatan karyawan untuk mengembangkan potensi secara optimal',
                        'atasan_solusi' => 'Kesediaan atasan untuk menyelesaikan masalah yang timbul',
                        'pendapat' => 'Kesempatan karyawan untuk mengeluarkan pendapat dan mengusulkan pendapat',
                        'kerjasama' => 'Kerjasama antar bagian',
                        'informasi' => 'Pemberian informasi mengenai perubahan-perubahan yang mempengaruhi tugas karyawan',
                        'ownership' => 'Rasa memiliki karyawan terhadap perusahaan'
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50%;">Aspek Evaluasi Perusahaan</th>
                                <th class="text-center" style="width: 10%;">1 (Sangat Buruk)</th>
                                <th class="text-center" style="width: 10%;">2</th>
                                <th class="text-center" style="width: 10%;">3</th>
                                <th class="text-center" style="width: 10%;">4</th>
                                <th class="text-center" style="width: 10%;">5 (Istimewa)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companyFactors as $key => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <td class="text-center">
                                            <input type="radio" name="company_ratings[{{ $key }}]" value="{{ $i }}" class="form-check-input" required>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Bagian C & D: Kritik & Saran --}}
        <div class="card exit-card mb-4">
            <div class="exit-card-header">
                <div>
                    <h3 class="exit-card-title">
                        <i class="fa fa-commenting-o text-primary"></i> C & D. KRITIK & SARAN PENGEMBANGAN
                    </h3>
                </div>
            </div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-dark">C. Berikan Kritik serta Saran untuk Divisi yang Pernah Berhubungan dengan Anda:</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="division_feedbacks[0][divisi]" class="form-control form-control-sm" placeholder="Nama Divisi (contoh: IT / HCGA / Finance)">
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="division_feedbacks[0][saran]" class="form-control form-control-sm" placeholder="Kritik / Saran untuk divisi tersebut">
                    </div>
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="division_feedbacks[1][divisi]" class="form-control form-control-sm" placeholder="Nama Divisi">
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="division_feedbacks[1][saran]" class="form-control form-control-sm" placeholder="Kritik / Saran">
                    </div>
                </div>

                <h6 class="fw-bold mb-2 text-dark">D. Saran untuk Perusahaan Secara Keseluruhan:</h6>
                <textarea name="company_suggestions" class="form-control" rows="3" placeholder="Tuliskan saran dan masukan Anda untuk kemajuan perusahaan ke depan..."></textarea>
            </div>
        </div>

        {{-- Bagian E: Checklist Karyawan Keluar (17 Butir Item) --}}
        <div class="card exit-card mb-4">
            <div class="exit-card-header">
                <div>
                    <h3 class="exit-card-title">
                        <i class="fa fa-list-check text-primary"></i> E. CEKLIST KARYAWAN KELUAR (17 ITEM STANDAR)
                    </h3>
                    <small class="text-muted">Sudahkah Anda melengkapi, menginformasikan, dan mengembalikan hal-hal berikut ini?</small>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 45%;">Item Kelengkapan / Kewajiban</th>
                                <th style="width: 15%;">Status</th>
                                <th style="width: 20%;">Keterangan</th>
                                <th style="width: 15%;">Penanggung Jawab Serah Terima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checklist as $idx => $item)
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                                    <td><strong>{{ $item->Item_Name }}</strong></td>
                                    <td>
                                        <select name="checklist[{{ $item->id }}][Status]" class="form-select form-select-sm">
                                            <option value="SUDAH" {{ $item->Status === 'SUDAH' ? 'selected' : '' }}>Sudah</option>
                                            <option value="TIDAK_ADA" {{ $item->Status === 'TIDAK_ADA' ? 'selected' : '' }}>Tidak Ada</option>
                                            <option value="BELUM" {{ $item->Status === 'BELUM' ? 'selected' : '' }}>Belum</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="checklist[{{ $item->id }}][Keterangan]" class="form-control form-control-sm" value="{{ $item->Keterangan }}" placeholder="Keterangan / No Aset...">
                                    </td>
                                    <td>
                                        <input type="text" name="checklist[{{ $item->id }}][Penanggung_Jawab]" class="form-control form-control-sm" value="{{ $item->Penanggung_Jawab }}" placeholder="Nama PIC">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Deklarasi Pernyataan --}}
        <div class="card exit-card mb-4 bg-light">
            <div class="card-body p-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="employee_declaration" value="1" id="declarationCheck" required>
                    <label class="form-check-label fw-bold text-dark" for="declarationCheck">
                        Saya menyatakan bahwa seluruh aset perusahaan telah saya kembalikan dan seluruh kewajiban kepada perusahaan telah saya selesaikan. Saya juga berkomitmen untuk menjaga kerahasiaan informasi perusahaan setelah berakhirnya hubungan kerja.
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="{{ route('hrd-exit-clearance-index') }}" class="btn btn-outline-secondary exit-btn">
                <i class="fa fa-arrow-left"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary exit-btn shadow-sm px-4 py-2">
                <i class="fa fa-paper-plane"></i> Kirimkan Formulir ke HCGA
            </button>
        </div>
    </div>
</form>
@endsection
