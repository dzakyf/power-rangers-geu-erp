@extends('layout.mainlayout-cns')

@section('content-wrapper')
@php
    $isEdit = $allocation !== null;
    $isFinal = $isEdit && $allocation->isFinal();
    $defaultRate = $isEdit ? (float) $allocation->default_exchange_rate : 16000;
    $currentSiNo = $isEdit ? $allocation->si_no : '';
    $currentMeta = $currentSiNo && isset($siMeta[$currentSiNo]) ? $siMeta[$currentSiNo] : [];
@endphp

@include('cns.partials.page-header', [
    'title' => $isEdit ? 'Cost Allocation: ' . $allocation->allocation_no : 'New Cost Allocation per Shipment',
    'breadcrumbs' => [
        ['label' => 'Export Management'],
        ['label' => 'Cost Control'],
        ['label' => 'Cost Allocation per Shipment', 'url' => route('cns-export-cost-allocation.index')],
        ['label' => $isEdit ? $allocation->allocation_no : 'New'],
    ],
])

<form id="costAllocationForm" method="POST" action="{{ $isEdit ? route('cns-export-cost-allocation.update', $allocation->id) : route('cns-export-cost-allocation.store') }}">
    @csrf

    <div class="card cns-card">
        <div class="card-header">
            <h5 class="card-title mb-0">Shipment Reference (Shipping Instruction)</h5>
            @if($isEdit)
                <span class="badge rounded-pill cns-status-badge {{ $isFinal ? 'bg-success' : 'bg-warning text-dark' }}">{{ $allocation->status }}</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <label class="small font-weight-bold">Shipping Instruction (SI No) <span class="text-danger">*</span></label>
                    @if($isEdit)
                        <input type="text" class="form-control form-control-sm" value="{{ $allocation->si_no }}{{ $allocation->final_si_no ? ' / ' . $allocation->final_si_no : '' }}" readonly>
                    @else
                        <select name="si_id" id="si_id" class="form-control form-control-sm select2-si" required>
                            <option value="">-- Pilih Shipping Instruction --</option>
                            @foreach($shipments as $s)
                                <option value="{{ $s['id'] }}"
                                    data-si="{{ $s['si_no'] }}"
                                    data-final-si="{{ $s['final_si_no'] }}"
                                    data-consignee="{{ $s['consignee'] }}"
                                    data-vessel="{{ $s['vessel_name'] }}"
                                    data-pod="{{ $s['pod'] }}"
                                    data-mt="{{ $s['volume_mt'] }}"
                                    data-ctr="{{ $s['containers'] }}">
                                    {{ $s['si_no'] }}{{ $s['final_si_no'] !== '-' ? ' (' . $s['final_si_no'] . ')' : '' }} — {{ $s['consignee'] }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold">Default Kurs (IDR / 1 USD) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="default_exchange_rate" id="default_exchange_rate" class="form-control form-control-sm" value="{{ $defaultRate }}" {{ $readOnly ? 'readonly' : '' }} required>
                </div>
                <div class="col-md-5 mb-2">
                    <label class="small font-weight-bold">Catatan Umum</label>
                    <input type="text" name="notes" class="form-control form-control-sm" value="{{ $isEdit ? $allocation->notes : '' }}" {{ $readOnly ? 'readonly' : '' }} placeholder="Catatan opsional untuk shipment ini">
                </div>
            </div>

            <div class="row mt-2 pt-2 border-top small text-muted">
                <div class="col-md-3">Consignee: <strong id="info_consignee" class="text-dark">{{ $currentMeta['consignee'] ?? '-' }}</strong></div>
                <div class="col-md-3">Vessel / POD: <strong id="info_vessel" class="text-dark">{{ ($currentMeta['vessel_name'] ?? '-') . ' / ' . ($currentMeta['pod'] ?? '-') }}</strong></div>
                <div class="col-md-3">Volume (MT): <strong id="info_mt" class="text-dark">{{ $isEdit ? number_format((float) $allocation->volume_mt, 3) : '0.000' }}</strong> MT</div>
                <div class="col-md-3">Containers: <strong id="info_ctr" class="text-dark">{{ $isEdit ? (int) $allocation->container_count : 0 }}</strong> Ctr</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Total Cost (USD)</div>
                    <div class="h5 mb-0 font-weight-bold text-primary">$ <span id="summary_total_usd">0.00</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Total Cost (IDR)</div>
                    <div class="h5 mb-0 font-weight-bold text-dark">Rp <span id="summary_total_idr">0</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Cost / MT (USD)</div>
                    <div class="h5 mb-0 font-weight-bold text-success">$ <span id="summary_cost_mt">0.00</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Cost / Container (USD)</div>
                    <div class="h5 mb-0 font-weight-bold text-info">$ <span id="summary_cost_ctr">0.00</span></div>
                </div>
            </div>
        </div>
    </div>

    @php $rowIndex = 0; @endphp
    @foreach($categories as $catKey => $catLabel)
        <div class="card cns-card category-card" data-category="{{ $catKey }}">
            <div class="card-header">
                <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h5 class="card-title mb-0">{{ $catLabel }}</h5>
                    <span class="badge badge-light">Subtotal: $ <span class="cat-subtotal-usd" data-cat="{{ $catKey }}">0.00</span></span>
                </div>
                @if(!$readOnly)
                    <button type="button" class="btn btn-xs btn-outline-primary btn-add-item" data-cat="{{ $catKey }}">
                        <i class="fa fa-plus"></i> Tambah {{ $catLabel }}
                    </button>
                @endif
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:22%">Deskripsi Biaya</th>
                                <th style="width:15%">Vendor / Pihak</th>
                                <th style="width:12%">No. Ref / Inv</th>
                                <th style="width:8%">Curr</th>
                                <th style="width:12%">Nominal</th>
                                <th style="width:11%">Kurs (IDR/USD)</th>
                                <th style="width:10%" class="text-right">USD Eqv</th>
                                <th style="width:10%" class="text-right">IDR Eqv</th>
                                @if(!$readOnly)<th style="width:4%"></th>@endif
                            </tr>
                        </thead>
                        <tbody class="cat-tbody" data-cat="{{ $catKey }}">
                            @foreach($itemsByCat[$catKey] as $item)
                                <tr class="cost-item-row" data-cat="{{ $catKey }}">
                                    <td>
                                        <input type="hidden" name="items[{{ $rowIndex }}][category]" value="{{ $catKey }}">
                                        <input type="text" name="items[{{ $rowIndex }}][description]" class="form-control form-control-sm" value="{{ $item->description }}" {{ $readOnly ? 'readonly' : '' }} required>
                                    </td>
                                    <td><input type="text" name="items[{{ $rowIndex }}][vendor_name]" class="form-control form-control-sm" value="{{ $item->vendor_name }}" {{ $readOnly ? 'readonly' : '' }}></td>
                                    <td><input type="text" name="items[{{ $rowIndex }}][reference_no]" class="form-control form-control-sm" value="{{ $item->reference_no }}" {{ $readOnly ? 'readonly' : '' }}></td>
                                    <td>
                                        <select name="items[{{ $rowIndex }}][currency]" class="form-control form-control-sm item-currency" {{ $readOnly ? 'disabled' : '' }}>
                                            <option value="USD" {{ $item->currency === 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="IDR" {{ $item->currency === 'IDR' ? 'selected' : '' }}>IDR</option>
                                        </select>
                                        @if($readOnly)<input type="hidden" name="items[{{ $rowIndex }}][currency]" value="{{ $item->currency }}">@endif
                                    </td>
                                    <td><input type="number" step="0.01" min="0" name="items[{{ $rowIndex }}][amount]" class="form-control form-control-sm text-right item-amount" value="{{ (float) $item->amount }}" {{ $readOnly ? 'readonly' : '' }} required></td>
                                    <td><input type="number" step="0.01" min="1" name="items[{{ $rowIndex }}][exchange_rate]" class="form-control form-control-sm text-right item-rate" value="{{ (float) $item->exchange_rate }}" {{ $readOnly ? 'readonly' : '' }} required></td>
                                    <td class="text-right align-middle item-usd-display">0.00</td>
                                    <td class="text-right align-middle item-idr-display">0</td>
                                    @if(!$readOnly)
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-xs btn-danger btn-remove-item"><i class="fa fa-times"></i></button>
                                        </td>
                                    @endif
                                </tr>
                                @php $rowIndex++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('cns-export-cost-allocation.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
        <div>
            @if(!$readOnly)
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan Draft
                </button>
            @endif
            @if($isEdit && !$isFinal && function_exists('cns_can') && cns_can('cost_allocation_shipment', 'edit'))
                <button type="button" id="btnFinalize" class="btn btn-success ml-2">
                    <i class="fa fa-check"></i> Finalize
                </button>
            @endif
            @if($isEdit && $isFinal && function_exists('cns_can') && cns_can('cost_allocation_shipment', 'edit'))
                <button type="button" id="btnReopen" class="btn btn-warning ml-2">
                    <i class="fa fa-unlock"></i> Reopen to Draft
                </button>
            @endif
        </div>
    </div>
</form>

@if($isEdit)
    <form id="finalizeForm" method="POST" action="{{ route('cns-export-cost-allocation.finalize', $allocation->id) }}" class="d-none">@csrf</form>
    <form id="reopenForm" method="POST" action="{{ route('cns-export-cost-allocation.reopen', $allocation->id) }}" class="d-none">@csrf</form>
@endif

<script>
$(function () {
    var nextIndex = {{ $rowIndex }};
    var shipmentMt = {{ $isEdit ? (float) $allocation->volume_mt : 0 }};
    var shipmentCtr = {{ $isEdit ? (int) $allocation->container_count : 0 }};

    if ($.fn.select2 && $('.select2-si').length) {
        $('.select2-si').select2({ width: '100%' });
    }

    $('#si_id').on('change', function () {
        var opt = $(this).find(':selected');
        shipmentMt = parseFloat(opt.data('mt')) || 0;
        shipmentCtr = parseInt(opt.data('ctr'), 10) || 0;
        $('#info_consignee').text(opt.data('consignee') || '-');
        $('#info_vessel').text((opt.data('vessel') || '-') + ' / ' + (opt.data('pod') || '-'));
        $('#info_mt').text(shipmentMt.toFixed(3));
        $('#info_ctr').text(shipmentCtr);
        recalcAll();
    });

    function fmtNum(val, dec) {
        return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }

    function recalcAll() {
        var defaultRate = parseFloat($('#default_exchange_rate').val()) || 16000;
        var catTotals = { freight: 0, trucking: 0, surveyor: 0, port: 0 };
        var grandUsd = 0;
        var grandIdr = 0;

        $('.cost-item-row').each(function () {
            var $row = $(this);
            var cat = $row.data('cat');
            var curr = $row.find('.item-currency').val() || 'USD';
            var amount = parseFloat($row.find('.item-amount').val()) || 0;
            var rate = parseFloat($row.find('.item-rate').val()) || defaultRate;
            if (rate <= 0) rate = defaultRate;

            var usd = curr === 'IDR' ? (amount / rate) : amount;
            var idr = curr === 'IDR' ? amount : (amount * rate);

            $row.find('.item-usd-display').text(fmtNum(usd, 2));
            $row.find('.item-idr-display').text(fmtNum(idr, 0));

            if (catTotals.hasOwnProperty(cat)) {
                catTotals[cat] += usd;
            }
            grandUsd += usd;
            grandIdr += idr;
        });

        $.each(catTotals, function (cat, val) {
            $('.cat-subtotal-usd[data-cat="' + cat + '"]').text(fmtNum(val, 2));
        });

        $('#summary_total_usd').text(fmtNum(grandUsd, 2));
        $('#summary_total_idr').text(fmtNum(grandIdr, 0));
        $('#summary_cost_mt').text(fmtNum(shipmentMt > 0 ? (grandUsd / shipmentMt) : 0, 2));
        $('#summary_cost_ctr').text(fmtNum(shipmentCtr > 0 ? (grandUsd / shipmentCtr) : 0, 2));
    }

    $(document).on('click', '.btn-add-item', function () {
        var cat = $(this).data('cat');
        var defaultRate = parseFloat($('#default_exchange_rate').val()) || 16000;
        var idx = nextIndex++;
        var html = '<tr class="cost-item-row" data-cat="' + cat + '">' +
            '<td><input type="hidden" name="items[' + idx + '][category]" value="' + cat + '">' +
            '<input type="text" name="items[' + idx + '][description]" class="form-control form-control-sm" placeholder="Nama biaya" required></td>' +
            '<td><input type="text" name="items[' + idx + '][vendor_name]" class="form-control form-control-sm" placeholder="Vendor"></td>' +
            '<td><input type="text" name="items[' + idx + '][reference_no]" class="form-control form-control-sm" placeholder="No. Inv"></td>' +
            '<td><select name="items[' + idx + '][currency]" class="form-control form-control-sm item-currency"><option value="USD">USD</option><option value="IDR">IDR</option></select></td>' +
            '<td><input type="number" step="0.01" min="0" name="items[' + idx + '][amount]" class="form-control form-control-sm text-right item-amount" value="0" required></td>' +
            '<td><input type="number" step="0.01" min="1" name="items[' + idx + '][exchange_rate]" class="form-control form-control-sm text-right item-rate" value="' + defaultRate + '" required></td>' +
            '<td class="text-right align-middle item-usd-display">0.00</td>' +
            '<td class="text-right align-middle item-idr-display">0</td>' +
            '<td class="text-center align-middle"><button type="button" class="btn btn-xs btn-danger btn-remove-item"><i class="fa fa-times"></i></button></td>' +
            '</tr>';
        $('.cat-tbody[data-cat="' + cat + '"]').append(html);
        recalcAll();
    });

    $(document).on('click', '.btn-remove-item', function () {
        $(this).closest('tr').remove();
        recalcAll();
    });

    $(document).on('input change', '.item-currency, .item-amount, .item-rate, #default_exchange_rate', recalcAll);

    $('#btnFinalize').on('click', function () {
        swal({
            title: 'Finalize Cost Allocation?',
            text: 'Data akan dikunci dan tidak bisa diedit sebelum di-reopen.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Finalize'
        }, function (ok) {
            if (ok) $('#finalizeForm').submit();
        });
    });

    $('#btnReopen').on('click', function () {
        swal({
            title: 'Reopen ke Draft?',
            text: 'Data akan dibuka kembali agar dapat diedit.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reopen'
        }, function (ok) {
            if (ok) $('#reopenForm').submit();
        });
    });

    recalcAll();
});
</script>
@endsection
