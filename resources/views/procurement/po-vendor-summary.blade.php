@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Purchase Order</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Vendor Summary</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <input type="text" id="Tgl1"  size="10"  readonly="readonly" class="form-control-sm" /> To <input type="text" id="Tgl2"  size="10"  readonly="readonly"  class="form-control-sm"/>
		<input type="button" id="SubmitCheck" onclick="refreshdata()" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>

		<div id="POMgt" style="width:auto;height:550px; background-color:white;overflow:hidden"></div>

	</div>
<div id="POMgtDiv"></div>

<script>
function POMgtDoubleClick() {

	POMgtMID = POMgt.getSelectedId();
	LoadM('POMgtDiv','po-vendor-summary-detail?ID=' + POMgtMID);

}
var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

POMgt = new dhtmlXGridObject('POMgt');
POMgt.setImagePath("./dhtmlx/imgs/");
POMgt.setHeader("No.,Vendor ID,Vendor Name,Num PO,Amount,Paid,Remain");
POMgt.setInitWidths("50,100,380,100,100,100,100");
POMgt.attachHeader("&nbsp;,#text_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter,#text_filter");
POMgt.setColAlign("center,center,left,right,right,right,right,right");
POMgt.setColTypes("ro,ro,ro,ron,ron,ron,ron");
POMgt.setNumberFormat("0,000",3,".",",");
POMgt.setNumberFormat("0,000",4,".",",");
POMgt.setNumberFormat("0,000",5,".",",");
POMgt.setNumberFormat("0,000",6,".",",");
POMgt.attachEvent("onRowDblClicked", POMgtDoubleClick);
POMgt.setColSorting("int,str,str,int,int,int,int");
POMgt.setEditable(true);
POMgt.enableMultiline(true);
POMgt.setSkin("dhx_skyblue");
POMgt.init();
refreshdata();
function refreshdata(){
POMgt.clearAndLoad("xml-po-vendor-summary?Tgl1=" + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());
}
</script>
@endsection
