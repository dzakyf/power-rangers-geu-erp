<div class="badge badge-info w-100 my-3 py-3">INFORMASI PARTNER</div>
        <div id="ContentPages">
            <form>
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Daftar Sebagai</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->register_as }}">
                    </div>
                </div>

                <div class="badge badge-info w-100 my-3 py-3">1. Informasi Pribadi</div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Nama Lengkap</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->full_name }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Nomor Telepon</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->contact }}">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Email</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->email }}">
                    </div>
                </div>


                <div class="form-group">
                    <label for="Head_Office_Address">Alamat Rumah</label>
                    <!-- <input type="text" name="Head_Office_Address" id="Head_Office_Address" class="form-control"/> -->
                    </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                <label for="Address">Alamat</label>
                                <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->address ?? '-'}}">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col">
                                    <label for="Address">RT</label>
                                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pi_rt ?? '-'}}">
                                </div>
                                <div class="col">
                                    <label for="Address">RW</label>
                                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pi_rw ?? '-'}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="Address">Kelurahan</label>
                                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pi_ward ?? '-'}}">
                                </div>
                                <div class="col">
                                    <label for="Address">Kecamatan</label>
                                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pi_subdistrict ?? '-'}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="Address">Kota / Kabupaten</label>
                                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pi_city ?? '-'}}">
                                    <!-- <select name="Address_City" id="Address_City" class="form-control">
                                        <option value="">-- Pilih --</option>
                                    </select> -->
                                </div>
                                <div class="col">
                                    <label for="Address">Provinsi</label>
                                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pi_province ?? '-'}}">
                                    <!-- <select name="Address_Province" id="Address_Province" class="form-control">
                                        <option value="">-- Pilih --</option>
                                    </select> -->
                                </div>
                                <div class="col">
                                    <label for="Address">Kode Pos</label>
                                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pi_postal_code ?? '-'}}">
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Jenis Kelamin</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->gender }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Pendidikan Terakhir</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->highest_education }}">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Source Leads</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->source_channels }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">PIC Sales</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->pic }}">
                    </div>
                </div>

                <div class="badge badge-info w-100 my-3 py-3">2. Informasi Pekerjaan</div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Status Pekerjaan</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->employment_status }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Nama Perusahaan</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->company_name }}">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Posisi</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->position }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Lamanya Bekerja</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->length_of_work }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Alamat Perusahaan</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->company_address }}">
                    </div>
                </div>

                <div class="badge badge-info w-100 my-3 py-3">3. Informasi Keuangan</div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Total Gross Revenue (Bulan)</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->gross_monthly }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Total Gross Revenue (Tahunan)</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->gross_annual }}">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Sumber Dana</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->source_personal_savings ?? "-" }}">
                    </div>
                </div>

                <div class="badge badge-info w-100 my-3 py-3">4. Informasi Kemitraan</div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Total Kepemilikan Gudang</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->warehouse_count ?? "-" }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Motivasi</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->motivation ?? "-" }}">
                    </div>
                </div>

                <div class="badge badge-info w-100 my-3 py-3">5. Informasi Lokasi</div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Provinsi</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->province }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Kabupaten</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->city }}">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Status Lokasi</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->location_status ?? "-" }}">
                    </div>
                </div>

                <div class="badge badge-info w-100 my-3 py-3">6. Informasi data F&B</div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Outlet F&B Yang Dimiliki</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->c_p6q1a1 ?? "-" }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Bergerak di bidang</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->c_p6q2  ?? '-'}}">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Jumlah Relasi Usaha</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->c_p6q3a1 ?? "-" }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-3 col-form-label">Bersedia bermitra dengan GEU</label>
                    <div class="col-sm">
                    <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->c_p6q4 ?? "-" }}">
                    </div>
                </div>

                <div class="badge badge-info w-100 my-3 py-3">Pertanyaan Kemitraan</div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-6 col-form-label">1. Perusahaan kami bersedia berkomitmen untuk mematuhi seluruh standarisasi bisnis GEU sesuai dengan ketentuan yang berlaku.</label>
                    <div class="col-sm">
                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->q1 ?? "-" }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-6 col-form-label">2. Perusahaan kami bersedia mendukung pertumbuhan dan pencapaian target operasional Hub GEU Express.</label>
                    <div class="col-sm">
                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->q2 ?? "-" }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-6 col-form-label">3. Perusahaan kami memahami bahwa kegiatan usaha kemitraan ini memiliki potensi keuntungan maupun risiko kerugian.</label>
                    <div class="col-sm">
                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->q3 ?? "-" }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-6 col-form-label">4. Perusahaan kami bersedia membayar investment fee sesuai dengan ketentuan yang telah ditetapkan.</label>
                    <div class="col-sm">
                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->q4 ?? "-" }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-6 col-form-label">5. Perusahaan kami berkomitmen untuk tidak membuka bisnis sejenis, termasuk menggunakan nama lain, selama masa kemitraan berlangsung maupun setelah kerjasama berakhir sesuai ketentuan perjanjian.</label>
                    <div class="col-sm">
                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->q5 ?? "-" }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-6 col-form-label">6. Perusahaan kami bersedia untuk menaati dan melaksanakan seluruh isi kontrak perjanjian bisnis kemitraan yang telah disepakati oleh kedua belah pihak.</label>
                    <div class="col-sm">
                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->q6 ?? "-" }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-6 col-form-label">Opsi Paket Dipilih</label>
                    <div class="col-sm">
                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="{{ $partner->package_option ?? "-" }}">
                    </div>
                </div>
               
            </form>

                <div class="form-group">
                   
                </div>
        
                <!-- Individual Section -->
                <div id="IndividualDiv" style="display: {{ $partner->register_as == 'individual' ? 'block' : 'none' }}; ">
                    
                    <div class="form-group">
                        <label><strong>Dokumen Pendukung (Lampiran)</strong></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <!-- <input readonly class="form-check-input" type="checkbox" name="Doc_KTP" id="Doc_KTP" value="KTP" {{ (!empty($partner->doc_ktp) || (old('Doc_KTP') == 'KTP')) ? 'checked' : '' }} /> -->
                                     @foreach($documents as $document)
                                        <!-- <p>Type: {{ $document->type }}</p> -->
                                        <p>File: {{ $document->type }}</p>
                                        <a href="{{ $document->url }}">Download</a>
                                    @endforeach
                                </div>
                                <div style="display:none;" id="Doc_KTP_Files_Div" class="mt-2 text-center">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload KTP</span>
                                    <div id="Doc_KTP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input readonly class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_KTP_File" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <!-- <input readonly class="form-check-input" type="checkbox" name="Doc_NPWP" id="Doc_NPWP" value="NPWP" {{ (!empty($partner->doc_npwp) || (old('Doc_NPWP') == 'NPWP')) ? 'checked' : '' }} /> -->
                                    <!-- <label class="form-check-label" for="Doc_NPWP">NPWP</label> -->
                                </div>
                                <div style="display:none;" id="Doc_NPWP_Files_Div" class="mt-2">
                                    <span class="badge badge-info py-2 mb-3 w-100">Upload NPWP</span>
                                    <div id="Doc_NPWP_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
                                    <input readonly class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_NPWP_File" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <!-- <input readonly class="form-check-input" type="checkbox" name="Doc_Others" id="Doc_Others" value="Others" {{ (!empty($partner->doc_others) || (old('Doc_Others') == 'Others')) ? 'checked' : '' }} /> -->
                                    <!-- <label class="form-check-label" for="Doc_Others">Lainnya</label> -->
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