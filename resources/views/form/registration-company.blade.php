 <div id="CompanyDiv" style="display:none;">
     <div class="badge badge-info w-100 my-3 py-3">1. Informasi Perusahaan</div>

     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label for="Company_Official_Name">Nama Perusahaan <span style="color: red;">*</span></label>
                 <input type="text" name="Company_Official_Name" id="Company_Official_Name" class="form-control" />
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label for="Primary_Business">Bidang Usaha Utama <span style="color: red;">*</span></label>
                 <input type="text" name="Primary_Business" id="Primary_Business" class="form-control" />
             </div>
         </div>
     </div>

     <div class="form-group">
         <label><strong>Bentuk Bidang Usaha <span style="color: red;">*</span></strong></label>
         <div class="row">
             <div class="col-md-3">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Business_Entity" id="Legal_PT" value="PT" />
                     <label class="form-check-label" for="Legal_PT">PT</label>
                 </div>
             </div>
             <div class="col-md-3">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Business_Entity" id="Legal_CV" value="CV" />
                     <label class="form-check-label" for="Legal_CV">CV</label>
                 </div>
             </div>
             <div class="col-md-3">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Business_Entity" id="Legal_Coop" value="Cooperative" />
                     <label class="form-check-label" for="Legal_Coop">Koperasi</label>
                 </div>
             </div>
             <div class="col-md-3">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Business_Entity" id="Legal_Foundation" value="Foundation" />
                     <label class="form-check-label" for="Legal_Foundation">Yayasan</label>
                 </div>
             </div>
         </div>
         <div class="row mt-2">
             <div class="col-md-12">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Business_Entity" id="Legal_Other" value="Other" />
                     <label class="form-check-label" for="Legal_Other">Lainnya</label>
                 </div>
             </div>
         </div>
     </div>

     <div class="row">
         <div class="col-md-4">
             <div class="form-group">
                 <label for="Year_Established">Tahun Berdiri Perusahaan <span style="color: red;">*</span></label>
                 <input type="text" name="Year_Established" id="Year_Established" class="form-control" />
             </div>
         </div>
         <div class="col-md-4">
             <div class="form-group">
                 <label for="Company_Tax_ID">NPWP Perusahaan <span style="color: red;">*</span></label>
                 <input type="text" name="Company_Tax_ID" id="Company_Tax_ID" class="form-control" />
             </div>
         </div>
         <div class="col-md-4">
             <div class="form-group">
                 <label for="Company_NIB">Nomor NIB <span style="color: red;">*</span></label>
                 <input type="text" name="Company_NIB" id="Company_NIB" class="form-control" />
             </div>
         </div>
     </div>

     <div class="form-group">
         <label for="Head_Office_Address">Alamat Perusahaan <span style="color: red;">*</span></label>
         <!-- <input type="text" name="Head_Office_Address" id="Head_Office_Address" class="form-control"/> -->
     </div>

     <div class="form-group">
         <div class="row">
             <div class="col">
                 <label for="Address">Alamat Detail <span style="color: red;">*</span></label>
                 <input type="text" name="Address_Detail" id="Address_Detail" class="form-control" required />
             </div>
         </div>

         <div class="row">
             <div class="col">
                 <label for="Address">RT <span style="color: red;">*</span></label>
                 <input type="text" name="Address_RT" id="Address_RT" class="form-control" required />
             </div>
             <div class="col">
                 <label for="Address">RW <span style="color: red;">*</span></label>
                 <input type="text" name="Address_RW" id="Address_RW" class="form-control" required />
             </div>
         </div>
         <div class="row">
             <div class="col">
                 <label for="Address">Kelurahan <span style="color: red;">*</span></label>
                 <input type="text" name="Address_Ward" id="Address_Ward" class="form-control" required />
             </div>
             <div class="col">
                 <label for="Address">Kecamatan <span style="color: red;">*</span></label>
                 <input type="text" name="Address_Subdistrict" id="Address_Subdistrict" class="form-control" required />
             </div>
         </div>
         <div class="row">
             <div class="col">
                 <label for="Address">Kota / Kabupaten <span style="color: red;">*</span></label>
                 <input type="text" name="Address_City" id="Address_City" class="form-control" required />
                 <!-- <select name="Address_City" id="Address_City" class="form-control">
                                                    <option value="">-- Pilih --</option>
                                                </select> -->
             </div>
             <div class="col">
                 <label for="Address">Provinsi <span style="color: red;">*</span></label>
                 <input type="text" name="Address_Province" id="Address_Province" class="form-control" required />
                 <!-- <select name="Address_Province" id="Address_Province" class="form-control">
                                                    <option value="">-- Pilih --</option>
                                                </select> -->
             </div>
             <div class="col">
                 <label for="Address">Kode Pos <span style="color: red;">*</span></label>
                 <input type="text" name="Address_Postal" id="Address_Postal" class="form-control" required />
             </div>
         </div>
     </div>

     <div class="form-group">
         <label for="Company_Website">Website Perusahaan <span style="color: red;">*</span></label>
         <input type="text" name="Company_Website" id="Company_Website" class="form-control" />
     </div>

     <div class="badge badge-info w-100 my-3 py-3">2. PIC (Penanggung Jawab)</div>

     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label for="PIC_Name">Nama PIC <span style="color: red;">*</span></label>
                 <input type="text" name="PIC_Name" id="PIC_Name" class="form-control" />
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label for="PIC_Position">Jabatan PIC <span style="color: red;">*</span></label>
                 <input type="text" name="PIC_Position" id="PIC_Position" class="form-control" />
             </div>
         </div>
     </div>

     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label for="PIC_Phone">No. Handphone PIC <span style="color: red;">*</span></label>
                 <input type="text" name="PIC_Phone" id="PIC_Phone" class="form-control" />
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label for="PIC_Email">Email PIC <span style="color: red;">*</span></label>
                 <input type="text" name="PIC_Email" id="PIC_Email" class="form-control" />
             </div>
         </div>
     </div>

     <div class="badge badge-info w-100 my-3 py-3">3. Keuangan Perusahaan</div>

     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>Skala Perusahaan <span style="color: red;">*</span></strong></label>
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
                 <label for="Estimated_Revenue">Estimasi Omzet Perusahaan (per Tahun) <span style="color: red;">*</span></label>
                 <input type="text" name="Estimated_Revenue" id="Estimated_Revenue" class="form-control" />
             </div>
         </div>

         <div class="col-md-6">
             <div class="form-group">
                 <label for="Estimated_Revenue">Estimasi anggaran yang disiapkan untuk kemitraan GEU <span style="color: red;">*</span></label>
                 <input type="text" name="Estimated_Revenue" id="Estimated_Revenue" class="form-control" />
             </div>
         </div>
     </div>

     <div class="form-group">
         <label><strong>Sumber dana yang akan diinvestasikan dalam kemitraan GEU <span style="color: red;">*</span></strong></label>
         <div class="row">
             <div class="col-md-6">
                 <div class="form-check">
                     <input class="form-check-input styled-radio" type="radio" name="Company_Fund" id="Company_Fund_Business" value="business" />
                     <label class="form-check-label" for="Company_Fund_Business">Laba Usaha</label>
                 </div>
                 <div class="form-check">
                     <input class="form-check-input styled-radio" type="radio" name="Company_Fund" id="Company_Fund_Investor" value="investor" />
                     <label class="form-check-label" for="Company_Fund_Investor">Investor</label>
                 </div>
                 <div class="form-check">
                     <input class="form-check-input styled-radio" type="radio" name="Company_Fund" id="Company_Fund_Loan" value="loan" />
                     <label class="form-check-label" for="Company_Fund_Loan">Pinjaman</label>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="form-check">
                     <input class="form-check-input styled-radio" type="radio" name="Company_Fund" id="Company_Fund_CSR" value="csr" />
                     <label class="form-check-label" for="Company_Fund_CSR">CSR</label>
                 </div>
                 <div class="form-check">
                     <input class="form-check-input styled-radio" type="radio" name="Company_Fund" id="Company_Fund_Private" value="private" />
                     <label class="form-check-label" for="Company_Fund_Private">Pribadi</label>
                 </div>
             </div>
         </div>
     </div>

     <div class="badge badge-info w-100 my-3 py-3">4. Informasi Kemitraan</div>

     <div class="form-group">
         <label><strong>Jumlah warehouse / titik operasional yang direncanakan <span style="color: red;">*</span></strong></label>
         <div class="row">
             <div class="col-md-3">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_1" value="1" />
                     <label class="form-check-label" for="Company_Warehouse_Count_1">1</label>
                 </div>
             </div>
             <div class="col-md-3">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_5" value="5" />
                     <label class="form-check-label" for="Company_Warehouse_Count_5">5</label>
                 </div>
             </div>
             <div class="col-md-3">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_3" value=">10" />
                     <label class="form-check-label" for="Company_Warehouse_Count_3">>10</label>
                 </div>
             </div>
             <div class="col-md-2">
                 <div class="form-group">
                     <input class="form-check-input" type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_3" value=">10" />
                     <label for="Estimated_Revenue">Lainnya</label>
                     <input type="text" name="Estimated_Revenue" id="Estimated_Revenue" class="form-control" />
                 </div>
             </div>
         </div>
     </div>

     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label for="Company_Experience">Apakah Anda memiliki pengalaman dalam bisnis kemitraan? <span style="color: red;">*</span></label>
                 <textarea name="Company_Experience" id="Company_Experience" rows="2" class="form-control"></textarea>
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label for="Company_Experience">Motivasi perusahaan untuk bergabung dalam kemitraan GEU <span style="color: red;">*</span></label>
                 <textarea name="Motivation" id="Motivation" rows="2" class="form-control"></textarea>
             </div>
         </div>
     </div>

     <div class="badge badge-info w-100 my-3 py-3">5. Informasi Lokasi Operasional</div>

     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>Apakah perusahaan memiliki lokasi operasional? <span style="color: red;">*</span></strong></label>
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
                 <label><strong>Jenis Lokasi <span style="color: red;">*</span></strong></label>

                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Location_Type" id="Loc_Warehouse" value="warehouse" />
                     <label class="form-check-label" for="Loc_Warehouse">Gudang</label>
                 </div>

                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Location_Type" id="Loc_Store" value="store" />
                     <label class="form-check-label" for="Loc_Store">Kios / Toko</label>
                 </div>

                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Location_Type" id="Loc_Vacant" value="vacant_land" />
                     <label class="form-check-label" for="Loc_Own">Lahan Kosong</label>
                 </div>

                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Location_Type" id="Loc_Others" value="others" />
                     <label class="form-check-label" for="Loc_Rent">Lainnya</label>
                 </div>
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>Kepemilikan Lokasi <span style="color: red;">*</span></strong></label>

                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Location_Type" id="Loc_Private" value="private" />
                     <label class="form-check-label" for="Loc_Warehouse">Milik Sendiri</label>
                 </div>

                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Location_Type" id="Loc_Rent" value="rent" />
                     <label class="form-check-label" for="Loc_Store">Sewa</label>
                 </div>
             </div>
         </div>
     </div>


     <div class="row mt-2">
         <div class="col-md-4">
             <div class="form-group">
                 <label for="Company_Latitude">Latitude</label>
                 <input type="text" name="Company_Latitude" id="Company_Latitude" class="form-control" placeholder="Latitude" />
             </div>
         </div>
         <div class="col-md-4">
             <div class="form-group">
                 <label for="Company_Longitude">Longitude</label>
                 <input type="text" name="Company_Longitude" id="Company_Longitude" class="form-control" placeholder="Longitude" />
             </div>
         </div>
         <div class="col-md-1">
             <div class="form-group">
                 <label>&nbsp;</label>
                 <button type="button" class="btn btn-outline-secondary btn-block" id="btnGetGpsCompany" title="Get current GPS position">Get GPS</button>
                 <small id="gpsStatusCompany" class="text-muted"></small>
             </div>
         </div>
     </div>
     <div class="badge badge-info w-100 my-3 py-3">6. Informasi data F&B</div>
     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>1. Apakah Anda memiliki usaha F&B? dan berapa outlet yang dimiliki? <span style="color: red;">*</span></strong></label>
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="P6Q1" id="P6Q1_Yes" value="Yes" />
                     <label class="form-check-label" for="P6Q1_Yes">Ya</label>
                 </div>

                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="P6Q1" id="P6Q1_No" value="No" />
                     <label class="form-check-label" for="P6Q1_No">Tidak</label>
                 </div>

                 <div class="Amount_Outlet">
                     <label for="Input_Amount_Outlet">Jumlah Outlet</label>
                     <input type="text" name="P6Q1A1" id="P6Q1A1" class="form-control" />
                 </div>
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>2. Bergerak di bidang apa? <span style="color: red;">*</span></strong></label>
                 <select name="Business_Type" id="Business_Type" class="form-control">
                     <option value="">-- Pilih --</option>
                     <option value="">Restoran</option>
                     <option value="">Cafe</option>
                     <option value="">Catering</option>
                     <option value="">UMKM Kuliner</option>
                     <option value="">Pabrik Makanan</option>
                     <option value="">Lainnya</option>
                 </select>
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>3. Apakah Anda memiliki relasi langsung dengan pelaku usaha tersebut? Jika Ya, perkiraan jumlah relasi usaha F&B yang dimiliki: <span style="color: red;">*</span></strong></label>
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="P6Q3" id="P6Q3_Yes" value="Yes" />
                     <label class="form-check-label" for="P6Q3_Yes">Ya</label>
                 </div>
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="P6Q3" id="P6Q3_No" value="No" />
                     <label class="form-check-label" for="P6Q3_No">Tidak</label>
                 </div>
                 <div class="Amount_Outlet">
                     <label for="Input_Amount_Outlet">Jumlah Relasi</label>
                     <input type="text" name="P6Q3A1" id="P6Q3A1" class="form-control" />
                 </div>
             </div>
         </div>
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>4. Sebagai bentuk dukungan terhadap praktik keberlanjutan, saya bersedia minyak jelantah dari usaha F&B atau jaringan F&B tersebut agar disetorkan secara rutin ke Hub GEU Anda <span style="color: red;">*</span></strong></label>
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

     <div class="badge badge-info w-100 my-3 py-3">Pertanyaan Kemitraan</div>

     <div class="row">
         <div class="col-md-6">
             <div class="form-group">
                 <label><strong>1. Perusahaan kami bersedia berkomitmen untuk mematuhi seluruh standarisasi bisnis GEU sesuai dengan ketentuan yang berlaku. <span style="color: red;">*</span></strong></label>
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
                 <label><strong>2. Perusahaan kami bersedia mendukung pertumbuhan dan pencapaian target operasional Hub GEU Express. <span style="color: red;">*</span></strong></label>
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
                 <label><strong>3. Perusahaan kami memahami bahwa kegiatan usaha kemitraan ini memiliki potensi keuntungan maupun risiko kerugian. <span style="color: red;">*</span></strong></label>
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
                 <label><strong>4. Perusahaan kami bersedia membayar investment fee sesuai dengan ketentuan yang telah ditetapkan. <span style="color: red;">*</span></strong></label>
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
                 <label><strong>5. Perusahaan kami berkomitmen untuk tidak membuka bisnis sejenis, termasuk menggunakan nama lain, selama masa kemitraan berlangsung maupun setelah kerjasama berakhir sesuai ketentuan perjanjian. <span style="color: red;">*</span></strong></label>
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
                 <label><strong>6. Perusahaan kami bersedia untuk menaati dan melaksanakan seluruh isi kontrak perjanjian bisnis kemitraan yang telah disepakati oleh kedua belah pihak. <span style="color: red;">*</span></strong></label>
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
         <label><strong>Pilihan Paket <span style="color: red;">*</span></strong></label>
         <div class="row">
             <div class="col-md-6">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Company_Package" id="Company_Package_1" value="mobil" />
                     <label class="form-check-label" for="Company_Package_1">Mobil Listrik</label>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="form-check">
                     <input class="form-check-input" type="radio" name="Company_Package" id="Company_Package_2" value="booth" />
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