@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .ga-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .ga-filter-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .ga-filter-group .form-control,
    .ga-filter-group .form-select {
        width: auto;
        min-width: 150px;
    }
    .ga-btn-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
</style>

<div class="main-content-header">
    <div>
        <h3 class="main-content-title tx-24 mg-b-5">GA Room Booking & Shared Facilities</h3>
        <ol class="breadcrumb mg-b-0">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-ticket-index') }}">GA Operation</a></li>
            <li class="breadcrumb-item active" aria-current="page">Room Booking</li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="row mb-3">
            <div class="col-12">
                <ul class="nav nav-pills" id="roomTab" role="tablist" style="gap: 0.5rem;">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="schedule-tab" data-bs-toggle="pill" data-toggle="pill" href="#tabSchedule" role="tab">Jadwal & Reservasi</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="rooms-tab" data-bs-toggle="pill" data-toggle="pill" href="#tabRooms" role="tab">Daftar Ruang Meeting</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="roomTabContent">
            <!-- TAB 1: JADWAL & RESERVASI -->
            <div class="tab-pane fade show active" id="tabSchedule" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="ga-toolbar">
                            <div class="ga-filter-group">
                                <label class="mb-0 fw-bold">Filter Tanggal:</label>
                                <input type="date" id="filterDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                <label class="mb-0 fw-bold ms-2">Ruangan:</label>
                                <select id="filterRoom" class="form-control form-control-sm">
                                    <option value="">Semua Ruangan</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->Room_Name }} ({{ $r->Location }})</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-info text-white" onclick="reloadBookingGrid()">
                                    <i class="mdi mdi-filter"></i> Filter
                                </button>
                            </div>
                            <div class="ga-btn-group">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalBookRoom" onclick="showRoomModal('#modalBookRoom')">
                                    <i class="mdi mdi-calendar-plus"></i> Booking Ruangan Baru
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="cancelSelectedBooking()">
                                    <i class="mdi mdi-calendar-remove"></i> Batalkan Booking
                                </button>
                            </div>
                        </div>
                        <div id="gridBookings" style="width:100%;height:380px;background-color:white;"></div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: DAFTAR RUANG MEETING -->
            <div class="tab-pane fade" id="tabRooms" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="ga-toolbar">
                            <h5 class="card-title mb-0">Master Data Ruang Meeting & Fasilitas</h5>
                            <div class="ga-btn-group">
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalAddRoom" onclick="showRoomModal('#modalAddRoom')">
                                    <i class="mdi mdi-plus"></i> Tambah Ruangan Baru
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="reloadRoomGrid()">
                                    <i class="mdi mdi-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="gridRooms" style="width:100%;height:380px;background-color:white;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Booking Ruangan -->
<div class="modal fade" id="modalBookRoom" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formBookRoom" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Form Booking Ruang Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Ruangan <span class="text-danger">*</span></label>
                        <select name="Room_ID" class="form-control" required>
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->Room_Name }} (Kapasitas: {{ $r->Capacity }} org)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Tanggal Rapat / Kegiatan <span class="text-danger">*</span></label>
                        <input type="date" name="Booking_Date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="Start_Time" class="form-control" value="09:00" required>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="End_Time" class="form-control" value="10:00" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Nama Pemesan / Host <span class="text-danger">*</span></label>
                        <input type="text" name="Organizer_Name" class="form-control" value="{{ Auth::user()->Name ?? Auth::user()->username }}" required>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Departemen & No Kontak</label>
                        <input type="text" name="Organizer_Department" class="form-control" placeholder="Contoh: HRD / Ext. 102">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>Judul / Topik Rapat <span class="text-danger">*</span></label>
                    <input type="text" name="Subject" class="form-control" placeholder="Contoh: Koordinasi Proyek Mingguan" required>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Estimasi Jumlah Peserta</label>
                        <input type="number" name="Attendees_Count" class="form-control" value="5" min="1">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Kebutuhan Tambahan Fasilitas GA</label>
                        <div class="mt-2 d-flex flex-wrap gap-3">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="checkbox" name="Need_Projector" value="1" class="form-check-input"> Proyektor
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="checkbox" name="Need_Sound" value="1" class="form-check-input"> Sound & Mic
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="checkbox" name="Need_Layout" value="1" class="form-check-input"> Layout Khusus
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label>Catatan Setup / Penataan Kursi</label>
                    <textarea name="Layout_Note" class="form-control" rows="2" placeholder="Contoh: Meja bentuk U-shape, siapkan kabel rol"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Konfirmasi Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="modalAddRoom" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formAddRoom" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Daftarkan Ruang Rapat Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Kode Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="Room_Code" class="form-control" placeholder="Contoh: R-MTG-01" required>
                </div>
                <div class="form-group mb-3">
                    <label>Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="Room_Name" class="form-control" placeholder="Contoh: Ruang Rapat Arjuna" required>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Lokasi / Site</label>
                        <input type="text" name="Location" class="form-control" value="{{ Auth::user()->Location ?? 'HO' }}">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Lantai / Gedung</label>
                        <input type="text" name="Floor" class="form-control" placeholder="Contoh: Lantai 2">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Kapasitas (Orang)</label>
                        <input type="number" name="Capacity" class="form-control" value="10" min="1">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Akses Khusus (Restricted)</label>
                        <select name="Is_Restricted" class="form-control">
                            <option value="0" selected>Terbuka Bebas (Open)</option>
                            <option value="1">Perlu Persetujuan (Restricted)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label>Fasilitas Standar</label>
                    <input type="text" name="Facilities" class="form-control" placeholder="Contoh: AC, Smart TV, Whiteboard, Wifi">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Simpan Ruangan</button>
            </div>
        </form>
    </div>
</div>

<script>
var gridBookings, gridRooms;

function showRoomModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (typeof $ !== 'undefined' && $.fn.modal) {
        $(el).modal('show');
    }
}

function hideRoomModal(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        var instance = window.bootstrap.Modal.getInstance(el);
        if (instance) instance.hide();
    }
    if (typeof $ !== 'undefined' && $.fn.modal) {
        $(el).modal('hide');
    }
}

function initGrids() {
    gridBookings = new dhtmlXGridObject('gridBookings');
    gridBookings.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridBookings.setHeader("No,No Booking,Ruangan,Tanggal,Jam,Topik Rapat,Pemesan,Departemen,Peserta,Status");
    gridBookings.setInitWidths("50,150,160,100,120,200,140,110,80,100");
    gridBookings.setColAlign("center,left,left,center,center,left,left,left,center,center");
    gridBookings.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    gridBookings.setColSorting("int,str,str,date,str,str,str,str,int,str");
    gridBookings.setSkin("dhx_skyblue");
    gridBookings.init();
    reloadBookingGrid();

    gridRooms = new dhtmlXGridObject('gridRooms');
    gridRooms.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    gridRooms.setHeader("No,Kode Ruang,Nama Ruangan,Lokasi,Gedung,Lantai,Kapasitas,Fasilitas,Akses,Status");
    gridRooms.setInitWidths("50,120,160,100,100,80,90,180,90,90");
    gridRooms.setColAlign("center,left,left,center,left,center,center,left,center,center");
    gridRooms.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    gridRooms.setColSorting("int,str,str,str,str,str,int,str,str,str");
    gridRooms.setSkin("dhx_skyblue");
    gridRooms.init();
    reloadRoomGrid();
}

function reloadBookingGrid() {
    var date = $('#filterDate').val();
    var roomId = $('#filterRoom').val();
    gridBookings.clearAndLoad("{{ url('hrd-ga-room-booking-data') }}?date=" + encodeURIComponent(date) + "&room_id=" + encodeURIComponent(roomId));
}

function reloadRoomGrid() {
    gridRooms.clearAndLoad("{{ url('hrd-ga-room-data') }}");
}

function cancelSelectedBooking() {
    var id = gridBookings.getSelectedRowId();
    if (!id) {
        alert('Pilih jadwal booking yang ingin dibatalkan terlebih dahulu.');
        return;
    }
    var reason = prompt('Masukkan alasan pembatalan booking:');
    if (reason !== null && reason.trim() !== '') {
        $.post("{{ url('hrd-ga-room-booking-cancel') }}", {
            _token: "{{ csrf_token() }}",
            id: id,
            reason: reason
        }, function(resp) {
            alert(resp.message);
            reloadBookingGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal membatalkan booking.');
        });
    }
}

$(document).ready(function() {
    initGrids();

    $('#roomTab a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('#roomTab .nav-link').removeClass('active');
        $(this).addClass('active');
        $('#roomTabContent .tab-pane').removeClass('show active');
        $(target).addClass('show active');

        setTimeout(function() {
            if (target === '#tabSchedule' && gridBookings) gridBookings.setSizes();
            if (target === '#tabRooms' && gridRooms) gridRooms.setSizes();
        }, 50);
    });

    $('#formBookRoom').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-room-booking-save') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideRoomModal('#modalBookRoom');
            $('#formBookRoom')[0].reset();
            reloadBookingGrid();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal membuat booking.');
        });
    });

    $('#formAddRoom').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ url('hrd-ga-room-save') }}", $(this).serialize(), function(resp) {
            alert(resp.message);
            hideRoomModal('#modalAddRoom');
            $('#formAddRoom')[0].reset();
            reloadRoomGrid();
            location.reload();
        }).fail(function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal menyimpan ruangan.');
        });
    });
});
</script>
@endsection
