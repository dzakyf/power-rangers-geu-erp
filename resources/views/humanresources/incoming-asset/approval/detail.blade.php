<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-alt mr-2"></i> DETAIL APPROVAL INCOMING ASSET (GR: {{ $gr->GR_No }})</span>
        <button class="btn btn-sm btn-light" onclick="refreshdata()"><i class="fas fa-arrow-left mr-1"></i> Kembali</button>
    </div>
    
    <div class="card-body">
        <!-- Informasi Header GR -->
        <div class="card mb-3 bg-light border-secondary">
            <div class="card-body">
                <h6 class="font-weight-bold border-bottom pb-2 text-primary">Informasi Header Good Receive</h6>
                <div class="row">
                    <div class="col-md-3"><strong>GR No:</strong> {{ $gr->GR_No }}</div>
                    <div class="col-md-3"><strong>PO No:</strong> {{ $gr->PO_No }}</div>
                    <div class="col-md-3"><strong>PR No:</strong> {{ $gr->PR_No }}</div>
                    <div class="col-md-3"><strong>Status Saat Ini:</strong> <span class="badge badge-info">{{ $gr->Status }}</span></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3"><strong>Reference:</strong> {{ $gr->Reference }}</div>
                    <div class="col-md-3"><strong>Location:</strong> {{ $gr->Location }}</div>
                    <div class="col-md-3"><strong>Tanggal Dibuat:</strong> {{ $gr->Date_Created }}</div>
                    <div class="col-md-3"><strong>Dibuat Oleh:</strong> {{ $gr->Created_By }}</div>
                </div>
                @if($gr->Remark)
                <div class="row mt-2">
                    <div class="col-md-12">
                        <strong>Catatan / Log Approval:</strong>
                        <pre class="bg-white p-2 border rounded text-muted mt-1" style="font-size:12px;">{{ $gr->Remark }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Tabel Detail Barang Aset Terkait -->
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white font-weight-bold">
                Daftar Barang / Unit Aset Diterima
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Asset No</th>
                                <th>Deskripsi Product</th>
                                <th>Spesifikasi</th>
                                <th>Brand / Model</th>
                                <th>No Registrasi / SN</th>
                                <th>Nilai Pembelian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $index => $asset)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge badge-secondary">{{ $asset->Asset_No }}</span></td>
                                <td>{{ $asset->Product_Description }}</td>
                                <td>{{ $asset->Specification }}</td>
                                <td>{{ $asset->Product_Brand }} {{ $asset->Model ? '/ '.$asset->Model : '' }}</td>
                                <td>{{ $asset->Registration_No ?? ($asset->SN ?? '-') }}</td>
                                <td>Rp {{ number_format($asset->Buy_Val ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada rincian unit aset fisik yang terhubung secara langsung.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Tindakan Approval -->
        <div class="card border-info">
            <div class="card-header bg-info text-white font-weight-bold">
                <i class="fas fa-user-check mr-2"></i> Form Persetujuan (Manager HCGA/Finance & Vice President)
            </div>
            <div class="card-body">
                <form id="formIncomingApproval">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $gr->ID }}">
                    
                    <div class="form-group">
                        <label for="Note" class="font-weight-bold">Catatan Approval / Alasan Reject:</label>
                        <textarea id="Note" name="Note" class="form-control" rows="3" placeholder="Masukkan catatan persetujuan atau alasan penolakan (opsional)..."></textarea>
                    </div>

                    <div class="text-right">
                        <button type="button" class="btn btn-danger px-4 mr-2" onclick="submitIncomingApproval(2)">
                            <i class="fas fa-times-circle mr-1"></i> Reject Penerimaan
                        </button>
                        <button type="button" class="btn btn-success px-4" onclick="submitIncomingApproval(1)">
                            <i class="fas fa-check-circle mr-1"></i> Approve Penerimaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function submitIncomingApproval(approvalType) {
    let actionText = (approvalType == 1) ? "menyetujui" : "menolak";
    if (!confirm("Apakah Anda yakin ingin " + actionText + " penerimaan aset ini?")) {
        return;
    }

    let formData = {
        _token: "{{ csrf_token() }}",
        ID: "{{ $gr->ID }}",
        Approval: approvalType,
        Note: $('#Note').val()
    };

    $.ajax({
        url: "hrd-asset-incoming-approval-submit",
        type: "POST",
        data: formData,
        success: function(res) {
            if (res.status == 'success') {
                alert(res.message);
                refreshdata();
            } else {
                alert("Gagal: " + res.message);
            }
        },
        error: function() {
            alert("Terjadi kesalahan sistem saat memproses approval.");
        }
    });
}
</script>
