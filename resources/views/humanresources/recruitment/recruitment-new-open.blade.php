<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Form - GEU</title>
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
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Auth;
    ?>
    <div class="container-fluid p-md-5 p-0">
        <div class="card">
            <div class="card-header" style="background-color: rgba(105, 178, 87, 1); color: white;padding:10px;">
                <h5 class="card-title m-2" style="color:#f8f9fa">New Recruitment Data Employee</h5>
            </div>
            <div class="card-body" id="ContentPages">
                <p>Please fill in the recruitment data completely and correctly.</p>
                <form>
					<div id="ContentPages">
						<div class="row">
							<div class="col-6">
								<div class="form-group">
									<label for="Position">Position</label>
									
									<select id='Position' class="form-control">
										<option value="">-- Pilih Posisi yang dilamar --</option>
										<?php
										$q = DB::table("requisition")->where("Status","CREATED")->where("Location", 'GEU-HO')->orderBy("ID","DESC")->get();
										foreach ($q as $fa){
												echo "<option value='$fa->Task_ID'>$fa->Task_ID - $fa->Job_Position ($fa->Department)</option>";	
										}
										?>
									</select>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Name">Name of Candidate</label>
									<input type="text" id="Name" size="50" class="form-control"/>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Gender">Gender</label>
									<select id="Gender" class="form-control">
										<option>MALE</option>
										<option>FEMALE</option>
									</select>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Birth_Date">Birth Date</label>
								
									<input type="text" id="Birth_Date" size="15" value="<?php echo date('1990-m-d');?>" class="form-control bg-white" readonly />
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Birth_Place">Birth Place</label>
									<input type="text" id="Birth_Place" size="35" class="form-control" />
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="KTP">Citizen ID/KTP</label>
									<input type="text" id="KTP" size="35" class="form-control" />
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Address">Address</label>
									<textarea cols="100" rows="4" id="Address" class="form-control"></textarea>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="City">City</label>
									<input type="text" name="City" id="City" class="form-control" />
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Phone">Phone</label>
									<input type="text" name="Phone" id="Phone" class="form-control" />
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Handphone">WhatsApp</label>
									<input type="text" name="Handphone" id="Handphone" class="form-control" />
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Marital_Status">Martial Status</label>
									<select name="Marital_Status" id="Marital_Status" class="form-control">
										<option>SINGLE</option>
										<option>MARRIED</option>
										<option>WIDOWER</option>
										<option>WIDOW</option>
									</select>
								</div>
							</div>
							<div class="col-6">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="Last_Education">Last Education From</label>
											<select name="Last_Education" id="Last_Education" class="form-control">
												<option>SMA/SMK</option>
												<option>D1</option>
												<option>D2</option>
												<option>D3</option>
												<option>D4</option>
												<option>S1</option>
												<option>S2</option>
												<option>S3</option>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="Last_Education_From">Name</label>
											<input type="text" id="Last_Education_From" name="Last_Education_From" class="form-control" />
										</div>
									</div>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Email">Email Address</label>
									<input type="text" name="Email" id="Email" class="form-control" />
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="Religion">Religion</label>
									<select name="Religion" id="Religion" class="form-control">
										<option>ISLAM</option>
										<option>PROTESTAN</option>
										<option>KATHOLIK</option>
										<option>BUDHA</option>
										<option>HINDU</option>
									</select>
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label for="kewarganegaraan">Citizenship</label>
									<input type="text" name="kewarganegaraan" id="kewarganegaraan" class="form-control" />
								</div>
							</div>
							<div class="col-12">
								<div id="divtom">
									<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" />                
								</div>
							</div>
						</div>
					</div>
					<div id="DivProcess"></div>
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

		$('#SubmitCustDetail').click(function(){

			gettingVars = getAllValues();

			$.ajax({
				headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url : "recruitment-new-submit?_token={{ csrf_token() }}",
				type: "POST",
				data : gettingVars,
				success: function(data, textStatus, jqXHR)
				{
					var dataPos = data.indexOf("success");
					if (dataPos != '-1'){
						var cariAWB = data.split(":");
							alert('Data New Recruitment Submitted!');

							location.href="recruitment-new";
					}else{
						alert('Recruitment Failed to Submit!');
					}
				},
				error: function (jqXHR, textStatus, errorThrown)
				{
				alert( "Error Internal! Please Try again!" );
			
				}
			});
		})

		function getAllValues() {
				var inputValues = [];
				$('#ContentPages input,select,textarea').each(function() {
					var type = $(this).attr("type");
					if ((type == "checkbox" || type == "radio") && this.checked) {
						inputValues.push($(this).attr("id") + "=" + $(this).val());
					}
					else if (type != "button" || type != "submit") {
						inputValues.push($(this).attr("id") + "=" + $(this).val());
					} else if ($this.is("textarea")) {
						inputValues.push($(this).attr("id") + "=" + $(this).val());
					}
				})
				return inputValues.join('&');
		}


		
		var myCalendar;
			myCalendar = new dhtmlXCalendarObject(["Birth_Date","End_Date"]);
			myCalendar.setDateFormat("%Y-%m-%d");

    </script>
</body>

</html>