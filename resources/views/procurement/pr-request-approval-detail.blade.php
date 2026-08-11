<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->Emp_ID;

	$dpt =  Auth::user()->Department;
	$date = date('Y-m');
	$fetch = DB::table("purchase_request")->where("ID",$_GET['ID'])->first();
	$sobjf = DB::select(DB::raw("SHOW FIELDS FROM purchase_request"));

	echo "<script>";

		foreach($sobjf as $objf){
			if ($objf->Field != 'Note') {
				$idobj = $objf->Field;
				$isinya = $fetch->$idobj;
					
				echo "$('#$idobj').val('$isinya'); \n";
			}
		}
	
	echo "</script>";

?>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">DETAIL PURCHASE REQUEST</div>

		<div id="ContentPages">
			<div class="FormInput_Form_Content">
				<div class="FormInput_left">
						
					   PR No
				
				</div>
				<div class="FormInput_right">
						
						<input type="text" id="PR_No" size="50" readonly="readonly" disabled="disabled"/>        	
						:: <input type="text" readonly="readonly" id="ID" size="5"  disabled="disabled"/>           
						:: <input type="text" readonly="readonly" id="Status" size="12" disabled="disabled" />           

				 </div>
				<div class="FormInput_left">
						
					   Date Request
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PR_Date" readonly="readonly" size="15" value="<?php echo date('Y-m-d');?>"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
						User Request
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="User_Request" size="15" value="<?php echo $NIK ?>" readonly="readonly"/>  &nbsp;&nbsp;
						<input type="text" id="User_Request_Name" size="40" value="<?php echo $Saya?>" readonly="readonly"/>        	
				 
				 </div>
				 
				 <!---->
				 
				<div class="FormInput_left">
						
						PIC to Receive
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PIC" size="40" value="" /> <i>(Penerima Barang)</i>
			   
				 </div>
				
				 
				<div class="FormInput_left" style="height:60px;">
						
						PIC Address
				
				</div>
				<div class="FormInput_right" style="height:60px;">
				
						<textarea id="PIC_Address" rows="2" cols="55"></textarea>        	

				 
				 </div>
				<div class="FormInput_left">
						
						PIC Phone
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PIC_Phone" size="30"/>        	

				 
				 </div>
				
				 
				 
				<div class="FormInput_left">
						
						Dept. (Budgeting)
				
				</div>
				<div class="FormInput_right">
				
				
					<select id="Department" class="" style="width:250px;">
					 <?php
						$Query = DB::table("master_department")->get();
						foreach ($Query as $Data){
							echo "<option value='$Data->Initial'>$Data->Department - $Data->Directorat</option>";	
							
							}
					?>
					</select>
						
				 </div>
				 
				 
				<div class="FormInput_left">
						
						Category
				
				</div>
				<div class="FormInput_right">
				
						<select id="Category" onchange="loadSubCat(this.value);">
							<option>ASSET</option>
							<option>NON ASSET</option>
						</select>
				 
				 </div>
				<div class="FormInput_left">
						
						References/FPB (Manual)
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Reference" size="40"/>        	
				 
				 </div>
			<div class="FormInput_left" style="height:80px;">
						
						Specification
				
				</div>	
				<div class="FormInput_right" style="height:80px;">
						<textarea id="Remark" cols="100" rows="2"></textarea>

				 </div>
				
				
			<div id="gridPRDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<div id="divtom">
				<input type="button" id="AddPRDetail" NAME="AddPRDetail" class="btn btn-info mt-2 mr-2" value="Add New" />
				<input type="button" id="DelPRDetail" NAME="DelPRDetail"  class="btn btn-danger mt-2 mr-2" value="Delete" />
				              
			</div>
			<div class="FormInput_left" style="height:80px;">
						
						Approval Note
				
				</div>	
				<div class="FormInput_right" style="height:80px;">
						<textarea id="Note" cols="100" rows="2"></textarea>

				 </div>
			<div id="divtom2">
				<input type="button" id="Rejects" NAME="Rejects" class="btn btn-danger mt-2 mr-2" value="REJECT" />                
				<input type="button" id="SubmitPRDetail" NAME="SubmitPRDetail" class="btn btn-success mt-2 mr-2" value="Approve &amp; Submit" />                
			</div>
			<div id="WinCOA"></div>
			
			
			</div>
		</div>


<script>
var WinPR;
var WPR;
function loadPR_Product(text,url,width,height) {
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
        if ((type == "checkbox" || type == "radio")) {
			if (this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
        }
        else if (type != "button" || type != "submit") {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
      	 } else if ($this.is("textarea")) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }
    })
	
    return inputValues.join('&');
}



		PRDetail = new dhtmlXGridObject('gridPRDetail');
		PRDetail.setImagePath("dhtmlx/imgs/");
		PRDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Prices,Total");
		PRDetail.setInitWidths("50,100,100,100,200,200,100,200,100,100,100,100");
		PRDetail.setColTypes("ro,ro,ro,ro,edn,ed,ed,ed,ro,ro,edn,ron");
		PRDetail.setColAlign("center,left,center,left,right,left,center,center,center,center,right,right");
		PRDetail.setColSorting("int,str,str,str,str,str,str,str,str,str");
		PRDetail.setEditable(true);
		PRDetail.setNumberFormat("0,000.00", 4, ".", ",");
		PRDetail.setNumberFormat("0,000.00", 10, ".", ",");
		PRDetail.setNumberFormat("0,000.00", 11, ".", ",");
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
				"PR_No": "<?php echo $fetch->PR_No;  ?>"
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
        PRDetail.clearAndLoad("xml-hrd-procurement-detail?PR_No={{$fetch->PR_No}}");

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
var count=PRDetail.getRowsNum();
if(count == 0){
	alert('Masukkan Item Request Terlebih Dahulu! ');
	return false;
}else{
	Reference = $("#Reference").val();
	Note = $("#Note").val();
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
	if(PIC == ''){
		alert("Kolom Penerima Barang (PIC to Receive) Harus Diisi!");$("#PIC").focus();return false;
	}else if (PIC_Address == '') {
		alert("Kolom alamat PIC (PIC Address) Harus Diisi!");$("#PIC_Address").focus();return false;
	}else if (PIC_Address.length < 3) {
		alert("Kolom alamat PIC (PIC Address) Minimal 3 Karakter!");$("#PIC_Address").focus();return false;
	}else if (PIC_Phone == '') {
		alert("Kolom nomor telepon (PIC Phone) Harus Diisi!");$("#PIC_Phone").focus();return false;
	}else if (Note == '') {
		alert("Kolom catatan (Note) Harus Diisi!");$("#Note").focus();return false;
	}else if (Note.length < 5) {
		alert("Kolom catatan (Note) minimal 5 karakter!");$("#Note").focus();return false;
	}else{
		PPRDetail.sendData();

		var sip = 1;
		for (var i = 0; i < PRDetail.getRowsNum(); i++) {
			PurchaseType = PRDetail.cells2(i,1).getValue();
			if(PurchaseType==Sub_Category2){
				sip *= 1;
			}else{
				sip *= 0;
			}
		}
		$.blockUI(); 
	
		dapat = getAllValues();
		$.ajax({
			 headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
			 },
		
			url : "purchasing-request-approval-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data : dapat,
			success: function(data, textStatus, jqXHR)
			{
				if (data == 'success'){
					alert('Purchase Request Approved!');
					$.unblockUI(); 
					setTimeout(function(){
							
						
					
					location.href='purchasing-request-approval';
					},1000);				

				}else{
					
				}
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
			 alert( "error" );
		
			}
		});			
			
				
	}
}
});

$("#Rejects").click(function() {
	dhtmlx.confirm('Reject This Task?', 'Purchase Request', function(r) {
					
			if (r==true){
				
				$.blockUI(); 
			
				dapat = getAllValues();
				$.ajax({
					 headers: {
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
					 },
				
					url : "purchasing-request-approval-detail-reject?_token={{ csrf_token() }}",
					type: "POST",
					data : dapat,
					success: function(data, textStatus, jqXHR)
					{
						if (data == 'success'){
							alert('Purchase Request REJECTED!');
							$.unblockUI(); 
							setTimeout(function(){
									
								
							
							location.href='purchasing-request-approval';
							},1000);				

						}else{
							
						}
					},
					error: function (jqXHR, textStatus, errorThrown)
					{
					 alert( "error" );
				
					}
				});			
			
			}
		});	

});
function selesai(){
	$.unblockUI();
	return true;
}

function loadSubCat(valCat){
		LoadM('SubCat','pr-load-subcat?Category=' + valCat);
}
function gantiSub(valCat,vals){
		LoadM('SubCat2','pr-load-sub?Main_Category=' + valCat +'&subval=' + vals);
}

</script>
