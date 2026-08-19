<div class="mt-3">
    <div class="card">
        <div class="card-header my-2 ml-2">
            <h6 class="d-inline-block">Detail Form Pemusnahan Aset</h6>
			<span class="float-right">
				<input type="button" id="CloseAssetDisposalDetail" class="btn btn-secondary mr-2 py-2 px-3 my-2" NAME="CloseAssetDisposalDetail" value="Close" onclick="$('#AssetDisposalDiv').html('')" />
			</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div id="ContentPages">
                    <div class="FormInput_Form_Content">
                        <h6 style="font-weight:bold; margin-bottom:10px;">Identitas Pemohon</h6>
                        <div class="FormInput_left">Nama</div>
                        <div class="FormInput_right">
                            <input type="text" id="User_Name" size="40" value="{{ $ad->User_Name }}" readonly="readonly" />
                        </div>
                        <div class="FormInput_left">Jabatan</div>
                        <div class="FormInput_right">
                            <input type="text" id="User_Position" size="40" value="{{ $ad->User_Position }}" readonly="readonly" />
                        </div>
                        <div class="FormInput_left">Tanggal Pengajuan</div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_Date" readonly="readonly" size="15" value="{{ $ad->Disposal_Date }}" />
                        </div>
                        <div class="FormInput_left">Nomor Formulir</div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_No" size="30" readonly="readonly" value="{{ $ad->Disposal_No }}" />
                        </div>

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Informasi Aset / Equipment</h6>
                        <div class="FormInput_left">Nama Aset/equipment</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Name" size="40" value="{{ $ad->Asset_Name }}" readonly="readonly" />
                        </div>
                        <div class="FormInput_left">Nomor Seri</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Serial_No" size="40" value="{{ $ad->Asset_Serial_No }}" readonly="readonly" />
                        </div>
                        <div class="FormInput_left" style="height:60px;">Deskripsi Aset/equipment</div>
                        <div class="FormInput_right" style="height:60px;">
                            <textarea id="Asset_Description" cols="80" rows="2" readonly="readonly">{{ $ad->Asset_Description }}</textarea>
                        </div>
                        <div class="FormInput_left">Kondisi Aset/equipment</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Condition" size="40" value="{{ $ad->Asset_Condition }}" readonly="readonly" />
                        </div>
                        <div class="FormInput_left">Lokasi Saat Ini</div>
                        <div class="FormInput_right">
                            <input type="text" id="Asset_Location" size="40" value="{{ $ad->Asset_Location }}" readonly="readonly" />
                        </div>

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Detail Pemusnahan</h6>
                        <div class="FormInput_left" style="height:80px;">Alasan Pemusnahan</div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Disposal_Reason" cols="80" rows="3" readonly="readonly">{{ $ad->Disposal_Reason }}</textarea>
                        </div>
                        <div class="FormInput_left">Tanggal Pemusnahan</div>
                        <div class="FormInput_right">
                            <input type="text" id="Disposal_Actual_Date" size="15" value="{{ $ad->Disposal_Actual_Date }}" readonly="readonly" />
                        </div>
                        <div class="FormInput_left">Status</div>
                        <div class="FormInput_right">
                            <input type="text" id="Status" size="15" value="{{ $ad->Status }}" readonly="readonly" />
                        </div>

                        @if (!empty($ad->Approval_Note))
                        <div class="FormInput_left" style="height:80px;">Catatan Approval</div>
                        <div class="FormInput_right" style="height:80px;">
                            <textarea id="Approval_Note" cols="80" rows="2" readonly="readonly">{{ $ad->Approval_Note }}</textarea>
                        </div>
                        @endif

                        <h6 style="font-weight:bold; margin:20px 0 10px 0;">Persetujuan</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Diajukan Oleh / Mengetahui</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Pemohon</strong></td><td>{{ $ad->Pemohon_Name }}</td><td>{{ $ad->Pemohon_Date }}</td></tr>
                                <tr><td><strong>Kepala Divisi</strong></td><td>{{ $ad->Kadiv_Name }}</td><td>{{ $ad->Kadiv_Date }}</td></tr>
                                <tr><td><strong>Ast. Man. Ops. Support &amp; Controller</strong></td><td>{{ $ad->AstOps_Name }}</td><td>{{ $ad->AstOps_Date }}</td></tr>
                                <tr><td><strong>VP Operation</strong></td><td>{{ $ad->VPO_Name }}</td><td>{{ $ad->VPO_Date }}</td></tr>
                                <tr><td><strong>Manager HCGA</strong></td><td>{{ $ad->HCGA_Name }}</td><td>{{ $ad->HCGA_Date }}</td></tr>
                                <tr><td><strong>Direktur</strong></td><td>{{ $ad->Direktur_Name }}</td><td>{{ $ad->Direktur_Date }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>