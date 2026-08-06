<div id="FormQualityControl" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CREATE UCO STORAGE PELINDO</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="UCOStorageForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Type">Type <span style="color:red;">*</span></label>
                            <select name="Type" id="Type" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <option value="inbound">Inbound</option>
                                <option value="outbound">Outbound</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Quantity">Quantity (Kg) <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" name="Quantity" id="Quantity" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Transaction_Date">Transaction Date <span style="color:red;">*</span></label>
                            <input type="date" name="Transaction_Date" id="Transaction_Date" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Quantity_PTU">Qty PTU</label>
                            <input type="number" step="0.01" name="Quantity_PTU" id="Quantity_PTU" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Quantity_Sounding">Qty Sounding</label>
                            <input type="number" step="0.01" name="Quantity_Sounding" id="Quantity_Sounding" class="form-control" />
                        </div>
                    </div>

                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Reference_Number">Reference Number</label>
                            <input type="text" name="Reference_Number" id="Reference_Number" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Attachment">Attachment Nota Timbang</label>
                            <input type="file" name="Attachment" id="Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="Notes">Notes</label>
                            <textarea name="Notes" id="Notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Quality Control <span style="color:red;">*</span></label>

                            <table class="table table-bordered text-center">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>FFA As Palmitic</th>
                                        <th>FFA As Oleic</th>
                                        <th>MC</th>
                                        <th>IMP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="align-middle font-weight-bold">Top</td>
                                        <td><input type="number" step="0.01" name="top_FFA_As_Palmitic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="top_FFA_As_Oleic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="top_MC" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="top_IMP" class="form-control" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle font-weight-bold">Middle</td>
                                        <td><input type="number" step="0.01" name="middle_FFA_As_Palmitic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="middle_FFA_As_Oleic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="middle_MC" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="middle_IMP" class="form-control" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle font-weight-bold">Bottom</td>
                                        <td><input type="number" step="0.01" name="bottom_FFA_As_Palmitic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="bottom_FFA_As_Oleic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="bottom_MC" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="bottom_IMP" class="form-control" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <label for="QC_Attachment">Attachment QC</label>
                            <input type="file" name="QC_Attachment" id="QC_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" onclick="submitUCOStorage()">Submit</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="$('#FormQualityControl').hide(); $('#UCOStorageForm')[0].reset();">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function submitUCOStorage() {
        var form = document.getElementById('UCOStorageForm');

        if (!form.Type.value) {
            alert('Type wajib diisi');
            return;
        }
        if (!form.Quantity.value) {
            alert('Quantity wajib diisi');
            return;
        }
        if (!form.Transaction_Date.value) {
            alert('Transaction Date wajib diisi');
            return;
        }

        var formData = new FormData(form);

        $.ajax({
            url: 'cns-inventory-tracking-submit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil disimpan');
                    form.reset();
                    $('#FormQualityControl').hide();
                    ProjectionGrid.clearAndLoad(`cns-inventory-tracking-data?month=${$('#filterMonth').val()}`);
                } else {
                    alert('Gagal menyimpan data: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Gagal menyimpan data');
            }
        });
    }
</script>