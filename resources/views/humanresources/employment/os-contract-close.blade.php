<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$dx = DB::table("contract_lists")->where("NIK",$_GET['ID'])->where("Status",'CREATED')->orderBy("Urutan","asc")->get();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'contract_lists'");
	$jumdata = count($dx);
	$nomor = 1;
	$jumlah = count($result);
	if ($jumlah > 0) {
		foreach($dx as $datahasil){
		
?>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA CONTRACT <?php echo $nomor ?> OF <?php echo $jumdata?></div>

		<div>
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
				 
			</div>
			
		</div>
		
		
<?php
			$nomor++;
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
	}
	
	
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CLOSING ACTION</div>
<div id="isidiv">
		<input type="hidden" id='NIK' value='<?php echo $datahasil->NIK?>'/>
				<div class="FormInput_left">
						
						Close Date       
				</div>
				<div class="FormInput_right">
				
						<input type="text" id="Close_Date" size="20" readonly="readonly"/>        	

				 
				 </div>
				<div class="FormInput_left" style="height:180px;">
						
					   Note Closing
				
				</div>
				<div class="FormInput_right" style="height:180px;">
				
						<textarea cols="100" rows="4" id="Close_Note"></textarea>
						<Br>
						<input type="button" value="CLOSE CONTRACT" id="Closebutton" class="btn btn-danger mt-2 mr-2" >
				 
				 </div>
				 
				 
		</div>
<SCRIPT>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Close_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");

$('#Closebutton').click(function(){

	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "os-contract-close-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Contract was Closed!');
					refreshdata();
					$("#detailcontractdiv").html('');
					
			}else{
				alert('Contract Data Failed to Submit!');
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
		$('#isidiv input,select,textarea').each(function() {
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