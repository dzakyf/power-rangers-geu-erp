<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CREATE TRAINING</div>
	<div class="card">
		<div class="card-body" id="ContentPages">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label for="material_name">Nama Materi</label>
                        <input type="text" name="material_name" id="material_name" class="form-control" value="">
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label for="material_category">Kategori Materi</label>
						<select name="material_category" id="material_category" class="form-control">
							<option value="Training">Training</option>
							<option value="Umum">Umum</option>
							<option value="News">News</option>
						</select>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label for="material_link">Link</label> <br>
                        <input type="text" name="material_link" id="material_link" class="form-control" value="">
                    </div>
                </div>
				<div class="col-12 mb-3">
					<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ATTACHMENT</div>
					<div id="material_attachments" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
					<input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="material_attachment" />
				</div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label for="material_download">Dapat di download</label> <br>
                        <input type="checkbox" name="material_download" id="material_download" value="1"> Ya
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label for="material_view">Dapat di lihat</label> <br>
                        <input type="checkbox" name="material_view" id="material_view" value="1"> Ya
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label for="MasterMaterialUser">Users</label>
                    <div id="MasterMaterialUser" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
                </div>
                <div class="col-12">
                    <button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
                </div>
            </div>
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>    
	var vaultMaterial_attachment = new dhx.Vault("material_attachments", {
		uploader: {
			target: "upload-receiveinvoice.php"
		},
		filesLimit: 1

	});

	vaultMaterial_attachment.events.on("uploadComplete", function(obj) {
		console.log("upload complete")
		$("#material_attachment").val(obj[0].name);
	});

	vaultMaterial_attachment.events.on("uploadBegin", function(obj) {
		console.log("upload begin")
		$("#material_attachment").val("");

	});
	vaultMaterial_attachment.events.on("beforeAdd", function(fileItem) {
		if (vaultMaterial_attachment.data.getLength() >= 1) {
			alert('Only can upload 1 file per checkpoint.');
			return false;
		}
	});


	vaultMaterial_attachment.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop().toLowerCase();

		var allowed = ["pdf","jpg","jpeg","png","gif","webp"];

		var predicate = allowed.includes(extension);

		if (!predicate) {
			dhtmlx.alert("Hanya boleh mengupload file PDF atau gambar (JPG, PNG, GIF, WEBP)");
		}
		return predicate;
	});
	MasterMaterialUser = new dhtmlXGridObject('MasterMaterialUser');
	MasterMaterialUser.setImagePath("./dhtmlx/imgs/");
	MasterMaterialUser.setHeader("No,checklist, Nama, Posisi, Department");
	MasterMaterialUser.attachHeader("#rspan,#master_checkbox,#text_filter,#text_filter,#text_filter");
	// MasterMaterialUser.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	MasterMaterialUser.setInitWidths("60,80,100,100,100");
	MasterMaterialUser.setColAlign("center,center,left,left,left");
	MasterMaterialUser.setColSorting("int,int,str,str,str");
	MasterMaterialUser.setColTypes("ron,ch,ro,ro,ro");
	MasterMaterialUser.enableSmartRendering(true, 150);
	// MasterMaterialUser.setNumberFormat("0,000", 7, ".", ",");
	// MasterMaterialUser.attachEvent("onRowDblClicked", MasterMaterialUserKlik);
	MasterMaterialUser.setSkin("dhx_skyblue");
	MasterMaterialUser.init();
	refreshdataMaterial();

	function refreshdataMaterial() {
		MasterMaterialUser.clearAndLoad('xml-partner-master-material-user');
	}

	$('#SubmitCustDetail').click(function() {

		let MasterMaterialUserID = MasterMaterialUser.getChangedRows().split(',').filter(item => item !== '');
        let counter = 0;
        let ids = [];
        for (let i = 0; i < MasterMaterialUserID.length; i++) {
            const ele = MasterMaterialUserID[i];
            const check = parseInt(MasterMaterialUser.cells(ele, 1).getValue());
            if (check) {
                counter++;
                ids.push(ele);
            }
        }

		gettingVars = getAllValues();
        gettingVars['material_speaker'] = ids;

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-master-material-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.status;
				if (dataPos == 'success') {
					// var cariAWB = data.split(":");
					alert('Training Application Submitted!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                    window.reload();
				} else {
					alert('Application Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = {};
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues[$(this).attr("name")] = $(this).val();
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues[$(this).attr("id")] = $(this).val();
			} else if ($(this).is("textarea")) {
				inputValues[$(this).attr("id")] = $(this).val();
			}
		})
		return inputValues;
	}
</script>