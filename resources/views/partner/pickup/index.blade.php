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
            <span class="active">Branding</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">POO</div>
    <div class="card">
        <div class="card-body">
            <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
                <input type="button" id="detailGrid1" name="detailGrid1" value="Detail Data" class="btn btn-info mt-2 mr-2" />

            </div>
        </div>
    </div>

    <div id="Grid1Detail" class="row ml-3 m-1" style="display:none;">
        @include('partner.poo.edit')
    </div>
</div>


<script>
    var marketingId = null;

    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateApplication() {
        window.location.href = `master-poo-create`
    }

    function initTableGrid1() {
        let tHeader = ['No', null, 'Type', 'Name', 'PIC', 'Code', 'Employee', 'Phone', 'Position', 'Category', 'UCO Type', 'Price', 'Price Unit', 'Province', 'City', 'Subdistrict', 'Village', 'Cycle', 'Full Address', 'Location Coordinate', 'Nearest Warehouse', 'Bank Account Number', 'Bank Account Name', 'Bank Name', 'Point', 'Created At', 'Updated At']
        let tWidth = tHeader.map((e, i) => i == 1 ? 0 : '150')
        let headerAttachment = tHeader.map((e, i) => i <= 1 ? '#rspan' : '#text_filter')
        let colId = ['No', 'ID', 'Type', 'Name', 'PIC', 'Code', 'Employee', 'Phone', 'Position', 'Category', 'UCO_Type', 'Price', 'Price_Unit', 'Province', 'City', 'Subdistrict', 'Village', 'Cycle', 'Full_Address', 'Location_Coordinate', 'Nearest_Warehouse', 'Bank_Account_Number', 'Bank_Account_Name', 'Bank_Name', 'Point', 'Created_At', 'Updated_At'].join(',')


        Grid1 = new dhtmlXGridObject('MembershipGrid');
        Grid1.setImagePath("./dhtmlx/imgs/");
        Grid1.setHeader(tHeader.join(','));
        Grid1.setInitWidths(tWidth.join(','));
        Grid1.attachHeader(headerAttachment);
        Grid1.setColTypes(tHeader.map((e, i) => {
            return 'ro';
        }).join(','));

        Grid1.setColumnIds(colId);
        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");


        Grid1.init();

        Grid1.clearAndLoad('master-poo-data?format=xml');
    }

    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
        getPOOData()
    }


    function getPOOData() {
        var id = marketingId;
        if (!id) {
            alert('Pilih data terlebih dulu');
            return;
        }

        $.ajax({
            url: 'master-poo-data?format=json&id=' + id,
            type: 'GET',
            success: function(res) {
                var data = res.data[0];
                if (!data) return;

                $('#Type').val(data.Type);
                $('#Name').val(data.Name);
                $('#PIC').val(data.PIC);
                $('#Code').val(data.Code);
                $('#Employee').val(data.Employee);
                $('#Phone').val(data.Phone);
                $('#Position').val(data.Position);
                $('#Category').val(data.Category);
                $('#UCO_Type').val(data.UCO_Type);
                $('#Price').val(data.Price);
                $('#Price_Unit').val(data.Price_Unit);
                $('#Cycle').val(data.Cycle);
                $('#Full_Address').val(data.Full_Address);
                $('#Location_Coordinate').val(data.Location_Coordinate);
                $('#Nearest_Warehouse').val(data.Nearest_Warehouse);
                $('#Bank_Account_Number').val(data.Bank_Account_Number);
                $('#Bank_Account_Name').val(data.Bank_Account_Name);
                $('#Bank_Name').val(data.Bank_Name);
                $('#Point').val(data.Point);

                // Prefill chained selects in order
                setSelect2Val('#Province', data.Province);
                $('#City').prop('disabled', false);
                makeSelect2('#City', 'master-city', function() {
                    return {
                        province: $('#Province').val()
                    };
                }, 'City', 'City');
                setSelect2Val('#City', data.City);

                $('#Subdistrict').prop('disabled', false);
                makeSelect2('#Subdistrict', 'master-district', function() {
                    return {
                        province: $('#Province').val(),
                        city: $('#City').val()
                    };
                }, 'District', 'District');
                setSelect2Val('#Subdistrict', data.Subdistrict);

                $('#Village').prop('disabled', false);
                makeSelect2('#Village', 'master-subdistrict', function() {
                    return {
                        province: $('#Province').val(),
                        city: $('#City').val(),
                        district: $('#Subdistrict').val()
                    };
                }, 'Sub_District', 'Sub_District');
                setSelect2Val('#Village', data.Village);
            },
            error: function() {
                alert('Gagal memuat data POO.');
            }
        });
    }

    $('#detailGrid1').on('click', function() {
        if (!marketingId) {
            alert('Pilih data terlebih dulu');
            return;
        }
        $('#ID').val(marketingId);
        $('#Grid1Detail').show();
        getPOOData();
    });

    function selectMitra(id, cellIdx) {
        marketingId = Grid1.getRowAttribute(id, 'ID');

        console.log(marketingId)
        $('#marketingId').val(marketingId)
    }

    function doubleClickTableGrid1() {
        initDetailTableGrid1()
    }

    $('#updatePOODetailForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('POO_Id', marketingId)

        $('#submitBtn').prop('disabled', true).text('Submitting...');

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response, status, xhr) {
                alert("Berhasil mengupdate info pengajuan")
                // $('#updateMarketinDetailForm')[0].reset()
            },
            error: function(xhr, status, error) {
                console.log('Error details:', xhr.responseText);

            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).text('Submit');
                $('#marketingId').val(marketingId)
            }
        });

    })
</script>
@endsection