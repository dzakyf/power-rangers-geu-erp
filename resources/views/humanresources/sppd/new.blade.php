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
    if ($("#Route_Start").val() == $("#Route_Finish").val()) {
        alert("Rute asal dan tujuan tidak boleh sama!");
        return;
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
        Estimated_Cost: $('#Estimated_Cost').val()
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
