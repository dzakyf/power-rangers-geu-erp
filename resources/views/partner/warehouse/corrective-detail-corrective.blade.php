<style>
    .radio-lg {
        transform: scale(1.5);
    }
</style>
<div class="card mt-3">
    <div class="card-body">
        <div class="audit-title">
            FORM CORRECTIVE ACTION
        </div>
    
        <div class="row" id="ContentPages">
            <div class="col-12">
                <div class="form-group">
                    <input type="hidden" id="id" name="id" value="{{ $corrective->id }}">
                    <label for="corrective_action">Corrective Action</label>
                    <textarea name="corrective_action" id="corrective_action" rows="5" class="form-control" {{ $corrective->status == 'CLOSED' ? 'disabled' : '' }}>{{ $corrective->corrective_action }}</textarea>
                </div>
            </div>

            <div class="col-12 mt-3">
                <div class="form-group">
                    <label for="audit_compliance">Kepatuhan</label>
                    <select name="audit_compliance" id="audit_compliance" class="form-control" {{ $corrective->status == 'CLOSED' ? 'disabled' : '' }}>
                        <option value="Tidak Patuh" {{ $corrective->audit_compliance == 'Tidak Patuh' ? 'selected' : '' }}>Tidak Patuh</option>
                        <option value="Patuh" {{ $corrective->audit_compliance == 'Patuh' ? 'selected' : '' }}>Patuh</option>
                    </select>
                </div>
            </div>

            <div class="col-12 mt-3">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" {{ $corrective->status == 'CLOSED' ? 'disabled' : '' }}>
                        <option value="IN_PROGRESS" {{ $corrective->status == 'IN_PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
                        <option value="CLOSED" {{ $corrective->status == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                    </select>
                </div>
            </div>

            <div class="col-12 mt-3" {{ $corrective->status == 'CLOSED' ? 'style=display:none;' : '' }}>
                <button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
            </div>
        </div>
    </div>
</div>
<div class="card mt-3">
    <div class="card-body">
        <div class="audit-title">
            FORM AUDIT & INSPEKSI LAPANGAN WAREHOUSE
        </div>

        <!-- A. INFORMASI UMUM -->
        <div class="section-title">A. Informasi Umum</div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Tanggal Audit</label>
            <div class="col-md-4">
                <input type="text" class="form-control bg-white" id="tanggal_audit" name="tanggal_audit" value="{{ $getAudit->tanggal_audit }}" readonly>
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Jenis Audit</label>
            <div class="col-md-9">

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit1" value="rutin" {{ $getAudit->jenis_audit == 'rutin' ? 'checked' : '' }} disabled>
                    <label class="form-check-label" for="jenis_audit1">Rutin</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit2" value="insidentil" {{ $getAudit->jenis_audit == 'insidentil' ? 'checked' : '' }} disabled>
                    <label class="form-check-label" for="jenis_audit2">Insidentil</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_audit" id="jenis_audit3" value="investigasi" {{ $getAudit->jenis_audit == 'investigasi' ? 'checked' : '' }} disabled>
                    <label class="form-check-label" for="jenis_audit3">Investigasi</label>
                </div>

            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Nomor Audit</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="nomor_audit" id="nomor_audit" value="{{ $getAudit->nomor_audit }}" readonly>
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Kode Hub GEU Express</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="warehouse_task_id" id="warehouse_task_id" value="{{ $getAudit->warehouse_task_id }}" readonly>
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Lokasi Warehouse</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="warehouse_location" id="warehouse_location" value="{{ $getAudit->warehouse_location }}" readonly>
            </div>
        </div>

        <div class="form-group row align-items-center">
            <label class="col-md-3">Auditor / PIC Inspeksi</label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="auditor" id="auditor" value="{{ $getAudit->auditor }}" readonly>
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

        <textarea class="form-control mb-3" rows="3" placeholder="Ringkasan Temuan" id="ringkasan_temuan" name="ringkasan_temuan" readonly>{{ $getAudit->ringkasan_temuan }}</textarea>

        <label>Kategori Temuan</label><br>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori_temuan" id="kategori_temuan1" value="baik" {{ $getAudit->kategori_temuan == 'baik' ? 'checked' : '' }} disabled>
        <label class="form-check-label" for="kategori_temuan1">Baik</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori_temuan" id="kategori_temuan2" value="perbaikan" {{ $getAudit->kategori_temuan == 'perbaikan' ? 'checked' : '' }} disabled>
        <label class="form-check-label" for="kategori_temuan2">Perlu Perbaikan</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori_temuan" id="kategori_temuan3" value="tindakan" {{ $getAudit->kategori_temuan == 'tindakan' ? 'checked' : '' }} disabled>
        <label class="form-check-label" for="kategori_temuan3">Perlu Tindakan Khusus</label>
        </div>


        <!-- H -->
        <div class="section-title">H. Dokumentasi</div>

        <label>Foto Lokasi</label><br>

        <div class="form-check form-check-inline">
        <input type="radio" name="foto" id="foto1" value="ada" {{ $getAudit->foto_lokasi ? 'checked' : '' }} disabled>
        <label class="form-check-label" for="foto1">Ada</label>
        </div>

        <div class="form-check form-check-inline">
        <input type="radio" name="foto" id="foto2" value="tidak" {{ !$getAudit->foto_lokasi ? 'checked' : '' }} disabled>
        <label class="form-check-label" for="foto2">Tidak</label>
        </div>
        
        <div id="divFoto" class="my-3" style="display:{{ $getAudit->foto_lokasi ? 'block' : 'none' }};">
            <div class="col-3">
                <img src="{{ public_path() }}/{{ $getAudit->foto_lokasi_path }}" alt="foto lokasi" class="img-fluid">
            </div>
        </div>

        <br><br>

        <label>Bukti Pendukung</label><br>

        <div class="form-check form-check-inline">
        <input type="radio" name="bukti" id="bukti1" value="ada" {{ $getAudit->bukti_pendukung ? 'checked' : '' }} disabled>
        <label class="form-check-label" for="bukti1">Ada</label>
        </div>

        <div class="form-check form-check-inline">
        <input type="radio" name="bukti" id="bukti2" value="tidak" {{ !$getAudit->bukti_pendukung ? 'checked' : '' }} disabled>
        <label class="form-check-label" for="bukti2">Tidak</label>
        </div>

        <div id="divBukti" class="my-3" style="display:{{ $getAudit->bukti_pendukung ? 'block' : 'none' }};">
            <div class="col-3">
                <img src="{{ public_path() }}/{{ $getAudit->bukti_pendukung_path }}" alt="foto lokasi" class="img-fluid">
            </div>
        </div>


        <!-- I -->
        <div class="section-title">I. Tanda Tangan</div>

        <div class="row">

        <div class="col-md-4">
        <label>Auditor</label>
        <input class="form-control" name="auditor_ttd" id="auditor_ttd" value="{{ $getAudit->auditor }}" readonly>
        </div>

        <div class="col-md-4">
        <label>Verifikasi</label>
        <input class="form-control" name="verifikasi" id="verifikasi" value="{{ $getAudit->verifikasi }}" readonly>
        </div>

        <div class="col-md-4">
        <label>Auditee</label>
        <input class="form-control" name="auditee" id="auditee" value="{{ $getAudit->auditee }}" readonly>
        </div>

        </div>
    </div>
</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script>
    // Populate detail values from PHP data
    var auditDetails = {!! json_encode($getAuditDtl->pluck('parameter', 'section')->toArray()) !!};
    var auditNotes = {!! json_encode($getAuditDtl->pluck('catatan', 'section')->toArray()) !!};

    // Set values for all form fields
    $(document).ready(function() {
        for (let section in auditDetails) {
            let value = auditDetails[section];
            let note = auditNotes[section];
            
            // Set radio button values
            $('input[name="' + section + '"][value="' + value + '"]').prop('checked', true);
            $('input[name="' + section + '"]').prop('disabled', true);
            
            // Set note values
            let noteField = $('#' + section + '_note');
            if (noteField.length) {
                noteField.val(note).prop('readonly', true);
            }
        }
    });

	$("#warehouse_task_id").coolautosuggest({
		url:"partner-warehouse-audit-get-warehouse?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#warehouse_task_id"),
		width:700,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#warehouse_task_id").val(result.id);
			$("#warehouse_location").val(result.location);
		  }
		}
	});
    
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-warehouse-corrective-action-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.status;
				if (dataPos == 'success') {
					// var cariAWB = data.split(":");
					alert('Data Audit Submitted!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
				} else {
					alert('Audit Failed to Submit!');
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