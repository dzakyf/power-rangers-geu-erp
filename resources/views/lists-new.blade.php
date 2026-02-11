<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	
	
?>

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">I. DATA ASSET</div>
<div id="DivContentFinish">
    <div class="FormInput_Form_Content">
		
   		<div class="FormInput_left">
        		
               Asset Category
        
        </div>
		<div class="FormInput_right">
			<select id="Asset_Category" onchange="loadSubcategory();">
						<?php
							$qx = DB::select(DB::raw("SELECT * FROM mam_asset.master_product_group WHERE Initial='FA'"));
							foreach ($qx as $fa){
								
								echo "<option value='$fa->Product_Group'>$fa->Product_Group - $fa->Initial</option>";
							}
						
						?>
			</select>     	
         
         </div>
		 <div class="FormInput_left">
        		
               Asset Sub Category
        
        </div>
		<div class="FormInput_right">
			<div id="subcat">
			<select id="Asset_Sub_Category">
			</select>     	
			</div>
         
         </div>
		  <div class="FormInput_left">
        		
               Asset Type
        
        </div>
		<div class="FormInput_right">
			<div id="subcat2">
			<select id="Asset_Type">
			</select>     	
			</div>
         
         </div>
				 
   		<div class="FormInput_left">
        		
               Asset Name/Description
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Product_Description" size="55"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
			Spesification        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Specification" size="50"/>        	
         
         </div>
		  
		<div class="FormInput_left">	
                Brand
        
        </div>
		<div class="FormInput_right">
        
        		<input type="text" size="35" id="Product_Brand"  />    ex : toyota 	

         
         </div>
		<div class="FormInput_left">
        		
                Model
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Model" size="30"/>  ex : Kijang      	
         </div>		 
		 <div class="FormInput_left">
        		
               Type
        
        </div>
		
		<div class="FormInput_right">
        
				<input type="text" id="Product_Type" size="30"/>    ex : innova    	
         
         </div>
		 <div class="FormInput_left">
        		
			Sub Product  Type       
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Sub_Product_Type" size="30"/>  ex : G      	
         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
               Function
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<input type="text" id="Product_Function" size="50"/>   ex: MPV     	
         
         </div>
   		
   		<div class="FormInput_left">
        		
                Unit Measurement
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Unit_Name" size="10"/> ex : unit       	
         </div>
   		<div class="FormInput_left">
        		
                Manufacture Date
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Manufacture_Date" size="10" readonly="readonly"/>      	
         </div>
		 <div class="FormInput_left">
        		
                Source Production
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Source_Production" size="10"/> ex : BBM,Listrik       	
         </div>
		 <div class="FormInput_left">
        		
                Colour
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Colour" size="10"/>    	
         </div>
		  <div class="FormInput_left">
        		
                Size
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Size" size="20"/> ex : 40m x 20 m (land),CDD/Engkel (vehicle),etc   	
         </div>
		  <div class="FormInput_left">
        		
                Capacity
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Capacity" size="10"/>    	
         </div>
		 <div class="FormInput_left">
        		
                Group No
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Group_No" size="30"/> 
         </div>
		 <div class="FormInput_left">
        		
                Part No
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Part_No" size="30"/>    ex : nomor rangka,product code	
         </div>
		 <div class="FormInput_left">
        		
                Serial Number
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="SN" size="30"/>    	ex : nomor mesin,SN Product
         </div>
		 <div class="FormInput_left">
        		
                Registration No
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Registration_No" size="25"/>    	ex : nomor plat,no shm,dll
         </div>
		 <div class="FormInput_left">
        		
                Registration Date
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Registration_Date" size="10" readonly="readonly"/>   ex: tgl bpkb,tgl shm 	
         </div>
		 <div class="FormInput_left">
        		
                Expired Date
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Exp_Date" readonly="readonly" size="10"/>    	
         </div>
		 
		 <div class="FormInput_left">
        		
                Owner
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Owner" size="30"/>    	ex : raddix,MAM,dll
         </div>
		 <div class="FormInput_left">
        		
                Buy Date
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Buy_Date" size="10" readonly="readonly"/>    	
         </div>
		 <div class="FormInput_left">
        		
                Buy Value
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Buy_Val" size="15"/>   ex: 000000000 	
         </div>
		 <div class="FormInput_left">
        		
                Supplier
        
        </div>
		<div class="FormInput_right">
				<?php
				$qr = DB::select(DB::raw("SELECT Supplier_ID, Company_Name FROM mam_asset.supplier ORDER BY Company_Name ASC"));
				echo "<select id='Supplier' class='cs-corner-all'  style='width:350px;'>";
				foreach($qr as $fa){
				echo "<option value='".$fa->Supplier_ID."'>".$fa->Company_Name."-".$fa->Supplier_ID."</option>";	
				}
				echo "</select>";
				?>    	
         </div>
		  <div class="FormInput_left">
        		
                Depreciation Year Max
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Depr" size="5" value="5"/>
         </div>
		 <div class="FormInput_left">
        		
                Depreciation Method
        
        </div>
		<div class="FormInput_right">
				<select id="Depr_M">
					<option>STRAIGHT_MONTH</option>
					<option>STRAIGHT_YEAR</option>
					<option>SUM_OF_YEAR</option>
					<option>UNIT_OF_PRODUCTION</option>
					<option>DOUBLE_DECLINING_BALANCE</option>
				</select>	
         </div>
		   <div class="FormInput_left">
        		
                Current Book Value
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Book_Val" size="15" value="0"/>
         </div>
		   <div class="FormInput_left">
        		
                Additional Value
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Additional_Val" size="15"/>
         </div>
		<div class="FormInput_left">
        		
                Asset Location
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Asset_Location" size="35"/>
         </div>
		   <div class="FormInput_left">
        		
                Asset Location Address
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Asset_Location_Address" size="50" />
         </div>
		  <div class="FormInput_left">
        		
                Asset Location Street
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Asset_Location_Street" size="50" />
         </div>
		 <div class="FormInput_left">
        		
                Asset Location City
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Asset_Location_City" size="25"/>
         </div>
		 <div class="FormInput_left">
        		
                Asset Location Department
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Asset_Location_Department" size="35"/>
         </div>
		 <div class="FormInput_left">
        		
               User Responsible
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="User_Responsible" size="25"/>
         </div>
		 <div class="FormInput_left">
        		
                Leasing Vendor
        
        </div>
		<div class="FormInput_right">
				<?php
				$qr = DB::select(DB::raw("SELECT * FROM mam_asset.supplier ORDER BY Company_Name ASC"));
				echo "<select id='Leasing_Vendor' class='cs-corner-all'  style='width:350px;'>";
				foreach($qr as $fa){
				echo "<option value='".$fa->Supplier_ID."'>".$fa->Company_Name."-".$fa->Supplier_ID."</option>";	
				}
				echo "</select>";
				?>    	
         </div>
		 <div class="FormInput_left">
        		
               Leasing Total Value
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Leasing_Val" size="20" value="0"/> info : nilai total leasing
         </div>
		 <div class="FormInput_left">
        		
               Leasing Remain Value
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Leasing_Remain" size="20" value="0"/> info : nilai sisa pinjaman
         </div>
		<div class="FormInput_left">
        		
               Payment Method
        
        </div>
		<div class="FormInput_right">
					<select id="Leasing_Method">
					<option>MONTHLY</option>
					<option>YEARLY</option>
					<option>WEEKLY</option>
				</select>	
         </div>
		 <div class="FormInput_left">
        		
               Payment Times Number
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Leasing_Method_Value" size="5" value="0"/> info :misal jumlah bulan pembayaran

         </div>
		  <div class="FormInput_left">
        		
               Installment Amount per Method
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Installment" size="25" value="0"/> info : nilai setiap pembayaran

         </div>
		 <div class="FormInput_left">
        		
               Installment Method
        
        </div>
		<div class="FormInput_right">
					<select id="Installment_Method">
					<option>FIX_COST</option>
					<option>FLOATING_POINT</option>
				</select>	

         </div>
		  <div class="FormInput_left">
        		
               Installment Basic
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Installment_Basic" size="12" value="0"/> info : nilai pinjaman pokok

         </div>
		  <div class="FormInput_left">
        		
               Installment Interest
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Installment_Interest" size="12" value="0"/> info : nilai bunga

         </div>
		 <div class="FormInput_left">
        		
               Installment Number
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Installment_No" size="5" value="0"/> info : pembayaran cicilan ke sekian

         </div>
		 <div class="FormInput_left">
        		
               Installment Due Days
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Installment_Due_Days" size="5" value="25"/> info : tanggal jatuh tempo setiap bulan

         </div>
    </div>
	
		
</div>
    <div id="divtom" style="height:200px">
        <Br><br><br>
        <input type="button" id="SubmitNewAsset" NAME="SubmitNewAsset" value="Save" />
		
    </div>
    
<div id="DivProcess"></div>
<script>

var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Buy_Date","Exp_Date",'Manufacture_Date','Registration_Date']);
myCalendar.setDateFormat("%Y-%m-%d");

function loadSubcategory(){
	LoadM('subcat','asset-lists-sub-category?Category=' + encodeURI($("#Asset_Category").val()));
}
function loadassettype(){
	LoadM('subcat2','asset-lists-sub-sub-category?Category=' + encodeURI($("#Asset_Category").val()) + '&Sub_Category=' + encodeURI($("#Asset_Sub_Category").val()));
}

function getAllValues() {
    var inputValues = [];
    $('#DivContentFinish input,select,textarea').each(function() {
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


$('#SubmitNewAsset').click(function(){
	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "asset-lists-new-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data New Asset Submitted!');
					DATAASSETSRefresh();
					$("#DivContentFinish").html('');
					
			}else{
				alert('New Asset Failed to Submit!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
})


</script>