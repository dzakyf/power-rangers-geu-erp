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
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Recruitment</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">New Data</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
			
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW RECRUITMENT</div>
	
		<div id="ContentPages">
			<div class="FormInput_Form_Content" >
			
			<div class="FormInput_left">
        		
               Position 
        
        </div>
		<div class="FormInput_right">
        		<select id='Requisition_ID'>
					<option value=""></option>
					<?php
					$q = DB::table("requisition")->where("Status","CREATED")->orderBy("ID","DESC")->get();
					foreach ($q as $fa){
							echo "<option value='$fa->Task_ID'>$fa->Requisition_Type - $fa->PO_No - $fa->PO_Desc</option>";	
					}
					?>
					</select>
                
         
         </div>
		 
		 
				<div class="FormInput_left">
        		
              Name of Candidate
        
        </div>
		<div class="FormInput_right">
      
				<input type="text" id="Name" size="50"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Gender
        
        </div>
		<div class="FormInput_right">
        
        
				<select id="Gender">
                    <option>MALE</option>
                    <option>FEMALE</option>
                </select>
         
         </div>
   		<div class="FormInput_left">
        		
               Birth Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Birth_Date" size="15" value="<?php echo date('1990-m-d');?>"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Birth Place
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Birth_Place" size="35"/>        	
         
         </div>
   		<div class="FormInput_left" >
        		
               Citizen ID Card/KTP
        
        </div>
		<div class="FormInput_right" >
        
				<input type="text" id="KTP" size="35"/>        	
         
         </div>

   		<div class="FormInput_left" style="height:80px;">
        		
               Address
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Address"></textarea>        	
         
         </div>
   		<div class="FormInput_left">
        		
               City
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="City" size="35"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Handphone
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Phone" size="35"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               WhatsApp No
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Handphone" size="35"/>        	
         
         </div>
   		<div class="FormInput_left" >
        		
               Marital Status
        
        </div>
		<div class="FormInput_right" >
        
				<select id="Marital_Status">
                    <option>SINGLE</option>
                    <option>MARRIED</option>
                    <option>WIDOWER</option>
                    <option>WIDOW</option>
				</select>
         
         </div>
   		<div class="FormInput_left" >
        		
               Last Education From
        
        </div>
		<div class="FormInput_right" >
        
				<select id="Last_Education">
                    <option>SMA/SMK</option>
                    <option>D1</option>
                    <option>D2</option>
                    <option>D3</option>
                    <option>D4</option>
                    <option>S1</option>
                    <option>S2</option>
                    <option>S3</option>
                </select>
                 Name        	
				<input type="text" id="Last_Education_From" size="45"/>        	
         
         </div>
         <div class="FormInput_left">
        		
               Email Address
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Email" size="35"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Religion
        
        </div>
		<div class="FormInput_right">
        
				<select id="Religion">
                    <option>ISLAM</option>
                    <option>PROTESTAN</option>
                    <option>KATHOLIK</option>
                    <option>BUDHA</option>
                    <option>HINDU</option>
                </select>

         
         </div>
   		<div class="FormInput_left">
        		
               Citizenship
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="kewarganegaraan" size="35"/>        	
         
         </div>

    </div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" />                
			</div>
		</div>
		<div id="DivProcess"></div>

</div>

<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Expected_Closing_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");


$('#SubmitCustDetail').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "recruitment-new-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data New Recruitment Submitted!');

					location.href="recruitment-new";
			}else{
				alert('Recruitment Failed to Submit!');
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


 
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Birth_Date","End_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");


</script>
@endsection