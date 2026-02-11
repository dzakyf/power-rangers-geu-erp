<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FILE BATCH</div>

<div id="vault" class="with-header" style='height:200px;margin:0px;width:auto;'></div>

<input type="hidden" id="file_lists">
<div id='loadexcelcoa'><div>

<script>
var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-proyeksi.php"
                },
				    filesLimit: 1

            });
vault.events.on("uploadComplete", function(obj) {
	$.blockUI();
	LoadM('loadexcelcoa','proyeksi-load-excel-coa?filenya=' + obj[0].name);
	
});
vault.events.on("uploadBegin", function(obj) {
	$("#file_lists").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});