<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	

?>


<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW REQUISITION </div>
<form id="NewRec">
<div id="ContentPages">
    <div class="FormInput_Form_Content" >
		
		<div class="FormInput_left">
        		
              Job Position
        
        </div>
		<div class="FormInput_right">
				<input type="hidden" value="internal"/>
				<input type="text" id="Job_Position" size="50"/>        	
         
         </div>

   		<div class="FormInput_left" style="height:80px;">
        		
               Description
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Description"></textarea>        	
         
         </div>
   		<div class="FormInput_left"  style="height:80px;">
        		
               Detail Requirement
        
        </div>
		<div class="FormInput_right" style="height:80px;" >
        
				<textarea cols="100" rows="4" id="Requirement"></textarea>        	
         
         </div>
   		
   		<div class="FormInput_left">
        		
               Priority
        
        </div>
		<div class="FormInput_right">
        
        
				<select id="Priority">
                    <option>NORMAL</option>
                    <option>URGENT</option>
                </select>
         
         </div>
   		<div class="FormInput_left" >
        		
               Qty
        
        </div>
		<div class="FormInput_right" >
        
				<input type="text" id="Qty" size="10" value="1"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Open Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Start_Date" size="15" value="<?php echo date('Y-m-d');?>"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
               Close Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="End_Date" size="15" value="<?php echo date('Y-m-d');?>"/>        	
         
         </div>
  </div>
</div>
</form>

<script> 
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["PO_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

 	
</script>
