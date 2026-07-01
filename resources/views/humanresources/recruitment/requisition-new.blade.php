<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$positions = DB::table('master_position')->orderBy('Position', 'asc')->get();

?>


{{-- <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW REQUISITION </div> --}}
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-primary text-white p-3">
        New PTK
    </div>
    <div class="card-body">
        <form id="NewRec">
            <div id="ContentPages">
                <div class="FormInput_Form_Content" class="form-row" >
                    <div class="form-group col-md-6">
                        <label>
                            Job Position
                        </label>
                        
                        <select id="Job_Position" class="form-control">
                            <option value="">-- Pilih Posisi Pekerjaan  -- </option>
                            @foreach($positions as $position)
                                <option value="{{ $position->ID }}">
                                    Dir : {{ $position->Directorat }} &nbsp;&nbsp;&nbsp;&nbsp;
                                    Dep : {{ $position->Department }} &nbsp;&nbsp;&nbsp;&nbsp;
                                    Sub : {{ $position->Subsection }} &nbsp;&nbsp;&nbsp;&nbsp;
                                    Pos : {{ $position->Position }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>
                            Subject
                        </label>
                        
                        <input type="text" class="form-control" id="Subject" />
                    </div>
                    <div class="form-group col-md-6">
                        <label>
                            Placement
                        </label>
                        
                        <input type="text" class="form-control" id="Placement" />
                    </div>
                    <div class="form-group col-md-6">
                        <label>
                            Description
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
                            Priority
                        </label>
                        
                        <select id="Priority" class="form-control">
                            <option>NORMAL</option>
                            <option>HIGH</option>
                            <option>URGENT</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Type of Allocation
                        </label>
                        
                        <select id="Allocation" class="form-control">
                            <option>NEW</option>
                            <option>REPLACEMENT</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Work Area
                        </label>
                        
                        <input type="text" id="Work_Area" class="form-control" size="15" value="" />
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Qty
                        </label>
                        <input type="text" id="Qty" class="form-control" size="10" value="1"/>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Open Date
                        </label>
                        <input type="text" id="Start_Date" class="form-control bg-white" size="15" value="<?php echo date('d M Y');?>" readonly/>
                    </div>
                    <div class="form-group col-md-3">
                        <label>
                            Close Date
                        </label>
                        <input type="text" id="End_Date" class="form-control bg-white" size="15" value="<?php echo date('d M Y');?>" readonly/>
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
    myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date"]);
	myCalendar.setDateFormat("%d %M %Y");

 	$('#SubmitBtn').on('click', function(e) {
        e.preventDefault();
        var formData = {
            Job_Position: $('#Job_Position').val(),
            Description: $('#Description').val(),
            Requirement: $('#Requirement').val(),
            Priority: $('#Priority').val(),
            Allocation: $('#Allocation').val(),
            Qty: $('#Qty').val(),
            Start_Date: $('#Start_Date').val(),
            End_Date: $('#End_Date').val()
            Work_Area: $('#Work_Area').val()
        };

        $.ajax({
            type: 'POST',
            url: 'recruitment-requisition-new-submit',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(response) {
                alert('Requisition submitted successfully!');
                // Optionally, you can reset the form or redirect the user
                $('#NewRec')[0].reset();
                refreshdata();
                $('#divlistRequisition').html('');
            },
            error: function(xhr, status, error) {
                alert('Error submitting requisition: ' + xhr.responseText);
            }
        });
    });
</script>
