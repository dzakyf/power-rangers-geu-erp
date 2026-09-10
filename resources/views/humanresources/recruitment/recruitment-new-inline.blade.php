<div class="card">
    <div class="card-header" style="background-color: rgba(105,178,87,1); color: white;">
        <h5 class="card-title mb-0">NEW RECRUITMENT</h5>
    </div>
    <div class="card-body" id="ContentPages">
        <div class="row">
            <!-- Position -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Requisition_ID">Position</label>
                    <select id="Requisition_ID" class="form-control">
                        <option value="">-- Pilih Position --</option>
                        <?php
                        $q = DB::table("requisition")->where("Status","APPROVED")->where("Location", Auth::user()->Location)->orderBy("ID","DESC")->get();
                        foreach ($q as $fa){
                            echo "<option value='$fa->Task_ID'>$fa->Task_ID - $fa->Job_Position ($fa->Department)</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!-- Name -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Name">Name of Candidate</label>
                    <input type="text" id="Name" class="form-control">
                </div>
            </div>
            <!-- Gender -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Gender">Gender</label>
                    <select id="Gender" class="form-control">
                        <option>MALE</option>
                        <option>FEMALE</option>
                    </select>
                </div>
            </div>
            <!-- Birth Date -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Birth_Date">Birth Date</label>
                    <input type="text" id="Birth_Date" class="form-control" value="<?php echo date('1990-m-d');?>">
                </div>
            </div>
            <!-- Birth Place -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Birth_Place">Birth Place</label>
                    <input type="text" id="Birth_Place" class="form-control">
                </div>
            </div>
            <!-- KTP -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="KTP">Citizen ID Card/KTP</label>
                    <input type="text" id="KTP" class="form-control">
                </div>
            </div>
            <!-- Address -->
            <div class="col-md-12">
                <div class="form-group">
                    <label for="Address">Address</label>
                    <textarea cols="100" rows="4" id="Address" class="form-control"></textarea>
                </div>
            </div>
            <!-- City -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="City">City</label>
                    <input type="text" id="City" class="form-control">
                </div>
            </div>
            <!-- Phone -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Phone">Phone</label>
                    <input type="text" id="Phone" class="form-control">
                </div>
            </div>
            <!-- Handphone -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Handphone">WhatsApp No</label>
                    <input type="text" id="Handphone" class="form-control">
                </div>
            </div>
            <!-- Marital Status -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Marital_Status">Marital Status</label>
                    <select id="Marital_Status" class="form-control">
                        <option>SINGLE</option>
                        <option>MARRIED</option>
                        <option>WIDOWER</option>
                        <option>WIDOW</option>
                    </select>
                </div>
            </div>
            <!-- Last Education -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Last_Education">Last Education</label>
                    <select id="Last_Education" class="form-control">
                        <option>SMA/SMK</option>
                        <option>D1</option>
                        <option>D2</option>
                        <option>D3</option>
                        <option>D4</option>
                        <option>S1</option>
                        <option>S2</option>
                        <option>S3</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Last_Education_From">Education Institution</label>
                    <input type="text" id="Last_Education_From" class="form-control">
                </div>
            </div>
            <!-- Email -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Email">Email Address</label>
                    <input type="text" id="Email" class="form-control">
                </div>
            </div>
            <!-- Religion -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Religion">Religion</label>
                    <select id="Religion" class="form-control">
                        <option>ISLAM</option>
                        <option>PROTESTAN</option>
                        <option>KATHOLIK</option>
                        <option>BUDHA</option>
                        <option>HINDU</option>
                    </select>
                </div>
            </div>
            <!-- Citizenship -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="kewarganegaraan">Citizenship</label>
                    <input type="text" id="kewarganegaraan" class="form-control">
                </div>
            </div>
        </div>
        <input type="hidden" id="source" name="source" value="public">
        <div class="row mt-3">
            <div class="col-12">
                <button type="button" id="SubmitCustDetail" class="btn btn-primary btn-block">Submit</button>
            </div>
        </div>
    </div>
</div>
<div id="DivProcess"></div>

<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Expected_Closing_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");

$('#SubmitCustDetail').click(function(){
    var gettingVars = getAllValues();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "recruitment-new-submit?_token=<?php echo csrf_token(); ?>",
        type: "POST",
        data: gettingVars,
        success: function(data, textStatus, jqXHR) {
            var dataPos = data.indexOf("success");
            if (dataPos != '-1') {
                alert('Data New Recruitment Submitted!');
                if (typeof refreshdata === 'function') refreshdata();
                if (typeof $('#divlistRecruitment') !== 'undefined') $('#divlistRecruitment').html('');
            } else {
                alert('Recruitment Failed to Submit!');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error Internal! Please Try again!");
        }
    });
});

function getAllValues() {
    var inputValues = [];
    $('#ContentPages input,select,textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }
        else if (type != "button" || type != "submit") {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        } else if ($(this).is("textarea")) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }
    });
    return inputValues.join('&');
}

var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Birth_Date","End_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");
</script>
