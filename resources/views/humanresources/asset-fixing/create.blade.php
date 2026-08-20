<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$assetList = DB::table('asset_list')
    ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location_Department', 'Asset_Location', 'WH_ID')
    ->get();
?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
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
                                   value="<?= Auth::user()->Name ?? '' ?>" readonly />
                        </div>
                    </div>

                    <!-- Kolom khusus hanya untuk Site Gudang -->
                    <div class="col-md-6 col-12" id="siteGudangFields" style="display:none;">
                        <div class="form-group">
                            <label for="Ast_Man_Ops_Support_Controller">Ast. Man. Ops. Support & Controller</label>
                            <input type="text" id="Ast_Man_Ops_Support_Controller" class="form-control shadow-sm"
                                   name="Ast_Man_Ops_Support_Controller"
                                   value="<?= Auth::user()->Name ?? '' ?>" readonly />
                        </div>
                    </div>

                    <!-- Common Fields -->
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Nama_Pengaju">Nama Pengaju</label>
                            <input type="text" id="Nama_Pengaju" class="form-control shadow-sm" name="Nama_Pengaju"
                                   value="<?= Auth::user()->Name ?? '' ?>" readonly />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Jabatan">Jabatan</label>
                            <input type="text" id="Jabatan" class="form-control shadow-sm" name="Jabatan"
                                   value="<?= Auth::user()->Position ?? '' ?>" readonly />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Tanggal_Pengajuan">Tanggal Pengajuan</label>
                            <input type="date" id="Tanggal_Pengajuan" class="form-control shadow-sm" name="Tanggal_Pengajuan"
                                   value="<?= date('Y-m-d') ?>" />
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
                                    <option value="{{ $a->Asset_No }}" data-sn="{{ $a->SN }}" data-pc="{{ $a->Product_Code }}" data-pd="{{ $a->Product_Description }}" data-loc="{{ $a->Asset_Location ?? $a->Asset_Location_Department ?? '' }}">
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
</div>

<script>
    function toggleFormLocation(jenis) {
        if (jenis === 'office') {
            document.getElementById('officeFields').style.display = 'block';
            document.getElementById('siteGudangFields').style.display = 'none';
        } else {
            document.getElementById('officeFields').style.display = 'none';
            document.getElementById('siteGudangFields').style.display = 'block';
        }
    }

    function PilihAset() {
        var select = document.getElementById('Asset_No');
        var val = select.value;
        if (!val) {
            document.getElementById('Nomor_Seri').value = '';
            document.getElementById('Nama_Aset').value = '';
            document.getElementById('Product_Code').value = '';
            document.getElementById('Lokasi_Saat_Ini').value = '';
            document.getElementById('Deskripsi_Aset').value = '';
            return;
        }
        var opt = select.options[select.selectedIndex];
        var sn = opt.getAttribute('data-sn') || '';
        var pc = opt.getAttribute('data-pc') || '';
        var pd = opt.getAttribute('data-pd') || '';
        var loc = opt.getAttribute('data-loc') || '';
        document.getElementById('Nomor_Seri').value = sn;
        document.getElementById('Nama_Aset').value = pd;
        document.getElementById('Product_Code').value = pc;
        document.getElementById('Lokasi_Saat_Ini').value = loc;
        document.getElementById('Deskripsi_Aset').value = pd; // isi otomatis deskripsi dari asset_list
    }

    function SubmitForm() {
        var jenis = document.getElementById('Jenis_Perbaikan').value;
        var estimasiRaw = document.getElementById('Estimasi_Biaya').value.replace(/[^\d]/g, '');
        var estimasi = parseFloat(estimasiRaw) || 0;

        var assetNo = document.getElementById('Asset_No').value;
        var sn = document.getElementById('Nomor_Seri').value;
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                Jenis_Perbaikan: jenis,
                Nama_Pengaju: document.getElementById('Nama_Pengaju').value,
                Jabatan: document.getElementById('Jabatan').value,
                Tanggal_Pengajuan: document.getElementById('Tanggal_Pengajuan').value,
                Nomor_Formulir: document.getElementById('Nomor_Formulir').value,
                Asset_No: document.getElementById('Asset_No').value,
                Nomor_Seri: document.getElementById('Nomor_Seri').value,
                Nama_Aset: document.getElementById('Nama_Aset').value,
                Product_Code: document.getElementById('Product_Code').value,
                Deskripsi_Aset: document.getElementById('Deskripsi_Aset').value,
                Kondisi_Aset: document.getElementById('Kondisi_Aset').value,
                Lokasi_Saat_Ini: document.getElementById('Lokasi_Saat_Ini').value,
                Alasan_Perkiraan: document.getElementById('Alasan_Perkiraan').value,
                Estimasi_Biaya: 'Rp ' + estimasi.toLocaleString('id-ID'),
                Vendor_Yang_Digunakan: document.getElementById('Vendor_Yang_Digunakan').value,
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
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan server';
                alert(msg);
            }
        });
    }

    function CancelForm() {
        AssetFixingNew();
    }

    // Init: default Office
    toggleFormLocation('office');
</script>
@endsection
