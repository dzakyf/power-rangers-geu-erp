@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">Data Diri &amp; Profil Kandidat</h4>
            <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Lengkapi &amp; Perbarui Data</span>
        </div>
    </div>
</div>

<div id="profileAlert" class="alert d-none" role="alert"></div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white pb-0">
        <ul class="nav nav-tabs card-header-tabs flex-wrap" id="profileTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-biodata" type="button">Biodata Utama</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-academics" type="button">Pendidikan Formal</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trainings" type="button">Pelatihan</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-experiences" type="button">Pengalaman Kerja</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-families" type="button">Keluarga</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-healths" type="button">Riwayat Kesehatan</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-drivings" type="button">Driving Licence</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-contacts" type="button">Kontak Darurat</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-insurances" type="button">Asuransi</button></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <!-- 1. BIODATA UTAMA -->
            <div class="tab-pane fade show active" id="tab-biodata">
                <form id="biodataForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light" value="{{ $rec->Name ?? '' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Posisi yang Dilamar</label>
                            <input type="text" class="form-control bg-light" value="{{ $rec->Position ?? '' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>
                            <select id="Gender" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ ($rec->Gender ?? '') == 'L' ? 'selected' : '' }}>Laki-laki (MALE)</option>
                                <option value="P" {{ ($rec->Gender ?? '') == 'P' ? 'selected' : '' }}>Perempuan (FEMALE)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tempat Lahir</label>
                            <input type="text" id="Birth_Place" class="form-control" value="{{ $rec->Birth_Place ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir</label>
                            <input type="date" id="Birth_Date" class="form-control" value="{{ $rec->Birth_Date ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK / No. KTP (16 digit)</label>
                            <input type="text" id="KTP" class="form-control" maxlength="16" value="{{ $rec->KTP ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kewarganegaraan</label>
                            <input type="text" id="kewarganegaraan" class="form-control" value="{{ $rec->kewarganegaraan ?? 'Indonesia' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pendidikan Terakhir</label>
                            <select id="Last_Education" class="form-select">
                                <option value="">-- Pilih --</option>
                                @foreach(['SMA/SMK','D1','D2','D3','D4','S1','S2','S3'] as $edu)
                                <option value="{{ $edu }}" {{ ($rec->Last_Education ?? '') == $edu ? 'selected' : '' }}>{{ $edu }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Asal Sekolah / Universitas</label>
                            <input type="text" id="Last_Education_From" class="form-control" value="{{ $rec->Last_Education_From ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Agama</label>
                            <select id="Religion" class="form-select">
                                <option value="">-- Pilih --</option>
                                @foreach(['ISLAM','PROTESTAN','KATHOLIK','BUDHA','HINDU','KONGHUCU'] as $rel)
                                <option value="{{ $rel }}" {{ ($rec->Religion ?? '') == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Pernikahan</label>
                            <select id="Marital_Status" class="form-select">
                                <option value="">-- Pilih --</option>
                                @foreach(['SINGLE','MARRIED','WIDOWER','WIDOW'] as $ms)
                                <option value="{{ $ms }}" {{ ($rec->Marital_Status ?? '') == $ms ? 'selected' : '' }}>{{ $ms }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Alamat Domisili</label>
                            <textarea id="Address" class="form-control" rows="2">{{ $rec->Address ?? '' }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kota</label>
                            <input type="text" id="City" class="form-control" value="{{ $rec->City ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" id="Phone" class="form-control" value="{{ $rec->Phone ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. WhatsApp / HP</label>
                            <input type="text" id="Handphone" class="form-control" value="{{ $rec->Handphone ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" id="Email" class="form-control" value="{{ $rec->Email ?? '' }}">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fa fa-save me-1"></i> Simpan Biodata Utama</button>
                    </div>
                </form>
            </div>

            <!-- 2. PENDIDIKAN FORMAL -->
            <div class="tab-pane fade" id="tab-academics">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Riwayat Pendidikan Formal</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-academics"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-academics" data-section="academics">
                        <thead class="table-light">
                            <tr>
                                <th>Jenjang</th><th>Sekolah / Universitas</th><th>Jurusan</th><th>Kota</th><th>Tahun Lulus</th><th>IPK / Nilai</th><th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($academics as $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Education_Level" value="{{ $row->Education_Level ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="College" value="{{ $row->College ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Education_Major" value="{{ $row->Education_Major ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Location" value="{{ $row->Location ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Certified_Year" value="{{ $row->Certified_Year ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="GPA" value="{{ $row->GPA ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Education_Level" placeholder="S1 / D3 / SMA"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="College"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Education_Major"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Location"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Certified_Year"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="GPA"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-academics"><i class="fa fa-save me-1"></i> Simpan Pendidikan Formal</button>
            </div>

            <!-- 3. PELATIHAN / NON-FORMAL -->
            <div class="tab-pane fade" id="tab-trainings">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Pendidikan &amp; Pelatihan (Non-Formal / Kursus)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-trainings"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-trainings" data-section="trainings">
                        <thead class="table-light">
                            <tr><th>Penyelenggara / Kursus</th><th>Kota / Lokasi</th><th>Tahun</th><th>Keterangan / Sertifikat</th><th style="width:50px"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($trainings as $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Training_Company" value="{{ $row->Training_Company ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Location" value="{{ $row->Location ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Year_Training" value="{{ $row->Year_Training ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Note" value="{{ $row->Note ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Training_Company"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Location"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Year_Training"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Note"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-trainings"><i class="fa fa-save me-1"></i> Simpan Pelatihan</button>
            </div>

            <!-- 4. PENGALAMAN KERJA -->
            <div class="tab-pane fade" id="tab-experiences">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Riwayat Pengalaman Kerja</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-experiences"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-experiences" data-section="experiences">
                        <thead class="table-light">
                            <tr><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Jabatan / Posisi</th><th>Nama Perusahaan</th><th>Gaji Terakhir</th><th>Alasan Berhenti / Catatan</th><th style="width:50px"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($experiences as $row)
                            <tr>
                                <td><input type="date" class="form-control form-control-sm" data-col="Start_Date" value="{{ $row->Start_Date ?? '' }}"></td>
                                <td><input type="date" class="form-control form-control-sm" data-col="End_Date" value="{{ $row->End_Date ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Position" value="{{ $row->Position ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Company_Name" value="{{ $row->Company_Name ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Last_Salary" value="{{ $row->Last_Salary ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Note" value="{{ $row->Note ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="date" class="form-control form-control-sm" data-col="Start_Date"></td>
                                <td><input type="date" class="form-control form-control-sm" data-col="End_Date"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Position"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Company_Name"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Last_Salary"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Note"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-experiences"><i class="fa fa-save me-1"></i> Simpan Pengalaman Kerja</button>
            </div>

            <!-- 5. KELUARGA -->
            <div class="tab-pane fade" id="tab-families">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Susunan Keluarga</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-families"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-families" data-section="families">
                        <thead class="table-light">
                            <tr><th>Hubungan</th><th>Nama</th><th>L/P</th><th>Tanggal Lahir</th><th>Pendidikan</th><th>Pekerjaan</th><th style="width:50px"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($families as $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Family_Status" value="{{ $row->Family_Status ?? '' }}" placeholder="Ayah/Ibu/Istri/Anak"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Name" value="{{ $row->Name ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Gender" value="{{ $row->Gender ?? '' }}" placeholder="L/P"></td>
                                <td><input type="date" class="form-control form-control-sm" data-col="Birth_Date" value="{{ $row->Birth_Date ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Last_Education" value="{{ $row->Last_Education ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Occupation" value="{{ $row->Occupation ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Family_Status" placeholder="Ayah/Ibu/Istri/Anak"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Name"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Gender" placeholder="L/P"></td>
                                <td><input type="date" class="form-control form-control-sm" data-col="Birth_Date"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Last_Education"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Occupation"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-families"><i class="fa fa-save me-1"></i> Simpan Data Keluarga</button>
            </div>

            <!-- 6. RIWAYAT KESEHATAN -->
            <div class="tab-pane fade" id="tab-healths">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Riwayat Kesehatan</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-healths"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-healths" data-section="healths">
                        <thead class="table-light">
                            <tr><th>Kategori / Keterangan</th><th>Penyakit / Keluhan</th><th>Tahun</th><th>Lama Perawatan (Hari)</th><th style="width:50px"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($healths as $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Description" value="{{ $row->Description ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Disease" value="{{ $row->Disease ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Year_Health" value="{{ $row->Year_Health ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Days" value="{{ $row->Days ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Description" placeholder="Rawat Inap / Alergi / dll"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Disease"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Year_Health"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Days"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-healths"><i class="fa fa-save me-1"></i> Simpan Riwayat Kesehatan</button>
            </div>

            <!-- 7. DRIVING LICENCE -->
            <div class="tab-pane fade" id="tab-drivings">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Kepemilikan SIM (Driving Licence)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-drivings"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-drivings" data-section="drivings">
                        <thead class="table-light">
                            <tr><th>Jenis SIM</th><th>Status / Tipe</th><th>No. SIM / Keterangan</th><th style="width:50px"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($drivings as $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Description" value="{{ $row->Description ?? '' }}" placeholder="SIM A / SIM C"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Tipe" value="{{ $row->Tipe ?? '' }}" placeholder="AKTIF / TIDAK"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Note" value="{{ $row->Note ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Description" placeholder="SIM A / SIM C"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Tipe" placeholder="AKTIF / TIDAK"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Note"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-drivings"><i class="fa fa-save me-1"></i> Simpan Driving Licence</button>
            </div>

            <!-- 8. KONTAK DARURAT & REFERENSI -->
            <div class="tab-pane fade" id="tab-contacts">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Kontak Darurat &amp; Referensi</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-contacts"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-contacts" data-section="contacts">
                        <thead class="table-light">
                            <tr><th>Nama</th><th>Hubungan / Perusahaan</th><th>Jabatan</th><th>No. Kontak</th><th>Email</th><th>Telepon</th><th style="width:50px"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Name" value="{{ $row->Name ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Employee_Department" value="{{ $row->Employee_Department ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Position" value="{{ $row->Position ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Contact_No" value="{{ $row->Contact_No ?? '' }}"></td>
                                <td><input type="email" class="form-control form-control-sm" data-col="Email" value="{{ $row->Email ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Phone" value="{{ $row->Phone ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="Name"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Employee_Department"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Position"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Contact_No"></td>
                                <td><input type="email" class="form-control form-control-sm" data-col="Email"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Phone"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-contacts"><i class="fa fa-save me-1"></i> Simpan Kontak Darurat</button>
            </div>

            <!-- 9. ASURANSI -->
            <div class="tab-pane fade" id="tab-insurances">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Data Asuransi / BPJS</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="#tbl-insurances"><i class="fa fa-plus me-1"></i> Tambah Baris</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="tbl-insurances" data-section="insurances">
                        <thead class="table-light">
                            <tr><th>Atas Nama</th><th>Hubungan</th><th>Jenis Asuransi / BPJS</th><th>No. Kartu</th><th>No. Polis</th><th>Status</th><th>Tgl Mulai</th><th style="width:50px"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($insurances as $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="On_Behalf" value="{{ $row->On_Behalf ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="On_Behalf_Status" value="{{ $row->On_Behalf_Status ?? 'OWNER' }}" placeholder="OWNER/ISTRI/ANAK"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Insurances" value="{{ $row->Insurances ?? '' }}" placeholder="BPJS Kesehatan / TK / dll"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Card_No" value="{{ $row->Card_No ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Polis_No" value="{{ $row->Polis_No ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Polis_Status" value="{{ $row->Polis_Status ?? '' }}" placeholder="AKTIF / NONAKTIF"></td>
                                <td><input type="date" class="form-control form-control-sm" data-col="Date_Start" value="{{ $row->Date_Start ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" data-col="On_Behalf" value="{{ $rec->Name ?? '' }}"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="On_Behalf_Status" value="OWNER"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Insurances" placeholder="BPJS Kesehatan / TK"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Card_No"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Polis_No"></td>
                                <td><input type="text" class="form-control form-control-sm" data-col="Polis_Status" placeholder="AKTIF"></td>
                                <td><input type="date" class="form-control form-control-sm" data-col="Date_Start"></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-del-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold btn-save-section mt-2" data-table="#tbl-insurances"><i class="fa fa-save me-1"></i> Simpan Data Asuransi</button>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function showProfileAlert(type, msg) {
        $('#profileAlert').removeClass('d-none alert-success alert-danger').addClass('alert-' + type).text(msg);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    $('#biodataForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            _token: '{{ csrf_token() }}',
            section: 'biodata',
            Gender: $('#Gender').val(),
            Birth_Date: $('#Birth_Date').val(),
            Birth_Place: $('#Birth_Place').val(),
            KTP: $('#KTP').val(),
            Last_Education: $('#Last_Education').val(),
            Last_Education_From: $('#Last_Education_From').val(),
            Religion: $('#Religion').val(),
            Marital_Status: $('#Marital_Status').val(),
            kewarganegaraan: $('#kewarganegaraan').val(),
            Address: $('#Address').val(),
            City: $('#City').val(),
            Phone: $('#Phone').val(),
            Handphone: $('#Handphone').val(),
            Email: $('#Email').val()
        };

        $.post('{{ url("/candidate/profile-update") }}', data, function(res) {
            showProfileAlert('success', res.message || 'Biodata berhasil diperbarui.');
        }, 'json').fail(function(xhr) {
            var msg = 'Gagal memperbarui biodata.';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
            }
            showProfileAlert('danger', msg);
        });
    });

    $(document).on('click', '.btn-add-row', function() {
        var target = $($(this).data('target'));
        var tbody = target.find('tbody');
        var clone = tbody.find('tr:first').clone();
        clone.find('input').val('');
        tbody.append(clone);
    });

    $(document).on('click', '.btn-del-row', function() {
        var tbody = $(this).closest('tbody');
        if (tbody.find('tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            $(this).closest('tr').find('input').val('');
        }
    });

    $('.btn-save-section').on('click', function() {
        var table = $($(this).data('table'));
        var section = table.data('section');
        var rows = [];

        table.find('tbody tr').each(function() {
            var rowObj = {};
            $(this).find('[data-col]').each(function() {
                rowObj[$(this).data('col')] = $(this).val();
            });
            rows.push(rowObj);
        });

        var btn = $(this);
        btn.prop('disabled', true);

        $.post('{{ url("/candidate/profile-update") }}', {
            _token: '{{ csrf_token() }}',
            section: section,
            rows: rows
        }, function(res) {
            showProfileAlert('success', res.message || 'Data berhasil disimpan.');
            btn.prop('disabled', false);
        }, 'json').fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data.';
            showProfileAlert('danger', msg);
            btn.prop('disabled', false);
        });
    });
});
</script>
@endsection
