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
	$fetch = DB::table("purchase_request_cns")->where("ID",$_GET['ID'])->first();
	$fetch->Supplier_Name = $fetch->Supplier != '' ? DB::table('supplier')->where("Supplier_ID", $fetch->Supplier)->value('Supplier_Name') : '';
	$sobjf = DB::select(DB::raw("SHOW FIELDS FROM purchase_request_cns"));

	echo "<script>";

		foreach($sobjf as $objf){
		$idobj = $objf->Field;
		$isinya = $fetch->$idobj;
			
				echo "$('#$idobj').val(`$isinya`); \n";
		}
	
	echo "</script>";

?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">DETAIL PURCHASE REQUEST</div>
	<div class="card">
		<div class="card-body">
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
	
					Vendor
	
				</div>
				<div class="FormInput_right">
	
					<input type="text" id="Supplier" size="10" value="" required/>
					<input type="text" id="Supplier_Name" size="40" value="" required readonly/>
	
				</div>
	
				<div class="FormInput_left">
	
					Delivery Date
	
				</div>
				<div class="FormInput_right">
	
					<input type="date" id="Delivery_Date" size="30"  value="{{ date("Y-m-d") }}" required/>
	
				</div>
	
				<div class="FormInput_left" style="height:60px;">
	
					Delivery Site
	
				</div>
				<div class="FormInput_right" style="height:60px;">
	
					<textarea id="Delivery_Site" rows="2" cols="55" required></textarea>
	
	
				</div>
	
				<div class="FormInput_left">
	
					Delivery Term
	
				</div>
				<div class="FormInput_right">
	
					<input type="text" id="Delivery_Term" size="50" required/>
	
				</div>
	
				<div class="FormInput_left">
	
					Shipped By
	
				</div>
				<div class="FormInput_right">
	
					<input type="text" id="Shipped_By" size="30"/>
	
				</div>
	
				<div class="FormInput_left">
	
					Payment Term
	
				</div>
				<div class="FormInput_right">
	
					<input type="text" id="Payment_Term" size="80" value="Payment 25% before delivery and 75% 30 days after delivery (valid on working days)"/>
	
				</div>
	
				<div class="FormInput_left">
	
					Product Specification
	
				</div>
				<div class="FormInput_right">
	
					<input type="text" id="Supplies_Type" size="30" value="Flexibag 24KL TLBD"/>
	
				</div>
						
						
					<div class="FormInput_left">
							
							Dept. (Budgeting)
					
					</div>
					<div class="FormInput_right">
					
					
						<select id="Department" class="" style="width:250px;">
							<?php
							$Query = DB::table("master_department")->get();
							foreach ($Query as $Data){
								echo "<option value='$Data->Department'>$Data->Department - $Data->Directorat</option>";	
								
								}
						?>
						</select>
							
						</div>
					<div class="FormInput_left">
							
							Product Specification
					
					</div>
					<div class="FormInput_right">
						<div id="SubCat2">
							<input type="text" readonly="readonly" id="Supplies_Type" value="" size="30" />           
				
							</div>
						
					</div>
					
					
				<div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
				<div class="FormInput_left">
							
							References/FPB (Manual)
					
					</div>
					<div class="FormInput_right">
					
							<input type="text" id="Reference" size="40"/>        	
						
						</div>
				<div class="FormInput_left" style="height:80px;">
							
							Note
					
					</div>	
					<div class="FormInput_right" style="height:80px;">
							<textarea id="Note" cols="100" rows="2"></textarea>
	
						</div>
						@if($fetch->Status == 'APPROVAL_UPPER')
				<div id="divtom2">
					<input type="button" id="Rejects" NAME="Rejects" class="btn btn-danger mt-2 mr-2" value="REJECT" />                
					<input type="button" id="SubmitPRNewDetail" NAME="SubmitPRNewDetail" class="btn btn-success mt-2 mr-2" value="Approve &amp; Submit" />                
				</div>
				@endif
				<div id="WinCOA"></div>
				
				
				</div>
			</div>
		</div>
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
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }
        else if (type != "button" || type != "submit") {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
      	 } else if ($this.is("textarea")) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }
    })
	
    return inputValues.join('&');
}


PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
PRNewDetail.setImagePath("dhtmlx/imgs/");
PRNewDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Qty PO, Ordered, Remaining,Specification,Measurement,Note,Category,SubCat,Prices, Total");
PRNewDetail.setInitWidths("50,100,100,250,50,50,50,50,,200,100,100,100,100,100,100");
PRNewDetail.setColTypes("ron,ro,ro,ro,ron,edn,ron,ron,ro,ro,ro,ro,ro,edn,ron");
PRNewDetail.setColAlign("center,left,center,left,right,right,right,right,left,center,center,center,center,right,right");
PRNewDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
PRNewDetail.setEditable(true);
PRNewDetail.setNumberFormat("0,000",4,".",",");
PRNewDetail.setNumberFormat("0,000",5,".",",");
PRNewDetail.setNumberFormat("0,000",6,".",",");
PRNewDetail.setNumberFormat("0,000",7,".",",");
PRNewDetail.setNumberFormat("0,000",13,".",",");
PRNewDetail.setNumberFormat("0,000",14,".",",");
PRNewDetail.enableKeyboardSupport(true);
PRNewDetail.setSkin("dhx_skyblue");
PRNewDetail.enableMultiline(true);
PPRNewDetail = new dataProcessor("purchasing-request-approval-detail-update");
	PPRNewDetail.setTransactionMode({
	 mode: "POST",
	  total:true,
	  headers:{
		'X-CSRF-TOKEN': "{{ csrf_token() }}"
	  }	,
	  payload: {"PR_No":"<?php echo $fetch->PR_No;  ?>"}


	})
PPRNewDetail.setUpdateMode("Off");
PPRNewDetail.init(PRNewDetail);
PRNewDetail.init();
PRNewDetail.attachEvent('onEditCell', function(stage, rId, cInd, nValue, oValue) {
	if (stage == 2) {
		if (cInd == 4 || cInd == 5 || cInd == 7 || cInd == 11) {
			let qty = parseFloat(PRNewDetail.cells(rId, 4).getValue());
			let qtyPO = parseFloat(PRNewDetail.cells(rId, 5).getValue());
			let qtyOrdered = parseFloat(PRNewDetail.cells(rId, 6).getValue());
			let qtyRemaining = parseFloat(PRNewDetail.cells(rId, 7).getValue());
			if (Number.isNaN(qtyPO)) {
				qtyPO = 0;
				PRNewDetail.cells(rId, 5).setValue('0');
			}
			if (qty - qtyPO - qtyOrdered < 0) {
				alert("Qty exceeds the remaining quantity!");
				PRNewDetail.cells(rId, 5).setValue(oValue);
				return false;
			}

			let prices = parseFloat(PRNewDetail.cells(rId, 13).getValue());
			if (Number.isNaN(prices)) {
				prices = 0;
			}
			if (qty != "" && prices != '') {
				PRNewDetail.cells(rId, 14).setValue((qtyPO * prices).toFixed(2));
			}
			
			PRNewDetail.cells(rId, 7).setValue(parseFloat(qty - qtyPO - qtyOrdered));
		}
	}
	return true; // Allow the edit
})
refreshDataDetail();
function refreshDataDetail (){
    PRNewDetail.clearAndLoad("xml-cns-procurement-purchase-request-outstanding-detail?ID=<?php echo $fetch->PR_No;  ?>");
}

$("#AddPRNewDetail").click(function(){
	loadPR_Product('Product Lists','purchasing-product-list',1024,450);
	
})
$("#DelPRNewDetail").click(function(){
		
	PRNewDetail.deleteSelectedRows();
	
})


$("#SubmitPRNewDetail").click(function() {
var count=PRNewDetail.getRowsNum();
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
	if (Note == '') {
		alert("Kolom catatan (Note) Harus Diisi!");$("#Note").focus();return false;
	}else if (Note.length < 5) {
		alert("Kolom catatan (Note) minimal 5 karakter!");$("#Note").focus();return false;
	}else{
		PPRNewDetail.sendData();

		// var sip = 1;
		// for (var i = 0; i < PRNewDetail.getRowsNum(); i++) {
		// 	PurchaseType = PRNewDetail.cells2(i,1).getValue();
		// 	if(PurchaseType==Sub_Category2){
		// 		sip *= 1;
		// 	}else{
		// 		sip *= 0;
		// 	}
		// }
		$.blockUI(); 
	
		dapat = getAllValues();
		$.ajax({
			 headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
			 },
		
			url : "cns-procurement-purchase-request-detail-approve?_token={{ csrf_token() }}",
			type: "POST",
			data : dapat,
			success: function(data, textStatus, jqXHR)
			{
				if (data == 'success'){
					alert('Purchase Request Approved!');
					$.unblockUI(); 
					setTimeout(function(){
							
						refreshdata();
						$("#PurchaseManagementDiv").html('');
					
					},1000);				

				}else{
					alert('Gagal Update');
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
			
				url : "cns-procurement-purchase-request-detail-reject?_token={{ csrf_token() }}",
				type: "POST",
				data : dapat,
				success: function(data, textStatus, jqXHR)
				{
					if (data == 'success'){
						alert('Purchase Request REJECTED!');
						$.unblockUI(); 
						setTimeout(function(){
							refreshdata();
							$("#PurchaseManagementDiv").html('');
						},1000);				

					}else{
						alert('Gagal Update');
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
