<style>
    .radio-lg {
        transform: scale(1.5);
    }
</style>
<div class="card mt-3">
    <div class="card-body" id="ContentPages">
        <div class="audit-title">
            FORM AUDIT & INSPEKSI LAPANGAN WAREHOUSE
        </div>

        <!-- A. INFORMASI UMUM -->
        <div class="section-title">A. Informasi Umum</div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Tanggal Audit</label>
            <div class="col-md-4">
                <input type="text" class="form-control bg-white" id="tanggal_audit" name="tanggal_audit" value="{{ date('Y-m-d') }}" readonly>
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Jenis Audit</label>
            <div class="col-md-9">

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit1" value="rutin">
                    <label class="form-check-label" for="jenis_audit1">Rutin</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit2" value="insidentil">
                    <label class="form-check-label" for="jenis_audit2">Insidentil</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit3" value="investigasi">
                    <label class="form-check-label" for="jenis_audit3">Investigasi</label>
                </div>

            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Nomor Audit</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="nomor_audit" id="nomor_audit">
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Kode Hub GEU Express</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="warehouse_task_id" id="warehouse_task_id">
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Lokasi Warehouse</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="warehouse_location" id="warehouse_location">
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Auditor / PIC Inspeksi</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="auditor" id="auditor">
            </div>
        </div>


        <!-- B -->
        <div class="section-title">B. Kondisi Umum Warehouse</div>

        <table class="table table-bordered" style="vertical-align: middle;">

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
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi1" value="sesuai"></td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi1" value="tidak"></td>
            <td><input class="form-control" name="kondisi1_note" id="kondisi1_note" /></td>
        </tr>

        <tr>
            <td>2</td>
            <td>Tidak ada tumpahan minyak</td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi2" value="sesuai"></td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi2" value="tidak"></td>
            <td><input class="form-control" name="kondisi2_note" id="kondisi2_note" /></td>
        </tr>

        <tr>
            <td>3</td>
            <td>Kebersihan & kelayakan area operasional</td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi3" value="sesuai"></td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi3" value="tidak"></td>
            <td><input class="form-control" name="kondisi3_note" id="kondisi3_note" /></td>
        </tr>

        <tr>
            <td>4</td>
            <td>Penerangan & ventilasi memadai</td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi4" value="sesuai"></td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi4" value="tidak"></td>
            <td><input class="form-control" name="kondisi4_note" id="kondisi4_note" /></td>
        </tr>

        <tr>
            <td>5</td>
            <td>Akses kendaraan operasional aman dan lancar</td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi5" value="sesuai"></td>
            <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="kondisi5" value="tidak"></td>
            <td><input class="form-control" name="kondisi5_note" id="kondisi5_note" /></td>
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
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k31" value="sesuai"></td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k31" value="tidak"></td>
                <td><input class="form-control" name="k31_note" id="k31_note" /></td>
            </tr>

            <tr>
                <td>2</td>
                <td>Booth</td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k32" value="sesuai"></td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k32" value="tidak"></td>
                <td><input class="form-control" name="k32_note" id="k32_note" /></td>
            </tr>

            <tr>
                <td>3</td>
                <td>Motor</td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k33" value="sesuai"></td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k33" value="tidak"></td>
                <td><input class="form-control" name="k33_note" id="k33_note" /></td>
            </tr>

            <tr>
                <td>4</td>
                <td>Mobil</td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k34" value="sesuai"></td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k34" value="tidak"></td>
                <td><input class="form-control" name="k34_note" id="k34_note" /></td>
            </tr>

            <tr>
                <td>5</td>
                <td>CCTV</td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k35" value="sesuai"></td>
                <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="k35" value="tidak"></td>
                <td><input class="form-control" name="k35_note" id="k35_note" /></td>
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
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="warna" value="baik"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="warna" value="cukup"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="warna" value="buruk"></td>
        <td><input class="form-control" name="warna_note" id="warna_note" /></td>
        </tr>

        <tr>
        <td>2</td>
        <td>Bau</td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="bau" value="baik"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="bau" value="cukup"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="bau" value="buruk"></td>
        <td><input class="form-control" name="bau_note" id="bau_note" /></td>
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
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="apd" value="ada"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="apd" value="tidak"></td>
        <td><input class="form-control" name="apd_note" id="apd_note" /></td>
        </tr>

        <tr>
        <td>2</td>
        <td>Alat penanganan tumpahan</td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="tumpahan" value="ada"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="tumpahan" ></td>
        <td><input class="form-control" name="tumpahan_note" id="tumpahan_note" /></td>
        </tr>

        <tr>
        <td>3</td>
        <td>Alat pemadam api ringan (APAR)</td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="apar" value="ada"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="apar" value="tidak"></td>
        <td><input class="form-control" name="apar_note" id="apar_note" /></td>
        </tr>

        <tr>
        <td>4</td>
        <td>Rambu keselamatan</td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="rambu" value="ada"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="rambu" value="tidak"></td>
        <td><input class="form-control" name="rambu_note" id="rambu_note" /></td>
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
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm1" value="sesuai"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm1" value="tidak"></td>
        <td><input class="form-control" name="adm1_note" id="adm1_note" /></td>
        </tr>

        <tr>
        <td>2</td>
        <td>Jadwal pickup dipatuhi</td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm2" value="sesuai"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm2" value="tidak"></td>
        <td><input class="form-control" name="adm2_note" id="adm2_note" /></td>
        </tr>

        <tr>
        <td>3</td>
        <td>Kondisi booth / mobil tertata dan layak operasional</td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm3" value="sesuai"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm3" value="tidak"></td>
        <td><input class="form-control" name="adm3_note" id="adm3_note" /></td>
        </tr>

        <tr>
        <td>4</td>
        <td>Pencatatan aktivitas pengumpulan dilakukan secara rutin</td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm4" value="sesuai"></td>
        <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg" name="adm4" value="tidak"></td>
        <td><input class="form-control" name="adm4_note" id="adm4_note" /></td>
        </tr>

        </tbody>
        </table>


        <!-- G -->
        <div class="section-title">G. Temuan Audit</div>

        <textarea class="form-control mb-3" rows="3" placeholder="Ringkasan Temuan"></textarea>

        <label>Kategori Temuan</label><br>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori" id="kategori1" value="ringan">
        <label class="form-check-label" for="kategori1">Ringan</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori" id="kategori2" value="sedang">
        <label class="form-check-label" for="kategori2">Sedang</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori" id="kategori3" value="berat">
        <label class="form-check-label" for="kategori3">Berat</label>
        </div>


        <!-- H -->
        <div class="section-title">H. Dokumentasi</div>

        <label>Foto Lokasi</label><br>

        <div class="form-check form-check-inline">
        <input type="radio" name="foto" id="foto1" value="ada">
        <label class="form-check-label" for="foto1">Ada</label>
        </div>

        <div class="form-check form-check-inline">
        <input type="radio" name="foto" id="foto2" value="tidak">
        <label class="form-check-label" for="foto2">Tidak</label>
        </div>

        <br><br>

        <label>Bukti Pendukung</label><br>

        <div class="form-check form-check-inline">
        <input type="radio" name="bukti" id="bukti1" value="ada">
        <label class="form-check-label" for="bukti1">Ada</label>
        </div>

        <div class="form-check form-check-inline">
        <input type="radio" name="bukti" id="bukti2" value="tidak">
        <label class="form-check-label" for="bukti2">Tidak</label>
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
        <input class="form-control" name="verifikasi" id="verifikasi">
        </div>

        <div class="col-md-4">
        <label>Auditee</label>
        <input class="form-control" name="auditee" id="auditee">
        </div>

        </div>
    </div>
</div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["tanggal_audit"]);
	myCalendar.setDateFormat("%Y-%m-%d");
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-warehouse-audit-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Opportunity Submitted!');

					location.href = "partner-warehouse-list";
				} else {
					alert('Opportunity Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}
</script>