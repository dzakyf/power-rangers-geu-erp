<div class="main-content-header">
    <h1>FORM PERUBAHAN STATUS KARYAWAN</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
        <li class="breadcrumb-item active"><span class="active">Detail {{ $sc->form_number ?: '#' . $sc->id }}</span></li>
    </ol>
</div>

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold p-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0" style="color:azure"><i class="fas fa-file-alt mr-2"></i>Detail Form Perubahan Status</h5>
        <div>
            <button type="button" class="btn btn-warning btn-sm mr-2" onclick="window.location.href='hrd-status-change-edit?ID={{ $sc->id }}'">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-info btn-sm mr-2" onclick="window.open('hrd-status-change-print?ID={{ $sc->id }}', '_blank')">
                <i class="fas fa-file-pdf"></i> Print
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='hrd-status-change-index?jenis={{ $sc->jenis ?: 'MUTATION' }}'">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>
    </div>
    <div class="card-body">
        <dl class="row small">
            <dt class="col-md-3">Nomor Form</dt><dd class="col-md-9">{{ $sc->form_number ?: '-' }}</dd>
            <dt class="col-md-3">Jenis Perubahan</dt><dd class="col-md-9">{{ $sc->jenis ?: 'MUTATION' }} <small class="text-muted">{{ ($sc->jenis ?: 'MUTATION') == 'PROMOTION' ? '(Promosi)' : '(Mutasi)' }}</small></dd>
            <dt class="col-md-3">Nama Karyawan</dt><dd class="col-md-9">{{ $sc->employee_name }}</dd>
            <dt class="col-md-3">NIK</dt><dd class="col-md-9">{{ $sc->nik }}</dd>
            <dt class="col-md-3">Tanggal Join</dt><dd class="col-md-9">{{ $sc->join_date ? \Carbon\Carbon::parse($sc->join_date)->format('d/m/Y') : '-' }}</dd>
            <dt class="col-md-3">Tanggal Efektif</dt><dd class="col-md-9">{{ $sc->effective_date ? \Carbon\Carbon::parse($sc->effective_date)->format('d/m/Y') : '-' }}</dd>
        </dl>

        @php
            $comparerFields = [
                'position'          => 'Jabatan',
                'division'          => 'Divisi',
                'department'        => 'Departemen',
                'grade'             => 'Grade',
                'work_location'     => 'Lokasi Kerja',
                'employment_status' => 'Status Kekaryawanan',
                'marital_dependents'=> 'Status Perkawinan & Tanggungan',
                'salary'            => 'Salary (Rp)',
                'benefit'           => 'Benefit',
                'description'       => 'Deskripsi',
            ];
        @endphp

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr><th style="width:30%">Item</th><th style="width:35%">Saat Ini</th><th style="width:35%">Usulan</th></tr>
                </thead>
                <tbody>
                    @foreach($comparerFields as $field => $label)
                    <tr>
                        <th>{{ $label }}</th>
                        <td>{{ $sc->{'current_' . $field} ?: '-' }}</td>
                        <td>{{ $sc->{'proposed_' . $field} ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h6 style="font-weight:bold; margin:16px 0 10px 0;">Persetujuan</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr><th>Diusulkan oleh</th><th>Dept. Head Asal</th><th>Dept. Head Tujuan</th><th>Diverifikasi HCGA</th><th>Mengetahui</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $sc->proposed_by ?: '-' }}</td>
                        <td>{{ $sc->origin_department_head ?: '-' }}</td>
                        <td>{{ $sc->destination_department_head ?: '-' }}</td>
                        <td>{{ $sc->hcga_verifier ?: '-' }}</td>
                        <td>{{ $sc->director_approver ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>