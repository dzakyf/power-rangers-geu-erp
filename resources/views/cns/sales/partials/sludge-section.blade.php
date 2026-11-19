@php
  $sludgeDrainings = $drainings ?? collect();
  $currentDrainingId = $selectedDrainingId ?? null;
  $currentMovementId = $selectedMovementId ?? null;
  $currentOptionValue = $currentMovementId ? ('M:' . $currentMovementId) : ($currentDrainingId !== null ? (string) $currentDrainingId : '');
  $currentSludgeQty = $sludgeQty ?? 0;
  $currentSludgePrice = $sludgePrice ?? 0;
  $currentSludgeCode = $selectedDrainingCode ?? '';
  $currentSludgeMax = 0;
  foreach ($sludgeDrainings as $d) {
      $optVal = (string) ($d->option_value ?? $d->id);
      if ($currentOptionValue !== '' && $optVal === $currentOptionValue) {
          $currentSludgeMax = (float) ($d->available_qty_sludge ?? $d->qty_sludge ?? 0);
          if ($currentSludgeCode === '') {
              $currentSludgeCode = (string) $d->draining_code;
          }
          break;
      }
  }
@endphp

<div id="sludgeItemsSection" class="mb-3" style="{{ !empty($isSludge) ? '' : 'display:none;' }}">
  <div class="section-header d-flex justify-content-between align-items-center">
    <span>Item Sludge (Draining / Moving Gudang)</span>
  </div>
  <div class="section-body p-0">
    <div class="items-table-wrap">
      <table class="items-table">
        <thead>
          <tr>
            <th class="desc-col">Kode Draining / Moving</th>
            <th class="stock-col text-center">Sisa Qty Sludge</th>
            <th class="qty-col text-center">Qty Sludge</th>
            <th class="unit-col text-center">Unit</th>
            <th class="price-col text-right">Unit Price (IDR)</th>
            <th class="total-col text-right">Total (IDR)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="desc-col">
              <input type="hidden" id="sludgeDrainingCode" name="sludgeDrainingCode" value="{{ $currentSludgeCode }}">
              <select class="form-control form-control-sm" id="sludgeDrainingId" name="sludgeDrainingId" style="width:100%;">
                <option value="">-- Pilih Kode Draining / Moving --</option>
                @foreach($sludgeDrainings as $draining)
                  @php
                    $availQty = (float) ($draining->available_qty_sludge ?? $draining->qty_sludge ?? 0);
                    $optVal = (string) ($draining->option_value ?? $draining->id);
                    $isSelected = $currentOptionValue !== '' && $currentOptionValue === $optVal;
                  @endphp
                  <option value="{{ $optVal }}"
                          data-code="{{ $draining->draining_code }}"
                          data-qty="{{ $availQty }}"
                          {{ $isSelected ? 'selected' : '' }}>
                    {{ $draining->draining_code }} ({{ $draining->tank_name ?? '-' }} - Sisa: {{ number_format($availQty, 2, '.', ',') }} KG)
                  </option>
                @endforeach
              </select>
            </td>
            <td class="stock-col text-center align-middle" style="white-space:nowrap;">
              <span id="sludgeAvailableInfo" style="color: #000; font-size: 15px; font-weight: 700;">
                @if($currentDrainingId)
                  {{ number_format($currentSludgeMax, 2, '.', ',') }} KG
                @else
                  -
                @endif
              </span>
            </td>
            <td class="qty-col">
              <input type="text"
                     inputmode="decimal"
                     id="sludgeQty"
                     name="sludgeQty"
                     class="form-control form-control-sm text-center cns-decimal"
                     data-max-qty="{{ $currentSludgeMax }}"
                     value="{{ number_format((float) $currentSludgeQty, 2, '.', ',') }}"
                     oninput="validateSludgeQty(); recalc();"
                     onchange="validateSludgeQty(); recalc();" />
            </td>
            <td class="unit-col">
              <input type="text" id="sludgeUnit" class="form-control form-control-sm text-center" value="KG" readonly />
            </td>
            <td class="price-col">
              <input type="text"
                     inputmode="decimal"
                     id="sludgePrice"
                     name="sludgePrice"
                     class="form-control form-control-sm text-right cns-decimal"
                     value="{{ number_format((float) $currentSludgePrice, 2, '.', ',') }}"
                     oninput="recalc()"
                     onchange="recalc()"
                     placeholder="0.00" />
            </td>
            <td class="total-col text-right" id="sludgeTotalDisplay" style="font-weight:600;">
              {{ number_format((float) $currentSludgeQty * (float) $currentSludgePrice, 2, '.', ',') }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
