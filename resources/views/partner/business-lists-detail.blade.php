<div class="card my-3">
    <div class="card-body">
        <h5 class="text-center">Form Registrasi Partnership</h5>

        <div class="badge badge-info w-100 my-3 py-3">INFORMASI PARTNER</div>

        <div id="ContentPages">
            <form class="needs-validation">
                <div class="form-group">
                    <label for="Register_As"><strong>Daftar Sebagai</strong></label>
                    <div class="form-check">
                        <input readonly class="form-check-input" type="radio" name="Register_As" id="Register_As_Individual" value="individual" {{ $partner->register_as == 'individual' ? 'checked' : '' }} />
                        <label class="form-check-label" for="Register_As_Individual">Individu</label>
                    </div>
                    <div class="form-check">
                        <input readonly class="form-check-input" type="radio" name="Register_As" id="Register_As_Company" value="company" {{ $partner->register_as == 'company' ? 'checked' : '' }} />
                        <label class="form-check-label" for="Register_As_Company">Perusahaan</label>
                    </div>
                </div>

                <!-- Individual Section -->
                <div id="IndividualDiv" style="display:{{ $partner->register_as == 'individual' ? 'block' : 'none' }};">
                    <div class="badge badge-info w-100 my-3 py-3">1. Informasi Pribadi</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Full_Name">Nama Lengkap</label>
                                <input readonly type="text" name="Full_Name" id="Full_Name" class="form-control" value="{{ old('Full_Name', $partner->full_name ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Contact">Nomor Telepon</label>
                                <input readonly type="text" name="Contact" id="Contact" class="form-control" value="{{ old('Contact', $partner->contact ?? $partner->phone ?? '') }}" required />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Email">Email</label>
                                <input readonly type="email" name="Email" id="Email" class="form-control" value="{{ old('Email', $partner->email ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Address">Alamat Rumah</label>
                                <input readonly type="text" name="Address" id="Address" class="form-control" value="{{ old('Address', $partner->address ?? '') }}" required />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Jenis Kelamin</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Gender" id="Gender_1" value="M" {{ (old('Gender', $partner->gender ?? '') == 'M') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Gender_1">Laki-laki</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Gender" id="Gender_2" value="F" {{ (old('Gender', $partner->gender ?? '') == 'F') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Gender_2">Perempuan</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Pendidikan Terakhir</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Last_Education" id="Last_Education_1" value="1" {{ (old('Last_Education', $partner->last_education ?? '') == '1') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Last_Education_1">
                                        < SMA</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Last_Education" id="Last_Education_2" value="2" {{ (old('Last_Education', $partner->last_education ?? '') == '2') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Last_Education_2">SMA</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Last_Education" id="Last_Education_3" value="3" {{ (old('Last_Education', $partner->last_education ?? '') == '3') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Last_Education_3">Diploma</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Last_Education" id="Last_Education_4" value="4" {{ (old('Last_Education', $partner->last_education ?? '') == '4') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Last_Education_4">S1</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Last_Education" id="Last_Education_5" value="5" {{ (old('Last_Education', $partner->last_education ?? '') == '5') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Last_Education_5">>S1</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Bagaimana Anda mengetahui program kemitraan ini?</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source" id="Source_Instagram" value="Instagram" {{ (old('Source', $partner->source ?? '') == 'Instagram') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Instagram">Instagram</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source" id="Source_Facebook" value="Facebook" {{ (old('Source', $partner->source ?? '') == 'Facebook') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Facebook">Facebook</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source" id="Source_TikTok" value="TikTok" {{ (old('Source', $partner->source ?? '') == 'TikTok') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_TikTok">TikTok</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source" id="Source_Website" value="Website" {{ (old('Source', $partner->source ?? '') == 'Website') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Website">Website</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source" id="Source_Event" value="Event" {{ (old('Source', $partner->source ?? '') == 'Event') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Event">Pameran/Acara</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="PIC">PIC Penjualan</label>
                        <select name="PIC" id="PIC" class="form-control">
                            <option value="">-- Pilih PIC Penjualan --</option>
                            @foreach($pic as $p)
                            <option value="{{ $p->username }}" {{ (old('PIC', $partner->PIC ?? '') == $p->username) ? 'selected' : '' }}>{{ $p->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">2. Informasi Pekerjaan</div>

                    <div class="form-group">
                        <label><strong>Status Pekerjaan</strong></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_1" value="1" {{ (old('Employment_Status', $partner->employment_status ?? '') == '1') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Employment_Status_1">Karyawan Swasta</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_2" value="2" {{ (old('Employment_Status', $partner->employment_status ?? '') == '2') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Employment_Status_2">PNS/Aparat</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_3" value="3" {{ (old('Employment_Status', $partner->employment_status ?? '') == '3') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Employment_Status_3">Pengusaha</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Company_Name">Nama Perusahaan</label>
                                <input readonly type="text" name="Company_Name" id="Company_Name" class="form-control" value="{{ old('Company_Name', $partner->company_name ?? '') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Position">Posisi</label>
                                <input readonly type="text" name="Position" id="Position" class="form-control" value="{{ old('Position', $partner->position ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Length_of_Work">Lamanya Bekerja</label>
                                <div class="input-group">
                                    <input readonly type="number" min="0" name="Length_of_Work" id="Length_of_Work" class="form-control" value="{{ old('Length_of_Work', $partner->length_of_work ?? '') }}" />
                                    <div class="input-group-append" style="margin-left: 10px;">
                                        <div class="form-check" style="margin-right: 15px;">
                                            <input readonly class="form-check-input" type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_1" value="1" {{ (old('Length_of_Work_Unit', $partner->length_of_work_unit ?? '') == '1') ? 'checked' : '' }} />
                                            <label class="form-check-label" for="Length_of_Work_Unit_1">Tahun</label>
                                        </div>
                                        <div class="form-check">
                                            <input readonly class="form-check-input" type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_2" value="2" {{ (old('Length_of_Work_Unit', $partner->length_of_work_unit ?? '') == '2') ? 'checked' : '' }} />
                                            <label class="form-check-label" for="Length_of_Work_Unit_2">Bulan</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Company_Address">Alamat Perusahaan</label>
                                <input readonly type="text" name="Company_Address" id="Company_Address" class="form-control" value="{{ old('Company_Address', $partner->company_address ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">3. Informasi Keuangan</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Gross_Monthly">Total Pendapatan Kotor (Bulanan)</label>
                                <div class="row">
                                    <div class="col-10">
                                        <input readonly type="text" name="Gross_Monthly" id="Gross_Monthly" class="form-control" value="{{ old('Gross_Monthly', $partner->gross_monthly ?? '') }}" />
                                    </div>
                                    <div class="col-2">
                                        <p class="text-danger">Rp. <span id="Gross_Monthly_Text"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Gross_Annual">Total Pendapatan Kotor (Tahunan)</label>
                                <div class="row">
                                    <div class="col-10">
                                        <input readonly type="text" name="Gross_Annual" id="Gross_Annual" class="form-control" value="{{ old('Gross_Annual', $partner->gross_annual ?? '') }}" />
                                    </div>
                                    <div class="col-2">
                                        <p class="text-danger">Rp. <span id="Gross_Annual_Text"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Sumber Dana untuk Kemitraan</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source_Personal_Savings" id="Source_Personal_Savings" value="savings"
                                        {{ in_array('savings', $sourceFunds) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Personal_Savings">Tabungan Pribadi</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source_Inheritance" id="Source_Inheritance" value="inheritance"
                                        {{ in_array('inheritance', $sourceFunds) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Inheritance">Warisan</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source_Business_Income" id="Source_Business_Income" value="business"
                                        {{ in_array('business', $sourceFunds) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Business_Income">Penghasilan Bisnis</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source_Loan" id="Source_Loan" value="loan"
                                        {{ in_array('loan', $sourceFunds) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Loan">Pinjaman</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Source_Pension" id="Source_Pension" value="pension"
                                        {{ in_array('pension', $sourceFunds) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Source_Pension">Pensionan</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">4. Informasi Kemitraan</div>

                    <div class="form-group">
                        <label><strong>Apakah Anda saat ini menjalankan kemitraan / bisnis dengan gudang? Berapa banyak?</strong></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_0" value="0" {{ (old('Warehouse_Count', $partner->company_warehouse_count ?? '') == '0') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Warehouse_Count_0">0</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_1" value="1" {{ (old('Warehouse_Count', $partner->company_warehouse_count ?? '') == '1') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Warehouse_Count_1">1</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_2" value="2" {{ (old('Warehouse_Count', $partner->company_warehouse_count ?? '') == '2') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Warehouse_Count_2">2</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_3" value=">2" {{ (old('Warehouse_Count', $partner->company_warehouse_count ?? '') == '>2') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Warehouse_Count_3">>2</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Motivation">Motivasi untuk Bergabung dalam Kemitraan</label>
                        <textarea readonly name="Motivation" id="Motivation" rows="2" class="form-control">{{ old('Motivation', $partner->motivation ?? '') }}</textarea>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">5. Informasi Lokasi</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Province">Provinsi</label>
                                <select name="Province" id="Province" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @if(!empty($partner->province))
                                    <option value="{{ $partner->province }}" selected>{{ $partner->province }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="City">Kota / Kabupaten</label>
                                <select name="City" id="City" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @if(!empty($partner->city))
                                    <option value="{{ $partner->city }}" selected>{{ $partner->city }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Q1 --}}
                    <div class="badge badge-info w-100 my-3 py-3">6. Informasi data F&B </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>1. Apakah Anda memiliki usaha F&B? dan berapa outlet yang dimiliki? <span style="color: red;">*</span></strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="P6Q1" id="P6Q1_Yes" value="Yes"
                                        {{ $partner->c_p6q1 == 'Yes' ? 'checked' : '' }} />
                                    <label class="form-check-label" for="P6Q1_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="P6Q1" id="P6Q1_No" value="No"
                                        {{ $partner->c_p6q1 == 'No' ? 'checked' : '' }} />
                                    <label class="form-check-label" for="P6Q1_No">Tidak</label>
                                </div>
                                <div class="Amount_Outlet">
                                    <label for="P6Q1A1">Jumlah Outlet</label>
                                    <input type="text" name="P6Q1A1" id="P6Q1A1" class="form-control"
                                        value="{{ $partner->c_p6q1_a1 ?? '' }}" readonly />
                                </div>
                            </div>
                        </div>

                        {{-- Q2 --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>2. Bergerak di bidang apa? <span style="color: red;">*</span></strong></label>
                                <select name="P6Q2" id="P6Q2" class="form-control" disabled>
                                    <option value="">-- Pilih --</option>
                                    <option value="RESTAURANT" {{ $partner->c_p6q2 == 'RESTAURANT' ? 'selected' : '' }}>Restoran</option>
                                    <option value="CAFE" {{ $partner->c_p6q2 == 'CAFE'       ? 'selected' : '' }}>Cafe</option>
                                    <option value="CATERING" {{ $partner->c_p6q2 == 'CATERING'   ? 'selected' : '' }}>Catering</option>
                                    <option value="UMKM" {{ $partner->c_p6q2 == 'UMKM'       ? 'selected' : '' }}>UMKM Kuliner</option>
                                    <option value="PABRIK" {{ $partner->c_p6q2 == 'PABRIK'     ? 'selected' : '' }}>Pabrik Makanan</option>
                                    <option value="LAINNYA" {{ $partner->c_p6q2 == 'LAINNYA'    ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
                        </div>

                        {{-- Q3 --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>3. Apakah Anda memiliki relasi langsung dengan pelaku usaha tersebut? Jika Ya, perkiraan jumlah relasi usaha F&B yang dimiliki: <span style="color: red;">*</span></strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="P6Q3" id="P6Q3_Yes" value="Yes"
                                        {{ $partner->c_p6q3 == 'Yes' ? 'checked' : '' }} />
                                    <label class="form-check-label" for="P6Q3_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="P6Q3" id="P6Q3_No" value="No"
                                        {{ $partner->c_p6q3 == 'No' ? 'checked' : '' }} />
                                    <label class="form-check-label" for="P6Q3_No">Tidak</label>
                                </div>
                                <div class="Amount_Outlet">
                                    <label for="P6Q3A1">Jumlah Relasi</label>
                                    <input type="text" name="P6Q3A1" id="P6Q3A1" class="form-control"
                                        value="{{ $partner->c_p6q3_a1 ?? '' }}" readonly />
                                </div>
                            </div>
                        </div>

                        {{-- Q4 --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>4. Sebagai bentuk dukungan terhadap praktik keberlanjutan, saya bersedia minyak jelantah dari usaha F&B atau jaringan F&B tersebut agar disetorkan secara rutin ke Hub GEU Anda <span style="color: red;">*</span></strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="P6Q4" id="P6Q4_Yes" value="Yes"
                                        {{ $partner->c_p6q4 == 'Yes' ? 'checked' : '' }} />
                                    <label class="form-check-label" for="P6Q4_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="P6Q4" id="P6Q4_No" value="No"
                                        {{ $partner->c_p6q4 == 'No' ? 'checked' : '' }} />
                                    <label class="form-check-label" for="P6Q4_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Status Lokasi</strong></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Location_Status" id="Location_Own" value="own" {{ (old('Location_Status', $partner->location_status ?? '') == 'own') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Location_Own">Milik Sendiri</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Location_Status" id="Location_Rent" value="rent" {{ (old('Location_Status', $partner->location_status ?? '') == 'rent') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Location_Rent">Sewa</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Location_Status" id="Location_NoTarget" value="notarget" {{ (old('Location_Status', $partner->location_status ?? '') == 'notarget') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Location_NoTarget">Belum ada target</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">Pertanyaan Kemitraan</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>1. Bersedia mematuhi standardisasi bisnis?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q1" id="Q1_Yes" value="Yes" {{ (old('Q1', $partner->q1 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q1_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q1" id="Q1_No" value="No" {{ (old('Q1', $partner->q1 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q1_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>2. Bersedia mendukung target pertumbuhan Hub GEU Express?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q2" id="Q2_Yes" value="Yes" {{ (old('Q2', $partner->q2 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q2_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q2" id="Q2_No" value="No" {{ (old('Q2', $partner->q2 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q2_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>3. Menyadari bahwa kemitraan membawa risiko bisnis?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q3" id="Q3_Yes" value="Yes" {{ (old('Q3', $partner->q3 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q3_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q3" id="Q3_No" value="No" {{ (old('Q3', $partner->q3 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q3_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>4. Bersedia mengikuti aturan investasi?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q4" id="Q4_Yes" value="Yes" {{ (old('Q4', $partner->q4 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q4_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q4" id="Q4_No" value="No" {{ (old('Q4', $partner->q4 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q4_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>5. Bersedia tidak menjalankan bisnis sejenis yang bersaing?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q5" id="Q5_Yes" value="Yes" {{ (old('Q5', $partner->q5 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q5_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q5" id="Q5_No" value="No" {{ (old('Q5', $partner->q5 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q5_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>6. Bersedia menandatangani kontrak kemitraan?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q6" id="Q6_Yes" value="Yes" {{ (old('Q6', $partner->q6 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q6_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Q6" id="Q6_No" value="No" {{ (old('Q6', $partner->q6 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Q6_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Pilihan Paket</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Package" id="Package_1" value="mobil" {{ (old('Package', $partner->package_option ?? '') == 'mobil') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Package_1">Mobil Listrik</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Package" id="Package_2" value="booth" {{ (old('Package', $partner->package_option ?? '') == 'booth') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Package_2">Booth</label>
                                </div>
                            </div>
                        </div>
                        <small class="text-danger d-block mt-2">Catatan: Paket berisi detail seperti uang muka kemitraan dan nomor rekening.</small>
                    </div>

                    <div class="form-group">
                        <label><strong>Dokumen Pendukung (Lampirkan)</strong></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Doc_KTP" id="Doc_KTP" value="KTP" {{ (!empty($partner->doc_ktp) || (old('Doc_KTP') == 'KTP')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Doc_KTP">KTP</label>
                                </div>
                                <div style="display:none;" id="Doc_KTP_Files_Div" class="mt-2 text-center">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload KTP</span>
                                    <div id="Doc_KTP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input readonly class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_KTP_File" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Doc_NPWP" id="Doc_NPWP" value="NPWP" {{ (!empty($partner->doc_npwp) || (old('Doc_NPWP') == 'NPWP')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Doc_NPWP">NPWP</label>
                                </div>
                                <div style="display:none;" id="Doc_NPWP_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload NPWP</span>
                                    <div id="Doc_NPWP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input readonly class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_NPWP_File" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Doc_Others" id="Doc_Others" value="Others" {{ (!empty($partner->doc_others) || (old('Doc_Others') == 'Others')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Doc_Others">Lainnya</label>
                                </div>
                                <div style="display:none;" id="Doc_Others_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload Dokumen Lainnya</span>
                                    <div id="Doc_Others_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input readonly class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_Others_File" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Section -->
                <div id="CompanyDiv" style="display:{{ $partner->register_as == 'individual' ? 'none' : 'block' }};">
                    <div class="badge badge-info w-100 my-3 py-3">1. Informasi Perusahaan</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Company_Official_Name">Nama Perusahaan</label>
                                <input readonly type="text" name="Company_Official_Name" id="Company_Official_Name" class="form-control" value="{{ old('Company_Official_Name', $partner->company_official_name ?? '') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Primary_Business">Bisnis Utama</label>
                                <input readonly type="text" name="Primary_Business" id="Primary_Business" class="form-control" value="{{ old('Primary_Business', $partner->primary_business ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Bentuk Hukum</strong></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Legal_PT" id="Legal_PT" value="PT" {{ (!empty($partner->legal_pt) || (old('Legal_PT') == 'PT')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Legal_PT">PT</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Legal_CV" id="Legal_CV" value="CV" {{ (!empty($partner->legal_cv) || (old('Legal_CV') == 'CV')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Legal_CV">CV</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Legal_Coop" id="Legal_Coop" value="Cooperative" {{ (!empty($partner->legal_coop) || (old('Legal_Coop') == 'Cooperative')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Legal_Coop">Koperasi</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Legal_Foundation" id="Legal_Foundation" value="Foundation" {{ (!empty($partner->legal_foundation) || (old('Legal_Foundation') == 'Foundation')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Legal_Foundation">Yayasan</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Legal_Other" id="Legal_Other" value="Other" {{ (!empty($partner->legal_other) || (old('Legal_Other') == 'Other')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Legal_Other">Lainnya</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Year_Established">Tahun Didirikan</label>
                                <input readonly type="text" name="Year_Established" id="Year_Established" class="form-control" value="{{ old('Year_Established', $partner->year_established ?? '') }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Company_Tax_ID">NPWP Perusahaan</label>
                                <input readonly type="text" name="Company_Tax_ID" id="Company_Tax_ID" class="form-control" value="{{ old('Company_Tax_ID', $partner->company_tax_id ?? $partner->company_npwp ?? '') }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Company_NIB">NIB</label>
                                <input readonly type="text" name="Company_NIB" id="Company_NIB" class="form-control" value="{{ old('Company_NIB', $partner->company_nib ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Head_Office_Address">Alamat Kantor Pusat</label>
                        <input readonly type="text" name="Head_Office_Address" id="Head_Office_Address" class="form-control" value="{{ old('Head_Office_Address', $partner->head_office_address ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="Company_Website">Website Perusahaan</label>
                        <input readonly type="text" name="Company_Website" id="Company_Website" class="form-control" value="{{ old('Company_Website', $partner->company_website ?? '') }}" />
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">2. PIC (Penanggung Jawab)</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Name">Nama PIC</label>
                                <input readonly type="text" name="PIC_Name" id="PIC_Name" class="form-control" value="{{ old('PIC_Name', $partner->pic_name ?? '') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Position">Posisi PIC</label>
                                <input readonly type="text" name="PIC_Position" id="PIC_Position" class="form-control" value="{{ old('PIC_Position', $partner->pic_position ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Phone">Telepon PIC</label>
                                <input readonly type="text" name="PIC_Phone" id="PIC_Phone" class="form-control" value="{{ old('PIC_Phone', $partner->pic_phone ?? '') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Email">Email PIC</label>
                                <input readonly type="text" name="PIC_Email" id="PIC_Email" class="form-control" value="{{ old('PIC_Email', $partner->pic_email ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">3. Keuangan Perusahaan</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Skala Perusahaan</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_1" value="micro" {{ (old('Company_Scale', $partner->company_scale ?? '') == 'micro') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Scale_1">Mikro</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_2" value="small" {{ (old('Company_Scale', $partner->company_scale ?? '') == 'small') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Scale_2">Kecil</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_3" value="medium" {{ (old('Company_Scale', $partner->company_scale ?? '') == 'medium') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Scale_3">Menengah</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_4" value="large" {{ (old('Company_Scale', $partner->company_scale ?? '') == 'large') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Scale_4">Besar</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Estimated_Revenue">Perkiraan Pendapatan Tahunan</label>
                                <input readonly type="text" name="Estimated_Revenue" id="Estimated_Revenue" class="form-control" value="{{ old('Estimated_Revenue', $partner->estimated_revenue ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Sumber Dana</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Company_Fund_Business" id="Company_Fund_Business" value="business" {{ (!empty($partner->company_fund_business) || (old('Company_Fund_Business') == 'business')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Fund_Business">Bisnis</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Company_Fund_Investor" id="Company_Fund_Investor" value="investor" {{ (!empty($partner->company_fund_investor) || (old('Company_Fund_Investor') == 'investor')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Fund_Investor">Investor</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Company_Fund_Loan" id="Company_Fund_Loan" value="loan" {{ (!empty($partner->company_fund_loan) || (old('Company_Fund_Loan') == 'loan')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Fund_Loan">Pinjaman</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Company_Fund_CSR" id="Company_Fund_CSR" value="csr" {{ (!empty($partner->company_fund_csr) || (old('Company_Fund_CSR') == 'csr')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Fund_CSR">CSR</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Company_Fund_Private" id="Company_Fund_Private" value="private" {{ (!empty($partner->company_fund_private) || (old('Company_Fund_Private') == 'private')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Fund_Private">Pribadi</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">4. Informasi Kemitraan</div>

                    <div class="form-group">
                        <label><strong>Jumlah gudang / outlet operasional yang direncanakan</strong></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_1" value="1" {{ (old('Company_Warehouse_Count', $partner->company_warehouse_count ?? '') == '1') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Warehouse_Count_1">1</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_2" value="2" {{ (old('Company_Warehouse_Count', $partner->company_warehouse_count ?? '') == '2') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Warehouse_Count_2">2</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_3" value=">2" {{ (old('Company_Warehouse_Count', $partner->company_warehouse_count ?? '') == '>2') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Warehouse_Count_3">>2</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Company_Experience">Apakah Anda memiliki pengalaman dalam bisnis kemitraan?</label>
                        <textarea name="Company_Experience" id="Company_Experience" rows="2" class="form-control">{{ old('Company_Experience', $partner->company_experience ?? '') }}</textarea>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">5. Lokasi Operasional</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Apakah perusahaan memiliki lokasi operasional?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Has_Location" id="Company_Has_Location_Yes" value="yes" />
                                    <label class="form-check-label" for="Company_Has_Location_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Has_Location" id="Company_Has_Location_No" value="no" />
                                    <label class="form-check-label" for="Company_Has_Location_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Tipe Lokasi</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Loc_Warehouse" id="Loc_Warehouse" value="warehouse" />
                                    <label class="form-check-label" for="Loc_Warehouse">Gudang</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Loc_Store" id="Loc_Store" value="store" />
                                    <label class="form-check-label" for="Loc_Store">Kios / Toko</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Loc_Own" id="Loc_Own" value="own" />
                                    <label class="form-check-label" for="Loc_Own">Milik Sendiri</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="Loc_Rent" id="Loc_Rent" value="rent" />
                                    <label class="form-check-label" for="Loc_Rent">Sewa</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="badge badge-info w-100 my-3 py-3">Pertanyaan Kemitraan</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>1. Apakah perusahaan akan mematuhi standar dan kebijakan operasional?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q1" id="C_Q1_Yes" value="Yes" {{ (old('C_Q1', $partner->c_q1 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q1_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q1" id="C_Q1_No" value="No" {{ (old('C_Q1', $partner->c_q1 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q1_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>2. Apakah perusahaan berkomitmen untuk mendukung target pertumbuhan?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q2" id="C_Q2_Yes" value="Yes" {{ (old('C_Q2', $partner->c_q2 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q2_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q2" id="C_Q2_No" value="No" {{ (old('C_Q2', $partner->c_q2 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q2_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>3. Apakah perusahaan memahami risiko kemitraan?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q3" id="C_Q3_Yes" value="Yes" {{ (old('C_Q3', $partner->c_q3 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q3_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q3" id="C_Q3_No" value="No" {{ (old('C_Q3', $partner->c_q3 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q3_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>4. Apakah perusahaan akan memenuhi kewajiban investasi?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q4" id="C_Q4_Yes" value="Yes" {{ (old('C_Q4', $partner->c_q4 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q4_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q4" id="C_Q4_No" value="No" {{ (old('C_Q4', $partner->c_q4 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q4_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>5. Apakah perusahaan akan menghindari bisnis sejenis yang bersaing?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q5" id="C_Q5_Yes" value="Yes" {{ (old('C_Q5', $partner->c_q5 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q5_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q5" id="C_Q5_No" value="No" {{ (old('C_Q5', $partner->c_q5 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q5_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>6. Apakah perusahaan akan menandatangani perjanjian kerja sama (MOU)?</strong></label>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q6" id="C_Q6_Yes" value="Yes" {{ (old('C_Q6', $partner->c_q6 ?? '') == 'Yes') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q6_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="C_Q6" id="C_Q6_No" value="No" {{ (old('C_Q6', $partner->c_q6 ?? '') == 'No') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Q6_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Pilihan Paket</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Package" id="Company_Package_1" value="mobil" {{ (old('Company_Package', $partner->company_package ?? '') == 'mobil') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Package_1">Mobil Listrik</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="radio" name="Company_Package" id="Company_Package_2" value="booth" {{ (old('Company_Package', $partner->company_package ?? '') == 'booth') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="Company_Package_2">Booth</label>
                                </div>
                            </div>
                        </div>
                        <small class="text-danger d-block mt-2">Catatan: Paket berisi detail seperti uang muka kemitraan dan nomor rekening.</small>
                    </div>

                    <div class="form-group">
                        <label><strong>Dokumen Pendukung (Lampirkan)</strong></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="C_Doc_Akta" id="C_Doc_Akta" value="akta" {{ (!empty($partner->c_doc_akta) || (old('C_Doc_Akta') == 'akta')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Doc_Akta">Akta Pendirian</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="C_Doc_NIB" id="C_Doc_NIB" value="nib" {{ (!empty($partner->c_doc_nib) || (old('C_Doc_NIB') == 'nib')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Doc_NIB">NIB</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="C_Doc_NPWP" id="C_Doc_NPWP" value="npwp" {{ (!empty($partner->c_doc_npwp) || (old('C_Doc_NPWP') == 'npwp')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Doc_NPWP">NPWP</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" name="C_Doc_Others" id="C_Doc_Others" value="others" {{ (!empty($partner->c_doc_others) || (old('C_Doc_Others') == 'others')) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="C_Doc_Others">Lainnya</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input readonly class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_NPWP_File" />
                <input readonly class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_Others_File" />

                <!-- div class="form-group mt-4">
                    <button type="button" id="SubmitCustDetail" class="btn btn-success btn-lg">Kirim</button>
                </div -->
            </form>
        </div>
    </div>
</div>