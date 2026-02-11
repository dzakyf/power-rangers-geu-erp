<Br><div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>
<div id="BRMGTHistory" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>

<div id="xxid">
<div style="float:left;width:300px;">Date</div>
<div>:: <input type="text" id="TglNew" size="12" onkeypress="return tabloc(event,this.id);" value="<?php echo date('Y-m-d');?>" /> <input type="hidden" size="10" readonly="readonly"  id="City" value="<?php echo $_GET['City']?>" /></div>
<div style="float:left;width:300px;">Document No</div>
<div>:: <input type="text" id="Doc_No" size="40" onkeypress="return tabloc(event,this.id);"/></div>
<div style="float:left;width:300px;">External Doc No</div>
<div>:: <input type="text" id="External_Doc" size="40" onkeypress="return tabloc(event,this.id);"/></div>
<div style="float:left;width:300px;">Acc No</div>
<div>:: <input type="text" id="Acc_No" size="10" onkeypress="return tabloc(event,this.id);"/> <input type="text" id="Acc_No_Desc" disabled="disabled" readonly="readonly" size="40"/> Ref: <input readonly="readonly" type="text" id="Reference" size="10" disabled="disabled"/></div>
<div style="float:left;width:300px;">Deskripsi</div>
<div>:: <input type="text" id="Deskripsi" size="60" onkeypress="return tabloc(event,this.id);"/></div>
<div style="float:left;width:300px;">Debet</div>
<div>:: <input type="text" id="Debet" size="10" value="0" onkeypress="return tabloc(event,this.id);"/></div>
<div style="float:left;width:300px;">Credit</div>
<div>:: <input type="text" id="Credit" size="10" value="0" onkeypress="return tabloc(event,this.id);"/></div>
<div style="float:left;width:300px;">Counterpart Name</div>
<div>:: <input type="text" id="Counterpart_Name" size="40" onkeypress="return tabloc(event,this.id);" style='text-transform:uppercase'/></div>
<div style="float:left;width:300px;">Counterpart Code</div>
<div>:: <input type="text" id="Counterpart_Code" size="20" onkeypress="return tabloc(event,this.id);" style='text-transform:uppercase'/></div>
</div>

<div id="BRMGTDetail" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>

function tabloc(e,idname){
	 if (e.keyCode == 13){
		switch(idname){
			case "TglNew": $("#Doc_No").focus();break;	
			case "Doc_No": $("#External_Doc").focus();break;	
			case "External_Doc": $("#Acc_No").focus();break;	
			case "Deskripsi": $("#Debet").focus();break;	
			case "Debet": $("#Credit").focus();break;	
			case "Credit": $("#Counterpart_Name").focus();break;	
			case "Counterpart_Code": 
			
			tambahinbaru2();
			kredits = $("#Credit").val();
			debets = $("#Debet").val();
			$("#Credit").val(debets);
			$("#Debet").val(kredits);
			$("#Acc_No,#Acc_No_Desc,#Reference").val('');
			$("#TglNew").focus();
			
			break;	
		}
	}
}
$("#Acc_No").coolautosuggest({
	url:"Branch_Journal_Mgt_Entry_Account?chars=",
	showThumbnail:false,
	showDescription:true,
	idField:$("#Acc_No"),
	width:500,
	minChars:1,
	onSelected:function(result){
	  if(result!=null){
		$("#Acc_No").val(result.id); 
		$("#Acc_No_Desc").val(result.data); 
		$("#Reference").val(result.thumbnail); 
			$("#Deskripsi").focus();

	  }
	}
});
$("#Counterpart_Name").coolautosuggest({
	url:"Branch_Journal_Mgt_Entry_Counterpart?City=<?php echo $_GET['City']?>&chars=",
	showThumbnail:false,
	showDescription:true,
	idField:$("#Counterpart_Name"),
	width:500,
	minChars:1,
	onSelected:function(result){
	  if(result!=null){
		$("#Counterpart_Name").val(result.id); 
		$("#Counterpart_Name").val(result.data); 
			$("#Counterpart_Code").focus();

	  }
	}
});

$.blockUI(); 


BRMGTHistory = new dhtmlXGridObject('BRMGTHistory');
BRMGTHistory.setImagePath("./dhtmlx/imgs/");
BRMGTHistory.setHeader("<b>TASK HISTORY</b>,#cspan,#cspan,#cspan,#cspan,#cspan");
BRMGTHistory.attachHeader("No,Date,User Actions,Note,Actions,Status");
BRMGTHistory.setInitWidths("100,150,100,400,200,100");
BRMGTHistory.setColAlign("center,center,center,left,left,center");
BRMGTHistory.setColTypes("ro,ro,ro,ro,ro,ro");
BRMGTHistory.setColSorting("int,str,str,str,str,str");
BRMGTHistory.setSkin("dhx_skyblue");
BRMGTHistory.enableMultiline(true);
BRMGTHistory.init();
BRMGTHistory.clearAndLoad("xml-accounting-journal-transaction-detail-log?Task_ID=<?php echo $_GET['ID']?>");

var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["TglNew"]);
	myCalendar.setDateFormat("%Y-%m-%d");

function BRMGTDetailKLIK(rId,cInd,stage) {

	if (cInd == 5){
		ListCoA();
	
	}
	return true;
}
BRMGTDetail = new dhtmlXGridObject('BRMGTDetail');
BRMGTDetail.setImagePath("./dhtmlx/imgs/");
BRMGTDetail.setHeader("<b>Journal Ledger</b>,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan");
BRMGTDetail.attachHeader("No,Posting Date,Document No,External Doc,Journal No,GL/Account,GL Name,Reference,Description,Debet,Kredit,Counterpart Name,Counterpart Code");
BRMGTDetail.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#text_filter,#rspan");
BRMGTDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,");
BRMGTDetail.attachFooter("Balance,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,<div style='color:red;font-weight:bold;' id='sumfooternew'></div>,,,,");
BRMGTDetail.setInitWidths("80,100,200,200,0,100,200,150,150,200,200,140,100,100");
BRMGTDetail.setColAlign("center,center,left,left,center,center,left,left,left,right,right,center,center,center");
BRMGTDetail.setColTypes("ro,dhxCalendar,ed,ed,ro,ro,ro,ro,ed,edn,edn,ed,ed,ed,ed");
BRMGTDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
BRMGTDetail.setEditable(true);
BRMGTDetail.setDateFormat("%Y-%m-%d"); 
BRMGTDetail.enableSmartRendering(true, 100);
BRMGTDetail.attachEvent("onRowDblClicked", BRMGTDetailKLIK);

BRMGTDetail.setSkin("dhx_skyblue");
BRMGTDetail.enableMultiselect(true);
BRMGTDetail.enableMultiline(true);
BRMGTDetail.setNumberFormat("0,000",9,".",",");
BRMGTDetail.setNumberFormat("0,000",10,".",",");
BRMGTDetail.attachEvent("onStatReady",function(){
	var c3 = String(BRMGTDetail.getFooterLabel(10));
	var c2 = String(BRMGTDetail.getFooterLabel(9));

	var cariString = new RegExp(',', 'g');
	c2 = c2.replace(cariString, '');	
	c3 = c3.replace(cariString, '');	

	var res = c2-c3;
	var srQ = document.getElementById("sumfooternew");
	srQ.innerHTML = number_format(res);

})
PBRMGTDetail = new dataProcessor("accounting-journal-transaction-detail-submit");
	PBRMGTDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		       

      }	,
	  payload: {"Task_ID":"{{ __($_GET['ID']) }}", "_token": "{{ csrf_token() }}"}
    })
	
PBRMGTDetail.setUpdateMode("Off");
PBRMGTDetail.init(BRMGTDetail);
BRMGTDetail.init();
BRMGTDetail.loadXML("xml-accounting-journal-transaction-detail?Task_ID=<?php echo $_GET['ID']?>",selesais);

function selesais(){
	$.unblockUI();
}

$("#SaveJournal").click(function(){
		PBRMGTDetail.sendData();
		alert("Data Journal was Saved!");

		CloseTaskBRMGTDetail();
});
$("#FinalSubmit").click(function(){
    var SubmitReason = $("#SubmitReason").val();
	var srQ = document.getElementById("sumfooternew");
	var mid = '<?php echo $_GET['ID']; ?>';
	if(srQ.innerHTML == '0'){
		dhtmlx.confirm('Are you sure want  to final Submit?', 'Journal', function(r) {
			
		         if (r==true){
				PBRMGTDetail.sendData();
				alert("Data Journal was Posted!");
				Status = BranchMGTJournalTask.cells("<?php echo $_GET['ID']; ?>",4).getValue();
				urlcode = mid + "|" + Status + "|" + SubmitReason;
				LoadM('BRMGTDivSub',"accounting-journal-transaction-detail-final-submit?ID=" + mid);
				
                    }
		});
	} else {
		alert('Final submit tidak dapat dilakukan karena data tidak balance!');
		return true;	
	}

});
$("#DeleteJournal").click(function(){
		BRMGTDetail.deleteSelectedRows();
		
});
$("#aDDDataJournal").click(function(){
		Urutan = BRMGTDetail.getRowsNum();
		BRMGTDetail.addRow(BRMGTDetail.getRowsNum(),[Urutan+1,$("#TglNew").val(),$("#Doc_No").val(),$("#External_Doc").val(),,,,,,,,$("#Counterpart_Name").val(),$("#Counterpart_Code").val()],Urutan);
		BRMGTDetail.selectRow(Urutan);
		
});
function tambahinbaru2(){
Urutan = BRMGTDetail.getRowsNum();
		BRMGTDetail.addRow(BRMGTDetail.getRowsNum(),[Urutan+1,$("#TglNew").val(),$("#Doc_No").val(),$("#External_Doc").val(),'',$("#Acc_No").val(),$("#Acc_No_Desc").val(),$("#Reference").val(),$("#Deskripsi").val(),$("#Debet").val(),$("#Credit").val(),$("#Counterpart_Name").val(),$("#Counterpart_Code").val()],Urutan);
		BRMGTDetail.selectRow(Urutan);
			
}

function CloseTaskBRMGTDetail(){
	reloadJournal();
	$.blockUI(); 
	setTimeout(function(){
		$("#DivCashDetail").html('');
		selesais();
	},1500);
	
}
function ListCoA() {
    winListCOABranch = new dhtmlXWindows();
    winListCOABranch.attachViewportTo("WinCOA");
    winListCOABranch.enableAutoViewport(true);
	winListCOABranch.setImagePath("./dhtmlx/imgs/");
    WListCOABranch = winListCOABranch.createWindow("WListCOABranch", 0, 0, 860, 350);
    WListCOABranch.setText("Lists of CoA");
    WListCOABranch.attachURL('Branch_COA_List.php', true);
	WListCOABranch.button("minmax1").hide();
    WListCOABranch.button("park").hide();
	WListCOABranch.centerOnScreen();
	WListCOABranch.allowMove();	
}

function changeobject(nilai){
	BRTB.clearAndLoad('xml-accounting-journal-transaction-detail-tb?Task_ID=<?php echo $_GET['ID']?>&objek=' + nilai);

}
			
			BRTB = new dhtmlXGridObject('BRTB');
			BRTB.setImagePath("./dhtmlx/imgs/");
			BRTB.setHeader("<b>Trial Balance Checker</b>,#cspan,#cspan,Object:,<select onchange='changeobject(this.value)'><option>NORMAL</option><option>GROUP</option></select>,#cspan");
			BRTB.attachHeader("&nbsp;,Acc No,Description,Debet,Credit,Net Mutation");
			BRTB.attachHeader("#rspan,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#rspan");
			BRTB.attachFooter(["Total",'#cspan','#cspan',"#stat_total","#stat_total","<div class='merah' id='jumtotal'>0</div>"]);
			BRTB.setInitWidths("50,80,440,100,100,100,100");
			BRTB.setColAlign("center,center,left,right,right,right,right,right,right");
			BRTB.setColSorting("int,str,str,str,str,int,int,int,int,int");
			BRTB.setColTypes("ro,ro,ro,ron,ron,ron,ron,ron,ron,ron");
			BRTB.setNumberFormat("0,000",3,".",",");
			BRTB.setNumberFormat("0,000",4,".",",");
			BRTB.setNumberFormat("0,000",5,".",",");
			BRTB.setEditable(false);
			BRTB.enableSmartRendering(true);
			BRTB.setSkin("dhx_skyblue");
			BRTB.attachEvent("onStatReady",function(){
				var c3 = String(BRTB.getFooterLabel(4));
				var c2 = String(BRTB.getFooterLabel(3));
			
				var cariString = new RegExp(',', 'g');
				c2 = c2.replace(cariString, '');	
				c3 = c3.replace(cariString, '');	
			
				var res = c2-c3;
				var srQ = document.getElementById("jumtotal");
				srQ.innerHTML = number_format(res);
			
			})
			
			BRTB.init();
			BRTB.loadXML('xml-accounting-journal-transaction-detail-tb?Task_ID=<?php echo $_GET['ID']?>&objek=NORMAL');
</script>
<div>
     <input type="button" id="ExportExl" name="ExportExl" value="Export Excel" onclick="BRMGTDetail.toExcel('./export_advanced/generate?filename=Branch_Detail_Task_Journal&title=Branch_Detail_Task_Journal');"  />

    <input type="button" id="CloseWins" NAME="CloseWins" value="Cancel" />
    <input type="button" id="DeleteJournal" NAME="DeleteJournal" value="Delete Data" />                
    <input type="button" id="aDDDataJournal" NAME="aDDDataJournal" value="Add Data" />
    <input type="button" id="SaveJournal" NAME="SaveJournal" value="Save Data" />
    <input type="button" id="FinalSubmit" NAME="FinalSubmit" value="Final Submit" />
    :: Final Submit Comment <input type="text" size="30" id="SubmitReason" />    
	
</div>
<Br><div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRIAL BALANCE VALIDATION</div>

<div id="BRTB" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
<div id="BRMGTDivSub">

     <input type="button" id="ekselCOAs" name="ekselCOAs" value="Export Excel" onclick="BRTB.toExcel('./export_advanced/generate?filename=Trial_Balance_Checker&title=Trial_Balance_Checker');"  />

</div>
