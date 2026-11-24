<div class="mt-3">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-clipboard-check me-2"></i>Detail Permohonan Perawatan Kendaraan</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#VMApDTL').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <input type="hidden" id="ID" name="ID" value="{{ $data->id ?? '' }}" />

            @php
                $statusLabels = [
                    'DRAFT'                 => 'Draft',
                    'WAITING_APPROVAL'      => 'Menunggu Persetujuan',
                    'APPROVED'              => 'Disetujui',
                    'IN_PROGRESS'           => 'Sedang Servis',
                    'COMPLETED'             => 'Selesai',
                    'RETURNED'              => 'Dikembalikan untuk Revisi',
                    'REJECTED'              => 'Ditolak',
                    'APPROVAL_PIC'          => 'Menunggu PIC / Manager Dept',
                    'APPROVAL_ATASAN'       => 'Menunggu Atasan Departemen',
                    'APPROVAL_VP'           => 'Menunggu VP Departemen',
                    'APPROVAL_HCGA_MANAGER' => 'Menunggu Manager HCGA',
                ];
                $st = $data->Status ?? '';
                $label = $statusLabels[$st] ?? $st;
                $badgeClass = in_array($st, ['WAITING_APPROVAL', 'RETURNED', 'APPROVAL_PIC', 'APPROVAL_ATASAN', 'APPROVAL_VP', 'APPROVAL_HCGA_MANAGER'], true)
                    ? 'bg-warning text-dark'
                    : (in_array($st, ['APPROVED', 'COMPLETED'], true) ? 'bg-success text-white' : ($st === 'IN_PROGRESS' ? 'bg-info text-white' : 'bg-danger text-white'));
                $tglPengajuan = $data->Tanggal_Pengajuan ?: ($data->created_at ? substr((string) $data->created_at, 0, 10) : '');
            @endphp

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Pengajuan &amp; Status</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">No Transaksi</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->No_Trx ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Jenis Perawatan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ ucfirst($data->Jenis_Perawatan ?? '') }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status Approval Saat Ini</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $st }}" readonly />
                        <span class="badge {{ $badgeClass }} text-nowrap">{{ $label }}</span>
                    </div>
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
                    <label class="form-label small fw-semibold">Kilometer Saat Ini</label>
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

            {{-- Section 4: Aksi Approval --}}
            @if(!empty($canApprove) || !empty($canForceApprove))
                <div class="card border-warning mb-4">
                    <div class="card-header bg-light fw-bold text-dark">
                        <i class="fa fa-gavel me-1"></i> Keputusan Approval ({{ $approver->Name ?? Auth::user()->username }})
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="Approval_Note" class="form-label small fw-semibold">Catatan / Alasan Approval</label>
                            <textarea id="Approval_Note" class="form-control form-control-sm" rows="2" placeholder="Masukkan catatan persetujuan atau alasan penolakan (wajib jika menolak)..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-danger btn-sm vm-act-btn" onclick="submitVmApproval('REJECT');">
                                <i class="fa fa-times-circle me-1"></i> Reject / Kembalikan
                            </button>
                            <button type="button" class="btn btn-success btn-sm vm-act-btn" onclick="submitVmApproval('APPROVE');">
                                <i class="fa fa-check-circle me-1"></i> Approve
                            </button>
                        </div>
                    </div>
                </div>
            @elseif($st === 'WAITING_APPROVAL')
                <div class="alert alert-info py-2 mb-4">
                    <i class="fa fa-info-circle me-1"></i> Anda bukan kandidat approver aktif untuk tahap persetujuan saat ini.
                </div>
            @endif

            {{-- Section 5: Timeline Riwayat Approval --}}
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
                <button type="button" class="btn btn-secondary btn-sm" onclick="$('#VMApDTL').html('')">
                    <i class="fa fa-arrow-left me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function submitVmApproval(action) {
    var note = $.trim($('#Approval_Note').val());
    if (action === 'REJECT' && note === '') {
        alert('Harap isi catatan/alasan penolakan terlebih dahulu.');
        $('#Approval_Note').focus();
        return;
    }

    var label = action === 'REJECT' ? 'menolak' : 'menyetujui';
    if (!confirm('Apakah Anda yakin ingin ' + label + ' permohonan perawatan kendaraan ini?')) {
        return;
    }

    $('.vm-act-btn').prop('disabled', true);

    $.ajax({
        url: 'hrd-vehicle-maintenance-approval-approve',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        data: {
            Data: $('#ID').val(),
            Action: action,
            Note: note
        },
        success: function(res) {
            if (res.status === 'success') {
                alert(res.message || 'Approval berhasil diproses.');
                if (typeof refreshdata === 'function') refreshdata();
                $('#VMApDTL').html('');
            } else {
                alert(res.message || 'Gagal memproses approval.');
                $('.vm-act-btn').prop('disabled', false);
            }
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server';
            alert(msg);
            $('.vm-act-btn').prop('disabled', false);
        }
    });
}
</script>
