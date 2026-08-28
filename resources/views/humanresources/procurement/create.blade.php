<div class="mt-3">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-3 px-2">
            <h5 class="card-title mb-0 font-weight-bold d-inline-block" style="color: azure">Form Procurement</h5>
			<button type="button" class="float-right btn btn-secondary" onclick="$('#PurchaseManagementDiv').html('')"><i class="fa fa-times"></i> Close</button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
				<!-- Row 1: Dokumen & Vendor -->
				<div class="row mt-3">
					<div class="col-md-3">
						<label class="font-weight-bold">PR No</label>
						<input type="text" id="PR_No" class="form-control form-control-sm bg-light" readonly value="<?php echo $PR_No; ?>" />
					</div>
					<div class="col-md-3">
						<label class="font-weight-bold">Date Request</label>
						<input type="text" id="PR_Date" class="form-control form-control-sm bg-light" readonly value="<?php echo date('Y-m-d'); ?>" />
					</div>
					<div class="col-md-6">
						<label class="font-weight-bold">User Request</label>
						<div class="input-group input-group-sm">
							<input type="text" class="form-control form-control-sm col-md-4 bg-light" id="User_Request" value="<?php echo $NIK; ?>" readonly />
							<input type="text" class="form-control form-control-sm col-md-8 bg-light" id="User_Request_Name" value="<?php echo $Saya; ?>" readonly />
						</div>
					</div>
				</div>

				<!-- Row 2: Budgeting -->
				<div class="row mt-3">
					<div class="col-md-6">
						<label class="font-weight-bold">Dept. (Budgeting)</label>
						<select id="Department" class="form-control form-control-sm">
							<?php foreach ($Department as $Data): ?>
								<option value="<?php echo $Data->Initial; ?>" <?php echo ($dpt == $Data->Initial) ? 'selected' : ''; ?>>
									<?php echo $Data->Department . ' - ' . $Data->Initial_Code . ' - ' . $Data->Initial; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="font-weight-bold">Vendor</label>
						<select name="Supplier_ID" id="Supplier_ID" class="form-control form-control-sm">
							<option value="">-- Pilih Vendor --</option>
							<?php foreach ($company as $item): ?>
								<option value="<?php echo $item->Supplier_ID; ?>">
									<?php echo $item->Supplier_ID . ' - ' . $item->Company_Name; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- Row 3: PIC -->
				<div class="row mt-3">
					<div class="col-md-6">
						<label class="font-weight-bold">PIC to Receive</label>
						<input type="text" id="PIC" class="form-control form-control-sm" required placeholder="Nama penerima" />
					</div>
					<div class="col-md-3">
						<label class="font-weight-bold">PIC Phone</label>
						<input type="text" id="PIC_Phone" class="form-control form-control-sm" required placeholder="Nomor telepon" />
					</div>
					<div class="col-md-3">
						<label class="font-weight-bold">PIC Address</label>
						<input type="text" id="PIC_Address" class="form-control form-control-sm" required placeholder="Alamat" />
					</div>
				</div>

				<!-- Row 4: Kategori -->
				<div class="row mt-3">
					<div class="col-md-4">
						<label class="font-weight-bold">Category</label>
						<select id="Category" class="form-control form-control-sm">
							<option value="">Select Category</option>
							<option>ASSET</option>
							<option>NON ASSET</option>
						</select>
					</div>
					<div class="col-md-4">
						<label class="font-weight-bold">Priority</label>
						<select name="Priority_Status" id="Priority_Status" class="form-control form-control-sm">
							<option>NORMAL</option>
							<option>URGENT</option>
						</select>
					</div>
					<div class="col-md-4">
						<label class="font-weight-bold">Estimated Budget</label>
						<input type="number" id="Estimated_Budget" class="form-control form-control-sm" value="0" min="0" required />
					</div>
				</div>

				<!-- Row 5: Multi Product Table -->
				<div class="row mt-3">
					<div class="col-md-12">
						<label class="font-weight-bold">Product List</label>
						<p class="text-muted small">Tambah produk dengan klik "Add Product". Klik "Search" untuk mencari produk dari master.</p>
						
						<table class="table table-sm table-bordered" id="productTable">
							<thead>
								<tr>
									<th style="width:3%">No</th>
									<!-- th style="width:12%">Product Code</th -->
									<th style="width:22%">Product Description</th>
									<th style="width:5%">Qty</th>
									<th style="width:15%">Specification</th>
									<th style="width:8%">Unit</th>
									<th style="width:10%">Price</th>
									<th style="width:10%">Total</th>
									<th style="width:10%">Reference</th>
									<th style="width:7%">Action</th>
								</tr>
							</thead>
							<tbody id="productTableBody">
								<!-- Baris default -->
								<tr class="product-row">
									<td>1</td>
									<!-- td><input type="text" name="Product_Code[]" class="form-control form-control-sm product-code" placeholder="Kode" /></td -->
									<td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Deskripsi" /></td>
									<td><input type="number" name="Qty[]" class="form-control form-control-sm qty" value="1" min="1" /></td>
									<td><input type="text" name="Specification[]" class="form-control form-control-sm specification" placeholder="Spesifikasi" /></td>
									<td><input type="text" name="Unit[]" class="form-control form-control-sm unit" value="PCS" /></td>
									<td><input type="number" name="Price[]" class="form-control form-control-sm price" value="0" min="0" step="1000" /></td>
									<td><input type="text" name="Total[]" class="form-control form-control-sm total" value="0" readonly class="bg-light text-right" /></td>
									<td><input type="text" name="Reference[]" class="form-control form-control-sm reference" value="" class="bg-light text-right" /></td>
									<td>
										<button type="button" class="btn btn-danger btn-sm remove-row" title="Remove">
											<i class="fa fa-times"></i>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
						
						<div class="float-right mt-2">
							<button type="button" id="addProductRow" class="btn btn-info btn-sm">
								<i class="fa fa-plus"></i> Add Product
							</button>
							<span class="text-muted ml-2">Total: <span id="totalProducts">1</span> products</span>
							<span class="text-muted ml-2">Estimate: <span id="totalEstimate">Rp 0</span></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Action Buttons -->
			<div id="divtom" class="d-flex justify-content-end mt-3">
				<button type="button" id="SubmitPRDetail" class="btn btn-success btn-sm">
					<i class="fa fa-check"></i> Submit PR
				</button>
			</div>

			<div id="WinCOA"></div>
        </div>
    </div>
</div>

<!-- Simple product search modal -->
<div class="modal fade" id="productSearchModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Search Product</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-3">
						<select id="modalCategory" class="form-control">
							<option value="">All Category</option>
							<option value="ASSET">ASSET</option>
							<option value="NON ASSET">NON ASSET</option>
						</select>
					</div>
					<div class="col-md-3">
						<input type="text" id="modalSearch" class="form-control" placeholder="Search..." />
					</div>
					<div class="col-md-6 text-right">
						<button type="button" id="modalSearchBtn" class="btn btn-info">Search</button>
					</div>
				</div>
				<div class="table-responsive mt-3">
					<table class="table table-sm table-bordered" id="modalProductTable">
						<thead>
							<tr>
								<th>Code</th>
								<th>Description</th>
								<th>Unit</th>
								<th>Stock</th>
								<th>Price</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody id="modalProductBody">
							<!-- Will be filled via AJAX -->
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		$('#Supplier_ID').select2();
		$('#Department').select2();
		
		var productCount = 1;

		// Auto calculate total
		function calculateTotal(row) {
			var qty = parseFloat(row.find('.qty').val()) || 0;
			var price = parseFloat(row.find('.price').val()) || 0;
			row.find('.total').val(qty * price);
			updateTotalEstimate();
		}

		$(document).on('input change', '#productTableBody .qty, #productTableBody .price', function() {
			calculateTotal($(this).closest('.product-row'));
		});

		// Update total estimate
		function updateTotalEstimate() {
			var total = 0;
			$('#productTableBody .total').each(function() {
				total += parseFloat($(this).val()) || 0;
			});
			$('#totalEstimate').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
		}

		// Add product row
		$('#addProductRow').click(function() {
			productCount++;
			var newRow = `
				<tr class="product-row">
					<td>${productCount}</td>
					<!-- td><input type="text" name="Product_Code[]" class="form-control form-control-sm product-code" placeholder="Kode" /></td -->
					<td><input type="text" name="Product_Desc[]" class="form-control form-control-sm product-desc" placeholder="Deskripsi" /></td>
					<td><input type="number" name="Qty[]" class="form-control form-control-sm qty" value="1" min="1" /></td>
					<td><input type="text" name="Specification[]" class="form-control form-control-sm specification" placeholder="Spesifikasi" /></td>
					<td><input type="text" name="Unit[]" class="form-control form-control-sm unit" value="PCS" /></td>
					<td><input type="number" name="Price[]" class="form-control form-control-sm price" value="0" min="0" step="1000" /></td>
					<td><input type="text" name="Total[]" class="form-control form-control-sm total" value="0" readonly class="bg-light text-right" /></td>
					<td>
					<td><input type="text" name="Reference[]" class="form-control form-control-sm reference" value="" class="bg-light text-right" /></td>
					<td>
						<button type="button" class="btn btn-danger btn-sm remove-row" title="Remove">
							<i class="fa fa-times"></i>
						</button>
					</td>
				</tr>
			`;
			$('#productTableBody').append(newRow);
			calculateTotal($('#productTableBody tr:last'));
		});

		// Remove product row
		$(document).off('click', '.remove-row').on('click', '.remove-row', function() {
			var row = $(this).closest('.product-row');
			if ($('#productTableBody tr.product-row').length > 1) {
				row.remove();
				renumberRows();
			} else {
				alert('Minimal harus ada 1 produk!');
			}
		});

		// Renumber rows
		function renumberRows() {
			var i = 1;
			$('#productTableBody tr.product-row').each(function() {
				$(this).find('td:first').text(i);
				i++;
			});
			productCount = i;
			$('#totalProducts').text(productCount);
		}

		// Search product click
		$(document).off('click', '.search-product').on('click', '.search-product', function() {
			var row = $(this).closest('.product-row');
			var currentCode = row.find('.product-code').val();
			
			if (currentCode) {
				openProductModal(row.index());
			} else {
				alert('Masukkan Product Code terlebih dahulu, atau klik Search untuk mencari!');
			}
		});

		// Open product modal
		$('#modalSearchBtn').click(function() {
			var category = $('#modalCategory').val();
			var search = $('#modalSearch').val();
			loadModalProducts(category, search);
		});

		$('#modalCategory, #modalSearch').on('change input', function() {
			// Debounce
			clearTimeout(window.searchTimeout);
			window.searchTimeout = setTimeout(function() {
				var category = $('#modalCategory').val();
				var search = $('#modalSearch').val();
				loadModalProducts(category, search);
			}, 500);
		});

		function loadModalProducts(category, search) {
			$('#modalProductBody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
			
			$.ajax({
				url: 'xml-hrd-procurement-product',
				type: 'GET',
				data: { Category: category || '', q: search || '' },
				success: function(xml) {
					var html = '';
					var found = false;
					$(xml).find('rows row').each(function() {
						var code = $(this).find('cell:nth-child(2)').text();
						var desc = $(this).find('cell:nth-child(3)').text();
						var unit = $(this).find('cell:nth-child(6)').text();
						var stock = $(this).find('cell:nth-child(5)').text();
						var price = $(this).find('cell:nth-child(8)').text();
						
						if (!search || code.toLowerCase().indexOf(search.toLowerCase()) >= 0 || desc.toLowerCase().indexOf(search.toLowerCase()) >= 0) {
							found = true;
							html += `
								<tr>
									<td><input type="text" value="${code}" class="form-control form-control-sm w-25 modal-code" /></td>
									<td><input type="text" value="${desc}" class="form-control form-control-sm modal-desc" /></td>
									<td><input type="text" value="${unit}" class="form-control form-control-sm modal-unit" /></td>
									<td>${stock}</td>
									<td>
										<input type="number" value="${price}" class="form-control form-control-sm modal-price" min="0" step="1000" />
									</td>
									<td>
										<button type="button" class="btn btn-success btn-sm select-product" 
											data-code="${code}" data-desc="${desc}" data-unit="${unit}">
											<i class="fa fa-check"></i> Select
										</button>
								</td>
							</tr>
							`;
						}
					});
					
					if (!found) {
						html = '<tr><td colspan="6" class="text-center">No products found</td></tr>';
					}
					$('#modalProductBody').html(html);
				},
				error: function() {
					$('#modalProductBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading products</td></tr>');
				}
			});
		}

		// Select product from modal
		$(document).off('click', '.select-product').on('click', '.select-product', function() {
			var code = $(this).data('code');
			var desc = $(this).data('desc');
			var unit = $(this).data('unit');
			var rowIndex = $('#selectedRowIndex').val();
			
			var row = $('#productTableBody tr.product-row').eq(rowIndex);
			row.find('.product-code').val(code);
			row.find('.product-desc').val(desc);
			row.find('.unit').val(unit);
			
			$('#productSearchModal').modal('hide');
		});

		// Submit PR
		$('#SubmitPRDetail').click(function() {
			var PIC = $('#PIC').val();
			var PIC_Address = $('#PIC_Address').val();
			var PIC_Phone = $('#PIC_Phone').val();
			var Estimated_Budget = $('#Estimated_Budget').val();
			
			if (PIC == '') {
				alert('PIC harus diisi!');
				$("#PIC").focus();
				return;
			}
			if (PIC_Address == '') {
				alert('PIC Address harus diisi!');
				$("#PIC_Address").focus();
				return;
			}
			if (PIC_Address.length < 3) {
				alert('PIC Address minimal 3 karakter!');
				$("#PIC_Address").focus();
				return;
			}
			if (PIC_Phone == '') {
				alert('PIC Phone harus diisi!');
				$("#PIC_Phone").focus();
				return;
			}
			if (Estimated_Budget == '' || Estimated_Budget == 0) {
				alert('Estimated Budget harus diisi!');
				$("#Estimated_Budget").focus();
				return;
			}
			if ($('#productTableBody tr.product-row').length == 0) {
				alert('Minimal harus ada 1 produk!');
				return;
			}

			$.blockUI();

			dapat = getAllValues();
			
			// Collect product data
			var gridData = [];
			$('#productTableBody tr.product-row').each(function() {
				gridData.push({
					Product_Code: $(this).find('.product-code').val(),
					Product_Desc: $(this).find('.product-desc').val(),
					Qty: $(this).find('.qty').val(),
					Specification: $(this).find('.specification').val(),
					Unit: $(this).find('.unit').val(),
					Price: $(this).find('.price').val(),
					Total: $(this).find('.total').val(),
					Reference: $(this).find('.reference').val(),
					Category: $(this).find('.category').val()
				});
			});
			
			dapat['GridData'] = JSON.stringify(gridData);
			dapat['RowCount'] = gridData.length;
			
			$.ajax({
				headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
				url: "hrd-procurement-new-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: dapat,
				success: function(data) {
					if (data == 'success') {
						alert('PR Submitted! (' + gridData.length + ' products)');
						setTimeout(function() {
							$.unblockUI();
							refreshData();
							$('#PurchaseManagementDiv').html('');
						}, 1000);
					} else {
						alert('Error: ' + data);
						$.unblockUI();
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert('Error: ' + errorThrown);
					$.unblockUI();
				}
			});
		});
	});

	function getAllValues() {
		var inputValues = {};
		$('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
			var type = $(this).attr('type');
			var id = $(this).attr('id');
			var value = $(this).val();
			if ((type == 'checkbox' || type == 'radio')) {
				if (this.checked) {
					inputValues[id] = value;
				}
			} else if (type != 'button' && type != 'submit') {
				inputValues[id] = value;
			}
		});
		return inputValues;
	}

	function selesai() {
		$.unblockUI();
		return true;
	}
	$.unblockUI();
</script>

<input type="hidden" id="selectedRowIndex" value="0" />

<div id="PRSupplierDet"></div>