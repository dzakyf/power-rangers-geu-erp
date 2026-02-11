<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	

?>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">NEW PRODUCT</div>
<div id="ContentPages">
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr><td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" border="0">
    <tr height="30">
        <td class="badan" width="150">Category</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
           <select id="Main_Category" onchange="loadSubCat(this.value);">
							<option value="">Select</option>
							<?php
									$q = DB::select(DB::raw("SELECT * FROM master_product_group ORDER BY Product_Group"));
									foreach ($q as $fa){
										
										echo "<option>$fa->Product_Group</option>";	
									}
									?>
							
						</select></td>
        <td class="badan" width="100">Sub Category</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <div id="SubCat">
						 &nbsp;  None       
					 
					 </div>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Sub Sub Category</td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'><div id="SubCat2">
						 &nbsp;  None       
			
					 </div></td>
        <td class="badan">Product Code </td>
        <td class="badan">:</td>
        <td class="badan_kiri" width='250'>
        			<input type="text" size="30" id="Product_Code" disabled="disabled"  tabindex='7' style="border:none;outline:none;" />
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
            <input type="button" id="UpdateData" NAME="UpdateData" onclick ="Submitkan()" value="Save Data" />                
    </div>
<div id="DivProcess"></div>

<script>


function loadSubCat(valCat){
		LoadM('SubCat','pr-load-subcat2?Category=' + encodeURI(valCat));
}
function gantiSub(valCat,vals){
		LoadM('SubCat2','pr-load-sub?Main_Category=' + encodeURI(valCat) +'&subval=' + encodeURI(vals));
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

function Submitkan(){
	dapat = getAllValues();
	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "master-product-code-new-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('NEW Product Created!');

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
