 <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>
 <div id="ProyeksiDetail" style="width:auto;height:340px; background-color:white;overflow:hidden"></div>
 <input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="ProyeksiDetail.toExcel('./export/generate.php');" />
 <input type="button" id="RefreshMx" NAME="RefreshMx" value="Refresh Data" class="btn btn-info mt-2 mr-2" />
 <?php if ($_GET['status'] == 'CREATED') { ?>
 	<input type="button" id="delProyeksi" NAME="delProyeksi" value="Delete Data" class="btn btn-warning mt-2 mr-2" />
 	<input type="button" id="loadcoa2" NAME="loadcoa2" value="Load From File" onclick="loadfromfile()" class="btn btn-info mt-2 mr-2" />
 	<input type="button" id="SaveDataBM" NAME="SaveDataBM" value="Update Detail" class="btn btn-success mt-2 mr-2" />
 	<input type="button" id="lockProyeksi" NAME="lockProyeksi" value="Finalize Proyeksi" onclick="finalizeProyeksi()" class="btn btn-danger mt-2 mr-2" /> :: <a href="./fileupdateproyeksi.xls">Download File Sample Upload</a>
 <?php } ?>
 </div>
 <div id='loaddivbatch'></div>

 <script>
 	var status = "<?php echo $_GET['status'] ?>";

 	function finalizeProyeksi() {
 		dhtmlx.confirm('Are you sure want to finalize now?', 'Proyeksi Management', function(r) {

 			if (r == true) {
 				$.blockUI();
 				PProyeksiDetail.sendData();
 				setTimeout(function() {
 					LoadM('loaddivbatch', 'proyeksi-cashflow-finalize-submit?ID=<?php echo $_GET['ID'] ?>');
 				}, 3000);

 			}
 		});
 	}

 	function loadcoabatch() {
 		$.blockUI();
 		LoadM('loaddivbatch', 'proyeksi-cashflow-load-batch-coa');
 	}


 	function loadfromfile() {
 		LoadM('loaddivbatch', 'proyeksi-cashflow-load-from-file');
 	}

 	ProyeksiDetail = new dhtmlXGridObject('ProyeksiDetail');
 	ProyeksiDetail.setImagePath("./dhtmlx/imgs/");
 	ProyeksiDetail.setHeader("No,CoA Number,Description,Bulan,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,Total");
 	ProyeksiDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
 	ProyeksiDetail.attachFooter("Total,#cspan,#cspan,#cspan,<div id='div_1'>{#stat_total}</div>,<div id='div_2'>{#stat_total}</div>,<div id='div_3'>{#stat_total}</div>,<div id='div_4'>{#stat_total}</div>,<div id='div_5'>{#stat_total}</div>,<div id='div_6'>{#stat_total}</div>,<div id='div_7'>{#stat_total}</div>,<div id='div_8'>{#stat_total}</div>,<div id='div_9'>{#stat_total}</div>,<div id='div_10'>{#stat_total}</div>,<div id='div_11'>{#stat_total}</div>,<div id='div_12'>{#stat_total}</div>,<div id='div_13'>{#stat_total}</div>,<div id='div_14'>{#stat_total}</div>,<div id='div_15'>{#stat_total}</div>,<div id='div_16'>{#stat_total}</div>,<div id='div_17'>{#stat_total}</div>,<div id='div_18'>{#stat_total}</div>,<div id='div_19'>{#stat_total}</div>,<div id='div_20'>{#stat_total}</div>,<div id='div_21'>{#stat_total}</div>,<div id='div_22'>{#stat_total}</div>,<div id='div_23'>{#stat_total}</div>,<div id='div_24'>{#stat_total}</div>,<div id='div_25'>{#stat_total}</div>,<div id='div_26'>{#stat_total}</div>,<div id='div_27'>{#stat_total}</div>,<div id='div_28'>{#stat_total}</div>,<div id='div_29'>{#stat_total}</div>,<div id='div_30'>{#stat_total}</div>,<div id='div_31'>{#stat_total}</div>,<div id='div_total'>{#stat_total}</div>");

 	ProyeksiDetail.setInitWidths("80,100,180,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,130,150");
 	ProyeksiDetail.setColAlign("center,left,left,left,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right");
 	ProyeksiDetail.setColTypes("ro,combo,ro,dhxCalendar,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,ron[=c3+c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15+c16+c17+c18+c19+c20+c21+c22+c23+c24+c25+c26+c27+c28+c29+c30+c31+c32+c33]");
 	ProyeksiDetail.setColSorting("int,int,str,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int");
 	if (status == 'CREATED') {
 		ProyeksiDetail.setEditable(true);
 	} else {
 		ProyeksiDetail.setEditable(false);
 	}
 	ProyeksiDetail.setDateFormat("%Y-%m");

 	ProyeksiDetail.setNumberFormat("0,000.00", 4, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 5, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 6, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 7, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 8, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 9, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 10, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 11, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 12, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 13, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 14, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 15, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 16, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 17, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 18, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 19, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 20, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 21, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 22, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 23, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 24, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 25, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 26, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 27, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 28, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 29, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 30, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 31, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 32, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 33, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 34, ".", ",");
 	ProyeksiDetail.setNumberFormat("0,000.00", 35, ".", ",");
 	ProyeksiDetail.enableMultiselect(true);

 	ProyeksiDetail.setSkin("dhx_skyblue");
 	PProyeksiDetail = new dataProcessor("finance-proyeksi-cashflow-management-detail-update");
 	PProyeksiDetail.setTransactionMode({
 		mode: "POST",
 		total: true,
 		headers: {
 			'X-CSRF-TOKEN': "{{ csrf_token() }}"
 		},
 		payload: {
 			"ID": "<?php echo $_GET['ID'] ?>"
 		}

 	})
 	PProyeksiDetail.setUpdateMode("Off");
 	PProyeksiDetail.init(ProyeksiDetail);
 	ProyeksiDetail.init();
 	combocurr = ProyeksiDetail.getColumnCombo(1);
 	combocurr.enableFilteringMode(true);
 	combocurr.load("combo-coa-all");

 	reloadDx();


 	function reloadDx() {
 		ProyeksiDetail.clearAndLoad("xml-finance-proyeksi-cashflow-management-detail?ID=<?php echo $_GET['ID'] ?>", updategrid);

 	}

 	function updategrid() {

 		var out = 0;
 		for (var i = 0; i < ProyeksiDetail.getRowsNum(); i++) {
 			rowIdMov = ProyeksiDetail.getRowId(i);
 			PProyeksiDetail.setUpdated(rowIdMov, true);
 		}

 		return out;
 	}
 	$("#SaveDataBM").click(function() {
 		PProyeksiDetail.sendData();
 		setTimeout(function() {
 			alert("Data was Saved!");
 			reloadProyeksimgt();
 		}, 2000);

 	});
 	$("#delProyeksi").click(function() {
 		dhtmlx.confirm('Are you sure want to delete?', 'Proyeksi Management', function(r) {

 			if (r == true) {
 				ProyeksiDetail.deleteSelectedRows();
 				PProyeksiDetail.sendData();
 				alert("Data were deleted!");
 				reloadDx();
 				reloadProyeksimgt();
 			}
 		});



 	});
 	$("#ADDDataBM").click(function() {
 		var idbi = ProyeksiDetail.uid();
 		ProyeksiDetail.addRow(idbi, [ProyeksiDetail.getRowsNum() + 1, '', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'], 0);
 		ProyeksiDetail.selectRow(0);

 	});


 	$("#RefreshMx").click(function() {
 		reloadDx();
 	});
 </script>