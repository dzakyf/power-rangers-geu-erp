 <div id="ContentPages">
     
         <input type="hidden" name="lead_id" id="lead_id" value="{{ $leadID ?? ''}}" readonly />
         <div class="form-group">
             <label for="Register_As"><strong>Daftar Sebagai <span style="color: red;">*</span></strong></label>
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
                         <label for="Full_Name">Nama Lengkap <span style="color: red;">*</span></label>
                         <input type="text" name="Full_Name" id="Full_Name" class="form-control" required value="{{ $lead->Full_Name ?? '' }}" />
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="form-group">
                         <label for="Contact">Nomor Telepon <span style="color: red;">*</span></label>
                         <input type="text" name="Contact" id="Contact" class="form-control" required value="{{ $lead->Phone_Number ?? '' }}" />
                     </div>
                 </div>
             </div>

             <div class="row">
                 <div class="col-md-6">
                     <div class="form-group">
                         <label for="Email">Email <span style="color: red;">*</span></label>
                         <input type="email" name="Email" id="Email" class="form-control" required />
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="form-group">
                         <div class="row">
                             <div class="col">
                                 <label for="Address">Alamat Rumah <span style="color: red;">*</span></label>
                                 <input type="text" name="I_Address_Detail" id="I_Address_Detail" class="form-control" required />
                             </div>
                         </div>

                         <div class="row">
                             <div class="col">
                                 <label for="Address">RT <span style="color: red;">*</span></label>
                                 <input type="text" name="I_Address_RT" id="I_Address_RT" class="form-control" required />
                             </div>
                             <div class="col">
                                 <label for="Address">RW <span style="color: red;">*</span></label>
                                 <input type="text" name="I_Address_RW" id="I_Address_RW" class="form-control" required />
                             </div>
                         </div>
                         <div class="row">
                             <div class="col">
                                 <label for="I_Address_Province">Provinsi <span style="color: red;">*</span></label>
                                 <select name="I_Address_Province" id="I_Address_Province" class="form-control" required>
                                     <option value="">-- Pilih --</option>
                                 </select>
                             </div>
                             <div class="col">
                                 <label for="I_Address_City">Kota / Kabupaten <span style="color: red;">*</span></label>
                                 <select name="I_Address_City" id="I_Address_City" class="form-control" required disabled>
                                     <option value="">-- Pilih --</option>
                                 </select>
                             </div>
                             <div class="col">
                                 <label for="I_Address_Subdistrict">Kecamatan <span style="color: red;">*</span></label>
                                 <select name="I_Address_Subdistrict" id="I_Address_Subdistrict" class="form-control" required disabled>
                                     <option value="">-- Pilih --</option>
                                 </select>
                             </div>
                         </div>
                         <div class="row">
                             <div class="col">
                                 <label for="I_Address_Ward">Kelurahan <span style="color: red;">*</span></label>
                                 <select name="I_Address_Ward" id="I_Address_Ward" class="form-control" required disabled>
                                     <option value="">-- Pilih --</option>
                                 </select>
                             </div>
                             <div class="col">
                                 <label for="I_Address_Postal">Kode Pos <span style="color: red;">*</span></label>
                                 <select name="I_Address_Postal" id="I_Address_Postal" class="form-control" required disabled>
                                     <option value="">-- Pilih --</option>
                                 </select>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="row">
                 <div class="col-md-6">
                     <div class="form-group">
                         <label><strong>Jenis Kelamin <span style="color: red;">*</span></strong></label>
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
                         <label><strong>Pendidikan Terakhir <span style="color: red;">*</span></strong></label>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Last_Education" id="Last_Education_1" value="1" />
                             <label class="form-check-label" for="Last_Education_1">
                                 < SMA</label>
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
                             <label class="form-check-label" for="Last_Education_5">S2</label>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="form-group">
                 <label><strong>Bagaimana Anda mengetahui program kemitraan ini? <span style="color: red;">*</span></strong></label>
                 <div class="row">
                     <div class="col-md-6">
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source" id="Source_Instagram" value="Instagram" {{ ($lead->Source_Channel ?? '') === 'Instagram' ? 'checked' : '' }} />
                             <label class="form-check-label" for="Source_Instagram">Instagram</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source" id="Source_Facebook" value="Facebook" {{ ($lead->Source_Channel ?? '') === 'Facebook' ? 'checked' : '' }} />
                             <label class="form-check-label" for="Source_Facebook">Facebook</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source" id="Source_TikTok" value="TikTok" {{ ($lead->Source_Channel ?? '') === 'TikTok' ? 'checked' : '' }} />
                             <label class="form-check-label" for="Source_TikTok">TikTok</label>
                         </div>
                     </div>
                     <div class="col-md-6">
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source" id="Source_Website" value="Website" {{ ($lead->Source_Channel ?? '') === 'Website' ? 'checked' : '' }} />
                             <label class="form-check-label" for="Source_Website">Website</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source" id="Source_Event" value="Event" {{ ($lead->Source_Channel ?? '') === 'Event' ? 'checked' : '' }} />
                             <label class="form-check-label" for="Source_Event">Pameran/Acara</label>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="form-group">
                 <label for="PIC">PIC Sales</label>
                 <select name="PIC" id="PIC" class="form-control">
                     <option value="">-- Pilih PIC Sales --</option>
                     @foreach($pic as $p)
                     <option value="{{ $p->username }}">{{ $p->Name }}</option>
                     @endforeach
                 </select>
             </div>

             <div class="badge badge-info w-100 my-3 py-3">2. Informasi Pekerjaan</div>

             <div class="form-group">
                 <label><strong>Status Pekerjaan <span style="color: red;">*</span></strong></label>
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
                         <label for="Company_Name">Nama Perusahaan <span style="color: red;">*</span></label>
                         <input type="text" name="Company_Name" id="Company_Name" class="form-control" />
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="form-group">
                         <label for="Position">Posisi <span style="color: red;">*</span></label>
                         <input type="text" name="Position" id="Position" class="form-control" />
                     </div>
                 </div>
             </div>

             <div class="row">
                 <div class="col-md-6">
                     <div class="form-group">
                         <label for="Length_of_Work">Lamanya Bekerja <span style="color: red;">*</span></label>
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
                         <label for="Company_Address">Alamat Perusahaan <span style="color: red;">*</span></label>
                         <input type="text" name="Company_Address" id="Company_Address" class="form-control" />
                     </div>
                 </div>
             </div>

             <div class="badge badge-info w-100 my-3 py-3">3. Informasi Keuangan</div>

             <div class="row">
                 <div class="col-md-6">
                     <div class="form-group">
                         <label for="Gross_Monthly">Total Pendapatan Kotor (Bulanan) <span style="color: red;">*</span></label>
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
                         <label for="Gross_Annual">Total Pendapatan Kotor (Tahunan) <span style="color: red;">*</span></label>
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
                 <label><strong>Sumber Dana untuk Kemitraan <span style="color: red;">*</span></strong></label>
                 <div class="row">
                     <div class="col-md-6">
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source_Fund" id="Source_Personal_Savings" value="savings" />
                             <label class="form-check-label" for="Source_Personal_Savings">Tabungan Pribadi</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source_Fund" id="Source_Inheritance" value="inheritance" />
                             <label class="form-check-label" for="Source_Inheritance">Warisan</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source_Fund" id="Source_Business_Income" value="business" />
                             <label class="form-check-label" for="Source_Business_Income">Hasil Usaha</label>
                         </div>
                     </div>
                     <div class="col-md-6">
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source_Fund" id="Source_Loan" value="loan" />
                             <label class="form-check-label" for="Source_Loan">Pinjaman</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="Source_Fund" id="Source_Pension" value="pension" />
                             <label class="form-check-label" for="Source_Pension">Pensiunan</label>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="badge badge-info w-100 my-3 py-3">4. Informasi Kemitraan</div>

             <div class="form-group">
                 <label><strong>1. Anda ingin menjalankan bisnis kemitraan GEU, berapa warehouse? <span style="color: red;">*</span></strong></label>
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
                 <label for="Motivation">2. Motivasi untuk Bergabung dalam Kemitraan </label>
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
                         <select name="City" id="City" class="form-control" disabled>
                             <option value="">-- Pilih --</option>
                         </select>
                     </div>
                 </div>
             </div>

             <div class="form-group">
                 <label><strong>Status Lokasi <span style="color: red;">*</span></strong></label>
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


              <div class="alert alert-warning" id="gpsWarning" style="display:none;">
                  <strong>Perhatian:</strong> Izinkan akses lokasi pada browser Anda (GPS harus aktif) agar koordinat dapat diisi. Anda tetap dapat mengirim formulir.
              </div>
             <div class="row mt-2">
                 <div class="col-md-4">
                     <div class="form-group">
                         <label for="Latitude">Latitude</label>
                         <input type="text" name="Latitude" id="Latitude" class="form-control" placeholder="Latitude" />
                     </div>
                 </div>
                 <div class="col-md-4">
                     <div class="form-group">
                         <label for="Longitude">Longitude</label>
                         <input type="text" name="Longitude" id="Longitude" class="form-control" placeholder="Longitude" />
                     </div>
                 </div>
                 <div class="col-md-1">
                     <div class="form-group">
                         <label>&nbsp;</label>
                         <button type="button" class="btn btn-outline-secondary btn-block" id="btnGetGps" title="Get current GPS position">Get GPS</button>
                         <small id="gpsStatus" class="text-muted"></small>
                     </div>
                 </div>
             </div>
             <div class="badge badge-info w-100 my-3 py-3">6. Informasi data F&B </div>
             <div class="row">
                 <div class="col-md-6">
                     <div class="form-group">
                         <label><strong>1. Apakah Anda memiliki usaha F&B? dan berapa outlet yang dimiliki? <span style="color: red;">*</span></strong></label>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="I_P6Q1" id="I_P6Q1_Yes" value="Yes" />
                             <label class="form-check-label" for="I_P6Q1_Yes">Ya</label>
                         </div>

                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="I_P6Q1" id="I_P6Q1_No" value="No" />
                             <label class="form-check-label" for="I_P6Q1_No">Tidak</label>
                         </div>
                         <div class="Amount_Outlet">
                             <label for="I_P6Q1">Jumlah Outlet</label>
                             <input type="text" name="I_P6Q1A1" id="I_P6Q1A1" class="form-control" />
                         </div>
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="form-group">
                         <label><strong>2. Bergerak di bidang apa? <span style="color: red;">*</span></strong></label>
                         <select name="I_P6Q2" id="I_P6Q2" class="form-control">
                             <option value="">-- Pilih --</option>
                             <option value="RESTAURANT">Restoran</option>
                             <option value="CAFE">Cafe</option>
                             <option value="CATERING">Catering</option>
                             <option value="UMKM">UMKM Kuliner</option>
                             <option value="PABRIK">Pabrik Makanan</option>
                             <option value="LAINNYA">Lainnya</option>
                         </select>
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="form-group">
                         <label><strong>3. Apakah Anda memiliki relasi langsung dengan pelaku usaha tersebut? Jika Ya, perkiraan jumlah relasi usaha F&B yang dimiliki: <span style="color: red;">*</span></strong></label>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="I_P6Q3" id="I_P6Q3_Yes" value="Yes" />
                             <label class="form-check-label" for="I_P6Q3_Yes">Ya</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="I_P6Q3" id="I_P6Q3_No" value="No" />
                             <label class="form-check-label" for="I_P6Q3_No">Tidak</label>
                         </div>
                         <div class="Amount_Outlet">
                             <label for="I_P6Q3A1">Jumlah Relasi</label>
                             <input type="text" name="I_P6Q3A1" id="I_P6Q3A1" class="form-control" />
                         </div>
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="form-group">
                         <label><strong>4. Sebagai bentuk dukungan terhadap praktik keberlanjutan, saya bersedia minyak jelantah dari usaha F&B atau jaringan F&B tersebut agar disetorkan secara rutin ke Hub GEU Anda <span style="color: red;">*</span></strong></label>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="I_P6Q4" id="I_P6Q4_Yes" value="Yes" />
                             <label class="form-check-label" for="I_P6Q4_Yes">Ya</label>
                         </div>
                         <div class="form-check">
                             <input class="form-check-input" type="radio" name="I_P6Q4" id="I_P6Q4_No" value="No" />
                             <label class="form-check-label" for="I_P6Q4_No">Tidak</label>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="badge badge-info w-100 my-3 py-3">Pertanyaan Kemitraan</div>

             <div class="row">
                 <div class="col-md-6">
                     <div class="form-group">
                         <label><strong>1. Saya bersedia berkomitmen untuk mematuhi standarisasi bisnis GEU. <span style="color: red;">*</span></strong></label>
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
                         <label><strong>2. Saya bersedia berkomitmen untuk ikut support pertumbuhan pencapaian target Hub GEU Express. <span style="color: red;">*</span></strong></label>
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
                         <label><strong>3. Saya memahami bahwa bisnis ini juga mempunyai resiko kerugian. <span style="color: red;">*</span></strong></label>
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
                         <label><strong>4. Saya bersedia untuk membayar investasi fee sesuai dengan ketentuan. <span style="color: red;">*</span></strong></label>
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
                         <label><strong>5. Saya berkomitmen untuk tidak membuka bisnis sejenis, termasuk menggunakan nama lain, selama masa kemitraan berlangsung maupun setelah kerjasama berakhir sesuai ketentuan perjanjian. <span style="color: red;">*</span></strong></label>
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
                         <label><strong>6. Saya bersedia untuk menaati kontrak perjanjian
                                 bisnis kemitraan yang telah disepakati kedua belah pihak. <span style="color: red;">*</span></strong></label>
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



             <label><strong>Pilihan Paket <span style="color: red;">*</span></strong></label>
             <div class="row">
                 <div class="col-md-6">
                     <div class="form-check">
                         <input class="form-check-input" type="radio" name="Package" id="Package_1" value="mobil" {{ ($lead->Hub_Type ?? '') === 'mobil' ? 'checked' : '' }} />
                         <label class="form-check-label" for="Package_1">Mobil Listrik</label>
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="form-check">
                         <input class="form-check-input" type="radio" name="Package" id="Package_2" value="booth" {{ ($lead->Hub_Type ?? '') === 'booth' ? 'checked' : '' }} />
                         <label class="form-check-label" for="Package_2">Booth</label>
                     </div>
                 </div>
             </div>
             <small class="text-danger d-block mt-2">Catatan: Paket berisi detail seperti uang muka kemitraan dan nomor rekening.</small>

             <div id="IndividualDocuments" class="form-group">
                 <label><strong>Dokumen Pendukung (Lampirkan)</strong></label>
                 <div class="row">
                     <div class="col-md-4">
                         <div class="form-check">
                             <input class="form-check-input" type="checkbox" name="Doc_KTP" id="Doc_KTP" value="KTP" />
                             <label class="form-check-label" for="Doc_KTP">KTP</label>
                         </div>
                         <div style="display:none;" id="Doc_KTP_Files_Div" class="mt-2 text-center">
                             <div id="Doc_KTP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                             <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" name="Doc_KTP_File" id="Doc_KTP_File" />
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
                             <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" name="Doc_NPWP_File" id="Doc_NPWP_File" />
                         </div>
                     </div>
                     <div class="col-md-4">
                         <div class="form-check">
                             <input class="form-check-input" type="checkbox" name="Doc_Others" id="Doc_Others" value="Others" />
                             <label class="form-check-label" for="Doc_Others">Dokumen Lainnya</label>
                         </div>
                         <div style="display:none;" id="Doc_Others_Files_Div" class="mt-2">
                             <span class="badge badge-info py-2 mb-3 w-100">Upload Dokumen Lainnya</span>
                             <div id="Doc_Others_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                             <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" name="Doc_Others_File" id="Doc_Others_File" />
                         </div>
                     </div>
                 </div>
                 <div class="row mt-3">
                     <div class="col-md-6">
                         <label for="NIK">Nomor Induk Kependudukan (NIK)</label>
                         <input type="text" class="form-control" id="NIK" name="NIK" placeholder="Nomor KTP" maxlength="16" value="{{ $lead->KTP ?? '' }}" />
                     </div>
                     <div class="col-md-6">
                         <label for="NPWP">NPWP</label>
                         <input type="text" class="form-control" id="NPWP" name="NPWP" placeholder="Nomor NPWP" value="{{ $lead->NPWP ?? '' }}" />
                     </div>
                 </div>
             </div>
         </div>