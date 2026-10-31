<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	

	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->NIK;
	
	


?>

@extends('layout.mainlayout-asset')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Assets And Inventory</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Inventory</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">DATA ADJUSTMENT</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">Adjustment Product</div>
	<div id="ContentPages">
    <div class="FormInput_Form_Content" >
   		
		<div class="FormInput_left">
        		
               Action Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Action_Date" size="15" value="<?php echo date('Y-m-d');?>"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Place To
        
        </div>
		<div class="FormInput_right">
        
				<select id='WH_ID'>
						<?php
						$qm = DB::table("gudang_list")->get();
						foreach ($qm as $fam){
							
							echo "<option value='$fam->Gudang_ID'>$fam->Description</option>";	
						}
						?>
					</select>
         
         </div>
   		<div class="FormInput_left" >
        		
               Product Code
        
        </div>

		<div class="FormInput_right">
        
				<input type="text" id="Product_Code" size="30"/>        	
				<input type="text" id="Product_Code_Desc" size="60"/>        	
         
         </div>
   		<div class="FormInput_left" >
        		
               Barcode No
        
        </div>

		<div class="FormInput_right">
        
				<input type="text" id="Barcode" size="30"/>        	
         
         </div>
   		<div class="FormInput_left" >
        		
               Qty
        
        </div>

		<div class="FormInput_right">
        
				<input type="text" id="Qty" size="15" value="1"/>  
				
         
         </div>

   		<div class="FormInput_left" >
        		
               Reference
        
        </div>

		<div class="FormInput_right">
        
				<input type="text" id="PO_No" size="40"/>        	
         
         </div>
	
   		<div class="FormInput_left" style="height:80px;">
				<input type="hidden" id="Price" size="20"/>        	
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Note"></textarea>        	

         
         </div>
         
	</div>
	<div id="divtom">
        <input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Update" />                
    </div>
<div id="DivProcess"></div>
</div>
</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>

	$("#Product_Code").coolautosuggest({

		url:"search-produk?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#Product_Code"),
		width:700,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#Product_Code").val(result.id);
			$("#Product_Code_Desc").val(result.data);
			$("#Barcode").val(result.thumbnail);
			
			
		  }
		}
	});

$('#SubmitCustDetail').click(function(){
	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "inventory-adjustment-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Adjustment Submitted!');
					location.href='inventory-adjustment';
					
			}else{
				alert('Adjustment to Submit!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})

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

</script>
@endsection
