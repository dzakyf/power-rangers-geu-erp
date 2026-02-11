<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	$pod = DB::table("invoice")->where("Inv_No",$_GET['ID'])->first();
		

function konversiketitik($nilaiangka, $kurs){
	if(strtoupper($kurs)=='RP' || strtoupper($kurs)=='IDR' || strtoupper($kurs)==''){
		if(isset($_GET->dec)=='2'){
			$titik = number_format($nilaiangka, 2, ',','.');
		}else{
			$titik = number_format($nilaiangka, 0, '','.');
		}
	}else{
	    $titik = number_format($nilaiangka, 2, ',','.');
	}
return ($titik);
}

function konversiketitikdesimal($nilaiangka, $kurs, $desimal){
	if(strtoupper($kurs)=='RP' || strtoupper($kurs)=='IDR' || strtoupper($kurs)==''){
	    $titik = number_format($nilaiangka, 0, '','.');
	}else{
	    $titik = number_format($nilaiangka, $desimal, ',','.');
	}
return ($titik);
}

function konversinpwp($nilaiangka){
    $titik = substr($nilaiangka,0,2).".".substr($nilaiangka,2,3).".".substr($nilaiangka,5,3).".".substr($nilaiangka,8,1)."-".substr($nilaiangka,9,3).".".substr($nilaiangka,-3);
return ($titik);
}


$cust = DB::table("customerz")->where("Customer_No",$pod->Account_No)->first();


if ($cust->Due_Days == 0 ) $cust->Due_Days = 30;
$hargaasli = 0;
$hargasetelahdiskon = 0;

$Sql = DB::table("proforma")->where("Invoice_No",$pod->Inv_No)->get();
foreach ($Sql as $m){
	
	if ($m->Discount != 0){
		$diskonv = 100 - $m->Discount;
		$hargaasli += ($m->Total*100)/$diskonv;
		$hargasetelahdiskon += $m->Total;
	}

}
$kontaks = DB::table("registered_branch")->where('Location',$pod->Location)->first();

$TotalDiskon = 0;
$GrossVal  = $pod->Gross_Val;
$TotalDiskon = $hargaasli - $hargasetelahdiskon;


if ($pod->Materai_Cust == 0 ) $pod->Materai = 0;
	

?>
<style type="text/css">

body{
font-family:Arial, Helvetica, sans-serif;
font-size:12px;	
}
pre {
	text-align:left;
	font-family:arial;
	height:40px;
	font-size:14px; 
	word-wrap: break-word;  
	overflow:visible;
}
.header {font-size: 10px;font-family:Arial, Helvetica, sans-serif;height:70px;padding:2px;}
.atas {font-size: 10px;font-family:Arial, Helvetica, sans-serif;border:1px solid gray;height:40px;padding:2px;}
.atasbiasa {font-size: 13px;font-family:Arial, Helvetica, sans-serif;height:50px;padding:2px;}
.subatas {font-size: 14px;font-family:Arial, Helvetica, sans-serif;border-top:3px solid gray;padding:2px;font-weight:bold}
.style2 {font-size: 10px; font-weight: bold; }

</style>
<table width="1024" height="100%" cellpadding="0" cellspacing="0">
<tr>
    <td align="left" style="padding:10px;width:350px;" valign="top" height="20" class="header">&nbsp;
       	
    </td>
    <td align="right" valign="top" class="atas" height="40" width="500" style="padding:10px;">
        <div style="padding-top:10px;float:left;text-align:left;font-size:12px">
            Jumlah Yang Harus Dibayarkan<br />
            Amount Due To Be Paid
        </div>
        <div style="padding-top:10px; text-align:right;height:50px;font-size:12px">
            Tagihan Jatuh Tempo Pembayaran<br />
            Payment Due Date
        </div>
        <div style="padding-top:10px;float:left;text-align:left" class="subatas">
        	<?php if($pod->Currency_Symbol=='') echo "IDR"; else echo "IDR";?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
			
				echo konversiketitik ($pod->Total,$pod->Currency_Symbol);
		
		
			?>
			
			</div>
			
        </div>
        <div style="padding-top:10px; text-align:right" class="subatas">
		<?php 
			echo date('d/M/Y',(($cust->Due_Days*86400)+strtotime($pod->Faktur_Date)));?>
        </div>
    </td>
</tr>
<tr>
<td align="center" style="padding-right:10px;height:auto;" valign="top">
<div style="z-index;900000;background-color:#ff00;position:absolute;top:80px;left:230px;width:260px;height:35px;text-align:right" align="right"><img src="Barcode.php?id=<?php echo $pod->Inv_No?>" style="vertical-align:middle" height="30" /></div>
<div style="padding:10px;text-align:left;height:150px;border:1px solid gray;width:450px;font-family:arial;font-size:13px; text-wrap:!important; overflow:visible;">
<pre><b><?php  echo str_replace("amp;","",$cust->Company_Name);?></b>
<br/><?php echo str_replace("amp;","",$cust->Address);?>
</pre>
</div><br />
<div style="padding-top:10px;text-align:center;height:100px;width:auto;font-family:arial;font-size:23px;">
<u><b>INVOICE</b></u><br />
FAKTUR PAJAK</b></u>
</div>
</td>
<td align="right" valign="top" class="atasbiasa">
    <div style="padding-top:10px;float:left;text-align:left;width:250px;height:18px;">
        No. Kode Pelanggan / <i>Account Number</i>
    </div>
    <div style="padding-top:10px;width:250px; text-align:left;height:18px;">
		<?php echo $pod->Account_No;?>
    </div>
    <div style="padding-top:10px;float:left;text-align:left;width:250px;height:18px;">
        No. Invoice / <i>Invoice Number</i>
    </div>
    <div style="padding-top:10px;width:250px; text-align:left;height:20px;">
	    <?php echo substr($pod->Inv_No,0,12);?>
    </div>
    <div style="padding-top:10px;float:left;text-align:left;width:250px;height:18px;">
        Tanggal Invoice / <i>Invoice Date</i>
    </div>
    <div style="padding-top:10px;width:250px; text-align:left;height:18px;">
	    <?php echo date('d/M/Y',strtotime($pod->Faktur_Date));?>
    </div>
    <div style="padding-top:10px;float:left;text-align:left;width:250px;height:18px;">
    	No. Seri Faktur Pajak / <i>Tax Serial Number</i>
    </div>
    <div style="padding-top:10px;width:250px; text-align:left;float:none;height:18px;">
    	<?php echo $pod->Faktur_No;?>
    </div>
    <div style="padding-top:10px;float:left;text-align:left;width:250px;height:18px;">
   	 	Tanggal Faktur Pajak / <i>Tax Form  Date</i>
    </div>
    <div style="padding-top:10px;width:250px; text-align:left;height:18px;">
    	<?php if(trim($cust->NPWP)<>''){ echo date('d/M/Y',strtotime($pod->Faktur_Date)); }?>
    </div>
    <div style="padding-top:10px;float:left;width:130px;text-align:left;width:130px;">
    	Nama / Name</i>
    </div>
    <div style="padding-top:10px;width:380px; text-align:left">
    	<?php 	
			echo str_replace("amp;","",$cust->VAT_Name);;
		?>
    </div>
    <div style="padding-top:10px;float:left;text-align:left;width:130px;height:50px;">
    	Alamat / Address</i>
    </div>
    <div style="padding-top:10px;height:50px; text-align:left;width:380px !important; text-wrap:suppress;font-size:12px;">
    <?php
	if(trim($cust->NPWP !='')){
			echo str_replace("amp;","",$cust->VAT_Address)."<br>";
	}
	?>    
    
    </div>
    <div style="padding-top:25px;float:left;text-align:left;width:130px;">
    	NPWP</i>
    </div>
    <div style="padding-top:25px;width:380px; text-align:left">
    <?php if(trim($cust->NPWP)<>''){ echo konversinpwp($cust->NPWP); }?><br>
    </div>
    <div style="padding-top:0px; text-align:right">
   		 <div style="padding-top:10px; text-align:left">
   			<b>For Invoice Inquiries / Question:</b>
		</div>
   		 <div style="padding-top:0px;float:left; width:100px; text-align:left">
    		Phone
        </div>
   		 <div style="padding-top:0px;idth:240px; text-align:left">
    		<?php echo $kontaks->Phone_No?>
        </div>
   		 <div style="padding-top:0px;float:left;width:100px; text-align:left">
    		Fax
        </div>
   		 <div style="padding-top:0px;padding-left:0px;float:left;padding-right:100px;  text-align:left">
    		<?php echo $kontaks->Fax_No?>
        </div>
   		 <div style="padding-top:0px;float:left;width:100px; text-align:left">
    		Printed On
        </div>
   		 <div style="padding-top:0px;width:auto;  text-align:left;overflow:hidden">
    		<?php echo date('d/M/Y');?>
        </div>
   		 <div style="padding-top:0px;float:left;width:100px; text-align:left">
    		Email
        </div>
   		 <div style="padding-top:0px;padding-left:0px;float:left;padding-right:15px; text-align:left">
    		<?php echo $kontaks->Email_Invoice?>
        </div>
   		 <div style="padding-top:0px;float:left; width:100px;text-align:left">
    		Printed By
        </div>
   		 <div style="padding-top:0px;width:auto;overflow:hidden; text-align:left">
    		<?php echo $Saya;?>
        </div>
    </div>

</td>
</tr>
<tr>
<td align="center" style="padding-top:10px;padding-left:10px;padding-right:10px;height:auto;font-weight:bold;font-size:20px;" valign="top">Total Charges</td>
<td align="center" style="padding-top:10px;padding-left:10px;padding-right:10px;height:auto;font-weight:bold;font-size:20px;" valign="top">News</td>
</tr>
<tr>
<td align="right" valign="top" class="atasbiasa" style="font-size:14px;">
    <div style="padding-top:10px;font-weight:bold;text-align:right;height:20px;">
       <?php #echo $pod->Currency_Symbol;?>IDR
    </div>
    <div style="padding-top:10px;float:left;text-align:left">
       Jasa
    </div>
    <div style="padding-top:10px;padding-left:0px; width:200px; text-align:right">
		<?php echo konversiketitik($GrossVal,$pod->Currency_Symbol);?>
    </div>
    <div style="padding-top:10px;float:left;text-align:left">
       Management Fee
    </div>
    <div style="padding-top:10px;padding-left:0px; width:200px; text-align:right">
		<?php echo konversiketitik($pod->Other_Val,$pod->Currency_Symbol);?>
    </div>
    <div style="padding-top:10px;float:left;text-align:left">
       Potongan / <i>Discount</i>
    </div>
    <div style="padding-top:10px;padding-left:0px; width:200px; text-align:right">
		<?php if($pod->Account_No!='1000000743')echo konversiketitik($pod->Discount_Val,$pod->Currency_Symbol);else echo "0";?>	
    </div>
    
    <div style="padding-top:10px;float:left;font-weight:bold;text-align:left">
       Subtotal
    </div>
    <div style="padding-top:10px;padding-left:0px;font-weight:bold; width:200px; text-align:right">
		<?php echo konversiketitik($pod->Subtotal,$pod->Currency_Symbol);?>
    </div>
	
	 <div style="padding-top:10px;float:left;font-weight:bold;text-align:left">
       
	  DPP (Dasar Pengenaan Pajak) &nbsp;&nbsp;&nbsp;<br />
       PPN / VAT (10% x DPP)
    </div>
    <div style="padding-top:10px;padding-left:0px;vertical-align:middle;font-weight:bold; width:200px; text-align:right">
		<?php echo konversiketitik($pod->PPN,$pod->Currency_Symbol);?>
    </div>
	
	 <div style="padding-top:10px;float:left;font-weight:bold;text-align:left">
       
	 &nbsp;
    </div>
    <div style="padding-top:10px;padding-left:0px;font-weight:bold; width:200px; text-align:right">
		&nbsp;
    </div>
	
   
    <div style="padding-top:10px;float:left;font-weight:normal;text-align:left;width:200px" align="left">
       Biaya Tambahan non PPN
    </div>
    <div style="padding-top:10px;padding-left:0px;font-weight:normal; width:200px; text-align:right">
		<?php echo konversiketitik($pod->Other_Val,$pod->Currency_Symbol);?>
    </div>
    
    <div style="padding-top:10px;font-weight:bold;text-align:left;border-bottom:2px solid #000">
    &nbsp;</div>
    <div style="padding-top:10px;float:left;font-weight:bold;text-align:left;width:210px;">
       Grand Total
    </div>
	<div style="padding-top:10px;padding-right:40px;float:left;font-weight:bold;height:40px; width:100px; text-align:right;vertical-align:bottom;" align="right">
		<?php if($pod->Currency_Symbol=='') echo "IDR"; else echo "IDR";#$pod->Currency_Symbol;?>
    </div>
    <div style="padding-top:10px;padding-left:0px;font-weight:bold;text-align:right" align="right">
		<?php
		
				echo konversiketitik($pod->Total,$pod->Currency_Symbol);
			
		?>
    </div>

</td>


<td align="center" valign="top" class="atasbiasa" style="font-size:14px;">
<?php if($pod->Currency_Symbol=='USD') { ?>
 <div style="padding-top:10px;font-weight:bold;text-align:left;height:20px;padding-left:100px;">
    </div>
	
 <div style="padding-top:10px;text-align:left;height:20px;padding-left:180px;">
       Kurs Pajak = ID <?php echo number_format($pod->Currency_Value,2,",",".");?>/<?php echo $pod->Currency_Symbol;?>
    </div>
<div style="padding-top:150px;"></div>

<?php } ?>
<br>

<div style="width:430px;height:300px;text-wrap:suppress;padding:10px;" align="left">
<?php echo $pod->News;?>
</div>

</td>

</tr>
<tr>
<td class="atasbiasa">
    <div style="padding-top:10px;float:left;font-weight:none;width:500px;" align="left">
Catatan : Sesuai dengan Pasal 4 Ayat 1 PMK RI No. 38/PMK.03/2010 tanggal 22 Pebruari 2010, Invoice ini berlaku sebagai Faktur Pajak 

    </div>
  </td>
  <td>  
    <div style="padding-top:10px;padding-right:0px;font-weight:bold;font-size:12px;width:500px; text-align:center" align="center">(_____________________________)<br />
		(<?php if ($_GET['TTD']!='') echo strtoupper($_GET['TTD']);else echo "IRAWAN";?>)
    </div>

</td>
</tr>
<tr>
<td align="center" colspan="2" style="border-bottom:1px solid #333">&nbsp;

</td>
</tr>
<tr>
<td align="left" style="padding-top:10px;padding-left:10px;padding-right:10px;height:auto;font-weight:bold;" valign="top"><i>Remittance Advice</i></td>
<td align="right" style="padding-top:10px;padding-left:10px;padding-right:10px;height:auto;" valign="top" class="atasbiasa">
   		 <div style="padding-top:5px;float:left; width:100px; text-align:left" align="right">
    		Payment Date
        </div>
   		 <div style="padding-top:5px;padding-left:30px; text-align:right;" align="right">
    		<input type="text" size="20" />
        
        </div>
</td>
</tr>
<tr>
<td align="left" style="padding-top:10px;padding-left:10px;padding-right:10px;height:auto;font-weight:none;" valign="top">
<div style="padding:10px;text-align:left;height:60px;font-family:arial;font-size:13px;">
<b><?php  echo str_replace("amp;","",$cust->Company_Name); ;?></b><br />
	<?php echo str_replace("amp;","",$cust->Address);?><br>
</div>
   		 <div style="padding-top:7px;text-align:left;height:10px;" align="left" class="atasbiasa">
    		For change of address, please complete the following
        </div>
   		 <div style="padding-top:5px;float:left;height:40px; width:100px; text-align:left" align="left" class="atasbiasa">
    		New Address
        </div>
   		 <div style="padding-top:5px;padding-left:10px; text-align:right;" align="right">
 		<input type="text" style="height:60px;width:80%;" />	
             </div>
   		 <div style="padding-top:5px;float:left;height:10px; width:100px; text-align:left" align="left" class="atasbiasa">
    		Contact Person
        </div>
   		 <div style="padding-top:5px;padding-left:10px;height:20px; text-align:right;" align="right">
 		<input type="text" style="height:20px;width:80%;" />	
         </div>
   		 <div style="padding-top:5px;float:left; width:100px;height:10px; text-align:left" align="left" class="atasbiasa">
    		Phone
        </div>
   		 <div style="padding-top:5px;padding-left:10px; text-align:right;" align="right">
 		<input type="text" style="height:20px;width:80%;" />	
         </div>

   		 <div style="padding-top:5px;float:left; width:100px;height:10px; text-align:left" align="left" class="atasbiasa">
    		Fax No
        </div>
   		 <div style="padding-top:5px;padding-left:10px; text-align:right;" align="right">
 		<input type="text" style="height:20px;width:80%;" />	
             </div>
   		 <div style="padding-top:5px;float:left; width:100px; text-align:left" align="left" class="atasbiasa">
    		E-Mail 
        </div>
   		 <div style="padding-top:5px;padding-left:10px; text-align:right;" align="right">
 		<input type="text" style="height:20px;width:80%;" />	
             </div>

</td>
<td align="right" style="padding-top:10px;padding-left:10px;padding-right:10px;height:auto;" valign="top" class="atasbiasa">


   		 <div style="padding-top:5px;float:left; width:100px; text-align:left" align="right">
    		Invoice Number
        </div>
   		 <div style="padding-top:5px;padding-left:30px; text-align:right;" align="right">
    		<?php echo substr($pod->Inv_No,0,12);?>
        
        </div>
   		 <div style="padding-top:5px;float:left; width:100px; text-align:left" align="right">
    		Invoice Date
        </div>
   		 <div style="padding-top:5px;padding-left:30px; text-align:right;" align="right">
	   	 <?php echo date('d/M/Y',strtotime($pod->Faktur_Date));?>
        
        </div>
   		 <div style="padding-top:5px;float:left; width:100px; text-align:left" align="right">
    		Amount Due
        </div>
   		 <div style="padding-top:5px;padding-left:30px; text-align:right;" align="right">
		<b>  <?php if($pod->Currency_Symbol=='USD') echo "IDR"; else echo "IDR";?> &nbsp;&nbsp;&nbsp; 
		<?php 
			
			echo konversiketitik($pod->Total,$pod->Currency_Symbol);
		?>
        </b>
        </div>
   		 <div style="padding-top:7px;text-align:left" align="left">
    		<Br />For transfer payment, please transfer to
        </div>
   		 <div style="padding-top:7px;text-align:left;" align="left">
	   	<b> PT GREEN ENERGY UTAMA</b>
        
        </div>
		<?php 
		
				
				$sqlb = DB::table("bank_acc")->where("Status",'ACTIVE')->get();
				foreach($sqlb as $rowb){
					?>
						<div style="padding-top:7px;text-align:left" align="left">
							<input type="text" size="1" /> <b><?php echo $rowb->Acc_Hold;?></b>
						</div>
						 <div style="padding-top:7px;padding-left:50px; font-weight:bold;text-align:left;" align="left">
						A/C No. <?php echo $rowb->Acc_No;?>
						</div>
					<?php 
					 
				}
				
				?>
			</div>
		
</td>
</tr>
</table>
</p>

<script>
window.print();
</script>
