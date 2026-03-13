<div>
    <h5>Step 2</h5>
    <p>Penandatanganan PKS</p>

    <div class="mt-2 mb-2 row">
        <div class="col">
            <a href="#" class="downloadPKS">Download Template Perjanjian Kerjasama</a>
        </div>
    </div>
    <div class="mt-2 mb-2 row">
        <div class="col">
            <div class="form-check">
            </div>
            <label for="File_Documents">Upload Pengajuan</label>
            <div id="File_PKS" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
            <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_PKS" />
        </div>
    </div>
</div>

<button class="btn btn-secondary btn-prev">Previous</button>
<button class="btn btn-primary btn-next">Next</button>

</div>

@push('pks-script')
<script>
    var vault = new dhx.Vault("File_PKS", {
        uploader: {
            target: "/geu/hcga/public/partner-onboarding-submit-pks",
            params: {
                '_token': '{{ csrf_token() }}',
            }
        },
        filesLimit: 1
    });

    vault.events.on("BeforeUploadFile", function(file) {
        let opp_id = window.location.href.split('/').pop()


        if (vault && vault.uploader && vault.uploader.config) {
            vault.uploader.config.params.opportunity_id = opp_id;
        }
    });


    $('.downloadPKS').on('click', function() {
        generatePKS()
    })

    function generatePKS() {
        const pathSegments = window.location.pathname.split('/');
        const id = pathSegments[pathSegments.length - 1];

        $.ajax({
            url: `generate-pks?id=${id}`,
            type: "GET",
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response, status, xhr) {
                var blob = new Blob([response]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'pks.docx';
                link.click();

                window.URL.revokeObjectURL(link.href);
            },
            error: function(err) {
                alert("Telah terjadi kesalahan.");
            }
        })
    }
</script>
@endpush