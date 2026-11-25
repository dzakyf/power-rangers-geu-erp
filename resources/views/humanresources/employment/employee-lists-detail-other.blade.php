@include('layout.plugins')
@include('layout.adminor.tab-head')

@php
    $empTaskId = $taskId ?? ($_GET['ID'] ?? '');
    $med = $medical ?? [
        'Height' => '',
        'Weight' => '',
        'Glasses' => 'No',
        'Smoker' => 'No',
        'Longtime' => 'No',
        'Longtime_Desc' => '',
    ];
@endphp

<div class="container-fluid py-3">
    <div id="otherSaveAlert" class="alert d-none mb-3 py-2 px-3 small" role="alert"></div>

    <!-- Health History Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-heartbeat me-2"></i>Health &amp; Disease History</h6>
            <div class="d-flex gap-2">
                <button type="button" id="Health_Add" class="btn btn-sm btn-light text-primary fw-semibold"
                    onclick="addHealthRow()">
                    <i class="fa fa-plus me-1"></i> Add Health Record
                </button>
                <button type="button" id="Health_Delete" class="btn btn-sm btn-light text-danger fw-semibold"
                    onclick="Health.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Health" style="width:100%; height:170px; background-color:#ffffff; overflow:hidden;"></div>
            <div class="small text-muted px-2 pt-1">
                <i class="fa fa-info-circle me-1"></i> Klik 2x pada sel untuk mengedit Description, Disease, Year, atau Days.
            </div>
        </div>
    </div>

    <!-- Driving Licenses Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-id-card me-2"></i>Driving Licenses (SIM)</h6>
            <div class="d-flex gap-2">
                <button type="button" id="Driving_Licenses_Add" class="btn btn-sm btn-light text-primary fw-semibold"
                    onclick="addDrivingRow()">
                    <i class="fa fa-plus me-1"></i> Add License
                </button>
                <button type="button" id="Driving_Licenses_Delete" class="btn btn-sm btn-light text-danger fw-semibold"
                    onclick="Driving_Licenses.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Driving_Licenses" style="width:100%; height:170px; background-color:#ffffff; overflow:hidden;"></div>
            <div class="small text-muted px-2 pt-1">
                <i class="fa fa-info-circle me-1"></i> Klik 2x pada sel untuk mengisi jenis SIM (mis. SIM A / SIM C), status kepemilikan, dan catatan.
            </div>
        </div>
    </div>

    <!-- Medical & Physical Conditions -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-medkit me-2"></i>Medical &amp; Physical Information</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Height">Height (Tinggi Badan)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="Height" class="form-control" value="{{ $med['Height'] }}" placeholder="170">
                        <span class="input-group-text">cm</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Weight">Weight (Berat Badan)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="Weight" class="form-control" value="{{ $med['Weight'] }}" placeholder="65">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Glasses">Using Glasses / Contact Lens</label>
                    <select id="Glasses" class="form-select form-select-sm">
                        <option value="No" {{ $med['Glasses'] === 'No' ? 'selected' : '' }}>No</option>
                        <option value="Yes" {{ $med['Glasses'] === 'Yes' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Smoker">Smoker</label>
                    <select id="Smoker" class="form-select form-select-sm">
                        <option value="No" {{ $med['Smoker'] === 'No' ? 'selected' : '' }}>No</option>
                        <option value="Yes" {{ $med['Smoker'] === 'Yes' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Longtime">Congenital Disease for Longtime Care?</label>
                    <select id="Longtime" class="form-select form-select-sm" onchange="toggleLongtimeDesc()">
                        <option value="No" {{ $med['Longtime'] === 'No' ? 'selected' : '' }}>No</option>
                        <option value="Yes" {{ $med['Longtime'] === 'Yes' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-muted" for="Longtime_Desc">If Yes, Describe Disease</label>
                    <input type="text" id="Longtime_Desc" class="form-control form-control-sm"
                        value="{{ $med['Longtime_Desc'] }}"
                        placeholder="Nama riwayat penyakit kronis / bawaan"
                        {{ $med['Longtime'] === 'Yes' ? '' : 'disabled' }}>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Action -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="small text-muted">Menyimpan perubahan pada Health History, Driving Licenses, dan Medical Information.</span>
            <button type="button" id="btnSaveOtherData" class="btn btn-primary btn-sm px-4 shadow-sm" onclick="saveOtherData()">
                <i class="fa fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
    var empOtherTaskId = @json($empTaskId);

    function toggleLongtimeDesc() {
        var isYes = $('#Longtime').val() === 'Yes';
        $('#Longtime_Desc').prop('disabled', !isYes);
        if (!isYes) {
            $('#Longtime_Desc').val('');
        }
    }

    function addHealthRow() {
        var newId = Health.uid();
        Health.addRow(newId, [Health.getRowsNum() + 1, '', '', '', ''], 0);
        Health.selectRowById(newId);
    }

    function addDrivingRow() {
        var newId = Driving_Licenses.uid();
        Driving_Licenses.addRow(newId, [Driving_Licenses.getRowsNum() + 1, '', '1', ''], 0);
        Driving_Licenses.selectRowById(newId);
    }

    function showOtherStatus(type, message) {
        var $alert = $('#otherSaveAlert');
        $alert.removeClass('d-none alert-success alert-danger').addClass('alert-' + type).text(message);
    }

    function saveOtherData() {
        var $btn = $('#btnSaveOtherData');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        if (typeof PHealth !== 'undefined') PHealth.sendData();
        if (typeof PDriving_Licenses !== 'undefined') PDriving_Licenses.sendData();

        $.ajax({
            url: '{{ url("employee-lists-detail-other-medical-save") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                Task_ID: empOtherTaskId,
                Height: $('#Height').val(),
                Weight: $('#Weight').val(),
                Glasses: $('#Glasses').val(),
                Smoker: $('#Smoker').val(),
                Longtime: $('#Longtime').val(),
                Longtime_Desc: $('#Longtime_Desc').val()
            },
            success: function(res) {
                showOtherStatus('success', (res && res.message) ? res.message : 'Seluruh data Other Information berhasil disimpan.');
                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Changes');
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data Medical Information.';
                showOtherStatus('danger', msg);
                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Changes');
            }
        });
    }

    Driving_Licenses = new dhtmlXGridObject('Driving_Licenses');
    Driving_Licenses.setImagePath("./dhtmlx/imgs/");
    Driving_Licenses.setHeader("No.,Description,Active,Note");
    Driving_Licenses.setInitWidths("60,320,100,auto");
    Driving_Licenses.setColAlign("center,left,center,left");
    Driving_Licenses.setColSorting("int,str,str,str");
    Driving_Licenses.enableEditEvents(true, false, true);
    Driving_Licenses.setColTypes("ro,ed,ch,txt");
    Driving_Licenses.setEditable(true);
    PDriving_Licenses = new dataProcessor("employee-lists-detail-driving-update?Task_ID=" + encodeURIComponent(empOtherTaskId));
    PDriving_Licenses.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    PDriving_Licenses.init(Driving_Licenses);
    Driving_Licenses.setSkin("dhx_skyblue");
    Driving_Licenses.init();
    Driving_Licenses.loadXML("xml-employee-lists-detail-driving?ID=" + encodeURIComponent(empOtherTaskId));

    Health = new dhtmlXGridObject('Health');
    Health.setImagePath("./dhtmlx/imgs/");
    Health.setHeader("No.,Description,Disease,Year,Days");
    Health.setInitWidths("60,320,200,100,auto");
    Health.setColAlign("center,left,center,center,left");
    Health.setColSorting("int,str,str,str,str");
    Health.setColTypes("ro,ed,ed,ed,ed");
    Health.enableEditEvents(true, false, true);
    Health.setEditable(true);
    PHealth = new dataProcessor("employee-lists-detail-health-update?Task_ID=" + encodeURIComponent(empOtherTaskId));
    PHealth.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    PHealth.init(Health);
    Health.setSkin("dhx_skyblue");
    Health.init();
    Health.loadXML("xml-employee-lists-detail-health?ID=" + encodeURIComponent(empOtherTaskId));

    toggleLongtimeDesc();
</script>
