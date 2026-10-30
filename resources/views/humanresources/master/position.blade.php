@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master Data</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Position</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="MaterPosition" style="width:auto;height:520px; background-color:white;overflow:hidden"></div>
		 <div class="col-12 mt-2">
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MaterPosition.toExcel('./export/generate.php');" />
			<input type="button" id="RefreshBM" name="RefreshBM" value="Refresh Data" class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" name="DeleteBM" value="Delete" class="btn btn-danger mt-2 mr-2" />                
			<input type="button" id="AddBM" name="AddBM" value="Add Position" class="btn btn-primary mt-2 mr-2" />
			<input type="button" id="SaveBM" name="SaveBM" value="Save Changes" class="btn btn-success mt-2 mr-2" />
		 </div>
	</div>

	<div id="MaterPositionDiv"></div>

<script>
MaterPosition = new dhtmlXGridObject('MaterPosition');
MaterPosition.setImagePath("./dhtmlx/imgs/");
MaterPosition.setHeader("No,Directorat,Department,Subsection,Position,Initial Code,Initial,Level,Parent Position,Location,Company,Type,Status");
MaterPosition.attachHeader("#rspan,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter");
MaterPosition.setInitWidths("50,140,160,140,220,100,100,70,220,100,80,110,90");
MaterPosition.setColAlign("center,left,left,left,left,center,center,center,left,center,center,center,center");
MaterPosition.setColTypes("ro,combo,combo,ed,ed,ed,ed,ed,combo,ed,ed,combo,combo");
MaterPosition.setColSorting("int,str,str,str,str,str,str,int,str,str,str,str,str");
MaterPosition.setEditable(true);
MaterPosition.setSkin("dhx_skyblue");

PMaterPosition = new dataProcessor("master-position-update");
PMaterPosition.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
PMaterPosition.setUpdateMode("Off");
PMaterPosition.init(MaterPosition);
MaterPosition.init();

// Combos
comboDir = MaterPosition.getColumnCombo(1);
comboDir.enableFilteringMode(true);
comboDir.load("xml-combo-directorat");

comboDept = MaterPosition.getColumnCombo(2);
comboDept.enableFilteringMode(true);
comboDept.load("xml-combo-department");

comboParent = MaterPosition.getColumnCombo(8);
comboParent.enableFilteringMode(true);
comboParent.load("xml-combo-parent-position");

comboType = MaterPosition.getColumnCombo(11);
comboType.addOption([
    ['STRUCTURAL', 'STRUCTURAL'],
    ['FUNCTIONAL', 'FUNCTIONAL']
]);

comboStatus = MaterPosition.getColumnCombo(12);
comboStatus.addOption([
    ['ACTIVE', 'ACTIVE'],
    ['INACTIVE', 'INACTIVE']
]);

reloadgroup();

function reloadgroup(){
	MaterPosition.clearAndLoad("xml-hrd-master-position");	
}

$("#SaveBM").click(function(){
	PMaterPosition.sendData();
	alert("Data was Saved!");
	setTimeout(() => {
		reloadgroup();
	}, 600);
});

$("#DeleteBM").click(function(){
	if (confirm("Apakah Anda yakin ingin menghapus data posisi terpilih?")) {
		MaterPosition.deleteSelectedRows();
		PMaterPosition.sendData();
		alert("Data was deleted!");
		reloadgroup();
	}
});

$("#AddBM").click(function(){
	var idbi = MaterPosition.uid();
	// [No, Directorat, Department, Subsection, Position, Initial Code, Initial, Level, Parent Position, Location, Company, Type, Status]
	MaterPosition.addRow(idbi, [MaterPosition.getRowsNum() + 1, '', '', '', '', '', '', '4', '', 'GEU-HO', 'GEU', 'STRUCTURAL', 'ACTIVE'], 0);
	MaterPosition.selectRow(0);
});

$("#RefreshBM").click(function(){
	reloadgroup();		
});
</script>

@endsection
