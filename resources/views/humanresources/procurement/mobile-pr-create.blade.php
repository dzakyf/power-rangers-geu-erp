<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Buat Purchase Request</title>
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
        .item-box .float-group .float-control {
            background-color: #FFFFFF;
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
        .filter-option-item:hover,
        .filter-option-item.active {
            background: var(--emerald-soft);
            color: var(--emerald-dark);
            font-weight: 600;
        }
        .filter-empty {
            padding: 10px 12px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
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
            transition: opacity 0.2s ease, transform 0.15s ease;
        }
        .btn-submit-emerald:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    @php
        $hopeDateDefault = \Carbon\Carbon::now()->addDays(7)->format('Y-m-d');
        $picNameDefault = $user->Name ?? ($user->username ?? '');
        $picEmailDefault = $user->email ?? '';
    @endphp

    <div class="mobile-container">
        <div class="top-bar">
            <a href="{{ route('procurement.mobile.index') }}" class="btn-back-soft" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h6 fw-bold mb-0">Buat Purchase Request</h1>
                <div class="small text-muted">No. PR: <strong>{{ $generated_pr_no }}</strong></div>
            </div>
        </div>

        <form action="{{ url('purchase-request-store') }}" method="POST" enctype="multipart/form-data" id="formMobilePR">
            @csrf
            <input type="hidden" name="pr_no" value="{{ $generated_pr_no }}">

            <div class="section-card">
                <div class="section-label"><i class="fas fa-info-circle mr-1"></i> Informasi Utama PR</div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <input type="date" name="pr_date" class="float-control" value="{{ date('Y-m-d') }}" required>
                            <label class="float-label">Tanggal PR *</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <input type="date" name="hope_date" class="float-control" value="{{ $hopeDateDefault }}">
                            <label class="float-label">Hope Date</label>
                        </div>
                    </div>
                </div>

                <!-- Department dengan Filter Ketik & Floating Label -->
                <div class="float-group {{ !empty($userDepartment) ? 'is-filled' : '' }}">
                    <div class="filter-combo" data-filter-type="department">
                        <input type="text"
                               class="float-control filter-search-input"
                               value="{{ $userDepartment }}"
                               autocomplete="off"
                               required>
                        <label class="float-label">Department (Pembebanan Budget) *</label>
                        <input type="hidden" name="department" class="filter-hidden-value" value="{{ $userDepartment }}">
                        <div class="filter-dropdown-list">
                            @foreach ($departments as $dept)
                                @php $deptVal = $dept->Department ?? $dept->Department_Name ?? ''; @endphp
                                @if ($deptVal !== '')
                                    <div class="filter-option-item"
                                         data-value="{{ $deptVal }}"
                                         data-label="{{ $deptVal }}">
                                        {{ $deptVal }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="float-group is-filled">
                    <select name="approval_type" class="float-control" required>
                        <option value="NON-OPS" selected>B. Procurement Non-OPS</option>
                        <option value="OPS">A. Procurement OPS</option>
                    </select>
                    <label class="float-label">Jenis Pengadaan (SK Approval) *</label>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="main_category" class="float-control">
                                <option value="NON ASSET" selected>NON ASSET</option>
                                <option value="ASSET">ASSET</option>
                            </select>
                            <label class="float-label">Kategori Anggaran</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="procurement_category" class="float-control">
                                <option value="Rutin" selected>Rutin</option>
                                <option value="Non Rutin">Non Rutin</option>
                            </select>
                            <label class="float-label">Kategori Pengadaan</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="sub_category" class="float-control">
                                <option value="BARANG" selected>BARANG</option>
                                <option value="JASA">JASA</option>
                            </select>
                            <label class="float-label">Sub Kategori</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group is-filled">
                            <select name="urgency" class="float-control">
                                <option value="Normal" selected>Normal</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                            <label class="float-label">Tingkat Urgensi PR</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group">
                            <input type="text" name="cost_center" class="float-control">
                            <label class="float-label">Cost Center</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group">
                            <input type="text" name="site_destination" class="float-control">
                            <label class="float-label">Site Tujuan</label>
                        </div>
                    </div>
                </div>

                <div class="float-group {{ !empty($picNameDefault) ? 'is-filled' : '' }}">
                    <input type="text" name="pic_name" class="float-control" value="{{ $picNameDefault }}">
                    <label class="float-label">PIC (Penanggung Jawab)</label>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="float-group">
                            <input type="text" name="pic_phone" class="float-control">
                            <label class="float-label">Phone PIC</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-group {{ !empty($picEmailDefault) ? 'is-filled' : '' }}">
                            <input type="email" name="pic_email" class="float-control" value="{{ $picEmailDefault }}">
                            <label class="float-label">Email PIC</label>
                        </div>
                    </div>
                </div>

                <div class="float-group mb-0">
                    <textarea name="note" rows="2" class="float-control"></textarea>
                    <label class="float-label">Catatan / Note Baru</label>
                </div>
            </div>

            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-label mb-0"><i class="fas fa-boxes mr-1"></i> Rincian Material / Multi Produk</div>
                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" onclick="tambahItemMobile()">
                        + Item
                    </button>
                </div>

                <div id="mobileItemsContainer">
                    <div class="item-box mobile-pr-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small font-weight-bold text-muted item-number">Item #1</span>
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 btn-remove-item" style="display:none;" onclick="hapusItemMobile(this)" title="Hapus Item">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </div>

                        <!-- Product Code dengan Filter Ketik & Floating Label -->
                        <div class="float-group">
                            <div class="filter-combo" data-filter-type="material">
                                <input type="text"
                                       class="float-control filter-search-input"
                                       autocomplete="off"
                                       required>
                                <label class="float-label">Product Code *</label>
                                <input type="hidden" name="product_code[]" class="filter-hidden-value">
                                <div class="filter-dropdown-list">
                                    @foreach ($materials as $mat)
                                        @php
                                            $pCode = $mat->Product_Code ?? '';
                                            $pDesc = $mat->Description ?? '';
                                            $pSpec = $mat->Specification ?? '';
                                            $pUnit = $mat->Unit_Name ?? 'PCS';
                                            $fullLabel = trim($pCode . ' - ' . $pDesc);
                                        @endphp
                                        <div class="filter-option-item"
                                             data-value="{{ $pCode }}"
                                             data-label="{{ $fullLabel }}"
                                             data-desc="{{ $pDesc }}"
                                             data-spec="{{ $pSpec }}"
                                             data-unit="{{ $pUnit }}">
                                            <strong>{{ $pCode }}</strong> — {{ $pDesc }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="float-group">
                            <input type="text" name="product_desc[]" class="float-control input-desc">
                            <label class="float-label">Nama Item / Deskripsi</label>
                        </div>

                        <div class="row">
                            <div class="col-7">
                                <div class="float-group">
                                    <input type="text" name="specification[]" class="float-control input-spec">
                                    <label class="float-label">Spesifikasi (Spec)</label>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="float-group is-filled">
                                    <input type="text" name="unit[]" class="float-control input-unit" value="PCS">
                                    <label class="float-label">Satuan</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="float-group is-filled">
                                    <input type="number" name="qty[]" class="float-control input-qty" min="1" step="any" value="1" oninput="hitungEstimasiBudget()" required>
                                    <label class="float-label">Qty *</label>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="float-group is-filled">
                                    <input type="number" name="price[]" class="float-control input-price" min="0" step="any" value="0" oninput="hitungEstimasiBudget()">
                                    <label class="float-label">Est. Price (Rp)</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center bg-white border rounded px-2 py-1 mb-2">
                            <span class="small text-muted">Subtotal:</span>
                            <span class="small font-weight-bold text-dark item-subtotal-text">Rp 0</span>
                        </div>

                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-muted mb-1">Lampiran (Opsional, Max 5 MB)</label>
                            <input type="file" name="attachment[]" class="form-control-file form-control-sm" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <span class="small font-weight-bold text-muted">Estimated Budget (Rp):</span>
                    <span class="font-weight-bold text-success" id="displayEstimasiBudget">Rp 0</span>
                </div>
                <input type="hidden" name="estimated_budget" id="inputEstimasiBudget" value="0">
            </div>

            <button type="submit" class="btn-submit-emerald" id="btnSubmitPR">
                <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
                <i class="fas fa-paper-plane" id="submitIcon"></i>
                <span id="submitText">Simpan Purchase Request</span>
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
                el.addEventListener('focus', function() { syncFloatingGroup(el); });
                el.addEventListener('blur', function() { syncFloatingGroup(el); });
                el.addEventListener('input', function() { syncFloatingGroup(el); });
                el.addEventListener('change', function() { syncFloatingGroup(el); });
            });
        }

        function filterDropdownItems(comboEl) {
            var searchInp = comboEl.querySelector('.filter-search-input');
            var listEl = comboEl.querySelector('.filter-dropdown-list');
            var items = listEl.querySelectorAll('.filter-option-item');
            var query = (searchInp.value || '').toLowerCase().trim();
            var visibleCount = 0;

            items.forEach(function(item) {
                var text = (item.getAttribute('data-label') || item.textContent || '').toLowerCase();
                var matched = query === '' || text.indexOf(query) !== -1;
                item.style.display = matched ? '' : 'none';
                if (matched) visibleCount++;
            });

            var emptyEl = listEl.querySelector('.filter-empty');
            if (visibleCount === 0) {
                if (!emptyEl) {
                    emptyEl = document.createElement('div');
                    emptyEl.className = 'filter-empty';
                    emptyEl.textContent = 'Tidak ada data yang cocok';
                    listEl.appendChild(emptyEl);
                }
                emptyEl.style.display = '';
            } else if (emptyEl) {
                emptyEl.style.display = 'none';
            }

            listEl.classList.add('show');
        }

        function selectDropdownOption(comboEl, optionEl) {
            var searchInp = comboEl.querySelector('.filter-search-input');
            var hiddenInp = comboEl.querySelector('.filter-hidden-value');
            var listEl = comboEl.querySelector('.filter-dropdown-list');

            var val = optionEl.getAttribute('data-value') || '';
            var label = optionEl.getAttribute('data-label') || '';

            if (searchInp) {
                searchInp.value = label;
                syncFloatingGroup(searchInp);
            }
            if (hiddenInp) hiddenInp.value = val;
            if (listEl) listEl.classList.remove('show');

            if (comboEl.getAttribute('data-filter-type') === 'material') {
                var box = comboEl.closest('.mobile-pr-item');
                if (box) {
                    var descInp = box.querySelector('.input-desc');
                    var specInp = box.querySelector('.input-spec');
                    var unitInp = box.querySelector('.input-unit');
                    if (descInp) {
                        descInp.value = optionEl.getAttribute('data-desc') || '';
                        syncFloatingGroup(descInp);
                    }
                    if (specInp) {
                        specInp.value = optionEl.getAttribute('data-spec') || '';
                        syncFloatingGroup(specInp);
                    }
                    if (unitInp) {
                        unitInp.value = optionEl.getAttribute('data-unit') || 'PCS';
                        syncFloatingGroup(unitInp);
                    }
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

                searchInp.addEventListener('focus', function() {
                    filterDropdownItems(combo);
                });

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

        function refreshItemIndexes() {
            var items = document.querySelectorAll('#mobileItemsContainer .mobile-pr-item');
            items.forEach(function(box, idx) {
                var numLabel = box.querySelector('.item-number');
                var delBtn = box.querySelector('.btn-remove-item');
                if (numLabel) numLabel.textContent = 'Item #' + (idx + 1);
                if (delBtn) delBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
            });
        }

        function tambahItemMobile() {
            var container = document.getElementById('mobileItemsContainer');
            var firstItem = container.querySelector('.mobile-pr-item');
            if (!firstItem) return;

            var clone = firstItem.cloneNode(true);
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
                } else if (inp.type === 'file') {
                    inp.value = '';
                } else {
                    inp.value = '';
                }
            });

            var subText = clone.querySelector('.item-subtotal-text');
            if (subText) subText.textContent = 'Rp 0';

            container.appendChild(clone);
            bindFilterComboEvents(clone);
            bindFloatingLabels(clone);
            refreshItemIndexes();
            hitungEstimasiBudget();
        }

        function hapusItemMobile(btnEl) {
            var container = document.getElementById('mobileItemsContainer');
            var items = container.querySelectorAll('.mobile-pr-item');
            if (items.length <= 1) return;
            var box = btnEl.closest('.mobile-pr-item');
            if (box) box.remove();
            refreshItemIndexes();
            hitungEstimasiBudget();
        }

        function hitungEstimasiBudget() {
            var total = 0;
            document.querySelectorAll('.mobile-pr-item').forEach(function(box) {
                var qty = parseFloat(box.querySelector('.input-qty')?.value || 0);
                var price = parseFloat(box.querySelector('.input-price')?.value || 0);
                var sub = (qty > 0 && price > 0) ? (qty * price) : 0;
                total += sub;

                var subEl = box.querySelector('.item-subtotal-text');
                if (subEl) subEl.textContent = 'Rp ' + sub.toLocaleString('id-ID');
            });

            document.getElementById('displayEstimasiBudget').textContent = 'Rp ' + total.toLocaleString('id-ID');
            var hiddenBudget = document.getElementById('inputEstimasiBudget');
            if (hiddenBudget) hiddenBudget.value = total;
        }

        function setSubmittingState(isLoading) {
            var btn = document.getElementById('btnSubmitPR');
            var spinner = document.getElementById('submitSpinner');
            var icon = document.getElementById('submitIcon');
            var text = document.getElementById('submitText');

            if (btn) btn.disabled = isLoading;
            if (spinner) spinner.classList.toggle('d-none', !isLoading);
            if (icon) icon.classList.toggle('d-none', isLoading);
            if (text) text.textContent = isLoading ? 'Menyimpan Purchase Request...' : 'Simpan Purchase Request';
        }

        document.addEventListener('DOMContentLoaded', function() {
            bindFloatingLabels(document);
            bindFilterComboEvents(document);
            refreshItemIndexes();
            hitungEstimasiBudget();

            var form = document.getElementById('formMobilePR');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Pastikan semua product_code terpilih dari daftar
                var missingProduct = false;
                form.querySelectorAll('.mobile-pr-item').forEach(function(box) {
                    var hiddenProd = box.querySelector('input[name="product_code[]"]');
                    if (!hiddenProd || !hiddenProd.value.trim()) {
                        missingProduct = true;
                    }
                });

                if (missingProduct) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Produk Belum Dipilih',
                        text: 'Silakan ketik dan pilih Product Code dari daftar yang tersedia pada setiap item.',
                        confirmButtonColor: '#047857'
                    });
                    return;
                }

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                setSubmittingState(true);
                Swal.fire({
                    title: 'Menyimpan PR...',
                    text: 'Mohon tunggu sebentar, data sedang diproses.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                var formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(async function(response) {
                    var contentType = response.headers.get('content-type') || '';
                    if (contentType.indexOf('application/json') !== -1) {
                        var data = await response.json();
                        return { ok: response.ok, status: response.status, data: data };
                    }
                    var htmlText = await response.text();
                    return { ok: response.ok, status: response.status, html: htmlText, redirectedUrl: response.url };
                })
                .then(function(res) {
                    setSubmittingState(false);

                    if (res.data) {
                        if (res.ok && res.data.success !== false) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.data.message || 'Purchase Request berhasil disimpan.',
                                confirmButtonColor: '#047857'
                            }).then(function() {
                                window.location.href = res.data.redirect || "{{ url('purchase-request-list') }}";
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan',
                                text: res.data.message || 'Terjadi kesalahan saat menyimpan Purchase Request.',
                                confirmButtonColor: '#DC2626'
                            });
                        }
                        return;
                    }

                    // Jika endpoint mengembalikan HTML / redirect biasa
                    if (res.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Purchase Request berhasil disimpan.',
                            confirmButtonColor: '#047857'
                        }).then(function() {
                            window.location.href = res.redirectedUrl || "{{ url('purchase-request-list') }}";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan (' + res.status + ')',
                            text: 'Periksa kembali inputan atau lampiran file Anda.',
                            confirmButtonColor: '#DC2626'
                        });
                    }
                })
                .catch(function() {
                    setSubmittingState(false);
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