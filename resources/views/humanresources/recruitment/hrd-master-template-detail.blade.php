<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	
$datahasil = DB::table("recruitment_master_template")->where("ID",$_GET['ID'])->first();
		echo "<script>";
		$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'recruitment_master_template'");
	$jumlah = count($result);
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

	
?>


<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TEMPLATE EDITOR</div>
<form id="NewRec">
<div id="ContentPages">
    <div class="FormInput_Form_Content" >
		
		<div class="FormInput_left">
        		
              Template ID
        
        </div>
		<div class="FormInput_right">
				<input type="text" id="Template_ID" disabled="disabled" size="20"/>        	
         
         </div>

   		<div class="FormInput_left" style="height:80px;">
        		
               Message Type
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<select id="Message_Type"><option>EMAIL</option><option>SMS</option></select>        	
         
         </div>
   		<div class="FormInput_left"  style="height:380px;">
        		
               Message
        
        </div>
		<div class="FormInput_right" style="height:380px;" >
        Note : code replacement of candidate name : #_namakandidat_# <Br>
		 <div id="tambahan" style="width:100%; height:300px; border:#909090 1px solid;"></div>
		<input type="button" id="SaveDoc" NAME="SaveDoc" value="Submit"  class="cs-gradient-green cs-corner-all cs-shadow-bottom" style="width: 150px;"/>  	
         </div>
		 
   		
  </div>
</div>
</form>
<div id="loadx"></div>
<script> 

$("#SaveDoc").click(function() {

	var content = myEditor.getContent();
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/&/g, "dan");
	content.replace(/&nbsp;/g, " ");
	var gabung = 'Template_ID=' + $("#Template_ID").val() + '&Message_Type=' + $("#Message_Type").val() + '&Tambahan=' + content;
		var semuanya = encodeURI(gabung);

		$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "hrd-master-template-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : semuanya,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				
				alert('Templated Updated!');
				refreshdata();
				$('#divlistRecruitment').html('');
				
			}else{
				alert('Update Template Failed to Submit!');
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "Error Internal! Please Try again!" );
	
		}
	});
	
});
var myEditor = new dhtmlXEditor({
            parent: "tambahan",
            toolbar: true, //adds the toolbar
            iconsPath: "./dhtmlx/imgs/",   //path to toolbar's icons
            content: ""
        });
</script>


<?php
echo "<script>setTimeout(function(){";
echo "	myEditor.setContent('$datahasil->Message');";

echo "},3000);</script>";
?>
