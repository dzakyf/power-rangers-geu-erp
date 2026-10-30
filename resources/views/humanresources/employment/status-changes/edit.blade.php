@csrf
<div class="main-content-header">
    <h1>FORM PERUBAHAN STATUS KARYAWAN</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
        <li class="breadcrumb-item active"><span class="active">Edit Form Perubahan Status {{ $sc->form_number ?: '#' . $sc->id }}</span></li>
    </ol>
</div>

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold p-3">
        <h5 class="m-0" style="color:azure"><i class="fas fa-user-edit mr-2"></i>Edit Formulir Perubahan Status Karyawan</h5>
    </div>
    <div class="card-body">
        <form id="formStatusChange" method="POST" action="{{ url('hrd-status-change-edit-submit') }}" autocomplete="off">
            <input type="hidden" id="ID" name="ID" value="{{ $sc->id }}" />

            <!-- Nomor Form & Jenis -->
            <div class="badge badge-info w-100 my-2 py-2">Form &amp; Jenis Pengajuan</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Nomor Form</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="form_number_display" value="{{ $sc->form_number ?: '(dibuat otomatis)' }}" readonly />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Jenis Perubahan</label>
                        <div class="col-md-7">
                            <select class="form-control form-control-sm" id="jenis">
                                <option value="">Deteksi Otomatis (naik gaji/grade = Promosi, selain itu Mutasi)</option>
                                <option value="MUTATION" {{ ($sc->jenis ?: 'MUTATION') == 'MUTATION' ? 'selected' : '' }}>MUTATION (Mutasi)</option>
                                <option value="PROMOTION" {{ ($sc->jenis ?: 'MUTATION') == 'PROMOTION' ? 'selected' : '' }}>PROMOTION (Promosi)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cari Karyawan — isi ulang Identitas Karyawan & Data Saat Ini dari tabel employment -->
            <div class="badge badge-info w-100 my-2 py-2">Cari Karyawan (isi ulang Identitas &amp; Data Saat Ini)</div>
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

            <!-- Identitas Karyawan -->
            <div class="badge badge-info w-100 my-2 py-2">Identitas Karyawan</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Nama Karyawan <span class="text-danger">*</span></label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="employee_name" value="{{ $sc->employee_name }}" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Tanggal Join</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="join_date" value="{{ $sc->join_date ? \Carbon\Carbon::parse($sc->join_date)->format('Y-m-d') : '' }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Nomor Induk Karyawan (NIK) <span class="text-danger">*</span></label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="nik" value="{{ $sc->nik }}" />
                        </div>
                    </div>
                </div>
            </div>

            @php
                $comparerFields = ['position' => 'Jabatan', 'division' => 'Divisi', 'department' => 'Departemen', 'grade' => 'Grade', 'work_location' => 'Lokasi Kerja', 'employment_status' => 'Status Kekaryawanan'];
            @endphp

            @foreach([['prefix' => 'current', 'heading' => 'Data Saat Ini'], ['prefix' => 'proposed', 'heading' => 'Data Usulan']] as $section)
            <div class="badge badge-info w-100 my-2 py-2">{{ $section['heading'] }}</div>
            <div class="row">
                <div class="col-md-6">
                    @foreach(array_slice($comparerFields, 0, 3, true) as $field => $label)
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">{{ $label }}</label>
                        <div class="col-md-7">
                            @if($section['prefix'] == 'proposed' && $field == 'position')
                            <select class="form-control form-control-sm" id="proposed_position">
                                <option value="">-- Pilih Jabatan (master_position) --</option>
                                @if(!empty($sc->proposed_position) && !collect($positions)->firstWhere('Position', (string) $sc->proposed_position))
                                <option value="{{ $sc->proposed_position }}" selected>{{ $sc->proposed_position }} (nilai tersimpan — tidak ada di master)</option>
                                @endif
                                @foreach($positions as $mp)
                                <option value="{{ $mp->Position }}" data-department="{{ $mp->Department }}" data-division="{{ $mp->Directorat }}" {{ $sc->proposed_position == $mp->Position ? 'selected' : '' }}>{{ $mp->Department }} · {{ $mp->Position }} ({{ $mp->Initial }})</option>
                                @endforeach
                            </select>
                            @elseif($section['prefix'] == 'proposed' && $field == 'division')
                            <select class="form-control form-control-sm" id="proposed_division">
                                <option value="">-- Pilih Divisi --</option>
                                @if(!empty($sc->proposed_division) && !in_array($sc->proposed_division, $divisions, true))
                                <option value="{{ $sc->proposed_division }}" selected>{{ $sc->proposed_division }} (nilai tersimpan)</option>
                                @endif
                                @foreach($divisions as $d)
                                <option value="{{ $d }}" {{ $sc->proposed_division == $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                            @elseif($section['prefix'] == 'proposed' && $field == 'department')
                            <select class="form-control form-control-sm" id="proposed_department">
                                <option value="">-- Pilih Departemen --</option>
                                @if(!empty($sc->proposed_department) && !in_array($sc->proposed_department, $departments, true))
                                <option value="{{ $sc->proposed_department }}" selected>{{ $sc->proposed_department }} (nilai tersimpan)</option>
                                @endif
                                @foreach($departments as $d)
                                <option value="{{ $d }}" {{ $sc->proposed_department == $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="text" class="form-control form-control-sm" id="{{ $section['prefix'] }}_{{ $field }}" value="{{ $sc->{$section['prefix'] . '_' . $field} }}" />
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
                                <option value="{{ $value }}" {{ $sc->{$section['prefix'] . '_marital_dependents'} == $value ? 'selected' : '' }}>{{ $labelOption }}</option>
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
                            <input type="text" class="form-control form-control-sm" id="{{ $section['prefix'] }}_{{ $field }}" value="{{ $sc->{$section['prefix'] . '_' . $field} }}" />
                        </div>
                    </div>
                    @endforeach
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Salary (Rp)</label>
                        <div class="col-md-7">
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm" id="{{ $section['prefix'] }}_salary" value="{{ $sc->{$section['prefix'] . '_salary'} === null ? '0' : $sc->{$section['prefix'] . '_salary'} }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Benefit</label>
                        <div class="col-md-7">
                            <textarea class="form-control form-control-sm" id="{{ $section['prefix'] }}_benefit" rows="2">{{ $sc->{$section['prefix'] . '_benefit'} }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Deskripsi</label>
                        <div class="col-md-7">
                            <textarea class="form-control form-control-sm" id="{{ $section['prefix'] }}_description" rows="2">{{ $sc->{$section['prefix'] . '_description'} }}</textarea>
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
                            <input type="text" class="form-control form-control-sm" id="effective_date" value="{{ $sc->effective_date ? \Carbon\Carbon::parse($sc->effective_date)->format('Y-m-d') : '' }}" />
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

            <!-- Persetujuan -->
            <div class="badge badge-info w-100 my-2 py-2">Persetujuan</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Dept. Head Departemen Asal</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="origin_department_head" value="{{ $sc->origin_department_head }}" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Diverifikasi — Senior Manager HCGA</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="hcga_verifier" value="{{ $sc->hcga_verifier }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Dept. Head Departemen Tujuan</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="destination_department_head" value="{{ $sc->destination_department_head }}" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label col-form-label-sm">Mengetahui — Direktur</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" id="director_approver" value="{{ $sc->director_approver }}" />
                        </div>
                    </div>
                </div>
            </div>

            <hr />
            <div>
                <input type="submit" id="SubmitStatusChange" class="btn btn-success px-4" name="SubmitStatusChange" value="Simpan Perubahan" />
                <button type="button" class="btn btn-secondary px-3 ml-2" onclick="window.location.href='hrd-status-change-index'">Batal</button>
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
        $('#current_employment_status').val(e.Employment_Class || e.Employment_Status || '');
        $('#current_salary').val(e.Salary === 0 ? '' : (e.Salary || ''));
    }

    // -- Jabatan usulan: pilih dari master_position otomatis mengisi Divisi & Departemen --
    $('#proposed_position').on('change', function() {
        var $opt = $(this).find('option:selected');
        $('#proposed_department').val($opt.data('department') || '');
        $('#proposed_division').val($opt.data('division') || '');
    });

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

    $('#SubmitStatusChange').on('click', function(e) {
        e.preventDefault();

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

        $.blockUI({ message: '<div class="small">Menyimpan perubahan...</div>' });

        var values = ['_token={{ csrf_token() }}'];
        $('#formStatusChange :input').each(function() {
            if ($(this).attr('name') == 'SubmitStatusChange') return;
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
            url: 'hrd-status-change-edit-submit',
            type: 'POST',
            data: values.join('&'),
            success: function(data) {
                $.unblockUI();
                if (data.status == 'success') {
                    alert('Form perubahan status berhasil diperbarui!');
                    var scJenis = $('#jenis').val();
                    window.location.href = 'hrd-status-change-index' + (scJenis ? '?jenis=' + encodeURIComponent(scJenis) : '');
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
    });
</script>