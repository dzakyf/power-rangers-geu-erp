  <script>
    var masterKemasanOptions = @json($kemasans ?? []);
    var rowCount = 0;

    function fmt(n) {
      return cnsFormatNumber(n, 2);
    }

    function escapeHtml(str) {
      return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function buildKemasanSelectHtml(idx, selectedDesc) {
      let matched = false;
      let optionsHtml = '<option value="">-- Pilih Kemasan --</option>';
      masterKemasanOptions.forEach(item => {
        const rawName = String(item.Name ?? '').trim();
        const isAmpas = Boolean(item.Is_Ampas) || rawName.toLowerCase().includes('ampas');
        const name = isAmpas ? rawName : `${rawName} ${item.Volume ?? 0}L`;
        const price = item.Price !== null && item.Price !== undefined ? Number(item.Price) : 0;
        const unit = String(item.Unit ?? '').trim() || 'Pcs';
        const isSelected = selectedDesc !== '' && name === selectedDesc;
        if (isSelected) matched = true;
        optionsHtml += `<option value="${escapeHtml(name)}" data-price="${price}" data-unit="${escapeHtml(unit)}"${isSelected ? ' selected' : ''}>${escapeHtml(name)}</option>`;
      });

      if (selectedDesc !== '' && !matched) {
        optionsHtml += `<option value="${escapeHtml(selectedDesc)}" data-price="" data-unit="" selected>${escapeHtml(selectedDesc)}</option>`;
      }

      return `<select class="form-control form-control-sm desc-select" id="description${idx}" style="width:100%;">${optionsHtml}</select>`;
    }

    function addRow(desc = '', qty = 1, price = 0, unit = '') {
      rowCount++;
      const idx = rowCount;
      const qtyNum = cnsParseNumber(qty, 0);
      const priceNum = cnsParseNumber(price, 0);
      const total = qtyNum * priceNum;
      const tbody = document.getElementById('itemsBody');
      const tr = document.createElement('tr');
      tr.id = 'row_' + idx;
      tr.innerHTML = `
      <td class="num-col text-center row-num">${tbody.rows.length + 1}</td>
      <td class="desc-col">${buildKemasanSelectHtml(idx, desc)}</td>
      <td class="qty-col"><input type="text" inputmode="decimal" id="qty${idx}" class="form-control form-control-sm text-center qty-inp cns-decimal" value="${cnsFormatNumber(qtyNum, 2)}" onchange="recalc()"/></td>
      <td class="unit-col"><input type="text" id="item_unit${idx}" class="form-control form-control-sm text-center" value="${escapeHtml(unit)}" placeholder="Unit"/></td>
      <td class="price-col"><input type="text" inputmode="decimal" id="unit${idx}" class="form-control form-control-sm text-right price-inp cns-decimal" value="${cnsFormatNumber(priceNum, 2)}" onchange="recalc()" placeholder="0.00"/></td>
      <td class="total-col text-right total-cell" style="font-weight:600;">${fmt(total)}</td>
      <td class="no-print text-center"><button class="btn btn-danger btn-remove-row" onclick="removeRow('row_${idx}')">X</button></td>
    `;
      tbody.appendChild(tr);
      cnsInitDecimalInputs(tr);

      const $select = $('#description' + idx);
      const unitInput = document.getElementById('item_unit' + idx);
      if (unitInput && !String(unitInput.value ?? '').trim() && desc !== '') {
        const matchedUnit = $select.find('option:selected').attr('data-unit');
        if (matchedUnit) unitInput.value = matchedUnit;
      }
      if ($.fn.select2) {
        $select.select2({
          width: '100%',
          placeholder: '-- Pilih Kemasan --',
          allowClear: true
        });
      }

      $select.on('change', function() {
        const selectedOpt = $(this).find('option:selected');
        const val = $(this).val();
        const kemasanUnit = selectedOpt.attr('data-unit');
        const kemasanPrice = selectedOpt.attr('data-price');
        if (val && kemasanUnit !== undefined) {
          document.getElementById('item_unit' + idx).value = kemasanUnit;
        } else if (!val) {
          document.getElementById('item_unit' + idx).value = '';
        }
        if (val && kemasanPrice !== undefined && kemasanPrice !== '') {
          document.getElementById('unit' + idx).value = cnsFormatNumber(kemasanPrice, 2);
        } else if (!val) {
          document.getElementById('unit' + idx).value = cnsFormatNumber(0, 2);
        }
        recalc();
      });

      recalc();
    }

    function removeRow(id) {
      const el = document.getElementById(id);
      if (el) {
        if ($.fn.select2) {
          $(el).find('select.desc-select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
              $(this).select2('destroy');
            }
          });
        }
        el.remove();
      }
      renumber();
      recalc();
    }

    function renumber() {
      document.querySelectorAll('#itemsBody tr .row-num').forEach((el, i) => {
        el.textContent = i + 1;
      });
    }

    function recalc() {
      let subtotal = 0;
      document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const qty = cnsParseNumber(tr.querySelector('.qty-inp')?.value, 0);
        const price = cnsParseNumber(tr.querySelector('.price-inp')?.value, 0);
        const tot = qty * price;
        const cell = tr.querySelector('.total-cell');
        if (cell) cell.textContent = fmt(tot);
        subtotal += tot;
      });
      const tax = cnsParseNumber(document.getElementById('taxAmount').value, 0);
      const ship = cnsParseNumber(document.getElementById('shippingAmount').value, 0);
      const other = cnsParseNumber(document.getElementById('otherAmount').value, 0);
      const total = subtotal + tax + ship + other;
      document.getElementById('subtotalDisplay').textContent = fmt(subtotal);
      document.getElementById('totalDisplay').textContent = 'Rp ' + fmt(total);
      const totalEl = document.getElementById('totalAmount');
      if (totalEl) totalEl.value = total.toFixed(2);
    }

    function resetForm() {
      if (!confirm('Reset semua data form?')) return;
      if ($.fn.select2) {
        $('#itemsBody select.desc-select').each(function() {
          if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
          }
        });
      }
      document.getElementById('itemsBody').innerHTML = '';
      rowCount = 0;
      defaultItems.forEach(i => addRow(i.desc, i.qty, i.price, i.unit ?? ''));
      document.getElementById('taxAmount').value = cnsFormatNumber(0, 2);
      document.getElementById('shippingAmount').value = cnsFormatNumber(0, 2);
      document.getElementById('otherAmount').value = cnsFormatNumber(0, 2);
      recalc();
    }

    function saveSO() {
      const soCode = document.getElementById('soCode').value;
      const soDate = document.getElementById('soDate').value;
      if (!soCode || !soDate) {
        alert('SO Code dan tanggal wajib diisi!');
        return;
      }

      submitSalesOrder();
    }

    // ── Init ──
    (defaultItems || []).forEach(i => addRow(i.desc, i.qty, i.price, i.unit ?? ''));
    if ((!defaultItems || defaultItems.length === 0) && !document.getElementById('soId')) {
      recalc();
    }

    var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Stuffing", "Checking_BC", "Closing_CY", "ETD", "ATD", "ETA", "ATA"]);
    myCalendar.setDateFormat("%Y-%m-%d");

    function submitSalesOrder() {
      gettingVars = getAllValues();

      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ $submitUrl }}?_token={{ csrf_token() }}",
        type: "POST",
        data: gettingVars,
        success: function(data) {
          var dataPos = String(data).indexOf("success");
          if (dataPos != '-1') {
            alert('Data Sales Order Submitted!');
            refreshdata();
            $('#divlistOpportunity').html('');
          } else {
            alert('Sales Order Failed to Submit!');
          }
        },
        error: function() {
          alert("Error Internal! Please Try again!");
        }
      });
    }

    function getAllValues() {
      var inputValues = [];
      $('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
          var name = $(this).attr("name");
          if (name) inputValues.push(encodeURIComponent(name) + "=" + encodeURIComponent($(this).val() ?? ''));
        } else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
          var id = $(this).attr("id");
          var val = $(this).hasClass('cns-decimal') ? cnsParseNumber($(this).val(), 0) : ($(this).val() ?? '');
          if (id) inputValues.push(encodeURIComponent(id) + "=" + encodeURIComponent(val));
        }
      });
      return inputValues.join('&');
    }
  </script>
