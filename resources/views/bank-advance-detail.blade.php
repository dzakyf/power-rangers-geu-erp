<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('cash_handler')->where('NIK_handler',$Saya)->first();
	if($cekakun->Account_No == ''){
		
		echo "You Don't have access to this menu!";
		exit;
	}
	
	$priv =  Auth::user()->Priv_ID;
	

	$kursval = 1;
	$bankadvance = DB::table("bank_advance")->where("Task_ID",$_GET['ID'])->first();
	$from = DB::table('cash_handler')->where('NIK_Handler',$bankadvance->Created_By)->first();


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PELUNASAN UANG MUKA</div>

	<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
	<input type="button" id="AddGridbank" NAME="AddGridbank" value="Add Detail" class="btn btn-success mt-2 mr-2"/>
	<input type="button" id="DelGridbank" NAME="DelGridbank" value="Delete" class="btn btn-danger mt-2 mr-2"  />
	<input type="button" id="SaveGridbank" NAME="SaveGridbank" value="S A V E" class="btn btn-success mt-2 mr-2" />                
	<input type="button" id="SubmitGridbank" NAME="SubmitGridbank" value="SAVE AND SUBMIT" class="btn btn-success mt-2 mr-2" />                

   
	<div id="WinCOA">
	</div>
		


<script>
		
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
			for (var i = 0; i < Gridbank.getRowsNum(); i++) {
				var cek = parseFloat(Gridbank.cells2(i, 5).getValue());
				if (cek !== 0){
					out += parseFloat(Gridbank.cells2(i, ind).getValue());
				}
			}
			return out;
		}
		function sumColumn2(ind) {
			var out = 0;
			for (var i = 0; i < Gridbank.getRowsNum(); i++) {
				out += parseFloat(Gridbank.cells2(i, ind).getValue()) * parseFloat(Gridbank.cells2(i, 6).getValue());
			}
			return out;
		}
		function DBLcekbankAdvance(rId,cInd) {
				
				if (cInd==1){
					WindoWCOA("Chart of Accounts","finance-bank-advance-detail-coa",650,350);
				}else if (cInd==8){
					WindoWCOA("Chart of Accounts","finance-bank-advance-detail-coa2",650,350);
				}
			
			
			return true;
		}
		
		function editcekbankAdvance(stage,rId,cInd,nValue,oValue) {
				
				
					
				if (cInd==5 && stage == 2 && nValue > 0){
					var nilai = Gridbank.cells(Gridbank.getSelectedId(),5).getValue();
					var	totalrem = "<?php echo $bankadvance->Val_Remain ?>";
					var	totalkumpul = sumColumn(10);
					
					var sisanya = totalrem - totalkumpul;
					if (sisanya >= nValue){
						sisanya = sisanya - nValue;
						Gridbank.cells(Gridbank.getSelectedId(),10).setValue(nValue);
						Gridbank.cells(Gridbank.getSelectedId(),11).setValue(sisanya);
						var	akunpum = "<?php echo $_GET['PUM']?>";
						var	nilai = "<?php echo $bankadvance->Val_Remain ?>";
						var	sisa = nilai;
						var referensi = "<?php echo $bankadvance->User_Reference ?>"; 
						Gridbank.cells(Gridbank.getSelectedId(),8).setValue(akunpum);
						Gridbank.cells(Gridbank.getSelectedId(),9).setValue(referensi);
					
					}else{
					    	
						var	akunpum = "<?php echo $_GET['PUM']?>";
						var	nilai = "<?php echo $bankadvance->Val_Remain ?>" - totalkumpul;
						var	sisa = (totalkumpul + nValue) - totalrem;
						var referensi = "<?php echo $bankadvance->User_Reference ?>"; 
						if (nilai < 0) nilai = 0;
						sisanya = nilai - nValue;
						Gridbank.cells(Gridbank.getSelectedId(),10).setValue(nilai);
						Gridbank.cells(Gridbank.getSelectedId(),11).setValue(sisanya);
						Gridbank.cells(Gridbank.getSelectedId(),8).setValue(akunpum);
						Gridbank.cells(Gridbank.getSelectedId(),9).setValue(referensi);
					
						akunpum = "<?php echo $from->Account_No ?>";
						sisa = nilai;
						referensi = "<?php echo $bankadvance->Created_By ?>";
						
						getid = Gridbank.getRowIndex(Gridbank.getSelectedId()) + 1 ;
						newIDRow = parseInt(Gridbank.getRowsNum());
						Gridbank.addRow(newIDRow,[getdata],getid);
						Gridbank.copyRowContent(Gridbank.getSelectedId(),newIDRow);
						Gridbank.selectRow(getid);	
						
						nilai = sisanya * -1;
						sisanya = 0;
						Gridbank.cells(Gridbank.getSelectedId(),0).setValue(Gridbank.getRowsNum());
						Gridbank.cells(Gridbank.getSelectedId(),5).setValue('0');
						Gridbank.cells(Gridbank.getSelectedId(),10).setValue(nilai);
						Gridbank.cells(Gridbank.getSelectedId(),11).setValue(sisanya);
						Gridbank.cells(Gridbank.getSelectedId(),8).setValue(akunpum);
						Gridbank.cells(Gridbank.getSelectedId(),9).setValue(referensi);
						Gridbank.lockRow(Gridbank.getSelectedId(),true);

					
						
					
					}
					
						

				}
			
			
			return true;
		}

		 
		Gridbank = new dhtmlXGridObject('grid_kbterima');
		Gridbank.setImagePath("./dhtmlx/imgs/");
		Gridbank.setHeader("No.,Nomor Akun,Tanggal,Referensi,Catatan,Nilai,Kurs Val,Departemen,Akun Lawan,Reference,Nilai,Sisa");
		Gridbank.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","","","","","#stat_total",""]);
		Gridbank.setInitWidths("70,150,100,150,200,100,100,100,100,100,100,100");
		Gridbank.setColAlign("center,left,center,left,left,right,right,center,center,center,center");
		Gridbank.setColTypes("ro,ro,dhxCalendar,ed,ed,edn,ron,combo,ro,ro,ron,ron");
		Gridbank.setColSorting("int,str,str,str,str,str,str,str,str,int,int");
		Gridbank.setDateFormat("%Y-%m-%d"); 
		Gridbank.setEditable(true);
		Gridbank.setNumberFormat("0,000",5,".",",");
		Gridbank.setNumberFormat("0,000",6,".",",");
		Gridbank.attachEvent("onRowDblClicked", DBLcekbankAdvance);
		Gridbank.attachEvent("onEditCell", editcekbankAdvance);
		Gridbank.enableKeyboardSupport(true);
		Gridbank.setSkin("dhx_skyblue");
		PGridbank = new dataProcessor("finance-bank-advance-detail-update");
		PGridbank.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"ID":"<?php echo $_GET['ID'] ?>"}

		});
		
		PGridbank.setUpdateMode("Off");
		PGridbank.init(Gridbank);
		Gridbank.init();
		combocurr = Gridbank.getColumnCombo(7);
		combocurr.enableFilteringMode(true);
		combocurr.load("xml-finance-department");
		refreshdatadetail();
		
		function refreshdatadetail(){
					Gridbank.clearAndLoad("xml-finance-bank-advance-detail?ID=<?php echo $_GET['ID'];?>",UpdateGrid);

		}
		$('#SubmitGridbank').click(function(){
			

			
			PGridbank.sendData();
			bankAdvance.cells("<?php echo $_GET['ID']?>",9).setValue('CLOSED');
			PbankAdvance.setUpdated("<?php echo $_GET['ID']?>",true);
			PbankAdvance.sendData();

			alert("Data Berhasil Disubmit!");
			setTimeout(function(){
				$("#DivbankDetail").html('');
				refreshdata();
			},1500);
									   
		});

		$('#SaveGridbank').click(function(){
									   
			PGridbank.sendData();
			alert("Data Berhasil Disimpan!");
			setTimeout(function(){
				$("#DivbankDetail").html('');
				refreshdata();
			},1500);
									   
									   });

		

		$('#AddGridbank').click(function(){
			if(Gridbank.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=Gridbank.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
					var today = new Date();
					var dd = today.getDate();
					var mm = today.getMonth()+1; //January is 0!
				
					var yyyy = today.getFullYear();		
					var tanggals = "<?php echo $_GET['Tgl']?>";
					if(dd < 10){
						dd = '0'+ dd;
					} 
					if(mm < 10){
						mm='0'+mm;
					} 
					var today = yyyy + '-' + mm + '-' + dd;
					if (tanggals == '') skrg = today ; else skrg = tanggals;

					
					Gridbank.addRow(Gridbank.uid(), [nums_reqrow,'',skrg,'','',0,'<?php echo $kursval;?>','<?php echo $bankadvance->Department;?>','','',0,0],Gridbank.getRowsNum());
					Gridbank.selectRow(nomorrowsreq);
									});

		$('#DelGridbank').click(function(){

			dhtmlx.confirm('Delete Data?', 'bank Advance', function(r) {
					
				if (r==true){
					Gridbank.deleteSelectedRows();
					PGridbank.sendData();
					setTimeout(function(){
						refreshdatadetail();
					},1000);
					
				}
			});

		});
		function UpdateGrid() {
			var out = 0;
			for (var i = 0; i < Gridbank.getRowsNum(); i++) {
				rowIdMov=Gridbank.getRowId(i);
				PGridbank.setUpdated(rowIdMov,true);
			}
			
			return out;
		}


		</script>
