@extends('layout.mainlayout-mitra')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>INVENTORY</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="./">Unused Crude Oil (UCO)</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">OUTBOUND</span>
        </li>
    </ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
    <div id="OutboundTimbang" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
    <input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="OutboundTimbang.toExcel('./export/generate.php');" />
</div>



</div>
<script>
    OutboundTimbang = new dhtmlXGridObject('OutboundTimbang');
    OutboundTimbang.setImagePath("./dhtmlx/imgs/");
    OutboundTimbang.setHeader("No,Code,Point of Origin (POO),Weight-in,Unit,Picker,Dibuat Pada,Outbound Pickup");
    OutboundTimbang.setInitWidths("80,150,150,100,100,150,150,150,150");
    OutboundTimbang.setColAlign("center,center,left,center,left,center,left,center,center,center,center");
    OutboundTimbang.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    OutboundTimbang.setColSorting("int,str,str,str,str");
    OutboundTimbang.setEditable(false);
    OutboundTimbang.setSkin("dhx_skyblue");

    OutboundTimbang.init();
    reloadgroup();

    function reloadgroup() {
        OutboundTimbang.clearAndLoad("partner-uco-outbound-data");

    }

    $("#RefreshBM").click(function() {
        reloadgroup();
    });
</script>

@endsection