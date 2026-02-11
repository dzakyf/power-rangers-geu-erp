<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$dept =  Auth::user()->Department;
	
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Project Management</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">New</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Project</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ContentPages">
			<div class="FormInput_Form_Content" >
				<div class="FormInput_left">
						
						Project Date
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Project_Date" size="10" readonly="readonly" value="<?php echo date("Y-m-d")?>"/>        	

				 
				 </div>
				
				<div class="FormInput_left">
						
					  Related Customer
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Customer_No" size="20"/> <input type="text" disabled="disabled" id="Customer_Name" readonly="readonly" size="40"/>
				 </div>
				 
				<div class="FormInput_left">
						
					  Related Vendor/Supplier
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Vendor_ID" size="20"/> <input type="text" disabled="disabled" id="Vendor_Name" readonly="readonly" size="40"/>
				 </div>
				 
				<div class="FormInput_left">
						
					  Reference
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Reference" size="40"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					  Project Name
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Project_Name" size="50" onchange="this.value.toUpper()"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Description
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Description" size="70"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Budget Available
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Budget_Available" size="20" value="0" onkeypress="return NumOnly(event)"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Estimated Revenue
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Estimated_Revenue" size="20" value="0"/>        	
				 
				 </div>
				 	<div class="FormInput_left">
						
					   Estimated Costs
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Estimated_Cost" data-inputmask="'mask': '9', 'repeat': 10, 'greedy' : false"  size="20" value="0"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Department
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Department" size="35" value="<?php echo $dept?>"/>        	
				 
				 </div>
				 <div class="FormInput_left">
						
						Target Finish Date
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Target_Finish" size="10" readonly="readonly" value="<?php echo date("Y-m-d")?>"/>        	

				 
				 </div>
								 
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn  btn-primary mt-2 mr-2" />                
			</div>
		</div>
		<div id="DivProcess"></div>

</div>

<script>

$("#Customer_No").coolautosuggest({
	url:"project-search-customer?chars=",
	showThumbnail:false,
	showDescription:true,
	idField:$("#Customer_No"),
	width:500,
	minChars:1,
	onSelected:function(result){
	  if(result!=null){
		$("#Customer_No").val(result.id); 
		$("#Customer_Name").val(result.data); 
	  }
	}
});


$("#Vendor_ID").coolautosuggest({
	url:"project-search-vendor?chars=",
	showThumbnail:false,
	showDescription:true,
	idField:$("#Vendor_ID"),
	width:500,
	minChars:1,
	onSelected:function(result){
	  if(result!=null){
		$("#Vendor_ID").val(result.id); 
		$("#Vendor_Name").val(result.data); 
	  }
	}
});
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Project_Date","Target_Finish"]);
myCalendar.setDateFormat("%Y-%m-%d");


$('#SubmitCustDetail').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "project-new-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Project Submitted!');

					location.href="project-new";
			}else{
				alert('Project Failed to Submit!');
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