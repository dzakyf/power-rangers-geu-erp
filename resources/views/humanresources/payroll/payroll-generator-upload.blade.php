<script>

var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-payroll-generator.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#file_lists").val(obj[0].name);
	
	LoadM('divProcJournal','payroll-generator-upload-process?bulantahun=' + $("#bulantahun").val() + '&filename=' + obj[0].name);
	
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
            
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["bulantahun"]);
	myCalendar.setDateFormat("%Y-%m");

			
</script>
<div id="vault" class="with-header" style='height:200px;margin:0px;width:500px;'></div>
<input type="hidden" id="file_lists">
<div>
					
    <input type="button" value="Tutup"  id="Tmbhuploadtutup" onclick="CloseAccountingJournalTransactionWindow();" /> 
	  
</div>
<div id="divProcJournal"></div>
<script>
function CloseAccountingJournalTransactionWindow() {
	parent.winNewJournal.window("WNewJournal").close();
}




</script>