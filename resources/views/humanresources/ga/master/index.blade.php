@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <style>
        #GAMasterTabs .nav-link {
            cursor: pointer;
            font-weight: 600;
        }
        .ga-action-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
    </style>

    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-ticket-index') }}">General Affairs</a></li>
            <li class="breadcrumb-item active"><span class="active">Master Tiket</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <ul class="nav nav-tabs" id="GAMasterTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#GATabCategory" data-toggle="tab" href="#GATabCategory" role="tab">Kategori</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#GATabSla" data-toggle="tab" href="#GATabSla" role="tab">SLA</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#GATabTeam" data-toggle="tab" href="#GATabTeam" role="tab">Team</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#GATabMember" data-toggle="tab" href="#GATabMember" role="tab">Anggota Team</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#GATabRouting" data-toggle="tab" href="#GATabRouting" role="tab">Routing</a>
                </li>
            </ul>
            <div class="tab-content pt-3" id="GAMasterTabContent">

                {{-- ===================== KATEGORI ===================== --}}
                <div class="tab-pane fade show active" id="GATabCategory" role="tabpanel">
                    <div id="GACategoryGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    <div class="ga-action-bar">
                        <button type="button" class="btn btn-success btn-sm" onclick="GAFormCreate('GACategoryCard', 'Tambah Kategori')">
                            <i class="fa fa-plus me-1 me-1"></i> Create
                        </button>
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="GACategoryGrid.clearAndLoad('hrd-ga-ticket-master-category-data')">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="GACategoryDeactivate()">
                            <i class="fa fa-ban me-1 me-1"></i> Nonaktifkan
                        </button>
                        <small class="text-muted ms-2 ml-2">Klik dua kali pada baris untuk mengubah kategori.</small>
                    </div>
                </div>

                {{-- ===================== SLA ===================== --}}
                <div class="tab-pane fade" id="GATabSla" role="tabpanel">
                    <div id="GASlaGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    <div class="ga-action-bar">
                        <button type="button" class="btn btn-success btn-sm" onclick="GAFormCreate('GASlaCard', 'Tambah SLA')">
                            <i class="fa fa-plus me-1 me-1"></i> Create
                        </button>
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="GASlaGrid.clearAndLoad('hrd-ga-ticket-master-sla-data')">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <small class="text-muted ms-2 ml-2">Klik dua kali pada baris untuk mengubah SLA.</small>
                    </div>
                </div>

                {{-- ===================== TEAM ===================== --}}
                <div class="tab-pane fade" id="GATabTeam" role="tabpanel">
                    <div id="GATeamGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    <div class="ga-action-bar">
                        <button type="button" class="btn btn-success btn-sm" onclick="GAFormCreate('GATeamCard', 'Tambah Team')">
                            <i class="fa fa-plus me-1 me-1"></i> Create
                        </button>
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="GATeamGrid.clearAndLoad('hrd-ga-ticket-master-team-data')">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <small class="text-muted ms-2 ml-2">Klik dua kali pada baris untuk mengubah team.</small>
                    </div>
                </div>

                {{-- ===================== ANGGOTA TEAM ===================== --}}
                <div class="tab-pane fade" id="GATabMember" role="tabpanel">
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <select id="GAMemberTeamFilter" class="form-control form-control-sm">
                                <option value="">-- semua team --</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->ID }}">{{ $team->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="GAMemberGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    <div class="ga-action-bar">
                        <button type="button" class="btn btn-success btn-sm" onclick="GAFormCreate('GAMemberCard', 'Tambah Anggota Team')">
                            <i class="fa fa-plus me-1 me-1"></i> Create
                        </button>
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="GAMemberReload()">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="GAMemberDeactivate()">
                            <i class="fa fa-ban me-1 me-1"></i> Nonaktifkan
                        </button>
                        <small class="text-muted ms-2 ml-2">Klik dua kali pada baris untuk mengubah data anggota.</small>
                    </div>
                </div>

                {{-- ===================== ROUTING ===================== --}}
                <div class="tab-pane fade" id="GATabRouting" role="tabpanel">
                    <div id="GARoutingGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    <div class="ga-action-bar">
                        <button type="button" class="btn btn-success btn-sm" onclick="GAFormCreate('GARoutingCard', 'Tambah Routing')">
                            <i class="fa fa-plus me-1 me-1"></i> Create
                        </button>
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="GARoutingGrid.clearAndLoad('hrd-ga-ticket-master-routing-data')">
                            <i class="fa fa-sync-alt me-1 me-1"></i> Refresh
                        </button>
                        <small class="text-muted ms-2 ml-2">Klik dua kali pada baris untuk mengubah routing.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARD FORM (muncul di bawah card tab saat tombol Create ditekan) --}}
    {{-- ============================================================ --}}

    {{-- ---------- Form Kategori ---------- --}}
    <div class="card mt-3 d-none GAFormCard" id="GACategoryCard">
        <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 GAFormTitle">Tambah Kategori</h5>
                <small class="text-muted">Kategori mengelompokkan tiket (contoh: Kebersihan, Keamanan). Kategori dengan parent akan menjadi subkategori.</small>
            </div>
            <button type="button" class="btn-close" aria-label="Tutup" onclick="GAFormClose('GACategoryCard')"></button>
        </div>
        <div class="card-body">
            <form id="GACategoryForm">
                @csrf
                <input type="hidden" name="ID" id="GACategoryID" />
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GACategoryCode">Kode <span class="text-danger">*</span></label>
                        <input type="text" id="GACategoryCode" name="Code" class="form-control" maxlength="50" required />
                        <small class="form-text text-muted">Kode singkat dan unik, otomatis huruf kapital. Contoh: CLEANING.</small>
                    </div>
                    <div class="form-group col-md-8 mb-3">
                        <label class="form-label" for="GACategoryName">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" id="GACategoryName" name="Name" class="form-control" maxlength="150" required />
                        <small class="form-text text-muted">Nama yang tampil saat pelapor memilih kategori tiket.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GACategoryParent">Parent Kategori</label>
                        <select id="GACategoryParent" name="Parent_ID" class="form-control">
                            <option value="">-- tanpa parent (kategori utama) --</option>
                            @foreach ($categories as $category)
                                @if (!$category->Parent_ID)
                                    <option value="{{ $category->ID }}">{{ $category->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kosongkan untuk kategori utama. Pilih parent untuk menjadikannya subkategori.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GACategoryPriority">Prioritas Default</label>
                        <select id="GACategoryPriority" name="Default_Priority" class="form-control">
                            <option value="LOW">Low</option>
                            <option value="NORMAL" selected>Normal</option>
                            <option value="HIGH">High</option>
                            <option value="URGENT">Urgent</option>
                        </select>
                        <small class="form-text text-muted">Prioritas awal untuk tiket pada kategori ini.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GACategoryStatus">Status</label>
                        <select id="GACategoryStatus" name="Status" class="form-control">
                            <option value="ACTIVE" selected>ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                        <small class="form-text text-muted">Kategori INACTIVE tidak muncul sebagai pilihan.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6 mb-3">
                        <label class="form-label" for="GACategoryTeam">Default Team</label>
                        <select id="GACategoryTeam" name="Default_Team_ID" class="form-control">
                            <option value="">-- tanpa default team --</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->ID }}">{{ $team->Name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Team yang menangani tiket kategori ini secara default.</small>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        <label class="form-label" for="GACategorySla">Kebijakan SLA</label>
                        <select id="GACategorySla" name="Sla_Policy_ID" class="form-control">
                            <option value="">-- tanpa SLA --</option>
                            @foreach ($slas as $sla)
                                <option value="{{ $sla->ID }}">{{ $sla->Name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Batas waktu respon dan penyelesaian yang berlaku untuk kategori ini.</small>
                    </div>
                </div>
                <div class="border-top pt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Kategori</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="GAFormClose('GACategoryCard')">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------- Form SLA ---------- --}}
    <div class="card mt-3 d-none GAFormCard" id="GASlaCard">
        <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 GAFormTitle">Tambah SLA</h5>
                <small class="text-muted">SLA menentukan batas waktu tiket harus ditanggapi dan diselesaikan.</small>
            </div>
            <button type="button" class="btn-close" aria-label="Tutup" onclick="GAFormClose('GASlaCard')"></button>
        </div>
        <div class="card-body">
            <form id="GASlaForm">
                @csrf
                <input type="hidden" name="ID" />
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GASlaCode">Kode <span class="text-danger">*</span></label>
                        <input type="text" id="GASlaCode" name="Code" class="form-control" maxlength="50" required />
                        <small class="form-text text-muted">Kode singkat dan unik. Contoh: SLA-URGENT.</small>
                    </div>
                    <div class="form-group col-md-8 mb-3">
                        <label class="form-label" for="GASlaName">Nama SLA <span class="text-danger">*</span></label>
                        <input type="text" id="GASlaName" name="Name" class="form-control" maxlength="150" required />
                        <small class="form-text text-muted">Nama kebijakan. Contoh: SLA Kebersihan Prioritas Tinggi.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GASlaCategory">Kategori</label>
                        <select id="GASlaCategory" name="Category_ID" class="form-control">
                            <option value="">-- semua kategori --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->ID }}">{{ $category->Name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kosongkan agar SLA berlaku untuk semua kategori.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GASlaPriority">Prioritas</label>
                        <select id="GASlaPriority" name="Priority" class="form-control">
                            <option value="">-- semua prioritas --</option>
                            <option value="LOW">LOW</option>
                            <option value="NORMAL">NORMAL</option>
                            <option value="HIGH">HIGH</option>
                            <option value="URGENT">URGENT</option>
                        </select>
                        <small class="form-text text-muted">Kosongkan agar berlaku untuk semua prioritas.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GASlaStatus">Status</label>
                        <select id="GASlaStatus" name="Status" class="form-control">
                            <option value="ACTIVE" selected>ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                        <small class="form-text text-muted">SLA INACTIVE tidak dipakai lagi.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6 mb-3">
                        <label class="form-label" for="GASlaResponse">Batas Respon (menit) <span class="text-danger">*</span></label>
                        <input type="number" id="GASlaResponse" name="Response_Minutes" class="form-control" min="1" required />
                        <small class="form-text text-muted">Waktu maksimal sampai tiket pertama kali ditanggapi. Contoh: 60 = 1 jam.</small>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        <label class="form-label" for="GASlaResolve">Batas Penyelesaian (menit) <span class="text-danger">*</span></label>
                        <input type="number" id="GASlaResolve" name="Resolve_Minutes" class="form-control" min="1" required />
                        <small class="form-text text-muted">Waktu maksimal sampai tiket selesai. Contoh: 1440 = 1 hari.</small>
                    </div>
                </div>
                <div class="border-top pt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan SLA</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="GAFormClose('GASlaCard')">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------- Form Team ---------- --}}
    <div class="card mt-3 d-none GAFormCard" id="GATeamCard">
        <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 GAFormTitle">Tambah Team</h5>
                <small class="text-muted">Team adalah kelompok penanganan tiket, bisa dari internal perusahaan atau vendor.</small>
            </div>
            <button type="button" class="btn-close" aria-label="Tutup" onclick="GAFormClose('GATeamCard')"></button>
        </div>
        <div class="card-body">
            <form id="GATeamForm">
                @csrf
                <input type="hidden" name="ID" />
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GATeamCode">Kode <span class="text-danger">*</span></label>
                        <input type="text" id="GATeamCode" name="Code" class="form-control" maxlength="50" required />
                        <small class="form-text text-muted">Kode singkat dan unik. Contoh: GA-CLEAN.</small>
                    </div>
                    <div class="form-group col-md-8 mb-3">
                        <label class="form-label" for="GATeamName">Nama Team <span class="text-danger">*</span></label>
                        <input type="text" id="GATeamName" name="Name" class="form-control" maxlength="150" required />
                        <small class="form-text text-muted">Nama team yang tampil pada penugasan tiket.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-3 mb-3">
                        <label class="form-label" for="GATeamType">Tipe Team <span class="text-danger">*</span></label>
                        <select id="GATeamType" name="Team_Type" class="form-control" required>
                            <option value="INTERNAL" selected>INTERNAL</option>
                            <option value="VENDOR">VENDOR</option>
                        </select>
                        <small class="form-text text-muted">INTERNAL = karyawan sendiri. VENDOR = pihak ketiga.</small>
                    </div>
                    <div class="form-group col-md-5 mb-3">
                        <label class="form-label" for="GATeamVendor">Vendor</label>
                        <select id="GATeamVendor" name="Supplier_ID" class="form-control">
                            <option value="">-- pilih vendor --</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->ID }}">{{ $vendor->Company_Name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Hanya diisi jika tipe team VENDOR.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GATeamDepartment">Department</label>
                        <input type="text" id="GATeamDepartment" name="Department" class="form-control" maxlength="100" />
                        <small class="form-text text-muted">Department induk team (opsional).</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-3 mb-3">
                        <label class="form-label" for="GATeamStatus">Status</label>
                        <select id="GATeamStatus" name="Status" class="form-control">
                            <option value="ACTIVE" selected>ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                        <small class="form-text text-muted">Team INACTIVE tidak muncul sebagai pilihan.</small>
                    </div>
                </div>
                <div class="border-top pt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Team</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="GAFormClose('GATeamCard')">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------- Form Anggota Team ---------- --}}
    <div class="card mt-3 d-none GAFormCard" id="GAMemberCard">
        <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 GAFormTitle">Tambah Anggota Team</h5>
                <small class="text-muted">Anggota team bisa berupa user ERP (internal) atau vendor. Isi salah satu saja.</small>
            </div>
            <button type="button" class="btn-close" aria-label="Tutup" onclick="GAFormClose('GAMemberCard')"></button>
        </div>
        <div class="card-body">
            <form id="GAMemberForm">
                @csrf
                <input type="hidden" name="ID" />
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GAMemberTeam">Team <span class="text-danger">*</span></label>
                        <select id="GAMemberTeam" name="Team_ID" class="form-control" required>
                            <option value="">-- pilih team --</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->ID }}">{{ $team->Name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Team tempat anggota ini bergabung.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GAMemberRole">Peran di Team <span class="text-danger">*</span></label>
                        <select id="GAMemberRole" name="Role_In_Team" class="form-control" required>
                            <option value="MEMBER" selected>MEMBER</option>
                            <option value="LEADER">LEADER</option>
                        </select>
                        <small class="form-text text-muted">LEADER = ketua team.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GAMemberStatus">Status</label>
                        <select id="GAMemberStatus" name="Status" class="form-control">
                            <option value="ACTIVE" selected>ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                        <small class="form-text text-muted">Anggota INACTIVE tidak lagi menerima tugas.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6 mb-3">
                        <label class="form-label" for="GAMemberUser">User ERP (Internal)</label>
                        <select id="GAMemberUser" name="User_Id" class="form-control">
                            <option value="">-- pilih user --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->ID }}">{{ $user->Label }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Pilih untuk anggota internal yang punya akun ERP.</small>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        <label class="form-label" for="GAMemberVendor">Vendor</label>
                        <select id="GAMemberVendor" name="Supplier_ID" class="form-control">
                            <option value="">-- pilih vendor --</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->ID }}">{{ $vendor->Company_Name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Pilih untuk anggota dari pihak vendor. Isi User ERP atau Vendor, tidak keduanya.</small>
                    </div>
                </div>
                <div class="border-top pt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Anggota</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="GAFormClose('GAMemberCard')">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------- Form Routing ---------- --}}
    <div class="card mt-3 d-none GAFormCard" id="GARoutingCard">
        <div class="card-header d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 GAFormTitle">Tambah Routing</h5>
                <small class="text-muted">Rule routing menentukan team mana yang menerima tiket berdasarkan kategori dan prioritas.</small>
            </div>
            <button type="button" class="btn-close" aria-label="Tutup" onclick="GAFormClose('GARoutingCard')"></button>
        </div>
        <div class="card-body">
            <form id="GARoutingForm">
                @csrf
                <input type="hidden" name="ID" />
                <div class="row">
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GARoutingCategory">Kategori</label>
                        <select id="GARoutingCategory" name="Category_ID" class="form-control">
                            <option value="">-- semua kategori --</option>
                            @foreach ($categories as $category)
                                @if (!$category->Parent_ID)
                                    <option value="{{ $category->ID }}">{{ $category->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kosongkan agar rule berlaku untuk semua kategori.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GARoutingSubcategory">Subkategori</label>
                        <select id="GARoutingSubcategory" name="Subcategory_ID" class="form-control">
                            <option value="">-- semua subkategori --</option>
                            @foreach ($categories as $category)
                                @if ($category->Parent_ID)
                                    <option value="{{ $category->ID }}">{{ $category->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kosongkan agar berlaku untuk semua subkategori.</small>
                    </div>
                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label" for="GARoutingPriority">Prioritas</label>
                        <select id="GARoutingPriority" name="Priority" class="form-control">
                            <option value="">-- semua prioritas --</option>
                            <option value="LOW">LOW</option>
                            <option value="NORMAL">NORMAL</option>
                            <option value="HIGH">HIGH</option>
                            <option value="URGENT">URGENT</option>
                        </select>
                        <small class="form-text text-muted">Kosongkan agar berlaku untuk semua prioritas.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6 mb-3">
                        <label class="form-label" for="GARoutingTeam">Team Tujuan <span class="text-danger">*</span></label>
                        <select id="GARoutingTeam" name="Team_ID" class="form-control" required>
                            <option value="">-- pilih team --</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->ID }}">{{ $team->Name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Team yang menerima tiket jika rule ini cocok.</small>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <label class="form-label" for="GARoutingSort">Urutan</label>
                        <input type="number" id="GARoutingSort" name="Sort_Order" class="form-control" value="0" min="0" />
                        <small class="form-text text-muted">Angka lebih kecil tampil lebih dulu.</small>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <label class="form-label d-block">Status Rule</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="Is_Active" value="1" class="form-check-input" id="GARoutingActive" checked />
                            <label class="form-check-label" for="GARoutingActive">Aktif</label>
                        </div>
                        <small class="form-text text-muted">Hilangkan centang untuk menonaktifkan rule.</small>
                    </div>
                </div>
                <div class="border-top pt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Routing</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="GAFormClose('GARoutingCard')">Batal</button>
                </div>
            </form>
        </div>
    </div>

<script>
function selectOptionByText(selectSelector, text) {
    var $select = $(selectSelector);
    if (!text || text === '-' || text === 'Semua') {
        $select.val('');
        return;
    }
    var matchedVal = '';
    $select.find('option').each(function () {
        if ($.trim($(this).text()) === $.trim(text)) {
            matchedVal = $(this).val();
            return false;
        }
    });
    $select.val(matchedVal);
}

function GAFormReset(cardId) {
    var form = $('#' + cardId).find('form');
    form[0].reset();
    form.find('input[name=ID]').val('');
    form.find('[name=Team_Type]').trigger('change');
}

function GAFormClose(cardId) {
    GAFormReset(cardId);
    $('#' + cardId).addClass('d-none');
}

function GAFormOpen(cardId, title) {
    $('.GAFormCard').not('#' + cardId).each(function () { GAFormClose(this.id); });
    var card = $('#' + cardId);
    card.find('.GAFormTitle').text(title);
    card.removeClass('d-none');
    $('html, body').animate({scrollTop: card.offset().top - 80}, 200);
    card.find('input[type=text]:visible, input[type=number]:visible').first().trigger('focus');
}

function GAFormCreate(cardId, title) {
    GAFormReset(cardId);
    if (cardId === 'GAMemberCard') {
        $('#GAMemberTeam').val($('#GAMemberTeamFilter').val()).change();
    }
    GAFormOpen(cardId, title);
}

function GAMasterPost(formSelector, url, grid, dataUrl) {
    var form = $(formSelector);
    var card = form.closest('.GAFormCard');
    var button = form.find('button[type=submit]');
    button.prop('disabled', true);
    $.post(url, form.serialize(), function (response) {
        if (response.status === 'success') {
            alert(response.message);
            GAFormClose(card.attr('id'));
            grid.clearAndLoad(dataUrl);
        } else {
            alert(response.message || 'Gagal menyimpan.');
        }
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan.');
    }).always(function () {
        button.prop('disabled', false);
    });
}

function GACategoryDeactivate() {
    var id = GACategoryGrid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris kategori terlebih dahulu.');
        return;
    }
    if (!confirm('Nonaktifkan kategori ini?')) return;
    $.post('hrd-ga-ticket-master-category-delete', {ID: id, _token: '{{ csrf_token() }}'}, function (response) {
        alert(response.message);
        GACategoryGrid.clearAndLoad('hrd-ga-ticket-master-category-data');
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menonaktifkan.');
    });
}

function GAMemberUrl() {
    var teamId = $('#GAMemberTeamFilter').val();
    return 'hrd-ga-ticket-master-member-data' + (teamId ? '?Team_ID=' + encodeURIComponent(teamId) : '');
}

function GAMemberReload() {
    GAMemberGrid.clearAndLoad(GAMemberUrl());
}

function GAMemberDeactivate() {
    var id = GAMemberGrid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris anggota terlebih dahulu.');
        return;
    }
    if (!confirm('Nonaktifkan anggota ini?')) return;
    $.post('hrd-ga-ticket-master-member-delete', {ID: id, _token: '{{ csrf_token() }}'}, function (response) {
        alert(response.message);
        GAMemberReload();
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menonaktifkan.');
    });
}

GACategoryGrid = new dhtmlXGridObject('GACategoryGrid');
GACategoryGrid.setImagePath('./dhtmlx/imgs/');
GACategoryGrid.setHeader('No,Kode,Nama,Parent,Prioritas,Team,SLA,Status');
GACategoryGrid.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#select_filter');
GACategoryGrid.setInitWidths('50,110,220,150,100,160,160,100');
GACategoryGrid.setColAlign('center,center,left,left,center,left,left,center');
GACategoryGrid.setColSorting('int,str,str,str,str,str,str,str');
GACategoryGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro');
GACategoryGrid.setSkin('dhx_skyblue');
GACategoryGrid.init();
GACategoryGrid.attachEvent('onRowDblClicked', function (id) {
    GAFormReset('GACategoryCard');
    var cell = function (i) { return GACategoryGrid.cells(id, i).getValue(); };
    $('#GACategoryID').val(id);
    $('#GACategoryCode').val(cell(1));
    $('#GACategoryName').val(cell(2));
    selectOptionByText('#GACategoryParent', cell(3));
    $('#GACategoryPriority').val(cell(4) || 'NORMAL');
    selectOptionByText('#GACategoryTeam', cell(5));
    selectOptionByText('#GACategorySla', cell(6));
    $('#GACategoryStatus').val(cell(7) || 'ACTIVE');
    GAFormOpen('GACategoryCard', 'Ubah Kategori');
});
GACategoryGrid.clearAndLoad('hrd-ga-ticket-master-category-data');

GASlaGrid = new dhtmlXGridObject('GASlaGrid');
GASlaGrid.setImagePath('./dhtmlx/imgs/');
GASlaGrid.setHeader('No,Kode,Nama,Kategori,Prioritas,Response (menit),Resolve (menit),Status');
GASlaGrid.setInitWidths('50,110,220,160,110,130,130,100');
GASlaGrid.setColAlign('center,center,left,left,center,center,center,center');
GASlaGrid.setColSorting('int,str,str,str,str,int,int,str');
GASlaGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro');
GASlaGrid.setSkin('dhx_skyblue');
GASlaGrid.init();
GASlaGrid.attachEvent('onRowDblClicked', function (id) {
    GAFormReset('GASlaCard');
    var cell = function (i) { return GASlaGrid.cells(id, i).getValue(); };
    $('#GASlaForm input[name=ID]').val(id);
    $('#GASlaCode').val(cell(1));
    $('#GASlaName').val(cell(2));
    selectOptionByText('#GASlaCategory', cell(3));
    $('#GASlaPriority').val(cell(4) === 'Semua' ? '' : cell(4));
    $('#GASlaResponse').val(cell(5));
    $('#GASlaResolve').val(cell(6));
    $('#GASlaStatus').val(cell(7) || 'ACTIVE');
    GAFormOpen('GASlaCard', 'Ubah SLA');
});
GASlaGrid.clearAndLoad('hrd-ga-ticket-master-sla-data');

GATeamGrid = new dhtmlXGridObject('GATeamGrid');
GATeamGrid.setImagePath('./dhtmlx/imgs/');
GATeamGrid.setHeader('No,Kode,Nama,Tipe,Vendor,Department,Status');
GATeamGrid.setInitWidths('50,110,220,110,200,160,100');
GATeamGrid.setColAlign('center,center,left,center,left,left,center');
GATeamGrid.setColSorting('int,str,str,str,str,str,str');
GATeamGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro');
GATeamGrid.setSkin('dhx_skyblue');
GATeamGrid.init();
GATeamGrid.attachEvent('onRowDblClicked', function (id) {
    GAFormReset('GATeamCard');
    var cell = function (i) { return GATeamGrid.cells(id, i).getValue(); };
    $('#GATeamForm input[name=ID]').val(id);
    $('#GATeamCode').val(cell(1));
    $('#GATeamName').val(cell(2));
    $('#GATeamType').val(cell(3) || 'INTERNAL').trigger('change');
    selectOptionByText('#GATeamVendor', cell(4));
    $('#GATeamDepartment').val(cell(5) === '-' ? '' : cell(5));
    $('#GATeamStatus').val(cell(6) || 'ACTIVE');
    GAFormOpen('GATeamCard', 'Ubah Team');
});
GATeamGrid.clearAndLoad('hrd-ga-ticket-master-team-data');

GAMemberGrid = new dhtmlXGridObject('GAMemberGrid');
GAMemberGrid.setImagePath('./dhtmlx/imgs/');
GAMemberGrid.setHeader('No,Team,Tipe,Nama,Role,Status,Team_ID,User_ID,Supplier_ID');
GAMemberGrid.attachHeader('&nbsp;,#select_filter,#select_filter,#text_filter,#select_filter,#select_filter,&nbsp;,&nbsp;,&nbsp;');
GAMemberGrid.setInitWidths('50,180,90,280,100,100,0,0,0');
GAMemberGrid.setColAlign('center,left,center,left,center,center,center,center,center');
GAMemberGrid.setColSorting('int,str,str,str,str,str,int,int,int');
GAMemberGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro');
GAMemberGrid.setColumnHidden(6, true);
GAMemberGrid.setColumnHidden(7, true);
GAMemberGrid.setColumnHidden(8, true);
GAMemberGrid.setSkin('dhx_skyblue');
GAMemberGrid.init();
GAMemberGrid.attachEvent('onRowDblClicked', function (id) {
    GAFormReset('GAMemberCard');
    var form = $('#GAMemberForm');
    var cell = function (i) { return GAMemberGrid.cells(id, i).getValue(); };
    form.find('input[name=ID]').val(id);
    form.find('[name=Team_ID]').val(cell(6));
    form.find('[name=User_Id]').val(cell(7));
    form.find('[name=Supplier_ID]').val(cell(8));
    form.find('[name=Role_In_Team]').val(cell(4));
    form.find('[name=Status]').val(cell(5));
    GAFormOpen('GAMemberCard', 'Ubah Anggota Team');
});
GAMemberGrid.clearAndLoad(GAMemberUrl());

GARoutingGrid = new dhtmlXGridObject('GARoutingGrid');
GARoutingGrid.setImagePath('./dhtmlx/imgs/');
GARoutingGrid.setHeader('No,Kategori,Subkategori,Prioritas,Team,Sort,Active');
GARoutingGrid.setInitWidths('50,180,180,110,200,70,80');
GARoutingGrid.setColAlign('center,left,left,center,left,center,center');
GARoutingGrid.setColSorting('int,str,str,str,str,int,str');
GARoutingGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro');
GARoutingGrid.setSkin('dhx_skyblue');
GARoutingGrid.init();
GARoutingGrid.attachEvent('onRowDblClicked', function (id) {
    GAFormReset('GARoutingCard');
    var cell = function (i) { return GARoutingGrid.cells(id, i).getValue(); };
    $('#GARoutingForm input[name=ID]').val(id);
    selectOptionByText('#GARoutingCategory', cell(1));
    selectOptionByText('#GARoutingSubcategory', cell(2));
    $('#GARoutingPriority').val(cell(3) === 'Semua' ? '' : cell(3));
    selectOptionByText('#GARoutingTeam', cell(4));
    $('#GARoutingSort').val(cell(5) || 0);
    $('#GARoutingActive').prop('checked', cell(6) === 'YES');
    GAFormOpen('GARoutingCard', 'Ubah Routing');
});
GARoutingGrid.clearAndLoad('hrd-ga-ticket-master-routing-data');

// Vendor hanya relevan untuk team bertipe VENDOR.
$(document).on('change', '#GATeamType', function () {
    var isVendor = $(this).val() === 'VENDOR';
    $('#GATeamVendor').prop('disabled', !isVendor);
    if (!isVendor) $('#GATeamVendor').val('');
});

function GAFormCloseAll() {
    $('.GAFormCard').each(function () { GAFormClose(this.id); });
}

function resizeMasterGrids(target) {
    setTimeout(function () {
        if (target === '#GATabCategory' && GACategoryGrid) GACategoryGrid.setSizes();
        if (target === '#GATabSla' && GASlaGrid) GASlaGrid.setSizes();
        if (target === '#GATabTeam' && GATeamGrid) GATeamGrid.setSizes();
        if (target === '#GATabMember' && GAMemberGrid) GAMemberGrid.setSizes();
        if (target === '#GATabRouting' && GARoutingGrid) GARoutingGrid.setSizes();
    }, 50);
}

// Explicit tab click handler (Bootstrap 4 & Bootstrap 5 compatible)
$('#GAMasterTabs a.nav-link').on('click', function (e) {
    e.preventDefault();
    var $link = $(this);
    var target = $link.attr('data-bs-target') || $link.attr('href');
    if (!target) return;

    if (!$link.hasClass('active')) {
        GAFormCloseAll();
    }

    $('#GAMasterTabs a.nav-link').removeClass('active');
    $link.addClass('active');

    $('#GAMasterTabContent > .tab-pane').removeClass('show active');
    $(target).addClass('show active');

    resizeMasterGrids(target);
});

$(document).on('submit', '#GACategoryForm', function (event) {
    event.preventDefault();
    GAMasterPost('#GACategoryForm', 'hrd-ga-ticket-master-category-save', GACategoryGrid, 'hrd-ga-ticket-master-category-data');
});
$(document).on('submit', '#GASlaForm', function (event) {
    event.preventDefault();
    GAMasterPost('#GASlaForm', 'hrd-ga-ticket-master-sla-save', GASlaGrid, 'hrd-ga-ticket-master-sla-data');
});
$(document).on('submit', '#GATeamForm', function (event) {
    event.preventDefault();
    GAMasterPost('#GATeamForm', 'hrd-ga-ticket-master-team-save', GATeamGrid, 'hrd-ga-ticket-master-team-data');
});
$(document).on('submit', '#GAMemberForm', function (event) {
    event.preventDefault();
    GAMasterPost('#GAMemberForm', 'hrd-ga-ticket-master-member-save', GAMemberGrid, GAMemberUrl());
});
$('#GAMemberTeamFilter').on('change', GAMemberReload);
$(document).on('change', '#GAMemberUser', function () {
    if ($(this).val()) $('#GAMemberVendor').val('');
});
$(document).on('change', '#GAMemberVendor', function () {
    if ($(this).val()) $('#GAMemberUser').val('');
});
$(document).on('submit', '#GARoutingForm', function (event) {
    event.preventDefault();
    GAMasterPost('#GARoutingForm', 'hrd-ga-ticket-master-routing-save', GARoutingGrid, 'hrd-ga-ticket-master-routing-data');
});
</script>
@endsection
