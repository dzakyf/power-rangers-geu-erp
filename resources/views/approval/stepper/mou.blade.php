<div>
    <h5>Step 2</h5>
    <p>Penandatanganan MOU</p>

    <div class="mt-2 mb-2 row">
        <!-- <div class="col">
            <a href="#" class="downloadMOU">Download Template MOU</a>
        </div> -->
    </div>
    <div class="mt-2 mb-2 row">
        <div class="col">
            <div class="form-check">
                <!-- <input class="form-check-input" type="checkbox" id="mouAgreement" required> -->
                <!-- <label class="form-check-label" for="mouAgreement">
                    Saya telah membaca dengan seksama MOU & Menyetujui kesepakatan yang tertuang didalamnya
                </label> -->
            </div>
            <!-- <a href="#" class="uploadMOU">Unggah MOU</a>
						<input type="file" class="inputMOU" accept=".pdf" style="display:none;"> -->
            <label for="File_Documents">Upload Tanda Tangan Pengajuan</label>
            <!-- <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
            <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" /> -->

            @include('ui-components.signature-canvas', [
            'saveUrl' => "/geu/hcga/public/partner-onboarding-preview-mou?id=43",
            'saveDocumentUrl' => "/geu/hcga/public/partner-onboarding-submit-mou?id=43"]);
        </div>
    </div>
</div>

<button class="btn btn-secondary btn-prev">Previous</button>
<button class="btn btn-primary btn-next">Next</button>

</div>

@push('mou-script')
<script>
    let userId = null;


    var vault = new dhx.Vault("File_Documents", {
        uploader: {
            target: "/geu/hcga/public/partner-onboarding-submit-mou",
            params: {
                '_token': '{{ csrf_token() }}',
            }
        },
        filesLimit: 1
    });

    vault.events.on("BeforeUploadFile", function(file) {
        let opp_id = window.location.href.split('/').pop()
        userId = opp_id

        if (vault && vault.uploader && vault.uploader.config) {
            vault.uploader.config.params.opportunity_id = opp_id;
        }
    });
</script>
@endpush