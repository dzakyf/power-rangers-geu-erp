 @include('layout.plugins')


<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
      <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Health History</td>
	</tr>
	<tr height="30">
        <td class="badan" colspan="6">
			<div id="Health" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
			 <input type="button" id="Health_Add" value="Add" onclick="Health.addRow(Health.uid(),[Health.getRowsNum()+1],0)" size="20"/>    
					 <input type="button" id="Health_Delete" value="Delete" onclick="Health.deleteSelectedRows()" size="20"/>    
        </td>
	</tr>
 
     <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Driving Licenses</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Driving_Licenses" style="width:auto;height:150px; background-color:white;overflow:hidden"></div>
			         <input type="button" id="Driving_Licenses_Add" value="Add" onclick="Driving_Licenses.addRow(Driving_Licenses.uid(),[Driving_Licenses.getRowsNum()+1],0)" size="20"/>    
					 <input type="button" id="Driving_Licenses_Delete" value="Delete" onclick="Driving_Licenses.deleteSelectedRows()" size="20"/>    
					 

        </td>
	</tr>
	<tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Medical History</td>
	</tr>
     <tr height="30">
        <td class="badan" width="150">Height</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id = "Height"  size="5" /> cm</td>
        <td class="badan" width="100">Weight</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id="Weight" size="5"/> @include('layout.plugins')
@include('layout.adminor.tab-head')

<div class="container-fluid py-3">
    <!-- Health History Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-heartbeat me-2"></i>Health & Disease History</h6>
            <div class="d-flex gap-2">
                <button type="button" id="Health_Add" class="btn btn-sm btn-light text-primary" onclick="Health.addRow(Health.uid(),[Health.getRowsNum()+1],0)">
                    <i class="fa fa-plus me-1"></i> Add Health Record
                </button>
                <button type="button" id="Health_Delete" class="btn btn-sm btn-light text-danger" onclick="Health.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Health" style="width:100%;height:150px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Driving Licenses Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-id-card-o me-2"></i>Driving Licenses (SIM)</h6>
            <div class="d-flex gap-2">
                <button type="button" id="Driving_Licenses_Add" class="btn btn-sm btn-light text-primary" onclick="Driving_Licenses.addRow(Driving_Licenses.uid(),[Driving_Licenses.getRowsNum()+1],0)">
                    <i class="fa fa-plus me-1"></i> Add License
                </button>
                <button type="button" id="Driving_Licenses_Delete" class="btn btn-sm btn-light text-danger" onclick="Driving_Licenses.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Driving_Licenses" style="width:100%;height:150px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Medical & Physical Conditions -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-medkit me-2"></i>Medical & Physical Information</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Height">Height (Tinggi Badan)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="Height" class="form-control" placeholder="170">
                        <span class="input-group-text">cm</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Weight">Weight (Berat Badan)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="Weight" class="form-control" placeholder="65">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Glasses">Using Glasses/Contact Lens</label>
                    <select id="Glasses" class="form-select form-select-sm">
                        <option>No</option>
                        <option>Yes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Smoker">Smoker</label>
                    <select id="Smoker" class="form-select form-select-sm">
                        <option>No</option>
                        <option>Yes</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Longtime">Congenital Disease for Longtime Care?</label>
                    <select id="Longtime" class="form-select form-select-sm">
                        <option>No</option>
                        <option>Yes</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-muted" for="Longtime_Desc">If Yes, describe Disease:</label>
                    <input type="text" id="Longtime_Desc" class="form-control form-control-sm" placeholder="Nama riwayat penyakit kronis / bawaan">
                </div>
            </div>
        </div>
    </div>

    <!-- Save Action -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary px-4" onclick="saveOtherData()">
                <i class="fa fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
function saveOtherData() {
    if (typeof PHealth !== 'undefined') PHealth.sendData();
    if (typeof PDriving_Licenses !== 'undefined') PDriving_Licenses.sendData();
    alert('Data saved.');
}

Driving_Licenses = new dhtmlXGridObject('Driving_Licenses');
Driving_Licenses.setImagePath("./dhtmlx/imgs/");
Driving_Licenses.setHeader("No.,Description,,Note");
Driving_Licenses.setInitWidths("60,320,100,auto");
Driving_Licenses.setColAlign("center,left,center,left");
Driving_Licenses.setColSorting("int,str,str,str");
Driving_Licenses.enableEditEvents(true,false,true);
Driving_Licenses.setColTypes("ro,ro,ch,txt");
Driving_Licenses.setEditable(true);
PDriving_Licenses = new dataProcessor("employee-lists-detail-driving-update");
PDriving_Licenses.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PDriving_Licenses.init(Driving_Licenses);
Driving_Licenses.setSkin("dhx_skyblue");
Driving_Licenses.init();
Driving_Licenses.loadXML("xml-employee-lists-detail-driving?ID=<?php echo $_GET['ID'];?>");

Health = new dhtmlXGridObject('Health');
Health.setImagePath("./dhtmlx/imgs/");
Health.setHeader("No.,Description,Disease,Year,Days");
Health.setInitWidths("60,320,200,100,auto");
Health.setColAlign("center,left,center,center,left");
Health.setColSorting("int,str,str,str,str");
Health.setColTypes("ro,ro,ed,ed,ed");
Health.enableEditEvents(true,false,true);
PHealth = new dataProcessor("employee-lists-detail-health-update");
PHealth.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PHealth.setTransactionMode("POST",false);
PHealth.init(Health);
Health.setEditable(true);
Health.setSkin("dhx_skyblue");
Health.init();
Health.loadXML("xml-employee-lists-detail-health?ID=<?php echo $_GET['ID'];?>");
</script>
kg
        </td>
    </tr>
     <tr height="30">
        <td class="badan" width="150">Using Glasses/Contact Lens</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <select id = "Glasses"><option>No</option><option>Yes</option></select></td>
        <td class="badan" width="100">Smoker</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <select id = "Smoker"><option>No</option><option>Yes</option></select></td>
    </tr>
     <tr height="30">
        <td class="badan" width="150">Have Congenital Diseases for longtime Care?</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
              <select id = "Longtime"><option>No</option><option>Yes</option></select>
		</td>
        <td class="badan" width="100">if Yes,Disease:</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
         <input type="text" id="Longtime_Desc" size="20"/>    
         </td>
    </tr>
 </table>
 
 <script>
			
			Driving_Licenses = new dhtmlXGridObject('Driving_Licenses');
			Driving_Licenses.setImagePath("./dhtmlx/imgs/");
			Driving_Licenses.setHeader("No.,Description,,Note");
			Driving_Licenses.setInitWidths("60,320,100,300");
			Driving_Licenses.setColAlign("center,left,center,left");
			Driving_Licenses.setColSorting("int,str,str,str");
			Driving_Licenses.enableEditEvents(true,false,true);
			Driving_Licenses.setColTypes("ro,ro,ch,txt");
			Driving_Licenses.setEditable(true);
			Driving_Licenses.enableEditEvents(true,false,true);
			PDriving_Licenses = new dataProcessor("employee-lists-detail-driving-update");
			PDriving_Licenses.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			
			PDriving_Licenses.init(Driving_Licenses);
			Driving_Licenses.setSkin("dhx_skyblue");
			Driving_Licenses.init();
			Driving_Licenses.loadXML("xml-employee-lists-detail-driving?ID=<?php echo $_GET['ID'];?>");

			Health = new dhtmlXGridObject('Health');
			Health.setImagePath("./dhtmlx/imgs/");
			Health.setHeader("No.,Description,Disease,Year,Days");
			Health.setInitWidths("60,320,200,100,100");
			Health.setColAlign("center,left,center,center,left");
			Health.setColSorting("int,str,str,str,str");
			Health.setColTypes("ro,ro,ed,ed,ed");
			Health.enableEditEvents(true,false,true);
			PHealth = new dataProcessor("employee-lists-detail-health-update");
			PHealth.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			PHealth.setTransactionMode("POST",false);
			PHealth.init(Health);
			Health.setEditable(true);
			Health.setSkin("dhx_skyblue");
			Health.init();
			Health.loadXML("xml-employee-lists-detail-health?ID=<?php echo $_GET['ID'];?>");
			
			

</script>

