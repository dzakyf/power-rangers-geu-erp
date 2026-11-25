@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <style>
        #ITCatMasterTabs .nav-link {
            cursor: pointer;
            font-weight: 600;
        }
        .it-cat-action-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
    </style>

    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Master Kategori &amp; Subkategori IT
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('hrd-it-ticket-index') }}" style="color: rgba(255, 255, 255, 0.8) !important;">IT Support &amp; Helpdesk</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Master Kategori IT</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if (can_menu('SI01158', 'view'))
                <a class="btn btn-outline-light btn-sm text-light" href="{{ url('hrd-it-ticket-index') }}">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke IT Helpdesk
                </a>
            @endif
        </div>
    </div>

    <div class="card mt-2">
        <div class="card-body">
            <ul class="nav nav-tabs" id="ITCatMasterTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#ITTabMainCategory" data-toggle="tab" href="#ITTabMainCategory" role="tab">
                        <i class="fa fa-folder me-1 text-primary"></i> Kategori Utama
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#ITTabSubcategory" data-toggle="tab" href="#ITTabSubcategory" role="tab">
                        <i class="fa fa-sitemap me-1 text-info"></i> Subkategori
                    </a>
                </li>
            </ul>

            <div class="tab-content pt-3" id="ITCatMasterTabContent">
                {{-- ===================== TAB 1: KATEGORI UTAMA ===================== --}}
                <div class="tab-pane fade show active" id="ITTabMainCategory" role="tabpanel">
                    <div id="ITCategoryGrid" style="width:100%;height:340px;background-color:white;overflow:hidden"></div>
                    <div class="it-cat-action-bar">
                        @if (can_menu('SI01161', 'create'))
                            <button type="button" class="btn btn-success btn-sm" onclick="ITCatOpenCreate('ITCategoryCard', 'Tambah Kategori Utama IT')">
                                <i class="fa fa-plus me-1"></i> Tambah Kategori
                            </button>
                        @endif
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="ITCategoryReload()">
                            <i class="fa fa-sync-alt me-1"></i> Refresh
                        </button>
                        @if (can_menu('SI01161', 'delete'))
                            <button type="button" class="btn btn-danger btn-sm" onclick="ITCategoryDeactivate('main')">
                                <i class="fa fa-ban me-1"></i> Nonaktifkan
                            </button>
                        @endif
                        <small class="text-muted ms-2">Klik dua kali pada baris untuk mengubah kategori utama. Kategori dengan subkategori aktif tidak dapat dinonaktifkan.</small>
                    </div>
                </div>

                {{-- ===================== TAB 2: SUBKATEGORI ===================== --}}
                <div class="tab-pane fade" id="ITTabSubcategory" role="tabpanel">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-md-4">
                            <select id="ITSubcategoryParentFilter" class="form-control form-control-sm">
                                <option value="">-- semua kategori utama --</option>
                                @foreach ($allMainCategories as $cat)
                                    <option value="{{ $cat->ID }}">{{ $cat->Name }} ({{ $cat->Code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <small class="text-muted">Filter subkategori berdasarkan kategori utama induknya.</small>
                        </div>
                    </div>
                    <div id="ITSubcategoryGrid" style="width:100%;height:340px;background-color:white;overflow:hidden"></div>
                    <div class="it-cat-action-bar">
                        @if (can_menu('SI01161', 'create'))
                            <button type="button" class="btn btn-success btn-sm" onclick="ITCatOpenCreate('ITSubcategoryCard', 'Tambah Subkategori IT')">
                                <i class="fa fa-plus me-1"></i> Tambah Subkategori
                            </button>
                        @endif
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="ITSubcategoryReload()">
                            <i class="fa fa-sync-alt me-1"></i> Refresh
                        </button>
                        @if (can_menu('SI01161', 'delete'))
                            <button type="button" class="btn btn-danger btn-sm" onclick="ITCategoryDeactivate('sub')">
                                <i class="fa fa-ban me-1"></i> Nonaktifkan
                            </button>
                        @endif
                        <small class="text-muted ms-2">Klik dua kali pada baris untuk mengubah subkategori.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== FORM KATEGORI UTAMA ===================== --}}
    <div class="card mt-3 d-none ITCatFormCard" id="ITCategoryCard">
        <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 ITCatFormTitle">Tambah Kategori Utama IT</h5>
                <small class="text-muted">Kode otomatis diawali prefix <strong>IT-</strong> agar terisolasi dari kategori General Affairs.</small>
            </div>
            <button type="button" class="btn-close" aria-label="Tutup" onclick="ITCatFormClose('ITCategoryCard')"></button>
        </div>
        <div class="card-body">
            <div class="alert alert-warning py-2 d-none" id="ITMainSystemAlert">
                <i class="fa fa-shield-alt me-1"></i>
                Ini adalah <strong>Kategori Default Sistem</strong> (fallback otomatis saat pengajuan tiket IT). Kode, Scope Global, dan Status Aktif dikunci oleh sistem.
            </div>
            <form id="ITCategoryForm">
                @csrf
                <input type="hidden" name="ID" id="ITMainID" />
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITMainCode">Kode <span class="text-danger">*</span></label>
                        <input type="text" id="ITMainCode" name="Code" class="form-control" maxlength="50" placeholder="Contoh: IT-NET atau NET" required />
                        <small class="form-text text-muted">Otomatis kapital &amp; diawali <code>IT-</code>.</small>
                    </div>
                    <div class="form-group col-md-5 mb-3">
                        <label class="form-label" for="ITMainName">Nama Kategori Utama <span class="text-danger">*</span></label>
                        <input type="text" id="ITMainName" name="Name" class="form-control" maxlength="150" required />
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <label class="form-label" for="ITMainStatus">Status</label>
                        <select id="ITMainStatus" name="Status" class="form-control">
                            <option value="ACTIVE" selected>ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITMainScopeMode">Cakupan (Scope) <span class="text-danger">*</span></label>
                        <select id="ITMainScopeMode" name="Scope_Mode" class="form-control" onchange="ITSyncScopeFields('Main')">
                            <option value="GLOBAL" selected>Global (Semua Lokasi &amp; Company)</option>
                            <option value="SPECIFIC">Spesifik Lokasi / Company</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3 ITMainScopeWrap d-none">
                        <label class="form-label" for="ITMainLocation">Lokasi</label>
                        <select id="ITMainLocation" name="Location" class="form-control">
                            <option value="">-- semua lokasi --</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc }}">{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3 ITMainScopeWrap d-none">
                        <label class="form-label" for="ITMainCompany">Company</label>
                        <select id="ITMainCompany" name="Company_ID" class="form-control">
                            <option value="">-- semua company --</option>
                            @foreach ($companies as $comp)
                                <option value="{{ $comp }}">{{ $comp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITMainPriority">Prioritas Default</label>
                        <select id="ITMainPriority" name="Default_Priority" class="form-control">
                            <option value="LOW">LOW</option>
                            <option value="NORMAL" selected>NORMAL</option>
                            <option value="HIGH">HIGH</option>
                            <option value="URGENT">URGENT</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITMainTeam">Team IT Default</label>
                        <select id="ITMainTeam" name="Default_Team_ID" class="form-control">
                            <option value="">-- tanpa default team --</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->ID }}">{{ $team->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITMainSla">Kebijakan SLA</label>
                        <select id="ITMainSla" name="Sla_Policy_ID" class="form-control">
                            <option value="">-- tanpa SLA --</option>
                            @foreach ($slas as $sla)
                                <option value="{{ $sla->ID }}">{{ $sla->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="border-top pt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Kategori Utama</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="ITCatFormClose('ITCategoryCard')">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== FORM SUBKATEGORI ===================== --}}
    <div class="card mt-3 d-none ITCatFormCard" id="ITSubcategoryCard">
        <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 ITCatFormTitle">Tambah Subkategori IT</h5>
                <small class="text-muted">Subkategori terhubung langsung ke Kategori Utama (struktur 2 level) dan otomatis mewarisi batasan scope parent.</small>
            </div>
            <button type="button" class="btn-close" aria-label="Tutup" onclick="ITCatFormClose('ITSubcategoryCard')"></button>
        </div>
        <div class="card-body">
            <form id="ITSubcategoryForm">
                @csrf
                <input type="hidden" name="ID" id="ITSubID" />
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITSubParent">Kategori Utama (Parent) <span class="text-danger">*</span></label>
                        <select id="ITSubParent" name="Parent_ID" class="form-control" required onchange="ITOnSubParentChange()">
                            <option value="">-- pilih kategori utama --</option>
                            @foreach ($mainCategories as $cat)
                                <option value="{{ $cat->ID }}" data-location="{{ $cat->Location ?? '' }}" data-company="{{ $cat->Company_ID ?? '' }}">
                                    {{ $cat->Name }} ({{ $cat->Code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted" id="ITSubParentScopeHint">Pilih kategori utama terlebih dahulu.</small>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <label class="form-label" for="ITSubCode">Kode <span class="text-danger">*</span></label>
                        <input type="text" id="ITSubCode" name="Code" class="form-control" maxlength="50" placeholder="Contoh: IT-HELP-VPN" required />
                        <small class="form-text text-muted">Otomatis kapital &amp; diawali <code>IT-</code>.</small>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <label class="form-label" for="ITSubName">Nama Subkategori <span class="text-danger">*</span></label>
                        <input type="text" id="ITSubName" name="Name" class="form-control" maxlength="150" required />
                    </div>
                    <div class="form-group col-md-2 mb-3">
                        <label class="form-label" for="ITSubStatus">Status</label>
                        <select id="ITSubStatus" name="Status" class="form-control">
                            <option value="ACTIVE" selected>ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITSubScopeMode">Cakupan (Scope) <span class="text-danger">*</span></label>
                        <select id="ITSubScopeMode" name="Scope_Mode" class="form-control" onchange="ITSyncScopeFields('Sub')">
                            <option value="GLOBAL" selected>Global (Semua Lokasi &amp; Company)</option>
                            <option value="SPECIFIC">Spesifik Lokasi / Company</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3 ITSubScopeWrap d-none">
                        <label class="form-label" for="ITSubLocation">Lokasi</label>
                        <select id="ITSubLocation" name="Location" class="form-control">
                            <option value="">-- semua lokasi --</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc }}">{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3 ITSubScopeWrap d-none">
                        <label class="form-label" for="ITSubCompany">Company</label>
                        <select id="ITSubCompany" name="Company_ID" class="form-control">
                            <option value="">-- semua company --</option>
                            @foreach ($companies as $comp)
                                <option value="{{ $comp }}">{{ $comp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITSubPriority">Prioritas Default</label>
                        <select id="ITSubPriority" name="Default_Priority" class="form-control">
                            <option value="LOW">LOW</option>
                            <option value="NORMAL" selected>NORMAL</option>
                            <option value="HIGH">HIGH</option>
                            <option value="URGENT">URGENT</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITSubTeam">Team IT Default</label>
                        <select id="ITSubTeam" name="Default_Team_ID" class="form-control">
                            <option value="">-- ikuti kategori utama --</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->ID }}">{{ $team->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="ITSubSla">Kebijakan SLA</label>
                        <select id="ITSubSla" name="Sla_Policy_ID" class="form-control">
                            <option value="">-- ikuti kategori utama --</option>
                            @foreach ($slas as $sla)
                                <option value="{{ $sla->ID }}">{{ $sla->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="border-top pt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Subkategori</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="ITCatFormClose('ITSubcategoryCard')">Batal</button>
                </div>
            </form>
        </div>
    </div>

<script>
function ITSyncScopeFields(prefix) {
    var mode = $('#IT' + prefix + 'ScopeMode').val();
    var isSpecific = (mode === 'SPECIFIC');
    $('.IT' + prefix + 'ScopeWrap').toggleClass('d-none', !isSpecific);
    if (!isSpecific) {
        $('#IT' + prefix + 'Location').val('');
        $('#IT' + prefix + 'Company').val('');
    }
}

function ITSetMainSystemLock(isSystem) {
    $('#ITMainSystemAlert').toggleClass('d-none', !isSystem);
    $('#ITMainCode').prop('readonly', isSystem);
    $('#ITMainScopeMode').prop('disabled', isSystem);
    $('#ITMainLocation').prop('disabled', isSystem);
    $('#ITMainCompany').prop('disabled', isSystem);
    $('#ITMainStatus').prop('disabled', isSystem);
}

function ITOnSubParentChange() {
    var opt = $('#ITSubParent option:selected');
    var loc = opt.attr('data-location') || '';
    var comp = opt.attr('data-company') || '';
    var hint = 'Pilih kategori utama terlebih dahulu.';
    if (opt.val()) {
        if (loc || comp) {
            hint = 'Parent dibatasi pada scope: ' + (loc || 'Semua Lokasi') + ' / ' + (comp || 'Semua Company') + '.';
            if (!$('#ITSubID').val()) {
                $('#ITSubScopeMode').val('SPECIFIC');
                ITSyncScopeFields('Sub');
                if (loc) $('#ITSubLocation').val(loc);
                if (comp) $('#ITSubCompany').val(comp);
            }
        } else {
            hint = 'Parent berlaku Global (subkategori boleh Global atau Spesifik).';
        }
    }
    $('#ITSubParentScopeHint').text(hint);
}

function ITCatFormReset(cardId) {
    var card = $('#' + cardId);
    var form = card.find('form');
    form[0].reset();
    form.find('input[name=ID]').val('');
    if (cardId === 'ITCategoryCard') {
        ITSetMainSystemLock(false);
        ITSyncScopeFields('Main');
    } else {
        ITSyncScopeFields('Sub');
        $('#ITSubParentScopeHint').text('Pilih kategori utama terlebih dahulu.');
    }
}

function ITCatFormClose(cardId) {
    ITCatFormReset(cardId);
    $('#' + cardId).addClass('d-none');
}

function ITCatFormOpen(cardId, title) {
    $('.ITCatFormCard').not('#' + cardId).each(function () { ITCatFormClose(this.id); });
    var card = $('#' + cardId);
    card.find('.ITCatFormTitle').text(title);
    card.removeClass('d-none');
    $('html, body').animate({scrollTop: card.offset().top - 80}, 200);
    card.find('input[type=text]:not([readonly]):visible').first().trigger('focus');
}

function ITCatOpenCreate(cardId, title) {
    ITCatFormReset(cardId);
    if (cardId === 'ITSubcategoryCard') {
        var filterParent = $('#ITSubcategoryParentFilter').val();
        if (filterParent) {
            $('#ITSubParent').val(filterParent);
            ITOnSubParentChange();
        }
    }
    ITCatFormOpen(cardId, title);
}

function ITEnsureSelectOption(selectSelector, value) {
    if (!value) return;
    var $sel = $(selectSelector);
    if (!$sel.find('option').filter(function () { return $(this).val() === String(value); }).length) {
        $sel.append($('<option>', {value: value, text: value}));
    }
}

function ITRefreshParentOptions() {
    $.get('hrd-it-category-options', function (res) {
        if (!res || res.status !== 'success') return;
        var currentFilter = $('#ITSubcategoryParentFilter').val();
        var currentSubParent = $('#ITSubParent').val();

        var $filter = $('#ITSubcategoryParentFilter').empty().append('<option value="">-- semua kategori utama --</option>');
        (res.all_main_categories || []).forEach(function (cat) {
            $filter.append($('<option>', {value: cat.ID, text: cat.Name + ' (' + cat.Code + ')'}));
        });
        $filter.val(currentFilter);

        var $parent = $('#ITSubParent').empty().append('<option value="">-- pilih kategori utama --</option>');
        (res.main_categories || []).forEach(function (cat) {
            $parent.append($('<option>', {
                value: cat.ID,
                text: cat.Name + ' (' + cat.Code + ')',
                'data-location': cat.Location || '',
                'data-company': cat.Company_ID || ''
            }));
        });
        $parent.val(currentSubParent);
    });
}

function ITCategoryReload() {
    ITCategoryGrid.clearAndLoad('hrd-it-category-data');
}

function ITSubcategoryUrl() {
    var parentId = $('#ITSubcategoryParentFilter').val();
    return 'hrd-it-subcategory-data' + (parentId ? '?parent_id=' + encodeURIComponent(parentId) : '');
}

function ITSubcategoryReload() {
    ITSubcategoryGrid.clearAndLoad(ITSubcategoryUrl());
}

function ITCategoryDeactivate(kind) {
    var grid = (kind === 'sub') ? ITSubcategoryGrid : ITCategoryGrid;
    var id = grid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris terlebih dahulu.');
        return;
    }
    if (!confirm('Nonaktifkan data ini?')) return;

    $.post('hrd-it-category-deactivate', {
        ID: id,
        _token: '{{ csrf_token() }}'
    }, function (res) {
        alert(res.message || 'Data dinonaktifkan.');
        ITCategoryReload();
        ITSubcategoryReload();
        ITRefreshParentOptions();
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menonaktifkan data.');
    });
}

$('#ITCategoryForm').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var btn = form.find('button[type=submit]');
    var disabledFields = form.find(':disabled').prop('disabled', false);
    var payload = form.serialize();
    disabledFields.prop('disabled', true);

    btn.prop('disabled', true);
    $.post('hrd-it-category-save', payload, function (res) {
        if (res.status === 'success') {
            alert(res.message);
            ITCatFormClose('ITCategoryCard');
            ITCategoryReload();
            ITRefreshParentOptions();
        } else {
            alert(res.message || 'Gagal menyimpan kategori.');
        }
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan kategori.');
    }).always(function () {
        btn.prop('disabled', false);
    });
});

$('#ITSubcategoryForm').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var btn = form.find('button[type=submit]');
    btn.prop('disabled', true);
    $.post('hrd-it-subcategory-save', form.serialize(), function (res) {
        if (res.status === 'success') {
            alert(res.message);
            ITCatFormClose('ITSubcategoryCard');
            ITCategoryReload();
            ITSubcategoryReload();
        } else {
            alert(res.message || 'Gagal menyimpan subkategori.');
        }
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan subkategori.');
    }).always(function () {
        btn.prop('disabled', false);
    });
});

$('#ITSubcategoryParentFilter').on('change', function () {
    ITSubcategoryReload();
});

var ITCategoryGrid = new dhtmlXGridObject('ITCategoryGrid');
ITCategoryGrid.setImagePath('./dhtmlx/imgs/');
ITCategoryGrid.setHeader('No,Kode,Nama Kategori,Scope,Subkategori,Prioritas,Team Default,SLA,Status,Location,Company_ID,Team_ID,SLA_ID,Is_System');
ITCategoryGrid.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;');
ITCategoryGrid.setInitWidths('45,120,210,160,120,90,160,150,90,0,0,0,0,0');
ITCategoryGrid.setColAlign('center,center,left,center,center,center,left,left,center,left,left,center,center,center');
ITCategoryGrid.setColSorting('int,str,str,str,str,str,str,str,str,str,str,int,int,int');
ITCategoryGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
ITCategoryGrid.setColumnHidden(9, true);
ITCategoryGrid.setColumnHidden(10, true);
ITCategoryGrid.setColumnHidden(11, true);
ITCategoryGrid.setColumnHidden(12, true);
ITCategoryGrid.setColumnHidden(13, true);
ITCategoryGrid.setSkin('dhx_skyblue');
ITCategoryGrid.init();
ITCategoryGrid.attachEvent('onRowDblClicked', function (id) {
    ITCatFormReset('ITCategoryCard');
    var c = function (i) { return ITCategoryGrid.cells(id, i).getValue(); };
    var loc = c(9);
    var comp = c(10);
    var isSystem = String(c(13)) === '1';

    $('#ITMainID').val(id);
    $('#ITMainCode').val(c(1));
    $('#ITMainName').val(c(2));
    $('#ITMainPriority').val(c(5) || 'NORMAL');
    $('#ITMainStatus').val(c(8) || 'ACTIVE');
    $('#ITMainTeam').val(c(11) || '');
    $('#ITMainSla').val(c(12) || '');

    if (loc || comp) {
        $('#ITMainScopeMode').val('SPECIFIC');
        ITSyncScopeFields('Main');
        ITEnsureSelectOption('#ITMainLocation', loc);
        ITEnsureSelectOption('#ITMainCompany', comp);
        $('#ITMainLocation').val(loc);
        $('#ITMainCompany').val(comp);
    } else {
        $('#ITMainScopeMode').val('GLOBAL');
        ITSyncScopeFields('Main');
    }

    ITSetMainSystemLock(isSystem);
    ITCatFormOpen('ITCategoryCard', 'Ubah Kategori Utama IT');
});
ITCategoryGrid.clearAndLoad('hrd-it-category-data');

var ITSubcategoryGrid = new dhtmlXGridObject('ITSubcategoryGrid');
ITSubcategoryGrid.setImagePath('./dhtmlx/imgs/');
ITSubcategoryGrid.setHeader('No,Kode,Nama Subkategori,Kategori Utama,Scope,Prioritas,Team Default,SLA,Status,Parent_ID,Location,Company_ID,Team_ID,SLA_ID');
ITSubcategoryGrid.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#select_filter,#text_filter,#select_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;');
ITSubcategoryGrid.setInitWidths('45,130,200,200,150,90,150,140,90,0,0,0,0,0');
ITSubcategoryGrid.setColAlign('center,center,left,left,center,center,left,left,center,center,left,left,center,center');
ITSubcategoryGrid.setColSorting('int,str,str,str,str,str,str,str,str,int,str,str,int,int');
ITSubcategoryGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
ITSubcategoryGrid.setColumnHidden(9, true);
ITSubcategoryGrid.setColumnHidden(10, true);
ITSubcategoryGrid.setColumnHidden(11, true);
ITSubcategoryGrid.setColumnHidden(12, true);
ITSubcategoryGrid.setColumnHidden(13, true);
ITSubcategoryGrid.setSkin('dhx_skyblue');
ITSubcategoryGrid.init();
ITSubcategoryGrid.attachEvent('onRowDblClicked', function (id) {
    ITCatFormReset('ITSubcategoryCard');
    var c = function (i) { return ITSubcategoryGrid.cells(id, i).getValue(); };
    var loc = c(10);
    var comp = c(11);

    $('#ITSubID').val(id);
    $('#ITSubCode').val(c(1));
    $('#ITSubName').val(c(2));
    $('#ITSubPriority').val(c(5) || 'NORMAL');
    $('#ITSubStatus').val(c(8) || 'ACTIVE');
    $('#ITSubParent').val(c(9) || '');
    $('#ITSubTeam').val(c(12) || '');
    $('#ITSubSla').val(c(13) || '');

    if (loc || comp) {
        $('#ITSubScopeMode').val('SPECIFIC');
        ITSyncScopeFields('Sub');
        ITEnsureSelectOption('#ITSubLocation', loc);
        ITEnsureSelectOption('#ITSubCompany', comp);
        $('#ITSubLocation').val(loc);
        $('#ITSubCompany').val(comp);
    } else {
        $('#ITSubScopeMode').val('GLOBAL');
        ITSyncScopeFields('Sub');
    }

    ITOnSubParentChange();
    ITCatFormOpen('ITSubcategoryCard', 'Ubah Subkategori IT');
});
ITSubcategoryGrid.clearAndLoad(ITSubcategoryUrl());
</script>
@endsection
