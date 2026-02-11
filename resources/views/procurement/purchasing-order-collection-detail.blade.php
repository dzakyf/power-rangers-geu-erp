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
		$idobj = $objf->Field;
		$isinya = $fetch->$idobj;
			
				echo "$('#$idobj').val('$isinya'); \n";
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
							echo "<option value='$Data->Department'>$Data->Department - $Data->Directorat</option>";	
							
							}
					?>
					</select>
						
				 </div>
				 
				 
				<div class="FormInput_left">
						
						Category
				
				</div>
				<div class="FormInput_right">
				
						<select id="Category" onchange="loadSubCat(this.value);">
							<option>CONSUMABLE</option>
							<option>ASSET</option>
							<option>SERVICES</option>
						</select>
				 
				 </div>
				<div class="FormInput_left">
						
						Group Category
				
				</div>
				<div class="FormInput_right">
					<div id="SubCat">
						 <input type="text" readonly="readonly" id="Sub_Category" size="30" />     
					 
					 </div>
				 
				</div>
				<div class="FormInput_left">
						
					   Sub Category
				
				</div>
				<div class="FormInput_right">
					<div id="SubCat2">
												 <input type="text" readonly="readonly" id="Supplies_Type" size="30" />           
			
					 </div>
				 
				</div>
				<div class="FormInput_left">
						
						References/FPB (Manual)
				
				</div>
				
				<div class="FormInput_right">
				
						<input type="text" id="Reference" size="40"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
						Note
				
				</div>	
				<div class="FormInput_right" style="height:80px;">
						<textarea id="Note" cols="100" rows="2"></textarea>

				 </div>
			<div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			
			</div>
		</div>


<script>

PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
PRNewDetail.setImagePath("dhtmlx/imgs/");
PRNewDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Prices");
PRNewDetail.setInitWidths("50,100,100,250,50,200,100,100,100,100,100");
PRNewDetail.setColTypes("ro,ro,ro,ro,edn,ed,ed,ed,ro,ro,ro");
PRNewDetail.setColAlign("center,left,center,left,right,left,center,center,center,center");
PRNewDetail.setColSorting("int,str,str,str,str,str,str,str");
PRNewDetail.setEditable(true);
PRNewDetail.setNumberFormat("0,000",4,".",",");
PRNewDetail.enableKeyboardSupport(true);
PRNewDetail.setSkin("dhx_skyblue");
PRNewDetail.enableMultiline(true);
PRNewDetail.init();
PRNewDetail.loadXML("xml-purchasing-request-approval-detail?ID=<?php echo $fetch->PR_No;  ?>");



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




$("#SubmitPRNewDetail").click(function() {

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
	
	var sip = 1;
	for (var i = 0; i < PRNewDetail.getRowsNum(); i++) {
		PurchaseType = PRNewDetail.cells2(i,1).getValue();
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
	
		url : "purchasing-cancelation-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Purchase Request Successfully Canceled!');
				$.unblockUI(); 
				setTimeout(function(){
					location.href='purchasing-request-cancelation';
				},1000);				

			}else{
				alert('Purchase Request Cannot Process due to PO was Processed!');
				
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "error" );
	
		}
	});			
		
				
	

});

</script>
