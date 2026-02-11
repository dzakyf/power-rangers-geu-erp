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
						
						echo "$('#$kolom2').val('$dataForm2').attr('disabled','disabled');\n";
						
					}
				

			}
		   echo "</script>";

		}
	}


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL PO OPPORTUNITY</div>

<div id="DivDetailOut">
    <div class="FormInput_Form_Content">

    		<div class="FormInput_left">
        		
                PO Number
        
        	</div>
			<div class="FormInput_right">
        	        	<input type="hidden" id="Task_ID" size="25" value="<?php echo $datahasil->Task_ID?>" />   
        	        	<input type="hidden" id="ID" size="25" />   

						<input type="text" id="PO_No" size="25"/>        	

                
         
            </div>
   		<div class="FormInput_left">
        		
                PO Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="PO_Date" size="15" value="<?php echo date("Y-m-d");?>"  />        	
         
         </div>
		 <div class="FormInput_left">
        		
                Project Type
        
        	</div>
			<div class="FormInput_right">
        	        	
				<select id="Project_Type">        	
					<option>OUTSOURCING</option>
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
        		
                Alocation
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Alocation" size="55" />        	
         
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
        		
                Discount
        
        </div>
		<div class="FormInput_right">
        
				Rp. <input type="text" id="Discount" size="25" value="0"/>        	
         
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

<div id="GridDetailPO" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	
<div id="GridDetailPO2" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			 			   

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
				 
					


<script>
$("#DivDetailOut textarea,select,input").attr('disabled','disabled');
	GridDetailPO = new dhtmlXGridObject('GridDetailPO');
		GridDetailPO.setImagePath("./dhtmlx/imgs/");
		GridDetailPO.setHeader("<b>REQUIREMENT OF REQUISITIONS</b>,#cspan,#cspan,#cspan,#cspan");
		GridDetailPO.attachHeader("No.,Department,Position,Qty Person,Total Amount(IDR)");
		GridDetailPO.attachFooter(["Total",'#cspan','#cspan','#stat_total',"#stat_total"]);
		GridDetailPO.setInitWidths("70,250,200,100,120");
		GridDetailPO.setColAlign("center,left,center,left,left,right,right,center");
		GridDetailPO.setColTypes("ro,ed,ed,ed,edn");
		GridDetailPO.setColSorting("int,str,str,str,int");
		GridDetailPO.setDateFormat("%Y-%m-%d"); 
		GridDetailPO.setEditable(true);
		GridDetailPO.setNumberFormat("0,000",4,".",",");
		GridDetailPO.enableEditEvents(true,false,true);
		GridDetailPO.enableKeyboardSupport(true);
		GridDetailPO.setSkin("dhx_skyblue");
		PGridDetailPO = new dataProcessor("opportunity-lists-detail-update");
		PGridDetailPO.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"Task_ID":"<?php echo $datahasil->Task_ID ?>"}

		})
		
		PGridDetailPO.setUpdateMode("Off");
		PGridDetailPO.init(GridDetailPO);
		GridDetailPO.init();
		refreshgrid();
		
function refreshgrid(){
		GridDetailPO.clearAndLoad("xml-opportunity-lists-detail?Task_ID=<?php echo $datahasil->Task_ID ?>");
	
}

		GridDetailPO2 = new dhtmlXGridObject('GridDetailPO2');
		GridDetailPO2.setImagePath("./dhtmlx/imgs/");
		GridDetailPO2.setHeader("<b>MATERIAL REQUESTED</b>,#cspan,#cspan,#cspan,#cspan");
		GridDetailPO2.attachHeader("No.,Product Code,Description,Qty,Estimated Costs");
		GridDetailPO2.attachFooter(["Total",'#cspan','#cspan','#stat_total',"#stat_total"]);
		GridDetailPO2.setInitWidths("70,250,200,100,120");
		GridDetailPO2.setColAlign("center,left,left,center,right,right,right,center");
		GridDetailPO2.setColTypes("ro,ed,ed,ed,edn");
		GridDetailPO2.setColSorting("int,str,str,str,int");
		GridDetailPO2.setDateFormat("%Y-%m-%d"); 
		GridDetailPO2.setEditable(true);
		GridDetailPO2.setNumberFormat("0,000",4,".",",");
		GridDetailPO2.enableEditEvents(true,false,true);
		GridDetailPO2.enableKeyboardSupport(true);
		GridDetailPO2.setSkin("dhx_skyblue");
		PGridDetailPO2 = new dataProcessor("opportunity-lists-detail-update2");
		PGridDetailPO2.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"Task_ID":"<?php echo $datahasil->Task_ID ?>"}

		})
		
		PGridDetailPO2.setUpdateMode("Off");
		PGridDetailPO2.init(GridDetailPO2);
		GridDetailPO2.init();
		refreshgrid2();
		
function refreshgrid2(){
		GridDetailPO2.clearAndLoad("xml-opportunity-lists-detail2?Task_ID=<?php echo $datahasil->Task_ID ?>");
	
}
 	
</script>
