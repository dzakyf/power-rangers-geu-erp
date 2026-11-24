@php
    $statusPoUpper  = strtoupper($poHeader->Status ?? 'OPEN');
    $statusRecUpper = strtoupper($poHeader->Status_Receive ?? 'OPEN');

    $poBadgeBg = '#1565c0';
    if ($statusPoUpper === 'DELIVERY') $poBadgeBg = '#ef6c00';
    elseif ($statusPoUpper === 'PARTIAL') $poBadgeBg = '#00838f';
    elseif ($statusPoUpper === 'RECEIVED') $poBadgeBg = '#1b5e20';
    elseif ($statusPoUpper === 'CANCELLED') $poBadgeBg = '#c62828';

    $isDeliveryReached = in_array($statusPoUpper, ['DELIVERY', 'PARTIAL', 'RECEIVED']);
    $canReceive        = in_array($statusPoUpper, ['DELIVERY', 'PARTIAL']);
@endphp

<div class="card shadow-sm border-success mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid #28a745;">
    <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center flex-wrap">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-file-invoice mr-2"></i>Detail Purchase Order (PO) &mdash; {{ $poHeader->PO_No }}
        </h6>
        <div class="d-flex align-items-center mt-1 mt-md-0">
            @if($statusPoUpper === 'OPEN')
                <button type="button" class="btn btn-sm font-weight-bold py-0 px-2 mr-1 shadow-sm" style="background-color:#ef6c00; color:#fff;" onclick="prosesDeliveryPO('{{ $poHeader->PO_No }}')">
                    &#128666; Proses Delivery
                </button>
            @endif

            <a href="{{ url('po-print') }}?PO_No={{ urlencode($poHeader->PO_No) }}" target="_blank" class="btn btn-sm btn-warning text-dark font-weight-bold py-0 px-2 mr-1 shadow-sm">
                <i class="fas fa-print mr-1"></i> Cetak PDF PO
            </a>
            <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#PODetailDiv').html('');" title="Tutup Detail">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- VISUAL STEPPER WORKFLOW PO: OPEN -> DELIVERY -> PARTIAL -> RECEIVED -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 rounded" style="background-color: #f1f8e9; border: 1px solid #c5e1a5;">
            <div class="d-flex align-items-center flex-wrap" style="font-size: 11.5px;">
                <span class="font-weight-bold mr-2 text-dark">Alur Status PO:</span>
                <span style="padding:3px 10px; border-radius:12px; font-weight:bold; background:#2e7d32; color:#fff;">1. OPEN</span>
                <span class="mx-1 font-weight-bold">&rarr;</span>
                <span style="padding:3px 10px; border-radius:12px; font-weight:bold; background:{{ $isDeliveryReached ? '#ef6c00' : '#e0e0e0' }}; color:{{ $isDeliveryReached ? '#fff' : '#555' }};">2. DELIVERY</span>
                <span class="mx-1 font-weight-bold">&rarr;</span>
                <span style="padding:3px 10px; border-radius:12px; font-weight:bold; background:{{ $statusPoUpper === 'PARTIAL' ? '#00838f' : '#e0e0e0' }}; color:{{ $statusPoUpper === 'PARTIAL' ? '#fff' : '#555' }};">3. PARTIAL</span>
                <span class="mx-1 font-weight-bold">&rarr;</span>
                <span style="padding:3px 10px; border-radius:12px; font-weight:bold; background:{{ $statusPoUpper === 'RECEIVED' ? '#1b5e20' : '#e0e0e0' }}; color:{{ $statusPoUpper === 'RECEIVED' ? '#fff' : '#555' }};">4. RECEIVED</span>
            </div>

            <div class="mt-1 mt-md-0">
                @if($statusPoUpper === 'OPEN')
                    <button type="button" class="btn btn-sm font-weight-bold shadow-sm" style="background-color:#ef6c00; color:#fff;" onclick="prosesDeliveryPO('{{ $poHeader->PO_No }}')">
                        &#128666; Klik untuk Set Delivery (Kirim Vendor)
                    </button>
                @elseif(in_array($statusPoUpper, ['DELIVERY', 'PARTIAL']))
                    <span class="font-weight-bold text-primary" style="font-size:11.5px;">&#9998; Silakan isi kolom "Qty Terima Sekarang" di tabel bawah lalu simpan penerimaan.</span>
                @elseif($statusPoUpper === 'RECEIVED')
                    <span style="background:#1b5e20; color:#fff; padding:4px 10px; border-radius:4px; font-weight:bold;">&#10004; BARANG SUDAH DITERIMA LENGKAP</span>
                @endif
            </div>
        </div>

        <!-- INFORMASI HEADER PO & VENDOR -->
        <div class="row mb-3 p-2 bg-light rounded border">
            <div class="col-md-3 mb-2">
                <span class="text-muted font-weight-bold">No. Purchase Order:</span><br>
                <span class="text-primary font-weight-bold" style="font-size: 13px;">{{ $poHeader->PO_No }}</span><br>
                <small class="text-muted">Tanggal: {{ $poHeader->PO_Date }}</small>
            </div>
            <div class="col-md-3 mb-2">
                <span class="text-muted font-weight-bold">Vendor / Supplier:</span><br>
                <span class="font-weight-bold text-dark">{{ $poHeader->Supplier }} - {{ $poHeader->Company_Name ?? 'Vendor' }}</span><br>
                <small class="text-muted">PIC Vendor: {{ $poHeader->Supplier_PIC ?? $poHeader->Contact_Person ?? '-' }}</small>
            </div>
            <div class="col-md-3 mb-2">
                <span class="text-muted font-weight-bold">Ref. PR / Department:</span><br>
                <span class="font-weight-bold">{{ $poHeader->PR_No ?? '-' }}</span><br>
                <small class="text-muted">Dept: {{ $poHeader->Department ?? '-' }} | Site: {{ $poHeader->PO_Dest ?? '-' }}</small>
            </div>
            <div class="col-md-3 mb-2 text-right">
                <span class="text-muted font-weight-bold">Status PO / Penerimaan:</span><br>
                <span style="display:inline-block; background:{{ $poBadgeBg }}; color:#fff; font-weight:bold; padding:2px 8px; border-radius:4px; font-size:11px;">{{ $statusPoUpper }}</span>
                <span style="display:inline-block; background:#fbc02d; color:#000; font-weight:bold; padding:2px 8px; border-radius:4px; font-size:11px;">Receive: {{ $statusRecUpper }}</span><br>
                <small class="text-muted">Termin: <strong>{{ $poHeader->Term_Payment ?? 'NET 30' }}</strong></small>
            </div>
            <div class="col-12 border-top pt-2 mt-1">
                <span class="text-muted font-weight-bold">Catatan / Referensi Dokumen:</span>
                <span class="text-dark ml-1">{{ $poHeader->Remark ?? '-' }}</span>
            </div>
        </div>

        <!-- FORM PENERIMAAN BARANG (MUNCUL SETELAH DELIVERY / PARTIAL) -->
        <form id="formReceivePO" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="po_no" value="{{ $poHeader->PO_No }}">

            @if($canReceive)
            <div class="p-2 mb-2 rounded border" style="background-color: #fffde7;">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="font-weight-bold mb-1 text-dark" style="font-size: 11.5px;">No. Surat Jalan / DO Vendor:</label>
                        <input type="text" name="surat_jalan" class="form-control form-control-sm" placeholder="Contoh: SJ/2026/11/0089">
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="font-weight-bold mb-1 text-dark" style="font-size: 11.5px;">Note Penerimaan Barang:</label>
                        <input type="text" name="receive_note" class="form-control form-control-sm" placeholder="Catatan kondisi barang / penerima...">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="font-weight-bold mb-1 text-dark" style="font-size: 11.5px;">Bukti Foto Penerima Barang:</label>
                            <input type="file"
                               name="receive_proof"
                               id="receiveProofInput"
                               class="form-control-file form-control-sm"
                               accept=".jpg,.jpeg,.png,.webp"
                               onchange="validasiFotoBuktiPO(this)"
                               style="font-size: 10.5px;">
                    </div>
                    <div class="col-md-2 text-md-right">
                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold mb-1 w-100" onclick="isiSemuaSisaQty()">
                            &#9889; Isi Sisa Qty
                        </button>
                        <button type="button" class="btn btn-sm btn-success font-weight-bold shadow-sm w-100" onclick="simpanReceivePO('{{ $poHeader->PO_No }}')">
                            &#10004; Simpan GR
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- TABEL RINCIAN ITEM PURCHASE ORDER -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white text-center" style="font-size: 11.5px;">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 4%;">No.</th>
                            <th style="width: 12%;">Kode Item</th>
                            <th style="width: 24%;">Deskripsi Barang / Jasa</th>
                            <th style="width: 7%;">Qty Order</th>
                            <th style="width: 7%;">Diterima</th>
                            <th style="width: 7%;">Sisa</th>
                            @if($canReceive)
                                <th style="width: 11%; background-color: #1b5e20;">Qty Terima Sekarang</th>
                            @endif
                            <th style="width: 6%;">Satuan</th>
                            <th style="width: 11%;">Harga Satuan (Rp)</th>
                            <th style="width: 11%;">Total Harga (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($poItems as $idx => $item)
                            @php
                                $sisa = floatval($item->Qty_Remain ?? ($item->Qty - ($item->Qty_Receive ?? 0)));
                            @endphp
                        <tr>
                            <td class="align-middle">{{ $idx + 1 }}</td>
                            <td class="align-middle font-weight-bold text-primary">{{ $item->Product_Code }}</td>
                            <td class="text-left align-middle">
                                <span class="font-weight-bold text-dark">{{ $item->Product_Desc }}</span>
                                @if(!empty($item->Specification))
                                    <br><small class="text-muted">Spek: {{ $item->Specification }}</small>
                                @endif
                            </td>
                            <td class="align-middle font-weight-bold">{{ number_format($item->Qty, 0, ',', '.') }}</td>
                            <td class="align-middle text-success font-weight-bold">{{ number_format($item->Qty_Receive ?? 0, 0, ',', '.') }}</td>
                            <td class="align-middle text-danger font-weight-bold">{{ number_format($sisa, 0, ',', '.') }}</td>
                            @if($canReceive)
                                <td class="align-middle" style="background-color: #f1f8e9;">
                                    @if($sisa > 0)
                                        <input type="number"
                                            name="receive_qty[{{ $item->ID }}]"
                                            class="form-control form-control-sm text-center font-weight-bold input-qty-receive"
                                            data-sisa="{{ $sisa }}"
                                            min="0"
                                            max="{{ $sisa }}"
                                            step="any"
                                            value="{{ $sisa }}">
                                    @else
                                        <span class="badge badge-success">Lunas</span>
                                    @endif
                                </td>
                            @endif
                            <td class="align-middle">{{ $item->Unit }}</td>
                            <td class="text-right align-middle">{{ number_format($item->Price ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right align-middle font-weight-bold">{{ number_format($item->Total ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $canReceive ? 10 : 9 }}" class="text-center text-muted py-3">Tidak ada rincian item pada Purchase Order ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="{{ $canReceive ? 9 : 8 }}" class="text-right font-weight-bold">Subtotal (Rp):</td>
                            <td class="text-right font-weight-bold">{{ number_format($poHeader->Subtotal ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="{{ $canReceive ? 9 : 8 }}" class="text-right font-weight-bold">PPN (Rp):</td>
                            <td class="text-right text-muted">{{ number_format($poHeader->PPN ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="{{ $canReceive ? 9 : 8 }}" class="text-right font-weight-bold">Ongkos Kirim / Delivery Fee (Rp):</td>
                            <td class="text-right text-muted">{{ number_format($poHeader->Delivery_Fee ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-success font-weight-bold" style="font-size: 13px;">
                            <td colspan="{{ $canReceive ? 9 : 8 }}" class="text-right text-success">GRAND TOTAL PO (Rp):</td>
                            <td class="text-right text-success">Rp {{ number_format($poHeader->Total ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
function reloadDetailAndGridPO(poNo) {
    if (typeof POGrid !== 'undefined') {
        POGrid.clearAll();
        POGrid.load("{{ url('xml-outstanding-po-list') }}");
    }
    $('#PODetailDiv').load("{{ url('po-detail') }}?PO_No=" + encodeURIComponent(poNo));
}

function prosesDeliveryPO(poNo) {
    var sj = prompt('Ubah status ' + poNo + ' menjadi DELIVERY (Dalam Pengiriman Vendor)?\nMasukkan No. Surat Jalan / Resi Vendor (opsional):');
    if (sj === null) return;

    $.post("{{ url('po-delivery') }}", {
        _token: "{{ csrf_token() }}",
        po_no: poNo,
        delivery_note: sj
    }, function(res) {
        if (res.status === 'success') {
            alert(res.message);
            reloadDetailAndGridPO(poNo);
        } else {
            alert(res.message || 'Gagal mengubah status delivery.');
        }
    }).fail(function(err) {
        alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan saat proses delivery.');
    });
}

function isiSemuaSisaQty() {
    $('.input-qty-receive').each(function() {
        $(this).val($(this).data('sisa'));
    });
}

function simpanReceivePO(poNo) {
    if (!confirm('Simpan penerimaan barang untuk ' + poNo + '?')) return;

    var formEl = document.getElementById('formReceivePO');
    var formData = new FormData(formEl);

    $.ajax({
        url: "{{ url('po-receive') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status === 'success') {
                alert(res.message);
                reloadDetailAndGridPO(poNo);
            } else {
                alert(res.message || 'Gagal menyimpan penerimaan barang.');
            }
        },
        error: function(err) {
            alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan saat menyimpan penerimaan barang.');
        }
    });
}


function validasiFotoBuktiPO(input) {
    if (!input.files || !input.files[0]) return;

    var file = input.files[0];
    var maxBytes = 5 * 1024 * 1024; // 5 MB
    var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    var fileNameLower = (file.name || '').toLowerCase();
    var validExt = /\.(jpe?g|png|webp)$/i.test(fileNameLower);

    if (!validExt || (file.type && allowedTypes.indexOf(file.type.toLowerCase()) === -1)) {
        alert('Format file tidak sesuai! Gunakan gambar JPG, JPEG, PNG, atau WEBP.');
        input.value = '';
        return;
    }

    if (file.size > maxBytes) {
        var sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        alert('Ukuran file ' + sizeMb + ' MB melebihi batas maksimal 5 MB!');
        input.value = '';
        return;
    }

    var validSizeMb = (file.size / (1024 * 1024)).toFixed(2);
    alert('Foto bukti memenuhi syarat (' + file.name + ' — ' + validSizeMb + ' MB, Max 5 MB).');
}
</script>
