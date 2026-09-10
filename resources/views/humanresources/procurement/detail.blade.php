<div class="mt-3">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white py-3 px-2">
            <h5 class="card-title mb-0 font-weight-bold d-inline-block" style="color: azure">Detail Procurement Request</h5>
            <button type="button" class="float-right btn btn-secondary" onclick="$('#PurchaseManagementDiv').html('')"><i class="fa fa-times"></i> Close</button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <input type="hidden" id="ID" name="ID" value="{{ $pr->ID }}" />

                <!-- Row 1: Dokumen & Vendor -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label class="font-weight-bold">PR No</label>
                        <input type="text" id="PR_No" name="PR_No" class="form-control form-control-sm bg-light" readonly value="{{ $pr->PR_No }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Date Request</label>
                        <input type="text" id="PR_Date" name="PR_Date" class="form-control form-control-sm bg-light" readonly value="{{ date('Y-m-d', strtotime($pr->PR_Date)) }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">User Request</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm col-md-4 bg-light" id="User_Request" name="User_Request" value="{{ $pr->User_Request }}" readonly />
                            <input type="text" class="form-control form-control-sm col-md-8 bg-light" id="User_Request_Name" name="User_Request_Name" value="{{ $pr->User_Request_Name }}" readonly />
                        </div>
                    </div>
                </div>

                <!-- Row 2: Budgeting -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="font-weight-bold">Dept. (Budgeting)</label>
                        <select id="Department" name="Department" class="form-control form-control-sm">
                            @foreach ($Department as $Data)
                                <option value="{{ $Data->Initial }}" {{ $pr->Department == $Data->Initial ? 'selected' : '' }}>
                                    {{ $Data->Department }} - {{ $Data->Initial_Code }} - {{ $Data->Initial }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">Vendor</label>
                        <select name="Supplier_ID" id="Supplier_ID" class="form-control form-control-sm">
                            <option value="">-- Pilih Vendor --</option>
                            @foreach ($company as $item)
                                <option value="{{ $item->Supplier_ID }}" {{ $pr->Supplier_ID == $item->Supplier_ID ? 'selected' : '' }}>
                                    {{ $item->Supplier_ID }} - {{ $item->Company_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 3: PIC -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="font-weight-bold">PIC to Receive</label>
                        <input type="text" id="PIC" name="PIC" class="form-control form-control-sm" required placeholder="Nama penerima" value="{{ $pr->PIC }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">PIC Phone</label>
                        <input type="text" id="PIC_Phone" name="PIC_Phone" class="form-control form-control-sm" required placeholder="Nomor telepon" value="{{ $pr->PIC_Phone }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">PIC Address</label>
                        <input type="text" id="PIC_Address" name="PIC_Address" class="form-control form-control-sm" required placeholder="Alamat" value="{{ $pr->PIC_Address }}" />
                    </div>
                </div>

                <!-- Row 4: Kategori -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label class="font-weight-bold">Category</label>
                        <select id="Category" name="Category" class="form-control form-control-sm">
                            <option value="">Select Category</option>
                            <option {{ $pr->Category == 'ASSET' ? 'selected' : '' }}>ASSET</option>
                            <option {{ $pr->Category == 'NON ASSET' ? 'selected' : '' }}>NON ASSET</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Jenis</label>
                        <select id="Sub_Category" name="Sub_Category" class="form-control form-control-sm">
                            <option value="">Select Sub Category</option>
                            <option {{ $pr->Sub_Category == 'BARANG' ? 'selected' : '' }}>BARANG</option>
                            <option {{ $pr->Sub_Category == 'JASA' ? 'selected' : '' }}>JASA</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Priority</label>
                        <select name="Priority_Status" id="Priority_Status" class="form-control form-control-sm">
                            <option {{ $pr->Priority_Status == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                            <option {{ $pr->Priority_Status == 'URGENT' ? 'selected' : '' }}>URGENT</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">Estimated Budget</label>
                        <input type="number" id="Estimated_Budget" name="Estimated_Budget" class="form-control form-control-sm" value="{{ (float)$pr->Estimated_Budget }}" min="0" required />
                    </div>
                </div>

                <!-- Row 4b: Approval Note / Keterangan -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="font-weight-bold">Approval Note / Keterangan</label>
                        <textarea id="Remark" name="Remark" class="form-control form-control-sm" rows="2" placeholder="Catatan..."></textarea>
                        @if(!empty($pr->Note))
                            <small class="text-muted d-block mt-1">History Note: <br>{!! nl2br(e($pr->Note)) !!}</small>
                        @endif
                    </div>
                </div>

                <!-- Row 5: Multi Product Table (Persis Create) -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="font-weight-bold">Product List</label>
                        <p class="text-muted small">Daftar produk pengajuan PR No {{ $pr->PR_No }}.</p>
                        
                        <table class="table table-sm table-bordered" id="productTable">
                            <thead>
                                <tr>
                                    <th style="width:3%">No</th>
                                    <th style="width:15%">Product Description</th>
                                    <th style="width:8%">Qty</th>
                                    <th style="width:22%">Specification</th>
                                    <th style="width:8%">Unit</th>
                                    <th style="width:10%">Price</th>
                                    <th style="width:10%">Total</th>
                                    <th style="width:10%">Reference</th>
                                    <th style="width:10%">Upload File</th>
                                    <th style="width:7%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                @forelse($pr->details as $index => $dtl)
                                    <tr class="product-row">
                                        <td>{{ $index + 1 }}</td>
                                        <td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Deskripsi" value="{{ $dtl->Product_Desc }}" /></td>
                                        <td><input type="number" name="Qty[]" class="form-control form-control-sm qty" value="{{ $dtl->Qty }}" min="1" /></td>
                                        <td><textarea name="Specification[]" class="form-control form-control-sm specification">{{ $dtl->Specification }}</textarea></td>
                                        <td><input type="text" name="Unit[]" class="form-control form-control-sm unit" value="{{ $dtl->Unit ?: 'PCS' }}" /></td>
                                        <td><input type="number" name="Price[]" class="form-control form-control-sm price" value="{{ (float)($dtl->Default_Price ?: 0) }}" min="0" step="1000" /></td>
                                        <td><input type="text" name="Total[]" class="form-control form-control-sm total bg-light text-right" value="{{ (float)($dtl->Qty * ($dtl->Default_Price ?: 0)) }}" readonly /></td>
                                        <td><input type="text" name="Reference[]" class="form-control form-control-sm reference bg-light text-right" value="{{ $dtl->Reference }}" /></td>
                                        <td>
                                            @if(!empty($dtl->Attachment))
                                                <a href="{{ asset('storage/'.$dtl->Attachment) }}" target="_blank" class="btn btn-xs btn-outline-info mb-1"><i class="fa fa-download"></i> View</a>
                                            @endif
                                            <input type="file" name="Image[]" class="form-control form-control-sm image bg-light text-right" />
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="product-row">
                                        <td>1</td>
                                        <td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Deskripsi" /></td>
                                        <td><input type="number" name="Qty[]" class="form-control form-control-sm qty" value="1" min="1" /></td>
                                        <td><textarea name="Specification[]" class="form-control form-control-sm specification"></textarea></td>
                                        <td><input type="text" name="Unit[]" class="form-control form-control-sm unit" value="PCS" /></td>
                                        <td><input type="number" name="Price[]" class="form-control form-control-sm price" value="0" min="0" step="1000" /></td>
                                        <td><input type="text" name="Total[]" class="form-control form-control-sm total bg-light text-right" value="0" readonly /></td>
                                        <td><input type="text" name="Reference[]" class="form-control form-control-sm reference bg-light text-right" value="" /></td>
                                        <td><input type="file" name="Image[]" class="form-control form-control-sm image bg-light text-right" /></td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <div class="float-right mt-2">
                            <button type="button" id="addProductRow" class="btn btn-info btn-sm">
                                <i class="fa fa-plus"></i> Add Product
                            </button>
                            <span class="text-muted ml-2">Total: <span id="totalProducts">{{ count($pr->details) ?: 1 }}</span> products</span>
                            <span class="text-muted ml-2">Estimate: <span id="totalEstimate">Rp 0</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div id="divtom" class="d-flex justify-content-end mt-3">
                @if($pr->Status == 'CHANGES_REVIEWED_BY_CREATOR')
                    <button type="button" id="SubmitPRDetailApproveChanges" onclick="approvalFunc(3)" class="btn btn-warning btn-sm mr-2">
                        <i class="fa fa-check-circle"></i> Approve Changes
                    </button>
                @endif
                <button type="button" id="SubmitPRDetail" onclick="approvalFunc(0)" class="btn btn-success btn-sm">
                    <i class="fa fa-check"></i> Save / Update PR
                </button>
            </div>

            <div id="WinCOA"></div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $.unblockUI();
        $('#Supplier_ID').select2();
        $('#Department').select2();
        
        var productCount = $('#productTableBody tr.product-row').length || 1;

        function calculateTotal(row) {
            var qty = parseFloat(row.find('.qty').val()) || 0;
            var price = parseFloat(row.find('.price').val()) || 0;
            row.find('.total').val(qty * price);
            updateTotalEstimate();
        }

        $(document).on('input change', '#productTableBody .qty, #productTableBody .price', function() {
            calculateTotal($(this).closest('.product-row'));
        });

        function updateTotalEstimate() {
            var total = 0;
            $('#productTableBody .total').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalEstimate').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
        }

        updateTotalEstimate();

        $('#addProductRow').click(function() {
            productCount++;
            var newRow = `
                <tr class="product-row">
                    <td>${productCount}</td>
                    <td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Deskripsi" /></td>
                    <td><input type="number" name="Qty[]" class="form-control form-control-sm qty" value="1" min="1" /></td>
                    <td><textarea name="Specification[]" class="form-control form-control-sm specification" placeholder="Spesifikasi"></textarea></td>
                    <td><input type="text" name="Unit[]" class="form-control form-control-sm unit" value="PCS" /></td>
                    <td><input type="number" name="Price[]" class="form-control form-control-sm price" value="0" min="0" step="1000" /></td>
                    <td><input type="text" name="Total[]" class="form-control form-control-sm total bg-light text-right" value="0" readonly /></td>
                    <td><input type="text" name="Reference[]" class="form-control form-control-sm reference bg-light text-right" value="" /></td>
                    <td><input type="file" name="Image[]" class="form-control form-control-sm image bg-light text-right" /></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#productTableBody').append(newRow);
            renumberRows();
            calculateTotal($('#productTableBody tr:last'));
        });

        $(document).off('click', '.remove-row').on('click', '.remove-row', function() {
            var row = $(this).closest('.product-row');
            if ($('#productTableBody tr.product-row').length > 1) {
                row.remove();
                renumberRows();
                updateTotalEstimate();
            } else {
                alert('Minimal harus ada 1 produk!');
            }
        });

        function renumberRows() {
            var i = 1;
            $('#productTableBody tr.product-row').each(function() {
                $(this).find('td:first').text(i);
                i++;
            });
            productCount = i - 1;
            $('#totalProducts').text(productCount);
        }
    });

    function getAllValues() {
        var inputValues = {};
        $('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
            var type = $(this).attr("type");
            var id = $(this).attr("id");
            var value = $(this).val();
            if (id && type != "button" && type != "submit") {
                if (type == "checkbox" || type == "radio") {
                    if (this.checked) inputValues[id] = value;
                } else {
                    inputValues[id] = value;
                }
            }
        });
        return inputValues;
    }

    function approvalFunc(stat) {
        var PIC = $("#PIC").val();
        var PIC_Phone = $("#PIC_Phone").val();
        var PIC_Address = $("#PIC_Address").val();
        var Estimated_Budget = $("#Estimated_Budget").val();

        if (PIC == '') {
            alert("Kolom Penerima Barang (PIC to Receive) Harus Diisi!");
            $("#PIC").focus();
            return false;
        }
        if (PIC_Address == '') {
            alert("Kolom alamat PIC (PIC Address) Harus Diisi!");
            $("#PIC_Address").focus();
            return false;
        }
        if (PIC_Address.length < 3) {
            alert("Kolom alamat PIC (PIC Address) Minimal 3 Karakter!");
            $("#PIC_Address").focus();
            return false;
        }
        if (PIC_Phone == '') {
            alert("Kolom nomor telepon (PIC Phone) Harus Diisi!");
            $("#PIC_Phone").focus();
            return false;
        }
        if (Estimated_Budget == '' || parseFloat(Estimated_Budget) <= 0) {
            alert("Kolom Estimated Budget Harus Diisi!");
            $("#Estimated_Budget").focus();
            return false;
        }

        $.blockUI();

        var dapat = getAllValues();
        dapat['stat'] = stat;

        var gridData = [];
        $('#productTableBody tr.product-row').each(function() {
            gridData.push({
                Product_Desc: $(this).find('.product-desc').val(),
                Qty: $(this).find('.qty').val(),
                Specification: $(this).find('.specification').val(),
                Unit: $(this).find('.unit').val(),
                Price: $(this).find('.price').val(),
                Total: $(this).find('.total').val(),
                Reference: $(this).find('.reference').val(),
            });
        });

        dapat['GridData'] = JSON.stringify(gridData);
        dapat['RowCount'] = gridData.length;

        var targetUrl = stat == 3 ? "hrd-procurement-approval-detail-submit?_token={{ csrf_token() }}" : "hrd-procurement-detail-submit?_token={{ csrf_token() }}";

        $.ajax({
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
            url: targetUrl,
            type: "POST",
            data: dapat,
            success: function(data) {
                if (data == 'success') {
                    if (stat == 3) {
                        alert('Perubahan PR No <?php echo $pr->PR_No ?> telah disetujui! Status dikembalikan ke tahap approval.');
                    } else {
                        alert('Purchase Request Updated! No: <?php echo $pr->PR_No ?>');
                    }
                    setTimeout(function() {
                        $.unblockUI();
                        if (typeof refreshData === 'function') refreshData();
                        $('#PurchaseManagementDiv').html("");
                    }, 1000);
                } else {
                    alert('Gagal: ' + data);
                    $.unblockUI();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert("Error: " + errorThrown);
                $.unblockUI();
            }
        });
    }
</script>

<div id="PRSupplierDet"></div>
