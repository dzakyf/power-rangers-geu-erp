<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $tna ? 'Edit TNA: ' . $tna->tna_no : 'Form Pengajuan TNA Baru' }}</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divTnaContainer').html('')">Tutup</button>
    </div>
    <div class="card-body">
        <form id="formTna">
            @csrf
            <input type="hidden" name="id" value="{{ $tna->id ?? '' }}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Departemen *</label>
                    <select name="department" class="form-control" required>
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ (($tna->department ?? auth()->user()->Department) == $dept) ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Tahun *</label>
                    <input type="text" name="fiscal_year" class="form-control" value="{{ $tna->fiscal_year ?? date('Y') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Judul Kebutuhan Training *</label>
                    <input type="text" name="title" class="form-control" value="{{ $tna->title ?? '' }}" placeholder="Misal: Peningkatan Kompetensi K3 & SOP Operasional" required>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Latar Belakang / Analisa Kebutuhan</label>
                    <textarea name="background" class="form-control" rows="2">{{ $tna->background ?? '' }}</textarea>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Daftar Karyawan &amp; Gap Kompetensi</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addTnaItemRow()">+ Tambah Baris</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="tblTnaItems">
                    <thead class="table-light">
                        <tr>
                            <th>Karyawan</th>
                            <th>Gap Kompetensi</th>
                            <th>Rekomendasi Training</th>
                            <th>Program (Opsional)</th>
                            <th>Prioritas</th>
                            <th>Target Kuartal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $existingItems = $tna ? $tna->items : collect([]); @endphp
                        @forelse($existingItems as $idx => $item)
                            <tr>
                                <td>
                                    <select name="items[{{ $idx }}][employee_username]" class="form-control form-control-sm tna-emp-select" onchange="syncTnaEmpName(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->username }}" data-name="{{ $emp->name }}" data-pos="{{ $emp->Position }}" {{ $item->employee_username == $emp->username ? 'selected' : '' }}>
                                                {{ $emp->name }} ({{ $emp->Department }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[{{ $idx }}][employee_name]" class="tna-emp-name" value="{{ $item->employee_name }}">
                                    <input type="hidden" name="items[{{ $idx }}][position_name]" class="tna-emp-pos" value="{{ $item->position_name }}">
                                </td>
                                <td><input type="text" name="items[{{ $idx }}][competency_gap]" class="form-control form-control-sm" value="{{ $item->competency_gap }}" required></td>
                                <td><input type="text" name="items[{{ $idx }}][recommended_training]" class="form-control form-control-sm" value="{{ $item->recommended_training }}" required></td>
                                <td>
                                    <select name="items[{{ $idx }}][program_id]" class="form-control form-control-sm">
                                        <option value="">-- Bebas --</option>
                                        @foreach($programs as $prg)
                                            <option value="{{ $prg->id }}" {{ $item->program_id == $prg->id ? 'selected' : '' }}>{{ $prg->program_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $idx }}][priority]" class="form-control form-control-sm">
                                        @foreach(['HIGH','MEDIUM','LOW'] as $prio)
                                            <option value="{{ $prio }}" {{ $item->priority == $prio ? 'selected' : '' }}>{{ $prio }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $idx }}][target_quarter]" class="form-control form-control-sm">
                                        @foreach(['Q1','Q2','Q3','Q4'] as $q)
                                            <option value="{{ $q }}" {{ $item->target_quarter == $q ? 'selected' : '' }}>{{ $q }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">x</button></td>
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    <select name="items[0][employee_username]" class="form-control form-control-sm tna-emp-select" onchange="syncTnaEmpName(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->username }}" data-name="{{ $emp->name }}" data-pos="{{ $emp->Position }}">
                                                {{ $emp->name }} ({{ $emp->Department }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[0][employee_name]" class="tna-emp-name" value="">
                                    <input type="hidden" name="items[0][position_name]" class="tna-emp-pos" value="">
                                </td>
                                <td><input type="text" name="items[0][competency_gap]" class="form-control form-control-sm" placeholder="Contoh: Leadership / K3" required></td>
                                <td><input type="text" name="items[0][recommended_training]" class="form-control form-control-sm" placeholder="Nama Pelatihan" required></td>
                                <td>
                                    <select name="items[0][program_id]" class="form-control form-control-sm">
                                        <option value="">-- Bebas --</option>
                                        @foreach($programs as $prg)
                                            <option value="{{ $prg->id }}">{{ $prg->program_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][priority]" class="form-control form-control-sm">
                                        <option value="HIGH">HIGH</option>
                                        <option value="MEDIUM" selected>MEDIUM</option>
                                        <option value="LOW">LOW</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][target_quarter]" class="form-control form-control-sm">
                                        <option value="Q1">Q1</option>
                                        <option value="Q2">Q2</option>
                                        <option value="Q3">Q3</option>
                                        <option value="Q4">Q4</option>
                                    </select>
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">x</button></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Simpan TNA (Draft)</button>
            </div>
        </form>
    </div>
</div>

<script>
    var tnaItemIndex = {{ max(1, $existingItems->count()) }};

    function syncTnaEmpName(selectEl) {
        var opt = $(selectEl).find('option:selected');
        var tr = $(selectEl).closest('tr');
        tr.find('.tna-emp-name').val(opt.data('name') || '');
        tr.find('.tna-emp-pos').val(opt.data('pos') || '');
    }

    function addTnaItemRow() {
        var firstRow = $('#tblTnaItems tbody tr:first').clone();
        firstRow.find('input, select').each(function() {
            var name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + tnaItemIndex + ']'));
            }
            if ($(this).is('input')) {
                $(this).val('');
            }
        });
        $('#tblTnaItems tbody').append(firstRow);
        tnaItemIndex++;
    }

    $('#formTna').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-tna-store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message || 'TNA tersimpan.');
                refreshTnaGrid();
                LoadM('divTnaContainer', 'hrd-tna-detail?id=' + res.data.id);
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan TNA.');
            }
        });
    });
</script>
