<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	$datahasil = DB::table("project_documents")->where("ID",$_GET['ID'])->first();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'project_documents'");
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
					
					echo "$('#$kolom').val('$dataForm').attr('disabled','disabled');\n";
					}
				}
			

		}
	   echo "</script>";

	}
	
	
	
	?>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DOCUMENTS</div>

		<div id="ContentPages">
			 <div class="FormInput_Form_Content" >
				<div class="FormInput_left">
						
						Document Date   
				</div>
				<div class="FormInput_right">
					<input type="hidden" id="Task_ID" size="20"/>
					<input type="text" id="Document_Date" size="20"/>        	

				 
				 </div>
				<div class="FormInput_left">
						
						Document No       
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Document_No" size="40"/>        	

				 
				 </div>
				 <div class="FormInput_left">
						
						Reference No
				
				</div>
				<div class="FormInput_right">
						<input type="text" id="Reference_No" size="40"/>        	
				</div>
				
				<div class="FormInput_left">
						
					   Category Document
				
				</div>
				<div class="FormInput_right">
				
						<select id='File_Category'>
							<option>PO</option>
							<option>PKS</option>
							<option>SP1</option>
							<option>SP2</option>
							<option>SP3</option>
							<option>SPH</option>
							<option>SPPH</option>
							<option>NOKES</option>
							<option>MEMO</option>
							<option>OTHER</option>
						</select>

				 </div>
				  <div class="FormInput_left">
						
						File
				
				</div>
				<div class="FormInput_right">
				 			<input type="text" id="Paths_File" value="<?php echo $datahasil->Path_File?>" onclick="window.open('./project_file/<?php echo $datahasil->Path_File?>');" size="100" style='border:none;color:blue'/>        	
				</div>
				

		</div>
	</div>	
