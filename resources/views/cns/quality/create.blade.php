<div id="FormQualityControl" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CREATE QUALITY CONTROL</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="QualityControlForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Created_At">Tanggal <span style="color:red;">*</span></label>
                            <input type="date" name="Created_At" id="Created_At" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Attachment">Attachment</label>
                            <input type="file" name="Attachment" id="Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>
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
                            <td><input type="number" step="0.01" name="top_FFA_As_Palmitic" class="form-control" /></td>
                            <td><input type="number" step="0.01" name="top_FFA_As_Oleic"    class="form-control" /></td>
                            <td><input type="number" step="0.01" name="top_MC"              class="form-control" /></td>
                            <td><input type="number" step="0.01" name="top_IMP"             class="form-control" /></td>
                        </tr>
                        <tr>
                            <td class="align-middle font-weight-bold">Middle</td>
                            <td><input type="number" step="0.01" name="middle_FFA_As_Palmitic" class="form-control" /></td>
                            <td><input type="number" step="0.01" name="middle_FFA_As_Oleic"    class="form-control" /></td>
                            <td><input type="number" step="0.01" name="middle_MC"              class="form-control" /></td>
                            <td><input type="number" step="0.01" name="middle_IMP"             class="form-control" /></td>
                        </tr>
                        <tr>
                            <td class="align-middle font-weight-bold">Bottom</td>
                            <td><input type="number" step="0.01" name="bottom_FFA_As_Palmitic" class="form-control" /></td>
                            <td><input type="number" step="0.01" name="bottom_FFA_As_Oleic"    class="form-control" /></td>
                            <td><input type="number" step="0.01" name="bottom_MC"              class="form-control" /></td>
                            <td><input type="number" step="0.01" name="bottom_IMP"             class="form-control" /></td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-primary" onclick="submitQC()">Submit</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="$('#FormQualityControl').hide(); $('#QualityControlForm')[0].reset();">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function submitQC() {
        var form = document.getElementById('QualityControlForm');
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
            formData.append('Created_At',      createdAt);
            formData.append('Type',            type.charAt(0).toUpperCase() + type.slice(1));
            formData.append('FFA_As_Palmitic', form[type + '_FFA_As_Palmitic'].value);
            formData.append('FFA_As_Oleic',    form[type + '_FFA_As_Oleic'].value);
            formData.append('MC',              form[type + '_MC'].value);
            formData.append('IMP',             form[type + '_IMP'].value);

            if (attachmentFile) {
                formData.append('Attachment', attachmentFile);
            }

            return $.ajax({
                url: 'cns-quality-submit',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });
        });

        $.when.apply($, requests).then(function() {
            alert('Data berhasil disimpan');
            form.reset();
            $('#FormQualityControl').hide();
            ProjectionGrid.clearAndLoad('cns-quality-data');
        }).fail(function() {
            alert('Gagal menyimpan data');
        });
    }
</script>