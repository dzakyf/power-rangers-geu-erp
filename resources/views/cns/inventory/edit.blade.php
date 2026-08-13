<div id="FormEditStock" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT UCO TRACKING</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="StockEditForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="edit_id" />
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Type">Type <span style="color:red;">*</span></label>
                            <select name="Type" id="edit_Type" disabled class="form-control" required>
                                <option value="inbound">Inbound</option>
                                <option value="outbound">Outbound</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Quantity">Quantity (Kg) <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" name="Quantity" id="edit_Quantity" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Transaction_Date">Transaction Date <span style="color:red;">*</span></label>
                            <input type="date" name="Transaction_Date" id="edit_Transaction_Date" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Quantity_PTU">Qty Pelindo</label>
                            <input type="number" step="0.01" name="Quantity_PTU" id="edit_Quantity_PTU" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Quantity_Sounding">Qty Sounding</label>
                            <input type="number" step="0.01" name="Quantity_Sounding" id="edit_Quantity_Sounding" class="form-control" />
                        </div>
                    </div>
                    <!-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Created_By">Created By <span style="color:red;">*</span></label>
                            <input type="text" name="Created_By" id="edit_Created_By" class="form-control" required />
                        </div>
                    </div> -->
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Reference_Number">Reference Number</label>
                            <input type="text" name="Reference_Number" id="edit_Reference_Number" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Attachment">Attachment Nota Timbang</label>
                            <input type="file" name="Attachment" id="edit_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="edit_Notes">Notes</label>
                            <textarea name="Notes" id="edit_Notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Quality Control</label>

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
                                        <td><input type="number" step="0.01" name="top_FFA_As_Palmitic" id="edit_top_FFA_As_Palmitic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="top_FFA_As_Oleic" id="edit_top_FFA_As_Oleic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="top_MC" id="edit_top_MC" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="top_IMP" id="edit_top_IMP" class="form-control" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle font-weight-bold">Middle</td>
                                        <td><input type="number" step="0.01" name="middle_FFA_As_Palmitic" id="edit_middle_FFA_As_Palmitic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="middle_FFA_As_Oleic" id="edit_middle_FFA_As_Oleic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="middle_MC" id="edit_middle_MC" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="middle_IMP" id="edit_middle_IMP" class="form-control" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle font-weight-bold">Bottom</td>
                                        <td><input type="number" step="0.01" name="bottom_FFA_As_Palmitic" id="edit_bottom_FFA_As_Palmitic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="bottom_FFA_As_Oleic" id="edit_bottom_FFA_As_Oleic" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="bottom_MC" id="edit_bottom_MC" class="form-control" /></td>
                                        <td><input type="number" step="0.01" name="bottom_IMP" id="edit_bottom_IMP" class="form-control" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <label for="edit_QC_Attachment">QC Attachment</label>
                            <input type="file" name="QC_Attachment" id="edit_QC_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>

                </div>


                <button type="button" class="btn btn-primary" onclick="submitEditStock()">Update</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="$('#FormEditStock').hide(); $('#StockEditForm')[0].reset();">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function prefillEditQC(rowId) {
        $.get('cns-inventory-tracking-data-json', {
            ID: rowId
        }, function(res) {
            var row = res && res[0] ? res[0] : null;
            if (!row) {
                alert('Data tidak ditemukan');
                return;
            }

            document.getElementById('edit_id').value = rowId;
            document.getElementById('edit_Type').value = row.Type ?? 'inbound';
            document.getElementById('edit_Quantity').value = row.Quantity ?? '';
            document.getElementById('edit_Transaction_Date').value = row.Transaction_Date ? row.Transaction_Date.substring(0, 10) : '';
            document.getElementById('edit_Quantity_PTU').value = row.Quantity_PTU ?? '';
            document.getElementById('edit_Quantity_Sounding').value = row.Quantity_Sounding ?? '';
            // document.getElementById('edit_Created_By').value = row.Created_By ?? '';
            document.getElementById('edit_Reference_Number').value = row.Reference_Number ?? '';
            document.getElementById('edit_Notes').value = row.Notes ?? '';

            var qc = row.qc || {};
            ['top', 'middle', 'bottom'].forEach(function(type) {
                var item = qc[type] || {};
                document.getElementById('edit_' + type + '_FFA_As_Palmitic').value = item.FFA_As_Palmitic ?? '';
                document.getElementById('edit_' + type + '_FFA_As_Oleic').value = item.FFA_As_Oleic ?? '';
                document.getElementById('edit_' + type + '_MC').value = item.MC ?? '';
                document.getElementById('edit_' + type + '_IMP').value = item.IMP ?? '';
            });

            $('#FormEditStock').show();
        }).fail(function() {
            alert('Gagal memuat data');
        });
    }

    function submitEditStock() {
        var form = document.getElementById('StockEditForm');

        if (!form.Transaction_Date.value) {
            alert('Transaction Date wajib diisi');
            return;
        }

        var formData = new FormData(form);

        $.ajax({
            url: 'cns-inventory-tracking-submit-edit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil diupdate');
                    form.reset();
                    $('#FormEditStock').hide();
                    ProjectionGrid.clearAndLoad(`cns-inventory-tracking-data?month=${$('#filterMonth').val()}`);
                } else {
                    alert('Gagal mengupdate data');
                }
            },
            error: function() {
                alert('Gagal mengupdate data');
            }
        });
    }
</script>