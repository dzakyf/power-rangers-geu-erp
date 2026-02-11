<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	$priv =  Auth::user()->Priv_ID;
	$fetch = DB::table("product_code")->where("ID",$_GET['ID'])->first();
	$sobjf = DB::select(DB::raw("SHOW FIELDS FROM product_code"));

	echo "<script>";

		foreach($sobjf as $objf){
		$idobj = $objf->Field;
		$isinya = $fetch->$idobj;
			
				echo "$('#$idobj').val('$isinya'); \n";
		}
	
	echo "</script>";

?>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">DETAIL PRODUCT</div>
<div id="ContentPages">
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr><td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
    <tr height="30">
        <td class="badan" width="150">Category</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="hidden" id = "ID"  style="border:none;outline:none;" readonly="readonly" />
            <input type="text" id = "Main_Category"  style="border:none;outline:none;" readonly="readonly" /></td>
        <td class="badan" width="100">Sub Category</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id="Category" size="30" tabindex='6' style="border:none;outline:none;" readonly="readonly" />
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Purchase Type</td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><input type="text" size="30" id="Supplies_Type" readonly="readonly" tabindex='1'  style="border:none;outline:none;"/></td>
        <td class="badan">Product Code </td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'>
        			<input type="text" size="30" id="Product_Code"  tabindex='7' style="border:none;outline:none;" />
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Description </td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><input type="text" size="30" id="Description" tabindex='2' style="border:none;outline:none;" /></td>        
        <td class="badan">Specification </td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><input type="text" size="30" id="Specification" tabindex='8' style="border:none;outline:none;" /></td>        
    </tr>
    <tr height="30">
        <td class="badan">Product Brand </td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><input type="text" size="30" id="Product_Brand"  style="border:none;outline:none;" /></td>        
        <td class="badan">Product Type </td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><input type="text" size="30" id="Product_Type" style="border:none;outline:none;" /></td>        
    </tr>
    <tr height="30">
        <td class="badan">Unit Name</td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><input type="text" size="30" id="Unit_Name"  style="border:none;outline:none;" /></td>        

        <td class="badan">Price</td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><input type="text" size="30" id="UnitPrice"  style="border:none;outline:none;"/></td>        

    </tr>

</table>
</td>
</tr>
</table>
</div>
    <div id="divtom">
            <input type="button" id="UpdateData" NAME="UpdateData" onclick="Submitkan()" value="Save Data" />                
    </div>
<div id="DivProcess"></div>

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
    
		url : "master-product-code-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Detail Product Updated!');

				setTimeout(function(){
						
					$("#PRPRODUCTLISTDet").html('');
					
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
