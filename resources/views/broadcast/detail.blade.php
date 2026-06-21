<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Detail broadcast</h5>
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="Title">Judul <span style="color: red;">*</span></label>
                        <input type="text" name="Title" id="detail_Title" class="form-control" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="Type">Jenis <span style="color: red;">*</span></label>
                        <input type="text" name="Type" id="detail_Type" class="form-control" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="Short_Desc">Ringkasan <span style="color: red;">*</span></label>
                        <input type="text" name="Short_Desc" id="detail_Short_Desc" class="form-control" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="Long_Desc">Detail <span style="color: red;">*</span></label>
                        <input type="text" name="Long_Desc" id="detail_Long_Desc" class="form-control" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="Effective_Date">Tanggal Berlaku <span style="color: red;">*</span></label>
                        <input type="text" name="Effective_Date" id="detail_Effective_Date" class="form-control" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="Status">Status Kebijakan <span style="color: red;">*</span></label>
                        <input type="text" name="Status" id="detail_Status" class="form-control" value="" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Files</label>
                        <div id="detail_Files"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>