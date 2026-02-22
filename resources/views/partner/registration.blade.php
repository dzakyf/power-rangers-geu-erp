<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

// $LSP = Auth::user()->Location;
$Saya =  Auth::user()->username ?? "";

// $LSP = "GEU-HO";
// $Saya = "admin";
$pic = DB::table('users')->where('Priv_ID', 'SALES')->get();
$province = DB::table('master_kodepos')->select("Province")->groupBy("Province")->orderBy("Province", "asc")->get();

// $leadID = "L/BP/".date('Y')."/".sprintf('%02d',date('m'))."/".strtoupper(substr(uniqid(), 0, 4));

// $leadID = 'BP/'.$LSP.'/'.date('Ymdhis').'/'.substr(md5($Saya),0,3);
$leadID = Auth::user()->Emp_ID ?? "";
?>

@extends('layout.mainlayout-mitra')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('footer')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Sales</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Opportunity</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">New Data (Leads)</span>
		</li>
	</ol>
</div>

@include('partner.registration-form')

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
			$('#Estimated_Revenue').mask('#,##0.00', { reverse: true });
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
                // vaultKTP.clearAll();
            }
        });

        $('#Doc_NPWP').change(function() {
            if (this.checked) {
                $('#Doc_NPWP_Files_Div').show('swing');
            } else {
                $('#Doc_NPWP_Files_Div').hide('swing');
                $('#Doc_NPWP_File').val('');
                // vaultNPWP.clearAll();
            }
        });

        $('#Doc_Others').change(function() {
            if (this.checked) {
                $('#Doc_Others_Files_Div').show('swing');
            } else {
                $('#Doc_Others_Files_Div').hide('swing');
                $('#Doc_Others_File').val('');
                // doc_Others.clearAll();
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
                // vaultKTP.clearAll();
            }
        });

        $('#C_Doc_NIB').change(function() {
            if (this.checked) {
                $('#C_Doc_NIB_Files_Div').show('swing');
            } else {
                $('#C_Doc_NIB_Files_Div').hide('swing');
                $('#C_Doc_NIB_File').val('');
                // vaultKTP.clearAll();
            }
        });

        $('#C_Doc_NPWP').change(function() {
            if (this.checked) {
                $('#C_Doc_NPWP_Files_Div').show('swing');
            } else {
                $('#C_Doc_NPWP_Files_Div').hide('swing');
                $('#C_Doc_NPWP_File').val('');
                // vaultKTP.clearAll();
            }
        });

        $('#C_Doc_Others').change(function() {
            if (this.checked) {
                $('#C_Doc_Others_Files_Div').show('swing');
            } else {
                $('#C_Doc_Others_Files_Div').hide('swing');
                $('#C_Doc_Others_File').val('');
                // vaultKTP.clearAll();
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
                processResults: function (data) {
                    // Tranforms the top-level key of the response object from 'items' to 'results'
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.Province,
                                id: item.Province
                            };
                        })
                    };
                },
                cache: true,
            }
        }).on('change', function () {
            $('#City').empty(); // clear previous options
            $('#City').val(null).trigger('change'); // reset selection
            $('#City').select2({
                placeholder: "Pilih Kota",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: function (params) {
                        return 'city/' + btoa($('#Province').val());
                    },
                    dataType: 'json',
                    type: "POST",
                    delay: 250,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function (data) {
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: $.map(data, function (item) {
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

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "sales-opportunity-registration-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
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
@endsection