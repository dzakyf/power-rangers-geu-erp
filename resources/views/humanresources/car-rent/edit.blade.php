@extends('layout.mainlayout')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>PEMINJAMAN MOBIL OPERASIONAL</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
            <li class="breadcrumb-item active"><span class="active">Edit Data Peminjaman</span></li>
        </ol>
    </div>

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

                    <!-- Identitas Peminjam -->
                    <h6 style="font-weight:bold; margin-bottom:10px;">Identitas Peminjam</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">
                            Nomor Induk Karyawan (NIK)
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="User_NIK" size="35" value="{{ $cr->User_NIK }}" readonly />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Nama Lengkap
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="User_Name" size="35" value="{{ $cr->User_Name }}" readonly />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Jabatan
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="User_Jabatan" size="35" value="{{ $cr->User_Jabatan }}" readonly />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Departemen
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="User_Department" size="35" value="{{ $cr->User_Department }}" readonly />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Site / Office
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Site_Office" size="35" value="{{ $cr->Site_Office }}" />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Nomor Telepon
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Phone_Number" size="35" value="{{ $cr->Phone_Number ?? '' }}" />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Email Address
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Email" size="35" value="{{ $cr->Email ?? '' }}" />
                        </div>
                    </div>

                    <!-- Informasi SIM -->
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Informasi SIM</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-12">
                            Link SIM (Google Drive / URL)
                        </div>
                        <div class="FormInput_right col-md-12">
                            <input type="text" id="Sim_Link" size="60" value="{{ $cr->Sim_Link }}" placeholder="https://drive.google.com/open?id=..." />
                        </div>
                    </div>

                    <!-- Keperluan & Tujuan -->
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Keperluan & Tujuan Penggunaan</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">
                            Keperluan
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Keperluan" size="35" value="{{ $cr->Keperluan }}" readonly />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Tujuan Penggunaan
                        </div>
                        <div class="FormInput_right col-md-6">
                            <textarea id="Tujuan_Penggunaan" cols="80" rows="2">{{ $cr->Tujuan_Penggunaan }}</textarea>
                        </div>
                    </div>

                    <!-- Rincian Peminjaman -->
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Rincian Peminjaman</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">
                            Tanggal Peminjaman
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Tanggal_Peminjaman" size="15" value="{{ $cr->Tanggal_Peminjaman }}" readonly class="bg-white" />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Jam Peminjaman
                        </div>
                        <div class="FormInput_right col-md-6">
                            <select id="Jam_Peminjaman" class="form-control">
                                <option>00:00</option><option>00:30</option><option>01:00</option><option>01:30</option>
                                <option>02:00</option><option>02:30</option><option>03:00</option><option>03:30</option>
                                <option>04:00</option><option>04:30</option><option>05:00</option><option>05:30</option>
                                <option>06:00</option><option>06:30</option><option>07:00</option><option>07:30</option>
                                <option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option>
                                <option>10:00</option><option>10:30</option><option>11:00</option><option>11:30</option>
                                <option>12:00</option><option>12:30</option><option>13:00</option><option>13:30</option>
                                <option>14:00</option><option>14:30</option><option>15:00</option><option>15:30</option>
                                <option>16:00</option><option>16:30</option><option>17:00</option><option>17:30</option>
                                <option>18:00</option><option>18:30</option><option>19:00</option><option>19:30</option>
                                <option>20:00</option><option>20:30</option><option>21:00</option><option>21:30</option>
                                <option>22:00</option><option>22:30</option><option>23:00</option><option>23:30</option>
                            </select>
                        </div>
                        <div class="FormInput_left col-md-6">
                            Tanggal Pengembalian
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Tanggal_Pengembalian" size="15" value="{{ $cr->Tanggal_Pengembalian }}" class="bg-white" />
                        </div>
                        <div class="FormInput_left col-md-6">
                            Jam Pengembalian
                        </div>
                        <div class="FormInput_right col-md-6">
                            <select id="Jam_Pengembalian" class="form-control">
                                <option>00:00</option><option>00:30</option><option>01:00</option><option>01:30</option>
                                <option>02:00</option><option>02:30</option><option>03:00</option><option>03:30</option>
                                <option>04:00</option><option>04:30</option><option>05:00</option><option>05:30</option>
                                <option>06:00</option><option>06:30</option><option>07:00</option><option>07:30</option>
                                <option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option>
                                <option>10:00</option><option>10:30</option><option>11:00</option><option>11:30</option>
                                <option>12:00</option><option>12:30</option><option>13:00</option><option>13:30</option>
                                <option>14:00</option><option>14:30</option><option>15:00</option><option>15:30</option>
                                <option>16:00</option><option>16:30</option><option>17:00</option><option>17:30</option>
                                <option>18:00</option><option>18:30</option><option>19:00</option><option>19:30</option>
                                <option>20:00</option><option>20:30</option><option>21:00</option><option>21:30</option>
                                <option>22:00</option><option>22:30</option><option>23:00</option><option>23:30</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status -->
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Status</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">
                            Status
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Status" size="35" value="{{ $cr->Status }}" readonly class="bg-light" />
                            <small class="form-text text-muted">Status tidak dapat diubah secara manual.</small>
                        </div>
                    </div>

                    <!-- Jenis Mobil -->
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Jenis Mobil</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">
                            Jenis Mobil yang Dipinjam
                        </div>
                        <div class="FormInput_right col-md-6">
                            <select id="Jenis_Mobil" class="form-control">
                                <option value="">-- Pilih Jenis Mobil --</option>
                                <option value="Rush" {{ $cr->Jenis_Mobil == 'Rush' ? 'selected' : '' }}>Rush</option>
                                <option value="Avanza" {{ $cr->Jenis_Mobil == 'Avanza' ? 'selected' : '' }}>Avanza</option>
                                <option value="Innova" {{ $cr->Jenis_Mobil == 'Innova' ? 'selected' : '' }}>Innova</option>
                                <option value="Pajero" {{ $cr->Jenis_Mobil == 'Pajero' ? 'selected' : '' }}>Pajero</option>
                                <option value="Alphard" {{ $cr->Jenis_Mobil == 'Alphard' ? 'selected' : '' }}>Alphard</option>
                                <option value="Hiace" {{ $cr->Jenis_Mobil == 'Hiace' ? 'selected' : '' }}>Hiace</option>
                                <option value="Other" {{ $cr->Jenis_Mobil == 'Other' || $cr->Jenis_Mobil == 'Avanza' ? '' : '' }}>Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kerusakan / Denda -->
                    <h6 style="font-weight:bold; margin:20px 0 10px 0;">Kerusakan & Denda (jika ada)</h6>
                    <div class="row">
                        <div class="FormInput_left col-md-6">
                            Kerusakan / Perbaikan / Denda
                        </div>
                        <div class="FormInput_right col-md-6">
                            <textarea id="Kerusakan_Denda" cols="80" rows="2">{{ $cr->Kerusakan_Denda }}</textarea>
                        </div>
                        <div class="FormInput_left col-md-6">
                            Bukti Pembayaran Denda
                        </div>
                        <div class="FormInput_right col-md-6">
                            <input type="text" id="Bukti_Pembayaran" size="35" value="{{ $cr->Bukti_Pembayaran }}" placeholder="Link / nomor referensi bukti pembayaran" />
                        </div>
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
        var crEditCal = new dhtmlXCalendarObject(["Tanggal_Peminjaman", "Tanggal_Pengembalian"]);
        crEditCal.setDateFormat("%Y-%m-%d");

        $('#Jam_Peminjaman').val('{{ $cr->Jam_Peminjaman }}');
        $('#Jam_Pengembalian').val('{{ $cr->Jam_Pengembalian }}');

        $('#SubmitCarRentEdit').click(function(e) {
            e.preventDefault();

            let tanggalPengembalian = $('#Tanggal_Pengembalian').val();
            let tujuan = $('#Tujuan_Penggunaan').val();
            let jenisMobil = $('#Jenis_Mobil').val();

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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'hrd-car-rent-edit-submit',
                type: 'POST',
                data: values.join('&'),
                success: function(data) {
                    if (data.status == 'success') {
                        alert('Data Peminjaman Mobil Berhasil Diupdate!');
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
@endsection
