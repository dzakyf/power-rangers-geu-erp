@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<div class="main-content-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="hrd-sppd-index">Perjalanan Dinas</a></li>
            <li class="breadcrumb-item active"><span class="active">Master Regulasi SPPD</span></li>
        </ol>
    </div>
    <div>
        <a href="hrd-sppd-index" class="btn btn-secondary mr-2"><i class="fa fa-arrow-left"></i> Kembali</a>
        <button type="button" class="btn btn-warning shadow-sm" onclick="syncFromJson()"><i class="fa fa-sync"></i> Reset/Sync dari JSON</button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="masterTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold" id="matrix-tab" data-toggle="tab" href="#matrix-content" role="tab"><i class="fa fa-table mr-1"></i> Matriks Regulasi & Plafon</a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="component-tab" data-toggle="tab" href="#component-content" role="tab"><i class="fa fa-list-ul mr-1"></i> Master Komponen Tunjangan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="joblevel-tab" data-toggle="tab" href="#joblevel-content" role="tab"><i class="fa fa-id-badge mr-1"></i> Master Level Pekerjaan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="triptype-tab" data-toggle="tab" href="#triptype-content" role="tab"><i class="fa fa-plane-departure mr-1"></i> Master Jenis Perjalanan</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="masterTabsContent">
            
            <!-- TAB 1: MATRIKS REGULASI -->
            <div class="tab-pane fade show active" id="matrix-content" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 text-primary font-weight-bold">Daftar Plafon & Tunjangan Per Skenario</h5>
                </div>
                
                @foreach($tripTypes as $tt)
                <div class="card mb-4 border shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="m-0 font-weight-bold text-dark"><i class="fa fa-tag text-info mr-1"></i> {{ $tt->name }} <span class="badge badge-info ml-2">{{ $tt->duration }}</span></h6>
                            @if($tt->note)<small class="text-muted">{{ $tt->note }}</small>@endif
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered table-striped m-0 align-middle">
                            <thead class="bg-secondary text-white text-center">
                                <tr>
                                    <th style="width: 200px;">Komponen Tunjangan</th>
                                    @foreach($jobLevels as $jl)
                                    <th>{{ $jl->label }} <br><small class="font-italic">({{ $jl->grade }})</small></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-weight-bold text-dark">Transport Darat</td>
                                    @foreach($jobLevels as $jl)
                                    @php
                                        $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first();
                                        $land = $allw ? (is_string($allw->transport_land) ? json_decode($allw->transport_land, true) : $allw->transport_land) : null;
                                    @endphp
                                    <td class="text-center">
                                        {{ $land && is_array($land) ? implode(', ', $land) : '-' }}
                                    </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Transport Udara</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">{{ $allw && $allw->transport_air ? $allw->transport_air : '-' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Max Tiket / Malam Hotel</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">
                                        Tiket: {{ $allw && $allw->ticket_max ? 'Rp '.number_format($allw->ticket_max, 0, ',', '.') : 'Tak Terbatas' }}<br>
                                        Hotel/Nite: {{ $allw && $allw->reimburse_max_per_night ? 'Rp '.number_format($allw->reimburse_max_per_night, 0, ',', '.') : '-' }}
                                    </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Bintang Hotel</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">{{ $allw && $allw->hotel_star ? '★ '.$allw->hotel_star : '-' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Uang Makan / Hari</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">{{ $allw && $allw->daily_meal ? 'Rp '.number_format($allw->daily_meal, 0, ',', '.') : '-' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Uang Saku / Hari</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">{{ $allw && $allw->daily_pocket ? 'Rp '.number_format($allw->daily_pocket, 0, ',', '.') : '-' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Bantuan Kos / Bulan</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">{{ $allw && $allw->kos_per_month ? 'Rp '.number_format($allw->kos_per_month, 0, ',', '.') : '-' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Bantuan Relokasi (One Time)</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">{{ $allw && $allw->relocation_one_time_payment ? 'Rp '.number_format($allw->relocation_one_time_payment, 0, ',', '.') : '-' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Uang Perbantuan / Minggu</td>
                                    @foreach($jobLevels as $jl)
                                    @php $allw = $allowances->where('trip_type_key', $tt->type_key)->where('job_level_key', $jl->level)->first(); @endphp
                                    <td class="text-center">{{ $allw && $allw->mobilization_cost_per_week ? 'Rp '.number_format($allw->mobilization_cost_per_week, 0, ',', '.') : '-' }}</td>
                                    @endforeach
                                </tr>
                                <tr class="bg-light">
                                    <td class="font-weight-bold text-dark">Aksi Edit Plafon</td>
                                    @foreach($jobLevels as $jl)
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary" onclick="editAllowance('{{ $tt->type_key }}', '{{ $jl->level }}', '{{ $tt->name }}', '{{ $jl->label }}')">
                                            <i class="fa fa-edit"></i> Edit Plafon
                                        </button>
                                    </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- TAB 2: KOMPONEN TUNJANGAN -->
            <div class="tab-pane fade" id="component-content" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="m-0 text-primary font-weight-bold">Daftar Komponen Tunjangan Regulasi SPPD</h5>
                        <small class="text-muted"><i class="fa fa-info-circle"></i> Komponen tunjangan standar yang dikelola dalam matriks regulasi</small>
                    </div>
                    <button class="btn btn-success btn-sm" onclick="addComponent()"><i class="fa fa-plus"></i> Tambah Komponen</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="width: 150px;">Kode Key</th>
                                <th style="width: 220px;">Nama Komponen</th>
                                <th style="width: 180px;">Kategori</th>
                                <th style="width: 150px;">Tipe Input</th>
                                <th>Keterangan / Deskripsi Regulasi</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($components as $comp)
                            <tr>
                                <td>{{ $comp->id }}</td>
                                <td><code>{{ $comp->code }}</code></td>
                                <td><strong>{{ $comp->name }}</strong></td>
                                <td><span class="badge badge-info">{{ $comp->category }}</span></td>
                                <td><span class="badge badge-secondary">{{ $comp->type }}</span></td>
                                <td>{{ $comp->description }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="editComponent({{ $comp->id }}, '{{ $comp->code }}', '{{ addslashes($comp->name) }}', '{{ addslashes($comp->category) }}', '{{ addslashes($comp->type) }}', '{{ addslashes($comp->description) }}')"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteComponent({{ $comp->id }}, '{{ addslashes($comp->name) }}')"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: JOB LEVEL -->
            <div class="tab-pane fade" id="joblevel-content" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 text-primary font-weight-bold">Master Level Pekerjaan</h5>
                    <button class="btn btn-success btn-sm" onclick="addJobLevel()"><i class="fa fa-plus"></i> Tambah Job Level</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>Key Level</th>
                                <th>Grade</th>
                                <th>Label Display</th>
                                <th style="width: 150px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobLevels as $jl)
                            <tr>
                                <td>{{ $jl->id }}</td>
                                <td><code>{{ $jl->level }}</code></td>
                                <td>{{ $jl->grade }}</td>
                                <td><strong>{{ $jl->label }}</strong></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="editJobLevel({{ $jl->id }}, '{{ $jl->level }}', '{{ $jl->grade }}', '{{ $jl->label }}')"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteJobLevel({{ $jl->id }}, '{{ $jl->label }}')"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: TRIP TYPE -->
            <div class="tab-pane fade" id="triptype-content" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 text-primary font-weight-bold">Master Jenis Perjalanan Dinas</h5>
                    <button class="btn btn-success btn-sm" onclick="addTripType()"><i class="fa fa-plus"></i> Tambah Jenis Perjalanan</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>Key Type</th>
                                <th>Nama Jenis</th>
                                <th>Durasi Standard</th>
                                <th>Catatan</th>
                                <th style="width: 150px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tripTypes as $tt)
                            <tr>
                                <td>{{ $tt->id }}</td>
                                <td><code>{{ $tt->type_key }}</code></td>
                                <td><strong>{{ $tt->name }}</strong></td>
                                <td><span class="badge badge-info">{{ $tt->duration }}</span></td>
                                <td>{{ $tt->note ?: '-' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="editTripType({{ $tt->id }}, '{{ $tt->type_key }}', '{{ $tt->name }}', '{{ $tt->duration }}', '{{ $tt->note }}')"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTripType({{ $tt->id }}, '{{ $tt->name }}')"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL ALLOWANCE EDIT -->
<div class="modal fade" id="modalAllowance" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAllowanceTitle">Edit Plafon Tunjangan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formAllowance" onsubmit="submitAllowance(event)">
                <div class="modal-body">
                    <input type="hidden" id="allowance_trip_type_key" name="trip_type_key">
                    <input type="hidden" id="allowance_job_level_key" name="job_level_key">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transport Darat (Pisahkan koma)</label>
                                <input type="text" class="form-control" id="allowance_transport_land" name="transport_land" placeholder="Contoh: kereta ekonomi, bus ekonomi">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transport Udara</label>
                                <input type="text" class="form-control" id="allowance_transport_air" name="transport_air" placeholder="Contoh: pesawat ekonomi">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max Harga Tiket (Rp)</label>
                                <input type="number" class="form-control" id="allowance_ticket_max" name="ticket_max" placeholder="Kosongkan jika tidak terbatas">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max Reimburse Hotel / Malam (Rp)</label>
                                <input type="number" class="form-control" id="allowance_reimburse_max_per_night" name="reimburse_max_per_night">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bintang Hotel</label>
                                <input type="number" class="form-control" id="allowance_hotel_star" name="hotel_star" min="1" max="5">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Uang Makan / Hari (Rp)</label>
                                <input type="number" class="form-control" id="allowance_daily_meal" name="daily_meal">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Uang Saku / Hari (Rp)</label>
                                <input type="number" class="form-control" id="allowance_daily_pocket" name="daily_pocket">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bantuan Kos / Bulan (Rp)</label>
                                <input type="number" class="form-control" id="allowance_kos_per_month" name="kos_per_month">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bantuan Relokasi 1x Payment (Rp)</label>
                                <input type="number" class="form-control" id="allowance_relocation_one_time_payment" name="relocation_one_time_payment">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Uang Perbantuan / Minggu (Rp)</label>
                                <input type="number" class="form-control" id="allowance_mobilization_cost_per_week" name="mobilization_cost_per_week">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Catatan Mobilisasi/Bantuan</label>
                                <input type="text" class="form-control" id="allowance_mobilization_note" name="mobilization_note">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Plafon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL JOB LEVEL -->
<div class="modal fade" id="modalJobLevel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalJobLevelTitle">Level Pekerjaan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formJobLevel" onsubmit="submitJobLevel(event)">
                <div class="modal-body">
                    <input type="hidden" id="joblevel_id">
                    <div class="form-group">
                        <label>Key Level <small class="text-muted">(Huruf kecil, tanpa spasi)</small></label>
                        <input type="text" class="form-control" id="joblevel_level" required placeholder="Contoh: pelaksana, senior_middle">
                    </div>
                    <div class="form-group">
                        <label>Grade Range</label>
                        <input type="text" class="form-control" id="joblevel_grade" required placeholder="Contoh: 11-9, 8-7">
                    </div>
                    <div class="form-group">
                        <label>Label Tampilan</label>
                        <input type="text" class="form-control" id="joblevel_label" required placeholder="Contoh: Pelaksana, Senior Middle">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Level</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TRIP TYPE -->
<div class="modal fade" id="modalTripType" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTripTypeTitle">Jenis Perjalanan Dinas</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formTripType" onsubmit="submitTripType(event)">
                <div class="modal-body">
                    <input type="hidden" id="triptype_id">
                    <div class="form-group">
                        <label>Key Type <small class="text-muted">(Huruf kecil, tanpa spasi)</small></label>
                        <input type="text" class="form-control" id="triptype_key" required placeholder="Contoh: tugas_kerja, relokasi">
                    </div>
                    <div class="form-group">
                        <label>Nama Jenis Perjalanan</label>
                        <input type="text" class="form-control" id="triptype_name" required placeholder="Contoh: Tugas Kerja">
                    </div>
                    <div class="form-group">
                        <label>Durasi Standard</label>
                        <input type="text" class="form-control" id="triptype_duration" placeholder="Contoh: < 2 minggu">
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea class="form-control" id="triptype_note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Jenis</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL COMPONENT TUNJANGAN -->
<div class="modal fade" id="modalComponent" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalComponentTitle">Komponen Tunjangan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formComponent" onsubmit="submitComponent(event)">
                <div class="modal-body">
                    <input type="hidden" id="component_id">
                    <div class="form-group">
                        <label>Kode Key <small class="text-muted">(Huruf kecil, tanpa spasi, contoh: transport_land)</small></label>
                        <input type="text" class="form-control" id="component_code" required placeholder="Contoh: daily_meal">
                    </div>
                    <div class="form-group">
                        <label>Nama Komponen</label>
                        <input type="text" class="form-control" id="component_name" required placeholder="Contoh: Uang Makan / Hari">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select class="form-control" id="component_category" required>
                            <option value="Transportasi">Transportasi</option>
                            <option value="Akomodasi">Akomodasi</option>
                            <option value="Uang Saku">Uang Saku</option>
                            <option value="Bantuan Khusus">Bantuan Khusus</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipe Input</label>
                        <select class="form-control" id="component_type" required>
                            <option value="Nominal (Rp)">Nominal (Rp)</option>
                            <option value="Teks">Teks</option>
                            <option value="Teks / List Pilihan">Teks / List Pilihan</option>
                            <option value="Angka (Star)">Angka (Star)</option>
                            <option value="Angka">Angka</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Keterangan / Deskripsi Regulasi</label>
                        <textarea class="form-control" id="component_description" rows="3" placeholder="Penjelasan mengenai komponen regulasi ini"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Komponen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var rawAllowances = @json($allowances);

function editAllowance(tripTypeKey, jobLevelKey, tripTypeName, jobLevelLabel) {
    $('#modalAllowanceTitle').text('Edit Plafon: ' + tripTypeName + ' - ' + jobLevelLabel);
    $('#allowance_trip_type_key').val(tripTypeKey);
    $('#allowance_job_level_key').val(jobLevelKey);

    var allw = rawAllowances.find(function(item) {
        return item.trip_type_key === tripTypeKey && item.job_level_key === jobLevelKey;
    });

    if (allw) {
        var land = allw.transport_land;
        if (typeof land === 'string') {
            try { land = JSON.parse(land); } catch(e){}
        }
        $('#allowance_transport_land').val(Array.isArray(land) ? land.join(', ') : (land || ''));
        $('#allowance_transport_air').val(allw.transport_air || '');
        $('#allowance_ticket_max').val(allw.ticket_max !== null ? allw.ticket_max : '');
        $('#allowance_reimburse_max_per_night').val(allw.reimburse_max_per_night !== null ? allw.reimburse_max_per_night : '');
        $('#allowance_hotel_star').val(allw.hotel_star !== null ? allw.hotel_star : '');
        $('#allowance_daily_meal').val(allw.daily_meal !== null ? allw.daily_meal : '');
        $('#allowance_daily_pocket').val(allw.daily_pocket !== null ? allw.daily_pocket : '');
        $('#allowance_kos_per_month').val(allw.kos_per_month !== null ? allw.kos_per_month : '');
        $('#allowance_relocation_one_time_payment').val(allw.relocation_one_time_payment !== null ? allw.relocation_one_time_payment : '');
        $('#allowance_mobilization_cost_per_week').val(allw.mobilization_cost_per_week !== null ? allw.mobilization_cost_per_week : '');
        $('#allowance_mobilization_note').val(allw.mobilization_note || '');
    } else {
        $('#formAllowance')[0].reset();
        $('#allowance_trip_type_key').val(tripTypeKey);
        $('#allowance_job_level_key').val(jobLevelKey);
    }

    $('#modalAllowance').modal('show');
}

function submitAllowance(e) {
    e.preventDefault();
    $.ajax({
        url: 'hrd-sppd-master-allowance-save',
        type: 'POST',
        data: $('#formAllowance').serialize(),
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Terjadi kesalahan saat menyimpan plafon');
        }
    });
}

// Job Level Functions
function addJobLevel() {
    $('#joblevel_id').val('');
    $('#joblevel_level').val('').prop('readonly', false);
    $('#joblevel_grade').val('');
    $('#joblevel_label').val('');
    $('#modalJobLevelTitle').text('Tambah Level Pekerjaan Baru');
    $('#modalJobLevel').modal('show');
}

function editJobLevel(id, level, grade, label) {
    $('#joblevel_id').val(id);
    $('#joblevel_level').val(level).prop('readonly', true);
    $('#joblevel_grade').val(grade);
    $('#joblevel_label').val(label);
    $('#modalJobLevelTitle').text('Edit Level Pekerjaan');
    $('#modalJobLevel').modal('show');
}

function submitJobLevel(e) {
    e.preventDefault();
    var id = $('#joblevel_id').val();
    var url = id ? ('hrd-sppd-master-job-level-update/' + id) : 'hrd-sppd-master-job-level-store';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            level: $('#joblevel_level').val(),
            grade: $('#joblevel_grade').val(),
            label: $('#joblevel_label').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Gagal menyimpan Job Level');
        }
    });
}

function deleteJobLevel(id, label) {
    if (!confirm('Apakah Anda yakin ingin menghapus Job Level "' + label + '"? Data matriks terkait juga akan terhapus.')) return;
    $.ajax({
        url: 'hrd-sppd-master-job-level-delete/' + id,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        }
    });
}

// Trip Type Functions
function addTripType() {
    $('#triptype_id').val('');
    $('#triptype_key').val('').prop('readonly', false);
    $('#triptype_name').val('');
    $('#triptype_duration').val('');
    $('#triptype_note').val('');
    $('#modalTripTypeTitle').text('Tambah Jenis Perjalanan Dinas');
    $('#modalTripType').modal('show');
}

function editTripType(id, key, name, duration, note) {
    $('#triptype_id').val(id);
    $('#triptype_key').val(key).prop('readonly', true);
    $('#triptype_name').val(name);
    $('#triptype_duration').val(duration);
    $('#triptype_note').val(note);
    $('#modalTripTypeTitle').text('Edit Jenis Perjalanan Dinas');
    $('#modalTripType').modal('show');
}

function submitTripType(e) {
    e.preventDefault();
    var id = $('#triptype_id').val();
    var url = id ? ('hrd-sppd-master-trip-type-update/' + id) : 'hrd-sppd-master-trip-type-store';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            type_key: $('#triptype_key').val(),
            name: $('#triptype_name').val(),
            duration: $('#triptype_duration').val(),
            note: $('#triptype_note').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Gagal menyimpan Jenis Perjalanan');
        }
    });
}

function deleteTripType(id, name) {
    if (!confirm('Apakah Anda yakin ingin menghapus Jenis Perjalanan "' + name + '"? Data matriks terkait juga akan terhapus.')) return;
    $.ajax({
        url: 'hrd-sppd-master-trip-type-delete/' + id,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        }
    });
}

// Component Functions
function addComponent() {
    $('#component_id').val('');
    $('#component_code').val('').prop('readonly', false);
    $('#component_name').val('');
    $('#component_category').val('Transportasi');
    $('#component_type').val('Nominal (Rp)');
    $('#component_description').val('');
    $('#modalComponentTitle').text('Tambah Komponen Tunjangan Baru');
    $('#modalComponent').modal('show');
}

function editComponent(id, code, name, category, type, description) {
    $('#component_id').val(id);
    $('#component_code').val(code).prop('readonly', true);
    $('#component_name').val(name);
    $('#component_category').val(category);
    $('#component_type').val(type);
    $('#component_description').val(description);
    $('#modalComponentTitle').text('Edit Komponen Tunjangan');
    $('#modalComponent').modal('show');
}

function submitComponent(e) {
    e.preventDefault();
    var id = $('#component_id').val();
    var url = id ? ('hrd-sppd-master-component-update/' + id) : 'hrd-sppd-master-component-store';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            code: $('#component_code').val(),
            name: $('#component_name').val(),
            category: $('#component_category').val(),
            type: $('#component_type').val(),
            description: $('#component_description').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert(resp.message);
                location.reload();
            } else {
                alert(resp.message || 'Gagal menyimpan');
            }
        },
        error: function(err) {
            alert('Gagal menyimpan Komponen Tunjangan');
        }
    });
}

function deleteComponent(id, name) {
    if (!confirm('Apakah Anda yakin ingin menghapus Komponen Tunjangan "' + name + '"?')) return;
    $.ajax({
        url: 'hrd-sppd-master-component-delete/' + id,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        }
    });
}

function syncFromJson() {
    if (!confirm('Peringatan: Reset/Sync akan menghapus perubahan manual dan mengisi ulang data awal dari matric.json. Lanjutkan?')) return;
    $.ajax({
        url: 'hrd-sppd-master-sync-json',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(resp) {
            alert(resp.message);
            location.reload();
        },
        error: function() {
            alert('Terjadi kesalahan saat memproses sync');
        }
    });
}
</script>
@endsection
