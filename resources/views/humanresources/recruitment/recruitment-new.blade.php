@extends('layout.mainlayout')
@section('content-wrapper')
<div class="container-fluid">
    {{-- Main Content Header --}}
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Recruitment</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">New Data (Internal)</span>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12 mx-auto">
            <div class="card">
                <div class="card-header" style="background-color: rgba(105,178,87,1); color: white;">
                    <h5 class="card-title mb-0">NEW RECRUITMENT - INTERNAL</h5>
                </div>

                <div class="card-body" id="ContentPages">
                    <h6 class="mb-3">Please fill in the recruitment data completely and correctly.</h6>

                    <form id="recruitmentForm">
                        {{ csrf_field() }}

                        {{-- SECTION 1: INFORMASI PEKERJAAN --}}
                        <div class="mb-4">
                            <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">1. Informasi Pekerjaan</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Requisition_ID">Position</label>
                                        <select id="Requisition_ID" class="form-control">
                                            <option value="">-- Pilih Position --</option>
                                            <?php
                                            $LSP = Auth::user()->Location ?? 'GEU-HO';
                                            $q = DB::table("requisition")
                                                ->where("Status","APPROVED")
                                                ->where("Location", $LSP)
                                                ->orderBy("ID","DESC")
                                                ->get();
                                            foreach ($q as $fa){
                                                echo "<option value='$fa->Task_ID'>$fa->Task_ID - $fa->Job_Position ($fa->Department)</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 2: DATA PRIBADI --}}
                        <div class="mb-4">
                            <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">2. Data Pribadi</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Name">Name of Candidate</label>
                                        <input type="text" id="Name" class="form-control" placeholder="Nama lengkap kandidat">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Gender">Gender</label>
                                        <select id="Gender" class="form-control">
                                            <option value="MALE">MALE</option>
                                            <option value="FEMALE">FEMALE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Birth_Date">Birth Date</label>
                                        <input type="text" id="Birth_Date" class="form-control" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Birth_Place">Birth Place</label>
                                        <input type="text" id="Birth_Place" class="form-control" placeholder="Tempat lahir">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="KTP">Citizen ID Card / KTP</label>
                                        <input type="text" id="KTP" class="form-control" placeholder="Nomor KTP">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kewarganegaraan">Citizenship</label>
                                        <input type="text" id="kewarganegaraan" class="form-control" placeholder="Warga Negara">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="Address">Address</label>
                                        <textarea id="Address" class="form-control" rows="3" placeholder="Alamat lengkap"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="City">City</label>
                                        <input type="text" id="City" class="form-control" placeholder="Kota">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Marital_Status">Marital Status</label>
                                        <select id="Marital_Status" class="form-control">
                                            <option value="SINGLE">SINGLE</option>
                                            <option value="MARRIED">MARRIED</option>
                                            <option value="DIVORCED">DIVORCED</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 3: DATA KONTAK --}}
                        <div class="mb-4">
                            <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">3. Data Kontak</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Phone">Phone</label>
                                        <input type="text" id="Phone" class="form-control" placeholder="Nomor telepon">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Handphone">WhatsApp No</label>
                                        <input type="text" id="Handphone" class="form-control" placeholder="Nomor WhatsApp">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="Email">Email Address</label>
                                        <input type="text" id="Email" class="form-control" placeholder="email@contoh.com">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 4: DATA PENDIDIKAN & AGAMA --}}
                        <div class="mb-4">
                            <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">4. Data Pendidikan & Agama</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Last_Education">Last Education</label>
                                        <select id="Last_Education" class="form-control">
                                            <option value="SMA/SMK">SMA/SMK</option>
                                            <option value="D1">D1</option>
                                            <option value="D2">D2</option>
                                            <option value="D3">D3</option>
                                            <option value="D4">D4</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Last_Education_From">Education Institution</label>
                                        <input type="text" id="Last_Education_From" class="form-control" placeholder="Nama institusi pendidikan">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Religion">Religion</label>
                                        <select id="Religion" class="form-control">
                                            <option value="ISLAM">ISLAM</option>
                                            <option value="KRISTEN PROTESTAN">KRISTEN PROTESTAN</option>
                                            <option value="KATOLIK">KATOLIK</option>
                                            <option value="HINDU">HINDU</option>
                                            <option value="BUDHA">BUDHA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden field --}}
                        <input type="hidden" id="source" name="source" value="public">

                        {{-- SUBMIT BUTTON --}}
                        <div class="row mt-4">
                            <div class="col-12 text-right">
                                <button type="button" id="SubmitCustDetail" class="btn btn-primary px-4">
                                    <i class="fa fa-save"></i> Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="DivProcess"></div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    $(function() {
        // Inisialisasi Date Picker (sekali saja)
        var myCalendar = new dhtmlXCalendarObject(["Birth_Date"]);
        myCalendar.setDateFormat("%Y-%m-%d");

        $('#SubmitCustDetail').click(function() {
            var gettingVars = getAllValues();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('recruitment-new-submit') }}",
                type: "POST",
                data: gettingVars,
                success: function(data) {
                    var response = JSON.parse(data);
                    if (response.status === 'success') {
                        alert(response.message);
                        window.location.href = "{{ url('recruitment-new') }}";
                    } else {
                        alert(response.message || 'Recruitment Failed to Submit!');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error Internal! Please Try again!");
                }
            });
        });
    });

    function getAllValues() {
        var inputValues = [];
        $('#ContentPages input, select, textarea').each(function() {
            var type = $(this).attr("type");
            if ((type == "checkbox" || type == "radio") && this.checked) {
                inputValues.push($(this).attr("id") + "=" + $(this).val());
            } else if (type !== "button" && type !== "submit") {
                inputValues.push($(this).attr("id") + "=" + $(this).val());
            } else if ($(this).is("textarea")) {
                inputValues.push($(this).attr("id") + "=" + $(this).val());
            }
        });
        return inputValues.join('&');
    }
</script>
@endsection
