<div class="my-4 card">
    <div class="card-body">
        <h5>Registered Leads Detail</h5>
        <form id="RegisteredLeadsForm">
            <input type="hidden" id="reg_id" name="id" />
            <input type="hidden" id="reg_lead_id" name="lead_id" />

            <div class="badge badge-secondary w-100 my-3 py-3">INFORMASI PARTNER</div>
            <div class="row">
                <div class="col-md-6 mt-3">
                    <label>Daftar Sebagai</label>
                    <input type="text" id="reg_register_as" name="register_as" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Nama Lengkap</label>
                    <input type="text" id="reg_full_name" name="full_name" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Nomor Telepon</label>
                    <input type="text" id="reg_contact" name="contact" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Email</label>
                    <input type="text" id="reg_email" name="email" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Jenis Kelamin</label>
                    <input type="text" id="reg_gender" name="gender" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Pendidikan Terakhir</label>
                    <input type="text" id="reg_highest_education" name="highest_education" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Sumber Informasi Program</label>
                    <input type="text" id="reg_source_channels" name="source_channels" class="form-control" readonly />
                </div>
            </div>

            {{-- Individual Section --}}
            <div id="regIndividualFields">
                <div class="badge badge-secondary w-100 my-3 py-2">INFORMASI PEKERJAAN</div>
                <div class="row">
                    <div class="col-md-6 mt-3">
                        <label>Status Pekerjaan</label>
                        <input type="text" id="reg_employment_status" name="employment_status" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Nama Perusahaan (Pemberi Kerja)</label>
                        <input type="text" id="reg_company_name" name="company_name" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Posisi</label>
                        <input type="text" id="reg_position" name="position" class="form-control" readonly />
                    </div>
                    <div class="col-md-3 mt-3">
                        <label>Lamanya Bekerja</label>
                        <input type="text" id="reg_length_of_work" name="length_of_work" class="form-control" readonly />
                    </div>
                    <div class="col-md-3 mt-3">
                        <label>Satuan</label>
                        <input type="text" id="reg_length_of_work_unit" name="length_of_work_unit" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Alamat Perusahaan (Pemberi Kerja)</label>
                        <input type="text" id="reg_company_address" name="company_address" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Total Pendapatan Kotor (Bulanan)</label>
                        <input type="text" id="reg_gross_monthly" name="gross_monthly" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Total Pendapatan Kotor (Tahunan)</label>
                        <input type="text" id="reg_gross_annual" name="gross_annual" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Sumber Dana untuk Kemitraan</label>
                        <input type="text" id="reg_sources_of_funds" name="sources_of_funds" class="form-control" readonly />
                    </div>
                </div>

                <div class="badge badge-secondary w-100 my-3 py-2">ALAMAT RUMAH</div>
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <label>Alamat</label>
                        <input type="text" id="reg_address" name="address" class="form-control" readonly />
                    </div>
                    <div class="col-md-3 mt-3">
                        <label>RT</label>
                        <input type="text" id="reg_pi_rt" name="pi_rt" class="form-control" readonly />
                    </div>
                    <div class="col-md-3 mt-3">
                        <label>RW</label>
                        <input type="text" id="reg_pi_rw" name="pi_rw" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Kelurahan</label>
                        <input type="text" id="reg_pi_ward" name="pi_ward" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Kecamatan</label>
                        <input type="text" id="reg_pi_subdistrict" name="pi_subdistrict" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Kota / Kabupaten</label>
                        <input type="text" id="reg_pi_city" name="pi_city" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Provinsi</label>
                        <input type="text" id="reg_pi_province" name="pi_province" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Kode Pos</label>
                        <input type="text" id="reg_pi_postal_code" name="pi_postal_code" class="form-control" readonly />
                    </div>
                </div>

                <div class="badge badge-secondary w-100 my-3 py-2">DOKUMEN</div>
                <div class="row">
                    <div class="col-md-6 mt-3">
                        <label>KTP</label>
                        <div id="reg_ktp_container"></div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>NPWP</label>
                        <div id="reg_npwp_container"></div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <label>Dokumen Pendukung</label>
                        <div id="reg_supporting_documents_container"></div>
                    </div>
                </div>
            </div>

            {{-- Company Section --}}
            <div id="regCompanyFields" style="display:none;">
                <div class="badge badge-secondary w-100 my-3 py-2">INFORMASI PERUSAHAAN</div>
                <div class="row">
                    <div class="col-md-6 mt-3">
                        <label>Nama Perusahaan (Resmi)</label>
                        <input type="text" id="reg_company_official_name" name="company_official_name" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Bidang Usaha Utama</label>
                        <input type="text" id="reg_primary_business" name="primary_business" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Bentuk Badan Usaha</label>
                        <input type="text" id="reg_legal_forms" name="legal_forms" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Tahun Berdiri</label>
                        <input type="text" id="reg_year_established" name="year_established" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>NPWP Perusahaan</label>
                        <input type="text" id="reg_company_tax_id" name="company_tax_id" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Nomor NIB</label>
                        <input type="text" id="reg_company_nib" name="company_nib" class="form-control" readonly />
                    </div>
                    <div class="col-md-12 mt-3">
                        <label>Alamat Kantor Pusat</label>
                        <input type="text" id="reg_head_office_address" name="head_office_address" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Website Perusahaan</label>
                        <input type="text" id="reg_company_website" name="company_website" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Skala Perusahaan</label>
                        <input type="text" id="reg_company_scale" name="company_scale" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Estimasi Omzet (per Tahun)</label>
                        <input type="text" id="reg_estimated_revenue" name="estimated_revenue" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Sumber Dana Kemitraan</label>
                        <input type="text" id="reg_company_funds" name="company_funds" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Jumlah Warehouse yang Direncanakan</label>
                        <input type="text" id="reg_company_warehouse_count" name="company_warehouse_count" class="form-control" readonly />
                    </div>
                    <div class="col-md-12 mt-3">
                        <label>Pengalaman Bisnis Kemitraan</label>
                        <textarea id="reg_company_experience" name="company_experience" class="form-control" rows="2" readonly></textarea>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Memiliki Lokasi Operasional?</label>
                        <input type="text" id="reg_company_has_location" name="company_has_location" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Jenis & Kepemilikan Lokasi</label>
                        <input type="text" id="reg_location_types" name="location_types" class="form-control" readonly />
                    </div>
                    <div class="col-md-12 mt-3">
                        <label>Dokumen Perusahaan</label>
                        <div id="reg_company_documents_container"></div>
                    </div>
                </div>

                <div class="badge badge-secondary w-100 my-3 py-2">PIC</div>
                <div class="row">
                    <div class="col-md-6 mt-3">
                        <label>Nama PIC</label>
                        <input type="text" id="reg_pic_name" name="pic_name" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Jabatan PIC</label>
                        <input type="text" id="reg_pic_position" name="pic_position" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>No. Handphone PIC</label>
                        <input type="text" id="reg_pic_phone" name="pic_phone" class="form-control" readonly />
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Email PIC</label>
                        <input type="text" id="reg_pic_email" name="pic_email" class="form-control" readonly />
                    </div>
                </div>
            </div>

            {{-- Shared Fields --}}
            <div class="badge badge-secondary w-100 my-3 py-2">RENCANA KEMITRAAN</div>
            <div class="row">
                <div class="col-md-6 mt-3">
                    <label>Jumlah Warehouse</label>
                    <input type="text" id="reg_warehouse_count" name="warehouse_count" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Status Lokasi</label>
                    <input type="text" id="reg_location_status" name="location_status" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Provinsi (Lokasi Hub)</label>
                    <input type="text" id="reg_province" name="province" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Kota / Kabupaten (Lokasi Hub)</label>
                    <input type="text" id="reg_city" name="city" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Pilihan Paket</label>
                    <input type="text" id="reg_package_option" name="package_option" class="form-control" readonly />
                </div>
                <div class="col-md-12 mt-3">
                    <label>Motivasi Bergabung</label>
                    <textarea id="reg_motivation" name="motivation" class="form-control" rows="2" readonly></textarea>
                </div>
            </div>

            {{-- F&B Section --}}
            <div class="badge badge-secondary w-100 my-3 py-2">F&amp;B</div>
            <div class="row" id="regIndividualFnB">
                <div class="col-md-6 mt-3">
                    <label>1. Memiliki usaha F&amp;B?</label>
                    <input type="text" id="reg_q1" name="q1" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Jumlah Outlet</label>
                    <input type="text" id="reg_p6q1a1" name="p6q1a1" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>2. Bergerak di bidang apa?</label>
                    <input type="text" id="reg_q2" name="q2" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>3. Memiliki relasi langsung dengan pelaku usaha F&amp;B?</label>
                    <input type="text" id="reg_q3" name="q3" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Jumlah Relasi</label>
                    <input type="text" id="reg_p6q3a1" name="p6q3a1" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>4. Bersedia setor minyak jelantah secara rutin?</label>
                    <input type="text" id="reg_q4" name="q4" class="form-control" readonly />
                </div>
            </div>
            <div class="row" id="regCompanyFnB" style="display:none;">
                <div class="col-md-6 mt-3">
                    <label>1. Memiliki usaha F&amp;B?</label>
                    <input type="text" id="reg_c_p6q1" name="c_p6q1" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Jumlah Outlet</label>
                    <input type="text" id="reg_c_p6q1a1" name="c_p6q1a1" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>2. Bergerak di bidang apa?</label>
                    <input type="text" id="reg_c_p6q2" name="c_p6q2" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>3. Memiliki relasi langsung dengan pelaku usaha F&amp;B?</label>
                    <input type="text" id="reg_c_p6q3" name="c_p6q3" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Jumlah Relasi</label>
                    <input type="text" id="reg_c_p6q3a1" name="c_p6q3a1" class="form-control" readonly />
                </div>
                <div class="col-md-6 mt-3">
                    <label>4. Bersedia setor minyak jelantah secara rutin?</label>
                    <input type="text" id="reg_c_p6q4" name="c_p6q4" class="form-control" readonly />
                </div>
            </div>

            {{-- Commitment Section --}}
            <div class="badge badge-secondary w-100 my-3 py-2">KOMITMEN</div>
            <div class="row" id="regIndividualCommitment">
                <div class="col-md-4 mt-3">
                    <label>1. Mematuhi standarisasi bisnis GEU</label>
                    <input type="text" id="reg_c_q1" name="c_q1" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>2. Support pencapaian target Hub GEU</label>
                    <input type="text" id="reg_c_q2" name="c_q2" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>3. Memahami risiko kerugian bisnis</label>
                    <input type="text" id="reg_c_q3" name="c_q3" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>4. Bersedia membayar investment fee</label>
                    <input type="text" id="reg_c_q4" name="c_q4" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>5. Tidak membuka bisnis sejenis</label>
                    <input type="text" id="reg_c_q5" name="c_q5" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>6. Menaati kontrak perjanjian</label>
                    <input type="text" id="reg_c_q6" name="c_q6" class="form-control" readonly />
                </div>
            </div>
            <div class="row" id="regCompanyCommitment" style="display:none;">
                <div class="col-md-4 mt-3">
                    <label>1. Mematuhi standarisasi bisnis GEU</label>
                    <input type="text" id="reg_q1_c" name="q1_c" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>2. Support pencapaian target Hub GEU</label>
                    <input type="text" id="reg_q2_c" name="q2_c" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>3. Memahami risiko kerugian bisnis</label>
                    <input type="text" id="reg_q3_c" name="q3_c" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>4. Bersedia membayar investment fee</label>
                    <input type="text" id="reg_q4_c" name="q4_c" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>5. Tidak membuka bisnis sejenis</label>
                    <input type="text" id="reg_q5_c" name="q5_c" class="form-control" readonly />
                </div>
                <div class="col-md-4 mt-3">
                    <label>6. Menaati kontrak perjanjian</label>
                    <input type="text" id="reg_q6_c" name="q6_c" class="form-control" readonly />
                </div>
            </div>

            <div class="col-12 mt-4">
                <button type="button" class="btn btn-secondary" id="CancelRegisteredDetail">Tutup</button>
            </div>
        </form>
    </div>
</div>

<script>
    var baseUrl = '{{ asset("filevendor") }}';
    $('#CancelRegisteredDetail').on('click', function() {
        $('#divRegisteredLeadsDetail').hide();
    });

    function getRegisteredLeads(id) {
        var rowId = id;
        // var leadId = $('#reg_lead_id').val();
        var params = 'type=registered';
        // if (leadId) params += '&lead_id=' + leadId;
        if (rowId) params += '&id=' + rowId;

        $.ajax({
            url: 'partner-leads?' + params,
            type: 'GET',
            success: function(res) {
                var data = res.data[0];
                if (!data) return;

                $('#reg_id').val(data.id);
                $('#reg_lead_id').val(data.lead_id);
                $('#reg_register_as').val(data.register_as);
                $('#reg_full_name').val(data.full_name);
                $('#reg_contact').val(data.contact);
                $('#reg_email').val(data.email);
                $('#reg_gender').val(data.gender);
                $('#reg_highest_education').val(data.highest_education);
                $('#reg_source_channels').val(data.source_channels);
                $('#reg_warehouse_count').val(data.warehouse_count);
                $('#reg_motivation').val(data.motivation);
                $('#reg_location_status').val(data.location_status);
                $('#reg_province').val(data.province);
                $('#reg_city').val(data.city);
                $('#reg_package_option').val(data.package_option);

                if (data.register_as === 'individual') {
                    $('#regIndividualFields').show();
                    $('#regCompanyFields').hide();
                    $('#regIndividualFnB').show();
                    $('#regCompanyFnB').hide();
                    $('#regIndividualCommitment').show();
                    $('#regCompanyCommitment').hide();

                    $('#reg_address').val(data.address);
                    $('#reg_pi_rt').val(data.pi_rt);
                    $('#reg_pi_rw').val(data.pi_rw);
                    $('#reg_pi_ward').val(data.pi_ward);
                    $('#reg_pi_subdistrict').val(data.pi_subdistrict);
                    $('#reg_pi_city').val(data.pi_city);
                    $('#reg_pi_province').val(data.pi_province);
                    $('#reg_pi_postal_code').val(data.pi_postal_code);
                    $('#reg_employment_status').val(data.employment_status);
                    $('#reg_company_name').val(data.company_name);
                    $('#reg_position').val(data.position);
                    $('#reg_length_of_work').val(data.length_of_work);
                    $('#reg_length_of_work_unit').val(data.length_of_work_unit);
                    $('#reg_company_address').val(data.company_address);
                    $('#reg_gross_monthly').val(data.gross_monthly);
                    $('#reg_gross_annual').val(data.gross_annual);
                    $('#reg_sources_of_funds').val(data.sources_of_funds);
                    ['KTP', 'NPWP', 'Others'].forEach(function(ele) {
                        var filePath = data.all_documents ? data.all_documents[ele] : null;
                        var container = (ele == 'Others') ?
                            '#reg_supporting_documents_container' :
                            '#reg_' + ele.toLowerCase() + '_container';
                        if (filePath) {
                            $(container).html('<a href="' + baseUrl + '/' + filePath + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-download"></i> Lihat Dokumen</a>');
                        } else {
                            $(container).html('<span class="text-muted">No document uploaded</span>');
                        }
                    });

                    $('#reg_q1').val(data.q1);
                    $('#reg_p6q1a1').val(data.c_p6q1a1);
                    $('#reg_q2').val(data.q2);
                    $('#reg_q3').val(data.q3);
                    $('#reg_p6q3a1').val(data.c_p6q3a1);
                    $('#reg_q4').val(data.q4);

                    $('#reg_c_q1').val(data.c_q1);
                    $('#reg_c_q2').val(data.c_q2);
                    $('#reg_c_q3').val(data.c_q3);
                    $('#reg_c_q4').val(data.c_q4);
                    $('#reg_c_q5').val(data.c_q5);
                    $('#reg_c_q6').val(data.c_q6);
                } else {
                    $('#regCompanyFields').show();
                    $('#regIndividualFields').hide();
                    $('#regCompanyFnB').show();
                    $('#regIndividualFnB').hide();
                    $('#regCompanyCommitment').show();
                    $('#regIndividualCommitment').hide();

                    $('#reg_company_official_name').val(data.company_official_name);
                    $('#reg_primary_business').val(data.primary_business);
                    $('#reg_legal_forms').val(data.legal_forms);
                    $('#reg_year_established').val(data.year_established);
                    $('#reg_company_tax_id').val(data.company_tax_id);
                    $('#reg_company_nib').val(data.company_nib);
                    $('#reg_head_office_address').val(data.head_office_address);
                    $('#reg_company_website').val(data.company_website);
                    $('#reg_pic_name').val(data.pic_name);
                    $('#reg_pic_position').val(data.pic_position);
                    $('#reg_pic_phone').val(data.pic_phone);
                    $('#reg_pic_email').val(data.pic_email);
                    $('#reg_company_scale').val(data.company_scale);
                    $('#reg_estimated_revenue').val(data.estimated_revenue);
                    var compHtml = '';
                    ['akta', 'nib', 'npwp', 'others'].forEach(function(type) {
                        var path = data.all_documents ? data.all_documents[type] : null;
                        if (path) {
                            compHtml += '<a href="' + baseUrl + '/' + path + '" target="_blank" class="btn btn-sm btn-outline-primary mr-2"><i class="fas fa-file-download"></i> Lihat Dokumen</a>';
                        }
                    });
                    if (compHtml === '') {
                        compHtml = '<span class="text-muted">No document uploaded</span>';
                    }
                    $('#reg_company_documents_container').html(compHtml);
                    $('#reg_c_p6q3a1').val(data.c_p6q3a1);
                    $('#reg_c_p6q4').val(data.c_p6q4);

                    $('#reg_q1_c').val(data.q1);
                    $('#reg_q2_c').val(data.q2);
                    $('#reg_q3_c').val(data.q3);
                    $('#reg_q4_c').val(data.q4);
                    $('#reg_q5_c').val(data.q5);
                    $('#reg_q6_c').val(data.q6);
                }
            },
            error: function() {
                alert('Gagal memuat data registrasi.');
            }
        });
    }
</script>