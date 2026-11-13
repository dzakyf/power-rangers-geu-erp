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
	
					<div style="width:250px;display:inline-block;">
						<select id="Supplies_Type" style="width:100%;">
							<option value="">-- Pilih Product Specification --</option>
							@foreach(\App\CnsMasterKemasan::where('product_type', 'Export')->orderBy('Name')->get() as $spec)
								<option value="{{ $spec->Name }}" {{ ($fetch->Supplies_Type ?? '') === $spec->Name ? 'selected' : '' }}>{{ $spec->Name }}</option>
							@endforeach
						</select>
					</div>
	
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
							<input type="text" readonly="readonly" id="Supplies_Type_SubCat" value="" size="30" />           
				
							</div>
						
					</div>
					
					
				<div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
				<div id="divtom">
					<input type="button" id="AddPRNewDetail" NAME="AddPRNewDetail" class="btn btn-info mt-2 mr-2" value="Add New" />
					<input type="button" id="DelPRNewDetail" NAME="DelPRNewDetail"  class="btn btn-danger mt-2 mr-2" value="Delete" />
									
				</div>
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


if ($.fn.select2) {
    $('#Supplies_Type').select2({
        width: '100%',
        placeholder: '-- Pilih Product Specification --',
        allowClear: true
    }).trigger('change.select2');
}

PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
PRNewDetail.setImagePath("dhtmlx/imgs/");
PRNewDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Prices, Total");
PRNewDetail.setInitWidths("50,100,100,250,50,200,100,100,100,100,100,100");
PRNewDetail.setColTypes("ron,ed,ed,ed,edn,ed,ed,ed,ro,ro,edn,ron");
PRNewDetail.setColAlign("center,left,center,left,right,left,center,center,center,center,right,right");
PRNewDetail.setColSorting("int,str,str,str,str,str,str,str");
PRNewDetail.setEditable(true);
PRNewDetail.setNumberFormat("0,000.00",4,".",",");
PRNewDetail.setNumberFormat("0,000.00",10,".",",");
PRNewDetail.setNumberFormat("0,000.00",11,".",",");
PRNewDetail.enableKeyboardSupport(true);
PRNewDetail.setSkin("dhx_skyblue");
PRNewDetail.enableMultiline(true);
PRNewDetail.attachEvent('onEditCell', function(stage, rId, cInd) {
	if (stage == 2 && (cInd == 4 || cInd == 10)) {
		let qty = parseFloat(PRNewDetail.cells(rId, 4).getValue()) || 0;
		let prices = parseFloat(PRNewDetail.cells(rId, 10).getValue()) || 0;
		PRNewDetail.cells(rId, 11).setValue((qty * prices).toFixed(2));
	}
	return true;
});
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
refreshDataDetail();
function refreshDataDetail (){
    PRNewDetail.clearAndLoad("xml-cns-procurement-purchase-request-detail?ID=<?php echo $fetch->PR_No;  ?>");
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
