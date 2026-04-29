<div id="FormEditQualityControl" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT QUALITY CONTROL</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="QualityControlEditForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Created_At">Tanggal <span style="color:red;">*</span></label>
                            <input type="date" name="Created_At" id="edit_Created_At" class="form-control" required readonly />
                        </div>
                    </div>
                    <!-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Attachment">Attachment (kosongkan jika tidak diganti)</label>
                            <input type="file" name="Attachment" id="edit_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div> -->
                </div>

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

                <button type="button" class="btn btn-primary" onclick="submitEditQC()">Update</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="$('#FormEditQualityControl').hide(); $('#QualityControlEditForm')[0].reset();">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function prefillEditQC(rowId) {
        var grid = ProjectionGrid;

        // col: 0=No, 1=Tanggal, 2=TopPalmitic, 3=TopOleic, 4=TopMC, 5=TopIMP,
        //      6=MidPalmitic, 7=MidOleic, 8=MidMC, 9=MidIMP,
        //      10=BotPalmitic, 11=BotOleic, 12=BotMC, 13=BotIMP
        var tanggal = grid.cells(rowId, 1).getValue();
        var date = new Date(tanggal);
        var isoDate = date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');

        document.getElementById('edit_Created_At').value = isoDate;

        document.getElementById('edit_top_FFA_As_Palmitic').value = grid.cells(rowId, 2).getValue();
        document.getElementById('edit_top_FFA_As_Oleic').value = grid.cells(rowId, 3).getValue();
        document.getElementById('edit_top_MC').value = grid.cells(rowId, 4).getValue().replace('%', '');
        document.getElementById('edit_top_IMP').value = grid.cells(rowId, 5).getValue().replace('%', '');

        document.getElementById('edit_middle_FFA_As_Palmitic').value = grid.cells(rowId, 6).getValue();
        document.getElementById('edit_middle_FFA_As_Oleic').value = grid.cells(rowId, 7).getValue();
        document.getElementById('edit_middle_MC').value = grid.cells(rowId, 8).getValue().replace('%', '');
        document.getElementById('edit_middle_IMP').value = grid.cells(rowId, 9).getValue().replace('%', '');

        document.getElementById('edit_bottom_FFA_As_Palmitic').value = grid.cells(rowId, 10).getValue();
        document.getElementById('edit_bottom_FFA_As_Oleic').value = grid.cells(rowId, 11).getValue();
        document.getElementById('edit_bottom_MC').value = grid.cells(rowId, 12).getValue().replace('%', '');
        document.getElementById('edit_bottom_IMP').value = grid.cells(rowId, 13).getValue().replace('%', '');

        $('#FormEditQualityControl').show();
    }

    function submitEditQC() {
        var form = document.getElementById('QualityControlEditForm');
        var createdAt = form.Created_At.value;

        if (!createdAt) {
            alert('Tanggal wajib diisi');
            return;
        }

        var types = ['top', 'middle', 'bottom'];
        var attachmentFile = form.Attachment.files[0];

        var requests = types.map(function(type) {
            var formData = new FormData();
            formData.append('_token', form._token.value);
            formData.append('Created_At', createdAt);
            formData.append('Type', type.charAt(0).toUpperCase() + type.slice(1));
            formData.append('FFA_As_Palmitic', form[type + '_FFA_As_Palmitic'].value);
            formData.append('FFA_As_Oleic', form[type + '_FFA_As_Oleic'].value);
            formData.append('MC', form[type + '_MC'].value);
            formData.append('IMP', form[type + '_IMP'].value);

            if (attachmentFile) {
                formData.append('Attachment', attachmentFile);
            }

            return $.ajax({
                url: 'cns-quality-submit-edit',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });
        });

        $.when.apply($, requests).then(function() {
            alert('Data berhasil diupdate');
            form.reset();
            $('#FormEditQualityControl').hide();
            ProjectionGrid.clearAndLoad('cns-quality-data');
        }).fail(function() {
            alert('Gagal mengupdate data');
        });
    }
</script>