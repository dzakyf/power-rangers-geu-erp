<?php
use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->NIK;
	
		$APNo = "GR/$LSP/".date('ymdhis');


function getSupplier($SupID){
	
	$fsup = DB::table("supplier")->where('Supplier_ID',$SupID)->first();
	
	
	if ($fsup->Supplier_ID!=''){
		
		return $fsup;	
	
	}else{
		
		return "";
	
	}
}


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW INCOMING</div>
<div id='PageContent'>
    <div class="FormInput_Form_Content">
   		<div class="FormInput_left" style="font-weight:bold;">
        		
                GR No
        
        </div>
		<div class="FormInput_right" style="font-weight:bold;">
        
				<input type="text" size="40" readonly="readonly" id="AP_No" value="<?php echo $APNo?>" /> 	
         
         </div>
    		<div class="FormInput_left">
        		
               PO No
        
        	</div>
			<div class="FormInput_right">
			        	<input type="text" id="PO_No_KSPS" size="30" />::   	<input type="hidden" id="PO_No" size="30" />
            
         
            </div>
     <div id="GRDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
  		<div class="FormInput_left">
        		
                Incoming Date
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Incoming_Date" value="<?php echo date("Y-m-d")?>" size="25" readonly="readonly"/>        	
         
         </div>
         

   		<div class="FormInput_left">
        		
                Reference/DO No
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Reference" size="40"/>        	
         
         </div>
   		<div class="FormInput_left">
        		
                Note
        
        </div>
		<div class="FormInput_right" style="height:50px;">
        		<textarea id="Note" cols="40" rows="3"></textarea>

         </div>
		<div class="FormInput_right">
        
                 <input type="button" value="Submit" id="SubmitDeposit" class="btn btn-success mt-2 mr-2" />        	

         </div>

	</div>
</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Incoming_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

var preSelectedPO = "<?php echo isset($_GET['PO_NO']) ? addslashes($_GET['PO_NO']) : ''; ?>";
if (preSelectedPO !== '') {
	$("#PO_No_KSPS").val(preSelectedPO);
	$("#PO_No").val(preSelectedPO);
}

setTimeout(function(){
	
	$("#PO_No_KSPS").coolautosuggest({
		url:"search-po?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#PO_No"),
		width:500,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#PO_No_KSPS").val(result.data); 
			$("#PO_No").val(result.id); 
			gantidata(result.id);

		  }
		}
	});
},3000);
GRDetail = new dhtmlXGridObject('GRDetail');
GRDetail.setImagePath("./dhtmlx/imgs/");
GRDetail.setHeader("No,Category,Product Code,Name,SN,Qty PO,Qty Receive,Remain,Note,unitps,pono,supplier");
GRDetail.setInitWidths("50,100,100,300,100,100,100,100,100,100,100,100");
GRDetail.setColTypes("ro,ro,ro,ro,ed,ron,edn,ron[=c5-c6],edtxt,ron,ro,ro,ro");
GRDetail.setColAlign("center,left,left,left,center,right,right,center,center,center,center");
GRDetail.setColSorting("int,str,str,str,str,int,int,int,str,int,str,str");
GRDetail.setEditable(true);
GRDetail.setNumberFormat("0,000.00",5,".",",");
GRDetail.setNumberFormat("0,000.00",6,".",",");
GRDetail.setNumberFormat("0,000.00",7,".",",");
GRDetail.setNumberFormat("0,000.00",9,".",",");
GRDetail.enableKeyboardSupport(true);
PGRDetail = new dataProcessor("inventory-incoming-new-update?");
PGRDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
		  payload: {"GR_No":"<?php echo $APNo ?>"}

    })
	
PGRDetail.setTransactionMode("POST", true);
PGRDetail.setUpdateMode("Off");
PGRDetail.init(GRDetail);
GRDetail.setSkin("dhx_skyblue");
GRDetail.init();

var preSelectedPO = '<?php echo isset($_GET['PO_NO']) ? addslashes($_GET['PO_NO']) : ""; ?>';
if (preSelectedPO !== '') { $('#PO_No_KSPS').val(preSelectedPO); $('#PO_No').val(preSelectedPO); setTimeout(function(){ gantidata(preSelectedPO); }, 500); }
function gantidata(pono){
	GRDetail.clearAndLoad('xml-inventory-incoming-new?PO_NO=' + pono);

}

$('#SubmitDeposit').click(function(){
	PGRDetail.sendData();
	gettingVars = getAllValues();

	$.ajax({
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		url : "inventory-incoming-new-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : gettingVars,
		success: function(data, textStatus, jqXHR)
		{
			var dataPos = data.indexOf("success");
			if (dataPos != '-1'){
				var cariAWB = data.split(":");
					alert('Data New Incoming Submitted!');
					refreshdata();
					$("#IncomingPartsDiv").html('');
					
			}else{
				alert('New Incoming Failed to Submit!');
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
