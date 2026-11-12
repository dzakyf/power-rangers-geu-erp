<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Form - GEU</title>
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
    <link href="{{ asset('css/font-uniform.css') }}" rel="stylesheet">

    <script src="{{ asset('vault.js?v=3.0.0')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('vault.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/timeline.min.css') }}" />

    <script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
    <script src="{{ asset('jquery.coolfieldset.js') }}"></script>
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram.css') }}">
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram-editor.css') }}">
    <link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram.css') }}">

    <script src="{{ asset('dhtmlx/diagram.js')}}"></script>
    <script src="{{ asset('dhtmlx/diagram-editor.js')}}"></script>
    <script src="{{ asset('highcharts/highcharts.js')}}"></script>
    <script src="{{ asset('highcharts/modules/exporting.js')}}"></script>

    <script src="{{ asset('/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>

    <!-- Feather Icon JS -->
    <script src="{{ asset('/assets/js/feather.min.js') }}"></script>
    <!-- Gllery viewer JS -->
    <script src="{{ asset('/assets/js/viewer.min.js') }}"></script>

    <!-- ApexCharts JS -->
    <script type="text/javascript" src="jquery.mask.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('/assets/js/custom.js') }}"></script>
    <script src="{{ asset('/assets/js/apex-charts/apexcharts.min.js') }}"></script>

    <script src="{{ asset('jquery.ajaxloader.1.5.1.js') }}"></script>
    <script src="{{ asset('blockui.js') }}"></script>
</head>

<body class="p-md-5 p-0" style="background-color: #f8f9fa;">
    <?php
    $LSP = "GEU";
    $Saya = 'guest';
    $leadID = 'BP/' . $LSP . '/' . date('Ymdhis') . '/' . substr(md5($Saya), 0, 3);
    ?>
    <div class="container-fluid p-md-5 p-0">
        <div class="card">
            <div class="card-header" style="background-color: rgba(105, 178, 87, 1); color: white;padding:10px;">
                <h5 class="card-title m-2" style="color:#f8f9fa">New Partner (Leads) Data</h5>
            </div>
            <div class="card-body" id="ContentPages">
                <p>Please fill in the partner (leads) data completely and correctly.</p>
                <form>
                    <div class="form-group row">
                        <label for="Task_ID" class="col-sm-2 col-form-label">Lead ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="Task_ID" name="Task_ID" value="{{ $leadID }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Date" class="col-sm-2 col-form-label">Tanggal</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="Date" name="Date" value="{{ date('Y-m-d') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Full_Name" class="col-sm-2 col-form-label">Nama Lengkap</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="Full_Name" name="Full_Name" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Phone_Number" class="col-sm-2 col-form-label">Nomor Telepon Aktif</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="Phone_Number" name="Phone_Number" />
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label class="col-sm-2 col-form-label"><strong>Bagaimana Anda mengetahui program kemitraan ini?</strong></label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Instagram" value="Instagram" />
                                        <label class="form-check-label" for="Source_Instagram">Instagram</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Facebook" value="Facebook" />
                                        <label class="form-check-label" for="Source_Facebook">Facebook</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="Source_Channel" id="Source_TikTok" value="TikTok" />
                                        <label class="form-check-label" for="Source_TikTok">TikTok</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Website" value="Website" />
                                        <label class="form-check-label" for="Source_Website">Website</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Event" value="Event" />
                                        <label class="form-check-label" for="Source_Event">Pameran/Acara</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Minat Kemitraan</label>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Partnership_Interest" id="Partnership_Interest_1" value="1">
                                <label class="form-check-label" for="Partnership_Interest_1">Mobil Listrik</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Partnership_Interest" id="Partnership_Interest_2" value="2">
                                <label class="form-check-label" for="Partnership_Interest_2">Booth</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Estimasi Modal (RP)</label>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Capital_Estimation" id="Capital_Estimation_1" value="1">
                                <label class="form-check-label" for="Capital_Estimation_1">
                                    <50 Juta</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Capital_Estimation" id="Capital_Estimation_2" value="2">
                                <label class="form-check-label" for="Capital_Estimation_2">100 - 200 Juta</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Capital_Estimation" id="Capital_Estimation_3" value="3">
                                <label class="form-check-label" for="Capital_Estimation_3">200 - 300 Juta</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Capital_Estimation" id="Capital_Estimation_4" value="4">
                                <label class="form-check-label" for="Capital_Estimation_4">>300 Juta</label>
                            </div>
                        </div>
                    </div>
                    <div id="divtom">
                        <input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        var myCalendar;
        myCalendar = new dhtmlXCalendarObject("Date");
        myCalendar.setDateFormat("%Y-%m-%d");
        $('#SubmitCustDetail').click(function() {

            gettingVars = getAllValues();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "sales-opportunity-new-submit?_token={{ csrf_token() }}",
                type: "POST",
                data: gettingVars,
                success: function(data, textStatus, jqXHR) {
                    var dataPos = data.indexOf("success");
                    if (dataPos != '-1') {
                        var cariAWB = data.split(":");
                        alert('Data Leads Submitted!');

                        location.reload();
                    } else {
                        var cariAWB = data.split("||");
                        alert(cariAWB[0]);
                        $('#' + cariAWB[1]).focus();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error Internal! Please Try again!");

                }
            });
        })

        function getAllValues() {
            var inputValues = [];
            $('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
                var type = $(this).attr("type");
                if ((type == "checkbox" || type == "radio") && this.checked) {
                    inputValues.push($(this).attr("name") + "=" + $(this).val());
                } else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
                    inputValues.push($(this).attr("id") + "=" + $(this).val());
                } else if ($(this).is("textarea")) {
                    inputValues.push($(this).attr("id") + "=" + $(this).val());
                }
            })
            return inputValues.join('&');
        }
    </script>
</body>

</html>