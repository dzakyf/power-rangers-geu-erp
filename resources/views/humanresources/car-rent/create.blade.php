@csrf
<div class="main-content-header">
    <h1>PEMINJAMAN MOBIL OPERASIONAL</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
        <li class="breadcrumb-item active"><span class="active">Form Peminjaman Mobil</span></li>
    </ol>
</div>

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold p-3">
        <h5 class="m-0" style="color:azure"><i class="fas fa-car mr-2"></i>Formulir Peminjaman Mobil Operasional</h5>
    </div>
    <div class="card-body">
        <form id="formCarRent" method="POST" action="{{ url('hrd-car-rent-new-submit') }}">
            <div class="FormInput_Form_Content">
                <!-- Identitas Peminjam -->
                <h6 style="font-weight:bold; margin-bottom:10px;">Identitas Peminjam</h6>
                <div class="row">
                    <div class="FormInput_left col-md-6">Nomor Induk Karyawan (NIK)</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="User_NIK" size="35" value="{{ $NIK }}" readonly />
                    </div>
                    <div class="FormInput_left col-md-6">Nama Lengkap</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="User_Name" size="35" readonly />
                    </div>
                    <div class="FormInput_left col-md-6">Jabatan</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="User_Jabatan" size="35" readonly />
                    </div>
                    <div class="FormInput_left col-md-6">Departemen</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="User_Department" size="35" readonly />
                    </div>
                    <div class="FormInput_left col-md-6">Site / Office</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="Site_Office" size="35" />
                    </div>
                    <div class="FormInput_left col-md-6">Nomor Telepon</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="Phone_Number" size="35" placeholder="08xxxxxxxxxx" />
                    </div>
                    <div class="FormInput_left col-md-6">Email Address</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="Email" size="35" />
                    </div>
                </div>

                <!-- Informasi SIM -->
                <h6 style="font-weight:bold; margin:20px 0 10px 0;">Informasi SIM</h6>
                <div class="row">
                    <div class="FormInput_left col-md-12">Link SIM (Google Drive / URL)</div>
                    <div class="FormInput_right col-md-12">
                        <input type="text" id="Sim_Link" size="60" placeholder="https://drive.google.com/open?id=..." />
                    </div>
                </div>

                <!-- Jenis Perjalanan: SPPD vs Non-SPPD -->
                <h6 style="font-weight:bold; margin:20px 0 10px 0;">Jenis Perjalanan</h6>
                <div class="row">
                    <div class="FormInput_left col-md-6">Tipe Peminjaman</div>
                    <div class="FormInput_right col-md-6">
                        <label class="mr-3"><input type="radio" name="Trip_Type" id="Trip_Type_NON" value="NON_SPPD" {{ ($selectedSppd ?? '') ? '' : 'checked' }} /> Non-SPPD (Operasional)</label>
                        <label><input type="radio" name="Trip_Type" id="Trip_Type_SPPD" value="SPPD" {{ ($selectedSppd ?? '') ? 'checked' : '' }} /> SPPD (Dinas)</label>
                    </div>
                    <div class="FormInput_left col-md-6" id="SPPD_Label" style="display:none;">Nomor SPPD (harus ACCEPTED)</div>
                    <div class="FormInput_right col-md-6" id="SPPD_Field" style="display:none;">
                        <select id="SPPD_ID" class="form-control">
                            <option value="">-- Pilih SPPD --</option>
                            @foreach($sppdList ?? [] as $s)
                                <option value="{{ $s->ID }}" data-dest="{{ $s->Destination }}" data-date="{{ $s->Schedule_Date }}" data-no="{{ $s->SPPD_No }}" {{ (string)($selectedSppd ?? '') === (string)$s->ID ? 'selected' : '' }}>{{ $s->SPPD_No }} — {{ $s->Destination }} ({{ $s->Schedule_Date }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Keperluan & Tujuan -->
                <h6 style="font-weight:bold; margin:20px 0 10px 0;">Keperluan & Tujuan Penggunaan</h6>
                <div class="row">
                    <div class="FormInput_left col-md-6">Keperluan</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="Keperluan" size="35" value="Peminjaman Mobil Operasional" readonly />
                    </div>
                    <div class="FormInput_left col-md-6">Tujuan Penggunaan</div>
                    <div class="FormInput_right col-md-6">
                        <textarea id="Tujuan_Penggunaan" cols="80" rows="2"></textarea>
                    </div>
                </div>

                <!-- Rincian Peminjaman -->
                <h6 style="font-weight:bold; margin:20px 0 10px 0;">Rincian Peminjaman</h6>
                <div class="row">
                    <div class="FormInput_left col-md-6">Tanggal Peminjaman</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="Tanggal_Peminjaman" size="15" readonly class="bg-white" />
                    </div>
                    <div class="FormInput_left col-md-6">Jam Peminjaman</div>
                    <div class="FormInput_right col-md-6">
                        <select id="Jam_Peminjaman" class="form-control">
                            @for ($i = 0; $i < 24; $i++)
                                @for ($j = 0; $j < 60; $j += 30)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            @endfor
                        </select>
                    </div>
                    <div class="FormInput_left col-md-6">Tanggal Pengembalian</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="Tanggal_Pengembalian" size="15" class="bg-white" />
                    </div>
                    <div class="FormInput_left col-md-6">Jam Pengembalian</div>
                    <div class="FormInput_right col-md-6">
                        <select id="Jam_Pengembalian" class="form-control">
                            @for ($i = 0; $i < 24; $i++)
                                @for ($j = 0; $j < 60; $j += 30)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Jenis Mobil -->
                <h6 style="font-weight:bold; margin:20px 0 10px 0;">Jenis Mobil</h6>
                <div class="row">
                    <div class="FormInput_left col-md-6">Jenis Mobil yang Dipinjam</div>
                    <div class="FormInput_right col-md-6">
                        <select id="Jenis_Mobil" class="form-control">
                            <option value="">-- Pilih Jenis Mobil --</option>
                            @foreach(($fleetList ?? ['Rush','Avanza','Innova','Pajero','Alphard','Hiace','Other']) as $m)
                                <option value="{{ $m }}">{{ ($m == 'Other') ? 'Lainnya' : $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Kerusakan / Denda -->
                <h6 style="font-weight:bold; margin:20px 0 10px 0;">Kerusakan & Denda (jika ada)</h6>
                <div class="row">
                    <div class="FormInput_left col-md-6">Kerusakan / Perbaikan / Denda</div>
                    <div class="FormInput_right col-md-6">
                        <textarea id="Kerusakan_Denda" cols="80" rows="2" placeholder="Jika tidak ada, dikosongkan"></textarea>
                    </div>
                    <div class="FormInput_left col-md-6">Bukti Pembayaran Denda</div>
                    <div class="FormInput_right col-md-6">
                        <input type="text" id="Bukti_Pembayaran" size="35" placeholder="Link / nomor referensi bukti pembayaran" />
                    </div>
                </div>

                <div id="divtom" class="mt-3 text-right">
                    <input type="submit" id="SubmitCarRent" class="btn btn-success px-4" name="SubmitCarRent" value="Submit Formulir Peminjaman" />
                    <button type="button" class="btn btn-secondary px-3 ml-2" onclick="window.history.back()">Batal</button>
                </div>
                <div id="WinCOA"></div>
            </div>
        </form>
    </div>
</div>

<div id="DivProcess"></div>

<script>
    $(function() {
        var crCal = new dhtmlXCalendarObject(["Tanggal_Peminjaman", "Tanggal_Pengembalian"]);
        crCal.setDateFormat("%Y-%m-%d");

        // Isi tanggal peminjaman otomatis dengan hari ini
        $('#Tanggal_Peminjaman').val('{{ date("Y-m-d") }}');

        // Isi nama, jabatan, departemen dari session user jika tersedia
        @if(Auth::check())
            @if(old('User_Name'))
                $('#User_Name').val('{{ old('User_Name') }}');
            @endif
            @if(old('User_Jabatan'))
                $('#User_Jabatan').val('{{ old('User_Jabatan') }}');
            @endif
            @if(old('User_Department'))
                $('#User_Department').val('{{ old('User_Department') }}');
            @endif
            @if(old('Site_Office'))
                $('#Site_Office').val('{{ old('Site_Office') }}');
            @endif
        @endif

        function toggleSppd() {
            var isSppd = $('input[name="Trip_Type"]:checked').val() == 'SPPD';
            $('#SPPD_Label, #SPPD_Field').toggle(isSppd);
        }
        $('input[name="Trip_Type"]').on('change', toggleSppd);
        $('#SPPD_ID').on('change', function() {
            var opt = $(this).find('option:selected');
            if (opt.val() && !$('#Tujuan_Penggunaan').val()) {
                $('#Tujuan_Penggunaan').val(opt.data('no') + ' - ' + opt.data('dest'));
            }
            if (opt.val() && opt.data('date')) { $('#Tanggal_Peminjaman').val(opt.data('date')); }
        });
        toggleSppd();
    });

    $('#SubmitCarRent').on('click', function(e) {
        e.preventDefault();

        // Validasi wajib
        var tripType = $('input[name="Trip_Type"]:checked').val() || 'NON_SPPD';
        var sppdId = $('#SPPD_ID').val() || '';
        var tanggalPeminjaman = $('#Tanggal_Peminjaman').val();
        var tanggalPengembalian = $('#Tanggal_Pengembalian').val();
        var tujuan = $('#Tujuan_Penggunaan').val();
        var jenisMobil = $('#Jenis_Mobil').val();

        if (tripType == 'SPPD' && sppdId == '') {
            alert('SPPD wajib dipilih untuk tipe SPPD!');
            $('#SPPD_ID').focus();
            return false;
        }

        if (tanggalPeminjaman == '') {
            alert('Tanggal Peminjaman harus diisi!');
            $('#Tanggal_Peminjaman').focus();
            return false;
        }
        if (tanggalPengembalian == '') {
            alert('Tanggal Pengembalian harus diisi!');
            $('#Tanggal_Pengembalian').focus();
            return false;
        }
        if (tujuan == '') {
            alert('Tujuan Penggunaan harus diisi!');
            $('#Tujuan_Penggunaan').focus();
            return false;
        }
        if (jenisMobil == '') {
            alert('Jenis Mobil harus dipilih!');
            $('#Jenis_Mobil').focus();
            return false;
        }

        $.blockUI();

        var values = ['Trip_Type=' + encodeURIComponent(tripType)];
        if (tripType == 'SPPD') { values.push('SPPD_ID=' + encodeURIComponent(sppdId)); }
        $('#formCarRent input, #formCarRent select, #formCarRent textarea').each(function() {
            var id = $(this).attr('id');
            if (id == 'SPPD_ID') return; // sudah dikirim eksplisit di atas
            if ($(this).attr('name') == 'Trip_Type') return; // radio Trip_Type sudah dikirim eksplisit
            var type = $(this).attr('type');
            if ((type == 'checkbox' || type == 'radio') && this.checked) {
                values.push($(this).attr('id') + '=' + $(this).val());
            } else if (type != 'button' && type != 'submit') {
                values.push($(this).attr('id') + '=' + $(this).val());
            }
        });

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
            },
            url: 'hrd-car-rent-new-submit',
            type: 'POST',
            data: values.join('&'),
            success: function(data) {
                if (data.status == 'success') {
                    alert('Formulir Peminjaman Mobil Berhasil Disubmit!');
                    $.unblockUI();
                    setTimeout(function() {
                        window.location.href = 'hrd-car-rent-index';
                    }, 500);
                } else {
                    alert(data.message || 'Terjadi kesalahan!');
                    $.unblockUI();
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                alert('Error: ' + (xhr.responseText || errorThrown));
                $.unblockUI();
            }
        });
    });
</script>
