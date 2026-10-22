<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Form Perawatan Kendaraan</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Pilihan Jenis Perawatan -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Jenis_Perawatan">Jenis Perawatan</label>
                        <select id="Jenis_Perawatan" name="Jenis_Perawatan" class="form-control shadow-sm" onchange="toggleJenisPerawatan(this.value)">
                            <option value="preventif">Preventive Maintenance (Preventif)</option>
                            <option value="korektif">Corrective Maintenance (Korektif)</option>
                        </select>
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

                <!-- Pilih Kendaraan dari asset_list (hanya sub category VEHICLE - VC) -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Asset_No">Pilih Kendaraan dari Daftar *</label>
                        <select id="Asset_No" name="Asset_No" class="form-control shadow-sm" onchange="PilihKendaraan()">
                            <option value="">-- Pilih Kendaraan --</option>
                            @foreach ($assetList as $a)
                                <option value="{{ $a->Asset_No }}"
                                        data-sn="{{ $a->SN }}"
                                        data-pc="{{ $a->Product_Code }}"
                                        data-pd="{{ $a->Product_Description }}"
                                        data-plat="{{ $a->Registration_No }}"
                                        data-kategori="{{ $a->Jenis_Kendaraan }}"
                                        data-km="{{ $a->KM_Saat_Ini }}"
                                        data-km-jadwal="{{ $a->KM_Jadwal_PM }}"
                                        data-interval="{{ $a->Interval_PM }}"
                                        data-tgl-jadwal="{{ $a->Tanggal_Jadwal_PM }}"
                                        data-tgl-terakhir="{{ $a->Tanggal_PM_Terakhir }}"
                                        data-vendor="{{ $a->Repair_Vendor }}"
                                        data-loc="{{ $a->Asset_Location ?? $a->Asset_Location_Department ?? '' }}">
                                    {{ $a->Asset_No }} — {{ $a->Product_Description ?? 'Tanpa Deskripsi' }} [SN: {{ $a->SN ?? 'N/A' }}]
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hanya asset dengan sub category VEHICLE - VC yang ditampilkan.</small>
                    </div>
                </div>

                <!-- Ringkasan data kendaraan (otomatis dari asset_list) -->
                <div class="col-md-12 col-12">
                    <div class="card border-info mb-2">
                        <div class="card-body py-2">
                            <h6 class="card-title text-info font-weight-bold mb-2"><i class="fas fa-car mr-1"></i>Data Kendaraan &amp; Jadwal PM (dari Master Asset)</h6>
                            <div class="row" id="KendaraanInfo">
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Plat Nomor</small>
                                    <div class="font-weight-bold" id="Info_Plat">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Kategori Kendaraan</small>
                                    <div class="font-weight-bold" id="Info_Kategori">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">KM Saat Ini</small>
                                    <div class="font-weight-bold" id="Info_KM">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Batas KM PM Berikutnya</small>
                                    <div class="font-weight-bold" id="Info_KMJadwal">-</div>
                                </div>
                                <div class="col-md-3 col-6 mt-2">
                                    <small class="text-muted">PM Terakhir</small>
                                    <div class="font-weight-bold" id="Info_PMTerakhir">-</div>
                                </div>
                                <div class="col-md-3 col-6 mt-2">
                                    <small class="text-muted">PM Berikutnya</small>
                                    <div class="font-weight-bold" id="Info_PMBerikutnya">-</div>
                                </div>
                                <div class="col-md-3 col-6 mt-2">
                                    <small class="text-muted">Interval PM</small>
                                    <div class="font-weight-bold" id="Info_Interval">-</div>
                                </div>
                                <div class="col-md-3 col-6 mt-2">
                                    <small class="text-muted">Vendor Bengkel</small>
                                    <div class="font-weight-bold" id="Info_Vendor">-</div>
                                </div>
                            </div>
                            <div id="PMWarning" class="alert alert-warning py-1 px-2 mt-2 mb-0" style="display:none;">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span id="PMWarningText"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Plat_Nomor">Plat Nomor</label>
                        <input type="text" id="Plat_Nomor" class="form-control shadow-sm" name="Plat_Nomor" value="" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Kategori_Kendaraan">Kategori Kendaraan</label>
                        <input type="text" id="Kategori_Kendaraan" class="form-control shadow-sm" name="Kategori_Kendaraan"
                               value="" readonly />
                        <small class="text-muted">Otomatis mengikuti master asset (tidak ada input ganda).</small>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Kilometer_Saat_Ini">Kilometer Saat Ini</label>
                        <input type="text" id="Kilometer_Saat_Ini" class="form-control shadow-sm" name="Kilometer_Saat_Ini"
                               value="" placeholder="Contoh: 45000" />
                    </div>
                </div>

                <!-- COMMON: Kondisi Awal -->
                <div class="col-md-12 col-12">
                    <div class="form-group">
                        <label for="Kondisi_Awal">Kondisi Awal Kendaraan</label>
                        <textarea id="Kondisi_Awal" class="form-control shadow-sm" name="Kondisi_Awal" rows="2"
                                  placeholder="Contoh: Mesin suara normal, rem depan mulai berkilap, AC dingin"></textarea>
                    </div>
                </div>

                <!-- === PREVENTIF Fields === -->
                <div class="col-md-6 col-12" id="preventifFields">
                    <div class="form-group">
                        <label for="Tipe_Service_Preventif">Tipe Service Preventif</label>
                        <select id="Tipe_Service_Preventif" name="Tipe_Service_Preventif" class="form-control shadow-sm">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="Ganti_Oli_dan_Filter">Ganti Oli &amp; Filter</option>
                            <option value="Cek_Rem_dan_Ban">Cek Rem, Ban &amp; Tekanan Angin</option>
                            <option value="Cek_Belt_dan_Hidrolika">Cek Belt, Aki &amp; Sistem Hidrolika</option>
                            <option value="Service_Aki_dan_Alternator">Service Aki &amp; Alternator</option>
                            <option value="Cek_Injeksi_dan_Carb">Cek Sistem Bahan Bakar (Injeksi/Carb)</option>
                            <option value="Cek_Transmisi_dan_Oli_Gear">Cek Transmisi &amp; Oli Gear</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-12" id="preventifFields2" style="display:none;">
                    <div class="form-group">
                        <label for="Jadwal_Perawatan_Bulan">Jadwal Perawatan Berdasarkan Bulan Ke Berapa</label>
                        <input type="number" id="Jadwal_Perawatan_Bulan" class="form-control shadow-sm" name="Jadwal_Perawatan_Bulan"
                               value="" min="1" max="12" placeholder="Contoh: 6 (setiap 6 bulan)" />
                        <small class="text-muted">* Otomatis terisi dari Master Asset (Interval PM). Bisa diubah bila siklus perawatan berbeda.</small>
                    </div>
                </div>

                <!-- === KOREKTIF Fields === -->
                <div class="col-md-12 col-12" id="korektifFields" style="display:none;">
                    <div class="form-group">
                        <label for="Kerusakan_Yang_Ditemui">Kerusakan Yang Ditemui *</label>
                        <textarea id="Kerusakan_Yang_Ditemui" class="form-control shadow-sm" name="Kerusakan_Yang_Ditemui" rows="3"
                                  placeholder="Contoh: Mesin bunyi bedeng saat akselerasi, oli bocor di crankshaft seal, rem belakang tidak merespon"></textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12" id="korektifFields2" style="display:none;">
                    <div class="form-group">
                        <label for="Penyebab_Diperkirakan">Penyebab Diperkirakan</label>
                        <textarea id="Penyebab_Diperkirakan" class="form-control shadow-sm" name="Penyebab_Diperkirakan" rows="2"
                                  placeholder="Contoh: Oli kotor terakumulasi sediment, seal kendor akibat panas berlebih, tromol rem aus"></textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12" id="korektifFields3" style="display:none;">
                    <div class="form-group">
                        <label for="Komponen_Yang_Diganti">Komponen Yang Diganti / Diperbaiki</label>
                        <textarea id="Komponen_Yang_Diganti" class="form-control shadow-sm" name="Komponen_Yang_Diganti" rows="2"
                                  placeholder="Contoh: Crankshaft oil seal, tromol rem belakang, pad rem depan, kabel sterning"></textarea>
                    </div>
                </div>

                <!-- Common Detail -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Keterangan_Lain">Keterangan Lain</label>
                        <textarea id="Keterangan_Lain" class="form-control shadow-sm" name="Keterangan_Lain" rows="2"></textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Estimasi_Biaya">Estimasi Biaya Perawatan (Rp)</label>
                        <input type="text" id="Estimasi_Biaya" class="form-control shadow-sm" name="Estimasi_Biaya" placeholder="Contoh: 2500000" value="" />
                        <small class="text-danger">
                            * Jika estimasi melebihi Rp 2.500.000, wajib melampirkan persetujuan Direktur.
                        </small>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="Vendor_Bengkel">Vendor / Bengkel</label>
                        <input type="text" id="Vendor_Bengkel" class="form-control shadow-sm" name="Vendor_Bengkel" value="" />
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
    function toggleJenisPerawatan(jenis) {
        if (jenis === 'preventif') {
            $('#preventifFields').show();
            $('#preventifFields2').show();
            $('#korektifFields').hide();
            $('#korektifFields2').hide();
            $('#korektifFields3').hide();
        } else {
            $('#preventifFields').hide();
            $('#preventifFields2').hide();
            $('#korektifFields').show();
            $('#korektifFields2').show();
            $('#korektifFields3').show();
        }
    }

    function fmtTgl(v) {
        if (!v || v === '0000-00-00') { return '-'; }
        var p = String(v).split('-');
        return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : v;
    }

    function fmtNum(v) {
        if (v === null || v === undefined || v === '') { return '-'; }
        var n = parseInt(String(v).replace(/[^0-9]/g, ''), 10);
        return isNaN(n) ? '-' : n.toLocaleString('id-ID');
    }

    /**
     * Ambil data kendaraan terpilih dari asset_list (tanpa input ulang):
     * plat, kategori, KM, dan seluruh jadwal PM.
     */
    function PilihKendaraan() {
        var select = $('#Asset_No');
        var val = select.val();

        if (!val) {
            $('#Plat_Nomor').val('');
            $('#Kategori_Kendaraan').val('');
            $('#Kilometer_Saat_Ini').val('');
            $('#Jadwal_Perawatan_Bulan').val('');
            $('#Info_Plat').text('-');
            $('#Info_Kategori').text('-');
            $('#Info_KM').text('-');
            $('#Info_KMJadwal').text('-');
            $('#Info_PMTerakhir').text('-');
            $('#Info_PMBerikutnya').text('-');
            $('#Info_Interval').text('-');
            $('#Info_Vendor').text('-');
            $('#PMWarning').hide();
            return;
        }

        var opt = select.find('option:selected');

        var plat = opt.data('plat') || '';
        var kategori = opt.data('kategori') || '';
        var km = opt.data('km') || '';
        var kmJadwal = opt.data('km-jadwal') || '';
        var interval = opt.data('interval') || '';
        var tglJadwal = opt.data('tgl-jadwal') || '';
        var tglTerakhir = opt.data('tgl-terakhir') || '';
        var vendor = opt.data('vendor') || '';

        $('#Plat_Nomor').val(plat);
        $('#Kategori_Kendaraan').val(kategori);
        $('#Kilometer_Saat_Ini').val(km);
        $('#Jadwal_Perawatan_Bulan').val(interval);

        $('#Info_Plat').text(plat || '-');
        $('#Info_Kategori').text(kategori || '-');
        $('#Info_KM').text(fmtNum(km));
        $('#Info_KMJadwal').text(fmtNum(kmJadwal));
        $('#Info_PMTerakhir').text(fmtTgl(tglTerakhir));
        $('#Info_PMBerikutnya').text(fmtTgl(tglJadwal));
        $('#Info_Interval').text(interval ? (interval + ' bulan') : '-');
        $('#Info_Vendor').text(vendor || '-');

        // Peringatan bila PM sudah jatuh tempo (tanggal atau KM)
        var pesan = '';
        if (tglJadwal && tglJadwal !== '0000-00-00') {
            var today = new Date(); today.setHours(0, 0, 0, 0);
            var due = new Date(tglJadwal);
            if (!isNaN(due.getTime()) && due.getTime() <= today.getTime()) {
                pesan = 'Jadwal PM tanggal ' + fmtTgl(tglJadwal) + ' sudah jatuh tempo.';
            }
        }
        if (km && kmJadwal && parseInt(km) >= parseInt(kmJadwal)) {
            pesan = (pesan ? pesan + ' ' : '') + 'KM saat ini (' + fmtNum(km) + ') sudah melewati batas KM PM (' + fmtNum(kmJadwal) + ').';
        }
        if (pesan) {
            $('#PMWarningText').text(pesan);
            $('#PMWarning').show();
        } else {
            $('#PMWarning').hide();
        }
    }

    function SubmitForm() {
        var jenis = $('#Jenis_Perawatan').val();
        var estimasiRaw = $('#Estimasi_Biaya').val().replace(/[^\\d]/g, '');
        var estimasi = parseFloat(estimasiRaw) || 0;

        var assetNo = $('#Asset_No').val();
        if (!assetNo) {
            alert('Pilih kendaraan dari daftar terlebih dahulu!');
            return;
        }

        if (jenis === 'preventif') {
            var tipe = $('#Tipe_Service_Preventif').val();
            if (!tipe) {
                alert('Pilih Tipe Service Preventif terlebih dahulu!');
                return;
            }
        }

        if (estimasi > 2500000) {
            if (!confirm('Estimasi biaya melebihi Rp 2.500.000. Data ini membutuhkan persetujuan Direktur.\\nLanjutkan submit?')) {
                return false;
            }
        }

        $.ajax({
            url: 'hrd-vehicle-maintenance-submit',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
            },
            data: {
                Jenis_Perawatan: jenis,
                Nama_Pengaju: $('#Nama_Pengaju').val(),
                Jabatan: $('#Jabatan').val(),
                Tanggal_Pengajuan: $('#Tanggal_Pengajuan').val(),
                Nomor_Formulir: $('#Nomor_Formulir').val(),
                Asset_No: $('#Asset_No').val(),
                Plat_Nomor: $('#Plat_Nomor').val(),
                Kategori_Kendaraan: $('#Kategori_Kendaraan').val(),
                Kilometer_Saat_Ini: $('#Kilometer_Saat_Ini').val(),
                Tipe_Service_Preventif: $('#Tipe_Service_Preventif').val(),
                Jadwal_Perawatan_Bulan: $('#Jadwal_Perawatan_Bulan').val(),
                Kerusakan_Yang_Ditemui: $('#Kerusakan_Yang_Ditemui').val(),
                Penyebab_Diperkirakan: $('#Penyebab_Diperkirakan').val(),
                Komponen_Yang_Diganti: $('#Komponen_Yang_Diganti').val(),
                Kondisi_Awal: $('#Kondisi_Awal').val(),
                Keterangan_Lain: $('#Keterangan_Lain').val(),
                Estimasi_Biaya: 'Rp ' + estimasi.toLocaleString('id-ID'),
                Vendor_Bengkel: $('#Vendor_Bengkel').val(),
            },
            success: function(data) {
                console.log(data);
                if (data.status === 'success') {
                    alert('Data berhasil disimpan');
                    setTimeout(function() {
                        if (typeof refreshdata === 'function') {
                            refreshdata();
                        }
                        if (typeof VehicleMaintenanceNew === 'function') {
                            VehicleMaintenanceNew();
                        }
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
        if (typeof VehicleMaintenanceNew === 'function') {
            VehicleMaintenanceNew();
        }
    }

    // Init: default Preventif
    $(function() {
        toggleJenisPerawatan('preventif');
    });
</script>