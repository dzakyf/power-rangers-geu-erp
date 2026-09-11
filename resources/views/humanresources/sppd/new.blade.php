@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-sppd-index">Perjalanan Dinas</a></li>
            <li class="breadcrumb-item active"><span class="active">New Request SPPD</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white p-2">
                    <h4 class="card-title m-0 d-inline-block">Form Request Perjalanan Dinas</h4>
			        <button type="button" class="float-right btn btn-secondary" onclick="history.back();"><i class="fa fa-times"></i> Close</button>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="SPPD_No">No SPPD</label>
                                <input type="text" id="SPPD_No" class="form-control shadow-sm" name="SPPD_No" value="{{ $SPPD_No }}" readonly />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Requester_NIK">NIK Karyawan Pengaju</label>
                                <input type="text" id="Requester_NIK" class="form-control shadow-sm" name="Requester_NIK" value="{{ $user->Emp_ID ?: '' }}" readonly />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Sppd_Type">Jenis SPPD</label>
                                <select id="Sppd_Type" class="form-control shadow-sm" onchange="toggleType();">
                                    <option value="NORMAL">Normal SPPD</option>
                                    <option value="PERBANTUAN">Perbantuan Sementara</option>
                                    <option value="RELOKASI" disabled>Relokasi/Mutasi (belum tersedia)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Date_Start">Tanggal Mulai</label>
                                <input type="text" id="Date_Start" class="form-control shadow-sm" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Date_Finish">Tanggal Selesai</label>
                                <input type="text" id="Date_Finish" class="form-control shadow-sm" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Department">Nama Departemen</label>
                                <select id="Department" class="form-control shadow-sm" name="Department">
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->Department }}">{{ $d->Department }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Requester_Name">Nama Karyawan Pengaju</label>
                                <input type="text" id="Requester_Name" class="form-control shadow-sm" name="Requester_Name" value="{{ $user->Name ?: $user->username }}" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Destination">Tujuan Perjalanan Dinas</label>
                                <input type="text" id="Destination" class="form-control shadow-sm" name="Destination" placeholder="contoh: Jakarta" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Route_Start">Rute (Asal)</label>
                                <input type="text" id="Route_Start" class="form-control shadow-sm" name="Route_Start" placeholder="Asal" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Route_Finish">Rute (Tujuan)</label>
                                <input type="text" id="Route_Finish" class="form-control shadow-sm" name="Route_Finish" placeholder="Tujuan" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Schedule_Date">Tanggal Kegiatan</label>
                                <input type="text" id="Schedule_Date" class="form-control shadow-sm" name="Schedule_Date" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Schedule_Time">Jam Kegiatan</label>
                                <input type="text" id="Schedule_Time" class="form-control shadow-sm" name="Schedule_Time" placeholder="contoh: 08.00 WIB" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Program_Name">Kegiatan</label>
                                <input type="text" id="Program_Name" class="form-control shadow-sm" name="Program_Name" placeholder="contoh: Koordinasi dengan Mitra" />
                            </div>
                        </div>

                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label>Rincian Biaya yang Timbul</label>
                                <table id="tblCost" class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="180">Biaya (Rp)</th>
                                            <th>Keterangan</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" id="Cost_0" class="form-control" onkeyup="hitungTotal();" /></td>
                                            <td><input type="text" id="Desc_0" class="form-control" /></td>
                                            <td><button type="button" class="btn btn-success btn-sm" onclick="tambahRow();">+</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="font-weight-bold">
                                    Total Estimasi : Rp <span id="TotalCost">0</span>
                                </div>
                                <input type="hidden" id="Estimated_Cost" value="0" />
                            </div>
                        </div>

                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label for="Description">Keterangan</label>
                                <textarea id="Description" class="form-control shadow-sm" name="Description" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="col-md-12 col-12" id="pbSection" style="display:none;">
                            <div class="card border-warning mt-2">
                                <div class="card-header bg-warning text-dark p-2">
                                    <h6 class="m-0 font-weight-bold">Perbantuan Sementara</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="Origin_Site">Asal Site</label>
                                                <input type="text" id="Origin_Site" class="form-control shadow-sm" placeholder="contoh: GEU-HO" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="Target_Site">Lokasi Perbantuan</label>
                                                <input type="text" id="Target_Site" class="form-control shadow-sm" placeholder="contoh: Site Cirebon" />
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-12">
                                            <div class="form-group">
                                                <label for="Reason">Alasan Perbantuan</label>
                                                <textarea id="Reason" class="form-control shadow-sm" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-12">
                            <div class="card border-info mt-2">
                                <div class="card-header bg-info text-white p-2">
                                    <label class="m-0 font-weight-bold"><input type="checkbox" id="Need_Car" value="1" /> Butuh Mobil Operasional (otomatis booking saat SPPD disetujui)</label>
                                </div>
                                <div class="card-body" id="NeedCarBody" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="Car_Jenis_Mobil">Jenis Mobil</label>
                                                <select id="Car_Jenis_Mobil" class="form-control shadow-sm">
                                                    <option value="">-- Pilih Jenis Mobil --</option>
                                                    @foreach(($fleetList ?? ['Rush','Avanza','Innova','Pajero','Alphard','Hiace','Other']) as $m)
                                                        <option value="{{ $m }}">{{ ($m == 'Other') ? 'Lainnya' : $m }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <div class="form-group">
                                                <label for="Car_Tgl_Pinjam">Tgl Pinjam</label>
                                                <input type="text" id="Car_Tgl_Pinjam" class="form-control shadow-sm" />
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <div class="form-group">
                                                <label for="Car_Tgl_Kembali">Tgl Kembali</label>
                                                <input type="text" id="Car_Tgl_Kembali" class="form-control shadow-sm" />
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <div class="form-group">
                                                <label for="Car_Jam_Pinjam">Jam Pinjam</label>
                                                <input type="text" id="Car_Jam_Pinjam" class="form-control shadow-sm" value="08:00" />
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <div class="form-group">
                                                <label for="Car_Jam_Kembali">Jam Kembali</label>
                                                <input type="text" id="Car_Jam_Kembali" class="form-control shadow-sm" value="17:00" />
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">Tanggal default mengikuti Tanggal Kegiatan. Booking dibuat otomatis saat status jadi ACCEPTED; bila gagal (misal mobil bentrok) buat manual via menu Car Rent.</small>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-success" id="submitBtn">Submit</button>
                        <a href="hrd-sppd-index" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Schedule_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");
var rangeCal = new dhtmlXCalendarObject(["Date_Start", "Date_Finish"]);
rangeCal.setDateFormat("%Y-%m-%d");
var carCal = new dhtmlXCalendarObject(["Car_Tgl_Pinjam", "Car_Tgl_Kembali"]);
carCal.setDateFormat("%Y-%m-%d");

function toggleType() {
    var t = $('#Sppd_Type').val();
    $('#pbSection').toggle(t === 'PERBANTUAN');
    if (t === 'RELOKASI') {
        $('#Sppd_Type').val('NORMAL');
        toggleType();
        alert('Relokasi/Mutasi belum tersedia. Silakan pilih jenis lain.');
    }
}

$('#Need_Car').on('change', function () {
    var need = $(this).is(':checked');
    $('#NeedCarBody').toggle(need);
    if (need) {
        if (!$('#Car_Tgl_Pinjam').val()) $('#Car_Tgl_Pinjam').val($('#Schedule_Date').val());
        if (!$('#Car_Tgl_Kembali').val()) $('#Car_Tgl_Kembali').val($('#Schedule_Date').val());
    }
});
$('#Schedule_Date').on('change blur', function () {
    if ($('#Need_Car').is(':checked')) {
        if (!$('#Car_Tgl_Pinjam').val()) $('#Car_Tgl_Pinjam').val($(this).val());
        if (!$('#Car_Tgl_Kembali').val()) $('#Car_Tgl_Kembali').val($(this).val());
    }
});

var rowCost = 1;

function tambahRow() {
    var html = "<tr>"
        + "<td><input type='text' id='Cost_" + rowCost + "' class='form-control' onkeyup='hitungTotal();' /></td>"
        + "<td><input type='text' id='Desc_" + rowCost + "' class='form-control' /></td>"
        + "<td><button type='button' class='btn btn-danger btn-sm' onclick='hapusRow(this);'>-</button></td>"
        + "</tr>";
    $("#tblCost tbody").append(html);
    rowCost++;
}

function hapusRow(btn) {
    $(btn).closest("tr").remove();
    hitungTotal();
}

function hitungTotal() {
    var total = 0;
    $("[id^='Cost_']").each(function () {
        var v = parseFloat($(this).val().replace(/\./g, "").replace(/,/g, "."));
        if (!isNaN(v)) total += v;
    });
    $("#TotalCost").text(total.toLocaleString("id-ID"));
    $("#Estimated_Cost").val(total);
}

$('#submitBtn').click(function (e) {
    e.preventDefault();

    var listId = ['Department', 'Requester_Name', 'Destination', 'Route_Start', 'Route_Finish', 'Schedule_Date', 'Schedule_Time', 'Program_Name', 'Description'];
    for (var i = 0; i < listId.length; i++) {
        if ($.trim($("#" + listId[i]).val()) === "") {
            alert("Field " + listId[i].replace(/_/g, " ") + " wajib diisi!");
            return;
        }
    }
    if (parseFloat($("#Estimated_Cost").val()) <= 0) {
        alert("Rincian biaya wajib diisi minimal satu baris!");
        return;
    }
    if ($.trim($("#Date_Start").val()) === "" || $.trim($("#Date_Finish").val()) === "") {
        alert("Tanggal mulai dan selesai wajib diisi!");
        return;
    }
    var ds = new Date($("#Date_Start").val());
    var df = new Date($("#Date_Finish").val());
    if (df < ds) {
        alert("Tanggal selesai tidak boleh sebelum tanggal mulai!");
        return;
    }
    var span = Math.floor((df - ds) / 86400000) + 1;
    var st = $('#Sppd_Type').val();
    if (st === 'NORMAL' && span > 14) {
        alert("SPPD Normal maksimal 14 hari!");
        return;
    }
    if (st === 'PERBANTUAN') {
        if (span <= 14) {
            alert("Perbantuan Sementara harus lebih dari 14 hari!");
            return;
        }
        if ($.trim($("#Origin_Site").val()) === "" || $.trim($("#Target_Site").val()) === "" || $.trim($("#Reason").val()) === "") {
            alert("Asal Site, Lokasi Perbantuan, dan Alasan wajib diisi!");
            return;
        }
    }
    if ($("#Route_Start").val() == $("#Route_Finish").val()) {
        alert("Rute asal dan tujuan tidak boleh sama!");
        return;
    }
    if ($('#Need_Car').is(':checked')) {
        if (!$('#Car_Jenis_Mobil').val() || !$('#Car_Tgl_Pinjam').val() || !$('#Car_Tgl_Kembali').val()) {
            alert('Bila butuh mobil: jenis mobil + tanggal pinjam/kembali wajib diisi!');
            return;
        }
        if ($('#Car_Tgl_Kembali').val() < $('#Car_Tgl_Pinjam').val()) {
            alert('Tanggal kembali mobil tidak boleh sebelum tanggal pinjam!');
            return;
        }
    }

    $.ajax({
        url: 'hrd-sppd-new-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: buildData(),
        success: function (data) {
            if (data.status === 'success') {
                alert('Request SPPD Submitted!');
                location.href = 'hrd-sppd-index';
            } else {
                alert('Gagal submit! ' + data.message);
            }
        },
        error: function (xhr) {
            var msg = 'Terjadi kesalahan server';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
});

function buildData() {
    var out = {
        SPPD_No: $('#SPPD_No').val(),
        Sppd_Type: $('#Sppd_Type').val(),
        Date_Start: $('#Date_Start').val(),
        Date_Finish: $('#Date_Finish').val(),
        Origin_Site: $('#Origin_Site').val(),
        Target_Site: $('#Target_Site').val(),
        Reason: $('#Reason').val(),
        Requester_NIK: $('#Requester_NIK').val(),
        Department: $('#Department').val(),
        Requester_Name: $('#Requester_Name').val(),
        Destination: $('#Destination').val(),
        Route_Start: $('#Route_Start').val(),
        Route_Finish: $('#Route_Finish').val(),
        Schedule_Date: $('#Schedule_Date').val(),
        Schedule_Time: $('#Schedule_Time').val(),
        Program_Name: $('#Program_Name').val(),
        Description: $('#Description').val(),
        Estimated_Cost: $('#Estimated_Cost').val(),
        Need_Car: $('#Need_Car').is(':checked') ? 1 : 0,
        Car_Jenis_Mobil: $('#Car_Jenis_Mobil').val(),
        Car_Tgl_Pinjam: $('#Car_Tgl_Pinjam').val(),
        Car_Tgl_Kembali: $('#Car_Tgl_Kembali').val(),
        Car_Jam_Pinjam: $('#Car_Jam_Pinjam').val(),
        Car_Jam_Kembali: $('#Car_Jam_Kembali').val()
    };
    $("[id^='Cost_']").each(function () {
        var idx = $(this).attr('id').replace('Cost_', '');
        out['Cost_' + idx] = $(this).val();
        out['Desc_' + idx] = $('#Desc_' + idx).val();
    });
    return out;
}
</script>
@endsection
