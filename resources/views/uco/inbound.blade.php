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
            <span class="active">INBOUND TIMBANG</span>
        </li>
    </ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
    <div id="InboundTimbang" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
    <input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="InboundTimbang.toExcel('./export/generate.php');" />
    <input type="button" id="RedirectToCreateInboundData" NAME="RefreshBM" value="Buat Data" class="btn btn-info mt-2 mr-2" />
    <input type="button" id="RefreshBM" NAME="RefreshBM" value="Request Penjemputan" class="btn btn-info mt-2 mr-2" />
</div>

<div id="MasterDirectoratDiv"></div>



</div>
<script>
    $(document).ready(function() {
        $('#RedirectToCreateInboundData').on('click', function() {
            window.location.href = `partner-uco-inbound-timbang-create`
        })
    })
    InboundTimbang = new dhtmlXGridObject('InboundTimbang');
    InboundTimbang.setImagePath("./dhtmlx/imgs/");
    InboundTimbang.setHeader("No,Code,Point of Origin (POO),Weight-in,Unit,Picker,Inbound Pickup");
    InboundTimbang.setInitWidths("80,150,150,100,100,150,150,150");
    InboundTimbang.setColAlign("center,center,left,center,left,center,left,center,center,center");
    InboundTimbang.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    InboundTimbang.setColSorting("int,str,str,str,str");
    InboundTimbang.setEditable(false);
    InboundTimbang.setSkin("dhx_skyblue");

    InboundTimbang.init();
    reloadgroup();

    function reloadgroup() {
        InboundTimbang.clearAndLoad("partner-uco-inbound-timbang-data");

    }

    $("#RefreshBM").click(function() {
        reloadgroup();
    });
</script>

@endsection