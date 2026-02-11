<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('bank_handler')->where('NIK_handler',$Saya)->first();
	if($cekakun->Account_No == ''){
		
		echo "You Don't have access to this menu!";
		exit;
	}
	
	$priv =  Auth::user()->Priv_ID;
	$Fetch = DB::table("bank_transfer")->where("ID",$_GET['ID'])->first();
	

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL BANK TRANSFER</div>

<div id="BankTransferDiv">
    <div class="FormInput_Form_Content">
    		<div class="FormInput_left">
        		
                Transfer From
        
        	</div>
			<div class="FormInput_right">
        	        	<input type="hidden" id="Task_ID" size="25" value="<?php echo $Fetch->Task_ID?>" readonly="readonly"/>   
        	        	<input type="hidden" id="From_Acc" size="25" value="<?php echo $Fetch->From_Acc?>" readonly="readonly"/>   

                <?php
					echo $Fetch->From_Acc. " - ".$Fetch->To_Acc;
				?>
                
            </select>
         
            </div>
   		<div class="FormInput_left">
        		
                Transfer Value
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Transfer_Val" size="25" value="<?php echo $Fetch->Bank_Val?>" readonly="readonly"/>        	
         
         </div>
         

   		<div class="FormInput_left">
        		
                Reference
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Reference" size="80" value="<?php echo $Fetch->Reference?>" readonly="readonly"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:75px;">
        		<textarea id="Note" cols="80" rows="3" readonly="readonly"><?php echo $Fetch->Note?></textarea>

         </div>
		<div class="FormInput_right">
        
                 <input type="button" value="Receive" id="SubmitTransfer" onclick="SubmitTerima();" />        	

         </div>

	</div>
</div>
		


<script>
		

 				
function getAllValues() {
    var inputValues = [];
    $('#BankTransferDiv input,select,textarea').each(function() {
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

function SubmitTerima(){
	dapat = getAllValues();

	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "finance-bank-transfer-add-submit-receive?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Bank Transfer Task Closed and Received!');

				setTimeout(function(){
						
					$("#BankTransferDiv").html('');
					
					refreshdatatransfer();
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
