<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$iduser = $_REQUEST['ID'];
	$dm = DB::table("users")->where("id",$iduser)->first();
	
	
	$mainmenu = $dm->Sub_Menu;
	$role1 = DB::table("menu_lists")->where("Position",1)->get();
		
	?>

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ROLE LISTS  :: <a href='#' onclick='checkbokin()'>SELECT ALL</a></div>
					
			<div id="contentdata">
				<div class="FormInput_Form_Content">
				<?php 
					foreach($role1 as $pertama){
						$role2 = DB::table("menu_lists")->where("Position",2)->where("Parent_Menu",$pertama->Menu_ID)->get();
						$panjang = count($role2);
				?>
					
					<div style="padding-left:50px">
						 <div style="padding-left:10px">
							<?php	if (strpos($mainmenu,$pertama->Menu_ID)!== false) $check1= "checked='checked'" ; else $check1 = ""; ?>

							<input type='checkbox' id='<?php echo $pertama->Menu_ID?>' name='<?php echo $pertama->Menu_ID?>' <?php echo $check1?>>
							<?php echo $pertama->Name?>
						 </div>
							<?php
							
							
							 
									foreach($role2 as $kedua){
										$role3 = DB::table("menu_lists")->where("Position",3)->where("Parent_Menu",$kedua->Menu_ID)->get();
										$panjang2 = count($role3);
										
							?>
								 <div style="padding-left:30px">
								 <?php	if (strpos($mainmenu,$kedua->Menu_ID)!== false) $check2= "checked='checked'" ; else $check2 = ""; ?>

									<input type='checkbox' id='<?php echo $kedua->Menu_ID?>' name='<?php echo $kedua->Menu_ID?>' <?php echo $check2?> >
									<?php echo $kedua->Name?>								 
								 </div>
							<?php
										foreach($role3 as $ketiga){  ?>
											 <div style="padding-left:50px">
											 	 <?php	if (strpos($mainmenu,$ketiga->Menu_ID)!== false) $check3= "checked='checked'" ; else $check3 = ""; ?>

												<input type='checkbox' id='<?php echo $ketiga->Menu_ID?>' name='<?php echo $ketiga->Menu_ID?>'<?php echo $check3?> >
												<?php echo $ketiga->Name?>								 
											 </div>
							<?php				 
											$role4 = DB::table("menu_lists")->where("Position",4)->where("Parent_Menu",$ketiga->Menu_ID)->get();
											$panjang4= count($role4);	 
											foreach($role4 as $keempat){ 
											 ?>
											  <div style="padding-left:70px">
											   <?php	if (strpos($mainmenu,$keempat->Menu_ID)!== false) $check4= "checked='checked'" ; else $check4 = ""; ?>

												<input type='checkbox' id='<?php echo $keempat->Menu_ID?>' name='<?php echo $keempat->Menu_ID?>' <?php echo $check4?> >
												<?php echo $keempat->Name?>								 
											 </div>
							
											 
											 <?php
											}
										}
									}
									
									
							?>
					 </div>
				<?php
					}
					
					?>
					
				</div>	
				
			<div style="float:none;padding-top:30px;" id="ProformaBADiv">
				
				<input type="hidden" id="isian" NAME="isian" value="<?php echo $mainmenu?>" size="100"/>
				<input type="hidden" id="id" NAME="id" value="<?php echo $_GET['ID']?>"/>
				<input type="button" id="ProSubmt" NAME="ProSubmt" value="Save" class="btn btn-success mt-2 mr-2"/>

		</div>
		
<div id='divrolesetingaja'></div>

<script>
function checkbokin(){
	
	if (! $('input:checkbox').is('checked')) {
      $('input:checkbox').prop('checked',true);
  } else {
      $('input:checkbox').prop('checked', false);
  }      
}
function kumpulin(){	
	var isian = "";

	var selected = [];
	$('div#contentdata input[type=checkbox]').each(function() {
	   if ($(this).is(":checked")) {
				if (isian == ''){
					isian = $(this).attr("id") + ",";
				}else{
					isian = isian +  $(this).attr("id") + ",";
				}
				
	   }
	});
	
		$("#isian").val(isian);
	
}

function getAllValues() {
    var inputValues = [];
    $('#ProformaBADiv input,select,textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
        }else{
			if (type != "button" && type != "submit") {
				
            inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
        }
    })
	
    return inputValues.join('&');
} 	

$("#ProSubmt").click(function() {
	kumpulin();
	setTimeout(function(){
			
		dapat = getAllValues();
		$.ajax({
			 headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
			 },
		
			url : "credential-role-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data : dapat,
			success: function(data, textStatus, jqXHR)
			{
				if (data == 'success'){
					
					setTimeout(function(){
						alert('Credential Updated!');
						location.href='credential-role';
					},1000);			

				}else{
					alert(data);
				}
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
			 alert( "error" );
		
			}
		});
	},1000);
				
});

</script>
