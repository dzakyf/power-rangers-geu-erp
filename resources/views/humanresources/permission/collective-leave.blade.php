@extends('layout.mainlayout-hrd')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-users me-2"></i>Pengaturan & Batch Cuti Bersama (Collective Leave Setup)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Leave & Permission</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">Collective Leave Setup</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-arrow-left me-1"></i> Ke Portal Absensi
            </a>
            <a href="{{ url('/permission-all') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-list me-1"></i> Rekap Semua Izin
            </a>
            <a href="{{ url('/hrd-master-holiday') }}" class="btn btn-warning btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-calendar me-1"></i> Master Hari Libur
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <div class="row g-3">
        <!-- Formulir Inisiasi Cuti Bersama oleh HCGA -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-plus-circle text-primary me-2"></i>Setup Cuti Bersama Baru</h6>
                    <small class="text-muted">Inisiasi hari cuti bersama nasional atau keputusan manajemen untuk seluruh karyawan</small>
                </div>
                <div class="card-body p-4 pt-1">
                    <form id="collectiveLeaveForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Nama / Keterangan Cuti Bersama <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="Contoh: Cuti Bersama Idul Fitri 1447 H" required />
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required />
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Tanggal Akhir <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Kebijakan Pemotongan Saldo Cuti <span class="text-danger">*</span></label>
                            <select name="is_deduct_annual_leave" class="form-select rounded-3">
                                <option value="YES">YA - Memotong Saldo Cuti Tahunan Karyawan</option>
                                <option value="NO">TIDAK - Cuti Bersama Bebas / Tidak Memotong Saldo</option>
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Jika memilih 'YA', sistem akan otomatis mengurangi saldo kuota cuti tahunan dan mencatat ke buku besar saldo.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Scope / Target Karyawan <span class="text-danger">*</span></label>
                            <select name="target_scope" id="target_scope" class="form-select rounded-3" onchange="toggleScopeInputs(this.value)">
                                <option value="ALL">Semua Karyawan Aktif Perusahaan (Seluruh Cabang & HO)</option>
                                <option value="LOCATION">Berdasarkan Lokasi Kerja Tertentu</option>
                                <option value="DEPARTMENT">Berdasarkan Departemen Tertentu</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="scopeLocationDiv">
                            <label class="form-label small fw-semibold text-muted mb-1">Pilih Lokasi Kerja</label>
                            <select name="location" class="form-select rounded-3">
                                <option value="ALL">Semua Lokasi</option>
                                @foreach ($locations as $l)
                                    <option value="{{ $l }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="scopeDepartmentDiv">
                            <label class="form-label small fw-semibold text-muted mb-1">Pilih Departemen</label>
                            <select name="department" class="form-select rounded-3">
                                <option value="ALL">Semua Departemen</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Catatan atau nomor SK Direksi..."></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="btnSubmitCollective" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm w-100">
                                <i class="fe fe-check-circle me-1"></i> Terapkan Cuti Bersama
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Riwayat Batch Cuti Bersama yang Diterapkan -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-archive text-primary me-2"></i>Riwayat Batch Cuti Bersama</h6>
                        <small class="text-muted">Daftar ketetapan cuti bersama yang telah dieksekusi oleh tim HCGA</small>
                    </div>
                    <span class="badge bg-light text-primary border"><i class="fe fe-info me-1"></i>Auto Sync Terpasang</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2 small fw-semibold">No. Batch</th>
                                    <th class="py-2 small fw-semibold">Nama Cuti Bersama</th>
                                    <th class="py-2 small fw-semibold">Periode</th>
                                    <th class="py-2 small fw-semibold">Durasi</th>
                                    <th class="py-2 small fw-semibold">Potong Cuti?</th>
                                    <th class="py-2 small fw-semibold">Jml Karyawan</th>
                                    <th class="pe-3 py-2 small fw-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batches as $b)
                                    <tr>
                                        <td class="ps-3 fw-bold small text-primary">{{ $b->batch_no }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $b->title }}</span>
                                            <small class="text-muted d-block" style="font-size: 11px;">Oleh: {{ $b->created_by }}</small>
                                        </td>
                                        <td class="small">{{ date('d/m/Y', strtotime($b->start_date)) }} s.d {{ date('d/m/Y', strtotime($b->end_date)) }}</td>
                                        <td class="small fw-bold">{{ $b->total_days }} Hari</td>
                                        <td>
                                            @if ($b->is_deduct_annual_leave === 'YES')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Ya (Potong)</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Bebas</span>
                                            @endif
                                        </td>
                                        <td class="small text-center">{{ $b->total_employees_affected }} Org</td>
                                        <td class="pe-3 text-center">
                                            <span class="badge bg-success text-white rounded-pill px-3">{{ $b->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data batch cuti bersama yang diterapkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    function toggleScopeInputs(scope) {
        if (scope === 'LOCATION') {
            $('#scopeLocationDiv').removeClass('d-none');
            $('#scopeDepartmentDiv').addClass('d-none');
        } else if (scope === 'DEPARTMENT') {
            $('#scopeDepartmentDiv').removeClass('d-none');
            $('#scopeLocationDiv').addClass('d-none');
        } else {
            $('#scopeLocationDiv').addClass('d-none');
            $('#scopeDepartmentDiv').addClass('d-none');
        }
    }

    $('#collectiveLeaveForm').submit(function(e) {
        e.preventDefault();

        if (!confirm('Apakah Anda yakin ingin menerapkan batch cuti bersama ini ke seluruh karyawan target?')) {
            return;
        }

        var btn = $('#btnSubmitCollective');
        btn.prop('disabled', true).html('<i class="fe fe-loader me-1"></i> Memproses Batch...');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "{{ url('/permission-collective-leave-submit') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fe fe-check-circle me-1"></i> Terapkan Cuti Bersama');
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message || 'Gagal menerapkan cuti bersama.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fe fe-check-circle me-1"></i> Terapkan Cuti Bersama');
                var msg = 'Terjadi kesalahan sistem.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });
</script>
@endsection

