<div class="">
    <div class="card">
        <div class="card-body">
            <h5 class="text-center">Form Registrasi Partnership</h5>
            <input type="hidden" name="lead_id" id="lead_id" value="{{ $_GET['leads'] ?? '' }}">

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

@push('registration-script')
<script>
    $('input[name="Register_As"]').change(function() {
        var val = $(this).val();
        if (val === 'company') {
            $('#IndividualDiv').hide();
            $('#IndividualDiv input[type="text"], #IndividualDiv textarea').val('');
            $('#IndividualDiv input[type="checkbox"], #IndividualDiv input[type="radio"]').prop('checked', false).trigger('change');
            $('#IndividualDiv select').val(null).trigger('change');
            $('#CompanyDiv').show();
        } else {
            $('#CompanyDiv').hide();
            $('#CompanyDiv input[type="text"], #CompanyDiv textarea').val('');
            $('#CompanyDiv input[type="checkbox"], #CompanyDiv input[type="radio"]').prop('checked', false).trigger('change');
            $('#CompanyDiv select').val(null).trigger('change');
            $('#IndividualDiv').show();
        }
    });
    // $(document).ready(function() {
    //     loadProvince()
    // })



    // function loadProvince() {
    //     $('#Address_Province').select2({
    //         placeholder: "Loading province...",
    //         allowClear: true,
    //         ajax: ajaxConfig('province', 'get'),
    //         minimumInputLength: 0
    //     })
    // }

    // function ajaxConfig(url, method) {
    //     return {
    //         url: url,
    //         type: method,
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         dataType: 'json',
    //         delay: 250,
    //         processResults: function(d) {
    //             console.log(d.data)
    //             let res = $.map(d.data, function(item) {
    //                 return {
    //                     text: item.District,
    //                     id: item.District
    //                 };
    //             })

    //             return {
    //                 results: res
    //             }
    //         },
    //         cache: true
    //     }
    // }

    function bindGpsButton(btnId, latId, lngId, statusId) {
        $(btnId).on("click", function() {
            if (!navigator.geolocation) {
                $(statusId).text("Geolocation not supported").css("color", "red");
                return;
            }
            var btn = this;
            $(btn).prop("disabled", true).text("Locating...");
            $(statusId).text("Getting location...").css("color", "blue");
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    $(latId).val(pos.coords.latitude);
                    $(lngId).val(pos.coords.longitude);
                    $(statusId).text("Location acquired").css("color", "green");
                    $(btn).prop("disabled", false).text("Get GPS");
                },
                function(err) {
                    $(statusId).text("Error: " + err.message).css("color", "red");
                    $(btn).prop("disabled", false).text("Get GPS");
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });
    }

    bindGpsButton("#btnGetGps", "#Latitude", "#Longitude", "#gpsStatus");
    bindGpsButton("#btnGetGpsCompany", "#Company_Latitude", "#Company_Longitude", "#gpsStatusCompany");
</script>
@endpush