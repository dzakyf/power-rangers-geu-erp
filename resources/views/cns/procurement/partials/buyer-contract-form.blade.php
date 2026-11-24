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
    <div class="cns-section-header">{{ $isEdit ? 'DETAIL CONTRACT BUYER (UCO)' : 'NEW CONTRACT BUYER (UCO)' }}</div>
    <div class="card cns-card">
        <div class="card-body">
            <div id="ContentPagesContract">

                @if($isEdit)
                    <input type="hidden" name="ID" id="ID" value="{{ $contract->ID }}" />
                @endif

                <h6 class="font-weight-bold border-bottom pb-1 mt-2" style="color:#1a56a0;">Buyer &amp; KYC</h6>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>KYC (Approved) <span class="text-danger">*</span></label>
                            <select name="kyc_id" id="kyc_id" class="text-black form-control shadow-sm" required>
                                <option value="">-- Pilih KYC (Approved) --</option>
                                @foreach($approvedKycs as $kycItem)
                                    <option value="{{ $kycItem->id }}" {{ (string) old('kyc_id', $contract->kyc_id ?? '') === (string) $kycItem->id ? 'selected' : '' }}>
                                        {{ $kycItem->kyc_no }} - {{ $kycItem->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hanya KYC berstatus APPROVED yang dapat dipilih.</small>
                        </div>
                    </div>
                    <div class="col-md-7 d-flex align-items-center">
                        <a href="{{ route('cns-customer-compliance-kyc.index') }}" target="_blank" class="btn btn-outline-info btn-sm">
                            Kelola KYC
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Nama Buyer (Master Company)</label>
                            <select name="Supplier_ID" id="Supplier_ID" class="text-black form-control shadow-sm">
                                <option value="">-- Pilih Buyer (Opsional) --</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->ID }}" {{ (string) old('Supplier_ID', $contract->Supplier_ID ?? '') === (string) $buyer->ID ? 'selected' : '' }}>
                                        {{ $buyer->Company_Name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Opsional jika KYC standalone. Jika dipilih, telepon &amp; email ditarik dari Master Company.</small>
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
                            <label>Nama Buyer (dari KYC)</label>
                            <input type="text" name="Buyer_Name" id="Buyer_Name" value="{{ old('Buyer_Name', $contract->Buyer_Name ?? '') }}" class="text-black form-control shadow-sm" readonly required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="Buyer_Phone" id="Buyer_Phone" value="{{ old('Buyer_Phone', $contract->Buyer_Phone ?? '') }}" class="text-black form-control shadow-sm" {{ !empty($contract->Supplier_ID) ? 'readonly' : '' }} required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="Buyer_Address" id="Buyer_Address" rows="2" class="text-black form-control shadow-sm" {{ !empty($contract->Supplier_ID) ? 'readonly' : '' }} required>{{ old('Buyer_Address', $contract->Buyer_Address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Email Buyer (maksimal 10)</label>
                    <div id="UcoEmailRows">
                        @foreach($emails as $email)
                            <div class="input-group mb-2 uco-email-row">
                                <input type="email" name="Buyer_Emails[]" value="{{ $email }}" class="text-black form-control shadow-sm" placeholder="nama@domain.com" {{ !empty($contract->Supplier_ID) ? 'readonly' : '' }} />
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block">Otomatis dari Master Company jika dipilih, atau isi manual jika KYC standalone.</small>
                </div>

                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Kontrak</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nomor Kontrak</label>
                            <input type="text" name="Contract_No" id="Contract_No" value="{{ old('Contract_No', $contract->Contract_No ?? '') }}" class="text-black form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status Kontrak</label>
                            <select name="Status" id="Status" class="text-black form-control shadow-sm" required>
                                @foreach($statuses as $code => $label)
                                    <option value="{{ $code }}" {{ old('Status', $contract->Status ?? 'UNDER_REVIEW') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Incoterms</label>
                            <select name="Incoterms" id="Incoterms" class="text-black form-control shadow-sm" required>
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
                            <input type="text" inputmode="decimal" name="Quantity_MT" id="Quantity_MT" value="{{ old('Quantity_MT', isset($contract->Quantity_MT) && $contract->Quantity_MT !== '' ? number_format((float) $contract->Quantity_MT, 2, '.', ',') : '') }}" class="text-black form-control shadow-sm cns-decimal" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Jumlah Container</label>
                            <input type="number" min="1" name="Container_Count" id="Container_Count" value="{{ old('Container_Count', $contract->Container_Count ?? '') }}" class="text-black form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ukuran Container</label>
                            <select name="Container_Size" id="Container_Size" class="text-black form-control shadow-sm" required>
                                @foreach($containerSizes as $size)
                                    <option value="{{ $size }}" {{ old('Container_Size', $contract->Container_Size ?? '20 ft') === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Preview Quantity</label>
                            <div class="text-black form-control shadow-sm bg-light" id="QtyPreview">-</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Loading Period - Mulai</label>
                            <input type="date" name="Loading_Start" id="Loading_Start" value="{{ old('Loading_Start', $contract->Loading_Start ?? '') }}" class="text-black form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Loading Period - Selesai</label>
                            <input type="date" name="Loading_End" id="Loading_End" value="{{ old('Loading_End', $contract->Loading_End ?? '') }}" class="text-black form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Mata Uang Harga</label>
                            <select name="Price_Currency" id="Price_Currency" class="text-black form-control shadow-sm" required>
                                @foreach($currencies as $code => $label)
                                    <option value="{{ $code }}" {{ old('Price_Currency', $contract->Price_Currency ?? 'USD') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Harga per MT</label>
                            <input type="text" inputmode="decimal" name="Price_Per_MT" id="Price_Per_MT" value="{{ old('Price_Per_MT', isset($contract->Price_Per_MT) && $contract->Price_Per_MT !== '' ? number_format((float) $contract->Price_Per_MT, 2, '.', ',') : '') }}" class="text-black form-control shadow-sm cns-decimal" required />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Upload File Kontrak</label>
                            <input type="file" name="Contract_File" id="Contract_File" class="text-black form-control-file" accept=".pdf,.doc,.docx" {{ $isEdit ? '' : 'required' }} />
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
                            <textarea name="Note" id="Note" rows="3" class="text-black form-control shadow-sm">{{ old('Note', $contract->Note ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
