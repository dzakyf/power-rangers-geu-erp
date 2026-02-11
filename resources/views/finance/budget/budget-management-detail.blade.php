 <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>
<div id="BudgetDetail" style="width:auto;height:340px; background-color:white;overflow:hidden"></div>
	<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BudgetDetail.toExcel('./export/generate.php');"  />
		<input type="button" id="RefreshMx" NAME="RefreshMx" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
	<?php if ($_GET['status'] =='CREATED'){ ?>	
	<input type="button" id="delbudget" NAME="delbudget" value="Delete Data" class="btn btn-warning mt-2 mr-2"  />                
	<input type="button" id="loadcoa" NAME="loadcoa" value="Load Batch COA" onclick="loadcoabatch()" class="btn btn-info mt-2 mr-2"  />                
	<input type="button" id="loadcoa2" NAME="loadcoa2" value="Load From File" onclick="loadfromfile()" class="btn btn-info mt-2 mr-2"  />                
	<input type="button" id="ADDDataBM" NAME="ADDDataBM" value="Add COA" class="btn btn-info mt-2 mr-2"/>
	<input type="button" id="SaveDataBM" NAME="SaveDataBM" value="Update Detail" class="btn btn-success mt-2 mr-2"/>
	<input type="button" id="lockbudget" NAME="lockbudget" value="Finalize Budget" onclick="finalizebudget()" class="btn btn-danger mt-2 mr-2"/>  :: <a href="./fileupdatebudget.xls">Download File Sample Upload</a>
	<?php } ?>
</div>
<div id='loaddivbatch'></div>

<script>

var status = "<?php echo $_GET['status']?>";
function finalizebudget(){
	dhtmlx.confirm('Are you sure want to finalize now?', 'Budget Management', function(r) {

			if(r==true){
				$.blockUI();
				PBudgetDetail.sendData();
				setTimeout(function(){
				LoadM('loaddivbatch','budget-finalize-submit?ID=<?php echo $_GET['ID']?>');
				},3000);
				
			}
	});
}

function loadcoabatch(){
	$.blockUI();
	LoadM('loaddivbatch','budget-load-batch-coa');
}


function loadfromfile(){
	LoadM('loaddivbatch','budget-load-from-file');
}

BudgetDetail = new dhtmlXGridObject('BudgetDetail');
BudgetDetail.setImagePath("./dhtmlx/imgs/");
BudgetDetail.setHeader("No,CoA Number,Description,January,February,March,April,May,June,July,August,September,October,November,December,Total");
BudgetDetail.attachHeader("#rspan,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
BudgetDetail.attachFooter("Total,#cspan,#cspan,<div id='div_jan'>{#stat_total}</div>,<div id='div_feb'>{#stat_total}</div>,<div id='div_mar'>{#stat_total}</div>,<div id='div_apr'>{#stat_total}</div>,<div id='div_may'>{#stat_total}</div>,<div id='div_jun'>{#stat_total}</div>,<div id='div_jul'>{#stat_total}</div>,<div id='div_aug'>{#stat_total}</div>,<div id='div_sep'>{#stat_total}</div>,<div id='div_oct'>{#stat_total}</div>,<div id='div_nov'>{#stat_total}</div>,<div id='div_dec'>{#stat_total}</div>,<div id='div_total'>{#stat_total}</div>");

BudgetDetail.setInitWidths("80,100,180,130,130,130,130,130,130,130,130,130,130,130,130,150");
BudgetDetail.setColAlign("center,left,left,right,right,right,right,right,right,right,right,right,right,right,right,right");
BudgetDetail.setColTypes("ro,combo,ro,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,ron[=c3+c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14]");
BudgetDetail.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int");
if (status =='CREATED'){
BudgetDetail.setEditable(true);
}else{
	BudgetDetail.setEditable(false);
}
BudgetDetail.setNumberFormat("0,000.00",3,".",",");
BudgetDetail.setNumberFormat("0,000.00",4,".",",");
BudgetDetail.setNumberFormat("0,000.00",5,".",",");
BudgetDetail.setNumberFormat("0,000.00",6,".",",");
BudgetDetail.setNumberFormat("0,000.00",7,".",",");
BudgetDetail.setNumberFormat("0,000.00",8,".",",");
BudgetDetail.setNumberFormat("0,000.00",9,".",",");
BudgetDetail.setNumberFormat("0,000.00",10,".",",");
BudgetDetail.setNumberFormat("0,000.00",11,".",",");
BudgetDetail.setNumberFormat("0,000.00",12,".",",");
BudgetDetail.setNumberFormat("0,000.00",13,".",",");
BudgetDetail.setNumberFormat("0,000.00",14,".",",");
BudgetDetail.setNumberFormat("0,000.00",15,".",",");
BudgetDetail.enableMultiselect(true);

BudgetDetail.setSkin("dhx_skyblue");
PBudgetDetail = new dataProcessor("finance-budget-management-detail-update");
	PBudgetDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  	  		payload: {"ID":"<?php echo $_GET['ID'] ?>"}

    })
PBudgetDetail.setUpdateMode("Off");
PBudgetDetail.init(BudgetDetail);
BudgetDetail.init();
combocurr = BudgetDetail.getColumnCombo(1);
combocurr.enableFilteringMode(true);
combocurr.load("combo-coa-all");

reloadDx();


function reloadDx(){
	BudgetDetail.clearAndLoad("xml-finance-budget-management-detail?ID=<?php echo $_GET['ID']?>",updategrid);	

}

function updategrid(){
	
		var out = 0;
			for (var i = 0; i < BudgetDetail.getRowsNum(); i++) {
				rowIdMov=BudgetDetail.getRowId(i);
				PBudgetDetail.setUpdated(rowIdMov,true);
			}
			
			return out;
}
$("#SaveDataBM").click(function(){
		PBudgetDetail.sendData();
		setTimeout(function(){
			alert("Data was Saved!");
			reloadbudgetmgt();
		},2000);
		
});
$("#delbudget").click(function(){
	dhtmlx.confirm('Are you sure want to delete?', 'Budget Management', function(r) {

			if(r==true){
				BudgetDetail.deleteSelectedRows();
					PBudgetDetail.sendData();
					alert("Data were deleted!");
					reloadDx();
					reloadbudgetmgt();
			}
	});
		

		
});
$("#ADDDataBM").click(function(){
	var idbi =BudgetDetail.uid();
		BudgetDetail.addRow(idbi,[BudgetDetail.getRowsNum()+1,'','','0','0','0','0','0','0','0','0','0','0','0','0','0'],0);
		BudgetDetail.selectRow(0);
		
});


$("#RefreshMx").click(function(){
	reloadDx();		
});



</script>
