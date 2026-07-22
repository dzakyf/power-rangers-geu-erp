@csrf
<div class="mt-3">
	<div class="card">
		<div class="card-header">
			<h5 class="mt-3 ml-2">New Training</h5>
		</div>
		<div class="card-body">
			<div id="ContentPages">
				<div class="row">
					<div class="col-12">
						<div class="form-group">
							<input type="hidden" name="ID" id="ID" value="{{ $training->ID }}">
							<input type="hidden" name="Task_No" id="Task_No" value="{{ $training->Task_No }}">
							<label for="Training_Name">Training Name</label>
							<input class="form-control shadow-sm" type="text" name="Training_Name" id="Training_Name" value="{{ $training->Training_Name }}">
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Training_Category">Training Category</label>
							<select name="Training_Category" id="Training_Category" class="form-control shadow-sm">
								<option value="">-- Choose Training Category --</option>
								@foreach ($trCategory as $item)
									<option value="{{$item->Budget_Code}}" {{ $item->Budget_Code == $training->Training_Category ? 'selected' : '' }}>{{ $item->Description }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Training_Type">Training Type</label>
							<input class="form-control shadow-sm" type="text" name="Training_Type" id="Training_Type" value="{{ $training->Training_Type }}">
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Training_Method">Training Method</label>
							<input class="form-control shadow-sm" type="text" name="Training_Method" id="Training_Method" value="{{ $training->Training_Method }}">
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Training_Objective">Training Objective</label>
							<input class="form-control shadow-sm" type="text" name="Training_Objective" id="Training_Objective" value="{{ $training->Training_Objective }}">
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Training_Competence">Training Competence</label>
							<input class="form-control shadow-sm" type="text" name="Training_Competence" id="Training_Competence" value="{{ $training->Training_Competence }}">
						</div>
					</div>
					<div class="col-12">
						<div class="row">
							<div class="col-6">
								<div class="form-group">
									<label for="Start_Date">Start Date</label>
									<input class="form-control shadow-sm bg-white" type="text" name="Start_Date" id="Start_Date" value="{{ date("d M Y", strtotime($training->Start_Date)) }}" readonly>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="End_Date">End Date</label>
									<input class="form-control shadow-sm bg-white" type="text" name="End_Date" id="End_Date" value="{{ date("d M Y", strtotime($training->End_Date)) }}" readonly>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Currency">Currency</label>
							<select name="Currency" id="Curremcy" class="form-control shadow-sm">
								<option value="">-- Choose Currency --</option>
								@foreach ($currency as $item)
								<option value="{{ $item->Currency_Symbol }}" {{ $item->Currency_Symbol == $Fetch->Currency ? 'selected' : '' }}>{{ $item->Currency_Symbol }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-12 mb-3">
						<div id="TrainingListsCost" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
						<div class="mt-2">
							<button type="button" class="btn btn-primary" onclick="addTrainingCost()">Add Cost</button>
							<button type="button" class="btn btn-danger" onclick="delTrainingCost()">Remove Cost</button>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Total_Cost">Total Estimated Cost</label>
							<input class="form-control shadow-sm bg-white" type="text" name="Total_Cost" id="Total_Cost" value="{{ $training->Total_Cost }}" readonly>
						</div>
					</div>
					<div class="col-12">
						<button type="button" class="btn btn-success" id="SubmitDataTraining">Submit</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="divlistTraining"></div>
<script>
	TrainingListsCost = new dhtmlXGridObject('TrainingListsCost');
	TrainingListsCost.setImagePath("./dhtmlx/imgs/");
	TrainingListsCost.setHeader("No.,Item Type, Item Description, Estimated Cost");
	TrainingListsCost.setInitWidths("70,100,250,120");
	TrainingListsCost.setColAlign("center,left,left,right");
	TrainingListsCost.setColTypes("ro,ed,ed,edn");
	TrainingListsCost.setColSorting("int,str,str,int");
	TrainingListsCost.setNumberFormat("0,000",3,".",",");
	TrainingListsCost.setEditable(true);
	PTrainingListsCost = new dataProcessor("hrd-training-new-update");
	PTrainingListsCost.setTransactionMode({
		mode: "POST",
		total:true,
		headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		}	,
		payload: {"Task_No":"<?php echo $Task_No;  ?>"}
	})
	PTrainingListsCost.setUpdateMode("Off");
	PTrainingListsCost.init(TrainingListsCost);
	TrainingListsCost.setSkin("dhx_skyblue");
	TrainingListsCost.init();
	TrainingListsCost.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
		if(stage == 2){
			if(cInd == 3){
				let ids = TrainingListsCost.getAllRowIds().split(',');
				let total = 0;
				for (let i = 0; i < ids.length; i++) {
					const ele = ids[i];
					total += Number(TrainingListsCost.cells(ele, 3).getValue());
				}

				$('#Total_Cost').val(total);
			}
		}

		return true;
	});

	function addTrainingCost() {
		let uuid = TrainingListsCost.uid();
		TrainingListsCost.addRow(uuid, [TrainingListsCost.getRowsNum() + 1, '', '', '', '']);
		TrainingListsCost.selectRow(uuid);
	}

	function delTrainingCost() {
		TrainingListsCost.deleteSelectedRows();
		// dhtmlx.confirm('Cancel this Requisition?', 'Requisition', function(r) {

		// 	if (r == true) {
		// 		alert('Data Successfully Deleted!');
		// 		setTimeout(function() {
		// 			refreshdata();
		// 		}, 1000);
		// 	}
		// })
	}
    var myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date"]);
	myCalendar.setDateFormat("%d %M %Y");

	function SubmitGrid_Requisition() {
		TrainingListsCostProc.sendData();
		alert('Data Successfully Updated!');
		TrainingListsCost.clearAll();
		refreshdata();
	}

	function newreq() {
		LoadM('divlistTraining', 'hrd-training-new');
	}
	
	$('#SubmitDataTraining').click(function(){

		gettingVars = getAllValues();

		$.ajax({
			headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url : "hrd-training-detail-submit?_token={{ csrf_token() }}&Task_ID={{ $Task_ID }}",
			type: "POST",
			data : gettingVars,
			success: function(data, textStatus, jqXHR)
			{
				var dataPos = data.indexOf("success");
				if (dataPos != '-1'){
					alert('Data Was Submitted!');

					setTimeout(() => {
						refreshdata();
						$('#divlistTraining').html('');
					}, 500);
				}else{
					alert('Failed to Submit!');
				}
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
			alert( "Error Internal! Please Try again!" );
		
			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio")) {
				if (this.checked) {
					inputValues.push($(this).attr("id") + "=" + $(this).val());
				}
			}
			else if (type != "button" || type != "submit") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
				} else if ($this.is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}

</script>