<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;
$positions = DB::table('master_position')->orderBy('Position', 'asc')->get();
$workCalendar = DB::table('work_calendars')->get();
$req = DB::table('requisition')->where("Created_Date", 'LIKE', date("Y") . '%')->count() + 1;
$Taskid = 'REQ/' . $LSP . '/' . date('Ymdhis') . '-' . sprintf('%02d', $req);

$location = DB::table('location')->get()

?>


{{-- <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW REQUISITION </div> --}}
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-primary text-white p-3">
        Form PTK
    </div>
    <div class="card-body">
        <form id="NewRec">
            <div id="ContentPages">
                <div class="FormInput_Form_Content" class="form-row">
                    <div class="form-group col-md-6">
                        <label>
                            PTK No
                        </label>

                        <input type="text" class="form-control" id="Task_ID" value="{{ $Taskid }}" readonly />
                    </div>
                    <div class="form-group col-md-6">
                        <label>
                            Posisi
                        </label>

                        <select id="Job_Position" class="form-control">
                            <option value="">-- Pilih Posisi Pekerjaan -- </option>
                            @foreach($positions as $position)
                            <option value="{{ $position->ID }}">
                                {{ $position->Position }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- div class="form-group col-md-6">
                        <label>
                            Subject
                        </label>

                        <input type="text" class="form-control" id="Subject" />
                    </div -->
                    <div class="form-group col-md-6">
                        <label>
                            Site Penempatan
                        </label>
                        <select name="Placement" id="Placement" class="form-control">
                            <option value="">-- Pilih penempatan --</option>
                            @foreach ($location as $item)
                            <option value="{{ $item->Location_ID }}">{{ $item->Name }} ({{ $item->Location_ID }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>
                            Deskripsi
                        </label>

                        <textarea cols="100" rows="4" id="Description" class="form-control"></textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>
                            Detail Requirement
                        </label>

                        <textarea cols="100" rows="4" id="Requirement" class="form-control"></textarea>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Prioritas
                        </label>

                        <select id="Priority" class="form-control">
                            <option>NORMAL</option>
                            <option>HIGH</option>
                            <option>URGENT</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Tipe
                        </label>

                        <select id="Allocation" class="form-control">
                            <option value="NEW">KARYAWAN BARU</option>
                            <option value="REPLACEMENT">REPLACEMENT KARYAWAN </option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Permintaan kerja berkaitan dengan
                        </label>

                        <select id="Request_Type" class="form-control">
                            <option value="1">Ada peningkatan volume kerja</option>
                            <option value="2">Pengganti tenaga kerja keluar</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                        <div class="mt-3" style="display:none;" id="Div_Request_Type">
                            <input type="text" id="Request_Desc" value="" class="form-control" placeholder="Isi keterangan lainnya">
                        </div>
                    </div>
                    {{-- <div class="form-group col-md-3">
                        <label>
                            Work Area
                        </label>
                        
                        <input type="text" id="Work_Area" class="form-control" size="15" value="" />
                    </div> --}}
                    <div class="form-group col-md-3">
                        <label>
                            Work Schedule
                        </label>

                        <select name="Work_Schedule" id="Work_Schedule" class="form-control">
                            <option value="">-- Pilih Waktu Kerja --</option>
                            @foreach ($workCalendar as $item)
                            <option value="{{$item->ID}}">{{$item->Name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Jumlah
                        </label>
                        <input type="text" id="Qty" class="form-control" size="10" value="1" />
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Mulai Tanggal
                        </label>
                        <input type="text" id="Start_Date" class="form-control bg-white" size="15" value="<?php echo date('d M Y'); ?>" readonly />
                    </div>
                    <div class="form-group col-md-12">
                        <button class="btn btn-danger" type="reset">Reset</button>
                        <button class="btn btn-success" type="button" id="SubmitBtn">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Start_Date"]);
    myCalendar.setDateFormat("%d %M %Y");

    $('#Request_Type').on('change', function (e) {
        let val = $(this).find('option:selected').val()
        if (val == 'OTHER') {
            $('#Div_Request_Type').show();
            $('#Request_Desc').val('');
        }else {
            $('#Div_Request_Type').hide();
            if (val == 1) {
                $('#Request_Desc').val('Ada peningkatan volume kerja')
            }else {
                $('#Request_Desc').val('Pengganti tenaga kerja keluar')
            }
        }
    })

    $('#SubmitBtn').on('click', function(e) {
        e.preventDefault();
        var formData = {
            Task_ID: $('#Task_ID').val(),
            Job_Position: $('#Job_Position').val(),
            Subject: $('#Subject').val(),
            Placement: $('#Placement').val(),
            Description: $('#Description').val(),
            Requirement: $('#Requirement').val(),
            Priority: $('#Priority').val(),
            Allocation: $('#Allocation').val(),
            Request_Type: $('#Request_Type').val(),
            Request_Desc: $('#Request_Desc').val(),
            Work_Schedule: $('#Work_Schedule').val(),
            Qty: $('#Qty').val(),
            Start_Date: $('#Start_Date').val()
        };

        $.ajax({
            type: 'POST',
            url: 'recruitment-requisition-new-submit',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response == 'success') {
                    alert('Requisition submitted successfully!');
                    $('#NewRec')[0].reset();
                    refreshdata();
                    $('#divlistRequisition').html('');
                }else if(response == 'exist') {
                    alert('Sudah ada pengajuan yang sama');
                    $('#Placement').focus();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('.field-error').remove();
                    $('.is-invalid').removeClass('is-invalid');
                    $.each(errors, function(field, messages) {
                        var el = $('#' + field);
                        if (el.length) {
                            el.addClass('is-invalid');
                            el.after('<div class="field-error text-danger small">' + messages[0] + '</div>');
                        }
                    });
                } else {
                    alert('Error submitting requisition: ' + xhr.responseText);
                }
            }
        });
    });
</script>