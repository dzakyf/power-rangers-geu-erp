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
	
	$Saya =  Auth::user()->username;
	
	

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW CASH DEPOSIT</div>

<div id="PageCashDeposit">
    <div class="FormInput_Form_Content">
   		<div class="FormInput_left">
        		
                Deposit Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Deposit_Date" size="25" value="<?php echo date('Y-m-d')?>"/>        	
         
         </div>
    		<div class="FormInput_left">
        		
                Deposit To
        
        	</div>
			<div class="FormInput_right">
        	
            <select id="To_Acc" style="width:400px">
                <?php
					$Query = DB::table("bank_acc")->where("Status",'ACTIVE')->get();
					foreach ($Query as $Data){
						echo "<option value='$Data->Acc_No|$Data->Bank_Acc'>$Data->Acc_No - $Data->Bank_Name / $Data->Bank_Acc</option>";	
						
					}
				?>
                
            </select>
         
            </div>
   		<div class="FormInput_left">
        		
                Deposit Value
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Deposit_Val" size="25" value="0"/>        	
         
         </div>
         

   		<div class="FormInput_left">
        		
                Reference
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Reference" size="40"/>        	
         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:90px;">
        		<textarea id="Note" cols="40" rows="3"></textarea>

         </div>
		<div class="FormInput_right" style="height:90px;">
        
                 <input type="button" value="Submit" id="SubmitDeposit" onclick="SubmitDepositan()"  class="btn btn-success mt-2 mr-2" />        	

         </div>

	</div>
</div>
		

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>


<script>
$("#Reference").coolautosuggest({
		url: "reference-search?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#Reference"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {

				var Description = result.description;
				Expr = Description.split('<br>');
				$("#Reference").val(result.id);
				
			}
		}
	});
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Deposit_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");

$("#Deposit_Val").on( "keyup", function( event ) {
 
    // 1.
    var selection = window.getSelection().toString();
    if ( selection !== '' ) {
        return;
    }
 
    // 2.
    if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 ) {
        return;
    }
	 var $this = $( this );
	var input = $this.val();
	 
	// 2
	var input = input.replace(/[\D\s\._\-]+/g, "");
	 
	// 3
	input = input ? parseInt( input, 10 ) : 0;
	 
	// 4
	$this.val( function() {
		return ( input === 0 ) ? "" : input.toLocaleString( "en-US" );
	} );

} );	

 				
function getAllValues() {
    var inputValues = [];
    $('#PageCashDeposit input,select,textarea').each(function() {
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

function SubmitDepositan(){
	dapat = getAllValues();

	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "finance-cash-deposit-add-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Cash Deposit Task Created!');

				setTimeout(function(){
						
					$("#CashDepositDiv").html('');
					
					refreshdataDeposit();
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
