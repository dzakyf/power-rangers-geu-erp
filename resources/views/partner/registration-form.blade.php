<div class="">
    <div class="card">
        <div class="card-body">
            <h5 class="text-center">Form Registrasi Partnership</h5>

            <div class="badge badge-info w-100 my-3 py-3">INFORMASI PARTNER</div>

            @include('form.registration-individual')


            <!-- Company Section -->
            @include('form.registration-company')

        </div>

        <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_NPWP_File" />
        <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_Others_File" />

        <div class="form-group mt-4">
            <button type="button" id="SubmitCustDetail" class="btn btn-success btn-lg">Kirim</button>
        </div>
        </form>
    </div>
</div>

</div>
<div id="DivProcess"></div>

</div>