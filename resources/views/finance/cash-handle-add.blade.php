<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW CASH HANDLER</div>

	<div id="HandlerDIVAdd">
    <div class="FormInput_Form_Content">
		<div class="FormInput_left">
			
			Cashier Type
	
		</div>
		<div class="FormInput_right">
		
            <select id="Position">
              <option>CASHIER</option>
              <option>AGENT</option>  
            </select>
		</div>
		<div class="FormInput_left">
        		
                Cashier CoA
        
        	</div>
			<div class="FormInput_right">
        	
            <select id="Account_No">
                <?php
					
					$Query = DB::table('branch_coa')->where("Acc_No","LIKE","1.1.1.%")->where("Listed_OnSearch",1)->get();
					foreach ($Query as $Data) {
						echo "<option value='$Data->Acc_No'>$Data->Description -  $Data->Acc_No</option>";
					}
				?>
                
            </select>
		</div>
		<div class="FormInput_left">
        		
                Cashier ID
        
        	</div>
			<div class="FormInput_right">
        	
            <select id="username">
                <?php
					error_reporting(0);
					$Query = DB::table('users')->where("Status","=","ACTIVE")->get();
					foreach ($Query as $Data) {
						$Queryx = DB::table('cash_handler')->where("NIK_Handler","=",$Data->username)->first();
						if ($Queryx->Name_Handler == ''){
						echo "<option value='$Data->username'>$Data->Name $Data->Emp_ID - $Data->username - $Data->Priv_ID - $Data->Location</option>";
						}
					}
				?>
                
            </select>
		</div>
   		
		<div class="FormInput_right">
        
                 <input type="button" value="Submit" id="SubmitHandler" onclick="SubmitHandleran();" />        	

         </div>

	</div>
</div>
   

<script>
		

 				
function getAllValues() {
    var inputValues = [];
    $('#HandlerDIVAdd input,select,textarea').each(function() {
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

function SubmitHandleran(){
	dapat = getAllValues();

	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "finance-cash-handle-add-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Cash Handler was Created!');

				setTimeout(function(){
						
					$("#CashHandlerDiv").html('');
					
					refreshdataHandler();
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
