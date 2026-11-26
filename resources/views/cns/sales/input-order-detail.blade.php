<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<style>
  :root {
    --geu-green: skyblue;
    --geu-green-dark: #2d6b24;
    --geu-green-light: lightblue;
    --geu-green-mid: #6ab85e;
    --geu-header-bg: #3a8a30;
    --geu-gray: #f5f5f5;
    --geu-border: #c8e6c0;
    --geu-text: #2c2c2c;
  }

  /* ── CARD ── */
  .so-card {
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 4px 24px rgba(42, 100, 36, 0.13);
    overflow: hidden;
  }

  /* ── HEADER ── */
  .so-header {
    background: linear-gradient(135deg, var(--geu-green-dark) 0%, var(--geu-green) 100%);
    padding: 22px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
  }

  .so-header .brand-logo {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .so-header .logo-circle {
    width: 52px;
    height: 52px;
    background: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    color: var(--geu-green-dark);
    letter-spacing: -0.5px;
    border: 3px solid var(--geu-green-mid);
    flex-shrink: 0;
  }

  .so-header .brand-name {
    color: #fff;
  }

  .so-header .brand-name h1 {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 1px;
    margin: 0;
  }

  .so-header .brand-name p {
    font-size: 11px;
    margin: 0;
    opacity: 0.85;
  }

  .so-header .brand-contact {
    color: #d9f7d4;
    font-size: 11.5px;
    text-align: right;
    line-height: 1.7;
  }

  /* ── TITLE BAR ── */
  .so-titlebar {
    background: var(--geu-green-light);
    border-bottom: 3px solid var(--geu-green);
    padding: 14px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
  }

  .so-titlebar h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--geu-green-dark);
    letter-spacing: 2px;
    margin: 0;
    text-transform: uppercase;
  }

  /* ── SECTION HEADERS ── */
  .section-header {
    background: var(--geu-green);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 7px 14px;
    border-radius: 4px 4px 0 0;
    text-transform: uppercase;
  }

  .section-body {
    border: 1px solid var(--geu-border);
    border-top: none;
    border-radius: 0 0 4px 4px;
    padding: 16px 14px;
    background: #fafffe;
  }

  /* ── FORM CONTROLS ── */
  .form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--geu-green-dark);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
  }

  .form-control {
    font-size: 13.5px;
    border: 1.5px solid #c4dbbf;
    border-radius: 4px;
    padding: 7px 11px;
    color: var(--geu-text);
    transition: border-color .2s, box-shadow .2s;
  }

  .form-control:focus {
    border-color: var(--geu-green);
    box-shadow: 0 0 0 3px rgba(74, 155, 63, .15);
    outline: none;
  }

  textarea.form-control {
    resize: vertical;
  }

  /* ── ITEMS TABLE ── */
  .items-table-wrap {
    overflow-x: auto;
  }

  .items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .items-table thead tr {
    background: var(--geu-green);
  }

  .items-table thead th {
    color: #fff;
    font-size: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 10px 10px;
    font-weight: 600;
    white-space: nowrap;
  }

  .items-table tbody tr {
    border-bottom: 1px solid var(--geu-border);
  }

  .items-table tbody tr:nth-child(even) {
    background: var(--geu-green-light);
  }

  .items-table tbody td {
    padding: 7px 8px;
    vertical-align: middle;
  }

  .items-table .num-col {
    width: 44px;
    text-align: center;
  }

  .items-table .desc-col {
    width: 32%;
    min-width: 170px;
  }

  .items-table .stock-col {
    width: 10%;
    min-width: 90px;
  }

  .items-table .qty-col {
    width: 10%;
    min-width: 95px;
  }

  .items-table .unit-col {
    width: 10%;
    min-width: 95px;
  }

  .items-table .price-col,
  .items-table .total-col {
    width: 16%;
    min-width: 150px;
  }

  .items-table tfoot td {
    padding: 8px 10px;
  }

  .items-table tfoot tr.total-row {
    background: var(--geu-green);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
  }

  .items-table tfoot tr.subtotal-row {
    background: var(--geu-green-light);
  }

  /* ── ROW CONTROLS ── */
  .btn-add-row,
  .btn-remove-row {
    font-size: 12px;
    padding: 3px 10px;
    border-radius: 3px;
  }

  /* ── SUMMARY ── */
  .summary-table {
    width: 100%;
    font-size: 13.5px;
  }

  .summary-table td {
    padding: 5px 10px;
  }

  .summary-table .lbl {
    color: #666;
    font-weight: 500;
    text-align: right;
    width: 50%;
  }

  .summary-table .val {
    font-weight: 600;
    text-align: right;
  }

  .summary-table .total-lbl {
    font-weight: 700;
    color: var(--geu-green-dark);
    font-size: 15px;
  }

  .summary-table .total-val {
    font-weight: 700;
    color: var(--geu-green-dark);
    font-size: 15px;
  }

  .summary-divider {
    border-top: 2px solid var(--geu-green);
  }

  /* ── SIGNATORIES ── */
  .signatory-box {
    border: 1.5px solid var(--geu-border);
    border-radius: 5px;
    overflow: hidden;
  }

  .signatory-box .sig-header {
    background: var(--geu-green);
    color: #fff;
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 6px;
  }

  .signatory-box .sig-body {
    text-align: center;
    padding: 20px 10px 12px;
    background: #fafffe;
  }

  .sig-line {
    border-bottom: 1.5px solid #aaa;
    width: 80%;
    margin: 36px auto 6px;
  }

  .sig-name {
    font-weight: 600;
    font-size: 13px;
  }

  .sig-title {
    font-size: 11.5px;
    color: #666;
  }

  /* ── FOOTER ── */
  .so-footer {
    background: linear-gradient(90deg, var(--geu-green-dark), var(--geu-green));
    color: #d9f7d4;
    text-align: center;
    font-style: italic;
    font-size: 14px;
    font-weight: 500;
    padding: 13px;
    letter-spacing: 1px;
  }

  .so-footer i {
    color: #a5d6a7;
    margin: 0 6px;
  }

  /* ── SUBMIT ── */
  .btn-submit {
    background: linear-gradient(135deg, var(--geu-green-dark), var(--geu-green));
    color: #fff;
    border: none;
    font-size: 16px;
    letter-spacing: 1.5px;
    font-weight: 700;
    padding: 12px 40px;
    border-radius: 5px;
    text-transform: uppercase;
    transition: transform .15s, box-shadow .15s;
  }

  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(42, 100, 36, .35);
    color: #fff;
  }

  .btn-reset {
    background: #fff;
    color: var(--geu-green-dark);
    border: 2px solid var(--geu-green);
    font-size: 16px;
    letter-spacing: 1.5px;
    font-weight: 700;
    padding: 12px 32px;
    border-radius: 5px;
    text-transform: uppercase;
    transition: background .15s, color .15s;
  }

  .btn-reset:hover {
    background: var(--geu-green-light);
  }

  /* readonly look */
  .form-control[readonly] {
    background: #f2faf0;
    cursor: default;
  }

  @media print {
    body {
      background: #fff;
      padding: 0;
    }

    .no-print {
      display: none !important;
    }

    .so-card {
      box-shadow: none;
    }
  }

  /* ── APPROVAL STEPPER ── */
  .so-stepper {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 14px 16px;
    background: #f8fbf7;
    border: 1px solid #d9ebd5;
    border-radius: 6px;
  }
  .so-step {
    flex: 1;
    position: relative;
    text-align: center;
    padding: 0 6px;
  }
  .so-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 14px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: #dee2e6;
    z-index: 1;
  }
  .so-step.done:not(:last-child)::after {
    background: #28a745;
  }
  .so-step-circle {
    position: relative;
    z-index: 2;
    width: 30px;
    height: 30px;
    line-height: 28px;
    margin: 0 auto 6px;
    border-radius: 50%;
    background: #dee2e6;
    color: #495057;
    font-weight: 700;
    font-size: 13px;
  }
  .so-step.done .so-step-circle {
    background: #28a745;
    color: #fff;
  }
  .so-step.active .so-step-circle {
    background: #ffc107;
    color: #212529;
    box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3);
  }
  .so-step.rejected .so-step-circle {
    background: #dc3545;
    color: #fff;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
  }
  .so-step-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #2c2c2c;
  }
  .so-step-meta {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
  }
</style>
<div class="mt-3">
  <div class="cns-section-header">SALES ORDER DETAIL</div>
  <div class="card cns-card">
    <div class="card-body">
      @php
        $detailSoType = strtoupper((string) ($salesOrder->so_type ?? 'KEMASAN_AMPAS'));
        $isSludgeSo = $detailSoType === 'SLUDGE';
        $firstDetail = collect($details)->first();
      @endphp
      <div id="ContentPages">
        <input type="hidden" id="soId" value="{{ $salesOrder->id }}">
        <input type="hidden" id="soType" name="soType" value="{{ $detailSoType }}">
        <div class="FormInput_Form_Content">
          <!-- ── TITLE BAR ── -->
          <div class="so-titlebar">
            <h2><i class="fas fa-file-invoice mr-2"></i>Sales Order</h2>
            <div class="d-flex align-items-center gap-3 flex-wrap" style="gap:16px;">
              <span class="badge rounded-pill cns-status-badge {{ ($salesOrder->status ?? 'PENDING') === 'APPROVED' ? 'bg-success' : (($salesOrder->status ?? 'PENDING') === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                {{ $salesOrder->status ?? 'PENDING' }}
              </span>
              <div class="d-flex align-items-center">
                <label class="form-label mb-0 mr-2" style="white-space:nowrap;">DATE</label>
                <input type="date" id="soDate" class="text-black form-control form-control-sm" style="width:150px;" value="{{ $salesOrder->so_date }}" />
              </div>
              <div class="d-flex align-items-center ml-3">
                <label class="form-label mb-0 mr-2" style="white-space:nowrap;">SO CODE</label>
                <input type="text" id="soCode" class="text-black form-control form-control-sm" style="width:160px;" value="{{ $salesOrder->so_no }}" />
              </div>
            </div>
          </div>

          <div class="p-3 p-md-4">
            @php
              $soStatus = strtoupper((string) ($salesOrder->status ?? 'PENDING'));
              $isRejected = $soStatus === 'REJECTED';
              $step1Done = true;
              $step2Done = in_array($soStatus, ['APPROVED_GUDANG', 'SIGNED_BUYER', 'APPROVED'], true) || ($isRejected && !empty($salesOrder->gudang_approved_date));
              $step3Done = in_array($soStatus, ['SIGNED_BUYER', 'APPROVED'], true) || ($isRejected && !empty($salesOrder->buyer_signed_date));
              $step4Done = $soStatus === 'APPROVED';

              $steps = [
                [
                  'num' => 1,
                  'title' => 'SO Dibuat',
                  'done' => $step1Done,
                  'active' => false,
                  'rejected' => false,
                  'meta' => trim(($salesOrder->created_by ?? '-') . (($salesOrder->created_date ?? $salesOrder->so_date) ? ' • ' . ($salesOrder->created_date ?: $salesOrder->so_date) : '')),
                ],
                [
                  'num' => 2,
                  'title' => 'Kepala Gudang',
                  'done' => $step2Done,
                  'active' => $soStatus === 'PENDING',
                  'rejected' => $isRejected && !$step2Done,
                  'meta' => $step2Done ? trim(($salesOrder->gudang_approved_by ?? '-') . ($salesOrder->gudang_approved_date ? ' • ' . $salesOrder->gudang_approved_date : '')) : ($isRejected && !$step2Done ? 'Ditolak' : 'Menunggu'),
                ],
                [
                  'num' => 3,
                  'title' => 'TTD Buyer',
                  'done' => $step3Done,
                  'active' => $soStatus === 'APPROVED_GUDANG',
                  'rejected' => $isRejected && $step2Done && !$step3Done,
                  'meta' => $step3Done ? trim(($salesOrder->buyer_signed_by ?? '-') . ($salesOrder->buyer_signed_date ? ' • ' . $salesOrder->buyer_signed_date : '')) : ($isRejected && $step2Done && !$step3Done ? 'Ditolak' : 'Menunggu'),
                ],
                [
                  'num' => 4,
                  'title' => 'Manager CNS & SJ',
                  'done' => $step4Done,
                  'active' => $soStatus === 'SIGNED_BUYER',
                  'rejected' => $isRejected && $step3Done && !$step4Done,
                  'meta' => $step4Done
                    ? trim(($salesOrder->approved_by ?? '-') . ($salesOrder->surat_jalan_no ? ' • ' . $salesOrder->surat_jalan_no : ''))
                    : ($isRejected && $step3Done && !$step4Done ? 'Ditolak' : 'Menunggu'),
                ],
              ];
            @endphp
            <div class="so-stepper" id="soApprovalStepper">
              @foreach($steps as $st)
                <div class="so-step {{ $st['done'] ? 'done' : ($st['rejected'] ? 'rejected' : ($st['active'] ? 'active' : '')) }}">
                  <div class="so-step-circle">{{ $st['done'] ? '✓' : ($st['rejected'] ? '✕' : $st['num']) }}</div>
                  <div class="so-step-title">{{ $st['title'] }}</div>
                  <div class="so-step-meta">{{ $st['meta'] }}</div>
                </div>
              @endforeach
            </div>
            @if($isRejected)
              <div class="alert alert-danger py-2 px-3 mb-3">
                <strong>Ditolak oleh {{ $salesOrder->rejected_by ?? '-' }}</strong>
                @if(!empty($salesOrder->rejected_date)) ({{ $salesOrder->rejected_date }}) @endif
                @if(!empty($salesOrder->rejection_reason)) — Alasan: {{ $salesOrder->rejection_reason }} @endif
              </div>
            @endif

            <!-- ── BUYER + SALES DETAIL ── -->
            <div class="row mb-4">
              <!-- BUYER -->
              <div class="{{ $isSludgeSo ? 'col-md-12' : 'col-md-5' }} mb-3 mb-md-0" id="buyerCol">
                <div class="section-header">Buyer</div>
                <div class="section-body">
                  <div class="form-group mb-2">
                    <label class="form-label">PIC</label>
                    <input type="text" class="text-black form-control" id="buyerPic" placeholder="Nama PIC" value="{{ $salesOrder->buyer_pic }}" />
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Kota</label>
                    <input type="text" class="text-black form-control" id="buyerKota" placeholder="Kota" value="{{ $salesOrder->buyer_city }}" />
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Provinsi</label>
                    <input type="text" class="text-black form-control" id="buyerProvinsi" placeholder="Provinsi" value="{{ $salesOrder->buyer_province }}" />
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Phone</label>
                    <input type="text" class="text-black form-control" id="buyerPhone" placeholder="+62 xxx-xxxx-xxxx" value="{{ $salesOrder->buyer_phone }}" />
                  </div>
                  <div id="buyerPaymentTermSlot">
                    @if($isSludgeSo)
                      <div class="form-group mb-0" id="paymentTermGroup">
                        <label class="form-label">Payment Term</label>
                        <input type="text" id="paymentTerm" class="text-black form-control" value="{{ $salesOrder->payment_term }}">
                      </div>
                    @endif
                  </div>
                </div>
              </div>

              <!-- SALES DETAIL -->
              <div class="col-md-7" id="salesDetailCol" style="{{ $isSludgeSo ? 'display:none;' : '' }}">
                <div class="section-header">Sales Detail</div>
                <div class="section-body" id="salesDetailBody">
                  <div class="form-group mb-2">
                    <label class="form-label">Site</label>
                    <input type="hidden" id="site_id" name="site_id" value="{{ $salesOrder->site_id }}" />
                    <select class="text-black form-control" id="site" name="site" value="{{ $salesOrder->site }}" style="width: 100%;" data-selected="{{ $salesOrder->site }}">
                      <option value="" {{ empty($salesOrder->site) ? 'selected' : '' }}>-- Pilih Site --</option>
                      @if(!empty($salesOrder->site))
                        <option value="{{ $salesOrder->site }}" selected>{{ $salesOrder->site }}</option>
                      @endif
                    </select>
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Site Address</label>
                    <textarea class="text-black form-control" id="siteAddress" rows="3" placeholder="Alamat lengkap site">{{ $salesOrder->site_address }}</textarea>
                  </div>
                  @if(!$isSludgeSo)
                    <div class="form-group mb-0" id="paymentTermGroup">
                      <label class="form-label">Payment Term</label>
                      {{-- <select class="text-black form-control" id="paymentTerm">
                        <option value="Transfer" {{ $salesOrder->payment_term == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                      </select> --}}
                      <input type="text" id="paymentTerm" class="text-black form-control" value="{{ $salesOrder->payment_term }}">
                    </div>
                  @endif
                </div>
              </div>
            </div>

            <!-- ── ITEMS TABLE (KEMASAN & AMPAS) ── -->
            <div class="mb-3" id="kemasanItemsSection" style="{{ $isSludgeSo ? 'display:none;' : '' }}">
              <div class="section-header d-flex justify-content-between align-items-center">
                <span>Item / Produk</span>
                <button class="btn btn-primary btn-add-row no-print" onclick="addRow()">
                  <i class="fas fa-plus mr-1"></i>Tambah Baris
                </button>
              </div>
              <div class="section-body p-0">
                <div class="items-table-wrap">
                  <table class="items-table" id="itemsTable">
                    <thead>
                      <tr>
                        <th class="num-col">No.</th>
                        <th class="desc-col">Description</th>
                        <th class="stock-col text-center">Stok</th>
                        <th class="qty-col text-center">QTY</th>
                        <th class="unit-col text-center">Unit</th>
                        <th class="price-col text-right">Unit Price (IDR)</th>
                        <th class="total-col text-right">Total (IDR)</th>
                        <th class="no-print" style="width:48px;"></th>
                      </tr>
                    </thead>
                    <tbody id="itemsBody">
                      <!-- rows injected by JS -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- ── SLUDGE SECTION ── -->
            @include('cns.sales.partials.sludge-section', [
              'drainings' => $drainings ?? collect(),
              'selectedDrainingId' => $salesOrder->draining_id ?? ($firstDetail->product_code ?? null),
              'selectedMovementId' => $salesOrder->sludge_movement_id ?? null,
              'selectedDrainingCode' => $salesOrder->draining_code ?? '',
              'sludgeQty' => $isSludgeSo ? ($firstDetail->qty ?? 0) : 0,
              'sludgePrice' => $isSludgeSo ? ($firstDetail->unit_price ?? 0) : 0,
              'isSludge' => $isSludgeSo,
            ])

            <!-- ── SUMMARY TOTALS ── -->
            <div class="mb-3" id="summaryTotalsSection">
              <div class="section-body p-0">
                <div class="items-table-wrap">
                  <table class="items-table">
                    <tfoot>
                      <tr class="subtotal-row">
                        <td colspan="6" class="text-right font-weight-600">SUBTOTAL</td>
                        <td class="text-right font-weight-600 total-col" id="subtotalDisplay">{{ number_format((float) $salesOrder->subtotal, 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                        <td colspan="6" class="text-right">TAX</td>
                        <td class="text-right total-col">
                          <input type="text" inputmode="decimal" class="text-black form-control form-control-sm text-right cns-decimal" id="taxAmount" value="{{ number_format((float) $salesOrder->tax, 2, '.', ',') }}" onchange="recalc()" style="width:110px;margin-left:auto;" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="6" class="text-right">SHIPPING</td>
                        <td class="text-right total-col">
                          <input type="text" inputmode="decimal" class="text-black form-control form-control-sm text-right cns-decimal" id="shippingAmount" value="{{ number_format((float) $salesOrder->shipping, 2, '.', ',') }}" onchange="recalc()" style="width:110px;margin-left:auto;" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="6" class="text-right">OTHER</td>
                        <td class="text-right total-col">
                          <input type="text" inputmode="decimal" class="text-black form-control form-control-sm text-right cns-decimal" id="otherAmount" value="{{ number_format((float) $salesOrder->other, 2, '.', ',') }}" onchange="recalc()" style="width:110px;margin-left:auto;" />
                        </td>
                      </tr>
                      <tr class="total-row">
                        <td colspan="6" class="text-right">TOTAL</td>
                        <input type="hidden" name="totalAmount" id="totalAmount" value="{{ $salesOrder->total }}">
                        <td class="text-right total-col" id="totalDisplay">Rp {{ number_format((float) $salesOrder->total, 2, '.', ',') }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

            <!-- ── REMARKS ── -->
            <div class="mb-4">
              <div class="section-header">Remarks</div>
              <div class="section-body">
                <textarea class="text-black form-control" id="remarks" rows="3" placeholder="Catatan tambahan...">{{ $salesOrder->remarks }}</textarea>
              </div>
            </div>

          </div><!-- /p-4 -->
        </div>
        <div id="divtom">
          <!-- ── ACTION BUTTONS ── -->
          <div class="d-flex justify-content-center align-items-center flex-wrap no-print" style="gap: 10px;">
            @if($soStatus === 'PENDING')
              <button class="btn-reset" onclick="resetForm()">
                <i class="fas fa-undo mr-1"></i>Reset
              </button>
              <button class="btn-submit" onclick="saveSO()">
                <i class="fas fa-save mr-2"></i>Simpan Sales Order
              </button>
              @can('cns.sales_order.approve_gudang')
                <button type="button" id="ApproveGudang" class="btn btn-primary btn-lg" onclick="SalesOrderApproveGudang({{ $salesOrder->id }})">
                  Approve Kepala Gudang
                </button>
              @endcan
            @elseif($soStatus === 'APPROVED_GUDANG')
              @can('cns.sales_order.approve_buyer')
                <button type="button" id="ApproveBuyer" class="btn btn-warning btn-lg" onclick="SalesOrderOpenBuyerSignModal({{ $salesOrder->id }})">
                  TTD Buyer
                </button>
              @endcan
            @elseif($soStatus === 'SIGNED_BUYER')
              @can('cns.sales_order.approve_manager')
                <button type="button" id="ApproveSalesOrder" class="btn btn-success btn-lg" onclick="SalesOrderApprove({{ $salesOrder->id }})">
                  Approve Manager CNS
                </button>
              @endcan
            @elseif($soStatus === 'APPROVED')
              <button type="button" id="PrintSuratJalanDetail" class="btn btn-secondary btn-lg" onclick="window.open('cns-sales-order-surat-jalan-print?task={{ $salesOrder->id }}', '_blank')">
                Print Surat Jalan
              </button>
            @endif

            @if(!in_array($soStatus, ['APPROVED', 'REJECTED'], true))
              @can('cns.sales_order.edit')
                <button type="button" id="RejectSalesOrder" class="btn btn-danger btn-lg" onclick="SalesOrderReject({{ $salesOrder->id }})">
                  Reject Sales Order
                </button>
              @endcan
            @endif
          </div>
        </div>
      </div>
    </div>
    <div id="DivProcess"></div>

  </div>

  <!-- Modal TTD Buyer -->
  <div class="modal fade" id="buyerSignatureModal" tabindex="-1" role="dialog" aria-labelledby="buyerSignatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="buyerSignatureModalLabel">Tanda Tangan Buyer</h5>
          <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#buyerSignatureModal').modal('hide');">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="buyerSignedByInput">Nama Penerima / Buyer (Opsional)</label>
            <input type="text" id="buyerSignedByInput" class="text-black form-control" placeholder="Kosongkan untuk pakai PIC Buyer di SO" />
          </div>
          <div class="form-group mb-0">
            <label>Tanda Tangan di sini:</label>
            <div style="border: 2px solid #333; background: #fff; touch-action: none;">
              <canvas id="buyerSignatureCanvas" width="740" height="280" style="width: 100%; height: 280px; cursor: crosshair; display: block;"></canvas>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="clearBuyerSignature()">Clear</button>
          <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#buyerSignatureModal').modal('hide');">Batal</button>
          <button type="button" class="btn btn-primary" onclick="submitBuyerSignature()">Simpan TTD Buyer</button>
        </div>
      </div>
    </div>
  </div>
  @php
    $defaultItemsData = $isSludgeSo
      ? collect()
      : collect($details)->map(function ($item) {
          return [
            'desc'  => $item->description,
            'qty'   => $item->qty,
            'unit'  => $item->unit ?? '',
            'price' => $item->unit_price,
          ];
        })->values();
  @endphp
  <script>
    // ── Default items ──
    var defaultItems = @json($defaultItemsData);

    function reloadAfterSoAction(soId) {
      if (typeof refreshdata === 'function') {
        refreshdata();
      }
      if (typeof LoadM === 'function') {
        LoadM('divlistOpportunity', 'cns-sales-order-detail?task=' + soId);
      }
    }

    function SalesOrderApproveGudang(soId) {
      swal({
        title: 'Konfirmasi Kepala Gudang',
        text: 'Approve Sales Order ini sebagai Kepala Gudang?',
        icon: 'warning',
        buttons: ['Batal', 'Ya, Approve']
      }).then(function(confirmed) {
        if (!confirmed) return;

        $.ajax({
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          url: 'cns-sales-order-approve-gudang/' + soId + '?_token={{ csrf_token() }}',
          type: 'POST',
          dataType: 'json',
          success: function(res) {
            if (res && res.status === 'success') {
              swal('Berhasil!', res.message, 'success').then(function() {
                reloadAfterSoAction(soId);
              });
            } else {
              swal('Error', (res && res.message) || 'Gagal approve Kepala Gudang.', 'error');
            }
          },
          error: function(xhr) {
            var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || 'Terjadi kesalahan sistem.');
            swal('Error', msg, 'error');
          }
        });
      });
    }

    var buyerCanvasEl = document.getElementById('buyerSignatureCanvas');
    var buyerCtx = buyerCanvasEl ? buyerCanvasEl.getContext('2d') : null;
    var buyerIsDrawing = false;
    var buyerHasStrokes = false;
    var activeBuyerSoId = null;

    if (buyerCanvasEl && buyerCtx) {
      buyerCtx.lineWidth = 3;
      buyerCtx.lineCap = 'round';
      buyerCtx.lineJoin = 'round';
      buyerCtx.strokeStyle = '#000';

      var getCanvasPos = function(e) {
        var rect = buyerCanvasEl.getBoundingClientRect();
        var clientX = e.touches ? e.touches[0].clientX : e.clientX;
        var clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
          x: (clientX - rect.left) * (buyerCanvasEl.width / rect.width),
          y: (clientY - rect.top) * (buyerCanvasEl.height / rect.height)
        };
      };

      var startDraw = function(e) {
        e.preventDefault();
        buyerIsDrawing = true;
        buyerHasStrokes = true;
        var pos = getCanvasPos(e);
        buyerCtx.beginPath();
        buyerCtx.moveTo(pos.x, pos.y);
      };
      var moveDraw = function(e) {
        if (!buyerIsDrawing) return;
        e.preventDefault();
        var pos = getCanvasPos(e);
        buyerCtx.lineTo(pos.x, pos.y);
        buyerCtx.stroke();
      };
      var endDraw = function() { buyerIsDrawing = false; };

      buyerCanvasEl.addEventListener('mousedown', startDraw);
      buyerCanvasEl.addEventListener('mousemove', moveDraw);
      buyerCanvasEl.addEventListener('mouseup', endDraw);
      buyerCanvasEl.addEventListener('mouseleave', endDraw);
      buyerCanvasEl.addEventListener('touchstart', startDraw, { passive: false });
      buyerCanvasEl.addEventListener('touchmove', moveDraw, { passive: false });
      buyerCanvasEl.addEventListener('touchend', endDraw);
    }

    function clearBuyerSignature() {
      if (buyerCanvasEl && buyerCtx) {
        buyerCtx.clearRect(0, 0, buyerCanvasEl.width, buyerCanvasEl.height);
        buyerHasStrokes = false;
      }
    }

    function SalesOrderOpenBuyerSignModal(soId) {
      activeBuyerSoId = soId;
      $('#buyerSignedByInput').val('');
      clearBuyerSignature();
      $('#buyerSignatureModal').modal('show');
    }

    function submitBuyerSignature() {
      if (!activeBuyerSoId) return;
      if (!buyerHasStrokes) {
        swal('Perhatian', 'Silakan tanda tangan terlebih dahulu.', 'warning');
        return;
      }

      var dataUrl = buyerCanvasEl.toDataURL('image/png');
      $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: 'cns-sales-order-approve-buyer/' + activeBuyerSoId + '?_token={{ csrf_token() }}',
        type: 'POST',
        data: {
          signature: dataUrl,
          buyer_signed_by: $('#buyerSignedByInput').val()
        },
        dataType: 'json',
        success: function(res) {
          if (res && res.status === 'success') {
            $('#buyerSignatureModal').modal('hide');
            swal('Berhasil!', res.message, 'success').then(function() {
              reloadAfterSoAction(activeBuyerSoId);
            });
          } else {
            swal('Error', (res && res.message) || 'Gagal simpan TTD Buyer.', 'error');
          }
        },
        error: function(xhr) {
          var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || 'Terjadi kesalahan sistem.');
          swal('Error', msg, 'error');
        }
      });
    }

    function SalesOrderApprove(soId) {
      swal({
        title: 'Final Approve Manager CNS',
        text: 'Approve final Sales Order ini? Stok kemasan akan dipotong dan Surat Jalan otomatis dibuat.',
        icon: 'warning',
        buttons: ['Batal', 'Ya, Final Approve']
      }).then(function(confirmed) {
        if (!confirmed) return;

        $.ajax({
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          url: 'cns-sales-order-approve/' + soId + '?_token={{ csrf_token() }}',
          type: 'POST',
          dataType: 'json',
          success: function(res) {
            if (res && res.status === 'success') {
              swal({
                title: 'Berhasil!',
                text: res.message || 'Sales Order berhasil di-approve!',
                icon: 'success',
                buttons: {
                  cancel: 'Tutup',
                  print: {
                    text: 'Print Surat Jalan',
                    value: 'print'
                  }
                }
              }).then(function(choice) {
                if (choice === 'print' && res.print_sj_url) {
                  window.open(res.print_sj_url, '_blank');
                }
                reloadAfterSoAction(soId);
              });
            } else {
              swal('Error', res.message || 'Gagal approve Sales Order.', 'error');
            }
          },
          error: function(xhr) {
            var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || 'Terjadi kesalahan sistem.');
            swal('Error', msg, 'error');
          }
        });
      });
    }

    function SalesOrderReject(soId) {
      var reason = prompt("Masukkan alasan penolakan (opsional):");
      if (reason === null) return;

      $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: 'cns-sales-order-reject/' + soId + '?_token={{ csrf_token() }}',
        type: 'POST',
        data: { reason: reason },
        dataType: 'json',
        success: function(res) {
          if (res && res.status === 'success') {
            swal('Berhasil!', res.message || 'Sales Order berhasil di-reject.', 'success').then(function() {
              reloadAfterSoAction(soId);
            });
          } else {
            swal('Error', res.message || 'Gagal reject Sales Order.', 'error');
          }
        },
        error: function(xhr) {
          var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || 'Terjadi kesalahan sistem.');
          swal('Error', msg, 'error');
        }
      });
    }
  </script>
  @include('cns.sales.partials.item-row-script', ['submitUrl' => 'cns-sales-order-detail-submit'])