@if (empty($cr))
    <div class="alert alert-warning mt-3">Data peminjaman tidak ditemukan.</div>
@else
<div class="mt-3">
    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fas fa-car me-2"></i>Edit Formulir Peminjaman Mobil — {{ $cr->Car_Rent_No }}</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#CARRENT_DTL').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <form id="formCarRentEdit" method="POST">
                @csrf
                <input type="hidden" id="ID" value="{{ $cr->ID }}" />
                <input type="hidden" id="Car_Rent_No" value="{{ $cr->Car_Rent_No }}" />

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Identitas Peminjam</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="User_NIK" class="form-label small fw-semibold">Nomor Induk Karyawan (NIK)</label>
                        <input type="text" id="User_NIK" class="form-control form-control-sm bg-light" value="{{ $cr->User_NIK }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="User_Name" class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" id="User_Name" class="form-control form-control-sm bg-light" value="{{ $cr->User_Name }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="User_Jabatan" class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" id="User_Jabatan" class="form-control form-control-sm bg-light" value="{{ $cr->User_Jabatan }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="User_Department" class="form-label small fw-semibold">Departemen</label>
                        <input type="text" id="User_Department" class="form-control form-control-sm bg-light" value="{{ $cr->User_Department }}" readonly />
                    </div>
                    <div class="col-md-4">
                        <label for="Site_Office" class="form-label small fw-semibold">Site / Office</label>
                        <input type="text" id="Site_Office" class="form-control form-control-sm" value="{{ $cr->Site_Office }}" />
                    </div>
                    <div class="col-md-4">
                        <label for="Phone_Number" class="form-label small fw-semibold">Nomor Telepon</label>
                        <input type="text" id="Phone_Number" class="form-control form-control-sm" value="{{ $cr->Phone_Number ?? '' }}" />
                    </div>
                    <div class="col-md-4">
                        <label for="Email" class="form-label small fw-semibold">Email Address</label>
                        <input type="text" id="Email" class="form-control form-control-sm" value="{{ $cr->Email ?? '' }}" />
                    </div>
                </div>

                @php $tripType = $cr->Trip_Type ?? 'NON_SPPD'; @endphp
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi SIM &amp; Jenis Perjalanan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="Sim_Link" class="form-label small fw-semibold">Link SIM (Google Drive / URL)</label>
                        <input type="text" id="Sim_Link" class="form-control form-control-sm" value="{{ $cr->Sim_Link }}" placeholder="https://drive.google.com/open?id=..." />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold d-block">Tipe Peminjaman</label>
                        <div class="d-flex align-items-center gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Trip_Type" id="Trip_Type_NON" value="NON_SPPD" {{ $tripType == 'NON_SPPD' ? 'checked' : '' }} />
                                <label class="form-check-label small" for="Trip_Type_NON">Non-SPPD (Operasional)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Trip_Type" id="Trip_Type_SPPD" value="SPPD" {{ $tripType == 'SPPD' ? 'checked' : '' }} />
                                <label class="form-check-label small" for="Trip_Type_SPPD">SPPD (Dinas)</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" id="SPPD_Field" style="{{ $tripType == 'SPPD' ? '' : 'display:none;' }}">
                        <label for="SPPD_ID" id="SPPD_Label" class="form-label small fw-semibold">Nomor SPPD (harus ACCEPTED)</label>
                        <select id="SPPD_ID" class="form-select form-select-sm">
                            <option value="">-- Pilih SPPD --</option>
                            @foreach($sppdList ?? [] as $s)
                                <option value="{{ $s->ID }}" {{ (string)($cr->SPPD_ID ?? '') === (string)$s->ID ? 'selected' : '' }}>{{ $s->SPPD_No }} — {{ $s->Destination }} ({{ $s->Schedule_Date }})</option>
                            @endforeach
                            @if(!empty($cr->SPPD_ID) && ($sppdList ?? collect())->where('ID', $cr->SPPD_ID)->count() == 0)
                                <option value="{{ $cr->SPPD_ID }}" selected>{{ $cr->SPPD_No ?? $cr->SPPD_ID }} (arsip)</option>
                            @endif
                        </select>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Keperluan &amp; Rincian Peminjaman</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="Keperluan" class="form-label small fw-semibold">Keperluan</label>
                        <input type="text" id="Keperluan" class="form-control form-control-sm bg-light" value="{{ $cr->Keperluan }}" readonly />
                    </div>
                    <div class="col-md-4">
                        <label for="Jenis_Mobil" class="form-label small fw-semibold">Jenis Mobil yang Dipinjam <span class="text-danger">*</span></label>
                        <select id="Jenis_Mobil" class="form-select form-select-sm">
                            <option value="">-- Pilih Jenis Mobil --</option>
                            @foreach (($fleetList ?? ['Rush', 'Avanza', 'Innova', 'Pajero', 'Alphard', 'Hiace', 'Other']) as $m)
                                <option value="{{ $m }}" {{ $cr->Jenis_Mobil == $m ? 'selected' : '' }}>{{ ($m == 'Other') ? 'Lainnya' : $m }}</option>
                            @endforeach
                            @if(!empty($cr->Jenis_Mobil) && !in_array($cr->Jenis_Mobil, ($fleetList ?? [])))
                                <option value="{{ $cr->Jenis_Mobil }}" selected>{{ $cr->Jenis_Mobil }} (arsip)</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="Status" class="form-label small fw-semibold">Status</label>
                        <input type="text" id="Status" class="form-control form-control-sm bg-light fw-bold" value="{{ $cr->Status }}" readonly />
                        <div class="form-text">Status tidak dapat diubah secara manual.</div>
                    </div>
                    <div class="col-md-12">
                        <label for="Tujuan_Penggunaan" class="form-label small fw-semibold">Tujuan Penggunaan <span class="text-danger">*</span></label>
                        <textarea id="Tujuan_Penggunaan" class="form-control form-control-sm" rows="2">{{ $cr->Tujuan_Penggunaan }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label for="Tanggal_Peminjaman" class="form-label small fw-semibold">Tanggal Peminjaman</label>
                        <input type="text" id="Tanggal_Peminjaman" class="form-control form-control-sm bg-white" value="{{ $cr->Tanggal_Peminjaman }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label for="Jam_Peminjaman" class="form-label small fw-semibold">Jam Peminjaman</label>
                        <select id="Jam_Peminjaman" class="form-select form-select-sm">
                            @for ($i = 0; $i < 24; $i++)
                                @for ($j = 0; $j < 60; $j += 30)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}" {{ ($cr->Jam_Peminjaman ?? '') == str_pad($i, 2, '0', STR_PAD_LEFT) . ':' . str_pad($j, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="Tanggal_Pengembalian" class="form-label small fw-semibold">Tanggal Pengembalian <span class="text-danger">*</span></label>
                        <input type="text" id="Tanggal_Pengembalian" class="form-control form-control-sm bg-white" value="{{ $cr->Tanggal_Pengembalian }}" />
                    </div>
                    <div class="col-md-3">
                        <label for="Jam_Pengembalian" class="form-label small fw-semibold">Jam Pengembalian</label>
                        <select id="Jam_Pengembalian" class="form-select form-select-sm">
                            @for ($i = 0; $i < 24; $i++)
                                @for ($j = 0; $j < 60; $j += 30)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}" {{ ($cr->Jam_Pengembalian ?? '') == str_pad($i, 2, '0', STR_PAD_LEFT) . ':' . str_pad($j, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
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
                        <textarea id="Kerusakan_Denda" class="form-control form-control-sm" rows="2">{{ $cr->Kerusakan_Denda }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="Bukti_Pembayaran" class="form-label small fw-semibold">Bukti Pembayaran Denda</label>
                        <input type="text" id="Bukti_Pembayaran" class="form-control form-control-sm" value="{{ $cr->Bukti_Pembayaran }}" placeholder="Link / nomor referensi bukti pembayaran" />
                    </div>
                </div>

                <div id="divtom" class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="$('#CARRENT_DTL').html('')">
                        <i class="fa fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" id="SubmitCarRentEdit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i> Update Data
                    </button>
                </div>
                <div id="WinCOA"></div>
            </form>
        </div>
    </div>
    <div id="DivProcess"></div>
    <script>
        $(function() {
            if (typeof dhtmlXCalendarObject !== 'undefined') {
                var crEditCal = new dhtmlXCalendarObject(["Tanggal_Peminjaman", "Tanggal_Pengembalian"]);
                crEditCal.setDateFormat("%Y-%m-%d");
            }
            $('#Jam_Peminjaman').val('{{ $cr->Jam_Peminjaman }}');
            $('#Jam_Pengembalian').val('{{ $cr->Jam_Pengembalian }}');
            function toggleSppd() {
                var isSppd = $('input[name="Trip_Type"]:checked').val() == 'SPPD';
                $('#SPPD_Label, #SPPD_Field').toggle(isSppd);
            }
            $('input[name="Trip_Type"]').on('change', toggleSppd);
        });

        $('#SubmitCarRentEdit').on('click', function(e) {
            e.preventDefault();
            var tripType = $('input[name="Trip_Type"]:checked').val() || 'NON_SPPD';
            var sppdId = $('#SPPD_ID').val() || '';
            var tanggalPengembalian = $('#Tanggal_Pengembalian').val();
            var tujuan = $('#Tujuan_Penggunaan').val();
            var jenisMobil = $('#Jenis_Mobil').val();
            if (tripType == 'SPPD' && sppdId == '') { alert('SPPD wajib dipilih untuk tipe SPPD!'); $('#SPPD_ID').focus(); return false; }
            if (tanggalPengembalian == '') { alert('Tanggal Pengembalian harus diisi!'); $('#Tanggal_Pengembalian').focus(); return false; }
            if (tujuan == '') { alert('Tujuan Penggunaan harus diisi!'); $('#Tujuan_Penggunaan').focus(); return false; }
            if (jenisMobil == '') { alert('Jenis Mobil harus dipilih!'); $('#Jenis_Mobil').focus(); return false; }
            if ($.blockUI) $.blockUI();
            var values = ['Trip_Type=' + encodeURIComponent(tripType)];
            if (tripType == 'SPPD') { values.push('SPPD_ID=' + encodeURIComponent(sppdId)); }
            $('#formCarRentEdit input, #formCarRentEdit select, #formCarRentEdit textarea').each(function() {
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
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                url: 'hrd-car-rent-edit-submit',
                type: 'POST',
                data: values.join('&'),
                success: function(data) {
                    if (data.status == 'success') {
                        alert('Data Peminjaman Mobil Berhasil Diupdate!');
                        if ($.unblockUI) $.unblockUI();
                        setTimeout(function() { window.location.href = 'hrd-car-rent-index'; }, 500);
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
</div>
@endif
