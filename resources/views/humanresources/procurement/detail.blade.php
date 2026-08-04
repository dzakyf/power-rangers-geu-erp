<div class="mt-3">
    <div class="card">
        <div class="card-header my-2 ml-2">
            <h6>Form Procurement</h6>
        </div>
        <div class="card-body">
            <!-- Stats Card -->
            <div class="row">
                <div id="ContentPages">
                    <div class="FormInput_Form_Content">
                        <div class="FormInput_left">
            
                            PR No
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="PR_No" size="30" readonly="readonly" value="<?php echo $pr->PR_No; ?>" />
            
                        </div>
                        <div class="FormInput_left">
            
                            Date Request
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="PR_Date" readonly="readonly" size="15" value="<?php echo date('Y-m-d', strtotime($pr->PR_Date)); ?>" />
            
                        </div>
                        <div class="FormInput_left">
            
                            Vendor
            
                        </div>
                        <div class="FormInput_right">
            
                            <input class='form-control-sm' type="text" style="background-color:#fff" id="Supplier_ID" size="15" value="{{ $pr->Supplier_ID }}" /> &nbsp;&nbsp;
                            <input class='form-control-sm' type="text" style="background-color:#fff" id="Supplier_Name" size="40" readonly="readonly" value="{{ $pr->Supplier_Name }}" />
            
                        </div>
                        <div class="FormInput_left">
            
                            User Request
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="User_Request" size="15" value="<?php echo $pr->User_Request ?>" readonly="readonly" /> &nbsp;&nbsp;
                            <input type="text" id="User_Request_Name" size="40" value="<?php echo $pr->User_Request_Name ?>" readonly="readonly" />
            
                        </div>
            
                        <!---->
            
                        <div class="FormInput_left">
            
                            PIC to Receive
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="PIC" size="40" value="{{ $pr->PIC }}" required/> <i>(Penerima Barang)</i>
            
                        </div>
            
            
                        <div class="FormInput_left" style="height:60px;">
            
                            PIC Address
            
                        </div>
                        <div class="FormInput_right" style="height:60px;">
            
                            <textarea id="PIC_Address" rows="2" cols="55" required>{{ $pr->PIC_Address }}</textarea>
            
            
                        </div>
                        <div class="FormInput_left">
            
                            PIC Phone
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="PIC_Phone" size="30" value="{{ $pr->PIC_Phone }}" required/>
            
            
                        </div>
            
            
            
                        <div class="FormInput_left">
            
                            Dept. (Budgeting)
            
                        </div>
                        <div class="FormInput_right">
            
            
                            <select id="Department" class="" style="width:250px;">
                                <?php
                                foreach ($Department as $Data) {
                                    $selected = ($Data->Initial == $pr->Department) ? 'selected' : "";
                                    echo "<option value='$Data->Initial' $selected>$Data->Department - $Data->Initial_Code - $Data->Initial</option>";
                                }
                                ?>
                            </select>
            
                        </div>
            
            
                        <div class="FormInput_left">
            
                            Category
            
                        </div>
                        <div class="FormInput_right">
            
                            <select id="Category">
                                <option value="">Select</option>
                                <option {{ $pr->Category == 'ASSET' ? 'selected' : '' }}>ASSET</option>
                                <option {{ $pr->Category == 'NON ASSET' ? 'selected' : '' }}>NON ASSET</option>
                            </select>
            
                        </div>
                        <div class="FormInput_left">
            
                            References/FPB (Manual)
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="Reference" size="40" value="{{ $pr->Reference }}" />
            
                        </div>
                        <div class="FormInput_left">
            
                            Estimated Budget
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="Estimated_Budget" size="10" value="0" value="{{ $pr->Estimated_Budget }}" required/>
            
                        </div>
                        <div class="FormInput_left">
            
                            Note
            
                        </div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Remark" cols="100" rows="2" required>{{ $pr->Remark }}</textarea>
            
                        </div>
                        <div id="gridPRDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
                        <div id="divtom">
                            <input type="button" id="AddPRDetail" class="btn btn-info mt-2 mr-2" NAME="AddPRDetail" value="Add New" />
                            <input type="button" id="DelPRDetail" class="btn btn-danger mt-2 mr-2" NAME="DelPRDetail" value="Delete" />
                            <input type="submit" id="SubmitPRDetail" class="btn btn-success mt-2 mr-2" NAME="SubmitPRDetail" value="Submit" />
                        </div>
                        <div id="WinCOA"></div>
            
            
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
	<script>
		$("#Supplier_ID").coolautosuggest({

			url: "search-supplier?Procode=1&chars=",
			showThumbnail: false,
			showDescription: true,
			idField: $("#Supplier_ID"),
			width: 700,
			minChars: 1,
			onSelected: function(result) {
				if (result != null) {
					$("#Supplier_ID").val(result.id);
					$("#Supplier_Name").val(result.data);


				}
			}
		});
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
			var inputValues = [];
			$('#ContentPages input,select,textarea').each(function() {
				var type = $(this).attr("type");
				if ((type == "checkbox" || type == "radio") && this.checked) {
					inputValues.push($(this).attr("id") + "=" + $(this).val());
				} else if (type != "button" || type != "submit") {
					inputValues.push($(this).attr("id") + "=" + $(this).val());
				} else if ($this.is("textarea")) {
					inputValues.push($(this).attr("id") + "=" + $(this).val());
				}
			})

			return inputValues.join('&');
		}


		PRDetail = new dhtmlXGridObject('gridPRDetail');
		PRDetail.setImagePath("dhtmlx/imgs/");
		PRDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Prices");
		PRDetail.setInitWidths("50,100,100,100,200,200,100,200,100,100,100");
		PRDetail.setColTypes("ro,ro,ro,ro,edn,ed,ed,ed,ro,ro,edn");
		PRDetail.setColAlign("center,left,center,left,right,left,center,center,center,center");
		PRDetail.setColSorting("int,str,str,str,str,str,str,str");
		PRDetail.setEditable(true);
		PRDetail.setNumberFormat("0,000.00", 4, ".", ",");
		PRDetail.enableKeyboardSupport(true);
		PRDetail.setSkin("dhx_skyblue");
		PRDetail.attachEvent("onRowDblClicked", loadPrProduct);
		PRDetail.enableMultiline(true);
		PPRDetail = new dataProcessor("hrd-procurement-detail-update");
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
        PRDetail.clearAndLoad("xml-hrd-procurement-detail?PR_No={{$pr->PR_No}}");

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
			var count = PRDetail.getRowsNum();
			if (count == 0) {
				alert('Masukkan Item Request Terlebih Dahulu! ');
				return false;
			} else {
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
				} else if (Remark == '') {
					alert("Kolom catatan (Note) Harus Diisi!");
					$("#Remark").focus();
					return false;
				} else if (Remark.length < 5) {
					alert("Kolom catatan (Note) minimal 5 karakter!");
					$("#Remark").focus();
					return false;
				} else {
					PPRDetail.sendData();

					var sip = 1;
					for (var i = 0; i < PRDetail.getRowsNum(); i++) {
						PurchaseType = PRDetail.cells2(i, 1).getValue();
						if (PurchaseType == Sub_Category2) {
							sip *= 1;
						} else {
							sip *= 0;
						}
					}
					$.blockUI();

					dapat = getAllValues();
					$.ajax({
						headers: {
							'X-CSRF-TOKEN': "{{ csrf_token() }}"
						},

						url: "hrd-procurement-detail-submit?_token={{ csrf_token() }}",
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

						}
					});


				}
			}
		});

		function selesai() {
			$.unblockUI();
			return true;
		}
	</script>


<div id="PRSupplierDet"></div>