@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Onboarding Materials</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Human Resources</a></li>
        <li class="breadcrumb-item active"><span>Onboarding Materials</span></li>
    </ol>
</div>

<div class="card" id="mainCard">
    <div class="card-header p-2">
        <h5 class="mb-0">Onboarding Materials</h5>
    </div>
    <div class="card-body">
        <div class="overflow-auto">
            <div id="onboardingGrid" style="width:100%;height:400px;background:#fff;"></div>
        </div>
        <div class="mt-3">
            <input type="button" class="btn btn-primary mr-2" value="Create New" onclick="loadInlineForm('{{ url('hrd-onboarding-materials-create') }}')">
            <input type="button" class="btn btn-warning mr-2" value="Edit Selected" onclick="editSelected()">
            <input type="button" class="btn btn-danger" value="Delete Selected" onclick="deleteSelected()">
        </div>
    </div>
</div>
<div id="inlineFormContainer" class="d-none mt-3"></div>

<script>
    var onboardingGrid = new dhtmlXGridObject('onboardingGrid');
    onboardingGrid.setImagePath('{{ asset("dhtmlx/imgs/") }}/');
    onboardingGrid.setHeader("No.,Title,Category,Description,Dokumen,Created By,Created At");
    onboardingGrid.setInitWidthsP("5,20,15,25,15,10,10");
    onboardingGrid.setColAlign("center,left,left,left,center,left,center");
    onboardingGrid.setColTypes("ro,ro,ro,ro,link,ro,ro");
    onboardingGrid.setSkin("dhx_skyblue");
    onboardingGrid.init();
    onboardingGrid.load('{{ url("xml-hrd-onboarding-materials") }}', function() {
        onboardingGrid.setSizes();
    });

    $(window).resize(function() {
        if (onboardingGrid.entBox) onboardingGrid.setSizes();
    });

    function loadInlineForm(url) {
        $('#inlineFormContainer').removeClass('d-none').html('<div class="text-center py-4">Loading...</div>').load(url, function() {
            $('#inlineFormContainer').find('input[type="text"]:first').focus();
        });
        $('html, body').animate({
            scrollTop: $('#inlineFormContainer').offset().top - 20
        }, 300);
    }

    function closeInlineForm() {
        $('#inlineFormContainer').addClass('d-none').empty();
        refreshData();
    }

    function editSelected() {
        var id = onboardingGrid.getSelectedId();
        if (!id) {
            alert('Please select a row first.');
            return;
        }
        loadInlineForm('{{ url("hrd-onboarding-materials-edit") }}?id=' + id);
    }

    function deleteSelected() {
        var id = onboardingGrid.getSelectedId();
        if (!id) {
            alert('Please select a row first.');
            return;
        }
        dhtmlx.confirm('Delete this material?', 'Confirm', function(r) {
            if (r) {
                $.ajax({
                    url: '{{ url("/hrd-onboarding-materials-delete") }}',
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            alert('Material deleted.');
                            refreshData();
                        } else {
                            alert(res.message || 'Error deleting material.');
                        }
                    }
                });
            }
        });
    }

    function refreshData() {
        onboardingGrid.clearAndLoad('{{ url("xml-hrd-onboarding-materials") }}', function() {
            onboardingGrid.setSizes();
        });
    }
</script>
@endsection