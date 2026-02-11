<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$dx = DB::table("contract_lists")->where("NIK",$_GET['ID'])->orderBy("Urutan","asc")->get();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'contract_lists'");
	$jumdata = count($dx);
	$jumlah = count($result);
	if ($jumdata > 0) {
		foreach($dx as $datahasil){
	
?>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA CONTRACT <?php echo $datahasil->Urutan?> OF <?php echo $jumdata?></div>

		<div id="ContentPages<?php echo $datahasil->Urutan?>">
			 <div class="FormInput_Form_Content" >
				<div class="FormInput_left">
						
						Contract No
				</div>
				<div class="FormInput_right">
						<input type="hidden" id="ID<?php echo $datahasil->Urutan?>" size="20" disabled="disabled"/> 
						<input type="text" id="Contract_No<?php echo $datahasil->Urutan?>" size="40"/>
				 </div>
				 <div class="FormInput_left">
						
						Contract Date    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Contract_Date<?php echo $datahasil->Urutan?>" size="20" readonly="readonly"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						Start Date    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Start_Date<?php echo $datahasil->Urutan?>" size="20" readonly="readonly"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						End Date       
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="End_Date<?php echo $datahasil->Urutan?>" size="20" readonly="readonly"/>        	

				 
				 </div>
				  <div class="FormInput_left">
						
					   Category
				
				</div>
				<div class="FormInput_right">
				
					<input type="text" id="Category<?php echo $datahasil->Urutan?>" size="100" readonly="readonly"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
					   Category No
				
				</div>
				<div class="FormInput_right">
						
						<input type="text" id="Category_No<?php echo $datahasil->Urutan?>" size="5" readonly="readonly"/>        	

				 
				 </div>
				 
				<div class="FormInput_left">
						
					   Status
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Status<?php echo $datahasil->Urutan?>" size="20" readonly="readonly"/>        	

				 </div>
				<div class="FormInput_left" style="height:80px;">
						
					   Note
				
				</div>
				<div class="FormInput_right" style="height:80px;">
				
						<textarea cols="100" rows="4" id="Note<?php echo $datahasil->Urutan?>"><?php echo $datahasil->Note?></textarea>        	
				 
				 </div>
				 <div class="FormInput_left">
						
						Closing By    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Close_By<?php echo $datahasil->Urutan?>" size="10" readonly="readonly"/>        	

				 
				 </div>
				 <div class="FormInput_left">
						
						Closing Date    
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Close_Date<?php echo $datahasil->Urutan?>" size="20" readonly="readonly"/>        	

				 
				 </div>
				 <div class="FormInput_left" style="height:80px;">
						
					   Closing Note
				
				</div>
				<div class="FormInput_right" style="height:80px;">
				
						<textarea cols="100" rows="4" id="Close_Note<?php echo $datahasil->Urutan?>"><?php echo $datahasil->Note?></textarea>        	
				 
				 </div>
				 
				 
			</div>
			
		</div>

<?php
			echo "<script>";


				foreach ($result as $row) {

					$kolom = $row->column_name;
					
						
						if ($datahasil->$kolom == '0000-00-00')
						{
							echo "$('#$kolom').val('');\n";
						
						}else{
							if($datahasil->$kolom != 'ID'){
							$dataForm = $datahasil->$kolom;
							$kx = $kolom.$datahasil->Urutan;
							echo "$('#$kx').val('$dataForm').attr('disabled','disabled');\n";
							}
						}
					

				}
			   echo "</script>";
		}
	}else{
		
		echo "No data Contract";
	}
	
	
?>