<div id="vault" class="with-header" style='height:250px;margin:0px;width:580px;'></div>
<script>


var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-doclain.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
var nomorrow ="<?php echo $_GET['idrow']?>";
OrderDoc.cells(nomorrow,4).setValue(obj[0].name);	
parent.winCOA.window("WCOA").close();
});
vault.events.on("uploadBegin", function(obj) {
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});
   
</script>
