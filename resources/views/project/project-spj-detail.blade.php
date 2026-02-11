<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>



<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REQUESTED SPJ</div>

<div id="DetailSPJ" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
<script>
    DetailSPJ = new dhtmlXGridObject('DetailSPJ');
    DetailSPJ.setImagePath("./dhtmlx/imgs/");
    DetailSPJ.setHeader(
        "No,Task_ID,Subject,Tujuan,Start,End,Total,Status,Note,File"
    );
    DetailSPJ.attachHeader(
        "#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
    );
    DetailSPJ.setInitWidths("60,150,250,100,250,120,100,100,120,120");
    DetailSPJ.setColAlign("center,left,left,left,left,center,left,left,left,center");
    DetailSPJ.setColSorting("int,str,str,str,str,str,str,str,str,str");
    DetailSPJ.setColTypes("ro,ro,ro,ro,ro,ro,ron,ro,ro,ro");
    DetailSPJ.enableSmartRendering(true, 150);
    DetailSPJ.setNumberFormat("0,000", 6, ".", ",");
    DetailSPJ.setSkin("dhx_skyblue");
    DetailSPJ.init();
    refreshdata();

    function refreshdata() {
        DetailSPJ.clearAndLoad('xml-project-spj-detail?ID=<?php echo $_GET['ID']; ?>');

    }

    $('#SpjAdd').click(function() {
        source = DetailSPJ.cells(DetailSPJ.getSelectedId(), 5).getValue();
        ref = DetailSPJ.cells(DetailSPJ.getSelectedId(), 6).getValue();
        LoadM('DivAddSpj', 'project-spj-add?source=' + source + "&ref=" + ref);
    });
</script>