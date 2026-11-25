<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Edit PR</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --emerald-dark: #064E3B;
            --emerald-primary: #047857;
            --emerald-soft: #ECFDF5;
            --bg-canvas: #F6F8F7;
            --text-dark: #111827;
            --text-muted: #6B7280;
            --border-soft: #E5EBE8;
        }
        body {
            background-color: var(--bg-canvas);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            padding-bottom: 96px;
        }
        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            padding: 16px 16px 24px;
        }
        .top-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .btn-back-soft {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            flex-shrink: 0;
        }
        .section-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .section-label {
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--emerald-dark);
        }
        .item-box {
            background: #F9FAFB;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 14px 12px 12px;
            margin-bottom: 12px;
        }

        /* Floating Label Custom */
        .float-group {
            position: relative;
            margin-bottom: 14px;
        }
        .float-group .float-control {
            width: 100%;
            height: 44px;
            padding: 12px 12px 4px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
            background-color: #FFFFFF;
            border: 1px solid #D1D5DB;
            border-radius: 11px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .float-group textarea.float-control {
            height: auto;
            min-height: 68px;
            padding-top: 16px;
            resize: vertical;
        }
        .float-group .float-control:focus {
            border-color: var(--emerald-primary);
            box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.12);
        }
        .float-group .float-label {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12.5px;
            color: var(--text-muted);
            background-color: #FFFFFF;
            padding: 0 4px;
            pointer-events: none;
            transition: top 0.18s ease, transform 0.18s ease, font-size 0.18s ease, color 0.18s ease;
            line-height: 1;
            max-width: calc(100% - 20px);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .float-group textarea.float-control ~ .float-label {
            top: 16px;
            transform: translateY(0);
        }
        .float-group .float-control:focus ~ .float-label,
        .float-group.is-filled .float-label {
            top: 0;
            transform: translateY(-50%);
            font-size: 10.5px;
            font-weight: 700;
            color: var(--emerald-primary);
        }

        /* Searchable Filter Dropdown */
        .filter-combo {
            position: relative;
        }
        .filter-dropdown-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 210px;
            overflow-y: auto;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
            z-index: 1050;
        }
        .filter-dropdown-list.show {
            display: block;
        }
        .filter-option-item {
            padding: 8px 12px;
            font-size: 12.5px;
            cursor: pointer;
            border-bottom: 1px solid #F1F5F9;
            line-height: 1.3;
        }
        .filter-option-item:last-child {
            border-bottom: none;
        }
        .filter-option-item:hover {
            background: var(--emerald-soft);
            color: var(--emerald-dark);
            font-weight: 600;
        }

        .btn-submit-emerald {
            background: linear-gradient(135deg, #064E3B 0%, #047857 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 14px;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit-emerald:disabled {
            opacity: 0.75;
        }
    </style>
</head>
<body>
    @php
        $prId         = $pr->ID ?? $pr->id;
        $prNo         = $pr->PR_No ?? ($pr->pr_no ?? '');
        $prDate       = $pr->PR_Date ?? ($pr->pr_date ?? date('Y-m-d'));
        $hopeDate     = $pr->Hope_Date ?? ($pr->hope_date ?? \Carbon\Carbon::now()->addDays(7)->format('Y-m-d'));
        $deptVal      = $pr->Department ?? ($pr->department ?? '');
        $approvalType = $pr->Supplies_Type ?? ($pr->Approval_Type ?? 'NON-OPS');
        $mainCat      = $pr->Category ?? ($pr->Main_Category ?? 'NON ASSET');
        $procCat      = $pr->Procurement_Category ?? ($pr->procurement_category ?? 'Rutin');
        $subCat       = $pr->Sub_Category ?? ($pr->sub_category ?? 'BARANG');
        $urgency      = $pr->Urgency ?? ($pr->urgency ?? 'Normal');
        $costCenter   = $pr->Cost_Center ?? ($pr->cost_center ?? '');
        $siteDest     = $pr->PIC_Address ?? ($pr->Site_Destination ?? '');
        $picName      = $pr->PIC ?? ($pr->PIC_Name ?? ($user->Name ?? ''));
        $picPhone     = $pr->PIC_Phone ?? ($pr->pic_phone ?? ($user->Phone ?? ($user->phone ?? '')));
        $picEmail     = $pr->PIC_Email ?? ($pr->pic_email ?? ($user->email ?? ''));
        $rabLink      = $pr->RAB_Link ?? ($pr->rab_link ?? '');
        $currentStatus = strtoupper((string) ($pr->Status ?? ($pr->status ?? 'OPEN')));
    @endphp

    <div class="mobile-container">
        <div class="top-bar">
            <a href="{{ route('procurement.mobile.pr.edit.list') }}" class="btn-back-soft" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h6 fw-bold mb-0">Edit Purchase Request</h1>
                <div class="small text-muted">No. PR: <strong>{{ $prNo }}</strong> &bull; Status: <strong>{{ $currentStatus }}</strong></div>
            </div>
        </div>

        <form action="{{ url('purchase-request-update/' . $prId) }}" method="POST" enctype="multipart/form-data" id="formMobileEditPR">
            @csrf
            <input type="hidden" name="pr_id" value="{{ $prId }}">
            <input type="hidden" name="pr_no" value="{{ $prNo }}">

            <!-- Aksi Status Hold / Cancel & Keterangan -->
            <div class="section-card">
                <div class="section-label"><i class="fas fa-sliders-h mr-1"></i> Status Aksi, RAB &amp; Keterangan</div>

                <div class="float-group is-filled">
                    <select name="action_status" id="actionStatusSelect" class="float-control">
                        <option value="ACTIVE" {{ in_array($currentStatus, ['OPEN', 'ACTIVE', 'PENDING', 'ON PROCESS']) ? 'selected' : '' }}>Simpan / Tetap Proses</option>
                        <option value="HOLD" {{ $currentStatus === 'HOLD' ? 'selected' : '' }}>Hold PR</option>
                        <option value="CANCEL" {{ $currentStatus === 'CANCEL' ? 'selected' : '' }}>Cancel PR</option>
                    </select>
                    <label class="float-label">Aksi Status PR (Hold / Cancel)</label>
                </div>

                <div class="float-group {{ !empty($rabLink) ? 'is-filled' : '' }}">
                    <input type="url" name="rab_link" class="float-control" value="{{ $rabLink }}">
                    <label class="float-label">RAB Link (URL Dokumen / Drawing)</label>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted mb-1">Submit Attachment (Foto, Drawing, RAB - Max 5 MB)</label>
                    <input type="file" name="edit_attachments[]" class="form-control-file form-control-sm" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.dwg" multiple>
                </div>

                <div class="float-group mb-0">
                    <textarea name="note" id="editNoteInput" rows="2" class="float-control"></textarea>
                    <label class="float-label">Isi Keterangan / Catatan Edit, Hold atau Cancel</label>
                </div>
            </div>

            <!-- Informasi Utama PR -->
            <div class="section-card">
                <div class="section-label"><i class="fas fa-info-circle mr-1"></i> Informasi Utama PR</div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <input type="date" name="pr_date" class="float-control" value="{{ substr((string) $prDate, 0, 10) }}" required>
                            <label class="float-label">Tanggal PR *</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <input type="date" name="hope_date" class="float-control" value="{{ substr((string) $hopeDate, 0, 10) }}">
                            <label class="float-label">Hope Date</label>
                        </div>
                    </div>
                </div>

                <div class="float-group {{ !empty($deptVal) ? 'is-filled' : '' }}">
                    <div class="filter-combo" data-filter-type="department">
                        <input type="text" class="float-control filter-search-input" value="{{ $deptVal }}" autocomplete="off" required>
                        <label class="float-label">Department (Pembebanan Budget) *</label>
                        <input type="hidden" name="department" class="filter-hidden-value" value="{{ $deptVal }}">
                        <div class="filter-dropdown-list">
                            @foreach ($departments as $dept)
                                @php $dName = $dept->Department ?? $dept->Department_Name ?? ''; @endphp
                                @if ($dName !== '')
                                    <div class="filter-option-item" data-value="{{ $dName }}" data-label="{{ $dName }}">{{ $dName }}</div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="float-group is-filled">
                    <select name="approval_type" class="float-control">
                        <option value="NON-OPS" {{ $approvalType === 'NON-OPS' ? 'selected' : '' }}>B. Procurement Non-OPS</option>
                        <option value="OPS" {{ $approvalType === 'OPS' ? 'selected' : '' }}>A. Procurement OPS</option>
                    </select>
                    <label class="float-label">Jenis Pengadaan (SK Approval)</label>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="main_category" class="float-control">
                                <option value="NON ASSET" {{ $mainCat === 'NON ASSET' ? 'selected' : '' }}>NON ASSET</option>
                                <option value="ASSET" {{ $mainCat === 'ASSET' ? 'selected' : '' }}>ASSET</option>
                            </select>
                            <label class="float-label">Kategori Anggaran</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="procurement_category" class="float-control">
                                <option value="Rutin" {{ $procCat === 'Rutin' ? 'selected' : '' }}>Rutin</option>
                                <option value="Non Rutin" {{ $procCat === 'Non Rutin' ? 'selected' : '' }}>Non Rutin</option>
                            </select>
                            <label class="float-label">Kategori Pengadaan</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="sub_category" class="float-control">
                                <option value="BARANG" {{ $subCat === 'BARANG' ? 'selected' : '' }}>BARANG</option>
                                <option value="JASA" {{ $subCat === 'JASA' ? 'selected' : '' }}>JASA</option>
                            </select>
                            <label class="float-label">Sub Kategori</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="urgency" class="float-control">
                                <option value="Normal" {{ $urgency === 'Normal' ? 'selected' : '' }}>Normal</option>
                                <option value="Urgent" {{ $urgency === 'Urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            <label class="float-label">Tingkat Urgensi PR</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group {{ !empty($costCenter) ? 'is-filled' : '' }}">
                            <input type="text" name="cost_center" class="float-control" value="{{ $costCenter }}">
                            <label class="float-label">Cost Center</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group {{ !empty($siteDest) ? 'is-filled' : '' }}">
                            <input type="text" name="site_destination" class="float-control" value="{{ $siteDest }}">
                            <label class="float-label">Site Tujuan</label>
                        </div>
                    </div>
                </div>

                <div class="float-group {{ !empty($picName) ? 'is-filled' : '' }}">
                    <input type="text" name="pic_name" class="float-control" value="{{ $picName }}">
                    <label class="float-label">PIC (Penanggung Jawab)</label>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group {{ !empty($picPhone) ? 'is-filled' : '' }}">
                            <input type="text" name="pic_phone" class="float-control" value="{{ $picPhone }}">
                            <label class="float-label">Phone PIC</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group {{ !empty($picEmail) ? 'is-filled' : '' }}">
                            <input type="email" name="pic_email" class="float-control" value="{{ $picEmail }}">
                            <label class="float-label">Email PIC</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Material -->
            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-label mb-0"><i class="fas fa-boxes mr-1"></i> Rincian Material / Item</div>
                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" onclick="tambahItemEdit()">+ Item</button>
                </div>

                <div id="editItemsContainer">
                    @php $loopItems = count($items) > 0 ? $items : [(object)[]]; @endphp
					    @foreach ($loopItems as $idx => $it)
					        @php
					            $iCode   = $it->Product_Code ?? ($it->product_code ?? '');
					            $iDesc   = $it->Product_Desc ?? ($it->Description ?? ($it->product_desc ?? ''));
					            $iSpec   = $it->Specification ?? ($it->specification ?? '');
					            $iUnit   = $it->Unit ?? ($it->Unit_Name ?? ($it->unit ?? 'PCS'));
					            $iQty    = $it->Qty ?? ($it->qty ?? 1);
					            $iPrice  = $it->Default_Price ?? ($it->Est_Price ?? ($it->price ?? 0));
					            $iLabel  = trim($iCode . ($iDesc ? ' - ' . $iDesc : ''));
					        @endphp
                        <div class="item-box edit-pr-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small font-weight-bold text-muted item-number">Item #{{ $idx + 1 }}</span>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 btn-remove-item" style="{{ count($loopItems) > 1 ? '' : 'display:none;' }}" onclick="hapusItemEdit(this)">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </div>

                            <div class="float-group {{ !empty($iLabel) ? 'is-filled' : '' }}">
                                <div class="filter-combo" data-filter-type="material">
                                    <input type="text" class="float-control filter-search-input" value="{{ $iLabel }}" autocomplete="off" required>
                                    <label class="float-label">Product Code *</label>
                                    <input type="hidden" name="product_code[]" class="filter-hidden-value" value="{{ $iCode }}">
                                    <div class="filter-dropdown-list">
                                        @foreach ($materials as $mat)
                                            @php
                                                $mCode = $mat->Product_Code ?? '';
                                                $mDesc = $mat->Description ?? '';
                                                $mSpec = $mat->Specification ?? '';
                                                $mUnit = $mat->Unit_Name ?? 'PCS';
                                                $mLabel = trim($mCode . ' - ' . $mDesc);
                                            @endphp
                                            <div class="filter-option-item"
                                                 data-value="{{ $mCode }}"
                                                 data-label="{{ $mLabel }}"
                                                 data-desc="{{ $mDesc }}"
                                                 data-spec="{{ $mSpec }}"
                                                 data-unit="{{ $mUnit }}">
                                                <strong>{{ $mCode }}</strong> — {{ $mDesc }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="float-group {{ !empty($iDesc) ? 'is-filled' : '' }}">
                                <input type="text" name="product_desc[]" class="float-control input-desc" value="{{ $iDesc }}">
                                <label class="float-label">Nama Item / Deskripsi</label>
                            </div>

                            <div class="row">
                                <div class="col-7">
                                    <div class="float-group {{ !empty($iSpec) ? 'is-filled' : '' }}">
                                        <input type="text" name="specification[]" class="float-control input-spec" value="{{ $iSpec }}">
                                        <label class="float-label">Spesifikasi (Spec)</label>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="float-group is-filled">
                                        <input type="text" name="unit[]" class="float-control input-unit" value="{{ $iUnit }}">
                                        <label class="float-label">Satuan</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="float-group is-filled">
                                        <input type="number" name="qty[]" class="float-control input-qty" min="1" step="any" value="{{ $iQty }}" oninput="hitungEstimasiEdit()" required>
                                        <label class="float-label">Qty *</label>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="float-group is-filled">
                                        <input type="number" name="price[]" class="float-control input-price" min="0" step="any" value="{{ $iPrice }}" oninput="hitungEstimasiEdit()">
                                        <label class="float-label">Est. Price (Rp)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center bg-white border rounded px-2 py-1">
                                <span class="small text-muted">Subtotal:</span>
                                <span class="small font-weight-bold text-dark item-subtotal-text">Rp 0</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <span class="small font-weight-bold text-muted">Estimated Budget (Rp):</span>
                    <span class="font-weight-bold text-success" id="displayEstimasiEdit">Rp 0</span>
                </div>
            </div>

            <button type="submit" class="btn-submit-emerald" id="btnSubmitEditPR">
                <span class="spinner-border spinner-border-sm d-none" id="editSpinner" role="status" aria-hidden="true"></span>
                <i class="fas fa-save" id="editIcon"></i>
                <span id="editText">Simpan Perubahan PR</span>
            </button>
        </form>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'dashboard'])

    <script>
        function syncFloatingGroup(el) {
            var group = el.closest('.float-group');
            if (!group) return;
            var hasValue = String(el.value || '').trim() !== '';
            var isFocused = document.activeElement === el;
            if (hasValue || isFocused || el.tagName === 'SELECT') {
                group.classList.add('is-filled');
            } else {
                group.classList.remove('is-filled');
            }
        }

        function bindFloatingLabels(root) {
            (root || document).querySelectorAll('.float-group .float-control').forEach(function(el) {
                syncFloatingGroup(el);
                ['focus', 'blur', 'input', 'change'].forEach(function(ev) {
                    el.addEventListener(ev, function() { syncFloatingGroup(el); });
                });
            });
        }

        function filterDropdownItems(comboEl) {
            var searchInp = comboEl.querySelector('.filter-search-input');
            var listEl = comboEl.querySelector('.filter-dropdown-list');
            var query = (searchInp.value || '').toLowerCase().trim();

            listEl.querySelectorAll('.filter-option-item').forEach(function(item) {
                var text = (item.getAttribute('data-label') || item.textContent || '').toLowerCase();
                item.style.display = (query === '' || text.indexOf(query) !== -1) ? '' : 'none';
            });
            listEl.classList.add('show');
        }

        function selectDropdownOption(comboEl, optionEl) {
            var searchInp = comboEl.querySelector('.filter-search-input');
            var hiddenInp = comboEl.querySelector('.filter-hidden-value');
            var listEl = comboEl.querySelector('.filter-dropdown-list');

            if (searchInp) {
                searchInp.value = optionEl.getAttribute('data-label') || '';
                syncFloatingGroup(searchInp);
            }
            if (hiddenInp) hiddenInp.value = optionEl.getAttribute('data-value') || '';
            if (listEl) listEl.classList.remove('show');

            if (comboEl.getAttribute('data-filter-type') === 'material') {
                var box = comboEl.closest('.edit-pr-item');
                if (box) {
                    ['desc', 'spec', 'unit'].forEach(function(key) {
                        var inp = box.querySelector('.input-' + key);
                        if (inp) {
                            inp.value = optionEl.getAttribute('data-' + key) || (key === 'unit' ? 'PCS' : '');
                            syncFloatingGroup(inp);
                        }
                    });
                }
            }
        }

        function bindFilterComboEvents(root) {
            (root || document).querySelectorAll('.filter-combo').forEach(function(combo) {
                if (combo.dataset.bound === '1') return;
                combo.dataset.bound = '1';

                var searchInp = combo.querySelector('.filter-search-input');
                var hiddenInp = combo.querySelector('.filter-hidden-value');
                var listEl = combo.querySelector('.filter-dropdown-list');

                searchInp.addEventListener('focus', function() { filterDropdownItems(combo); });
                searchInp.addEventListener('input', function() {
                    if (hiddenInp && combo.getAttribute('data-filter-type') === 'department') {
                        hiddenInp.value = searchInp.value.trim();
                    } else if (hiddenInp && combo.getAttribute('data-filter-type') === 'material') {
                        hiddenInp.value = '';
                    }
                    filterDropdownItems(combo);
                });

                listEl.querySelectorAll('.filter-option-item').forEach(function(opt) {
                    opt.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        selectDropdownOption(combo, opt);
                    });
                });
            });
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.filter-combo')) {
                document.querySelectorAll('.filter-dropdown-list.show').forEach(function(el) {
                    el.classList.remove('show');
                });
            }
        });

        function refreshEditIndexes() {
            var items = document.querySelectorAll('#editItemsContainer .edit-pr-item');
            items.forEach(function(box, idx) {
                var num = box.querySelector('.item-number');
                var del = box.querySelector('.btn-remove-item');
                if (num) num.textContent = 'Item #' + (idx + 1);
                if (del) del.style.display = items.length > 1 ? 'inline-block' : 'none';
            });
        }

        function tambahItemEdit() {
            var container = document.getElementById('editItemsContainer');
            var first = container.querySelector('.edit-pr-item');
            if (!first) return;

            var clone = first.cloneNode(true);
            clone.querySelectorAll('.filter-combo').forEach(function(c) {
                delete c.dataset.bound;
                var dList = c.querySelector('.filter-dropdown-list');
                if (dList) dList.classList.remove('show');
            });

            clone.querySelectorAll('input').forEach(function(inp) {
                if (inp.type === 'number') {
                    inp.value = inp.classList.contains('input-qty') ? '1' : '0';
                } else if (inp.classList.contains('input-unit')) {
                    inp.value = 'PCS';
                } else {
                    inp.value = '';
                }
            });

            container.appendChild(clone);
            bindFilterComboEvents(clone);
            bindFloatingLabels(clone);
            refreshEditIndexes();
            hitungEstimasiEdit();
        }

        function hapusItemEdit(btn) {
            var items = document.querySelectorAll('#editItemsContainer .edit-pr-item');
            if (items.length <= 1) return;
            var box = btn.closest('.edit-pr-item');
            if (box) box.remove();
            refreshEditIndexes();
            hitungEstimasiEdit();
        }

        function hitungEstimasiEdit() {
            var total = 0;
            document.querySelectorAll('.edit-pr-item').forEach(function(box) {
                var qty = parseFloat(box.querySelector('.input-qty')?.value || 0);
                var price = parseFloat(box.querySelector('.input-price')?.value || 0);
                var sub = (qty > 0 && price > 0) ? (qty * price) : 0;
                total += sub;
                var subEl = box.querySelector('.item-subtotal-text');
                if (subEl) subEl.textContent = 'Rp ' + sub.toLocaleString('id-ID');
            });
            document.getElementById('displayEstimasiEdit').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        function setEditLoading(loading) {
            var btn = document.getElementById('btnSubmitEditPR');
            var spinner = document.getElementById('editSpinner');
            var icon = document.getElementById('editIcon');
            var text = document.getElementById('editText');
            if (btn) btn.disabled = loading;
            if (spinner) spinner.classList.toggle('d-none', !loading);
            if (icon) icon.classList.toggle('d-none', loading);
            if (text) text.textContent = loading ? 'Menyimpan Perubahan...' : 'Simpan Perubahan PR';
        }

        document.addEventListener('DOMContentLoaded', function() {
            bindFloatingLabels(document);
            bindFilterComboEvents(document);
            refreshEditIndexes();
            hitungEstimasiEdit();

            var form = document.getElementById('formMobileEditPR');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                var actionStatus = document.getElementById('actionStatusSelect')?.value || 'ACTIVE';
                var noteVal = (document.getElementById('editNoteInput')?.value || '').trim();

                if ((actionStatus === 'HOLD' || actionStatus === 'CANCEL') && noteVal === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Keterangan Wajib Diisi',
                        text: 'Silakan isi keterangan / alasan ketika memilih status ' + actionStatus + '.',
                        confirmButtonColor: '#047857'
                    });
                    return;
                }

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                setEditLoading(true);
                Swal.fire({
                    title: 'Menyimpan Perubahan PR...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() { Swal.showLoading(); }
                });

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: new FormData(form)
                })
                .then(async function(response) {
                    var ct = response.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') !== -1) {
                        return { ok: response.ok, status: response.status, data: await response.json() };
                    }
                    await response.text();
                    return { ok: response.ok, status: response.status, url: response.url };
                })
                .then(function(res) {
                    setEditLoading(false);
                    var ok = res.data ? (res.ok && res.data.success !== false) : res.ok;
                    var msg = (res.data && res.data.message) ? res.data.message : 'Perubahan Purchase Request berhasil disimpan.';

                    if (ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: msg,
                            confirmButtonColor: '#047857'
                        }).then(function() {
                            window.location.href = (res.data && res.data.redirect)
                                ? res.data.redirect
                                : "{{ route('procurement.mobile.pr.edit.list') }}";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: (res.data && res.data.message) ? res.data.message : 'Periksa kembali inputan atau lampiran Anda.',
                            confirmButtonColor: '#DC2626'
                        });
                    }
                })
                .catch(function() {
                    setEditLoading(false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Bermasalah',
                        text: 'Tidak dapat terhubung ke server. Silakan coba lagi.',
                        confirmButtonColor: '#DC2626'
                    });
                });
            });
        });
    </script>
</body>
</html>