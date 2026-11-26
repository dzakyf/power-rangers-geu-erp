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
</style>
<div class="mt-3">
  <div class="cns-section-header">SALES ORDER NEW</div>
  <div class="card cns-card">
    <div class="card-body">
      <div id="ContentPages">
        <input type="hidden" id="soType" name="soType" value="" />
        <div class="p-3 p-md-4 border-bottom bg-light" id="soTypeSelector">
          <div class="mb-2 font-weight-bold text-uppercase" style="font-size: 13px; color: var(--geu-green-dark); letter-spacing: 0.5px;">
            Pilih Jenis Sales Order
          </div>
          <div class="row">
            <div class="col-md-6 mb-2 mb-md-0">
              <button type="button" class="btn btn-outline-success btn-block text-left p-3 so-type-choice" data-so-type="SLUDGE" onclick="selectSoType('SLUDGE')" style="border-width:2px;">
                <div class="font-weight-bold" style="font-size:15px;">1. Sludge</div>
                <div class="small text-muted">Pilih kode draining dan qty sludge dari hasil draining tangki.</div>
              </button>
            </div>
            <div class="col-md-6">
              <button type="button" class="btn btn-outline-primary btn-block text-left p-3 so-type-choice" data-so-type="KEMASAN_AMPAS" onclick="selectSoType('KEMASAN_AMPAS')" style="border-width:2px;">
                <div class="font-weight-bold" style="font-size:15px;">2. Kemasan dan Ampas</div>
                <div class="small text-muted">Penjualan produk kemasan dan ampas berdasarkan stok site.</div>
              </button>
            </div>
          </div>
        </div>
        <div id="soFormContainer" style="display:none;">
        <div class="FormInput_Form_Content">
          <!-- ── TITLE BAR ── -->
          <div class="so-titlebar">
            <h2><i class="fas fa-file-invoice mr-2"></i>Sales Order</h2>
            <div class="d-flex align-items-center gap-3 flex-wrap" style="gap:16px;">
              <div class="d-flex align-items-center">
                <label class="form-label mb-0 mr-2" style="white-space:nowrap;">DATE</label>
                <input type="date" id="soDate" class="text-black form-control form-control-sm" style="width:150px;" value="{{ date("Y-m-d") }}" />
              </div>
              <div class="d-flex align-items-center ml-3">
                <label class="form-label mb-0 mr-2" style="white-space:nowrap;">SO CODE</label>
                <input type="text" id="soCode" class="text-black form-control form-control-sm" style="width:160px;" value="{{ $socode }}" />
              </div>
            </div>
          </div>

          <div class="p-3 p-md-4">

            <!-- ── BUYER + SALES DETAIL ── -->
            <div class="row mb-4">
              <!-- BUYER -->
              <div class="col-md-5 mb-3 mb-md-0" id="buyerCol">
                <div class="section-header">Buyer</div>
                <div class="section-body">
                  <div class="form-group mb-2">
                    <label class="form-label">PIC</label>
                    <input type="text" class="text-black form-control" id="buyerPic" placeholder="Nama PIC" />
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Kota</label>
                    <input type="text" class="text-black form-control" id="buyerKota" placeholder="Kota" />
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Provinsi</label>
                    <input type="text" class="text-black form-control" id="buyerProvinsi" placeholder="Provinsi" />
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Phone</label>
                    <input type="text" class="text-black form-control" id="buyerPhone" placeholder="+62 xxx-xxxx-xxxx" />
                  </div>
                  <div id="buyerPaymentTermSlot"></div>
                </div>
              </div>

              <!-- SALES DETAIL -->
              <div class="col-md-7" id="salesDetailCol">
                <div class="section-header">Sales Detail</div>
                <div class="section-body" id="salesDetailBody">
                  <div class="form-group mb-2">
                    <label class="form-label">Site</label>
                    <input type="hidden" id="site_id" name="site_id" value="" />
                    <select class="text-black form-control" id="site" name="site" value="" style="width: 100%;">
                      <option value="" selected>-- Pilih Site --</option>
                    </select>
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Site Address</label>
                    <textarea class="text-black form-control" id="siteAddress" rows="3" placeholder="Alamat lengkap site"></textarea>
                  </div>
                  <div class="form-group mb-0" id="paymentTermGroup">
                    <label class="form-label">Payment Term</label>
                    {{-- <select class="text-black form-control" id="paymentTerm">
                      <option value="Transfer" selected>Transfer</option>
                      <!-- <option value="COD">COD</option> -->
                      <!-- <option value="Credit 30">Credit 30 Hari</option>
                        <option value="Credit 60">Credit 60 Hari</option> -->
                      <!-- <option value="Net 30">Net 30</option> -->
                    </select> --}}
                    <input type="text" id="paymentTerm" class="text-black form-control" value="Transfer">
                  </div>
                </div>
              </div>
            </div>

            <!-- ── ITEMS TABLE (KEMASAN & AMPAS) ── -->
            <div class="mb-3" id="kemasanItemsSection">
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
            @include('cns.sales.partials.sludge-section', ['drainings' => $drainings ?? collect(), 'isSludge' => false])

            <!-- ── SUMMARY TOTALS ── -->
            <div class="mb-3" id="summaryTotalsSection">
              <div class="section-body p-0">
                <div class="items-table-wrap">
                  <table class="items-table">
                    <tfoot>
                      <tr class="subtotal-row">
                        <td colspan="6" class="text-right font-weight-600">SUBTOTAL</td>
                        <td class="text-right font-weight-600 total-col" id="subtotalDisplay">0</td>
                      </tr>
                      <tr>
                        <td colspan="6" class="text-right">TAX</td>
                        <td class="text-right total-col">
                          <input type="text" inputmode="decimal" class="text-black form-control form-control-sm text-right cns-decimal" id="taxAmount" value="0.00" onchange="recalc()" style="width:110px;margin-left:auto;" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="6" class="text-right">SHIPPING</td>
                        <td class="text-right total-col">
                          <input type="text" inputmode="decimal" class="text-black form-control form-control-sm text-right cns-decimal" id="shippingAmount" value="0.00" onchange="recalc()" style="width:110px;margin-left:auto;" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="6" class="text-right">OTHER</td>
                        <td class="text-right total-col">
                          <input type="text" inputmode="decimal" class="text-black form-control form-control-sm text-right cns-decimal" id="otherAmount" value="0.00" onchange="recalc()" style="width:110px;margin-left:auto;" />
                        </td>
                      </tr>
                      <tr class="total-row">
                        <td colspan="6" class="text-right">TOTAL</td>
                        <input type="hidden" name="totalAmount" id="totalAmount" value="">
                        <td class="text-right total-col" id="totalDisplay">Rp 0</td>
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
                <textarea class="text-black form-control" id="remarks" rows="3" placeholder="Catatan tambahan..."></textarea>
              </div>
            </div>

          </div><!-- /p-4 -->
        </div>
        <div id="divtom">
          <!-- ── ACTION BUTTONS ── -->
          <div class="text-center no-print">
            <button class="btn-reset mr-3" onclick="resetForm()">
              <i class="fas fa-undo mr-1"></i>Reset
            </button>
            <button class="btn-submit" onclick="saveSO()">
              <i class="fas fa-save mr-2"></i>Simpan Sales Order
            </button>
          </div>
        </div>
        </div><!-- /soFormContainer -->
      </div>
    </div>
    <div id="DivProcess"></div>

  </div>
  <script>
    // ── Default items ──
    var defaultItems = [];
  </script>
  @include('cns.sales.partials.item-row-script', ['submitUrl' => 'cns-sales-order-new-submit'])