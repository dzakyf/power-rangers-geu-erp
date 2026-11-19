@csrf
<div class="mt-3">
    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fas fa-car me-2"></i>Formulir Peminjaman Mobil Operasional</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#CARRENT_DTL').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <form id="formCarRent" method="POST" action="{{ url('hrd-car-rent-new-submit') }}">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Identitas Peminjam</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="User_NIK" class="form-label small fw-semibold">Nomor Induk Karyawan (NIK)</label>
                        <input type="text" id="User_NIK" class="form-control form-control-sm bg-light" value="{{ $NIK }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="User_Name" class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" id="User_Name" class="form-control form-control-sm bg-light" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="User_Jabatan" class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" id="User_Jabatan" class="form-control form-control-sm bg-light" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="User_Department" class="form-label small fw-semibold">Departemen</label>
                        <input type="text" id="User_Department" class="form-control form-control-sm bg-light" readonly />
                    </div>
                    <div class="col-md-4">
                        <label for="Site_Office" class="form-label small fw-semibold">Site / Office</label>
                        <input type="text" id="Site_Office" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-4">
                        <label for="Phone_Number" class="form-label small fw-semibold">Nomor Telepon</label>
                        <input type="text" id="Phone_Number" class="form-control form-control-sm" placeholder="08xxxxxxxxxx" />
                    </div>
                    <div class="col-md-4">
                        <label for="Email" class="form-label small fw-semibold">Email Address</label>
                        <input type="text" id="Email" class="form-control form-control-sm" />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi SIM &amp; Jenis Perjalanan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="Sim_Link" class="form-label small fw-semibold">Link SIM (Google Drive / URL)</label>
                        <input type="text" id="Sim_Link" class="form-control form-control-sm" placeholder="https://drive.google.com/open?id=..." />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold d-block">Tipe Peminjaman</label>
                        <div class="d-flex align-items-center gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Trip_Type" id="Trip_Type_NON" value="NON_SPPD" {{ ($selectedSppd ?? '') ? '' : 'checked' }} />
                                <label class="form-check-label small" for="Trip_Type_NON">Non-SPPD (Operasional)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Trip_Type" id="Trip_Type_SPPD" value="SPPD" {{ ($selectedSppd ?? '') ? 'checked' : '' }} />
                                <label class="form-check-label small" for="Trip_Type_SPPD">SPPD (Dinas)</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" id="SPPD_Field" style="display:none;">
                        <label for="SPPD_ID" id="SPPD_Label" class="form-label small fw-semibold">Nomor SPPD (harus ACCEPTED)</label>
                        <select id="SPPD_ID" class="form-select form-select-sm">
                            <option value="">-- Pilih SPPD --</option>
                            @foreach($sppdList ?? [] as $s)
                                <option value="{{ $s->ID }}" data-dest="{{ $s->Destination }}" data-date="{{ $s->Schedule_Date }}" data-no="{{ $s->SPPD_No }}" {{ (string)($selectedSppd ?? '') === (string)$s->ID ? 'selected' : '' }}>{{ $s->SPPD_No }} — {{ $s->Destination }} ({{ $s->Schedule_Date }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Keperluan &amp; Rincian Peminjaman</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="Keperluan" class="form-label small fw-semibold">Keperluan</label>
                        <input type="text" id="Keperluan" class="form-control form-control-sm bg-light" value="Peminjaman Mobil Operasional" readonly />
                    </div>
                    <div class="col-md-6">
                        <label for="Jenis_Mobil" class="form-label small fw-semibold">Jenis Mobil yang Dipinjam <span class="text-danger">*</span></label>
                        <select id="Jenis_Mobil" class="form-select form-select-sm">
                            <option value="">-- Pilih Jenis Mobil --</option>
                            @foreach(($fleetList ?? ['Rush','Avanza','Innova','Pajero','Alphard','Hiace','Other']) as $m)
                                <option value="{{ $m }}">{{ ($m == 'Other') ? 'Lainnya' : $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label for="Tujuan_Penggunaan" class="form-label small fw-semibold">Tujuan Penggunaan <span class="text-danger">*</span></label>
                        <textarea id="Tujuan_Penggunaan" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label for="Tanggal_Peminjaman" class="form-label small fw-semibold">Tanggal Peminjaman <span class="text-danger">*</span></label>
                        <input type="text" id="Tanggal_Peminjaman" class="form-control form-control-sm bg-white" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="Jam_Peminjaman" class="form-label small fw-semibold">Jam Peminjaman</label>
                        <select id="Jam_Peminjaman" class="form-select form-select-sm">
                            @for ($i = 0; $i < 24; $i++)
                                @for ($j = 0; $j < 60; $j += 30)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="Tanggal_Pengembalian" class="form-label small fw-semibold">Tanggal Pengembalian <span class="text-danger">*</span></label>
                        <input type="text" id="Tanggal_Pengembalian" class="form-control form-control-sm bg-white" />
                    </div>
                    <div class="col-md-3">
                        <label for="Jam_Pengembalian" class="form-label small fw-semibold">Jam Pengembalian</label>
                        <select id="Jam_Pengembalian" class="form-select form-select-sm">
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

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Kerusakan &amp; Denda (jika ada)</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="Kerusakan_Denda" class="form-label small fw-semibold">Kerusakan / Perbaikan / Denda</label>
                        <textarea id="Kerusakan_Denda" class="form-control form-control-sm" rows="2" placeholder="Jika tidak ada, dikosongkan"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="Bukti_Pembayaran" class="form-label small fw-semibold">Bukti Pembayaran Denda</label>
                        <input type="text" id="Bukti_Pembayaran" class="form-control form-control-sm" placeholder="Link / nomor referensi bukti pembayaran" />
                    </div>
                </div>

                <div id="divtom" class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="$('#CARRENT_DTL').html('')">
                        <i class="fa fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" id="SubmitCarRent" class="btn btn-success btn-sm" name="SubmitCarRent">
                        <i class="fa fa-check me-1"></i> Submit Formulir Peminjaman
                    </button>
                </div>
                <div id="WinCOA"></div>
            </form>
        </div>
    </div>
</div>

<div id="DivProcess"></div>

<script>
    $(function() {
        if (typeof dhtmlXCalendarObject !== 'undefined') {
            var crCal = new dhtmlXCalendarObject(["Tanggal_Peminjaman", "Tanggal_Pengembalian"]);
            crCal.setDateFormat("%Y-%m-%d");
        }

        $('#Tanggal_Peminjaman').val('{{ date("Y-m-d") }}');

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

        if ($.blockUI) $.blockUI();

        var values = ['Trip_Type=' + encodeURIComponent(tripType)];
        if (tripType == 'SPPD') { values.push('SPPD_ID=' + encodeURIComponent(sppdId)); }
        $('#formCarRent input, #formCarRent select, #formCarRent textarea').each(function() {
            var id = $(this).attr('id');
            if (!id || id == 'SPPD_ID') return;
            if ($(this).attr('name') == 'Trip_Type') return;
            var type = $(this).attr('type');
            if ((type == 'checkbox' || type == 'radio') && this.checked) {
                values.push(id + '=' + encodeURIComponent($(this).val()));
            } else if (type != 'button' && type != 'submit') {
                values.push(id + '=' + encodeURIComponent($(this).val()));
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
                    if ($.unblockUI) $.unblockUI();
                    setTimeout(function() {
                        window.location.href = 'hrd-car-rent-index';
                    }, 500);
                } else {
                    alert(data.message || 'Terjadi kesalahan!');
                    if ($.unblockUI) $.unblockUI();
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                alert('Error: ' + (xhr.responseText || errorThrown));
                if ($.unblockUI) $.unblockUI();
            }
        });
    });
</script>
