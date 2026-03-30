<style>
    .radio-lg {
        transform: scale(1.5);
    }
</style>
@php
    $indexUrut = 0;
@endphp
<div class="card mt-3">
    <div class="card-body" id="ContentPages">
        <div class="audit-title">
            FORM AUDIT & INSPEKSI LAPANGAN WAREHOUSE
        </div>

        <!-- A. INFORMASI UMUM -->
        <div class="section-title">{{ $urut[$indexUrut++] }}. Informasi Umum</div>

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
                <input type="text" class="form-control" name="nomor_audit" id="nomor_audit" value="{{ $nomor }}" readonly>
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
                <select name="auditor" id="auditor" class="form-control">
                    <option value="">-- Pilih auditor --</option>
                    @foreach ($users as $item)
                    <option value="{{ $item->username }}">{{ $item->Name }}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <!-- B -->
        @foreach ($category as $item)
            <div class="section-title">{{ $urut[$indexUrut++] }}. {{ $item->category_description }}</div>

            <table class="table table-bordered" style="vertical-align: middle;">

            <thead class="thead-light">
                <tr>
                    <th width="5%">No</th>
                    <th>Parameter</th>
                    @php
                        $pecah = explode(',', $item->parameter_list)
                    @endphp
                    @foreach ($pecah as $isi)
                    <th width="10%">{{ ucfirst($isi) }}</th>
                    @endforeach
                    <th width="10%">Kategori</th>
                    <th width="25%">Catatan</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($param[$item->category] as $value)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $value->parameter }}</td>
                    @foreach ($pecah as $isi)
                    <td style="vertical-align: middle;" class="text-center"><input type="radio" class="radio-lg catTemuan" name="{{ $value->parameter_id }}" value="{{ $isi }}"></td>
                    @endforeach
                    <td>
                        <select name="{{ $value->parameter_id }}_category" id="{{ $value->parameter_id }}_category" class="form-control" disabled>
                            <option value="minor">minor</option>
                            <option value="major">major</option>
                            <option value="critical">critical</option>
                        </select>
                    </td>
                    <td><input class="form-control" name="{{$value->parameter_id}}_note" autocomplete="false" id="{{$value->parameter_id}}_note" /></td>
                </tr>
                @endforeach

            </tbody>
            </table>
        @endforeach

        <!-- G -->
        <div class="section-title">G. Temuan Audit</div>

        <textarea class="form-control mb-3" rows="3" placeholder="Ringkasan Temuan" id="ringkasan_temuan" name="ringkasan_temuan"></textarea>

        <label>Kategori Temuan</label><br>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori_temuan" id="kategori_temuan1" value="Baik">
        <label class="form-check-label" for="kategori_temuan1">Baik</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori_temuan" id="kategori_temuan2" value="Perlu Perbaikan">
        <label class="form-check-label" for="kategori_temuan2">Perlu Perbaikan</label>
        </div>

        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" class="radio-lg" name="kategori_temuan" id="kategori_temuan3" value="Perlu Tindakan Khusus">
        <label class="form-check-label" for="kategori_temuan3">Perlu Tindakan Khusus</label>
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

        <div id="divFoto" class="my-3" style="display:none;">
            <div class="col-3">
				<div id="foto_lokasi_paths" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
				<input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="foto_lokasi_path" />
            </div>
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

        <div id="divBukti" class="my-3" style="display:none;">
            <div class="col-3">
				<div id="bukti_pendukung_paths" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
				<input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="bukti_pendukung_path" />
            </div>
        </div>


        <!-- I -->
        <div class="section-title">I. Tanda Tangan</div>

        <div class="row">

        {{-- <div class="col-md-4">
        <label>Auditor</label>
        <input class="form-control">
        </div> --}}

        <div class="col-md-4">
        <label>Verifikasi</label>
            <select name="verifikasi" id="verifikasi" class="form-control">
                <option value="">-- Pilih verifikator --</option>
                @foreach ($users as $item)
                <option value="{{ $item->username }}">{{ $item->Name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
        <label>Auditee</label>
            <select name="auditee" id="auditee" class="form-control">
                <option value="">-- Pilih auditee --</option>
                @foreach ($users as $item)
                <option value="{{ $item->username }}">{{ $item->Name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 mt-3">
            <button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
        </div>

        </div>
    </div>
</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script>
    $('.catTemuan').on('change', function() {
        let name = $(this).attr('name');
        let value = $(this).val();
        if (value != 'sesuai' && value != 'baik' && value != 'ada') {
            $(`#${name}_category`).prop('disabled', false);
        }else {
            $(`#${name}_category`).prop('disabled', true);
        }
    })
	var vaultFoto_lokasi_path = new dhx.Vault("foto_lokasi_paths", {
		uploader: {
			target: "upload-receiveinvoice.php"
		},
		filesLimit: 1

	});

	vaultFoto_lokasi_path.events.on("uploadComplete", function(obj) {
		console.log("upload complete")
		$("#foto_lokasi_path").val(obj[0].name);
	});

	vaultFoto_lokasi_path.events.on("uploadBegin", function(obj) {
		console.log("upload begin")
		$("#foto_lokasi_path").val("");

	});
	vaultFoto_lokasi_path.events.on("beforeAdd", function(fileItem) {
		if (vaultFoto_lokasi_path.data.getLength() >= 1) {
			alert('Only can upload 1 file per checkpoint.');
			return false;
		}
	});


	vaultFoto_lokasi_path.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop().toLowerCase();

		var allowed = ["pdf","jpg","jpeg","png","gif","webp"];

		var predicate = allowed.includes(extension);

		if (!predicate) {
			dhtmlx.alert("Hanya boleh mengupload file PDF atau gambar (JPG, PNG, GIF, WEBP)");
		}
		return predicate;
	});
	var vaultBukti_pendukung_path = new dhx.Vault("bukti_pendukung_paths", {
		uploader: {
			target: "upload-receiveinvoice.php"
		},
		filesLimit: 1

	});

	vaultBukti_pendukung_path.events.on("uploadComplete", function(obj) {
		console.log("upload complete")
		$("#bukti_pendukung_path").val(obj[0].name);
	});

	vaultBukti_pendukung_path.events.on("uploadBegin", function(obj) {
		console.log("upload begin")
		$("#bukti_pendukung_path").val("");

	});
	vaultBukti_pendukung_path.events.on("beforeAdd", function(fileItem) {
		if (vaultBukti_pendukung_path.data.getLength() >= 1) {
			alert('Only can upload 1 file per checkpoint.');
			return false;
		}
	});


	vaultBukti_pendukung_path.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop().toLowerCase();

		var allowed = ["pdf","jpg","jpeg","png","gif","webp"];

		var predicate = allowed.includes(extension);

		if (!predicate) {
			dhtmlx.alert("Hanya boleh mengupload file PDF atau gambar (JPG, PNG, GIF, WEBP)");
		}
		return predicate;
	});
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["tanggal_audit"]);
	myCalendar.setDateFormat("%Y-%m-%d");
	myCalendar.attachEvent("onChange", function(date, state) {
		if(state) {
			date = new Date(date);
			let year = date.getFullYear();
			let month = date.getMonth() + 1;
			let datenow = date.getDate();
			date = year + "-" + String(month).padStart(2, '0') + "-" + String(datenow).padStart(2, "0");
			$.ajax({
				url:"partner-warehouse-audit-get-nomor",
				type:"POST",
				data:{_token: "{{ csrf_token() }}",date},
				success:function(res){
                    $('#nomor_audit').val(res);
				}
			})
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

    $('input[name="kategori_temuan"]').on('change', function() {
        let nilai = $(this).val()
        $.ajax({
            url:"partner-warehouse-audit-get-temuan",
            type:"POST",
            data:{_token: "{{ csrf_token() }}", nilai},
            success:function(res) {
                res = JSON.parse(res),
                $('#detail_temuan').find('option').not(':first').remove();
                if (res.data.length > 0) {
                    $('#divTemuan').show();
                    for (let i = 0; i < res.data.length; i++) {
                        const ele = res.data[i];
                        $('#detail_temuan').append($("<option />", {
                            text: ele.area + " - " + ele.parameter,
                            value : ele.area + "||" + ele.parameter,
                        }));
                    }
                }
            }
        })
    })

    $('input[name="foto"]').on('change', function() {
        let nilai = $(this).val()
        if (nilai == 'ada') {
            $('#divFoto').show();
        }else {
            $('#divFoto').hide();
        }
    });

    $('input[name="bukti"]').on('change', function() {
        let nilai = $(this).val()
        if (nilai == 'ada') {
            $('#divBukti').show();
        }else {
            $('#divBukti').hide();
        }
    });
    
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