<?php
    
$LSP = 'GEU-HO';
$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();
$province = DB::table('master_kodepos')->select("Province")->groupBy("Province")->orderBy("Province", "asc")->get();
?>
<div class="card my-3">
    <div class="card-body">
        <h5 class="text-center">Form Registrasi Partnership</h5>
    
        <div class="badge badge-info w-100 my-3 py-3">INFORMASI PARTNER</div>
        
        <div id="ContentPages">
            <form class="needs-validation">
                <div class="form-group">
                    <input type="hidden" name="lead_id" id="lead_id" value="{{$_GET['ID']}}">
                    <label for="Register_As"><strong>Daftar Sebagai</strong></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="Register_As" id="Register_As_Individual" value="individual" checked />
                        <label class="form-check-label" for="Register_As_Individual">Individu</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="Register_As" id="Register_As_Company" value="company" />
                        <label class="form-check-label" for="Register_As_Company">Perusahaan</label>
                    </div>
                </div>
        
                <!-- Individual Section -->
                <div id="IndividualDiv">
                    <div class="badge badge-info w-100 my-3 py-3">1. Informasi Pribadi</div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Full_Name">Nama Lengkap</label>
                                <input type="text" name="Full_Name" id="Full_Name" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Contact">Nomor Telepon</label>
                                <input type="text" name="Contact" id="Contact" class="form-control" required />
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Email">Email</label>
                                <input type="email" name="Email" id="Email" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Address">Alamat Rumah</label>
                                <input type="text" name="Address" id="Address" class="form-control" required />
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Jenis Kelamin</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Gender" id="Gender_1" value="M" />
                                    <label class="form-check-label" for="Gender_1">Laki-laki</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Gender" id="Gender_2" value="F" />
                                    <label class="form-check-label" for="Gender_2">Perempuan</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Pendidikan Terakhir</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_1" value="1" />
                                    <label class="form-check-label" for="Last_Education_1">< SMA</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_2" value="2" />
                                    <label class="form-check-label" for="Last_Education_2">SMA</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_3" value="3" />
                                    <label class="form-check-label" for="Last_Education_3">Diploma</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_4" value="4" />
                                    <label class="form-check-label" for="Last_Education_4">S1</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_5" value="5" />
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
                                    <input class="form-check-input" type="radio" name="Source" id="Source_Instagram" value="Instagram" />
                                    <label class="form-check-label" for="Source_Instagram">Instagram</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source" id="Source_Facebook" value="Facebook" />
                                    <label class="form-check-label" for="Source_Facebook">Facebook</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source" id="Source_Tiktok" value="Tiktok" />
                                    <label class="form-check-label" for="Source_Tiktok">Tiktok</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source" id="Source_Website" value="Website" />
                                    <label class="form-check-label" for="Source_Website">Website</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source" id="Source_Event" value="Event" />
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
                                <option value="{{ $p->username }}">{{ $p->Name }}</option>
                            @endforeach
                        </select>
                    </div>
            
                    <div class="badge badge-info w-100 my-3 py-3">2. Informasi Pekerjaan</div>
                    
                    <div class="form-group">
                        <label><strong>Status Pekerjaan</strong></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_1" value="1" />
                                    <label class="form-check-label" for="Employment_Status_1">Karyawan Swasta</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_2" value="2" />
                                    <label class="form-check-label" for="Employment_Status_2">PNS/Aparat</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Employment_Status" id="Employment_Status_3" value="3" />
                                    <label class="form-check-label" for="Employment_Status_3">Pengusaha</label>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Company_Name">Nama Perusahaan</label>
                                <input type="text" name="Company_Name" id="Company_Name" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Position">Posisi</label>
                                <input type="text" name="Position" id="Position" class="form-control" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Length_of_Work">Lamanya Bekerja</label>
                                <div class="input-group">
                                    <input type="number" min="0" name="Length_of_Work" id="Length_of_Work" class="form-control" />
                                    <div class="input-group-append" style="margin-left: 10px;">
                                        <div class="form-check" style="margin-right: 15px;">
                                            <input class="form-check-input" type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_1" value="1" />
                                            <label class="form-check-label" for="Length_of_Work_Unit_1">Tahun</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_2" value="2" />
                                            <label class="form-check-label" for="Length_of_Work_Unit_2">Bulan</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Company_Address">Alamat Perusahaan</label>
                                <input type="text" name="Company_Address" id="Company_Address" class="form-control" />
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
                                        <input type="text" name="Gross_Monthly" id="Gross_Monthly" class="form-control" />
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
                                        <input type="text" name="Gross_Annual" id="Gross_Annual" class="form-control" />
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
                                    <input class="form-check-input" type="radio" name="Source_Personal_Savings" id="Source_Personal_Savings" value="savings" />
                                    <label class="form-check-label" for="Source_Personal_Savings">Tabungan Pribadi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source_Inheritance" id="Source_Inheritance" value="inheritance" />
                                    <label class="form-check-label" for="Source_Inheritance">Warisan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source_Business_Income" id="Source_Business_Income" value="business" />
                                    <label class="form-check-label" for="Source_Business_Income">Penghasilan Bisnis</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source_Loan" id="Source_Loan" value="loan" />
                                    <label class="form-check-label" for="Source_Loan">Pinjaman</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Source_Pension" id="Source_Pension" value="pension" />
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
                                    <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_0" value="0" />
                                    <label class="form-check-label" for="Warehouse_Count_0">0</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_1" value="1" />
                                    <label class="form-check-label" for="Warehouse_Count_1">1</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_2" value="2" />
                                    <label class="form-check-label" for="Warehouse_Count_2">2</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Warehouse_Count" id="Warehouse_Count_3" value=">2" />
                                    <label class="form-check-label" for="Warehouse_Count_3">>2</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="Motivation">Motivasi untuk Bergabung dalam Kemitraan</label>
                        <textarea name="Motivation" id="Motivation" rows="2" class="form-control"></textarea>
                    </div>
                    
                    <div class="badge badge-info w-100 my-3 py-3">5. Informasi Lokasi</div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Province">Provinsi</label>
                                <select name="Province" id="Province" class="form-control">
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="City">Kota / Kabupaten</label>
                                <select name="City" id="City" class="form-control">
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Status Lokasi</strong></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Location_Status" id="Location_Own" value="own" />
                                    <label class="form-check-label" for="Location_Own">Milik Sendiri</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Location_Status" id="Location_Rent" value="rent" />
                                    <label class="form-check-label" for="Location_Rent">Sewa</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Location_Status" id="Location_NoTarget" value="notarget" />
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
                                    <input class="form-check-input" type="radio" name="Q1" id="Q1_Yes" value="Yes" />
                                    <label class="form-check-label" for="Q1_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q1" id="Q1_No" value="No" />
                                    <label class="form-check-label" for="Q1_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>2. Bersedia mendukung target pertumbuhan Hub GEU Express?</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q2" id="Q2_Yes" value="Yes" />
                                    <label class="form-check-label" for="Q2_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q2" id="Q2_No" value="No" />
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
                                    <input class="form-check-input" type="radio" name="Q3" id="Q3_Yes" value="Yes" />
                                    <label class="form-check-label" for="Q3_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q3" id="Q3_No" value="No" />
                                    <label class="form-check-label" for="Q3_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>4. Bersedia mengikuti aturan investasi?</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q4" id="Q4_Yes" value="Yes" />
                                    <label class="form-check-label" for="Q4_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q4" id="Q4_No" value="No" />
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
                                    <input class="form-check-input" type="radio" name="Q5" id="Q5_Yes" value="Yes" />
                                    <label class="form-check-label" for="Q5_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q5" id="Q5_No" value="No" />
                                    <label class="form-check-label" for="Q5_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>6. Bersedia menandatangani kontrak kemitraan?</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q6" id="Q6_Yes" value="Yes" />
                                    <label class="form-check-label" for="Q6_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Q6" id="Q6_No" value="No" />
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
                                    <input class="form-check-input" type="radio" name="Package" id="Package_1" value="option1" />
                                    <label class="form-check-label" for="Package_1">Opsi 1</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Package" id="Package_2" value="option2" />
                                    <label class="form-check-label" for="Package_2">Opsi 2</label>
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
                                    <input class="form-check-input" type="checkbox" name="Doc_KTP" id="Doc_KTP" value="KTP" />
                                    <label class="form-check-label" for="Doc_KTP">KTP</label>
                                </div>
                                <div style="display:none;" id="Doc_KTP_Files_Div" class="mt-2 text-center">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload KTP</span>
                                    <div id="Doc_KTP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_KTP_File" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Doc_NPWP" id="Doc_NPWP" value="NPWP" />
                                    <label class="form-check-label" for="Doc_NPWP">NPWP</label>
                                </div>
                                <div style="display:none;" id="Doc_NPWP_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload NPWP</span>
                                    <div id="Doc_NPWP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_NPWP_File" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Doc_Others" id="Doc_Others" value="Others" />
                                    <label class="form-check-label" for="Doc_Others">Lainnya</label>
                                </div>
                                <div style="display:none;" id="Doc_Others_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload Dokumen Lainnya</span>
                                    <div id="Doc_Others_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_Others_File" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <!-- Company Section -->
                <div id="CompanyDiv" style="display:none;">
                    <div class="badge badge-info w-100 my-3 py-3">1. Informasi Perusahaan</div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Company_Official_Name">Nama Perusahaan</label>
                                <input type="text" name="Company_Official_Name" id="Company_Official_Name" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Primary_Business">Bisnis Utama</label>
                                <input type="text" name="Primary_Business" id="Primary_Business" class="form-control" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Bentuk Hukum</strong></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Legal_PT" id="Legal_PT" value="PT" />
                                    <label class="form-check-label" for="Legal_PT">PT</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Legal_CV" id="Legal_CV" value="CV" />
                                    <label class="form-check-label" for="Legal_CV">CV</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Legal_Coop" id="Legal_Coop" value="Cooperative" />
                                    <label class="form-check-label" for="Legal_Coop">Koperasi</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Legal_Foundation" id="Legal_Foundation" value="Foundation" />
                                    <label class="form-check-label" for="Legal_Foundation">Yayasan</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Legal_Other" id="Legal_Other" value="Other" />
                                    <label class="form-check-label" for="Legal_Other">Lainnya</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Year_Established">Tahun Didirikan</label>
                                <input type="text" name="Year_Established" id="Year_Established" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Company_Tax_ID">NPWP Perusahaan</label>
                                <input type="text" name="Company_Tax_ID" id="Company_Tax_ID" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Company_NIB">NIB</label>
                                <input type="text" name="Company_NIB" id="Company_NIB" class="form-control" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="Head_Office_Address">Alamat Kantor Pusat</label>
                        <input type="text" name="Head_Office_Address" id="Head_Office_Address" class="form-control" />
                    </div>
                    
                    <div class="form-group">
                        <label for="Company_Website">Website Perusahaan</label>
                        <input type="text" name="Company_Website" id="Company_Website" class="form-control" />
                    </div>
                    
                    <div class="badge badge-info w-100 my-3 py-3">2. PIC (Penanggung Jawab)</div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Name">Nama PIC</label>
                                <input type="text" name="PIC_Name" id="PIC_Name" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Position">Posisi PIC</label>
                                <input type="text" name="PIC_Position" id="PIC_Position" class="form-control" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Phone">Telepon PIC</label>
                                <input type="text" name="PIC_Phone" id="PIC_Phone" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="PIC_Email">Email PIC</label>
                                <input type="text" name="PIC_Email" id="PIC_Email" class="form-control" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="badge badge-info w-100 my-3 py-3">3. Keuangan Perusahaan</div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Skala Perusahaan</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_1" value="micro" />
                                    <label class="form-check-label" for="Company_Scale_1">Mikro</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_2" value="small" />
                                    <label class="form-check-label" for="Company_Scale_2">Kecil</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_3" value="medium" />
                                    <label class="form-check-label" for="Company_Scale_3">Menengah</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Scale" id="Company_Scale_4" value="large" />
                                    <label class="form-check-label" for="Company_Scale_4">Besar</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Estimated_Revenue">Perkiraan Pendapatan Tahunan</label>
                                <input type="text" name="Estimated_Revenue" id="Estimated_Revenue" class="form-control" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Sumber Dana</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Company_Fund_Business" id="Company_Fund_Business" value="business" />
                                    <label class="form-check-label" for="Company_Fund_Business">Bisnis</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Company_Fund_Investor" id="Company_Fund_Investor" value="investor" />
                                    <label class="form-check-label" for="Company_Fund_Investor">Investor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Company_Fund_Loan" id="Company_Fund_Loan" value="loan" />
                                    <label class="form-check-label" for="Company_Fund_Loan">Pinjaman</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Company_Fund_CSR" id="Company_Fund_CSR" value="csr" />
                                    <label class="form-check-label" for="Company_Fund_CSR">CSR</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Company_Fund_Private" id="Company_Fund_Private" value="private" />
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
                                    <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_1" value="1" />
                                    <label class="form-check-label" for="Company_Warehouse_Count_1">1</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_2" value="2" />
                                    <label class="form-check-label" for="Company_Warehouse_Count_2">2</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_3" value=">2" />
                                    <label class="form-check-label" for="Company_Warehouse_Count_3">>2</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="Company_Experience">Apakah Anda memiliki pengalaman dalam bisnis kemitraan?</label>
                        <textarea name="Company_Experience" id="Company_Experience" rows="2" class="form-control"></textarea>
                    </div>
                    
                    <div class="badge badge-info w-100 my-3 py-3">5. Lokasi Operasional</div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Apakah perusahaan memiliki lokasi operasional?</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Has_Location" id="Company_Has_Location_Yes" value="yes" />
                                    <label class="form-check-label" for="Company_Has_Location_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Has_Location" id="Company_Has_Location_No" value="no" />
                                    <label class="form-check-label" for="Company_Has_Location_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Tipe Lokasi</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Loc_Warehouse" id="Loc_Warehouse" value="warehouse" />
                                    <label class="form-check-label" for="Loc_Warehouse">Gudang</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Loc_Store" id="Loc_Store" value="store" />
                                    <label class="form-check-label" for="Loc_Store">Kios / Toko</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Loc_Own" id="Loc_Own" value="own" />
                                    <label class="form-check-label" for="Loc_Own">Milik Sendiri</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Loc_Rent" id="Loc_Rent" value="rent" />
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
                                    <input class="form-check-input" type="radio" name="C_Q1" id="C_Q1_Yes" value="Yes" />
                                    <label class="form-check-label" for="C_Q1_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q1" id="C_Q1_No" value="No" />
                                    <label class="form-check-label" for="C_Q1_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>2. Apakah perusahaan berkomitmen untuk mendukung target pertumbuhan?</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q2" id="C_Q2_Yes" value="Yes" />
                                    <label class="form-check-label" for="C_Q2_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q2" id="C_Q2_No" value="No" />
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
                                    <input class="form-check-input" type="radio" name="C_Q3" id="C_Q3_Yes" value="Yes" />
                                    <label class="form-check-label" for="C_Q3_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q3" id="C_Q3_No" value="No" />
                                    <label class="form-check-label" for="C_Q3_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>4. Apakah perusahaan akan memenuhi kewajiban investasi?</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q4" id="C_Q4_Yes" value="Yes" />
                                    <label class="form-check-label" for="C_Q4_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q4" id="C_Q4_No" value="No" />
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
                                    <input class="form-check-input" type="radio" name="C_Q5" id="C_Q5_Yes" value="Yes" />
                                    <label class="form-check-label" for="C_Q5_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q5" id="C_Q5_No" value="No" />
                                    <label class="form-check-label" for="C_Q5_No">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>6. Apakah perusahaan akan menandatangani perjanjian kerja sama (MOU)?</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q6" id="C_Q6_Yes" value="Yes" />
                                    <label class="form-check-label" for="C_Q6_Yes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="C_Q6" id="C_Q6_No" value="No" />
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
                                    <input class="form-check-input" type="radio" name="Company_Package" id="Company_Package_1" value="1" />
                                    <label class="form-check-label" for="Company_Package_1">Opsi 1</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="Company_Package" id="Company_Package_2" value="2" />
                                    <label class="form-check-label" for="Company_Package_2">Opsi 2</label>
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
                                    <input class="form-check-input" type="checkbox" name="C_Doc_Akta" id="C_Doc_Akta" value="Akta" />
                                    <label class="form-check-label" for="C_Doc_Akta">Akta Pendirian</label>
                                </div>
                                <div style="display:none;" id="C_Doc_Akta_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload Akta Pendirian</span>
                                    <div id="C_Doc_Akta_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="C_Doc_Akta_File" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="C_Doc_NIB" id="C_Doc_NIB" value="NIB" />
                                    <label class="form-check-label" for="C_Doc_NIB">NIB</label>
                                </div>
                                <div style="display:none;" id="C_Doc_NIB_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload NIB</span>
                                    <div id="C_Doc_NIB_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="C_Doc_NIB_File" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="C_Doc_NPWP" id="C_Doc_NPWP" value="NPWP" />
                                    <label class="form-check-label" for="C_Doc_NPWP">NPWP</label>
                                </div>
                                <div style="display:none;" id="C_Doc_NPWP_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload NPWP</span>
                                    <div id="C_Doc_NPWP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="C_Doc_NPWP_File" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="C_Doc_Others" id="C_Doc_Others" value="Others" />
                                    <label class="form-check-label" for="C_Doc_Others">Lainnya</label>
                                </div>
                                <div style="display:none;" id="C_Doc_Others_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload Lainnya</span>
                                    <div id="C_Doc_Others_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="C_Doc_Others_File" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_NPWP_File" />
                <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_Others_File" />
        
                <div class="form-group mt-4">
                    <button type="button" id="SubmitCustDetail" class="btn btn-success btn-lg">Kirim</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
	// toggle sections based on Register As
	$(function() {
		$('input[name="Register_As"]').change(function() {
			var val = $(this).val();
			if (val === 'company') {
				$('#IndividualDiv').hide();
                $('#IndividualDiv input[type="text"], #IndividualDiv textarea').val('');
                $('#IndividualDiv input[type="checkbox"], #IndividualDiv input[type="radio"]').prop('checked', false).trigger('change');
                $('#IndividualDiv select').val(null).trigger('change');
				$('#CompanyDiv').show();
			} else {
				$('#CompanyDiv').hide();
                $('#CompanyDiv input[type="text"], #CompanyDiv textarea').val('');
                $('#CompanyDiv input[type="checkbox"], #CompanyDiv input[type="radio"]').prop('checked', false).trigger('change');
                $('#CompanyDiv select').val(null).trigger('change');
				$('#IndividualDiv').show();
			}
		});

		// initialize mask if field exists
		if ($('#Estimated_Revenue').length) {
			$('#Estimated_Revenue').mask('#,##0.00', { reverse: true });
		}

        var vaultKTP = new dhx.Vault("Doc_KTP_Files", {
            uploader: {
                target: "upload-receiveinvoice.php"
            },
            filesLimit: 1

        });

        vaultKTP.events.on("uploadComplete", function(obj) {
            console.log("upload complete")
            $("#Doc_KTP_File").val(obj[0].name);
        });

        vaultKTP.events.on("uploadBegin", function(obj) {
            console.log("upload begin")
            $("#Doc_KTP_File").val("");

        });
        vaultKTP.events.on("beforeAdd", function(fileItem) {
            if (vaultKTP.data.getLength() >= 1) {
                alert('Only can upload 1 file per checkpoint.');
                return false;
            }
        });


        vaultKTP.events.on("beforeAdd", function(item) {
            var extension = item.file.name.split(".").pop();

            var predicate = extension === "pdf";
            if (!predicate) {
                dhtmlx.alert("Hanya Boleh mengupload tipe file PDF");
            }
            return predicate;
        });

        var vaultNPWP = new dhx.Vault("Doc_NPWP_Files", {
            uploader: {
                target: "upload-receiveinvoice.php"
            },
            filesLimit: 1

        });

        vaultNPWP.events.on("uploadComplete", function(obj) {
            $("#Doc_NPWP_File").val(obj[0].name);
        });
        vaultNPWP.events.on("uploadBegin", function(obj) {
            $("#Doc_NPWP_File").val("");

        });
        vaultNPWP.events.on("beforeAdd", function(fileItem) {
            if (vaultNPWP.data.getLength() >= 1) {
                alert('Only can upload 1 file per checkpoint.');
                return false;
            }
        });

        var doc_Others = new dhx.Vault("Doc_Others_Files", {
            uploader: {
                target: "upload-receiveinvoice.php"
            },
            filesLimit: 1

        });

        doc_Others.events.on("uploadComplete", function(obj) {
            $("#Doc_Others_File").val(obj[0].name);
        });
        doc_Others.events.on("uploadBegin", function(obj) {
            $("#Doc_Others_File").val("");

        });
        doc_Others.events.on("beforeAdd", function(fileItem) {
            if (doc_Others.data.getLength() >= 1) {
                alert('Only can upload 1 file per checkpoint.');
                return false;
            }
        });


        doc_Others.events.on("beforeAdd", function(item) {
            var extension = item.file.name.split(".").pop();

            var predicate = extension === "pdf";
            if (!predicate) {
                dhtmlx.alert("Hanya Boleh mengupload tipe file PDF");
            }
            return predicate;
        });

        $('#Doc_KTP').change(function() {
            if (this.checked) {
                $('#Doc_KTP_Files_Div').show('swing');
            } else {
                $('#Doc_KTP_Files_Div').hide('swing');
                $('#Doc_KTP_File').val('');
                // vaultKTP.clearAll();
            }
        });

        $('#Doc_NPWP').change(function() {
            if (this.checked) {
                $('#Doc_NPWP_Files_Div').show('swing');
            } else {
                $('#Doc_NPWP_Files_Div').hide('swing');
                $('#Doc_NPWP_File').val('');
                // vaultNPWP.clearAll();
            }
        });

        $('#Doc_Others').change(function() {
            if (this.checked) {
                $('#Doc_Others_Files_Div').show('swing');
            } else {
                $('#Doc_Others_Files_Div').hide('swing');
                $('#Doc_Others_File').val('');
                // doc_Others.clearAll();
            }
        });

        var vaultAkta = new dhx.Vault("C_Doc_Akta_Files", {
            uploader: {
                target: "upload-receiveinvoice.php"
            },
            filesLimit: 1

        });

        vaultAkta.events.on("uploadComplete", function(obj) {
            $("#C_Doc_Akta_File").val(obj[0].name);
        });
        vaultAkta.events.on("uploadBegin", function(obj) {
            $("#C_Doc_Akta_File").val("");

        });
        vaultAkta.events.on("beforeAdd", function(fileItem) {
            if (vaultAkta.data.getLength() >= 1) {
                alert('Only can upload 1 file per checkpoint.');
                return false;
            }
        });

        var vaultNIB = new dhx.Vault("C_Doc_NIB_Files", {
            uploader: {
                target: "upload-receiveinvoice.php"
            },
            filesLimit: 1

        });

        vaultNIB.events.on("uploadComplete", function(obj) {
            $("#C_Doc_NIB_File").val(obj[0].name);
        });
        vaultNIB.events.on("uploadBegin", function(obj) {
            $("#C_Doc_NIB_File").val("");

        });
        vaultNIB.events.on("beforeAdd", function(fileItem) {
            if (vaultNIB.data.getLength() >= 1) {
                alert('Only can upload 1 file per checkpoint.');
                return false;
            }
        });

        var vaultCNPWP = new dhx.Vault("C_Doc_NPWP_Files", {
            uploader: {
                target: "upload-receiveinvoice.php"
            },
            filesLimit: 1

        });

        vaultCNPWP.events.on("uploadComplete", function(obj) {
            $("#C_Doc_NPWP_File").val(obj[0].name);
        });
        vaultCNPWP.events.on("uploadBegin", function(obj) {
            $("#C_Doc_NPWP_File").val("");

        });
        vaultCNPWP.events.on("beforeAdd", function(fileItem) {
            if (vaultCNPWP.data.getLength() >= 1) {
                alert('Only can upload 1 file per checkpoint.');
                return false;
            }
        });

        var vaultCOthers = new dhx.Vault("C_Doc_Others_Files", {
            uploader: {
                target: "upload-receiveinvoice.php"
            },
            filesLimit: 1

        });

        vaultCOthers.events.on("uploadComplete", function(obj) {
            $("#C_Doc_Others_File").val(obj[0].name);
        });
        vaultCOthers.events.on("uploadBegin", function(obj) {
            $("#C_Doc_Others_File").val("");

        });
        vaultCOthers.events.on("beforeAdd", function(fileItem) {
            if (vaultCOthers.data.getLength() >= 1) {
                alert('Only can upload 1 file per checkpoint.');
                return false;
            }
        });

        $('#C_Doc_Akta').change(function() {
            if (this.checked) {
                $('#C_Doc_Akta_Files_Div').show('swing');
            } else {
                $('#C_Doc_Akta_Files_Div').hide('swing');
                $('#C_Doc_Akta_File').val('');
                // vaultKTP.clearAll();
            }
        });

        $('#C_Doc_NIB').change(function() {
            if (this.checked) {
                $('#C_Doc_NIB_Files_Div').show('swing');
            } else {
                $('#C_Doc_NIB_Files_Div').hide('swing');
                $('#C_Doc_NIB_File').val('');
                // vaultKTP.clearAll();
            }
        });

        $('#C_Doc_NPWP').change(function() {
            if (this.checked) {
                $('#C_Doc_NPWP_Files_Div').show('swing');
            } else {
                $('#C_Doc_NPWP_Files_Div').hide('swing');
                $('#C_Doc_NPWP_File').val('');
                // vaultKTP.clearAll();
            }
        });

        $('#C_Doc_Others').change(function() {
            if (this.checked) {
                $('#C_Doc_Others_Files_Div').show('swing');
            } else {
                $('#C_Doc_Others_Files_Div').hide('swing');
                $('#C_Doc_Others_File').val('');
                // vaultKTP.clearAll();
            }
        });
        
        $('#Province').select2({
            placeholder: "Pilih Provinsi",
            allowClear: true,
            width: '100%',

            ajax: {
                url: 'province',
                dataType: 'json',
                type: "POST",
                delay: 250,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: function (data) {
                    // Tranforms the top-level key of the response object from 'items' to 'results'
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.Province,
                                id: item.Province
                            };
                        })
                    };
                },
                cache: true,
            }
        }).on('change', function () {
            $('#City').empty(); // clear previous options
            $('#City').val(null).trigger('change'); // reset selection
            $('#City').select2({
                placeholder: "Pilih Kota",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: function (params) {
                        return 'city/' + btoa($('#Province').val());
                    },
                    dataType: 'json',
                    type: "POST",
                    delay: 250,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function (data) {
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.City,
                                    id: item.City
                                };
                            })
                        };
                    },
                    cache: true,
                }
            });
        });
	});

	function isValidKey(e) {
		var charCode = e.keyCode || e.which;
		if (charCode == 46) return false;
		return true;
	}
        var myCalendar;
        myCalendar = new dhtmlXCalendarObject("Date");
        myCalendar.setDateFormat("%Y-%m-%d");
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

        console.log(gettingVars)

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "sales-opportunity-registration-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Kemitraan Berhasil Dikirim!');

					// location.reload();
				} else {
					var cariAWB = data.split("||");
					alert(cariAWB[0]);
                    $('#' + cariAWB[1]).focus();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Kesalahan Internal! Silakan coba lagi!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}
</script>