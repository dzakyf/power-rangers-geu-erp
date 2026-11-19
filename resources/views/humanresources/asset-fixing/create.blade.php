<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-tools me-2"></i>Form Perbaikan Aset</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#ASSETFIXINGDTL1').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Pengaju &amp; Lokasi</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="Jenis_Perbaikan" class="form-label small fw-semibold">Jenis Perbaikan</label>
                    <select id="Jenis_Perbaikan" name="Jenis_Perbaikan" class="form-select form-select-sm" onchange="toggleFormLocation(this.value)">
                        <option value="office">Office</option>
                        <option value="site_gudang">Site Gudang</option>
                    </select>
                </div>

                <div class="col-md-4" id="officeFields">
                    <label for="PIC_Aset" class="form-label small fw-semibold">PIC Aset</label>
                    <input type="text" id="PIC_Aset" class="form-control form-control-sm bg-light" name="PIC_Aset"
                           value="{{ Auth::user()->Name ?? '' }}" readonly />
                </div>

                <div class="col-md-4" id="siteGudangFields" style="display:none;">
                    <label for="Ast_Man_Ops_Support_Controller" class="form-label small fw-semibold">Ast. Man. Ops. Support &amp; Controller</label>
                    <input type="text" id="Ast_Man_Ops_Support_Controller" class="form-control form-control-sm bg-light"
                           name="Ast_Man_Ops_Support_Controller"
                           value="{{ Auth::user()->Name ?? '' }}" readonly />
                </div>

                <div class="col-md-4">
                    <label for="Tanggal_Pengajuan" class="form-label small fw-semibold">Tanggal Pengajuan</label>
                    <input type="date" id="Tanggal_Pengajuan" class="form-control form-control-sm" name="Tanggal_Pengajuan"
                           value="{{ date('Y-m-d') }}" />
                </div>

                <div class="col-md-4">
                    <label for="Nama_Pengaju" class="form-label small fw-semibold">Nama Pengaju</label>
                    <input type="text" id="Nama_Pengaju" class="form-control form-control-sm bg-light" name="Nama_Pengaju"
                           value="{{ Auth::user()->Name ?? '' }}" readonly />
                </div>

                <div class="col-md-4">
                    <label for="Jabatan" class="form-label small fw-semibold">Jabatan</label>
                    <input type="text" id="Jabatan" class="form-control form-control-sm bg-light" name="Jabatan"
                           value="{{ Auth::user()->Position ?? '' }}" readonly />
                </div>

                <div class="col-md-4">
                    <label for="Nomor_Formulir" class="form-label small fw-semibold">Nomor Formulir</label>
                    <input type="text" id="Nomor_Formulir" class="form-control form-control-sm" name="Nomor_Formulir" value="" placeholder="Opsional" />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Aset</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="Asset_No" class="form-label small fw-semibold">Pilih Aset dari Daftar <span class="text-danger">*</span></label>
                    <select id="Asset_No" name="Asset_No" class="form-select form-select-sm" onchange="PilihAset()">
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

                <div class="col-md-3">
                    <label for="Nomor_Seri" class="form-label small fw-semibold">Nomor Seri (SN) <span class="text-danger">*</span></label>
                    <input type="text" id="Nomor_Seri" class="form-control form-control-sm bg-light" name="Nomor_Seri" value="" readonly />
                </div>

                <div class="col-md-3">
                    <label for="Product_Code" class="form-label small fw-semibold">Product Code</label>
                    <input type="text" id="Product_Code" class="form-control form-control-sm bg-light" name="Product_Code" value="" readonly />
                </div>

                <div class="col-md-6">
                    <label for="Nama_Aset" class="form-label small fw-semibold">Nama Aset / Deskripsi</label>
                    <input type="text" id="Nama_Aset" class="form-control form-control-sm" name="Nama_Aset" value="" />
                </div>

                <div class="col-md-6">
                    <label for="Deskripsi_Aset" class="form-label small fw-semibold">Deskripsi Aset (manual jika perlu)</label>
                    <input type="text" id="Deskripsi_Aset" class="form-control form-control-sm" name="Deskripsi_Aset" value="" />
                </div>

                <div class="col-md-6">
                    <label for="Kondisi_Aset" class="form-label small fw-semibold">Kondisi Aset</label>
                    <input type="text" id="Kondisi_Aset" class="form-control form-control-sm" name="Kondisi_Aset" value="" />
                </div>

                <div class="col-md-6">
                    <label for="Lokasi_Saat_Ini" class="form-label small fw-semibold">Lokasi Saat Ini</label>
                    <input type="text" id="Lokasi_Saat_Ini" class="form-control form-control-sm" name="Lokasi_Saat_Ini" value="" />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Perbaikan &amp; Estimasi</h6>
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="Alasan_Perkiraan" class="form-label small fw-semibold">Alasan Perbaikan</label>
                    <textarea id="Alasan_Perkiraan" class="form-control form-control-sm" name="Alasan_Perkiraan" rows="3"></textarea>
                </div>

                <div class="col-md-6">
                    <label for="Estimasi_Biaya" class="form-label small fw-semibold">Estimasi Biaya Perbaikan (Rp)</label>
                    <input type="text" id="Estimasi_Biaya" class="form-control form-control-sm" name="Estimasi_Biaya" placeholder="Contoh: 2500000" value="" />
                    <div class="form-text text-danger">
                        * Jika estimasi melebihi Rp 2.500.000, wajib melampirkan persetujuan Direktur.
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="Vendor_Yang_Digunakan" class="form-label small fw-semibold">Vendor Yang Digunakan</label>
                    <input type="text" id="Vendor_Yang_Digunakan" class="form-control form-control-sm" name="Vendor_Yang_Digunakan" value="" />
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm" id="CancelBtn" onclick="CancelForm()">
                    <i class="fa fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="SubmitBtn" onclick="SubmitForm()">
                    <i class="fa fa-check me-1"></i> Submit
                </button>
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
                if (data.status === 'success') {
                    alert('Data berhasil disimpan');
                    setTimeout(function() {
                        if (typeof refreshdata === 'function') {
                            refreshdata();
                        }
                        $('#ASSETFIXINGDTL1').html('');
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
        $('#ASSETFIXINGDTL1').html('');
    }

    $(function() {
        toggleFormLocation('office');
    });
</script>
