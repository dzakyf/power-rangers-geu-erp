<div class="card mt-3">
    <div class="card-body" id="ContentPages">
        <div class="audit-title">
            FORM AUDIT & INSPEKSI LAPANGAN WAREHOUSE
        </div>

        <!-- A. INFORMASI UMUM -->
        <div class="section-title">A. Informasi Umum</div>

        <div class="form-group row">
            <label class="col-md-3">Tanggal Audit</label>
            <div class="col-md-4">
                <input type="text" class="form-control bg-white" id="tanggal_audit" name="tanggal_audit" value="{{ date('Y-m-d') }}" readonly>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3">Jenis Audit</label>
            <div class="col-md-9">

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit1" value="rutin">
                    <label class="form-check-label">Rutin</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit2" value="insidentil">
                    <label class="form-check-label">Insidentil</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit3" value="investigasi">
                    <label class="form-check-label">Investigasi</label>
                </div>

            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3">Nomor Audit</label>
            <div class="col-md-6">
                <input type="text" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3">Kode Hub GEU Express</label>
            <div class="col-md-6">
                <input type="text" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3">Lokasi Warehouse</label>
            <div class="col-md-6">
                <input type="text" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3">Auditor / PIC Inspeksi</label>
            <div class="col-md-6">
                <input type="text" class="form-control">
            </div>
        </div>


        <!-- B -->
        <div class="section-title">B. Kondisi Umum Warehouse</div>

        <table class="table table-bordered">

        <thead class="thead-light">
            <tr>
                <th width="5%">No</th>
                <th>Parameter</th>
                <th width="10%">Sesuai</th>
                <th width="10%">Tidak Sesuai</th>
                <th width="25%">Catatan</th>
            </tr>
        </thead>

        <tbody>

        <tr>
            <td>1</td>
            <td>Area bersih & bebas bau menyengat</td>
            <td class="text-center"><input type="radio" name="kondisi1" value="sesuai"></td>
            <td class="text-center"><input type="radio" name="kondisi1" value="tidak"></td>
            <td><input class="form-control"></td>
        </tr>

        <tr>
            <td>2</td>
            <td>Tidak ada tumpahan minyak</td>
            <td class="text-center"><input type="radio" name="kondisi2" value="sesuai"></td>
            <td class="text-center"><input type="radio" name="kondisi2" value="tidak"></td>
            <td><input class="form-control"></td>
        </tr>

        <tr>
            <td>3</td>
            <td>Kebersihan & kelayakan area operasional</td>
            <td class="text-center"><input type="radio" name="kondisi3" value="sesuai"></td>
            <td class="text-center"><input type="radio" name="kondisi3" value="tidak"></td>
            <td><input class="form-control"></td>
        </tr>

        <tr>
            <td>4</td>
            <td>Penerangan & ventilasi memadai</td>
            <td class="text-center"><input type="radio" name="kondisi4" value="sesuai"></td>
            <td class="text-center"><input type="radio" name="kondisi4" value="tidak"></td>
            <td><input class="form-control"></td>
        </tr>

        <tr>
            <td>5</td>
            <td>Akses kendaraan operasional aman dan lancar</td>
            <td class="text-center"><input type="radio" name="kondisi5" value="sesuai"></td>
            <td class="text-center"><input type="radio" name="kondisi5" value="tidak"></td>
            <td><input class="form-control"></td>
        </tr>

        </tbody>
        </table>


        <!-- C -->
        <div class="section-title">C. Penyimpanan & Peralatan K3</div>

        <table class="table table-bordered">

            <thead class="thead-light">
            <tr>
                <th>No</th>
                <th>Parameter</th>
                <th>Sesuai</th>
                <th>Tidak Sesuai</th>
                <th>Catatan</th>
            </tr>
            </thead>

            <tbody>

            <tr>
                <td>1</td>
                <td>Tangki / IBC sesuai standar</td>
                <td class="text-center"><input type="radio" name="k31"></td>
                <td class="text-center"><input type="radio" name="k31"></td>
                <td><input class="form-control"></td>
            </tr>

            <tr>
                <td>2</td>
                <td>Booth</td>
                <td class="text-center"><input type="radio" name="k32"></td>
                <td class="text-center"><input type="radio" name="k32"></td>
                <td><input class="form-control"></td>
            </tr>

            <tr>
                <td>3</td>
                <td>Motor</td>
                <td class="text-center"><input type="radio" name="k33"></td>
                <td class="text-center"><input type="radio" name="k33"></td>
                <td><input class="form-control"></td>
            </tr>

            <tr>
                <td>4</td>
                <td>Mobil</td>
                <td class="text-center"><input type="radio" name="k34"></td>
                <td class="text-center"><input type="radio" name="k34"></td>
                <td><input class="form-control"></td>
            </tr>

            <tr>
                <td>5</td>
                <td>CCTV</td>
                <td class="text-center"><input type="radio" name="k35"></td>
                <td class="text-center"><input type="radio" name="k35"></td>
                <td><input class="form-control"></td>
            </tr>

            </tbody>
        </table>


        <!-- D -->
        <div class="section-title">D. Kualitas Minyak Jelantah</div>

        <table class="table table-bordered">

        <thead class="thead-light">
        <tr>
        <th>No</th>
        <th>Parameter</th>
        <th>Baik</th>
        <th>Cukup</th>
        <th>Buruk</th>
        <th>Catatan</th>
        </tr>
        </thead>

        <tbody>

        <tr>
        <td>1</td>
        <td>Warna</td>
        <td class="text-center"><input type="radio" name="warna"></td>
        <td class="text-center"><input type="radio" name="warna"></td>
        <td class="text-center"><input type="radio" name="warna"></td>
        <td><input class="form-control"></td>
        </tr>

        <tr>
        <td>2</td>
        <td>Bau</td>
        <td class="text-center"><input type="radio" name="bau"></td>
        <td class="text-center"><input type="radio" name="bau"></td>
        <td class="text-center"><input type="radio" name="bau"></td>
        <td><input class="form-control"></td>
        </tr>

        </tbody>
        </table>


        <!-- E -->
        <div class="section-title">E. Peralatan & Keamanan</div>

        <table class="table table-bordered">

        <thead class="thead-light">
        <tr>
        <th>No</th>
        <th>Parameter</th>
        <th>Ada</th>
        <th>Tidak Ada</th>
        <th>Catatan</th>
        </tr>
        </thead>

        <tbody>

        <tr>
        <td>1</td>
        <td>APD (sarung tangan, sepatu, dll)</td>
        <td class="text-center"><input type="radio" name="apd"></td>
        <td class="text-center"><input type="radio" name="apd"></td>
        <td><input class="form-control"></td>
        </tr>

        <tr>
        <td>2</td>
        <td>Alat penanganan tumpahan</td>
        <td class="text-center"><input type="radio" name="tumpahan"></td>
        <td class="text-center"><input type="radio" name="tumpahan"></td>
        <td><input class="form-control"></td>
        </tr>

        <tr>
        <td>3</td>
        <td>Alat pemadam api ringan (APAR)</td>
        <td class="text-center"><input type="radio" name="apar"></td>
        <td class="text-center"><input type="radio" name="apar"></td>
        <td><input class="form-control"></td>
        </tr>

        <tr>
        <td>4</td>
        <td>Rambu keselamatan</td>
        <td class="text-center"><input type="radio" name="rambu"></td>
        <td class="text-center"><input type="radio" name="rambu"></td>
        <td><input class="form-control"></td>
        </tr>

        </tbody>
        </table>


        <!-- F -->
        <div class="section-title">F. Administrasi & Operasional</div>

        <table class="table table-bordered">

        <thead class="thead-light">
        <tr>
        <th>No</th>
        <th>Parameter</th>
        <th>Sesuai</th>
        <th>Tidak Sesuai</th>
        <th>Catatan</th>
        </tr>
        </thead>

        <tbody>

        <tr>
        <td>1</td>
        <td>Kesesuaian pencatatan stok utama dengan data di ERP</td>
        <td class="text-center"><input type="radio" name="adm1"></td>
        <td class="text-center"><input type="radio" name="adm1"></td>
        <td><input class="form-control"></td>
        </tr>

        <tr>
        <td>2</td>
        <td>Jadwal pickup dipatuhi</td>
        <td class="text-center"><input type="radio" name="adm2"></td>
        <td class="text-center"><input type="radio" name="adm2"></td>
        <td><input class="form-control"></td>
        </tr>

        <tr>
        <td>3</td>
        <td>Kondisi booth / mobil tertata dan layak operasional</td>
        <td class="text-center"><input type="radio" name="adm3"></td>
        <td class="text-center"><input type="radio" name="adm3"></td>
        <td><input class="form-control"></td>
        </tr>

        <tr>
        <td>4</td>
        <td>Pencatatan aktivitas pengumpulan dilakukan secara rutin</td>
        <td class="text-center"><input type="radio" name="adm4"></td>
        <td class="text-center"><input type="radio" name="adm4"></td>
        <td><input class="form-control"></td>
        </tr>

        </tbody>
        </table>


        <!-- G -->
        <div class="section-title">G. Temuan Audit</div>

        <textarea class="form-control mb-3" rows="3" placeholder="Ringkasan Temuan"></textarea>

        <label>Kategori Temuan</label><br>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="kategori">
        <label class="form-check-label">Ringan</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="kategori">
        <label class="form-check-label">Sedang</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="kategori">
        <label class="form-check-label">Berat</label>
        </div>


        <!-- H -->
        <div class="section-title">H. Dokumentasi</div>

        <label>Foto Lokasi</label><br>

        <div class="form-check form-check-inline">
        <input type="radio" name="foto">
        <label class="form-check-label">Ada</label>
        </div>

        <div class="form-check form-check-inline">
        <input type="radio" name="foto">
        <label class="form-check-label">Tidak</label>
        </div>

        <br><br>

        <label>Bukti Pendukung</label><br>

        <div class="form-check form-check-inline">
        <input type="radio" name="bukti">
        <label class="form-check-label">Ada</label>
        </div>

        <div class="form-check form-check-inline">
        <input type="radio" name="bukti">
        <label class="form-check-label">Tidak</label>
        </div>


        <!-- I -->
        <div class="section-title">I. Tanda Tangan</div>

        <div class="row">

        <div class="col-md-4">
        <label>Auditor</label>
        <input class="form-control">
        </div>

        <div class="col-md-4">
        <label>Verifikasi</label>
        <input class="form-control">
        </div>

        <div class="col-md-4">
        <label>Auditee</label>
        <input class="form-control">
        </div>

        </div>
    </div>
</div>