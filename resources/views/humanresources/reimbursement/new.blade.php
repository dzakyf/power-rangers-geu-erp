@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-reimbursement-index">Reimbursement</a></li>
            <li class="breadcrumb-item active"><span class="active">New Request Reimbursement</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title m-0">Form Request Reimbursement</h4>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Reimbursement_No">No Reimbursement</label>
                                <input type="text" id="Reimbursement_No" class="form-control shadow-sm" name="Reimbursement_No" value="{{ $Reimbursement_No }}" readonly />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="User_Responsibility_ID">NIK Karyawan</label>
                                <input type="text" id="User_Responsibility_ID" class="form-control shadow-sm" name="User_Responsibility_ID" value="{{ $user->Emp_ID ?: '' }}" readonly />
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
                                <label for="User_Responsibility_Name">Nama Karyawan Pengaju</label>
                                <input type="text" id="User_Responsibility_Name" class="form-control shadow-sm" name="User_Responsibility_Name" value="{{ $user->Name ?: $user->username }}" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Reference_No">Reference No (opsional)</label>
                                <input type="text" id="Reference_No" class="form-control shadow-sm" name="Reference_No" placeholder="contoh: SPPD/GA/... / STTL/GA/..." />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Reimbursement_Date">Tanggal Reimbursement</label>
                                <input type="text" id="Reimbursement_Date" class="form-control shadow-sm" name="Reimbursement_Date" />
                            </div>
                        </div>

                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label for="Description">Keterangan / Description</label>
                                <textarea id="Description" class="form-control shadow-sm" name="Description" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label>Rincian Biaya</label>
                                <table id="tblRincian" class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="200">Reference</th>
                                            <th>Keterangan</th>
                                            <th width="180">Jumlah (Rp)</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" id="Ref_0" class="form-control" /></td>
                                            <td><input type="text" id="Remark_0" class="form-control" /></td>
                                            <td><input type="text" id="Amount_0" class="form-control" onkeyup="hitungTotal();" /></td>
                                            <td><button type="button" class="btn btn-success btn-sm" onclick="tambahRow();">+</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="font-weight-bold">
                                    Total : Rp <span id="TotalCost">0</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-success" id="submitBtn">Submit</button>
                        <a href="hrd-reimbursement-index" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Reimbursement_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");

var rowRincian = 1;

function tambahRow() {
    var html = "<tr>"
        + "<td><input type='text' id='Ref_" + rowRincian + "' class='form-control' /></td>"
        + "<td><input type='text' id='Remark_" + rowRincian + "' class='form-control' /></td>"
        + "<td><input type='text' id='Amount_" + rowRincian + "' class='form-control' onkeyup='hitungTotal();' /></td>"
        + "<td><button type='button' class='btn btn-danger btn-sm' onclick='hapusRow(this);'>-</button></td>"
        + "</tr>";
    $("#tblRincian tbody").append(html);
    rowRincian++;
}

function hapusRow(btn) {
    $(btn).closest("tr").remove();
    hitungTotal();
}

function hitungTotal() {
    var total = 0;
    $("[id^='Amount_']").each(function () {
        var v = parseFloat($(this).val().replace(/\./g, "").replace(/,/g, "."));
        if (!isNaN(v)) total += v;
    });
    $("#TotalCost").text(total.toLocaleString("id-ID"));
}

$('#submitBtn').click(function (e) {
    e.preventDefault();

    var listId = ['Department', 'User_Responsibility_Name', 'Reimbursement_Date', 'Description'];
    for (var i = 0; i < listId.length; i++) {
        if ($.trim($("#" + listId[i]).val()) === "") {
            alert("Field " + listId[i].replace(/_/g, " ") + " wajib diisi!");
            return;
        }
    }

    var hasRow = false;
    $("[id^='Amount_']").each(function () {
        var v = parseFloat($(this).val().replace(/\./g, "").replace(/,/g, "."));
        if (!isNaN(v) && v > 0) hasRow = true;
    });
    if (!hasRow) { alert("Rincian biaya wajib diisi minimal satu baris!"); return; }

    var data = {
        Reimbursement_No: $('#Reimbursement_No').val(),
        User_Responsibility_ID: $('#User_Responsibility_ID').val(),
        Department: $('#Department').val(),
        User_Responsibility_Name: $('#User_Responsibility_Name').val(),
        Reference_No: $('#Reference_No').val(),
        Reimbursement_Date: $('#Reimbursement_Date').val(),
        Description: $('#Description').val()
    };
    $("[id^='Amount_']").each(function () {
        var idx = $(this).attr('id').replace('Amount_', '');
        data['Amount_' + idx] = $(this).val();
        data['Remark_' + idx] = $('#Remark_' + idx).val();
        data['Ref_' + idx] = $('#Ref_' + idx).val();
    });

    $.ajax({
        url: 'hrd-reimbursement-new-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: data,
        success: function (res) {
            if (res.status === 'success') {
                alert('Request Reimbursement Submitted!');
                location.href = 'hrd-reimbursement-index';
            } else {
                alert('Gagal submit! ' + res.message);
            }
        },
        error: function (xhr) {
            var msg = 'Terjadi kesalahan server';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
});
</script>
@endsection
