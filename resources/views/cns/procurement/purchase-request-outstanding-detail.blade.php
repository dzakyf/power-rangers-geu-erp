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

	$ps = DB::select(DB::raw("SELECT COUNT(ID) as jumlah FROM purchase_summary_cns WHERE Location = '$LSP' and Purchase_Date LIKE '$date%'"));
		
	$NoR = $ps->jumlah + 1;
	if ( $NoR < 10 ) $NoR = "0$NoR";
	$dateR = date('YmdHis');
	$psno = "PS/".$LSP."/$dateR-" . sprintf("%02d", $NoR); 

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
							<input type="hidden" id="PS_No" size="50" readonly="readonly" value="{{ $psno }}"/>
							<input type="hidden" readonly="readonly" id="ID" size="5"  disabled="disabled"/>           
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
                    <div class="FormInput_left">

                        Subtotal

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Subtotal" size="10" value="0.00" readonly required />

                    </div>
                    <div class="FormInput_left">

                        Tax

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Tax" size="10" value="0.00" onkeyup="recalc()" required />

                    </div>
                    <div class="FormInput_left">

                        Shipping

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Shipping" size="10" value="0.00" onkeyup="recalc()" required />

                    </div>
                    <div class="FormInput_left">

                        Other

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Other" size="10" value="0.00" onkeyup="recalc()" required />

                    </div>
                    <div class="FormInput_left">
                        Total
                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Total" size="10" value="0.00" required />

                    </div>
                    <div class="FormInput_left">
                        PO Date
                    </div>
                    <div class="FormInput_right">

                        <input type="date" id="PO_Date" size="5" value="{{ date("Y-m-d") }}" required />

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
				<div id="divtom2">
					<input type="button" id="SubmitPRNewDetail" NAME="SubmitPRNewDetail" class="btn btn-success mt-2 mr-2" value="Submit to PO" />
				</div>
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
        var val = $(this).hasClass('cns-decimal') ? cnsParseNumber($(this).val(), 0) : $(this).val();
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + val);
        }
        else if (type != "button" || type != "submit") {
            inputValues.push($(this).attr("id") + "=" + val);
      	 } else if ($this.is("textarea")) {
            inputValues.push($(this).attr("id") + "=" + val);
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
PRNewDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Qty PO, Ordered, Remaining,Specification,Measurement,Note,Category,SubCat,Prices, Total");
PRNewDetail.setInitWidths("50,100,100,250,50,50,50,50,200,100,100,100,100,100,100");
PRNewDetail.setColTypes("ron,ro,ro,ro,ron,edn,ron,ron,ro,ro,ro,ro,ro,edn,ron");
PRNewDetail.setColAlign("center,left,center,left,right,right,right,right,left,center,center,center,center,right,right");
PRNewDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
PRNewDetail.setEditable(true);
PRNewDetail.setNumberFormat("0,000.00",4,".",",");
PRNewDetail.setNumberFormat("0,000.00",5,".",",");
PRNewDetail.setNumberFormat("0,000.00",6,".",",");
PRNewDetail.setNumberFormat("0,000.00",7,".",",");
PRNewDetail.setNumberFormat("0,000.00",13,".",",");
PRNewDetail.setNumberFormat("0,000.00",14,".",",");
PRNewDetail.enableKeyboardSupport(true);
PRNewDetail.setSkin("dhx_skyblue");
PRNewDetail.enableMultiline(true);
PPRNewDetail = new dataProcessor("cns-procurement-purchase-request-outstanding-detail-update");
	PPRNewDetail.setTransactionMode({
	 mode: "POST",
	  total:true,
	  headers:{
		'X-CSRF-TOKEN': "{{ csrf_token() }}"
	  }	,
	  payload: {
		"PR_No":"<?php echo $fetch->PR_No;  ?>",
		"PS_No": "{{ $psno }}"
	}


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
			recalc();
		}
	}
	return true; // Allow the edit
})
PPRNewDetail.attachEvent("onBeforeDataSending", function (id,state, data) {
	// Modify or add payload data here
	// console.log(data)
	return true; // Return true to continue processing
});
PPRNewDetail.attachEvent("onAfterUpdateFinish", function (id,state, data) {
		$.blockUI(); 
	
		dapat = getAllValues();
		$.ajax({
			 headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
			 },
		
			url : "cns-procurement-purchase-request-outstanding-detail-submit?_token={{ csrf_token() }}",
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
});
refreshDataDetail();
function refreshDataDetail (){
    PRNewDetail.clearAndLoad("xml-cns-procurement-purchase-request-outstanding-detail?ID=<?php echo $fetch->PR_No;  ?>");
}

function recalc() {
	let total = 0;
	if (PRNewDetail.getRowsNum() > 0) {
		let getIds = PRNewDetail.getAllRowIds().split(',');
		for (let i = 0; i < getIds.length; i++) {
			const ele = getIds[i];
			total += cnsParseNumber(PRNewDetail.cells(ele, 14).getValue(), 0);
		}
	}
	let tax = cnsParseNumber($('#Tax').val(), 0);
	let shipping = cnsParseNumber($('#Shipping').val(), 0);
	let other = cnsParseNumber($('#Other').val(), 0);
	$('#Subtotal').val(cnsFormatNumber(total, 2));
	$('#Total').val(cnsFormatNumber(total + tax + shipping + other, 2));
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
