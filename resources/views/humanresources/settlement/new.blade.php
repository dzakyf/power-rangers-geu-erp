@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-settlement-index">Request Settlement</a></li>
            <li class="breadcrumb-item active"><span class="active">New Request Settlement</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title m-0">Form Request Settlement</h4>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="STTL_No">No Settlement</label>
                                <input type="text" id="STTL_No" class="form-control shadow-sm" name="STTL_No" value="{{ $STTL_No }}" readonly />
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

                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label>Rincian Penggunaan Biaya</label>
                                <table id="tblExpense" class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="220">Pengeluaran</th>
                                            <th width="180">Jumlah (Rp)</th>
                                            <th>Keterangan</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" id="Expense_0" class="form-control" /></td>
                                            <td><input type="text" id="Cost_0" class="form-control" onkeyup="hitungTotal();" /></td>
                                            <td><input type="text" id="Desc_0" class="form-control" /></td>
                                            <td><button type="button" class="btn btn-success btn-sm" onclick="tambahRow();">+</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="font-weight-bold">
                                    Total Pengeluaran : Rp <span id="TotalCost">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Advance_Nominal">Biaya Advance yang Diterima (opsional)</label>
                                <input type="text" id="Advance_Nominal" class="form-control shadow-sm" name="Advance_Nominal" placeholder="contoh: 500000" onkeyup="hitungTotal();" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Bank">Bank Transfer (opsional)</label>
                                <input type="text" id="Bank" class="form-control shadow-sm" name="Bank" placeholder="contoh: BCA 123456789 a.n. Nama" />
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="Invoice_File">Berkas Invoice <span class="text-danger">*</span></label>
                                <input type="file" id="Invoice_File" name="Invoice_File" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required />
                                <small class="form-text text-muted">Format PDF/JPG/PNG, maksimal 5MB</small>
                            </div>
                        </div>

                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-success" id="submitBtn">Submit</button>
                        <a href="hrd-settlement-index" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
var rowExpense = 1;

function tambahRow() {
    var html = "<tr>"
        + "<td><input type='text' id='Expense_" + rowExpense + "' class='form-control' /></td>"
        + "<td><input type='text' id='Cost_" + rowExpense + "' class='form-control' onkeyup='hitungTotal();' /></td>"
        + "<td><input type='text' id='Desc_" + rowExpense + "' class='form-control' /></td>"
        + "<td><button type='button' class='btn btn-danger btn-sm' onclick='hapusRow(this);'>-</button></td>"
        + "</tr>";
    $("#tblExpense tbody").append(html);
    rowExpense++;
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
}

$('#submitBtn').click(function (e) {
    e.preventDefault();

    if ($.trim($("#Department").val()) === "") { alert("Field Department wajib diisi!"); return; }
    if ($.trim($("#Requester_Name").val()) === "") { alert("Field Requester Name wajib diisi!"); return; }
    if ($("#Invoice_File")[0].files.length === 0) { alert("Berkas Invoice wajib diupload!"); return; }

    var hasRow = false;
    $("[id^='Cost_']").each(function () {
        var v = parseFloat($(this).val().replace(/\./g, "").replace(/,/g, "."));
        if (!isNaN(v) && v > 0) hasRow = true;
    });
    if (!hasRow) { alert("Rincian penggunaan biaya wajib diisi minimal satu baris!"); return; }

    var formData = new FormData();
    formData.append('STTL_No', $('#STTL_No').val());
    formData.append('Requester_NIK', $('#Requester_NIK').val());
    formData.append('Department', $('#Department').val());
    formData.append('Requester_Name', $('#Requester_Name').val());
    formData.append('Advance_Nominal', $('#Advance_Nominal').val());
    formData.append('Bank', $('#Bank').val());
    formData.append('Invoice_File', $('#Invoice_File')[0].files[0]);
    $("[id^='Expense_']").each(function () {
        var idx = $(this).attr('id').replace('Expense_', '');
        formData.append('Expense_' + idx, $(this).val());
        formData.append('Cost_' + idx, $('#Cost_' + idx).val());
        formData.append('Desc_' + idx, $('#Desc_' + idx).val());
    });

    $.ajax({
        url: 'hrd-settlement-new-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.status === 'success') {
                alert('Request Settlement Submitted!');
                location.href = 'hrd-settlement-index';
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
</script>
@endsection
