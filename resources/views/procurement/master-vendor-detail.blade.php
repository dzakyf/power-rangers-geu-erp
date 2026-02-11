<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	$priv =  Auth::user()->Priv_ID;
	$fetch = DB::table("supplier")->where("ID",$_GET['ID'])->first();
	$sobjf = DB::select(DB::raw("SHOW FIELDS FROM supplier"));

	echo "<script>";

		foreach($sobjf as $objf){
		$idobj = $objf->Field;
		$isinya = $fetch->$idobj;
			
				echo "$('#$idobj').val('$isinya'); \n";
		}
	
	echo "</script>";

?>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">DETAIL SUPPLIER</div>
<div id="ContentPages">
	<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
		<tr>
		<td align="center">
			<table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
			<tr height="30">
				<td class="badan" width="150">Supplier Name</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
				            	<input type="hidden" size="30" id="ID" style="border:none;outline:none;" />

						<input type="text" size="30" id="Company_Name" style="border:none;outline:none;" />

				</td>
				<td class="badan" width="100">Contact Person</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
						<input type="text" size="30" id="Contact_Person" style="border:none;outline:none;" />
				</td>
			</tr>
			<tr height="30">
				<td class="badan">Address</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'>
						<input type="text"  size="40" id="Address" style="border:none;outline:none;" />
				</td>
				<td class="badan">Street</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'>
							<input type="text" size="40" id="Street"  tabindex='7' style="border:none;outline:none;" />
				</td>
			</tr>
			<tr height="30">
				<td class="badan">City </td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="15" id="City" tabindex='2' style="border:none;outline:none;" /></td>        
				<td class="badan">Phone </td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="20" id="Phone" tabindex='8' style="border:none;outline:none;" /></td>        
			</tr>
			<tr height="30">
				<td class="badan">Fax</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="20" id="Fax" style="border:none;outline:none;" /></td>        
				<td class="badan">HP</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="20" id="HP" style="border:none;outline:none;" /></td>        
			</tr>
			<tr height="30">
				<td class="badan">Email</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="30" id="Email" style="border:none;outline:none;" /></td>        

				<td class="badan">NPWP</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="30" id="NPWP" style="border:none;outline:none;"/></td>        

			</tr>
			<tr height="30">
				<td class="badan">Sales Name</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="30" id="Sales_Name" style="border:none;outline:none;" /></td>        

				<td class="badan">Sales No</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="20" id="Sales_Phone" style="border:none;outline:none;"/></td>        

			</tr>
			<tr height="30">
				<td class="badan">Payment Method</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="50" id="Payment" style="border:none;outline:none;" /></td>        

				<td class="badan">Note</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="50" id="Note" style="border:none;outline:none;"/></td>        

			</tr>
			<tr height="30">
				<td class="badan">Bank Name (Rp)</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="40" id="Bank_Rupiah" style="border:none;outline:none;" /></td>        

				<td class="badan">Bank Account (Rp)</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="20" id="Bank_Rupiah_Account" style="border:none;outline:none;"/></td>        

			</tr>
			<tr height="30">
				<td class="badan">Bank Account Name (Rp)</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="30" id="Bank_Rupiah_Name" style="border:none;outline:none;" /></td>        

				<td class="badan">Bank Foreign</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="40" id="Bank_Foreign" style="border:none;outline:none;"/></td>        

			</tr>
			<tr height="30">
				<td class="badan">Bank Foreign Account</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="20" id="Bank_Foreign_Account" style="border:none;outline:none;" /></td>        

				<td class="badan">Bank Foreign Name</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="30" id="Bank_Foreign_Name" style="border:none;outline:none;"/></td>        

			</tr>
			<tr height="30">
				<td class="badan">Bank Foreign Currency</td>
				<td class="badan">:</td>
				<td class="badan_kiri" width='250'><input type="text" size="5" value="" id="Bank_Foreign_Currency" style="border:none;outline:none;" /></td>        

				<td class="badan"></td>
				<td class="badan"></td>
				<td class="badan_kiri" width='250'>	
					<input type="button" id="UpdateData" NAME="UpdateData" onclick="Submitkan()" value="Submit Data" />                
				</td>        

			</tr>

			</table>
		</td>
		</tr>
	</table>
</div>

<script>

		
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

function Submitkan(){
	dapat = getAllValues();
	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "master-vendor-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Detail Supplier Updated!');

				setTimeout(function(){
						
					$("#PRSupplierDet").html('');
					
					refreshdata();
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


</script>
