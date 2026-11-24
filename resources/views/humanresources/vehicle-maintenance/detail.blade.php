<div class="mt-3">
    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-car me-2"></i>Detail Perawatan Kendaraan</h6>
            <div class="d-flex align-items-center gap-2">
                @if(($data->Status ?? '') !== 'DRAFT')
                    <a href="hrd-vehicle-maintenance-print-spk?ID={{ $data->id }}" target="_blank" class="btn btn-light btn-sm">
                        <i class="fa fa-print me-1"></i> Cetak SPK (PDF)
                    </a>
                @endif
                <button type="button" class="btn btn-light btn-sm" onclick="$('#VMDTL').html('')">
                    <i class="fa fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" id="VM_Detail_ID" value="{{ $data->id ?? '' }}" />

            @php
                $status = $data->Status ?? '';
                $badgeMap = [
                    'DRAFT'            => 'bg-secondary text-white',
                    'WAITING_APPROVAL' => 'bg-warning text-dark',
                    'APPROVED'         => 'bg-primary text-white',
                    'IN_PROGRESS'      => 'bg-info text-white',
                    'COMPLETED'        => 'bg-success text-white',
                    'RETURNED'         => 'bg-warning text-dark',
                    'REJECTED'         => 'bg-danger text-white',
                    'CANCELLED'        => 'bg-dark text-white',
                ];
                $statusBadge = $badgeMap[$status] ?? 'bg-secondary text-white';
                $tglPengajuan = $data->Tanggal_Pengajuan ?: ($data->created_at ? substr((string) $data->created_at, 0, 10) : '');
            @endphp

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Pengajuan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">No Transaksi</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->No_Trx ?? '' }}" readonly />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Perawatan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ ucfirst($data->Jenis_Perawatan ?? '') }}" readonly />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status Lifecycle</label>
                    <div class="pt-1">
                        <span class="badge {{ $statusBadge }} px-2 py-1">{{ $status }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Nomor Formulir</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Nomor_Formulir ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nama Pengaju</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Nama_Pengaju ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Jabatan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Jabatan ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Pengajuan</label>
                    <input type="date" class="form-control form-control-sm bg-light" value="{{ $tglPengajuan }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Kendaraan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kendaraan (Nama)</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Product_Description ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Asset No</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Asset_No ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nomor Seri / SN</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->SN ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Plat Nomor</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Plat_Nomor ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kategori Kendaraan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Kategori_Kendaraan ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kilometer Saat Pengajuan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ number_format($data->Kilometer_Saat_Ini ?? 0, 0, ',', '.') }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Pekerjaan &amp; Estimasi</h6>
            <div class="row g-3 mb-4">
                @if(($data->Jenis_Perawatan ?? '') === 'preventif')
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tipe Service Preventif</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Tipe_Service_Preventif ?? '' }}" readonly />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jadwal Perawatan (Bulan Ke-)</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Jadwal_Perawatan_Bulan ?? '' }}" readonly />
                    </div>
                @endif

                @if(($data->Jenis_Perawatan ?? '') === 'korektif')
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Kerusakan Yang Ditemui</label>
                        <textarea class="form-control form-control-sm bg-light" rows="3" readonly>{{ $data->Kerusakan_Yang_Ditemui ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Penyebab Diperkirakan</label>
                        <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Penyebab_Diperkirakan ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Komponen Yang Diganti / Diperbaiki</label>
                        <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Komponen_Yang_Diganti ?? '' }}</textarea>
                    </div>
                @endif

                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Kondisi Awal Kendaraan</label>
                    <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Kondisi_Awal ?? '' }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Estimasi Biaya (Rp)</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ number_format($data->Estimasi_Biaya ?? 0, 0, ',', '.') }}" readonly />
                        @if(($data->Estimasi_Biaya ?? 0) > 2500000)
                            <span class="badge bg-danger text-white text-nowrap">Perlu Approval VP/Direktur</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Vendor / Bengkel</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Vendor_Bengkel ?? '' }}" readonly />
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Keterangan Lain</label>
                    <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Keterangan_Lain ?? '' }}</textarea>
                </div>
            </div>

            {{-- Section 4: Aksi Eksekusi Lifecycle --}}
            @if(in_array($status, ['DRAFT', 'RETURNED', 'REJECTED'], true))
                <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <strong>Status {{ $status }}:</strong> Pengajuan ini dapat dikirimkan ke alur approval.
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="vmSubmitDraft({{ $data->id }});">
                        <i class="fa fa-paper-plane me-1"></i> {{ $status === 'DRAFT' ? 'Submit ke Approval' : 'Ajukan Ulang Approval' }}
                    </button>
                </div>
            @elseif($status === 'APPROVED')
                <div class="card border-primary mb-4">
                    <div class="card-header bg-light fw-bold text-primary">
                        <i class="fa fa-tools me-1"></i> Mulai Proses Servis (Kendaraan Masuk Bengkel)
                    </div>
                    <div class="card-body row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Mulai Servis</label>
                            <input type="date" id="VM_Start_Date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-primary btn-sm" onclick="vmStartExecution({{ $data->id }});">
                                <i class="fa fa-play me-1"></i> Mulai Servis (Ubah Status Aset ke MAINTENANCE)
                            </button>
                        </div>
                    </div>
                </div>
            @elseif($status === 'IN_PROGRESS')
                <div class="card border-success mb-4">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fa fa-check-double me-1"></i> Penyelesaian Servis &amp; Upload Bukti Nota
                    </div>
                    <div class="card-body">
                        <form id="FormCompleteService" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Tanggal Selesai Servis</label>
                                    <input type="date" name="Tanggal_Selesai_Service" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Kilometer Aktual Selesai</label>
                                    <input type="number" name="Kilometer_Selesai" class="form-control form-control-sm" value="{{ (int) ($data->Kilometer_Saat_Ini ?? 0) }}" min="0" required />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Biaya Aktual (Rp)</label>
                                    <input type="number" name="Biaya_Aktual" class="form-control form-control-sm" value="{{ (int) ($data->Estimasi_Biaya ?? 0) }}" min="0" required />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Vendor Bengkel Aktual</label>
                                    <input type="text" name="Vendor_Bengkel" class="form-control form-control-sm" value="{{ $data->Vendor_Bengkel ?? '' }}" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Upload Bukti Nota / Invoice (JPG/PNG/PDF, maks 5MB)</label>
                                    <input type="file" name="Bukti_Nota" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Catatan Hasil Pekerjaan</label>
                                    <input type="text" name="Catatan_Penyelesaian" class="form-control form-control-sm" placeholder="Contoh: Oli diganti, kampas rem baru" />
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-sm" onclick="vmCompleteExecution({{ $data->id }});">
                                        <i class="fa fa-check-circle me-1"></i> Selesaikan Servis &amp; Perbarui Jadwal PM
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Section 5: Realisasi & Bukti Nota (jika IN_PROGRESS / COMPLETED) --}}
            @if(in_array($status, ['IN_PROGRESS', 'COMPLETED'], true))
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Realisasi Servis &amp; Bukti Nota</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tgl Mulai Servis</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Tanggal_Mulai_Service ?: '-' }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tgl Selesai Servis</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Tanggal_Selesai_Service ?: '-' }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Kilometer Selesai</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Kilometer_Selesai ? number_format($data->Kilometer_Selesai, 0, ',', '.') . ' km' : '-' }}" readonly />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Biaya Aktual (Rp)</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $data->Biaya_Aktual !== null ? number_format($data->Biaya_Aktual, 0, ',', '.') : '-' }}" readonly />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Catatan Penyelesaian</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Catatan_Penyelesaian ?: '-' }}" readonly />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Bukti Nota / Invoice</label>
                        <div class="pt-1">
                            @if(!empty($data->Bukti_Nota_Path))
                                <a href="{{ asset($data->Bukti_Nota_Path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-file-invoice me-1"></i> Lihat / Unduh Nota
                                </a>
                            @else
                                <span class="text-muted small">Tidak ada lampiran nota</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Section 6: Timeline Riwayat Approval --}}
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Riwayat Approval</h6>
            @if(!empty($timeline))
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Stage / Level</th>
                                <th>Approver</th>
                                <th>Aksi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeline as $item)
                                <tr>
                                    <td class="text-nowrap">{{ $item['acted_at'] ?: '-' }}</td>
                                    <td>{{ $item['stage'] }}</td>
                                    <td>{{ $item['actor'] }} ({{ $item['dept'] }})</td>
                                    <td>
                                        @php
                                            $actUpper = strtoupper((string) $item['action']);
                                            $actBadge = in_array($actUpper, ['APPROVE', 'APPROVED', 'AUTO_APPROVE'], true)
                                                ? 'bg-success text-white'
                                                : (in_array($actUpper, ['REJECT', 'REJECTED'], true) ? 'bg-danger text-white' : 'bg-info text-white');
                                        @endphp
                                        <span class="badge {{ $actBadge }}">{{ $item['action'] }}</span>
                                    </td>
                                    <td>{{ $item['notes'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mb-3">Belum ada riwayat approval tercatat.</p>
            @endif

            <div class="mt-3 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary btn-sm" onclick="$('#VMDTL').html('')">
                    <i class="fa fa-arrow-left me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function vmSubmitDraft(id) {
    if (!confirm('Ajukan permohonan perawatan kendaraan ini ke alur approval?')) return;
    $.ajax({
        url: 'hrd-vehicle-maintenance-submit-draft',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        data: { id: id },
        success: function(res) {
            alert(res.message || 'Berhasil diajukan.');
            if (typeof refreshdata === 'function') refreshdata();
            LoadM('VMDTL', 'hrd-vehicle-maintenance-detail?ID=' + encodeURIComponent(id));
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengajukan approval.');
        }
    });
}

function vmStartExecution(id) {
    var startDate = $('#VM_Start_Date').val();
    if (!confirm('Mulai proses servis? Status kendaraan akan berubah menjadi MAINTENANCE.')) return;
    $.ajax({
        url: 'hrd-vehicle-maintenance-start',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        data: { id: id, Tanggal_Mulai_Service: startDate },
        success: function(res) {
            alert(res.message || 'Servis dimulai.');
            if (typeof refreshdata === 'function') refreshdata();
            LoadM('VMDTL', 'hrd-vehicle-maintenance-detail?ID=' + encodeURIComponent(id));
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memulai servis.');
        }
    });
}

function vmCompleteExecution(id) {
    var formEl = document.getElementById('FormCompleteService');
    var fd = new FormData(formEl);
    fd.append('id', id);

    if (!confirm('Selesaikan servis dan perbarui jadwal PM berikutnya?')) return;

    $.ajax({
        url: 'hrd-vehicle-maintenance-complete',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            alert(res.message || 'Servis selesai dicatat.');
            if (typeof refreshdata === 'function') refreshdata();
            LoadM('VMDTL', 'hrd-vehicle-maintenance-detail?ID=' + encodeURIComponent(id));
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyelesaikan servis.');
        }
    });
}
</script>
