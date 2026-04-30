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
                            <label for="edit_Date">Tanggal <span style="color:red;">*</span></label>
                            <input type="date" name="Date" id="edit_Date" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Police_No">No. Polisi / Kontainer <span style="color:red;">*</span></label>
                            <input type="text" name="Police_No" id="edit_Police_No" class="form-control" required />
                        </div>
                    </div>
                    <!-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Status">Status</label>
                            <input type="text" name="Status" id="edit_Status" class="form-control" />
                        </div>
                    </div> -->
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Qty_SJ">QTY SJ</label>
                            <input type="number" step="0.01" name="Qty_SJ" id="edit_Qty_SJ" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Stock_In">Stock In (Kg)</label>
                            <input type="number" step="0.01" name="Stock_In" id="edit_Stock_In" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Stock_Out">Stock Out (Kg)</label>
                            <input type="number" step="0.01" name="Stock_Out" id="edit_Stock_Out" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="edit_Note">Remarks</label>
                            <textarea name="Note" id="edit_Note" class="form-control" rows="2"></textarea>
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
            document.getElementById('edit_Date').value = row.Date ? row.Date.substring(0, 10) : '';
            document.getElementById('edit_Police_No').value = row.Police_No ?? '';
            // document.getElementById('edit_Status').value = row.Status ?? '';
            document.getElementById('edit_Qty_SJ').value = row.Qty_SJ ?? '';
            document.getElementById('edit_Stock_In').value = row.Stock_In ?? '';
            document.getElementById('edit_Stock_Out').value = row.Stock_Out ?? '';
            document.getElementById('edit_Note').value = row.Note ?? '';

            $('#FormEditStock').show();
        }).fail(function() {
            alert('Gagal memuat data');
        });
    }

    function submitEditStock() {
        var form = document.getElementById('StockEditForm');

        if (!form.Date.value) {
            alert('Tanggal wajib diisi');
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
                    applyFilters();
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