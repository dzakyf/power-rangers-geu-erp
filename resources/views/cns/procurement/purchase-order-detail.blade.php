<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


function Dot_Conversion($Nvalue) {
    $resval = number_format((float) $Nvalue, 2, '.', ',');
	return ($resval);
}
$ID = $_GET['ID'];
$Fetch = DB::table("purchase_order_cns")->where("PO_No",$ID)->first();
$Supnya = trim($Fetch->Supplier);
$row_cek = DB::table("purchase_summary_cns")->where("Task_ID",$Fetch->Task_ID)->first();
if(trim($row_cek->V1_ID)==$Supnya) {
	$Notesnya = $row_cek->V1_Note;
}else if(trim($row_cek->V2_ID)==$Supnya){
	$Notesnya = $row_cek->V2_Note;
}else if(trim($row_cek->V3_ID)==$Supnya){
	$Notesnya = $row_cek->V3_Note;
}else if(trim($row_cek->V4_ID)==$Supnya){
	$Notesnya = $row_cek->V4_Note;
}else if(trim($row_cek->V5_ID)==$Supnya){
	$Notesnya = $row_cek->V5_Note;
}
				$row_rem = DB::table("purchase_request_cns")->where("PR_No",$Fetch->PR_No)->first();

?>
<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL PURCHASE ORDER</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                       <div class="FormInput_left" style="height:30px;">
                            
                           PO No
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo $Fetch->PO_No_KSPS; ?>       	
                     
                     </div>
                     
                    <div class="FormInput_left" style="height:30px;color:red;font-style:italic;">
                            
                           Tanggal Kirim PO
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                                <input type="text" id="TglKirim" size="15"  readonly="readonly"  style="height:20px;color:red;font-style:italic;"/>    	
                     
                     </div>
                     <div class="FormInput_left" style="height:30px;color:green;font-style:italic;">
                            
                         Tanggal Estimasi Barang Datang 
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                                       <input type="text" id="TglDatang" size="15" readonly="readonly" style="height:20px;color:green;font-style:italic;"/> 
                     
                     </div>
                     
                       <div class="FormInput_left" style="height:30px;">
                            
                           PO Date
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo $Fetch->PO_Date; ?>       	
                     
                     </div>
                       <div class="FormInput_left" style="height:30px;">
                            
                           Department
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo $row_rem->Department; ?>       	
                     
                     </div>
                       <div class="FormInput_left" style="height:30px;">
                            
                            Approved By
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo $Fetch->Process_By; ?>       	
                     
                     </div>
                       <div class="FormInput_left" style="height:30px;">
                            
                            Approved Date
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo date("d/m/Y H:i",strtotime($Fetch->Date_Trx)); ?>       	
                     
                     </div>
                       <div class="FormInput_left" style="height:30px;">
                            
                            Subtotal
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo Dot_Conversion($Fetch->Subtotal); ?>       	
                     
                     </div>
                       <div class="FormInput_left" style="height:30px;">
                            
                            PPN/Tax Article
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo Dot_Conversion($Fetch->PPN); ?>       	
                     
                     </div>
                       <div class="FormInput_left" style="height:30px;">
                            
                            PPH
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo Dot_Conversion($Fetch->PPH); ?>       	
                     
                     </div>
                       <div class="FormInput_left" style="height:30px;">
                            
                           Total
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo Dot_Conversion($Fetch->Total); ?>       	
                     
                     </div>
                     
            
                       <div class="FormInput_left" style="height:30px;">
                            
                            Supplier
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <?php echo $Fetch->Supplier; ?> - 
                                      
                     
                     </div>
                       <div class="FormInput_left">
                            
                            Note
                    
                    </div>
            
                    
                    <div class="FormInput_right" style="height:100px;">
                            <textarea style="height:98%;overflow:scroll;width:100%;" readonly="readonly" ><?php 
                            echo (str_replace("<br>","&#13;",$row_rem->Note)); ?></textarea>     	
            
                     </div>
                     <div class="FormInput_left" style="color:red;">
                            
                            <i>Keterangan Tambahan</i>
                    
                    </div>
                    <div class="FormInput_right" style="height:300px;">
                     <div id="tambahan" style="width:100%; height:300px; border:#909090 1px solid;"></div>
                     </div>
                     <!-- <div class="FormInput_left" style="color:red;">
                            
                            <i>Keterangan Tambahan</i>
                    
                    </div>
                    <div class="FormInput_right" style="height:100px;">
                            <textarea rows="3" style="height:98%;width:100%;" id="tambahan" name="tambahan"><?php echo $Notesnya;?></textarea>
                     </div> -->
                     
                     <div class="FormInput_left" style="height:180px;">
                            
                            Log Approval History
                    
                    </div>	
                    <div class="FormInput_right" style="height:180px;overflow:scroll;">
                    <textarea style="height:98%;overflow:scroll;width:100%;"  readonly="readonly"><?php //print (str_replace("<br>","&#13;",$fetch->Note)); ?><?php
                    $sql = DB::table("purchase_log")->where("Task_ID",$Fetch->PR_No)->get();
                    foreach($sql as $row){
                        echo trim($row->Date_Trx . " : " . $row->User_Trx . " => " . $row->Status) . "&#13;";
                    }
                    $sql = DB::table("purchase_log")->where("Task_ID",$Fetch->Task_ID)->get();
                    foreach($sql as $row){
                        echo trim($row->Date_Trx . " : " . $row->User_Trx . " => " . $row->Status) . "&#13;";
                    }
                    ?>
                    </textarea>
            
                     </div>
                     
                        <div class="FormInput_left" style="height:30px;">
                            
                           Cara Pembayaran
                    
                    </div>
                    <div class="FormInput_right" style="height:30px;">
                    
                            <input type="text" size="40" id="Payment" />   	
                     
                     </div>
                        <div class="FormInput_left" style="height:50px;">
                            
                           Estimation Delivery
                    
                    </div>
                    <div class="FormInput_right" style="height:50px;">
                    
                            <input type="text" value="<?php echo date("Y-m-d") ?>" id="ETB" />   <input type="button" id="SaveDoc" NAME="SaveDoc" value="Print PO"  class="cs-gradient-green cs-corner-all cs-shadow-bottom" style="width: 150px;"/>  	
                     
                     </div>
                    </div>
                    
            </div> 
        </div>
    </div>
</div>
<script>		



$("#SaveDoc").click(function() {

	var content = myEditor.getContent();
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/&/g, "dan");
	var gabung = $("#ETB").val() + '&TglKirim=' + $("#TglKirim").val() + '&TglDatang=' + $("#TglDatang").val() + '&Payment=' + $("#Payment").val() + '&Tambahan=' + content;
	var semuanya = encodeURI(gabung);
	content.replace(/&nbsp;/g, " ");
	if(($("#TglKirim").val()!='') && ($("#TglDatang").val())){
		var tipe = 1;
	}else{
		var tipe = 0;
	}
		window.open('cns-procurement-purchase-order-print?PO_No=<?php echo $Fetch->PO_No?>&tipe='+tipe+'&dat=' + semuanya);
	
});
	
var PODMGT_Cal;
PODMGT_Cal = new dhtmlXCalendarObject(["ETB","TglKirim","TglDatang"]);
PODMGT_Cal.setDateFormat("%Y-%m-%d");
POType = "<?php echo $_GET['potype'] ?>";


var myEditor = new dhtmlXEditor({
            parent: "tambahan",
            toolbar: true, //adds the toolbar
            iconsPath: "./dhtmlx/imgs/",   //path to toolbar's icons
            content: `<?= htmlspecialchars($Notesnya); ?>`
        });

</script>
		