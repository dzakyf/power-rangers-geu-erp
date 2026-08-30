<?php
use Illuminate\Support\Facades\DB;

$cr = DB::table('car_rent')->where('Car_Rent_No', $_GET['Car_Rent_No'])->first();
if (!$cr) {
    echo '<div class="alert alert-warning">Data peminjaman tidak ditemukan.</div>';
    return;
}
?>
<div class="mt-3">
    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white font-weight-bold p-3">
            <h5 class="m-0" style="color:azure"><i class="fas fa-car mr-2"></i>Edit Formulir Peminjaman Mobil — {{ $cr->Car_Rent_No }}</h5>
        </div>
        <div class="card-body">
            <form id="formCarRentEdit" method="POST">
                @csrf
                <input type="hidden" id="ID" value="{{ $cr->ID }}" />
                <input type="hidden" id="Car_Rent_No" value="{{ $cr->Car_Rent_No }}" />
                <div class="FormInput_Form_Content">
                    <h6 style="font-weight:bold; margin-bottom:10px;">Identitas Peminjam</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">Nomor Induk Karyawan (NIK)</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="User_NIK" size="35" value="{{ $cr->User_NIK }}" readonly /></div>
                        <div class="FormInput_left col-md-6">Nama Lengkap</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="User_Name" size="35" value="{{ $cr->User_Name }}" readonly /></div>
                        <div class="FormInput_left col-md-6">Jabatan</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="User_Jabatan" size="35" value="{{ $cr->User_Jabatan }}" readonly /></div>
                        <div class="FormInput_left col-md-6">Departemen</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="User_Department" size="35" value="{{ $cr->User_Department }}" readonly /></div>
                        <div class="FormInput_left col-md-6">Site / Office</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Site_Office" size="35" value="{{ $cr->Site_Office }}" /></div>
                        <div class="FormInput_left col-md-6">Nomor Telepon</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Phone_Number" size="35" value="{{ $cr->Phone_Number ?? '' }}" /></div>
                        <div class="FormInput_left col-md-6">Email Address</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Email" size="35" value="{{ $cr->Email ?? '' }}" /></div>
                    </div>
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Informasi SIM</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-12">Link SIM (Google Drive / URL)</div>
                        <div class="FormInput_right col-md-12"><input type="text" id="Sim_Link" size="60" value="{{ $cr->Sim_Link }}" placeholder="https://drive.google.com/open?id=..." /></div>
                    </div>
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Keperluan & Tujuan Penggunaan</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">Keperluan</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Keperluan" size="35" value="{{ $cr->Keperluan }}" readonly /></div>
                        <div class="FormInput_left col-md-6">Tujuan Penggunaan</div>
                        <div class="FormInput_right col-md-6"><textarea id="Tujuan_Penggunaan" cols="80" rows="2">{{ $cr->Tujuan_Penggunaan }}</textarea></div>
                    </div>
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Rincian Peminjaman</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">Tanggal Peminjaman</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Tanggal_Peminjaman" size="15" value="{{ $cr->Tanggal_Peminjaman }}" readonly class="bg-white" /></div>
                        <div class="FormInput_left col-md-6">Jam Peminjaman</div>
                        <div class="FormInput_right col-md-6">
                            <select id="Jam_Peminjaman" class="form-control">
                                @for ($i = 0; $i < 24; $i++)
                                    @for ($j = 0; $j < 60; $j += 30)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}" {{ ($cr->Jam_Peminjaman ?? '') == str_pad($i, 2, '0', STR_PAD_LEFT) . ':' . str_pad($j, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                        <div class="FormInput_left col-md-6">Tanggal Pengembalian</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Tanggal_Pengembalian" size="15" value="{{ $cr->Tanggal_Pengembalian }}" class="bg-white" /></div>
                        <div class="FormInput_left col-md-6">Jam Pengembalian</div>
                        <div class="FormInput_right col-md-6">
                            <select id="Jam_Pengembalian" class="form-control">
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
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Status</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">Status</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Status" size="35" value="{{ $cr->Status }}" readonly class="bg-light" /><small class="form-text text-muted">Status tidak dapat diubah secara manual.</small></div>
                    </div>
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Jenis Mobil</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">Jenis Mobil yang Dipinjam</div>
                        <div class="FormInput_right col-md-6">
                            <select id="Jenis_Mobil" class="form-control">
                                <option value="">-- Pilih Jenis Mobil --</option>
                                @foreach (['Rush', 'Avanza', 'Innova', 'Pajero', 'Alphard', 'Hiace', 'Other'] as $m)
                                    <option value="{{ $m }}" {{ $cr->Jenis_Mobil == $m ? 'selected' : '' }}>{{ ($m == 'Other') ? 'Lainnya' : $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Kerusakan & Denda (jika ada)</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">Kerusakan / Perbaikan / Denda</div>
                        <div class="FormInput_right col-md-6"><textarea id="Kerusakan_Denda" cols="80" rows="2">{{ $cr->Kerusakan_Denda }}</textarea></div>
                        <div class="FormInput_left col-md-6">Bukti Pembayaran Denda</div>
                        <div class="FormInput_right col-md-6"><input type="text" id="Bukti_Pembayaran" size="35" value="{{ $cr->Bukti_Pembayaran }}" placeholder="Link / nomor referensi bukti pembayaran" /></div>
                    </div>
                    <div id="divtom" class="mt-3 text-right">
                        <input type="submit" id="SubmitCarRentEdit" class="btn btn-primary px-4" value="Update Data" />
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
            var crEditCal = new dhtmlXCalendarObject(["Tanggal_Peminjaman", "Tanggal_Pengembalian"]);
            crEditCal.setDateFormat("%Y-%m-%d");
            $('#Jam_Peminjaman').val('{{ $cr->Jam_Peminjaman }}');
            $('#Jam_Pengembalian').val('{{ $cr->Jam_Pengembalian }}');
        });

        $('#SubmitCarRentEdit').on('click', function(e) {
            e.preventDefault();
            var tanggalPengembalian = $('#Tanggal_Pengembalian').val();
            var tujuan = $('#Tujuan_Penggunaan').val();
            var jenisMobil = $('#Jenis_Mobil').val();
            if (tanggalPengembalian == '') { alert('Tanggal Pengembalian harus diisi!'); $('#Tanggal_Pengembalian').focus(); return false; }
            if (tujuan == '') { alert('Tujuan Penggunaan harus diisi!'); $('#Tujuan_Penggunaan').focus(); return false; }
            if (jenisMobil == '') { alert('Jenis Mobil harus dipilih!'); $('#Jenis_Mobil').focus(); return false; }
            $.blockUI();
            var values = [];
            $('#formCarRentEdit input, #formCarRentEdit select, #formCarRentEdit textarea').each(function() {
                var type = $(this).attr('type');
                if ((type == 'checkbox' || type == 'radio') && this.checked) {
                    values.push($(this).attr('id') + '=' + $(this).val());
                } else if (type != 'button' && type != 'submit') {
                    values.push($(this).attr('id') + '=' + $(this).val());
                }
            });
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: 'hrd-car-rent-edit-submit',
                type: 'POST',
                data: values.join('&'),
                success: function(data) {
                    if (data.status == 'success') {
                        alert('Data Peminjaman Mobil Berhasil Diupdate!');
                        $.unblockUI();
                        setTimeout(function() { window.location.href = 'hrd-car-rent-index'; }, 500);
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
