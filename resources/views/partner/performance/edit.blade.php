@extends('layout.mainlayout-mitra')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Legal Compliance</span>
        </li>
    </ol>
</div>

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Form Edit KPI</h5>

    {{-- Loading state --}}
    <div id="formLoading" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-2">Loading KPI data...</p>
    </div>

    {{-- Error state --}}
    <div id="formError" class="alert alert-danger d-none">
        Failed to load KPI data. Please refresh the page or check the KPI ID.
    </div>

    <form id="kpiForm" action="partner-performance-kpi-submit-edit" enctype="multipart/form-data" class="d-none">
        @csrf

        {{-- Work_Area --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Work_Area">Work Area <span style="color: red;">*</span></label>
                    <input type="text" name="Work_Area" id="Work_Area" class="form-control" required />
                </div>
            </div>
        </div>

        {{-- Indicator --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Indicator">Indicator <span style="color: red;">*</span></label>
                    <textarea name="Indicator" id="Indicator" class="form-control" rows="3" required></textarea>
                </div>
            </div>
        </div>

        {{-- Weight --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Weight">Weight (%) <span style="color: red;">*</span></label>
                    <input type="number" name="Weight" id="Weight" class="form-control" step="0.01" min="0" max="100" required />
                </div>
            </div>
        </div>

        {{-- Target --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Target">Target <span style="color: red;">*</span></label>
                    <textarea name="Target" id="Target" class="form-control" rows="3" required></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Realization">Realisasi <span style="color: red;">*</span></label>
                    <textarea name="Realization" id="Realization" class="form-control" rows="3" required></textarea>
                </div>
            </div>
        </div>

        {{-- Departement --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Departement">Departement <span style="color: red;">*</span></label>
                    <input type="text" name="Departement" id="Departement" class="form-control" required />
                </div>
            </div>
        </div>

        {{-- Periode --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Periode">Periode <span style="color: red;">*</span></label>
                    <input type="date" name="Periode" id="Periode" class="form-control" required />
                </div>
            </div>
        </div>

        <input type="hidden" name="ID" id="ID" />

        <button type="submit" class="btn btn-primary" id="submitBtn">Update</button>
        <a href="javascript:history.back()" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        // ─── Column mapping (positional, 0-indexed) ───────────────────────
        const XML_COLUMNS = {
            ID: 0,
            Work_Area: 1,
            Indicator: 2,
            Weight: 3,
            Target: 4,
            Periode: 7,
            Departement: 6,
        };

        // ─── Parse XML response into a plain object ───────────────────────
        function parseKpiXml(xmlDoc) {
            const cells = $(xmlDoc).find('row').first().find('cell');

            return {
                ID: $(cells[XML_COLUMNS.ID]).text().trim(),
                Work_Area: $(cells[XML_COLUMNS.Work_Area]).text().trim(),
                Indicator: $(cells[XML_COLUMNS.Indicator]).text().trim(),
                Weight: $(cells[XML_COLUMNS.Weight]).text().replace('%', '').trim(),
                Target: $(cells[XML_COLUMNS.Target]).text().trim(),
                Periode: $(cells[XML_COLUMNS.Periode]).text().trim(),
                Departement: $(cells[XML_COLUMNS.Departement]).text().trim(),
            };
        }

        // ─── Get kpi_id from URL ──────────────────────────────────────────
        function getQueryParam(param) {
            return new URLSearchParams(window.location.search).get(param);
        }

        const kpiId = getQueryParam('kpi_id');

        if (!kpiId) {
            $('#formLoading').addClass('d-none');
            $('#formError').text('No KPI ID provided in the URL.').removeClass('d-none');
            return;
        }

        // ─── Fetch & pre-fill ─────────────────────────────────────────────
        $.ajax({
            url: 'partner-performance-kpi-data?kpi_id=' + kpiId,
            type: 'GET',
            dataType: 'xml',
            success: function(xmlDoc) {
                const data = parseKpiXml(xmlDoc);

                $('#ID').val(kpiId);
                $('#Work_Area').val(data.Work_Area);
                $('#Indicator').val(data.Indicator);
                $('#Weight').val(data.Weight);
                $('#Target').val(data.Target);
                $('#Departement').val(data.Departement);

                // Format date to YYYY-MM-DD if Periode has a value
                if (data.Periode) {
                    const formatted = new Date(data.Periode).toISOString().split('T')[0];
                    $('#Periode').val(formatted);
                }

                $('#formLoading').addClass('d-none');
                $('#kpiForm').removeClass('d-none');
            },
            error: function(xhr) {
                console.error('Failed to load KPI data:', xhr.responseText);
                $('#formLoading').addClass('d-none');
                $('#formError').removeClass('d-none');
            }
        });

        // ─── Submit ───────────────────────────────────────────────────────
        $('#kpiForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function() {
                    alert('KPI berhasil diperbarui.');
                },
                error: function(xhr) {
                    console.error('Submit error:', xhr.responseText);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Update');
                }
            });
        });

    });
</script>
@endsection