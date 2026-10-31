@extends('layout.mainlayout-bp')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>INVENTORY</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="./">Unused Crude Oil (UCO)</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">INBOUND CATEGORIES</span>
        </li>
    </ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
    <div id="InboundCategory" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
    <input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="InboundCategory.toExcel('./export/generate.php');" />
    <input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data" class="btn btn-info mt-2 mr-2" />
</div>

<div id="MasterDirectoratDiv"></div>

</div>
<script>
    InboundCategory = new dhtmlXGridObject('InboundCategory');
    InboundCategory.setImagePath("./dhtmlx/imgs/");
    InboundCategory.setHeader("No,Categories");
    InboundCategory.setInitWidths("80,150,150,100,100,150,150,150");
    InboundCategory.setColAlign("center,center,left,center,left,center,left,center,center,center");
    InboundCategory.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    InboundCategory.setColSorting("int,str,str,str,str");
    InboundCategory.setEditable(false);
    InboundCategory.setSkin("dhx_skyblue");

    InboundCategory.init();
    reloadgroup();


    function reloadgroup() {
        InboundCategory.clearAndLoad("xml-inbound-timbang");

    }


    $("#RefreshBM").click(function() {
        reloadgroup();
    });
</script>

@endsection