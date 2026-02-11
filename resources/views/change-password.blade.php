<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->Emp_ID;

?>
@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
 <div class="main-content-header">
        <h1>Users</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Privilege</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Change Password</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CHANGE PASSWORD</div>
				<div id="ContentPages">
					 <div class="FormInput_Form_Content">
				<div class="FormInput_left" style="font-weight:bold;">
						
						User ID
				
				</div>
				<div class="FormInput_right" style="font-weight:bold;">
				
						<input type="text" size="30"  id="username" value="<?php echo $Saya?>" readonly="readonly" /> 	
				 
				 </div>
				

				<div class="FormInput_left" style="font-weight:bold;">
						
					   Old Password
				
				</div>
				<div class="FormInput_right" style="font-weight:bold;">
				
						<input type="password" size="25"  id="password" /> 	
				 
				 </div>
				<div class="FormInput_left" style="font-weight:bold;">
						
					   New Password
				</div>
				<div class="FormInput_right" style="font-weight:bold;">
				
						<input type="password" size="25"  id="New_Password" /> 	
				 
				 </div>
				<div class="FormInput_left" style="font-weight:bold;">
						
					   Confirm New Password
				</div>
				<div class="FormInput_right" style="font-weight:bold;">
				
						<input type="password" size="25"  id="Confirm_Password" /> 	
				 
				 </div>
					
					<div id="divtom">
						<input type="button" id="changepasssubmit" class="btn btn-success mt-2 mr-2" NAME="changepasssubmit" value="Change" />                
					</div>
					
					
					</div>
				</div>
				
				
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

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



$("#changepasssubmit").click(function() {

		$.blockUI(); 

		dapat = getAllValues();
		$.ajax({
			 headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
			 },
		
			url : "change-password-submit?_token={{ csrf_token() }}",
			type: "POST",
			data : dapat,
			success: function(data, textStatus, jqXHR)
			{
				if (data == 'success'){
					alert('Change Password Success!');

					setTimeout(function(){
							
						
						
						location.href='change-password';
					},1000);				
				}else if (data == 'wrongold'){
					alert('Invalid Old Password!');

				}else if (data == 'empty'){
					alert('Please complete the box!');

				}else if (data == 'notmatch'){
					alert('New Password and Confirm not Match!');

				}else{
					alert('error while change!');
				}
				$.unblockUI(); 
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
			$.unblockUI(); 

			 alert( "error" );
		
			}
		});			
			
				
	

});


</script>
</div>

@endsection
