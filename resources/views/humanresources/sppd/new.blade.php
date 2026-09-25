@extends('layout.mainlayout')

@section('content-wrapper')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
@csrf
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="hrd-sppd-index">Perjalanan Dinas</a></li>
        <li class="breadcrumb-item active"><span class="active">New Request SPPD</span></li>
    </ol>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white p-2">
                <h4 class="card-title m-0 d-inline-block">Form Request Perjalanan Dinas</h4>
                <button type="button" class="float-right btn btn-secondary" onclick="history.back();"><i class="fa fa-times"></i> Close</button>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="SPPD_No">No SPPD</label>
                            <input type="text" id="SPPD_No" class="form-control shadow-sm" name="SPPD_No" value="{{ $SPPD_No }}" readonly />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Requester_NIK">NIK Karyawan Pengaju</label>
                            <input type="text" id="Requester_NIK" class="form-control shadow-sm" name="Requester_NIK" value="{{ $user->Emp_ID ?: '' }}" readonly />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Sppd_Type">Jenis SPPD</label>
                            <select id="Sppd_Type" class="form-control shadow-sm" onchange="toggleType();">
                                <option value="NORMAL">Regular SPPD</option>
                                <option value="PERBANTUAN">Perbantuan Sementara</option>
                                <option value="RELOKASI" disabled>Relokasi/Mutasi (belum tersedia)</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Date_Start">Tanggal Mulai</label>
                            <input type="text" id="Date_Start" class="form-control shadow-sm" onblur="updateAllowanceInfo()" onchange="updateAllowanceInfo()" />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Date_Finish">Tanggal Selesai</label>
                            <input type="text" id="Date_Finish" class="form-control shadow-sm" onblur="updateAllowanceInfo()" onchange="updateAllowanceInfo()" />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Department">Nama Departemen</label>
                            <select id="Department" class="form-control shadow-sm" name="Department">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach ($departments as $d)
                                <option value="{{ $d->Department }}">{{ $d->Department }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Requester_Name">Nama Karyawan Pengaju</label>
                            <input type="text" id="Requester_Name" class="form-control shadow-sm" name="Requester_Name" value="{{ $user->Name ?: $user->username }}" />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Route_Start">Rute (Asal)</label>
                            <select id="Route_Start" class="form-control shadow-sm">
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach (($locations ?? collect()) as $loc)
                                <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Rute_Mode">Rute (Tujuan)</label>
                            <select id="Rute_Mode" class="form-control shadow-sm" onchange="updateRuteMode();">
                                <option value="KANTOR">Kantor</option>
                                <option value="TITIK">Pilih Titik Tujuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-12" id="ruteKantor">
                        <div class="form-group">
                            <label for="Route_Finish">Lokasi Kantor (Tujuan)</label>
                            <select id="Route_Finish" class="form-control shadow-sm">
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach (($locations ?? collect()) as $loc)
                                <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-12" id="ruteTitik" style="display:none;">
                        <div class="form-group">
                            <label for="Destination">Tujuan Perjalanan Dinas</label>
                            <input type="text" id="Destination" class="form-control shadow-sm" name="Destination" placeholder="contoh: Jakarta" />
                            <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="btnPickDestPoint">
                                <i class="fa fa-map-marker-alt"></i> Pilih Titik Tujuan
                            </button>
                            <div class="small mt-1" id="destPointInfo"></div>
                            <input type="hidden" id="Dest_Lat" />
                            <input type="hidden" id="Dest_Lng" />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Schedule_Date">Tanggal Kegiatan</label>
                            <input type="text" id="Schedule_Date" class="form-control shadow-sm" name="Schedule_Date" />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Departure_Time">Jam Berangkat <span class="text-danger">*</span></label>
                            <input type="time" id="Departure_Time" class="form-control shadow-sm" name="Departure_Time" />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Return_Time">Jam Kembali <span class="text-danger">*</span></label>
                            <input type="time" id="Return_Time" class="form-control shadow-sm" name="Return_Time" />
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="Program_Name">Kegiatan</label>
                            <input type="text" id="Program_Name" class="form-control shadow-sm" name="Program_Name" placeholder="contoh: Koordinasi dengan Mitra" />
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div id="allowanceInfo"></div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                            <label>Rincian Biaya yang Timbul</label>
                            <table id="tblCost" class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="180">Biaya (Rp)</th>
                                        <th>Keterangan</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" id="Cost_0" class="form-control" onkeyup="hitungTotal();" /></td>
                                        <td><input type="text" id="Desc_0" class="form-control" /></td>
                                        <td><button type="button" class="btn btn-success btn-sm" onclick="tambahRow();">+</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="font-weight-bold">
                                Total Estimasi : Rp <span id="TotalCost">0</span>
                            </div>
                            <input type="hidden" id="Estimated_Cost" value="0" />
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                            <label for="Description">Keterangan</label>
                            <textarea id="Description" class="form-control shadow-sm" name="Description" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="col-md-12 col-12" id="pbSection" style="display:none;">
                        <div class="card border-warning mt-2">
                            <div class="card-header bg-warning text-dark p-2">
                                <h6 class="m-0 font-weight-bold">Perbantuan Sementara</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="Origin_Site">Asal Site</label>
                                            <select id="Origin_Site" class="form-control shadow-sm">
                                                <option value="">-- Pilih Lokasi --</option>
                                                @foreach (($locations ?? collect()) as $loc)
                                                <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="Target_Site">Lokasi Perbantuan</label>
                                            <select id="Target_Site" class="form-control shadow-sm">
                                                <option value="">-- Pilih Lokasi --</option>
                                                @foreach (($locations ?? collect()) as $loc)
                                                <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-12">
                                        <div class="form-group">
                                            <label for="Reason">Alasan Perbantuan</label>
                                            <textarea id="Reason" class="form-control shadow-sm" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="card border-info mt-2">
                            <div class="card-header bg-info text-white p-2">
                                <label class="m-0 font-weight-bold"><input type="checkbox" id="Need_Car" value="1" /> Butuh Mobil Operasional (otomatis booking saat SPPD disetujui)</label>
                            </div>
                            <div class="card-body" id="NeedCarBody" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="Car_Jenis_Mobil">Jenis Mobil</label>
                                            <select id="Car_Jenis_Mobil" class="form-control shadow-sm">
                                                <option value="">-- Pilih Jenis Mobil --</option>
                                                @foreach(($fleetList ?? ['Rush','Avanza','Innova','Pajero','Alphard','Hiace','Other']) as $m)
                                                <option value="{{ $m }}">{{ ($m == 'Other') ? 'Lainnya' : $m }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-group">
                                            <label for="Car_Tgl_Pinjam">Tgl Pinjam</label>
                                            <input type="text" id="Car_Tgl_Pinjam" class="form-control shadow-sm" />
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-group">
                                            <label for="Car_Tgl_Kembali">Tgl Kembali</label>
                                            <input type="text" id="Car_Tgl_Kembali" class="form-control shadow-sm" />
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-group">
                                            <label for="Car_Jam_Pinjam">Jam Pinjam</label>
                                            <input type="text" id="Car_Jam_Pinjam" class="form-control shadow-sm" value="08:00" />
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-group">
                                            <label for="Car_Jam_Kembali">Jam Kembali</label>
                                            <input type="text" id="Car_Jam_Kembali" class="form-control shadow-sm" value="17:00" />
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">Tanggal default mengikuti Tanggal Kegiatan. Booking dibuat otomatis saat status jadi ACCEPTED; bila gagal (misal mobil bentrok) buat manual via menu Car Rent.</small>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-12 mt-2">
                    <button class="btn btn-success" id="submitBtn">Submit</button>
                    <a href="hrd-sppd-index" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Schedule_Date"]);
    myCalendar.setDateFormat("%Y-%m-%d");
    var rangeCal = new dhtmlXCalendarObject(["Date_Start", "Date_Finish"]);
    rangeCal.setDateFormat("%Y-%m-%d");
    rangeCal.attachEvent("onChange", function() { updateAllowanceInfo(); });
    rangeCal.attachEvent("onClick", function() { updateAllowanceInfo(); });
    var carCal = new dhtmlXCalendarObject(["Car_Tgl_Pinjam", "Car_Tgl_Kembali"]);
    carCal.setDateFormat("%Y-%m-%d");

    // Searchable dropdowns (select2 is loaded globally in mainlayout)
    $('#Department, #Route_Start, #Route_Finish, #Origin_Site, #Target_Site').select2({
        width: '100%',
        placeholder: '-- Pilih --',
        allowClear: true
    });

    var matrixData = null;
    var userJobLevel = "{{ $jobLevel ?? 'pelaksana' }}";

    var TYPE_CODES = {
        NORMAL: 'RS',
        PERBANTUAN: 'PS',
        RELOKASI: 'RL'
    };
    var TYPE_COUNTS = @json($typeCounts ?? []);
    var SPPD_YMD = "{{ $ymd ?? date('ymd') }}";

    function updateSppdNo() {
        var t = $('#Sppd_Type').val();
        var code = TYPE_CODES[t] || 'RS';
        var seq = ((TYPE_COUNTS[t] || 0) + 1);
        $('#SPPD_No').val('SPPD/' + code + '/GEU/' + SPPD_YMD + '-' + seq);
    }

    function updateRuteMode() {
        var m = $('#Rute_Mode').val();
        if (m === 'TITIK') {
            $('#ruteKantor').hide();
            $('#ruteTitik').show();
            $('#Route_Finish').val('');
            $('#Route_Finish').trigger('change.select2');
        } else {
            $('#ruteKantor').show();
            $('#ruteTitik').hide();
            $('#Destination').val('');
            $('#Dest_Lat').val('');
            $('#Dest_Lng').val('');
            $('#destPointInfo').html('');
        }
    }

    // Load matrix on page load
    $(document).ready(function() {
        updateSppdNo();
        updateRuteMode();
        $.get('hrd-sppd-matrix', function(resp) {
            if (resp.status === 'success') {
                matrixData = resp.data;
                updateAllowanceInfo();
            }
        });
    });

    function toggleType() {
        var t = $('#Sppd_Type').val();
        $('#pbSection').toggle(t === 'PERBANTUAN');
        if (t === 'RELOKASI') {
            $('#Sppd_Type').val('NORMAL');
            toggleType();
            alert('Relokasi/Mutasi belum tersedia. Silakan pilih jenis lain.');
        }
        updateSppdNo();
        updateAllowanceInfo();
    }

    function updateAllowanceInfo() {
        if (!matrixData) return;

        var sppdType = $('#Sppd_Type').val();
        var tripKey = sppdType === 'NORMAL' ? 'tugas_kerja' : (sppdType === 'PERBANTUAN' ? 'perbantuan_sementara' : 'relokasi');

        var trip = matrixData.trip_types[tripKey];
        if (!trip || !trip.allowances || !trip.allowances[userJobLevel]) return;

        var allowance = trip.allowances[userJobLevel];

        // Build info text
        var info = '<div class="alert alert-info mt-2"><strong>Allowance untuk Level Anda (' + userJobLevel.replace(/_/g, ' ').toUpperCase() + '):</strong><br>';

        if (allowance.transport) {
            var landTxt = '';
            if (Array.isArray(allowance.transport.land)) landTxt = allowance.transport.land.join(', ');
            else if (allowance.transport.land) landTxt = allowance.transport.land;
            info += '<strong>Transport:</strong> ' + (allowance.transport.air || landTxt || '-');
            if (allowance.transport.ticket_max) info += ' (Max: Rp ' + allowance.transport.ticket_max.toLocaleString('id-ID') + ')';
            if (allowance.transport.reimburse_max_per_night) info += ' | Reimburse/malam: Rp ' + allowance.transport.reimburse_max_per_night.toLocaleString('id-ID');
            info += '<br>';
        }

        if (allowance.accommodation) {
            if ((allowance.accommodation.hotel_star || '-') != '-') {
                info += '<strong>Hotel:</strong> ' + (allowance.accommodation.hotel_star || '-') + ' bintang<br>';
            }
        }

        if (allowance.daily) {
            if ((allowance.daily.meal || 0) > 0) {
                info += '<strong>Uang Saku:</strong> Saku Rp ' + (allowance.daily.pocket || 0).toLocaleString('id-ID') + '<br>';
            }
            if ((allowance.daily.pocket || 0) > 0) {
                info += '<strong>Uang Makan:</strong> Makan Rp ' + (allowance.daily.meal || 0).toLocaleString('id-ID') + '<br>';
                
            }
        }

        if (allowance.assistance && allowance.assistance.kos_per_month) {
            info += '<strong>Bantuan:</strong> Kos Rp ' + (allowance.assistance.kos_per_month || 0).toLocaleString('id-ID') + '/bulan<br>';
        }

        if (allowance.relocation && allowance.relocation.one_time_payment) {
            info += '<strong>Relokasi:</strong> Rp ' + (allowance.relocation.one_time_payment || 0).toLocaleString('id-ID') + ' (sekali)<br>';
        }

        if (allowance.mobilization && allowance.mobilization.cost_per_week) {
            info += '<strong>Uang Perbantuan:</strong> Rp ' + (allowance.mobilization.cost_per_week || 0).toLocaleString('id-ID') + '/minggu<br>';
        }

        info += '</div>';

        $('#allowanceInfo').html(info);
        buildRegulationRows(allowance);
    }

    $('#Need_Car').on('change', function() {
        var need = $(this).is(':checked');
        $('#NeedCarBody').toggle(need);
        if (need) {
            if (!$('#Car_Tgl_Pinjam').val()) $('#Car_Tgl_Pinjam').val($('#Schedule_Date').val());
            if (!$('#Car_Tgl_Kembali').val()) $('#Car_Tgl_Kembali').val($('#Schedule_Date').val());
        }
    });
    $('#Schedule_Date').on('change blur', function() {
        if ($('#Need_Car').is(':checked')) {
            if (!$('#Car_Tgl_Pinjam').val()) $('#Car_Tgl_Pinjam').val($(this).val());
            if (!$('#Car_Tgl_Kembali').val()) $('#Car_Tgl_Kembali').val($(this).val());
        }
    });

    var rowCost = 1;

    function collectManualRows() {
        var manuals = [];
        $('#tblCost tbody tr').not('.reg-row').each(function() {
            manuals.push({
                cost: $(this).find("input[id^='Cost_']").val() || '',
                desc: $(this).find("input[id^='Desc_']").val() || ''
            });
        });
        return manuals;
    }

    function moneyID(v) {
        return Number(v).toLocaleString('id-ID');
    }

    function buildRegulationRows(allowance) {
        var manuals = collectManualRows();

        var a = Date.parse($('#Date_Start').val());
        var b = Date.parse($('#Date_Finish').val());
        var hasDates = (!isNaN(a) && !isNaN(b) && b >= a);
        var days = hasDates ? Math.round((b - a) / 86400000) + 1 : 1;
        var nights = Math.max(days - 1, 0);
        var weeks = hasDates ? Math.ceil(days / 7) : 1;
        var months = hasDates ? Math.ceil(days / 30) : 1;

        var reg = [];
        var t = allowance.transport || {};
        var acc = allowance.accommodation || {};
        var daily = allowance.daily || {};

        if (t.ticket_max) {
            reg.push({ cost: t.ticket_max, desc: 'Tiket PP (maks. Rp ' + moneyID(t.ticket_max) + ')' });
        }
        if (t.reimburse_max_per_night && nights > 0) {
            var peng = 'Penginapan';
            if (acc.hotel_star) peng += ' \u2605' + acc.hotel_star;
            peng += ' (Rp ' + moneyID(t.reimburse_max_per_night) + '/malam' + (hasDates ? ' \u00d7 ' + nights + ' malam' : '') + ')';
            reg.push({ cost: t.reimburse_max_per_night * nights, desc: peng });
        }
        if (daily.meal) {
            reg.push({ cost: daily.meal * days, desc: 'Uang Makan (Rp ' + moneyID(daily.meal) + '/hari' + (hasDates ? ' \u00d7 ' + days + ' hari' : '') + ')' });
        }
        if (daily.pocket) {
            reg.push({ cost: daily.pocket * days, desc: 'Uang Saku (Rp ' + moneyID(daily.pocket) + '/hari' + (hasDates ? ' \u00d7 ' + days + ' hari' : '') + ')' });
        }
        if (allowance.assistance && allowance.assistance.kos_per_month) {
            var kos = allowance.assistance.kos_per_month;
            var kosTotal = kos * months;
            reg.push({ cost: kosTotal, desc: 'Uang Perbantuan / Kos (Rp ' + moneyID(kos) + '/bulan' + (hasDates ? ' \u00d7 ' + months + ' bulan' : '') + ')' });
        }
        if (allowance.relocation && allowance.relocation.one_time_payment) {
            reg.push({ cost: allowance.relocation.one_time_payment, desc: 'Bantuan Relokasi (sekali bayar)' });
        }
        if (allowance.mobilization && allowance.mobilization.cost_per_week) {
            var mob = allowance.mobilization.cost_per_week;
            var mobTotal = mob * weeks;
            reg.push({ cost: mobTotal, desc: 'Uang Perbantuan (Rp ' + moneyID(mob) + '/minggu' + (hasDates ? ' \u00d7 ' + weeks + ' minggu' : '') + ')' });
        }

        renderCostRows(reg, manuals);
    }

    function renderCostRows(regRows, manuals) {
        var escapeAttr = function(s) {
            return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        };
        var $tb = $('#tblCost tbody').empty();
        var idx = 0;

        $.each(regRows, function(_, r) {
            $tb.append(
                "<tr class='reg-row' data-reg='1'>" +
                "<td><input type='text' id='Cost_" + idx + "' class='form-control bg-light' value='" + escapeAttr(r.cost) + "' readonly /></td>" +
                "<td><input type='text' id='Desc_" + idx + "' class='form-control bg-light' value=\"" + escapeAttr(r.desc) + "\" readonly /></td>" +
                "<td class='text-center'><i class='fa fa-lock text-muted' title='Mengikuti regulasi master SPPD'></i></td>" +
                "</tr>"
            );
            idx++;
        });

        if (!manuals.length) manuals.push({ cost: '', desc: '' });
        $.each(manuals, function(i, m) {
            var btn = (i === 0)
                ? "<button type='button' class='btn btn-success btn-sm' onclick='tambahRow();'>+</button>"
                : "<button type='button' class='btn btn-danger btn-sm' onclick='hapusRow(this);'>-</button>";
            $tb.append(
                "<tr>" +
                "<td><input type='text' id='Cost_" + idx + "' class='form-control' onkeyup='hitungTotal();' value='" + escapeAttr(m.cost) + "' /></td>" +
                "<td><input type='text' id='Desc_" + idx + "' class='form-control' value=\"" + escapeAttr(m.desc) + "\" /></td>" +
                "<td class='text-center'>" + btn + "</td>" +
                "</tr>"
            );
            idx++;
        });

        rowCost = idx;
        hitungTotal();
    }

    function tambahRow() {
        var html = "<tr>" +
            "<td><input type='text' id='Cost_" + rowCost + "' class='form-control' onkeyup='hitungTotal();' /></td>" +
            "<td><input type='text' id='Desc_" + rowCost + "' class='form-control' /></td>" +
            "<td><button type='button' class='btn btn-danger btn-sm' onclick='hapusRow(this);'>-</button></td>" +
            "</tr>";
        $("#tblCost tbody").append(html);
        rowCost++;
    }

    function hapusRow(btn) {
        $(btn).closest("tr").remove();
        hitungTotal();
    }

    function hitungTotal() {
        var total = 0;
        $("[id^='Cost_']").each(function() {
            var v = parseFloat($(this).val().replace(/\./g, "").replace(/,/g, "."));
            if (!isNaN(v)) total += v;
        });
        $("#TotalCost").text(total.toLocaleString("id-ID"));
        $("#Estimated_Cost").val(total);
    }

    function loadLeafletAssets(cb) {
        if (typeof L !== 'undefined') {
            cb();
            return;
        }
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
        var script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = cb;
        document.head.appendChild(script);
    }

    var destMap = null,
        destMarker = null,
        destLatLng = null;

    function renderDestPointInfo(lat, lng) {
        if (!lat || !lng) {
            $('#destPointInfo').html('');
            return;
        }
        var url = 'https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lng + '#map=16/' + lat + '/' + lng;
        $('#destPointInfo').html('Titik: ' + lat + ', ' + lng + ' &mdash; <a href="' + url + '" target="_blank">Lihat di OpenStreetMap</a>');
    }

    function applyDestPoint() {
        if (!destLatLng) {
            alert('Klik pada peta untuk memilih titik tujuan terlebih dahulu.');
            return false;
        }
        var lat = destLatLng.lat.toFixed(6),
            lng = destLatLng.lng.toFixed(6);
        $('#Dest_Lat').val(lat);
        $('#Dest_Lng').val(lng);
        renderDestPointInfo(lat, lng);
        // Reverse geocode via Nominatim (free, OSM) to fill the destination name
        $.get('https://nominatim.openstreetmap.org/reverse', {
                format: 'jsonv2',
                lat: lat,
                lon: lng,
                zoom: 16,
                addressdetails: 1
            })
            .done(function(res) {
                if (res && res.display_name) {
                    $('#Destination').val(res.display_name);
                }
            });
    }

    $('#btnPickDestPoint').on('click', function() {
        loadLeafletAssets(function() {
            var bodyHtml = '<div id="destPickerMap" style="height:420px;"></div>' +
                '<div class="small text-muted mt-1">Klik/tap pada peta untuk memilih titik tujuan, lalu seret penanda untuk menyesuaikan.</div>' +
                '<div class="mt-1"><strong>Titik:</strong> <span id="destPickerCoord">-</span></div>';
            AppModal.open({
                title: 'Pilih Titik Tujuan',
                size: 'lg',
                body: bodyHtml,
                actions: [{
                        label: 'Batal',
                        class: 'btn-secondary'
                    },
                    {
                        label: 'Gunakan Titik Ini',
                        class: 'btn-primary',
                        onClick: function() {
                            return applyDestPoint();
                        }
                    }
                ]
            });
            setTimeout(function() {
                var start = [-6.2, 106.8];
                if ($('#Dest_Lat').val() && $('#Dest_Lng').val()) {
                    start = [parseFloat($('#Dest_Lat').val()), parseFloat($('#Dest_Lng').val())];
                }
                destMap = L.map('destPickerMap').setView(start, 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(destMap);
                destMarker = L.marker(start, {
                    draggable: true
                }).addTo(destMap).bindPopup('Titik Tujuan');
                destLatLng = {
                    lat: start[0],
                    lng: start[1]
                };
                $('#destPickerCoord').text(start[0].toFixed(6) + ', ' + start[1].toFixed(6));

                function setPoint(latlng) {
                    destLatLng = latlng;
                    destMarker.setLatLng(latlng);
                    $('#destPickerCoord').text(latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6));
                }
                destMap.on('click', function(e) {
                    setPoint(e.latlng);
                });
                destMarker.on('dragend', function(e) {
                    setPoint(e.target.getLatLng());
                });
                setTimeout(function() {
                    destMap.invalidateSize();
                }, 250);
            }, 250);
        });
    });

    $('#submitBtn').click(function(e) {
        e.preventDefault();

        var listId = ['Department', 'Requester_Name', 'Route_Start', 'Schedule_Date', 'Departure_Time', 'Return_Time', 'Program_Name', 'Description'];
        for (var i = 0; i < listId.length; i++) {
            if ($.trim($("#" + listId[i]).val()) === "") {
                alert("Field " + listId[i].replace(/_/g, " ") + " wajib diisi!");
                return;
            }
        }
        if (parseFloat($("#Estimated_Cost").val()) <= 0) {
            alert("Rincian biaya wajib diisi minimal satu baris!");
            return;
        }
        if ($.trim($("#Date_Start").val()) === "" || $.trim($("#Date_Finish").val()) === "") {
            alert("Tanggal mulai dan selesai wajib diisi!");
            return;
        }
        var ds = new Date($("#Date_Start").val());
        var df = new Date($("#Date_Finish").val());
        if (df < ds) {
            alert("Tanggal selesai tidak boleh sebelum tanggal mulai!");
            return;
        }
        var span = Math.floor((df - ds) / 86400000) + 1;
        var st = $('#Sppd_Type').val();
        if (st === 'NORMAL' && span > 14) {
            alert("SPPD Normal maksimal 14 hari!");
            return;
        }
        if (st === 'PERBANTUAN') {
            if (span <= 14) {
                alert("Perbantuan Sementara harus lebih dari 14 hari!");
                return;
            }
            if ($.trim($("#Origin_Site").val()) === "" || $.trim($("#Target_Site").val()) === "" || $.trim($("#Reason").val()) === "") {
                alert("Asal Site, Lokasi Perbantuan, dan Alasan wajib diisi!");
                return;
            }
        }
        if ($('#Rute_Mode').val() === 'TITIK') {
            if ($.trim($("#Destination").val()) === "" ||
                $.trim($("#Dest_Lat").val()) === "" || $.trim($("#Dest_Lng").val()) === "") {
                alert("Untuk Pilih Titik Tujuan: Tujuan Perjalanan Dinas + titik pada peta wajib diisi!");
                return;
            }
        } else {
            if ($.trim($("#Route_Finish").val()) === "") {
                alert("Lokasi Kantor (Tujuan) wajib diisi!");
                return;
            }
            if ($("#Route_Start").val() == $("#Route_Finish").val()) {
                alert("Rute asal dan tujuan tidak boleh sama!");
                return;
            }
        }
        if ($('#Need_Car').is(':checked')) {
            if (!$('#Car_Jenis_Mobil').val() || !$('#Car_Tgl_Pinjam').val() || !$('#Car_Tgl_Kembali').val()) {
                alert('Bila butuh mobil: jenis mobil + tanggal pinjam/kembali wajib diisi!');
                return;
            }
            if ($('#Car_Tgl_Kembali').val() < $('#Car_Tgl_Pinjam').val()) {
                alert('Tanggal kembali mobil tidak boleh sebelum tanggal pinjam!');
                return;
            }
        }

        $.ajax({
            url: 'hrd-sppd-new-submit',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: buildData(),
            success: function(data) {
                if (data.status === 'success') {
                    alert('Request SPPD Submitted!');
                    location.href = 'hrd-sppd-index';
                } else {
                    alert('Gagal submit! ' + data.message);
                }
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan server';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });

    function buildData() {
        var out = {
            SPPD_No: $('#SPPD_No').val(),
            Sppd_Type: $('#Sppd_Type').val(),
            Date_Start: $('#Date_Start').val(),
            Date_Finish: $('#Date_Finish').val(),
            Origin_Site: $('#Origin_Site').val(),
            Target_Site: $('#Target_Site').val(),
            Reason: $('#Reason').val(),
            Requester_NIK: $('#Requester_NIK').val(),
            Department: $('#Department').val(),
            Requester_Name: $('#Requester_Name').val(),
            Destination: $('#Destination').val(),
            Route_Start: $('#Route_Start').val(),
            Route_Finish: $('#Route_Finish').val(),
            Schedule_Date: $('#Schedule_Date').val(),
            Departure_Time: $('#Departure_Time').val(),
            Return_Time: $('#Return_Time').val(),
            Dest_Lat: $('#Dest_Lat').val(),
            Dest_Lng: $('#Dest_Lng').val(),
            Program_Name: $('#Program_Name').val(),
            Description: $('#Description').val(),
            Estimated_Cost: $('#Estimated_Cost').val(),
            Need_Car: $('#Need_Car').is(':checked') ? 1 : 0,
            Car_Jenis_Mobil: $('#Car_Jenis_Mobil').val(),
            Car_Tgl_Pinjam: $('#Car_Tgl_Pinjam').val(),
            Car_Tgl_Kembali: $('#Car_Tgl_Kembali').val(),
            Car_Jam_Pinjam: $('#Car_Jam_Pinjam').val(),
            Car_Jam_Kembali: $('#Car_Jam_Kembali').val()
        };
        $("[id^='Cost_']").each(function() {
            var idx = $(this).attr('id').replace('Cost_', '');
            out['Cost_' + idx] = $(this).val();
            out['Desc_' + idx] = $('#Desc_' + idx).val();
        });
        return out;
    }
</script>
@endsection