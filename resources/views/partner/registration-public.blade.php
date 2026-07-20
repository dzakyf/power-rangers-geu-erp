<?php

use Illuminate\Support\Facades\DB;

$LSP = 'GEU-HO';
$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();
$province = DB::table('master_kodepos')->select("Province")->groupBy("Province")->orderBy("Province", "asc")->get();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Registration - GEU</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/LineIcons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/viewer.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icofont.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/calendar.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon2.png') }}">
    <link href="{{ asset('ajaxloader/ajaxloader.css') }}" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolautosuggest.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolfieldset.css') }}" />

    <script src="{{ asset('jquery2.min.js')}}"></script>
    <script src="{{ asset('timeline.min.js')}}"></script>

    <script>
        function LoadM(DivID, PathID) {
            $("#" + DivID).ajaxloader(PathID);
        }
    </script>

    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/dhtmlx.css') }}">
    <script src="{{ asset('dhtmlx/dhtmlx.js')}}"></script>

    <script type="text/javascript" src="{{ asset('./jquery.confirm.js') }}"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.0.0.js"></script>

    <link rel="stylesheet" type="text/css" href="{{ asset('form_input.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('./jquery-confirm.css') }}" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">

    <script src="{{ asset('vault.js?v=3.0.0')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('vault.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/timeline.min.css') }}" />

    <script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
    <script src="{{ asset('jquery.coolfieldset.js') }}"></script>
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram.css') }}">
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram-editor.css') }}">

    <script src="{{ asset('dhtmlx/diagram.js')}}"></script>
    <script src="{{ asset('dhtmlx/diagram-editor.js')}}"></script>
    <script src="{{ asset('highcharts/highcharts.js')}}"></script>
    <script src="{{ asset('highcharts/modules/exporting.js')}}"></script>

    <script src="{{ asset('/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('/assets/js/feather.min.js') }}"></script>
    <script src="{{ asset('/assets/js/viewer.min.js') }}"></script>

    <script type="text/javascript" src="jquery.mask.min.js"></script>

    <script src="{{ asset('/assets/js/custom.js?v=2') }}"></script>
    <script src="{{ asset('/assets/js/apex-charts/apexcharts.min.js') }}"></script>

    <script src="{{ asset('jquery.ajaxloader.1.5.1.js') }}"></script>
    <script src="{{ asset('blockui.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body class="p-md-5 p-0" style="background-color: #f8f9fa;">
    <div class="container-fluid p-md-5 p-0">
        @include('partner.registration-form')

    </div>
    </div>

    <script>
        // toggle sections based on Register As
        $(function() {
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

            // initialize mask if field exists
            if ($('#Estimated_Revenue').length) {
                $('#Estimated_Revenue').mask('#,##0.00', {
                    reverse: true
                });
            }

            var vaultKTP = new dhx.Vault("Doc_KTP_Files", {
                uploader: {
                    target: "upload-receiveinvoice.php"
                },
                filesLimit: 1

            });

            vaultKTP.events.on("uploadComplete", function(obj) {
                $("#Doc_KTP_File").val(obj[0].name);
            });
            vaultKTP.events.on("uploadBegin", function(obj) {
                $("#Doc_KTP_File").val("");

            });
            vaultKTP.events.on("beforeAdd", function(fileItem) {
                if (vaultKTP.data.getLength() >= 1) {
                    alert('Only can upload 1 file per checkpoint.');
                    return false;
                }
            });


            vaultKTP.events.on("beforeAdd", function(item) {
                var extension = item.file.name.split(".").pop();

                var predicate = extension === "pdf";
                if (!predicate) {
                    dhtmlx.alert("Hanya Boleh mengupload tipe file PDF");
                }
                return predicate;
            });

            var vaultNPWP = new dhx.Vault("Doc_NPWP_Files", {
                uploader: {
                    target: "upload-receiveinvoice.php"
                },
                filesLimit: 1

            });

            vaultNPWP.events.on("uploadComplete", function(obj) {
                $("#Doc_NPWP_File").val(obj[0].name);
            });
            vaultNPWP.events.on("uploadBegin", function(obj) {
                $("#Doc_NPWP_File").val("");

            });
            vaultNPWP.events.on("beforeAdd", function(fileItem) {
                if (vaultNPWP.data.getLength() >= 1) {
                    alert('Only can upload 1 file per checkpoint.');
                    return false;
                }
            });

            var doc_Others = new dhx.Vault("Doc_Others_Files", {
                uploader: {
                    target: "upload-receiveinvoice.php"
                },
                filesLimit: 1

            });

            doc_Others.events.on("uploadComplete", function(obj) {
                $("#Doc_Others_File").val(obj[0].name);
            });
            doc_Others.events.on("uploadBegin", function(obj) {
                $("#Doc_Others_File").val("");

            });
            doc_Others.events.on("beforeAdd", function(fileItem) {
                if (doc_Others.data.getLength() >= 1) {
                    alert('Only can upload 1 file per checkpoint.');
                    return false;
                }
            });


            doc_Others.events.on("beforeAdd", function(item) {
                var extension = item.file.name.split(".").pop();

                var predicate = extension === "pdf";
                if (!predicate) {
                    dhtmlx.alert("Hanya Boleh mengupload tipe file PDF");
                }
                return predicate;
            });

            $('#Doc_KTP').change(function() {
                if (this.checked) {
                    $('#Doc_KTP_Files_Div').show('swing');
                } else {
                    $('#Doc_KTP_Files_Div').hide('swing');
                    $('#Doc_KTP_File').val('');
                }
            });

            $('#Doc_NPWP').change(function() {
                if (this.checked) {
                    $('#Doc_NPWP_Files_Div').show('swing');
                } else {
                    $('#Doc_NPWP_Files_Div').hide('swing');
                    $('#Doc_NPWP_File').val('');
                }
            });

            $('#Doc_Others').change(function() {
                if (this.checked) {
                    $('#Doc_Others_Files_Div').show('swing');
                } else {
                    $('#Doc_Others_Files_Div').hide('swing');
                    $('#Doc_Others_File').val('');
                }
            });

            var vaultAkta = new dhx.Vault("C_Doc_Akta_Files", {
                uploader: {
                    target: "upload-receiveinvoice.php"
                },
                filesLimit: 1

            });

            vaultAkta.events.on("uploadComplete", function(obj) {
                $("#C_Doc_Akta_File").val(obj[0].name);
            });
            vaultAkta.events.on("uploadBegin", function(obj) {
                $("#C_Doc_Akta_File").val("");

            });
            vaultAkta.events.on("beforeAdd", function(fileItem) {
                if (vaultAkta.data.getLength() >= 1) {
                    alert('Only can upload 1 file per checkpoint.');
                    return false;
                }
            });

            var vaultNIB = new dhx.Vault("C_Doc_NIB_Files", {
                uploader: {
                    target: "upload-receiveinvoice.php"
                },
                filesLimit: 1

            });

            vaultNIB.events.on("uploadComplete", function(obj) {
                $("#C_Doc_NIB_File").val(obj[0].name);
            });
            vaultNIB.events.on("uploadBegin", function(obj) {
                $("#C_Doc_NIB_File").val("");

            });
            vaultNIB.events.on("beforeAdd", function(fileItem) {
                if (vaultNIB.data.getLength() >= 1) {
                    alert('Only can upload 1 file per checkpoint.');
                    return false;
                }
            });

            var vaultCNPWP = new dhx.Vault("C_Doc_NPWP_Files", {
                uploader: {
                    target: "upload-receiveinvoice.php"
                },
                filesLimit: 1

            });

            vaultCNPWP.events.on("uploadComplete", function(obj) {
                $("#C_Doc_NPWP_File").val(obj[0].name);
            });
            vaultCNPWP.events.on("uploadBegin", function(obj) {
                $("#C_Doc_NPWP_File").val("");

            });
            vaultCNPWP.events.on("beforeAdd", function(fileItem) {
                if (vaultCNPWP.data.getLength() >= 1) {
                    alert('Only can upload 1 file per checkpoint.');
                    return false;
                }
            });

            var vaultCOthers = new dhx.Vault("C_Doc_Others_Files", {
                uploader: {
                    target: "upload-receiveinvoice.php"
                },
                filesLimit: 1

            });

            vaultCOthers.events.on("uploadComplete", function(obj) {
                $("#C_Doc_Others_File").val(obj[0].name);
            });
            vaultCOthers.events.on("uploadBegin", function(obj) {
                $("#C_Doc_Others_File").val("");

            });
            vaultCOthers.events.on("beforeAdd", function(fileItem) {
                if (vaultCOthers.data.getLength() >= 1) {
                    alert('Only can upload 1 file per checkpoint.');
                    return false;
                }
            });

            $('#C_Doc_Akta').change(function() {
                if (this.checked) {
                    $('#C_Doc_Akta_Files_Div').show('swing');
                } else {
                    $('#C_Doc_Akta_Files_Div').hide('swing');
                    $('#C_Doc_Akta_File').val('');
                }
            });

            $('#C_Doc_NIB').change(function() {
                if (this.checked) {
                    $('#C_Doc_NIB_Files_Div').show('swing');
                } else {
                    $('#C_Doc_NIB_Files_Div').hide('swing');
                    $('#C_Doc_NIB_File').val('');
                }
            });

            $('#C_Doc_NPWP').change(function() {
                if (this.checked) {
                    $('#C_Doc_NPWP_Files_Div').show('swing');
                } else {
                    $('#C_Doc_NPWP_Files_Div').hide('swing');
                    $('#C_Doc_NPWP_File').val('');
                }
            });

            $('#C_Doc_Others').change(function() {
                if (this.checked) {
                    $('#C_Doc_Others_Files_Div').show('swing');
                } else {
                    $('#C_Doc_Others_Files_Div').hide('swing');
                    $('#C_Doc_Others_File').val('');
                }
            });

            $('#Province').select2({
                placeholder: "Pilih Provinsi",
                allowClear: true,
                width: '100%',

                ajax: {
                    url: 'province',
                    dataType: 'json',
                    type: "POST",
                    delay: 250,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.Province,
                                    id: item.Province
                                };
                            })
                        };
                    },
                    cache: true,
                }
            }).on('change', function() {
                $('#City').empty();
                $('#City').val(null).trigger('change');
                $('#City').select2({
                    placeholder: "Pilih Kota",
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: function(params) {
                            return 'city/' + btoa($('#Province').val());
                        },
                        dataType: 'json',
                        type: "POST",
                        delay: 250,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.City,
                                        id: item.City
                                    };
                                })
                            };
                        },
                        cache: true,
                    }
                });
            });
        });

        function isValidKey(e) {
            var charCode = e.keyCode || e.which;
            if (charCode == 46) return false;
            return true;
        }
        var myCalendar;
        myCalendar = new dhtmlXCalendarObject("Date");
        myCalendar.setDateFormat("%Y-%m-%d");
        $('#SubmitCustDetail').click(function() {
            var formData = new FormData(document.getElementById('registrationForm'));

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "partner-registration-submit",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(data, textStatus, jqXHR) {
                    var dataPos = data.indexOf("success");
                    if (dataPos != '-1') {
                        alert('Data Kemitraan Berhasil Dikirim!');
                        location.reload();
                    } else {
                        var cariAWB = data.split("||");
                        alert(cariAWB[0]);
                        $('#' + cariAWB[1]).focus();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Kesalahan Internal! Silakan coba lagi!");
                }
            });
        })
    </script>
    @stack('registration-script')
</body>

</html>
