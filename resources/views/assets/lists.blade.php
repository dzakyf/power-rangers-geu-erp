<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder; ?>



<?php $__env->startSection('content-wrapper'); ?>
<?php echo csrf_field(); ?>
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Assets</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Data Assets</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Lists</span>
            </li>
        </ol>
    </div> 
	<div class="card">
		<div class="card-body">
			<div id="DATAASSETS" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselDATAASSETSDet" class="btn btn-warning mr-2 mt-2" value="Export Excel" onclick="DATAASSETS.toExcel('./export/generate.php');"  />
				<input type="button" id="DATAASSETS_Refresh" class="btn btn-info mr-2 mt-2" value="Refresh Data" onclick="DATAASSETSRefresh();"  />
				<input type="button" id="dxdata" class="btn btn-primary mr-2 mt-2" value="New Data" onclick="NEWDATA();"  />
				<input type="button" id="DATAASSETS_Refresh" class="btn btn-secondary mr-2 mt-2" value="Detail Data" onclick="DATAASSETSDBL();"  />
				<a href="hrd-vehicle-operations" class="btn btn-success mr-2 mt-2 font-weight-bold"><i class="fa fa-truck mr-1"></i> Logbook & Fuel Tracking</a>
			</div>
		</div>
	</div>
	<div id="DATAASSETSDTL"></div>
	
<script>

function NEWDATA() {
	LoadM('DATAASSETSDTL','asset-lists-new');
}
			
function DATAASSETSDBL() {
	TarifID = DATAASSETS.cells(DATAASSETS.getSelectedId(),1).getValue();
	LoadM('DATAASSETSDTL','asset-lists-detail?Task_ID=' + TarifID);
}

DATAASSETS = new dhtmlXGridObject('DATAASSETS');
DATAASSETS.setImagePath("./dhtmlx/imgs/");
DATAASSETS.setHeader("No,Asset No,Asset Category,Asset Sub Category,Asset Type,Asset Code,Asset Description,Brand,Sub Product Type,Manufacture Date,Registration No,Buy Date,Buy Value,Asset Location,Address,City,Departement,Vendor,Contract No,Acc No,Unit Name,Model,Bahan Bakar,CC,No Rangka,No Mesin,Jenis Kendaraan,No BPKB,No Surat Jalan,Asuransi,STNK Tahunan,STNK 5 Tahun,Jadwal KIR,Berkas KIR,Mypertamina,Barcode,Barcode PSAK,Service Terakhir,Service Berikutnya,PM Terakhir,Jadwal PM,KM Jadwal PM,Interval PM,Tgl Penghapusan,User Responsible,User Sebelumnya,Link QR,QR Aset,Status,Note");
DATAASSETS.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");

DATAASSETS.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan");
DATAASSETS.setInitWidths("60,110,120,120,100,100,200,100,100,100,100,100,110,110,150,100,110,120,100,90,90,100,90,80,130,120,130,100,110,180,100,100,150,120,90,120,130,110,110,100,110,100,90,100,120,120,150,110,90,200");
DATAASSETS.setColAlign("center,center,center,center,center,center,left,center,center,center,center,center,right,center,left,center,center,left,center,center,center,center,center,center,center,center,center,center,center,left,center,center,left,center,center,center,center,center,center,center,center,center,center,center,center,center,left,center,center,left");
DATAASSETS.setDateFormat("%Y-%m-%d"); 

DATAASSETS.setColSorting("int,str,str,str,str,str,str,str,str,date,str,date,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,date,date,str,str,str,str,str,date,date,date,date,int,int,date,str,str,str,str,str,str");
DATAASSETS.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro,date,ro,date,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,date,date,ro,ro,ro,ro,ro,date,date,date,date,ron,ron,date,ro,ro,ro,ro,ro,ro");
DATAASSETS.setNumberFormat("0,000",12,".",",");
DATAASSETS.attachEvent("onRowDblClicked", DATAASSETSDBL);
DATAASSETS.enableSmartRendering(true, 150);	
DATAASSETS.setEditable(true);
		
DATAASSETS.setSkin("dhx_skyblue");
DATAASSETS.init();

DATAASSETSRefresh();
function DATAASSETSRefresh(){
	DATAASSETS.clearAndLoad('xml-asset-lists?');
}

function SimpanPenyelesaianOrder(){
	PDATAASSETS.sendData();
	alert("Data Berhasil Disimpan");
	setTimeout(function(){
	DATAASSETSRefresh();
	},2000);
}


</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.mainlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\atha\work\hcga\resources\views/assets/lists.blade.php ENDPATH**/ ?>