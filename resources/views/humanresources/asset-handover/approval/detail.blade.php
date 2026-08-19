<div class="mt-3">
    <div class="card">
        <div class="card-header my-2 ml-2">
            <h6 class="d-inline-block">Detail Form Handover</h6>
			<span class="float-right">
				<input type="button" id="CloseAssetHandoverDetail" class="btn btn-secondary mr-2 py-2 px-3 my-2" NAME="CloseAssetHandoverDetail" value="Close" onclick="$('#AssetHandoverDiv').html('')" />
			</span>
        </div>
        <div class="card-body">
            <!-- Stats Card -->
            <div class="row">
                <div id="ContentPages">
                    <div class="FormInput_Form_Content">
                        <div class="FormInput_left">
            
                            Handover No
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="hidden" id="ID" size="30" readonly="readonly" value="{{ $ah->ID }}" />
                            <input type="text" id="Handover_No" size="30" readonly="readonly" value="<?php echo $ah->Handover_No; ?>" />
            
                        </div>
                        <div class="FormInput_left">
            
                            Handover Date
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="Handover_Date" readonly="readonly" size="15" value="<?php echo date('Y-m-d', strtotime($ah->Handover_Date)); ?>" />
            
                        </div>
                        <div class="FormInput_left">
            
                            User Create
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="User_NIK" size="15" value="<?php echo $ah->User_NIK ?>" readonly="readonly" /> &nbsp;&nbsp;
                            <input type="text" id="User_Name" size="40" value="<?php echo $ah->User_Name ?>" readonly="readonly" />
            
                        </div>
            
                        <!---->
            
                        <div class="FormInput_left">
            
                            PIC to Receive
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="PIC_Name" size="40" value="{{ $ah->PIC_Name }}" required/> <input type="hidden" id="PIC_NIK" value="{{ $ah->PIC_NIK }}"/><i>(Penerima Barang)</i>
                            
            
                        </div>
            
            
                        <div class="FormInput_left" style="height:60px;">
            
                            PIC Department
            
                        </div>
                        <div class="FormInput_right" style="height:60px;">
            
							<input type="text" id="PIC_Department" required size="25" value="{{ $ah->PIC_Department }}" />
            
            
                        </div>
                        <div class="FormInput_left">
            
                            PIC Phone
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="PIC_Phone" size="30" required value="{{ $ah->PIC_Phone }}" />
            
            
                        </div>
                        <div class="FormInput_left">
            
                            References/FPB (Manual)
            
                        </div>
                        <div class="FormInput_right">
            
                            <input type="text" id="Reference" size="40" value="{{ $ah->Reference }}" />
            
                        </div>
                        <div class="FormInput_left">
            
                            Remark
            
                        </div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Remark" cols="100" rows="2" required>{{ $ah->Remark }}</textarea>
            
                        </div>
                        <div id="gridAssetHandoverDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
                        <div class="FormInput_left">
            
                            Approval Note
            
                        </div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Approval_Note" cols="100" rows="2" required></textarea>
            
                        </div>
                        <div class="FormInput_left">

                        </div>
                        <div class="FormInput_right" style="height: 60px;">
                            <input type="button" id="RejectAssetHandoverDetail" class="btn btn-danger mt-2 mr-2" NAME="RejectAssetHandoverDetail" onclick="approval(2)" value="Reject" />
                            <input type="button" id="SubmitAssetHandoverDetail" class="btn btn-success mt-2 mr-2" NAME="SubmitAssetHandoverDetail" onclick="approval(1)" value="Approve" />
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
		$("#PIC_Name").coolautosuggest({
			url: "finance-ap-search-employee?chars=",
			showThumbnail: false,
			showDescription: true,
			idField: $("#PIC_Name"),
			width: 500,
			minChars: 1,
			onSelected: function(result) {
				if (result != null) {
					$("#PIC_Name").val(result.data);
					$("#PIC_NIK").val(result.id);
					$("#PIC_Department").val(result.thumbnail);
					$("#PIC_Phone").val(result.phone);

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


		AssetHandoverDetail = new dhtmlXGridObject('gridAssetHandoverDetail');
		AssetHandoverDetail.setImagePath("dhtmlx/imgs/");
		AssetHandoverDetail.setHeader("Checkbox,Purchase Type,Product Code,Product Desc,Qty,Product Brand, Product Type,Specification,Condition,Measurement,Note,Category,SubCat,Price@unit, Total Price,Received Date,Status");
		AssetHandoverDetail.setInitWidths("50,100,100,100,200,200,200,200,200,100,200,100,100,0,0,100,0");
		AssetHandoverDetail.setColTypes("ch,ro,ro,ro,edn,ed,ed,ed,ed,ed,ed,ro,ro,edn,ron,dhxCalendar,ro");
		AssetHandoverDetail.setColAlign("center,left,center,left,right,left,center,left,left,center,center,center,center,right,right,center,center");
		AssetHandoverDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
		AssetHandoverDetail.setEditable(true);
		AssetHandoverDetail.setNumberFormat("0,000.00", 4, ".", ",");
		AssetHandoverDetail.setNumberFormat("0,000.00", 13, ".", ",");
		AssetHandoverDetail.setNumberFormat("0,000.00", 14, ".", ",");
		AssetHandoverDetail.setDateFormat("%Y-%m-%d");
		AssetHandoverDetail.enableKeyboardSupport(true);
		AssetHandoverDetail.setColumnHidden(13, true)
		AssetHandoverDetail.setColumnHidden(14, true)
		AssetHandoverDetail.setColumnHidden(16, true)
		AssetHandoverDetail.setSkin("dhx_skyblue");
		AssetHandoverDetail.attachEvent("onRowDblClicked", loadPrProduct);
		AssetHandoverDetail.enableMultiline(true);
		PAssetHandoverDetail = new dataProcessor("hrd-asset-handover-confirmation-submit");
		PAssetHandoverDetail.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"Handover_No": "<?php echo $ah->Handover_No;  ?>"
			}


		})
		PAssetHandoverDetail.setUpdateMode("Off");
		PAssetHandoverDetail.init(AssetHandoverDetail);
		AssetHandoverDetail.init();
		AssetHandoverDetail.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
			if(stage == 2){

				if(cInd == 4 || cInd == 13){
					let qty = Number(AssetHandoverDetail.cells(rId,4).getValue());
					let price = Number(AssetHandoverDetail.cells(rId,13).getValue());
					if (qty == '' || price == '') {
						AssetHandoverDetail.cells(rId, 14).setValue('');
						return true;
					}

					AssetHandoverDetail.cells(rId, 14).setValue(qty * price);
				}
			}

			return true;
		});
		refreshDataDetail();
		function refreshDataDetail() {
			AssetHandoverDetail.clearAndLoad("hrd-asset-handover-confirmation-detail-data?Handover_No={{$ah->Handover_No}}");
		}
		$("#AddAssetHandoverDetail").click(function() {
            let ids = AssetHandoverDetail.uid();
            AssetHandoverDetail.addRow(ids, [AssetHandoverDetail.getRowsNum() + 1, '','','','','','','','','','']);
            AssetHandoverDetail.selectRowById(ids);
            loadPR_Product('Product Lists', 'hrd-asset-handover-product', 1024, 450);

		})

        function loadPrProduct(rId, cInd, event) {
            if(cInd == 2 || cInd == 3)
                loadPR_Product('Product Lists', 'hrd-asset-handover-product', 1024, 450);
            return true;
        }
		$("#DelAssetHandoverDetail").click(function() {

			AssetHandoverDetail.deleteSelectedRows();

		})


		function approval(stat) {
            if (stat == 1) {
                stat = "APPROVED";
            }else if(stat == 2) {
                stat = "REJECTED";
            }
            let broke = 0;
            let checkedCount = 0;
            let rowIds = AssetHandoverDetail.getAllRowIds();
            if (rowIds) {
                let ids = rowIds.split(',');
                for (let i = 0; i < ids.length; i++) {
                    const ele = ids[i];
                    const check = AssetHandoverDetail.cells(ele,0).getValue();
                    if (check == 1 || check == '1' || check == true) {
                        checkedCount++;
                        let date = AssetHandoverDetail.cells(ele, 15).getValue();
                        if (date == '') {
                            alert("Tanggal penerimaan wajib diisi pada item yang dipilih");
                            broke = 1;
                            break;
                        }
                        AssetHandoverDetail.cells(ele, 16).setValue(stat);
                        PAssetHandoverDetail.setUpdated(ele, true, "updated");
                    }
                }
            }

            if (broke) {
                return;
            }

            if (checkedCount === 0) {
                alert("Pilih minimal satu item detail (centang checkbox) yang ingin di-approve/reject");
                return;
            }

            PAssetHandoverDetail.sendData();
            $.blockUI();

            dapat = getAllValues();
            dapat += "&status=" + stat;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },

                url: "hrd-asset-handover-confirmation-submit?_token={{ csrf_token() }}",
                type: "POST",
                data: dapat,
                success: function(data, textStatus, jqXHR) {
                    if (data.status == 'success') {
                        alert('Asset Handover ' + stat + ' successfully for No: <?php echo $ah->Handover_No ?>!');

                        setTimeout(function() {
                            $.unblockUI();
                            if (typeof refreshData === 'function') {
                                refreshData();
                            }
                            $('#AssetHandoverDiv').html("");
                        }, 1000);

                    } else {
                        alert(data.message || 'Terjadi kesalahan saat memproses approval');
                        $.unblockUI();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error processing approval request");
                    $.unblockUI();
                }
            });
		};

		function selesai() {
			$.unblockUI();
			return true;
		}
	</script>


<div id="PRSupplierDet"></div>