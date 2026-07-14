<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("employment")->where("NIK",$_GET['ID'])->first();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'employment'");
	$jumlah = count($result);
	if ($jumlah > 0) {
		echo "<script>";
		
		foreach ($result as $row) {
			
			$kolom = $row->column_name;
			
				
				if ($datahasil->$kolom == '0000-00-00')
				{
					echo "$('#$kolom').val('');\n";
				
				}else{
					
					$dataForm = $datahasil->$kolom;
					
					echo "$('#$kolom').val('$dataForm');\n";
					
				}
			

		}
	   echo "</script>";

	}
	
?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EMPLOYEE PROMOTION</div>
    <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Form Perubahan Status</h5>
    </div>
    <div class="card-body">
 
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
 
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
 
      <form action="{{ route('perubahan-status.store') }}" method="POST">
        @csrf
 
        <!-- IDENTITAS KARYAWAN -->
        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="nik">NIK</label>
            <select name="NIK" id="nik" class="form-control" required onchange="loadEmployee(this.value)">
              <option value="">-- Pilih Karyawan --</option>
              @foreach ($employees as $emp)
                <option value="{{ $emp->NIK }}"
                  data-nama="{{ $emp->Name }}"
                  data-join="{{ $emp->Join_Date }}"
                  data-jabatan="{{ $emp->Jabatan }}"
                  data-divisi="{{ $emp->Divisi }}"
                  data-departemen="{{ $emp->Departemen }}"
                  data-grade="{{ $emp->Grade }}"
                  data-lokasi="{{ $emp->Lokasi_Kerja }}"
                  data-status="{{ $emp->Status_Kekaryawanan }}"
                  data-marital="{{ $emp->Marital_Status }}"
                  data-salary="{{ $emp->Salary }}"
                  data-benefit="{{ $emp->Benefit }}"
                  {{ old('NIK') == $emp->NIK ? 'selected' : '' }}>
                  {{ $emp->NIK }} - {{ $emp->Name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4">
            <label for="nama">Nama</label>
            <input type="text" id="nama" class="form-control" readonly>
          </div>
          <div class="form-group col-md-4">
            <label for="tanggal_join">Tanggal Join</label>
            <input type="text" id="tanggal_join" class="form-control" readonly>
          </div>
        </div>
 
        <hr>
 
        <!-- TABEL SAAT INI VS USULAN -->
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="thead-light">
              <tr>
                <th style="width:16%">Identitas</th>
                <th style="width:42%">Saat ini</th>
                <th style="width:42%">Usulan</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="font-weight-bold">Jabatan</td>
                <td><input type="text" id="current_jabatan" class="form-control" readonly></td>
                <td><input type="text" name="Proposed_Jabatan" class="form-control" value="{{ old('Proposed_Jabatan') }}"></td>
              </tr>
              <tr>
                <td class="font-weight-bold">Divisi</td>
                <td><input type="text" id="current_divisi" class="form-control" readonly></td>
                <td><input type="text" name="Proposed_Divisi" class="form-control" value="{{ old('Proposed_Divisi') }}"></td>
              </tr>
              <tr>
                <td class="font-weight-bold">Departemen</td>
                <td><input type="text" id="current_departemen" class="form-control" readonly></td>
                <td><input type="text" name="Proposed_Departemen" class="form-control" value="{{ old('Proposed_Departemen') }}"></td>
              </tr>
              <tr>
                <td class="font-weight-bold">Grade</td>
                <td><input type="text" id="current_grade" class="form-control" readonly></td>
                <td><input type="text" name="Proposed_Grade" class="form-control" value="{{ old('Proposed_Grade') }}"></td>
              </tr>
              <tr>
                <td class="font-weight-bold">Lokasi Kerja</td>
                <td><input type="text" id="current_lokasi" class="form-control" readonly></td>
                <td><input type="text" name="Proposed_Lokasi_Kerja" class="form-control" value="{{ old('Proposed_Lokasi_Kerja') }}"></td>
              </tr>
              <tr>
                <td class="font-weight-bold">Status Kekaryawanan</td>
                <td><input type="text" id="current_status" class="form-control" readonly></td>
                <td>
                  <select name="Proposed_Status_Kekaryawanan" class="form-control">
                    <option value="">-- Pilih --</option>
                    <option value="Permanent" {{ old('Proposed_Status_Kekaryawanan')=='Permanent'?'selected':'' }}>Permanent</option>
                    <option value="Kontrak" {{ old('Proposed_Status_Kekaryawanan')=='Kontrak'?'selected':'' }}>Kontrak</option>
                    <option value="Probation" {{ old('Proposed_Status_Kekaryawanan')=='Probation'?'selected':'' }}>Probation</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td class="font-weight-bold">Status Perkawinan<br>(dan jumlah tanggungan)</td>
                <td><input type="text" id="current_marital" class="form-control" readonly></td>
                <td>
                  <select name="Proposed_Marital_Status" class="form-control">
                    <option value="">-- Pilih --</option>
                    <option value="TK" {{ old('Proposed_Marital_Status')=='TK'?'selected':'' }}>Tidak Kawin (TK)</option>
                    <option value="TK/0" {{ old('Proposed_Marital_Status')=='TK/0'?'selected':'' }}>TK/0</option>
                    <option value="TK/1" {{ old('Proposed_Marital_Status')=='TK/1'?'selected':'' }}>TK/1</option>
                    <option value="TK/2" {{ old('Proposed_Marital_Status')=='TK/2'?'selected':'' }}>TK/2</option>
                    <option value="TK/3" {{ old('Proposed_Marital_Status')=='TK/3'?'selected':'' }}>TK/3</option>
                    <option value="K" {{ old('Proposed_Marital_Status')=='K'?'selected':'' }}>Kawin (K)</option>
                    <option value="K/0" {{ old('Proposed_Marital_Status')=='K/0'?'selected':'' }}>K/0</option>
                    <option value="K/1" {{ old('Proposed_Marital_Status')=='K/1'?'selected':'' }}>K/1</option>
                    <option value="K/2" {{ old('Proposed_Marital_Status')=='K/2'?'selected':'' }}>K/2</option>
                    <option value="K/3" {{ old('Proposed_Marital_Status')=='K/3'?'selected':'' }}>K/3</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td class="font-weight-bold">Salary</td>
                <td>
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input type="text" id="current_salary" class="form-control" readonly>
                  </div>
                </td>
                <td>
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input type="number" name="Proposed_Salary" class="form-control" min="0" step="1000" value="{{ old('Proposed_Salary') }}">
                  </div>
                </td>
              </tr>
              <tr>
                <td class="font-weight-bold">Benefit</td>
                <td><input type="text" id="current_benefit" class="form-control" readonly></td>
                <td><input type="text" name="Proposed_Benefit" class="form-control" value="{{ old('Proposed_Benefit') }}"></td>
              </tr>
              <tr>
                <td class="font-weight-bold">Deskripsi</td>
                <td colspan="2">
                  <textarea name="Deskripsi" class="form-control" rows="4">{{ old('Deskripsi') }}</textarea>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
 
        <div class="form-group">
          <label for="effective_date">Perubahan status ini berlaku sejak tanggal</label>
          <input type="date" name="Effective_Date" id="effective_date" class="form-control" style="max-width:250px;" value="{{ old('Effective_Date') }}" required>
        </div>
 
        <hr>
 
        <!-- PIC -->
        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="proposed_by">Diusulkan oleh</label>
            <select name="Proposed_By" id="proposed_by" class="form-control" required>
              <option value="">-- Pilih --</option>
              @foreach ($approvers as $a)
                <option value="{{ $a->NIK }}" {{ old('Proposed_By')==$a->NIK?'selected':'' }}>{{ $a->Name }} - {{ $a->Jabatan }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4">
            <label for="approved_by">Disetujui oleh</label>
            <select name="Approved_By" id="approved_by" class="form-control" required>
              <option value="">-- Pilih --</option>
              @foreach ($approvers as $a)
                <option value="{{ $a->NIK }}" {{ old('Approved_By')==$a->NIK?'selected':'' }}>{{ $a->Name }} - {{ $a->Jabatan }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4">
            <label for="verified_by">Diverifikasi oleh</label>
            <select name="Verified_By" id="verified_by" class="form-control" required>
              <option value="">-- Pilih --</option>
              @foreach ($approvers as $a)
                <option value="{{ $a->NIK }}" {{ old('Verified_By')==$a->NIK?'selected':'' }}>{{ $a->Name }} - {{ $a->Jabatan }}</option>
              @endforeach
            </select>
          </div>
        </div>
 
        <div class="text-right mt-3">
          <a href="{{ route('perubahan-status.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
 
      </form>
    </div>
  </div>
</div>
<div id="vault" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
			
 </div>
 </div>
    <div id="divtom" align="center"><br><br>
        <input type="button" id="SimpanNewPEg" NAME="SimpanNewPEg" value="Process" />                
    </div>		
<script>
var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-mutation.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#Mutation_File").val(obj[0].name);	
});
vault.events.on("uploadBegin", function(obj) {
	$("#Mutation_File").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});
   
var PODMGT_Cal;
PODMGT_Cal = new dhtmlXCalendarObject(["Start_Date","Birth_Date","Date_in","Resigned_Date"]);
PODMGT_Cal.setDateFormat("%Y-%m-%d");

			
var winUpload;
var WUpload;
function uploadGambar(idfoto) {
   winUpload = new dhtmlXWindows();
    winUpload.attachViewportTo("divtom");
    winUpload.enableAutoViewport(true);
    winUpload.setImagePath(PathDHTMLX + "imgs/");
    WUpload = winUpload.createWindow("WUpload", 0, 0, 450, 360);
    WUpload.setText("Upload Gambar");
    WUpload.attachURL('upload-foto-pegawai?idfoto='+idfoto, true);
	WUpload.button("minmax1").hide();
    WUpload.button("park").hide();
	WUpload.centerOnScreen();
	WUpload.allowMove();	
	WUpload.progressOn();

}
	
function cekFoto(pathf){
	if (pathf!='') {
		window.open ("./doc_recruitment/photo/"+pathf,"mywindow");
	}
}

function getAllValues() {
	 
		
    	var inputValues = [];
		$('#DataEmpDetail input,select,textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
			else if (type != "button" || type != "submit") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			 } else if ($this.is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
		
	
}
function loadEmployee(nik) {
  const select = document.getElementById('nik');
  const opt = select.options[select.selectedIndex];
 
  if (!nik) {
    document.querySelectorAll('input[readonly]').forEach(el => el.value = '');
    return;
  }
 
  document.getElementById('nama').value          = opt.dataset.nama || '';
  document.getElementById('tanggal_join').value  = opt.dataset.join || '';
  document.getElementById('current_jabatan').value   = opt.dataset.jabatan || '';
  document.getElementById('current_divisi').value    = opt.dataset.divisi || '';
  document.getElementById('current_departemen').value= opt.dataset.departemen || '';
  document.getElementById('current_grade').value     = opt.dataset.grade || '';
  document.getElementById('current_lokasi').value    = opt.dataset.lokasi || '';
  document.getElementById('current_status').value    = opt.dataset.status || '';
  document.getElementById('current_marital').value   = opt.dataset.marital || '';
  document.getElementById('current_salary').value    = opt.dataset.salary || '';
  document.getElementById('current_benefit').value   = opt.dataset.benefit || '';
}


$('#SimpanNewPEg').click(function(){

var	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "employee-promotion-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Employee Updated!');

					location.href="employee-promotion";
			}else{
				alert('Employee Failed to Update!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})

</script>
