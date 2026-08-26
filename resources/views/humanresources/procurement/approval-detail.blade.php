<div class="mt-3">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white py-3 px-2">
            <h5 class="card-title mb-0 font-weight-bold d-inline-block" style="color: azure">Approval Request</h5>
			<button type="button" class="float-right btn btn-secondary" onclick="$('#PurchaseManagementDiv').html('')"><i class="fa fa-times"></i> Close</button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
				<!-- Row 1: Informasi Dokumen & Vendor -->
				<div class="row">
					<div class="col-md-3 form-group">
						<label for="PR_No" class="font-weight-bold">PR No</label>
						<input type="text" id="PR_No" class="form-control form-control-sm shadow-sm bg-light" readonly value="<?php echo $pr->PR_No; ?>" />
					</div>
					<div class="col-md-3 form-group">
						<label for="PR_Date" class="font-weight-bold">Date Request</label>
						<input type="text" id="PR_Date" class="form-control form-control-sm shadow-sm bg-light" readonly value="<?php echo date('Y-m-d', strtotime($pr->PR_Date)); ?>" />
					</div>
					<div class="col-md-6 form-group">
						<label for="User_Request" class="font-weight-bold">User Request</label>
						<div class="input-group input-group-sm">
							<input type="text" class="form-control form-control-sm shadow-sm col-md-4 bg-light" id="User_Request" value="<?php echo $pr->User_Request; ?>" readonly />
							<input type="text" class="form-control form-control-sm shadow-sm col-md-8 bg-light" id="User_Request_Name" value="<?php echo $pr->User_Request_Name; ?>" readonly />
						</div>
					</div>
				</div>

				<!-- Row 2: User Request & Budgeting -->
				<div class="row">
					<div class="col-md-6 form-group">
						<label for="Department" class="font-weight-bold">Dept. (Budgeting)</label>
						<select id="Department" class="form-control form-control-sm shadow-sm">
							<?php
							foreach ($Department as $Data) {
								echo "<option value='$Data->Initial' ". ($pr->Department == $Data->Initial ? 'selected' : '') .">$Data->Department - $Data->Initial_Code - $Data->Initial</option>";
							}
							?>
						</select>
					</div>
					<div class="col-md-6 form-group">
						<label for="Supplier_ID" class="font-weight-bold">Vendor</label>
						<div class="input-group input-group-sm">
							<select name="Supplier_ID" id="Supplier_ID" class="form-control form-control-sm shadow-sm">
								<option value="">-- Pilih Vendor --</option>
								@foreach ($company as $item)
									<option value="{{$item->Supplier_ID}}" {{ $pr->Supplier_ID == $item->Supplier_ID ? 'selected' : '' }}>{{ $item->Supplier_ID }} - {{ $item->Company_Name }}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>

				<!-- Row 3: Detail PIC -->
				<div class="row">
					<div class="col-md-6 form-group">
						<label for="PIC" class="font-weight-bold">PIC to Receive <span class="text-muted font-weight-normal">(Penerima Barang)</span></label>
						<input type="text" id="PIC" class="form-control form-control-sm shadow-sm" required placeholder="Masukkan nama penerima" value="{{ $pr->PIC }}" />
					</div>
					<div class="col-md-6 form-group">
						<label for="PIC_Phone" class="font-weight-bold">PIC Phone</label>
						<input type="text" id="PIC_Phone" class="form-control form-control-sm shadow-sm" required placeholder="Nomor Telepon" value="{{ $pr->PIC_Phone }}" />
					</div>
					<div class="col-md-12 form-group">
						<label for="PIC_Address" class="font-weight-bold">PIC Address</label>
						<textarea id="PIC_Address" class="form-control form-control-sm shadow-sm" rows="2" required placeholder="Alamat lengkap penerima">{{ $pr->PIC_Address }}</textarea>
					</div>
				</div>

				<!-- Row 4: Kategori & Parameter Request -->
				<div class="row">
					<div class="col-md-4 form-group">
						<label for="Category" class="font-weight-bold">Category</label>
						<select id="Category" class="form-control form-control-sm shadow-sm">
							<option value="">Select Category</option>
							<option {{ $pr->Category == 'ASSET' ? 'selected' : '' }}>ASSET</option>
							<option {{ $pr->Category == 'NON ASSET' ? 'selected' : '' }}>NON ASSET</option>
						</select>
					</div>
					<div class="col-md-4 form-group">
						<label for="Priority_Status" class="font-weight-bold">Priority</label>
						<select name="Priority_Status" id="Priority_Status" class="form-control form-control-sm shadow-sm" required>
							<option {{ $pr->Priority_Status == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
							<option {{ $pr->Priority_Status == 'URGENT' ? 'selected' : '' }}>URGENT</option>
						</select>
					</div>
					<div class="col-md-4 form-group">
						<label for="Estimated_Budget" class="font-weight-bold">Estimated Budget</label>
						<input type="text" id="Estimated_Budget" class="form-control form-control-sm shadow-sm" value="{{ $pr->Estimated_Budget }}" required />
					</div>
				</div>

				<!-- Row 5: Specification -->
				<div class="row">
					<div class="col-md-12 form-group">
						<div class="row">
							<div class="col-md-3 col-12">
								<label for="Product_Desc" class="font-weight-bold">Product Name (ex: TV/Handphone/ATK)</label>
								<input type="text" name="Product_Desc" id="Product_Desc" class="form-control form-control-sm shadow-sm" value="{{ $pr->details[0]->Product_Desc }}" />
							</div>
							<div class="col-md-4 col-12">
								<label for="Specification" class="font-weight-bold">Product Specification</label>
								<textarea id="Specification" class="form-control form-control-sm shadow-sm" rows="3" required placeholder="Catatan spesifikasi barang...">{{ $pr->details[0]->Specification }}</textarea>
							</div>
							<div class="col-md-1 col-12">
								<label for="Qty" class="font-weight-bold">Quanity</label>
								<input type="number" name="Qty" id="Qty" class="form-control form-control-sm shadow-sm" value="{{ $pr->details[0]->Qty }}" />
							</div>
							<div class="col-md-1 col-12">
								<label for="Unit" class="font-weight-bold">Unit</label>
								<input type="text" name="Unit" id="Unit" class="form-control form-control-sm shadow-sm" value="{{ $pr->details[0]->Unit }}" />
							</div>
							<div class="col-md-3 form-group">
								<label for="Reference" class="font-weight-bold">References (Link)</label>
								<input type="text" id="Reference" class="form-control form-control-sm shadow-sm"  value="{{ $pr->details[0]->Reference }}"/>
							</div>
						</div>
					</div>
				</div>

				<!-- Grid DHTMLX Container -->
				<div class="form-group d-none">
					<label class="font-weight-bold">Item Details</label>
					<div id="gridPRDetail" class="border" style="width: 100%; height: 250px; background-color: white; overflow: hidden;"></div>
				</div>

				<!-- Action Buttons -->
				<div id="divtom" class="d-flex justify-content-end mt-3">
					<button type="button" id="AddPRDetail" class="btn btn-info btn-sm mr-2 d-none">
						<i class="fa fa-plus"></i> Add New
					</button>
					<button type="button" id="DelPRDetail" class="btn btn-danger btn-sm mr-2 d-none">
						<i class="fa fa-trash"></i> Delete
					</button>
					<button type="button" id="SubmitPRDetail" class="btn btn-success btn-sm">
						<i class="fa fa-check"></i> Update
					</button>
				</div>

				<div id="WinCOA"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
	<script>
		// $("#Supplier_ID").coolautosuggest({

		// 	url: "search-supplier?Procode=1&chars=",
		// 	showThumbnail: false,
		// 	showDescription: true,
		// 	idField: $("#Supplier_ID"),
		// 	width: 700,
		// 	minChars: 1,
		// 	onSelected: function(result) {
		// 		if (result != null) {
		// 			$("#Supplier_ID").val(result.id);
		// 			$("#Supplier_Name").val(result.data);


		// 		}
		// 	}
		// });

		$('#Supplier_ID').select2();
		$('#Department').select2();
		var WinPR;
		var WPR;

		function loadPR_Product(text, url, width, height) {
			WinPR = new dhtmlXWindows();
			WinPR.attachViewportTo("ContentPages");
			WPR = WinPR.createWindow("WPR", 0, 0, width, height);
			WPR.keepInViewport(false);
			WPR.setText(text);
			WPR.attachURL(url, true);
			WPR.button("minmax1").hide();
			WPR.button("park").hide();
			WPR.centerOnScreen();
			WPR.allowMove();
		}


		function getAllValues() {
			var inputValues = {};
			$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
				var type = $(this).attr("type");
				var id = $(this).attr("id");
				var value = $(this).val();
				if ((type == "checkbox" || type == "radio")) {
					if (this.checked) {
						inputValues[id] = value;
					}
				} else if (type != "button" || type != "submit") {
					inputValues[id] = value;
				}
			})

			return inputValues;
		}

		var SHPREVDAY_Cal;
		SHPREVDAY_Cal = new dhtmlXCalendarObject(["PR_Date"]);
		SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");


		PRDetail = new dhtmlXGridObject('gridPRDetail');
		PRDetail.setImagePath("dhtmlx/imgs/");
		PRDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Price@unit, Total Price");
		PRDetail.setInitWidths("50,100,100,100,200,200,100,200,100,100,100,100");
		PRDetail.setColTypes("ro,ro,ro,ro,edn,ed,ed,ed,ro,ro,edn,ron");
		PRDetail.setColAlign("center,left,center,left,right,left,center,center,center,center,right,right");
		PRDetail.setColSorting("int,str,str,str,str,str,str,str");
		PRDetail.setEditable(true);
		PRDetail.setNumberFormat("0,000.00", 4, ".", ",");
		PRDetail.setNumberFormat("0,000.00", 10, ".", ",");
		PRDetail.setNumberFormat("0,000.00", 11, ".", ",");
		PRDetail.enableKeyboardSupport(true);
		PRDetail.setSkin("dhx_skyblue");
		PRDetail.attachEvent("onRowDblClicked", loadPrProduct);
		PRDetail.enableMultiline(true);
		PPRDetail = new dataProcessor("hrd-procurement-new-update");
		PPRDetail.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"PR_No": "<?php echo $pr->PR_No;  ?>"
			}


		})
		PPRDetail.setUpdateMode("Off");
		PPRDetail.init(PRDetail);
		PRDetail.init();
		PRDetail.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
			if(stage == 2){

				if(cInd == 4 || cInd == 10){
					let qty = Number(PRDetail.cells(rId,4).getValue());
					let price = Number(PRDetail.cells(rId,10).getValue());
					if (qty == '' || price == '') {
						PRDetail.cells(rId, 11).setValue('');
						return true;
					}

					PRDetail.cells(rId, 11).setValue(qty * price);
				}
			}

			return true;
		});

		$("#AddPRDetail").click(function() {
            let ids = PRDetail.uid();
            PRDetail.addRow(ids, [PRDetail.getRowsNum() + 1, '','','','','','','','','','']);
            PRDetail.selectRowById(ids);
            loadPR_Product('Product Lists', 'hrd-procurement-product', 1024, 450);

		})

        function loadPrProduct(rId, cInd, event) {
            if(cInd == 2 || cInd == 3)
                loadPR_Product('Product Lists', 'hrd-procurement-product', 1024, 450);
            return true;
        }
		$("#DelPRDetail").click(function() {

			PRDetail.deleteSelectedRows();

		})


		$("#SubmitPRDetail").click(function() {
			Reference = $("#Reference").val();
			Remark = $("#Remark").val();
			Department = $("#Department").val();
			User_Request = $("#User_Request").val();
			Date_Request = $("#Date_Request").val();
			PR_No = $("#PR_No").val();
			Category = $("#Category").val();
			Sub_Category = $("#Sub_Category").val();
			Sub_Category2 = $("#Purchase_Type").val();
			PIC = $("#PIC").val();
			PIC_Phone = $("#PIC_Phone").val();
			PIC_Address = $("#PIC_Address").val();
			Estimated_Budget = $("#Estimated_Budget").val();
			
			if (PIC == '') {
				alert("Kolom Penerima Barang (PIC to Receive) Harus Diisi!");
				$("#PIC").focus();
				return false;
			} else if (PIC_Address == '') {
				alert("Kolom alamat PIC (PIC Address) Harus Diisi!");
				$("#PIC_Address").focus();
				return false;
			} else if (PIC_Address.length < 3) {
				alert("Kolom alamat PIC (PIC Address) Minimal 3 Karakter!");
				$("#PIC_Address").focus();
				return false;
			} else if (PIC_Phone == '') {
				alert("Kolom nomor telepon (PIC Phone) Harus Diisi!");
				$("#PIC_Phone").focus();
				return false;
			} else if (Estimated_Budget == '') {
				alert("Kolom Estimated_Budget Harus Diisi!");
				$("#Estimated_Budget").focus();
				return false;
			} else {
				// PPRDetail.sendData();
				$.blockUI();

				dapat = getAllValues();
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': "{{ csrf_token() }}"
					},

					url: "hrd-procurement-approval-detail-submit?_token={{ csrf_token() }}",
					type: "POST",
					data: dapat,
					success: function(data, textStatus, jqXHR) {
						if (data == 'success') {
							alert('Purchase Request Updated with no  <?php echo $pr->PR_No ?>!');

							setTimeout(function() {
								$.unblockUI();
								refreshData();
								$('#PurchaseManagementDiv').html("");
							}, 1000);

						} else {

						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						alert("error");
						$.unblockUI();
					}
				});


			}
		});

		function selesai() {
			$.unblockUI();
			return true;
		}
		$.unblockUI();
	</script>


<div id="PRSupplierDet"></div>