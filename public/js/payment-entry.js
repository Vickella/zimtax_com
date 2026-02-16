(function () {
  const cfg = window.__PAYMENT_ENTRY__ || { openSalesInvoices: [], openPurchaseInvoices: [] };

  const paymentTypeEl = document.getElementById('payment_type');
  const customerEl = document.getElementById('customer_id');
  const supplierEl = document.getElementById('supplier_id');

  const amountEl = document.getElementById('amount');
  const sumAmountEl = document.getElementById('sum_amount');
  const sumAllocatedEl = document.getElementById('sum_allocated');
  const sumUnallocEl = document.getElementById('sum_unallocated');

  const btnAdd = document.getElementById('btn_add_alloc');
  const tbody = document.getElementById('alloc_tbody');
  const tpl = document.getElementById('alloc_row_tpl');

  function money(n) {
    const x = Number(n || 0);
    return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function parseInvoiceKey(key) {
    if (!key) return null;
    // "SI:3" or "PI:9"
    const parts = String(key).split(':');
    if (parts.length !== 2) return null;
    const prefix = parts[0];
    const id = Number(parts[1]);
    if (!id) return null;

    if (prefix === 'SI') return { type: 'SalesInvoice', id };
    if (prefix === 'PI') return { type: 'PurchaseInvoice', id };
    return null;
  }

  function invoiceOptionsForType(type) {
    // RECEIVE => Sales invoices, PAY => Purchase invoices
    if (type === 'RECEIVE') return cfg.openSalesInvoices || [];
    if (type === 'PAY') return cfg.openPurchaseInvoices || [];
    // fallback show both
    return [...(cfg.openSalesInvoices || []), ...(cfg.openPurchaseInvoices || [])];
  }

  function setPartyVisibility() {
    const t = paymentTypeEl ? paymentTypeEl.value : 'RECEIVE';

    if (customerEl) customerEl.style.display = (t === 'RECEIVE') ? '' : 'none';
    if (supplierEl) supplierEl.style.display = (t === 'PAY') ? '' : 'none';

    // Clear hidden party to avoid wrong validation data
    if (t === 'RECEIVE' && supplierEl) supplierEl.value = '';
    if (t === 'PAY' && customerEl) customerEl.value = '';
  }

  function buildInvoiceSelect(selectEl) {
    const t = paymentTypeEl ? paymentTypeEl.value : 'RECEIVE';
    const options = invoiceOptionsForType(t);

    selectEl.innerHTML = `<option value="">Select invoice...</option>`;
    options.forEach(inv => {
      const label = `${inv.no} • Out: ${money(inv.outstanding)}`;
      const opt = document.createElement('option');
      opt.value = inv.key;
      opt.dataset.outstanding = String(inv.outstanding);
      opt.textContent = label;
      selectEl.appendChild(opt);
    });
  }

  function rowSetNames(row, idx) {
    // invoice_key
    const inv = row.querySelector('.alloc-invoice');
    inv.name = `allocations[${idx}][invoice_key]`;

    const type = row.querySelector('.alloc-invoice-type');
    type.name = `allocations[${idx}][invoice_type]`;

    const id = row.querySelector('.alloc-invoice-id');
    id.name = `allocations[${idx}][invoice_id]`;

    const amt = row.querySelector('.alloc-amount');
    amt.name = `allocations[${idx}][allocated_amount]`;
  }

  function renumberRows() {
    const rows = [...tbody.querySelectorAll('tr[data-row="1"]')];
    rows.forEach((r, i) => rowSetNames(r, i));
  }

  function updateRowOutstanding(row) {
    const invSel = row.querySelector('.alloc-invoice');
    const outSpan = row.querySelector('.alloc-outstanding');
    const invTypeEl = row.querySelector('.alloc-invoice-type');
    const invIdEl = row.querySelector('.alloc-invoice-id');

    const selectedKey = invSel.value;
    const parsed = parseInvoiceKey(selectedKey);

    let outstanding = 0;
    if (invSel.selectedOptions && invSel.selectedOptions[0]) {
      outstanding = Number(invSel.selectedOptions[0].dataset.outstanding || 0);
    }

    outSpan.textContent = money(outstanding);

    // Fill hidden structured fields
    invTypeEl.value = parsed ? parsed.type : '';
    invIdEl.value = parsed ? String(parsed.id) : '';
  }

  function sumAllocated() {
    const rows = [...tbody.querySelectorAll('tr[data-row="1"]')];
    return rows.reduce((acc, r) => {
      const amt = Number((r.querySelector('.alloc-amount')?.value || '0').replace(/,/g, ''));
      return acc + (isFinite(amt) ? amt : 0);
    }, 0);
  }

  function validateRows() {
    const totalAmount = Number((amountEl?.value || '0').replace(/,/g, '')) || 0;

    let ok = true;
    const rows = [...tbody.querySelectorAll('tr[data-row="1"]')];

    rows.forEach(r => {
      const err = r.querySelector('.alloc-error');
      err.classList.add('hidden');
      err.textContent = '';

      const invSel = r.querySelector('.alloc-invoice');
      const amtEl = r.querySelector('.alloc-amount');

      const invOk = !!invSel.value;
      const amt = Number((amtEl.value || '0').replace(/,/g, '')) || 0;

      const outstanding = Number(invSel.selectedOptions?.[0]?.dataset?.outstanding || 0);

      if (amt < 0) {
        ok = false;
        err.textContent = 'Allocated amount cannot be negative.';
        err.classList.remove('hidden');
      } else if (invOk && amt > outstanding + 0.0001) {
        ok = false;
        err.textContent = 'Allocated exceeds invoice outstanding.';
        err.classList.remove('hidden');
      }
    });

    const allocated = sumAllocated();
    if (allocated > totalAmount + 0.0001) ok = false;

    // Summary
    if (sumAmountEl) sumAmountEl.textContent = money(totalAmount);
    if (sumAllocatedEl) sumAllocatedEl.textContent = money(allocated);
    if (sumUnallocEl) sumUnallocEl.textContent = money(totalAmount - allocated);

    return ok;
  }

  function wireRow(row) {
    const invSel = row.querySelector('.alloc-invoice');
    const amtEl = row.querySelector('.alloc-amount');
    const removeBtn = row.querySelector('.btn-remove');

    buildInvoiceSelect(invSel);
    updateRowOutstanding(row);

    invSel.addEventListener('change', () => {
      updateRowOutstanding(row);
      validateRows();
    });

    amtEl.addEventListener('input', () => validateRows());

    removeBtn.addEventListener('click', () => {
      row.remove();
      renumberRows();
      validateRows();
    });
  }

  function addRow() {
    const node = tpl.content.cloneNode(true);
    const row = node.querySelector('tr[data-row="1"]');
    tbody.appendChild(row);
    wireRow(row);
    renumberRows();
    validateRows();
  }

  function refreshAllInvoiceSelects() {
    const rows = [...tbody.querySelectorAll('tr[data-row="1"]')];
    rows.forEach(r => {
      const sel = r.querySelector('.alloc-invoice');
      const current = sel.value;
      buildInvoiceSelect(sel);

      // try restore selected if exists
      if (current) sel.value = current;
      updateRowOutstanding(r);
    });
    validateRows();
  }

  // init
  setPartyVisibility();
  validateRows();

  if (paymentTypeEl) {
    paymentTypeEl.addEventListener('change', () => {
      setPartyVisibility();
      refreshAllInvoiceSelects();
    });
  }

  if (amountEl) amountEl.addEventListener('input', () => validateRows());

  if (btnAdd) btnAdd.addEventListener('click', addRow);

  // Wire any server-rendered rows
  const existingRows = [...tbody.querySelectorAll('tr[data-row="1"]')];
  existingRows.forEach(r => wireRow(r));
  renumberRows();
  validateRows();

  // If there are no rows initially, add one
  if (existingRows.length === 0) addRow();

})();
