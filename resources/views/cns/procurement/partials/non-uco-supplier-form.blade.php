@php
    $isEdit = isset($supplier) && $supplier;
    $formId = $formId ?? 'ContentPagesNonUco';
    $emailPrefix = $emailPrefix ?? 'NonUco';

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

    if (count($emails) === 0) {
        $emails = [''];
    }
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Vendor</label>
                            <input type="text" name="Company_Name" id="Company_Name" value="{{ old('Company_Name', $supplier->Company_Name ?? '') }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="Phone" id="Phone" value="{{ old('Phone', $supplier->Phone ?? '') }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="Address" id="Address" rows="2" class="form-control shadow-sm">{{ old('Address', $supplier->Address ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Alamat Email (maksimal 10)</label>
                    <div id="{{ $emailPrefix }}EmailRows">
                        @foreach($emails as $email)
                            <div class="input-group mb-2 {{ strtolower($emailPrefix) }}-email-row">
                                <input type="email" name="Emails[]" value="{{ $email }}" class="form-control shadow-sm" placeholder="nama@domain.com" />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger remove-{{ strtolower($emailPrefix) }}-email">Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="{{ $emailPrefix }}AddEmailBtn">+ Tambah Email</button>
                    <small class="text-muted d-block">Maksimal 10 alamat email.</small>
                </div>

                <div class="form-group">
                    <label>Kategori Supplier</label>
                    <select name="Supply_Category" id="Supply_Category" class="form-control shadow-sm" required>
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
