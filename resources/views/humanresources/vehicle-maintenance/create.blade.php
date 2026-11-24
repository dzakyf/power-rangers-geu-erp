<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-car me-2"></i>Form Perawatan Kendaraan</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#VMDTL1').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Pengajuan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="Jenis_Perawatan" class="form-label small fw-semibold">Jenis Perawatan</label>
                    <select id="Jenis_Perawatan" name="Jenis_Perawatan" class="form-select form-select-sm" onchange="toggleJenisPerawatan(this.value)">
                        <option value="preventif" {{ ($prefillJenis ?? 'preventif') === 'preventif' ? 'selected' : '' }}>Preventive Maintenance (Preventif)</option>
                        <option value="korektif" {{ ($prefillJenis ?? 'preventif') === 'korektif' ? 'selected' : '' }}>Corrective Maintenance (Korektif)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="Tanggal_Pengajuan" class="form-label small fw-semibold">Tanggal Pengajuan</label>
                    <input type="date" id="Tanggal_Pengajuan" class="form-control form-control-sm" name="Tanggal_Pengajuan" value="{{ date('Y-m-d') }}" />
                </div>
                <div class="col-md-4">
                    <label for="Nomor_Formulir" class="form-label small fw-semibold">Nomor Formulir</label>
                    <input type="text" id="Nomor_Formulir" class="form-control form-control-sm" name="Nomor_Formulir" value="" placeholder="Opsional" />
                </div>
                <div class="col-md-6">
                    <label for="Nama_Pengaju" class="form-label small fw-semibold">Nama Pengaju</label>
                    <input type="text" id="Nama_Pengaju" class="form-control form-control-sm bg-light" name="Nama_Pengaju" value="{{ Auth::user()->Name ?? '' }}" readonly />
                </div>
                <div class="col-md-6">
                    <label for="Jabatan" class="form-label small fw-semibold">Jabatan</label>
                    <input type="text" id="Jabatan" class="form-control form-control-sm bg-light" name="Jabatan" value="{{ Auth::user()->Position ?? '' }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Data Kendaraan &amp; Jadwal PM</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label for="Asset_No" class="form-label small fw-semibold">Pilih Kendaraan dari Daftar <span class="text-danger">*</span></label>
                    <select id="Asset_No" name="Asset_No" class="form-select form-select-sm" onchange="PilihKendaraan()">
                        <option value="">-- Pilih Kendaraan --</option>
                        @foreach ($assetList as $a)
                            <option value="{{ $a->Asset_No }}"
                                    {{ ($prefillAssetNo ?? '') === $a->Asset_No ? 'selected' : '' }}
                                    data-sn="{{ $a->SN }}"
                                    data-pc="{{ $a->Product_Code }}"
                                    data-pd="{{ $a->Product_Description }}"
                                    data-plat="{{ $a->Registration_No }}"
                                    data-kategori="{{ $a->Jenis_Kendaraan ?? $a->Asset_Type ?? '' }}"
                                    data-km="{{ $a->KM_Saat_Ini }}"
                                    data-km-jadwal="{{ $a->KM_Jadwal_PM }}"
                                    data-interval="{{ $a->Interval_PM }}"
                                    data-tgl-jadwal="{{ $a->Tanggal_Jadwal_PM }}"
                                    data-tgl-terakhir="{{ $a->Tanggal_PM_Terakhir }}"
                                    data-vendor="{{ $a->Repair_Vendor }}"
                                    data-loc="{{ $a->Asset_Location ?? $a->Asset_Location_Department ?? '' }}">
                                {{ $a->Asset_No }} — {{ $a->Product_Description ?? 'Tanpa Deskripsi' }} [Plat: {{ $a->Registration_No ?: 'N/A' }}]
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Hanya asset kendaraan yang ditampilkan. Odometer otomatis memakai nilai tertinggi dari Master / Logbook / Fuel.</div>
                </div>

                <div class="col-md-12">
                    <div class="card border-info bg-light mb-0">
                        <div class="card-body py-2">
                            <h6 class="card-title text-info fw-bold mb-2"><i class="fas fa-car me-1"></i>Data Kendaraan &amp; Jadwal PM (dari Master Asset)</h6>
                            <div class="row g-2" id="KendaraanInfo">
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Plat Nomor</small>
                                    <div class="fw-bold" id="Info_Plat">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Kategori Kendaraan</small>
                                    <div class="fw-bold" id="Info_Kategori">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">KM Saat Ini</small>
                                    <div class="fw-bold" id="Info_KM">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Batas KM PM Berikutnya</small>
                                    <div class="fw-bold" id="Info_KMJadwal">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">PM Terakhir</small>
                                    <div class="fw-bold" id="Info_PMTerakhir">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">PM Berikutnya</small>
                                    <div class="fw-bold" id="Info_PMBerikutnya">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Interval PM</small>
                                    <div class="fw-bold" id="Info_Interval">-</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Vendor Bengkel</small>
                                    <div class="fw-bold" id="Info_Vendor">-</div>
                                </div>
                            </div>
                            <div id="PMWarning" class="alert alert-warning py-1 px-2 mt-2 mb-0" style="display:none;">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="PMWarningText"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="Plat_Nomor" class="form-label small fw-semibold">Plat Nomor</label>
                    <input type="text" id="Plat_Nomor" class="form-control form-control-sm bg-light" name="Plat_Nomor" value="" readonly />
                </div>
                <div class="col-md-4">
                    <label for="Kategori_Kendaraan" class="form-label small fw-semibold">Kategori Kendaraan</label>
                    <input type="text" id="Kategori_Kendaraan" class="form-control form-control-sm bg-light" name="Kategori_Kendaraan" value="" readonly />
                    <div class="form-text">Otomatis mengikuti master asset.</div>
                </div>
                <div class="col-md-4">
                    <label for="Kilometer_Saat_Ini" class="form-label small fw-semibold">Kilometer Saat Ini</label>
                    <input type="text" id="Kilometer_Saat_Ini" class="form-control form-control-sm" name="Kilometer_Saat_Ini" value="" placeholder="Contoh: 45000" />
                </div>
                <div class="col-md-12">
                    <label for="Kondisi_Awal" class="form-label small fw-semibold">Kondisi Awal Kendaraan</label>
                    <textarea id="Kondisi_Awal" class="form-control form-control-sm" name="Kondisi_Awal" rows="2" placeholder="Contoh: Mesin suara normal, rem depan mulai bergetar, AC dingin"></textarea>
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Pekerjaan &amp; Estimasi</h6>
            <div class="row g-3">
                <div class="col-md-6" id="preventifFields">
                    <label for="Tipe_Service_Preventif" class="form-label small fw-semibold">Tipe Service Preventif <span class="text-danger">*</span></label>
                    <select id="Tipe_Service_Preventif" name="Tipe_Service_Preventif" class="form-select form-select-sm">
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

                <div class="col-md-6" id="preventifFields2" style="display:none;">
                    <label for="Jadwal_Perawatan_Bulan" class="form-label small fw-semibold">Jadwal Perawatan (Bulan Ke-)</label>
                    <input type="number" id="Jadwal_Perawatan_Bulan" class="form-control form-control-sm" name="Jadwal_Perawatan_Bulan" value="" min="1" max="24" placeholder="Contoh: 6 (setiap 6 bulan)" />
                    <div class="form-text">* Otomatis terisi dari Master Asset (Interval PM).</div>
                </div>

                <div class="col-md-12" id="korektifFields" style="display:none;">
                    <label for="Kerusakan_Yang_Ditemui" class="form-label small fw-semibold">Kerusakan Yang Ditemui <span class="text-danger">*</span></label>
                    <textarea id="Kerusakan_Yang_Ditemui" class="form-control form-control-sm" name="Kerusakan_Yang_Ditemui" rows="3" placeholder="Jelaskan kerusakan yang ditemui..."></textarea>
                </div>

                <div class="col-md-6" id="korektifFields2" style="display:none;">
                    <label for="Penyebab_Diperkirakan" class="form-label small fw-semibold">Penyebab Diperkirakan</label>
                    <textarea id="Penyebab_Diperkirakan" class="form-control form-control-sm" name="Penyebab_Diperkirakan" rows="2" placeholder="Perkiraan penyebab kerusakan..."></textarea>
                </div>

                <div class="col-md-6" id="korektifFields3" style="display:none;">
                    <label for="Komponen_Yang_Diganti" class="form-label small fw-semibold">Komponen Yang Diganti / Diperbaiki</label>
                    <textarea id="Komponen_Yang_Diganti" class="form-control form-control-sm" name="Komponen_Yang_Diganti" rows="2" placeholder="Daftar komponen yang diganti/diperbaiki..."></textarea>
                </div>

                <div class="col-md-6">
                    <label for="Estimasi_Biaya" class="form-label small fw-semibold">Estimasi Biaya Perawatan (Rp)</label>
                    <input type="text" id="Estimasi_Biaya" class="form-control form-control-sm" name="Estimasi_Biaya" placeholder="Contoh: 2500000" value="" />
                    <div class="form-text text-danger">* Jika estimasi melebihi Rp 2.500.000, alur approval otomatis melewati tahap VP / Direktur.</div>
                </div>

                <div class="col-md-6">
                    <label for="Vendor_Bengkel" class="form-label small fw-semibold">Vendor / Bengkel</label>
                    <input type="text" id="Vendor_Bengkel" class="form-control form-control-sm" name="Vendor_Bengkel" value="" />
                </div>

                <div class="col-md-12">
                    <label for="Keterangan_Lain" class="form-label small fw-semibold">Keterangan Lain</label>
                    <textarea id="Keterangan_Lain" class="form-control form-control-sm" name="Keterangan_Lain" rows="2"></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm" id="CancelBtn" onclick="CancelForm()">
                    <i class="fa fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" id="DraftBtn" onclick="SubmitForm(true)">
                    <i class="fa fa-save me-1"></i> Simpan Draft
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="SubmitBtn" onclick="SubmitForm(false)">
                    <i class="fa fa-paper-plane me-1"></i> Submit &amp; Ajukan Approval
                </button>
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

    function PilihKendaraan() {
        var select = $('#Asset_No');
        var val = select.val();

        if (!val) {
            $('#Plat_Nomor').val('');
            $('#Kategori_Kendaraan').val('');
            $('#Kilometer_Saat_Ini').val('');
            $('#Jadwal_Perawatan_Bulan').val('');
            $('#Vendor_Bengkel').val('');
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
        if (!$('#Vendor_Bengkel').val() && vendor) {
            $('#Vendor_Bengkel').val(vendor);
        }

        $('#Info_Plat').text(plat || '-');
        $('#Info_Kategori').text(kategori || '-');
        $('#Info_KM').text(fmtNum(km));
        $('#Info_KMJadwal').text(fmtNum(kmJadwal));
        $('#Info_PMTerakhir').text(fmtTgl(tglTerakhir));
        $('#Info_PMBerikutnya').text(fmtTgl(tglJadwal));
        $('#Info_Interval').text(interval ? (interval + ' bulan') : '-');
        $('#Info_Vendor').text(vendor || '-');

        var pesan = '';
        if (tglJadwal && tglJadwal !== '0000-00-00') {
            var today = new Date(); today.setHours(0, 0, 0, 0);
            var due = new Date(tglJadwal);
            if (!isNaN(due.getTime()) && due.getTime() <= today.getTime()) {
                pesan = 'Jadwal PM tanggal ' + fmtTgl(tglJadwal) + ' sudah jatuh tempo.';
            }
        }
        if (km && kmJadwal && parseInt(km, 10) >= parseInt(kmJadwal, 10)) {
            pesan = (pesan ? pesan + ' ' : '') + 'KM saat ini (' + fmtNum(km) + ') sudah melewati batas KM PM (' + fmtNum(kmJadwal) + ').';
        }
        if (pesan) {
            $('#PMWarningText').text(pesan);
            $('#PMWarning').show();
        } else {
            $('#PMWarning').hide();
        }
    }

    function SubmitForm(asDraft) {
        var jenis = $('#Jenis_Perawatan').val();
        var estimasiRaw = $('#Estimasi_Biaya').val().replace(/[^\d]/g, '');
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
        } else {
            var kerusakan = $.trim($('#Kerusakan_Yang_Ditemui').val());
            if (!kerusakan) {
                alert('Isi Kerusakan Yang Ditemui untuk perawatan korektif!');
                return;
            }
        }

        if (!asDraft && estimasi > 2500000) {
            if (!confirm('Estimasi biaya melebihi Rp 2.500.000. Pengajuan akan melewati persetujuan VP/Direktur.\nLanjutkan submit?')) {
                return false;
            }
        }

        $('#DraftBtn, #SubmitBtn').prop('disabled', true);

        $.ajax({
            url: 'hrd-vehicle-maintenance-submit',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: {
                as_draft: asDraft ? 1 : 0,
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
                Vendor_Bengkel: $('#Vendor_Bengkel').val()
            },
            success: function(data) {
                if (data.status === 'success') {
                    alert(data.message || 'Data berhasil disimpan');
                    setTimeout(function() {
                        if (typeof refreshdata === 'function') refreshdata();
                        $('#VMDTL1').html('');
                    }, 400);
                } else {
                    alert('Terjadi kesalahan saat menyimpan data: ' + (data.message || ''));
                    $('#DraftBtn, #SubmitBtn').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server';
                alert(msg);
                $('#DraftBtn, #SubmitBtn').prop('disabled', false);
            }
        });
    }

    function CancelForm() {
        $('#VMDTL1').html('');
    }

    $(function() {
        toggleJenisPerawatan($('#Jenis_Perawatan').val() || 'preventif');
        if ($('#Asset_No').val()) {
            PilihKendaraan();
        }
    });
</script>
