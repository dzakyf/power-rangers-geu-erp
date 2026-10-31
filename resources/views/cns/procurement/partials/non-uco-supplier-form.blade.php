@php
    $isEdit = isset($supplier) && $supplier;
    $formId = $formId ?? 'ContentPagesNonUco';
    $fieldPrefix = $fieldPrefix ?? 'Vendor';

    $emails = [];
    if ($isEdit && !empty($supplier->Emails)) {
        $decoded = json_decode($supplier->Emails, true);
        if (is_array($decoded)) {
            $emails = array_values(array_filter($decoded));
        }
    }

    if (count($emails) === 0 && $isEdit && !empty($supplier->Email)) {
        $emails = [$supplier->Email];
    }

    $emailText = implode(', ', $emails);
@endphp

<div class="mt-3">
    <div class="JudulContent" style="color:#699;">{{ $isEdit ? 'DETAIL SUPPLIER NON-UCO' : 'NEW SUPPLIER NON-UCO' }}</div>
    <div class="card">
        <div class="card-body">
            <div id="{{ $formId }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                @if($isEdit)
                    <input type="hidden" name="ID" id="ID" value="{{ $supplier->ID }}" />
                @endif

                <h6 class="font-weight-bold border-bottom pb-1 mt-2" style="color:#1a56a0;">Vendor</h6>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Vendor</label>
                            @if($isEdit)
                                <input type="text" class="form-control shadow-sm" value="{{ $supplier->Company_Name }}" readonly />
                            @else
                                <select name="Vendor_ID" id="Vendor_ID" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Vendor --</option>
                                </select>
                            @endif
                            <small class="text-muted">Vendor diambil dari Master Company (Supplier).</small>
                        </div>
                    </div>
                    @if(!$isEdit)
                        <div class="col-md-7 d-flex align-items-center">
                            <a href="cns-master-company" target="_blank" class="btn btn-outline-primary btn-sm mr-2">
                                + Tambah Supplier
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="RefreshVendorBtn">Refresh Vendor</button>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" id="{{ $fieldPrefix }}Phone" class="form-control shadow-sm" value="{{ $supplier->Phone ?? '' }}" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Alamat Email</label>
                            <input type="text" id="{{ $fieldPrefix }}Emails" class="form-control shadow-sm" value="{{ $emailText }}" readonly />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea id="{{ $fieldPrefix }}Address" rows="2" class="form-control shadow-sm" readonly>{{ $supplier->Address ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label>Kategori Supplier</label>
                    <select name="Supply_Category" id="{{ $fieldPrefix }}SupplyCategory" class="form-control shadow-sm" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ old('Supply_Category', $supplier->Supply_Category ?? '') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
