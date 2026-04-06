@extends('layout.mainlayout-mitra')
@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Proyeksi</span>
        </li>
    </ol>
</div>

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Buat proyeksi</h5>
    <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->

    <div class="col-md-6">
        <div class="form-group">
            <label for="Hub_Type">Template Proyeksi<span style="color: red;">*</span></label>
            <div class="">
                <a href="projection-template">Download template</a>
            </div>
        </div>
        <div id="SelectMitra" class="form-group">
            <label for="Mitra">Pilih Mitra <span style="color: red;">*</span></label>
            <select name="Mitra" id="Mitra" class="form-control" required>
                <option value="">-- Pilih Mitra--</option>
            </select>
        </div>
        <!-- <div id="SelectWarehouse" class="form-group">
            <label for="Warehouse">Gudang <span style="color: red;">*</span></label>
            <select name="Warehouse" id="Warehouse" class="form-control" required>
                <option value="">-- Pilih Gudang--</option>
            </select>
        </div> -->

        <div class="form-group">
            <label for="File_Documents">Upload Proyeksi</label>
            <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
            <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" />
        </div>
    </div>

</div>

<script>
    let mitraId = null;
    $(document).ready(function() {
        $('#Mitra').select2({
            ajax: {
                url: 'partner-data-active-mitra',
                dataType: 'xml',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(xmlData) {
                    var results = [];

                    $(xmlData).find('row').each(function() {
                        var cells = $(this).find('cell');

                        results.push({
                            id: cells.eq(0).text(),
                            text: cells.eq(1).text(),
                            cell2: cells.eq(2).text(),
                        });
                    });

                    return {
                        results: results,
                        pagination: {
                            more: results.length === 20
                        }
                    };
                }
            },
            minimumInputLength: 0,
            placeholder: 'Cari Mitra...',
            allowClear: true
        });

        $('#Mitra').on('select2:select', function(e) {
            var data = e.params.data;

            mitraId = data.id
            console.log('Selected:', mitraId);
        });

        $('#Mitra').on('select2:unselect', function(e) {
            console.log('Unselected');
        });


        // $('#Warehouse').select2({
        //     ajax: {
        //         url: 'partner-data-active-mitra',
        //         dataType: 'xml',
        //         delay: 300,
        //         data: function(params) {
        //             return {
        //                 search: params.term,
        //                 page: params.page || 1
        //             };
        //         },
        //         processResults: function(xmlData) {
        //             var results = [];

        //             $(xmlData).find('row').each(function() {
        //                 var cells = $(this).find('cell');

        //                 results.push({
        //                     id: cells.eq(0).text(),
        //                     text: cells.eq(1).text(),
        //                     cell2: cells.eq(2).text(),
        //                 });
        //             });

        //             return {
        //                 results: results,
        //                 pagination: {
        //                     more: results.length === 20
        //                 }
        //             };
        //         }
        //     },
        //     minimumInputLength: 0,
        //     placeholder: 'Cari Gudang...',
        //     allowClear: true
        // });

        // $('#Warehouse').on('select2:select', function(e) {
        //     var data = e.params.data;


        //     opportunityId = data.opportunityId
        //     console.log('Selected:', opportunityId);
        // });

        // $('#Warehouse').on('select2:unselect', function(e) {
        //     console.log('Unselected');
        // });
    })
    let type = null;
    let year = null;
    let userId = null;

    $("#Type, #Year").on('change', function() {
        $("#File_Documents").data('type', $("#Type").val());
        $("#File_Documents").data('year', $("#Year").val());
    });

    var vault = new dhx.Vault("File_Documents", {
        uploader: {
            target: "projection-upload",
            params: {
                '_token': '{{ csrf_token() }}',
            }
        },
        filesLimit: 1
    });

    vault.events.on("BeforeUploadFile", function(file) {
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('id');
        if (vault && vault.uploader && vault.uploader.config) {
            vault.uploader.config.params.user_id = mitraId || userId;
        }
    });
</script>
@endsection