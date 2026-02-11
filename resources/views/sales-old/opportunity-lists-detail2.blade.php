<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("opportunity")->where("ID",$_GET['ID'])->first();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'opportunity'");
	$jumlah = count($result);
	if ($jumlah > 0) {
		echo "<script>";
		
		foreach ($result as $row) {
			
			$kolom = $row->column_name;
			
				
				if ($datahasil->$kolom == '0000-00-00')
				{
					echo "$('#$kolom').val('');\n";
				
				}else{
					if($datahasil->$kolom != 'ID'){
					$dataForm = $datahasil->$kolom;
					
					echo "$('#$kolom').val('$dataForm');\n";
					}
				}
			

		}
	   echo "</script>";

	}
	
	$datahasil2 = DB::table("opportunity_detail")->where("Task_ID",$datahasil->Task_ID)->first();
	if(!$datahasil2){
		
	}else{
			
		$result2 = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'opportunity_detail'");
		$jumlah2 = count($result2);
		if ($jumlah2 > 0 && $datahasil2->ID != '') {
			echo "<script>";
			foreach ($result2 as $row2) {
				
				$kolom2 = $row2->column_name;
				
					
					if ($datahasil2->$kolom2 == '0000-00-00')
					{
						echo "$('#$kolom').val('');\n";
					
					}else{
						
						$dataForm2 = $datahasil2->$kolom2;
						
						echo "$('#$kolom2').val('$dataForm2');\n";
						
					}
				

			}
		   echo "</script>";

		}
	}


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL PO OPPORTUNITY</div>

<div id="BankTransferDiv">
    <div class="FormInput_Form_Content">

    		<div class="FormInput_left">
        		
                PO Number
        
        	</div>
			<div class="FormInput_right">

        	        	<input type="hidden" id="Task_ID" size="25" value="<?php echo $datahasil->Task_ID?>" />   

						<input type="text" id="PO_No" size="25"/>        	

                
         
            </div>
   		<div class="FormInput_left">
        		
                PO Date
        
        </div>
		<div class="FormInput_right">
        			        	        	<input type="hidden" id="ID" size="25" />   

				<input type="text" id="PO_Date" size="15" value="<?php echo date("Y-m-d");?>"  />        	
         
         </div>
		 
		 <div class="FormInput_left">
        		
                Project Type
        
        	</div>
			<div class="FormInput_right">
        	        	
				<select id="Project_Type">        	
					<option>OUTSOURCING</option>
					<option>OUTTASK</option>
					<option>BUILDING_MANAGEMENT</option>
				</select>
                
         
            </div>
         <div class="FormInput_left">
        		
                Contract No
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Contract_No" size="35" />        	
         
         </div>
		 <div class="FormInput_left">
        		
                Start Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Start_Date" size="15"  />        	
         
         </div>
		 <div class="FormInput_left">
        		
                End Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="End_Date" size="15"  />        	
         
         </div>
		<div class="FormInput_left">
        		
                Reference No
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Reference_No" size="35" />        	
         
         </div>
		 <div class="FormInput_left">
        		
                Customer Department
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Department" size="35" />        	
         
         </div>
		 <div class="FormInput_left">
        		
                Site/Location
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Alocation" size="55" />        	
         
         </div>
		 <div class="FormInput_left">
        		
                Subject
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Subject" size="50"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
                Description
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Description" size="80"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                Gross Amount
        
        </div>
		<div class="FormInput_right">
        
				Rp. <input type="text" id="Subtotal" size="20" value="0"/>        	
         
         </div>
		 <div class="FormInput_left">
        		
                PPN
        
        </div>
		<div class="FormInput_right">
		
				<select id="PPN">
					<option value='1'>Include</option>
					<option value='0'>Exclude</option>
				</select>
         
         </div>
		 
		 <div class="FormInput_left">
        		
                Net Amounts
        
        </div>
		<div class="FormInput_right">
        
				Rp. <input type="text" id="Total" size="25" value="0" />        	
         
         </div>
		  <div class="FormInput_left">
        		
                Uang Muka
        
        </div>
		<div class="FormInput_right">
        
				Rp. <input type="text" id="Uang_Muka" size="25" value="0"/>        	
         
         </div>
		  <div class="FormInput_left">
        		
                Termin
        
        </div>
		<div class="FormInput_right">
        
				<select id="Termin">
				<option>MONTHLY</option>
				<option>YEARLY</option>
				<option>WEEKLY</option>
				<option>2 TIMES</option>
				</select>
         
         </div>
   		
	</div>


<div id="GridDetailPO3" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="AddGridDetailPO3" NAME="AddGridDetailPO3" value="Add Data" class="btn btn-success mt-2 mr-2"/>
		<input type="button" id="DelGridDetailPO3" NAME="DelGridDetailPO3" value="Delete Data" class="btn btn-danger mt-2 mr-2" />
	</div>
				 			   

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL OPPORTUNITY</div>
		<div id="CPOPR">
		
				<div class="FormInput_left">
						
						Source Information
				
				</div>
				<div class="FormInput_right">
						<select id='Source_Information'>
							<option>WEBSITE</option>
							<option>AGENT</option>
							<option>COURIER</option>
							<option>CUSTOMER SERVICES</option>
							<option>BLITZ</option>
							<option>OTHER</option>
						</select>
				 
				 </div>
				<div class="FormInput_left">
						
					  Source Reference
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Source_Reference" size="40"/>        	
				 
				 </div>
				
				<div class="FormInput_left">
						
					  Subject
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Oppor_Subject" size="50" onchange="this.value.toUpper()"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Company Name
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Company_Name" size="35"/>        	
				 
				 </div>
				<div class="FormInput_left" style="height:80px;">
						
					   Address
				
				</div>
				<div class="FormInput_right" style="height:80px;">
				
						<textarea cols="100" rows="4" id="Address"></textarea>        	
				 
				 </div>
				<div class="FormInput_left" style="height:80px;">
						
					   Street
				
				</div>
				<div class="FormInput_right" style="height:80px;">
				
						<textarea cols="100" rows="4" id="Street"></textarea>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   City
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="City" size="35"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Phone
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Phone" size="35"/>        	
				 
				 </div>
				<div class="FormInput_left">
						
					   Ext
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Ext" size="35"/>        	
				 
				 </div>

				<div class="FormInput_left">
						
					   Fax
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Fax" size="35"/>        	
				 
				 </div>

				<div class="FormInput_left">
						
					   Email
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Email" size="35"/>        	
				 
				 </div>

				<div class="FormInput_left">
						
						PIC
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="PIC" size="30"/>        	
				 </div>
				<div class="FormInput_left">
						
						PIC Phone
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PIC_Phone" size="30"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						PIC Function
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="PIC_Function" size="40"/>        	

				 
				 </div>
		 		
				<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);"> OPPORTUNITY DESCRIPTION </div>
				<div class="FormInput_left">
						
						Reference
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Reference" size="50"/>        	

				 
				 </div>

				<div class="FormInput_left">
						
						Priority
				
				</div>
				<div class="FormInput_right">
						<select id='Priority'>
							<option>NORMAL</option>
							<option>HIGH</option>
							<option>HIGHEST</option>
							<option>LOW</option>
							<option>LOWEST</option>
						</select>
				 
				 </div>


				<div class="FormInput_left">
						
						Espectation Revenue
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Estimated_Rev" size="30"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						Estimated Closing Date
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Expected_Closing_Date" size="10" />        	

				 
				 </div>

				<div class="FormInput_left">
						
						Category
				
				</div>
				<div class="FormInput_right">
				
					<select id='Category'>
					<option value=""></option>
					<?php
					$q = DB::select(DB::raw("SELECT * FROM industry_type ORDER BY Industry_Type"));
					foreach ($q as $fa){
						
						echo "<option>$fa->Industry_Type</option>";	
					}
					?>
					</select>
				   <input type="hidden" id="Task_ID" size="25" value="<?php echo $datahasil->Task_ID?>" />   

				 </div>
				<div class="FormInput_left">
						
					   Sub Category
				
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Sub_Category" size="30"/>        	

				 
				 </div>

				<div class="FormInput_left" style="height:80px;">

						Note

				</div>
				<div class="FormInput_right" style="height:80px;">
						<textarea id="Note" cols="100" rows="2"></textarea>
				 </div>
		</div>
				 
				<div class="FormInput_right" style="height:175px;vertical-align:bottom;">

					<input type="button" value="Update" id="UpdatePO" onclick="UpdatePO();" class="btn btn-info mt-2 mr-2"  />        	

				</div>		


<script>


GridDetailPO3 = new dhtmlXGridObject('GridDetailPO3');
		GridDetailPO3.setImagePath("./dhtmlx/imgs/");
		GridDetailPO3.setHeader("<b>KELENGKAPAN DOKUMEN PENAGIHAN</b>,#cspan,#cspan,#cspan,#cspan");
		GridDetailPO3.attachHeader("No.,Nama Dokumen,Mandatory,Qty,Source");
		GridDetailPO3.attachFooter(["Total",'#cspan','#cspan','#stat_total',"#stat_total"]);
		GridDetailPO3.setInitWidths("70,250,100,100,120");
		GridDetailPO3.setColAlign("center,left,left,center,right,right,right,center");
		GridDetailPO3.setColTypes("ro,ed,coro,ed,coro");
		GridDetailPO3.setColSorting("int,str,str,str,int");
		GridDetailPO3.setDateFormat("%Y-%m-%d"); 
		GridDetailPO3.setEditable(true);
		GridDetailPO3.enableEditEvents(true,false,true);
		GridDetailPO3.enableKeyboardSupport(true);
		GridDetailPO3.setSkin("dhx_skyblue");
		PGridDetailPO3 = new dataProcessor("opportunity-lists-attachment-update");
		PGridDetailPO3.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"Task_ID":"<?php echo $datahasil->Task_ID ?>"}

		})
		
		PGridDetailPO3.setUpdateMode("Off");
		PGridDetailPO3.init(GridDetailPO3);
		GridDetailPO3.init();
		GridDetailPO3.getCombo(2).put("MANDATORY","MANDATORY");
		GridDetailPO3.getCombo(2).put("OPTIONAL","OPTIONAL");
		GridDetailPO3.getCombo(4).put("INTERNAL","INTERNAL");
		GridDetailPO3.getCombo(4).put("EXTERNAL","EXTERNAL");
		refreshgrid3();
		
function refreshgrid3(){
		GridDetailPO3.clearAndLoad("xml-opportunity-lists-attachment?Task_ID=<?php echo $datahasil->Task_ID ?>");
	
}


	
 

$('#AddGridDetailPO3').click(function(){
			if(GridDetailPO3.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=GridDetailPO3.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
			
			GridDetailPO3.addRow(GridDetailPO3.uid(), [nums_reqrow],GridDetailPO3.getRowsNum());
			GridDetailPO3.selectRow(nomorrowsreq);
});

$('#DelGridDetailPO3').click(function(){

	dhxml.confirm('Delete Attachment?', 'Attachment Management', function(r) {
			
		if (r==true){
			GridDetailPO3.deleteSelectedRows();
			
		}
	})

});				
function getAllValues() {
    var inputValues = [];
    $('#CPOPR input,select,textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }else{
			if (type != "button" && type != "submit") {
				
            inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
        }
    })
	
    return inputValues.join('&');
} 				
function getAllValues2() {
    var inputValues2 = [];
    $('#BankTransferDiv input,select,textarea').each(function() {
		if ($(this).attr("id") != 'Source_Information'){
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues2.push($(this).attr("id") + "=" + $(this).val());
			}else{
				if (type != "button" && type != "submit") {
					
				inputValues2.push($(this).attr("id") + "=" + $(this).val());
				}
			}
		
		}else{
				
			return false;
		}
    })
	return inputValues2.join('&');

}

function UpdatePO2(){
	dapat2 = getAllValues2();
	PGridDetailPO3.sendData();
	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "opportunity-lists-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat2,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Data Opportunity Updated!');

				setTimeout(function(){
						
					$("#divlistOpportunity").html('');
					
					refreshdata();
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

function UpdatePO(){
	dapat = getAllValues();
	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "opportunity-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				UpdatePO2();			

			}else{
				
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "error" );
	
		}
	});
	
}
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["PO_Date","Start_Date","End_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

 	
</script>
