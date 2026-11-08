<!-- Tab 1: Profile & Contact Information -->
<div class="row g-2">
    <!-- Informasi Kontak & Komunikasi (Bisa dilihat oleh Karyawan Sendiri, HCGA, dan Karyawan Lain) -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-address-book text-primary"></i> Kontak & Komunikasi
            </div>
            <div class="row g-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="info-label">WhatsApp / No. Handphone</div>
                        <div class="info-value">
                            @if(!empty($emp->Handphone))
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $emp->Handphone) }}" target="_blank" class="text-decoration-none text-success">
                                    <i class="fab fa-whatsapp me-1"></i> {{ $emp->Handphone }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    @if($isOwnProfile || $isHcga)
                    <button class="btn-edit-field btn-trigger-edit" data-category="CONTACT" data-field="Handphone" data-label="No. Handphone" data-value="{{ $emp->Handphone ?? '' }}">
                        <i class="fa fa-pen me-1"></i> Ubah
                    </button>
                    @endif
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center border-top pt-2">
                    <div>
                        <div class="info-label">Email Kantor / Korporat</div>
                        <div class="info-value">
                            @if(!empty($emp->Email))
                                <a href="mailto:{{ $emp->Email }}" class="text-decoration-none text-primary">
                                    <i class="fa fa-envelope me-1"></i> {{ $emp->Email }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    @if($isHcga)
                    <button class="btn-edit-field btn-trigger-edit" data-category="CONTACT" data-field="Email" data-label="Email Korporat" data-value="{{ $emp->Email ?? '' }}">
                        <i class="fa fa-pen me-1"></i> Ubah
                    </button>
                    @endif
                </div>

                @if($isOwnProfile || $isHcga)
                <div class="col-12 d-flex justify-content-between align-items-center border-top pt-2">
                    <div>
                        <div class="info-label">Email Pribadi</div>
                        <div class="info-value">{{ $emp->Email_Individual ?? '-' }}</div>
                    </div>
                    <button class="btn-edit-field btn-trigger-edit" data-category="CONTACT" data-field="Email_Individual" data-label="Email Pribadi" data-value="{{ $emp->Email_Individual ?? '' }}">
                        <i class="fa fa-pen me-1"></i> Ubah
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Data Jabatan & Penugasan (Bisa dilihat oleh semua user) -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-briefcase text-primary"></i> Posisi & Penempatan Kerja
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="info-label">Jabatan / Posisi</div>
                    <div class="info-value">{{ $emp->Position ?? '-' }}</div>
                </div>
                <div class="col-6">
                    <div class="info-label">Departemen</div>
                    <div class="info-value">{{ $emp->Department ?? '-' }}</div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Lokasi Kerja</div>
                    <div class="info-value">{{ $emp->Location ?? 'Head Office' }}</div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Direktorat</div>
                    <div class="info-value">{{ $emp->Directorat ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(!$isOwnProfile && !$isHcga)
    <!-- Banner Pembatasan Hak Akses untuk Karyawan Lain -->
    <div class="col-12">
        <div class="alert alert-light border text-center p-3 text-muted small" style="border-radius: 14px;">
            <i class="fa fa-shield-alt text-primary d-block mb-1" style="font-size: 20px;"></i>
            Rincian data pribadi lainnya (Identitas KTP, Alamat Domisili, Keluarga, dan Payroll) dilindungi privasi dan hanya dapat diakses oleh karyawan bersangkutan atau tim HCGA.
        </div>
    </div>
    @endif

    @if($isOwnProfile || $isHcga)
    <!-- Data Pribadi & Kependudukan (Hanya Diri Sendiri & HCGA) -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-id-card text-primary"></i> Identitas & Kependudukan
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="info-label">No. KTP / NIK KTP</div>
                    <div class="info-value">{{ $emp->KTP ?? '-' }}</div>
                </div>
                <div class="col-6">
                    <div class="info-label">NPWP</div>
                    <div class="info-value">{{ $emp->NPWP ?? '-' }}</div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Tempat & Tgl Lahir</div>
                    <div class="info-value">
                        {{ $emp->Birth_Place ?? '-' }}, 
                        {{ !empty($emp->Birth_Date) && $emp->Birth_Date != '0000-00-00' ? date('d/m/Y', strtotime($emp->Birth_Date)) : '-' }}
                    </div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Jenis Kelamin</div>
                    <div class="info-value">{{ $emp->Gender == 'M' || $emp->Gender == 'L' ? 'Laki-Laki' : ($emp->Gender == 'F' || $emp->Gender == 'P' ? 'Perempuan' : ($emp->Gender ?? '-')) }}</div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Agama</div>
                    <div class="info-value">{{ $emp->Religion ?? '-' }}</div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Golongan Darah</div>
                    <div class="info-value">{{ $emp->Blood ?? '-' }} {{ $emp->Blood_Rhesus ?? '' }}</div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Status Pernikahan</div>
                    <div class="info-value">{{ $emp->Marital_Status ?? '-' }}</div>
                </div>
                <div class="col-6 border-top pt-2">
                    <div class="info-label">Status PTKP</div>
                    <div class="info-value">{{ $emp->PTKP_Status ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alamat Domisili & KTP (Hanya Diri Sendiri & HCGA) -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-map-marker-alt text-primary"></i> Alamat & Domisili
            </div>
            <div class="row g-3">
                <div class="col-12 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="info-label">Alamat KTP</div>
                        <div class="info-value">{{ $emp->Address ?? '-' }}</div>
                        <div class="small text-muted mt-1" style="font-size: 11px;">
                            {{ $emp->Sub_District ?? '' }} {{ $emp->District ?? '' }} {{ $emp->City ?? '' }} {{ $emp->Province ?? '' }} {{ $emp->ZipCode ?? '' }}
                        </div>
                    </div>
                    @if($isHcga)
                    <button class="btn-edit-field btn-trigger-edit" data-category="ADDRESS" data-field="Address" data-label="Alamat KTP" data-value="{{ $emp->Address ?? '' }}">
                        <i class="fa fa-pen me-1"></i> Ubah
                    </button>
                    @endif
                </div>

                <div class="col-12 border-top pt-2 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="info-label">Alamat Tempat Tinggal Saat Ini (Domisili)</div>
                        <div class="info-value">{{ $emp->Address_Living ?? ($emp->Address ?? '-') }}</div>
                        <div class="small text-muted mt-1" style="font-size: 11px;">
                            {{ $emp->Sub_District_Living ?? '' }} {{ $emp->District_Living ?? '' }} {{ $emp->City_Living ?? '' }} {{ $emp->Province_Living ?? '' }} {{ $emp->ZipCode_Living ?? '' }}
                        </div>
                    </div>
                    <button class="btn-edit-field btn-trigger-edit" data-category="ADDRESS" data-field="Address_Living" data-label="Alamat Domisili" data-value="{{ $emp->Address_Living ?? '' }}">
                        <i class="fa fa-pen me-1"></i> Ubah
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Payroll & Rekening Bank (Hanya Diri Sendiri & HCGA) -->
    <div class="col-12">
        <div class="info-card">
            <div class="card-section-title">
                <i class="fa fa-building-columns text-primary"></i> Payroll & Rekening Bank
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="info-label">Metode Pembayaran</div>
                    <div class="info-value">{{ $emp->Payment_Method ?? 'Bank Transfer' }}</div>
                </div>
                <div class="col-6">
                    <div class="info-label">Nama Bank</div>
                    <div class="info-value">{{ $emp->Payment_Reference ?? '-' }}</div>
                </div>
                <div class="col-12 border-top pt-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="info-label">Nomor Rekening</div>
                        <div class="info-value">{{ $emp->Payment_Reference2 ?? '-' }}</div>
                        <div class="small text-muted" style="font-size: 11px;">A.N: {{ $emp->Payment_Reference3 ?? $emp->Name }}</div>
                    </div>
                    <button class="btn-edit-field btn-trigger-edit" data-category="BANK" data-field="Payment_Reference2" data-label="Nomor Rekening" data-value="{{ $emp->Payment_Reference2 ?? '' }}">
                        <i class="fa fa-pen me-1"></i> Ubah
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
