<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('cash_handler')->where('NIK_handler',$Saya)->first();
	if($cekakun->Account_No == ''){
		
		echo "You Don't have access to this menu!";
		exit;
	}
	
	$priv =  Auth::user()->Priv_ID;
	$Fetch = DB::table("bank_withdrawal")->where("ID",$_GET['ID'])->first();
	
	

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL BANK WITHDRAWAL</div>

<div id="PageBankWithDrawal">
    <div class="FormInput_Form_Content">
	<div class="FormInput_left">
        		
                Withdrawal From
        
        	</div>
			<div class="FormInput_right">
                <?php
					echo $Fetch->From_Bank;
				?>
                

               </div> 
			   
   		<div class="FormInput_left">
        		
                WithDrawal Date
        
        </div>
		<div class="FormInput_right">
        				<input type="hidden" id="ID" size="10" readonly="readonly" value="<?php echo $_GET['ID']?>"/>        	

				<?php
					echo $Fetch->Withdrawal_Date;
				?>         
         </div>
    		<div class="FormInput_left">
        		
                WithDrawal To
        
        	</div>
			<div class="FormInput_right">
        	
            <?php
					echo $Fetch->To_Acc. " # ".$Fetch->Withdrawal_To;
				?>  
         
            </div>
   		<div class="FormInput_left">
        		
                WithDrawal Value
        
        </div>
		<div class="FormInput_right">
        
				
				<?php
					echo $Fetch->Withdrawal_Value;
				?>           	
         
         </div>
         

   		<div class="FormInput_left">
        		
                Reference
        
        </div>
		<div class="FormInput_right">
        

				<?php
					echo $Fetch->Reference;
				?>             
         </div>
		  <div class="FormInput_left">
        		
                Department
        
		</div>
		<div class="FormInput_right">
        	
            
				<?php
					echo $Fetch->Department;
				?>    
		 </div>	
		 
   		<div class="FormInput_left" style="height:70px;">
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:70px;">

				<?php
					echo $Fetch->Description;
				?>    
         </div>
		<div class="FormInput_right" style="height:70px;">
        
                 <input type="button" value="POSTING" id="SubmitWithDrawal" onclick="SubmitWithDrawal();" class="btn btn-success mt-2 mr-2" />        	

         </div>

	</div>
</div>
		


<script>

 				
function getAllValues() {
    var inputValues = [];
    $('#PageBankWithDrawal input,select,textarea').each(function() {
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

function SubmitWithDrawal(){
	dapat = getAllValues();

	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "accounting-bank-withdrawal-add-submit-confirm?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Bank Withdrawal POSTED!');

				setTimeout(function(){
						
					$("#BankWithDrawalDiv").html('');
					
					refreshdataWithDrawal();
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
