<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Form Perbaikan Aset</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Pilihan Jenis -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Jenis_Perbaikan">Jenis Perbaikan</label>
                        <select id="Jenis_Perbaikan" name="Jenis_Perbaikan" class="form-control shadow-sm" onchange="toggleFormLocation(this.value)">
                            <option value="office">Office</option>
                            <option value="site_gudang">Site Gudang</option>
                        </select>
                    </div>
                </div>

                <!-- Kolom khusus hanya untuk Office -->
                <div class="col-md-6 col-12" id="officeFields">
                    <div class="form-group">
                        <label for="PIC_Aset">PIC Aset</label>
                        <input type="text" id="PIC_Aset" class="form-control shadow-sm" name="PIC_Aset"
                               value="{{ Auth::user()->Name ?? '' }}" readonly />
                    </div>
                </div>

                <!-- Kolom khusus hanya untuk Site Gudang -->
                <div class="col-md-6 col-12" id="siteGudangFields" style="display:none;">
                    <div class="form-group">
                        <label for="Ast_Man_Ops_Support_Controller">Ast. Man. Ops. Support & Controller</label>
                        <input type="text" id="Ast_Man_Ops_Support_Controller" class="form-control shadow-sm"
                               name="Ast_Man_Ops_Support_Controller"
                               value="{{ Auth::user()->Name ?? '' }}" readonly />
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Nama_Pengaju">Nama Pengaju</label>
                        <input type="text" id="Nama_Pengaju" class="form-control shadow-sm" name="Nama_Pengaju"
                               value="{{ Auth::user()->Name ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Jabatan">Jabatan</label>
                        <input type="text" id="Jabatan" class="form-control shadow-sm" name="Jabatan"
                               value="{{ Auth::user()->Position ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Tanggal_Pengajuan">Tanggal Pengajuan</label>
                        <input type="date" id="Tanggal_Pengajuan" class="form-control shadow-sm" name="Tanggal_Pengajuan"
                               value="{{ date('Y-m-d') }}" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Nomor_Formulir">Nomor Formulir</label>
                        <input type="text" id="Nomor_Formulir" class="form-control shadow-sm" name="Nomor_Formulir" value="" />
                    </div>
                </div>

                <!-- Aset dari asset_list (pilihan) -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Asset_No">Pilih Aset dari Daftar *</label>
                        <select id="Asset_No" name="Asset_No" class="form-control shadow-sm" onchange="PilihAset()">
                            <option value="">-- Pilih Aset --</option>
                            @foreach ($assetList as $a)
                                <option value="{{ $a->Asset_No }}"
                                        data-sn="{{ $a->SN }}"
                                        data-pc="{{ $a->Product_Code }}"
                                        data-pd="{{ $a->Product_Description }}"
                                        data-loc="{{ $a->Asset_Location ?? $a->Asset_Location_Department ?? '' }}">
                                    {{ $a->Asset_No }} — {{ $a->Product_Description ?? 'Tanpa Deskripsi' }} [SN: {{ $a->SN ?? 'N/A' }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Nomor_Seri">Nomor Seri (SN) * — Dari asset_list</label>
                        <input type="text" id="Nomor_Seri" class="form-control shadow-sm" name="Nomor_Seri" value="" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Nama_Aset">Nama Aset / Deskripsi</label>
                        <input type="text" id="Nama_Aset" class="form-control shadow-sm" name="Nama_Aset" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Product_Code">Product Code</label>
                        <input type="text" id="Product_Code" class="form-control shadow-sm" name="Product_Code" value="" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Deskripsi_Aset">Deskripsi Aset (manual jika perlu)</label>
                        <input type="text" id="Deskripsi_Aset" class="form-control shadow-sm" name="Deskripsi_Aset" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Kondisi_Aset">Kondisi Aset</label>
                        <input type="text" id="Kondisi_Aset" class="form-control shadow-sm" name="Kondisi_Aset" value="" />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Lokasi_Saat_Ini">Lokasi Saat Ini</label>
                        <input type="text" id="Lokasi_Saat_Ini" class="form-control shadow-sm" name="Lokasi_Saat_Ini" value="" />
                    </div>
                </div>

                <!-- Detail Perbaikan -->
                <div class="col-md-12 col-12">
                    <div class="form-group">
                        <label for="Alasan_Perkiraan">Alasan Perbaikan</label>
                        <textarea id="Alasan_Perkiraan" class="form-control shadow-sm" name="Alasan_Perkiraan" rows="3"></textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Estimasi_Biaya">Estimasi Biaya Perbaikan (Rp)</label>
                        <input type="text" id="Estimasi_Biaya" class="form-control shadow-sm" name="Estimasi_Biaya" placeholder="Contoh: 2500000" value="" />
                        <small class="text-danger">
                            * Jika estimasi melebihi Rp 2.500.000, wajib melampirkan persetujuan Direktur.
                        </small>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Vendor_Yang_Digunakan">Vendor Yang Digunakan</label>
                        <input type="text" id="Vendor_Yang_Digunakan" class="form-control shadow-sm" name="Vendor_Yang_Digunakan" value="" />
                    </div>
                </div>

                <!-- Submit -->
                <div class="col-md-12 col-12 mt-3">
                    <button class="btn btn-primary" id="SubmitBtn" onclick="SubmitForm()">Submit</button>
                    <button class="btn btn-secondary" id="CancelBtn" onclick="CancelForm()">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFormLocation(jenis) {
        if (jenis === 'office') {
            $('#officeFields').show();
            $('#siteGudangFields').hide();
        } else {
            $('#officeFields').hide();
            $('#siteGudangFields').show();
        }
    }

    function PilihAset() {
        var select = $('#Asset_No');
        var val = select.val();
        if (!val) {
            $('#Nomor_Seri').val('');
            $('#Nama_Aset').val('');
            $('#Product_Code').val('');
            $('#Lokasi_Saat_Ini').val('');
            $('#Deskripsi_Aset').val('');
            return;
        }
        var opt = select.find('option:selected');
        var sn = opt.data('sn') || '';
        var pc = opt.data('pc') || '';
        var pd = opt.data('pd') || '';
        var loc = opt.data('loc') || '';
        $('#Nomor_Seri').val(sn);
        $('#Nama_Aset').val(pd);
        $('#Product_Code').val(pc);
        $('#Lokasi_Saat_Ini').val(loc);
        $('#Deskripsi_Aset').val(pd);
    }

    function SubmitForm() {
        var jenis = $('#Jenis_Perbaikan').val();
        var estimasiRaw = $('#Estimasi_Biaya').val().replace(/[^\d]/g, '');
        var estimasi = parseFloat(estimasiRaw) || 0;

        var assetNo = $('#Asset_No').val();
        var sn = $('#Nomor_Seri').val();
        if (!assetNo || !sn) {
            alert('Pilih aset dari daftar terlebih dahulu!');
            return;
        }

        if (estimasi > 2500000) {
            if (!confirm('Estimasi biaya melebihi Rp 2.500.000. Data ini membutuhkan persetujuan Direktur.\nLanjutkan submit?')) {
                return false;
            }
        }

        $.ajax({
            url: 'hrd-asset-fixing-submit',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
            },
            data: {
                Jenis_Perbaikan: jenis,
                Nama_Pengaju: $('#Nama_Pengaju').val(),
                Jabatan: $('#Jabatan').val(),
                Tanggal_Pengajuan: $('#Tanggal_Pengajuan').val(),
                Nomor_Formulir: $('#Nomor_Formulir').val(),
                Asset_No: $('#Asset_No').val(),
                Nomor_Seri: $('#Nomor_Seri').val(),
                Nama_Aset: $('#Nama_Aset').val(),
                Product_Code: $('#Product_Code').val(),
                Deskripsi_Aset: $('#Deskripsi_Aset').val(),
                Kondisi_Aset: $('#Kondisi_Aset').val(),
                Lokasi_Saat_Ini: $('#Lokasi_Saat_Ini').val(),
                Alasan_Perkiraan: $('#Alasan_Perkiraan').val(),
                Estimasi_Biaya: 'Rp ' + estimasi.toLocaleString('id-ID'),
                Vendor_Yang_Digunakan: $('#Vendor_Yang_Digunakan').val(),
            },
            success: function(data) {
                console.log(data);
                if (data.status === 'success') {
                    alert('Data berhasil disimpan');
                    setTimeout(function() {
                        if (typeof refreshdata === 'function') {
                            refreshdata();
                        }
                        AssetFixingNew();
                    }, 500);
                } else {
                    alert('Terjadi kesalahan saat menyimpan data: ' + (data.message || ''));
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server';
                alert(msg);
            }
        });
    }

    function CancelForm() {
        AssetFixingNew();
    }

    // Init: default Office
    $(function() {
        toggleFormLocation('office');
    });
</script>
