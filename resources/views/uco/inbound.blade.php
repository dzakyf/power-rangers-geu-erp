@php
$sigCanvas = view('ui-components.signature-canvas-field', [
'id' => 'pickup_sig', // fixed ID so you can always reference it
'name' => 'signature',
'label' => 'Signature',
'required' => true,
'height' => 200,
])->render();
@endphp


@extends('layout.mainlayout-mitra')


@section('content-wrapper')

<!-- Main Content Header -->
<div class="main-content-header">
    <h1>INVENTORY</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="./">Unused Crude Oil (UCO)</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">INBOUND TIMBANG</span>
        </li>
    </ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
    <div id="InboundTimbang" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
    <input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="InboundTimbang.toExcel('./export/generate.php');" />
    <input type="button" id="RedirectToCreateInboundData" NAME="RefreshBM" value="Buat Data" class="btn btn-info mt-2 mr-2" />
    <input type="button" id="RequestPickup" NAME="RefreshBM" value="Request Penjemputan" class="btn btn-info mt-2 mr-2" />
</div>


@push('scripts')
<script>
    let totalWeight = 0;
    let unit = 'kg';
    $(document).ready(function() {
        $('#RedirectToCreateInboundData').on('click', function() {
            window.location.href = `partner-uco-inbound-timbang-create`
        })

        $('#RequestPickup').on('click', function() {
            AppModal.open({
                title: 'Request Pickup',
                size: 'lg',
                body: `
            <form id="pickupForm" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                     <label for="photoDocument">
                        Total Berat
                    </label>
                    <p>${totalWeight} ${unit}</p>
                </div>
                <div class="form-group">
                    <label for="photoDocument">
                        Photo Document <span class="text-danger">*</span>
                    </label>

                    <div class="custom-file">
                        <input 
                            type="file" 
                            class="custom-file-input" 
                            id="photoDocument" 
                            name="photo_document"
                            accept="image/*,.pdf"
                        >
                        <label class="custom-file-label" for="photoDocument">
                            Choose file...
                        </label>
                    </div>

                    <small class="form-text text-muted">
                        Accepted formats: JPG, PNG, PDF. Max size: 2MB.
                    </small>

                    <!-- Preview container -->
                    <div id="photoPreview" class="mt-2" style="display:none;">
                        <img id="previewImg" src="" alt="Preview" 
                             class="img-thumbnail" style="max-height:150px;">
                    </div>
                </div>
                {{-- other fields --}}
                {!! $sigCanvas !!}
            </form>
        `,
                actions: [{
                    label: 'Submit Request',
                    class: 'btn-primary',
                    id: 'submitPickupBtn',
                    onClick: function() {
                        $('#pickupForm').trigger('submit');
                        return false;
                    }
                }]
            });

            // Re-init after modal is visible so canvas has real dimensions
            $('#appModal').one('shown.bs.modal', function() {
                Signature.init('pickup_sig');
            });
        });


        $(document).on('submit', '#pickupForm', function(e) {
            e.preventDefault();

            // Validate before submitting
            if (!Signature.validate('pickup_sig', 'Please provide your signature.')) return;

            var $btn = $('#submitPickupBtn');
            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Submitting...'
            );

            $.ajax({
                url: '/pickup/request',
                method: 'POST',
                data: new FormData(this), // picks up signature hidden input automatically
                processData: false,
                contentType: false,
                success: function() {
                    AppModal.close();
                    location.reload();
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('Submit Request');
                    var errors = xhr.responseJSON?.errors ?
                        Object.values(xhr.responseJSON.errors).flat().join('<br>') :
                        'Something went wrong.';
                    $('#pickupForm').prepend('<div class="alert alert-danger">' + errors + '</div>');
                }
            });
        });


        $(document).on('change', '#photoDocument', function() {
            var fileName = this.files[0] ? this.files[0].name : 'Choose file...';
            $(this).next('.custom-file-label').text(fileName);

            // Image preview
            if (this.files[0] && this.files[0].type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#photoPreview').show();
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                $('#photoPreview').hide();
            }
        });

    })

    InboundTimbang = new dhtmlXGridObject('InboundTimbang');
    InboundTimbang.setImagePath("./dhtmlx/imgs/");
    InboundTimbang.setHeader("No,Code,Point of Origin (POO),Weight-in,Unit,Picker,Dibuat Pada,Inbound Pickup");
    InboundTimbang.attachFooter("Total,#cspan,#cspan,#stat_total,#cspan,#cspan,#cspan,#cspan");
    InboundTimbang.setInitWidths("80,150,150,100,100,150,150,150");
    InboundTimbang.setColAlign("center,center,left,center,left,center,left,center");
    InboundTimbang.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
    InboundTimbang.setColSorting("int,str,str,str,str,str,str,str");
    InboundTimbang.setEditable(false);
    InboundTimbang.setSkin("dhx_skyblue");

    InboundTimbang.init();

    InboundTimbang.attachEvent("onXLE", function() {
        var cumulativeWeigth = getTotalWeight();

        totalWeight = cumulativeWeigth;
    });

    reloadgroup();

    function reloadgroup() {
        InboundTimbang.clearAndLoad("partner-uco-inbound-timbang-data");
    }

    function getTotalWeight() {
        var total = 0;
        var count = InboundTimbang.getRowsNum();

        for (var i = 0; i < count; i++) {
            var rowId = InboundTimbang.getRowId(i);
            var val = parseFloat(InboundTimbang.cells(rowId, 3).getValue()) || 0;
            total += val;
        }

        return total;
    }

    $("#RefreshBM").click(function() {
        reloadgroup();
    });
</script>
@endpush

@endsection