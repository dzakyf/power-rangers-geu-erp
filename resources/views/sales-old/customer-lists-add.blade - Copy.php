<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
		

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW CUSTOMER</div>

<div id="CPOPR">
    <div class="FormInput_Form_Content">

   		<div class="FormInput_left">
        		
                Parent Customer
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Customer_Parent" size="40" />        	
         
         </div>
         <div class="FormInput_left">
        		
                Company_Name
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Company_Name" size="35" />        	
         
         </div>
		<div class="FormInput_left">
        		
                Address
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Address" size="100" />        	
         
         </div>
		 <div class="FormInput_left">
        		
                City	
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="City" size="35" />        	
         
         </div>
		 <div class="FormInput_left">
        		
                Province
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Province" size="45" />        	
         
         </div>
   		<div class="FormInput_left">
        		
                Phone
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Phone" size="35"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                PIC
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="PIC" size="35"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                PIC Phone
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="PIC_Phone" size="20"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                PIC Position
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="PIC_Position" size="40"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                NPWP
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="NPWP" size="30"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                VAT Name
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="VAT_Name" size="45"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                VAT Address
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="VAT_Address" size="100"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                VAT Prefix
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="VAT_Prefix" size="20" value="050"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                Sales Person
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="Sales_Person" size="40"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                Email
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="Email" size="50"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                Credit Collector
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="C_Collector" size="50"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                PIC Billing
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="PIC_Billing" size="40"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                PIC Billing Phone
        
        </div>
		<div class="FormInput_right">
        
				 <input type="text" id="PIC_Billing_Phone" size="40"/>        	
         
         </div>
		    		
	</div>

				<div class="FormInput_right" style="height:175px;vertical-align:bottom;">

					<input type="button" value="Submit" id="UpdatePO" onclick="UpdatePO();" class="btn btn-info mt-2 mr-2"  />        	

				</div>		


<script>
			
function getAllValues() {
    var inputValues = [];
    $('#CPOPR input,select,textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }else{
			if (type != "button" && type != "submit") {
				
            inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
        }
    })
	
    return inputValues.join('&');
} 	

function UpdatePO(){
	dapat = getAllValues();
	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "customer-lists-add-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Data Customer Submitted!');

				setTimeout(function(){
						
					$("#divlistcustomer").html('');
					
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
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["PO_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

 	
</script>
