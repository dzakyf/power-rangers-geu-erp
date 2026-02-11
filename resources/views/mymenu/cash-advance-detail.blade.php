<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $cekakun = DB::table('cash_handler')->where('NIK_handler', $Saya)->first();
// if ($cekakun->Account_No == '') {

// 	echo "You Don't have access to this menu!";
// 	exit;
// }

$priv =  Auth::user()->Priv_ID;


$kursval = 1;
$cashadvance = DB::table("cash_advance")->where("Task_ID", $_GET['ID'])->first();
$from = DB::table('cash_handler')->where('NIK_Handler', $cashadvance->Created_By)->first();


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PELUNASAN UANG MUKA</div>

<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
<div id="divtom">
	<input type="button" id="AddGridCash" NAME="AddGridCash" value="Add Detail" class="btn btn-success mt-2 mr-2" />
	<input type="button" id="DelGridCash" NAME="DelGridCash" value="Delete" class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="SaveGridCash" NAME="SaveGridCash" value="S A V E" class="btn btn-success mt-2 mr-2" />
	<input type="button" id="SubmitGridCash" NAME="SubmitGridCash" value="SAVE AND SUBMIT" class="btn btn-success mt-2 mr-2" />


	<div id="WinCOA">
	</div>



	<script>
		var winCOA;
		var WCOA;

		function WindoWCOA(text, url, width, height) {
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
				var cek = parseFloat(GridCash.cells2(i, 5).getValue());
				if (cek != 0) {
					out += parseFloat(GridCash.cells2(i, ind).getValue());
				}
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

		function DBLcekCashAdvance(rId, cInd) {

			if (cInd == 1) {
				WindoWCOA("Chart of Accounts", "finance-cash-advance-detail-coa", 650, 350);
			} else if (cInd == 8) {
				WindoWCOA("Chart of Accounts", "finance-cash-advance-detail-coa2", 650, 350);
			}


			return true;
		}

		function getdata(idrow, newrowid) {
			var rowcells = GridCash.copyRowContent("from_row_id", "to_row_id");
		}

		function editcekCashAdvance(stage, rId, cInd, nValue, oValue) {



			if (cInd == 5 && stage == 2 && nValue > 0) {
				var nilai = GridCash.cells(GridCash.getSelectedId(), 5).getValue();
				var totalrem = "<?php echo $cashadvance->Val_Remain ?>";
				var totalkumpul = sumColumn(10);

				var sisanya = totalrem - totalkumpul;
				if (sisanya >= nValue) {
					sisanya = sisanya - nValue;
					GridCash.cells(GridCash.getSelectedId(), 10).setValue(nValue);
					GridCash.cells(GridCash.getSelectedId(), 11).setValue(sisanya);
					var akunpum = "<?php echo $_GET['PUM'] ?>";
					var nilai = "<?php echo $cashadvance->Val_Remain ?>";
					var sisa = nilai;
					var referensi = "<?php echo $cashadvance->User_Reference ?>";
					GridCash.cells(GridCash.getSelectedId(), 8).setValue(akunpum);
					GridCash.cells(GridCash.getSelectedId(), 9).setValue(referensi);

				} else {

					var akunpum = "<?php echo $_GET['PUM'] ?>";
					var nilai = "<?php echo $cashadvance->Val_Remain ?>" - totalkumpul;
					var sisa = (totalkumpul + nValue) - totalrem;
					var referensi = "<?php echo $cashadvance->User_Reference ?>";
					if (nilai < 0) nilai = 0;
					sisanya = nilai - nValue;
					GridCash.cells(GridCash.getSelectedId(), 10).setValue(nilai);
					GridCash.cells(GridCash.getSelectedId(), 11).setValue(sisanya);
					GridCash.cells(GridCash.getSelectedId(), 8).setValue(akunpum);
					GridCash.cells(GridCash.getSelectedId(), 9).setValue(referensi);

					akunpum = "<?php echo $from->Account_No ?>";
					sisa = nilai;
					referensi = "<?php echo $cashadvance->Created_By ?>";

					getid = GridCash.getRowIndex(GridCash.getSelectedId()) + 1;
					newIDRow = parseInt(GridCash.getRowsNum());
					GridCash.addRow(newIDRow, [getdata], getid);
					GridCash.copyRowContent(GridCash.getSelectedId(), newIDRow);
					GridCash.selectRow(getid);

					nilai = sisanya * -1;
					sisanya = 0;
					GridCash.cells(GridCash.getSelectedId(), 0).setValue(GridCash.getRowsNum());
					GridCash.cells(GridCash.getSelectedId(), 5).setValue('0');
					GridCash.cells(GridCash.getSelectedId(), 10).setValue(nilai);
					GridCash.cells(GridCash.getSelectedId(), 11).setValue(sisanya);
					GridCash.cells(GridCash.getSelectedId(), 8).setValue(akunpum);
					GridCash.cells(GridCash.getSelectedId(), 9).setValue(referensi);
					GridCash.lockRow(GridCash.getSelectedId(), true);




				}



			}


			return true;
		}


		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Nomor Akun,Tanggal,Referensi,Catatan,Nilai,Kurs Val,Departemen,Akun Lawan,Reference,Nilai,Sisa");
		GridCash.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', "#stat_total", "", "", "", "", "#stat_total", ""]);
		GridCash.setInitWidths("70,150,100,150,200,100,100,100,100,100,100,100");
		GridCash.setColAlign("center,left,center,left,left,right,right,center,center,center,center");
		GridCash.setColTypes("ro,ro,dhxCalendar,ed,ed,edn,ron,combo,ro,ro,ron,ron");
		GridCash.setColSorting("int,str,str,str,str,str,str,str,str,int,int");
		GridCash.setDateFormat("%Y-%m-%d");
		GridCash.setEditable(true);
		GridCash.setNumberFormat("0,000", 5, ".", ",");
		GridCash.setNumberFormat("0,000", 6, ".", ",");
		GridCash.setNumberFormat("0,000", 10, ".", ",");
		GridCash.setNumberFormat("0,000", 11, ".", ",");
		GridCash.attachEvent("onRowDblClicked", DBLcekCashAdvance);
		GridCash.attachEvent("onEditCell", editcekCashAdvance);
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		PGridCash = new dataProcessor("finance-cash-advance-detail-update");
		PGridCash.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"ID": "<?php echo $_GET['ID'] ?>"
			}

		})

		PGridCash.setUpdateMode("Off");
		PGridCash.init(GridCash);
		GridCash.init();
		combocurr = GridCash.getColumnCombo(7);
		combocurr.enableFilteringMode(true);
		combocurr.load("xml-finance-department");
		refreshdatadetail();

		function refreshdatadetail() {
			GridCash.clearAndLoad("xml-finance-cash-advance-detail?ID=<?php echo $_GET['ID']; ?>", UpdateGrid);

		}
		$('#SubmitGridCash').click(function() {



			PGridCash.sendData();
			CashAdvance.cells("<?php echo $_GET['ID'] ?>", 9).setValue('CLOSED');
			PCashAdvance.setUpdated("<?php echo $_GET['ID'] ?>", true);
			PCashAdvance.sendData();

			alert("Data Berhasil Disubmit!");
			setTimeout(function() {
				$("#DivCashDetail").html('');
				refreshdata();
			}, 1500);

		});

		$('#SaveGridCash').click(function() {

			PGridCash.sendData();
			alert("Data Berhasil Disimpan!");
			setTimeout(function() {
				$("#DivCashDetail").html('');
				refreshdata();
			}, 1500);

		});



		$('#AddGridCash').click(function() {
			if (GridCash.getRowsNum() == 0) {
				nums_reqrow = 1;
				nomorrowsreq = 0;
			} else {
				nums_reqrow = GridCash.getRowsNum() + 1;
				nomorrowsreq = nums_reqrow - 1;
			}
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth() + 1; //January is 0!

			var yyyy = today.getFullYear();
			var tanggals = "<?php echo $_GET['Tgl'] ?>";
			if (dd < 10) {
				dd = '0' + dd
			}
			if (mm < 10) {
				mm = '0' + mm
			}
			var today = yyyy + '-' + mm + '-' + dd;
			if (tanggals == '') skrg = today;
			else skrg = tanggals;


			GridCash.addRow(GridCash.uid(), [nums_reqrow, '', skrg, '', '', 0, '<?php echo $kursval; ?>', '<?php echo $cashadvance->Department; ?>', '', '', 0, 0], GridCash.getRowsNum());
			GridCash.selectRow(nomorrowsreq);
		});

		$('#DelGridCash').click(function() {

			dhtmlx.confirm('Hapus Data?', 'Cash Advance', function(r) {

				if (r == true) {
					GridCash.deleteSelectedRows();



				}
			})

		});

		function UpdateGrid() {
			var out = 0;
			for (var i = 0; i < GridCash.getRowsNum(); i++) {
				rowIdMov = GridCash.getRowId(i);
				PGridCash.setUpdated(rowIdMov, true);
			}

			return out;
		}
	</script>