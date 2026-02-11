<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->NIK;
	

$fetch = DB::table("good_receive")->where("GR_No",$_GET['ID'])->first();
	$sobjf = DB::select(DB::raw("SHOW FIELDS FROM good_receive"));

	echo "<script>";

		foreach($sobjf as $objf){
		$idobj = $objf->Field;
		$isinya = $fetch->$idobj;
			if ($idobj !='Remark'){
				echo "$('#$idobj').val('$isinya').attr('disabled','disabled'); \n";
			}
		}
	echo "</script>";


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL INCOMING</div>
<div id='PageContent'>
    <div class="FormInput_Form_Content">
   		<div class="FormInput_left" style="font-weight:bold;">
        		
                GR No
        
        </div>
		<div class="FormInput_right" style="font-weight:bold;">
        
				<input type="text" size="40" readonly="readonly" id="GR_No" class="form-control"/> 	
         
         </div>
    		<div class="FormInput_left">
        		
               PO No
        
        	</div>
			<div class="FormInput_right">
			        	<input type="text" id="PO_No_KSPS" size="30" class="form-control" />::   	<input type="hidden" id="PO_No" size="30" />
            
         
            </div>
     <div id="GRDetail2" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
  		<div class="FormInput_left">
        		
                Incoming Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Incoming_Date" size="25"  class="form-control" readonly="readonly"/>        	
         
         </div>
         

   		<div class="FormInput_left">
        		
                Reference/DO No
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Reference" size="40" class="form-control"/>        	
         
         </div>
   		<div class="FormInput_left" style="height:120px;">
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:120px;">
        		<textarea id="Note" cols="40" rows="3" class="form-control" style="background-color:#fff"><?php echo $fetch->Remark?></textarea>

         </div>
		<div class="FormInput_right" style="height:120px;">
        
                 <input type="button" value="Submit" id="SubmitDeposit" class="btn btn-success mt-2 mr-2" />        	

         </div>

	</div>
</div>

<script>
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Incoming_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");


GRDetail2 = new dhtmlXGridObject('GRDetail2');
GRDetail2.setImagePath("./dhtmlx/imgs/");
GRDetail2.setHeader("No,Category,Product Code,Name,SN,Qty PO,Qty Receive,Remain,Note,unitps,pono,supplier");
GRDetail2.setInitWidths("50,100,100,300,100,100,100,100,100,100,100,100");
GRDetail2.setColTypes("ro,ro,ro,ro,ed,ron,edn,ron,edtxt,ron,ro,ro,ro");
GRDetail2.setColAlign("center,left,left,left,center,right,right,center,center,center,center");
GRDetail2.setColSorting("int,str,str,str,str,int,int,int,str,int,str,str");
GRDetail2.setEditable(true);
GRDetail2.setNumberFormat("0,000.00",5,".",",");
GRDetail2.setNumberFormat("0,000.00",6,".",",");
GRDetail2.setNumberFormat("0,000.00",7,".",",");
GRDetail2.setNumberFormat("0,000.00",9,".",",");
GRDetail2.enableKeyboardSupport(true);
PGRDetail2 = new dataProcessor("inventory-incoming-detail-update?");
PGRDetail2.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
		  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

    })
	
PGRDetail2.setTransactionMode("POST", true);
PGRDetail2.setUpdateMode("Off");
PGRDetail2.init(GRDetail2);
GRDetail2.setSkin("dhx_skyblue");
GRDetail2.init();

GRDetail2.clearAndLoad('xml-inventory-incoming-detail?ID=<?php echo $_GET['ID'] ?>');



$('#SubmitDeposit').click(function(){
	PGRDetail2.sendData();
	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "inventory-incoming-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data Incoming Updated!');
					refreshdata();
					$("#IncomingPartsDiv").html('');
					
			}else{
				alert('Update Incoming Failed to Submit!');
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
		$('#PageContent input,select,textarea').each(function() {
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
