@php
    $isEdit = isset($contract) && $contract;
    $emails = [];
    if ($isEdit && !empty($contract->Buyer_Emails)) {
        $decoded = json_decode($contract->Buyer_Emails, true);
        if (is_array($decoded)) {
            $emails = array_values(array_filter($decoded));
        }
    }
    if (count($emails) === 0) {
        $emails = [''];
    }
@endphp

<div class="mt-3">
    <div class="JudulContent" style="color:#699;">{{ $isEdit ? 'DETAIL CONTRACT BUYER (UCO)' : 'NEW CONTRACT BUYER (UCO)' }}</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPagesContract">

                @if($isEdit)
                    <input type="hidden" name="ID" id="ID" value="{{ $contract->ID }}" />
                @endif

                <h6 class="font-weight-bold border-bottom pb-1 mt-2" style="color:#1a56a0;">Buyer</h6>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Nama Buyer</label>
                            <select name="Supplier_ID" id="Supplier_ID" class="form-control shadow-sm" required>
                                <option value="">-- Pilih Buyer --</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->ID }}" {{ (string) old('Supplier_ID', $contract->Supplier_ID ?? '') === (string) $buyer->ID ? 'selected' : '' }}>
                                        {{ $buyer->Company_Name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Buyer diambil dari Master Company (Consignee / Buyer).</small>
                        </div>
                    </div>
                    <div class="col-md-7 d-flex align-items-center">
                        <a href="cns-master-company" target="_blank" class="btn btn-outline-primary btn-sm mr-2">
                            + Tambah Data Buyer
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="RefreshBuyerBtn">Refresh Buyer</button>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nama Buyer (Snapshot Kontrak)</label>
                            <input type="text" name="Buyer_Name" id="Buyer_Name" value="{{ old('Buyer_Name', $contract->Buyer_Name ?? '') }}" class="form-control shadow-sm" readonly required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="Buyer_Phone" id="Buyer_Phone" value="{{ old('Buyer_Phone', $contract->Buyer_Phone ?? '') }}" class="form-control shadow-sm" readonly required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="Buyer_Address" id="Buyer_Address" rows="2" class="form-control shadow-sm" readonly required>{{ old('Buyer_Address', $contract->Buyer_Address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Email Buyer (maksimal 10)</label>
                    <div id="UcoEmailRows">
                        @foreach($emails as $email)
                            <div class="input-group mb-2 uco-email-row">
                                <input type="email" name="Buyer_Emails[]" value="{{ $email }}" class="form-control shadow-sm" placeholder="nama@domain.com" readonly />
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block">Email diambil dari Master Company.</small>
                </div>

                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Kontrak</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nomor Kontrak</label>
                            <input type="text" name="Contract_No" id="Contract_No" value="{{ old('Contract_No', $contract->Contract_No ?? $generatedContractNo ?? '') }}" class="form-control shadow-sm" readonly required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status Kontrak</label>
                            <select name="Status" id="Status" class="form-control shadow-sm" required>
                                @foreach($statuses as $code => $label)
                                    <option value="{{ $code }}" {{ old('Status', $contract->Status ?? 'UNDER_REVIEW') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Incoterms</label>
                            <select name="Incoterms" id="Incoterms" class="form-control shadow-sm" required>
                                @foreach($incoterms as $code => $label)
                                    <option value="{{ $code }}" {{ old('Incoterms', $contract->Incoterms ?? 'FOB') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Quantity (MT)</label>
                            <input type="text" inputmode="decimal" name="Quantity_MT" id="Quantity_MT" value="{{ old('Quantity_MT', isset($contract->Quantity_MT) && $contract->Quantity_MT !== '' ? number_format((float) $contract->Quantity_MT, 2, '.', ',') : '') }}" class="form-control shadow-sm cns-decimal" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Jumlah Container</label>
                            <input type="number" min="1" name="Container_Count" id="Container_Count" value="{{ old('Container_Count', $contract->Container_Count ?? '') }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ukuran Container</label>
                            <select name="Container_Size" id="Container_Size" class="form-control shadow-sm" required>
                                @foreach($containerSizes as $size)
                                    <option value="{{ $size }}" {{ old('Container_Size', $contract->Container_Size ?? '20 ft') === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Preview Quantity</label>
                            <div class="form-control shadow-sm bg-light" id="QtyPreview">-</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Loading Period - Mulai</label>
                            <input type="date" name="Loading_Start" id="Loading_Start" value="{{ old('Loading_Start', $contract->Loading_Start ?? '') }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Loading Period - Selesai</label>
                            <input type="date" name="Loading_End" id="Loading_End" value="{{ old('Loading_End', $contract->Loading_End ?? '') }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Mata Uang Harga</label>
                            <select name="Price_Currency" id="Price_Currency" class="form-control shadow-sm" required>
                                @foreach($currencies as $code => $label)
                                    <option value="{{ $code }}" {{ old('Price_Currency', $contract->Price_Currency ?? 'USD') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Harga per MT</label>
                            <input type="text" inputmode="decimal" name="Price_Per_MT" id="Price_Per_MT" value="{{ old('Price_Per_MT', isset($contract->Price_Per_MT) && $contract->Price_Per_MT !== '' ? number_format((float) $contract->Price_Per_MT, 2, '.', ',') : '') }}" class="form-control shadow-sm cns-decimal" required />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Upload File Kontrak</label>
                            <input type="file" name="Contract_File" id="Contract_File" class="form-control-file" accept=".pdf,.doc,.docx" {{ $isEdit ? '' : 'required' }} />
                            <small class="text-danger d-block mt-1">Kontrak yang di upload harus sudah ditandatangani.</small>
                            <small class="text-muted d-block">Maksimal 5 MB (PDF/DOC/DOCX).</small>
                            @if($isEdit && !empty($contract->Contract_File))
                                <a href="{{ route('cns-procurement-buyer-contract.file', ['ID' => $contract->ID], false) }}" target="_blank" class="d-inline-block mt-1">Lihat file kontrak saat ini</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="Note" id="Note" rows="3" class="form-control shadow-sm">{{ old('Note', $contract->Note ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
