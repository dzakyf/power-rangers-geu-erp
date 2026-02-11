<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$data = DB::table("account_payable")->where("ID",$_GET['ID'])->first();

$kursval =1;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DATA DETAIL</div>
<div class="FormInput_Form_Content">
   		<div class="FormInput_left" style="font-weight:bold;">
        		
                Task No
        
        </div>
		<div class="FormInput_right" style="font-weight:bold;">
        
				<input class='form-control'   disabled="disabled" type="text" size="40" readonly="readonly" id="AP_No" value="<?php echo $data->Task_ID?>" /> 	
         
				<input  type="hidden" size="40" readonly="readonly" id="ID" value="<?php echo $data->ID?>" /> 	
         </div>
		 
   		<div class="FormInput_left" style="font-weight:bold;">
        		
                Invoice Type
        
        </div>
		<div class="FormInput_right" style="font-weight:bold;">
        
				<select id="tipeInv" class='form-control'>
					<option><?php echo $data->Inv_Type?></option>
					<option value="SINGLE">SINGLE</option>
					<option value="PARTIAL">PARTIAL</option>
				</select>
				&nbsp;&nbsp;
         
		  </div>
		 
    		<div class="FormInput_left">
        		
               PO No
        
        	</div>
			<div class="FormInput_right">

				<input class='form-control'  disabled="disabled" type="text" id="PO_No" value="<?php echo $data->Reference?>" size="50"/>
            </div>
   		 <div class="FormInput_left" style="font-weight:bold;">
        		
         Peruntukan       
        
        </div>
		<div class="FormInput_right" style="font-weight:bold;">
        				<input class='form-control-sm' type="text"  disabled="disabled" id="Department" size="40" value="<?php echo $data->Department?>"/>

			
				&nbsp;&nbsp;
         
		  </div>
		
		 <div class="FormInput_left" style="font-weight:bold;height:150px">
        		
         Vendor Bank Account       
        
        </div>
		<div class="FormInput_right"  style="background-color:#fff;font-weight:bold;height:150px">
        
				<input class='form-control-sm' type="text"  disabled="disabled" id="Bank_Type" size="20" value="<?php echo $data->Bank_Type?>"/>
		<br>
				Bank Name <input   disabled="disabled" class='form-control-sm' type="text" id="Bank_Name" value="<?php echo $data->Bank_Name?>"  size="30" /><Br>Acc No&nbsp;&nbsp;&nbsp;&nbsp; <input  disabled="disabled"value="<?php echo $data->Bank_Acc?>"  class='form-control-sm' type="text" id="Bank_Acc" size="15" /><br>Acc Name <input class='form-control-sm' value="<?php echo $data->Bank_Acc_Name?>"  disabled="disabled" class='form-control' type="text" id="Bank_Acc_Name" size="30" />
         
		  </div>
		<div class="FormInput_left">
        		
                DP
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control-sm' type="text"  disabled="disabled" id="DP" size="25" value="<?php echo $data->DP?>"/>
         </div>
		 <div class="FormInput_left">
        		
                Management_Fee
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control-sm' type="text"  disabled="disabled" id="Management_Fee" size="25" value="<?php echo $data->Management_Fee?>"/>
         </div>
		<div class="FormInput_left">
        		
                Delivery Costs
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control-sm' type="text"  disabled="disabled" id="Delivery_Cost" size="25" value="<?php echo $data->Delivery_Costs?>"/>
         </div>
		 <div class="FormInput_left">
        		
                PPN 
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control-sm' type="text"  disabled="disabled" id="PPN" size="25" value="<?php echo $data->PPN?>"/>
         </div>
		  <div class="FormInput_left">
        		
                PPH 
        
        </div>
		<div class="FormInput_right">
			<input class='form-control-sm' type="text"  disabled="disabled" id="PPH_Type" size="25" value="<?php echo $data->PPH_Type?>"/>	 :: 
				<input class='form-control-sm' type="text"  disabled="disabled" id="PPH" size="25" value="<?php echo $data->PPH?>"/>
         </div>
		<div class="FormInput_left">
        		
                Total Amount
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control-sm' type="text"  disabled="disabled" id="Invoice_Val" size="25" value="<?php echo $data->Total?>"/>
         </div>
         
		<div class="FormInput_left">
        		
                Tanggal Jatuh Tempo
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control'  disabled="disabled" type="text" id="JatuhTempo" size="25" value="<?php echo $data->Due_Date?>" />        	
         
         </div>
		<div class="FormInput_left">
        		
                Tanggal Terima Invoice
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control'  disabled="disabled" type="text" id="Invoice_Received_Date" size="25" value="<?php echo $data->Date_Received?>" />        	
         
         </div>
         

   		<div class="FormInput_left">
        		
                Reference/No Faktur/Invoice
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control'  disabled="disabled" type="text" id="Reference" size="40" value="<?php echo $data->Reference?>" />        	
         
         </div>
		 <div class="FormInput_left">
        		
                Tanggal Invoice
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control'  disabled="disabled" type="text" id="Inv_Vendor_Date" size="25"  value="<?php echo $data->Inv_Vendor_Date?>"/>        	
         
         </div>
		  <div class="FormInput_left">
        		
                Deskripsi Pembayaran
        
        </div>
		<div class="FormInput_right">
        
				<input class='form-control'  disabled="disabled" type="text" id="Subject_Payment" size="50"  value="<?php echo $data->Subject_Payment?>"/>        	
         
         </div>
   		<div class="FormInput_left" style="height:100px;">
        		
                Deskripsi Lampiran
        
        </div>
		<div class="FormInput_right" style="height:100px;">
        		<textarea id="Note" cols="80"  disabled="disabled" rows="5"><?php echo $data->Description?></textarea>

         </div>
		
			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LAMPIRAN FILE</div>
			File : <input readonly="readonly" class='form-control'  type="text" id="File_Document" size="50"  value="<?php echo $data->File_Document?>" onclick="window.open('./filevendor/' + this.value);"/>   
			
			
			
			
</div>
			
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PAYMENT PROCESS</div>
<div id="CashTransferDiv">
        	   <input type="hidden" disabled="disabled" value="<?php echo $data->Task_ID?>" id="Task_ID"/>
        	   <input type="hidden" disabled="disabled" value="<?php echo $data->ID?>" id="ID"/>

		<div class="FormInput_Form_Content">
    		<div class="FormInput_left">
        		
                Transfer From
        
        	</div>
			<div class="FormInput_right">
			
        	   <select id="Payment_Mode">
				 <?php
					$Query = DB::table("bank_acc")->where("Status",'ACTIVE')->get();
					foreach ($Query as $Data){
						echo "<option value='BANK|$Data->Acc_No|$Data->Bank_Acc'>$Data->Acc_No - $Data->Bank_Name / $Data->Bank_Acc</option>";	
						
					}
					
				
				?>
                
            </select>
         
            </div>
			
			<div class="FormInput_left">
					
					Total
			
			</div>
			<div class="FormInput_right">
				   <input type="hidden" disabled="disabled" value="<?php echo $data->Total?>" id="Total"/>
				Rp. <?php echo number_format($data->Total,0,".",",")?>
			</div>
			<div class="FormInput_left">
					
					Paid
			
			</div>
			
			<div class="FormInput_right">
				Rp. <?php echo number_format($data->Paid,0,".",",")?>
			</div>
			<div class="FormInput_left">
					
					Remain
			
			</div>
			
			<div class="FormInput_right">
				Rp. <?php echo number_format($data->Remain_Val,0,".",",")?>
			</div>
			<div class="FormInput_left">
        		
                Payment Date
        
			</div>
			<div class="FormInput_right">
			
					<input type="text" id="Payment_Date" size="25" value="<?php echo date("Y-m-d")?>" readonly="readonly"/>        	
			 
			 </div>
			<div class="FormInput_left">
					
					Amount to Pay
			
			</div>
			<div class="FormInput_right">
				   <input type="text" onkeydown="return isValidKey(event)"  value="<?php echo $data->Remain_Val?>" id="Payment_Value"/>
			</div>
			<div class="FormInput_left" style="height:90px;">
        		
                Note Process
        
			</div>
			<div class="FormInput_right" style="height:100px;">
					<textarea id="Payment_Note" cols="80" rows="3"></textarea>

			 </div>
			
			<div class="FormInput_right" style="height:100px;">
					<input type="button" value="Reject" id="notreject" onclick="rejectprocess();"  class="btn btn-danger mt-2 mr-2" />        	

					 <input type="button" value="Process" id="SubmitTransfer" onclick="PaymentProcess();"  class="btn btn-success mt-2 mr-2" />        	

			 </div>
			
				
		</div>
</div>		

	
		


<script>
  $(function() {
	  $('#Payment_Value').mask('#.##0', {reverse: true});
  });
function isValidKey(e)
{
    var charCode = e.keyCode || e.which;
    if (charCode == 46)
        return false;

    return true;
}
	var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Payment_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");
		
var winCOA;
var WCOA;
function WindoWCOA(text,url,width,height) {
    winCOA = new dhtmlXWindows();

	winCOA.attachViewportTo('PagesContent');
    WCOA = winCOA.createWindow("WCOA", 0, 0, width, height);
    WCOA.keepInViewport(false);
	WCOA.setText(text);
    WCOA.attachURL(url, true);
	WCOA.button("minmax1").hide();
    WCOA.button("park").hide();
	WCOA.centerOnScreen();
	WCOA.allowMove();	
}


		function sumColumn(ind) {
			var out = 0;
			for (var i = 0; i < GridCash.getRowsNum(); i++) {
				out += parseFloat(GridCash.cells2(i, ind).getValue());
			}
			return out;
		}
		function sumColumn2(ind) {
			var out = 0;
			for (var i = 0; i < GridCash.getRowsNum(); i++) {
				out += parseFloat(GridCash.cells2(i, ind).getValue()) * parseFloat(GridCash.cells2(i, 6).getValue());
			}
			return out;
		}
		

		 
		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val");
		GridCash.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"<div id='totalamount'>{#stat_total}</div>","",""]);
		GridCash.setInitWidths("70,0,100,150,300,100,100,0");
		GridCash.setColAlign("center,left,center,left,left,right,right,center");
		GridCash.setColTypes("ro,ro,ro,ed,ed,edn,ron,combo");
		GridCash.setColSorting("int,str,str,str,str,str,str");
		GridCash.setDateFormat("%Y-%m-%d"); 
		GridCash.setEditable(false);
		GridCash.setNumberFormat("0,000",5,".",",");
		GridCash.setNumberFormat("0,000",6,".",",");
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		GridCash.init();
		refreshdatadetail();

function refreshdatadetail(){
			GridCash.clearAndLoad("xml-finance-ap-management-detail?ID=<?php echo $_GET['ID'];?>");

}
function PaymentProcess(){
	
	dhtmlx.confirm('Are you sure want to process?', 'Payable Management', function(r) {
			
		if (r==true){
				SubmitTransferan();
			
			
			
		}
	})
}
function rejectprocess(){
	
	dhtmlx.confirm('Reject this Payable?', 'Payable Management', function(r) {
			
		if (r==true){
				rejectlah();
			
			
			
		}
	})
}

				
function getAllValues() {
    var inputValues = [];
    $('#CashTransferDiv input,select,textarea').each(function() {
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

function SubmitTransferan(){
	dapat = getAllValues();

	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "finance-ap-management-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Payable Payment Done!');

				setTimeout(function(){
						
					$("#divlistcustomer").html('');
					
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


function rejectlah(){
	dapat = getAllValues();

	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "finance-ap-management-detail-reject?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				alert('Payable Payment Done!');

				setTimeout(function(){
						
					$("#divlistProjectPayable").html('');
					
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

		</script>
