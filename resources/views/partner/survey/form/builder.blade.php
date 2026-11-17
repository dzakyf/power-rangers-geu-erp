@extends('layout.mainlayout-bp')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Pengaturan</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Pengaturan</a></li>
        <li class="breadcrumb-item"><a href="#">Survey</a></li>
        <li class="breadcrumb-item"><a href="{{ url('partner-survey-form-index') }}">Form Builder</a></li>
        <li class="breadcrumb-item active"><span class="active">Editor</span></li>
    </ol>
</div>

@php
    $decode = function ($config) {
        if (is_array($config)) return $config;
        $decoded = is_string($config) ? json_decode($config, true) : [];
        return is_array($decoded) ? $decoded : [];
    };
    $fBuckets = $config['f_buckets'] ?? [];
    if (!$fBuckets) {
        $fBuckets = [
            ['gt' => 15, 'score' => 15],
            ['gte' => 10, 'score' => 12],
            ['gte' => 6, 'score' => 8],
            ['gte' => 4, 'score' => 5],
            ['gte' => 0, 'score' => 2],
        ];
    }
@endphp

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Editor Form — v{{ $version->Version_No }} ({{ $version->Status }})</h5>
        <a href="{{ url('partner-survey-form-index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <form action="{{ url('partner-survey-form-save') }}" method="POST" id="builderForm">
        @csrf
        <input type="hidden" name="version_id" value="{{ $version->ID }}">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Form</label>
                    <input type="text" name="name" class="form-control" value="{{ $version->Name }}" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Pass Threshold (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="pass_threshold" id="pass_threshold" class="form-control" value="{{ $version->Pass_Threshold_Pct }}" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Conditional (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="conditional_threshold" id="conditional_threshold" class="form-control" value="{{ $version->Conditional_Threshold_Pct }}" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Max Score F</label>
                    <input type="number" step="0.01" min="0" name="f_max" id="f_max" class="form-control" value="{{ $config['f_max'] ?? 15 }}" required>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Bucket Skor F (Estimasi UCO)</div>
            <div class="card-body">
                <table class="table table-sm table-bordered" id="bucketTable">
                    <thead><tr><th>Min / Lebih Dari</th><th>Operator</th><th>Skor</th><th></th></tr></thead>
                    <tbody>
                        @foreach($fBuckets as $bucket)
                            <tr class="bucket-row">
                                <td><input type="number" step="0.01" class="form-control bucket-value" value="{{ $bucket['gt'] ?? $bucket['gte'] ?? 0 }}"></td>
                                <td>
                                    <select class="form-control bucket-op">
                                        <option value="gt" {{ isset($bucket['gt']) ? 'selected' : '' }}>&gt;</option>
                                        <option value="gte" {{ !isset($bucket['gt']) ? 'selected' : '' }}>&gt;=</option>
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" class="form-control bucket-score" value="{{ $bucket['score'] ?? 0 }}"></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-bucket">Hapus</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addBucket">Tambah Bucket</button>
                <input type="hidden" name="f_buckets" id="f_buckets">
            </div>
        </div>

        @foreach($sections as $sectionCode => $sectionLabel)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $sectionLabel }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-primary add-row" data-section="{{ $sectionCode }}">Tambah Baris</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm item-table" data-section="{{ $sectionCode }}">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th style="width:90px">Kode</th>
                                @if($sectionCode === 'F')
                                    <th>Sumber / Kriteria</th>
                                @else
                                    <th>Kriteria</th>
                                    <th>Keterangan</th>
                                    @if(in_array($sectionCode, ['D', 'E']))<th>Parameter Penilaian</th>@endif
                                    @if($sectionCode === 'D')<th>Standar Minimum</th>@endif
                                    @if(in_array($sectionCode, ['C', 'D']))
                                        <th style="width:90px">Skor Ya</th>
                                        <th style="width:90px">Skor Tidak</th>
                                    @elseif($sectionCode === 'E')
                                        <th style="width:320px">Opsi Penilaian</th>
                                    @endif
                                    @if(in_array($sectionCode, ['C', 'D']))
                                        <th style="width:80px">File</th>
                                        <th style="width:80px">Catatan</th>
                                    @endif
                                @endif
                                <th style="width:130px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($items[$sectionCode] ?? collect()) as $item)
                                @php
                                    $cfg = $decode($item->Score_Config);
                                    $options = $cfg['options'] ?? [];
                                    if ($sectionCode === 'E' && !$options) {
                                        $options = [
                                            ['label' => 'Kurang', 'value' => 1, 'score' => 1],
                                            ['label' => 'Sedang', 'value' => 2, 'score' => 3],
                                            ['label' => 'Tinggi', 'value' => 3, 'score' => 5],
                                        ];
                                    }
                                @endphp
                                <tr class="item-row">
                                    <td class="row-number">{{ $loop->iteration }}</td>
                                    <td>
                                        <input type="text" name="items[{{ $item->ID }}][item_code]" class="form-control item-code" value="{{ $item->Item_Code }}" required>
                                        <input type="hidden" name="items[{{ $item->ID }}][id]" value="{{ $item->ID }}">
                                        <input type="hidden" name="items[{{ $item->ID }}][section_code]" value="{{ $sectionCode }}">
                                        <input type="hidden" name="items[{{ $item->ID }}][input_type]" value="{{ $item->Input_Type }}">
                                    </td>
                                    <td><input type="text" name="items[{{ $item->ID }}][criteria]" class="form-control" value="{{ $item->Criteria }}"></td>
                                    @if($sectionCode !== 'F')
                                        <td><textarea name="items[{{ $item->ID }}][keterangan]" class="form-control" rows="3">{{ $item->Keterangan }}</textarea></td>
                                        @if(in_array($sectionCode, ['D', 'E']))
                                            <td><textarea name="items[{{ $item->ID }}][parameter_evaluation]" class="form-control" rows="3">{{ $item->Parameter_Evaluation }}</textarea></td>
                                        @endif
                                        @if($sectionCode === 'D')
                                            <td><textarea name="items[{{ $item->ID }}][minimum_standard]" class="form-control" rows="3">{{ $item->Minimum_Standard }}</textarea></td>
                                        @endif
                                        @if(in_array($sectionCode, ['C', 'D']))
                                            <td><input type="number" step="0.01" name="items[{{ $item->ID }}][yes_score]" class="form-control" value="{{ $cfg['yes'] ?? 0 }}"></td>
                                            <td><input type="number" step="0.01" name="items[{{ $item->ID }}][no_score]" class="form-control" value="{{ $cfg['no'] ?? 0 }}"></td>
                                            <td class="text-center"><input type="checkbox" name="items[{{ $item->ID }}][file_required]" value="1" {{ $item->File_Required ? 'checked' : '' }}></td>
                                            <td class="text-center"><input type="checkbox" name="items[{{ $item->ID }}][notes_enabled]" value="1" {{ $item->Notes_Enabled ? 'checked' : '' }}></td>
                                        @elseif($sectionCode === 'E')
                                            <td>
                                                <div class="option-list">
                                                    @foreach($options as $option)
                                                        <div class="option-row input-group input-group-sm mb-1">
                                                            <input type="text" name="items[{{ $item->ID }}][options][{{ $loop->index }}][label]" class="form-control" value="{{ $option['label'] ?? '' }}" placeholder="Label">
                                                            <input type="number" name="items[{{ $item->ID }}][options][{{ $loop->index }}][value]" class="form-control" value="{{ $option['value'] ?? ($loop->index + 1) }}" placeholder="Nilai">
                                                            <input type="number" step="0.01" name="items[{{ $item->ID }}][options][{{ $loop->index }}][score]" class="form-control" value="{{ $option['score'] ?? 0 }}" placeholder="Skor">
                                                            <div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-option">x</button></div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-secondary add-option">Tambah Opsi</button>
                                            </td>
                                        @endif
                                    @endif
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary move-up">↑</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary move-down">↓</button>
                                        <button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center">
            <div>Max score saat ini: <strong id="maxScorePreview">{{ $version->Max_Score }}</strong></div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>

    @if($version->Status !== 'ACTIVE')
        <form action="{{ url('partner-survey-form-publish') }}" method="POST" class="mt-3" onsubmit="return confirm('Publish versi ini sebagai form aktif?');">
            @csrf
            <input type="hidden" name="version_id" value="{{ $version->ID }}">
            <button type="submit" class="btn btn-success">Publish Form Ini</button>
        </form>
    @endif
</div>

<template id="template-C">
    <tr class="item-row">
        <td class="row-number"></td>
        <td><input type="text" name="items[NEWKEY][item_code]" class="form-control item-code" required><input type="hidden" name="items[NEWKEY][id]" value="0"><input type="hidden" name="items[NEWKEY][section_code]" value="C"><input type="hidden" name="items[NEWKEY][input_type]" value="boolean_photo"></td>
        <td><input type="text" name="items[NEWKEY][criteria]" class="form-control"></td>
        <td><textarea name="items[NEWKEY][keterangan]" class="form-control" rows="3"></textarea></td>
        <td><input type="number" step="0.01" name="items[NEWKEY][yes_score]" class="form-control" value="0"></td>
        <td><input type="number" step="0.01" name="items[NEWKEY][no_score]" class="form-control" value="0"></td>
        <td class="text-center"><input type="checkbox" name="items[NEWKEY][file_required]" value="1"></td>
        <td class="text-center"><input type="checkbox" name="items[NEWKEY][notes_enabled]" value="1"></td>
        <td><button type="button" class="btn btn-sm btn-outline-secondary move-up">↑</button><button type="button" class="btn btn-sm btn-outline-secondary move-down">↓</button><button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button></td>
    </tr>
</template>
<template id="template-D">
    <tr class="item-row">
        <td class="row-number"></td>
        <td><input type="text" name="items[NEWKEY][item_code]" class="form-control item-code" required><input type="hidden" name="items[NEWKEY][id]" value="0"><input type="hidden" name="items[NEWKEY][section_code]" value="D"><input type="hidden" name="items[NEWKEY][input_type]" value="boolean_checklist"></td>
        <td><input type="text" name="items[NEWKEY][criteria]" class="form-control"></td>
        <td><textarea name="items[NEWKEY][keterangan]" class="form-control" rows="3"></textarea></td>
        <td><textarea name="items[NEWKEY][parameter_evaluation]" class="form-control" rows="3"></textarea></td>
        <td><textarea name="items[NEWKEY][minimum_standard]" class="form-control" rows="3"></textarea></td>
        <td><input type="number" step="0.01" name="items[NEWKEY][yes_score]" class="form-control" value="0"></td>
        <td><input type="number" step="0.01" name="items[NEWKEY][no_score]" class="form-control" value="0"></td>
        <td class="text-center"><input type="checkbox" name="items[NEWKEY][file_required]" value="1"></td>
        <td class="text-center"><input type="checkbox" name="items[NEWKEY][notes_enabled]" value="1" checked></td>
        <td><button type="button" class="btn btn-sm btn-outline-secondary move-up">↑</button><button type="button" class="btn btn-sm btn-outline-secondary move-down">↓</button><button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button></td>
    </tr>
</template>
<template id="template-E">
    <tr class="item-row">
        <td class="row-number"></td>
        <td><input type="text" name="items[NEWKEY][item_code]" class="form-control item-code" required><input type="hidden" name="items[NEWKEY][id]" value="0"><input type="hidden" name="items[NEWKEY][section_code]" value="E"><input type="hidden" name="items[NEWKEY][input_type]" value="rating"></td>
        <td><input type="text" name="items[NEWKEY][criteria]" class="form-control"></td>
        <td><textarea name="items[NEWKEY][keterangan]" class="form-control" rows="3"></textarea></td>
        <td><textarea name="items[NEWKEY][parameter_evaluation]" class="form-control" rows="3"></textarea></td>
        <td>
            <div class="option-list">
                <div class="option-row input-group input-group-sm mb-1">
                    <input type="text" name="items[NEWKEY][options][0][label]" class="form-control" value="Kurang">
                    <input type="number" name="items[NEWKEY][options][0][value]" class="form-control" value="1">
                    <input type="number" step="0.01" name="items[NEWKEY][options][0][score]" class="form-control" value="1">
                    <div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-option">x</button></div>
                </div>
                <div class="option-row input-group input-group-sm mb-1">
                    <input type="text" name="items[NEWKEY][options][1][label]" class="form-control" value="Sedang">
                    <input type="number" name="items[NEWKEY][options][1][value]" class="form-control" value="2">
                    <input type="number" step="0.01" name="items[NEWKEY][options][1][score]" class="form-control" value="3">
                    <div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-option">x</button></div>
                </div>
                <div class="option-row input-group input-group-sm mb-1">
                    <input type="text" name="items[NEWKEY][options][2][label]" class="form-control" value="Tinggi">
                    <input type="number" name="items[NEWKEY][options][2][value]" class="form-control" value="3">
                    <input type="number" step="0.01" name="items[NEWKEY][options][2][score]" class="form-control" value="5">
                    <div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-option">x</button></div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary add-option">Tambah Opsi</button>
        </td>
        <td><button type="button" class="btn btn-sm btn-outline-secondary move-up">↑</button><button type="button" class="btn btn-sm btn-outline-secondary move-down">↓</button><button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button></td>
    </tr>
</template>
<template id="template-F">
    <tr class="item-row">
        <td class="row-number"></td>
        <td><input type="text" name="items[NEWKEY][item_code]" class="form-control item-code" required><input type="hidden" name="items[NEWKEY][id]" value="0"><input type="hidden" name="items[NEWKEY][section_code]" value="F"><input type="hidden" name="items[NEWKEY][input_type]" value="estimation"></td>
        <td><input type="text" name="items[NEWKEY][criteria]" class="form-control"></td>
        <td><button type="button" class="btn btn-sm btn-outline-secondary move-up">↑</button><button type="button" class="btn btn-sm btn-outline-secondary move-down">↓</button><button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button></td>
    </tr>
</template>

<script>
    var newRowCounter = 0;

    function renumber(section) {
        $('.item-table[data-section="' + section + '"] tbody .item-row').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }

    function updateMaxPreview() {
        var max = parseFloat($('#f_max').val()) || 0;
        $('.item-table').each(function() {
            var section = $(this).data('section');
            $(this).find('.item-row').each(function() {
                if (section === 'C' || section === 'D') {
                    max += parseFloat($(this).find('input[name$="[yes_score]"]').val()) || 0;
                } else if (section === 'E') {
                    var itemMax = 0;
                    $(this).find('input[name$="[score]"]').each(function() {
                        itemMax = Math.max(itemMax, parseFloat($(this).val()) || 0);
                    });
                    max += itemMax;
                }
            });
        });
        $('#maxScorePreview').text(max.toFixed(2));
    }

    function refreshSection(section) {
        renumber(section);
        updateMaxPreview();
    }

    $(document).on('click', '.add-row', function() {
        var section = $(this).data('section');
        var key = 'new_' + (++newRowCounter);
        var html = $('#template-' + section).html().replace(/NEWKEY/g, key);
        var $row = $(html);
        var count = $('.item-table[data-section="' + section + '"] tbody .item-row').length + 1;
        $row.find('.item-code').val(section + count);
        $('.item-table[data-section="' + section + '"] tbody').append($row);
        refreshSection(section);
    });

    $(document).on('click', '.remove-row', function() {
        var $table = $(this).closest('.item-table');
        $(this).closest('tr').remove();
        refreshSection($table.data('section'));
    });

    $(document).on('click', '.move-up', function() {
        var $row = $(this).closest('tr');
        var $table = $(this).closest('.item-table');
        $row.prev('.item-row').before($row);
        refreshSection($table.data('section'));
    });

    $(document).on('click', '.move-down', function() {
        var $row = $(this).closest('tr');
        var $table = $(this).closest('.item-table');
        $row.next('.item-row').after($row);
        refreshSection($table.data('section'));
    });

    $(document).on('click', '.add-option', function() {
        var $list = $(this).siblings('.option-list');
        var index = $list.find('.option-row').length;
        var code = $(this).closest('tr').find('input[name$="[item_code]"]').val() || 'E';
        var namePrefix = $(this).closest('tr').find('input[name$="[section_code]"]').attr('name').replace('[section_code]', '');
        var html = '<div class="option-row input-group input-group-sm mb-1">' +
            '<input type="text" name="' + namePrefix + '[options][' + index + '][label]" class="form-control" placeholder="Label">' +
            '<input type="number" name="' + namePrefix + '[options][' + index + '][value]" class="form-control" value="' + (index + 1) + '">' +
            '<input type="number" step="0.01" name="' + namePrefix + '[options][' + index + '][score]" class="form-control" value="0">' +
            '<div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-option">x</button></div>' +
            '</div>';
        $list.append(html);
        updateMaxPreview();
    });

    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-row').remove();
        updateMaxPreview();
    });

    $(document).on('input change', '#f_max, input[name$="[yes_score]"], input[name$="[score]"]', updateMaxPreview);

    $('#addBucket').on('click', function() {
        var html = '<tr class="bucket-row">' +
            '<td><input type="number" step="0.01" class="form-control bucket-value" value="0"></td>' +
            '<td><select class="form-control bucket-op"><option value="gt">&gt;</option><option value="gte" selected>&gt;=</option></select></td>' +
            '<td><input type="number" step="0.01" class="form-control bucket-score" value="0"></td>' +
            '<td><button type="button" class="btn btn-sm btn-danger remove-bucket">Hapus</button></td>' +
            '</tr>';
        $('#bucketTable tbody').append(html);
    });

    $(document).on('click', '.remove-bucket', function() {
        $(this).closest('tr').remove();
    });

    $('#builderForm').on('submit', function() {
        var buckets = [];
        $('#bucketTable tbody .bucket-row').each(function() {
            var row = {};
            row[$(this).find('.bucket-op').val()] = parseFloat($(this).find('.bucket-value').val()) || 0;
            row.score = parseFloat($(this).find('.bucket-score').val()) || 0;
            buckets.push(row);
        });
        $('#f_buckets').val(JSON.stringify(buckets));
    });

    $('.item-table').each(function() {
        refreshSection($(this).data('section'));
    });
</script>
@endsection
