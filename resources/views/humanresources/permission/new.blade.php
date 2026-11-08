@extends('layout.mainlayout-hrd')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-calendar me-2"></i>Pengajuan Izin & Cuti Baru (New Request)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Employee Portal</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">New Permission & Leave</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-arrow-left me-1"></i> Ke Portal Absensi
            </a>
            <a href="{{ url('/permission-all') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-list me-1"></i> Rekap Semua Izin
            </a>
            <a href="{{ url('/permission-approval') }}" class="btn btn-warning btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-check-square me-1"></i> Menu Approval
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    @php
        $Saya = Auth::user()->username ?? '';
        $LSP = Auth::user()->Location ?? 'HO';
        $optJenis = $jenisIzin ?? \Illuminate\Support\Facades\DB::table("jns_izin")->get();
        $taskNo = $taskId ?? ("IZIN/$LSP-" . date('ymdhis') . '/' . strtoupper(substr(md5($Saya . rand(999, 9999)), -5)));
        $holidayListStr = $listLibur ?? '';
    @endphp

    <!-- Card Form Pengajuan -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-edit-3 text-primary me-2"></i>Formulir Permohonan Izin / Cuti Karyawan</h6>
                <small class="text-muted">No. Tiket: <strong>{{ $taskNo }}</strong> (Terintegrasi otomatis dengan Attendance & Approval Engine)</small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                <i class="fe fe-shield me-1"></i>Sync Active
            </span>
        </div>
        <div class="card-body p-4" id="divBLade">
            <input type="hidden" id="Task_ID" value="{{ $taskNo }}" readonly="readonly"/>
            <input type="hidden" id="izin_tgl_pengajuan" value="{{ date('Y-m-d') }}"/>	
            <input type="hidden" id="izin_status" value="3"/>
            <input type="hidden" id="File_Path" value=""/>

            <div class="row g-3">
                <!-- Data Karyawan -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-user me-1"></i>Cari Karyawan (NIK / Nama) <span class="text-danger">*</span></label>
                    <input type="text" id="Name" name="Name" class="form-control rounded-3" placeholder="Ketik nama karyawan untuk mencari..." required />
                    <input type="hidden" id="NIK" name="NIK" />
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-briefcase me-1"></i>Kategori Karyawan</label>
                    <input type="text" id="Employee_Category" class="form-control rounded-3 bg-light" readonly />
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-pie-chart me-1"></i>Sisa Cuti Tahunan</label>
                    <input type="text" id="Sisa_Cuti" class="form-control rounded-3 bg-light fw-bold text-primary" readonly />
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-user-check me-1"></i>Nama Lengkap Terpilih</label>
                    <input type="text" id="Employee_Name" class="form-control rounded-3 bg-light" readonly />
                </div>

                <!-- Jenis & Kategori Izin -->
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-tag me-1"></i>Grup Jenis Izin <span class="text-danger">*</span></label>
                    <select id='izin_jenis_id' class="form-select rounded-3" onchange='loadkategori();'>
                        @foreach ($optJenis as $fm)
                            <option value='{{ $fm->izin_jenis_id }}'>{{ $fm->izin_jenis_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-list me-1"></i>Kategori Izin / Cuti <span class="text-danger">*</span></label>
                    <div id="DivKategori">
                        <select id="kat_izin_id" class="form-select rounded-3">
                            <option value="">-- Pilih Kategori --</option>
                        </select>
                    </div>
                </div>

                <!-- Periode Tanggal & Jam -->
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-calendar me-1"></i>Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" id="izin_tgl" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required />
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-calendar me-1"></i>Tanggal Akhir <span class="text-danger">*</span></label>
                    <input type="date" id="izin_tgl2" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required />
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-clock me-1"></i>Info Jam (Opsional)</label>
                    <input type="text" id="Informasi_Waktu" class="form-control rounded-3" placeholder="Contoh: 15:30 atau 08:00 - 12:00" />
                    <small class="text-muted" style="font-size: 11px;">Khusus izin parsial / terlambat / pulang cepat</small>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-map-pin me-1"></i>Lokasi / Keterangan Lain</label>
                    <input type="text" id="izin_ket_lain" class="form-control rounded-3" placeholder="Contoh: HO / Mess / Lapangan" />
                </div>

                <!-- Catatan / Alasan -->
                <div class="col-12">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-message-square me-1"></i>Alasan / Catatan Izin <span class="text-danger">*</span></label>
                    <textarea id="izin_catatan" class="form-control rounded-3" rows="3" placeholder="Tuliskan keterangan detail keperluan izin atau cuti..." required></textarea>
                </div>

                <!-- Unggah Dokumen -->
                <div class="col-12">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-paperclip me-1"></i>Dokumen Lampiran (Surat Dokter / Bukti Pendukung)</label>
                    <div id="vault" class="rounded-3 border p-2" style="height: 180px; width: 100%; background: #fafafa;"></div>
                    <small class="text-muted" style="font-size: 11px;">Maksimal 1 file. Format: PDF, PNG, JPG, JPEG.</small>
                </div>

                <!-- Tombol Aksi -->
                <div class="col-12 text-end pt-2">
                    <button type="button" id="Processdata" class="btn btn-primary rounded-pill px-5 fw-semibold shadow-sm">
                        <i class="fe fe-send me-1"></i> Kirim Permohonan Izin
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
    setTimeout(function(){
        loadkategori();
    }, 500);

    function loadkategori(){
        var pxid = $("#izin_jenis_id").val();
        if (pxid != ''){
            LoadM('DivKategori', 'load-kategori-izin?ID=' + pxid);
        }
    }

    var vault = new dhx.Vault("vault", {
        uploader: {
            target: "upload-permission.php"
        },
        filesLimit: 1
    });
        
    vault.events.on("uploadComplete", function(obj) {
        if (obj && obj[0]) {
            $("#File_Path").val(obj[0].name);
        }
    });

    vault.events.on("uploadBegin", function() {
        $("#File_Path").val("");
    });

    vault.events.on("beforeAdd", function() {
        if (vault.data.getLength() >= 1) {
            alert('Hanya diperbolehkan mengunggah 1 dokumen.');
            return false;
        }
    });

    $("#Name").coolautosuggest({
        url: "search-employee?chars=",
        showThumbnail: false,
        showDescription: true,
        idField: $("#NIK"),
        width: 450,
        minChars: 1,
        onSelected: function(result){
            if (result != null) {
                var xx = result.thumbnail || '';
                var exp = xx.split("|");
                $("#NIK").val(result.id); 
                $("#Employee_Category").val(exp[0] || 'STAFF'); 
                $("#Sisa_Cuti").val(exp[1] || '0'); 
                $("#Employee_Name").val(result.data); 
            }
        }
    });

    $("#Processdata").click(function() {
        var nik = $("#NIK").val();
        var tgl1 = $("#izin_tgl").val();
        var tgl2 = $("#izin_tgl2").val();
        var reason = $("#izin_catatan").val();
        var kat = $("#kat_izin_id").val();

        if (!nik) {
            alert("Harap pilih karyawan terlebih dahulu!");
            $("#Name").focus();
            return;
        }

        if (!reason) {
            alert("Harap isi alasan / catatan pengajuan izin!");
            $("#izin_catatan").focus();
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fe fe-loader me-1"></i> Menyimpan...');

        var postData = {
            _token: "{{ csrf_token() }}",
            Task_ID: $("#Task_ID").val(),
            NIK: nik,
            Employee_Name: $("#Employee_Name").val(),
            Employee_Category: $("#Employee_Category").val(),
            izin_jenis_id: $("#izin_jenis_id").val(),
            kat_izin_id: kat,
            izin_tgl: tgl1,
            izin_tgl2: tgl2,
            Informasi_Waktu: $("#Informasi_Waktu").val(),
            izin_ket_lain: $("#izin_ket_lain").val(),
            izin_catatan: reason,
            File_Path: $("#File_Path").val()
        };

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "permission-new-submit",
            type: "POST",
            data: postData,
            success: function(data) {
                btn.prop('disabled', false).html('<i class="fe fe-send me-1"></i> Kirim Permohonan Izin');
                if (data === 'success') {
                    alert('Permohonan izin berhasil diajukan dengan Task ID: ' + $("#Task_ID").val());
                    location.href = 'permission-new';
                } else {
                    alert(data);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fe fe-send me-1"></i> Kirim Permohonan Izin');
                var msg = xhr.responseText || 'Terjadi kesalahan sistem.';
                alert(msg);
            }
        });
    });
</script>
@endsection
