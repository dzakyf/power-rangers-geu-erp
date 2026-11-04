<div class="main-content-header">
    <h1>FORM PERUBAHAN STATUS KARYAWAN</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
        <li class="breadcrumb-item active"><span class="active">Form Perubahan Status Baru</span></li>
    </ol>
</div>

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold p-3">
        <h5 class="m-0" style="color:azure"><i class="fas fa-user-edit mr-2"></i>Formulir Perubahan Status Karyawan</h5>
    </div>
    <div class="card-body">
        <form id="formStatusChange" method="POST" action="{{ url('hrd-status-change-new-submit') }}" autocomplete="off">

            <!-- Cari Karyawan — autoisi Identitas Karyawan & Data Saat Ini dari tabel employment -->
            <div class="badge badge-info w-100 my-2 py-2">Cari Karyawan (autoisi Identitas &amp; Data Saat Ini)</div>
            <div class="row">
                <div class="col-md-6 position-relative">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Cari Karyawan</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="employee_search" placeholder="Ketik nama atau NIK karyawan..." autocomplete="off" />
                            <div id="employee_search_results" class="list-group position-absolute w-100 shadow-sm" style="z-index:1000; max-height:250px; overflow-y:auto; display:none; top:calc(100% - 1px);"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jenis Pengajuan -->
            <div class="badge badge-info w-100 my-2 py-2">Jenis Pengajuan</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Jenis Perubahan</label>
                        <div class="col-md-7">
                            <select class="form-control form-control-sm" id="jenis">
                                <option value="">Deteksi Otomatis (naik gaji/grade = Promosi, selain itu Mutasi)</option>
                                <option value="MUTATION" {{ $jenisDefault == 'MUTATION' ? 'selected' : '' }}>MUTATION (Mutasi)</option>
                                <option value="PROMOTION" {{ $jenisDefault == 'PROMOTION' ? 'selected' : '' }}>PROMOTION (Promosi)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identitas Karyawan -->
            <div class="badge badge-info w-100 my-2 py-2">Identitas Karyawan</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Nama Karyawan <span class="text-danger">*</span></label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="employee_name" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Tanggal Join</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="join_date" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Nomor Induk Karyawan (NIK) <span class="text-danger">*</span></label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="nik" />
                        </div>
                    </div>
                </div>
            </div>

            @php $comparerFields = ['position' => 'Jabatan', 'division' => 'Divisi', 'department' => 'Departemen', 'grade' => 'Grade', 'work_location' => 'Lokasi Kerja', 'employment_status' => 'Status Kekaryawanan']; @endphp

            @foreach([['prefix' => 'current', 'heading' => 'Data Saat Ini'], ['prefix' => 'proposed', 'heading' => 'Data Usulan']] as $section)
            <div class="badge badge-info w-100 my-2 py-2">{{ $section['heading'] }}</div>
            <div class="row">
                <div class="col-md-6">
                    @foreach(array_slice($comparerFields, 0, 3, true) as $field => $label)
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">{{ $label }}</label>
                        <div class="col-md-7">
                            @if($section['prefix'] == 'proposed' && $field == 'position')
                            <!-- Jabatan usulan: dari master_position; pilih otomatis isi Divisi & Departemen -->
                            <select class="form-control form-control-sm" id="proposed_position">
                                <option value="">-- Pilih Jabatan (master_position) --</option>
                                @foreach($positions as $mp)
                                <option value="{{ $mp->Position }}" data-department="{{ $mp->Department }}" data-division="{{ $mp->Directorat }}">{{ $mp->Department }} · {{ $mp->Position }} ({{ $mp->Initial }})</option>
                                @endforeach
                            </select>
                            @elseif($section['prefix'] == 'proposed' && $field == 'division')
                            <!-- Divisi usulan: daftar Directorat dari master_position -->
                            <select class="form-control form-control-sm" id="proposed_division">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisions as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                            @elseif($section['prefix'] == 'proposed' && $field == 'department')
                            <!-- Departemen usulan: daftar Department dari master_position -->
                            <select class="form-control form-control-sm" id="proposed_department">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="text" class="form-control form-control-sm" id="{{ $section['prefix'] }}_{{ $field }}" />
                            @endif
                        </div>
                    </div>
                    @endforeach
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Status Perkawinan &amp; Tanggungan</label>
                        <div class="col-md-7">
                            <select class="form-control form-control-sm" id="{{ $section['prefix'] }}_marital_dependents">
                                <option value="">-- Pilih --</option>
                                @foreach($maritalOptions as $value => $labelOption)
                                <option value="{{ $value }}">{{ $labelOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @foreach(array_slice($comparerFields, 3, 3, true) as $field => $label)
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">{{ $label }}</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="{{ $section['prefix'] }}_{{ $field }}" />
                        </div>
                    </div>
                    @endforeach
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Salary (Rp)</label>
                        <div class="col-md-7">
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm" id="{{ $section['prefix'] }}_salary" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Benefit</label>
                        <div class="col-md-7">
                            <textarea class="form-control form-control-sm" id="{{ $section['prefix'] }}_benefit" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Deskripsi</label>
                        <div class="col-md-7">
                            <textarea class="form-control form-control-sm" id="{{ $section['prefix'] }}_description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Perubahan Berlaku Sejak -->
            <div class="badge badge-info w-100 my-2 py-2">Perubahan Berlaku Sejak</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Tanggal Berlaku</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="effective_date" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Diusulkan oleh</label>
                        <div class="col-md-7 position-relative">
                            <input type="hidden" id="proposed_by" value="{{ $proposerDefault }}" />
                            <input type="text" class="form-control form-control-sm" id="proposed_by_display" value="{{ $proposerDefault }}" readonly title="Hasil pilihan user — tidak bisa diketik bebas" />
                            <input type="text" class="form-control form-control-sm mt-1" id="proposer_search" placeholder="Ganti pengusul: cari nama / username / NIK..." autocomplete="off" />
                            <div id="proposer_search_results" class="list-group position-absolute w-100 shadow-sm" style="z-index:1000; max-height:250px; overflow-y:auto; display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alur Persetujuan -->
            <div class="badge badge-info w-100 my-2 py-2">Alur Persetujuan (Otomatis dari Approval Policy Engine)</div>
            <div class="alert alert-light border small mb-3">
                <i class="fas fa-info-circle text-primary me-1"></i>
                Persetujuan (Dept. Head Asal, Dept. Head Tujuan, Verifikator HCGA, dan Direktur) akan <strong>otomatis ditentukan dan dicatat oleh Approval Engine</strong> sesuai dengan kebijakan organisasi dan siapa yang memberikan approval. Anda tidak perlu menginput nama approver secara manual.
            </div>

            <hr />
            <div>
                <button type="button" id="SaveDraftStatusChange" class="btn btn-primary px-4 me-2">
                    <i class="fas fa-save me-1"></i> Simpan Sebagai Draft
                </button>
                <button type="button" id="SubmitStatusChange" class="btn btn-success px-4 me-2">
                    <i class="fas fa-paper-plane me-1"></i> Simpan &amp; Langsung Ajukan Approval
                </button>
                <button type="button" class="btn btn-secondary px-3" onclick="window.location.href='hrd-status-change-index{{ $jenisDefault ? '?jenis=' . $jenisDefault : '' }}'">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="DivProcess"></div>

<script>
    $(function() {
        var scCal = new dhtmlXCalendarObject(["join_date", "effective_date"]);
        scCal.setDateFormat("%Y-%m-%d");
    });

    // -- Pencarian karyawan: isi langsung Identitas Karyawan & Data Saat Ini --
    var scSearchTimer = null;

    $('#employee_search').on('keyup', function() {
        var q = $(this).val().trim();
        clearTimeout(scSearchTimer);
        if (q.length < 2) {
            $('#employee_search_results').empty().hide();
            return;
        }
        scSearchTimer = setTimeout(function() {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                url: 'hrd-status-change-employee-search?q=' + encodeURIComponent(q),
                type: 'GET',
                success: function(res) {
                    var box = $('#employee_search_results').empty();
                    if (!res.data || !res.data.length) {
                        box.append($('<span class="list-group-item text-muted small">').text('Tidak ada karyawan yang cocok.')).show();
                        return;
                    }
                    $.each(res.data, function(i, e) {
                        box.append(
                            $('<a href="javascript:void(0)" class="list-group-item list-group-item-action py-1">')
                                .text(e.NIK + ' — ' + e.Name + (e.Position ? ' · ' + e.Position : ''))
                                .on('mousedown', function(ev) {
                                    ev.preventDefault();
                                    SCFill(e);
                                    $('#employee_search').val('');
                                    box.hide();
                                })
                        );
                    });
                    box.show();
                },
                error: function(xhr) {
                    alert('Pencarian karyawan gagal: ' + (xhr.responseText || xhr.statusText));
                }
            });
        }, 300);
    });

    $('#employee_search').on('blur', function() {
        setTimeout(function() { $('#employee_search_results').hide(); }, 200);
    });

    function SCFill(e) {
        $('#employee_name').val(e.Name || '');
        $('#nik').val(e.NIK || '');
        $('#join_date').val(e.join_date || '');
        $('#current_position').val(e.Position || '');
        $('#current_division').val(e.Division || '');
        $('#current_department').val(e.Department || '');
        $('#current_grade').val(e.Employment_Grade || '');
        $('#current_work_location').val(e.Location || '');
        $('#current_marital_dependents').val(e.Marital_Status || '').change();
        $('#current_employment_status').val(e.Employment_Class || e.Employment_Status || '');
        $('#current_salary').val(e.Salary === 0 ? '' : (e.Salary || ''));
    }

    // -- Pengusul: dipilih dari users/employment (bukan teks bebas), nilai "Nama (username)" --
    var scProposerTimer = null;

    $('#proposer_search').on('keyup', function() {
        var q = $(this).val().trim();
        clearTimeout(scProposerTimer);
        if (q.length < 2) {
            $('#proposer_search_results').empty().hide();
            return;
        }
        scProposerTimer = setTimeout(function() {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                url: 'hrd-status-change-proposer-search?q=' + encodeURIComponent(q),
                type: 'GET',
                success: function(res) {
                    var box = $('#proposer_search_results').empty();
                    if (!res.data || !res.data.length) {
                        box.append($('<span class="list-group-item text-muted small">').text('Tidak ada user yang cocok.')).show();
                        return;
                    }
                    $.each(res.data, function(i, e) {
                        box.append(
                            $('<a href="javascript:void(0)" class="list-group-item list-group-item-action py-1">')
                                .text(e.label + (e.position ? ' · ' + e.position : '') + (e.department ? ' · ' + e.department : ''))
                                .on('mousedown', function(ev) {
                                    ev.preventDefault();
                                    $('#proposed_by').val(e.label);
                                    $('#proposed_by_display').val(e.label);
                                    $('#proposer_search').val('');
                                    box.hide();
                                })
                        );
                    });
                    box.show();
                },
                error: function(xhr) {
                    alert('Pencarian pengusul gagal: ' + (xhr.responseText || xhr.statusText));
                }
            });
        }, 300);
    });

    $('#proposer_search').on('blur', function() {
        setTimeout(function() { $('#proposer_search_results').hide(); }, 200);
    });

    // -- Jabatan usulan secara otomatis menarik Divisi & Departemen dari opsi (data-department / data-division) --
    $('#proposed_position').on('change', function() {
        var $opt = $(this).find('option:selected');
        $('#proposed_department').val($opt.data('department') || '');
        $('#proposed_division').val($opt.data('division') || '');
    });

    function doSubmitStatusChange(isDirectSubmit) {
        if ($('#employee_name').val().trim() == '') {
            alert('Nama karyawan wajib diisi!');
            $('#employee_name').focus();
            return false;
        }
        if ($('#nik').val().trim() == '') {
            alert('NIK wajib diisi!');
            $('#nik').focus();
            return false;
        }

        var loadingMsg = isDirectSubmit ? 'Menyimpan & mengajukan approval...' : 'Menyimpan draft data...';
        $.blockUI({ message: '<div class="small">' + loadingMsg + '</div>' });

        var values = ['_token={{ csrf_token() }}'];
        if (isDirectSubmit) {
            values.push('direct_submit=1');
        }

        $('#formStatusChange :input').each(function() {
            if ($(this).attr('id') == 'SubmitStatusChange' || $(this).attr('id') == 'SaveDraftStatusChange') return;
            var type = $(this).attr('type');
            if ((type == 'checkbox' || type == 'radio') && this.checked) {
                values.push($(this).attr('id') + '=' + encodeURIComponent($(this).val()));
            } else if (type != 'submit' && type != 'button') {
                values.push($(this).attr('id') + '=' + encodeURIComponent($(this).val()));
            }
        });

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: 'hrd-status-change-new-submit',
            type: 'POST',
            data: values.join('&'),
            success: function(data) {
                $.unblockUI();
                if (data.status == 'success') {
                    alert('Nomor otomatis: ' + (data.form_number || '') + '\n\n' + (data.message || 'Berhasil disimpan.'));
                    var jenis = data.jenis || '{{ $jenisDefault }}';
                    window.location.href = 'hrd-status-change-index' + (jenis ? '?jenis=' + encodeURIComponent(jenis) : '');
                } else {
                    alert(data.message || 'Terjadi kesalahan!');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                $.unblockUI();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || errorThrown);
                alert('Error: ' + msg);
            }
        });

        return false;
    }

    $('#SaveDraftStatusChange').on('click', function(e) {
        e.preventDefault();
        return doSubmitStatusChange(false);
    });

    $('#SubmitStatusChange').on('click', function(e) {
        e.preventDefault();
        return doSubmitStatusChange(true);
    });
</script>
