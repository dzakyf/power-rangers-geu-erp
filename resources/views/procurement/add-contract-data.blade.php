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
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD CONTRACT DATA</div>
		<div id="ContentPages">
			 <div class="FormInput_Form_Content">
   		<div class="FormInput_left" style="font-weight:bold;">
        		
                Contract No
        
        </div>
		<div class="FormInput_right" style="font-weight:bold;">
        
				<input type="text" size="40"  id="Contract_No" /> 	
         
         </div>
   		<div class="FormInput_left">
        		
                Contract Type
        
        </div>
		<div class="FormInput_right">
        
        	
            <select id="Contract_Type">
                <option>SERVICES</option>
                <option>FIXED ASSET</option>
                <option>INVENTORY</option>
            </select>
         
         
         </div> 

  		<div class="FormInput_left" style="font-weight:bold;">
        		
                Contract Title
        
        </div>
		<div class="FormInput_right" style="font-weight:bold;">
        
				<input type="text" size="80"  id="Contract_Title" /> 	
         
         </div>

		 
     <div class="FormInput_left">
            
          Contract  Date
    
        </div>
        <div class="FormInput_right">
        
            From <input type="text" size="15"  id="Start_Date" value="<?php date('Y-m-d');?>" /> 
            To <input type="text" size="15"  id="End_Date" value="<?php date('Y-m-d');?>" /> 
     
        </div>
		<div class="FormInput_left">
        		
                Recurring Type
        
        </div>
		<div class="FormInput_right">
        
        	
            <select id="Recurring_Type">
                <option onclick="ubahtampilan()">MONTHLY</option>
                <option onclick="ubahtampilan()">YEARLY</option>
            </select>
         
         
         </div> 
		<div class="FormInput_left">
            
          Recurring  Date
    
        </div>
        <div class="FormInput_right">
        
         Date   <input type="text" size="5" maxlength="2"  id="Recurring_Day" value="01" />   <select id="Recurring_Month">
                <option value="01">JANUARY</option>
                <option value="02">FEBRUARY</option>
                <option value="03">MARCH</option>
                <option value="04">APRIL</option>
                <option value="05">MAY</option>
                <option value="06">JUNE</option>
                <option value="07">JULY</option>
                <option value="08">AUGUST</option>
                <option value="09">SEPTEMBER</option>
                <option value="10">OCTOBER</option>
                <option value="11">NOVEMBER</option>
                <option value="12">DECEMBER</option>
            </select>
         
     
        </div>
		
   		<div class="FormInput_left">
        		
                Supplier
        
        </div>
		<div class="FormInput_right">
        
        	<input type="text" size="20"  id="Supplier_ID" /> <input type="text" size="40" disabled="disabled"  id="Supplier_Name" /> 
           
         
         
         </div>
         

   		<div class="FormInput_left">
        		
                Reference
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Reference" size="40"/>        	
				<input type="hidden" id="File_Document" size="40"/>        	
         
         </div>
   		<div class="FormInput_left" style="height:100px;">
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:100px;">
        		<textarea id="Note" cols="40" rows="3"></textarea>

         </div>
		 		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FILE CONTRACT</div>

			<div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
			
			
			
			<div id="divtom">
				<input type="button" id="ContractDataSubmitr" class="btn btn-success mt-2 mr-2" NAME="ContractDataSubmitr" value="Submit" />                
			</div>
			
			
			</div>
		</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>
function ubahtampilan(){
	var tipe = $("#Recurring_Type").val();
	if (tipe == 'MONTHLY'){
		
		$("#Recurring_Month").attr("disabled","disabled");
	}else{
		$("#Recurring_Month").attr("disabled",false);
	}
	
}
setTimeout(function(){
	$("#Supplier_ID").coolautosuggest({

		url:"search-supplier?Procode=1&chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#Supplier_ID"),
		width:700,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#Supplier_ID").val(result.id); 
			$("#Supplier_Name").val(result.data); 
			
			
		  }
		}
	});
},5000);
var vault = new dhx.Vault("File_Documents", {
                uploader: {
                    target: "upload-contract.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#File_Document").val(obj[0].name);	
});
vault.events.on("uploadBegin", function(obj) {
	$("#File_Document").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});
myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");



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



$("#ContractDataSubmitr").click(function() {

		$.blockUI(); 

		dapat = getAllValues();
		$.ajax({
			 headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
			 },
		
			url : "add-contract-data-submit?_token={{ csrf_token() }}",
			type: "POST",
			data : dapat,
			success: function(data, textStatus, jqXHR)
			{
				if (data == 'success'){
					alert('Contract was Created!');

					setTimeout(function(){
							
						
						$.unblockUI(); 
						location.href='po-contract-data';
					},1000);				

				}else{
					alert('error while post!');
				}
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
			$.unblockUI(); 

			 alert( "error" );
		
			}
		});			
			
				
	

});


</script>

