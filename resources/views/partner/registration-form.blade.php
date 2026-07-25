<div class="">
    <div class="card">
        <div class="card-body">
            <form id="registrationForm" enctype="multipart/form-data">
                @csrf
                <h5 class="text-center">Form Registrasi Partnership</h5>
                <input type="hidden" name="registration_token" id="registration_token" value="{{ $_GET['token'] ?? '' }}">

                <div class="badge badge-info w-100 my-3 py-3">INFORMASI PARTNER</div>
                @include('form.registration-individual')
                <!-- Company Section -->
                @include('form.registration-company')

                {{-- <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_NPWP_File" />
                <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Doc_Others_File" /> --}}

                <div class="form-group mt-4">
                    <button type="button" id="SubmitCustDetail" class="btn btn-success btn-lg">Kirim</button>
                </div>
            </form>
        </div>
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

    // ========== Address chained select helpers ==========
    function makeSelect2(selector, url, params, valueField, labelField) {
        $(selector).select2({
            placeholder: '-- Pilih --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    var extra = typeof params === 'function' ? params() : params;
                    return Object.assign({ q: p.term || '' }, extra);
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return { id: row[valueField], text: row[labelField] };
                        })
                    };
                },
                cache: true
            }
        });
    }

    function resetSelect2(selector) {
        $(selector).val(null).trigger('change').prop('disabled', true);
        $(selector).empty().trigger('change');
    }

    function initAddressChained(prefix) {
        var pSel = '#' + prefix + 'Address_Province';
        var cSel = '#' + prefix + 'Address_City';
        var dSel = '#' + prefix + 'Address_Subdistrict';
        var sSel = '#' + prefix + 'Address_Ward';
        var zSel = '#' + prefix + 'Address_Postal';

        function resetChain() {
            resetSelect2(cSel);
            resetSelect2(dSel);
            resetSelect2(sSel);
            resetSelect2(zSel);
        }

        $(pSel).on('change', function() {
            resetChain();
            var province = $(this).val();
            if (!province) return;
            $(cSel).prop('disabled', false);
            makeSelect2(cSel, 'master-city', function() { return { province: $(pSel).val() }; }, 'City', 'City');
        });

        $(cSel).on('change', function() {
            resetSelect2(dSel);
            resetSelect2(sSel);
            resetSelect2(zSel);
            var city = $(this).val();
            if (!city) return;
            $(dSel).prop('disabled', false);
            makeSelect2(dSel, 'master-district', function() { return { province: $(pSel).val(), city: $(cSel).val() }; }, 'District', 'District');
        });

        $(dSel).on('change', function() {
            resetSelect2(sSel);
            resetSelect2(zSel);
            var district = $(this).val();
            if (!district) return;
            $(sSel).prop('disabled', false);
            makeSelect2(sSel, 'master-subdistrict', function() { return { province: $(pSel).val(), city: $(cSel).val(), district: $(dSel).val() }; }, 'Sub_District', 'Sub_District');
        });

        $(sSel).on('change', function() {
            resetSelect2(zSel);
            var sub = $(this).val();
            if (!sub) return;
            $(zSel).prop('disabled', false);
            makeSelect2(zSel, 'master-postal-code', function() { return { province: $(pSel).val(), city: $(cSel).val(), district: $(dSel).val(), subdistrict: $(sSel).val() }; }, 'ZipCode', 'ZipCode');
        });

        // Initially disable all except province
        resetChain();
        // Init province
        makeSelect2(pSel, 'master-province', {}, 'Province', 'Province');
    }

    function initAddressChainedWhenReady() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            setTimeout(initAddressChainedWhenReady, 50);
            return;
        }
        initAddressChained('I_');
        initAddressChained('C_');
    }
    initAddressChainedWhenReady();</script>
@endpush
