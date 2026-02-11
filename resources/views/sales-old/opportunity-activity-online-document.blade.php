<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Sales</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Activity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Document Upload</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div>
			<form id="targets" action="post">
			   <div class="FormInput_Form_Content">
					<div class="FormInput_left">
							
						  Task ID
					
					</div>
					<div class="FormInput_right">
					
						<select id="Task_ID">
							<option value="">Select</option>
							<?php
								$Query = DB::table("opportunity")->where("Created_By",$Saya)->where("Status","!=","'CLOSED'")->get();
								foreach ($Query as $Data) {
									echo "<option value='$Data->Task_ID'>$Data->Company_Name - $Data->Oppor_Subject - $Data->Task_ID</option>";	
								}
							?>
						</select>
					 
					 </div>
					<div class="FormInput_left">
							
						   Also Email To 
					
					</div>
					<div class="FormInput_right">
					
							<input type="text" id="EmailTo" size="40"/> (use , to multi dest)        	
					 
					 </div>
					<div class="FormInput_left">
							
						   Email Subject
					
					</div>
					<div class="FormInput_right">
					
							<input type="text" id="EmailSubject" size="60"/>         	
					 
					 </div>
					 
				</div>
				</form>
			</div>
			<div id="editorObj" class="dhx_cal_container"  style="width:100%; height:300px; border:#909090 1px solid;"></div>
				<div>
					<input type="reset" id="DelCustDetail" NAME="DelCustDetail" value="Reset" onclick="resetkan();" />
					<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" onclick="simpans();" value="Submit" />                
				</div>

				<div id="DivProcess"></div>

		
		</div>	
	</div>
<script>

		var myEditor;
		function doOnLoad() {
			myEditor = new dhtmlXEditor({
				parent: "editorObj",
				toolbar: true, // force dhtmlxToolbar using
				iconsPath: "./dhtmlx/imgs/", // path for toolbar icons
			})
		}
		doOnLoad();
		function resetkan(){
			
		myEditor.setContent('');
			document.getElementById('targets').reset();
			
		}
		function simpans(){
		dataeditor = myEditor.getContent(); 
		params = "DATAPOST=" + $("#Task_ID").val() + "|" +  $("#EmailTo").val() + "|" + $("#EmailSubject").val() + "|" + dataeditor;
		success = "berhasil";
		$.ajax({
		  type: "POST",
		  url: 'sales-opportunity-activity-online-document-submit?_token={{ csrf_token() }}',
		  data: params,
		  success: success,
		  dataType: "html"
		});

		
		}

</script>
@endsection